<?php
//=========================================================================
//✔️✔️✔️ SET DEFAULT TIME 
    date_default_timezone_set('Asia/Ho_Chi_Minh');


//✔️✔️✔️ HÀM PHẢN HỒI KẾT QUẢ KHI GẶP LỖI CHỦ ĐỘNG
// =========================================================================
function RESPONSE_CLIENT($code, $msg ,$title = 'RESPONSE FROM SERVER') 
{
    http_response_code($code);
    if (!headers_sent()) {header('Content-Type: application/json; charset=utf-8');}
    echo json_encode([
        "ok"        => false,
        "title"     => $title,
        "msg"       => $msg,
        "ErrCode"   => $code,
    ], JSON_UNESCAPED_UNICODE);
    // CLOSE CONNECT nếu có
    if (isset($conn) && is_resource($conn)) {sqlsrv_close($conn);}
    exit;
}



//=========================================================================
//🟡🟡🟡 HÀM CONNECT SV-ITV
function db_connect() 
{
    //🍒Load file cấu hình
        $config = require __DIR__ . '/../config/config.php';
    //🍒Mỏ connect
        $conn = sqlsrv_connect(
            $config['db']['server'],
                [   "Database"                  => $config['db']['database'],
                    "UID"                       => $config['db']['uid'],
                    "PWD"                       => $config['db']['pwd'],
                    "CharacterSet"              => $config['db']['charset'],
                    "TrustServerCertificate"    => true
                ]
        );
    //🍒Kiểm tra conn có tồng tại không
        if (!$conn) {
            $errors = sqlsrv_errors();          //Gán biến errors nếu xảy ra lỗi
            $errorMsg = $errors[0]['message'];  //lấy thông tin lỗi cuối cùng
            
            // Kiểm tra xem lỗi là do sai mật khẩu SQL hay do Server Offline
            
            if (strpos($errorMsg, 'Login failed') !== false) {
                $msg = "Sai tài khoản/mật khẩu kết nối SQL";
            } else {
                $msg = "Server Database đang Offline";
            }
            
            // Trả về JSON và dừng chương trình
            header('Content-Type: application/json');
            echo json_encode([
                "ok"        => false,
                "title"     => "CANNOT CONNECT TO SERVER",
                "msg"       => $msg,
                "ErrDetail" => $errorMsg
            ], JSON_UNESCAPED_UNICODE);
            exit; 
        }

        return $conn;
}


//🟡🟡🟡 HÀM CHECK KẾT NỐI MỖI KHI THỰC HIỆN TRUY VẤN 
// Cách dùng:
// CHKQUERY($stmt, $conn, "Đang kiểm tra cái gì?")
// =========================================================================
function CHKQUERY($stmt, $conn, $msg = "Lỗi trong quá trình thực thi SQL") 
{
    if ($stmt !== false) {
        return true;
    }

    try {    // ============================================================
            //🛢️ 1. Kiểm tra kết quả query
        if ($stmt === false) {

            // ========================================================
            //🛢️ 2. Thông tin lỗi SQL
            $sql_Err  = sqlsrv_errors(SQLSRV_ERR_ERRORS) ?? [];
            $err_detail = '';

            if ($sql_Err !== null) {
                foreach ($sql_Err as $err) {
                    $err_detail .= sprintf(
                        "[SQLSTATE: %s] [SQL CODE: %s] %s\n",
                        $err['SQLSTATE'] ?? '',
                        $err['code'] ?? '',
                        $err['message'] ?? ''
                    );
                    PROLOG('CHKQUERY : ' . $err_detail);
                }
            }

            // ========================================================
            //🛢️ 3. Thông báo chính
            $MsgDB = !empty(trim($msg))
                ? trim($msg)
                : "Lỗi trong quá trình thực thi SQL";

            // ========================================================
            //🛢️ 4. GHI LOG QUERY ERROR
                try {
                    // ✅ [TỐI ƯU #5] Bỏ dấu '/' bị lặp
                    $logDir = __DIR__ . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . 'SQLSRV';

                    if (!is_dir($logDir)) {
                        @mkdir($logDir, 0775, true);
                    }

                    // ✅ [TỐI ƯU #10] Log theo ngày để dễ quản lý / xóa định kỳ
                    $logFile = $logDir . DIRECTORY_SEPARATOR . 'SQL-Err-' . date('Y-m-d') . '.log';

                    // ✅ [TỐI ƯU #2] Dùng frame [0] = nơi gọi CHKQUERY()
                    // (nếu bạn luôn gọi qua 1 hàm wrapper thì mới đổi lại [1])
                    $trace      = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
                    $callerFile = $trace[0]['file'] ?? 'Unknown';
                    $callerLine = $trace[0]['line'] ?? 'Unknown';

                    // ✅ [TỐI ƯU #4] Truy cập an toàn bằng ??, không cảnh báo
                    $UsDisplay = $_SESSION['user']['UsDisplay'] ?? 'Unknown';

                    // ✅ [TỐI ƯU #3] Ghi $MsgDB thay cho $message (biến có thể chưa tồn tại)
                    $logContent  = "\n============================================================\n";
                    $logContent .= "USER       : {$UsDisplay}\n";
                    $logContent .= "TIME       : " . date('Y-m-d H:i:s') . "\n";
                    $logContent .= "IP         : " . ($_SERVER['REMOTE_ADDR'] ?? 'CLI') . "\n";
                    $logContent .= "REQUEST    : " . ($_SERVER['REQUEST_URI'] ?? 'CLI') . "\n";
                    $logContent .= "FILE       : {$callerFile}\n";
                    $logContent .= "LINE       : {$callerLine}\n";
                    $logContent .= "MESSAGE    : {$MsgDB}\n";
                    $logContent .= "SQL ERROR  :\n" . trim($err_detail) . "\n";

                    @file_put_contents($logFile, $logContent, FILE_APPEND | LOCK_EX);

                } catch (Throwable $logError) {
                    // Bỏ qua: ghi log thất bại không được làm hỏng việc xử lý lỗi SQL
                    PROLOG('LOG CATCH');
                }



            // ========================================================
            //🛢️ 5. Rollback transaction
            // ========================================================

            if ($conn) {
                @sqlsrv_rollback($conn);
            }


            // ========================================================
            //🛢️ 6. Đóng connection
            // ========================================================

            if ($conn) {
                @sqlsrv_close($conn);
            }


            // ========================================================
            //🛢️ 7. Header JSON
            // ========================================================

            if (!headers_sent()) {
                http_response_code(500);
                header('Content-Type: application/json; charset=utf-8');
            }


            // ========================================================
            //🛢️ 8. Trả JSON về Frontend
            // ========================================================

            echo json_encode([
                "ok"        => false,
                "title"     => "LỖI KHI KIỂM TRA TRUY VẤN",
                "msg"       => $MsgDB, 
                "ErrDetail" => $err_detail
            ], JSON_UNESCAPED_UNICODE);


            // ========================================================
            //🛢️ 9. Dừng toàn bộ request
            // ========================================================

            exit;
        }
        // ============================================================
        // Query thành công
        // ============================================================

        return true;


    } catch (Throwable $e) {

        // ============================================================
        // Trường hợp chính hàm KIEMTRATRUYVAN() tự phát sinh lỗi
        // ============================================================

        if ($conn) {
            @sqlsrv_rollback($conn);
            @sqlsrv_close($conn);
        }

        //
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
        }
        // TRẢ RESPONSE CLIENT
        echo json_encode([
            "ok"        => false,
            "title"     => "LỖI HỆ THỐNG",
            "msg"       => "Lỗi xử lý ngoài",
            "ErrCode"   => $e->getCode()?? '505',
            "ErrDetail" => $e->getMessage() ?? '',

        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}


//🟡🟡🟡 HÀM LẤY LỖI CHI TIẾT (CHỈ DÀNH CHO LỖI SQLSRV)
//=========================================================================
function GET_SQLSRV_ERROR_DETAIL(): string 
{
    $errors = sqlsrv_errors();
    $errDetail = '';
    if (!empty($errors)) {
        foreach ($errors as $e) {
            $errDetail .= 'SQL:' . $e['SQLSTATE'] . ' - Code: ' . $e['code'] . ' - Msg: ' . $e['message'] . ' ! ';
        }
    }
    return $errDetail;
}


//🟣Utils - HÀM TẠO KÝ TỰ NGẪU NHIÊN
//=========================================================================
function GetSecCode(int $length): string 
{
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charsLength = strlen($chars);
    $result = '';
    
    for ($i = 0; $i < $length; $i++) {
        $result .= $chars[random_int(0, $charsLength - 1)];
    }
    
    return $result;
}


//🟣Utils - HÀM ÉP DỮ LIỆU TIME DẠNG OBJECT > STRING
function STRINGTIME($TIME,$TIME_FORMT = 'Y-m-d H:i')
{   // Trả về chuỗi rỗng ngay nếu dữ liệu trống
    if (empty($TIME)) {
        return ''; 
    }

    // Trường hợp driver trả về dạng mảng 
    if (is_array($TIME) && isset($TIME['date'])) {
        return date($TIME_FORMT, strtotime($TIME['date']));
    } 

    // Trường hợp driver tự map sang object DateTime của PHP
    if ($TIME instanceof DateTime) {
        return $TIME->format($TIME_FORMT);
    } 
    // Trường hợp chuỗi datetime2(0) từ SQL
    return date($TIME_FORMT, strtotime((string)$TIME));
}

//🟣Utils - HÀM GIÁN TIẾP LẤY CONFIG
function db_SMB()
{
    $config = require __DIR__ . '/../config/config.php';
    $smb = [
        'smb_ip'    =>$config['db']['smb_ip'],
        'smb_user'  =>$config['db']['smb_user'],
        'smb_pass'  =>$config['db']['smb_pass'],
    ];

    return $smb;
}


/**⚪🔴🔴LOG
 * Ghi log chi tiết lỗi khi không thể khởi tạo hoặc thao tác Transaction SQL Server
 * @param string $message Thông điệp mô tả ngữ cảnh lỗi (Ví dụ: "Không thể khởi tạo Transaction")
 * @return void
 */
function LOG_TRANSACTION_ERROR(string $message = "Lỗi thao tác Transaction"): void
{
    try {
        // 1. Đường dẫn folder log (Đồng bộ với cấu trúc bạn đang dùng)
        $logDir = __DIR__ . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . 'SQLSRV';

        if (!is_dir($logDir)) {
            @mkdir($logDir, 0775, true);
        }

        // File log lưu lỗi Transaction
        $logFile = $logDir . DIRECTORY_SEPARATOR . 'LOG_TRANSACTION_ERROR.log';

        // 2. Xác định vị trí file + dòng gọi hàm này
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $callerFile = $backtrace[0]['file'] ?? 'Unknown';
        $callerLine = $backtrace[0]['line'] ?? 'Unknown';

        // 3. Trích xuất chi tiết lỗi từ SQLSRV Driver
        $sqlsrvErrors = sqlsrv_errors();
        $errDetail = '';
        if ($sqlsrvErrors !== null) {
            foreach ($sqlsrvErrors as $err) {
                $errDetail .= sprintf(
                    "[Code: %s][SQLState: %s] %s\n",
                    $err['code'] ?? 'N/A',
                    $err['SQLSTATE'] ?? 'N/A',
                    $err['message'] ?? 'N/A'
                );
            }
        } else {
            $errDetail = "Không lấy được chi tiết lỗi từ sqlsrv_errors().\n";
        }
        // LẤY THÔNG TIN NGƯỜI DÙNG
        if (isset($_SESSION['user']) && isset($_SESSION['user']['UsID'])) {
            $UsDisplay = $_SESSION['user']['UsDisplay']; 
        } else {
            $UsDisplay ='Unknown';
        }
        // 4. Dựng nội dung log
        $logContent  = "\n";
        $logContent .= "============================================================\n";
        $logContent .= "USER       : " . $UsDisplay . "\n";
        $logContent .= "TIME       : " . date('Y-m-d H:i:s') . "\n";
        $logContent .= "TYPE       : TRANSACTION ERROR\n";
        $logContent .= "IP         : " . ($_SERVER['REMOTE_ADDR'] ?? 'CLI') . "\n";
        $logContent .= "REQUEST    : " . ($_SERVER['REQUEST_URI'] ?? 'CLI') . "\n";
        $logContent .= "FILE       : " . $callerFile . "\n";
        $logContent .= "LINE       : " . $callerLine . "\n";
        $logContent .= "MESSAGE    : " . $message . "\n";
        $logContent .= "SQL ERROR  :\n";
        $logContent .= trim($errDetail) . "\n";

        // 5. Ghi file
        @file_put_contents(
            $logFile,
            $logContent,
            FILE_APPEND | LOCK_EX
        );

    } catch (Throwable $logError) {
        // Bỏ qua lỗi ghi log 
    }
}

/**⚪🔴🔴LOG
 * CHỦ ĐỘNG GHI LOG ĐỂ DEBUG
 */
function PROLOG(string $message = "Code đã chạy đến đây "): void 
{
    try {
        // 1. ĐƯỜNG DẪN FOLDER LOG
        $logDir = __DIR__ . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . 'PROCESS';
        // 2. CHECK Nếu không có tự động tạo
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0775, true);
        }

        // 3. FILENAME CHỨA LOG
        $logFile = $logDir . DIRECTORY_SEPARATOR . 'PROLOG.log';

        // 4. Xác định vị trí file + dòng gọi hàm này
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $callerFile = $backtrace[0]['file'] ?? 'Unknown';
        $callerLine = $backtrace[0]['line'] ?? 'Unknown';   

        // LẤY THÔNG TIN NGƯỜI DÙNG
        if (isset($_SESSION['user']) && isset($_SESSION['user']['UsID'])) {
            $UsDisplay = $_SESSION['user']['UsDisplay']; 
        } else {
            $UsDisplay ='Unknown';
        }
        // 4. Dựng nội dung log
        $logContent  = "";
        $logContent .= "TIME: " . date('Y-m-d H:i:s') . ", ";
        $logContent .= "USER: " . $UsDisplay . ", ";
        $logContent .= "FILE: " . $callerFile . ", ";
        $logContent .= "LINE: " . $callerLine . ", ";
        $logContent .= "MESSAGE: " . $message ;
        // 5. Ghi file
        @file_put_contents(
            $logFile,
            $logContent,
            FILE_APPEND | LOCK_EX
        );
    } catch (Throwable $logError) {
        // Bỏ qua lỗi ghi log 
    }
}

/**⚪🔴🔴LOG
 * ĐẶT NGAY SAU MỖI CATCH
 */
function CATCHLOG(Throwable $e, string $customMessage = "Catch: Error"): void 
{
    try {
        // 1. ĐƯỜNG DẪN FOLDER LOG 
        $logDir = __DIR__ . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . 'CATCHLOG';
        // 2. CHECK Nếu không có tự động tạo
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0775, true);
        }

        // 3. FILENAME CHỨA LOG LỖI
        $logFile = $logDir . DIRECTORY_SEPARATOR . 'CATCHLOG.log';

        // 4. Lấy vị trí xảy ra ngoại lệ thực tế từ Exception
        $errorFile = $e->getFile();
        $errorLine = $e->getLine();
        
        // LẤY THÔNG TIN NGƯỜI DÙNG
        if (isset($_SESSION['user']) && isset($_SESSION['user']['UsID'])) {
            $UsDisplay = $_SESSION['user']['UsDisplay']; 
        } else {
            $UsDisplay = 'Unknown';
        }

        // 5. NỘI DUNG LOG
        $logContent  = "\n";
        $logContent .= "============================================================\n";
        $logContent .= "USER       : " . $UsDisplay . "\n";
        $logContent .= "TIME       : " . date('Y-m-d H:i:s') . "\n";
        $logContent .= "TYPE       : EXCEPTION ERROR LOG\n";
        $logContent .= "REQUEST    : " . ($_SERVER['REQUEST_URI'] ?? 'CLI') . "\n";
        $logContent .= "FILE       : " . $errorFile . "\n";
        $logContent .= "LINE       : " . $errorLine . "\n";
        $logContent .= "ERROR CODE : " . $e->getCode() . "\n";
        $logContent .= "MESSAGE    : " . $customMessage . " | " . $e->getMessage() . "\n";
        $logContent .= "--- STACK TRACE ---\n" . $e->getTraceAsString() . "\n";
        
        // 6. RECORD
        @file_put_contents(
            $logFile,
            $logContent,
            FILE_APPEND | LOCK_EX
        );
    } catch (Throwable $logError) {
        // Bỏ qua nếu lỗi ghi log để không làm sập luồng chính của ứng dụng
    }
}


