<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../../core/Authorization.php';

checkLevel(1);



// 1. Đọc dữ liệu thô từ luồng input (vì JS gửi kiểu JSON)
$json = file_get_contents('php://input');
$data = json_decode($json, true);

    if (!$data|| !is_array($data)) {
        echo json_encode([
            "oke" => false, 
            "msg" => "Không có dữ liệu"]);
        exit;
    }

// 2. OPEN CONNECT
$conn = db_connect();

try {
    // Bắt đầu Transaction để tăng tốc độ và an toàn
    if (sqlsrv_begin_transaction($conn) === false) {
        throw new Exception("Không thể bắt đầu Transaction");
    }

    $sql = "UPDATE WebUsers SET UsIsDelete = 1
            WHERE UsID = ?";

    foreach ($data as $row) {
        // Chống lỗi undefined index bằng cách dùng ?? null
        $params = [
            $row['UsID'] ?? 0
        ];

        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt === false) {
            sqlsrv_rollback($conn); // Hủy mọi thay đổi nếu 1 dòng lỗi
            throw new Exception("Lỗi SQL tại ID " . ($row['UsID'] ?? '?') . ": " . sqlsrv_errors()[0]['message']);
        }
    }

    // Nếu mọi thứ ổn, commit dữ liệu
    sqlsrv_commit($conn);
    echo json_encode(["ok" => true, "msg" => "xóa thành công " . count($data) . " dòng"]);

} catch (Exception $e) {
    echo json_encode(["ok" => false, "msg" => $e->getMessage()]);
}