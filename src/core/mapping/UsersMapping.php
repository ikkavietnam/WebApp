<?php
// UserMapping.php - Lưu ở khu vực bảo mật của Server
class UserMapping {

// 1. Bản đồ ánh xạ từ DB gốc sang Key bảo mật (và ngược lại)
private static $map = [
/////////////////////////////////////////////////////////////////
'IsDelete' => 'us01',
'IsActive' => 'us02',
'UsID' => 'us03',
'UsName' => 'us04',
'UsDisplay' => 'us05',
'UsMemberCode' => 'us06',
'UsType' => 'us07',
'UsEmail' => 'us08',
'UsPhone' => 'us09',
'DeptID' => 'us10',
'PosID' => 'us11',
'PosName' => 'us12',
'PosLevel' => 'us13',
'DeptName' => 'us14',

/////////////////////////////////////////////////////////////////
];

// 2. Bản đồ dịch nhãn hiển thị UI
private static $labels = [
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
'us01' => 'Stt xóa',
'us02' => 'Stt hoạt động',
'us03' => 'ID',
'us04' => 'Tên đăng nhập',
'us05' => 'Tên hiển thị',
'us06' => 'Mã NV',
'us07' => 'Phân loại',
'us08' => 'Email',
'us09' => 'Phone',
'us10' => 'DeptID',
'us11' => 'PosID',
'us12' => 'Chức vụ',
'us13' => 'PosLevel',
'us14' => 'Bộ phận',
];
// 3. Bản đồ dịch nhãn hiển thị UI
private static $trangthai = [
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
'us01' => 'isReadOnly',
'us02' => 'isReadOnly',
'us03' => 'isReadOnly',
'us04' => 'isReadOnly',
'us05' => '',
'us06' => 'isReadOnly',
'us07' => 'isReadOnly',
'us08' => '',
'us09' => '',
'us10' => 'isReadOnly',
'us11' => 'isReadOnly',
'us12' => 'isReadOnly',
'us13' => 'isReadOnly',
'us14' => 'isReadOnly',


//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
];

//🟢🟢🟢🟢
public static function MappingToClient($_dbUser) {
    $cleanData  = []; 
    $statusData = []; 

    foreach ($_dbUser as $_dbKey => $_value) {    
        if (isset(self::$map[$_dbKey])) {       //kiểm tra tồn tại của tên từng cột có trong self::$map[$_dbKey] >>> kiểm tra IsDelete ... có trong &map không
            $_secureKey = self::$map[$_dbKey]; // tạo secureKey s01, us02, us03...

            if (isset(self::$labels[$_secureKey])) {    //kiểm tra tiếp có trong label không
                $_viLabel = self::$labels[$_secureKey]; // lấy giá trị tương ứng với secureKey
                
                // 1. Dữ liệu: Lấy KEY là mã bảo mật để JS làm ID/Name dễ dàng
                $cleanData[$_secureKey] = $_value;

                // 2. Cấu hình: Gom cả Nhãn Tiếng Việt và trạng thái ReadOnly vào đây
                $statusData[$_secureKey] = [
                    "label"      => $_viLabel,
                    "isReadOnly" => isset(self::$trangthai[$_secureKey]) ? self::$trangthai[$_secureKey] : ''
                ];
                
                continue;
            }
        }
        // Phòng hờ cột lạ không có trong map
        $cleanData[$_dbKey] = $_value;
        $statusData[$_dbKey] = ["label" => $_dbKey, "isReadOnly" => ''];
    }

    return [
        "user"   => $cleanData,
        "config" => $statusData
    ];
}//End


} // End UserMapping