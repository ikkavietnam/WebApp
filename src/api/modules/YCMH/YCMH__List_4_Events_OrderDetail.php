<?php
//🟢 FORM CHUNG THAM CHIẾU CÁC MODULE CHỨC NĂNG
    //✅ THAM CHIẾU CÁC MODULE CHỨC NĂNG
    require_once __DIR__ . '/../../../core/Database.php';
    require_once __DIR__ . '/../../../core/Authorization.php';
    require_once __DIR__ . '/YCMH__Function_LOAD_Data_PHIEU.php';
    //✅ CHECK SESSION & HEADER
    if (session_status() === PHP_SESSION_NONE) {session_start();}
    header('Content-Type: application/json');

    //✅LẤY ID (UsID) NGƯỜI DÙNG & KIỂM TRA QUYỀN HẠN & ĐĂNG NHẬP
        if (isset($_SESSION['user']) && isset($_SESSION['user']['UsID'])) {
            $UsID = (int)$_SESSION['user']['UsID'];
        }

        checkLevel(3);


    //✅ KHAI BÁO CÁC BIẾN THỜI GIAN ĐỒNG BỘ THEO NHIỀU ĐỊNH DẠNG
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $DATE = new DateTime(); 
        // Định dạng đối tượng đó ra các chuỗi khác nhau theo ý muốn (Dùng dấu mũi tên -> format)
        $CurrentTime_VN   = $DATE->format('d/m/Y H:i:s'); // Kết quả: 02/07/2026 14:30:00
        $CurrentTime_SQL  = $DATE->format('Y-m-d H:i:s'); // Kết quả: 2026-07-02 14:30:00
        $CurrentTime_Date = $DATE->format('d/m/Y');       // Kết quả: 02/07/2026

    //✅ INPUT
        $dataIP = json_decode(file_get_contents("php://input"), true) ?? []; 
        $mhhd_name  = $dataIP['mhhd_name'];

//🟡 1. OPEN CONNECT
    $conn = db_connect();
        if (!$conn) {
            if (!headers_sent()) {header('Content-Type: application/json; charset=utf-8');}
            echo json_encode([
                "ok"        => false,
                "title"     => "DISCONNECTED TO SERVER",
                "msg"       => "Lỗi mất kết nối với máy chủ. có thể đang trong thời gian bảo trì hoặc máy chủ có vấn đề ! Hãy thử lại sau 5 phút nữa hoặc liên hệ IT",
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

//⚪ LẤY DỮ LIỆU INSTANCE
    $QrINS = "
        SELECT * FROM WF_INSTANCES WHERE DocumentID = ? and IsDelete = 0
    ";

    $stmtINS = sqlsrv_query($conn,$QrINS,[$mhhd_name]);
    $dataINS = sqlsrv_fetch_array($stmtINS,SQLSRV_FETCH_ASSOC);
    CHKQUERY($dataINS,$conn,"QUERY TO INSTANCES");

    if ($dataINS === null) {
        PROLOG('dataINS : '.$mhhd_name);
    } else {
        $InstanceID = $dataINS['InstanceID'];
    }
    sqlsrv_free_stmt($stmtINS);


    //🟢 LẤY DỮ LIỆU ĐỂ TẠO UI
    $dataRep = LOAD_Data_PHIEU($UsID,$InstanceID);




//🟡 FINISH
echo json_encode([
    'ok'    => true,
    'dataRep' => $dataRep,
], JSON_UNESCAPED_UNICODE);
exit;