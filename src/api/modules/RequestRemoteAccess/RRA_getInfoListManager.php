<?php
//⚪ LOAD DATA 
    // REQUIRE
        require_once __DIR__ . '/../../../core/Database.php';
        require_once __DIR__ . '/../../../core/Authorization.php';
    
    header('Content-Type: application/json');
    
    
//⚪ MỞ KẾT NỐI
    $conn = db_connect();

    //QR
        $sqlstr = "
            SELECT UsDisplay,
                    DeptName,
                    CreateTime,
                    Hostname,
                    TimeStart,
                    TimeEnd,
                    RraTimeTotal,
                    RraDestion,
                    RraPurpose,
                    RraConfirmBy,
                    RraConfirmTime,
                    RraStatus,
                    ROW_NUMBER() OVER (ORDER BY CreateTime DESC) AS UsID
            FROM ERP_RequestRemoteAccess
            WHERE IsDelete = 0
        ";
    //THAM SỐ
        
    //EXECUTE
        $stmt = sqlsrv_query($conn,$sqlstr);
    //CHECK
        
    $data = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $row['CreateTime']     = ($row['CreateTime'] instanceof DateTime)     ? $row['CreateTime']->format('Y-m-d H:i:s')     : $row['CreateTime'];
        $row['TimeStart']      = ($row['TimeStart'] instanceof DateTime)      ? $row['TimeStart']->format('Y-m-d H:i:s')      : $row['TimeStart'];
        $row['TimeEnd']        = ($row['TimeEnd'] instanceof DateTime)        ? $row['TimeEnd']->format('Y-m-d H:i:s')        : $row['TimeEnd'];
        $row['RraConfirmTime'] = ($row['RraConfirmTime'] instanceof DateTime) ? $row['RraConfirmTime']->format('Y-m-d H:i:s') : $row['RraConfirmTime'];
        if ($row) {
           $data[]  = $row;
        }
        
    }    
    sqlsrv_free_stmt($stmt);

    if ($data) {
        echo json_encode([
            "ok"        => true,
            "data"      => $data
	    ], JSON_UNESCAPED_UNICODE);
    }else {
        echo json_encode([
            "ok"        => false,
            "msg"       => "Không tìm thấy dữ liệu",
	    ], JSON_UNESCAPED_UNICODE);
    }
    
    sqlsrv_close($conn);
