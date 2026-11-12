<?php
//⚪ LOAD DATA 
    // REQUIRE
        require_once __DIR__ . '/../../../core/Database.php';
        require_once __DIR__ . '/../../../core/Authorization.php';
    
    header('Content-Type: application/json');

//⚪LẤY THỜI ĐIỂM CHUNG   
    $DATE = new DateTime(); 
		// 2. Định dạng đối tượng đó ra các chuỗi khác nhau theo ý muốn (Dùng dấu mũi tên -> format)
		$CurrentTime_SQL  = $DATE->format('Y-m-d H:i:s'); 			// Kết quả: 2026-07-02 14:30:00

//⚪ LẤY DỮ LIỆU GỬI TỪ CLIENT RETURN Email
    $payload = json_decode(file_get_contents("php://input"), true);
    $StrSec = $payload['mabimat'];
    if ($payload['trangthai']==="dongy") {
        $Status = "Approved";
    } else {
        $Status = "Rejected";
    }

//⚪ MỞ KẾT NỐI
    $conn = db_connect();
//⚪ MAIN
    //🍒CHECK STATUS
        $qr = "
            SELECT RraStatus FROM ERP_RequestRemoteAccess
            WHERE RraStrSec = ?
        ";

        $stmt = sqlsrv_query($conn,$qr,[$StrSec]);
        CHKQUERY($stmt,$conn,"QUERY ERR - Kiểm tra trạng thái phiếu");

        $row = sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);
        if ($row && isset($row['RraStatus'])) {
            $StatusCheck = $row['RraStatus'];
        } else {
            $StatusCheck = null; 
        }

        sqlsrv_free_stmt($stmt);

        // StatusCheck = Null
            if (!$StatusCheck) {
                echo json_encode([
                    "ok" => false,
                    "msg"=> "Mã bí mật không đúng!".$StrSec
                ]);
                exit;
            }
        // StatusCheck = wait
            if ($StatusCheck && $StatusCheck==="Approved") {
                echo json_encode([
                    "title" => "Đã xác nhận",
                    "ok" => false,
                    "msg"=> "Yêu cầu này đã được bạn XÁC NHẬN thành công trước đó!"
                ]);
                exit;
            }
            if ($StatusCheck && $StatusCheck==="Rejected") {
                echo json_encode([
                    "ok" => false,
                    "msg"=> "Yêu cầu này đã bị bạn TỪ CHỐI trước đó!"
                ]);
                exit;
            }
    //🍒UPDATE
    //QR
        $sqlstr = "
            UPDATE ERP_RequestRemoteAccess SET
                RraConfirmTime = ?,
                RraStatus = ?
            WHERE RraStrSec = ?
        ";

    //THAM SỐ
        $params = [
            $CurrentTime_SQL,
            $Status,
            $StrSec,
        ];
    //EXECUTE
        $stmt = sqlsrv_query($conn,$sqlstr,$params);
    //CHECK
       

        $rowsAffected = sqlsrv_rows_affected($stmt);
        sqlsrv_free_stmt($stmt);

        if ($rowsAffected !== false && $rowsAffected > 0) {
            echo json_encode([
                "ok"    => true,
                "msg"   => "Xác nhận thành công trạng thái : ". $Status,
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                "ok"  => false,
                "msg" => "Không thể cập nhật dữ liệu vào hệ thống!",
            ], JSON_UNESCAPED_UNICODE);
        }

        sqlsrv_close($conn);

