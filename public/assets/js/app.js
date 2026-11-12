
import { sidebarRender } from "./modules/sidebar/sidebarRender.js"; //🔵✅
import { HANDLEROUTER } from "./router.js";
import { 
        GW_LoginClick,
        GW_CheckSession,
} from "./getway.js";
import {apiFetch}     from "./core/request/fetch_Api.js";



/*⚪*/  let app;                                                //📌 Khai báo biến app (tùy chọn đặt tên)
/*⚪*/  window.addEventListener("DOMContentLoaded", () => {
            app = document.getElementById("APP");               //📌kết nối với id=APP
            init();                                             //📌gọi hàm init
        });
 
/* 
🟢🟢🟢🟢✅  1.INIT
````````````````````````````````````````````````````````````````````````````````` INITIALIZE */
async function init(){
    try {
        const SIDEBAR=document.getElementById("SIDEBAR");
        let data = await apiFetch("/api/auth/check-session",{method:"GET"});//🔵✅ 
    
        if(data.ok){
            SIDEBAR.style.display = "block";
            AppRender(data.user);   //🔵✅
        }
        
    } catch (error) {
         /*📌 LƯU HASH HIỆN TẠI để khi nào login xong sẽ điều hướng tới hash đó*/
            const currentHash = location.hash || "#Home";           /*📌 khai báo và gán biến currentHast = đường dẫn truy cập, mặc định là Home */
            sessionStorage.setItem("url_after_login", currentHash); /*📌 Lưu biến vào bộ nhớ */    
            SIDEBAR.style.display = "none";
            LoginRender();      //🔵✅
        
    }
    
} /*❌❌❌❌❌ End Function */ 



/* 
🟢🟢🟢🟢✅  2.LOGINRENDER 
````````````````````````````````````````````````````````````````````````````````` RENĐERLOGIN */ 
function LoginRender(){
    //Render frame
    app.innerHTML = `
        <div id="APPLOGIN">
        <div id="LoginFrame">
            <h4>Welcome to IKKA Technology VIET NAM</h4>
            <h2>Login</h2>
            <input id="username" placeholder="Mã nhân viên"><br>
            <input id="password" type="password" placeholder="Mật khẩu"><br>
            <button id="btnLogin">Đăng nhập</button>
            <div id="msg"></div>
            <div id="LoginNote">Nếu chưa có tài khoản hoặc quên mật khẩu vui lòng liên lạc IT</div>
        </div>
        </div>
    `;
    //🚩ADD EVENT ONCLICK
    document.getElementById("btnLogin").addEventListener("click", LoginClick);//🔵✅

    //🚩ENTER trong ô username = chuyển xuống password
    document.getElementById("username").addEventListener("keydown", function(e){
            if(e.key === "Enter"){
                document.getElementById("password").focus();
            }
        }
    );
        
    //🚩ENTER trong ô password = login 
    document.getElementById("password").addEventListener("keydown", function(e){
            if(e.key === "Enter"){
                LoginClick(); //🔵✅  // 👉 thêm event gọi hàm LoginClick
            }
        }
    );

}/*❌❌❌❌❌ End Function */ 



/* 
🟢🟢🟢🟢✅ 3.AppRender
````````````````````````````````````````````````````````````````````````````````` RENĐER APP */ 
function AppRender(user) {
    window.currentUser = user; 
    //1. build header righit
    document.getElementById("HeaderRight").innerHTML = `
        <span style="margin-right:10px; font-size:11px">
        ${user.UsDisplay} | ${user.DeptName} | ${user.UsMemberCode} |
        </span>
        <button id="btnLogOut">Logout</button>
    `;
//2. Add event click Logout
    document.getElementById("btnLogOut").addEventListener("click", LogoutClick);
//3. RENDER SIDEBAR
    sidebarRender(user);//🔵✅
//4. HASH CHANGE 
    window.removeEventListener("hashchange", HANDLEROUTER);
    window.addEventListener("hashchange", HANDLEROUTER);

/* 👉 chạy lần đầu (fix F5 mất highlight) */
    HANDLEROUTER();//🔵✅

}/*❌❌❌❌❌ End Function */ 








/* 
🟢🟢🟢🟢✅ 4.LoginClick
`````````````````````````````````````````````````````````````````````````````````☘️ LoginClick */
async function LoginClick(){   

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
        setTimeout(() => {msgElement.classList.remove("MsgEff002");}, 500);
        // Xóa msg sau 3s và focus vào ô nhập liệu
        setTimeout(()=>{msgElement.innerHTML="";},  3000);
        username.focus();
        return;
    }

    // 2. Kiểm tra độ dài (Nhỏ hơn 4 ký tự)
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
    // 3. Kiểm tra ký tự không hợp lệ
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
    //PWD
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
    let payload = {
        _username,
        _password
    };
    try {

        const data = await apiFetch("/api/auth/login", {
                method: "POST",
                body: payload, //KHÔNG CẦN Stringify 
        }); 
        
        if(data.ok){
            let redirect = sessionStorage.getItem("url_after_login"); // ✅ lấy lại route cũ
            if(!redirect) redirect = "#Home"; //Nếu biến redirect chưa có giá trị (hoặc mang giá trị phủ định), thì hãy gán cho nó giá trị là "#Home"."
            sessionStorage.removeItem("url_after_login");
            location.hash = redirect;
            init();//🔵✅
        }
        
    } catch (error) {
        document.getElementById("msg").innerText = error.mesage();
    }
    
}/*❌❌❌❌❌ End Function */ 




/* 
🟢🟢🟢🟢✅5.LogoutClick
`````````````````````````````````````````````````````````````````````````````````☘️ LOGOUT */
async function LogoutClick(){


    await fetch("/../../api/user/logout.php",{credentials:"include"});
        document.getElementById("HeaderRight").innerHTML = "";  /* CLEAR */
        document.getElementById("SIDEBAR").innerHTML = "";      /* CLEAR */

    location.hash = ""; 
    init();   
}/*❌❌❌❌❌ End Function */ 
