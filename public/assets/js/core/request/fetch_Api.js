
import { ApiError } from "./fetch_ApiError.js";

/* 
fetch_Api.js            → chuyên gọi API và chuẩn hóa response.
fetch_ApiError.js       → chuẩn hóa tất cả loại lỗi.
fetch_ApiShowError.js   → chuyên hiển thị lỗi ra PAGE + SweetAlert.
*/

export async function apiFetch(url, options = {}) {

    let response;

    // =========================================================
    // 1. CHUẨN BỊ OPTIONS
    const {
        responseType = "json",
        ...fetchOptions
    } = options;

    // =========================================================
    // 2. CHUẨN BỊ HEADERS
    const headers = {
        ...(fetchOptions.headers || {})
    };


    // =========================================================
    // 3. XỬ LÝ BODY
    let body = fetchOptions.body;
    /*
     * Nếu body là object thông thường
     * → tự chuyển thành JSON
     *
     * Không áp dụng cho:
     *  - FormData
     *  - Blob
     *  - URLSearchParams
     *  - ArrayBuffer
     */
    // =========================================================
    // 3. XỬ LÝ HEADER
    if (
        body &&
        typeof body === "object" &&
        !(body instanceof FormData) &&
        !(body instanceof Blob) &&
        !(body instanceof URLSearchParams) &&
        !(body instanceof ArrayBuffer)
    ) {
        body = JSON.stringify(body);

        // Chỉ thêm Content-Type nếu chưa có
        if (!headers["Content-Type"]) {
            headers["Content-Type"] = "application/json";
        }
    }

    /*
     * Nếu body là FormData
     * KHÔNG được tự thêm:
     * Content-Type: multipart/form-data
     * Vì browser phải tự thêm boundary.
     */

    if (body instanceof FormData) {
        delete headers["Content-Type"];
    }
    // =========================================================
    //🟡001. GỬI REQUEST
    try {
        response = await fetch(url, {
            ...fetchOptions,
            body,
            // Luôn gửi cookie/session
            credentials: "include",
            headers
        });

    } catch (e) {
        throw new ApiError({
            type: "NETWORK_ERROR",
            title: "Err-001 LỖI KẾT NỐI",
            msg: "Mất kết nối mạng hoặc máy chủ không phản hồi.",
            ErrDetail: e.message || ""
        });
    }


    // =========================================================
    //🟡002. XỬ LÝ DOWNLOAD / BLOB
    if (responseType === "blob") {
        if (!response.ok) {
            let errorData = null;

            try {
                errorData = await response.json();
            } catch (_) {
                // Response không phải JSON
            }

            throw new ApiError({
                type: "HTTP_ERROR",
                title:
                    errorData?.title ||
                    "Err-002 Lỗi tải dữ liệu",
                msg:
                    errorData?.msg ||
                    `Máy chủ trả về mã lỗi HTTP ${response.status}.`,
                ErrDetail:
                    errorData?.ErrDetail ||
                    response.statusText,
                status: response.status
            });
        }

        return await response.blob();
    }


    // =========================================================
    //🟡003. ĐỌC RESPONSE
    let raw;

    try {
        raw = await response.text();

    } catch (e) {
        throw new ApiError({
            type: "RESPONSE_READ_ERROR",
            title: "Err-003 Lỗi đọc dữ liệu",
            msg: "Không thể đọc phản hồi từ máy chủ.",
            ErrDetail: e.message || "",
            status: response.status
        });
    }


    // =========================================================
    //🟡004. RESPONSE RỖNG
    if (raw.trim() === "") {

        throw new ApiError({
            type: "EMPTY_RESPONSE",
            title: "Err-004 Phản hồi không hợp lệ",
            msg: "Máy chủ không trả về dữ liệu.",
            ErrDetail:
                `HTTP ${response.status} ${response.statusText}`,
            status: response.status
        });
    }


    // =========================================================
    //🟡005. PARSE JSON
    let data;

    try {
        data = JSON.parse(raw);

    } catch (e) {
        throw new ApiError({
            type: "INVALID_JSON",
            title: "Err-005 Phản hồi không hợp lệ",
            msg: "Máy chủ đã phản hồi nhưng dữ liệu không đúng định dạng JSON.",
            ErrDetail:
                `HTTP ${response.status} ${response.statusText}`,
            status: response.status
        });
    }


    // =========================================================
    //🟡006. HTTP ERROR
    if (!response.ok) {
        throw new ApiError({
            type: "HTTP_ERROR",
            title:
                data?.title ||
                "Err-006 Lỗi yêu cầu",
            msg:
                data?.msg ||
                `Máy chủ trả về mã lỗi HTTP ${response.status}.`,
            ErrDetail:
                data?.ErrDetail ||
                response.statusText,
            status: response.status
        });
    }

    // =========================================================
    //🟡007. KIỂM TRA CẤU TRÚC RESPONSE
    if (
        data === null ||
        typeof data !== "object" ||
        Array.isArray(data)
    ) {

        throw new ApiError({
            type: "INVALID_RESPONSE",
            title: "Err-007 Phản hồi không hợp lệ",
            msg: "Dữ liệu trả về không đúng cấu trúc API.",
            ErrDetail:
                `HTTP ${response.status}`,
            status: response.status
        });
    }


    // =========================================================
    //🟡008. KIỂM TRA OK
    if (data.ok !== true) {
        throw new ApiError({
            type: "API_ERROR",
            title: data.title
                ? `Err-008 ${data.title}`
                : "Err-008 Xử lý thất bại",
            msg:
                data.msg ||
                "Máy chủ không xác nhận yêu cầu.",
            ErrDetail:
                data.ErrDetail ||
                "",
            status: response.status
        });
    }

    // =========================================================
    //🟡009. THÀNH CÔNG
    // =========================================================
    return data;
}






export async function apiFetchForm(url, options = {}) {
    let response;

    // =========================================================
    // 1. CHUẨN BỊ OPTIONS
    const {
        responseType = "json",
        isUrlEncoded = false, // true: gửi dạng urlencoded | false: gửi dạng multipart FormData (mặc định)
        ...fetchOptions
    } = options;

    // =========================================================
    // 2. CHUẨN BỊ HEADERS & BODY
    const headers = {
        ...(fetchOptions.headers || {})
    };

    let body = fetchOptions.body;

    // Tự động chuyển Object thành FormData hoặc URLSearchParams nếu cần
    if (body && typeof body === "object" && !(body instanceof FormData) && !(body instanceof URLSearchParams) && !(body instanceof Blob)) {
        if (isUrlEncoded) {
            body = new URLSearchParams(body);
        } else {
            const formData = new FormData();
            Object.entries(body).forEach(([key, value]) => {
                // Nếu giá trị là Array, append từng phần tử (hỗ trợ upload nhiều file)
                if (Array.isArray(value)) {
                    value.forEach((item) => formData.append(key, item));
                } else if (value !== undefined && value !== null) {
                    formData.append(key, value);
                }
            });
            body = formData;
        }
    }

    // Xử lý Header Content-Type đúng chuẩn Form
    if (body instanceof FormData) {
        /*
         * VỚI FORMDATA (multipart/form-data):
         * Tuyệt đối KHÔNG set 'Content-Type'. Trình duyệt sẽ tự động thêm 
         * boundary thích hợp (ví dụ: multipart/form-data; boundary=----WebKitFormBoundary...)
         */
        delete headers["Content-Type"];
        delete headers["content-type"];
    } else if (body instanceof URLSearchParams || isUrlEncoded) {
        /*
         * VỚI URL-ENCODED (application/x-www-form-urlencoded):
         * Set Content-Type nếu chưa có
         */
        if (!headers["Content-Type"] && !headers["content-type"]) {
            headers["Content-Type"] = "application/x-www-form-urlencoded; charset=UTF-8";
        }
    }

    // =========================================================
    // 🟡001. GỬI REQUEST
    try {
        response = await fetch(url, {
            ...fetchOptions,
            method: fetchOptions.method || "POST", // Mặc định Form gửi POST
            body,
            credentials: "include", // Luôn gửi cookie/session
            headers
        });

    } catch (e) {
        throw new ApiError({
            type: "NETWORK_ERROR",
            title: "Err-001 LỖI KẾT NỐI",
            msg: "Mất kết nối mạng hoặc máy chủ không phản hồi.",
            ErrDetail: e.message || ""
        });
    }

    // =========================================================
    // 🟡002. XỬ LÝ DOWNLOAD / BLOB
    if (responseType === "blob") {
        if (!response.ok) {
            let errorData = null;

            try {
                errorData = await response.json();
            } catch (_) {
                // Response không phải JSON
            }

            throw new ApiError({
                type: "HTTP_ERROR",
                title:
                    errorData?.title ||
                    "Err-002 Lỗi tải dữ liệu",
                msg:
                    errorData?.msg ||
                    `Máy chủ trả về mã lỗi HTTP ${response.status}.`,
                ErrDetail:
                    errorData?.ErrDetail ||
                    response.statusText,
                status: response.status
            });
        }

        return await response.blob();
    }

    // =========================================================
    // 🟡003. ĐỌC RESPONSE
    let raw;

    try {
        raw = await response.text();

    } catch (e) {
        throw new ApiError({
            type: "RESPONSE_READ_ERROR",
            title: "Err-003 Lỗi đọc dữ liệu",
            msg: "Không thể đọc phản hồi từ máy chủ.",
            ErrDetail: e.message || "",
            status: response.status
        });
    }

    // =========================================================
    // 🟡004. RESPONSE RỖNG
    if (raw.trim() === "") {
        throw new ApiError({
            type: "EMPTY_RESPONSE",
            title: "Err-004 Phản hồi không hợp lệ",
            msg: "Máy chủ không trả về dữ liệu.",
            ErrDetail: `HTTP ${response.status} ${response.statusText}`,
            status: response.status
        });
    }

    // =========================================================
    // 🟡005. PARSE JSON
    let data;

    try {
        data = JSON.parse(raw);

    } catch (e) {
        throw new ApiError({
            type: "INVALID_JSON",
            title: "Err-005 Phản hồi không hợp lệ",
            msg: "Máy chủ đã phản hồi nhưng dữ liệu không đúng định dạng JSON.",
            ErrDetail: `HTTP ${response.status} ${response.statusText}`,
            status: response.status
        });
    }

    // =========================================================
    // 🟡006. HTTP ERROR
    if (!response.ok) {
        throw new ApiError({
            type: "HTTP_ERROR",
            title:
                data?.title ||
                "Err-006 Lỗi yêu cầu",
            msg:
                data?.msg ||
                `Máy chủ trả về mã lỗi HTTP ${response.status}.`,
            ErrDetail:
                data?.ErrDetail ||
                response.statusText,
            status: response.status
        });
    }

    // =========================================================
    // 🟡007. KIỂM TRA CẤU TRÚC RESPONSE
    if (
        data === null ||
        typeof data !== "object" ||
        Array.isArray(data)
    ) {
        throw new ApiError({
            type: "INVALID_RESPONSE",
            title: "Err-007 Phản hồi không hợp lệ",
            msg: "Dữ liệu trả về không đúng cấu trúc API.",
            ErrDetail: `HTTP ${response.status}`,
            status: response.status
        });
    }

    // =========================================================
    // 🟡008. KIỂM TRA OK
    if (data.ok !== true) {
        throw new ApiError({
            type: "API_ERROR",
            title: data.title
                ? `Err-008 ${data.title}`
                : "Err-008 Xử lý thất bại",
            msg:
                data.msg ||
                "Máy chủ không xác nhận yêu cầu.",
            ErrDetail: data.ErrDetail || "",
            status: response.status
        });
    }

    // =========================================================
    // 🟡009. THÀNH CÔNG
    return data;
}