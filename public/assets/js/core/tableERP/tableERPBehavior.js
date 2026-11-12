export function attachERPBehavior(table) {
    const t = table.table;

    // =========================================================================
    // 1. SỰ KIỆN PHÍM TẮT (KEYBOARD NAVIGATION)
    // =========================================================================
    document.addEventListener("keydown", function (e) {
        // Nếu đang gõ trong một input/textarea nào đó thì không xử lý phím tắt bảng
        if (["INPUT", "TEXTAREA"].includes(document.activeElement.tagName)) return;

        // ENTER → Bật chế độ chỉnh sửa cho ô đang được focus
        if (e.key === "Enter") {
            const cell = document.activeElement?.closest(".tabulator-cell")?._cell;
            if (cell) {
                e.preventDefault();
                cell.edit(); // Kích hoạt editor (chính là erpAutoFit  đã đăng ký)
            }
        }
    });

    // =========================================================================
    // 2. CẤU HÌNH HÀNH VI BẢNG (TABLE BEHAVIORS)
    // =========================================================================
    
    // Click vào ô thì focus vào ô đó (để có thể bấm Enter ngay)
    t.on("cellClick", function (e, cell) {
        cell.getElement().focus();
    });

    // Sau khi chỉnh sửa xong, ép hàng tính toán lại độ cao lần cuối cho chuẩn
    t.on("cellEdited", function (cell) {
        cell.getRow().normalizeHeight();
    });

    // Khi di chuyển bằng phím Tab hoặc điều hướng, tự động focus vào cell element
    // Trong file attachERPBehavior.js
t.on("cellNavigation", function (cell) {
    if (cell) {
        const el = cell.getElement();
        el.focus();
        // Ngăn việc scroll gây ra nhảy layout
        el.style.overflow = "hidden"; 
        console.log ("Phím điều hướng")
    }
});
}