<?php
if (session_status() === PHP_SESSION_NONE){
  session_start();
}
// Điều hướng về trang login nếu chưa có session
if (!isset($_SESSION['user'])) {
  // Tạm thời giả định file login nằm cùng cấp hoặc xử lý qua router
}
?>

<!DOCTYPE html>
<html>
  <head>
    <link rel="stylesheet" href="assets/css/app.css?v=1.0">
  </head>
  <body>

    <!-- Khung nền tối của Popup (Mặc định ẩn bằng display: none) -->
  <div id="LoginFrame" style="display: flex; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.7);">
    <!-- Hộp thoại Login -->
    <div style="background-color: #fff; margin: 15% auto; padding: 25px; border-radius: 5px; width: 200px; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
      <h4 style="margin-top: 0; color: #0a332f; text-align: center;">
        THỜI GIAN DUY TRÌ ĐĂNG NHẬP ĐÃ KẾT THÚC
      </h4>
      <p style="color: #020404; text-align: center; width: 200px">
        Đăng nhập lại để tiếp tục công việc.
      </p>
      
      <form id="erp-timeout-form">
        <div style="margin-bottom: 15px;">
          <label style="display:block; margin-bottom:5px; font-weight:bold;">
            Tài khoản
          </label>
          <input type="text" id="username" required >
        </div>

        <div style="margin-bottom: 20px;">
          <label style="display:block; margin-bottom:5px; font-weight:bold;">
            Mật khẩu
          </label>
          <input type="password" id="password" required >
        </div>
        <div id="msg" style="color: red; font-size: 14px; margin-bottom: 10px; text-align: center;">

        </div>

        <button id="btnLogin" type="submit">
          Đăng nhập lại
        </button>
      </form>
    </div>
  </div>


  </body>
</html>