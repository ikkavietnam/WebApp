<?php
// Xuất PDF phiếu YCMH và lưu lên thư mục chia sẻ (bind mount /mnt/itv_share)
// Input : JSON số nguyên InstanceID
// Output: {"ok", "msg", "pdf_link", "dataRES"}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../core/Authorization.php';
require_once __DIR__ . '/YCMH__Function_LOAD_Data_PHIEU.php';
require_once __DIR__ . '/../../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// 1. AUTH
checkLevel(3);
$UsID = (int)$_SESSION['user']['UsID'];
session_write_close(); // ❗ nhả khóa session sớm: tạo PDF chạy lâu, không chặn request khác

// 2. INPUT
$InstanceID = (int)(json_decode(file_get_contents('php://input'), true) ?? 0);

// 3. DỮ LIỆU PHIẾU
$dataRES = LOAD_Data_PHIEU($UsID, $InstanceID);
if (empty($dataRES['ok'])) {
    RESPONSE_CLIENT(400, $dataRES['msg'] ?? 'Dữ liệu không hợp lệ');
}

// 4. TEMPLATE HTML
$root = $_SERVER['DOCUMENT_ROOT'];
$logoFile = $root . '/assets/img/ITV128x128.png';
$logoSrc  = is_file($logoFile)
    ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoFile))
    : '';

$placeholders = [
    '{{LOGO_SRC}}'          => $logoSrc,
    '{{FONT_PATH}}'         => $root . '/assets/fonts',
    '{{SO_PHIEU}}'          => $dataRES['SO_PHIEU'],
    '{{NGAY_TAO}}'          => $dataRES['NGAY_TAO'],
    '{{NGUOI_TAO}}'         => $dataRES['NGUOI_TAO'],
    '{{MA_NV}}'             => $dataRES['MA_NV'],
    '{{BO_PHAN}}'           => $dataRES['BO_PHAN'],
    '{{DELIVERY}}'          => $dataRES['DELIVERY'],
    '{{TABLE_DETAIL_ROWS}}' => $dataRES['TABLE_DETAIL_ROWS'],
    '{{GHI_CHU}}'           => $dataRES['GHI_CHU'],
    '{{ATT_FILE}}'          => $dataRES['ATT_FILE'],
    '{{APPROVAL_ROWS}}'     => $dataRES['APPROVAL_ROWS'],
];
// strtr thay một lượt, nội dung đã chèn không bị thay thế lần nữa
$html = strtr(
    file_get_contents($root . '/view/ycmh/YCMH_TemplateForm.html'),
    array_map('strval', $placeholders)
);

// 5. RENDER PDF
$options = new Options();
$options->set([
    'isRemoteEnabled' => true,
    'defaultFont'     => 'DejaVu Sans', // Font hỗ trợ tiếng Việt
    'chroot'          => $root,
    'fontDir'         => $root . '/assets/fonts/font_cache',
    'fontCache'       => $root . '/assets/fonts/font_cache',
]);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// 6. HEADER SỐ TRANG (căn phải, vẽ trên mọi trang)
$so_phieu_number = $dataRES['so_phieu_number'];
$metrics = $dompdf->getFontMetrics();
$font    = $metrics->get_font('Roboto Condensed', 'bold');
$size    = 7;
$canvas  = $dompdf->getCanvas();

$sampleWidth = $metrics->getTextWidth("YCMH - {$so_phieu_number} Page 9 / 9", $font, $size);
$x = $canvas->get_width() - $sampleWidth - 10;
$canvas->page_text(
    $x, 10,
    "YCMH - {$so_phieu_number} Page {PAGE_NUM} / {PAGE_COUNT}",
    $font, $size, [0.1, 0.1, 0.1]
);

// 7. LƯU FILE LÊN SHARE
$saveDir  = '/mnt/itv_share/Data/YCMH/';
$mhhd_name = $so_phieu_number; // TODO: đổi thành mã phiếu khi chạy thật
$fileName = 'YCMH_' . str_replace('-', '_', $mhhd_name) . '.pdf';
$target   = $saveDir . $fileName;

if (!is_dir($saveDir)) {
    RESPONSE_CLIENT(500, "Không tìm thấy thư mục lưu PDF ($saveDir). Kiểm tra volume /mnt/itv_share đã được mount chưa. Liên hệ IT.");
}
if (file_put_contents($target, $dompdf->output()) === false) {
    RESPONSE_CLIENT(500, 'Lỗi: Không thể ghi file PDF lên thư mục chia sẻ!');
}

// 8. RESPONSE
echo json_encode([
    'ok'       => true,
    'msg'      => 'Lưu file PDF thành công',
    'pdf_link' => '\\\\ITV-SV\\Document\\Data\\YCMH\\' . $fileName, // UNC để lưu DB / hiển thị
    'dataRES'  => $dataRES,
], JSON_UNESCAPED_UNICODE);
