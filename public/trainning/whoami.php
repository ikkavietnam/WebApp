<?php
// Cách 1: Sử dụng hàm posix_getpwuid (Hoạt động tốt trên Linux)
if (function_exists('posix_getpwuid')) {
    $userInfo = posix_getpwuid(posix_geteuid());
    echo "PHP User: <b>" . $userInfo['name'] . "</b> (UID: " . $userInfo['uid'] . ")<br>";
    echo "PHP Group: (GID: " . $userInfo['gid'] . ")<br>";
} else {
    // Cách 2: Gọi lệnh hệ thống whoami
    $user = exec('whoami');
    echo "PHP User: <b>" . $user . "</b><br>";
}

