---
name: add-api-endpoint
description: Thêm một API endpoint mới vào dự án (handler trong src/api và khai báo route trong src/core/ApiRouter.php). Dùng khi người dùng muốn tạo endpoint/API mới.
---

# Thêm API endpoint mới

1. Tạo handler `src/api/{feature}/{action}.php`:
   ```php
   <?php
   if (session_status() === PHP_SESSION_NONE) { session_start(); }   // gateway có thể đã mở session
   $conn = db_connect();

   // Kiểm tra đăng nhập nếu cần
   if (!isset($_SESSION['user'])) {
       RESPONSE_CLIENT(401, 'Not authenticated');
   }

   // Logic: luôn dùng prepared statement
   $sql = "SELECT * FROM table WHERE id = ?";
   $stmt = sqlsrv_query($conn, $sql, [$_POST['id']]);
   CHKQUERY($stmt, $conn, "Error fetching data");

   $data = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
   echo json_encode(["ok" => true, "data" => $data], JSON_UNESCAPED_UNICODE);
   sqlsrv_close($conn);
   ```

2. Đăng ký trong `src/core/ApiRouter.php`, mảng `$routes` (bắt buộc chọn quyền, thiếu quyền → 500):
   ```php
   'feature/action' => ['feature/action.php', GW_USER, [100, 60]],  // rate limit tùy chọn
   ```

3. Gọi bằng `POST http://localhost/api/feature/action`.

Quy tắc chi tiết (quyền, rate limit, format trả về): xem `src/api/CLAUDE.md`.
