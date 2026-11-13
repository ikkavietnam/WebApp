/* 
fetch_Api.js            → chuyên gọi API và chuẩn hóa response.
fetch_ApiError.js       → chuẩn hóa tất cả loại lỗi.
fetch_ApiShowError.js   → chuyên hiển thị lỗi ra PAGE + SweetAlert.
*/
import { ApiError } from "./fetch_ApiError.js";
let MSG_ApiFetch_Timer = null;

// =========================================================
// ESCAPE HTML
// =========================================================

function escapeHtml(value) {return String(value ?? "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// =========================================================
// CHUẨN HÓA ERROR
// =========================================================

function normalizeApiError(error) {
    //Check TypeError
    if (error instanceof ApiError) {
        return error;
    }
    // Nếu lỗi không xuất phát từ nguồn ApiFetch thì quy về chuẩn lỗi
    return new ApiError({
        type     : "UNKNOWN_ERROR",
        title    : "UNKNOWN_ERROR : normalizeApiError ",
        msg      : error?.message ||"Không thể xử lý",
        ErrDetail: ""
    });
}


// =========================================================
// HIỂN THỊ ERROR LÊN ELEMENT
// =========================================================

export function showApiError(error,elementId = "MSG_ApiFetch",time_DisplayMsg=0) {
    const e         = normalizeApiError(error);
    const title     = e.title || ""
    const msg       = e.msg || "ĐÃ XẢY RA LỖI";
    const errDetail = e.ErrDetail || "";
    const element   = document.getElementById(elementId);

    if (!element) {
        return;
    }

    element.innerHTML = `
        <div style="
            color: #f83f3f;
            font-family:Roboto condensed, sans-serif;
            font-size: 12px;
            line-height: 1.2;
            text-align: left;

        ">  <h4 style="color: #f83f3f;">${escapeHtml(title)}</h4>
            <b></b>
            <b style="font-style: italic; font-weight: 500;">${escapeHtml(msg)}</b>

            ${
                errDetail
                    ? `
                        <br>
                        <small style="color:#777;">
                            Chi tiết: ${escapeHtml(errDetail)}
                        </small>
                    `
                    : ""
            }
        </div>
    `;

    // TH HIỂN THỊ SAU ĐÓ ẨN HIỂN THỊ
    if (time_DisplayMsg!==0) {
        if (MSG_ApiFetch_Timer) {
            clearTimeout(MSG_ApiFetch_Timer);
        }

        MSG_ApiFetch_Timer = setTimeout(() => {
            element.innerHTML = "";
        }, time_DisplayMsg*1000);
    }
        
}

// =========================================================
// CONVERT ERROR → SWAL CONFIG
// =========================================================
export function ConverErrToDataSwal(error) {
    const e = normalizeApiError(error);
    const title =
        e.title ||
        "Đã xảy ra lỗi";

    const msg =
        e.msg ||
        "Không thể xử lý yêu cầu.";

    const errDetail =
        e.ErrDetail ||
        "";

    return {
        icon: "error",
        titleText: title,
        html: `
            <div style="color: #f83f3f;">
                <b>${escapeHtml(msg)}</b>

                ${
                    errDetail
                        ? `
                            <br>
                            <small style="color:#777;">
                                Chi tiết: ${escapeHtml(errDetail)}
                            </small>
                        `
                        : ""
                }
            </div>
        `,

        confirmButtonText: "CLOSE",
    
    };
}







