---
name: local-setup
description: Hướng dẫn cài đặt và chạy dự án ERP trên máy local (composer, file config, web root, đăng nhập thử, test API bằng curl/Postman). Dùng khi người dùng hỏi cách setup, chạy local hoặc test API.
---

# Setup & chạy local

## Cài đặt lần đầu
```bash
cd src/
composer install

# Tạo file config (chỉ local, không commit)
cp src/config/config.php.example src/config/config.php
cp src/config/config_smb_server.php.example src/config/config_smb_server.php
```

## Yêu cầu môi trường
- PHP 8.x với extension: `sqlsrv`, `gd`, `zip`, `json`
- SQL Server 2019 trở lên (local hoặc remote)
- Web server: Apache (có `.htaccess`) hoặc IIS
- Composer (vendor nằm trong `src/vendor/`)

## Chạy
1. Trỏ document root của web server vào thư mục `public/`.
2. Điền thông tin SQL Server trong `src/config/config.php`.
3. Sửa đường dẫn `/var/www/App` (đang hardcode cho Docker) ở 2 chỗ: `$appRoot` trong `src/core/ApiRouter.php` và dòng `require_once` trong `public/api_gateway.php`.
4. Truy cập `http://localhost/` → tự chuyển `#Welcome`; đăng nhập tại `#Login`.

## Test API
```bash
curl -X POST http://localhost/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"...", "password":"..."}'
```
- Postman: đặt header Cookie bằng PHP session ID nhận từ response đăng nhập.
- Mọi response đều là JSON.
