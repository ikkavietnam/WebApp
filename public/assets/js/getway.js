
/* 
🟢🟢🟢✅  GW_CheckSession
`````````````````````````````````````````````````````````````````````````````````☘️*/
export async function GW_CheckSession() {

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
🟢🟢🟢✅ GW_LoginClick
`````````````````````````````````````````````````````````````````````````````````☘️*/
export async function GW_LoginClick(username, password) {
    try {
        let KQ = await fetch("/api/auth/login", { 
            method: "POST",
            headers: { "Content-Type": "application/json" },
            credentials: "include",
            body: JSON.stringify({ 
                username: username, 
                password: password 
            })
        });
        

        const KQJS = await KQ.json();

        
        if (!KQJS.ok) {
      
            return KQJS;
        }else{
            return KQJS;
        }
  
    } 
    catch (error) {
        console.error("Lỗi kết nối API login : ", error);
        return {   
                ok: false, 
                error: "Err fetch bị lỗi",
                msg: "Lỗi kết nối API login",
                vitri: "getway.js Catch fetch"
        };
    }
}

/* account
🟢🟢🟢🟢 GW_getUsersDetail
`````````````````````````````````````````````````````````````````````````````````☘️*/
export async function GW_getUsersDetail() {

    try {
        let KQ = await fetch("/api/users/getUsersDetail", { 
            method: "GET",
            credentials: "include" 
        });

        const KQJS = await KQ.json(); //✅
        return KQJS;
    } 
    catch (error) {
        console.error("Error API GW_getUsersDetail : ", error);
        return {   
                ok: false, 
                error:  "Err fetch bị lỗi",
                msg:    "Lỗi kết nối API login",
                ErrCode: 9999,
                vitri:  "getway.js Catch fetch"
        };
    }
}//EndFund

/* account
🟢🟢🟢🟢 GW_SaveUser
`````````````````````````````````````````````````````````````````````````````````☘️*/
export async function GW_SaveUser(payload) {

    try {
        let res = await fetch("/api/users/SaveUser", { 
            method: "POST",
            headers: { "Content-Type": "application/json" },
            credentials: "include",
            body: JSON.stringify(payload)
        });
        if (!res.ok) return null;
        return await res.json();
    } 
    catch (error) {
        console.error("Lỗi kết nối API:", error);
        return null;
    }
}

/* account
🟢🟢🟢🟢 GW_ChangePass
`````````````````````````````````````````````````````````````````````````````````☘️*/
export async function GW_ChangePass (oldPwd,newPwd) {

    try {/////////////////////////////////////////////////////////////
        let KQ = await fetch("/api/users/ChangePassword", { // Gọi qua Router mới
            method: "POST",
            headers: { "Content-Type": "application/json" },
            credentials: "include",
            body: JSON.stringify({ 
                oldPwd: oldPwd, 
                newPwd: newPwd 
            })
        });///////////////////////////////////////////////////////////
        const KQJS = await KQ.json();

        if (!KQJS.ok) {
         
            return KQJS;
        }else{
            return KQJS;
        }
    } 
    catch (error) {
        console.error("Lỗi kết nối API ChangePassword : ", error);
        return {   
                ok: false, 
                error: "Err fetch bị lỗi",
                msg: "",
                vitri: "getway.js Catch fetch"
            };
    }

}



/* manager
☘️☘️☘️☘️ GW_getListUsers
`````````````````````````````````````````````````````````````````````````````````☘️*/
export async function GW_getListUsers() {

    try {
        let KQ = await fetch("/api/modules/getListUsers", { 
            method: "GET",
            credentials: "include" 
        });

        const KQJS = await KQ.json();

        if (!KQJS.ok) {
    
            return KQJS;
        }else{
            return KQJS;
        }
  
    } 
    catch (error) {
        console.error("Lỗi kết nối API GetListUser : ", error);
        return {   
                ok: false, 
                error: "Err fetch bị lỗi",
                msg: "Lỗi kết nối API GetListUser",
                vitri: "getway.js Catch fetch"
            };
    }
}



/* manager
☘️☘️☘️☘️ GW_usersProfile
`````````````````````````````````````````````````````````````````````````````````☘️*/
export async function GW_usersProfile() {

    try {
        let res = await fetch("/api/users/profile", { 
            method: "GET",
            credentials: "include" 
        });
        if (!res.ok) {
            console.error(`API bị lỗi mã: ${res.status} (${res.statusText})`);
            return null;
        }    
        return await res.json();
    } 
    catch (error) {
        console.error("Lỗi kết nối API GetListUser :", error);
        return null;
    }
}



/* manager
☘️☘️☘️☘️ GW_userNewProfile
`````````````````````````````````````````````````````````````````````````````````☘️*/
export async function GW_userNewProfile (newProfile) {

    try {
        let res = await fetch("/api/users/NewProfile", { // Gọi qua Router mới
            method: "POST",
            headers: { "Content-Type": "application/json" },
            credentials: "include",
            body: JSON.stringify({ 
                newProfile: newProfile, 
            })
        });

        if (!res.ok) {
            const errorData = await res.json();
            throw new Error(errorData.error || "Lỗi trong quá trình ghi dữ liệu");
        }
        return await res.json(); // Trả về { ok: true, user: {...} }
    } 
    catch (error) {
        console.error("Login Error:", error.message);
        return { ok: false, error: error.message };
    }

}

/* manager
☘️☘️☘️☘️ GW_SaveProfile
`````````````````````````````````````````````````````````````````````````````````☘️*/
export async function GW_SaveProfile (SaveProfile) {


    try {
        let res = await fetch("/api/users/SaveProfile", { // Gọi qua Router mới
            method: "POST",
            headers: { "Content-Type": "application/json" },
            credentials: "include",
            body: JSON.stringify(SaveProfile)
        });

        if (!res.ok) {
            const errorData = await res.json();
            throw new Error(errorData.error || "Lỗi trong quá trình ghi dữ liệu");
        }
        return await res.json(); // Trả về { ok: true, user: {...} }
    } 
    catch (error) {
        console.error("Login Error:", error.message);
        return { ok: false, error: error.message };
    }

}



/* manager
☘️☘️☘️☘️ GW_DeleteProfile
`````````````````````````````````````````````````````````````````````````````````☘️*/
export async function GW_DeleteProfile (Profile) {

    try {
        let res = await fetch("/api/users/DeleteProfile", { // Gọi qua Router mới
            method: "POST",
            headers: { "Content-Type": "application/json" },
            credentials: "include",
            body: JSON.stringify(Profile)
        });

        if (!res.ok) {
            const errorData = await res.json();
            throw new Error(errorData.error || "Lỗi trong quá trình ghi dữ liệu");
        }
        return await res.json(); // Trả về { ok: true, user: {...} }
    } 
    catch (error) {
        console.error("Login Error:", error.message);
        return { ok: false, error: error.message };
    }

}



/* YCMH
☘️☘️☘️☘️ GW_YCMH_GetListHeader
`````````````````````````````````````````````````````````````````````````````````☘️*/
export async function GW_YCMH_GetListHeader (Profile) {

    try {
        let res = await fetch("/api/modules/YCMH_GetListHeader", { // Gọi qua Router mới
            method: "GET",
            credentials: "include",
        });

        if (!res.ok) {
            const errorData = await res.json();
            throw new Error(errorData.error || "Lỗi trong quá trình lấy dữ liệu");
        }
        return await res.json(); // Trả về { ok: true, user: {...} }
    } 
    catch (error) {
        console.error( error.message);
        return { ok: false, error: error.message };
    }

}

/* YCMH
☘️☘️☘️☘️ GW_getYCMHdetail
`````````````````````````````````````````````````````````````````````````````````☘️*/
export async function GW_getYCMHdetail (Profile) {
    try {
        let res = await fetch("/api/modules/getYCMHdetail", { // Gọi qua Router mới
            method: "POST",
            credentials: "include",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(Profile)
        });

        if (!res.ok) {
            const errorData = await res.json();
            throw new Error(errorData.error || "Lỗi trong quá trình lấy dữ liệu");
        }
        return await res.json(); // Trả về { ok: true, user: {...} }
    } 
    catch (error) {
        console.error( error.message);
        return { ok: false, error: error.message };
    }

}

/* YCMH
☘️☘️☘️☘️ GW_PhieuGetInfo
`````````````````````````````````````````````````````````````````````````````````☘️*/
export async function GW_PhieuGetInfo (Profile) {
    try {
        let res = await fetch("/api/modules/PhieuGetInfo", { 
            method: "GET",
            credentials: "include",
        });      
        if (!res.ok) {
            const errorData = await res.json();
            throw new Error(errorData.error || "Lỗi trong quá trình lấy dữ liệu");
        }
        return await res.json(); // Trả về { ok: true, user: {...} }
    } 
    catch (error) {
        console.error( error.message);
        return { ok: false, error: error.message };
    }

}




/* YCMH
☘️☘️☘️☘️ 
`````````````````````````````````````````````````````````````````````````````````☘️*/
export async function GW_VIEW_YCMH_DUYET_checking (ClienData) {
    try {
        let res = await fetch("/api/modules/YCMH_DuyetPhieu", {
            method: "POST",
            credentials: "include",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(ClienData)
        });

        const GWdata = await res.json().catch(() => null);

        console.log(`${res.status} (${res.statusText})`);
        console.log (GWdata);
        if (!res.ok || GWdata === null) {
            return {
                ok: false,
                msg: "GW Lỗi kết nối hệ thống hoặc Server gặp sự cố.",
                ErrDetail: `HTTP Status: ${res.status}.(${res.statusText}) Không thể đọc định dạng JSON từ Server.`,
            };
        }
        
        return GWdata;
    } 
    catch (error) {
        return {ok: false, msg: "Error try GW", ErrDetail: " Không biết lỗi gì"};
    }

}


//GW_YCMH_GetListHeader GW_PhieuGetInfo





/*
✅ TRUYỀN THAM SỐ GW_DeleteProfile
try {
        let res = await fetch("/api/auth/login", { // Gọi qua Router mới
            method: "POST",
            headers: { "Content-Type": "application/json" },
            credentials: "include",
            body: JSON.stringify({ 
                username: username, 
                password: password 
            })

        });

        if (!res.ok) {
            const errorData = await res.json();
            throw new Error(errorData.error || "Lỗi đăng nhập");
        }

        return await res.json(); // Trả về { ok: true, user: {...} }
    } catch (error) {
        console.error("Login Error:", error.message);
        return { ok: false, error: error.message };
    } */

/* 
✅ KHÔNG THAM SỐ
async function checkUserSession() {console.log("⏳⏳⏳ APP 🔈 checkUserSession")
    try {
        let res = await fetch("/api/auth/check-session", { 
            method: "GET",
            credentials: "include" 
        });
        if (!res.ok) return null;
        return await res.json();
    } 
    catch (error) {
        console.error("Lỗi kết nối API:", error);
        return null;
    }
} */