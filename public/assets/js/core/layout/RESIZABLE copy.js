export function MakeResizable(containerId, storageKey) {

//📖KHỞI TẠO & LẤY CÁC THÀNH PHẦN UI --> Kiểm tra sự tồn tại của container để tránh lỗi Runtime.
    const container = document.getElementById(containerId); // Find & check Element
    if (!container) return;

    const columns = container.querySelectorAll('.resizable-column');    
    const dividers = container.querySelectorAll('.resizable-divider');
    const containerWidth = container.offsetWidth;
//BƯỚC 1: PHỤC HỒI KÍCH THƯỚC TỪ LOCALSTORAGE -------------------------------------------------------
    if (storageKey) {
        const savedWidths = JSON.parse(localStorage.getItem(`resizer-${storageKey}`));
        if (savedWidths) {
            let totalSavedWidth = 0;
            const widthValues = savedWidths.map(w => parseInt(w));
            totalSavedWidth = widthValues.reduce((a, b) => a + b, 0);
            // PHÒNG VỆ: Nếu tổng cột cũ to hơn màn hình mới, xóa cache để dùng mặc định
            // hoặc tính toán lại tỉ lệ. Ở đây ta chọn cách an toàn là dùng mặc định 
            // nếu màn hình bị thu nhỏ quá mức so với lúc lưu.
            if (totalSavedWidth < containerWidth) {
                columns.forEach((col, index) => {
                   if (index === columns.length - 1) {
                    col.style.flex = "1 1 auto";
                    col.style.width = ""; // Xóa width cố định để không bị giới hạn
                } else {
                    // Các cột phía trước: Cố định độ rộng theo giá trị đã lưu
                    col.style.flex = "0 0 auto"; // Hoặc "none"
                    col.style.width = `${widthValues[index]}px`;
                }
                });
            } else {
                // Nếu màn hình quá nhỏ, để trình duyệt tự chia đều hoặc theo CSS mặc định
                console.warn("Màn hình quá nhỏ, áp dụng layout linh hoạt.");
                columns.forEach(col => col.style.flex = "1"); 
            }
        }
    }

//BƯỚC 2: LOGIC KÉO THẢ -------------------------------------------------------------------------------------

    dividers.forEach((divider, index) => {
        const leftCol = divider.previousElementSibling;
        const rightCol = divider.nextElementSibling;

        // SỰ KIỆN KHI CLICK THANH RESIZABLE (TRƯỚC KHI DI CHUYỂN)
        divider.addEventListener('mousedown', (e) => {

            e.preventDefault();  // ❗❗❗ Ngăn chặn bôi đen văn bản khi đang kéo 
            divider.classList.add('is-resizing');

            // 1. Tính tổng độ rộng container (không bao gồm các divider)
            // Ta tính bằng tổng offsetWidth của tất cả các cột .resizable-column
            const allCols = container.querySelectorAll('.resizable-column');
            let wTotalContainer = 0;
            allCols.forEach(col => wTotalContainer += col.offsetWidth);

            // 2 & 3. Tính độ rộng hiện tại của cột trái và phải
            const wLEFT = leftCol.offsetWidth;
            const wRIGHT = rightCol.offsetWidth;

            // 4. Tính tổng độ rộng của cặp layout đang tác động
            const wTOTAL = wLEFT + wRIGHT;

            // Khai báo các hằng số cấu hình theo yêu cầu của bạn
            const wRateMax = 85; // 75%
            const wMin = 150;    // 150px

            // Tính độ rộng tối đa tính theo pixel (wMax)
            const wMax = (wRateMax * wTotalContainer) / 100;

            const startX = e.clientX;


            /* const startLeftWidth = leftCol.offsetWidth;
            const startRightWidth = rightCol.offsetWidth;
            const currentContainerWidth = container.offsetWidth; */

            /* // Lấy max-width từ CSS (nếu có) 
            const leftMaxWidth = parseInt(window.getComputedStyle(leftCol).maxWidth) || Infinity;
            const rightMaxWidth = parseInt(window.getComputedStyle(rightCol).maxWidth) || Infinity;
            // Độ rộng tối thiểu mặc định
            const minWidth = 200;  */

            // SỰ KIỆN DI CHUYỂN CHUỘT
            const onMouseMove = (e) => {

                const dx = e.clientX - startX;

                // Tính toán độ rộng mới tiềm năng
                let newLeftWidth = wLEFT + dx;
                let newRightWidth = wTOTAL - newLeftWidth; // Đảm bảo tổng không đổi

                // Tính tổng mới để kiểm tra điều kiện (trong logic này wTOTALnew luôn = wTOTAL)
                const wTOTALnew = newLeftWidth + newRightWidth;
                
                // ÁP ĐIỀU KIỆN
                if (
                    newLeftWidth >= wMin && 
                    newRightWidth >= wMin && 
                    newLeftWidth <= wMax && 
                    newRightWidth <= wMax &&
                    wTOTALnew <= wTOTAL // Đảm bảo không vượt quá phạm vi tổng ban đầu
                ) {
                    // Nếu thỏa mãn thì cập nhật DOM
                    leftCol.style.width = `${newLeftWidth}px`;
                    rightCol.style.width = `${newRightWidth}px`;
                    leftCol.style.flex = "none";
                    rightCol.style.flex = "none";
                }
                // Nếu không thỏa mãn, trình duyệt sẽ giữ nguyên vị trí cuối cùng hợp lệ
            };

            const onMouseUp = () => {
                divider.classList.remove('is-resizing');
                document.removeEventListener('mousemove', onMouseMove);
                document.removeEventListener('mouseup', onMouseUp);

//BƯỚC 3: LƯU VÀO LOCALSTORAGE KHI KẾT THÚC KÉO -------------------------------------------------------------------------------------

                if (storageKey) {
                    const widths = Array.from(columns).map(col => col.style.width || `${col.offsetWidth}px`);
                    localStorage.setItem(`resizer-${storageKey}`, JSON.stringify(widths));
                }
            };

            document.addEventListener('mousemove', onMouseMove);
            document.addEventListener('mouseup', onMouseUp);
        });
    });

}

/* 
CẤU TRÚC HTML ÁP DỤNG



// Kích hoạt Resizable
    MakeResizable("LIST_FOLLOW_FRAME");
*/


/* 

đâu là sự kiện khi click thanh resizable ( trước khi di chuyển) ?

    1 tính tổng độ rộng containerid ( tổng độ rộng các cột có trong layout tổng không bao gồm resizable-divider )    wTotalContainer
    2 tính độ rộng bên trái resizable-divider   wLEFT
    3 tính độ rộng bên phải resizable-divider   wRIGHT
    4 tính độ rộng tổng 2 layout trên          wTOTAL = wLEFT + wRIGHT 

    khai báo biến wRateMax = 75% 
    khai máo     wMin   = 150
    > độ rộng px max wMax = wRateMax*75*wTotalContainer/100 

    đén đây có 3 thông số  wTOTAL & wMax & wMin

Sự kiện di chuyển chuột

    tính newLeftWidth
    tính newRightWidth
     wTOTALnew = newRightWidth + newLeftWidth

    áp điều kiện
            newLeftWidth    >= wMin && 
            newRightWidth   >= wMin && 
            newLeftWidth    <= wMax && 
            newRightWidth   <= wMax &&
            wTOTALnew <= wTOTAL
        còn thỏa mãn thì còn kéo chuột được. nếu không thỏa mãn được thì dừng ở vị trí cuối cùng thỏa mãn


*/