<?php
//⚪ LOAD DATA 
    // REQUIRE
        require_once __DIR__ . '/../../../core/Authorization.php';
        require_once __DIR__ . '/YCMH__Function_LOAD_Data_PHIEU.php';
    // Gọi autoload của Composer
        require_once __DIR__ . '/../../../vendor/autoload.php';
        
        use Dompdf\Dompdf;
        use Dompdf\Options;

        // Cấu hình tùy chọn cho Dompdf (Hỗ trợ tiếng Việt tốt hơn)
            $options = new Options();
            $options->set("isRemoteEnabled", true);
            $options->set("defaultFont", "DejaVu Sans"); // Font hỗ trợ tiếng Việt chuẩn trong Dompdf
            $options->set('chroot', '/var/www/html');

        // 📌 CẤU HÌNH CHO PHÉP DOMPDF ĐỌC VÀ LƯU CACHE FONT TẠI THƯ MỤC TỰ TẠO
            $options->set('fontDir',    $_SERVER['DOCUMENT_ROOT'] . '/assets/fonts/font_cache');
            $options->set('fontCache',  $_SERVER['DOCUMENT_ROOT'] . '/assets/fonts/font_cache');
        // Khởi tạo Dompdf với cấu hình
            $dompdf = new Dompdf($options);

//⚪ KHỞI TẠO HOẶC LOAD SESSION 
    //✅ CHECK SESSION & HEADER
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    header('Content-Type: application/json; charset=utf-8'); 

    //✅LẤY ID (UsID) NGƯỜI DÙNG & KIỂM TRA QUYỀN HẠN & ĐĂNG NHẬP
        if (isset($_SESSION['user']) && isset($_SESSION['user']['UsID'])) {
            $UsID = (int)$_SESSION['user']['UsID'];
        }
    //✅Auth
        checkLevel(3);
    //✅INPUT    
        $InstanceID = (int)json_decode(file_get_contents("php://input"), true) ?? 0;

        

//🟢 LẤY DỮ LIỆU ĐỂ TẠO UI
    $dataRES = LOAD_Data_PHIEU($UsID,$InstanceID);

        $so_phieu       = $dataRES['SO_PHIEU'];           
        $ngay_tao       = $dataRES['NGAY_TAO'];            
        $nguoi_tao      = $dataRES['NGUOI_TAO'];           
        $ma_nv          = $dataRES['MA_NV'];               
        $bo_phan        = $dataRES['BO_PHAN'];             
        $delivery       = $dataRES['DELIVERY'];            
        $table_detail_rows = $dataRES['TABLE_DETAIL_ROWS'];
        $ghi_chu        = $dataRES['GHI_CHU'];              // Nội dung ghi chú
        $ATT_FILE       = $dataRES['ATT_FILE'];             // Nội dung hoặc thông báo đính kèm
        $approval_rows  = $dataRES['APPROVAL_ROWS'];        // Chuỗi HTML các thẻ <tr>...</tr> của quy trình duyệt
        $so_phieu_number = $dataRES['so_phieu_number'];

//🟢 LOAD CẤU HÌNH HTML
     $html = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/view/ycmh/YCMH_TemplateForm.html');

     
//🟢 FONT
    $font_path = $_SERVER['DOCUMENT_ROOT'] . '/assets/fonts'; // Đường dẫn tuyệt đối chuẩn trên server

//🟢 LOGO
    $logo_path = $_SERVER['DOCUMENT_ROOT'] . '/assets/img/ITV128x128.png';
    $logo_base64 = '';
    if (file_exists($logo_path)) {
        $logo_type = pathinfo($logo_path, PATHINFO_EXTENSION);
        $logo_data = file_get_contents($logo_path);
        $logo_base64 = 'data:image/' . $logo_type . ';base64,' . base64_encode($logo_data);
    }

//🟢 LOGO
    $html = str_replace(
        [   
            '{{LOGO_SRC}}',
            '{{FONT_PATH}}',
            '{{SO_PHIEU}}',
            '{{NGAY_TAO}}',
            '{{NGUOI_TAO}}',
            '{{MA_NV}}',
            '{{BO_PHAN}}',
            '{{DELIVERY}}',
            '{{TABLE_DETAIL_ROWS}}',
            '{{GHI_CHU}}',
            '{{ATT_FILE}}',
            '{{APPROVAL_ROWS}}'
        ],
        [   
            $logo_base64,   //✔️
            $font_path,     //✔️
            $so_phieu,            
            $ngay_tao,            
            $nguoi_tao,           
            $ma_nv,                
            $bo_phan,             
            $delivery,             
            $table_detail_rows,    // Chuỗi HTML các thẻ <tr>...</tr> của sản phẩm
            $ghi_chu,              // Nội dung ghi chú
            $ATT_FILE,        // Nội dung hoặc thông báo đính kèm
            $approval_rows         // Chuỗi HTML các thẻ <tr>...</tr> của quy trình duyệt
        ],
        $html
    );
//🟢 NẠP NỘI DUNG VÀO
    $dompdf->loadHtml($html);
    $dompdf->setPaper("A4", "portrait"); //Thiết lập khổ giấy (A4) và hướng giấy (portrait: dọc, landscape: ngang)

        
    //Tiến hành render HTML thành PDF
    $dompdf->render();

    $smb_ip     	= '192.168.110.232';
    $smb_user   	= 'itv_smb';
    $smb_pass  	    = 'Ikka@2026';	
    $mhhd_name      = 'PDF_TEST';

    
    // 1. Mã hóa thông tin đăng nhập và chuẩn bị đường dẫn SMB cho thư mục YCMH
    $encoded_user = urlencode($smb_user);
    $encoded_pass = urlencode($smb_pass);
    $clean_ip     = $smb_ip;

    // Thư mục đích trên SMB cho YCMH
    $pdf_upload_dir = "smb://{$encoded_user}:{$encoded_pass}@{$clean_ip}/Document/Data/YCMH/";


    // 2. Lấy đối tượng Canvas và Font Metrics
        $canvas = $dompdf->getCanvas();
        $font_metrics = $dompdf->getFontMetrics();
        $font = $font_metrics->get_font("Roboto Condensed", "bold");
        $size = 7;

    // Chuỗi hiển thị thực tế trên PDF (có chứa thẻ của Dompdf)
        $header_text = "YCMH - " . $so_phieu_number . " Page {PAGE_NUM} / {PAGE_COUNT}";

        // 📌 TẠO CHUỖI MẪU ĐỂ TÍNH ĐỘ RỘNG CHÍNH XÁC (Thay {PAGE_NUM} bằng số mẫu như "9 / 9")
        $sample_text = "YCMH - " . $so_phieu_number . " Page 9 / 9";
        $text_width = $font_metrics->getTextWidth($sample_text, $font, $size);

        // Lấy tổng chiều rộng khổ giấy A4
        $page_width = $canvas->get_width();

        // Khoảng cách lề phải thực tế (Ví dụ: 30pt ~ 10mm, bạn có thể tăng giảm tùy ý)
        $right_margin = 10; 

        // Tính toán tọa độ X chuẩn xác để căn phải
        $x = $page_width - $text_width - $right_margin;
        $y = 10; // Khoảng cách từ mép trên xuống

        // Vẽ chữ lên mọi trang với chuỗi thật
        $canvas->page_text($x, $y, $header_text, $font, $size, array(0.1, 0.1, 0.1));


    // 2. Lấy nội dung nhị phân của file PDF sau khi render
    $output = $dompdf->output();

    // 3. Đặt tên file PDF theo logic của bạn
    $pdf_file_name = "YCMH_" . str_replace('-', '_', $mhhd_name) . ".pdf";
    $target_pdf_path = $pdf_upload_dir . $pdf_file_name;

    // 4. Ghi file trực tiếp vào thư mục SMB thông qua hàm file_put_contents
    if (file_put_contents($target_pdf_path, $output) !== false) {
        echo "Lưu file PDF thành công trên SMB tại: " . $target_pdf_path;
        
        // Đường dẫn UNC (nếu bạn cần lưu vào Database để hiển thị dạng \\ITV-SV\...)
        $pdf_link = "\\\\ITV-SV\\Document\\Data\\YCMH\\" . $pdf_file_name;
    } else {
        echo "Lỗi: Không thể ghi file PDF qua giao thức SMB!";
    }


//🟢 RESPONSE CLIENT  
echo json_encode ([
    'ok' => true,
    'msg' => '',
    'dataRES' => $dataRES,
])
?>