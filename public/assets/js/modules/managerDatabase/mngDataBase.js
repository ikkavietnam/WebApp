



/* 
🟢🟢🟢 DbMNG1
`````````````````````````````````````````````````````````````````````````````````☘️*/
/* 🟢🟢🟢 DbMNG1
`````````````````````````````````````````````````````````````````````````````````☘️*/
export async function DbMNG1(){
    const app = document.getElementById("APP");
    
    // 1. Render Giao diện HTML
    app.innerHTML = `
    <div id="PAGEFRAME" style="padding-left: 15px">
        <div class="import-container">
            <h3>Nhập Dữ Liệu Máy Tính Từ Excel</h3>
            <div id="drop-zone" class="drop-zone">
                <span class="drop-zone__prompt">Kéo thả file Excel vào đây hoặc click để chọn file</span>
                <input type="file" name="excel_file" id="excel_file" class="drop-zone__input" accept=".xlsx, .xls">
            </div>
            <div id="import-status" class="import-status"></div>
        </div>
    </div>

    <style>
    #PAGEFRAME {
        width: 100%;
        height: 100%;
        display: flex;
    }
    .import-container {
        width: 500px;
        height: 500px;
        margin: 20px auto;
        font-family: Arial, sans-serif;
        padding: 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        background: #f9f9f9;
    }
    .drop-zone {
        height: 150px;
        padding: 25px;
        display: flex;
        flex-direction: column; /* Xếp dọc các phần tử bên trong */
        align-items: center;
        justify-content: center;
        text-align: center;
        border: 2px dashed #0075ff;
        border-radius: 5px;
        cursor: pointer;
        background: #fff;
        transition: background 0.3s;
        gap: 10px; /* Tạo khoảng cách giữa chữ và nút input */
    }
    .drop-zone--over {
        background: #e0f0ff;
        border-style: solid;
    }
    .import-status {
        margin-top: 15px;
        padding: 10px;
        border-radius: 4px;
        display: none;
        font-weight: bold;
    }
    .status-success { background: #d4edda; color: #155724; display: block; }
    .status-error { background: #f8d7da; color: #721c24; display: block; }
    </style>
    `;

    // 2. Định nghĩa các DOM Elements ngay sau khi đã Render xong xuôi vào #APP
    const dropZone = document.getElementById("drop-zone");
    const fileInput = document.getElementById("excel_file");
    const statusDiv = document.getElementById("import-status");

    // --- XỬ LÝ SỰ KIỆN CLICK ---
    // Ngăn nổi bọt khi click trúng nút input
    fileInput.addEventListener("click", (e) => {
        e.stopPropagation();
    });

    // Bấm vào vùng trống của dropZone thì tự click giùm input
    dropZone.addEventListener("click", (e) => {
        if (e.target !== fileInput) {
            fileInput.click();
        }
    });

    // --- XỬ LÝ KÉO THẢ (DRAG & DROP) ---
    ["dragenter", "dragover"].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.add("drop-zone--over");
        }, false);
    });

    ["dragleave", "dragend"].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.remove("drop-zone--over");
        }, false);
    });

    // Thả file vào vùng drop
    dropZone.addEventListener("drop", (e) => {
        e.preventDefault();
        e.stopPropagation();
        dropZone.classList.remove("drop-zone--over");

        console.log("Here1 - Đã thả file thành công");
        
        if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
            fileInput.files = e.dataTransfer.files; 
            uploadFile(e.dataTransfer.files[0]);
        }
    });

    // --- XỬ LÝ NÚT INPUT THAY ĐỔI FILE (CHANGE) ---
    fileInput.addEventListener("change", (e) => {
        console.log("Here2 - Đã chọn file từ nút bấm");
        if (fileInput.files && fileInput.files.length > 0) {
            uploadFile(fileInput.files[0]);
        }
    });

    // --- HÀM GỬI FILE (UPLOAD) ---
    function uploadFile(file) {
        console.log("Here3 - Bắt đầu Upload file:", file.name);
        
        statusDiv.className = "import-status";
        statusDiv.innerHTML = "Đang xử lý dữ liệu, vui lòng đợi...";
        statusDiv.style.display = "block";

        const formData = new FormData();
        formData.append("excel_file", file);

        // Lưu ý: Đường dẫn url này bạn đổi lại cho khớp với backend (upload.php hoặc api/db/upload) nhé!
        fetch("api/db/upload", {
            method: "POST",
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error("Lỗi HTTP! Trạng thái: " + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                statusDiv.className = "import-status status-success";
                statusDiv.innerHTML = `Thành công! Đã xử lý ${data.processed} dòng.`;
            } else {
                statusDiv.className = "import-status status-error";
                statusDiv.innerHTML = "Lỗi: " + data.message;
            }
        })
        .catch(error => {
            statusDiv.className = "import-status status-error";
            statusDiv.innerHTML = "Đã xảy ra lỗi hệ thống hoặc kết nối mạng.";
            console.error("Lỗi Fetch:", error);
        });
    }
}

/* 
🟢🟢🟢 DbMNG2
`````````````````````````````````````````````````````````````````````````````````☘️*/
export async function DbMNG2(){

//fetch("api/db/upload",
}
/* 
🟢🟢🟢 DbMNG3
`````````````````````````````````````````````````````````````````````````````````☘️*/
export async function DbMNG3(){

}