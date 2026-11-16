/*
🟢 DROPDOWN USER (kiểu mectrl của Microsoft 365) — dùng chung Guest header & ERP header
*/

const esc = (v) => String(v ?? "").replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#039;" }[c]));

function initials(name) {
    const w = String(name ?? "").trim().split(/\s+/).filter(Boolean);
    if (!w.length) return "?";
    return ((w.length > 1 ? w[0][0] : "") + w[w.length - 1][0]).toUpperCase();
}

export function renderUserMenu(container, user, onLogout) {
    const ini = esc(initials(user.UsDisplay));
    container.innerHTML = `
        <div class="mectrl">
            <button class="mectrl-btn" type="button" aria-haspopup="true" aria-expanded="false">
                <span class="mectrl-name">${esc(user.UsDisplay)}</span>
                <span class="mectrl-avatar">${ini}</span>
            </button>
            <div class="mectrl-panel" hidden>
                <div class="mectrl-head">
                    <span>IKKA TECHNOLOGY VIETNAM</span>
                    <button class="mectrl-logout" type="button">Đăng xuất</button>
                </div>
                <div class="mectrl-body">
                    <span class="mectrl-avatar lg">${ini}</span>
                    <div class="mectrl-info">
                        <b>${esc(user.UsDisplay)}</b>
                        <span>${esc(user.UsEmail)}</span>
                        <span>${esc(user.DeptName)}${user.PosName ? " · " + esc(user.PosName) : ""}</span>
                        <span>Mã NV: ${esc(user.UsMemberCode)}</span>
                    </div>
                </div>
            </div>
        </div>
    `;

    const btn = container.querySelector(".mectrl-btn");
    const panel = container.querySelector(".mectrl-panel");
    btn.addEventListener("click", (e) => {
        e.stopPropagation();
        const open = panel.hidden;
        closeAllMenus();
        panel.hidden = !open;
        btn.setAttribute("aria-expanded", String(open));
    });
    panel.addEventListener("click", (e) => e.stopPropagation());
    container.querySelector(".mectrl-logout").addEventListener("click", onLogout);
}

function closeAllMenus() {
    document.querySelectorAll(".mectrl-panel").forEach((p) => (p.hidden = true));
    document.querySelectorAll(".mectrl-btn").forEach((b) => b.setAttribute("aria-expanded", "false"));
}

document.addEventListener("click", closeAllMenus);
document.addEventListener("keydown", (e) => { if (e.key === "Escape") closeAllMenus(); });
window.addEventListener("hashchange", closeAllMenus);
