<?php
//⚪ LOAD DATA 
    //Xuất Excell
        use PhpOffice\PhpSpreadsheet\IOFactory;
    //Dùng PHPMailer để gửi mail bảo mật
        use PHPMailer\PHPMailer\PHPMailer;
        use PHPMailer\PHPMailer\Exception;

    // REQUIRE
        require_once __DIR__ . '/../../../../public/vendor/autoload.php';
        require_once __DIR__ . '/../../../core/Database.php';
    header('Content-Type: application/json; charset=utf-8'); //THÔNG BÁO VỚI CLIENT : Dữ liệu sắp được trả về sẽ có định dạng JSON (JavaScript Object Notation) 🔈 Bắt buocj phải có dòng này
    //LẤY DỮ LIỆU TỪ CLIENT GỬI
    $data = json_decode(file_get_contents("php://input"), true);

    $conn = db_connect();