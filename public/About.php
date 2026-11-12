<?php
echo "PHP Version: " . phpversion() . "<br>";
echo "SQLSRV Extension: " . (extension_loaded('sqlsrv') ? 'Đã cài đặt' : 'Chưa có') . "<br>";
echo "PDO_SQLSRV Extension: " . (extension_loaded('pdo_sqlsrv') ? 'Đã cài đặt' : 'Chưa có') . "<br>";

header('Content-Type: text/html; charset=utf-8');

echo "<h2>KẾT QUẢ KIỂM TRA USER HỆ THỐNG CỦA WEB SERVER</h2>";

// 1. Kiểm tra User đang thực thi file PHP này
$currentUser = get_current_user();
echo "<b>1. User sở hữu file PHP (Script Owner):</b> " . $currentUser . "<br>";

// 2. Kiểm tra tên tài khoản dịch vụ đang vận hành PHP (Quan trọng nhất)
if (function_exists('exec')) {
    $web_user = exec('whoami');
    echo "<b>2. User hệ thống đang chạy Web Server (Whoami):</b> <span style='color:red; font-size:18px;'>" . $web_user . "</span><br>";
} else {
    // Phương án thay thế nếu hàm exec bị disabled
    echo "<b>2. User hệ thống đang chạy Web Server (Environment):</b> <span style='color:red; font-size:18px;'>" . getenv('USERNAME') . "</span><br>";
}

// 3. Test thử quyền tạo file trực tiếp lên ổ mạng
$test_dir = '//ITV-SV/Document/Data/Attach/';
$test_file = $test_dir . 'test_connection.txt';

echo "<h3>TEST KẾT NỐI THỰC TẾ:</h3>";
if (is_dir($test_dir)) {
    echo "✔️ Đã nhìn thấy thư mục mạng: $test_dir <br>";
    if (@file_put_contents($test_file, "Test kết nối từ Web Server lúc: " . date('Y-m-d H:i:s'))) {
        echo "<b style='color:green;'>✔️ THÀNH CÔNG: Tạo được file test trên ổ mạng!</b><br>";
        @unlink($test_file); // Xóa file test sau khi tạo thành công
    } else {
        echo "<b style='color:red;'>❌ THẤT BẠI: Nhìn thấy thư mục nhưng KHÔNG THỂ GHI FILE.</b><br>";
        echo "Lỗi chi tiết: " . error_get_last()['message'] . "<br>";
    }
} else {
    echo "<b style='color:red;'>❌ THẤT BẠI: Không thể nhìn thấy thư mục mạng. Sai đường dẫn hoặc bị chặn hoàn toàn.</b><br>";
}



echo "<h2>DANH SÁCH CÁC Ổ ĐĨA MẠNG ĐANG MOUNT TRÊN LINUX</h2>";

if (function_exists('exec')) {
    // Lệnh hiển thị các kết nối mạng Samba/CIFS hoặc NFS đang hoạt động
    exec('mount | grep -E "cifs|smb|192.168.110.232"', $output_mount);
    
    echo "<b>Các điểm Mount liên quan đến ổ mạng:</b><pre>";
    if (!empty($output_mount)) {
        print_r($output_mount);
    } else {
        echo "Không tìm thấy điểm mount trực tiếp nào bằng lệnh 'mount'.\n";
    }
    echo "</pre>";

    // Kiểm tra file cấu hình mount tự động của hệ thống (fstab)
    exec('cat /etc/fstab | grep "192.168.110.232"', $output_fstab);
    echo "<b>Cấu hình tự động trong /etc/fstab (nếu có):</b><pre>";
    if (!empty($output_fstab)) {
        print_r($output_fstab);
    } else {
        echo "Không cấu hình mount tự động trong fstab.\n";
    }
    echo "</pre>";
} else {
    echo "<span style='color:red;'>Hàm exec() đã bị khóa trên Server, không thể chạy lệnh check hệ thống.</span>";
}




// Kiểm tra xem một đường dẫn cụ thể có phải là liên kết ảo đến ổ mạng không
$path = '//192.168.110.232/Document/Form/ERP_YCMH/Form_ycmh.xlsx';
if (is_link($path)) {
    echo "Đây là thư mục ảo (Symlink)! Nó trỏ tới: " . readlink($path);
} else {
    echo "Đây là thư mục thật, không phải Symlink.";
}
?>