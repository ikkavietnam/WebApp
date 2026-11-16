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
    <!--
    ⬜⬜⬜~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~    HEAD    ~~~~~~~~~~~~~~~~~~~~~~~~~~~   BODY
    📌📌📌Thứ tự ưu tiên META → TITLE → FONT → LIB CSS → CUSTOM CSS → LIB JS → APP JS --> 
    <head>
 
        <!--
        💛 META -->
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <!--
        💚 TITLE -->
        <title>Ikka ERP Application</title>
        <link rel="icon" type="image/png" sizes="32x32" href="assets/img/Logo32x32.png">
        <!--
        💙 FONTS -->
        <link rel="stylesheet" href="assets/css/base/fonts.css">

        <!--
        💜 LIB CSS -->
        <link rel="stylesheet" href="assets/vendor/scrollbars/overlayscrollbars.min.css">
        <link rel="stylesheet" href="assets/vendor/tabulator/tabulator.min.css">

        <!--
        💜 CUSTOM CSS -->
        <link rel="stylesheet" href="assets/css/app.css?v=1.0">

        <!--
        💛 LIB JS **** -->
        <script src="assets/vendor/scrollbars/overlayscrollbars.browser.es6.min.js"></script>
        <script src="assets/vendor/tabulator/tabulator.min.js"></script>
        <script src="assets/vendor/sweetalert2.all.min.js"></script>

        <!--
        💛 APP JS --> 
        <script type="module" src="assets/js/app.js"></script>
        
    </head>

    <!--
    ⬜⬜⬜~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~    BODY    ~~~~~~~~~~~~~~~~~~~~~~~~~~~   BODY -->
    <body class="guest">

        <div id="GUEST"></div>
        <div id="HEADER">
            <div id="HeaderLeft">
                <a id="btnBackGuest" href="#Welcome" title="Về trang Welcome">⌂ WELCOME</a>
                <span> ♡✩‧₊˚ ✧⋄⋆⋅⋆⋄✧ ☾ ⋆*･ﾟ:⋆*･ﾟ </span>
                    Welcome to IKKA Technology VIETNAM 
                ✩‧₊˚ ✧⋄⋆⋅⋆⋄✧ ☾ ⋆*･ﾟ:⋆*･ﾟ


            </div>
            <div id="HeaderRight"></div>
        </div>
        <div id="SIDEBAR"></div>
        <div id="APP"></div>


    </body>


</html>