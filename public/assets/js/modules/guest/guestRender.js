/*
🟢 GIAO DIỆN GUEST (public) — không cần đăng nhập
   Hash: #Welcome (mặc định) | #About | #Services | #Contact | #Login
   Nền + topbar dựng 1 lần, chỉ thay nội dung <section class="content">.
   Điều phối bởi APPROUTER trong app.js.
*/
import { renderUserMenu } from "../../core/ui/userMenu.js";

export const GUEST_ROUTES = ["Welcome", "About", "Services", "Contact", "Login"];

const PAGES = {
    Welcome: () => `
        <div class="eyebrow">A softer digital space</div>
        <h1>Nơi ý tưởng tạo nên giá trị</h1>
        <p class="description">
            Công ty TNHH Công Nghệ IKKA Việt Nam là công ty 100% vốn đầu tư từ Nhật Bản.
            Chúng tôi chuyên sản xuất các linh kiện dành cho ô tô, xe máy.
            Với nhu cầu mở rộng sản xuất cho dây chuyền mới, chúng tôi có nhu cầu tuyển 100 Công nhân sản xuất.
            Hãy gửi hồ sơ ngay cho chúng tôi và trở thành thành viên ưu tú của mái nhà Công ty TNHH Công Nghệ IKKA Việt Nam.
        </p>
        <div class="actions">
            <a class="primary" href="#Contact" style="text-decoration:none">Thông tin tuyển dụng ↗</a>
            <a class="secondary" href="#About">Tìm hiểu thêm →</a>
        </div>
    `,
    About: () => `
        <div class="eyebrow">About</div>
        <h1>IKKA Technology Việt Nam</h1>
        <p class="description">
            Công ty 100% vốn đầu tư từ Nhật Bản, chuyên sản xuất linh kiện cho ô tô và xe máy.
        </p>
        <p class="description"><i>Nội dung đang được cập nhật.</i></p>
    `,
    Services: () => `
        <div class="eyebrow">Services</div>
        <h1>Sản phẩm &amp; dịch vụ</h1>
        <p class="description"><i>Nội dung đang được cập nhật.</i></p>
    `,
    Contact: () => `
        <div class="eyebrow">Contact</div>
        <h1>Liên hệ</h1>
        <p class="description"><i>Nội dung đang được cập nhật.</i></p>
    `,
    Login: () => `
        <div class="eyebrow">Ikka ERP</div>
        <h1>ITV SYSTEM</h1>
        <div id="GuestLogin" class="login-card">
            <label>Member code
                <input id="username" autocomplete="username" placeholder="Mã nhân viên">
            </label>
            <label>Password
                <input id="password" type="password" autocomplete="current-password" placeholder="Mật khẩu">
            </label>
            <div id="msg"></div>
            <div class="actions">
                <button id="btnLogin" class="primary" type="button">Login</button>
                <a class="secondary" href="#Welcome"> ← Quay lại</a>
            </div>
            <p class="login-note">Nếu chưa có tài khoản hoặc quên mật khẩu vui lòng liên lạc với bộ phận IT</p>
        </div>
    `,
};





//🟡
function buildShell(root) {
    root.innerHTML = `
        <main class="scene">
            <div class="glow one"></div>
            <div class="glow two"></div>
            <div class="ribbon c"></div>
            <div class="shape square s1"></div>
            <div class="shape circle s2"></div>
            <div class="shape circle s3"></div>
            <div class="shape square s4"></div>
            <div class="shape circle s5"></div>
            <div class="shape square s6"></div>
            <div class="particles"></div>

            <header class="topbar">
                <div class="brand">
                    <span class="brand-mark">
                        <img src="./assets/img/ITV128x128.png" alt="ITV Logo">
                    </span>
                    IKKA TECHNOLOGY VIETNAM CO.,LTD
                </div>

                <nav aria-label="Điều hướng chính">
                    <a href="#Welcome" data-guest="Welcome">HOME</a>
                    <a href="#About" data-guest="About">ABOUT</a>
                    <a href="#Services" data-guest="Services">SERVICE</a>
                    <a href="#Contact" data-guest="Contact">CONTACT</a>
                    <a id="SWITCHPAGES" href="#Home">SYSTEM</a>
                </nav>
                <div id="GuestRight"></div>
            </header>

            <section class="content"></section>
        </main>
    `;
    //TẠO particles
    const layer = root.querySelector(".particles");
    for (let i = 0; i < 100; i++) {
        const dot = document.createElement("i");
        dot.className = "particle";
        dot.style.left = `${Math.random() * 100}%`;
        dot.style.top = `${Math.random() * 100}%`;
        dot.style.setProperty("--size", `${0.5 + Math.random() * 2.5}px`);
        dot.style.setProperty("--duration", `${7 + Math.random() * 10}s`);
        dot.style.setProperty("--delay", `${-Math.random() * 10}s`);
        layer.appendChild(dot);
    }
}


//🟡 BIND EVENT FORM LOGIN
function bindLoginEvents(onLogin) {
    const user = document.getElementById("username");
    const pwd = document.getElementById("password");

    document.getElementById("btnLogin").addEventListener("click", onLogin);
    user.addEventListener("keydown", (e) => { if (e.key === "Enter") pwd.focus(); });
    pwd.addEventListener("keydown", (e) => { if (e.key === "Enter") onLogin(); });
    user.focus();
}


//🟡 GÓC PHẢI TOPBAR: chưa login → nút LOGIN | đã login → dropdown user
function renderGuestRight(root, key, user, onLogout) {
    const right = root.querySelector("#GuestRight");
    if (user) {
        renderUserMenu(right, user, onLogout);
        return;
    }
    right.innerHTML = key === "Login" ? "" : `<button class="cta" type="button">LOGIN ↗</button>`;
    right.querySelector(".cta")?.addEventListener("click", () => { location.hash = "#Login"; });
}


//🟡 RENDER — key: guest route | user: object hoặc null
export function GuestRender(key, user, { onLogin, onLogout }) {
    document.body.classList.add("guest");

    const root = document.getElementById("GUEST");
    if (!root.querySelector(".scene")) buildShell(root);

    const content = root.querySelector(".content");
    content.classList.toggle("is-login", key === "Login");
    content.innerHTML = PAGES[key]();

    root.querySelectorAll("nav a").forEach((a) => {
        a.classList.toggle("active", a.dataset.guest === key);
    });

    renderGuestRight(root, key, user, onLogout);
    if (key === "Login") bindLoginEvents(onLogin);
}
