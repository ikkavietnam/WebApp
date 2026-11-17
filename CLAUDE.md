# CLAUDE.md

Hướng dẫn cho Claude Code khi làm việc trong repo này.

**Tech stack**: PHP 8.x, SQL Server 2019 (`sqlsrv_*`), Composer, Tabulator.js, Handsontable. Phụ thuộc xem trong `src/composer.json`.

**Nạp theo nhu cầu:**
- Frontend SPA, router, layout ERP → `public/assets/CLAUDE.md`
- API gateway, route, quy ước endpoint → `src/api/CLAUDE.md`
- Thêm endpoint → skill `add-api-endpoint`
- Setup/chạy local, test API → skill `local-setup`
- Triển khai server → skill `deployment-checklist`
- Tổng quan cho người mới → `TONG_QUAN_DU_AN.md`

## Cấu trúc

```
public/                      Web root
├── index.php                SPA: Guest (#GUEST) + ERP (#HEADER/#SIDEBAR/#APP)
├── api_gateway.php          Chỉ require src/core/ApiRouter.php (đường dẫn tuyệt đối /var/www/App)
├── view/ycmh/               Trang duyệt phiếu YCMH độc lập (link email)
├── RequestRemoteAccess/     Form xin remote access (public)
└── assets/js/               app.js (APPROUTER), router.js, core/, modules/<Module>/
src/
├── core/ApiRouter.php       BẢNG ROUTE $routes + check quyền + rate limit ($appRoot hardcode)
├── core/                    Database.php, Authorization.php, Helpers.php, mapping/, log/
├── api/                     auth/, users/, modules/<Module>/
└── config/                  gitignored — credential DB/SMB
```

## Database

- Luôn dùng `sqlsrv_query($conn, $sql, $params)` với tham số bind — không ghép chuỗi SQL.
- Helper trong `src/core/Database.php`: `db_connect()`, `CHKQUERY($stmt, $conn, $msg)`, `RESPONSE_CLIENT($code, $msg, $title)`.
- Lỗi thì gọi `RESPONSE_CLIENT()` (gửi JSON và thoát).

## Xác thực

- User hiện tại: `$_SESSION['user']`; role: `$_SESSION['user']['UsType']` (số).
- Phân quyền dùng `src/core/Authorization.php`. Gateway kiểm tra quyền trước khi gọi handler, handler vẫn nên kiểm tra lại.

## Bảo mật — điều cấm

- ❌ **NEVER** dùng `eval()` hoặc `include $_POST['file']` với dữ liệu người dùng.
- ❌ **NEVER** hardcode credential — dùng `src/config/` (gitignored).
- ❌ **NEVER** đặt file config trong `public/`.
- Credential SMB chỉ đặt trong `src/config/config_smb_server.php`.

## Config & dependency

- File config trong `src/config/` bị gitignore — phải tạo thủ công trên mỗi máy/server.
- Composer chạy trong `src/`, **không** chạy ở root. `vendor/` bị gitignore.
- `composer update` hiếm khi dùng — ưu tiên version đã khóa trong `composer.lock`. Kiểm tra lỗ hổng: `composer audit` (trong `src/`).

## Lỗi hay gặp

- **404 trên API route**: kiểm tra route đã khai báo trong `$routes` của `src/core/ApiRouter.php`.

## Commit

- Format: `<type>: <description>` (ví dụ `feat:`, `fix:`, `refactor:`, `docs:`, `chore:`). Tiếng Việt hoặc tiếng Anh, ưu tiên tiếng Anh.
- Git user: `ikkavietnam`.
