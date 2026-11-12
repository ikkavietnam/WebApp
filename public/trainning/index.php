<?php


//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ MAPPING
// 1. Khai báo danh sách Mapping
/* $Dept_to_Info = [
    'ACC'        => 'ACC-Ms.Lý      , acc@ikka-itv.com.vn',
    'ADM'        => 'CS-Ms.Linh     , adm3@ikka-itv.com.vn',
    'ASSY'       => 'QAC-Khải       , assy3@ikka-itv.com.vn',
    'CS'         => 'CS-Ms.Linh     , adm3@ikka-itv.com.vn',
    'FC'         => 'Mr-Karibe      , k.karibe@ikka-itv.com.vn',
    'INJ'        => 'INJ-Mr.Tuấn    , inj@ikka-itv.com.vn',
    'IP'         => 'Mr-Takada      , y.takada@ikka-itv.com.vn',
    'MG'         => 'CS-Ms.Linh     , adm3@ikka-itv.com.vn',
    'PC'         => 'PC-Ms.Nguyệt   , qa@ikka-itv.com.vn',
    'PRES'       => 'QAC-Khải       , assy3@ikka-itv.com.vn',
    'PURCHASING' => 'CS-Ms.Linh     , adm3@ikka-itv.com.vn',
    'QAC'        => 'QAC-Mr.Hoàng   , hoang@ikka-itv.com.vn',
    'TC'         => 'INJ-Mr.Tuấn    , inj@ikka-itv.com.vn',
    'WH'         => 'PC-Ms.Nguyệt   , qa@ikka-itv.com.vn'
]; */

// 2. Hàm hỗ trợ lấy thông tin người dùng & email theo bộ phận
/* function MAPPING_DEPT_TO_INFO($deptKey, $map) {
    $deptKey = strtoupper(trim($deptKey)); // Chuẩn hóa mã bộ phận về chữ hoa
    
    if (isset($map[$deptKey])) {
        // Tách chuỗi bởi dấu phẩy
        $parts = explode(',', $map[$deptKey]);
        
        return [
            'username' => trim($parts[0]),
            'email'    => isset($parts[1]) ? trim($parts[1]) : ''
        ];
    }
    
    // Trả về mặc định nếu không tìm thấy mã bộ phận
    return [
        'username' => 'Unknown',
        'email'    => ''
    ];
} */

// --- THỬ NGHIỆM ---

// Bật hiển thị lỗi để dễ dàng debug nếu có sự cố
error_reporting(E_ALL);
ini_set('display_errors', 1);

$targetDir = '/mnt/itv_share/Data/Attach/';
$testFile = $targetDir . 'test_php_write.txt';

echo "<h3>Bắt đầu kiểm tra ghi file vào NAS:</h3>";

// 1. Kiểm tra và tự động tạo thư mục nếu chưa có
if (!is_dir($targetDir)) {
    if (mkdir($targetDir, 0755, true)) {
        echo "<p style='color: green;'>[OK] Đã tạo thành công thư mục: {$targetDir}</p>";
    } else {
        echo "<p style='color: red;'>[LỖI] Không thể tạo thư mục {$targetDir}. Kiểm tra lại phân quyền uid/gid.</p>";
    }
} else {
    echo "<p style='color: green;'>[OK] Thư mục đích đã tồn tại.</p>";
}

// 2. Thử ghi file test
$content = "Test ghi file lúc: " . date('Y-m-d H:i:s') . " từ container PHP 8.5";
if (file_put_contents($testFile, $content) !== false) {
    echo "<p style='color: green; font-weight: bold;'>[THÀNH CÔNG] PHP đã ghi file thành công vào: {$testFile}</p>";
    
    // Đọc lại nội dung file để xác thực
    $readBack = file_get_contents($testFile);
    echo "<p>Nội dung đọc lại từ file: <i>{$readBack}</i></p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>[LỖI] PHP bị từ chối quyền ghi file (Permission denied)!</p>";
}
?>
