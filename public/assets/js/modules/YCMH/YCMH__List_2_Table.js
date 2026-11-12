
import { TableBase } from "../../core/tableERP/tableERPBase.js";


/* HIGHLIGHT TEXT
🟡 --●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--● */
function highlightText(text, keyword) {
    if (!text) return "";
    
    // 1. Loại bỏ các thẻ <mark> cũ (nếu có) để lấy lại text nguyên bản
    const rawText = String(text).replace(/<\/?mark>/gi, "");

    // 2. Kiểm tra từ khóa hợp lệ
    if (!keyword || keyword.trim().length < 1) return rawText;

    // 3. Escape các ký tự đặc biệt trong keyword
    const escapedKeyword = keyword.trim().replace(/[-\/\\^$*+?.()|[\]{}]/g, "\\$&");
    const regex = new RegExp(`(${escapedKeyword})`, "gi");

    // 4. Highlight trên chuỗi sạch
    return rawText.replace(regex, "<mark>$1</mark>");
}


/* TẠO LIST & SỰ KIỆN HighLight
🟡 --●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--● */
export function INIT_YCMH_Table(IdElement) {
    return new TableBase(IdElement, {
        layout: "fitColumns",
        headerVisible: false,
        selectable: 1,
        height: "100%",
        pagination: "local",      // Bật phân trang Client-side
        paginationSize: 50,       // 50 record / trang

        footerElement:footerDOM,  // DOM được khởi tạo phía dưới

        columns: [
            {
                field: "YCMH_name",
                resizable: false,
                widthGrow: 1,
                formatter: function (cell) {
                    const data = cell.getData();
                    const keyword = document.getElementById("YCMH_Search")?.value.trim() || "";
                    
                    /* const keyword = filterState.search || ""; */
                    const highlightedName     = typeof highlightText === "function" ? highlightText(data.YCMH_name, keyword) : (data.YCMH_name || "");
                    const highlightedBoPhan   = typeof highlightText === "function" ? highlightText(data.YCMH_bophan, keyword) : (data.YCMH_bophan || "");
                    const highlightedNhanVien = typeof highlightText === "function" ? highlightText(data.YCMH_nhanvien, keyword) : (data.YCMH_nhanvien || "");

                    let statusClass = "status-default";
                    if (data.YCMH_status === "Chờ phê duyệt")   statusClass = "status-pending";
                    if (data.YCMH_status === "Đã phê duyệt")    statusClass = "status-approved";
                    if (data.YCMH_status === "Bị từ chối")      statusClass = "status-rejected";

                    return `
                        <div class="outlook-card-row">
                            <div class="card-line-1">
                                <span class="card-name">${highlightedName}</span>
                                <span class="card-status ${statusClass}">${data.YCMH_status || ""}</span>
                            </div>
                            <div class="card-line-2">
                                <div class="cardleft">
                                    <span class="card-member">${highlightedNhanVien}</span>
                                    <div class="card-bp"><span>${highlightedBoPhan}</span></div>
                                    <span class="card-item">${data.YCMH_ITEM || ""}</span>
                                </div>
                                <span class="card-date">${data.YCMH_date || ""}</span>
                            </div>
                        </div>`;
                }
            }
        ]
    });
}


/* Tạo DOM Element cho Footer STATUS PAGE
🟡 --●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--● */
const footerDOM = document.createElement("div");
footerDOM.style.fontFamily = "var(--font-main)";
footerDOM.innerHTML = `
    <div class="tbl_footer" style="display: flex; 
                                        justify-content: space-between;
                                        align-items: center;
                                        font-size: 11px; 
                                        box-sizing: border-box; 
                                        padding: 2px 0px 2px 10px; 
                                        width: 100%;
                                        font-weight: bold;
                                        background-color: rgb(243, 243, 243);
                                        ">
                                        

        <span id="TBL_FOOTER_L">Count total: 0 </span>
        <div id="TBL_FOOTER_R" style="display: flex; align-items: center;">
            <button id="BTN_PREV" class="btn_sm" style="color: #ff0432; font-size: 10px;"> 
                ❮
            </button>
            <span style="padding: 0px 5px;">
                Page 
            </span>

            <input type="text" id="INPUT_PAGE_NUM"class="Box_Input" value="1" 
                style="
                    text-align: center;
                    box-sizing: border-box; 
                    padding:0px; 
                    font-size:11px; 
                    font-weight:700;
                    height: 20px !important; 
                    fon-family: var(--font-main);
                    max-width: 30px;"> 

            <span id="PAGE_NUM" style="padding: 0px 5px;">
                of total Pages
            </span>

            <button id="BTN_NEXT" class="btn-sm" style="color: #ff042a; font-size: 10px;">
                ❯
            </button>
        </div>
    </div>
`;






