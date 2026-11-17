# API (src/api)

Chỉ nạp khi làm việc trong `src/api/`.

## API Gateway & Routing

Mọi API đi qua một cổng duy nhất:

1. **API Gateway**: `public/api_gateway.php` chỉ `require` sang `src/core/ApiRouter.php` — mọi logic nằm ở `ApiRouter.php`.
   - URL: `/api/<route>` (qua `.htaccess`) hoặc `api_gateway.php?route=<route>`
   - Mảng `$routes` (trong `ApiRouter.php`) ánh xạ tên route → handler và quyền
   - Gateway đã mở session khi kiểm tra quyền → handler gọi `session_start()` phải bọc `if (session_status() === PHP_SESSION_NONE)`, nếu không sẽ sinh Notice làm hỏng JSON
   - Mọi response là JSON: `{"ok": true/false, ...}`

2. **Khai báo route**:
   ```php
   $routes = [
       'auth/login'          => ['auth/login.php',          GW_PUBLIC,  [20, 300]],  // rate limit
       'manager/account/list' => ['manager/account/list.php', GW_USER, [100, 60]],
       'YCMH/Export'         => ['modules/YCMH/YCMH_Export_Excel.php', 3],  // require UsType <= 3
   ];
   ```
   - Phần tử 1: đường dẫn handler tương đối trong `src/api/`
   - Phần tử 2: quyền (`GW_PUBLIC`, `GW_USER`, hoặc ngưỡng UsType số)
   - Phần tử 3 (tùy chọn): rate limit `[số request, số giây]`, tính theo IP

3. **Quyền**:
   - `GW_PUBLIC` — không cần đăng nhập
   - `GW_USER` — mọi user đã đăng nhập
   - Số (1, 3, 5...) — theo role: số ≤ ngưỡng thì được phép (1 = Admin, số càng lớn quyền càng thấp)

## Quy ước khi viết endpoint

- **Vị trí**: `src/api/{feature}/{action}.php`
- **Đầu file**: `if (session_status() === PHP_SESSION_NONE) { session_start(); }`
- **DB**: dùng helper từ `src/core/Database.php` — `db_connect()`, `CHKQUERY()`, `RESPONSE_CLIENT()`
- **Trả về**: luôn `echo json_encode([...], JSON_UNESCAPED_UNICODE);`
- **Cấu trúc**: `{"ok": true/false, "data": {...}, "msg": "...", "ErrCode": ...}`
- Ghi chú yêu cầu của endpoint ngay trong comment đầu file.
- Thêm endpoint mới: xem skill `add-api-endpoint`.
