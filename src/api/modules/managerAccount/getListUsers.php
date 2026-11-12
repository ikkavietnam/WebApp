<?php

require_once __DIR__ . '/../../../core/Database.php';
require_once __DIR__ . '/../../../core/Authorization.php';

if (session_status() === PHP_SESSION_NONE) {session_start();}
header('Content-Type: application/json');

checkLevel(1);

// 2. OPEN CONNECT
$conn = db_connect();
    if (!$conn) {
        echo json_encode([
			"ok" 	=> 	false, 
			"error"	=>	sqlsrv_errors(),
			"vitri" =>"getListUsers.php",
			"msg" 	=> 	"Connection failed"
			]);
        exit;
    }

// 3. TRUY VẤN DATA


$sql = "select 
		 u.UsID
		,u.UsName
		,u.UsPwd
		,u.UsMemberCode
		,u.UsDisplay
		,d.DeptName
		,p.PosName
		,u.UsType
		,u.UsEmail
		,u.UsPhone
        ,u.IsActive
		,u.IsDelete

from SYS_USERS u
left join SYS_DEPARTMENT d on d.DeptID = u.DeptID
left join SYS_POSITION p on p.PosID = u.PosID
";

// 4. THỰC THI LỆNH TRUY VẤN
$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    echo json_encode([
		"ok" 	=> false, 
		"error" => sqlsrv_errors(),
		"msg"	=>"Lỗi query hoặc không có dữ liệu thỏa mãn",
		"vitri" =>"getListUsers.php",
		]);
    exit;
}

//ĐƯA DỮ LIỆU VÀO MẢNG
$data = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {   
    $row['UsType'] = (int)$row['UsType'];// Chỉnh sửa định dạng dữ liệu
    $data[] = $row;
}


echo json_encode($data);	// 4. Trả kết quả về cho Client

sqlsrv_free_stmt($stmt);	// 5. Giải phóng bộ nhớ và đóng kết nối
sqlsrv_close($conn);
?>