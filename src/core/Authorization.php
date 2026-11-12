<?php
require_once __DIR__ . '/Database.php';

/**
 * 1. Hàm checkUser: Xác thực danh tính (Authentication)
 * Mục đích: Kiểm tra Session và đối soát với Database xem User còn tồn tại/bị khóa không.
 * Trả về: Trả về thông tin User trong DB nếu hợp lệ, nếu không thì exit.
 🟢🟢🟢🟢*/
function checkUser() {
    //🍒 KHỞI TẠO SESSION
    if (session_status() === PHP_SESSION_NONE) {session_start();}
    // 
    header('Content-Type: application/json; charset=utf-8');

    //🍒 KIỂM TRA SESSION ( Session không tồn tại hoặc session có tồn tại nhưng không có ID)
        if (!isset($_SESSION['user']) || !isset($_SESSION['user']['UsID'])) {
            TraKetQuaError(401, "Auth-01 : Không tìm thấy thông tin người dùng, Vui lòng đăng nhập lại!");
        }

    //🍒 KIỂM TRA THỜI GIAN DUY TRÌ ĐĂNG NHẬP
        $LifeTime = time() - $_SESSION['LastActivity'];
        if($LifeTime>$_SESSION['LifeActivity']){
            session_unset();
            session_destroy();
            TraKetQuaError(401, "Auth-02 : Thời gian duy trì đăng nhập đã quá hạn. Vui lòng đăng nhập lại...");
        } else{
            $_SESSION['LastActivity'] = time(); // LÀM MỚI THỜI GIAN
        }


    //🍒 Đối chiếu với Database (Xác thực thời gian thực)
        $conn = db_connect();
        $userId = (int)$_SESSION['user']['UsID'];
        $params = [$userId];
        //🎯 THAY ĐỔI Ở ĐÂY
        ///////////////////////////////////////////////////////
        $sql = "SELECT UsID, UsType 
                FROM dbo.SYS_USERS 
                WHERE UsID = ? 
                    AND IsActive = 1
                    AND isnull(IsDelete,0) <>  1";
        ///////////////////////////////////////////////////////
        $stmt = sqlsrv_query($conn, $sql, $params);
        CHKQUERY($stmt,$conn,'CHECKING USERS');

        if ($stmt === false) {
            TraKetQuaError(500, "Auth-03 : Lỗi kết nối hệ thống.");
        }

        $dbUser = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

        // Nếu không tìm thấy trong DB hoặc dữ liệu sai lệch
        if (!$dbUser) {
            session_destroy(); // Xóa session rác
            TraKetQuaError(401, "Auth-04 : Tài khoản không hợp lệ hoặc đã bị xóa.");
        }

        return $dbUser; // Trả về dữ liệu sạch từ DB để các hàm sau sử dụng
}





/**
 * 2. Hàm checkLevel: Phân quyền (Authorization)
 * Mục đích: Chạy checkUser trước, sau đó mới kiểm tra quyền truy cập.
 🟢🟢🟢🟢*/
function checkLevel($maxAllowedLevel = -1) {
    if (!is_numeric($maxAllowedLevel)) {
        TraKetQuaError(400, "Auth-05 : Tham số maxAllowedLevel không hợp lệ.");
    }
    // Bước 1: Gọi hàm checkUser để đảm bảo người dùng hợp lệ
    $currentUser = checkUser();
    
    // Bước 2: Kiểm tra cấp độ
    // Giả sử: Level càng thấp quyền càng cao (ví dụ: 1 là Admin, 5 là User)
    $userType = (int)$currentUser['UsType'];

    if ($userType > $maxAllowedLevel) {
        TraKetQuaError(403, "Auth-06 : Bạn không có quyền thực hiện chức năng này.");
    }

    return true; // Hợp lệ hoàn toàn
}




/**
 * Hàm phụ trợ: Xuất thông báo lỗi JSON
🟢🟢🟢🟢 */
function TraKetQuaError($code, $msg) {

    http_response_code($code);
    if (!headers_sent()) {header('Content-Type: application/json; charset=utf-8');}
        echo json_encode([
            "ok"        => false,
            "title"     => "Error Authorization",
            "msg"       => $msg,
            "ErrCode"   => $code,
            "ErrDetail" => '',
            "vitri"     => "Authorization.php",

        ], JSON_UNESCAPED_UNICODE);
    exit;
}