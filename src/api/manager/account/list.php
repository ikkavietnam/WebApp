<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../../core/Database.php';
header('Content-Type: application/json');

$conn = Database::getConnection();
$sql = "SELECT * FROM dbo.WebUsers ORDER BY UsID DESC";
$stmt = sqlsrv_query($conn, $sql);
$data = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $data[] = $row;
}
echo json_encode($data);
