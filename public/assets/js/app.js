
import { sidebarRender } from "./modules/sidebar/sidebarRender.js"; //🔵✅
import { HANDLEROUTER } from "./router.js";

import { apiFetch } from "./core/request/fetch_Api.js";
import { showApiError } from "./core/request/fetch_ApiShowError.js";
import { GuestRender, GUEST_ROUTES } from "./modules/guest/guestRender.js";
import { renderUserMenu } from "./core/ui/userMenu.js";



/*⚪*/  let app;
/*⚪*/  window.addEventListener("DOMContentLoaded", () => {
    app = document.getElementById("APP");
    window.addEventListener("hashchange", APPROUTER);
    APPROUTER();
});


/*
🟢🟢🟢🟢✅  1.APPROUTER — router chung cho 2 giao diện
    Hash Guest (GUEST_ROUTES) → giao diện Guest (vẫn hiện user nếu đã login)
    Hash còn lại              → giao diện ERP, BẮT BUỘC check session
````````````````````````````````````````````````````````````````````````````````` */
async function APPROUTER() {
    let key = location.hash.replace("#", "");
    if (!key) {
        history.replaceState(null, "", "#Welcome");
        key = "Welcome";
    }

    const user = await checkSession();
    if (key !== location.hash.replace("#", "")) return;     // hash đã đổi trong lúc chờ API

    //🟢 GUEST
    if (GUEST_ROUTES.includes(key)) {
        if (key === "Login" && user) { location.hash = "#Home"; return; }
        if (key !== "Login") sessionStorage.removeItem("url_after_login");
        window.currentUser = user;
        GuestRender(key, user, { onLogin: LoginClick, onLogout: LogoutClick });
        return;
    }

    //🟢 ERP — chưa login → lưu hash, chuyển sang #Login
    if (!user) {
        sessionStorage.setItem("url_after_login", "#" + key);
        location.hash = "#Login";
        return;
    }
    AppRender(user);

} /*❌❌❌❌❌ End Function */


async function checkSession() {
    try {
        const data = await apiFetch("/api/auth/check-session", { method: "GET" });
        return data.user;
    } catch {
        return null;
    }
}



/*
🟢🟢🟢🟢✅ 3.AppRender — mỗi lần đi từ Guest sang ERP: dựng lại header/sidebar (reset active)
````````````````````````````````````````````````````````````````````````````````` RENĐER APP */
function AppRender(user) {
    window.currentUser = user;

    if (document.body.classList.contains("guest")) {
        document.body.classList.remove("guest");
        renderUserMenu(document.getElementById("HeaderRight"), user, LogoutClick);
        sidebarRender(user);
    }

    HANDLEROUTER();

}/*❌❌❌❌❌ End Function */








/* 
🟢🟢🟢🟢✅ 4.LoginClick
`````````````````````````````````````````````````````````````````````````````````☘️ LoginClick */
async function LoginClick() {

    const _username = document.getElementById("username").value;
    const _password = document.getElementById("password").value;
    let msgElement = document.getElementById("msg");
    const UsElement = document.getElementById("username");
    const PwdElement = document.getElementById("password")

    /*KIỂM TTRA CÁC TRƯỜNG NHẬP LIỆU
    >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> */
    // 1.CHƯA NHẬP DỮ LIỆU
    if (_username === "" || _password === "") {
        msgElement.innerText = "Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu!";
        // Tạo và Xóa class hiệu ứng sau 400ms 
        msgElement.classList.add("MsgEff002");
        setTimeout(() => { msgElement.classList.remove("MsgEff002"); }, 500);
        // Xóa msg sau 3s và focus vào ô nhập liệu
        setTimeout(() => { msgElement.innerHTML = ""; }, 3000);
        UsElement.focus();
        return;
    }

    // 2. Kiểm tra độ dài (Nhỏ hơn 4 ký tự)
    if (_username.length < 4 || _password.length < 4) {
        msgElement.innerText = "Tài khoản và mật khẩu phải có ít nhất 4 ký tự!";
        msgElement.classList.add("MsgEff002");
        setTimeout(() => { msgElement.classList.remove("MsgEff002"); }, 500);

        setTimeout(() => { msgElement.innerHTML = ""; }, 3000);
        if (_username.length < 4) {
            UsElement.focus();
            UsElement.select();
        } else {
            PwdElement.focus();
            PwdElement.select();

        }
        return;
    }
    // 3. Kiểm tra ký tự không hợp lệ
    // Định nghĩa regex: Chỉ cho phép chữ cái không dấu (a-z, A-Z), chữ số (0-9) và dấu gạch dưới (_)
    const validRegex = /^[a-zA-Z0-9_]+$/;
    //USER
    if (!validRegex.test(_username)) {
        msgElement.innerText = "Tài khoản chứa ký tự không hợp lệ! (Chỉ chấp nhận chữ cái không dấu, số và dấu_";
        msgElement.classList.add("MsgEff002");
        setTimeout(() => { msgElement.classList.remove("MsgEff002"); }, 500);
        UsElement.focus();
        UsElement.select();
        return;
    }
    //PWD
    if (!validRegex.test(_password)) {
        msgElement.innerText = "Mật khẩu chứa ký tự không hợp lệ! (Chỉ chấp nhận chữ cái không dấu, số và dấu_";
        msgElement.classList.add("MsgEff002");
        setTimeout(() => { msgElement.classList.remove("MsgEff002"); }, 500);
        PwdElement.focus();
        PwdElement.select();
        return;
    }

    /*GỬI DỮ LIỆU LÊN SERVER & NHẬN KẾT QUẢ VÀ PHÂN TÍCH
    >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> */
    let payload = {
        _username,
        _password
    };
    try {

        const data = await apiFetch("/api/auth/login", {
            method: "POST",
            body: payload, //KHÔNG CẦN Stringify 
        });

        if (data.ok) {
            //✅ quay lại trang ERP bị chặn trước đó, nếu không có → #Welcome
            const redirect = sessionStorage.getItem("url_after_login") || "#Welcome";
            sessionStorage.removeItem("url_after_login");
            location.hash = redirect;
        }

    } catch (e) {
        showApiError(e, "msg", 10);
    }

}/*❌❌❌❌❌ End Function */




/* 
🟢🟢🟢🟢✅5.LogoutClick
`````````````````````````````````````````````````````````````````````````````````☘️ LOGOUT */
async function LogoutClick() {


    await fetch("/api/user/logout.php", { credentials: "include" });
    document.getElementById("HeaderRight").innerHTML = "";
    document.getElementById("SIDEBAR").innerHTML = "";
    app.innerHTML = "";
    window.currentUser = null;

    if (location.hash === "#Welcome") APPROUTER();
    else location.hash = "#Welcome";
}/*❌❌❌❌❌ End Function */
