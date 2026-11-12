import { 
        GW_getUsersDetail,
        GW_ChangePass,
        GW_SaveUser,
        GW_CheckSession,

} from "./../../getway.js";

import { handleSessionExpired } from "./../../core/auth/handleSessionExpired.js"; //🔒
//handleSessionExpired(accountRender); //>>>🔳 Auth001

/* 
🟢BEGIN
````````````````````````````````````````````````````````````````````````````````` */
export async function accountRender() {
console.log("🚩 accountRender 🔈  accountRender()")
handleSessionExpired(accountRender); //🔳Auth001


const app = document.getElementById("APP");

    let res = await GW_getUsersDetail();
    if (!res.ok) {
        app.innerHTML = "Xảy ra lỗi chưa xác định";
        return;
    }
   
    /////////////////////////////////////////////////
    let userData = res.user.user;  
    let configData = res.user.config;
    /////////////////////////////////////////////////
    // 💬Tạo nội dung các hàng cho bảng bằng cách duyệt qua Object data.user
    const tableRows = Object.entries(userData).map(([key, value]) => {
        let _key = configData[key].label;
        let _satus = configData[key].isReadOnly==='isReadOnly' ? 'disabled' : '';
        return `
            <tr>
                <td><label>${_key}</label></td>
                <td><input class="rowsTable" 
                    id="${key}" 
                    value="${value || ''}"
                    ${_satus}
                    style="${_satus ? 'background-color: #f0f0f0; ' : ''}"
                    >
                </td>
            </tr>
        `;
    }).join("");

    // Render cấu trúc HTML
    app.innerHTML = `
    <div id="ACCOUNTFRAME" style="max-width: 500px; margin-left: 40px; margin-top: 20px">
        <div id="AccFrameHEADER">
            <div id="AccFrameHeaderL"></div>
            <div id="AccFrameHeaderM"><h2>👤 Account Information</h2></div>
            <div id="AccFrameHeaderR"></div>
        </div>

        <div id="AccFrameCONTENTS">
            <table class="account-table">
                ${tableRows}
            </table>

            <div class="account-actions"style="margin-top: 20px;" >
                <button class="btn" id="btnSave">💾 Save Changes</button>
                <button class="btn" id="btnChangePwd">🔑 Change Password</button>
            </div>
            <div id="accountMsg" style="margin-top: 20px;font-weight: 600;"></div>
        </div>
    </div>
    `;

// 💬===== XỬ LÝ LƯU (SAVE) =====
    document.getElementById("btnSave").onclick = async () => {
        //💬 Tự động thu thập dữ liệu từ các input (chỉ lấy những trường được phép sửa)
        let payload = {};
        Object.keys(userData).forEach(key => {
            if (!readOnlyFields.includes(key)) {
                payload[key] = document.getElementById(key).value;
            }
        });
  
        let res = await GW_SaveUser (payload);  

        const msg = document.getElementById("accountMsg");
        msg.innerText = "Saved ✅";
        setTimeout(() => msg.innerText = "", 3000); // Ẩn thông báo sau 3s
        
    };


    // ===== CHUYỂN TRANG ĐỔI MẬT KHẨU =====
    document.getElementById("btnChangePwd").onclick = () => {
        renderChangePassword();
    };
}//END❌


/* 
🟢BEGIN
````````````````````````````````````````````````````````````````````````````````` */
export async function renderChangePassword() {
console.log("🚩 accountRender 🔈2.  renderChangePassword()")

    const app = document.getElementById("APP");

    // 📝1. Render giao diện sử dụng các Class trong components.css và layout.css đã dựng
    app.innerHTML = `
    <div id="CHANGEPWDFRAME" class="card" style="max-width: 500px; margin-left: 40px; margin-top: 20px">
        <div id="AccFrameHEADER">
            <div id="AccFrameHeaderM"><h2>🔑 Change Password</h2></div>
        </div>

        <div id="AccFrameCONTENTS">
            <div class="form-group"> 
                <label class="form-label">Mật khẩu hiện tại * </label>
                <input type="password" id="oldPwd" class="form-control" placeholder="nhập mật khẩu hiện tại">
            </div>
            
            <div class="form-group">
                <label class="form-label">Mật khẩu mới * </label>
                <input type="password" id="newPwd" class="form-control" placeholder="nhập mật khẩu mới Minimum 6 characters">
            </div>

            <div class="form-group"> 
                <label class="form-label">Nhập lại mật khẩu mới * </label>
                <input type="password" id="confirmPwd" class="form-control" placeholder="Re-type new password">
            </div>

            <div class="account-actions">
                <button id="btnUpdatePwd" class="btn">Update Password</button>
                <button id="btnBackAccount" class="btn">Cancel</button>
            </div>

            <div id="pwdMsg" style="margin-top: 25px;"></div>
        </div>
    </div>
    `;

    // 📝2. Nút Quay lại trang thông tin tài khoản
    document.getElementById("btnBackAccount").onclick = () => {
        accountRender(); // Gọi lại hàm cũ để quay về
    };

    // 📝3. Xử lý logic Đổi mật khẩu Khi Click

    document.getElementById("btnUpdatePwd").onclick = async () => {
        ChangePass();
    };
    
}//END❌




/*  
🟢BEGIN
☘️ ◆▪▪▪ ◆▪▪▪◆▪▪▪◆▪▪▪◆▪▪▪◆▪▪▪◆▪▪▪◆▪▪▪◆▪▪▪◆▪▪▪◆▪▪▪◆▪▪▪◆▪▪▪◆▪▪▪◆▪▪▪◆▪▪▪◆▪▪▪◆▪▪▪◆▪▪▪◆▪▪▪☘️*/
async function ChangePass () {

    const app = document.getElementById("APP");
    let isUser = await GW_CheckSession();

    if (!isUser.ok && isUser.ErrCode === 401){
        handleSessionExpired(ChangePass); 
        return;
    }
        

        const oldPwd = document.getElementById("oldPwd").value;
        const newPwd = document.getElementById("newPwd").value;
        const confirmPwd = document.getElementById("confirmPwd").value;
        const msg = document.getElementById("pwdMsg");
        
        // 📝3.1 Kiểm tra logic Client-side (Validation) ---
        if (!oldPwd) {
            msg.innerText = "❌ Mật khẩu hiện tại Không được để trống";
            setTimeout(() => msg.innerText = "", 3000); 
            msg.style.color = "red";
            return;
        }
                if (!newPwd) {
                    msg.innerText = "❌ Mật khẩu mới không được để trống";
                    msg.style.color = "red";

                    return;
                }
                        if (!confirmPwd) {
                            msg.innerText = "❌ nhập lại mật khẩu mới ! không được để trống";
                            msg.style.color = "red";

                            return;
                        }

        if (newPwd.length < 6) {
            msg.innerText = "❌ Mật khẩu mới phải có ít nhất 6 ký tự";
            msg.style.color = "red";
            return;
        }

        if (newPwd !== confirmPwd) {
            msg.innerText = "❌ Mật khẩu mới và mật khẩu xác nhận không trùng nhau";
            msg.style.color = "red";
            return;
        }

        // --- Gửi dữ liệu lên Server ---
        msg.innerText = "Processing...";
        msg.style.color = "orange";
//************************************************************************************************* CHANGE
        try {
            let result = await GW_ChangePass (oldPwd,newPwd);

            if (result.ok) {
                // THAY ĐỔI THÀNH CÔNG
                msg.innerText = "✅ Password changed Success ✅";
                msg.style.color = "green";
                // Sau 3 giây tự động quay về trang tài khoản
                

            Swal.fire({
                title: 'Thông báo',
                text: result.msg,
                icon: 'error', // Các icon có sẵn: 'success', 'error', 'warning', 'info', 'question'
                confirmButtonText: 'OK'
            }).then((sweetResult) => {
                    if (sweetResult.isConfirmed) { 
                        setTimeout(() => accountRender(), 3000);
                    }
                });

            } else {
                msg.innerText = "❌ " + (result.msg || "Update failed!");
                msg.style.color = "red";
                //========================================================= THONG BÁO LỖI
                Swal.fire({
                    title: 'Thông báo',
                    text: result.msg,
                    icon: 'error', // Các icon có sẵn: 'success', 'error', 'warning', 'info', 'question'
                    confirmButtonText: 'OK'
                }).then((sweetResult) => {
                    if (sweetResult.isConfirmed) { 
                        if (result.ErrCode && result.ErrCode === 401){
                            localStorage.clear();
                            sessionStorage.clear();
                            window.location.href = '/#Home';
                        }
                    }
                });
                //=========================================================


            }
        } 
        catch (error) {
            msg.innerText = "❌ Server error!";
            msg.style.color = "red";
        }
}//END❌
/*  //**************************************************   
            Swal.fire({
                title: 'Thông báo',
                text: result.msg,
                icon: 'error', // Các icon có sẵn: 'success', 'error', 'warning', 'info', 'question'
                confirmButtonText: 'OK'
            }).then((sweetResult) => {
                if (sweetResult.isConfirmed) { 
                    if (result.ErrCode && result.ErrCode === 401){
                        localStorage.clear();
                        sessionStorage.clear();
                        window.location.href = '/#Home';
                    }
                }
            }); */