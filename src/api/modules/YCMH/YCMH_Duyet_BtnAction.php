<?php
//🟢 FORM CHUNG THAM CHIẾU CÁC MODULE CHỨC NĂNG
    //✅ ADDIN
        use PhpOffice\PhpSpreadsheet\IOFactory; //EXPORT EXCEL
        use PHPMailer\PHPMailer\PHPMailer;  //HỖ TRỢ SEND MAIL
        use PHPMailer\PHPMailer\Exception;  //HỖ TRỢ SEND MAIL

    //✅ THAM CHIẾU CÁC MODULE CHỨC NĂNG
        require_once __DIR__ . '/../../../core/Database.php';     //Database
        require_once __DIR__ . '/../../../core/Authorization.php';//Auth
        require_once __DIR__ . '/../../../../public/vendor/autoload.php';   //HỖ TRỢ SEND MAIL

    //✅ CHECK SESSION & HEADER
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        header('Content-Type: application/json; charset=utf-8'); //THÔNG BÁO VỚI CLIENT : Dữ liệu sắp được trả về sẽ có định dạng JSON (JavaScript Object Notation) 🔈 Bắt buocj phải có dòng này

    //✅LẤY ID (UsID) NGƯỜI DÙNG & KIỂM TRA QUYỀN HẠN & ĐĂNG NHẬP
        if (isset($_SESSION['user']) && isset($_SESSION['user']['UsID'])) {
            $userID = (int)$_SESSION['user']['UsID'];
        }

        checkLevel(3);
        


    //🌟 KHAI BÁO CÁC BIẾN THỜI GIAN ĐỒNG BỘ THEO NHIỀU ĐỊNH DẠNG
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $DATE = new DateTime(); 
        // Định dạng đối tượng đó ra các chuỗi khác nhau theo ý muốn (Dùng dấu mũi tên -> format)
        $CurrentTime_VN   = $DATE->format('d/m/Y H:i:s'); // Kết quả: 02/07/2026 14:30:00
        $CurrentTime_SQL  = $DATE->format('Y-m-d H:i:s'); // Kết quả: 2026-07-02 14:30:00
        $CurrentTime_Date = $DATE->format('d/m/Y');       // Kết quả: 02/07/2026


    //🌟 OPEN CONNECT
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
   



/* 
==================================================================================================================================
🔴   MAIN CODE  🔴
    │
    ├── [🟡XỬ lÝ INPUT DATA]
    │   ├── 1-🔸 InstanceID     
    │   └── 2-🔸 ActionStatus    
    │
    ├── [🟡PHÂN LUỒNG
    │   ├── 1-🔸 DONGY
    │   └── 2-🔸 HUY


    Function ActionDONGY
    │
    ├── [🟡 OPENCONNECT & TRANSACTION
    │   ├── 1-🔸 KIỂM TRA NGƯỜI DUYỆT UsViewer_InStatus === processing
    │   ├── 2-🔸 INSERT > INSTANCE
    │   └── 3-🔸 INSERT > APPROVALS
    │   └── 4-🔸 INSERT > MHHD
    │   └── 5-🔸 INSERT > 
    │   └── 6-🔸 INSERT >
    │
    ├── [🟡INSERT DATA]
    │   ├── 1-🔸
    │   ├── 2-🔸INSET DATA
    │   │   ├──
    │   │   ├── 1. INSERT TABLE TO HEADER > return  $mhhd_id = $row['mhhd_id'];
    │   │   ├── 2. INSERT TABLE TO DETAIL
    │   │   ├── 3. INSERT TABLE TO ATTACH FILE
    │   │   ├── 4. INSERT TABLE INSTANCES (Tạo tiến trình mới) Trả về 📑[InstanceID]
    │   │   ├── 5. INSERT TABLE APPROVALS (Bảng quản lý tiến trình) 
    │   │   └── 6. 
    │   │ 
    │   │ 
    │   └── 3-🔸COMMIT TRANSACTION🔑
    │
    └── [🟡RESPONE TO CLIENT [⭕KẾT QUẢ ĐÚNG]

================================================================================================================================== */

//🌟 BIẾN PUBLIC 
    $data = [];
    $MsgPhp ='';
    $ActionStatus ='';
    $ActionNote ='';

//🟡 XỬ lÝ INPUT DATA
    $dataIN = json_decode(file_get_contents("php://input"), true) ?? [];
    // PHÂN TÁCH
        $InstanceID     = intval(($dataIN['InstanceID'] ?? 0) ?: 0);
        $ActionStatus   = $dataIN['ActionStatus'] ?? '';
        $ActionNote     = $dataIN['ActionNote'] ?? '';
//🟡 INSERT DATA
    try {
        //🔸1.CHECK
            if ($InstanceID === 0) { throw new Exception("Lỗi không xác định được dữ liệu tiến trình", 405); }
            if ($ActionStatus !== 'DONGY' && $ActionStatus !== 'HUY') { throw new Exception("Lỗi không xác định được hành động của Client", 405); }


        //🔸2.[OPEN TRANSACTION]🔐

            ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            //🔹1 - Kiểm tra kết nối CSDL
            if (!isset($conn)|| $conn === false) { // Tương đương: if (!($conn ?? false))
                $msg = 'Không tìm thấy kết nối';
                $title = 'Lỗi thao tác CSDL';

                PROLOG($msg);
                
                RESPONSE_CLIENT(505,$msg,$title);
            }
            //🔹2 - Khởi tạo TRANSACTION
                // SP đảm nhận vai trò này
            ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            if ($ActionStatus === 'DONGY') {
                $qr = 'EXEC SP_ERP_YCMH_DUYET_ACTION_DONGY ?, ?, ?';
            } else {
                $qr = 'EXEC SP_ERP_YCMH_DUYET_ACTION_CANCEL ?, ?, ?';
            }
            $params = [
                $InstanceID,
                $userID,
                $ActionNote
            ];

            $stmt = sqlsrv_query($conn,$qr,$params);
            CHKQUERY($stmt,$conn,'INSERT_BY_SP_ACTION');
            $KQSP = sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);
            sqlsrv_free_stmt($stmt);

            if (!$KQSP || $KQSP['Status'] === 0) {
                throw new Exception($KQSP['Message']??"Lỗi trong quá trình ghi dữ liệu", 505);
            }
            $msg = $KQSP['Message']??'';
            $InsFinalStatus = $KQSP['InsFinalStatus']??'';
            sqlsrv_close($conn);

    } catch (Exception $e) {
        //🚩 Phân tích lỗi & Ghi log
            // Lấy thông tin chi tiết lỗi SQLSRV (nếu có)
            $sqlErrors = GET_SQLSRV_ERROR_DETAIL();
            $msg = $e->getMessage() ?: 'Lỗi không xác định trong quá trình xử lý';
            $msg .= $sqlErrors;

            CATCHLOG($e,$msg);

        //🚩 Trả lời Client
            RESPONSE_CLIENT(505,$msg,'Lỗi thao tác trên CSDL');
        exit;
    }




//🟡 RESPONSE TO CLIENT
    if (!headers_sent()) {header('content-Type: application/json; charset=utf-8');}
    echo json_encode([
        'ok'       => true,
        'msg'       => $msg,
        'title'     => 'Duyệt thành công <br> Success',
        'InsFinalStatus' => $InsFinalStatus,

    ], JSON_UNESCAPED_UNICODE);
    exit;

