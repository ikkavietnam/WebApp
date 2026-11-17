# Tổng quan dự án ITV ERP

Tài liệu dành cho người mới tham gia dự án: đọc trong 10 phút để biết hệ thống làm gì, code nằm ở đâu, và một yêu cầu đi qua hệ thống như thế nào.

---

## 1. Dự án này là gì?

Một **web ERP nội bộ** của IKKA Technology Vietnam (ITV), gồm 2 phần trên cùng một trang:

- **Trang giới thiệu (Guest)** — ai cũng xem được: Welcome, About, Services, Contact, Login.
- **Hệ thống ERP** — phải đăng nhập: yêu cầu mua hàng, văn phòng phẩm, tài khoản, quản trị...

Ngoài ra có vài **trang độc lập** dùng qua đường link (không cần vào ERP), ví dụ trang duyệt phiếu gửi qua email.

**Công nghệ:**

| Phần | Dùng gì |
|---|---|
| Backend | PHP 8 |
| Database | SQL Server 2019 (hàm `sqlsrv_*`) |
| Frontend | HTML, CSS, JavaScript thuần (ES module), bảng dữ liệu bằng Tabulator / Handsontable |
| Thư viện PHP (Composer) | PhpSpreadsheet (Excel), PHPMailer (gửi mail), Dompdf (xuất PDF) |
| Server | Apache trong Docker, chạy trên NAS |

---

## 2. Cấu trúc thư mục

```
App/
├── public/                     ← Thư mục web (người dùng truy cập được)
│   ├── index.php               ← Trang chính: chứa cả giao diện Guest và ERP
│   ├── loginForm.php           ← Popup đăng nhập lại khi hết phiên
│   ├── api_gateway.php         ← Cổng nhận mọi request /api/... (chỉ chuyển tiếp sang src/core/ApiRouter.php)
│   ├── .htaccess               ← Đổi URL /api/xxx → api_gateway.php, chặn file nhạy cảm
│   ├── api/user/logout.php     ← Đăng xuất
│   ├── view/ycmh/              ← Trang duyệt phiếu YCMH (mở từ link trong email)
│   ├── RequestRemoteAccess/    ← Form xin quyền truy cập từ xa (công khai)
│   ├── downloadAttach/         ← Trang tải file đính kèm (đang làm dở)
│   └── assets/
│       ├── css/                ← base/ layout/ components/ modules/  (app.css gom tất cả)
│       ├── js/
│       │   ├── app.js          ← Router chung: chuyển trang Guest ↔ ERP, login/logout
│       │   ├── router.js       ← Chọn trang ERP cần hiển thị theo #hash
│       │   ├── app.config.js   ← Cấu hình frontend (chế độ test, version, số dòng/trang)
│       │   ├── core/           ← Dùng chung: gọi API, xử lý hết phiên, bảng ERP, menu user
│       │   └── modules/        ← Mỗi chức năng 1 thư mục (YCMH, VPP, account, sidebar...)
│       └── vendor/             ← Thư viện JS bên ngoài (Tabulator, SweetAlert2...)
│
├── src/                        ← Code backend (KHÔNG truy cập trực tiếp từ web)
│   ├── core/
│   │   ├── ApiRouter.php       ← BẢNG ROUTE API + kiểm tra quyền + giới hạn tốc độ
│   │   ├── Database.php        ← Kết nối SQL Server và hàm hỗ trợ (db_connect, CHKQUERY...)
│   │   ├── Authorization.php   ← Kiểm tra đăng nhập / cấp quyền (checkUser, checkLevel)
│   │   ├── Helpers.php         ← Hàm tiện ích
│   │   ├── mapping/            ← Chuyển dữ liệu DB sang dạng trả về client
│   │   └── log/                ← File log lỗi SQL, lỗi xử lý
│   ├── api/
│   │   ├── auth/               ← Đăng nhập, kiểm tra phiên
│   │   ├── users/              ← Thông tin, đổi mật khẩu, tạo/sửa/xóa tài khoản
│   │   └── modules/            ← API theo chức năng (YCMH, RequestRemoteAccess, managerAccount...)
│   ├── config/                 ← Mật khẩu DB, SMB... (KHÔNG commit lên git)
│   ├── vendor/                 ← Thư viện Composer (không commit, cài bằng composer install)
│   └── composer.json
│
└── tools/prepare_gateway.sh    ← Script chỉnh các file API cũ cho tương thích gateway
```

**Quy tắc vàng:** `public/` là thứ người dùng thấy được; mọi logic và mật khẩu nằm trong `src/`.

---

## 3. Các chức năng chính

| Chức năng | Trạng thái | Frontend | Backend |
|---|---|---|---|
| **Đăng nhập / phiên làm việc** | Chạy | `app.js`, `core/auth/` | `src/api/auth/` |
| **YCMH — Yêu cầu mua hàng**: tạo phiếu, danh sách, xem chi tiết, gửi mail duyệt, duyệt qua link email, xuất PDF, xuất Excel | Chạy | `modules/YCMH/`, `view/ycmh/` | `src/api/modules/YCMH/` |
| **Request Remote Access**: nhân viên xin quyền truy cập từ xa, quản lý xác nhận qua email | Chạy | `public/RequestRemoteAccess/` | `src/api/modules/RequestRemoteAccess/` |
| **Tài khoản cá nhân**: xem thông tin, đổi mật khẩu | Chạy | `modules/account/` | `src/api/users/` |
| **Quản trị tài khoản** (Admin): danh sách, thêm mới, phân quyền | Chạy | `modules/managerAccount/` | `src/api/users/`, `modules/managerAccount/` |
| **Quản lý dữ liệu** (Admin): import danh sách máy tính | Chạy | `modules/managerDatabase/` | `modules/managerDatabase/` |
| **Follow Work**: to-do list | Đang làm | `modules/FollowWork/` | — |
| **VPP — Văn phòng phẩm** | Đang làm | `modules/VPP/` | — |
| **Sales Order** | Chưa làm | — | — |

**Phân quyền (UsType):** số càng nhỏ quyền càng cao. `1` = Admin, `3` = người dùng thường, `5` = chỉ xem một số trang.

---

## 4. Một yêu cầu đi qua hệ thống như thế nào?

### Chuyển trang (frontend)

```
Người dùng bấm menu → URL đổi thành #listYCMH
   → app.js (APPROUTER) hỏi server: đã đăng nhập chưa?  (/api/auth/check-session)
       ├─ Chưa → lưu trang muốn vào, chuyển sang #Login
       └─ Rồi  → router.js kiểm tra quyền (menuConfig.js) → vẽ trang YCMH vào khung #APP
```

### Gọi API (backend)

```
JS gọi apiFetch('/api/modules/YCMH__List_1_Load')
   → .htaccess đổi thành api_gateway.php?route=modules/YCMH__List_1_Load
   → ApiRouter.php:
        1. Route có trong bảng không?        Không → 404
        2. Có vượt giới hạn tốc độ không?     Có    → 429
        3. Đủ quyền không?                    Không → 401 / 403
        4. Chạy file src/api/modules/YCMH/YCMH__List_1_Load.php
   → File API truy vấn SQL Server, trả JSON: {"ok": true, "data": ...}
```

---

## 5. Muốn làm việc thường gặp thì sửa ở đâu?

| Việc | Sửa ở đâu |
|---|---|
| Thêm 1 API mới | Tạo file trong `src/api/...` + thêm 1 dòng vào bảng `$routes` trong `src/core/ApiRouter.php` |
| Thêm 1 trang ERP mới | Thêm key vào `menuConfig.js` (menu + quyền) + thêm `case` trong `router.js` |
| Thêm 1 trang giới thiệu (Guest) | `modules/guest/guestRender.js` |
| Đổi giao diện chung | `public/assets/css/layout/` |
| Đổi kết nối database | `src/config/config.php` (chỉ trên máy/server, không commit) |

---

## 6. Cài đặt nhanh trên máy mới

1. `cd src && composer install`
2. Tạo `src/config/config.php` từ `src/config/config.example.php`, điền thông tin SQL Server.
3. Trỏ web server vào thư mục `public/`.
4. Kiểm tra đường dẫn `$appRoot` trong `src/core/ApiRouter.php` và dòng `require_once` trong `public/api_gateway.php` (đang để `/var/www/App` cho Docker).
5. Mở `http://localhost/` → trang Welcome → đăng nhập tại `#Login`.

---

## 7. Điều cần nhớ

- Mọi câu lệnh SQL phải dùng tham số (`sqlsrv_query($conn, $sql, $params)`), không ghép chuỗi.
- Không bao giờ đặt mật khẩu trong code hoặc trong `public/`.
- Quy tắc đặt tên và comment: xem `.claude/rules/naming.md`.
- Commit theo mẫu `feat: ...`, `fix: ...`, `docs: ...`.

---

## Phụ lục: danh sách file (.php .js .css .html)

Không tính thư viện ngoài (`src/vendor/`, `public/assets/vendor/`) và file cấu hình mật (`src/config/`).

### A. Trang & cổng vào (`public/`)

| File | Chức năng |
|---|---|
| `public/index.php` | Trang chính (SPA): chứa khung Guest `#GUEST` và khung ERP `#HEADER` `#SIDEBAR` `#APP` |
| `public/loginForm.php` | Form đăng nhập lại, hiện dạng popup khi hết phiên giữa chừng |
| `public/api_gateway.php` | Cổng API: chỉ chuyển tiếp sang `src/core/ApiRouter.php` |
| `public/api/user/logout.php` | Đăng xuất: hủy session |
| `public/downloadAttach/index.html` | Trang tải file đính kèm (đang làm dở) |
| `public/test_ycmh_pdf.html` | Trang thử nghiệm xuất PDF phiếu YCMH (chỉ dùng khi phát triển) |

### B. Trang độc lập (mở qua link, không qua menu ERP)

| File | Chức năng |
|---|---|
| `public/view/ycmh/approval/index.html` | Trang duyệt phiếu YCMH (link gửi trong email) |
| `public/view/ycmh/approval.js` | Logic trang duyệt: tải phiếu, bấm duyệt/từ chối, tạo PDF |
| `public/view/ycmh/YCMH_TemplateForm.html` | Mẫu giao diện phiếu YCMH dạng in |
| `public/RequestRemoteAccess/index.html` | Form xin quyền truy cập từ xa (công khai) |
| `public/RequestRemoteAccess/js/RequestRemoteAccess.js` | Logic form: tra thông tin nhân viên, chọn quản lý, gửi yêu cầu |
| `public/RequestRemoteAccess/css/RequestRemoteAccess.css` | Giao diện form Request Remote Access |

### C. JavaScript lõi (`public/assets/js/`)

| File | Chức năng |
|---|---|
| `app.js` | Router chung `APPROUTER`: chuyển Guest ↔ ERP, kiểm tra phiên, login/logout |
| `router.js` | `HANDLEROUTER` / `RENDERCONTENT`: kiểm tra quyền và vẽ trang ERP theo `#hash` |
| `app.config.js` | Cấu hình frontend: chế độ test, version (ép tải lại CSS/JS), số dòng mỗi trang |
| `getway.js` | Các hàm gọi API kiểu cũ (`GW_CheckSession`, `GW_getUsersDetail`...) |
| `core/request/fetch_Api.js` | `apiFetch()`: hàm gọi API chuẩn, ném lỗi khi HTTP lỗi hoặc `ok !== true` |
| `core/request/fetch_ApiError.js` | Lớp `ApiError`: chuẩn hóa mọi loại lỗi API |
| `core/request/fetch_ApiShowError.js` | Hiển thị lỗi API ra trang / hộp thoại SweetAlert |
| `core/auth/handleSessionExpired.js` | Xử lý hết phiên: hiện popup đăng nhập lại rồi chạy tiếp thao tác dở |
| `core/ui/userMenu.js` | Dropdown thông tin user ở góc phải (dùng chung Guest và ERP) |
| `core/layout/RESIZABLE.js` | `MakeResizable`: kéo thay đổi độ rộng các cột giao diện |
| `core/tableERP/tableERPBase.js` | `TableBase`: lớp bọc Tabulator dùng chung cho mọi bảng ERP |
| `core/tableERP/tableERPConfig.js` | Cấu hình mặc định cho bảng Tabulator |
| `core/tableERP/tableERPBehavior.js` | Hành vi bảng: phím tắt, di chuyển ô kiểu Excel |
| `core/Utils/helpers.js` | Hàm tiện ích: `debounce`, bỏ dấu tiếng Việt, kiểm tra rỗng, thông báo |
| `core/Utils/styleLoader.js` | `loadModuleCSS`: nạp CSS riêng cho từng trang |

### D. JavaScript theo module (`public/assets/js/modules/`)

| File | Chức năng |
|---|---|
| `guest/guestRender.js` | Giao diện Guest: Welcome, About, Services, Contact, Login |
| `sidebar/menuConfig.js` | Danh sách menu và cấp quyền của từng trang (`MENU`, `MenuPermission`) |
| `sidebar/sidebarRender.js` | Vẽ sidebar theo quyền user, đánh dấu mục đang chọn |
| `account/accountRender.js` | Trang thông tin cá nhân và đổi mật khẩu |
| `managerAccount/ManagerAccount.js` | Admin: danh sách tài khoản, thêm mới, phân quyền |
| `managerDatabase/mngDataBase.js` | Admin: import / quản lý dữ liệu (`DbMNG1-3`) |
| `FollowWork/FollowWork.js` | To-do list (đang làm) |
| `VPP/VPP.js` | Văn phòng phẩm (đang làm) |
| `YCMH/YCMH___Main.js` | Điểm vào trang danh sách YCMH (`YCMH_INIT`) |
| `YCMH/YCMH__List_1_Load.js` | Bước 1: dựng khung trang và tải dữ liệu danh sách phiếu |
| `YCMH/YCMH__List_2_Table.js` | Bước 2: cấu hình bảng danh sách phiếu, tô sáng từ khóa tìm |
| `YCMH/YCMH__List_3_FilterController.js` | Bước 3: bộ lọc trung tâm (`FINTER_CENTER`) |
| `YCMH/YCMH__List_4_Events.js` | Bước 4: sự kiện tìm kiếm, lọc, xem chi tiết phiếu |
| `YCMH/YCMH__Create.js` | Trang tạo phiếu YCMH mới |

### E. CSS (`public/assets/css/`)

| File | Chức năng |
|---|---|
| `app.css` | File tổng: `@import` toàn bộ CSS theo thứ tự |
| `base/fonts.css` | Khai báo font Roboto Condensed |
| `base/base.css` | Reset trình duyệt và biến màu/kích thước dùng chung |
| `layout/body.css` | Lưới bố cục ERP vừa 1 màn hình |
| `layout/header.css` | Thanh header ERP |
| `layout/sidebar.css` | Sidebar ERP |
| `components/components.css` | Nút bấm, icon và thành phần dùng chung |
| `components/tableTabulator.css` | Giao diện bảng Tabulator |
| `components/userMenu.css` | Dropdown user |
| `modules/welcome.css` | Giao diện Guest (mọi selector nằm trong `#GUEST`) |
| `modules/login.css` | Popup đăng nhập lại |
| `modules/YCMH.css` | Giao diện trang YCMH |
| `modules/FollowWork.css` | Giao diện Follow Work (đang trống) |

### F. Backend lõi (`src/core/`)

| File | Chức năng |
|---|---|
| `ApiRouter.php` | Bảng route API, kiểm tra quyền, giới hạn tốc độ, chạy file API |
| `Database.php` | Kết nối SQL Server (`db_connect`), kiểm tra truy vấn (`CHKQUERY`), ghi log |
| `Authorization.php` | `checkUser` (đã đăng nhập chưa), `checkLevel` (đủ cấp quyền chưa) |
| `Helpers.php` | Hàm tiện ích, `RESPONSE_CLIENT` (trả lỗi JSON và dừng) |
| `mapping/UsersMapping.php` | Đổi tên cột DB sang key an toàn trước khi trả về client |

### G. API (`src/api/`)

| File | Route | Chức năng |
|---|---|---|
| `auth/checkSession.php` | `auth/check-session` | Trả trạng thái đăng nhập hiện tại |
| `auth/login.php` | `auth/login` | Đăng nhập, tạo session |
| `users/usersDetail.php` | `users/getUsersDetail` | Lấy thông tin user đang đăng nhập |
| `users/usersSave.php` | `users/SaveUser` | User tự sửa thông tin của mình |
| `users/usersChangePassword.php` | `users/ChangePassword` | Đổi mật khẩu |
| `users/usersProfile.php` | `users/profile` | Admin: xem hồ sơ một user |
| `users/usersNewProfile.php` | `users/NewProfile` | Admin: tạo tài khoản mới |
| `users/usersUpdateProfile.php` | `users/SaveProfile` | Admin: cập nhật tài khoản |
| `users/usersDeleteProfile.php` | `users/DeleteProfile` | Admin: xóa tài khoản |
| `modules/managerAccount/getListUsers.php` | `modules/getListUsers` | Admin: danh sách tài khoản |
| `modules/managerDatabase/NetUploadMAYTINH.php` | `db/upload` | Admin: import file Excel danh sách máy tính |
| `modules/managerNET/getSoftwareInfomation.php` | `modules/getSoftwareInfomation` | Danh sách thiết bị và phần mềm |
| `modules/YCMH/YCMH__List_1_Load.php` | `modules/YCMH__List_1_Load` | Danh sách phiếu YCMH |
| `modules/YCMH/YCMH__List_4_Events_OrderDetail.php` | `modules/YCMH__List_4_Events_OrderDetail` | Chi tiết một phiếu |
| `modules/YCMH/YCMH__Create_Getinfo.php` | `modules/YCMH__Create_Getinfo` | Dữ liệu ban đầu cho form tạo phiếu |
| `modules/YCMH/YCMH__Create_LuuPhieu.php` | `modules/YCMH__Create_LuuPhieu` | Lưu phiếu mới, đính kèm file |
| `modules/YCMH/YCMH__WF_SENDMAIL.php` | `modules/YCMH__WF_SENDMAIL` | Gửi mail theo luồng duyệt |
| `modules/YCMH/YCMH_Duyet_INIT.php` | `modules/YCMH_Duyet_INIT` | Tải dữ liệu cho trang duyệt |
| `modules/YCMH/YCMH_Duyet_BtnAction.php` | `modules/YCMH_DuyetPhieu_BtnAction` | Xử lý nút duyệt / từ chối / hủy |
| `modules/YCMH/YCMH_PDF_Creator.php` | `view/ycmh/createdPDF` | Xuất PDF phiếu, lưu vào thư mục chia sẻ NAS |
| `modules/YCMH/YCMH_Export_Excel.php` | *(chưa có route)* | Xuất Excel danh sách phiếu |
| `modules/YCMH/YCMH__Function_LOAD_Data_PHIEU.php` | *(file dùng chung)* | Hàm đọc dữ liệu phiếu, được các file YCMH khác gọi |
| `modules/RequestRemoteAccess/RRA_getInfoMember.php` | `RRA_getInfoMember` | Tra thông tin nhân viên |
| `modules/RequestRemoteAccess/RRA_getInfoListManager.php` | `RRA_getInfoListManager` | Danh sách quản lý để chọn người duyệt |
| `modules/RequestRemoteAccess/RRA_SendRequest.php` | `RRA_SendRequest` | Lưu yêu cầu truy cập từ xa |
| `modules/RequestRemoteAccess/RRA_ActionConfirm.php` | `RRA_ActionConfirm` | Quản lý xác nhận / từ chối qua link email |
| `modules/RequestRemoteAccess/RRA_SEND_MAIL.php` | `RRA_SendMAIL` | Gửi mail yêu cầu (⚠️ đang nhận nội dung mail từ client) |
| `modules/AttachDownload/AttachDownload.php` | *(chưa có route)* | Tải file đính kèm (đang làm dở) |

### H. Mẫu email (`src/api/modules/`)

| File | Chức năng |
|---|---|
| `YCMH/YHMH_EmailTemplate.html` | Mail gửi người duyệt phiếu YCMH |
| `YCMH/YHMH_EmailStatus.html` | Mail báo trạng thái phiếu |
| `YCMH/YHMH_EmailStatus_Hoanthanh.html` | Mail báo phiếu đã hoàn thành |
| `YCMH/YHMH_EmailStatus_Huy.html` | Mail báo phiếu bị hủy |
| `RequestRemoteAccess/RRA_EmailTemplate.html` | Mail gửi quản lý xác nhận yêu cầu truy cập từ xa |
