
import { MakeResizable } from "./../../core/layout/RESIZABLE.js";
/* 
️☘️ PHÂN QUYỀN 
````````````````````````````````````````````````````````````````````````````````` PHÂN LUỒNG */
export async function listVPPRender() {
    const app = document.getElementById("APP");

    app.innerHTML = `
        <div id="LIST_VPP_FRAME" class="resizable-container" style="height: 100%;">
            <div class="resizable-column" style="width: 400px;">

            Kính gửi Quý khách hàng,
                Công ty TNHH La Vie xin gửi đến Quý khách lời chào trân trọng và xin chân thành cảm ơn sự tin dùng của Quý khách dành cho các sản phẩm nước khoáng thiên nhiên La Vie. 
                Chúng tôi trân trọng thông báo rằng đơn hàng của Quý khách đã được giao thành công. Phiếu xác nhận giao hàng được đính kèm trong email này dưới dạng tệp PDF. 
                • Mã khách hàng: C0012547
                • Tên công ty: CÔNG TY TNHH CÔNG NGHỆ IKKA (VIỆT NAM)
                • Địa chỉ giao hàng: KHU CÔNG NGHIỆP TÂN TRƯỜNG,XÃ MAO ĐIỀN,THÀNH PHỐ HẢI PHÒNG,VIỆT NAM.
                • Thời gian giao hàng: 14-05-2026 19:24:00
                • Mã đơn giao: 200698118
                • Đường dẫn tải phiếu giao hàng: MỞ PHIẾU XÁC NHẬN 

                Nếu Quý khách cần hỗ trợ thêm, vui lòng liên hệ Hotline: 1900 1906. 
                Xin chân thành cảm ơn Quý khách đã tin tưởng và đồng hành cùng La Vie. 
                Trân trọng,
                CÔNG TY TNHH LA VIE 


            </div>
            <div class="resizable-divider"></div>
            <div class="resizable-column" style="flex: 1;">
            
            Kính gửi Quý khách hàng,
                Công ty TNHH La Vie xin gửi đến Quý khách lời chào trân trọng và xin chân thành cảm ơn sự tin dùng của Quý khách dành cho các sản phẩm nước khoáng thiên nhiên La Vie. 
                Chúng tôi trân trọng thông báo rằng đơn hàng của Quý khách đã được giao thành công. Phiếu xác nhận giao hàng được đính kèm trong email này dưới dạng tệp PDF. 
                • Mã khách hàng: C0012547
                • Tên công ty: CÔNG TY TNHH CÔNG NGHỆ IKKA (VIỆT NAM)
                • Địa chỉ giao hàng: KHU CÔNG NGHIỆP TÂN TRƯỜNG,XÃ MAO ĐIỀN,THÀNH PHỐ HẢI PHÒNG,VIỆT NAM.
                • Thời gian giao hàng: 14-05-2026 19:24:00
                • Mã đơn giao: 200698118
                • Đường dẫn tải phiếu giao hàng: MỞ PHIẾU XÁC NHẬN 

                Nếu Quý khách cần hỗ trợ thêm, vui lòng liên hệ Hotline: 1900 1906. 
                Xin chân thành cảm ơn Quý khách đã tin tưởng và đồng hành cùng La Vie. 
                Trân trọng,
                CÔNG TY TNHH LA VIE 



            </div>
        </div>
    `;
    // Kích hoạt Resizable
    MakeResizable("LIST_VPP_FRAME","VPP_Storage");
}
