---
name: deployment-checklist
description: Checklist triển khai dự án ERP lên server (PHP, SQL Server, composer, config, quyền thư mục, NAS mount). Dùng khi người dùng hỏi về deploy, triển khai hoặc chuẩn bị server.
---

# Checklist triển khai

- [ ] PHP 8.x với extension `sqlsrv`, `gd`, `zip` đã bật
- [ ] SQL Server 2019+ đã cấu hình và truy cập được
- [ ] Đã chạy `composer install` trong `src/` (vendor bị gitignore)
- [ ] Đã tạo file config: `src/config/config.php`, `src/config/config_smb_server.php`
- [ ] Document root của web server trỏ vào `public/`
- [ ] `.htaccess` hoạt động (hoặc tương đương trên IIS)
- [ ] Thư mục có quyền ghi: `public/uploadAttach/`, `public/downloadAttach/`
- [ ] Đường dẫn lưu session nằm ngoài web root (mặc định của PHP hoặc `session.save_path`)
- [ ] Đã bật log lỗi (kiểm tra cấu hình PHP ini)
- [ ] Đường dẫn deploy khớp `$appRoot` trong `src/core/ApiRouter.php` và `require_once` trong `public/api_gateway.php`
- [ ] **NAS (IKKA02)**: mount CIFS `//192.168.110.232/Document` → `/mnt/itv_share` với `uid=33,gid=33` (www-data), chạy lúc boot bằng DSM Task Scheduler; container bind `/mnt/itv_share:/mnt/itv_share`. Mount trên host **trước** khi container chạy (mount sau → phải `docker restart`). Chưa mount → upload file đính kèm YCMH lỗi "Không tìm thấy thư mục lưu file đính kèm".
