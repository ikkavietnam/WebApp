<?php

require_once __DIR__ . '/../../../core/Database.php';
require_once __DIR__ . '/../../../core/Authorization.php';


if (session_status() === PHP_SESSION_NONE) {session_start();}
header('Content-Type: application/json');
//CHECK
checkLevel(5);






// 2. OPEN CONNECT
$conn = db_connect();
    if (!$conn) {
        echo json_encode([
			"ok" 	=> 	false, 
			"error"	=>	sqlsrv_errors(),
			"vitri" =>  "getListUsers.php",
			"msg" 	=> 	"Connection failed"
			]);
        exit;
    }

// 3. TRUY VẤN DATA


$sql = "
    SELECT
        hd.mhhd_id as YCMH_id
        ,hd.mhhd_name as YCMH_name
        , mhhd_status as YCMH_status
        , mhhd_department as YCMH_bophan
        , CreateBy as YCMH_nhanvien
        , CONVERT(VARCHAR, CreateTime, 103) AS YCMH_date
        ,(select top 1 dt.mhdt_item from ERP_MHDT dt where dt.mhhd_id = hd.mhhd_id) as YCMH_ITEM
    FROM ERP_MHHD hd
    Order By hd.CreateTime DESC, hd.mhhd_name DESC
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
    $data[] = $row;
}


echo json_encode([
    "ok"    => true,
    "data"  => $data,
    
]);	// 4. Trả kết quả về cho Client

sqlsrv_free_stmt($stmt);	// 5. Giải phóng bộ nhớ và đóng kết nối
sqlsrv_close($conn);
?>