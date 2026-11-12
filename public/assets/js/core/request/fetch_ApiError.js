/* 
fetch_Api.js            → chuyên gọi API và chuẩn hóa response.
fetch_ApiError.js       → chuẩn hóa tất cả loại lỗi.
fetch_ApiShowError.js   → chuyên hiển thị lỗi ra PAGE + SweetAlert.
*/

export class ApiError extends Error {

    constructor({
        type = "UNKNOWN_ERROR",
        title = "Đã xảy ra lỗi",
        msg = "Không thể xử lý yêu cầu.",
        ErrDetail = "",
        status = null
    } = {}) {

        super(msg);
        this.name = "ApiError";
        this.type = type;           // Loại lỗi
        this.title = title;         // Tiêu đề hiển thị
        this.msg = msg;             // Nội dung chính
        this.ErrDetail = ErrDetail; // Chi tiết kỹ thuật
        this.status = status;       // HTTP status
    }
}

