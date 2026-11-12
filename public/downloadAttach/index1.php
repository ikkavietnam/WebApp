<?php
// 1. Tắt hiển thị lỗi HTML để tránh chèn ký tự thừa làm hỏng file binary
ini_set('display_errors', 0);
error_reporting(0);

// 2. Nhúng Database hệ thống
require_once '/var/www/App/src/core/Database.php';
$conn = db_connect();

// 3. Thông tin tài khoản SMB (trùng khớp với lúc bạn Upload)
$smb_ip   = "192.168.110.232"; 
$smb_user = "Administrator"; 
$smb_pass = "Ikka@145266219"; 

// 4. Lấy tham số từ URL (JS render thẻ <a>)
$AttID      = isset($_GET['Id'])   ? trim($_GET['Id'])   : '';
$AttType    = isset($_GET['Type']) ? trim($_GET['Type']) : '';
$AttSecCode = isset($_GET['Sec'])  ? trim($_GET['Sec'])  : '';

if (empty($AttID) || empty($AttType)) {
    http_response_code(400);
    die("Lỗi: Thiếu thông tin file cần tải!");
}

// 5. Query CSDL
if (empty($AttSecCode) || strtolower($AttSecCode) === 'null') {
    $sql = "SELECT AttLink, AttFileNameLogic, AttFileNameOrg, FileExtension 
            FROM ERP_AttLink 
            WHERE IsDelete = 0 AND AttID = ? AND AttType = ?";
    $params = [$AttID, $AttType];
} else {
    $sql = "SELECT AttLink, AttFileNameLogic, AttFileNameOrg, FileExtension 
            FROM ERP_AttLink 
            WHERE IsDelete = 0 AND AttID = ? AND AttType = ? AND AttSecCode = ?";
    $params = [$AttID, $AttType, $AttSecCode];
}

$stmt = sqlsrv_query($conn, $sql, $params);
if ($stmt === false) {
    http_response_code(500);
    die("Lỗi truy vấn CSDL.");
}

$FilePath = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
if ($FilePath === null) {
    http_response_code(403);
    die("Bạn không có quyền xem hoặc tải file này!");
}

$rawAttLink       = trim($FilePath['AttLink']); // Ví dụ: \\ITV-SV\Document\Data\Attach\YCMH_...pdf
$AttFileNameLogic = trim($FilePath['AttFileNameLogic']);
$AttFileNameOrg   = trim($FilePath['AttFileNameOrg']);
$ext              = strtolower(trim($FilePath['FileExtension']));

// =========================================================================
// 💡 CHUYỂN ĐỔI UNC PATH (\\ITV-SV\Document\...) SANG SMB STREAM PATH
// =========================================================================
$encoded_user = urlencode($smb_user);
$encoded_pass = urlencode($smb_pass);
$clean_ip     = str_replace(' ', '', $smb_ip);

// Lấy phần đường dẫn tương đối đằng sau (bỏ \\ITV-SV\)
// Chuyển: \\ITV-SV\Document\Data\Attach\xyz.pdf -> Document/Data/Attach/xyz.pdf
$relativePath = str_replace(['\\\\ITV-SV\\', '\\'], ['', '/'], $rawAttLink);

// Tạo đường dẫn SMB Stream đầy đủ credentials
$smbStreamPath = "smb://{$encoded_user}:{$encoded_pass}@{$clean_ip}/" . $relativePath;

// Kiểm tra sự tồn tại của file qua SMB Stream
if (!file_exists($smbStreamPath)) {
    http_response_code(404);
    die("Lỗi: Không tìm thấy file trên máy chủ ITV-SV!");
}

// 6. Dọn dẹp Output Buffer (Tránh đè whitespace làm hỏng file Excel/PDF)
if (ob_get_level()) {
    ob_end_clean();
}

// 7. Mapping Content-Type chuẩn
$mimeTypes = [
    'pdf'  => 'application/pdf',
    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'xls'  => 'application/vnd.ms-excel',
    'doc'  => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'png'  => 'image/png',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg'
];

$contentType = $mimeTypes[$ext] ?? 'application/octet-stream';

// 8. Thiết lập Header trả về Client
header('Content-Description: File Transfer');
header('Content-Type: ' . $contentType);

if (in_array($ext, ['pdf', 'png', 'jpg', 'jpeg'])) {
    header('Content-Disposition: inline; filename="' . rawurlencode($AttFileNameOrg) . '"');
} else {
    header('Content-Disposition: attachment; filename="' . rawurlencode($AttFileNameOrg) . '"');
}

header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . filesize($smbStreamPath));

// 9. Đọc trực tiếp từ SMB Stream trả về cho Browser
readfile($smbStreamPath);
exit;