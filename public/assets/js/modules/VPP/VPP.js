
import { MakeResizable } from "./../../core/layout/RESIZABLE.js";
/* 
️☘️ PHÂN QUYỀN 
````````````````````````````````````````````````````````````````````````````````` PHÂN LUỒNG */
export async function listVPPRender() {
    const app = document.getElementById("APP");

    app.innerHTML = `
        <div id="LIST_VPP_FRAME" class="resizable-container" style="height: 100%;">
            <div class="resizable-column" style="width: 400px;">

            • Mã khách hàng: C0012547
                • Tên công ty: CÔNG TY TNHH CÔNG NGHỆ IKKA (VIỆT NAM)
                • Địa chỉ giao hàng: KHU CÔNG NGHIỆP TÂN TRƯỜNG,XÃ MAO ĐIỀN,THÀNH PHỐ HẢI PHÒNG,VIỆT NAM.
                • Thời gian giao hàng: 14-05-2026 19:24:00
                • Mã đơn giao: 200698118
                • Đường dẫn tải phiếu giao hàng: MỞ PHIẾU XÁC NHẬN 



            </div>
            <div class="resizable-divider"></div>
            <div class="resizable-column" style="flex: 1;">
            
            Kính gửi Quý khách hàng,
              

            </div>
        </div>
    `;
    // Kích hoạt Resizable
    MakeResizable("LIST_VPP_FRAME","VPP_Storage");
}
