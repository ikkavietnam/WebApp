<?php
//🟢 FORM CHUNG THAM CHIẾU CÁC MODULE CHỨC NĂNG
    //✅ ADDIN
        use PhpOffice\PhpSpreadsheet\IOFactory; //EXPORT EXCEL
        use PHPMailer\PHPMailer\PHPMailer;  //HỖ TRỢ SEND MAIL
        use PHPMailer\PHPMailer\Exception;  //HỖ TRỢ SEND MAIL
        
    //✅ THAM CHIẾU CÁC MODULE CHỨC NĂNG
        require_once __DIR__ . '/../../../core/Database.php';
        require_once __DIR__ . '/../../../core/Authorization.php';
        require_once __DIR__ . '/../../../../public/vendor/autoload.php';//HỖ TRỢ SEND MAIL

    //✅ CHECK SESSION & HEADER
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        header('Content-Type: application/json; charset=utf-8'); 

    //✅LẤY ID (UsID) NGƯỜI DÙNG & KIỂM TRA QUYỀN HẠN & ĐĂNG NHẬP
        if (isset($_SESSION['user']) && isset($_SESSION['user']['UsID'])) {
            $userID = (int)$_SESSION['user']['UsID'];
        }

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
    //🌟 BIẾN PUBLIC ///////////////////////////////////////////////////////////////////////////// 
        $MsgPhp         = "";
        $MsgStatus      = "";
        $DOCUMENT_TYPE  = "WF_YCMH";
/* 
==================================================================================================================================
MAIN 
==================================================================================================================================
*/

//🟡1-XỬ lÝ INPUT DATA
    $InstanceID = (int)json_decode(file_get_contents("php://input"), true) ?? 0;
    $data = []; // BIẾN TRUYỀN THAM SỐ //🚩
    //KIỂM TRA Instance
    if ($InstanceID <=0) { //❌ THAM SỐ KHÔNG ĐÚNG
        //❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯
        http_response_code(400); // Bad request. Lỗi ở Client
        if (!headers_sent()) {header('Content-type: application/json; charset=utf-8');}
        echo json_encode([
            'oke'   => false,
            'title' => "ERROR INPUT",
            'msg'   => "Dữ liệu không hợp lệ",
        ],JSON_UNESCAPED_UNICODE);
        if (isset($conn) && $conn) {sqlsrv_close($conn);}
        exit;

    }

    //❯❯❯❯❯❯❯❯❯❯ TEST
    //$InstanceID = 154;


    $data['InstanceID'] = $InstanceID; //🚩


//🟡2-TRUY VẤN LẤY TOÀN BỘ BẢNG] Input[💡InstanceID]

    //   ├──🔶2.1 LOAD TIẾN TRÌNH        ✅$dataINS  🔎Refby: InstanceID
    //   │   ├── Check Exists = false    > 💲KIỂM TRA TỒN TẠI ? 
    //   │   ├── Check Status = done     > 💲KIỂM TRA TRẠNG THÁI
    //   │   └── Check permission        > KIỂM TRA QUYỀN HẠN Người dùng có nằm trong bảng tiến trình không ? CÓ > CHẶN || KHÔNG > bypass
    //   │ 
    //   ├──🔶2.2 LOAD QUY TRÌNH DUYỆT   ✅$dataApp  🔎Refby: InstanceID
    //   ├──🔶2.3 LOAD YCMH HEADER       ✅$dataMHHD 🔎Refby: $dataINS['DocumentID'] = mhhd_name
    //   ├──🔶2.4 LOAD YCMH DETAIL       ✅$dataMHDT 🔎Refby: $dataMHHD['mhhd_id']
    //   ├──🔶2.5 LOAD MHHD_ATTACK       ✅$dataATT  🔎Refby: $dataMHHD['mhhd_id']
    //   └──🔶2.6 CLOSE CONNECTION
    try {
        //🔶2.1 LOAD TIẾN TRÌNH        ✅dataINS   🔎Refby: InstanceID
            $qr = "SELECT 
                    InstanceID,
                    DocumentID,
                    WfType,
                    CurrentStep,
                    ApprovalStatus
                    
                FROM WF_INSTANCES 
                WHERE   Isdelete    = 0 
                    and InstanceID  = ?
            ";
            
            $wfInstances = sqlsrv_query($conn,$qr,[$InstanceID]);
            CHKQUERY($wfInstances,$conn,'QUERY INSTANCES');
            
            $dataINS = sqlsrv_fetch_array($wfInstances,SQLSRV_FETCH_ASSOC);    
            //📌TRƯỜNG HỢP KHÔNG CÓ DỮ LIỆU (null)  > FALSE 
            //📌NẾU LỖI TRONG QUÁ TRÌNH ĐỌC         > FALSE
            if (!$dataINS) {
                throw new Exception("Không tồn tại tiến trình có ID : ".$InstanceID, 505);
            }

            sqlsrv_free_stmt($wfInstances);
            $data['dataINS'] = $dataINS; //🚩

                      
        //🔶2.2 LOAD QUY TRÌNH DUYỆT   ✅$dataApp  🔎Refby: InstanceID
            $qr="SELECT * 
                FROM WF_APPROVALS 
                WHERE IsDelete = 0 AND InstanceID = ? 
                ORDER BY StepOrder, ApproverLevel DESC 
            ";

            $stmtAPP = sqlsrv_query($conn,$qr,[$InstanceID]);
            CHKQUERY($stmtAPP,$conn,"QUERY APP");

            $dataAPP = [];
            while ($row = sqlsrv_fetch_array($stmtAPP,SQLSRV_FETCH_ASSOC)) {
                $dataAPP[] = $row;
            }
            sqlsrv_free_stmt($stmtAPP);
            $data['dataAPP'] = $dataAPP; //🚩


        //🔶2.3 LOAD YCMH HEADER       ✅$dataMHHD 🔎Refby: $dataINS['DocumentID'] = mhhd_name
            $qr = "SELECT * 
                FROM ERP_MHHD 
                WHERE IsDelete = 0 AND mhhd_name = ?
            ";

            $stmtMHHD = sqlsrv_query($conn, $qr, [$dataINS['DocumentID']]);
            CHKQUERY($stmtMHHD,$conn,'QUERY MH HEADER');

            $dataMHHD = sqlsrv_fetch_array($stmtMHHD,SQLSRV_FETCH_ASSOC);
            if (!$dataMHHD) {
                throw new Exception("Không tìm thấy phiếu yêu cầu : ".$dataINS['DocumentID'], 505);
            }
            sqlsrv_free_stmt($stmtMHHD);            
            $data['dataMHHD'] = $dataMHHD; //🚩


        //🔶2.4 LOAD YCMH DETAIL       ✅$dataMHDT 🔎Refby: $dataMHHD['mhhd_id']
            $qr = "SELECT  
                        ROW_NUMBER () OVER (Partition By mhhd_id order by mhhd_id) as STT,
                        mhdt_item,
                        mhdt_qty,
                        mhdt_unit,
                        mhdt_description,
                        mhdt_purpose,
                        mhdt_delivery_time

                FROM ERP_MHDT 
                WHERE IsDelete = 0 and mhhd_id = ?
            ";

            $stmtMHDT = sqlsrv_query($conn, $qr, [$dataMHHD['mhhd_id']]);
            CHKQUERY($stmtMHDT,$conn,'QUERY MH DETAIL');
            //LẤY DỮ LIỆU
            $dataMHDT = [];
            while ($row = sqlsrv_fetch_array($stmtMHDT,SQLSRV_FETCH_ASSOC)) {
                $dataMHDT[]=$row;
            }
            //CHECK
            if (!$dataMHDT) {
                throw new Exception("Không tìm thấy dữ liệu chi tiết của phiếu : ".$dataMHHD['mhhd_name'], 505); 
            }

            sqlsrv_free_stmt($stmtMHDT);
            $data['dataMHDT'] = $dataMHDT; //🚩


        //🔶2.5 LOAD MHHD_ATTACK       ✅$dataATT  🔎Refby: $dataMHHD['mhhd_id']
            $qr="SELECT AttFileNameOrg 
                 FROM ERP_AttLink 
                 WHERE Isdelete = 0 AND AttType = 'YCMH' AND AttTypeCode =?
            ";

            $stmtATT = sqlsrv_query($conn,$qr,[$dataMHHD['mhhd_id']]);
            CHKQUERY($stmtATT,$conn,"QUERY ATTACK");
            //LẤY DỮ LIỆU
            $dataATT = '';
            while ($row = sqlsrv_fetch_array($stmtATT, SQLSRV_FETCH_ASSOC)) {
                if (!empty($row['AttFileNameOrg'])) {
                    $dataATT .= '<p style="color: #2f32ff; font-style: italic" >' . htmlspecialchars($row['AttFileNameOrg']) . '</p>';
                }
            }

            if ($dataATT === '') {
                $dataATT = '<p style="color:#e92626; font-style:italic ">Không có file đính kèm / No file acttack </p>';
            }else {
                $dataATT .= '<p style="color: #59595b; font-size: 10px">Xem chi tiết trên trình duyệt </p>';
            }

            $data['dataATT'] = $dataATT; //🚩
            $data['MsgPhp'] = $MsgPhp; //🚩
            $data['MsgStatus'] = $MsgStatus; //🚩

        //🔶2.6 CLOSE CONNECTION
            sqlsrv_close($conn);
            

    } catch (Exceptione $e) {
        //🔶 SEND_LOG
            $msg =  $e->getMessage() ? $e->getMessage() : " Err : 2-TRUY VẤN LẤY TOÀN BỘ BẢNG";
            CATCHLOG($e,$msg);
        //🔶 REPSION ERROR TO CLIENT
            if (!headers_sent()) {header('Content-Type: application/json; charset=utf-8');}
            echo json_encode([
                "ok" => false, 
                "title"     => "TẠO THÔNG TIN THẤT BẠI",
                "msg" => $e->getMessage() ? $e->getMessage() : "Err : 2-TRUY VẤN LẤY TOÀN BỘ BẢNG",  
                "ErrDetail" =>$Err_detail ? $Err_detail : $e->getTraceAsString()
            ], JSON_UNESCAPED_UNICODE);
            sqlsrv_close($conn);
            exit;
    }
        
//🔴ROUNTER 🔎Refby: $dataINS['ApprovalStatus']
        //   ├──🔶TH1 Status === approved HOÀN THÀNH 
        //   │    └── ⏩SENDMAIL_HOAN_THANH           
        //   ├──🔶TH1 Status === approved HỦY
        //   │    └── ⏩SENDMAIL_HUY   
        //   └──🔶TH3 Status === approved IN PROCESS (Defaul)
    $INS_STATUS = $dataINS['ApprovalStatus'];
    if (strtolower($INS_STATUS) === 'approved') {
        SENDMAIL_HOAN_THANH($data);
    } elseif (strtolower($INS_STATUS) === 'rejected') {
        SENDMAIL_HUY($data);
    } 

//🟡3-BUILD DATA CHO FORM EMAIL

    // 🔶INS
        $INSTANCE_ID  = $dataINS['InstanceID'] ?? '';
        $DOCUMENT_ID  = $dataINS['DocumentID'] ?? '';
        $CURRENT_STEP = $dataINS['CurrentStep'] ?? 0;
        $DOCUMENT_TYPE =$dataINS['WfType'] ?? 'WF_YCMH';

    // 🔶APP
        $EmailList      = []; // Email Người duyệt bước hiện tại
        $NameList       = []; // Tên tương ứng Email
        $CURRENT_USER   = ''; // Nối tên những người nhận được Email phê duyệt
        $APPROVERS_LIST = ''; // Render body bảng quy trình
        $APPROVAL_TYPE  = ''; // Kiểu duyệt AND OR SIGNLE

        $EmailUpdateStatus  = []; // Danh sách Email những người đã phê duyệt 
        $UPDATE_LIST        = ''; // Render Body bảng quy trình
        

        $count = count($dataAPP); 
        //STYLE
            $StyleTruoc     = 'style="font-style: normal; color: #2c2fff;"';
            $StyleHientai   = 'style="font-weight: bold; color: #070707;"';
            $StyleSau       = 'style="font-style: italic; color: #5e5e5e;"';
            
            $tdStyle   = '';
            $StatusRepsion  = '';
            
        //LẶP TỪNG DÒNG TRONG MẢNG
        for ($i = 0; $i < $count; $i++) {
            $row = $dataAPP[$i];
            
            // 1.FILTER theo CURENTSTEP
            if ($row['ApprovalStatus'] === 'Created'|| $row['ApprovalStatus'] === 'Approved' || $row['ApprovalStatus'] === 'ByPass') {
                $EmailUpdateStatus[]  = $row['UsEmail'];
                $tdStyle   = $StyleTruoc;
                $StatusRepsion  = '✓ ' . $row['ApprovalStatus'];
        
            } elseif ($row['ApprovalStatus'] === 'Processing') {
                $EmailList[]    = $row['UsEmail'];
                $NameList[]     = $row['ApproverName'];
                $APPROVAL_TYPE  = $row['ApprovalType'];

                $tdStyle   = $StyleHientai;
                $StatusRepsion  = '⏳ ' . $row['ApprovalStatus'];

            } else {
                $tdStyle   = $StyleSau;
                $StatusRepsion  = $row['ApprovalStatus'];

            }

            // BODY MAIL REPSION
                $UPDATE_LIST.= "<tr>";
                    $UPDATE_LIST.= "<td {$tdStyle}>" . htmlspecialchars($row['StepOrder'] + 1   ?? '') . "</td>";
                    $UPDATE_LIST.= "<td {$tdStyle}>" . htmlspecialchars($row['ApproverName']    ?? '') . "</td>";
                    $UPDATE_LIST.= "<td {$tdStyle}>" . htmlspecialchars($row['MemberCode']      ?? '') . "</td>";
                    $UPDATE_LIST.= "<td {$tdStyle}>" . htmlspecialchars($row['ApproverPos']     ?? '') . "</td>";
                    $UPDATE_LIST.= "<td {$tdStyle}>" . htmlspecialchars($row['ApprovalType']    ?? '') . "</td>";
                    $UPDATE_LIST.= "<td {$tdStyle}>" . htmlspecialchars($StatusRepsion)                . "</td>";
                    $UPDATE_LIST.= "<td {$tdStyle}>" . htmlspecialchars(STRINGTIME($row['ActionTime'])). "</td>";
                    $UPDATE_LIST.= "<td {$tdStyle}>" . htmlspecialchars($row['ApproverComment'] ?? '') . "</td>";
                $UPDATE_LIST.= "</tr>";   

            // BODY MAIL MAIN
                $APPROVERS_LIST .= "<tr>";
                    $APPROVERS_LIST .= "<td {$tdStyle}>" . htmlspecialchars($row['StepOrder'] + 1   ?? '') . "</td>";
                    $APPROVERS_LIST .= "<td {$tdStyle}>" . htmlspecialchars($row['ApproverName']    ?? '') . "</td>";
                    $APPROVERS_LIST .= "<td {$tdStyle}>" . htmlspecialchars($row['MemberCode']      ?? '') . "</td>";
                    $APPROVERS_LIST .= "<td {$tdStyle}>" . htmlspecialchars($row['ApproverPos']     ?? '') . "</td>";
                    $APPROVERS_LIST .= "<td {$tdStyle}>" . htmlspecialchars($row['ApprovalType']    ?? '') . "</td>";
                    $APPROVERS_LIST .= "<td {$tdStyle}>" . htmlspecialchars($StatusRepsion)                . "</td>";
                    $APPROVERS_LIST .= "<td {$tdStyle}>" . htmlspecialchars(STRINGTIME($row['ActionTime'])). "</td>";
                    $APPROVERS_LIST .= "<td {$tdStyle}>" . htmlspecialchars($row['ApproverComment'] ?? '') . "</td>";
                $APPROVERS_LIST .= "</tr>";          
        }
        
        // Nối danh sách tên thành chuỗi dạng "Mr-Takada & Mr-Hoang"
        $CURRENT_USER = implode(' & ', $NameList);
        

    // 🔶MHHD
        //├── CREATE_BY       CreateBy   
        //├── MHHD_NOTE       mhhd_note
        //├── CREATE_TIME     CreateTime
        //└── CREATE_DEPT     mhhd_department
        $CREATE_BY      = $dataMHHD['CreateBy']; 
        $MHHD_NOTE      = $dataMHHD['mhhd_note']; 
        $CREATE_DEPT    = $dataMHHD['mhhd_department']; 
        $CREATE_TIME    = STRINGTIME($dataMHHD['CreateTime'],'Y-m-d');
    // 🔶MHDT
        $count = count($dataMHDT);
        $TABLE_DETAIL_ROWS ="";
        for ($i = 0; $i < $count; $i++) {
            $row = $dataMHDT[$i];
            // Ép border xám nhẹ bằng inline CSS và khống chế padding mượt mà
            $tdStyle = 'style="padding: 4px 5px; font-size: 11px; border: 1px solid #c2c0c0; font-family: \'Segoe UI\', Arial, sans-serif; line-height: 1.2;"';

            $TABLE_DETAIL_ROWS .= "<tr>";
            $TABLE_DETAIL_ROWS .= "<td " . $tdStyle . " align='center'>"    . htmlspecialchars($row['STT']                  ?? '') . "</td>";
            $TABLE_DETAIL_ROWS .= "<td " . $tdStyle . ">"                   . htmlspecialchars($row['mhdt_item']            ?? '') . "</td>";
            $TABLE_DETAIL_ROWS .= "<td " . $tdStyle . " align='center'>"    . htmlspecialchars($row['mhdt_qty']             ?? '') . "</td>";
            $TABLE_DETAIL_ROWS .= "<td " . $tdStyle . " align='center'>"    . htmlspecialchars($row['mhdt_unit']            ?? '') . "</td>";
            $TABLE_DETAIL_ROWS .= "<td " . $tdStyle . ">"                   . htmlspecialchars($row['mhdt_description']     ?? '') . "</td>";
            $TABLE_DETAIL_ROWS .= "<td " . $tdStyle . ">"                   . htmlspecialchars($row['mhdt_purpose']         ?? '') . "</td>";
            $TABLE_DETAIL_ROWS .= "<td " . $tdStyle . " align='center'>"    . htmlspecialchars($row['mhdt_delivery_time']   ?? '') . "</td>";
            $TABLE_DETAIL_ROWS .= "</tr>";
        
        }
        
    // 🔶MHHD_ATTACK
        $MHHD_ATTACK = $dataATT;
    

//🟡4-LOAD FORM EMAIL
        // Đọc file Template
        $HtmlBody_MailSenToProcess = file_get_contents(__DIR__ . '/YHMH_EmailTemplate.html'); //Cùng cấp với PHP

        // Thay thế dữ liệu động vào Khung Sườn Email
        $HtmlBody_MailSenToProcess = str_replace('{{INSTANCE_ID}}',          $INSTANCE_ID,       $HtmlBody_MailSenToProcess);//🔐
        $HtmlBody_MailSenToProcess = str_replace('{{DOCUMENT_ID}}',          $DOCUMENT_ID,       $HtmlBody_MailSenToProcess);//🔐
        $HtmlBody_MailSenToProcess = str_replace('{{CURRENT_USER}}',         $CURRENT_USER,      $HtmlBody_MailSenToProcess);//🔐
        $HtmlBody_MailSenToProcess = str_replace('{{CURRENT_STEP}}',         $CURRENT_STEP,      $HtmlBody_MailSenToProcess);//🔐
        $HtmlBody_MailSenToProcess = str_replace('{{APPROVAL_TYPE}}',        $APPROVAL_TYPE,     $HtmlBody_MailSenToProcess);//🔐
        
        $HtmlBody_MailSenToProcess = str_replace('{{CREATE_BY}}',            $CREATE_BY,         $HtmlBody_MailSenToProcess);//🔐
        $HtmlBody_MailSenToProcess = str_replace('{{CREATE_TIME}}',          $CREATE_TIME,       $HtmlBody_MailSenToProcess);//🔐
        $HtmlBody_MailSenToProcess = str_replace('{{CREATE_DEPT}}',          $CREATE_DEPT,       $HtmlBody_MailSenToProcess);//🔐
        $HtmlBody_MailSenToProcess = str_replace('{{MHHD_NOTE}}',            $MHHD_NOTE,         $HtmlBody_MailSenToProcess);//🔐
        $HtmlBody_MailSenToProcess = str_replace('{{MHHD_ATTACK}}',          $MHHD_ATTACK,       $HtmlBody_MailSenToProcess);//🔐
        $HtmlBody_MailSenToProcess = str_replace('{{APPROVERS_LIST}}',       $APPROVERS_LIST,    $HtmlBody_MailSenToProcess);//🔐
        $HtmlBody_MailSenToProcess = str_replace('{{TABLE_DETAIL_ROWS}}',    $TABLE_DETAIL_ROWS, $HtmlBody_MailSenToProcess);//🔐
        $HtmlBody_MailSenToProcess = str_replace('{{APPROVE_ACTION_URL}}', "http://192.168.110.235:8085/view/ycmh/approval?id=" . $INSTANCE_ID, $HtmlBody_MailSenToProcess);


//🟡5-SETUP EMAIL & SEND       
        // Khởi tạo tiến trình gửi mail bằng SMTP
        if (!empty($EmailList)) {
            $mail = new PHPMailer(true);
            try {
                // 2. Cấu hình kết nối SMTP Server Microsoft 365
                $mail->isSMTP();
                $mail->Host       = 'smtp.office365.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'sys@ikka-itv.com.vn'; // Tài khoản gửi
                $mail->Password   = '5RXWQjpYFh';          // Mật khẩu tài khoản
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;
                $mail->CharSet    = 'UTF-8';

                // 3. Thiết lập Người gửi
                $mail->setFrom('sys@ikka-itv.com.vn', 'Hệ thống ERP IkkaVN');
                
                // 4. Nội dung Email thông báo duyệt
                $mail->isHTML(true);

                $mail->Subject = "🔸OrderPuchase " . $DOCUMENT_ID . " Yêu cầu duyệt đơn mua hàng / Request for order approval ";
                // Nội dung
                $mail->Body    = $HtmlBody_MailSenToProcess;
                
                // Người nhận
                foreach ($EmailList as $recipientEmail) {
                    $mail->addAddress($recipientEmail);
                }

                // SEND
                $mail->send();

                // NOTIFY     
                $MsgStatus  .= 'SEND Mail to Processing THÀNH CÔNG<br>';
                $MsgPhp     .="<br>Đã gửi Email PROCESSING PROCESSING To Confirmer : " . $CURRENT_USER;
                PROLOG($CurrentTime_SQL . ' YCMH__WF_SENDMAIL : Đã gửi Email PROCESSING ✔️THÀNH CÔNG tới : '. $CURRENT_USER); 

            } catch (Exception $e) {
                $MsgStatus  .= 'SEND Mail to Processing THẤT BẠI<br>';
                $MsgPhp     .="<br>Lỗi gửi EmailProcess. Chi tiết: <br>" . $mail->ErrorInfo;
                CATCHLOG($e,$MsgPhp);
            }

        } else {
            $MsgStatus  .= 'SEND Mail to Processing THẤT BẠI<br>';
            $MsgPhp     .="<br>Lỗi gửi EmailProcess: EmailList Rỗng ";
            PROLOG($CurrentTime_SQL . ' YCMH__WF_SENDMAIL : Lỗi gửi EmailProcess ❌THẤT BẠI: EmailList Rỗng');     
        }

//🟡6-LOAD FORM EMAIL STATUS UPDATE CHO NHỮNG NGƯỜI ĐÃ TÁC ĐỘNG ĐẾN QUY TRÌNH
        // Đọc file Template
        $HtmlBody_MailStatusUpdate = file_get_contents(__DIR__ . '/YHMH_EmailStatus.html'); //Cùng cấp với PHP

        // Thay thế dữ liệu động vào Khung Sườn Email
        $HtmlBody_MailStatusUpdate = str_replace('{{INSTANCE_ID}}',          $INSTANCE_ID,       $HtmlBody_MailStatusUpdate);//🔐
        $HtmlBody_MailStatusUpdate = str_replace('{{DOCUMENT_ID}}',          $DOCUMENT_ID,       $HtmlBody_MailStatusUpdate);//🔐
        $HtmlBody_MailStatusUpdate = str_replace('{{DOCUMENT_TYPE}}',        $DOCUMENT_TYPE,     $HtmlBody_MailStatusUpdate);//🔐
        $HtmlBody_MailStatusUpdate = str_replace('{{UPDATE_LIST}}',          $UPDATE_LIST,       $HtmlBody_MailStatusUpdate);//🔐
        $HtmlBody_MailStatusUpdate = str_replace('{{CHITIET_URL}}', "http://192.168.110.235:8085/view/ycmh/approval?id=" . $INSTANCE_ID, $HtmlBody_MailStatusUpdate);

//🟡7-SETUP EMAIL & SEND EMAIL STATUS UPDATE
        // Khởi tạo tiến trình gửi MailNotify bằng SMTP
        if (!empty($EmailUpdateStatus) && count($EmailUpdateStatus) > 1) {
            $MailNotify = new PHPMailer(true);
            try {
                // 2. Cấu hình kết nối SMTP Server Microsoft 365
                $MailNotify->isSMTP();
                $MailNotify->Host       = 'smtp.office365.com';
                $MailNotify->SMTPAuth   = true;
                $MailNotify->Username   = 'sys@ikka-itv.com.vn'; // Tài khoản gửi
                $MailNotify->Password   = '5RXWQjpYFh';          // Mật khẩu tài khoản
                $MailNotify->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $MailNotify->Port       = 587;
                $MailNotify->CharSet    = 'UTF-8';

                // 3. Thiết lập Người gửi
                $MailNotify->setFrom('sys@ikka-itv.com.vn', 'Hệ thống ERP IkkaVN');
                
                // 4. Nội dung EMailNotify thông báo duyệt
                $MailNotify->isHTML(true);
                $MailNotify->Subject = "🔈 Notify Status update for " . $DOCUMENT_ID . ' Step-' . $CURRENT_STEP;
                // Nội dung
                $MailNotify->Body    = $HtmlBody_MailStatusUpdate;
                
                // Người nhận (BẮT BUỘC PHẢI CÓ 1 address)
                $MailNotify->addAddress('sys@ikka-itv.com.vn', 'Hệ thống ERP IkkaVN');
                foreach ($EmailUpdateStatus as $EmailRep) {
                    $MailNotify->addBCC($EmailRep);
                }
                // SEND   ↓↓↓↓↓↓↓↓↓↓
                $MailNotify->send();

                 // NOTIFY     
                $MsgStatus .= 'SEND Mail Notifications THÀNH CÔNG<br>';     
                $MsgPhp .="<br>Đã gửi Email thông báo tới :" . $CURRENT_USER;
                PROLOG($CurrentTime_SQL . ' YCMH__WF_SENDMAIL : Đã gửi Email thông báo ✔️THÀNH CÔNG tới : '. $CURRENT_USER);


            } catch (Exception $e) {
                $MsgStatus .= 'SEND Mail Notifications THẤT BẠI<br>'; 
                $MsgPhp .="<br>Lỗi Mail MailNotify THẤT BẠI. Chi tiết: " . $MailNotify->ErrorInfo ?? 'Không có chi tiết lỗi';

                $MsgCatch = "Lỗi Mail MailNotify THẤT BẠI. Chi tiết: " . $MailNotify->ErrorInfo ?? 'Không có chi tiết lỗi';
                CATCHLOG($e,'Lỗi Mail MailNotify ❌THẤT BẠI. Chi tiết: ' . $MailNotify->ErrorInfo ?? 'Không có chi tiết lỗi');
            }
        } elseif (empty($EmailUpdateStatus)) {
            $MsgStatus .= 'SEND Mail Notifications THẤT BẠI<br>';
            $MsgPhp .="<br>Lỗi Mail MailNotify THẤT BẠI. Chi tiết: EmailUpdateStatus Rỗng !";
            PROLOG($CurrentTime_SQL . ' YCMH__WF_SENDMAIL : Lỗi Mail MailNotify ❌THẤT BẠI. Chi tiết: EmailUpdateStatus Rỗng !');
        } elseif (count($EmailUpdateStatus)<=1) {
            $MsgStatus .= '<br><span style="color: blue; font-size: 11px;"> SEND Mail Notifications THẤT BẠI</span>';
            $MsgPhp .="<br>Lỗi Mail MailNotify THẤT BẠI. Chi tiết: EmailUpdateStatus ít hơn 2 người !";
            PROLOG($CurrentTime_SQL . ' YCMH__WF_SENDMAIL : Lỗi Mail MailNotify ❌THẤT BẠI. Chi tiết: EmailUpdateStatus ít hơn 2 người !');
        }

//🟡REPSION CLIENT
    if (!headers_sent()) {header('Content-Type: application/json; charset=utf-8');}
    echo json_encode([
        "ok"        => true,
        "msg"       => $MsgStatus,
        "MsgPhp"    => $MsgPhp,
        
    ], JSON_UNESCAPED_UNICODE);

//🟥 EXIT
    exit;



//꧁⎝ 𓆩༺✧༻𓆪 ⎠꧂==================================================================================   

function SENDMAIL_HOAN_THANH ($data)
{
    //🌟 KHAI BÁO CÁC BIẾN THỜI GIAN ĐỒNG BỘ THEO NHIỀU ĐỊNH DẠNG
            date_default_timezone_set('Asia/Ho_Chi_Minh');
            $DATE = new DateTime(); 
            $CurrentTime_SQL  = $DATE->format('Y-m-d H:i:s'); // Kết quả: 2026-07-02 14:30:00

    //🟡1-PHÂN GIẢI BIẾN & BUID DATA CHO FORM EMAIL
        // 🔶GIẢI NÉN THAM SỐ
            $InstanceID = $data['InstanceID'];
            $dataINS    = $data['dataINS'];
            $dataAPP    = $data['dataAPP'];
            $dataMHHD   = $data['dataMHHD'];
            $dataMHDT   = $data['dataMHDT'];
            $dataATT    = $data['dataATT'];
            $MsgPhp     = $data['MsgPhp'];
            $MsgStatus  = $data['MsgStatus'];
           
        // 🔶INS
            $INSTANCE_ID  = $dataINS['InstanceID'] ?? '';
            $DOCUMENT_ID  = $dataINS['DocumentID'] ?? '';
            $CURRENT_STEP = $dataINS['CurrentStep'] ?? 0;
            $DOCUMENT_TYPE ='WF_YCMH';

        // 🔶APP
            $count = count($dataAPP); 
            $APPROVERS_LIST = ''; //
            $EmailList  = [];
                       
        // 🔶APP - LẶP TỪNG DÒNG
            for ($i = 0; $i < $count; $i++) {
                $row = $dataAPP[$i];

                //1. Format Ngày ActionTime
                $actionTimeStr = STRINGTIME($row['ActionTime']);

                //2. Style cho dòng trong Table
                $tdStyle = 'style="color: #0342ff; border: 1px solid #c2c0c0;"';

                //3. Render Table Row
                $APPROVERS_LIST .= "<tr>";
                $APPROVERS_LIST .= "<td {$tdStyle}>" . htmlspecialchars($row['StepOrder'] + 1   ?? '') . "</td>";
                $APPROVERS_LIST .= "<td {$tdStyle}>" . htmlspecialchars($row['ApproverName']    ?? '') . "</td>";
                $APPROVERS_LIST .= "<td {$tdStyle}>" . htmlspecialchars($row['MemberCode']      ?? '') . "</td>";
                $APPROVERS_LIST .= "<td {$tdStyle}>" . htmlspecialchars($row['ApproverPos']     ?? '') . "</td>";
                $APPROVERS_LIST .= "<td {$tdStyle}>" . htmlspecialchars($row['ApprovalType']    ?? '') . "</td>";
                $APPROVERS_LIST .= "<td {$tdStyle}>" .'✓ ' . htmlspecialchars($row['ApprovalStatus']  ?? '') . "</td>";
                $APPROVERS_LIST .= "<td {$tdStyle}>" . htmlspecialchars($actionTimeStr)                . "</td>";
                $APPROVERS_LIST .= "<td {$tdStyle}>" . htmlspecialchars($row['ApproverComment'] ?? '') . "</td>";
                $APPROVERS_LIST .= "</tr>"; 

                //4. GET LIST Emai 
                $EmailList[] =  $row['UsEmail'];       

            }
                 
    //🟡2-LOAD FORM EMAIL
        // Đọc file Template
        $HtmlBody_EmailSuccess = file_get_contents(__DIR__ . '/YHMH_EmailStatus_Hoanthanh.html'); //Cùng cấp với PHP

        // Thay thế dữ liệu động vào Khung Sườn Email
        $HtmlBody_EmailSuccess = str_replace('{{INSTANCE_ID}}',          $INSTANCE_ID,       $HtmlBody_EmailSuccess);//🔐
        $HtmlBody_EmailSuccess = str_replace('{{DOCUMENT_ID}}',          $DOCUMENT_ID,       $HtmlBody_EmailSuccess);//🔐
        $HtmlBody_EmailSuccess = str_replace('{{DOCUMENT_TYPE}}',        $DOCUMENT_TYPE,     $HtmlBody_EmailSuccess);//🔐
        $HtmlBody_EmailSuccess = str_replace('{{APPROVERS_LIST}}',       $APPROVERS_LIST,    $HtmlBody_EmailSuccess);//🔐
        $HtmlBody_EmailSuccess = str_replace('{{CHITIET_URL}}', "http://192.168.110.235:8085/view/ycmh/approval?id=" . $INSTANCE_ID, $HtmlBody_EmailSuccess);
    //🟡3-SETUP EMAIL & SEND
        // Khởi tạo tiến trình gửi EmailSuccess bằng SMTP
        if (!empty($EmailList)) {

            $EmailSuccess = new PHPMailer(true);
            try {
                // 2. Cấu hình kết nối SMTP Server Microsoft 365
                $EmailSuccess->isSMTP();
                $EmailSuccess->Host       = 'smtp.office365.com';
                $EmailSuccess->SMTPAuth   = true;
                $EmailSuccess->Username   = 'sys@ikka-itv.com.vn'; // Tài khoản gửi
                $EmailSuccess->Password   = '5RXWQjpYFh';          // Mật khẩu tài khoản
                $EmailSuccess->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $EmailSuccess->Port       = 587;
                $EmailSuccess->CharSet    = 'UTF-8';

                // 3. Thiết lập Người gửi
                $EmailSuccess->setFrom('sys@ikka-itv.com.vn', 'Hệ thống ERP IkkaVN');
                
                // 4. Nội dung EEmailSuccess thông báo duyệt
                $EmailSuccess->isHTML(true);
                $EmailSuccess->Subject = "✓ Success! YCMH " . $DOCUMENT_ID . ' Đã thông qua xét duyệt';
                // Nội dung
                $EmailSuccess->Body    = $HtmlBody_EmailSuccess;
                
                // Người nhận (BẮT BUỘC PHẢI CÓ 1 address)
                $EmailSuccess->addAddress('sys@ikka-itv.com.vn', 'Hệ thống ERP IkkaVN');

                //BCC
                foreach ($EmailList as $EmailAdr) {
                    $EmailSuccess->addBCC($EmailAdr);
                    $MsgPhp .= $EmailAdr;
                    $ListReceive = $EmailAdr . ', ';
                }
                
                // SEND
                $EmailSuccess->send();

                $MsgStatus  .= 'SEND Mail EmailSuccess THÀNH CÔNG<br>';
                $MsgPhp .= 'Gửi EmailSuccess THÀNH CÔNG tới :' . $ListReceive;

                PROLOG($CurrentTime_SQL . ' YCMH__WF_SENDMAIL : Gửi EmailSuccess ✔️THÀNH CÔNG tới :' . $ListReceive);

            } catch (Exception $e) {
                $MsgStatus  .= 'SEND Mail EmailSuccess THẤT BẠI<br>';
                $MsgPhp .="<br>Lỗi Send EmailSuccess THẤT BẠI. Chi tiết: " . $EmailSuccess->ErrorInfo;
                CATCHLOG($e,"Lỗi Send EmailSuccess ❌THẤT BẠI. Chi tiết: " . $EmailSuccess->ErrorInfo);
            }
        } else {
            $MsgStatus  .= 'SEND Mail EmailSuccess THẤT BẠI<br>';
            $MsgPhp     .="<br>Lỗi Mail MailNotify THẤT BẠI. Chi tiết: EmailList Rỗng !";
            PROLOG($CurrentTime_SQL . ' YCMH__WF_SENDMAIL : Lỗi gửi EmailSuccess ❌THẤT BẠI. Chi tiết: EmailList rỗng !');
        }

    //🟡REPSION CLIENT & EXIT
        if (!headers_sent()) {header('Content-Type: application/json; charset=utf-8');}
        echo json_encode([
            "ok"        => true,
            "msg"       => $MsgStatus,
            "MsgPhp"    => $MsgPhp,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } 



//꧁⎝ 𓆩༺✧༻𓆪 ⎠꧂==================================================================================

function SENDMAIL_HUY ($data)
{
    //🌟 KHAI BÁO CÁC BIẾN THỜI GIAN ĐỒNG BỘ THEO NHIỀU ĐỊNH DẠNG
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $DATE = new DateTime(); 
        $CurrentTime_SQL  = $DATE->format('Y-m-d H:i:s'); // Kết quả: 2026-07-02 14:30:00
    //🟡1-PHÂN GIẢI BIẾN & BUID DATA CHO FORM EMAIL
        // 🔶INS GIẢI NÉN THAM SỐ
            $InstanceID = $data['InstanceID'];
            $dataINS    = $data['dataINS'];
            $dataAPP    = $data['dataAPP'];
            $dataMHHD   = $data['dataMHHD'];
            $dataMHDT   = $data['dataMHDT'];
            $dataATT    = $data['dataATT'];
            $MsgPhp     = $data['MsgPhp'];
            $MsgStatus  = $data['MsgStatus'];
        // 🔶INS
            $INSTANCE_ID  = $dataINS['InstanceID'] ?? '';
            $DOCUMENT_ID  = $dataINS['DocumentID'] ?? '';
            $CURRENT_STEP = $dataINS['CurrentStep'] ?? 0;
            $DOCUMENT_TYPE ='WF_YCMH';

        // 🔶APP
            $count = count($dataAPP); 
            $APPROVERS_LIST = ''; //
            $EmailList  = [];
                       
        //  🔶APP-LẶP TỪNG DÒNG
            for ($i = 0; $i < $count; $i++) {
                $row = $dataAPP[$i];

                //1. Format Ngày ActionTime
                $actionTimeStr = STRINGTIME($row['ActionTime']);

                //2. Style cho dòng trong Table
                $tdStyle1 = 'style="color: #0342ff; border: 1px solid #c2c0c0;"';
                $tdStyle2 = 'style="color: #ff2e2e; border: 1px solid #c2c0c0; font-weight: bold"';
                $tdStyle3 = 'style="color: #797979; border: 1px solid #c2c0c0;"';

                $APPstatus  = '';
                $tdStyle    = '';

                if (strtolower($row['ApprovalStatus']) === 'created'|| strtolower($row['ApprovalStatus']) === 'approved' ) {
                    $APPstatus = '✓ ' . $row['ApprovalStatus'];
                    $tdStyle = $tdStyle1;
                } elseif (strtolower($row['ApprovalStatus']) === 'rejected') {
                    $APPstatus = '✘ ' . $row['ApprovalStatus'];
                    $tdStyle = $tdStyle2;
                } else {
                    $APPstatus = $row['ApprovalStatus'];
                    $tdStyle = $tdStyle3;
                }
                //3. Render Table Row
                $APPROVERS_LIST .= "<tr>";
                $APPROVERS_LIST .= "<td {$tdStyle}>" . htmlspecialchars($row['StepOrder'] + 1   ?? '') . "</td>";
                $APPROVERS_LIST .= "<td {$tdStyle}>" . htmlspecialchars($row['ApproverName']    ?? '') . "</td>";
                $APPROVERS_LIST .= "<td {$tdStyle}>" . htmlspecialchars($row['MemberCode']      ?? '') . "</td>";
                $APPROVERS_LIST .= "<td {$tdStyle}>" . htmlspecialchars($row['ApproverPos']     ?? '') . "</td>";
                $APPROVERS_LIST .= "<td {$tdStyle}>" . htmlspecialchars($row['ApprovalType']    ?? '') . "</td>";
                $APPROVERS_LIST .= "<td {$tdStyle}>" . htmlspecialchars($APPstatus              ?? '') . "</td>";
                $APPROVERS_LIST .= "<td {$tdStyle}>" . htmlspecialchars($actionTimeStr)                . "</td>";
                $APPROVERS_LIST .= "<td {$tdStyle}>" . htmlspecialchars($row['ApproverComment'] ?? '') . "</td>";
                $APPROVERS_LIST .= "</tr>"; 

                //4. GET LIST Emai 
                $EmailList[] =  $row['UsEmail'];       

            }
                 
    //🟡2-LOAD FORM EMAIL
        // Đọc file Template
        $HtmlBody_EmailRejected = file_get_contents(__DIR__ . '/YHMH_EmailStatus_Huy.html'); //Cùng cấp với PHP

        // Thay thế dữ liệu động vào Khung Sườn Email
        $HtmlBody_EmailRejected = str_replace('{{INSTANCE_ID}}',          $INSTANCE_ID,       $HtmlBody_EmailRejected);//🔐
        $HtmlBody_EmailRejected = str_replace('{{DOCUMENT_ID}}',          $DOCUMENT_ID,       $HtmlBody_EmailRejected);//🔐
        $HtmlBody_EmailRejected = str_replace('{{DOCUMENT_TYPE}}',        $DOCUMENT_TYPE,     $HtmlBody_EmailRejected);//🔐
        $HtmlBody_EmailRejected = str_replace('{{APPROVERS_LIST}}',       $APPROVERS_LIST,    $HtmlBody_EmailRejected);//🔐
        $HtmlBody_EmailRejected = str_replace('{{CHITIET_URL}}', "http://192.168.110.235:8085/view/ycmh/approval?id=" . $INSTANCE_ID, $HtmlBody_EmailRejected);
    //🟡3-SETUP EMAIL & SEND
        // KHỞI TẠO TIẾN TRÌNH GỬI MAIL bằng SMTP
        if (!empty($EmailList)) {

            $EmailRejected = new PHPMailer(true);
            try {
                //♦️1. CẤU HÌNH KẾT NỐI SMTP Server Microsoft 365
                $EmailRejected->isSMTP();
                $EmailRejected->Host       = 'smtp.office365.com';
                $EmailRejected->SMTPAuth   = true;
                $EmailRejected->Username   = 'sys@ikka-itv.com.vn'; // Tài khoản gửi
                $EmailRejected->Password   = '5RXWQjpYFh';          // Mật khẩu tài khoản
                $EmailRejected->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $EmailRejected->Port       = 587;
                $EmailRejected->CharSet    = 'UTF-8';

                //♦️2. THIẾT LẬP NGƯỜI GỬI
                $EmailRejected->setFrom('sys@ikka-itv.com.vn', 'Hệ thống ERP IkkaVN');
                
                //♦️3. TIÊU ĐỀ
                $EmailRejected->isHTML(true);
                $EmailRejected->Subject = "✘Rejected ! YCMH " . $DOCUMENT_ID . ' YÊU CẦU NÀY ĐÃ BỊ HỦY';
                //♦️4. NỘI DUNG
                $EmailRejected->Body    = $HtmlBody_EmailRejected; //⚠️ Dạng Html
                
                //♦️5. NGƯỜI NHẬN (BẮT BUỘC PHẢI CÓ 1 ADRRESS)
                $EmailRejected->addAddress('sys@ikka-itv.com.vn', 'Hệ thống ERP IkkaVN');
                foreach ($EmailList as $EmailAdr) {
                    $EmailRejected->addBCC($EmailAdr);
                    $ListReceive .= $EmailAdr . ", ";
                }

                //♦️7.SEND✔️
                $EmailRejected->send(); 

                $MsgStatus  .= 'SEND Mail EmailRejected THÀNH CÔNG<br>';
                $MsgPhp .= 'Gửi EmailRejected THÀNH CÔNG tới :' . $ListReceive;
                PROLOG($CurrentTime_SQL . ' YCMH__WF_SENDMAIL : Gửi EmailSuccess ✔️THÀNH CÔNG tới :' . $ListReceive);
              
            } catch (Exception $e) {
                $MsgStatus  .= 'SEND Mail EmailRejected THẤT BẠI<br>';
                $MsgPhp     .="<br>Lỗi gửi EmailRejected THẤT BẠI. Chi tiết: " . $EmailRejected->ErrorInfo;
                CATCHLOG($e,$MsgPhp);
            }
        } else {
            $MsgStatus  .= 'SEND Mail EmailRejected THẤT BẠI<br>';
            $MsgPhp     .="<br>Lỗi gửi EmailRejected THẤT BẠI. Chi tiết: EmailList Rỗng !";
            PROLOG($CurrentTime_SQL . ' YCMH__WF_SENDMAIL : Gửi EmailSuccess ❌THẤT BẠI - Chi tiết : EmailList rỗng');
        }

    //🟡REPSION CLIENT & EXIT
        if (!headers_sent()) {header('Content-Type: application/json; charset=utf-8');}
        echo json_encode([
            "ok"        => true,
            "msg"       => $MsgStatus,
            "MsgPhp"    => $MsgPhp,
        ], JSON_UNESCAPED_UNICODE);
        exit; 
}
//==================================================================================




// MAP CODE
/*
    ⏩SEND_EMAIL
    │
    ├──🟡1-XỬ lÝ INPUT DATA
    │   └── 🚩InstanceID
    │   
    ├──🟡2-TRUY VẤN LẤY TOÀN BỘ BẢNG] 💭InstanceID
    │   ├──🔶2.1 LOAD TIẾN TRÌNH        ✅$dataINS  🔎Refby: InstanceID
    │   │   ├── Check Exists = false    > 💲KIỂM TRA TỒN TẠI ? 
    │   │   ├── Check Status = done     > 💲KIỂM TRA TRẠNG THÁI
    │   │   └── Check permission        > KIỂM TRA QUYỀN HẠN Người dùng có nằm trong bảng tiến trình không ? CÓ > CHẶN || KHÔNG > bypass
    │   │ 
    │   ├──🔶2.2 LOAD QUY TRÌNH DUYỆT   ✅$dataApp  🔎Refby: InstanceID
    │   ├──🔶2.3 LOAD YCMH HEADER       ✅$dataMHHD 🔎Refby: $dataINS['DocumentID'] = mhhd_name
    │   ├──🔶2.4 LOAD YCMH DETAIL       ✅$dataMHDT 🔎Refby: $dataMHHD['mhhd_id']
    │   ├──🔶2.5 LOAD MHHD_ATTACK       ✅$dataATT  🔎Refby: $dataMHHD['mhhd_id']
    │   └──🔶2.6 CLOSE CONNECTION
    │   
    ├──🔴ROUNTER 🔎Refby: $dataINS['ApprovalStatus']
    │   ├──🔶TH1 Status === approved HOÀN THÀNH 
    │   │    └── ⏩SENDMAIL_HOAN_THANH           
    │   ├──🔶TH1 Status === approved HỦY
    │   │    └── ⏩SENDMAIL_HUY   
    │   └──🔶TH3 Status === approved IN PROCESS 
    │        └── Continue
    │
    ├──🟡3-BUID DATA CHO FORM EMAIL
    │   ├── 🔶3.1 INSTANCES
    │   │    ├── INSTANCE_ID        //dataINS-InstanceID
    │   │    ├── DOCUMENT_ID        //dataINS-DocumentID
    │   │    ├── CURRENT_STEP       //dataINS-CurrentStep
    │   │    └── CURRENT_TYPE       //WF_YCMH  ✍🏼
    │   │    
    │   │
    │   ├── 🔶3.1 APP
    │   │    ├── $EmailList      = []; // Email Người duyệt bước hiện tại
    │   │    ├── $NameList       = []; // Tên tương ứng Email
    │   │    ├── $CURRENT_USER   = ''; // Nối tên những người nhận được Email phê duyệt
    │   │    ├── $APPROVERS_LIST = ''; // Render body bảng quy trình
    │   │    ├── $APPROVAL_TYPE  = ''; // Kiểu duyệt AND OR SIGNLE
    │   │    ├── $EmailUpdateStatus  = []; // Danh sách Email những người đã phê duyệt 
    │   │    └── $UPDATE_LIST        = ''; // Render Body bảng quy trình
    │   │
    │   ├── 🔶3.2 MHHD
    │   │    ├── CREATE_BY       CreateBy
    │   │    ├── MHHD_NOTE       mhhd_note
    │   │    ├── CREATE_DEPT     mhhd_department
    │   │    └── CREATE_TIME     CreateTime
    │   │
    │   ├──	🔶3.3 MHDT
    │   │    └── TABLE_DETAIL_ROWS
    │   │    
    ├──🟡4-LOAD FORM EMAIL
    ├──🟡5-SETUP EMAIL & SEND


    ⏩SENDMAIL_HOAN_THANH   🔎$data
    ├──🟡1-PHÂN GIẢI BIẾN & BUID DATA CHO FORM EMAIL
    │   ├── 🔶1.1 INSTANCES
    │   │    ├── INSTANCE_ID
    │   │    ├── DOCUMENT_ID
    │   │    ├── CURRENT_STEP
    │   │    └── DOCUMENT_TYPE - WF_YCMH
    │   │    
    │   │
    │   ├── 🔶1.2 APP
    │   │    ├── CURRENT_USER       
    │   │    ├── APPROVAL_TYPE      
    │   │    ├── EmailList          
    │   │    └── APPROVERS_LIST     
    │   │
    │   ├── 🔶1.3 MHHD
    │   │    ├── CREATE_BY       CreateBy
    │   │    ├── MHHD_NOTE       mhhd_note
    │   │    ├── CREATE_DEPT     mhhd_department
    │   │    └── CREATE_TIME     CreateTime
    │   │
    │   ├──	🔶1.4 MHDT
    │   │    └── TABLE_DETAIL_ROWS
    │   │    
    ├──🟡2-LOAD FORM EMAIL
    ├──🟡3-SETUP EMAIL & SEND


    //Tham số input
        //$mhhd_id_input
        //$mhhd_name_input
    // tìm 
        //$InstanceID
                //$dataINS
                //$dataApp
                //$QrEmail
                //$Users
                //$dataHD
                //$dataDT
    //'✓ ' . $row['ApprovalStatus'];
    //'✘ ' . $row['ApprovalStatus'];


    ⏩SENDMAIL_HUY      TƯƠNG TỰ 

    $data['InstanceID'] = $InstanceID;  //🚩
    $data['dataINS']    = $dataINS;     //🚩
    $data['dataAPP']    = $dataAPP;     //🚩
    $data['dataMHHD']   = $dataMHHD;    //🚩
    $data['dataMHDT']   = $dataMHDT;    //🚩
    $data['dataATT']    = $dataATT;     //🚩
    $data['MsgPhp']     = $MsgPhp;      //🚩

    */