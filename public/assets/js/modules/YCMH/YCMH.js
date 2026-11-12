
import {  TableBase } from "../../core/tableERP/tableERPBase.js";
import {  
          debounce,
          removeVietnameseTones,
          isEmpty,
          ErpAlert 
        
        } from '../../core/Utils/helpers.js';

//LOAD FUNCTION
import { MakeResizable } from "../../core/layout/RESIZABLE.js";
import { getCoreTable} from '../../core/Utils/helpers.js';

//NEW FETCH API 
import {apiFetch, apiFetchForm}     from "../../core/request/fetch_Api.js";
import {showApiError,ConverErrToDataSwal} from "../../core/request/fetch_ApiShowError.js";



/*🚩CONST PULIC */
    let attachedFiles = []; 

/*
⭕ XÂY DỰNG BẢNG BÊN TRÁI
📃 ====================================================================================
[ YCMH_HEADER_RENDER (Main Orchestrator) ]
    ├── 1. Render HTML Frame --------> InnerHTML vào #APP & Kích hoạt MakeResizable
    ├── 2. Init Table ---------------> Khởi tạo TableBase & Cấu hình Formatter (Highlight, Status)
    ├── 3. Init State Management ----> Khai báo trạng thái Lọc (Filter State: search, depts, status, sort)
    ├── 4. Bind UI Events ------------> Đăng ký DOM Events (Input Search, Dropdown Dept, Select Status, Sort)
    └── 5. Load Data & Render -------> Gọi API -> Đẩy dữ liệu vào Table -> Áp dụng Filter Master

📃 ====================================================================================
*/


/* HÀM TẠO PHIẾU YÊU CẦU MỚI (CLICK MENU)
🟡 CREATE NEW
🆘 ======================================================================================================================================================================== YCMH_CREATE_RENDER */
export async function YCMH_CREATE_RENDER() {
    const app = document.getElementById("APP");
    app.innerHTML=``;

    //🟢1-KHAI BÁO CÁC BIẾN
        let tableRows = "";
        let tabITEM ="";
        let RES;
    //🟢2-MAPPING LABEL CHO BẢNG THÔNG TIN
    const mappingTable = {
        ngaylamphieu: "NGÀY TẠO PHIẾU/ Date",
        NhanVien:     "NGƯỜI LÀM PHIẾU / Ordered by",      
        MaNhanVien:   "MÃ NHÂN VIÊN / Member code",         
        MaBoPhan:     "BỘ PHẬN / Requested department",    
        diemgiao:     "ĐIỂM GIAO HÀNG / Place of delivery"
    };

    //🔴3-GETDATA =====================================================================
        try {//SEND NEW apiFetch
            RES = await apiFetch("/api/modules/PhieuGetInfo", {
                method: "GET",
            });// VƯỢT QUA CHECKING  
        } catch (e) {
            const errorResult = await Swal.fire(//SWAL THÔNG BÁO & SAU KHI CLICK OK
                ConverErrToDataSwal(e)
            );
            if (errorResult.isConfirmed) {  
                return;// Nếu cần ngắt code
            }
        }

    //🟢4-XULY DATA
        //📌1. xử lý kết quả lấy gợi ý data
        const result = RES.data;
        const suggestList = Object.values(result.ListItems || {});      // Gợi ý items
        const ListDelivery = Object.values(result.ListDelivery || {});  // Gợi ý delivery

        //📌2. Đổ mảng ITEM vào table OR Xóa dữ liệu cũ, hiển thị bảng trống
        if (result.ITEM && Array.isArray(result.ITEM) && result.ITEM.length > 0) { 
            tabITEM = result['ITEM'];
        } 
        
        //📌3. CẬP NHẬT USER 
        if (result.US) {
            const u = result['US'] || {};      
            const readOnlyFields = ["NhanVien", "MaNhanVien", "MaBoPhan"];
            // 2. Duyệt qua mappingTable để sinh các hàng của Table theo đúng thứ tự mong muốn
            tableRows = Object.entries(mappingTable).map(([key, value]) => {

                const isReadOnly = readOnlyFields.includes(key) ? "readonly style='background:#eee; color:#555;'" : ""; 
                const labelText = mappingTable[key] || key;
                
                let valueData = u[key] !== undefined && u[key] !== null ? u[key] : "";
                //📌THÊM LOGIC MẶC ĐỊNH CHO TRƯỜNG DIEMGIAO TẠI ĐÂY
                if (key === "diemgiao" && (!valueData || String(valueData).trim() === "")) {
                    valueData = "ITV";
                }
                //CẤU HÌNH GIÁ TRỊ MẶC ĐỊNH CHO NGÀY LÀM PHIẾU
                if (key === "ngaylamphieu" && !valueData) {
                    valueData = new Date().toLocaleDateString('vi-VN',{
                        day:    '2-digit',
                        month:  '2-digit',
                        year:   'numeric'
                    });
                } 

                // LẤY NHÃN HIỂN THỊ
                return `
                    <tr>
                        <td><label>${labelText}</label></td>
                        <td>
                            <input class="rowsTable" 
                                id="${key}" 
                                value="${valueData}" ${isReadOnly}
                            >
                        </td>
                    </tr>
                `;
            }).join("");
        }

    //🟢5-RENDER UI >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> */
        app.innerHTML = `
            <div id="YCMH_CREATE_FRAME" class="resizable-container" style="height: 100%;">
                <!--🟢TABLEFT ----------------------------------------------------------------  -->
                <div class="BENTRAI resizable-column" style="width: 210mm;">
                    <!-- 📌~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~HD --> 
                    <div class="LEFTHD"></div>

                    <!-- 📌~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~TOP --> 
                    <div class="PHIEU_TOP"> 
                        <div class="TIEUDE">
                            <div class="TIEUDE_NEARBY">
                                <img class="LogoITV" src="../../../../assets/img/ITV128x128.png" alt="Logo">
                            </div>

                            <div class="TIEUDE_MID">
                                <h2>CÔNG TY TNHH CÔNG NGHỆ IKKA (VIỆT NAM)</h2>
                                <h4>KCN TÂN TRƯỜNG, XÃ MAO ĐIỀN, TP HẢI PHÒNG</h4>
                                <h3>PHIẾU ĐỀ NGHỊ MUA HÀNG HÓA/ DỊCH VỤ</h3>
                                <h4>REQUEST PURCHASE ODER GOODS /SERVICES</h4>

                                <div class="PHIEU_INFO">
                                    ${tableRows}
                                </div>
                            </div>

                            <div class="TIEUDE_NEARBY Sophieu">
                                <p class="SophieuText">Số phiếu / Request No</p>
                                <p class="SophieuNum">ITV-YYMMDD-Number</p>   
                            </div>
                        </div>

                        <div class="TIEUDE_BOT"> 
                            <button class="btn" id="BTN_DELROW"> Xóa dòng đang chọn / Delete selected</button>
                            <div class="FormISO"><span>ICS-ST-002/F-001(REV04-05.25)</span></div>
                        </div>
                    </div>

                    <!-- 📌-------------------------------------------------------------- CONTENT -->
                    <div class="PHIEU_CONTENTS">
                        <div class="table" id="tblYCMH_CREATE" style=" min-height: calc(100% - 35px); margin-left: 0px; width: 100%;"></div>
                        <div class="YCMH-Create-Note">
                                 - Click dòng cuối để thêm 1 dòng mới
                            <br> - Điền đầy đủ thông tin trên một hàng
                            <br> - Tích chọn vào ô checkbox để chọn xóa nhanh từng hàng hoặc nhiều hàng
                        </div>
                    </div>

                    <!-- 📌-------------------------------------------------------------- ATTFILE -->
                    <div class="PHIEU_ATTFILE" style="padding-top:10px">
                        <h5>TÀI LIỆU ĐÍNH KÈM (NẾU CÓ)/ ATTACH FILE</h5>
                        <button class="btn" id="BTN_ATTFILE" style="margin-top: 5px; font-size:10px" > FILE ĐÍNH KÈM / ATTACH FILE </button>

                        <div class="YCMH-Create-Note">
                            - Có thể gửi trực tiếp các file báo giá đính kèm theo phiếu
                            <br>
                            - Chỉ nhận dạng file PDF, Excel. Các định dạng file khác không được hỗ trợ
                        </div>
                    </div>

                    <!-- 📌-------------------------------------------------------------- BOT -->
                    <div class="PHIEU_BOT" style="padding-top:10px">
                        <h5>GHI CHÚ / NOTE</h5>
                        <textarea id="YCMH_note" class="Box_Input input" rows="5" placeholder="Nhập ghi chú ở đây (nếu có) :..."></textarea>
                        <button class="btn" id="BTN_HOANTHANH" style="margin-top: 10px;" > HOÀN THÀNH / FINISH </button>

                        <div class="YCMH-Create-Note">
                            - Kiểm tra kỹ thông tin trước khi hoàn thành phiếu, sau khi hoàn thành sẽ không thể thay đổi hoặc sửa
                            <br>
                            - Khi click hoàn thành phiếu sẽ tạo phiếu nháp và được gửi lên trình ký duyệt và gửi mail xác nhận đến các thành viên trong quy trình duyệt
                        </div>
                    </div>
                </div>

                <!--🟢THANH RESIZE ---------------------------------------------------------------   -->
                <div class="resizable-divider"></div>

                <!--🟢TABRIGHT ---------------------------------------------------------------  -->
                <div class="resizable-column BENPHAI" style="flex:1">
                    <!-- 📌 -->
                    <div class="BENPHAI_TOP"></div>

                    <!-- 📌 -->
                    <div class="BENPHAI_MID">
                        <h4>LỊCH SỬ CÁC VẬT PHẨM YÊU CẦU CỦA BỘ PHẬN (Tham khảo)</h4>

                        <!-- 🍒 -->   
                        <div class="IP_SEARCH">
                            <input type="text" id="List_Search" class="Box_Input" placeholder="Search...">
                            <span class="search-icon2">
                                <svg xmlns="[http://www.w3.org/2000/svg](http://www.w3.org/2000/svg)" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                            </span>
                        </div>

                        <!-- 🍒 --> 
                        <div class="table" id="danhsachITEM" style=" flex: 1; margin-left: 0px; width: 100%;"></div>
                    </div>

                    <!-- 📌 -->
                    <div class="BENPHAI_BOT"></div>
                </div>
                
            </div>  

        `;

    //🟢6-KÍCH HOẠT Resizable (Chia cột) 
        MakeResizable("YCMH_CREATE_FRAME");

    //🟢7-KHỞI TẠO TABLE CREATE
        //🍒 TEMP DATA
        const initialData = Array.from({ length: 10 }, (_, i) => ({
            id: i + 1,
            UsID: null,
        }));

        //🍒TABULATOR  
        let tableCREATE;  
        tableCREATE = new TableBase("#tblYCMH_CREATE", {
            //CẤU HÌNH CHUNG
            data: initialData,          //loaddata
            headerWordWrap: true,
            autoAddRowOnLastClick: true,// AUTO ADD ROW WHEN CLICK
            layout: "fitColumns",       //fitData 
            
            //THUỘC TÍNH ĐIỀU HƯỚNG PHÍM TAB CHUẨN ERP
            tabEndNewRow: true,                 // Khi Tab ở ô cuối cùng của dòng, tự động nhảy xuống dòng tiếp theo
            keybindings: {"navNext": "9", },    // Ép phím Tab (mã 9) luôn thực hiện hành động nhảy sang ô nhập liệu tiếp theo
            
            //CẤU HÌNH CỘT
            columns: [
                //📍ROWS SELECTION
                {title: "",
                    formatter: "rowSelection", 
                    hozAlign: "center",       // Căn giữa ô nội dung
                    maxWidth: 40,                  // Tăng nhẹ lên 30px để checkbox không bị tràn/sát viền
        
                    titleFormatter: "rowSelection", 
                    headerHozAlign: "center",// Căn giữa ô tiêu đề (header)
                    headerSort: false,
                    cssClass: "YCMH-checkbox", 
                    cellClick: function(e, cell) { 
                        e.stopPropagation(); 
                    }
                }, 

                //📍ROWS NO /TỰ TĂNG GIÁ TRỊ
                {title: "No", 
                    hozAlign: "center", 
                    //vertAlign: "middle",
                    maxWidth: 40, 
                    headerSort: false,
                    formatter: function(cell) {
                        const row = cell.getRow();
                        const table = cell.getTable();
                        const rowData = row.getData();

                        // 1. Nếu dòng hiện tại không có dữ liệu ở cột mhdt_item, không hiển thị số thứ tự
                        if (!rowData.mhdt_item || rowData.mhdt_item.trim() === "") {
                            return "";
                        }
                        // 2. Lấy tất cả các dòng hiện tại của bảng
                        const allRows = table.getRows();
                        let count = 0;
                        // 3. Đếm xem từ dòng đầu tiên đến dòng hiện tại, có bao nhiêu dòng đã nhập mhdt_item
                        for (let i = 0; i < allRows.length; i++) {
                            const currentData = allRows[i].getData();
                            // Nếu dòng có dữ liệu, tăng biến đếm
                            if (currentData.mhdt_item && currentData.mhdt_item.trim() !== "") {
                                count++;
                            }
                            // Nếu duyệt đến đúng dòng hiện tại thì dừng lại và trả về số thứ tự
                            if (allRows[i] === row) {
                                return count;
                            }
                        }
                        return "";
                    }
                },

                //📍ROWS ITEMS
                {title: "Hàng hóa dịch vụ/ Goods, services name", 
                    field: "mhdt_item",  
                    minWidth: 150,
                    //maxWidth: 200,
                    widthGrow: 5,
                    variableHeight: true,  
                    headerSort: false, 
                    formatter: "textarea",   
                    editor: "erpAutoFitList", 
                    editorParams:{
                        values: suggestList,           // Mảng danh sách gợi nhớ
                    }
                },

                //📍ROWS QUATITY
                {title: "Số lượng/ Quantity",                     
                    formatter: "textarea", 
                    field: "mhdt_qty",          
                    minWidth: 50,
                    maxWidth: 60,
                    widthGrow: 1,
                    variableHeight: true,  
                    headerSort: false,  
                    editor: "erpAutoFit",
                    validator: ["numeric"]
                },
                
                //📍ROWS UNIT
                {title: "Đơn vị/Unit",                            
                    formatter: "plaintext",
                    field: "mhdt_unit",         
                    minWidth: 50,
                    maxWidth: 70,   
                    widthGrow: 1,                      
                    headerSort: false,  
                    editor: "list", 
                    editorParams: {
                        values: [
                            // --- Hệ Đếm / Đóng gói (Counting / Packaging) ---
                            { value: "PCS", label: "PCS - Cái/Chiếc/Quả" },
                            { value: "SET", label: "SET - Bộ/Cặp" },
                            { value: "BOX", label: "BOX - Hộp/Thùng nhỏ" },
                            { value: "PACK",label: "PACK - Gói/Kiện" },
                            { value: "ROLL",label: "ROLL - Cuộn/Băng keo" },
                            { value: "CTN", label: "CTN - Thùng Carton (Carton)" },
                            { value: "PAIR",label: "PAIR - Đôi/Cặp (Găng tay, giày...)" },
                            { value: "BAG", label: "BAG - Bao/Túi" },
                            { value: "BTL", label: "BTL - Chai/Lọ (Bottle)" },
                            { value: "CAN", label: "CAN - Can/Bình thiếc" },
                            { value: "BAR", label: "BAR - Thanh/Thỏi (Nhôm, sắt...)" },
                            { value: "PLT", label: "PLT - Pallet" },
                            { value: "DRUM",label: "DRUM - Phuy/Thùng phuy (Dầu, hóa chất...)" },
                            { value: "DZ",  label: "DZ - Tá (12 cái)" },

                            // --- Hệ Khối Lượng (Weight) ---
                            { value: "KG",  label: "KG - Kilogram" },
                            { value: "G",   label: "G - Gram" },
                            { value: "TON", label: "TON - Tấn" },
                            { value: "LBS", label: "LBS - Pound (Cân Anh)" },

                            // --- Hệ Chiều Dài / Độ dày (Length) ---
                            { value: "M",   label: "M - Mét" },
                            { value: "MM",  label: "MM - Milimét" },
                            { value: "CM",  label: "CM - Centimét" },
                            { value: "INCH",label: "INCH - Inch (Hệ thợ máy/ống nước)" },
                            { value: "FT",  label: "FT - Feet (Bộ)" },

                            // --- Hệ Diện Tích (Area) ---
                            { value: "M2",  label: "M² - Mét vuông" },
                            { value: "CM2", label: "CM² - Centimét vuông" },

                            // --- Hệ Thể Tích / Chất lỏng (Volume) ---
                            { value: "L",   label: "L - Lít" },
                            { value: "ML",  label: "ML - Mililít" },
                            { value: "M3",  label: "M³ - Mét khối" },
                            { value: "GAL", label: "GAL - Gallon" },

                            // --- Hệ Điện năng / Công suất / Áp suất (Technical / Electrical) ---
                            { value: "KW",  label: "KW - Kilowatt" },
                            { value: "KWH", label: "KWH - Số điện (Kilowatt-giờ)" },
                            { value: "V",   label: "V - Volt" },
                            { value: "A",   label: "A - Ampe" },
                            { value: "HP",  label: "HP - Mã lực / Ngựa (Mô-tơ, máy lạnh)" },
                            { value: "PSI", label: "PSI - Áp suất (Pound/inch vuông)" },
                            { value: "BAR_P", label: "BAR - Áp suất Bar" },

                            // --- Hệ Thời Gian / Nhân công (Time / Labor) ---
                            { value: "HR",  label: "HR - Giờ công (Hour)" },
                            { value: "DAY", label: "DAY - Ngày công" },
                            { value: "MTH", label: "MTH - Tháng" },

                            // --- Hệ Dịch Vụ / Dự Án / Khác (Services / ERP Specific) ---
                            { value: "JOB", label: "JOB - Công việc/Gói dịch vụ" },
                            { value: "LOT", label: "LOT - Lô hàng" },
                            { value: "TRIP",label: "TRIP - Chuyến (Vận chuyển, xe tải...)" },
                            { value: "CONT",label: "CONT - Container" },
                            { value: "LIC", label: "LIC - License (Bản quyền phần mềm)" }
                        ],
                        autocomplete: true,
                        clearable: true,
                        showListOnEmpty: true, // Click vào tự xổ danh sách ngay khi chưa gõ gì
                        freetext: true,        // Cho phép gõ tự do để dễ dàng xóa rỗng
                    },

                    // 🟢 SỬ DỤNG TÍNH NĂNG CHẶN KHÔNG CHO THOÁT (VALIDATOR)
                    validator: function(cell, value, parameters) {
                        // Ngoại lệ 1: Nếu người dùng click vào rồi click ra ngoài ngay (không sửa gì), cho thoát
                        if (value === cell.getOldValue()) return true;

                        // Ngoại lệ 2: Nếu người dùng chủ động bấm nút "x" hoặc xóa trống để về rỗng, vẫn cho thoát
                        if (value === undefined || value === null || String(value).trim() === "") {
                            return true;
                        }

                        // Lấy danh sách các mã đơn vị hợp lệ để kiểm tra
                        const columnDef = cell.getColumn().getDefinition();
                        const validValues = columnDef.editorParams.values.map(v => v.value);
                        const upperValue = String(value).trim().toUpperCase();

                        // Trường hợp 1: Nếu nhập đúng chuẩn (hoặc chữ thường khớp chuẩn) -> Tự chuẩn hóa viết hoa và cho thoát
                        if (validValues.includes(upperValue)) {
                            // Tự động chuyển chữ thường thành chữ hoa nếu cần
                            if (value !== upperValue) {
                                cell.setValue(upperValue); 
                            }
                            return true; // Cho phép thoát khỏi ô
                        }

                        // Trường hợp 2: GÕ SAI HOÀN TOÀN
                        // TRẢ VỀ FALSE -> Tabulator sẽ khóa cứng ô này lại, báo đỏ và KHÔNG CHO THOÁT
                        return false; 
                    }
                },

                //📍ROWS DESCRIPTION
                {title: "Mô tả SP/ Description",                  
                    formatter: "textarea", 
                    field: "mhdt_description",  
                    minWidth: 150,
                    maxWidth: 180,
                    widthGrow: 2,
                    variableHeight: true,  
                    headerSort: false,  
                    editor: "erpAutoFit",
                },
                
                //📍ROWS MUC DICH
                {title: "Mục đích sử dụng/ Acceptable use policy",
                    formatter: "textarea", 
                    field: "mhdt_purpose",      
                    minWidth: 50,
                    maxWidth: 150,  
                    widthGrow: 2,
                    variableHeight: true,  
                    headerSort: false,  
                    editor: "erpAutoFit",
                },
                
                //📍ROWS DELIVERY TIME
                {title: "Thời gian nhận/ Delivery time",     
                    field: "mhdt_delivery",          
                    widthGrow:1,  
                    minWidth: 70,
                    maxWidth: 90,
                    variableHeight: true,  
                    headerSort: false,  
                    formatter: "textarea", 
                    editor: "erpAutoFitList",
                    editorParams:{
                        values: ListDelivery,           // Mảng danh sách gợi nhớ
                    }
                },
            ],

            //SỰ KIỆN CLCIK CHECK BOX 
            rowSelectionChanged: function(data, rows) {
                // data: mảng dữ liệu các dòng đang được chọn
                // rows: mảng các đối tượng dòng đang được chọn
                const btnDelete = document.getElementById("BTN_DELROW");

                if (btnDelete) {
                    if (rows.length > 0) {
                        // Có ít nhất 1 dòng được tích chọn -> Hiện nút xóa
                        btnDelete.style.setProperty("display", "flex", "important");

                    } else {
                        // Không có dòng nào được chọn -> Ẩn nút xóa
                        btnDelete.style.setProperty("display", "none", "important");

                    }
                }
            },    

            // SỰ KIỆN CỘT STT    
            cellEdited: function(cell) {
                // Nếu cột vừa sửa là cột hàng hóa dịch vụ
                if (cell.getField() === "mhdt_item") {
                    
                    // 1. Lấy ra đối tượng dòng hiện tại
                    const row = cell.getRow();
                    
                    // 2. Gọi hàm formatter của cột No để tính toán ra số thứ tự chuẩn xác lúc này
                    // (Hàm này mượn lại chính logic tính toán thông minh mà bạn đã viết ở trên)
                    const columnNo = cell.getTable().getColumns()[1]; 
                    const columnDef = columnNo.getDefinition();
                    const currentNoValue = columnDef.formatter(row.getCells()[1]); // Lấy số thứ tự mới nhất

                    // 3. Tìm phần tử HTML (DOM) của ô "No" trên dòng này và ghi đè chữ vào
                    const cellNoElement = row.getCells()[1].getElement();
                    if (cellNoElement) {
                        cellNoElement.textContent = currentNoValue; 
                        
                    }
                }
            }

        }); /* KẾT THÚC CẤU HÌNH BẢNG */


    //🟢8- KHỞI TẠO BẢNG DANH SÁCH THƯỜNG DÙNG
        const tableDANHSACH = new TableBase("#danhsachITEM", {
            //CẤU HÌNH CHUNG
            data: tabITEM,        // Đổ trực tiếp mảng dữ liệu của bạn vào đây
            layout: "fitColumns", // Ép cột giãn hết chiều rộng của khung chứa bảng
            headerVisible: false, // Ẩn hoàn toàn thanh tiêu đề (Header) của bảng
            selectable: 1,        // Cấu hình chuẩn click chọn: chỉ cho phép chọn 1 dòng
            height: "50vh",       // Kích hoạt chế độ Virtual DOM để cuộn mượt mà không lo giật lag

            //CẤU HÌNH COLUMN
            columns: [
                {
                field: "YCMH_ITEM", // Khớp chính xác với trường dữ liệu trong object của bạn
                resizable: false,   // Khóa tính năng kéo giãn độ rộng cột bằng tay
                widthGrow: 2,       // Tự động co giãn chiếm 100% diện tích chiều ngang

                // Bộ định dạng hiển thị dữ liệu
                formatter: function (cell, formatterParams, onRendered) {
                    const data = cell.getData();
                    // Phòng trường hợp dữ liệu bị null hoặc undefined
                    const itemText = data.YCMH_ITEM ? data.YCMH_ITEM : "";

                    // Kiểm tra xem có phần tử ô tìm kiếm trên giao diện không
                    const searchInputEl = document.getElementById("YCMH_Search");
                    const keyword = searchInputEl ? searchInputEl.value.trim() : "";

                    // Nếu có từ khóa đang gõ, tự động bôi vàng cụm từ khớp trên danh sách
                    if (keyword !== "") {
                        const regex = new RegExp(`(${keyword})`, "gi");
                        return itemText.replace(regex, `<mark style="background-color: #ffeb3b; padding: 0 2px; border-radius: 2px;">$1</mark>`);
                    }

                    // Nếu không tìm kiếm, trả về chuỗi văn bản thông thường
                    return itemText;
                },
                },

                {formatter: "textarea", field: "YCMH_UNIT", width: 50,  variableHeight: true,  headerSort: false,}

            ],
        });



    //🟡9-SỰ KIỆN TÌM KIẾM SẢN PHẨM (ỨNG DỤNG DEBOUNCE & REMOVE VIETNAMESE TONES)
        const searchInput = document.getElementById("List_Search");
        if (searchInput) {
            // 1. Định nghĩa hàm xử lý tìm kiếm thô
            const performSearch = (keyword) => {
                const coreTable = getCoreTable(tableDANHSACH);// Sử dụng helper getCoreTable để lấy instance bảng chuẩn
                if (!coreTable) return;

                // Chuẩn hóa từ khóa: xóa khoảng trắng thừa và bỏ dấu tiếng Việt
                const cleanKeyword = removeVietnameseTones(keyword).trim().toLowerCase();

                if (isEmpty(cleanKeyword)) {
                    coreTable.clearFilter();// Nếu ô tìm kiếm trống, xóa bỏ mọi bộ lọc để hiển thị lại toàn bộ danh sách

                } else {
                    // Nếu có từ khóa, tiến hành lọc trên cột "YCMH_ITEM"
                    // Sử dụng custom filter function để tìm kiếm không phụ thuộc vào dấu tiếng Việt
                    coreTable.setFilter((rowData) => {
                        if (!rowData.YCMH_ITEM) return false;
                        
                        // Chuyển đổi dữ liệu của dòng hiện tại sang không dấu để so sánh
                        const cleanRowData = removeVietnameseTones(rowData.YCMH_ITEM).toLowerCase();
                        // Trả về true nếu dữ liệu chứa từ khóa tìm kiếm
                        return cleanRowData.includes(cleanKeyword);
                    });
                }
            };

            //2. Bọc hàm tìm kiếm bằng bộ hoãn debounce (delay 400ms mới kích hoạt)
            const debouncedSearch = debounce((event) => {
                const keyword = event.target.value;
                performSearch(keyword);
            }, 400);

            //3. Gắn sự kiện "input" vào ô Tìm kiếm
            searchInput.addEventListener("input", debouncedSearch);
        }


    //🟡10-SỰ KIỆN CLICK NÚT XÓA DÒNG (ĐẢM BẢO TỐI THIỂU 5 DÒNG)
        document.getElementById("BTN_DELROW").addEventListener("click", function() {
            // 1. Truy cập vào lõi tabulator gốc bên trong TableBase để lấy các dòng được chọn
            const selectedRows = tableCREATE.table.getSelectedRows();
            
            if (selectedRows.length > 0) {
                // Thực hiện xóa toàn bộ các dòng đã tích chọn
                selectedRows.forEach(row => {
                    row.delete();
                });
                
                // 2. Ẩn nút xóa ngay sau khi thực hiện xong
                this.style.setProperty("display", "none", "important");

                // 3. LOGIC BẢO HỘ SỐ DÒNG TỐI THIỂU = 5
                // Lấy tổng số dòng hiện còn lại trong bảng sau khi xóa
                let currentTotalRows = tableCREATE.table.getRows().length;

                if (currentTotalRows < 5) {
                    // Tính toán xem đang bị thiếu bao nhiêu dòng
                    const missingCount = 5 - currentTotalRows;

                    // Chạy vòng lặp để thêm bù số dòng trống vào CUỐI BẢNG (addToTop = false)
                    for (let i = 0; i < missingCount; i++) {
                        // Sử dụng API .addRow() trực tiếp từ class TableBase
                        tableCREATE.addRow({}, false); 
                    }
                }

                // 4. Cập nhật lại số thứ tự (Cột 'No') để bảng sắp xếp lại số từ đầu đến cuối mượt mà
                if (tableCREATE.table.getColumns()[1]) {
                    tableCREATE.table.getColumns()[1].updateDefinition();
                }
            }
        });

    //🟡SỰ KIỆN CLICK ATTACH FILE
        document.getElementById('BTN_ATTFILE').addEventListener('click', YCMH_ATTFILE);

    //🟡SỰ KIỆN CLICK HOANTHANH (CẬP NHẬT THÊM FILE ĐÍNH KÈM)
        document.getElementById("BTN_HOANTHANH").addEventListener("click", function(e) {
            //🌟 1. Thu thập dữ liệu từ các ô input thông tin phiếu
            const infoData = {};
            document.querySelectorAll(".PHIEU_INFO .rowsTable").forEach(input => {
                if (input.id) {
                    infoData[input.id] = input.value.trim();
                }
            });

            //🌟 Lấy thêm dữ liệu GHI CHÚ
            const noteInput = document.getElementById("YCMH_note");
            if (noteInput) {
                infoData["YCMH_note"] = noteInput.value.trim();
            }

            //🌟 KHỞI TẠO FORMDATA ĐỂ ĐÓNG GÓI DỮ LIỆU GỬI SERVER
            const formData = new FormData();
            formData.append("infoData", JSON.stringify(infoData)); // Đóng gói các thông tin chuỗi (Info + Ghi chú) dưới dạng OBJECT

            //🌟 ĐÓNG GÓI CÁC FILE ĐÍNH KÈM (attachedFiles) VÀO FORMDATA
            // attachedFiles là mảng chứa các File Object local đã chọn ở hàm đính kèm trước đó
            if (typeof attachedFiles !== 'undefined' && attachedFiles.length > 0) {
                attachedFiles.forEach((file, index) => {
                    // Đặt tên key là attached_files[] để Backend (như PHP) tự nhận diện thành một mảng file
                    formData.append("attached_files[]", file); 
                });
            }
            // 3. Truyền formData này vào tham số thứ 3 của hàm xử lý
            Fun_HOANTHANH(e, tableCREATE, formData); 
        });

}//❌END YCMH_CREATE_RENDER

/* HÀM ATTACH FILE (CLICK BTN FILE ĐÍNH KÈM)
⭕ ======================================================================================================================================================================== YCMH_ATTFILE */
function YCMH_ATTFILE() {
    // Tìm hoặc tạo ngầm một input[type="file"] để kích hoạt hộp thoại chọn file
    let fileInput = document.getElementById('YCMH_hidden_file_input');
    if (!fileInput) {
        fileInput = document.createElement('input');
        fileInput.id = 'YCMH_hidden_file_input';
        fileInput.type = 'file';
        fileInput.multiple = true; // Cho phép chọn nhiều file một lúc
        fileInput.accept = '.pdf, .xls, .xlsx'; // Giới hạn định dạng
        fileInput.style.display = 'none';
        document.body.appendChild(fileInput);

        // SỰ KIỆN CHỌN FILE
        fileInput.addEventListener('change', function(e) {
            const files = e.target.files;
            for (let i = 0; i < files.length; i++) {
                // Kiểm tra trùng lặp tên file để tránh đính kèm một file nhiều lần
                const isDuplicate = attachedFiles.some(f => f.name === files[i].name && f.size === files[i].size);
                if (!isDuplicate) {
                    attachedFiles.push(files[i]);
                }
            }
            // Render lại danh sách giao diện sau khi thêm file
            RENDER_ATTFILE();//⭕
            // Reset giá trị input để có thể chọn lại cùng một file nếu lỡ xóa trước đó
            fileInput.value = ''; 
        });
    }
    // Kích hoạt click vào input ẩn
    fileInput.click();

}//❌END YCMH_ATTFILE 


/* RENDER_ATTFILE render danh sách file đính kèm theo phong cách Outlook 2019 
⭕ ======================================================================================================================================================================== RENDER_ATTFILE */
function RENDER_ATTFILE() {
    const attSection = document.querySelector('.PHIEU_ATTFILE');
    if (!attSection) return;

    // Tìm hoặc tạo vùng chứa các file đính kèm (nằm sau nút bấm)
    let container = document.getElementById('YCMH_attachments_container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'YCMH_attachments_container';
        container.className = 'outlook-attachment-container';
        attSection.appendChild(container); // Gắn ngay dưới/sau vùng cấu trúc nút
    }

    // Xóa danh sách cũ đi để render lại
    container.innerHTML = '';

    // Duyệt qua mảng file để build UI từng Card
    attachedFiles.forEach((file, index) => {
        const ext = file.name.split('.').pop().toLowerCase();
        let iconClass = 'excel';
        let iconSvg = '';

          if (ext === 'pdf') {
              // Mã SVG Icon Adobe PDF chuẩn màu đỏ đặc trưng
              iconSvg = `
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48">
                  <path fill="#E53935" d="M38,42H10c-2.2,0-4-1.8-4-4V8c0-2.2,1.8-4,4-4h21l11,11v23C42,40.2,40.2,42,38,42z"/>
                  <path fill="#FFF" d="M34,15H29V10L34,15z"/>
                  <path fill="#FFF" d="M14 26H18C19.1 26 20 25.1 20 24V22C20 20.9 19.1 20 18 20H14V30H16V26ZM16 22H18V24H16V22Z"/>
                  <path fill="#FFF" d="M22 20H25C26.7 20 28 21.3 28 23V27C28 28.7 26.7 30 25 30H22V20ZM24 22V28H25C25.6 28 26 27.6 26 27V23C26 22.4 25.6 22 25 22H24Z"/>
                  <path fill="#FFF" d="M30 20H36V22H32V24H35V26H32V30H30V20Z"/>
              </svg>`;
          } else if (ext === 'xls' || ext === 'xlsx') {
              // Mã SVG Icon Microsoft Excel 2019/365 chuẩn màu xanh lá đặc trưng
              iconSvg = `
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48">
                  <path fill="#107C41" d="M28,4H11.5C9.6,4,8,5.6,8,7.5v33c0,1.9,1.6,3.5,3.5,3.5H36.5c1.9,0,3.5-1.6,3.5-3.5V16L28,4z"/>
                  <path fill="#FFF" opacity=".5" d="M31 18H26V13L31 18z"/>
                  <path fill="#FFF" d="M14.5 19h2.3l2.2 4.3c.2.4.4.8.5 1.1h.1c.1-.3.3-.7.5-1.1l2.3-4.3h2.2l-3.6 6 3.8 6h-2.3l-2.4-4.6c-.2-.4-.3-.7-.4-1h-.1c-.1.3-.2.6-.4 1l-2.4 4.6h-2.3l3.7-5.9-3.6-6.1z"/>
              </svg>`;
          } else {
              // Icon file mặc định (Generic File) nếu người dùng cố tình attach file khác
              iconSvg = `
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48">
                  <path fill="#90A4AE" d="M38,42H10c-2.2,0-4-1.8-4-4V8c0-2.2,1.8-4,4-4h21l11,11v23C42,40.2,40.2,42,38,42z"/>
                  <path fill="#FFF" opacity=".5" d="M34 15H29V10L34,15z"/>
              </svg>`;
          }

        // Đổi kích thước file sang định dạng dễ đọc (KB/MB)
        const fileSizeStr = file.size > 1024 * 1024 
            ? (file.size / (1024 * 1024)).toFixed(2) + ' MB' 
            : (file.size / 1024).toFixed(0) + ' KB';

        // Tạo thẻ card tệp
        const card = document.createElement('div');
        card.className = 'outlook-att-card';
        card.innerHTML = `
            <span class="outlook-att-icon">${iconSvg}</span>
            <div class="outlook-att-info">
                <span class="outlook-att-name" title="${file.name}">${file.name}</span>
                <span class="outlook-att-size">${fileSizeStr}</span>
            </div>
            <span class="outlook-att-remove" data-index="${index}">&times;</span>
        `;
        
        //🟢SỰ KIỆN DOUBLE_CLICK ATTACHMENT          
        // Sự kiện double-click vào thân Card để mở tệp local
        card.addEventListener('dblclick', function(e) {
            // Nếu click trúng vào nút xóa (X) thì không làm gì cả
            if (e.target.classList.contains('outlook-att-remove')) return;

            // Khởi tạo một đường dẫn nội bộ trỏ thẳng vào tệp trong bộ nhớ máy tính
            const localBlobURL = URL.createObjectURL(file);

            if (ext === 'pdf') {
                // Mở trực tiếp file PDF local trên một Tab mới của trình duyệt
                const newTab = window.open();
                if (newTab) {
                    newTab.document.write(`
                        <html style="margin:0; padding:0;">
                            <head><title>${file.name}</title></head>
                            <body style="margin:0; padding:0;">
                                <embed width="100%" height="100%" src="${localBlobURL}" type="application/pdf">
                            </body>
                        </html>
                    `);
                }
            } else if (ext === 'xls' || ext === 'xlsx') {
                // Đẩy file Excel local ngược ra luồng tải về để người dùng click mở bằng Excel của Windows
                const tempLink = document.createElement('a');
                tempLink.href = localBlobURL;
                tempLink.download = file.name; // Giữ nguyên tên gốc của file local
                document.body.appendChild(tempLink);
                tempLink.click();
                document.body.removeChild(tempLink);
            }

            // Giải phóng vùng nhớ của trình duyệt sau khi xử lý xong link tạm
            setTimeout(() => {
                URL.revokeObjectURL(localBlobURL);
            }, 200);
        });

        //🟢SỰ KIỆN XÓA ATTACHMENT  
        // Gán sự kiện xóa file khi click vào dấu (X)
        card.querySelector('.outlook-att-remove').addEventListener('click', function(e) {
            const idx = parseInt(this.getAttribute('data-index'));
            attachedFiles.splice(idx, 1); // Xóa khỏi mảng dữ liệu
            RENDER_ATTFILE(); // Render lại giao diện
        });

        container.appendChild(card);
    });
}//❌END RENDER_ATTFILE





/*HOÀN THÀNH PHIẾU (CLICK BTN HOÀN THÀNH)
🟢
⭕ ======================================================================================================================================================================== Fun_HOANTHANH */
export async function Fun_HOANTHANH(e, tableCREATE, infoPayload) {
//*************************************************** Đổi trạng thái nút để chống double click
    const btn = document.getElementById("BTN_HOANTHANH");
    const noteInput = document.getElementById("YCMH_note");

    const originalText = btn.innerText;
    btn.disabled = true;
    btn.innerText = "Đang trong quá trình xử lý...";
//***************************************************
    //⚪1 THU THẬP DỮ LIỆU & KIỂM TRA SAU ĐÓ GỬI DỮ LIỆU LÊN SERVER
    try {
        //📌1: LẤY DỮ LIỆU TỪ BẢNG THÔNG TIN PHIẾU ✔;
            let infoData = JSON.parse(infoPayload.get("infoData")); // Tách lấy infoData
            //👻1 KIỂM TRA NGÀY LÀM PHIẾU CÓ ĐÚNG ĐỊNH DẠNG KHÔNG 
                if (!infoData.ngaylamphieu || infoData.ngaylamphieu.trim() === "") {
                    ErpAlert('Thiếu thông tin', 'Vui lòng nhập Ngày tạo phiếu!', 'warning');
                    btn.disabled = false; btn.innerText = originalText;
                    return;
                }
            //👻2 CHK TIME FORMAT    
                const dateParts = infoData.ngaylamphieu.split("/");
                if (dateParts.length === 3) {
                    const day = parseInt(dateParts[0], 10);
                    const month = parseInt(dateParts[1], 10) - 1;
                    const year = parseInt(dateParts[2], 10);
                    const checkDate = new Date(year, month, day);
                    //BỔ SUNG ĐIỀU KIỆN: Kiểm tra chuỗi năm bắt buộc phải đủ 4 ký tự
                    if (dateParts[2].trim().length !== 4 || isNaN(checkDate.getTime()) || checkDate.getDate() !== day || checkDate.getMonth() !== month ) {
                        ErpAlert('Sai định dạng TIME', 'Ngày tạo phiếu không hợp lệ hoặc năm phải đủ 4 chữ số (VD: 2026)!', 'warning');
                        btn.disabled = false; btn.innerText = originalText;
                        return;
                    }
                } else {
                    ErpAlert('Sai định dạng thời gian', 'Ngày tạo phiếu phải có định dạng Ngày/Tháng/Năm (VD: 24/06/2026)!', 'warning');
                    btn.disabled = false; btn.innerText = originalText;
                    return;
                }

            //👻3 CHK Delivery
            if (!infoData.diemgiao || infoData.diemgiao === "") {
                ErpAlert('Thiếu thông tin', 'Nhập dữ liệu vào Điểm giao hàng!', 'warning');
                btn.disabled = false; btn.innerText = originalText;
                return;
            }

        //📌2 LẤY DỮ LIỆU TỪ BẢNG CHI TIẾT SẢN PHẨM (Tabulator)
            if (!tableCREATE || !tableCREATE.table) {
                ErpAlert('Thiếu thông tin', 'Dữ liệu bảng không tìm thấy!', 'warning');
                btn.disabled = false; btn.innerText = originalText;
                return;
            }
             
        //📌3 KIỂM TRA DỮ LIỆU BẢNG CHI TIẾT
            const tableData = tableCREATE.table.getData();

            //👻1 CHECK TABLE TỒN TẠI ?
            if (!tableData || tableData.length === 0) {
                ErpAlert('Thông báo lỗi','Không tìm thấy dữ liệu mặt hàng!','warning');
                btn.disabled = false; btn.innerText = originalText;
                return; 
            }

            //👻2 CHECK TABLE - Lọc bỏ dòng trống và kiểm tra xem có dòng nào quên nhập Tên hàng hoặc Số lượng không
            let hasValidData = false; // Cờ kiểm
            for (let i = 0; i < tableData.length; i++) {
                //>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> FOR_START
                const row = tableData[i];
                
                //🎲1. Đọc và làm sạch dữ liệu của hàng hiện tại
                const item = row.mhdt_item ? String(row.mhdt_item).trim() : "";
                const qtyStr = row.mhdt_qty ? String(row.mhdt_qty).trim() : "";
                const unit = row.mhdt_unit ? String(row.mhdt_unit).trim() : "";
                const delivery = row.mhdt_delivery ? String(row.mhdt_delivery).trim() : "";

                //🎲2. Trường hợp TRỐNG TOÀN BỘ DÒNG -> Bỏ qua, không kiểm tra, đi tiếp sang dòng sau
                if (item === "" && qtyStr === "" && unit === "" && delivery === "") {
                    continue; 
                }
                //🎲3. Cột HÀNG HÓA NOT NULL
                if (item !== "") {
                    // Kiểm tra Số lượng (mhdt_qty)
                    const qty = parseFloat(qtyStr);
                    if (qtyStr === "" || isNaN(qty) || qty <= 0) {
                        ErpAlert('Thông báo lỗi',`Dòng số ${i + 1}: Số lượng phải là số và lớn hơn 0!`,'warning');
                        btn.disabled = false; btn.innerText = originalText;
                        return;
                    }

                    // Kiểm tra Đơn vị (mhdt_unit)
                    if (unit === "") {
                        ErpAlert('Thông báo lỗi',`Dòng số ${i + 1}: Vui lòng chọn Đơn vị tính!`,'warning');
                        btn.disabled = false; btn.innerText = originalText;
                        return;
                    }

                    // Kiểm tra Thời gian nhận (mhdt_delivery)
                    if (delivery === "") {
                        ErpAlert('Thông báo lỗi',`Dòng số ${i + 1}: Vui lòng nhập Thời gian nhận hàng!`,'warning');
                        btn.disabled = false; btn.innerText = originalText;
                        return;
                    }

                    // Nếu vượt qua hết tất cả các bước check trên -> Dòng này là hợp lệ
                    hasValidData = true; 

                } else {
                //🎲4. Cột HÀNG HÓA NULL
                    ErpAlert('Thông báo lỗi', `Dòng số ${i + 1}: Chưa nhập Tên hàng hóa/dịch vụ!`,'warning');
                    btn.disabled = false; btn.innerText = originalText;
                    return;
                }
                //<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< FOR_END
            }

            //👻3 KIỂM TRA CỜ KIỂM : BẮT BUỘC PHẢI CÓ IT NHẤT 1 ROW HỢP LỆ == hasValidData TRUE
            if (!hasValidData) {
                ErpAlert('Thông báo lỗi','Yêu cầu phải nhập ít nhất 1 mặt hàng hợp lệ vào bảng biểu mẫu!','warning');
                btn.disabled = false; btn.innerText = originalText;
                return;
            }

        //📌4  LÀM SẠCH PLAYLOAD & ĐÓNG GÓI PAYLOAD GỬI LÊN SERVER
            const itemPayload = tableData.filter(row => row.mhdt_item && String(row.mhdt_item).trim() !== "");
            //  KHỞI TẠO FORM DATA
            let finalFormData = new FormData();
                // Đóng gói Header & Detail
                finalFormData.append("Header", JSON.stringify(infoData));
                finalFormData.append("Details", JSON.stringify(itemPayload));

                // Đóng gói thêm mảng AttachFile
                if (typeof attachedFiles !== 'undefined' && attachedFiles.length > 0) {
                    attachedFiles.forEach((file) => {
                        finalFormData.append("attached_files[]", file);
                    });
                }
            //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ KIỂM TRA DỮ LIỆU TRƯỚC KHI GỬI LÊN SV
            //console.log("🌏🌏🌏 KÊT QUẢ GỬI ĐI CHUẨN")
            //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~  
                // Tạo bộ kiểm soát thời gian chờ (Timeout) đề phòng file lớn hoặc mạng LAN nghẽn
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 30000); // 30s

                try {
                    const KETQUA = await apiFetchForm("/api/modules/YCMH_LUUDATA", {
                        method: "POST",
                        body: finalFormData,
                        signal: controller.signal // Truyền signal vào apiFetchForm
                    });

                    // Xóa thời gian chờ ngay khi Server đã phản hồi
                    clearTimeout(timeoutId);
                    console.log("KETQUA",KETQUA);

                    //🟡 KẾT QUẢ OKE
                        if (KETQUA.ok) {
                            // SENDMAIL
                            apiFetch("/api/modules/YCMH_SEND_WF_MAIL",{
                                method: "POST",
                                body: KETQUA.InstanceID,
                            });    

                            
                            //SUCCESS MESSENGER
                            const resultMsg = await Swal.fire({ 
                                icon: 'success',
                                title: KETQUA.title || "DONE", 
                                html: `<div style="color: #0f4832; font-size: 11px; line-height: 1.2; text-align: left"> ${KETQUA.msg}</div>`,
                                allowOutsideClick: false // Khóa không cho click ra ngoài để đóng
                            });

                            if (resultMsg.isConfirmed) {
                                // Xóa note input
                                if (typeof noteInput !== 'undefined' && noteInput) { noteInput.value = '';}
                                // Xóa danh sách file tạm
                                if (typeof attachedFiles !== 'undefined') {
                                    attachedFiles = [];
                                    if (typeof RENDER_ATTFILE === 'function') RENDER_ATTFILE();
                                }
                                // Reset dữ liệu Bảng
                                if (typeof tableCREATE !== 'undefined' && tableCREATE.table) {
                                    tableCREATE.table.clearData(); 
                                    for (let i = 0; i < 10; i++) {
                                        tableCREATE.addRow({}, false); 
                                    }   
                                }
                            }          
                        }

                } catch (e) {
                    
                    const errorResult =  Swal.fire(
			            ConverErrToDataSwal(e)
		            );

		            if (errorResult.isConfirmed) {  
			            return;// nếu cần ngắt code
		            }

                } finally {
                    // Luôn mở lại nút bấm (dù thành công hay thất bại)
                    if (typeof btn !== 'undefined' && btn) {
                        btn.disabled = false; 
                        btn.innerText = originalText;
                    }
                }

    } catch (error) {
            // 🎈 STEP 5: LỖI HỆ THỐNG NGOÀI Ý MUỐN
            console.error("Lỗi sập hệ thống hoặc lỗi kết nối mạng:", error);
            ErpAlert('LỖI KẾT NỐI','Không thể gửi dữ liệu lên máy chủ. Vui lòng kiểm tra lại mạng!','error');
            btn.disabled = false; btn.innerText = originalText;
    }

}/*❌END Fun_HOANTHANH */

function YCMH_CREATE_HOANTHANH_SENDMAIL(params) {
    
}