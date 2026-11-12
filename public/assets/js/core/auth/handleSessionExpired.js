

import { 

        GW_CheckSession,

} from "./../../getway.js";

/**
 * Hàm xử lý tập trung khi phiên làm việc (Session) hết hạn
 * @param {Function} retryCallback - Hàm sẽ được gọi lại sau khi người dùng login thành công tại chỗ
 */
export async function handleSessionExpired(retryCallback) {

let isUser = await GW_CheckSession();
if (isUser && isUser.ok){return}
if (!isUser.ok && isUser.ErrCode === 401){
//🔷**ErrCode401

    // STEP1.KTra ID #LoginFrame >>> Notfound >>> create
    let loginFrame = document.getElementById("LoginFrame");

    if (!loginFrame) {  //🔹IF01 1.KTraID Not.exists LoginFrame
        try { // tr1
            // 1.1. Tải file loginForm.php về dạng text
            let response = await fetch('loginForm.php'); 
            let htmlText = await response.text();

            // 1.2. Bóc tách lấy thẻ #LoginFrame
            let parser = new DOMParser();
            let doc = parser.parseFromString(htmlText, 'text/html');
            let frameFromPhp = doc.getElementById("LoginFrame"); 
            
            if (frameFromPhp) {
                document.body.appendChild(frameFromPhp);
                loginFrame = document.getElementById("LoginFrame");
            }

        } catch (error) {
            console.error("Không thể tải khung đăng nhập:", error);
            return false;
        } // tr1

    }//🔹IF01 1.KTraID

    // STEP2. #LoginFrame
    if (loginFrame) { 
    //🔹S2I1
        loginFrame.style.display = "block";
        
        const usernameInput = loginFrame.querySelector("#username");
        if (usernameInput) usernameInput.focus();

        const timeoutForm = loginFrame.querySelector("#erp-timeout-form");
        const msgDiv = loginFrame.querySelector("#msg");

        // Sử dụng onsubmit để không bị trùng lặp sự kiện khi gọi nhiều lần
        timeoutForm.onsubmit = async function(e) {
            e.preventDefault(); 
            
            const username = loginFrame.querySelector("#username").value;
            const password = loginFrame.querySelector("#password").value;

            msgDiv.style.color = "#0275d8";
            msgDiv.innerText = "Đang xác thực...";

            try {
                let loginRes = await fetch('/api/auth/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ username, password })
                });

                let loginData = await loginRes.json();

                if (loginData.ok) {
                    msgDiv.innerText = "";
                    loginFrame.querySelector("#password").value = ""; // Xóa password
                    loginFrame.style.display = "none"; // Ẩn popup
                    
                   /*  alert("Đăng nhập lại thành công! Hệ thống đang tải lại dữ liệu..."); */
                    
                    // 🟢 NẾU CÓ TRUYỀN HÀM CALLBACK, TỰ ĐỘNG CHẠY LẠI ĐỂ RE-LOAD DATA
                    if (typeof retryCallback === "function") {
                        retryCallback();
                    }
                } else {
                    msgDiv.style.color = "red";
                    msgDiv.innerText = loginData.msg || "Sai tài khoản hoặc mật khẩu";
                }
            } catch (err) {
                msgDiv.style.color = "red";
                msgDiv.innerText = "Lỗi kết nối hệ thống.";
                console.error(err);
            }
        };
        return true;
    } //🔹S2I1
    return false;
} //🔷**ErrCode401
} // * END FUNC