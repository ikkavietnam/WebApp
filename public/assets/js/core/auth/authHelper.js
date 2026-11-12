

export async function authSession() {
    
    try {
        let KQ = await fetch("/api/auth/check-session", { 
            method: "GET",
            credentials: "include" 
        });
        //
        const KQJS = await KQ.json(); //✅
        return KQJS;
    } 
    catch (error) {
        console.error("Error API login : ", error);
        return {   
                ok: false, 
                error:  "Err fetch bị lỗi",
                msg:    "Lỗi kết nối API login",
                ErrCode: 9999,
                vitri:  "getway.js Catch fetch"
        };
    }
}

/*  
🟢BEGIN
☘️ ◆▪▪▪ ◆▪▪▪◆▪▪▪◆▪▪▪◆▪▪▪◆▪▪▪◆▪▪▪◆▪▪▪◆▪▪▪◆▪▪▪◆▪▪▪◆▪▪▪◆▪▪▪◆▪▪▪◆▪▪▪◆▪▪▪◆▪▪▪◆▪▪▪◆▪▪▪◆▪▪▪☘️*/
export async function thongbao(MSG) {
    
    try {
        let KQ = await fetch("/api/auth/check-session", { 
            method: "GET",
            credentials: "include" 
        });
        
        const KQJS = await KQ.json(); //✅

        return KQJS;
    } 
    catch (error) {
        console.error("Error API login : ", error);
        return {   
                ok: false, 
                error:  "Err fetch bị lỗi",
                msg:    "Lỗi kết nối API login",
                ErrCode: 9999,
                vitri:  "getway.js Catch fetch"
        };
    }
}



// CHƯA DÙNG TỚI

/*  //**************************************************   
            Swal.fire({
                title: 'Thông báo',
                text: result.msg,
                icon: 'error', // Các icon có sẵn: 'success', 'error', 'warning', 'info', 'question'
                confirmButtonText: 'OK'
            }).then((sweetResult) => {
                if (sweetResult.isConfirmed) { 
                    if (result.ErrCode && result.ErrCode === 401){
                        localStorage.clear();
                        sessionStorage.clear();
                        window.location.href = '/#Home';
                    }
                }
            }); */