<?php

//⚪ LOAD DATA 
    //Xuất Excell
        use PhpOffice\PhpSpreadsheet\IOFactory;
    //Dùng PHPMailer để gửi mail bảo mật
        use PHPMailer\PHPMailer\PHPMailer;
        use PHPMailer\PHPMailer\Exception;
    // REQUIRE
        require_once __DIR__ . '/../../../../public/vendor/autoload.php'; //* Support SEND EMAIL

    header('Content-Type: application/json; charset=utf-8'); //THÔNG BÁO VỚI CLIENT : Dữ liệu sắp được trả về sẽ có định dạng JSON (JavaScript Object Notation) 🔈 Bắt buocj phải có dòng này
    date_default_timezone_set('Asia/Ho_Chi_Minh');
//⚪LẤY THỜI ĐIỂM CHUNG   
    $DATE = new DateTime(); 
		// 2. Định dạng đối tượng đó ra các chuỗi khác nhau theo ý muốn (Dùng dấu mũi tên -> format)
		$CurrentTime_SQL  = $DATE->format('Y-m-d H:i:s'); 			// Kết quả: 2026-07-02 14:30:00


        $CREATE_TIME = ($CurrentTime_SQL instanceof DateTime) ? $CurrentTime_SQL->format('Y-m-d') : ($CurrentTime_SQL ?? '');
        $CREATE_DATE = $DATE->format('Y-m-d'); 
//⚪ LẤY DỮ LIỆU GỬI TỪ CLIENT RETURN Email
    $payload = json_decode(file_get_contents("php://input"), true);
        $ListEmailSenTo = [
            $payload['NguoixacnhanEmail'] ?? 'adm6@ikka-itv.com.vn','adm6@ikka-itv.com.vn'

        ];


        $NGUOIXACNHAN   = $payload['NguoixacnhanName'] ?? 'Unknown';
       
        $CREATE_BY      = $payload['NHANVIEN'] ?? 'Unknown';
        $MEMBER_CODE    = $payload['MEMBERCODE'] ?? 'Unknown';
        $CREATE_DEPT    = $payload['BOPHAN'] ?? 'Unknown';
        $MAYTINH        = $payload['MAYTINH'] ?? 'Unknown';
        $TIME_START     = $payload['TIMESTART'] ?? 'Unknown';
        $TIME_END       = $payload['TIMEEND'] ?? 'Unknown';
        $TIME_TOTAL     = $payload['MSGTOTAL'] ?? 'Unknown';
        $WHOCONNECT     = $payload['DESTION'] ?? 'Unknown';
        $PURPOSE        = $payload['MsgText'] ?? 'Unknown';
        $StrSec         = $payload['StrSec'] ?? 'Unknown';

        $XACNHANNHANH_URL = "http://192.168.110.235:8085/RequestRemoteAccess/?sec=" . $StrSec;
//⚪ MAIN

        // $ListEmailSenTo

        // Đọc file Template
            $FormHtml = file_get_contents(__DIR__ . '/RRA_EmailTemplate.html'); //Cùng cấp với PHP

            // 🛠️ [SỬA LỖI]: Định dạng lại biến $CreateTime nếu nó là Object DateTime trước khi replace vào HTML ModifiedDate
            //$createTimeStr = ($CreateTime instanceof DateTime) ? $CreateTime->format('Y-m-d') : ($CreateTime ?? '');
            //$ModifiedDateStr = ($ModifiedDate instanceof DateTime) ? $ModifiedDate->format('Y-m-d h:m:s') : ($ModifiedDate ?? '');

            // Thay thế dữ liệu động vào Khung Sườn Email
            $FormHtml = str_replace('{{NGUOIXACNHAN}}',     $NGUOIXACNHAN,          $FormHtml);//🔐

            $FormHtml = str_replace('{{CREATE_TIME}}',      $CREATE_TIME,           $FormHtml);//🔐
            $FormHtml = str_replace('{{CREATE_BY}}',        $CREATE_BY,             $FormHtml);//🔐
            $FormHtml = str_replace('{{MEMBER_CODE}}',      $MEMBER_CODE,           $FormHtml);//🔐
            $FormHtml = str_replace('{{CREATE_DEPT}}',      $CREATE_DEPT,           $FormHtml);//🔐
            $FormHtml = str_replace('{{MAYTINH}}',          $MAYTINH,               $FormHtml);//🔐
            
            $FormHtml = str_replace('{{TIME_START}}',       $TIME_START,            $FormHtml);//🔐
            $FormHtml = str_replace('{{TIME_END}}',         $TIME_END,              $FormHtml);//🔐
            $FormHtml = str_replace('{{TIME_TOTAL}}',       $TIME_TOTAL,            $FormHtml);//🔐
            $FormHtml = str_replace('{{WHOCONNECT}}',       $WHOCONNECT,            $FormHtml);//🔐
            $FormHtml = str_replace('{{PURPOSE}}',          $PURPOSE,               $FormHtml);//🔐
            $FormHtml = str_replace('{{StrSec}}',           $StrSec,                $FormHtml);//🔐


            $FormHtml = str_replace('{{XACNHAN_URL}}',      "http://192.168.110.235:8085/RequestRemoteAccess",                 $FormHtml);//🔐
            $FormHtml = str_replace('{{XACNHANNHANH_URL}}', $XACNHANNHANH_URL, $FormHtml);//🔐




        //🟡8 THIẾT LẬP EMAIL
            // Khởi tạo tiến trình gửi mail bằng SMTP

                if (!empty($ListEmailSenTo)) {
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
                        $mail->Subject = "[Requets Ultraviewer] " . $CREATE_DATE . " - " . $CREATE_DEPT ." / YÊU CẦU XÁC NHẬN MỞ KẾT NỐI UNLTRAVIEWER ";
                        // Nội dung
                        $mail->Body    = $FormHtml;
                        
                        // Người nhận
                        foreach ($ListEmailSenTo as $recipientEmail) {
                            $mail->addAddress($recipientEmail);
                        }
                        $emailsCC1 = ['adm3@ikka-itv.com.vn', 'adm6@ikka-itv.com.vn'];
                        $emailsCC = ['adm3@ikka-itv.com.vn', 'adm6@ikka-itv.com.vn'];
                        // Người nhận CC
                        foreach ($emailsCC as $emails) {
                            $mail->addCC(trim($emails));
                        }
                        // SEND
                        $mail->send();     
  
                    } catch (Exception $e) {
                        // 🛠️ [DEBUG]: in lỗi thực tế ra để biết chính xác SMTP bị block hay do lỗi code:
             
                    }
                } else {
                    // Ghi log lỗi gửi mail
                    error_log("Lỗi gửi mail: " . $mail->ErrorInfo);
                }


//🔴🔴🔴 TRẢ LỜI CLIENT HỢP LỆ       
echo json_encode([
    "ok"  => true, 
    "title"=> "THÀNH CÔNG",
    "StrSec" => $StrSec,
    "XACNHANNHANH_URL" => $XACNHANNHANH_URL,
   
], JSON_UNESCAPED_UNICODE);

