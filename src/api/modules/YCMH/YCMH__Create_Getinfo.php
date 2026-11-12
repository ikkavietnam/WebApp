<?php
//🟡 FORM CHUNG THAM CHIẾU CÁC MODULE CHỨC NĂNG
    //✅ THAM CHIẾU CÁC MODULE CHỨC NĂNG
    require_once __DIR__ . '/../../../core/Database.php';
    require_once __DIR__ . '/../../../core/Authorization.php';

    //✅ CHECK SESSION & HEADER
    if (session_status() === PHP_SESSION_NONE) {session_start();}
    header('Content-Type: application/json');

    //✅LẤY ID (UsID) NGƯỜI DÙNG & KIỂM TRA QUYỀN HẠN & ĐĂNG NHẬP
        
        if (isset($_SESSION['user']) && isset($_SESSION['user']['UsID'])) {
            $userID = (int)$_SESSION['user']['UsID'];
        }

        checkLevel(3);


    //✅ KHAI BÁO CÁC BIẾN THỜI GIAN ĐỒNG BỘ THEO NHIỀU ĐỊNH DẠNG
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $DATE = new DateTime(); 
    
        $CurrentTime_VN   = $DATE->format('d/m/Y H:i:s'); // Kết quả: 02/07/2026 14:30:00
        $CurrentTime_SQL  = $DATE->format('Y-m-d H:i:s'); // Kết quả: 2026-07-02 14:30:00
        $CurrentTime_Date = $DATE->format('d/m/Y');       // Kết quả: 02/07/2026

    //✅ INPUT
        //$dataIP = json_decode(file_get_contents("php://input"), true) ?? [];


//🟡 1. OPEN CONNECT
$conn = db_connect();
    if (!$conn) {
        if (!headers_sent()) {header('Content-Type: application/json; charset=utf-8');}
        echo json_encode([
            "ok"        => false,
            "title"     => "DISCONNECTED TO SERVER",
            "msg"       => "Lỗi mất kết nối với máy chủ. có thể đang trong thời gian bảo trì hoặc máy chủ có vấn đề ! Hãy thử lại sau 5 phút nữa hoặc liên hệ IT",
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
//🟡 2. LẤY DỮ LIỆU NGƯỜI DÙNG
    $paramsUser = [$userID];
    $sql = "
    SELECT 
        UsID           as ID
        ,UsName         as tendangnhap
        ,UsDisplay      as NhanVien
        ,UsMemberCode   as MaNhanVien
        ,UsEmail        as Email
        ,d.DeptID       as MaBoPhan
        ,d.DeptName     as BoPhan
        ,p.PosID        as MaChucVu
        ,p.PosName      as ChucVu

    FROM SYS_USERS u
    left join SYS_DEPARTMENT d ON d.DeptID = u.DeptID
    left join SYS_POSITION p ON p.PosID = u.PosID
    WHERE 
        u.IsDelete = 0 
        AND u.IsActive =1
        AND u.UsID = ?
    ";
    $stmtUser = sqlsrv_query($conn, $sql, $paramsUser);
    CHKQUERY($stmtUser,$conn,"QUERY TO ERP-USER");//⭕

    $dataUS = null;
    if ($row = sqlsrv_fetch_array($stmtUser, SQLSRV_FETCH_ASSOC)) {   
        $dataUS = $row;
    }
    sqlsrv_free_stmt($stmtUser);

    
//🟡 3. GET DATA HISTORY
    $dataPHIEU = [];
    if ($dataUS && isset($dataUS['MaBoPhan'])) {
        // Thêm điều kiện lọc theo DeptID vào WHERE 
        $sqlPhieu = "
            SELECT
                hd.mhhd_id as YCMH_id
                ,hd.mhhd_name as YCMH_name
                ,mhhd_status as YCMH_status
                ,mhhd_department as YCMH_bophan
                ,CreateBy as YCMH_nhanvien
                ,CONVERT(VARCHAR, CreateTime, 103) AS YCMH_date
                ,(select top 1 dt.mhdt_item from ERP_MHDT dt where dt.mhhd_id = hd.mhhd_id) as YCMH_ITEM
            FROM ERP_MHHD hd
            WHERE hd.mhhd_department = ? 
            ORDER BY hd.CreateTime DESC
        ";

        $paramsPhieu = [$dataUS['MaBoPhan']];
        $stmtPhieu = sqlsrv_query($conn, $sqlPhieu, $paramsPhieu);

        CHKQUERY($stmtPhieu,$conn,"QUERY TO ERP-MHHD");//⭕
        
        // ĐƯA DỮ LIỆU PHIẾU VÀO MẢNG
        while ($row = sqlsrv_fetch_array($stmtPhieu, SQLSRV_FETCH_ASSOC)) {   
            $dataPHIEU[] = $row;
        }
        sqlsrv_free_stmt($stmtPhieu);
    }


//🟡 4.GET ITEM HISTORY
    //=================================================
    $dataITEM = [];
    
    if ($dataUS && isset($dataUS['MaBoPhan'])) {
    $sqlITEM ="
        SELECT DISTINCT dt.mhdt_item as YCMH_ITEM, dt.mhdt_unit as YCMH_UNIT, mhdt_delivery_time as Delivery
        FROM ERP_MHDT dt
        LEFT JOIN ERP_MHHD hd ON dt.mhhd_id = hd.mhhd_id
        WHERE hd.mhhd_department = ?
        ORDER BY dt.mhdt_item;
    ";
    $paramsITEM = [$dataUS['MaBoPhan']];
    $stmtITEM = sqlsrv_query($conn, $sqlITEM, $paramsITEM);

    CHKQUERY($stmtITEM,$conn,"QUERY TO ERP-MHDT");//⭕
    $ListItems=[];
    $ListDelivery=[];
    while ($row = sqlsrv_fetch_array($stmtITEM, SQLSRV_FETCH_ASSOC)) {   
        $dataITEM[] = $row;

        if (!empty($row['YCMH_ITEM'])) {
            $val = trim($row['YCMH_ITEM']);
            // Gán key = value
            $ListItems[$val] = $val; 
        }

        if (!empty($row['Delivery'])) {
            $val = trim($row['Delivery']);
            // Gán key = value
            $ListDelivery[$val] = $val; 
        }
    }
    sqlsrv_free_stmt($stmtITEM);
    }

    $suggestList = array_values($ListItems);


//🟡 3.GỘP CHUNG DATA ĐỂ TRẢ VỀ
//=================================================
    $data = [
        "ok"    => true,
        "US"    => $dataUS,    // Object thông tin User
        "PHIEU" => $dataPHIEU,  // Array danh sách phiếu
        "ITEM"  => $dataITEM,
        "ListItems"=>array_values($ListItems),
        "ListDelivery"=>array_values($ListDelivery),
    ];

    if (!headers_sent()) {header('Content-Type: application/json; charset=utf-8');}
            echo json_encode([
                "ok"        => true,
                "data"      => $data,    
            ], JSON_UNESCAPED_UNICODE);
            exit;

    sqlsrv_close($conn);
    ?>