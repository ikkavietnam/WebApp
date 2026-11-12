
//NEW FETCH API 
    import {apiFetch}     from "../../assets/js/core/request/fetch_Api.js";
    import {showApiError,ConverErrToDataSwal} from "../../assets/js/core/request/fetch_ApiShowError.js";



document.addEventListener("DOMContentLoaded", function() { 
    //InitApp();
    INIT_APPROVAL();
    return;           
});

/* 01 HÀM INIT_APPROVAL 🔸 🔹
🟥====================================================================================
*/
async function INIT_APPROVAL() {

    try {
        //🔸THAM SỐ
            const MsgResponse    = document.getElementById('MsgResponse');
            const APPLOGIN       = document.getElementById('APPLOGIN');

            const UrlParams     = new URLSearchParams(window.location.search);
            const InstanceID    = { "id" : UrlParams.get('id')};
            //KIỂM TRA SỐ NGUYÊN ID
            const isInteger = /^\d+$/.test(InstanceID.id);
            if (!isInteger) {
                MsgResponse.innerText = "Link không chính xác !";
                return;
            }
        //🔸SEND NEW apiFetch
            const KQ = await apiFetch("/api/modules/YCMH_Duyet_INIT", {
                method: "POST",
                body: InstanceID.id,
            });  
            //RESION OKE    
            DuyetBuildUI(KQ);

    } catch (e) {
        //🚸NẾU CHƯA ĐĂNG NHẬP HOẶC PHIÊN BẢN HẾT HẠN & BUILD LOGIN PORT
        if (e.status ===401) {
            MsgResponse.innerHTML = "BẠN CẦN ĐĂNG NHẬP ĐỂ TIẾP TỤC QUÁ TRÌNH XEM VÀ DUYỆT ! " + '<br><span class="MsgResponse-ErrLogin">' + e.msg  ;
            APPLOGIN.style.display  = 'flex';
           
            //♂️ADD EVENT ONCLICK
                document.getElementById("btnLogin").addEventListener("click", btnLoginClick);

            //♂️ENTER trong ô username = chuyển xuống password
            document.getElementById("username").addEventListener("keydown", function(e){
                    if(e.key === "Enter"){document.getElementById("password").focus();}
                }
            );
                
            //♂️ENTER trong ô password = login 
            document.getElementById("password").addEventListener("keydown", function(e){
                    if(e.key === "Enter"){
                        btnLoginClick();   //👉 thêm event gọi hàm btnLoginClick
                    }
                }
            );

        } else {
            showApiError(e,"MsgResponse");
            const errorResult = await Swal.fire(ConverErrToDataSwal(e));
        }
    }
        
}//❌❌❌❌❌ End Function 



/* HÀM DuyetBuildUI
⭕ ==================================================================================== DuyetBuildUI */
async function DuyetBuildUI(KQ){
    //🟢 CHK
    if (KQ.ChkPermission !== undefined && KQ.ChkPermission===false) {
        const MsgResponse    = document.getElementById('MsgResponse');
        if (MsgResponse) {
            MsgResponse.innerHTML = `No permission! \n <br> Bạn không có quyền phê duyệt ở phiếu này`;
        }

        const APPLOGIN    = document.getElementById('APPLOGIN');
        if (APPLOGIN) {
            APPLOGIN.style.display = 'none';
        }
        return;   
    }
    
    //🟢 DỰNG KHUNG CONTENTS
    try {
        //♦️ Dùng fetch để gọi file HTML từ thư mục public
        const response = await fetch('/view/ycmh/YCMH_TemplateForm.html?v=' + Date.now());
        if (!response.ok) {
            throw new Error(`Không thể tải template (HTTP status: ${response.status})`);
        }

        
        let htmlTemplate = await response.text();

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

        for (const [key, value] of Object.entries(data)) {
            const regex = new RegExp(`{{${key}}}`, 'g');
            htmlTemplate = htmlTemplate.replace(regex, value ?? '');
        }

        //♦️ DỰNG KHUNG CONTENTSADD HEADER
        document.open();
        document.write(htmlTemplate);
        document.close();
     

      
        //♦️ DỰNG KHUNG CONTENTS
        const HD    = document.getElementById('HEADER');
        HD.style.display = 'flex';
        if (HD) {
            HD.innerHTML = `
                <div class="HEADER-colunm1">
                    <div class="btn btn_BACK">
                        <a href="http://192.168.110.235:8085/#ToDoList"> Back to Home </a>
                    </div>          
                </div>
                <div class="HEADER-colunm2">
                    <h4 style="margin: 0; padding: auto; color: white; width: 100%; text-align: center;">
                        APPROVAL ORDER REQUEST
                    </h4>
                </div>
                <div class="HEADER-colunm3"></div>
            `;

        } else {
            console.warn("Không tìm thấy phần tử Header trên DOM để render!");
        }
       
        //♦️ DỰNG KHUNG APPROVAL ( PHIẾU CHƯA KẾT THÚC TIẾN TRÌNH)
        const AppForm    = document.getElementById('AppForm');

        if (KQ.InstanceStatus === 'Waiting') {
            AppForm.style.display = 'flex';
            console.log("HERRR")
            //TRẠNG THÁI NGƯỜI VIEW
            const APP_UsViewer_Status   = KQ.APP_UsViewer_Status;
            if (APP_UsViewer_Status === "Created" ) {
               LockACTION("Created","CREATED BY YOU");
            }

            if (APP_UsViewer_Status === "Waiting" ) {
               LockACTION("Waiting","WAIT NEXT STEP");
            }

            if (APP_UsViewer_Status === "Approved" ) {
               LockACTION("Approved","BẠN ĐÃ DUYỆT / APPROVED BY YOU");
            }

            if (APP_UsViewer_Status === "Bypass" ) {
               LockACTION("Bypass","ĐÃ THÔNG QUA / BYPASS");
            }

            if (APP_UsViewer_Status === "Rejected" ) {
               LockACTION("Rejected","BẠN ĐÃ HỦY / REJECTED BY YOU");
            }
        } else {
            AppForm.style.display = 'none';
        }
        
    } catch (error) {
        console.error('Lỗi khi load YCMH template:', error);
    }
   
    //🟨 ADD SỰ KIỆN CHO BTN
        document.getElementById('BTN_dongy').addEventListener('click',function(e){
            //BTNCLICK_DONGY(KQ.InstanceID);
            BTNCLICK_ACTION(KQ.InstanceID,"DONGY");
        });

        document.getElementById('BTN_huy').addEventListener('click', function(e){
           BTNCLICK_ACTION(KQ.InstanceID,"HUY");
        });



    //🟨 KHÓA CHỨC NANG NGƯỜI DUYỆT
    function LockACTION(statusKey, statusText) {
        const AppForm    = document.getElementById('AppForm');
        const box = document.querySelector('.app_button');

        if (!box) return;
        
        // 1. Thêm class khóa
        box.classList.add('IsLocked');

        // 2. Cập nhật các Attribute động cho CSS lấy dữ liệu
        box.setAttribute('data-status', statusKey);           // Ví dụ: 'approved'
        box.setAttribute('data-status-text', statusText);     // Ví dụ: '✓ ĐÃ KÝ DUYỆT / APPROVED'

        // 3. Khóa các thẻ input bên trong
        AppForm.querySelectorAll('button').forEach(btn => {
            btn.disabled = true;
            btn.classList.add('IsLocked');
        });

        AppForm.querySelectorAll('textarea').forEach(btn => {
            btn.readOnly = true;
            btn.classList.add('IsLocked');
        });

        const textarea = AppForm.querySelector('textarea');
        if (textarea) textarea.readOnly = true;
        
    }

}//❌❌❌❌❌ End Function 




/* HÀM btnLoginClick
⭕ ==================================================================================== btnLoginClick */
async function btnLoginClick(){   

    const _username     = document.getElementById("username").value;
    const _password     = document.getElementById("password").value;
    const msgElement    = document.getElementById("msg");
    const UsElement     = document.getElementById("username");
    const PwdElement    = document.getElementById("password");

    //🔸1.CHƯA NHẬP DỮ LIỆU
    if (_username === "" || _password === "") {
        msgElement.innerText = "Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu!";
        // Tạo và Xóa class hiệu ứng sau 400ms 
        msgElement.classList.add("MsgEff002");
        setTimeout(() => {msgElement.classList.remove("MsgEff002");}, 500);
        // Xóa msg sau 3s và focus vào ô nhập liệu
        setTimeout(()=>{msgElement.innerHTML="";},  3000);
        username.focus();
        return;
    }

    //🔸2. Kiểm tra độ dài (Nhỏ hơn 4 ký tự)
    if (_username.length < 4 || _password.length < 4) {
        msgElement.innerText = "Tài khoản và mật khẩu phải có ít nhất 4 ký tự!";
        msgElement.classList.add("MsgEff002");
        setTimeout(() => {msgElement.classList.remove("MsgEff002");}, 500);

        setTimeout(()=>{msgElement.innerHTML="";},  3000);
        if (_username.length <4 ) {
            UsElement.focus();
            UsElement.select();
        } else {
            PwdElement.focus();
            PwdElement.select();

        }      
        return;
    }
    //🔸3. Kiểm tra ký tự không hợp lệ
    // Định nghĩa regex: Chỉ cho phép chữ cái không dấu (a-z, A-Z), chữ số (0-9) và dấu gạch dưới (_)
    const validRegex = /^[a-zA-Z0-9_]+$/;
    //USER
    if (!validRegex.test(_username)) {
        msgElement.innerText = "Tài khoản chứa ký tự không hợp lệ! (Chỉ chấp nhận chữ cái không dấu, số và dấu_";
        msgElement.classList.add("MsgEff002");
        setTimeout(() => {msgElement.classList.remove("MsgEff002");}, 500);
        UsElement.focus();
        UsElement.select();
        return;
    }
    //🔸PWD
    if (!validRegex.test(_password)) {
        msgElement.innerText = "Mật khẩu chứa ký tự không hợp lệ! (Chỉ chấp nhận chữ cái không dấu, số và dấu_";
        msgElement.classList.add("MsgEff002");
        setTimeout(() => {msgElement.classList.remove("MsgEff002");}, 500);
        PwdElement.focus();
        PwdElement.select();
        return;
    }

    /*GỬI DỮ LIỆU LÊN SERVER & NHẬN KẾT QUẢ VÀ PHÂN TÍCH
    >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> */
        try {
            //🔴SEND NEW API
            const username = _username;
            const password = _password;
            const RES = await apiFetch("/api/auth/login", {
                method: "POST",
                body: {username, password}, 
            });   
            //❯❯❯❯
            let redirect = sessionStorage.getItem("url_after_login"); // ✅ lấy lại route cũ
            if(!redirect) redirect = "#Home"; //Nếu biến redirect chưa có giá trị (hoặc mang giá trị phủ định), thì hãy gán cho nó giá trị là "#Home"."
            sessionStorage.removeItem("url_after_login");
            location.hash = redirect;
            INIT_APPROVAL();
    
        } catch (e) {
            console.log(e.msg)
            //Hide msg nếu có ID. time hiển thị
            showApiError(e,"msg",10);
    
            //SWAL THÔNG BÁO & SAU KHI CLICK OK
            const errorResult = await Swal.fire(
                ConverErrToDataSwal(e)
            );
        }

}//❌❌❌❌❌ End Function 


/* 
⭕ ==================================================================================== */
//♀️Tải một file ảnh từ thư mục assets để chuyển thành Base64 (thường dùng qua fetch)
async function getLogoBase64() {
    const logoPath = window.location.origin + '/assets/img/ITV128x128.png';
    let logoBase64 = '';

    try {
        const response = await fetch(logoPath);
        if (response.ok) {
            const blob = await response.blob();
            logoBase64 = await new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onloadend = () => resolve(reader.result);
                reader.onerror = reject;
                reader.readAsDataURL(blob);
            });
        }
    } catch (error) {
        console.error('Không thể đọc file ảnh:', error);
    }
    console.log(logoBase64)
    return logoBase64;
}


/* BTNCLICK
⭕ ==================================================================================== ⭕ BTNCLICK_DONGY */
async function BTNCLICK_ACTION(instanceID,action){
    //♦️1 KHAI BÁO BIẾT
        const InstanceID    = instanceID ?? ''; // Loại giá trị null hoặc undefined
        const ActionStatus  = action    ?? '';  // Loại giá trị null hoặc undefined
        const ActionNote = document.getElementById('TextComment')?.value || '';

        let KQ;
        let BTN_dongy   =document.getElementById('BTN_dongy');
        let BTN_huy     =document.getElementById('BTN_huy');

        let originalText        = BTN_dongy.textContent;
        let originalText_huy    = BTN_huy.textContent;

        let btnAction;
        let btnActionText = '';

    //♦️2 KIỂM TRA ĐIỀU KIỆN ĐẦU VÀO Number.isInteger(InstanceID)
        //⚠️CHK ID quy trình
        if (!InstanceID){
            Swal.fire({ 
                title: ("ERROR-Instance ID"), 
                html: `<div style="color: #df5718; font-size: 12px; line-height: 1.2; text-align: left"> Không tìm thấy mã tiến trình Work Flow ! Thử reload lại trang </div>`, 
                icon: 'warning' 
            });
        };

        if (!Number.isInteger(Number(InstanceID)) || InstanceID === ''){
            Swal.fire({ 
                title: ("ERROR-Instance ID"), 
                html: `<div style="color: #df5718; font-size: 12px; line-height: 1.2; text-align: left"> Định dạng dữ liệu đầu vào không đúng. Instance ID phải là số nguyên </div>`, 
                icon: 'warning'
            });
        };

        //⚠️CHK hành động của thao tác
        if (!InstanceID){
            Swal.fire({ 
                title: ("ERROR-Action status"), 
                html: `<div style="color: #df5718; font-size: 12px; line-height: 1.2; text-align: left"> Chưa xác định được hành động của thao tác </div>`, 
                icon: 'warning' 
            });
        };

        if (ActionStatus ==='DONGY') {
            btnAction       = BTN_dongy;
            btnActionText   = BTN_dongy.textContent;
        } else {
            btnAction       = BTN_huy;
            btnActionText   = BTN_huy.textContent;
        }


    
    //♦️3 GỌI API & PHÂN TÍCH KẾT QUẢ     
    try {
        // Tạm khóa nút ấn
        BTN_dongy.disabled = true;
        BTN_dongy.disabled = true;
        btnAction.innerText = "Đang trong quá trình xử lý...";

        //Thu thập dữ liệu
        const Action = {
            InstanceID   : parseInt(InstanceID,10),
            ActionStatus,
            ActionNote
        };

        // Gọi API
        KQ = await apiFetch("/api/modules/YCMH_DuyetPhieu_BtnAction", {
            method: "POST",
            body: Action
        });

        
        //Phân tích kết quả và thông báo
        const color = ActionStatus === 'DONGY' ? '#0836cf' : '#cf2608';
        const msgHTML = `<div style="color: ${color}; font-size: 12px; line-height: 1.2; text-align: center">${KQ.msg}</div>`;
        console.log("KQ",KQ);
        
            //✅ SUCCESS
            Swal.fire({ 
                title: KQ.title || "SUCCESS", 
                icon: 'success',
                html: msgHTML, 
            }).then((result) => {  
                //Reload page 
                INIT_APPROVAL(); 
                //Send mail thông báo
                apiFetch("/api/modules/YCMH__WF_SENDMAIL", {
                    method: "POST",
                    body: InstanceID
                });
                // TẠO FILE PDF
                if (KQ.InsFinalStatus === 'Approved' || KQ.InsFinalStatus === 'Rejected') {
                    apiFetch("/api/view/ycmh/createdPDF", {
                    method: "POST",
                    body: InstanceID
                });

                }
            });

    } catch (e) {
        const errorResult = await Swal.fire(
            ConverErrToDataSwal(e)
        );
        if (errorResult.isConfirmed) {  
			return;// Nếu cần ngắt code
		}

    } finally {
        // Luôn mở lại nút bấm (dù thành công hay thất bại)
        if (typeof btnAction !== 'undefined' && btnAction) {

            BTN_dongy.disabled = false;
            BTN_dongy.disabled = false;

            btnAction.textContent = btnActionText;
        }
    }

}////❌❌❌❌❌ End Function 





