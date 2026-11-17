# Frontend SPA (public/assets)

Chỉ nạp khi làm việc trong `public/assets/`.

## Frontend SPA — 2 giao diện, 1 router

`public/index.php` là SPA duy nhất, chứa 2 giao diện. Chuyển qua lại bằng class `guest` trên `<body>` (CSS ẩn/hiện trong `welcome.css`):

| Giao diện | Container | Hash | Session |
|-----------|-----------|------|---------|
| **Guest** (public) | `#GUEST` | `#Welcome` (mặc định), `#About`, `#Services`, `#Contact`, `#Login` | Không bắt buộc. Nếu đã login thì góc phải hiện dropdown user thay vì nút LOGIN |
| **ERP** (local) | `#HEADER` + `#SIDEBAR` + `#APP` | `#Home`, `#listYCMH`, `#Account`... (key trong `menuConfig.js`) | **Bắt buộc** — check session mỗi lần đổi hash |

**Luồng điều phối** — `APPROUTER()` trong `assets/js/app.js` là listener `hashchange` **duy nhất** cho việc chuyển trang:
1. Hash rỗng → `#Welcome`
2. Gọi `/api/auth/check-session` lấy `user` (hoặc `null`)
3. Hash thuộc `GUEST_ROUTES` → `GuestRender(key, user, {onLogin, onLogout})`
4. Hash khác (ERP): chưa login → lưu hash vào `sessionStorage.url_after_login` → `#Login`; đã login → `AppRender(user)` → `HANDLEROUTER()` (`router.js`) kiểm tra `MenuPermission` rồi render trang

**Chuyển giao diện (giữ session):** nav `SYSTEM` (Guest) → `#Home`; nút `⌂ WELCOME` (`#btnBackGuest` trong `#HeaderLeft`) → `#Welcome`.
**Login OK** → `url_after_login` hoặc `#Welcome`. **Logout** (trong dropdown) → `#Welcome`.

**Quy tắc khi thêm trang:**
- Trang ERP: thêm key vào `MenuPermission`/`MENU` (`menuConfig.js`) + `case` trong `RENDERCONTENT` (`router.js`). Không thêm listener `hashchange` riêng.
- Trang Guest: thêm key vào `GUEST_ROUTES` + hàm trong `PAGES` (`modules/guest/guestRender.js`).
- Hash 2 giao diện **không được trùng nhau**.
- CSS Guest phải nằm trong `#GUEST` để không lan sang ERP.
- **Không dùng id `HEADER`/`SIDEBAR`/`APP`/`GUEST` trong nội dung module** và không viết selector toàn cục (`html`, `body`, `h1`...) trong CSS module — CSS module (`loadModuleCSS`) không bị gỡ khi rời trang.

**Layout ERP (`layout/body.css`)** — vừa khít 1 màn hình: `body:not(.guest)` là grid `30px | 1fr` × `160px | 1fr`, `html/body` `overflow:hidden`. Chỉ `#SIDEBAR` và `#APP` tự cuộn. Không dùng `100vw`/`100vh` trong module — dùng `100%` của `#APP`.
**Sidebar**: dựng lại mỗi lần vào ERP từ Guest (reset active); `SyncSidebarWithHash` (sidebarRender.js) là nơi duy nhất set `.active`/`.sub-active`.
