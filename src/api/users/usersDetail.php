<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../../core/Authorization.php';
require_once __DIR__ . '/../../core/mapping/UsersMapping.php';

//1. Kiểm tra đăng nhập
checkUser();
//
//2. Khởi tạo SESSION
if (session_status() === PHP_SESSION_NONE) {session_start();}
$conn = db_connect();

$userId = $_SESSION['user']['UsID']; 

$sql = "select 
         UsID
		,UsName
		,UsDisplay
		,UsMemberCode
		,UsEmail
		,UsPhone
		,d.DeptName
		,p.PosName

from SYS_USERS u
left join SYS_DEPARTMENT d on d.DeptID = u.DeptID
left join SYS_POSITION p on p.PosID = u.PosID
where 
    u.IsDelete = 0 
    AND u.IsActive =1
    AND u.UsID = ?";

$stmt = sqlsrv_query($conn, $sql, [$userId]);
$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

// Code New
if ($row) {
    // 1. Chuyển đổi dữ liệu user sang dạng Key Tiếng Việt
    $TransToClient = UserMapping::MappingToClient($row);

    // 2. Định nghĩa danh sách các nhãn hiển thị KHÔNG cho sửa (Bằng tiếng Việt)
    // Rất trực quan, nhìn vào là biết ô nào bị khóa

    echo json_encode([
        "ok" => true,
        "user" => $TransToClient,
    ]);

} else {
    echo json_encode([
        "ok" => false,
        "msg" => "Không tìm thấy thông tin người dùng!"
    ]);
};




