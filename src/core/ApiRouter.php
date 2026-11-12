<?php
/**
 * ======================================================================
 *  API GATEWAY - cổng vào duy nhất của toàn bộ API
 * ======================================================================
 * URL không đổi:  /api/<route>  (qua .htaccess)  hoặc  api_gateway.php?route=<route>
 *
 * Mỗi route khai báo ĐÚNG MỘT dòng trong bảng $routes bên dưới:
 *     'ten/route' => [ 'file/tuong/doi/trong/src/api', QUYỀN, [số_lần, số_giây] ],
 *
 * QUYỀN (phần tử thứ 2) chỉ có 3 loại:
 *     GW_PUBLIC  : công khai, KHÔNG cần đăng nhập
 *     GW_USER    : cần đăng nhập, mọi cấp
 *     1, 3, 5... : cần đăng nhập và UsType <= số này (số NHỎ = quyền CAO, 1 = Admin)
 *
 * Phần tử thứ 3 (tuỳ chọn): giới hạn tốc độ theo IP, dạng [tối đa N request, trong M giây].
 *
 * Quy tắc an toàn:
 *   - Route KHÔNG có trong bảng            -> 404
 *   - Quyền khai báo sai/thiếu             -> 500 (từ chối, KHÔNG mở cửa)
 *   - Muốn thêm API mới: thêm 1 dòng vào bảng, bắt buộc phải chọn quyền.
 *
 * File này đặt ở đâu cũng chạy (src/core hoặc public) vì dùng đường dẫn tuyệt đối.
 * ======================================================================
 */

const GW_PUBLIC = 'public';
const GW_USER   = 'user';

$appRoot = '/var/www/App';                 // đường dẫn App TRONG container
$apiDir  = $appRoot . '/src/api/';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// ======================================================================
//🟢  BẢNG ROUTE
// ======================================================================
$routes = [

    // ───────────── CÔNG KHAI (không cần đăng nhập) ─────────────
        'auth/check-session'     => ['auth/checkSession.php', GW_PUBLIC],                 // tự trả về trạng thái đăng nhập
        'auth/login'             => ['auth/login.php',        GW_PUBLIC, [20, 300]],      // chống dò mật khẩu: 20 lần / 5 phút / IP

    //Form "Request Remote Access" (người chưa đăng nhập dùng, xác nhận qua email bằng mã bí mật)
        'RRA_getInfoMember'      => ['modules/RequestRemoteAccess/RRA_getInfoMember.php',     GW_PUBLIC, [60, 60]],
        'RRA_getInfoListManager' => ['modules/RequestRemoteAccess/RRA_getInfoListManager.php', GW_PUBLIC, [60, 60]],
        'RRA_SendRequest'        => ['modules/RequestRemoteAccess/RRA_SendRequest.php',        GW_PUBLIC, [5, 600]],
        'RRA_ActionConfirm'      => ['modules/RequestRemoteAccess/RRA_ActionConfirm.php',      GW_PUBLIC, [20, 600]],
        //RRA_SendMAIL đang nhận người nhận + nội dung mail TỪ CLIENT -> ai cũng có thể bắt hệ thống gửi mail tuỳ ý.
        //Giới hạn tốc độ chỉ là biện pháp tạm. Cần sửa file để lấy người nhận/nội dung từ database theo StrSec.
        'RRA_SendMAIL'           => ['modules/RequestRemoteAccess/RRA_SEND_MAIL.php',          GW_PUBLIC, [5, 600]],

    // ───────────── ĐĂNG NHẬP, MỌI CẤP ─────────────
    'users/getUsersDetail'          => ['users/usersDetail.php',   GW_USER],           // file đã có checkUser
    'users/SaveUser'                => ['users/usersSave.php',     GW_USER],           // chỉ sửa thông tin của chính mình ($_SESSION['user'])
    'modules/getSoftwareInfomation' => ['modules/managerNET/getSoftwareInfomation.php', GW_USER], // TRƯỚC ĐÂY không kiểm tra: lộ danh sách thiết bị/IP/MAC

    // ───────────── QUẢN TRỊ (cấp 1) ─────────────
    'users/ChangePassword'  => ['users/usersChangePassword.php', 1],   // khớp checkLevel(1) trong file. Nếu user thường cũng cần tự đổi mật khẩu -> đổi thành GW_USER và rà lại file
    'users/profile'         => ['users/usersProfile.php',        1],
    'users/NewProfile'      => ['users/usersNewProfile.php',     1],   // TRƯỚC ĐÂY không kiểm tra: ai cũng tạo được tài khoản
    'users/SaveProfile'     => ['users/usersUpdateProfile.php',  1],
    'users/DeleteProfile'   => ['users/usersDeleteProfile.php',  1],
    'modules/getListUsers'  => ['modules/managerAccount/getListUsers.php', 1],
    'db/upload'             => ['modules/managerDatabase/NetUploadMAYTINH.php', 1],  // TRƯỚC ĐÂY không kiểm tra: ai cũng upload ghi đè bảng MAYTINH

    // ───────────── YCMH () ─────────────
    //YCMH-LIST
    'modules/YCMH__List_1_Load'               => ['modules/YCMH/YCMH__List_1_Load.php',               5],   //✔️ modules/YCMH/YCMH__List_1_Load.js
    'modules/YCMH__List_4_Events_OrderDetail' => ['modules/YCMH/YCMH__List_4_Events_OrderDetail.php', 3],   //✔️ modules/YCMH/YCMH__List_4_Events.js Nội dung chi tiết phiếu
    
    //YCMH-CREATE
    'modules/YCMH__Create_Getinfo'            => ['modules/YCMH/YCMH__Create_Getinfo.php',            3],   //✔️ modules/YCMH/YCMH__Create  YCMH_CREATE_RENDER
    'modules/YCMH__Create_LuuPhieu'           => ['modules/YCMH/YCMH__Create_LuuPhieu.php',           3],   //✔️ modules/YCMH/YCMH__Create  BTN-HOANTHANH
        
    //YCMH-EMAIL & NOTIFY
    'modules/YCMH__WF_SENDMAIL'               => ['modules/YCMH/YCMH__WF_SENDMAIL.php',               3],  // TRƯỚC ĐÂY không gọi checkLevel
        //✔️ modules/YCMH/YCMH__Create.js   BTN-HOANTHANH
        //✔️ view/ycmh/approval.js          BTNCLICK_ACTION 

    //APPROVAL
    'modules/YCMH_Duyet_INIT'                 => ['modules/YCMH/YCMH_Duyet_INIT.php',                 3],   //✔️ view/ycmh/approval.js
    'modules/YCMH_DuyetPhieu_BtnAction'       => ['modules/YCMH/YCMH_Duyet_BtnAction.php',            3],   //✔️ view/ycmh/approval.js
    
    // Created PDF
    'view/ycmh/createdPDF'                    => ['modules/YCMH/YCMH_PDF_Creator.php',                3],   //✔️ view/ycmh/approval.js
];

// ======================================================================
//  HÀM HỖ TRỢ
// ======================================================================
function gwFail(int $code, string $title, string $msg): void
{
    http_response_code($code);
    echo json_encode([
        'ok'    => false,
        'title' => $title,
        'error' => (string)$code,
        'msg'   => $msg,
        'vitri' => 'API_GateWay',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/** Giới hạn $max request trong $window giây cho mỗi $key (route + IP). Lưu bằng file tạm. */
function gwRateLimit(string $key, int $max, int $window): void
{
    if (random_int(1, 200) === 1) {          // thỉnh thoảng dọn file cũ
        foreach (glob(sys_get_temp_dir() . '/gw_rl_*') ?: [] as $old) {
            if (@filemtime($old) < time() - 86400) { @unlink($old); }
        }
    }

    $fp = @fopen(sys_get_temp_dir() . '/gw_rl_' . hash('sha256', $key), 'c+');
    if (!$fp) { return; }                    // không ghi được file tạm -> bỏ qua giới hạn

    flock($fp, LOCK_EX);
    $now  = time();
    $raw  = stream_get_contents($fp);
    $hits = ($raw !== '' && $raw !== false) ? (json_decode($raw, true) ?: []) : [];
    $hits = array_values(array_filter($hits, fn($t) => $t > $now - $window));

    if (count($hits) >= $max) {
        $retry = max(1, $window - ($now - min($hits)));
        flock($fp, LOCK_UN);
        fclose($fp);
        header('Retry-After: ' . $retry);
        gwFail(429, 'LỖI API - QUÁ NHANH', 'Bạn thao tác quá nhanh. Vui lòng thử lại sau ' . $retry . ' giây.');
    }

    $hits[] = $now;
    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, json_encode($hits));
    flock($fp, LOCK_UN);
    fclose($fp);
}

// ======================================================================
//🟢  XỬ LÝ REQUEST
// ======================================================================
$route = $_GET['route'] ?? '';

    //🧧1- CKH Route phải là chuỗi và có trong bảng
    if (!is_string($route) || !isset($routes[$route])) {
        gwFail(404, 'LỖI API - NOTFOUND', 'Route không hợp lệ hoặc không tồn tại trong API_GW: ' . (is_string($route) ? substr($route, 0, 80) : ''));
    }

    $file   = $routes[$route][0];
    $access = $routes[$route][1] ?? null;
    $rate   = $routes[$route][2] ?? null;
    $path   = $apiDir . $file;

    //🧧2- File nguồn phải tồn tại
    if (!is_file($path)) {
        gwFail(404, 'LỖI API - NOTFOUND', 'api_gateway: không tìm thấy file nguồn của route này.');
    }

    //🧧3- Giới hạn tốc độ (nếu route có khai báo)
    if (is_array($rate)) {
        gwRateLimit($route . '|' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), (int)$rate[0], (int)$rate[1]);
    }

    //🧧4- Kiểm tra quyền TRƯỚC khi chạy file API
    if ($access !== GW_PUBLIC) {
        require_once $appRoot . '/src/core/Authorization.php';   // checkUser(), checkLevel(), TraKetQuaError()

        if ($access === GW_USER) {
            checkUser();                                          // chưa đăng nhập -> 401 và dừng
        } elseif (is_int($access)) {
            checkLevel($access);                                  // thiếu quyền -> 403 và dừng
        } else {
            gwFail(500, 'LỖI API - CẤU HÌNH', 'Route chưa khai báo quyền hợp lệ trong API_GW.');
        }
    }

    //🧧4 Chạy file API
    require $path;
