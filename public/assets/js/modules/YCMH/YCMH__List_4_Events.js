
import {FINTER_CENTER} from "./YCMH__List_3_FilterController.js";
import {debounce} from "../../core/Utils/helpers.js";
//NEW FETCH API 
import {apiFetch}     from "../../core/request/fetch_Api.js";
import {showApiError,ConverErrToDataSwal} from "../../core/request/fetch_ApiShowError.js";




let inputTimer = null;


export async function Bind_YCMH_Events(coreTable, state) {
        
    //🟡 Sự kiện Click chọn dòng
        coreTable.on("rowClick", function (e, row) {
            const data = row.getData();
            // Kiểm tra dòng vừa click HIỆN TẠI có đang được select hay không
            const isSelected = row.isSelected(); 

            if (isSelected) {
                if (typeof ROW_SELECT === "function") ROW_SELECT(data);
            } else {
                if (typeof ROW_UNSELECT === "function") ROW_UNSELECT(data);
            }
        });


    //🟡 Ô Tìm kiếm (Search Input)
    /* 
    Người dùng thao tác trên ô searchInput
    │
    ├── [Lần đầu gọi hàm khởi tạo] ──> Kiểm tra dataset.bound?
    │                                   ├─ Chưa có: Đánh dấu dataset.bound = "true" & Gán Event Listener
    │                                   └─ Đã có: Bỏ qua (Tránh gán lặp sự kiện)
    │
    └── [Người dùng gõ phím] ──> Sự kiện "input" kích hoạt
                                    │
                                    ├── Kiểm tra e.isTrusted? 
                                    │    └─ False (Gõ bằng code/tool): Bỏ qua (return)
                                    │
                                    └── True (Người dùng thật gõ):
                                            ├─ Cập nhật giá trị vào state.search
                                            │
                                            ├─ Nếu ô tìm kiếm RỖNG (""):
                                            │    ├─ Hủy bỏ Timer hoãn của Debounce (nếu có)
                                            │    └─ Lọc ngay lập tức: Gọi FINTER_CENTER(coreTable, state)
                                            │
                                            └─ Nếu ô tìm kiếm CÓ CHỮ:
                                                └─ Chờ 300ms dừng gõ mới lọc: Gọi debouncedFilter()
    */
    const searchInput = document.getElementById("YCMH_Search");

    if (searchInput) {
        // 1. Dùng flag chống gán lặp Listener nếu hàm Bind bị gọi nhiều lần
        //.dataset.bound thuộc tính kiểm tra đã liên kết (bound) với sự kiện nào chưa
        if (!searchInput.dataset.bound) {
            searchInput.dataset.bound = "true";
            

            // 2. Tạo hàm debouncedFilter có khả năng cancel
            const debouncedFilter = debounce(() => FINTER_CENTER(coreTable, state), 300);

            searchInput.addEventListener("input", function (e) {
                // 3. Chỉ nhận sự kiện do người dùng gõ thực tế
                if (!e.isTrusted) return;

                const value = e.target.value.trim().toLowerCase();
                state.search = value;

                if (value === "") {
                    // 4. Hủy ngay timer đang hoãn (nếu có) để không bị gọi đè lần 2
                    if (typeof debouncedFilter.cancel === "function") {
                        debouncedFilter.cancel();
                    }
                    FINTER_CENTER(coreTable, state);
                } else {
                    debouncedFilter();
                }
            });
        }
    }
    ///////////////////////////////////////////////////////////////////////////////
    //🟡 SELECT TRANG THÁI (Status Select Box)
    const statusSelect = document.getElementById("YCMH_Select_Part");
    if (statusSelect) {
        statusSelect.addEventListener("change", function (e) {
            state.status = e.target.value;
            
            FINTER_CENTER(coreTable, state);
        });
    }
    ///////////////////////////////////////////////////////////////////////////////
    //🟡 Nút Sort Mũi tên (Tăng / Giảm)
    const btnSort = document.getElementById("btn_YCMH_Sort_Direction");
    if (btnSort) {
        btnSort.addEventListener("click", function () {
            state.isAscending = !state.isAscending;
            this.style.transform = state.isAscending ? "rotate(180deg)" : "rotate(0deg)";
            coreTable.setSort("YCMH_name", state.isAscending ? "asc" : "desc");
        });
    }
    ///////////////////////////////////////////////////////////////////////////////
    // 🔴 Menu Lọc Phòng Ban (Multi-select Dept Dropdown)
    
    bindDeptDropdownEvents(coreTable, state);
    bindFooterEvents(coreTable);
}
//🟡EVENT DROPDOWN_TOGGLE
//**********************************************************************************************************
function bindDeptDropdownEvents(coreTable, state) {
    const deptToggle = document.getElementById("YCMH_Dept_Dropdown_Toggle");
    const deptMenu   = document.getElementById("YCMH_Dept_Menu");
    const deptLabel  = document.getElementById("YCMH_Filter_Department");
    //📌CHK
    if (!deptToggle || !deptMenu) return;

    //📌Nếu menu đang mở thì đóng & ngược lại
    deptToggle.addEventListener("click", (e) => {
        e.stopPropagation();
        deptMenu.style.display = deptMenu.style.display === "block" ? "none" : "block"; 
    });

    //📌 Khi Click ra ngoài thì tự ản menu
    document.addEventListener("click", () => (deptMenu.style.display = "none"));

    //📌 Đăng ký sự kiện Click cho từng Tùy chọn Phòng ban
    deptMenu.querySelectorAll(".sort-item").forEach((item) => {
        item.addEventListener("click", function (e) {
            e.stopPropagation();
            const deptVal = this.getAttribute("data-dept"); //Lấy giá trị của thuộc tính tùy chỉnh data-dept trên item vừa được click

            if (deptVal === "all") {
                state.depts = [];
                deptMenu.querySelectorAll(".sort-item").forEach((i) => i.classList.remove("active"));
                this.classList.add("active");
            } else {
                const allItem = deptMenu.querySelector('[data-dept="all"]'); // Xóa active all
                if (allItem) allItem.classList.remove("active");

                //----------------------------------------------------- XÓA ALL
                state.depts = [];
                deptMenu.querySelectorAll(".sort-item").forEach((i) => i.classList.remove("active"));
                //----------------------------------------------------- XÓA ALL


                this.classList.toggle("active"); //Bật/tắt class active trên mục vừa chọn

                if (this.classList.contains("active")) {    //Nếu mục này vừa được kích hoạt
                    state.depts.push(deptVal);              //Thêm mã phòng ban vào mảng state.depts
                } else {//Nếu mục này vừa bị bỏ chọn
                    state.depts = state.depts.filter((d) => d !== deptVal); //Lọc và loại bỏ phòng ban đó ra khỏi mảng state.depts
                }

                //Trường hợp người dùng bỏ chọn tất cả các phòng ban lẻ, mảng state.depts trở thành rỗng. 
                // Lúc này tự động bật lại trạng thái active cho mục "Tất cả".
                if (state.depts.length === 0 && allItem) {
                    allItem.classList.add("active");
                }
            }

            // Update UI Button Text
           // Ý nghĩa: Thay đổi văn bản hiển thị trên nút chọn tùy theo số lượng phòng ban đang chọn:
           // Không chọn mục riêng lẻ nào (0): Hiển thị "All Department".
           // Chọn đúng 1 phòng ban (1): Hiển thị tên phòng ban đó (ví dụ: "IT").
           // Chọn từ 2 phòng ban trở lên: Hiển thị số lượng (ví dụ: "3 Depts Selected").

            if (state.depts.length === 0) deptLabel.innerText = "All";
            else if (state.depts.length === 1) deptLabel.innerText = state.depts[0];
            else deptLabel.innerText = `${state.depts.length} Depts Selected`;

            // Trigger Filter & Hide
            FINTER_CENTER(coreTable, state);    // Gọi hàm filter
            deptMenu.style.display = "none";    // Close Menu
        });
    });
}



//🟡EVENT FOOTER
//**********************************************************************************************************
function bindFooterEvents(coreTable) {
    //1.1 UPDATE UI Footer
    const ReloadFooterUI = () => {
        requestAnimationFrame(() => {
            //CHECK
            const footerDOM = document.querySelector(".tbl_footer") || coreTable.element?.querySelector(".tbl_footer");
                if (!footerDOM) {console.warn("⚠️ Không tìm thấy .tbl_footer để gán footerDOM!"); return;}

            const totalRows     = coreTable.getDataCount("active") || 0;
            const rawPage       = coreTable.getPage();
            const currentPage   = (typeof rawPage === "number") ? rawPage : 1;
            const maxPage       = coreTable.getPageMax() || 1;

            const totalEl   = footerDOM.querySelector("#TBL_FOOTER_L");
            const inputEl   = footerDOM.querySelector("#INPUT_PAGE_NUM");
            const maxPageEl = footerDOM.querySelector("#PAGE_NUM");

            if (totalEl) totalEl.innerText = `Count total: ${totalRows}`;
            if (maxPageEl) maxPageEl.innerText = `/ ${maxPage}`;
            
            if (inputEl && document.activeElement !== inputEl) {
                inputEl.value = currentPage;
            }
            
        });
    };


    //1.2 Đăng ký Event Hooks duy nhất 1 lần cho coreTable
    if (!coreTable._footerEventsBound) {
        coreTable._footerEventsBound = true;
        coreTable.on("renderComplete", ReloadFooterUI);
        coreTable.on("pageLoaded", ReloadFooterUI);
        coreTable.on("dataFiltered", ReloadFooterUI);
    }

    // 2. GÁN SỰ KIỆN TRỰC TIẾP CHO DOM Footer
    const AttackEventsToDOM = () => {
        const footerDOM = document.querySelector(".tbl_footer") || coreTable.element?.querySelector(".tbl_footer");
        
        if (!footerDOM) {
            // Liên tục thử lai để xử lý bất đồng bộ khi DOM chưa kịp xuất hiện trong HTML.
            if (!AttackEventsToDOM.retry) AttackEventsToDOM.retry = 0;
            if (AttackEventsToDOM.retry < 10) {
                AttackEventsToDOM.retry++;
                setTimeout(AttackEventsToDOM, 100);
            } else {
                console.warn("⚠️ Không tìm thấy .tbl_footer để gán sự kiện!");
            }
            return;
        }
        AttackEventsToDOM.retry = 0;

        const inputEl   = footerDOM.querySelector("#INPUT_PAGE_NUM");
        let btnPrev     = footerDOM.querySelector("#BTN_PREV");
        let btnNext     = footerDOM.querySelector("#BTN_NEXT");

        // Hàm nhảy trang bằng chỉ số trang (Hoạt động ổn định hơn nextPage/previousPage)
        const changePageByStep = (step) => {
            const curr  = coreTable.getPage() || 1;
            const max   = coreTable.getPageMax() || 1;
            let target  = curr + step;

            if (target < 1)     target = 1;
            if (target > max)   target = max;

            if (target !== curr) {
                coreTable.setPage(target).then(() => {
                    ReloadFooterUI();
                }).catch(err => {
                    console.error("Lỗi chuyển trang Tabulator:", err);
                });
            }
        };

        //🔴--- Gán Nút PREV ---
        if (btnPrev) {
            // Clone Node để xóa bỏ toàn bộ listener cũ bị kẹt trên DOM
            const newBtnPrev = btnPrev.cloneNode(true);
            btnPrev.parentNode.replaceChild(newBtnPrev, btnPrev);

            newBtnPrev.addEventListener("click", (e) => {
                e.preventDefault();
                changePageByStep(-1);
            });
        }

        //🔴 --- Gán Nút NEXT ---
        if (btnNext) {
            // Clone Node để xóa bỏ toàn bộ listener cũ
            const newBtnNext = btnNext.cloneNode(true);
            btnNext.parentNode.replaceChild(newBtnNext, btnNext);

            newBtnNext.addEventListener("click", (e) => {
                e.preventDefault();
                changePageByStep(1);
            });
        }

        //🔴 --- Gán Ô Input ---
        if (inputEl) {
            // Clone Node để xóa toàn bộ event listeners cũ bị kẹt
            const newInputEl = inputEl.cloneNode(true);
            inputEl.parentNode.replaceChild(newInputEl, inputEl);

            // Hàm nhảy trang dùng chính newInputEl (phần tử đang nằm trên DOM)
            const jumpToPageFromInput = () => {
                if (inputTimer) clearTimeout(inputTimer);
                const maxPage = coreTable.getPageMax() || 1;
                let val = parseInt(newInputEl.value, 10);

                if (isNaN(val) || val < 1) val = 1;
                else if (val > maxPage) val = maxPage;

                newInputEl.value = val;
                coreTable.setPage(val).catch((err) => {
                    console.error("Lỗi setPage từ Input:", err);
                });
            };

            // A. Lọc phím gõ & Xử lý Enter
            newInputEl.addEventListener("keydown", function (e) {
                const allowedKeys = ["Backspace", "Delete", "Tab", "Escape", "Enter", "ArrowLeft", "ArrowRight"];
                
                if (e.key === "Enter") {
                    e.preventDefault();
                    newInputEl.blur(); // Tự động trigger sự kiện 'blur' ở dưới
                    return;
                }

                if (allowedKeys.includes(e.key) || e.ctrlKey || e.metaKey) return;

                // Chặn ký tự không phải số
                if (!/^[0-9]$/.test(e.key)) {
                    e.preventDefault();
                }
            });

            // B. Debounce tự động nhảy trang sau 1s khi dừng gõ
            newInputEl.addEventListener("input", function () {
                this.value = this.value.replace(/[^0-9]/g, "");
                
                if (inputTimer) clearTimeout(inputTimer);

                if (this.value.trim() !== "") {
                    inputTimer = setTimeout(jumpToPageFromInput, 500);
                }
            });

            // C. Nhảy trang khi click ra ngoài (Blur)
            newInputEl.addEventListener("blur", jumpToPageFromInput);
        }

        ReloadFooterUI();
    };

    AttackEventsToDOM();
}





//🔴HÀNH ĐỘNG EVENT SELECT ROW
//**********************************************************************************************************
async function ROW_SELECT(data){
    SWITCH_FRAME(true);
    
    const YCMH_Pages = document.getElementById("CONTAINER_DETAIL");
    if (!YCMH_Pages) return;
    const dataUP = data;
    console.log('data', data)
    //🟢 DỰNG KHUNG HD
    YCMH_Pages.innerHTML=`
        <!-- ---------------------------------------------- -->
        <div id="TABRIGHT_HD">
            <div class="HD_line1">
                <div class="HD_line1_option">  <button class="btn" id="btnBACK">
                                                    <svg class="icon-left"><use href="assets/icon/icons.svg#ico-quaylai"></use></svg>
                                                    <span>Quay lại / Back</span>
                                                </button> 
                </div>
                <div class="HD_line1_option">  <button class="btn" id="btnNEW">
                                                    <svg class="icon-left"><use href="assets/icon/icons.svg#ico-add1"></use></svg>
                                                    <span> Tạo phiếu yêu cầu mới / CREATE NEW REQUEST </span>
                                                </button> 
                </div>
                <div class="HD_line1_option">  </div>
                <div class="HD_line1_option">   </div>
            </div>
            <!-- ~~~~~~~~~~~~~ -->
            <div class="HD_line2">
                <div class="HD_line2_L">${data.YCMH_name || ""}</div>
                <!-- +++++++++++++++++ -->
                <div class="HD_line2_R">
                    <div class="HD_line2_R1">
                        <div class="HD_line2_R1_STATUS">${data.YCMH_status || ""}</div>
                    </div>
                    <!-- +++++++++++++++++ -->  
                    <div class="HD_line2_R2">
                        <div class="HD_line2_R2_noidung">Tạo bởi/Createby :</div>
                        <div class="HD_line2_R2-User HD_line2_R2_noidung">${data.YCMH_nhanvien || ""}</div>
                        <div class="HD_line2_R2_noidung">Bộ phận/Department  :</div> 
                        <div class="HD_line2_R2-Part HD_line2_R2_noidung">${data.YCMH_bophan || ""}</div>
                        <div class="HD_line2_R2_noidung">Nơi nhận/Delivery :</div> 
                        <div class="HD_line2_R2-Delivery HD_line2_R2_noidung">ITV</div>

                        <div class="HD_line2_R2-time HD_line2_R2_noidung">${data.YCMH_date}</div>
                    </div> 
                </div>
            </div>
        </div>
        <!-- ---------------------------------------------- -->
        <div id="TABRIGHT_CONTENTS">
            <div id="duyetYCMH_WRAP">
                <!-- ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ -->
                <div id="NOIDUNGPHIEU" class="phieu-frame">
                    
                </div>
                <!-- ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ -->
                <div id="FOOTER">

                </div>
            </div>    

        </div>
    `;

    //LOAD DOM XONG CHẠY LOAD DATA
    //let mhhd_name = dataUP.YCMH_name;
    let mhhd_id = dataUP.YCMH_id;
    let mhhd_name = dataUP.YCMH_name;
    let payload = {
        mhhd_name,
        mhhd_id,
    };

    try {
		//🔴SEND NEW apiFetch
		const RES =  await apiFetch("/api/modules/YCMH__List_4_Events_OrderDetail", {
			method: "POST",
			body: payload, //KHÔNG CẦN Stringify 
		});   
        const KQ = RES.dataRep;
        let     ContentHtml = GET_HTML_RENDER_CONTENT();
        const   ContentStyle = GET_HTML_RENDER_STYLE();
        
        //REPALCE html
        const fontPath = window.location.origin + '/assets/fonts';
        const LOGO_SRC = window.location.origin + '/assets/img/ITV128x128.png';

        const data = {
            'FONT_PATH' : fontPath, //✔️                
            'LOGO_SRC'  : LOGO_SRC, //✔️
            'NGAY_TAO'          : KQ.NGAY_TAO,  //✔️
            'NGUOI_TAO'         : KQ.NGUOI_TAO, //✔️
            'MA_NV'             : KQ.MA_NV,     //✔️
            'BO_PHAN'           : KQ.BO_PHAN,   //✔️
            'DELIVERY'          : KQ.DELIVERY,  //✔️
            'SO_PHIEU'          : KQ.SO_PHIEU,  //✔️
            'GHI_CHU'           : KQ.GHI_CHU,   //✔️
            'TABLE_DETAIL_ROWS' : KQ.TABLE_DETAIL_ROWS, //✔️
            'APPROVAL_ROWS'     : KQ.APPROVAL_ROWS,     //✔️
            'ATT_FILE'          : KQ.ATT_FILE,          //✔️
        }                   
        console.log('🔴12')
        for (const [key, value] of Object.entries(data)) {
            const regex = new RegExp(`{{${key}}}`, 'g');
            ContentHtml = ContentHtml.replace(regex, value ?? '');
        }
        console.log('🔴13')
        //gán Html vào id
        
        let NOIDUNGPHIEU = document.getElementById('NOIDUNGPHIEU');
        
        NOIDUNGPHIEU.innerHTML = `${ContentHtml}` ;
        
        // STYLE
        const newStyle = document.createElement('style');
        newStyle.id = 'custom-SelectedRow-style';
        newStyle.textContent = `${ContentStyle}`;

        const linkElement = document.getElementById('ikka-module-css');
            if (linkElement) {
                linkElement.parentNode.insertBefore(newStyle, linkElement.nextSibling);
            } else {
                document.head.appendChild(newStyle);
            }

	} catch (e) {
		const errorResult =  Swal.fire(
			ConverErrToDataSwal(e)
		);
		if (errorResult.isConfirmed) {  
			return;// nếu cần ngắt code
		}
	}

    //ĐĂNG KÝ SỰ KIỆN CHO CÁC BTN
    const btnBACK =document.getElementById("btnBACK");
    btnBACK.addEventListener("click", function() {
        SWITCH_FRAME(false);
    });

    const btnNEW =document.getElementById("btnNEW");
    btnNEW.addEventListener("click", function() {
        location.hash ="createYCMH";
    });

}//❌❌❌❌❌ End Function 




//🔵 GET_HTML_RENDER_CONTENT
function GET_HTML_RENDER_CONTENT() {
    let htmlTemplate = `
        <table class="table_form_header">
            <thead></thead>
            <tbody>
                <tr>
                    <td>
                        <div id="PHIEU_HEADER"></div>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <table class="table_info">
            <tr>
                <td class="col-left">
                    <img class="LogoITV" src="{{LOGO_SRC}}" alt="Logo">
                    <!-- <img class="LogoITV" src="../../../../assets/img/ITV128x128.png" alt="Logo">  -->
                </td>

                <td class="col-mid">
                    <h3 class="ikka ikka1">CÔNG TY TNHH CÔNG NGHỆ IKKA (VIỆT NAM)</h3>
                    <h5 class="ikka ikka2">KCN TÂN TRƯỜNG, XÃ MAO ĐIỀN, TP HẢI PHÒNG</h5>
                    <h4 class="ikka ikka3">PHIẾU ĐỀ NGHỊ MUA HÀNG HÓA & DỊCH VỤ</h4>
                    <h5 class="ikka ikka4">REQUEST PURCHASE ORDER GOODS & SERVICES</h5>

                    <table class="top_info_M_table">
                        <tr>
                            <td>NGÀY TẠO PHIẾU / <span class="descrip">Date</span></td>
                            <td class="creator">{{NGAY_TAO}}</td>
                        </tr>
                        <tr>
                            <td>NGƯỜI LÀM PHIẾU / <span class="descrip">Created by</span></td>
                            <td class="creator">{{NGUOI_TAO}}</td>
                        </tr>
                        <tr>
                            <td>MÃ NHÂN VIÊN / <span class="descrip">Member code</span></td>
                            <td class="creator">{{MA_NV}}</td>
                        </tr>
                        <tr>
                            <td>BỘ PHẬN / <span class="descrip">Department</span></td>
                            <td class="creator">{{BO_PHAN}}</td>
                        </tr>
                        <tr>
                            <td>ĐIỂM GIAO HÀNG / <span class="descrip">Delivery</span></td>
                            <td class="creator">{{DELIVERY}}</td>
                        </tr>
                    </table>
                </td>

                <td class="col-right">
                    <p class="SophieuLabel">SỐ PHIẾU / REQUEST NO</p>
                    <div class="SophieuNum">{{SO_PHIEU}}</div>
                </td>
            </tr>
        </table>

        <div class="FormISO">
            <span class="descrip">ICS-ST-002/F-001(REV04-05.25)</span>
        </div>

        <!-- Bảng sản phẩm -->
        <table class="table_detail_header" style="width: 100%; table-layout: fixed;">
            <thead>
                <tr>
                    <th style="width: 3%;">     NO</th>
                    <th style="width: 30%;">    TÊN HÀNG HÓA DỊCH VỤ</th>
                    <th style="width: 8%;">     SỐ LƯỢNG</th>
                    <th style="width: 7%;">     ĐƠN VỊ</th>
                    <th style="width: 25%;">    MÔ TẢ</th>
                    <th>                        MỤC ĐÍCH</th>
                    <th style="width: 10%;">    DỰ KIẾN</th>
                </tr>
            </thead>
            <tbody>
                {{TABLE_DETAIL_ROWS}}
            </tbody>
        </table>

        <h5>GHI CHÚ</h5>
        <div class="descrip" style="margin-bottom: 8px;">{{GHI_CHU}}</div>

        <h5>TÀI LIỆU ĐÍNH KÈM</h5>
        <table style="width: 100%; border-collapse: collapse; border: none;">
            <tr>
                <td style="padding: 3px 0; border: none;">
                    <table style="width: auto; border-collapse: collapse; border: none; margin: 0;">
                        {{ATT_FILE}}            
                    </table>
                </td>   
            </tr>
        </table>

        <h5>THÔNG TIN QUY TRÌNH KÝ DUYỆT <span class="descrip">(Processing info)</span></h5>
        <table class="app-table">
            <thead>
                <tr>
                    <th style="width: 3%;"  >NO</th>
                    <th style="width: 20%;" >NGƯỜI DUYỆT</th>
                    <th style="width: 6%;"  >MÃ NV</th>
                    <th style="width: 15%;">CHỨC VỤ</th>
                    <th style="width: 8%;">KIỂU DUYỆT</th>
                    <th style="width: 12%;">TRẠNG THÁI</th>
                    <th style="width: 15%;">THỜI GIAN DUYỆT</th>
                    <th style="width: auto;">GHI CHÚ</th>
                </tr>
            </thead>
            <tbody>
                {{APPROVAL_ROWS}}
            </tbody>
        </table>
        <!-- Action -->
        <h5 id="Action_title_h5"><span id="Action_title_span" class="descrip"></span></h5>
        <table class="app-table">
            <thead>
            </thead>
            <tbody>
                <tr>
                    <div id="AppForm" class="appform">
                        
                        <div class="app_button">
                            <button class="Atcbtn" id="BTN_dongy" style="margin-top: 0px;" > Duyệt phiếu này/ Approve </button>
                            <button class="Atcbtn" id="BTN_huy" style="margin-top: 8px; color: rgb(233, 99, 99)" > Hủy phiếu / Cancel </button>
                        </div>

                        <div id="App_comment" class="app_note">
                            <textarea id="TextComment" spellcheck="false" rows="3" placeholder="Nhập ghi chú ở đây! (input comment here!) :..."></textarea>
                        </div>

                    </div>    
                </tr>
            </tbody>
        </table>
    `;
    return htmlTemplate;
}


//🔵 GET_HTML_RENDER_CONTENT
function GET_HTML_RENDER_STYLE() {
    let StyleTemplate =  `
        #NOIDUNGPHIEU {
                font-family: "Roboto Condensed", "DejaVu Sans", sans-serif;
                font-size: 10pt;
                color: #000000;
                display: flex;
                flex-direction: column;
            
                padding: 10px;
                
                line-height: 1.2;
                width: 200mm;
                max-width: 200mm;
                
                margin: auto;
                background-color: #ffffff !important;
            
                box-sizing: border-box;
            }
        table { 
            border-collapse: collapse; 
            table-layout: fixed;
            border-spacing: 0; 
            width: 100%; 
            
        }



        thead   { display: table-header-group; }
            tr  { page-break-inside: avoid; }

        .descrip { font-style: italic; font-size: 10px; color: #484848; }

        h5 { font-size: 11px; font-weight: bold; margin: 12px 0 4px 0; color: #222; text-transform: uppercase; }


        /* 🔴 HEADER */
        #PHIEU_HEADER {
            display: none;
            width: 100%;
            height: max-content;
            max-height: 30px;
            padding: 5px 10px;
            background-color: rgb(12, 40, 24) !important;
            box-sizing: border-box;
            justify-content: center;
            align-items: center;
            margin: auto;
            margin-top: 0;
            margin-bottom: 10px;
        }
        .HEADER-colunm1,
        .HEADER-colunm2,
        .HEADER-colunm3 {
            flex: 1;
            display: flex;
            
        }

        .btn_BACK {
            height: 100%;
            padding: 0px 10px;
            box-shadow: 0px 0px 0px 0.5px white;
        }
        .btn_BACK:hover {
            box-shadow: 0px 0px 0px 1px rgb(255, 255, 255);
            transition: all 0.3s ease-in-out;
            
        }

        .btn_BACK a {
            font-size: 11px;
            font-weight: 500;
            color: whitesmoke !important;
            text-decoration: none;
        }
        .btn_BACK a:hover{font-weight: 700;}









        /* 🟢 TABLE INFO */
            .table_info { width: 100%; margin-bottom: 5px; }
            .table_info td { vertical-align: top; padding: 0; }

            .col-left { width: 20%; }
                .LogoITV { width: 60px; height: auto; object-fit: contain; }
                
            .col-mid { width: 60%; text-align: center; }
                .ikka  { margin: 0; padding: 0; }
                .ikka1 { font-size: 15px; font-weight: 700; }
                .ikka2 { font-size: 10px; font-weight: 500; }
                .ikka3 { font-size: 13px; font-weight: 700; margin-top: 4px; }
                .ikka4 { font-size: 9px; font-weight: 400; font-style: italic; }

                .top_info_M_table       { font-size: 10px; width: auto; margin: 8px auto 0 auto; }
                .top_info_M_table td    { padding: 2px 4px; text-align: left; font-weight: 500; border: none; }

                    .creator { font-weight: 500; }
            /* RIGHT */
            .col-right { width: 20%; text-align: center; vertical-align: middle !important;}
                .SophieuLabel { font-size: 10px; font-weight: bold; margin: 0; text-align: center; }
                .SophieuNum { font-size: 14px; font-weight: 900; margin-top: 2px;text-align: center; }

        /* 🟢 DIV FormISO */
            .FormISO { text-align: right; font-size: 9.5px; font-weight: 500; margin-bottom: 10px; } 

        /* 🟢 TABLE table_detail_header */
            .table_detail_header { width: 100%; border-collapse: collapse; margin-bottom: 10px; table-layout: fixed !important;}
            
            .table_detail_header th, 
            .table_detail_header td { border: 0.5px solid #929292; padding: 2px 1px 2px 5px;  /* overflow: hidden; */}
            
            .table_detail_header th { font-size: 11px; background-color: #e7e7e7 !important; font-weight: 700; padding: 2px 1px 2px 1px;}
            .table_detail_header td { font-size: 11px; font-family:'Segoe UI', 'Roboto Condensed', 'DejaVu Sans', sans-serif; word-wrap: break-word;}



        /* 🟢 TABLE ATT_FILE */    
            .att_file_item{
                margin: 0;
                max-width: fit-content;
            }

            .att_file_info{min-width: max-content; }

        /* 🟢 TABLE app-table */
            .app-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; table-layout: fixed; margin-top: 5px;  }
            .app-table th, 
            .app-table td { border: 0.5px solid #c2c0c0; padding: 1px 5px; vertical-align: middle; word-wrap: break-word;}

            .app-table th { font-size: 11px; background-color: #e7e7e7 !important; font-weight: bold; text-align: center; padding: 2px 0px;}
            .app-table td { font-size: 11px; font-family:'Segoe UI', 'Roboto Condensed', 'DejaVu Sans', sans-serif; word-wrap: break-word;}
        
        
        /* 🟢 APPROVAL_FORM */
        #AppForm {
            display: flex;
            width: 100%;
            box-sizing: border-box;
            margin-top: 25px;
            gap: 5px;

        }  
        .appform.IsLocked{
            pointer-events: none;
            user-select: none;
            opacity: 0.7;
            position: relative;
        }
        .app_button {
           display: flex; 
           flex-direction: column;
         
        } 
            .Atcbtn{
                height: 22px;
                font-family: "Roboto Condensed", "DejaVu Sans", sans-serif;
                font-size: 11px;
                font-weight: 700;
                color: blue;
                padding: 0px 10px;
                box-shadow: 0px 0px 0px 0.5px white;
                border: 1px solid rgb(145, 145, 145);
                box-shadow: 0px 0px 3px  rgb(145, 145, 145);
                
                
            }

            .Atcbtn:hover{
                border: 1px solid rgb(255, 255, 255);
                box-shadow: 0px 0px 3px 1px rgb(3, 75, 245);  
                transition: all 0.3s ease-in-out;
            }
        .app_note {
            
            flex: 1;
        }
        #TextComment {
            width: 100%;             /* Bắt buộc: Chiếm toàn bộ chiều rộng của khung cha (app_note) */
            max-width: 100%;         /* Không cho phép vượt quá chiều rộng tối đa */
            box-sizing: border-box;  /* Quan trọng: Tính cả padding và border vào tổng chiều rộng */
            resize: vertical;        /* Chỉ cho phép kéo giãn theo chiều dọc (hoặc dùng 'none' nếu không muốn cho kéo) */
            display: block;          /* Đảm bảo hiển thị dạng khối */
        }
            
        textarea {
            font-family: 'Segoe UI', sans-serif;
            font-size: 11px;
            width: 100%;
            max-width: 100%;
            outline: none;
            padding: 3px 10px;
        }
        textarea::placeholder {
            font-size: 10px;
        }
        textarea:focus-visible {
            outline: none !important;
        }
`;
    return StyleTemplate;
}


//🔵 HÀM CHUYỂN LAYER FRAME
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~



































//🔴HÀNH ĐỘNG UNSELECT
//**********************************************************************************************************
function ROW_UNSELECT(data){
    SWITCH_FRAME(false);
    const frameDETAIL = document.getElementById("CONTAINER_DETAIL");
    frameDETAIL.innerHTML=`
    `;
}

















//🔵 HÀM CHUYỂN LAYER FRAME
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
function SWITCH_FRAME(showDetail = true) {
    const frameDETAIL = document.getElementById("CONTAINER_DETAIL");
    const frameTOTAL = document.getElementById("CONTAINER_TOTAL");

    if (frameDETAIL && frameTOTAL) {
        frameDETAIL.style.zIndex = showDetail ? "10" : "1";
        frameTOTAL.style.zIndex = showDetail ? "1" : "10";
    }
}



