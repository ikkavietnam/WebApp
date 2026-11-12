

//🟡BỘ LỌC TRUNG TÂM 

/*  

FINTER_CENTER
├── 1. Kiểm tra tồn tại coreTable -> Return
├── 2. Xóa sạch bộ lọc cũ
├── 3. Chuẩn hóa dữ liệu đầu vào từ STATES
│   ├── state.search
│   │   state.status
│   │   state.depts
│   │   state.isAscending
│   │
│   └── Kiểm tra bộ lọc, nếu không có bộ lọc thì dừng ở trạng thaais full table 
│       ✅FINISH FINTER
│
├── 4. Filter theo các điều kiện từ state
│   ├── Lọc Status
│   ├── Lọc Bộ phận
│   └── Lọc Ô Search
        ✅FINISH FINTER
        
*/

export function FINTER_CENTER(coreTable, state) {
    //⭕1. Kiểm tra tồn tại coreTable
    if (!coreTable) return;

    //⭕2. Xóa sạch bộ lọc cũ
    coreTable.clearFilter();

    //⭕3. Chuẩn hóa dữ liệu đầu vào từ STATES
    const searchKeyword = (state.search || "").trim().toLowerCase();
    const hasStatus = state.status && state.status !== "all";
    const hasDepts = Array.isArray(state.depts) && state.depts.length > 0;
        // Nếu không chọn bất kỳ bộ lọc nào -> Dừng tại đây (bảng đã ở trạng thái gốc sau khi clearFilter)
        if (!searchKeyword && !hasStatus && !hasDepts) return;

    //⭕4. Filter theo các điều kiện từ state
    coreTable.setFilter(function (data) {
        // Lọc Status
        if (hasStatus) {
            const rowStatus = String(data.YCMH_status || "").toLowerCase();
            if (!rowStatus.includes(state.status.toLowerCase())) return false;
        }

        // Lọc Bộ phận
        if (hasDepts) {
            const rowDept = String(data.YCMH_bophan || "").toUpperCase();
            if (!state.depts.includes(rowDept)) return false;
        }

        // Lọc Ô Search (Đã bổ sung YCMH_ITEM nếu cần)
        if (searchKeyword) {
            const name = String(data.YCMH_name || "").toLowerCase();
            const nhanvien = String(data.YCMH_nhanvien || "").toLowerCase();
            const bophan = String(data.YCMH_bophan || "").toLowerCase();
            /* const item = String(data.YCMH_ITEM || "").toLowerCase(); */

            const matchKeyword = name.includes(searchKeyword) || 
                                 nhanvien.includes(searchKeyword) || 
                                 bophan.includes(searchKeyword);

            if (!matchKeyword) return false;
        }
        
        return true; // Thỏa mãn tất cả điều kiện
    });
    coreTable.redraw(true);
   
}