<?php
//⚪ LOAD DATA 
    // REQUIRE
        require_once __DIR__ . '/../../../core/Database.php';
        require_once __DIR__ . '/../../../core/Authorization.php';
    
    header('Content-Type: application/json');
    $MemberCode = json_decode(file_get_contents("php://input"), true);
    
//⚪ MỞ KẾT NỐI
    $conn = db_connect();

    //QR
        $sqlstr = "
            SELECT UsDisplay as MemName ,DeptID as Depart
            FROM SYS_USERS
            WHERE IsDelete = 0 AND UsMemberCode = ?
        ";
    //THAM SỐ
        $params = [$MemberCode];
    //EXECUTE
        $stmt = sqlsrv_query($conn,$sqlstr,$params);
    //CHECK
     

    $data = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    sqlsrv_free_stmt($stmt);

    if ($data) {
        echo json_encode([
            "ok"        => true,
            "data"      => $data
	    ], JSON_UNESCAPED_UNICODE);
    }else {
        echo json_encode([
            "ok"        => false,
            "msg"       => "Không tìm thấy mã nhân viên : " . $MemberCode,
	    ], JSON_UNESCAPED_UNICODE);
    }
    
    sqlsrv_close($conn);
