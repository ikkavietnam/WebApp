import { TABLE_DEFAULT_CONFIG } from "./tableERPConfig.js";

/* 
================================================================================
1. ĐĂNG KÝ CUSTOM EDITOR TOÀN CỤC (GLOBAL REGISTRATION)
Sử dụng: editor: "erpAutoFit" trong column definition
================================================================================ 
*/
if (typeof Tabulator !== "undefined") {
    Tabulator.extendModule("edit", "editors", {
        erpAutoFit: function (cell, onRendered, success, cancel, editorParams) {
            // Khởi tạo textarea
            const editor = document.createElement("textarea");
            
            // Style cơ bản để khớp với cell
            editor.style.padding = "0px";
            editor.style.width = "100%";
            editor.style.height = "100%"; // Ép độ cao chiếm trọn ô
            editor.style.boxSizing = "border-box";
            editor.style.overflow = "hidden";
            editor.style.border = "none";
            editor.style.outline = "none";
            editor.style.fontFamily = "inherit";
            editor.style.fontSize = "inherit";
            editor.style.background = "transparent";

            editor.value = cell.getValue() !== undefined && cell.getValue() !== null ? cell.getValue() : "";

            // 3. Tích hợp Datalist gợi nhớ (nếu có truyền editorParams.values)
            var datalist = null;
            if (editorParams && Array.isArray(editorParams.values) && editorParams.values.length > 0) {
                // Tạo ID duy nhất cho datalist của ô này
                var datalistId = "datalist_" + Math.random().toString(36).substring(2, 9);
                
                datalist = document.createElement("datalist");
                datalist.id = datalistId;

                // Đưa các mục gợi nhớ vào datalist
                editorParams.values.forEach(function(item) {
                    if (item) {
                        var option = document.createElement("option");
                        option.value = item;
                        datalist.appendChild(option);
                    }
                });

                document.body.appendChild(datalist);
                editor.setAttribute("list", datalistId);
                
            }

            // ... bên trong Tabulator.extendModule("edit", "editors", { erpAutoFit: ...
            onRendered(function () {
                editor.focus();
                // 1. Reset độ cao về 0 để ép trình duyệt tính toán lại scrollHeight từ đầu
                editor.style.height = "0px"; 
                // 2. Khai báo biến scrollHeight (đây là giá trị thực tế của nội dung)
                const currentContentHeight = editor.scrollHeight;
                // 3. Gán lại độ cao cho editor
                editor.style.height = currentContentHeight + "px";
                // 4. Chỉ normalizeHeight nếu nội dung thực sự dài (ví dụ > 35px)
                // Điều này ngăn chặn việc "cộng dồn" pixel khi di chuyển phím lên xuống
                if (currentContentHeight > 25) {
                    cell.getRow().normalizeHeight();
                }
            });
          

            // TỰ ĐỘNG NỞ DÒNG 
            editor.addEventListener("input", function () { // Dùng 'input' thay vì 'keyup' để bắt được cả hành vi Paste chuột phải. Không dùng //editor.style.height = "auto"; // > Tình trạng tự thêm chiều cao khi gõ
                editor.style.height = editor.scrollHeight + "px";   //📌1
                cell.getRow().normalizeHeight();                    //📌2
                cell.getTable().redraw();                           //📌3
                
            });


            // 🟢 CHẶN LỖI CRASH KHI PASTE TEXT
            editor.addEventListener("paste", function (e) {
                // Cho phép paste bình thường bên trong textarea nhưng chặn không cho sự kiện này nhảy ra ngoài làm loạn cấu trúc quản lý phím của Tabulator
                e.stopPropagation();
  
                // Đợi text paste vào xong xuôi (sau 10ms) thì ép tính lại độ cao của hàng
                setTimeout(() => {
                    editor.style.height = editor.scrollHeight + "px";
                    cell.getRow().normalizeHeight();
                }, 10);
            });

            // 🟢 XỬ LÝ KHI MẤT FORCUS
            editor.addEventListener("blur", function () {
                success(editor.value);
            });

   

            // 🟢 QUẢN LÝ VÀ CÁCH LY PHÍM BẤM TUYỆT ĐỐI (SỬA LỖI KEYDOWN/KEYUP)
            editor.addEventListener("keydown", function (e) {
                // Chặn phím Mũi tên lên (38) và Mũi tên xuống (40) không cho Tabulator can thiệp di chuyển hàng
                if (e.keyCode === 37 || e.keyCode === 39) {
                    // Nếu con trỏ văn bản đang ở đầu hoặc cuối ô text, hoặc ô trống không có chữ
                    // Thì cho phép sự kiện thoát ra ngoài để Tabulator di chuyển sang cell bên cạnh
                    if (editor.selectionStart === 0 && editor.selectionEnd === editor.value.length) {
                        // Cho phép Tabulator bắt sự kiện để dịch chuyển ô
                        return; 
                    }
                    // Ngược lại, nếu đang gõ dở giữa câu, phím mũi tên sẽ dùng để di chuyển con trỏ chữ nội bộ
                    e.stopPropagation();
                }

                // 2. Phím Mũi tên Lên (38) và Mũi tên Xuống (40)
                if (e.keyCode === 38 || e.keyCode === 40) {
                    // Cho phép thoát ra ngoài để di chuyển lên xuống giữa các dòng của bảng
                    return; 
                }

                // Phím Tab (keyCode 9)
                if (e.keyCode === 9) {
                    e.stopPropagation(); 
                    success(editor.value); // Lưu dữ liệu ô hiện tại trước


                }

                // Enter để lưu (trừ khi giữ Shift thì mới xuống dòng)
                if (e.keyCode === 13 && !e.shiftKey) {
                    e.preventDefault(); 
                    e.stopPropagation();
                    success(editor.value);

                }
                
                // Esc để hủy
                if (e.keyCode === 27) {
                    e.stopPropagation();
                    cancel();
                }
            });

            // 🟢 BỔ SUNG: Chặn luôn cả keyup lọt ra ngoài (Gốc rễ của lỗi nhảy về ô Checkbox)
            editor.addEventListener("keyup", function (e) {
                e.stopPropagation(); // Ép tất cả hành vi nhả phím chỉ nằm gói gọn trong textarea này
            });

            return editor;
        },

        // ✅  CHỈNH SỬA + LIST ĐÍNH KÈM DANH SÁCH CHỌN
        erpAutoFitList: function (cell, onRendered, success, cancel, editorParams) {
            var values = (editorParams && Array.isArray(editorParams.values)) ? editorParams.values : [];
            var currentValue = cell.getValue() || "";
            var cellElement = cell.getElement();
            var rowElement = cell.getRow().getElement();
            var isCommitted = false; // Cờ đánh dấu tránh commit trùng lặp

            // 1. Container cho Editor
            var container = document.createElement("div");
            container.style.width = "100%";
            container.style.height = "100%";
            container.style.position = "relative";

            // 2. TEXTAREA (Hỗ trợ nhập nhiều dòng, giữ độ cao ban đầu & tự co dãn)
            var textarea = document.createElement("textarea");

                textarea.value = currentValue;
                textarea.style.width = "100%";
                textarea.style.minHeight = Math.max(cellElement.clientHeight, 32) + "px";
                textarea.style.height = "100%";
                textarea.style.boxSizing = "border-box";
                textarea.style.outline = "none";
                textarea.style.fontFamily = "inherit";
                textarea.style.fontSize = "inherit";
                textarea.style.resize = "none";
                textarea.style.overflowY = "hidden";
                textarea.style.background = "transparent";

            // 3. Popup Dropdown được gắn trực tiếp vào BODY
            var dropdown = document.createElement("div");
                dropdown.style.position = "fixed";
                dropdown.style.maxHeight = "180px";
                dropdown.style.overflowY = "auto";
                dropdown.style.background = "#ffffff";
                //dropdown.style.border = "1px solid #cbd5e1";
                dropdown.style.borderRadius = "4px";
                //dropdown.style.boxShadow = "0 10px 15px -3px rgba(0,0,0,0.1)";
                dropdown.style.zIndex = "99";
                dropdown.style.display = "none";
                dropdown.style.scrollbarWidth = "thin"; // Thuộc tính chuẩn theo yêu cầu của bạn
                dropdown.style.scrollbarColor = "#cbd5e1 transparent"; // Tùy chỉnh màu thanh cuộn (Firefox/Chuẩn mới)
                document.body.appendChild(dropdown);

            // Vị trí Popup luôn bám theo đáy của Textarea
            function positionDropdown() {
                var rect = textarea.getBoundingClientRect();
                dropdown.style.top = (rect.bottom + 1) + "px";
                dropdown.style.left = (rect.left - 4) + "px";
                dropdown.style.width = (rect.width + 6) + "px";
            }

            // Render danh sách gợi ý
            function renderOptions(filterText) {
                dropdown.innerHTML = "";
                var filterVal = (filterText || "").trim().toLowerCase();

                var filtered = values.filter(function (item) {
                    return !filterVal || String(item).toLowerCase().indexOf(filterVal) !== -1;
                });

                if (filtered.length === 0) {
                    dropdown.style.display = "none";
                    return;
                }

                filtered.forEach(function (val) {
                    var itemDiv = document.createElement("div");
                    itemDiv.textContent = val;
                    itemDiv.style.padding = "4px 6px";
                    itemDiv.style.cursor = "pointer";
                    itemDiv.style.fontSize = "12px";

                    itemDiv.addEventListener("mouseenter", function () {
                        itemDiv.style.background = "#dbdbdb";
                    });
                    itemDiv.addEventListener("mouseleave", function () {
                        itemDiv.style.background = "#fff";
                    });

                    // Lựa chọn bằng Click chuột
                    itemDiv.addEventListener("mousedown", function (e) {
                        e.preventDefault(); // Giữ focus không bị mất khỏi textarea
                        textarea.value = val;
                        autoResize();
                        dropdown.style.display = "none";
                        commit(); // Commit ngay khi chọn item
                    });

                    dropdown.appendChild(itemDiv);
                });

                positionDropdown();
                dropdown.style.display = "block";
            }

            // 4. Tự động co dãn chiều cao cả TEXTAREA lẫn ROW
            function autoResize() {
                // Mở rộng chiều cao thẻ Hàng (Row) để không bị xén bớt
                rowElement.style.height = "auto";
                cellElement.style.height = "auto";

                textarea.style.height = "auto";
                var newHeight = textarea.scrollHeight;
                textarea.style.height = newHeight + "px";           //📌1
                positionDropdown(); // Cập nhật lại vị trí popup
            }

            // Xóa Popup khỏi DOM
            function destroyDropdown() {
                if (dropdown && dropdown.parentNode) {
                    dropdown.parentNode.removeChild(dropdown);
                }
            }

            // Commit dữ liệu về Tabulator chuẩn xác
            function commit() {
                if (isCommitted) return;
                isCommitted = true;

                dropdown.style.display = "none";
                destroyDropdown();

                var val = textarea.value;
                success(val); // Lưu giá trị vào Tabulator

                setTimeout(function () {
                    cell.getRow().normalizeHeight(); // Tính lại height chuẩn cho cả dòng
                    cell.getTable().redraw(); 
                }, 10);
            }

            function doCancel() {
                if (isCommitted) return;
                isCommitted = true;

                dropdown.style.display = "none";
                destroyDropdown();
                cancel();
            }

            // --- SỰ KIỆN VĂN BẢN (EVENTS) ---

            textarea.addEventListener("input", function () {
                autoResize();
                renderOptions(textarea.value);
            });

            textarea.addEventListener("focus", function () {
               
                renderOptions(textarea.value);
                autoResize();
            });

            textarea.addEventListener("click", function () {
                renderOptions(textarea.value);
            });

            textarea.addEventListener("blur", function (e) {
                // Cho phép mousedown trên Popup thực thi trước nếu user click vào item gợi ý
                setTimeout(function () {
                    if (!isCommitted) {
                        commit();
                    }
                }, 100);
            });

            textarea.addEventListener("keydown", function (e) {
                if (e.key === "Enter" && !e.shiftKey) {
                    // Enter để hoàn tất & lưu dữ liệu (Shift + Enter để xuống dòng)
                    e.preventDefault();
                    commit();
                } else if (e.key === "Escape") {
                    e.preventDefault();
                    doCancel();
                } else if (e.key === "Tab") {
                    commit();
                }
            });

            container.appendChild(textarea);

            onRendered(function () {
                textarea.focus();
                textarea.setSelectionRange(textarea.value.length, textarea.value.length);
                autoResize();
                renderOptions(textarea.value);
            });

            return container;
        }
                


    });
}

/* 
================================================================================
2. CLASS TABLEBASE
================================================================================ 
*/
export class TableBase {
    /* --- Hàm khởi tạo --- */
    constructor(selector, options = {}) {
        this.selector = selector;
        this.options = options;
        this.table = null;
        this.init();
    }

    /* --- Khởi tạo --- */
    init() {
        // Tách các sự kiện callback để xử lý qua .on() 
        const { cellEdited, 
                rowClick,
                rowSelectionChanged,
                autoAddRowOnLastClick = false,   
                ...pureOptions 
            } = this.options;

        this.table = new Tabulator(this.selector, {
            ...TABLE_DEFAULT_CONFIG,
            ...pureOptions
        });

        //🟢 Đăng ký các sự kiện mặc định của ERP
        if (cellEdited && typeof cellEdited === "function") {
            this.table.on("cellEdited", cellEdited);
        }

        //🟢ĐĂNG KÝ SỰ KIỆN CHỌN DÒNG CHO LÕI TABULATOR QUA HÀM .on() >>>>>>>>>>>>>>>> 
        // Sự kiện CLICK chọn checkbox của Formater
        if (rowSelectionChanged && typeof rowSelectionChanged === "function") {
            this.table.on("rowSelectionChanged", rowSelectionChanged);
        }

        // --- VỊ TRÍ THÊM: Xử lý sự kiện click dòng cuối tự tạo dòng mới 
        this.table.on("rowClick", (e, row) => {
            if (autoAddRowOnLastClick) {
                const rows = this.table.getRows();
                const lastRow = rows[rows.length - 1];

                // Nếu click đúng vào dòng cuối cùng
                if (row === lastRow) {
                    // 🟢 SỬ DỤNG SETTIMEOUT ĐỂ TÁCH LUỒNG: 
                    // Cho phép phím Tab hoàn thành việc dịch chuyển ô sang cột Số lượng trước, 
                    // sau đó mới tiến hành chèn dòng mới ở dưới cùng mà không gây mất Focus.
                    setTimeout(() => {
                        // Kiểm tra lại lần nữa phòng trường hợp dòng cuối đã thay đổi trong 50ms qua
                        const currentRows = this.table.getRows();
                        const currentLastRow = currentRows[currentRows.length - 1];
                        
                        // Nếu dòng được click vẫn đang là dòng cuối cùng thì mới thêm dòng
                        if (row === currentLastRow) {
                            this.addRow({}, false); 
                        }
                    }, 50); 
                }
            }

            // Nếu phía ngoài giao diện vẫn truyền hàm rowClick riêng, chạy song song nó luôn
            if (rowClick && typeof rowClick === "function") {
                rowClick(e, row);
            }
        });
    }

    /* --- Hệ thống API rút gọn cho ERP --- */
    
    // Lấy toàn bộ dữ liệu bảng
    getData() { 
        return this.table.getData(); 
    }

    // Cập nhật dữ liệu mới cho bảng
    setData(data) { 
        return this.table.setData(data); 
    }

    // Thêm dòng mới (mặc định lên đầu bảng nếu không truyền vị trí)
    addRow(row = {}, addToTop = true) { 
        return this.table.addRow(row, addToTop); 
    }

    // Lấy danh sách các dòng đang được tích chọn (Checkbox)
    getSelected() { 
        return this.table.getSelectedData(); 
    }

    // Xóa sạch dữ liệu trong bảng
    clear() { 
        this.table.clearData(); 
    }

    // Cập nhật lại layout bảng (Hữu ích khi đóng/mở sidebar hoặc resize cửa sổ)
    redraw() {
        this.table.redraw();
    }
}