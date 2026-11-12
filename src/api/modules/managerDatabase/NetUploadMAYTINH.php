<?php
header('Content-Type: application/json; charset=utf-8');

// 1. Nhúng thư viện Composer và đọc file Config
require_once __DIR__ . '/../../../vendor/autoload.php'; 
$config = require_once __DIR__ . '/../../../config/configNET.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['excel_file'])) {
    echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ.']);
    exit;
}

try {
    // 2. Kết nối Cơ sở dữ liệu (SQL Server qua PDO)
    $dbConfig = $config['db'];
    
    // Tùy thuộc vào extension của Docker bạn dùng là sqlsrv hay dblib, chuỗi DSN sẽ khác một chút.
    // Dưới đây là chuẩn dsn phổ biến cho SQL Server trên Linux/Docker:
    $dsn = "sqlsrv:Server={$dbConfig['server']};Database={$dbConfig['database']}";
    // Nếu dùng dblib (FreeTDS) hãy đổi thành: $dsn = "dblib:host={$dbConfig['server']};dbname={$dbConfig['database']};charset=utf8";

    $pdo = new PDO($dsn, $dbConfig['uid'], $dbConfig['pwd'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // 3. Đọc dữ liệu từ file Excel được upload lên
    $fileTmpPath = $_FILES['excel_file']['tmp_name'];
    $spreadsheet = IOFactory::load($fileTmpPath);
    $worksheet = $spreadsheet->getActiveSheet();
    $rows = $worksheet->toArray();

    if (count($rows) <= 1) {
        echo json_encode(['success' => false, 'message' => 'File Excel trống hoặc không đúng định dạng.']);
        exit;
    }

    // 4. Chuẩn bị câu lệnh SQL MERGE (UPSERT) tối ưu hiệu năng
    // Nếu trùng MtHost -> Cập nhật MtUser, MtPart. Nếu chưa có -> INSERT mới.
    $sql = "MERGE MAYTINH AS target
            USING (SELECT :MtHost, :MtUser, :MtPart) AS source (MtHost, MtUser, MtPart)
            ON (target.MtHost = source.MtHost)
            WHEN MATCHED THEN
                UPDATE SET target.MtUser = source.MtUser, target.MtPart = source.MtPart
            WHEN NOT MATCHED THEN
                INSERT (MtHost, MtUser, MtPart) VALUES (source.MtHost, source.MtUser, source.MtPart);";
    
    $stmt = $pdo->prepare($sql);
    
    $processedCount = 0;
    $pdo->beginTransaction(); // Dùng Transaction để tăng tốc độ ghi dữ liệu

    foreach ($rows as $index => $row) {
        // Bỏ qua dòng tiêu đề đầu tiên (MtHost | MtUser | MtPart)
        if ($index === 0) continue; 

        // Đọc dữ liệu từ cột A, B, C
        $mtHost = isset($row[0]) ? trim($row[0]) : '';
        $mtUser = isset($row[1]) ? trim($row[1]) : '';
        $mtPart = isset($row[2]) ? trim($row[2]) : '';

        // Bỏ qua dòng trống nếu không có tên máy chủ
        if (empty($mtHost)) continue;

        // Thực thi câu lệnh
        $stmt->execute([
            ':MtHost' => $mtHost,
            ':MtUser' => $mtUser,
            ':MtPart' => $mtPart
        ]);
        $processedCount++;
    }

    $pdo->commit(); // Lưu toàn bộ thay đổi vào DB

    echo json_encode([
        'success' => true,
        'processed' => $processedCount
    ]);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack(); // Hoàn tác nếu có lỗi xảy ra giữa chừng
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}