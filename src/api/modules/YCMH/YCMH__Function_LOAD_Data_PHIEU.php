<?php
//⚪ LOAD DATA 
   //🟢 FORM CHUNG THAM CHIẾU CÁC MODULE CHỨC NĂNG
    //✅ ADDIN
    //✅ THAM CHIẾU CÁC MODULE CHỨC NĂNG
        require_once __DIR__ . '/../../../core/Database.php';               //Database

function LOAD_Data_PHIEU($UsID,$InstanceID) {

//🌟 OPEN CONNECT
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
    
//🟡1-KHAI BÁO BIẾN
    // COMPANENTS
    $data   = []; // BIẾN TRUYỀN THAM SỐ //🚩
    $dataRES = [];
    $MsgPhp = '';
    //Người dùng đang truy cập
    $UsViewer               =[];
    $APP_UsViewer_Status    = '';
       
    $HtmlAPP            ='';
    //Tình trạng hiện tại của phiếu
    $InstanceStatus     = '';
    $MaskStatus         = '';
    $DocumentNote       = '';
    $ChkPermission      = false;
    //Khác

       
//🟡2-XỬ lÝ INPUT DATA
    //KIỂM TRA Instance
    if ($InstanceID <=0) { //❌ THAM SỐ KHÔNG ĐÚNG
        //❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯❯
        $dataRES = [
            'ok' => false,
            'msg'   => "Dữ liệu không hợp lệ",
        ];
        return $dataRES;
    }

//🟡2-TRUY VẤN LẤY TOÀN BỘ BẢNG] Input[💡InstanceID]
    try {
        //🔶2.0 NGƯỜI ĐANG ĐĂNG NHẬP 
            $qr= "SELECT * FROM SYS_USERS WHERE IsDelete = 0 AND IsActive =1 AND UsID = ?";
            $stmtUs = sqlsrv_query($conn,$qr,[$UsID]);
            CHKQUERY($stmtUs,$conn,"QUERY TO US");
            $UsViewer = sqlsrv_fetch_array($stmtUs,SQLSRV_FETCH_ASSOC);


        //🔶2.1 LOAD TIẾN TRÌNH        ✅dataINS   🔎Refby: InstanceID
            $qr = "SELECT 
                    InstanceID,
                    DocumentID,
                    WfType,
                    CurrentStep,
                    ApprovalStatus,
                    ModifiedDate
                    
                FROM WF_INSTANCES 
                WHERE   Isdelete    = 0 
                    and InstanceID  = ?
            ";
            
            $wfInstances = sqlsrv_query($conn,$qr,[$InstanceID]);
            CHKQUERY($wfInstances,$conn,'QUERY INSTANCES');
            
            $dataINS = sqlsrv_fetch_array($wfInstances,SQLSRV_FETCH_ASSOC);    
            //📌TRƯỜNG HỢP KHÔNG CÓ DỮ LIỆU (null)  > FALSE 
            //📌NẾU LỖI TRONG QUÁ TRÌNH ĐỌC         > FALSE
            if (!$dataINS) {
                throw new Exception("Không tồn tại tiến trình có ID : " . $InstanceID, 505);
            }

            sqlsrv_free_stmt($wfInstances);
            $data['dataINS'] = $dataINS; //🚩

                      
        //🔶2.2 LOAD QUY TRÌNH DUYỆT   ✅$dataApp  🔎Refby: InstanceID
            $qr="SELECT * 
                FROM WF_APPROVALS 
                WHERE IsDelete = 0 AND InstanceID = ? 
                ORDER BY StepOrder, ApproverLevel DESC 
            ";

            $stmtAPP = sqlsrv_query($conn,$qr,[$InstanceID]);
            CHKQUERY($stmtAPP,$conn,"QUERY APP");

            $dataAPP = [];
            while ($row = sqlsrv_fetch_array($stmtAPP,SQLSRV_FETCH_ASSOC)) {
                $dataAPP[] = $row;
            }
            sqlsrv_free_stmt($stmtAPP);
            $data['dataAPP'] = $dataAPP; //🚩


        //🔶2.3 LOAD YCMH HEADER       ✅$dataMHHD 🔎Refby: $dataINS['DocumentID'] = mhhd_name
            $qr = "SELECT * 
                FROM ERP_MHHD 
                WHERE IsDelete = 0 AND mhhd_name = ?
            ";

            $stmtMHHD = sqlsrv_query($conn, $qr, [$dataINS['DocumentID']]);
            CHKQUERY($stmtMHHD,$conn,'QUERY MH HEADER');

            $dataMHHD = sqlsrv_fetch_array($stmtMHHD,SQLSRV_FETCH_ASSOC);
            if (!$dataMHHD) {
                throw new Exception("Không tìm thấy phiếu yêu cầu : ".$dataINS['DocumentID'], 505);
            }
            sqlsrv_free_stmt($stmtMHHD);            
            $data['dataMHHD'] = $dataMHHD; //🚩


        //🔶2.4 LOAD YCMH DETAIL       ✅$dataMHDT 🔎Refby: $dataMHHD['mhhd_id']
            $qr = "SELECT  
                        ROW_NUMBER () OVER (Partition By mhhd_id order by mhhd_id) as STT,
                        mhdt_item,
                        mhdt_qty,
                        mhdt_unit,
                        mhdt_description,
                        mhdt_purpose,
                        mhdt_delivery_time

                FROM ERP_MHDT 
                WHERE IsDelete = 0 and mhhd_id = ?
            ";

            $stmtMHDT = sqlsrv_query($conn, $qr, [$dataMHHD['mhhd_id']]);
            CHKQUERY($stmtMHDT,$conn,'QUERY MH DETAIL');
            //LẤY DỮ LIỆU
            $dataMHDT = [];
            while ($row = sqlsrv_fetch_array($stmtMHDT,SQLSRV_FETCH_ASSOC)) {
                $dataMHDT[]=$row;
            }
            //CHECK
            if (!$dataMHDT) {
                throw new Exception("Không tìm thấy dữ liệu chi tiết của phiếu : ".$dataMHHD['mhhd_name'], 505); 
            }

            sqlsrv_free_stmt($stmtMHDT);
            $data['dataMHDT'] = $dataMHDT; //🚩


        //🔶2.5 LOAD MHHD_ATTACK       ✅$dataATT  🔎Refby: $dataMHHD['mhhd_id']
            $qr="SELECT * 
                 FROM ERP_AttLink 
                 WHERE Isdelete = 0 AND AttType = 'YCMH' AND AttTypeCode =?
            ";

            $stmtATT = sqlsrv_query($conn,$qr,[$dataMHHD['mhhd_id']]);
            CHKQUERY($stmtATT,$conn,"QUERY ATTACK");
            //LẤY DỮ LIỆU
            $dataATT = [];
            $msgATT = '';
            while ($row = sqlsrv_fetch_array($stmtATT, SQLSRV_FETCH_ASSOC)) {
                $dataATT[]=$row;
            }

            if (!$dataATT) {
                $msgATT = '<p style="color:#e92626; font-style:italic ">Không có file đính kèm / No file acttack </p>';
            }

        //🔶2.6 CLOSE CONNECTION
            sqlsrv_close($conn);
            

    } catch (Exception $e) {
        $msg =  $e->getMessage() ? $e->getMessage() : " Err : 2-TRUY VẤN LẤY TOÀN BỘ BẢNG";
        //🔶 REPSION ERROR TO CLIENT
            if (!headers_sent()) {header('Content-Type: application/json; charset=utf-8');}
            http_response_code($e->getCode());
            echo json_encode([
                "ok"        => false, 
                "title"     => "TẠO THÔNG TIN THẤT BẠI",
                "msg"       => $msg,  

            ], JSON_UNESCAPED_UNICODE);

        //🔶 SEND_LOG
            CATCHLOG($e,$msg);
            sqlsrv_close($conn);
            exit;
    }
        
//🟡3-BUID DATA 
    try {
   
        //🔶3.1 INSTANCES
            $INSTANCE_ID  = $dataINS['InstanceID'] ?? '';
            $DOCUMENT_ID  = $dataINS['DocumentID'] ?? '';   // #####
            $CURRENT_STEP = $dataINS['CurrentStep'] ?? 0;
            $DOCUMENT_TYPE =$dataINS['WfType'] ?? 'WF_YCMH';

            $InstanceStatus = $dataINS['ApprovalStatus'];


            $MaskStatus .= $DOCUMENT_ID; //###
            $MaskStatus .= ' - ' . STRINGTIME($dataINS['ModifiedDate'],'Y-m-d H:i:s');

        //🔶3.2 APP
            $count = count($dataAPP); 

            //STYLE
                
                $StyleTruoc     = 'style="padding: 0px 3px; font-style: normal;     color: #2c2fff;"';
                $StyleHientai   = 'style="padding: 0px 3px; font-weight: normal;    color: #070707;"';
                $StyleSau       = 'style="padding: 0px 3px; font-style: italic;     color: #5e5e5e;"';
                $StyleHuy       = 'style="padding: 0px 3px; font-weight: normal;    color: #ff0000;"';

                $tdStyle   = '';
                $tdClass = 'class="appcol"';
                $StatusRepsion  = '';

                
            //LẶP TỪNG DÒNG TRONG MẢNG
            for ($i = 0; $i < $count; $i++) {
                $row = $dataAPP[$i];
                
                //FILTER theo ApprovalStatus
                if (strtolower($row['ApprovalStatus']) === 'created'|| strtolower($row['ApprovalStatus']) === 'approved' ) { 
                    $tdStyle   = $StyleTruoc;
                    $StatusRepsion  = '✓ ' . $row['ApprovalStatus'];
            
                } elseif (strtolower($row['ApprovalStatus']) === 'processing') { //processing
                    $tdStyle   = $StyleHientai;
                    $StatusRepsion  = '⏳ ' . $row['ApprovalStatus'];

                } elseif (strtolower($row['ApprovalStatus']) === 'rejected') {  //rejected
                    $tdStyle   = $StyleHuy;
                    $StatusRepsion  = '❌ ' . $row['ApprovalStatus'];

                } else {    //Waiting
                    $tdStyle   = $StyleSau;
                    $StatusRepsion  = $row['ApprovalStatus'];
 
                }

                //BODY HTML
                    $HtmlAPP .= "<tr>";
                        $HtmlAPP .= "<td {$tdClass} {$tdStyle}>" . htmlspecialchars($row['StepOrder'] + 1   ?? '') . "</td>";
                        $HtmlAPP .= "<td            {$tdStyle}>" . htmlspecialchars($row['ApproverName']    ?? '') . "</td>";
                        $HtmlAPP .= "<td {$tdClass} {$tdStyle}>" . htmlspecialchars($row['MemberCode']      ?? '') . "</td>";
                        $HtmlAPP .= "<td            {$tdStyle}>" . htmlspecialchars($row['ApproverPos']     ?? '') . "</td>";
                        $HtmlAPP .= "<td            {$tdStyle}>" . htmlspecialchars($row['ApprovalType']    ?? '') . "</td>";
                        $HtmlAPP .= "<td            {$tdStyle}>" . htmlspecialchars($StatusRepsion)                . "</td>";
                        $HtmlAPP .= "<td            {$tdStyle}>" . htmlspecialchars(STRINGTIME($row['ActionTime'])). "</td>";
                        $HtmlAPP .= "<td            {$tdStyle}>" . htmlspecialchars($row['ApproverComment'] ?? '') . "</td>";
                    $HtmlAPP .= "</tr>";    

                //Trạng thái & Quyền hạn người đang xem   
                if ($UsViewer['UsMemberCode'] === $row['MemberCode']) {
                    $APP_UsViewer_Status = $row['ApprovalStatus'];
                    $ChkPermission       = true;
                }
            }
        
        //🔶3.3 MHHD
            $DocumentNote = $dataMHHD['mhhd_note'] ?? '';

        //🔶3.4 MHDT
        
            $HtmlMHDT = "";

            foreach ($dataMHDT as $index => $row) {
                $tdStyleNormal = 'style="line-height: 1.2;"';
                $tdStyleTop    = 'style="line-height: 1.2; vertical-align: top;"';

                $HtmlMHDT .= "<tr>";
                $HtmlMHDT .= "<td " . $tdStyleNormal . " align='center'>" . htmlspecialchars($row['STT']                ?? '') . "</td>";
                $HtmlMHDT .= "<td " . $tdStyleTop . ">"                  . htmlspecialchars($row['mhdt_item']          ?? '') . "</td>";
                $HtmlMHDT .= "<td " . $tdStyleNormal . " align='center'>" . htmlspecialchars($row['mhdt_qty']           ?? '') . "</td>";
                $HtmlMHDT .= "<td " . $tdStyleNormal . " align='center'>" . htmlspecialchars($row['mhdt_unit']          ?? '') . "</td>";
                $HtmlMHDT .= "<td " . $tdStyleTop . ">"                  . htmlspecialchars($row['mhdt_description']   ?? '') . "</td>";
                $HtmlMHDT .= "<td " . $tdStyleTop . ">"                  . htmlspecialchars($row['mhdt_purpose']       ?? '') . "</td>";
                $HtmlMHDT .= "<td " . $tdStyleNormal . " align='center'>" . htmlspecialchars($row['mhdt_delivery_time'] ?? '') . "</td>";
                $HtmlMHDT .= "</tr>";
            }

        //🔶3.5 NGƯỜI KHỞI TẠO (INFOMATION)
            $CreateTime = ($dataMHHD['CreateTime'] instanceof DateTime) ? $dataMHHD['CreateTime']->format('Y-m-d') : $dataMHHD['CreateTime'];
            
        //🔶3.5 ATT 
            //❄️FUNCTION QUY ĐỔI DUNG LƯỢNG
            function formatFileSize($bytes) {
                if (!$bytes || $bytes == 0) return '0 KB';
                $k = 1024;
                $sizes = ['Bytes', 'KB', 'MB', 'GB'];
                $i = floor(log($bytes) / log($k));
                return round($bytes / pow($k, $i), 1) . ' ' . $sizes[$i];
            }
            $count = count($dataATT);
            $HtmlATT ="";
            for ($i = 0; $i < $count; $i++) {
                $row = $dataATT[$i];
                // Xác định loại file để đổi màu NỀN (Đỏ cho PDF, Xanh lá cho Excel)
                $fileName = $row['AttFileNameOrg'];
                $fileSize = formatFileSize($row['FileSize']); //QUY ĐỔI DUNG LƯỢNG
                $fileId   = $row['AttID'];
                $fileSec  = $row['AttSecCode'];
                $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $isExcel = ($ext === 'xlsx' || $ext === 'xls');
                $badgeColor = $isExcel ? '#4CAF50' : '#E53935';
                $badgeText  = $isExcel ? 'Excel' : 'Pdf';
                

                $HtmlATT .= '
                    <tr>
                        <td style="width: 32px; vertical-align: middle; padding: 0; border: none;">
                            <span style="display: inline-block; background-color: ' . $badgeColor . '; color: #ffffff; font-size: 9px; font-weight: bold; padding: 7px 4px; border-radius: 2px; text-align: center; line-height: 1;">
                                ' . $badgeText . '
                            </span>
                        </td>
                        
                        <td style="vertical-align: middle; padding: 0 10px 0 0px; border: none; text-align: left;">
                            <a href="../../../../downloadAttach?Id=' . $fileId . '&amp;Type=YCMH&amp;Sec=' . $fileSec . '" target="_blank" title="' . htmlspecialchars($fileName) . '" style="font-size: 10px; font-weight: 700; color: #0732f2; text-decoration: none;">
                                ' . htmlspecialchars($fileName) . '
                            </a>

                            <p style="font-size: 9px; font-style: italic; font-weight: 500; color: #575757; margin: 0;">
                                ' . htmlspecialchars($fileSize) . '
                            </p>
                        </td>
                    </tr>
                
                ';
            }

            if (!$HtmlATT) {
                $HtmlATT = 'Không có file đính kèm';
            }

       
    } catch (Exception $e) {
        $msg =  $e->getMessage() ? $e->getMessage() : " Err : 3-XÂY DỰNG DỮ LIÊU";
        //🔶 REPSION ERROR TO CLIENT
            if (!headers_sent()) {header('Content-Type: application/json; charset=utf-8');}
            http_response_code($e->getCode());
            echo json_encode([
                "ok"        => false, 
                "title"     => "TẠO THÔNG TIN THẤT BẠI",
                "msg"       => $msg,  
            ], JSON_UNESCAPED_UNICODE);
        //🔶 SEND_LOG & THOÁT
            CATCHLOG($e,$msg);
            exit;
    } 

//🟨TẠO TEM TRÊN SỐ PHIẾU
    if ($InstanceStatus === 'Approved') {
        $BoxStatus = '
            <span style="position: absolute; top: -10px; left: -95px; border: 2px dashed #0aad2d7f; color: #0aad2d7f; font-size: 13px; font-weight: 700; padding: 5px 10px; text-transform: uppercase; white-space: nowrap; transform: rotate(-8deg);">
                <nobr> &#10003; ĐÃ HOÀN THÀNH /APPROVED</nobr>
            </span>
        ';
    } elseif ($InstanceStatus === 'Rejected') {
        $BoxStatus = '
            <span style="position: absolute; top: -10px; left: -60px; border: 2px dashed #ff1900b1; color: #ff1900b1; font-size: 13px; font-weight: 700; padding: 5px 10px; text-transform: uppercase; white-space: nowrap; transform: rotate(-8deg);">
                <nobr> &#10005; ĐÃ HỦY / REJECTED </nobr>
            </span>
        ';
    }else {
        $BoxStatus = '
            <span style="position: absolute; top: -10px; left: -60px; border: 2px dashed #50505075; color: #50505075; font-size: 13px; font-weight: 700; padding: 5px 10px; text-transform: uppercase; white-space: nowrap; transform: rotate(-8deg);">
                <nobr>  BẢN NHÁP / DRAFT  </nobr>
            </span>
        ';
    }


    $so_phieu = '
        <div style="position: relative; display: inline-block;">
            <span style="font-weight: bold; font-size: 13px;">' . $DOCUMENT_ID . '</span> '. $BoxStatus . ' 
        </div>';



//🟨PHẢN HỒI
    $dataRES = [
        "ok"            => true,            //    
        "SO_PHIEU"      => $so_phieu,    //6✔️
        "InstanceID"    => $INSTANCE_ID,    //
        "NGAY_TAO"      => $CreateTime,                         //1✔️
        "NGUOI_TAO"     => $dataMHHD['CreateBy'],               //2✔️
        "MA_NV"         => $dataMHHD['Mem_code'],               //3✔️
        "BO_PHAN"       => $dataMHHD['mhhd_department'],        //4✔️
        "DELIVERY"      => $dataMHHD['mhhd_delivery_place'],    //5 ✔️
        "GHI_CHU"       => $DocumentNote,                       //7✔️
        "APPROVAL_ROWS" => $HtmlAPP,                            //10✔️
        "TABLE_DETAIL_ROWS" => $HtmlMHDT,                       //8✔️
        "ATT_FILE"          => $HtmlATT,                        //9✔️
        "InstanceStatus"         => $InstanceStatus,        //⚠️Trạng thái phiếu
        "APP_UsViewer_Status"    => $APP_UsViewer_Status,   //⚠️Trạng thái người đang xem trong tiến trình
        "MaskStatus"             => $MaskStatus,            //⚠️TAM THỜI CHƯA DÙNG TỚI
        "ChkPermission"          => $ChkPermission,         //✔️Kiểm quyền hạn người duyệt    
        "so_phieu_number"      => $DOCUMENT_ID
    ];
    return $dataRES;
}
