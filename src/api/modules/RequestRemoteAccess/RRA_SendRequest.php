<?php
//⚪ LOAD DATA 
    // REQUIRE
        require_once __DIR__ . '/../../../core/Database.php';
        require_once __DIR__ . '/../../../core/Authorization.php';
    
    header('Content-Type: application/json');

//⚪LẤY THỜI ĐIỂM CHUNG   
    $DATE = new DateTime(); 
		// 2. Định dạng đối tượng đó ra các chuỗi khác nhau theo ý muốn (Dùng dấu mũi tên -> format)
		$CurrentTime_SQL  = $DATE->format('Y-m-d H:i:s'); 			// Kết quả: 2026-07-02 14:30:00

//⚪ LẤY DỮ LIỆU GỬI TỪ CLIENT RETURN Email
    $payload = json_decode(file_get_contents("php://input"), true);
    //MAPPING
    
    $Dept_to_Info = [

        'ACC'        => 'ACC-Ms.Lý      , acc@ikka-itv.com.vn',
        'ADM'        => 'CS-Ms.Linh     , adm3@ikka-itv.com.vn',
        'ASSY'       => 'QAC-Khải       , assy3@ikka-itv.com.vn',
        'CS'         => 'CS-Ms.Linh     , adm6@ikka-itv.com.vn',
        'FC'         => 'Mr-Karibe      , k.karibe@ikka-itv.com.vn',
        'INJ'        => 'INJ-Mr.Tuấn    , inj@ikka-itv.com.vn',
        'IP'         => 'Mr-Takada      , y.takada@ikka-itv.com.vn',
        'MG'         => 'CS-Ms.Linh     , adm3@ikka-itv.com.vn',
        'PC'         => 'PC-Ms.Nguyệt   , qa@ikka-itv.com.vn',
        'PRES'       => 'QAC-Khải       , assy3@ikka-itv.com.vn',
        'PURCHASING' => 'CS-Ms.Linh     , adm3@ikka-itv.com.vn',
        'QAC'        => 'QAC-Mr.Hoàng   , hoang@ikka-itv.com.vn',
        'TC'         => 'INJ-Mr.Tuấn    , inj@ikka-itv.com.vn',
        'WH'         => 'PC-Ms.Nguyệt   , qa@ikka-itv.com.vn'

      /*   'ACC'        => 'ACC-Ms.Lý      , adm6@ikka-itv.com.vn',
        'ADM'        => 'CS-Ms.Linh     , adm6@ikka-itv.com.vn',
        'ASSY'       => 'QAC-Khải       , adm6@ikka-itv.com.vn',
        'CS'         => 'CS-Ms.Linh     , adm6@ikka-itv.com.vn',
        'FC'         => 'Mr-Karibe      , adm6@ikka-itv.com.vn',
        'INJ'        => 'INJ-Mr.Tuấn    , adm6@ikka-itv.com.vn',
        'IP'         => 'Mr-Takada      , adm6@ikka-itv.com.vn',
        'MG'         => 'CS-Ms.Linh     , adm6@ikka-itv.com.vn',
        'PC'         => 'PC-Ms.Nguyệt   , adm6@ikka-itv.com.vn',
        'PRES'       => 'QAC-Khải       , adm6@ikka-itv.com.vn',
        'PURCHASING' => 'CS-Ms.Linh     , adm6@ikka-itv.com.vn',
        'QAC'        => 'QAC-Mr.Hoàng   , adm6@ikka-itv.com.vn',
        'TC'         => 'INJ-Mr.Tuấn    , adm6@ikka-itv.com.vn',
        'WH'         => 'PC-Ms.Nguyệt   , adm6@ikka-itv.com.vn', */

    ];

    $DeptInfo = MAPPING_DEPT_TO_INFO($payload['BOPHAN'],$Dept_to_Info);
    $NguoixacnhanName   = $DeptInfo['username'];
    $NguoixacnhanEmail  = $DeptInfo['email'];


//⚪ MỞ KẾT NỐI
    $conn = db_connect();
//⚪ MAIN
    //🍒Tạo security code
        $sql="
            DECLARE @Chars VARCHAR(62) = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            DECLARE @Code VARCHAR(20);
            DECLARE @IsExists INT = 1;

            -- Vòng lặp cho đến khi sinh ra 1 code CHƯA TỒN TẠI trong bảng ERP_RRA
            WHILE @IsExists = 1
            BEGIN
                SET @Code = '';
                
                -- Ghép 20 ký tự ngẫu nhiên
                WHILE LEN(@Code) < 20
                BEGIN
                    -- RAND(CHECKSUM(NEWID())) đảm bảo tính ngẫu nhiên chuẩn khi chạy liên tục
                    SET @Code = @Code + SUBSTRING(@Chars, CAST(FLOOR(RAND(CHECKSUM(NEWID())) * 62) + 1 AS INT), 1);
                END

                -- Kiểm tra xem Code này đã có trong bảng ERP_RRA chưa
                IF NOT EXISTS (SELECT 1 FROM ERP_RequestRemoteAccess WHERE RraStrSec = @Code)
                BEGIN
                    SET @IsExists = 0; -- Thoát vòng lặp khi tạo được mã duy nhất
                END
            END
            SELECT @Code as StrSec
        ";
    //Execute
        $stmtStrSec = sqlsrv_query($conn,$sql);
        
    //LẤY KẾT QUẢ
        $rowSec = sqlsrv_fetch_array($stmtStrSec, SQLSRV_FETCH_ASSOC);
        $StrSec = $rowSec['StrSec'];

    //QR
        $sqlstr = "
            INSERT INTO ERP_RequestRemoteAccess (
                CreateBy,
                UsDisplay,
                UsMemberCode,
                DeptName,
                Hostname,
                TimeStart,
                TimeEnd,
                RraTimeTotal,
                RraDestion,
                RraPurpose,
                RraStrSec,
                RraConfirmBy
            )   
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?
            )
        ";

    //THAM SỐ
        $params = [
            $payload['NHANVIEN'],
            $payload['NHANVIEN'],
            $payload['MEMBERCODE'],
            $payload['BOPHAN'],
            $payload['MAYTINH'],
            $payload['TIMESTART'],
            $payload['TIMEEND'],
            $payload['MSGTOTAL'],
            $payload['DESTION'],
            $payload['MsgText'],
            $StrSec,
            $NguoixacnhanName,

        ];
    //EXECUTE
        $stmt = sqlsrv_query($conn,$sqlstr,$params);
    //CHECK
       

        $rowsAffected = sqlsrv_rows_affected($stmt);
        sqlsrv_free_stmt($stmt);

        if ($rowsAffected !== false && $rowsAffected > 0) {
            echo json_encode([
                "ok"     => true,
                "StrSec" => $StrSec, // Sửa lại đúng chính tả key return
                "NguoixacnhanName"  => $NguoixacnhanName,
                "NguoixacnhanEmail" => $NguoixacnhanEmail,
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                "ok"  => false,
                "msg" => "Không thể cập nhật dữ liệu vào hệ thống!",
            ], JSON_UNESCAPED_UNICODE);
        }

        sqlsrv_close($conn);


//🚩~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ 🚩MAPPING
// 1🔴. DANH SÁCH NGƯỜI NHẬN EMAIL XÁC NHẬN CHO CÁC BỘ PHẬN
$Dept_to_Info1 = [
    'ACC'        => 'ACC-Ms.Lý      , acc@ikka-itv.com.vn',
    'ADM'        => 'CS-Ms.Linh     , adm3@ikka-itv.com.vn',
    'ASSY'       => 'QAC-Khải       , assy3@ikka-itv.com.vn',
    'CS'         => 'CS-Ms.Linh     , adm3@ikka-itv.com.vn',
    'FC'         => 'Mr-Karibe      , k.karibe@ikka-itv.com.vn',
    'INJ'        => 'INJ-Mr.Tuấn    , inj@ikka-itv.com.vn',
    'IP'         => 'Mr-Takada      , y.takada@ikka-itv.com.vn',
    'MG'         => 'CS-Ms.Linh     , adm3@ikka-itv.com.vn',
    'PC'         => 'PC-Ms.Nguyệt   , qa@ikka-itv.com.vn',
    'PRES'       => 'QAC-Khải       , assy3@ikka-itv.com.vn',
    'PURCHASING' => 'CS-Ms.Linh     , adm3@ikka-itv.com.vn',
    'QAC'        => 'QAC-Mr.Hoàng   , hoang@ikka-itv.com.vn',
    'TC'         => 'INJ-Mr.Tuấn    , inj@ikka-itv.com.vn',
    'WH'         => 'PC-Ms.Nguyệt   , qa@ikka-itv.com.vn'
];


// 2🔴. HÀM HỖ TRỢ LẤY THÔNG TIN NGƯỜI DÙNG & EMAIL THEO BỘ PHẬN
function MAPPING_DEPT_TO_INFO($deptKey, $map) {
    $deptKey = strtoupper(trim($deptKey)); // Chuẩn hóa mã bộ phận về chữ hoa
    
    if (isset($map[$deptKey])) {
        // Tách chuỗi bởi dấu phẩy
        $parts = explode(',', $map[$deptKey]);
        
        return [
            'username' => trim($parts[0]),
            'email'    => isset($parts[1]) ? trim($parts[1]) : ''
        ];
    }
    
    // Trả về mặc định nếu không tìm thấy mã bộ phận
    return [
        'username' => 'Unknown',
        'email'    => ''
    ];
}