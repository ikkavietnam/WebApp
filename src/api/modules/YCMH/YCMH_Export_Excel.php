<?php
require_once __DIR__ . '/../../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
require_once __DIR__ . '/../../../core/Database.php';
require_once __DIR__ . '/../../../core/Authorization.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

/////////////////////////////////////////////////////////////////////////////////////// //📌 1. ĐỌC DỮ LIỆU TỪ INPUT
$json = file_get_contents('php://input');
$inputData = json_decode($json, true);

// ấy chính xác giá trị mhhd_id từ client gửi lên (bất kể client gửi dạng số đơn hay dạng object {mhhd_id: X})
$mhhd_id = is_array($inputData) && isset($inputData['mhhd_id']) ? $inputData['mhhd_id'] : $inputData;

if (empty($mhhd_id)) {
    header('Content-Type: application/json');
    echo json_encode(["ok" => false, "msg" => "THIẾU THÔNG TIN MHHD_ID"]);
    exit;
}

/////////////////////////////////////////////////////////////////////////////////////// //📌 2. OPEN CONNECT
$conn = db_connect();

/////////////////////////////////////////////////////////////////////////////////////// //📌 3. LẤY THÔNG TIN BẢNG HEADER
$sql_info = "
    SELECT 
         CreateBy
        ,CreateTime
        ,Mem_code
        ,mhhd_id
        ,mhhd_name
        ,mhhd_department
        ,mhhd_delivery_place
        ,mhhd_note
    FROM ERP_MHHD
    WHERE IsDelete = 0 and mhhd_id = ?
";
// * Đưa tham số vào mảng [] để sqlsrv_query không báo lỗi cấu trúc
$params_info = [$mhhd_id];

// * THỰC THI
$stmt_info = sqlsrv_query($conn, $sql_info, $params_info);
if ($stmt_info === false) {
    header('Content-Type: application/json');
    echo json_encode(["ok" => false, "msg" => "LỖI QUERY HOẶC THỰC THI LỖI HEADER", "error" => sqlsrv_errors()]);
    sqlsrv_close($conn);
    exit;
}
// 🌟🌟🌟
$headerData = sqlsrv_fetch_array($stmt_info, SQLSRV_FETCH_ASSOC);

if (!$headerData) { 
    header('Content-Type: application/json');
    echo json_encode(["ok" => false, "msg" => "HEADER KHÔNG CÓ DỮ LIỆU TRẢ VỀ"]);
    sqlsrv_free_stmt($stmt_info);
    sqlsrv_close($conn);
    exit;
}
sqlsrv_free_stmt($stmt_info); 

/////////////////////////////////////////////////////////////////////////////////////// //📌 4. LẤY THÔNG TIN BẢNG DETAIL
$sql_details = "
    SELECT   mhdt_item
            ,mhdt_qty
            ,mhdt_unit
            ,mhdt_description     
            ,mhdt_purpose
            ,mhdt_delivery_time
    FROM ERP_MHDT
    WHERE IsDelete = 0 and mhhd_id = ?
";   
// * Đưa tham số vào mảng []
$params_details = [$mhhd_id];

// * THỰC THI
$stmt_details = sqlsrv_query($conn, $sql_details, $params_details);
if ($stmt_details === false) {
    header('Content-Type: application/json');
    echo json_encode(["ok" => false, "msg" => "LỖI QUERY HOẶC THỰC THI LỖI DETAILS", "error" => sqlsrv_errors()]);
    sqlsrv_close($conn);
    exit;
}
// 🌟🌟🌟 KHI TRUY VẤN TRẢ VỀ NHIỀU DÒNG
$detailsData = [];
while ($row = sqlsrv_fetch_array($stmt_details, SQLSRV_FETCH_ASSOC)) {
    $detailsData[] = $row;
}

if (empty($detailsData)) {
    header('Content-Type: application/json');
    echo json_encode(["ok" => false, "msg" => "KHÔNG TÌM THẤY DỮ LIỆU BẢNG DETAILS"]);
    sqlsrv_free_stmt($stmt_details);
    sqlsrv_close($conn);
    exit;
}
sqlsrv_free_stmt($stmt_details);
sqlsrv_close($conn);

//🌟/////////////////////////////////////////////////////////////////////////////////////🌟 GIAI ĐOẠN EXPORT EXCEL & LƯU Ổ MẠNG
try {
    // 🎈 1. TẢI FILE MẪU TỪ Ổ MẠNG VỀ CONTAINER
    $localTemplatePath = '/tmp/Form_ycmh.xlsx'; 
    $smbGetCommand = 'smbclient //192.168.110.232/Document -U Guest% -c "get Form\\ERP_YCMH\\Form_ycmh.xlsx ' . $localTemplatePath . '" 2>&1';
    
    exec($smbGetCommand, $getOutput, $getReturnVar);
    if ($getReturnVar !== 0 || !file_exists($localTemplatePath)) {
        throw new Exception("Không thể tải file mẫu từ ổ mạng. Chi tiết: " . implode("\n", $getOutput));
    }

    // 🎈 2. LOAD FILE EXCEL MẪU VÀ ĐỔ DỮ LIỆU
    $spreadsheet = IOFactory::load($localTemplatePath);
    $sheet = $spreadsheet->getActiveSheet();

    // Convert date lập phiếu
    $formatted_date = "";
    if (isset($headerData['CreateTime'])) {
        if ($headerData['CreateTime'] instanceof DateTime) {
            $formatted_date = $headerData['CreateTime']->format('d/m/Y');
        } else {
            $formatted_date = date('d/m/Y', strtotime($headerData['CreateTime']));
        }
    }

    // Đổ dữ liệu Header
    $sheet->setCellValue('E5', $formatted_date);
    $sheet->setCellValue('E6', $headerData['CreateBy']);
    $sheet->setCellValue('E7', $headerData['Mem_code']);
    $sheet->setCellValue('E8', $headerData['mhhd_department']);
    $sheet->setCellValue('E9', $headerData['mhhd_delivery_place'] ?? ''); 
    $sheet->setCellValue('B15', $headerData['mhhd_note'] ?? ''); 

    // Đổ dữ liệu Details
    $startRow = 12; 
    $currentRow = $startRow;

    foreach ($detailsData as $index => $row) {
        if ($currentRow > $startRow) {
            $sheet->insertNewRowBefore($currentRow, 1);
        }

        $delivery_time = "";
        if (isset($row['mhdt_delivery_time']) && !empty($row['mhdt_delivery_time'])) {
            if ($row['mhdt_delivery_time'] instanceof DateTime) {
                $delivery_time = $row['mhdt_delivery_time']->format('d/m/Y');
            } else {
                $delivery_time = date('d/m/Y', strtotime($row['mhdt_delivery_time']));
            }
        }

        $sheet->setCellValue('B' . $currentRow, $index + 1);
        $sheet->setCellValue('C' . $currentRow, $row['mhdt_item']);
        $sheet->setCellValue('D' . $currentRow, $row['mhdt_qty']);
        $sheet->setCellValue('E' . $currentRow, $row['mhdt_unit']);
        $sheet->setCellValue('F' . $currentRow, $row['mhdt_description']);
        $sheet->setCellValue('G' . $currentRow, $row['mhdt_purpose']);
        $sheet->setCellValue('H' . $currentRow, $delivery_time);

        $currentRow++;
    }

    // 🎈 3. LƯU THÀNH FILE EXCEL HOÀN CHỈNH TẠI THƯ MỤC TẠM CỦA CONTAINER
    $fileName = "YCMH_" . $headerData['mhhd_name'] . ".xlsx";
    $localResultPath = '/tmp/' . $fileName;

    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save($localResultPath); // Lưu file tạm hoàn chỉnh vào container

    // 🎈 4. ĐẨY FILE HOÀN CHỈNH NGƯỢC LÊN Ổ MẠNG ĐÍCH (Thư mục Data\YCMH)
    // Lệnh sử dụng lệnh 'put' của smbclient để upload file lên Windows Share
    $smbPutCommand = 'smbclient //192.168.110.232/Document -U Guest% -c "put ' . $localResultPath . ' Data\\YCMH\\' . $fileName . '" 2>&1';
    
    exec($smbPutCommand, $putOutput, $putReturnVar);

    // Xóa các file rác tạm trong container để giải phóng bộ nhớ
    if (file_exists($localTemplatePath)) { unlink($localTemplatePath); }
    if (file_exists($localResultPath)) { unlink($localResultPath); }

    // Kiểm tra kết quả upload lên ổ mạng
    if ($putReturnVar !== 0) {
        throw new Exception("Lỗi không thể lưu file vào ổ mạng Data\\YCMH. Chi tiết: " . implode("\n", $putOutput));
    }

    // 🎈 5. TRẢ KẾT QUẢ ĐỒNG BỘ JSON VỀ CHO CLIENT WEBAPP
    header('Content-Type: application/json');
    echo json_encode([
        "ok" => true, 
        "msg" => "Tạo phiếu và lưu lưu trữ File bản gốc thành công!",
        "file_name" => $fileName
    ]);
    exit;

} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(["ok" => false, "msg" => "Lỗi xử lý file: " . $e->getMessage()]);
    exit;
}