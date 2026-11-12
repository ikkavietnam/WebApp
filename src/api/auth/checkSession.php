<?php
// Khởi tạo SESSION
if (session_status() === PHP_SESSION_NONE) {session_start();} //✅ 

header('Content-Type: application/json'); 

if(isset($_SESSION['user'])){  /* KIỂM TRA TRẠNG THÁI ĐĂNG NHẬP */
//1.0
    if(isset($_SESSION['LastActivity'])){
    //1.1
        $LifeTime = time() - $_SESSION['LastActivity'];
        if($LifeTime>$_SESSION['LifeActivity']){
        //1.2
            session_unset();
            session_destroy();
            // Nếu là request thông thường, chuyển hướng về login <CHƯA XÁC ĐỊNH ĐƯỢC TẠM ĐỂ CHỐNG CHÁY>
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {//1.3
                header('Content-Type: application/json', true,555); 
                echo json_encode([
                    "ok"       =>false,
                    "msg"       =>"Đoạn này chưa biết nó vì sao",
                    "error"     =>"",
                    "ErrCode"   =>555,
                ],JSON_UNESCAPED_UNICODE);
                exit;

            } else {
                // Nếu là API, bạn có thể trả về JSON mã 401
                header('Content-Type: application/json', true,401); 
                echo json_encode([
                    "ok"       =>false,
                    "msg"       =>"Thời gian Offline quá lâu! Hãy đăng nhập lại 2",
                    "error"     =>"",
                    "ErrCode"   =>401,
                ],JSON_UNESCAPED_UNICODE);
                exit;
            }//end 1.3

        }else{//1.2
            $_SESSION['LastActivity'] = time();
            echo json_encode([
                "ok" => true,
                "user" => $_SESSION['user'],
            ],JSON_UNESCAPED_UNICODE);

        }//end 1.2

    }//end 1.1

} //end 1.0
else {
    echo json_encode([
        "ok"       =>false,
        "msg"       =>"Chưa đăng nhập",
        "error"     =>"",
        "ErrCode"   =>401,
        "vitri"     =>"",
    ],JSON_UNESCAPED_UNICODE);
    exit;
}//end 1.0
/*⛔*******************************/


