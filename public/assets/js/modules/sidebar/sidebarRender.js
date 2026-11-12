
import { MENU, MenuPermission } from "./menuConfig.js";

//RENDERSIDEBAR
/* 
🟢🟢🟢🟢✅ 1. sidebarRender (Đã sửa để lọc Subitem theo UsType)
`````````````````````````````````````````````````````````````````````````````````  */
export function sidebarRender(user) {

    let html = "";
    MENU.forEach(group => {
        
        if (!group.roles.includes(user.UsType)) return; //Kiểm tra quyền của Group
        let groupHtml = "";                             // Bắt đầu render group
        
        group.items.forEach(item => {
        // ===== TRƯỜNG HỢP MENU CÓ SUB ===== 
            if (item.children) {
                // 💡 LỌC SUBITEM: Chỉ giữ lại những sub có MenuPermission >= user.UsType
                // Điều kiện là: MenuPermission[key] >= user.UsType
                const validChildren = item.children.filter(sub => {
                    const requiredRole = MenuPermission[sub.key];
                    return requiredRole !== undefined && requiredRole >= user.UsType;
                });

                // Nếu sau khi lọc mà không còn con nào thì không render Menu cha này
                if (validChildren.length > 0) {
                    groupHtml += `
                        <div class="menu has-sub">
                            <div class="menu-title">
                                <span class="menu-icon">${item.icon}</span>
                                <span>${item.name}</span>
                            </div>
                            <div class="submenu">
                                ${validChildren.map(sub => `
                                    <div class="submenu-item" data-key="${sub.key}">
                                        ${sub.name}
                                    </div>
                                `).join("")}
                            </div>
                        </div>
                    `;
                }
            } 
            // ===== TRƯỜNG HỢP MENU THƯỜNG (KHÔNG CÓ SUB) =====
            else {
                const requiredRole = MenuPermission[item.key];
                if (requiredRole !== undefined && requiredRole >= user.UsType) {
                    groupHtml += `
                        <div class="menu single" data-key="${item.key}">
                            <div class="menu-title">
                                <span class="menu-icon">${item.icon}</span>
                                <span>${item.name}</span>
                            </div>
                        </div>
                    `;
                }
            }
        });

        // Chỉ thêm group vào html nếu group đó có chứa item hợp lệ
        if (groupHtml !== "") {
            html += `<div class="frame">`;
            html += `<h4>${group.title}</h4>`;
            html += groupHtml;
            html += `</div>`;
        }
    });

    document.getElementById("SIDEBAR").innerHTML = html;

    BindSidebarEvent();     //🔵✅
    SyncSidebarWithHash();  //🔵✅


}/*❌❌❌❌❌ End Function */ 




/* 
🟢🟢🟢🟢✅  2.bindSidebarEvent 
````````````````````````````````````````````````````````````````````````````````` bindSidebarEvent */ 
function BindSidebarEvent(){

    // ===== CLICK MENU CHA (Chỉ làm nhiệm vụ đóng/mở visual) =====
    document.querySelectorAll(".menu.has-sub .menu-title")
    .forEach(el => {
        el.addEventListener("click", function(){
            const parent = this.closest(".menu");
            
            // Chỉ toggle đóng mở bình thường
            parent.classList.toggle("open");

            // Đóng các menu khác để gọn giao diện (ERP Style)
            //document.querySelectorAll(".menu.has-sub")
            //    .forEach(m => {
            //        if(m !== parent) m.classList.remove("open");
            //    });

        });
    });

    // ===== CLICK MENU THƯỜNG =====
    document.querySelectorAll(".menu.single")
    .forEach(el => {
        el.addEventListener("click", function(){
            location.hash = this.dataset.key;
        });
    });

    // ===== ĐOẠN 3: CLICK VÀO MENU CON (.submenu-item) =====
    document.querySelectorAll(".submenu-item")
    .forEach(el => {
        el.addEventListener("click", function(e){
            e.stopPropagation();
            
            // 1. Xóa class đỏ cũ trên TẤT CẢ các menu-title khác
            document.querySelectorAll(".menu-title.sub-active")
                .forEach(title => title.classList.remove("sub-active"));

            // 2. Tìm menu cha trực tiếp của submenu này
            const parentMenu = this.closest(".menu");
            if (parentMenu) {
                // Tìm thẻ .menu-title của menu cha này và nhuộm ĐỎ nó
                const menuTitle = parentMenu.querySelector(".menu-title");
                if (menuTitle) {
                    menuTitle.classList.add("sub-active");
                }
            }

            // 3. Đổi hash để kích hoạt chuyển trang
            location.hash = this.dataset.key;
        });
    });

    // ===== HASH CHANGE =====
    window.removeEventListener("hashchange", SyncSidebarWithHash);
    window.addEventListener("hashchange", SyncSidebarWithHash); 
}

/* ===== SYNC SIDEBAR WITH HASH ===== */ 
function SyncSidebarWithHash(){

    const hash = location.hash.replace("#", "");
    if (!hash) return;

    // Chỉ dọn dẹp class active của các item con và menu đơn (Không đụng vào class sub-active của menu-title)
    document.querySelectorAll(".submenu-item.active, .menu.single.active")
        .forEach(el => el.classList.remove("active"));

    // ===== TRƯỜNG HỢP: SUBMENU =====
    const sub = document.querySelector(`.submenu-item[data-key="${hash}"]`);
    if(sub){
        sub.classList.add("active");

        const parent = sub.closest(".menu");
        if(parent){
            parent.classList.add("open");
            // Tự động kích hoạt màu đỏ cho menu-title lần đầu khi tải lại trang (F5) dựa vào URL
            const menuTitle = parent.querySelector(".menu-title");
            if(menuTitle) menuTitle.classList.add("sub-active");
        }
        return;
    }

    // ===== TRƯỜNG HỢP: MENU THƯỜNG =====
    const menu = document.querySelector(`.menu.single[data-key="${hash}"]`);
    if(menu){
        menu.classList.add("active");
        // Nếu chuyển sang menu thường, xóa màu đỏ của các menu cấp con cũ đi
        document.querySelectorAll(".menu-title.sub-active")
            .forEach(title => title.classList.remove("sub-active"));
    }
}