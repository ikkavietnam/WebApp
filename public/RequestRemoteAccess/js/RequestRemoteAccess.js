import { MakeResizable } from "../../assets/js/core/layout/RESIZABLE.js";   //RESIZE COLUMNM
import {  TableBase } from "../../assets/js/core/tableERP/tableERPBase.js"; //TABULATOR
//NEW FETCH API 
import {apiFetch}     from "../../assets/js/core/request/fetch_Api.js";
import {showApiError,ConverErrToDataSwal} from "../../assets/js/core/request/fetch_ApiShowError.js";

RequestRemoteAccess_INIT();
CHECK_URL();
LOAD_LIST_MANAGER();
let msgSendTimer = null;
let MSG_CONFIRM_Timer = null;

/*LOAD PAGE
🟡 --●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--● */
function RequestRemoteAccess_INIT(){
    
    /*
    📌 MAKE RESIZEABLE */
    MakeResizable("RRA_CONTENTS", "RRA_Storage");

    
    /*
    📌 HIDE CONTENTS */
    const CONTENT01 = document.getElementById("CONTENT01");
    const CONTENT02 = document.getElementById("CONTENT02");
    CONTENT01.style.display="flex";
    CONTENT02.style.display="none";



        /*
    🟢 EVENT INPUT MEMBERCODE  */
    const inputMemberCode = document.getElementById("IP_MEMBER_CODE");
    // 1.Keydown Chỉ cho phép nhập số (Chặn ký tự chữ và ký tự đặc biệt)
    inputMemberCode.addEventListener("keydown", function (e) {
        const allowedKeys = ["Backspace", "Delete", "Tab", "Escape", "Enter", "ArrowLeft", "ArrowRight"];// Cho phép: Backspace, Delete, Tab, Escape, Enter, các phím mũi tên
        if (allowedKeys.includes(e.key)) return;
        if (!/^[0-9]$/.test(e.key)) {// Nếu không phải là số (0-9) thì chặn lại
            e.preventDefault();
        }
    });

    // 2.input Tự động xử lý khi gõ đủ 4 ký tự (Dùng sự kiện 'input' để kiểm tra)
    inputMemberCode.addEventListener("input", function () {
        this.value = this.value.replace(/[^0-9]/g, "");// Lọc lại chắc chắn chỉ giữ số (Phòng Paste/Dán chữ)
        // Kiểm tra độ dài
        if (this.value.length === 4) { //Hàm xử lý tiếp
            LOAD_FORM(this.value);
        }
    });






    const BTN_DONGY = document.getElementById("BTN_CONFIRM_DONGY");
    const BTN_TUCHOI = document.getElementById("BTN_CONFIRM_TUCHOI");
    const ipconfirm_code = document.getElementById("IP_CONFIRM_CODE");
        /*
    🟢 EVENT INPUT CONFIRMODE  */ //HIDE OR UNHIDE BUTTON FUNCTION
    ipconfirm_code.addEventListener("input", function() {
        if (ipconfirm_code.value.trim().length ===20) {
            BTN_DONGY.style.display ="flex";
            BTN_TUCHOI.style.display ="flex";
        }else {
            BTN_DONGY.style.display ="none";
            BTN_TUCHOI.style.display ="none";
        }
    });

        /*
    🟢🔴EVENT BUTTON CONFIRMODE APPROVED */
    BTN_DONGY.addEventListener("click", function() {
        const ipconfirm_code = document.getElementById("IP_CONFIRM_CODE");
        const StrSec = ipconfirm_code.value;
        CONFIRM_REQUEST(StrSec,"dongy");
    });

        /*
    🟢🔴EVENT BUTTON CONFIRMODE REJECTED */    
        BTN_TUCHOI.addEventListener("click", function() {
            const ipconfirm_code = document.getElementById("IP_CONFIRM_CODE");
            const StrSec = ipconfirm_code.value;
            CONFIRM_REQUEST(StrSec,"tuchoi");
        });

    ///////////////////////////////////////////////////////////////////////////////
    const inputStart    = document.getElementById("TIMESTART");
    const inputEnd      = document.getElementById("TIMEEND");
    const msgSTART      = document.getElementById("MSGSTART");
    const msgEND        = document.getElementById("MSGEND");    
        /*
    🟢 EVENT INPUT TIMESTART  */       
        inputStart.addEventListener("change", function () {

            msgSTART.innerText = "";
            inputEnd.value = "";
            // Null
            if (this.value ==="") return; 
            
            const inputStart_date    = new Date(this.value);
            const inputStart_time    = inputStart_date.getHours()*60 + inputStart_date.getMinutes();

            const now = new Date();
            //GIOI HAN TIME
            const timeLimitLow = 8*60;  
            const timeLimitHight = 17*60;
            //CHỈ LẤY DATE
            inputStart_date.setHours(0,0,0,0);
            now.setHours(0,0,0,0);

                if (inputStart_date<now) {
                    msgSTART.innerText = "Error: Không hợp lệ ! Ngày không được nhỏ hơn hiện tại";
                    msgSTART.style.color ="red";
                    return;
                } else if (inputStart_time < timeLimitLow || inputStart_time > timeLimitHight) {
                    msgSTART.innerText = "Error: Không hợp lệ ! Thời gian giới hạn trong giờ hành chính từ 08hAM - 17hPM"; 
                    msgSTART.style.color ="red";
                    return;
                } else {  
                    msgSTART.innerText = "Ready: Dữ liệu hợp lệ";
                    msgSTART.style.color ="green";
                    msgSTART.style.padding ="5px 20px";
                }

            //STORAGE
                const selectedDateOnly = this.value.split(" ")[0];  // Lấy riêng phần ngày YYYY-MM-DD từ giá trị master
                sessionStorage.setItem("flatpickrSession", selectedDateOnly);   // Ghi đè vào sessionStorage
                sessionStorage.setItem("inputDateMaster", this.value);   // Ghi đè vào sessionStorage
                // Clear giá trị của các ô phụ để người dùng chọn lại theo khung giờ mới
                document.querySelectorAll(".input_select_time_ext").forEach(input => {
                    if (input._flatpickr) {
                        input._flatpickr.clear();
                    }
                });
            
        });

        /*
    🟢 EVENT INPUT TIMEEND  */
        inputEnd.addEventListener("change",function(){
            const msgTotal = document.getElementById("MSGTOTAL");
            //Ckeck InputMaster value
            if (!inputStart ||inputStart.value ==="") {
                this.value="";
                msgEND.innerText = "Error: Sai thứ tự chọn thời gian! Hãy chọn thời gian bắt đầu trước!";
                msgEND.style.color ="red";
                msgTotal.innerText = "";
                return;
            }
            //Ckeck InputMaster MSG
            const keySTART = msgSTART.innerText.trim().substring(0, 6);
            if (keySTART === "Error:") {
                this.value="";
                msgEND.innerHTML = "Error: Starrt time không hợp lệ ! Chọn lại thời gian bắt đầu <br> Thời gian chỉ cho phép trong phạm vi giờ hành chính";
                msgEND.style.color ="red";
                msgTotal.innerText = "";
                return;
            }

            //END null
            if (inputEnd.value.length === 0) {
                msgEND.innerText    = "";
                msgTotal.innerText  = "";
                return;
            }

            // SS DATA END & START
            const start = new Date(inputStart.value);
            const end = new Date(this.value);
            // 1. So sánh ngày/tháng/năm theo múi giờ địa phương
            const isSameDay =   start.getFullYear() === end.getFullYear() &&
                                start.getMonth()    === end.getMonth() &&
                                start.getDate()     === end.getDate();

            // 2. KHÁC NGÀY
            if (!isSameDay) {
                msgEND.innerText = "Error: Không hợp lệ! Ultraviewer không được phép mở qua ngày!";
                msgEND.style.color ="red";
                msgTotal.innerText = "";
                return;
            }

            // 3. Nếu CÙNG NGÀY -> Kiểm tra giờ kết thúc phải lớn hơn giờ bắt đầu
            if (end <= start) {
                msgEND.innerText = "Error: Không hợp lệ! Thời gian kết thúc phải lớn hơn thời gian bắt đầu!";
                msgEND.style.color ="red";
                msgTotal.innerText = "";
                return;
            }

            // 4. Everything oke
            msgEND.innerText = "Ready: Dữ liệu hợp lệ";
            msgEND.style.color ="green";
            msgEND.style.padding ="5px 20px";

            // 5. TÍNH TOTAL TIME
            const totalMinutes = Math.floor((end - start) / (1000 * 60));
            const hours = Math.floor(totalMinutes / 60);
            const minutes = totalMinutes % 60;

            
            msgTotal.innerText = `TOTAL : ${hours} Hour ${minutes} Minutes`;
            msgTotal.style.color ="green";
            msgEND.style.padding ="5px 20px";

        });
    ///////////////////////////////////////////////////////////////////////////////
        /*
    🟢🔴 EVENT BTN GUIYEUCAU  */
    const btnSend = document.getElementById("BTN_SEND");
        btnSend.addEventListener("click",function(){
            SEND_REQUEST();
        });
        /*
    🟢🔴 EVENT BTN HUY  */
    const   btnBack = document.getElementById("BTN_BACK");
            btnBack.addEventListener("click", BACK_FORM);
}

/*LOAD PAGE
🟡 --●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--● */
function CHECK_URL(){
    
    const UrlParams = new URLSearchParams(window.location.search);
    const secParam = UrlParams.get('sec');

    if (secParam) {
        // .replace(/\s+/g, '') sẽ xóa sạch tất cả khoảng trống/khoảng trắng trong chuỗi
        const sec = secParam.replace(/\s+/g, '');
        if (sec !== ''&& sec.length === 20) {
            CONFIRM_REQUEST(sec,"dongy");
        }
    }
}
/*LOAD PAGE
🟡 --●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--● */
async function LOAD_LIST_MANAGER(){
    //FIND SEC
    console.log("LOAD_LIST_MANAGER")
    //🟢 DỰNG TAB NỘI DUNG - SỬ DỤNG BẢNG TABLULATOR

   
    const TABLE_LIST = document.getElementById("LISTMANAGER");
    if (!TABLE_LIST) {
        return;
    }
    
    let bangdanhsach = new TableBase("#LISTMANAGER", {
        data: [],
        headerWordWrap: true,
        layout: "fitDataStretch",
        renderVertical: "basic",
        
        rowFormatter: function(row) {
            let status = row.getData().RraStatus;
            let element = row.getElement();

            let color = "#000000"; // Mặc định đen (Approved)
            if (status === "Wait") {
                color = "#a0a0a0"; // Xám nhạt
                console.log("HERE2");
            } else if (status === "Rejected") {
                color = "#dc3545"; // Đỏ
            }

            // Đổi màu cho tất cả các ô bên trong hàng này
            element.style.setProperty("color", color, "important");
        },

        columns: [              
            { title: "No"               ,formatter: "rownum",   hozAlign: "center"      ,width: 30,    variableHeight: true,  headerSort: false, },
            { title: "Người làm phiếu"  ,formatter: "textarea", field: "UsDisplay"      ,  headerSort: false, },
            { title: "Bộ phận"          ,formatter: "textarea", field: "DeptName"       ,  headerSort: false, },
            { title: "Ngày yêu cầu"     ,formatter: "textarea", field: "CreateTime"     ,  headerSort: false, },
            { title: "Máy mở Ultra"     ,formatter: "textarea", field: "Hostname"       ,  headerSort: false, },
            { title: "Thời gian mở"     ,formatter: "textarea", field: "TimeStart"      ,  headerSort: false, },
            { title: "Thời gian đóng"   ,formatter: "textarea", field: "TimeEnd"        ,  headerSort: false, },
            { title: "Tổng thời gian"   ,formatter: "textarea", field: "RraTimeTotal"   ,  headerSort: false, },
            { title: "Nguồn kết nối"    ,formatter: "textarea", field: "RraDestion"     ,  headerSort: false, },
            { title: "Mục đích"         ,formatter: "textarea", field: "RraPurpose"     ,  headerSort: false, },
            { title: "Người duyệt"      ,formatter: "textarea", field: "RraConfirmBy"   ,  headerSort: false, },
            { title: "Thời gian duyệt"  ,formatter: "textarea", field: "RraConfirmTime" ,  headerSort: false, },
            { title: "Status"           ,formatter: "textarea", field: "RraStatus"      ,  headerSort: false, },
            
        ],
    });
    // GETDATA
    try {
        //🆘Request API 
        const KQPOST = await fetch("/api/RRA_getInfoListManager", { 
            method: "GET",
            credentials: "include",
            headers: { "Content-Type": "application/json" },
        });  

        //🔃Convert data
        const KQ = await KQPOST.json().catch(() => null);   // 🔃 Convert data JSON (kể cả khi status là 400, 500 nếu backend có trả JSON lỗi)

		// 💌 Kiểm tra nếu HTTP status không thành công (400, 404, 500...)//💌Receive data
		if (!KQPOST.ok) {
			const errorMsg = KQ?.msg || `Lỗi kết nối máy chủ (Mã lỗi: ${KQPOST.status})`;
			const errorDetail = KQ?.ErrDetail || KQPOST.statusText;
			throw new Error(JSON.stringify({ title: KQ.title ? KQ.title :'Lỗi Yêu Cầu', msg: errorMsg, ErrDetail: errorDetail }));
            
		}   

		// ⚠️ Kiểm tra nếu Server trả HTTP 200 nhưng logic backend báo lỗi ({ ok: false })
		if (KQ && KQ.ok === false) {
			throw new Error(JSON.stringify({ 
				title: KQ.title || 'Xử lý thất bại', 
				msg: KQ.msg || 'Dữ liệu không hợp lệ', 
				ErrDetail: KQ.ErrDetail || '' 
			}));
		}

		// ✅🎉 Thành công ->

        let initialData = KQ.data;
        bangdanhsach.setData(initialData);
        bangdanhsach.redraw(true);

    } catch (e) {
        // 🚨 Xử lý các tình huống lỗi tại khối catch
		let title = 'Đã xảy ra lỗi';
		let msg = 'Không thể kết nối đến máy chủ. Vui lòng thử lại sau!';
		let errDetail = e.message;

		// Phân tích nếu lỗi được throw từ dạng JSON ở trên
		try {
			const parsedError = JSON.parse(e.message);
			title = parsedError.title || title;
			msg = parsedError.msg || msg;
			errDetail = parsedError.ErrDetail || '';
		} catch (_) {
			// Lỗi mất mạng hoặc Fetch API sập hoàn toàn (TypeError: Failed to fetch)
			if (e.name === 'TypeError') {
				msg = 'Mất kết nối mạng hoặc máy chủ không phản hồi.';
			}
		}

		// Hiển thị Swal thông báo lỗi an toàn
		Swal.fire({
			title: title,
			icon: "error",
			html: `
				<div style="color: #f83f3f; 
							font-size: 13px; 
							line-height: 1.4; 
							text-align: left;">
					<b>${msg}</b>
					${errDetail ? `<br><small style="color: #777;">Chi tiết: ${errDetail}</small>` : ''}
				</div>
			`
		});
    }

}

/* XỬ LÝ EVENT NHAP XONG MA NHAN VIEN
⚪ --●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--● */
async function  LOAD_FORM(MemberCode) {
    //LOAD MEMBER INFOR
    try {
        //🆘Request API 
        const KQPOST = await fetch("/api/RRA_getInfoMember", { 
            method: "POST",
            credentials: "include",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(MemberCode)
        });     
        //💌Receive data
        if (!KQPOST.ok) {
            throw new Error(`HTTP Error Status: ${KQPOST.status}`);
        }
        //🔃Convert data
        const KQ = await KQPOST.json();

        if (!KQ.ok) {
            throw new Error(KQ.msg);
        }

         //LOAD UI
        const CONTENT01 = document.getElementById("CONTENT01");
        const CONTENT02 = document.getElementById("CONTENT02");
        CONTENT01.style.display="none";
        CONTENT02.style.display="flex";
        //clear msg
        document.querySelectorAll('input, textarea').forEach(el => {
            el.value="";
        });
        document.querySelectorAll('.msgclear').forEach(el =>{
            el.innerHTML="";
        });

        //MEMBERCODE
        const   ipMembercode        = document.getElementById("MEMBERCODE");
                ipMembercode.value  = MemberCode;     

        const   ipNhanVien        = document.getElementById("NHANVIEN");
                ipNhanVien.value  = KQ.data.MemName; 

        const   ipBoPhan        = document.getElementById("BOPHAN");
                ipBoPhan.value  = KQ.data.Depart; 
        //REQUEST SRV
        




    } catch (error) {
        const   MSG1            = document.getElementById("MSG1");
                MSG1.innerText  = error.message;
        document.querySelectorAll('input, textarea').forEach(el => el.value = '');
    }
}

/* BTN REQUEST SERVER
⚪ --●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--● */
async function SEND_REQUEST(){
    //🍒 DISABLE
    const thisbtn = document.getElementById("BTN_SEND")
    thisbtn.disabled = true;

    //🍒 KỂM TRA
        // INPUT NULL
        const elements = document.querySelectorAll('#CONTENT02 input, #CONTENT02 textarea');

            for (const el of elements) {
                if (el.value.trim() === '') {
                    el.focus();
                    const   msgSend =document.getElementById('MSG_SEND') ;
                    msgSend.innerHTML=`Thời gian yêu cầu phải hợp lệ ! vui lòng chọn lại thời gian`;
                    if (msgSendTimer) clearTimeout(msgSendTimer);
                    msgSendTimer = setTimeout(function() {msgSend.innerHTML = "";}, 5000);
                    thisbtn.disabled = false;
                    return; // Dừng vòng lặp ngay khi tìm thấy ô trống đầu tiên
                }
            }
        // INPUT VALID
        const inputValid = document.querySelectorAll('#MSGSTART, #MSGEND');

        for (const elmsg of inputValid) {
            if (elmsg.innerText.trim().substring(0, 6)==="Error:") {
                const   msgSend =document.getElementById('MSG_SEND') ;
                        msgSend.innerHTML=`Thời gian yêu cầu phải hợp lệ ! vui lòng chọn lại thời gian`;
                if (msgSendTimer) clearTimeout(msgSendTimer);
                    msgSendTimer = setTimeout(function() {msgSend.innerHTML = "";thisbtn.disabled = false;}, 5000);
                    thisbtn.disabled = false;
                return;
            }
            
        }
           
    //🍒 THU THẬP DỮ LIỆU
        let payload = Object.fromEntries(
            Array.from(document.querySelectorAll('#CONTENT02 input[id], #CONTENT02 select[id], #CONTENT02 textarea[id]'))
                .filter(el => !((el.type === 'radio' || el.type === 'checkbox') && !el.checked))
                .map(el => [el.id, el.value.trim()])
        );

        // 2. Thêm cặp key-value cho MSGTOTAL
        const msgTotalEl = document.getElementById('MSGTOTAL');
        if (msgTotalEl) {
            payload['MSGTOTAL'] = msgTotalEl.innerText.trim();
        }
        console.log(payload);


    //🍒SEND REQUEST **********************************************************************
        try {
		// 🆘 Request API
		const KQPOST = await fetch("/api/RRA_SendRequest", {
			method: "POST",
			credentials: "include",
			headers: { "Content-Type": "application/json" },
			body: JSON.stringify(payload) // 
		});
		const KQ = await KQPOST.json().catch(() => null);   // 🔃 Convert data JSON (kể cả khi status là 400, 500 nếu backend có trả JSON lỗi)

		// 💌 Kiểm tra nếu HTTP status không thành công (400, 404, 500...)
		if (!KQPOST.ok) {
			const errorMsg = KQ?.msg || `Lỗi kết nối máy chủ (Mã lỗi: ${KQPOST.status})`;
			const errorDetail = KQ?.ErrDetail || KQPOST.statusText;
			throw new Error(JSON.stringify({ title: 'Lỗi Yêu Cầu', msg: errorMsg, ErrDetail: errorDetail }));
		}

		// ⚠️ Kiểm tra nếu Server trả HTTP 200 nhưng logic backend báo lỗi ({ ok: false })
		if (KQ && KQ.ok === false) {
			throw new Error(JSON.stringify({ 
				title: KQ.title || 'Xử lý thất bại', 
				msg: KQ.msg || 'Dữ liệu không hợp lệ', 
				ErrDetail: KQ.ErrDetail || '' 
			}));
		}

		// ✅🎉 Thành công -> Xử lý tiếp với KQ tại đây
		thisbtn.disabled = false;
        Swal.fire({ 
            title:'Gửi yêu cầu thành công',
            icon: "success",
            html: `
                <div style="color: #2308f3; 
                            font-size: 12px; 
                            line-height: 1.2; 
                            text-align: left"> <br> Chủ động liên hệ trưởng bộ phận hoặc IT để xác nhận!
                </div>      
            `, 
        }).then((result) => {    
            //UPDATE payload
            payload['StrSec']=KQ.StrSec;
            payload['NguoixacnhanName']=KQ.NguoixacnhanName;
            payload['NguoixacnhanEmail']=KQ.NguoixacnhanEmail;

            //SEND MAIL
            fetch("/api/RRA_SendMAIL", {
                method: "POST",
                credentials: "include",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload) // 
            });
            //RELOAD LIST QUAY VỀ
            LOAD_LIST_MANAGER()
            BACK_FORM(); 
        }); 

	} catch (e) {
		// 🚨 Xử lý các tình huống lỗi tại khối catch
        thisbtn.disabled = false;
		let title = 'Đã xảy ra lỗi';
		let msg = 'Không thể kết nối đến máy chủ. Vui lòng thử lại sau!';
		let errDetail = e.message;

		// Phân tích nếu lỗi được throw từ dạng JSON ở trên
		try {
			const parsedError = JSON.parse(e.message);
			title = parsedError.title || title;
			msg = parsedError.msg || msg;
			errDetail = parsedError.ErrDetail || '';
		} catch (_) {
			// Lỗi mất mạng hoặc Fetch API sập hoàn toàn (TypeError: Failed to fetch)
			if (e.name === 'TypeError') {
				msg = 'Mất kết nối mạng hoặc máy chủ không phản hồi.';
			}
		}

		// Hiển thị Swal thông báo lỗi an toàn
        thisbtn.disabled = false;
		Swal.fire({
			title: title,
			icon: "error",
			html: `
				<div style="color: #f83f3f; 
							font-size: 13px; 
							line-height: 1.4; 
							text-align: left;">
					<b>${msg}</b>
					${errDetail ? `<br><small style="color: #777;">Chi tiết: ${errDetail}</small>` : ''}
				</div>
			`
		});
	}

    //🍒
        
    //🍒
             
}/* SEND_REQUEST */


/* BTN QUAYLAI
⚪ --●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--● */
function BACK_FORM() {
    //LOAD UI
    const CONTENT01 = document.getElementById("CONTENT01");
    const CONTENT02 = document.getElementById("CONTENT02");
    CONTENT01.style.display="flex";
    CONTENT02.style.display="none";
    //Clear btn
    const BTN_DONGY = document.getElementById("BTN_CONFIRM_DONGY");
    const BTN_TUCHOI = document.getElementById("BTN_CONFIRM_TUCHOI");
    BTN_DONGY.style.display ="none";
    BTN_TUCHOI.style.display ="none";
    //CLEAR INPUT
    document.querySelectorAll('input, textarea').forEach(el => el.value = ''); 
    document.getElementById("MSG1").innerText = ''       
}

/* CONFIRM_REQUEST
⚪ --●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--● */

async function CONFIRM_REQUEST(mabimat,trangthai) {
    let payload = {
        mabimat,
        trangthai
    };
    //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    try {
        //🔴SEND NEW apiFetch
        const RES = await apiFetch("/api/RRA_ActionConfirm", {
            method: "POST",
            body: payload, //KHÔNG CẦN Stringify 
        });   
        // THÔNG BÁO KẾT QUẢ
        Swal.fire({ 
            title:RES.msg || "Xác nhận thành công",
            icon: "success",
            html: `
                <div style="color: #2308f3; 
                            font-size: 12px; 
                            font-weight:500;
                            line-height: 1.2; 
                            text-align: center"> <br> Chủ động liên hệ bộ phận IT hoặc đợi IT mở khóa connect!
                </div>      
            `, 
        });

    } catch (e) {
        //Hide msg nếu có ID. time hiển thị
        showApiError(e,"MSG_CONFIRM",10);

        //SWAL THÔNG BÁO & SAU KHI CLICK OK
        const errorResult = await Swal.fire(
            ConverErrToDataSwal(e)
        );
        if (errorResult.isConfirmed) {  
            return;// nếu cần ngắt code
        }
    }
    
}


/* CONFIRM_REQUEST
⚪ --●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--● */

async function CONFIRM_REQUEST1(mabimat,trangthai) {
    let payload = {
        mabimat,
        trangthai
    };
    //SEND TO SV
    try {
        const KQPOST = await fetch("/api/RRA_ActionConfirm", {
			method: "POST",
			credentials: "include",
			headers: { "Content-Type": "application/json" },
			body: JSON.stringify(payload) // 
		});
        //🔃 Convert data JSON Nếu Srv trả kết quả không phải là dạng JSON thì KQ = null
		const KQ = await KQPOST.json().catch(() => null);   
        
		// ⚠️ Kiểm tra nếu HTTP status không thành công (400, 404, 500...)
		if (!KQPOST.ok) {
			const errorMsg = KQ?.msg || `Lỗi kết nối máy chủ (Mã lỗi: ${KQPOST.status})`;
			const errorDetail = KQ?.ErrDetail || KQPOST.statusText;
			throw new Error(JSON.stringify({ 
                title: 'Lỗi Yêu Cầu', 
                msg: errorMsg, 
                ErrDetail: errorDetail 
            }));
		}

		// ⚠️ Kiểm tra nếu Server trả HTTP 200 nhưng logic backend báo lỗi ({ ok: false })
		if (KQ && KQ.ok === false) {
			throw new Error(JSON.stringify({ 
				title: KQ.title || 'Xử lý thất bại', 
				msg: KQ.msg || 'Dữ liệu không hợp lệ', 
				ErrDetail: KQ.ErrDetail || '' 
			}));
		}
		// ✅🎉 Thành công -> Xử lý tiếp với KQ tại đây
        Swal.fire({ 
            title:KQ.msg || "Xác nhận thành công",
            icon: "success",
            html: `
                <div style="color: #2308f3; 
                            font-size: 12px; 
                            font-weight:500;
                            line-height: 1.2; 
                            text-align: center"> <br> Chủ động liên hệ bộ phận IT hoặc đợi IT mở khóa connect!
                </div>      
            `, 
        });
		  
    } catch (e) {
        // 🚨 Xử lý các tình huống lỗi tại khối catch
		let title = 'Đã xảy ra lỗi';
		let msg = 'Không thể kết nối đến máy chủ. Vui lòng thử lại sau!';
		let errDetail = e.message;

		// Phân tích nếu lỗi được throw từ dạng JSON ở trên
		try {
			const parsedError = JSON.parse(e.message);
			title = parsedError.title || title;
			msg = parsedError.msg || msg;
			errDetail = parsedError.ErrDetail || '';
		} catch (_) {
			// Lỗi mất mạng hoặc Fetch API sập hoàn toàn (TypeError: Failed to fetch)
			if (e.name === 'TypeError') {
				msg = 'Mất kết nối mạng hoặc máy chủ không phản hồi.';
			}
		}
		// HIỂN THỊ TRÊN PAGE
        const MSG_XACNHAN = document.getElementById("MSG_CONFIRM");
        MSG_XACNHAN.innerHTML=`
                <div style="color: #f83f3f; 
							font-size: 13px; 
							line-height: 1.4; 
							text-align: left;">
					<b>${msg}</b>
					${errDetail ? `<br><small style="color: #777;">Chi tiết: ${errDetail}</small>` : ''}
				</div>  
        `;

        if (MSG_CONFIRM_Timer) clearTimeout(MSG_CONFIRM_Timer);
            MSG_CONFIRM_Timer = setTimeout(function() {MSG_XACNHAN.innerHTML = "";}, 5000);
        // HIỂN THỊ TRÊN SWAL
        Swal.fire({
			title: title,
			icon: "error",
			html: `
				<div style="color: #f83f3f; 
							font-size: 13px; 
							line-height: 1.4; 
							text-align: left;">
					<b>${msg}</b>
					${errDetail ? `<br><small style="color: #777;">Chi tiết: ${errDetail}</small>` : ''}
				</div>
			`
		});
        ///
    }       
    
}