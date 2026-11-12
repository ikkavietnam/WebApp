<?php

require_once __DIR__ . '/../../core/Database.php';      //load function db_connect
require_once __DIR__ . '/../../core/Authorization.php';

if (session_status() === PHP_SESSION_NONE) {session_start();}
header('Content-Type: application/json');

checkLevel(1);

// 1. Lấy dữ liệu từ Fetch API và kết nối DB
    $data = json_decode(file_get_contents("php://input"), true);
    $oldPwd = $data['oldPwd'] ?? '';
    $newPwd = $data['newPwd'] ?? '';
    $userId = $_SESSION['user']['UsID'];

    $conn = db_connect(); // Sử dụng hàm kết nối

// 2. Kiểm tra mật khẩu cũ có đúng không
    $sqlCheck = "SELECT UsPwdHash FROM SYS_USERS WHERE UsID = ?";
    $paramsCheck = [$userId];

    $stmtCheck = sqlsrv_query($conn, $sqlCheck, $paramsCheck);

        if ($stmtCheck === false) {
            echo json_encode([
                "ok" 	=> false, 
                "error" => sqlsrv_errors(),
                "msg"	=>"Lỗi query hoặc không có dữ liệu thỏa mãn",
                "vitri" =>"usersChangePassword.php",
            ]);
        exit;
        }


    
        // LẤY PWS CŨ
        $user = sqlsrv_fetch_array($stmtCheck, SQLSRV_FETCH_ASSOC);
            //Không khớp MK
            if (!password_verify($oldPwd, $user['UsPwdHash'])) {
                echo json_encode([
                    "ok" => false,
                    "error" => sqlsrv_errors(), 
                    "msg" => "Mật khẩu cũ không chính xác!",
                    "vitri" =>"usersChangePassword.php",
                ]);
            exit;
            }



// GET PwdHash
//1. Tạo hash mới cho mật khẩu mới
$PwdHash = password_hash($newPwd, PASSWORD_DEFAULT);
// 4. Cập nhật mật khẩu mới
$sqlUpdate = "UPDATE SYS_USERS 
              SET UsPwd = ?, UsPwdHash = ?
              WHERE UsID = ?";
$paramsUpdate = [$newPwd, $PwdHash, $userId];
$stmtUpdate = sqlsrv_query($conn, $sqlUpdate, $paramsUpdate);

if ($stmtUpdate) {
    echo json_encode([
        "ok" => true, 

        "msg" => "Đổi mật khẩu thành công!",
    ]);
} else {
    echo json_encode([
        "ok" => false, 
        "error" => sqlsrv_errors(),
        "msg" => "Không thể cập nhật mật khẩu!", 
        "vitri" => "usersChangePassword.php Lưu dữ liệu mới"
    ]);
}
// Giải phóng tài nguyên
if(isset($stmtCheck))sqlsrv_free_stmt($stmtCheck);
if(isset($stmtUpdate)) sqlsrv_free_stmt($stmtUpdate);