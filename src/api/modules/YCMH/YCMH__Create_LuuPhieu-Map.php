<?php

│   ├──
│   ├── └──
 
// TOÀN BỘ THÔNG TIN NGƯỜI DÙNG
    $Users

//người dùng hiện tại
    $nguoidung

 //TẠO mhhd_name mới
    $mhhd_name

// INPUT HEADER
    $NGAYLAMPHIEU
    $NHANVIEN       
    $MANHANVIEN     
    $MABOPHAN       
    $DIEMGIAO       
    $GHICHU        

// LOAD CONFIG
    $WfConfig
// INSER MHHD 
    $mhhd_id // Mới





/*


🔸 🔹 
YCMH_LUUDATA
│
├── [🟡XỬ lÝ INPUT DATA]
│   ├──(1-🔸HEADER) << $_POST['Header']
│   │   ├── 1.📑 NGAYLAMPHIEU
│   │   ├── 2.📑 NHANVIEN
│   │   ├── 3.📑 MANHANVIEN
│   │   ├── 4.📑 MABOPHAN
│   │   └── 5.📑 DIEMGIAO        
│   │   
│   ├──(2-🔸DETAIL) << $_POST['Details']
│   │   └──  .📚 $details
│   │   
│   ├──(3-🔸COMMENT)
│   │   └──  .📑 $GHICHU
│   │
│   └──(4-🔸AttachFile) << $_FILES['attached_files']
│       └──  .📂 $files
│
├── [🟡LẤY THÔNG TIN CHUNG]
│   ├── 1-🔸 TOÀN BỘ USER & NGƯỜI DÙNG HIỆN TẠI
│   ├── 2-🔸 TẠO SỐ PHIẾU REQUEST NO                >  (mhhd_name)
│   ├── 3-🔸 LẤY DỮ LIỆU CONFIG WF                  <   [$MABOPHAN ]
│
├── [🟡CHECKING]
│   ├── 1-🔸Người dùng có khớp với session không ?
│   ├── 2-🔸Người dùng có nằm trong danh sách WF không ?
│   └── 3-🔸
│
├── [🟡INSERT DATA]
│   ├── 1-🔸[OPEN TRANSACTION]🔐
│   ├── 2-🔸INSET DATA
│   │   ├──
│   │   ├── 1. INSERT TABLE TO HEADER > return  $mhhd_id = $row['mhhd_id'];
│   │   ├── 2. INSERT TABLE TO DETAIL
│   │   ├── 3. INSERT TABLE TO ATTACH FILE
│   │   ├── 4. INSERT TABLE INSTANCES (Tạo tiến trình mới) Trả về 📑[InstanceID]
│   │   ├── 5. INSERT TABLE APPROVALS (Bảng quản lý tiến trình) 
│   │   └── 6. 
│   │ 
│   │ 
│   └── 3-🔸COMMIT TRANSACTION🔑
│
└── [🟡RESPONE TO CLIENT [⭕KẾT QUẢ ĐÚNG]
        📑InstanceID


[❌KẾT QUẢ LỖI]
    function RESPONSE_CLIENT($code,$msg,$title = 'RESPONSE FROM SERVER')


// ⚪⚪⚪ HÀM PHẢN HỒI KẾT QUẢ KHI GẶP LỖI CHỦ ĐỘNG
// =========================================================================
function RESPONSE_CLIENT($code, $msg ,$title = 'RESPONSE FROM SERVER') {
    http_response_code($code);
    if (!headers_sent()) {header('Content-Type: application/json; charset=utf-8');}
    echo json_encode([
        "ok"        => false,
        "title"     => $title,
        "msg"       => $msg,
        "ErrCode"   => $code,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
*/

// 🟢 1. KHAI BÁO THÔNG TIN MÁY CHỦ ITV-SV
$smb_ip   = "192.168.110.232"; 
$smb_user = "Administrator"; 
$smb_pass = "Ikka@145266219"; 

// 🟢 2. KIỂM TRA & XỬ LÝ ATTACH FILE
$SecCode = GetSecCode(10);

try {
    // Kiểm tra cấu trúc mảng file hợp lệ từ Client
    if (
        isset($_FILES['attached_files']) && 
        is_array($_FILES['attached_files']['name']) && 
        !empty($_FILES['attached_files']['name'][0])
    ) {
        $files = $_FILES['attached_files'];
        
        // Cấu hình URL Stream SMB
        $encoded_user = urlencode($smb_user);
        $encoded_pass = urlencode($smb_pass);
        $clean_ip     = str_replace(' ', '', $smb_ip);
        $upload_dir   = "smb://{$encoded_user}:{$encoded_pass}@{$clean_ip}/Document/Data/Attach/";

        $MsgPhp_attach = "";
        $uploadedCount = 0;
        $fileIndex     = 1; // Biến đếm số thứ tự file thực tế lưu thành công

        for ($i = 0; $i < count($files['name']); $i++) {
            
            // Chỉ xử lý các file không bị lỗi upload từ máy khách
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                
                $att_file_name_org = $files['name'][$i];
                $tmp_path          = $files['tmp_name'][$i];

                // Lấy đuôi file và dung lượng
                $ext           = pathinfo($att_file_name_org, PATHINFO_EXTENSION);
                $FileExtension = !empty($ext) ? strtolower($ext) : 'unknown';
                $FileSize      = isset($files['size'][$i]) ? intval($files['size'][$i]) : 0;

                // Tên file logic theo STT tăng dần chuẩn xác
                $att_file_name_logic = "YCMH_" . str_replace('-', '_', $mhhd_name) . "_ATTACH" . sprintf("%02d", $fileIndex) . "." . $FileExtension;
                $target_file         = $upload_dir . $att_file_name_logic;
                $att_link            = "\\\\ITV-SV\\Document\\Data\\Attach\\" . $att_file_name_logic;

                // Dùng move_uploaded_file để chuyển file tạm sang ổ đĩa SMB
                if (move_uploaded_file($tmp_path, $target_file)) {

                    // Thêm dữ liệu vào bảng ERP_AttLink
                    $QrIns_AttLink = "
                        INSERT INTO ERP_AttLink (
                            isDelete, CreateBy, CreateTime, AttType, 
                            AttTypeCode, AttFileNameOrg, AttFileNameLogic, 
                            FileExtension, FileSize, AttLink, AttSecCode
                        ) VALUES (0, ?, ?, 'YCMH', ?, ?, ?, ?, ?, ?, ?)
                    ";

                    $params_att = [
                        $MANHANVIEN,
                        $CurrentTime_SQL,
                        $mhhd_id, 
                        $att_file_name_org, 
                        $att_file_name_logic,
                        $FileExtension, 
                        $FileSize,      
                        $att_link,
                        $SecCode
                    ];

                    $stmt_att = sqlsrv_query($conn, $QrIns_AttLink, $params_att);

                    if ($stmt_att === false) {
                        $errors  = sqlsrv_errors();
                        $err_msg = isset($errors[0]['message']) ? $errors[0]['message'] : 'Unknown SQL Error';
                        throw new Exception("File đã upload nhưng không thể lưu vào CSDL. Chi tiết: " . $err_msg);
                    }
                    
                    sqlsrv_free_stmt($stmt_att);

                    // Ghi nhận thành công
                    $MsgPhp_attach .= "<br> - " . $att_file_name_org;
                    $uploadedCount++;
                    $fileIndex++; // Tăng STT cho file tiếp theo

                } else {
                    $last_error = error_get_last();
                    $err_detail = isset($last_error['message']) ? $last_error['message'] : 'Không thể kết nối hoặc ghi vào đường dẫn SMB.';
                    throw new Exception("Lỗi: Không thể copy file đính kèm ({$att_file_name_org}) sang máy chủ ITV-SV. Chi tiết: " . $err_detail);
                }
            }
        }

        // Kiểm tra sau vòng lặp xem có file nào được lưu thực sự không
        if ($uploadedCount > 0) {
            $MsgPhp .= "<br>✅ Lưu File đính kèm thành công: " . $MsgPhp_attach;
        } else {
            $MsgPhp .= "<br> File đính kèm: Không có file hợp lệ nào được gửi lên!";
        }

    } else {
        $MsgPhp .= "<br> File đính kèm: Không có !";
    }

} catch (Throwable $e) {
    // 🟢 XỬ LÝ LỖI: Ghi log hệ thống và ném Exception ra tầng trên
    if (function_exists('LOG_TRANSACTION_ERROR')) {
        LOG_TRANSACTION_ERROR("Lỗi xử lý File đính kèm", $e);
    }
    
    // Đẩy lỗi ra ngoài khối try-catch chính của API để Rollback Transaction nếu có
    throw $e; 
}