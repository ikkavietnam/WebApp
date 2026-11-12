<?php
echo "PHP Version: " . phpversion() . "<br>";
echo "SQLSRV Extension: " . (extension_loaded('sqlsrv') ? 'Đã cài đặt' : 'Chưa có') . "<br>";
echo "PDO_SQLSRV Extension: " . (extension_loaded('pdo_sqlsrv') ? 'Đã cài đặt' : 'Chưa có');
