---
paths:
  - "**/*.js"
  - "**/*.php"
---

# QUY ƯỚC ĐẶT TÊN & COMMENT

Phạm vi: chỉ áp dụng cho **code mới**. Không đổi tên code cũ (vd `APPROUTER`, `RESPONSE_CLIENT`, `CHKQUERY`, `db_connect`) — đổi tên sẽ làm hỏng chỗ gọi.

## 1. Quy ước chung

| Thành phần | Quy ước | Ví dụ |
|---|---|---|
| Biến | camelCase | `userName` |
| Hàm | Tiền tố_ + PascalCase (xem mục 3) | `Fget_UserInfo()` |
| Class | PascalCase | `UserController` |
| Interface (PHP) | PascalCase | `UserRepository` |
| Hằng số | UPPER_SNAKE_CASE | `MAX_LOGIN_ATTEMPTS` |
| Boolean | tiền tố is / has / can | `isActive`, `hasPermission` |
| ID HTML | MODULE_TenThanhPhan | `YCMH_Search` |
| File chính module | MODULE___Main (3 dấu `_`, luôn xếp đầu) | `YCMH___Main.js` |
| File con module | MODULE__Nhom_SoThuTu_ChucNang | `YCMH__List_1_Load.js` |
| Folder module | Viết tắt nghiệp vụ: CHỮ HOA; còn lại: PascalCase | `YCMH`, `VPP`, `FollowWork` |
| Folder chuẩn chung | chữ thường | `assets`, `css`, `middleware` |

## 2. Tên biến

- **Biến mặc định:**
  - `_username`, `_password` — dùng cho các form login.
  - `RES` — response thô trả về từ `apiFetch`.
  - `KQ` — kết quả/dữ liệu đã xử lý từ `RES`.
- **Biến tự đặt:** camelCase, dễ nhớ, trực quan, không quá dài, ưu tiên tiếng Việt không dấu. Vd: `tenPhongBan`, `soLuong`.
- **Biến element (JS):** tiền tố `el_` + nguyên id của element.
  ```js
  const el_YCMH_Search = document.getElementById("YCMH_Search");
  ```

## 3. Tên hàm: Tiền tố_ + TenHam (PascalCase)

| Tiền tố | Ý nghĩa | Vai trò |
|---|---|---|
| `F_` | Function | Hàm thông thường |
| `Fm_` | Main | Hàm chính, điều phối xử lý |
| `Fs_` | Sub | Hàm bổ trợ, phục vụ hàm khác |
| `Fu_` | Utility | Hàm tiện ích dùng chung |
| `Fc_` | Core | Xử lý nghiệp vụ cốt lõi |
| `Fv_` | Validate | Kiểm tra, xác thực dữ liệu |
| `Fcvt_` | Convert | Chuyển đổi dữ liệu |
| `Ffmt_` | Format | Định dạng dữ liệu |
| `Fget_` | Get | Lấy dữ liệu |
| `Fset_` | Set | Thiết lập dữ liệu |
| `Fchk_` | Check | Kiểm tra điều kiện |
| `Fhdl_` | Handle | Xử lý sự kiện |
| `Fcb_` | Callback | Hàm callback |
| `Fapi_` | API | Gọi hoặc xử lý API |
| `Fdb_` | Database | Thao tác cơ sở dữ liệu |
| `Flog_` | Log | Ghi nhật ký |
| `Fsec_` | Security | Bảo mật |

## 4. Quy ước khi thêm hàm (JS & PHP)

- Giữa 2 hàm cách nhau **5 dòng trống**.
- Mỗi hàm có comment đầu theo mẫu:
  ```
  /*
      ⎝ 𓆩༺✧🔴 INIT_FORM_LIST_HTML 🔴✧༻𓆪 ⎠
  Mô tả chức năng :
  Các hàm liên quan :
      -
      -
  */
  ```
- Màu trong tiêu đề:
  - 🔴 hàm xử lý chính
  - 🟢 hàm bổ trợ
  - 🟡 hàm có kết nối database SQL
  - 🟣 hàm tái sử dụng nhiều lần
  - Hàm thuộc nhiều loại thì chọn theo ưu tiên: 🟡 > 🔴 > 🟣 > 🟢

## 5. Comment trong thân hàm

- Mỗi đoạn code có comment ngắn mô tả nhiệm vụ.
- Cấp comment:
  - `//🔸` comment lớn (cấp 1)
  - `//🔹` comment con của 🔸 (cấp 2)
  - Các cấp sau chỉ dùng `//`
- Code quan trọng: comment bên phải, kèm ký tự ❗. Vd: `$id = (int)$_POST['id']; // ❗ ép kiểu chống injection`
