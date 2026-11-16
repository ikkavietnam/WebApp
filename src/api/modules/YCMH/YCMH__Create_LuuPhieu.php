<?php

//🔴 LOAD DATA 
    //✅ ADDIN
        use PhpOffice\PhpSpreadsheet\IOFactory; //Xuất Excell
        
        use PHPMailer\PHPMailer\PHPMailer;      //Dùng PHPMailer để gửi mail bảo mật
        use PHPMailer\PHPMailer\Exception;      //Dùng PHPMailer để gửi mail bảo mật

    //✅ THAM CHIẾU CÁC MODULE CHỨC NĂNG
        require_once __DIR__ . '/../../../vendor/autoload.php';
        require_once __DIR__ . '/../../../core/Database.php';
        require_once __DIR__ . '/../../../core/Authorization.php';

    //✅ CHECK SESSION & HEADER
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        header('Content-Type: application/json; charset=utf-8'); //🔒

    //✅LẤY ID (UsID) NGƯỜI DÙNG & KIỂM TRA QUYỀN HẠN & ĐĂNG NHẬP
        if (isset($_SESSION['user']) && isset($_SESSION['user']['UsID'])) {
            $UsID = (int)$_SESSION['user']['UsID']; //🔍LẤY ID NGƯỜI DÙNG ĐANG ĐĂNG NHẬP
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
    //🌟 BIẾN PUBLIC ///////////////////////////////////////////////////////////////////////////// 
        $MsgPhp     = "";
        $MsgProcess = "";
        $WfType     = "WF_YCMH";



        /* 
==================================================================================================================================
🔴   MAIN CODE  🔴
==================================================================================================================================
*/

//[🟡XỬ lÝ INPUT DATA] Text/JSON từ mảng $_POST thay vì php://input
    //1-🔸HEADER
        // trích xuất từ INPUT & KIỂM TRA
        $headerDataRaw  = isset($_POST['Header'])  ? $_POST['Header'] : '{}';
        $header  = json_decode($headerDataRaw, true);
        if (!is_array($header))  { $header = []; }
        if (empty($header)) {
            RESPONSE_CLIENT(404,"Không tìm thấy thông tin người tạo phiếu","DATA HEADER NOT FOUND");
        }

        //1.📑 NGAYLAMPHIEU
            //ĐỔI ĐỊNH DẠNG NGÀY: "DD/MM/YYYY" -> "YYYY-MM-DD"
            $raw_date = isset($header['ngaylamphieu']) ? trim($header['ngaylamphieu']) : '';
            if (!empty($raw_date)) {
                $date_obj = DateTime::createFromFormat('d/m/Y', $raw_date);
                // Nếu chuyển đổi thành công thì định dạng lại, nếu thất bại lấy ngày hiện tại
                $NGAYLAMPHIEU = ($date_obj && $date_obj->format('d/m/Y') === $raw_date) ? $date_obj->format('Y-m-d') : date('Y-m-d');
            } else {
                $NGAYLAMPHIEU = date('Y-m-d'); 
            }

        //2.📑 NHANVIEN
            $NHANVIEN   = $header['NhanVien']  ?? '';
        //3.📑 MABOPHAN
            $MABOPHAN   = $header['MaBoPhan']   ?? '';
        //4.📑 MANHANVIEN
            $MANHANVIEN = $header['MaNhanVien'] ?? '';
        //5.📑 DIEMGIAO  
            $DIEMGIAO   = $header['diemgiao']   ?? 'ITV';
      
            


    //2-🔸DETAIL
        $detailsDataRaw = isset($_POST['Details']) ? $_POST['Details'] : '[]';
        $details = json_decode($detailsDataRaw, true);
        if (!is_array($details)) { $details = []; }
        if (empty($details)) {
            RESPONSE_CLIENT(404,"Dữ liệu bị trống","DATA DETAIL NOT FOUND");
        }

    //3-🔸COMMENT
        $GHICHU     = $header['YCMH_note']  ?? '';      
    //4-🔸AttachFile
        if (
            isset($_FILES['attached_files']) && 
            is_array($_FILES['attached_files']['name']) && 
            !empty($_FILES['attached_files']['name'][0])
            ) {
            $files = $_FILES['attached_files'];
        }


//[🟡LẤY THÔNG TIN CHUNG]
    //1-🔸 TOÀN BỘ USER & NGƯỜI DÙNG HIỆN TẠI
        //TOÀN BỘ Users
        $qr = "
            SELECT 
                u.UsID			
                ,u.UsMemberCode
                ,u.UsDisplay	
                ,u.DeptID		
                ,d.DeptName	
                ,u.UsEmail
                ,u.PosID		
                ,p.PosName		
                ,p.PosLevel		
                ,u.UsType		
            FROM SYS_USERS u
            LEFT JOIN SYS_DEPARTMENT	d On d.DeptID	= u.DeptID
            LEFT JOIN SYS_POSITION		p ON p.PosID	= u.PosID	
            WHERE u.IsDelete = 0 AND u.IsActive = 1
        ";
        $stmtUs = sqlsrv_query($conn,$qr);//⭕

        $Users = [];
        while ($row = sqlsrv_fetch_array($stmtUs,SQLSRV_FETCH_ASSOC)) {
            $val = $row['UsID'];
            $Users[$val]= $row;
        }
        //CURRENT USER
        $MaNguoiDung = $Users[$UsID]['UsMemberCode'];
        $CurrentUser = $Users[$UsID];
        //CLEAR stmt
        sqlsrv_free_stmt($stmtUs); //❌
    
    //2-🔸 TẠO SỐ PHIẾU REQUEST NO (mhhd_name)
        $qr = "
                DECLARE @Today VARCHAR(6) = FORMAT(GETDATE(), 'yyMMdd');
                --✔01--------------------------Lấy số chạy lớn nhất
                DECLARE @MaxNo INT;
                    SELECT @MaxNo = MAX(CAST(RIGHT(mhhd_name, len(mhhd_name)-11) AS INT))
                    FROM ERP_MHHD
                    -- WHERE mhhd_name LIKE 'ITV-' + @Today + '-%'; --  Chỉ tìm các số thuộc ngày hôm nay
                --✔02--------------------------Tăng số
                SET @MaxNo = ISNULL(@MaxNo, 0) + 1;
                --✔03--------------------------Build mhhd_name mới
                DECLARE @REQUEST_NO VARCHAR(20);
                    SET @REQUEST_NO = CONCAT('ITV-', @Today, '-', FORMAT(@MaxNo, '0000'));
                    SELECT @REQUEST_NO as REQUEST_NO; 
        ";
        //QUERY & GETDATA
        $stmtTENPHIEU = sqlsrv_query($conn, $qr);//⭕

        $row = sqlsrv_fetch_array($stmtTENPHIEU, SQLSRV_FETCH_ASSOC);
        $mhhd_name = $row['REQUEST_NO'];
        sqlsrv_free_stmt($stmtTENPHIEU); //❌


    //3-🔸 LẤY CẤU HÌNH CONFIG WF
        $qr = "SELECT * FROM WF_CONFIG WHERE IsDelete = 0 AND IsActive =1 AND WfType = ? AND DeptID = ?";
        $stmtWF_CONFIG = sqlsrv_query($conn,$qr,[$WfType, $MABOPHAN]);//⭕
        CHKQUERY($stmtWF_CONFIG,$conn,"ERR-QUERY CONFIG");
        
        $WfConfig = [];
        while ($row = sqlsrv_fetch_array($stmtWF_CONFIG,SQLSRV_FETCH_ASSOC)) {
            $WfConfig[] = $row;
        }
        // KIỂM TRA NULL
        if (empty($WfConfig)) {
            RESPONSE_CLIENT(404,"Không tìm thấy cấu hình cho bộ phận","NOT FOUND");
        }
        sqlsrv_free_stmt($stmtWF_CONFIG);//❌
    

//[🟡CHECKING]
    //1-🔸Người dùng có khớp với session không ? <HIẾM KHI XẢY RA>
        if ($MaNguoiDung!==$MANHANVIEN) {
            PROLOG("KHÔNG KHỚP MÃ NHÂN VIÊN");
            RESPONSE_CLIENT(505,"Mã nhân viên không khớp với hệ thống", "ERROR 505");
        }

    //2-🔸Người dùng có nằm trong danh sách WF không ?
        //Trích xuất toàn bộ giá trị cột 'UsID' thành 1 mảng 1 chiều
        //Kiểm tra $UsID có nằm trong mảng $ArrID không
        $ArrID = array_column($WfConfig, 'UsID');
        
        if (in_array($UsID, $ArrID)) {
            PROLOG("UsID : ".$UsID." Nằm trong danh sách ký duyệt");
            RESPONSE_CLIENT(505,"Người yêu cầu không được phép nằm trong danh sách ký duyệt!", "BẠN KHÔNG THỂ MỞ PHIẾU");
        }  


//[🟡INSERT DATA]
    try {
        //1-🔸[OPEN TRANSACTION]🔐
            ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            //🔹1 - Kiểm tra kết nối CSDL
            if (!isset($conn)|| $conn === false) { // Tương đương: if (!($conn ?? false))
                $msg = 'Không tìm thấy kết nối';
                $title = 'Lỗi thao tác CSDL';

                LOG_TRANSACTION_ERROR($msg);
                RESPONSE_CLIENT(505,$msg,$title);
            }
            //🔹2 - Khởi tạo TRANSACTION
            if (sqlsrv_begin_transaction($conn)===false) {
                $errDetail = GET_SQLSRV_ERROR_DETAIL();
                $msg = 'Error while open transaction database:' . ($errDetail !== '' ? (' - Error Detail : ' .$errDetail) : '');

                LOG_TRANSACTION_ERROR($msg);
                RESPONSE_CLIENT(505,$msg,'Lỗi trong quá trình thao tác với CSDL');  
            }
            ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        

        //2-🔸INSET DATA
            //📌1. INSERT TABLE TO HEADER > return ID
                $sql_insert_hd = "
                    SET NOCOUNT ON;
                    INSERT INTO [dbo].[ERP_MHHD] (
                        [CreateBy],
                        [CreateTime],
                        [ModifiedDate], 
                        [Mem_code], 
                        [mhhd_name], 
                        [mhhd_department], 
                        [mhhd_delivery_place],
                        [mhhd_note]
                    ) 
                    OUTPUT INSERTED.mhhd_id
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ";
                //Tham số
                $params_hd = [
                    $NHANVIEN,         // CreateBy
                    $NGAYLAMPHIEU,     // CreateTime (Đã format Y-m-d)
                    $CurrentTime_SQL,
                    $MANHANVIEN,       // Mem_code
                    $mhhd_name,        // mhhd_name 
                    $MABOPHAN,         // mhhd_department
                    $DIEMGIAO,         // mhhd_delivery_place
                    $GHICHU            // mhhd_note
                ];
                // QUERY & CHECk
                $stmt_hd = sqlsrv_query($conn, $sql_insert_hd, $params_hd);
                CHKQUERY($stmt_hd,$conn,"QUERY INSERT HEADER");
                // GET DATA
                $row = sqlsrv_fetch_array($stmt_hd, SQLSRV_FETCH_ASSOC);
                sqlsrv_free_stmt($stmt_hd); 

                if (!$row || empty($row['mhhd_id'])) {
                    throw new Exception("Messenger : Không thể lấy ID của Phiếu mua hàng vừa tạo.");
                }

                $mhhd_id = $row['mhhd_id'];
                $MsgPhp .= "<br>🟢 Mã phiếu - ".$mhhd_name;
                $MsgPhp .= "<br>✅ Cập nhật thành công vào danh sách";
             

            //📌2. INSERT TABLE TO DETAIL
                $sql_insert_dt = "
                    INSERT INTO [dbo].[ERP_MHDT] (
                        CreateBy,
                        CreateTime,
                        ModifiedDate,
                        mhhd_id,
                        mhdt_item,
                        mhdt_qty,
                        mhdt_unit,
                        mhdt_description,
                        mhdt_purpose,
                        mhdt_delivery_time
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ";

                //Paramas Khai báo trước các biến tham chiếu để đưa vào sqlsrv_prepare
                $mhdt_item = '';
                $mhdt_qty = 0;
                $mhdt_unit = null;
                $mhdt_description = null;
                $mhdt_purpose = null;

                //Sắp xếp lại thứ tự các biến mapping chính xác 100% với cột trong SQL câu lệnh Insert ở trên
                $stmt_dt = sqlsrv_prepare($conn, $sql_insert_dt, [
                    $NHANVIEN,          // CreateBy
                    $NGAYLAMPHIEU,      // CreateTime     
                    $CurrentTime_SQL,   // ModifiedDate
                    $mhhd_id,           // mhhd_id
                    &$mhdt_item,        // mhdt_item
                    &$mhdt_qty,         // mhdt_qty (Đúng cột vị trí 5)
                    &$mhdt_unit,        // mhdt_unit (Đúng cột vị trí 6)
                    &$mhdt_description, // mhdt_description (Đúng cột vị trí 7)
                    &$mhdt_purpose,      // mhdt_purpose (Đúng cột vị trí 8)
                    &$mhdt_delivery      // mhdt_delivery (Đúng cột vị trí 9)

                ]);
                //CHECK
                CHKQUERY($stmt_dt,$conn,"QUERY TRY INSERT");
                //INSERT ERP_MHDT //Duyệt qua mảng Details
                foreach ($details as $row) {
                    $mhdt_item          = isset($row['mhdt_item']) ? trim($row['mhdt_item']) : '';
                    $mhdt_qty           = isset($row['mhdt_qty']) ? intval($row['mhdt_qty']) : 0;
                    $mhdt_unit          = isset($row['mhdt_unit']) ? trim($row['mhdt_unit']) : null;
                    $mhdt_description   = isset($row['mhdt_description']) ? trim($row['mhdt_description']) : null;
                    $mhdt_purpose       = isset($row['mhdt_purpose']) ? trim($row['mhdt_purpose']) : null;
                    $mhdt_delivery      = isset($row['mhdt_delivery']) ? trim($row['mhdt_delivery']) : null;

                    if ($mhdt_item === "") {
                        continue;
                    }
                   
                    if (sqlsrv_execute($stmt_dt) === false) {
                        throw new Exception("Messenger : Gặp lỗi trong quá trình lưu dữ liệu mặt hàng chi tiết.");
                    }
                }
                sqlsrv_free_stmt($stmt_dt); 
                $MsgPhp .= "<br>✅ Cập nhật thành công vào chi tiết phiếu";  

            //✔️ 3. INSERT TABLE TO ATTACH FILE (Sử dụng Bind Mount /mnt/itv_share)
                if (isset($files) && !empty($files)) { // CHỈ CHẠY KHI CÓ FILE ĐÍNH KÈM
                    
                    // ⭐ Trỏ trực tiếp vào thư mục Bind Mount trên NAS host (đã thông sang container)
                    $upload_dir = '/mnt/itv_share/Data/Attach/';

                    // ⭐ Kiểm tra thư mục lưu trữ (lỗi → Exception → rollback, không in Warning làm hỏng JSON)
                    if (!is_dir($upload_dir) && !@mkdir($upload_dir, 0775, true)) {
                        throw new Exception("Không tìm thấy thư mục lưu file đính kèm ($upload_dir). Kiểm tra volume /mnt/itv_share đã được mount vào container chưa. Liên hệ IT.");
                    }
                    if (!is_writable($upload_dir)) {
                        throw new Exception("Máy chủ web không có quyền ghi vào thư mục lưu file đính kèm ($upload_dir). Liên hệ IT.");
                    }

                    // ⭐ Tạo mã security
                    $SecCode = GetSecCode(10);

                    // ⭐ Khởi tạo biến 
                    $MsgPhp_attach = "";
                    $uploadedCount = 0; 
                    $fileIndex     = 1; // Biến đếm số thứ tự file thực tế lưu thành công

                    // ⭐ COPY & LƯU
                    for ($i = 0; $i < count($files['name']); $i++) {
                        // Chỉ xử lý các file không bị lỗi upload từ máy khách
                        if ($files['error'][$i] === UPLOAD_ERR_OK) {
                            
                            $att_file_name_org = $files['name'][$i];
                            $tmp_path          = $files['tmp_name'][$i];

                            // Lấy đuôi file và dung lượng
                            $ext           = pathinfo($att_file_name_org, PATHINFO_EXTENSION);
                            $FileExtension = !empty($ext) ? strtolower($ext) : 'unknown';
                            $FileSize      = isset($files['size'][$i]) ? intval($files['size'][$i]) : 0;

                            // Tên file logic theo STT tăng dần chuẩn xác
                            $att_file_name_logic = "YCMH_" . str_replace('-', '_', $mhhd_name) . "_ATTACH" . sprintf("%02d", $fileIndex) . "." . $FileExtension;
                            $target_file         = $upload_dir . $att_file_name_logic;
                            $att_link            = "\\\\ITV-SV\\Document\\Data\\Attach\\" . $att_file_name_logic;

                            // Dùng move_uploaded_file để chuyển file tạm sang đường dẫn Bind Mount local
                            if (@move_uploaded_file($tmp_path, $target_file)) {

                                // Thêm dữ liệu vào bảng ERP_AttLink
                                $QrIns_AttLink = "
                                    INSERT INTO ERP_AttLink (
                                        isDelete, 
                                        CreateBy, 
                                        CreateTime, 
                                        AttType, 
                                        AttTypeCode, 
                                        AttFileNameOrg, 
                                        AttFileNameLogic, 
                                        FileExtension, 
                                        FileSize, 
                                        AttLink, 
                                        AttSecCode
                                    ) VALUES (0, ?, ?, 'YCMH', ?, ?, ?, ?, ?, ?, ?)
                                ";

                                $params_att = [
                                    $MANHANVIEN,
                                    $CurrentTime_SQL,
                                    $mhhd_id, 
                                    $att_file_name_org, 
                                    $att_file_name_logic,
                                    $FileExtension, 
                                    $FileSize,      
                                    $att_link,
                                    $SecCode
                                ];

                                $stmt_att = sqlsrv_query($conn, $QrIns_AttLink, $params_att);

                                if ($stmt_att === false) {
                                    $errors  = sqlsrv_errors();
                                    $err_msg = isset($errors[0]['message']) ? $errors[0]['message'] : 'Unknown SQL Error';
                                    throw new Exception("File đã upload nhưng không thể lưu vào CSDL. Chi tiết: " . $err_msg);
                                }
                                
                                sqlsrv_free_stmt($stmt_att);

                                // Ghi nhận thành công
                                $MsgPhp_attach .= "<br> - " . $att_file_name_org;
                                $uploadedCount++;
                                $fileIndex++;   // Tăng STT cho file tiếp theo

                            } else {
                                $last_error = error_get_last();
                                $err_detail = isset($last_error['message']) ? $last_error['message'] : 'Không thể ghi file vào thư mục local.';
                                throw new Exception("Lỗi: Không thể copy file đính kèm ({$att_file_name_org}) qua Bind Mount. Chi tiết: " . $err_detail);
                            }
                        }
                    }

                    // Kiểm tra sau vòng lặp xem có file nào được lưu thực sự không
                    if ($uploadedCount > 0) {
                        $MsgPhp .= "<br>✅ Lưu File đính kèm thành công: " . $MsgPhp_attach;
                    } else {
                        $MsgPhp .= "<br>❌ File đính kèm: Không có file hợp lệ nào được gửi lên!";
                    }

                } else {
                    $MsgPhp .= "<br>❌ File đính kèm: Không có !";
                }
//📌4. INSERT TABLE INSTANCES (Tạo tiến trình mới) Trả về 📑[InstanceID]
                $sql_Inser_INSTANCES = "INSERT INTO WF_INSTANCES 
                    (
                        CreateBy,
                        CreateTime,
                        DocumentId,
                        WfType,
                        CurrentStep,
                        ApprovalStatus
                    ) 
                    OUTPUT INSERTED.InstanceID  
                    VALUES ( ?, ?, ?, ?, ?, ?)
                ";
                //Tham số ✔
                $params_Inser_INSTANCES =[
                    $MANHANVIEN,
                    $CurrentTime_SQL,
                    $mhhd_name,   // Khớp với DocumentId
                    $WfType,    // Khớp với WfType
                    1,
                    'Waiting'
                ];
                //QUERY & CHECK
                $stmt_load_WFconfig = sqlsrv_query($conn, $sql_Inser_INSTANCES, $params_Inser_INSTANCES);
                CHKQUERY($stmt_load_WFconfig,"QUERY INSERT INSTANCE");
                
                if ($row = sqlsrv_fetch_array($stmt_load_WFconfig,SQLSRV_FETCH_ASSOC)) {
                    $InstanceID = $row['InstanceID'];  // ✔✔✔ InstanceID Chưa kiểm tra tồn tại
                }
                sqlsrv_free_stmt($stmt_load_WFconfig);
                $MsgPhp .= "<br>✅ Đã thêm tiến trình WorkFlow mới"; 

            //📌5. INSERT TABLE APPROVALS (Bảng quản lý tiến trình)
                // INST NGƯỜI TẠO PHIẾU VỚI STEP 0 
                    $qrCreator ="
                            INSERT INTO WF_APPROVALS (
                            CreateBy,
                            InstanceID,
                            StepOrder,
                            MemberCode,
                            ApprovalStatus,
                            ApproverName,
                            ApproverPos,
                            ApproverLevel,
                            ApprovalType,
                            CreateTime,
                            ActionTime,
                            UsEmail
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ";

                    $paramsCreator = [
                        $CurrentUser['UsID'],
                        $InstanceID,
                        0,
                        $CurrentUser['UsMemberCode'],
                        'Created',
                        $CurrentUser['UsDisplay'],
                        $CurrentUser['PosName'],
                        $CurrentUser['PosLevel'],
                        'SINGLE',
                        $CurrentTime_SQL,
                        $CurrentTime_SQL,
                        $CurrentUser['UsEmail'],
                    ];

                    $stmtCreator = sqlsrv_query($conn, $qrCreator, $paramsCreator);
                    CHKQUERY($stmtCreator,$conn,"QUERY INST APPROVAL");
                    
                    // Kiểm tra số dòng thực tế được chèn
                    $rows_affected = sqlsrv_rows_affected($stmtCreator);
                    if ($rows_affected !==1){
                        throw new Exception("Err: Creator Không thể insert vào APPROVAL");
                    }
                    sqlsrv_free_stmt($stmtCreator);

                // INSERT CẤU HÌNH
                    $sql = "SET NOCOUNT OFF;
                        INSERT INTO WF_APPROVALS 
                        (     
                            CreateBy,
                            InstanceID,
                            StepOrder,
                            MemberCode,
                            ApprovalStatus,
                            ApproverName,
                            ApproverPos,
                            ApproverLevel,
                            ApprovalType,
                            CreateTime,
                            UsEmail
                        ) 
                        SELECT 
                              ?                     -- Tương ứng với CreateBy
                            , ?                     -- Tương ứng với InstanceID
                            , c.StepOrder
                            , c.UsMemberCode
                            ,   CASE                -- Tương ứng với ApprovalStatus [Tự động đổi trạng thái nếu là bước 1] 
                                    WHEN c.StepOrder = 1 THEN 'Processing' 
                                    ELSE 'Waiting' 
                                END                   
                            , u.UsDisplay
                            , p.PosName
                            , p.PosLevel
                            , c.ApprovalType
                            , ?                     -- Thời gian hệ thống SQL Server
                            , u.UsEmail             -- Email
                        FROM WF_CONFIG c
                        INNER JOIN SYS_USERS u ON c.UsMemberCode = u.UsMemberCode
                        INNER JOIN SYS_POSITION p ON u.PosID = p.PosID
                        WHERE c.IsDelete = 0    AND c.IsActive = 1 
                                                AND c.WfType = ? 
                                                AND c.DeptID = ? -- Tương ứng với WfType và DeptID
                    "; 

                    // Tham số                               
                    $params = array(
                        $UsID,                      // UserID đang đăng nhập
                        $InstanceID,                // Mã phiên duyệt phát sinh mới
                        $CurrentTime_SQL, 
                        $WfType,                    // Loại quy trình
                        $MABOPHAN,                  // Mã phòng ban
                        
                    );

                    //QUERY & CHECK
                    $stmt = sqlsrv_query($conn, $sql, $params);
                    CHKQUERY($stmt,$conn,"QUERY INSET APPROVALS");
                    
                    // Kiểm tra số dòng thực tế được chèn
                    $rows_affected = sqlsrv_rows_affected($stmt);
                    if ($rows_affected !== false && $rows_affected < 1){
                        throw new Exception("Err: CONFIG Không thể insert vào APPROVAL");
                    }
                    sqlsrv_free_stmt($stmt);
                    $MsgPhp .= "<br>✅ Cấu hình WorkFlow thành công và đang gửi mail tới người nhận"; 



        //3-🔸COMMIT TRANSACTION🔑
            sqlsrv_commit($conn);

    } catch (Exception $e) {
            //🚩NẾU XẢY RA LỖI -> ROLLBACK
                sqlsrv_rollback($conn); // Chắc chắn transac đã mở
                
            //🚩PHÂN TÍCH LỖI
            
                $SQL_Err = sqlsrv_errors();
                $Err_detail = "";
                if ($SQL_Err !== null){
                    foreach ($SQL_Err as $err) {
                        $Err_detail .= "[SQL " . $err['code'] . "]" . "\n" . $err['message'] . "\n"; 
                    }
                    PROLOG ($Err_detail);
                }

            //🚩TRẢ LỜI CLIENT ❌ KẾT QUẢ LỖI
                if (!headers_sent()) {header('Content-Type: application/json; charset=utf-8');}
                echo json_encode([
                    "ok" => false, 
                    "title"     => "TẠO PHIẾU THẤT BẠI",
                    "msg" => $e->getMessage() ? $e->getMessage() : "Lỗi giai đoạn ghi dữ liệu vào table YCMH",  
                    "SwTitle"   => "Lỗi kết nối SQL",
                    "ErrDetail" =>$Err_detail ? $Err_detail : $e->getTraceAsString()
                ]);
            //🚩CLOSE CONNET & THOÁT
                sqlsrv_close($conn);
                exit;
    }


//[🟡RESPONE TO CLIENT [⭕KẾT QUẢ ĐÚNG]

    /////////////////////////////////////////////////////////////////////////////////////////
	if (!headers_sent()) {header('Content-Type: application/json; charset=utf-8');}
	echo json_encode([
		"ok"        => true,
		"title"     => "TẠO PHIẾU THÀNH CÔNG",
        "msg"       => $MsgPhp ? $MsgPhp :'',   
        "mhhd_name" => $mhhd_name,
        "mhhd_id"   => $mhhd_id,
        "InstanceID" => $InstanceID,

	], JSON_UNESCAPED_UNICODE);
    sqlsrv_close($conn);
    exit;
    /////////////////////////////////////////////////////////////////////////////////////////



















