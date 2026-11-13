<?php     
//🟩 FORM CHUNG THAM CHIẾU CÁC MODULE CHỨC NĂNG
    //✅ ADDIN

    //✅ THAM CHIẾU CÁC MODULE CHỨC NĂNG, THƯ VIỆN & FILE HỆ THỐNG
        //LOAD SETTING & CONFIG ...
            require_once __DIR__ . '/../../core/Database.php';

    //✅ CHECK SESSION & HEADER
        if (session_status() === PHP_SESSION_NONE) { session_start();}
        header('Content-Type: application/json; charset=utf-8');
    // ﮩ٨ـﮩﮩ٨ـ♡ﮩ٨ـﮩﮩ٨ـ  ﮩ٨ـﮩﮩ٨ـ♡ﮩ٨ـﮩﮩ٨ـ  ﮩ٨ـﮩﮩ٨ـ♡ﮩ٨ـﮩﮩ٨ـ  ﮩ٨ـﮩﮩ٨ـ♡ﮩ٨ـﮩﮩ٨ـ  ﮩ٨ـﮩﮩ٨ـ♡ﮩ٨ـﮩﮩ٨ـ 

    //🟩 KHAI BÁO CÁC BIẾN THỜI GIAN ĐỒNG BỘ THEO NHIỀU ĐỊNH DẠNG
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $DATE = new DateTime(); 
        
        // Định dạng đối tượng đó ra các chuỗi khác nhau theo ý muốn (Dùng dấu mũi tên -> format)
        $CurrentTime_SQL  = $DATE->format('Y-m-d H:i:s'); 

    //🟩 OPEN CONNECT
        $conn = db_connect();

        if (!$conn) {
            if (!headers_sent()) {header('Content-Type: application/json; charset=utf-8');}
            echo json_encode([
                "ok"        => false,
                "title"     => "DISCONNECTED TO SERVER",
                "msg"       => "Lỗi mất kết nối với máy chủ. có thể đang trong thời gian bảo trì hoặc máy chủ có vấn đề ! Hãy thử lại sau 5 phút nữa hoặc liên hệ IT",
                "ErrCode"   => 500,
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

    //🟩 BIẾN PUBLIC
        $MsgPhp     = "";
      
    //🟩INPUT***  ĐỌC DỮ LIỆU INPUT GỬI LÊN (JSON Body)   
        $payload = json_decode(file_get_contents("php://input"), true);




//  ﮩ٨ـﮩﮩ٨ـ♡ﮩ٨ـﮩﮩ٨ـ  𓇼☆ ⋆ ⚡︎ ꧁⎝ 𓆩༺✧MAIN✧༻𓆪 ⎠꧂ ⚡︎ ⋆ ☆𓇼 ⋆   ﮩ٨ـﮩﮩ٨ـ♡ﮩ٨ـﮩﮩ٨ـ 

//🔴1 THAM SỐ 
    // Input
        $username = $payload['_username'] ?? '';
        $password = $payload['_password'] ?? '';

        if ($username==='' || $username==='' ) {
            echo json_encode([
                "ok"        => false,
                "title"     => '-----',
                "msg"       => "*****",
                "ErrCode"   => 401,
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

    // Open conn
        $conn = db_connect();

//🔴2 CONNECT DATABASE SYS_USERS
    //query
        $sql = "
            SELECT 

                UsID1
                ,UsPwdHash
                ,UsName
                ,UsDisplay
                ,UsMemberCode
                ,UsType
                ,UsEmail
                ,d.DeptID
                ,d.DeptName
                ,p.PosID
                ,p.PosName
                ,p.PosLevel

            FROM SYS_USERS u
                LEFT JOIN SYS_DEPARTMENT    d on d.DeptID   = u.DeptID
                LEFT JOIN SYS_POSITION      p on p.PosID    = u.PosID

            WHERE 
                    u.IsDelete  = 0 
                AND u.IsActive  = 1 
                AND u.UsName    = ?
        ";

    //Tham số
        $params = [$username];
    //EXECUTE 
        $stmt = sqlsrv_query($conn, $sql, $params);
        CHKQUERY($stmt,$conn,"CHKQUERY : Lỗi truy vấn Login");
    
    //
        $user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);

//🔴3 KIỂM TRA LÀM SẠCH DỮ LIỆU
    //Check data
        if (!$user) {
            echo json_encode([
                "ok"        => false,
                "title"     => 'ĐĂNG NHẬP THẤT BẠI',
                "msg"       => "Sai tài khoản hoặc mật khẩu",
                "MsgPhp"     => $MsgPhp,
                "ErrCode"   => 401,
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

    //Thu thập passHash
        $UsPwdHash = $user['UsPwdHash'];
    //CONFIRM 
        if (password_verify($password, $UsPwdHash)) {
            //🔐 XÓA FIELD KHÔNG CẦN THIẾT
            unset($user['UsPwdHash']);
            //🔐Lưu SESSION & THÊM FIELD
            $_SESSION['user'] = $user; //🟢Lưu SESSION
            $_SESSION['LastActivity'] = time();
            $_SESSION['LifeActivity'] = 1800;  //🟢 🔐🔐🔐 SET TIME LIFE
            
        } else {
            echo json_encode([
                "ok"        => false,
                "title"     => 'ĐĂNG NHẬP THẤT BẠI ❌',
                "msg"       => "Sai tài khoản hoặc mật khẩu",
                "ErrCode"   => 401,
       
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
//🔴4 TRẢ LỜI CLIENT
    echo json_encode([
        "ok"    => true, 
        "msg"   => "KẾT NỐI THÀNH CÔNG ",
        "vitri" => $MsgPhp,
    ], JSON_UNESCAPED_UNICODE);

//END ////////////////////////////////////////////////////////////////////////


    