<?php
if (session_status() === PHP_SESSION_NONE) {session_start();}
    header('Content-Type: application/json');

require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../../core/Authorization.php';



checkLevel(1);

$conn = db_connect();

$userId = $_SESSION['user']['UsID'];

$sql = "SELECT * FROM dbo.WebUsers WHERE UsID = ?";

$stmt = sqlsrv_query($conn, $sql, [$userId]);
if ($stmt === false) {
    echo json_encode(["ok" => false, "msg" => sqlsrv_errors()]);
    exit;
}

$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

echo json_encode([
    "user" => $row
]);