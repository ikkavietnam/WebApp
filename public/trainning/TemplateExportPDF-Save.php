<?php
// Gọi autoload của Composer
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// 1. Cấu hình tùy chọn cho Dompdf (Hỗ trợ tiếng Việt tốt hơn)
$options = new Options();
$options->set("isRemoteEnabled", true);
$options->set("defaultFont", "DejaVu Sans"); // Font hỗ trợ tiếng Việt chuẩn trong Dompdf
$options->set('chroot', '/var/www/html');

// 📌 CẤU HÌNH CHO PHÉP DOMPDF ĐỌC VÀ LƯU CACHE FONT TẠI THƯ MỤC TỰ TẠO
$options->set('fontDir', __DIR__ . '/fonts_cache');
$options->set('fontCache', __DIR__ . '/fonts_cache');


// Khởi tạo Dompdf với cấu hình
$dompdf = new Dompdf($options);


// KHỞI TẠO BIẾN
    $so_phieu_number        = 'ITV-261021-3143';
    $ngay_tao               = '2026-10-21';
    $nguoi_tao              = 'Bùi Đắc Thủy';
    $ma_nv                  = '1976';
    $bo_phan                = 'ADM';
    $delivery               = 'ITV';

$so_phieu = '
<div style="position: relative; display: inline-block;">
    <!-- Số phiếu hiển thị bên dưới -->
    <span style="font-weight: bold; font-size: 13px;">' . $so_phieu_number . '</span>
    
    <!-- Con dấu stamp đặt đè lên trên (position: absolute) -->
    <span style="position: absolute; top: -10px; left: -60px; border: 2px dashed #50505075; color: #50505075; font-size: 13px; font-weight: 700; padding: 5px 10px; text-transform: uppercase; white-space: nowrap; transform: rotate(-8deg);">
        <nobr>  BẢN NHÁP / DRAFT  </nobr>
    </span>

</div>';

/* ĐÃ HOÀN THÀNH
    <span style="position: absolute; top: -10px; left: -95px; border: 2px dashed #0b661f75; color: #0b661f75; font-size: 13px; font-weight: 700; padding: 5px 10px; text-transform: uppercase; white-space: nowrap; transform: rotate(-8deg);">
        <nobr>&check; ĐÃ HOÀN THÀNH /APPROVED</nobr>
    </span>
*/

/* ĐÃ HỦY
    <span style="position: absolute; top: -10px; left: -60px; border: 2px dashed #f92e1775; color: #f92e1775; font-size: 13px; font-weight: 700; padding: 5px 10px; text-transform: uppercase; white-space: nowrap; transform: rotate(-8deg);">
        <nobr> &#10005; ĐÃ HỦY / REJECTED </nobr>
    </span>
*/

/* ĐÃ HỦY
    <span style="position: absolute; top: -10px; left: -60px; border: 2px dashed #f92e1775; color: #f92e1775; font-size: 13px; font-weight: 700; padding: 5px 10px; text-transform: uppercase; white-space: nowrap; transform: rotate(-8deg);">
        <nobr> &#10005; ĐÃ HỦY / REJECTED </nobr>
    </span>
*/

$table_detail_rows      = ' 
    <tr>
        <td>10</td>
        <td style="vertical-align: top">Người mẫu, hoa hậu thời trang</td>
        <td>2</td>
        <td>SET</td>
        <td>Mặt : thon gọn V-line. Da : Trắng hồng, mịn màng. Dáng : cân đối, chân dài Chiều cao : 1m78 Cân nặng : 50kg Số đo 3 vòng : 75/55/85</td>
        <td style="vertical-align: top;">Chăm sóc nhân viên</td>
        <td>10 days</td>
    </tr>

    <tr>
        <td>10</td>
        <td style="vertical-align: top">Người mẫu, hoa hậu thời trang</td>
        <td>2</td>
        <td>SET</td>
        <td>Mặt : thon gọn V-line. Da : Trắng hồng, mịn màng. Dáng : cân đối, chân dài Chiều cao : 1m78 Cân nặng : 50kg Số đo 3 vòng : 75/55/85</td>
        <td style="vertical-align: top;">Chăm sóc nhân viên</td><td>10 days</td>
    </tr>                          
';


$ghi_chu                = "Nội dung ghi chú";
$ATT_FILE          ='KHÔNG CÓ FILE ĐÍNH KÈM';  // Nội dung hoặc thông báo đính kèm




$fileId   = '60';
$fileSec  = 'ayJrWUWksk';
$fileName = 'QUO Label Plan Sys ITV_15Mar26.xlsx';
$fileSize = '929.4 KB';

// Xác định loại file để đổi màu huy hiệu (Đỏ cho PDF, Xanh lá cho Excel)
$ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
$isExcel = ($ext === 'xlsx' || $ext === 'xls');
$badgeColor = $isExcel ? '#4CAF50' : '#E53935';
$badgeText  = $isExcel ? 'XLSX' : 'PDF';

$ATT_FILE = '
    <tr>
        <td style="width: 32px; vertical-align: middle; padding: 0; border: none;">
            <!-- Đã bỏ height cố định, dùng padding để text tự động căn giữa đều đẹp -->
            <span style="display: inline-block; background-color: ' . $badgeColor . '; color: #ffffff; font-size: 9px; font-weight: bold; padding: 7px 4px; border-radius: 2px; text-align: center; line-height: 1;">
                ' . $badgeText . '
            </span>
        </td>
        
        <td style="vertical-align: middle; padding: 0 10px 0 0px; border: none; text-align: left;">
            <a href="../../../../downloadAttach?Id=' . $fileId . '&amp;Type=YCMH&amp;Sec=' . $fileSec . '" target="_blank" title="' . htmlspecialchars($fileName) . '" style="font-size: 10px; font-weight: 700; color: #0732f2; text-decoration: none;">
                ' . htmlspecialchars($fileName) . '
            </a>

            <p style="font-size: 9px; font-style: italic; font-weight: 500; color: #575757; margin: 0;">
                ' . htmlspecialchars($fileSize) . ')
            </p>
        </td>
    </tr>

    <tr>
        <td style="width: 32px; vertical-align: middle; padding: 0; border: none;">
            <!-- Đã bỏ height cố định, dùng padding để text tự động căn giữa đều đẹp -->
            <span style="display: inline-block; background-color: ' . $badgeColor . '; color: #ffffff; font-size: 9px; font-weight: bold; padding: 7px 4px; border-radius: 2px; text-align: center; line-height: 1;">
                ' . $badgeText . '
            </span>
        </td>
        
        <td style="vertical-align: middle; padding: 0 10px 0 0px; border: none; text-align: left;">
            <a href="../../../../downloadAttach?Id=' . $fileId . '&amp;Type=YCMH&amp;Sec=' . $fileSec . '" target="_blank" title="' . htmlspecialchars($fileName) . '" style="font-size: 10px; font-weight: 700; color: #0732f2; text-decoration: none;">
                ' . htmlspecialchars($fileName) . '
            </a>

            <p style="font-size: 9px; font-style: italic; font-weight: 500; color: #575757; margin: 0;">
                ' . htmlspecialchars($fileSize) . ')
            </p>
        </td>
    </tr>
';


$approval_rows  ='
    <tr><td class="appcol" style=" font-style: normal; color: #2c2fff;">1</td>
            <td style=" font-style: normal; color: #2c2fff;">Administrator</td>
            <td class="appcol" style=" font-style: normal; color: #2c2fff;">9999</td>
            <td style=" font-style: normal; color: #2c2fff;">Staff</td>
            <td style=" font-style: normal; color: #2c2fff;">SINGLE</td>
            <td style=" font-style: normal; color: #2c2fff;">✓ Created</td>
            <td style=" font-style: normal; color: #2c2fff;">2026-10-22 14:36</td>
            <td style=" font-style: normal; color: #2c2fff;"></td>
        </tr>
        <tr>
            <td class="appcol" style=" font-style: normal; color: #2c2fff;">2</td>
            <td style=" font-style: normal; color: #2c2fff;">Lương Thị Linh</td>
            <td class="appcol" style=" font-style: normal; color: #2c2fff;">0235</td>
            <td style=" font-style: normal; color: #2c2fff;">Assistant Manager</td>
            <td style=" font-style: normal; color: #2c2fff;">SINGLE</td>
            <td style=" font-style: normal; color: #2c2fff;">✓ Approved</td>
            <td style=" font-style: normal; color: #2c2fff;">2026-10-22 16:32</td>
            <td style=" font-style: normal; color: #2c2fff;"></td>
        </tr>

        <tr>
            <td class="appcol" style=" font-weight: bold; color: #ff0000;">3</td>
            <td style=" font-weight: bold; color: #ff0000;">Mai Huyền Trang</td>
            <td class="appcol" style=" font-weight: bold; color: #ff0000;">2383</td>
            <td style=" font-weight: bold; color: #ff0000;">Staff</td>
            <td style=" font-weight: bold; color: #ff0000;">SINGLE</td>
            <td style=" font-weight: bold; color: #ff0000;">❌ Rejected</td>
            <td style=" font-weight: bold; color: #ff0000;">2026-10-22 16:33</td>
            <td style=" font-weight: bold; color: #ff0000;"></td>
        </tr>
        
        <tr>
            <td class="appcol" style=" font-style: italic; color: #5e5e5e;">4</td>
            <td style=" font-style: italic; color: #5e5e5e;">Lê Bá Hoàng</td>
            <td class="appcol" style=" font-style: italic; color: #5e5e5e;">0007</td>
            <td style=" font-style: italic; color: #5e5e5e;">Manager</td>
            <td style=" font-style: italic; color: #5e5e5e;">OR</td>
            <td style=" font-style: italic; color: #5e5e5e;">Waiting</td>
            <td style=" font-style: italic; color: #5e5e5e;"></td>
            <td style=" font-style: italic; color: #5e5e5e;"></td>
        </tr>
        
        <tr>
            <td class="appcol" style=" font-style: italic; color: #5e5e5e;">4</td>
            <td style=" font-style: italic; color: #5e5e5e;">Mr-Takada</td>
            <td class="appcol" style=" font-style: italic; color: #5e5e5e;">0001</td>
            <td style=" font-style: italic; color: #5e5e5e;">General Director</td>
            <td style=" font-style: italic; color: #5e5e5e;">OR</td>
            <td style=" font-style: italic; color: #5e5e5e;">Waiting</td>
            <td style=" font-style: italic; color: #5e5e5e;">2026-10-22 16:31</td>
            <td style=" font-style: italic; color: #5e5e5e;"></td>
        </tr>    
';// Chuỗi HTML các thẻ <tr>...</tr> của quy trình duyệt

// 2. Chuẩn bị đoạn HTML của bạn (bao gồm cả cấu trúc bảng đã làm ở trên)
    //2.1. Đọc nội dung từ file TemplateExport.html cùng cấp thư mục
    $html = file_get_contents(__DIR__ . '/TemplateExport.html');
   
    $font_path = __DIR__ . '/../assets/fonts'; // Đường dẫn tuyệt đối chuẩn trên server
    


    $logo_path = __DIR__ . '/../assets/img/ITV128x128.png';
    $logo_base64 = '';
    if (file_exists($logo_path)) {
        $logo_type = pathinfo($logo_path, PATHINFO_EXTENSION);
        $logo_data = file_get_contents($logo_path);
        $logo_base64 = 'data:image/' . $logo_type . ';base64,' . base64_encode($logo_data);
    }

    // Thay thế placeholder vào template
    $html = str_replace('{{LOGO_SRC}}', $logo_base64, $html);
    //2.2. Thực hiện str_replace để thay thế các biến động vào template

    $html = str_replace(
        [
            '{{FONT_PATH}}',
            '{{SO_PHIEU}}',
            '{{NGAY_TAO}}',
            '{{NGUOI_TAO}}',
            '{{MA_NV}}',
            '{{BO_PHAN}}',
            '{{DELIVERY}}',
            '{{TABLE_DETAIL_ROWS}}',
            '{{GHI_CHU}}',
            '{{ATT_FILE}}',
            '{{APPROVAL_ROWS}}'
        ],
        [   
            $font_path,
            $so_phieu,            
            $ngay_tao,            
            $nguoi_tao,           
            $ma_nv,                
            $bo_phan,             
            $delivery,             
            $table_detail_rows,    // Chuỗi HTML các thẻ <tr>...</tr> của sản phẩm
            $ghi_chu,              // Nội dung ghi chú
            $ATT_FILE,        // Nội dung hoặc thông báo đính kèm
            $approval_rows         // Chuỗi HTML các thẻ <tr>...</tr> của quy trình duyệt
        ],
        $html
    );

echo $html;
// 3. Nạp nội dung HTML vào Dompdf
$dompdf->loadHtml($html);

// 4. Thiết lập khổ giấy (A4) và hướng giấy (portrait: dọc, landscape: ngang)
$dompdf->setPaper("A4", "portrait");

// 5. Tiến hành render HTML thành PDF
$dompdf->render();

$smb_ip     	= '192.168.110.232';
$smb_user   	= 'itv_smb';
$smb_pass  	    = 'Ikka@2026';	
$mhhd_name      = 'PDF_TEST';

// 1. Mã hóa thông tin đăng nhập và chuẩn bị đường dẫn SMB cho thư mục YCMH
$encoded_user = urlencode($smb_user);
$encoded_pass = urlencode($smb_pass);
$clean_ip     = $smb_ip;

// Thư mục đích trên SMB cho YCMH
$pdf_upload_dir = "smb://{$encoded_user}:{$encoded_pass}@{$clean_ip}/Document/Data/YCMH/";


// 2. Lấy đối tượng Canvas và Font Metrics
    $canvas = $dompdf->getCanvas();
    $font_metrics = $dompdf->getFontMetrics();
    $font = $font_metrics->get_font("Roboto Condensed", "bold");
    $size = 7;

   // Chuỗi hiển thị thực tế trên PDF (có chứa thẻ của Dompdf)
    $header_text = "YCMH - " . $so_phieu_number . " page {PAGE_NUM} / {PAGE_COUNT}";

    // 📌 TẠO CHUỖI MẪU ĐỂ TÍNH ĐỘ RỘNG CHÍNH XÁC (Thay {PAGE_NUM} bằng số mẫu như "9 / 9")
    $sample_text = "YCMH - " . $so_phieu_number . " page 9 / 9";
    $text_width = $font_metrics->getTextWidth($sample_text, $font, $size);

    // Lấy tổng chiều rộng khổ giấy A4
    $page_width = $canvas->get_width();

    // Khoảng cách lề phải thực tế (Ví dụ: 30pt ~ 10mm, bạn có thể tăng giảm tùy ý)
    $right_margin = 10; 

    // Tính toán tọa độ X chuẩn xác để căn phải
    $x = $page_width - $text_width - $right_margin;
    $y = 10; // Khoảng cách từ mép trên xuống

    // Vẽ chữ lên mọi trang với chuỗi thật
    $canvas->page_text($x, $y, $header_text, $font, $size, array(0.1, 0.1, 0.1));


// 2. Lấy nội dung nhị phân của file PDF sau khi render
$output = $dompdf->output();

// 3. Đặt tên file PDF theo logic của bạn
$pdf_file_name = "YCMH_" . str_replace('-', '_', $mhhd_name) . "_" . time() . ".pdf";
$target_pdf_path = $pdf_upload_dir . $pdf_file_name;

// 4. Ghi file trực tiếp vào thư mục SMB thông qua hàm file_put_contents
if (file_put_contents($target_pdf_path, $output) !== false) {
    echo "Lưu file PDF thành công trên SMB tại: " . $target_pdf_path;
    
    // Đường dẫn UNC (nếu bạn cần lưu vào Database để hiển thị dạng \\ITV-SV\...)
    $pdf_link = "\\\\ITV-SV\\Document\\Data\\YCMH\\" . $pdf_file_name;
} else {
    echo "Lỗi: Không thể ghi file PDF qua giao thức SMB!";
}

// HOẶC: Nếu bạn muốn ép trình duyệt tải xuống file ngay lập tức thay vì lưu trên server:
// $dompdf->stream("danh_sach.pdf", ["Attachment" => true]);
?>