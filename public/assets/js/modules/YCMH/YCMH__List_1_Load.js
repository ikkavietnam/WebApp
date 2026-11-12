
//LOAD FUNCTION
import { MakeResizable } from "../../core/layout/RESIZABLE.js";
import { getCoreTable} from '../../core/Utils/helpers.js';

import { INIT_YCMH_Table } from "./YCMH__List_2_Table.js";
import { Bind_YCMH_Events } from "./YCMH__List_4_Events.js";

//NEW FETCH API 
import {apiFetch}     from "../../core/request/fetch_Api.js";
import {showApiError,ConverErrToDataSwal} from "../../core/request/fetch_ApiShowError.js";


/*
⭕ XÂY DỰNG BẢNG BÊN TRÁI
📃 ================================================================================================
    
    ├── 1. YCMH_List_LOAD    
        ├── 1.1.    INIT_FORM_LIST_HTML         ...Render HTML Frame    📌 
        ├── 1.2.    FINTER_STATE                ...tham số lọc          
        ├── 1.3.    INIT_YCMH_Table             ...Khởi tạo Bảng        📞YCMH__List_2_Table.js
        ├── 1.4.    getCoreTable                ...Khởi tạo mảng        📞core/Utils/helpers.js
        ├── 1.4.    Bind_YCMH_Events            ...Sự kiện              📞YCMH__List_4_Events.js
        ├── 1.5.    LOAD DATA TABLE             ...Sự kiện              📞YCMH__List_4_Events.js
        GET DATA FROM SERVER

📃 ================================================================================================
 FINTER_STATE                 ...tham số lọc          
 MakeResizable               ...Kéo dãn cột          📞core/layout/RESIZABLE.js
*/

/*LOAD LIST TABLE
🟡 --●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--● */
export async function YCMH_List_LOAD() {

    const app = document.getElementById("APP");
    //🍒1. TẠO FORM LIST & RESIZABLE
        app.innerHTML = await INIT_FORM_LIST_HTML();
        MakeResizable("YCMH_HEADER_FRAME", "YCMH_Storage");

    //🍒2. THAM SÓ FILTER
        const FINTER_STATE = {
            search: "",
            status: "all",
            depts: [],
            isAscending: false,
        };

    //🍒3. KHỞI TẠO BẢNG 
        const tableInstance =  INIT_YCMH_Table("#tblYCMH_LIST");    //from "./YCMH__List_2_Table.js";

    //🍒4. ĐĂNG KÝ SỰ KIỆN
        const coreTable = getCoreTable(tableInstance);
        if (coreTable) {Bind_YCMH_Events(coreTable, FINTER_STATE);} //from "./YCMH__List_4_Events.js";

    //🍒5. LOAD DATA TABLE
        try {
            //🔴SEND NEW apiFetch 🔄
            let KQ = await apiFetch("/api/modules/YCMH__List_1_Load", { 
                method: "GET",
            });

            //💌Receive data
            const result = KQ.data
            if (Array.isArray(result)) {
                if (coreTable) {
                    await   coreTable.setData(result); 
                            coreTable.redraw(true);   
                }
            } else if (result && result.ok === false) {
                handleAPIError(result);
            }

        } catch (e) {
            console.error(e);
            await Swal.fire(
                ConverErrToDataSwal(e)
            );
        }
        
}//End LOAD


/*INIT_FORM_LIST_HTML Tạo giao diện bảng hiển thị
🟡 --●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●  INIT_FORM_LIST_HTML */
async function INIT_FORM_LIST_HTML() {
    return `
        <div class="resizable-container" id="YCMH_HEADER_FRAME"  style="height: 100%;">
            <div class="resizable-column Tab_Left" style="width: 450px; padding-bottom:0px; padding-right:5px">
                <div class="YCMH_List_Header"> 
                    <div class="YCMH_List_Header_Left">
                        <input type="text" id="YCMH_Search" class="Box_Input" placeholder="Search user or depart...">
                        <span class="search-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        </span>
                    </div>
                    <select id="YCMH_Select_Part" class="YCMH_List_Header_Right">
                        <option value="all">All</option>
                        <option value="pending">Chờ phê duyệt/ Waiting</option>
                        <option value="approved">Đã phê duyệt/ Approved</option>
                        <option value="rejected">Bị từ chối/</option>
                        <option value="draft">Bản nháp</option>
                    </select>
                </div>
                <div class="YCMH_List_Body">
                    <div class="bodytop">
                        <div class="bodytop-left">
                            <span id="YCMH_List_Group_Title">DANH SÁCH PHIẾU / LIST ORDER REQUESTED</span>
                        </div>
                        <div class="bodytop-right">
                            <div class="outlook-sort-dropdown" id="YCMH_Dept_Dropdown_Toggle">
                                <span id="YCMH_Filter_Department" data-selected="all">All Department</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points='6 9 12 15 18 9'></polyline></svg>
                                <div class="sort-menu-context" id="YCMH_Dept_Menu">
                                    <div class="sort-item active" data-dept="all">
                                        <span class="menu-check-icon"></span>
                                        <span class="dept-text">Tất cả bộ phận</span>
                                    </div>
                                    <hr style="margin: 4px 0; border: none; border-top: 1px solid #e0e0e0;">
                                    <div class="sort-item" data-dept="ACC"><span class="menu-check-icon"></span><span class="dept-text">ACC</span></div>
                                    <div class="sort-item" data-dept="ADM"><span class="menu-check-icon"></span><span class="dept-text">ADM</span></div>
                                    <div class="sort-item" data-dept="INJ"><span class="menu-check-icon"></span><span class="dept-text">INJ</span></div>
                                    <div class="sort-item" data-dept="IP"><span class="menu-check-icon"></span><span class="dept-text">IP</span></div>
                                    <div class="sort-item" data-dept="PC"><span class="menu-check-icon"></span><span class="dept-text">PC</span></div>
                                    <div class="sort-item" data-dept="FC"><span class="menu-check-icon"></span><span class="dept-text">FC</span></div>
                                    <div class="sort-item" data-dept="WH"><span class="menu-check-icon"></span><span class="dept-text">WH</span></div>
                                    <div class="sort-item" data-dept="QAC"><span class="menu-check-icon"></span><span class="dept-text">QAC</span></div>
                                    <div class="sort-item" data-dept="ASSY"><span class="menu-check-icon"></span><span class="dept-text">ASSY</span></div>
                                    <div class="sort-item" data-dept="PRESS"><span class="menu-check-icon"></span><span class="dept-text">PRESS</span></div>
                                </div>
                            </div>
                            <button id="btn_YCMH_Sort_Direction" class="outlook-icon-btn" title="Thay đổi thứ tự sắp xếp">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><polyline points="19 12 12 19 5 12"></polyline></svg>
                            </button>
                        </div>
                    </div>
                    <div class="table" id="tblYCMH_LIST" style="height: calc(100% - 35px); margin-left: 0px; width: 100%;"></div>
                </div>
            </div>
            <div class="resizable-divider"></div>
            <div class="resizable-column Tab_Right" id="TABRIGHT" style="position: relative;">
               <div id="CONTAINER_TOTAL"></div>
               <div id="CONTAINER_DETAIL"></div>
            </div>
        </div>
    `;


    
}//End


/*
🟡 --●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●   */
function handleAPIError(result) {
    console.error("Lỗi từ SQL Server:", result.msg);
    Swal.fire({
        title: "Thông báo",
        text: result.msg,
        icon: "error",
        confirmButtonText: "OK",
    }).then((sweetResult) => {
        if (sweetResult.isConfirmed && result.ErrCode === 401) {
            localStorage.clear();
            sessionStorage.clear();
            window.location.href = "/#Home";
        }
    });
}

