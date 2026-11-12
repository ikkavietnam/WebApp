<?php
/* 
🟢🟢🟢🟢 */
header("Content-Type: application/json");

require_once __DIR__ . '/../../core/Database.php';

$data = json_decode(file_get_contents("php://input"), true);
$userData = $data['newProfile'] ?? null;

if (!$userData) {
    echo json_encode(["ok" => false, "message" => "Dữ liệu không hợp lệ"]);
    exit;
}

$conn = db_connect();
if (!$conn) {
    echo json_encode(["ok" => false, "message" => "Kết nối CSDL thất bại"]);
    exit;
}

// 🛡️ Mã hóa mật khẩu trước khi lưu (Nên làm)
if (isset($userData['UsPwd'])) {
    // Nếu bạn muốn bảo mật, hãy dùng password_hash. 
    // Tuy nhiên nếu hệ thống cũ dùng MD5 hoặc Plaintext thì giữ nguyên tùy mục đích.
    // $userData['UsPwd'] = password_hash($userData['UsPwd'], PASSWORD_DEFAULT);
}

$fields = implode(", ", array_keys($userData));
$params = array_values($userData); 
$placeholders = implode(", ", array_fill(0, count($userData), "?"));

$sql = "INSERT INTO dbo.WebUsers ($fields) VALUES ($placeholders)";

$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    // Trả về lỗi chi tiết từ SQL Server để debug
    $errors = sqlsrv_errors();
    echo json_encode([
        "ok" => false, 
        "message" => "Lỗi SQL: " . $errors[0]['message']
    ]);
} else {
    echo json_encode([
        "ok" => true, 
        "message" => "Tạo người dùng thành công!"
    ]);
}

sqlsrv_close($conn);
?>