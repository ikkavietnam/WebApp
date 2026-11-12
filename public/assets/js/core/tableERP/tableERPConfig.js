export const TABLE_DEFAULT_CONFIG = {

   
    layout: "fitDataStretch", /* layout: "fitData", fitDataStretch, fitDataFill, fitColumns */
    
    resizableColumns: true,
    /* renderVertical: "basic", *///Tắt Virtual Render chiều dọc để ép tính đúng chiều cao toàn bộ hàng ngay từ đầu
    /* renderHorizontal:	"basic", */

    // 🔥 quan trọng
    editTriggerEvent: false,
    reactiveData: true,
};