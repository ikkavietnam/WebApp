<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../config/configNET.php';
require_once __DIR__ . '/../../../core/Database.php';

$conn = db_connectNET();

// --- BẢNG 1: LẤY DANH SÁCH THIẾT BỊ ---
$sql = "
SELECT 
    d.MtHost,
    d.CurrentUser,
    d.IPAddress,
    d.MACAddress,
    (
        SELECT TOP 1 s.OS
        FROM PHANMEM s
        WHERE s.MtHost = d.MtHost
        ORDER BY s.ScanTime DESC
     ) AS OS,
    d.Mainboard_Model,
    d.CPU_Name,
    d.RAM_Total_GB,
    d.RAM_Details,
    d.Disk_Details,
    d.VGA_Name,
    d.UpdatedTime
FROM THIETBI d
WHERE d.IsActive = 1 
ORDER BY d.UpdatedTime DESC"; // Sắp xếp mới nhất lên đầu

$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    gwNetError(500, "Lỗi kết nối hệ thống khi lấy dữ liệu thiết bị.");
}

$table1 = [];
// Dùng vòng lặp để lấy TẤT CẢ các thiết bị, không chỉ lấy 1 dòng
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    // Định dạng lại thời gian nếu có kiểu dữ liệu DateTime để tránh lỗi JSON
    if ($row['UpdatedTime'] instanceof DateTime) {
        $row['UpdatedTime'] = $row['UpdatedTime']->format('Y-m-d H:i:s');
    }
    $table1[] = $row;
}


// --- BẢNG 2: LẤY DANH SÁCH PHẦN MỀM ---
$sql2 = "
SELECT MtHost, DisplayName, DisplayVersion, InstallDate
FROM PHANMEM 
WHERE IsActive = 1 
ORDER BY ScanTime DESC";

// SỬA LỖI: Truyền đúng $sql2 và gán vào biến $stmt2 riêng biệt
$stmt2 = sqlsrv_query($conn, $sql2);

if ($stmt2 === false) {
    gwNetError(500, "Lỗi kết nối hệ thống khi lấy dữ liệu phần mềm.");
}

$table2 = [];
while ($row2 = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC)) {
    if ($row2['InstallDate'] instanceof DateTime) {
        $row2['InstallDate'] = $row2['InstallDate']->format('Y-m-d');
    }
    $table2[] = $row2;
}

// Giải phóng tài nguyên
sqlsrv_free_stmt($stmt);
sqlsrv_free_stmt($stmt2);
sqlsrv_close($conn);


// --- TRẢ KẾT QUẢ VỀ CHO CLIENT ---
// Thay vì `return`, gom dữ liệu vào mảng JSON và xuất ra màn hình
echo json_encode([
    "ok" => true,
    "msg" => "Lấy dữ liệu thành công.",
    "data" => [
        "table1" => $table1, // Dữ liệu THIETBI
        "table2" => $table2  // Dữ liệu PHANMEM
    ]
], JSON_UNESCAPED_UNICODE);
exit;


/**
 * Hàm phụ trợ: Xuất thông báo lỗi JSON
 */
function gwNetError($code, $msg) {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        "ok" => false,
        "msg" => $msg,
        "ErrCode" => $code,
        "error" => $code,
        "vitri" => "Authorization.php",
    ], JSON_UNESCAPED_UNICODE);
    exit;
}