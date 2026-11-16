// =========================================================================
// HELPERS.JS - THƯ VIỆN CÁC HÀM BỔ TRỢ HỆ THỐNG (UTILITIES)
// =========================================================================
import { TableBase } from "../../core/tableERP/tableERPBase.js";
/**
 * 1. Chuẩn hóa để lấy đối tượng bảng lõi (Core Table) từ wrapper object.
 * Ứng dụng: Giúp các hàm xử lý filter, export, refresh... tìm đúng thực thể bảng.
 */
/*📖 Hàm này giúp tìm và trả về đúng đối tượng chứa hàm setFilter 
(hàm dùng để lọc dữ liệu của bảng), bất kể cấu trúc bọc bên ngoài của nó như thế nào. */
export const getCoreTable = (tblObj) => {
    /* if (!tblObj) return null;
    if (typeof tblObj.setFilter === "function") return tblObj;
    if (tblObj.table && typeof tblObj.table.setFilter === "function") return tblObj.table;
    if (tblObj.instance && typeof tblObj.instance.setFilter === "function") return tblObj.instance;
    return null; */
    if (!tblObj) return null;

    // 1. Nếu bản thân nó đã là instance của Tabulator
    if (typeof tblObj.setData === "function" && typeof tblObj.setFilter === "function") {
        return tblObj;
    }

    // 2. Bóc tách từ các thuộc tính bọc phổ biến trong class ERP
    if (tblObj.table && typeof tblObj.table.setData === "function") return tblObj.table;
    if (tblObj.instance && typeof tblObj.instance.setData === "function") return tblObj.instance;
    if (tblObj.tabulator && typeof tblObj.tabulator.setData === "function") return tblObj.tabulator;
    if (tblObj.tbl && typeof tblObj.tbl.setData === "function") return tblObj.tbl;

    return null;


};



/**
 * 2. Trì hoãn thực thi (Debounce)
 * Ứng dụng: Chống spam click, trì hoãn gọi API khi người dùng gõ tìm kiếm nhanh (Search Input).
 */
/*📖 Trì hoãn việc thực thi một hành động cho đến khi người dùng đã dừng thao tác trong một khoảng thời gian nhất định. */
export function debounce(func, timeout = 500) {
    let timer;
    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => {
            func.apply(this, args);
        }, timeout);
    };
}


/**
 * 3. Định dạng số thành tiền tệ (Currency Formatter)
 * Ứng dụng: Hiển thị giá trị hóa đơn, doanh thu, giá vốn chuẩn ERP theo từng quốc gia.
 */
export const formatCurrency = (amount, locale = 'vi-VN', currency = 'VND') => {
    if (amount === undefined || amount === null || isNaN(amount)) return '0';
    return new Intl.NumberFormat(locale, {
        style: 'currency',
        currency: currency
    }).format(amount);
};

/**
 * 4. Định dạng ngày tháng (Date Formatter)
 * Ứng dụng: Chuẩn hóa hiển thị ngày chứng từ, ngày tạo đơn hàng từ dữ liệu thô (ISO String/Timestamp).
 */
export const formatDate = (dateInput, formatType = 'DD/MM/YYYY') => {
    if (!dateInput) return '';
    const date = new Date(dateInput);
    if (isNaN(date.getTime())) return '';

    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');

    if (formatType === 'DD/MM/YYYY') return `${day}/${month}/${year}`;
    if (formatType === 'YYYY-MM-DD') return `${year}-${month}-${day}`; // Thường dùng cho thẻ <input type="date">
    if (formatType === 'DD/MM/YYYY HH:mm') return `${day}/${month}/${year} ${hours}:${minutes}`;
    return date.toLocaleDateString();
};

/**
 * 5. Loại bỏ dấu tiếng Việt (Remove Vietnamese Tones)
 * Ứng dụng: Tạo bộ lọc tìm kiếm không dấu (gõ "ke toán" vẫn tìm được "Kế toán") hoặc tự động tạo mã khách hàng/mã sản phẩm.
 */
export const removeVietnameseTones = (str) => {
    if (!str) return '';
    return str
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/đ/g, 'd')
        .replace(/Đ/g, 'D');
};

/**
 * 6. Kiểm tra đối tượng trống (Check Empty Object/Array/Value)
 * Ứng dụng: Kiểm tra nhanh xem Form dữ liệu có trống không, hoặc payload trước khi gửi lên API có hợp lệ không.
 */
export const isEmpty = (value) => {
    if (value === null || value === undefined) return true;
    if (typeof value === 'string' && value.trim() === '') return true;
    if (Array.isArray(value) && value.length === 0) return true;
    if (typeof value === 'object' && Object.keys(value).length === 0) return true;
    return false;
};

/**
 * 7. Tải file (Download File từ Blob/Stream)
 * Ứng dụng: Xuất file báo cáo Excel, PDF từ API Backend trả về dạng Binary dữ liệu.
 */
export const downloadFileFromBlob = (blobData, filename) => {
    if (!blobData) return;
    const url = window.URL.createObjectURL(new Blob([blobData]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', filename);
    document.body.appendChild(link);
    link.click();
    link.parentNode.removeChild(link);
};


/**
 * 8. MESENGER SWAL
 * Ứng dụng: Tạo thông báo trong quá trình sử dụng
 */
export async function ErpAlert(title, text, type = 'warning', themeColor = null) {
    // Nếu người dùng KHÔNG truyền themeColor, hệ thống tự chọn màu chuẩn theo loại icon (type)
    if (!themeColor) {
        switch (type) {
            case 'error':
                themeColor = '#ff0a0a'; // Đỏ rực cho lỗi nghiêm trọng
                break;
            case 'warning':
                themeColor = '#ff9e03'; // Vàng cam cho cảnh báo/nhắc nhở
                break;
            case 'success':
                themeColor = '#041b0d'; // Xanh lá cho thành công
                break;
            case 'info':
                themeColor = '#0494f3'; // Xanh dương cho thông tin
                break;
            default:
                themeColor = '#3085d6'; // Màu xanh mặc định của Swal
        }
    }

    await Swal.fire({
        title: title,
        text: text,
        icon: type,
        confirmButtonText: 'Đóng lại',
        didOpen: () => {
            Swal.getTitle().style.color = themeColor;
            Swal.getConfirmButton().style.backgroundColor = themeColor;
        }
    });
    Swal.getIcon().style.borderColor = themeColor;
    /* Swal.getHtmlContainer().style.color = themeColor; */
}
// Cách xài cực kỳ ngắn gọn ở các file JS khác:
// Lỗi nghiêm trọng -> Đỏ
//ErpAlert('Cảnh báo lỗi', 'Bạn chưa nhập tên hàng hóa!', 'error', '#d32f2f');
// Nhắc nhở nhẹ nhàng -> Màu cam
//ErpAlert('Lưu ý', 'Số lượng hơi lớn, bạn chắc chứ?', 'warning', '#f39c12');

