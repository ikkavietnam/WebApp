<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
header('Content-Type: application/json');

require_once __DIR__ . '/../../core/Database.php';      //load function db_connect

// 2. Kiểm tra đăng nhập <Lỗi này chỉ xuất hiện khi online quá lâu hoặc clear cache>

if (!isset($_SESSION['user'])) {
    echo json_encode(["ok" => false, "message" => "Hết phiên làm việc!"]);
    exit;
}

// 3. Lấy dữ liệu từ Fetch API và kết nối DB
$data = json_decode(file_get_contents("php://input"), true);

$conn = db_connect();

$userId = $_SESSION['user']['UsID']; 
//****************************************************************** */
$params = [
    $data['UsDisplay'],
    $data['UsEmail'],
    $data['UsPhone'],
    $userId
];

$sql = "UPDATE SYS_USERS
        SET UsDisplay = ?, UsEmail = ?, UsPhone = ?
        WHERE UsID = ?";

//****************************************************************** */
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt) {
    echo json_encode(["ok" => true]);
} else {
    echo json_encode(["ok" => false, "error" => sqlsrv_errors()]);
}
