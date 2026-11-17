<?php
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