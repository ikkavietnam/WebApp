<?php
//🟢 FORM CHUNG THAM CHIẾU CÁC MODULE CHỨC NĂNG
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
        // Định dạng đối tượng đó ra các chuỗi khác nhau theo ý muốn (Dùng dấu mũi tên -> format)
        $CurrentTime_VN   = $DATE->format('d/m/Y H:i:s'); // Kết quả: 02/07/2026 14:30:00
        $CurrentTime_SQL  = $DATE->format('Y-m-d H:i:s'); // Kết quả: 2026-07-02 14:30:00
        $CurrentTime_Date = $DATE->format('d/m/Y');       // Kết quả: 02/07/2026

    //✅ INPUT
        $dataIP = json_decode(file_get_contents("php://input"), true) ?? [];


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

// 3. TRUY VẤN DATA
/* 
    const nguoikhoitao  =dataForTable.nguoikhoitao || ""; 
    const mhhd_name     =dataForTable.mhhd_name || ""🚩;
    const dataDT        =dataForTable.dataDT || "";
    const mhhd_note     =dataForTable.mhhd_note || "";
    const dataApp       =dataForTable.dataApp || "";
*/
//🟡 2. LẤY THÔNG TIN BẢNG ERP_MHHD
    $mhhd_name  = $dataIP['mhhd_name'];
    $mhhd_id    = $dataIP['mhhd_id'];

    $dataout = [];
    $dataout['mhhd_name']   = $mhhd_name;   //🚩

    $qr = "
        SELECT * FROM ERP_MHHD WHERE mhhd_name = ? and IsDelete = 0
    ";

    $stmt = sqlsrv_query($conn, $qr,[$mhhd_name]);

    CHKQUERY($stmt,$conn,"QUERY TO YCMH HEADER");

    $dataHD = sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);

    if (!$dataHD) {
        if (!headers_sent()) {header('Content-Type: application/json; charset=utf-8');}
        echo json_encode([
            "ok"        => false,
            "title"     => 'Data null',
            "msg"       => "KHÔNG TỒN TẠI PHIẾU : " . $mhhd_name,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    
    $nguoikhoitao   = $dataHD['CreateBy'];    //🚩
    $mhhd_note      = $dataHD['mhhd_note'];       //🚩

    
        $CreateTime = ($dataHD['CreateTime'] instanceof DateTime) ? $dataHD['CreateTime']->format('Y-m-d') : $dataHD['CreateTime'];
        $StyleSpan = '<span style="font-style: italic;color: rgb(227, 75, 75)">';
        $ADDCLASS ='<td class="nguoikhoitaolbl">';


        $nguoikhoitao ="";
        $nguoikhoitao .="<tr>";
        $nguoikhoitao .="<td>" . "NGÀY TẠO PHIẾU /" . $StyleSpan . "Date </span>" . "</td>";
        $nguoikhoitao .=$ADDCLASS . htmlspecialchars($CreateTime ?? '') . "</td>";
        $nguoikhoitao .="</tr>";
        $nguoikhoitao .="<tr>";
        $nguoikhoitao .="<td>" . "NGƯỜI LÀM PHIẾU /" . $StyleSpan . "Created by</span>" . "</td>";
        $nguoikhoitao .=$ADDCLASS . htmlspecialchars($dataHD['CreateBy'] ?? '') . "</td>";
        $nguoikhoitao .="</tr>";
        $nguoikhoitao .="<tr>";
        $nguoikhoitao .="<td>" . "MÃ NHÂN VIÊN /" . $StyleSpan . "Member code</span>" . "</td>";
        $nguoikhoitao .=$ADDCLASS . htmlspecialchars($dataHD['Mem_code'] ?? '') . "</td>";
        $nguoikhoitao .="</tr>";
        $nguoikhoitao .="<tr>";
        $nguoikhoitao .="<td>" . "BỘ PHẬN /" . $StyleSpan . "Department</span>" . "</td>";
        $nguoikhoitao .=$ADDCLASS . htmlspecialchars($dataHD['mhhd_department'] ?? '') . "</td>";
        $nguoikhoitao .="</tr>";
        $nguoikhoitao .="<tr>";
        $nguoikhoitao .="<td>" . "ĐIỂM GIAO HÀNG /" . $StyleSpan . "Delivery</span>" . "</td>";
        $nguoikhoitao .=$ADDCLASS . htmlspecialchars($dataHD['mhhd_delivery_place'] ?? '') . "</td>";
        $nguoikhoitao .="</tr>";

        $Note = ($dataHD['mhhd_note']===null ? $dataHD['mhhd_note'] : "");

//🟡 3. LẤY THÔNG TIN BẢNG ERP_MHDT
    $QrDt = "
                SELECT  ROW_NUMBER () OVER (Partition By mhhd_id order by mhhd_id) as STT,
                
                        mhdt_item,
                        mhdt_qty,
                        mhdt_unit,
                        mhdt_description,
                        mhdt_purpose,
                        mhdt_delivery_time

                FROM ERP_MHDT 
                WHERE IsDelete = 0 
                    and mhhd_id = (SELECT mhhd_id From ERP_MHHD WHERE IsDelete = 0 and mhhd_name = ?)
            ";

    $stmtDT = sqlsrv_query($conn,$QrDt,[$mhhd_name]);

    $dataDT ="";
    while ($row = sqlsrv_fetch_array($stmtDT, SQLSRV_FETCH_ASSOC)) {
        $deliveryTime = ($row['mhdt_delivery_time'] instanceof DateTime) ? $row['mhdt_delivery_time']->format('Y-m-d') : $row['mhdt_delivery_time'];

        // Ép border xám nhẹ bằng inline CSS và khống chế padding mượt mà
        $tdStyle = 'style="padding: 4px 5px; font-size: 11px; border: 1px solid #c2c0c0; font-family: \'Segoe UI\', Arial, sans-serif; line-height: 1.2;"';

        $dataDT .= "<tr>";
        $dataDT .= "<td " . $tdStyle . " align='center'>" . htmlspecialchars($row['STT'] ?? '') . "</td>";
        $dataDT .= "<td " . $tdStyle . ">" . htmlspecialchars($row['mhdt_item'] ?? '') . "</td>";
        $dataDT .= "<td " . $tdStyle . " align='center'>" . htmlspecialchars($row['mhdt_qty'] ?? '') . "</td>";
        $dataDT .= "<td " . $tdStyle . " align='center'>" . htmlspecialchars($row['mhdt_unit'] ?? '') . "</td>";
        $dataDT .= "<td " . $tdStyle . ">" . htmlspecialchars($row['mhdt_description'] ?? '') . "</td>";
        $dataDT .= "<td " . $tdStyle . ">" . htmlspecialchars($row['mhdt_purpose'] ?? '') . "</td>";
        $dataDT .= "<td " . $tdStyle . " align='center'>" . htmlspecialchars($deliveryTime ?? '') . "</td>";
        $dataDT .= "</tr>";
    }


    if ($stmtDT===null) {
        echo json_encode([
            "oke" => false,
            "msg"   => " Không tìm thấy dữ liệu chi tiết của mã phiếu " . $mhhd_name
        ]);
        exit;
    }

//⚪ LẤY DỮ LIỆU INSTANCE
    $QrINS = "
        SELECT * FROM WF_INSTANCES WHERE DocumentID = ? and IsDelete = 0
    ";

    $stmtINS = sqlsrv_query($conn,$QrINS,[$mhhd_name]);
    $dataINS = sqlsrv_fetch_array($stmtINS,SQLSRV_FETCH_ASSOC);
    CHKQUERY($dataINS,$conn,"QUERY TO INSTANCES");
    if ($dataINS === null) {
        PROLOG('dataINS : '.$mhhd_name);
    } else {
        $InstanceID = $dataINS['InstanceID'];
    }
    sqlsrv_free_stmt($stmtINS);

    


    //⚪ LẤY DỮ LIỆU FILE ATTACH
        $QrAttach = "
            SELECT AttID, AttFileNameOrg, AttFileNameLogic, FileExtension, FileSize, AttLink ,AttType ,AttSecCode
            FROM ERP_AttLink 
            WHERE IsDelete = 0 AND AttType = 'YCMH' AND AttTypeCode =?
        ";
        $stmtATT = sqlsrv_query($conn, $QrAttach,[$dataHD['mhhd_id']]);
        $dataATT = [];
        while ($row = sqlsrv_fetch_array($stmtATT,SQLSRV_FETCH_ASSOC)) {
            $dataATT[] = [
                "AttID"             => &$row['AttID'],
                "AttFileNameOrg"    => &$row['AttFileNameOrg'],
                "AttFileNameLogic"  => &$row['AttFileNameLogic'],
                "FileExtension"     => &$row['FileExtension'],
                "FileSize"          => &$row['FileSize'],
                "AttLink"           => &$row['AttLink'],
                "AttType"           => &$row['AttType'],
                "AttSecCode"        => &$row['AttSecCode']
            ];
        }

    /* $ModifiedDate = $dataINS['DocumentID'];
    $ModifiedDate .= "  Approved : ";
    $ModifiedDate .= ($dataINS['ModifiedDate'] instanceof DateTime) ? $dataINS['ModifiedDate']->format('Y-m-d H:i:s') : ($dataINS['ModifiedDate'] ?? ''); */
    



    

    //⚪ LẤY DỮ LIỆU TABLE APPROVAL
    if (isset($InstanceID)&& $InstanceID !== null) {
        $QrAPP = "
            SELECT * FROM WF_APPROVALS WHERE InstanceID = ? AND IsDelete = 0 ORDER BY StepOrder DESC
        ";

        $stmtApp = sqlsrv_query($conn,$QrAPP,[$InstanceID]);
        $trangthainguoiduyet ="";
        $dataApp= "";
        $maxStepOrder = 10;
        
        while ($row = sqlsrv_fetch_array($stmtApp,SQLSRV_FETCH_ASSOC)) {

            /* if ($Us['UsCode'] === $row['MemberCode'] && $maxStepOrder === 10)  {
                $maxStepOrder = $row['StepOrder'];
                $trangthainguoiduyet = $row['ApprovalStatus'];
            } */



            $ActionTime = (!empty($row['ActionTime']) && $row['ActionTime'] instanceof DateTime) 
                ? $row['ActionTime']->format('Y-m-d H:i') 
                : ($row['ActionTime'] ?? '');
            
           
            $tdClass = 'class="appcol"';
            if ($row['StepOrder'] > $dataINS['CurrentStep']) {
                $tdStyle = 'style="font-size: 11px;  color: #003ec2;"';
                $ApprovalStatus = '<span style="color:blue; font-weight: bold;"> ✓  </span>' . $row['ApprovalStatus'];
            } elseif ($row['StepOrder'] === $dataINS['CurrentStep']) {
                $tdStyle = 'style="font-size: 11px;font-style: italic;  color: rgb(227, 75, 75);"';
                $ApprovalStatus = '🔘  ' . $row['ApprovalStatus'];
            }
            else {
               $tdStyle = 'style="font-size: 11px; font-style: italic; color:rgb(202, 202, 202)4;"';
               $ApprovalStatus = '⏳ ' . $row['ApprovalStatus'];
            }

            $dataApp .= "<tr>";
                $dataApp .= "<td . $tdClass . $tdStyle >" . htmlspecialchars($row['StepOrder'] ??          '') . "</td>";
                $dataApp .= "<td            . $tdStyle >" . htmlspecialchars($row['ApproverName'] ??       '') . "</td>";
                $dataApp .= "<td . $tdClass . $tdStyle >" . htmlspecialchars($row['MemberCode'] ??         '') . "</td>";
                $dataApp .= "<td            . $tdStyle >" . htmlspecialchars($row['ApproverPos'] ??        '') . "</td>";
                $dataApp .= "<td            . $tdStyle >" . htmlspecialchars($row['ApprovalType'] ??       '') . "</td>";
                $dataApp .= "<td            . $tdStyle >" . ($ApprovalStatus ??     '') . "</td>";
                $dataApp .= "<td            . $tdStyle >" . htmlspecialchars($ActionTime ??                '') . "</td>";
                $dataApp .= "<td            . $tdStyle >" . htmlspecialchars($row['ApproverComment'] ??    '') . "</td>";
            $dataApp .= "</tr>";
        };

        sqlsrv_free_stmt($stmtApp);

        if ($dataApp===null) {
            echo json_encode([
                "ok" => false,
                "msg" => "Không tìm thấy cấu hình"
            ]);
            exit;
        }
    }




//🟡 FINISH
echo json_encode([
    "ok"    => true,
    'mhhd_name'     => $mhhd_name,
    "nguoikhoitao"  => $nguoikhoitao,
    "Note"     => $mhhd_note,

    "dataDT"        => $dataDT,
    "dataApp"       => isset($dataApp) ? $dataApp : '',

    "data"=>$dataout,
    
], JSON_UNESCAPED_UNICODE);
exit;