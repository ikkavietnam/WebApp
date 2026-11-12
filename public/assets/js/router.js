
/*🔴 LOAD STYLE Load module Css cho từng Pages*/
import { loadModuleCSS } from "./core/Utils/styleLoader.js";

/*🔴 LOAD FUNCTION MODULE */
    //✅SIDEBAR
    import { MenuPermission } from "./modules/sidebar/menuConfig.js";

    /*✅ USER & FOLLOW ListFollow */
    import {    accountRender,
                renderChangePassword 
            } from "./modules/account/accountRender.js";
    import {    ListFollowRender 

            } from "./modules/FollowWork/FollowWork.js";
    /*✅ ADMINISTRATOR */
    import {    ListAccountRender,
                PermissionAccountRender,
                AddNewAccountRender 
            } from "./modules/managerAccount/ManagerAccount.js";

    import {    listVPPRender 

            } from "./modules/VPP/VPP.js";


    //✅YCMH old
    import {YCMH_CREATE_RENDER } from "./modules/YCMH/YCMH__Create.js";

    //✅YCMH NEW
    import {YCMH_INIT} from "./modules/YCMH/YCMH___Main.js";

    import {
            DbMNG1,DbMNG2,DbMNG3    
    } from "./modules/managerDatabase/mngDataBase.js"


// 

/*   
🎲🎲🎲✅  KÍCH HOẠT  MỖI KHI LOCATION HASH THAY ĐỔI 
````````````````````````````````````````````````````````````````````````````````` 🎲 HANDLEROUTERR 🎲*/
export function HANDLEROUTER(){
    //🟢 NẾU CHƯA ĐĂNG NHẬP\
        if(!window.currentUser){return;}

    let key = location.hash.replace("#", "");
    const useType = MenuPermission[key];
    //🟢 KHÔNG ĐỦ QUYỀN HẠN
    if(useType && currentUser.UsType > useType){ 
        location.hash = "Home";
        return;
    }
        // Kiểm tra tồn tại (useType): Đảm bảo biến useType không bị null, undefined hoặc bằng 0 trước khi so sánh. Điều này giúp tránh lỗi chương trình (Crash).
        // So sánh giá trị (currentUser.UsType > useType): Kiểm tra xem cấp bậc/loại của người dùng hiện tại có cao hơn mức yêu cầu hay không.

    //🟢 ===== HIGHLIGHT =====
        const menus = document.querySelectorAll("#SIDEBAR .menu");
        menus.forEach(m => m.classList.remove("active"));

        const el = document.querySelector(`[data-menu="${key}"]`);
        if(el) el.classList.add("active");

    // ===== RENDER ===== 
        RENDERCONTENT(key); //🔵✅


}/*❌❌❌❌❌*/

/* MENU > MenuPermission > Router.RENDERCONTENT(key)
🟢🟢🟢  BEGIN FUNCTION
````````````````````````````````````````````````````````````````````````````````` */
function RENDERCONTENT(key){

    const app = document.getElementById("APP");
    switch(key){
    //>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> GENERAL
    //menu11
        case "Home":
            app.innerHTML = "<h2>Home Page</h2>";
            break;

    //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ Tài khoản
        case "ToDoList":
            ListFollowRender()
            break;

        //-------------------------------
        case "ChuKy":
            
             app.innerHTML = "<h2>Err 404 listVPP WEB đang quá trình xây dựng</h2>";
            break;
        //-------------------------------

        case "Account":
            accountRender() //🔴🟢
            break;
        //-------------------------------
        case "AccountPassword":
            renderChangePassword(); //🔴🟢
            break;
    //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ YCVPP
        case "listVPP":
             app.innerHTML = "<h2>Err 404 listVPP WEB đang quá trình xây dựng</h2>";
            listVPPRender();//🔴
            break;
        //-------------------------------
        case "createVPP":
             app.innerHTML = "<h2>Err 404 createVPP WEB đang quá trình xây dựng</h2>";
            //createVPPRender();//🔴
            break;
    //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ YCMH
        case "listYCMH":
            //YCMH_HEADER_RENDER();//🔴
            loadModuleCSS('assets/css/modules/YCMH.css','IkkaTestMode');
            YCMH_INIT();
            break;
        //-------------------------------
        case "createYCMH":
            loadModuleCSS('assets/css/modules/YCMH.css','IkkaTestMode');
            YCMH_CREATE_RENDER();//🔴
            
            //createYCMHRender();//🔴
            break;
    //>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>  APP
    //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ SALE ORDER
        case "SOmanager":
             app.innerHTML = "<h2>Err 404 SOmanager WEB đang quá trình xây dựng</h2>";
           /*  SalesOrderRender();//🔴 */
            break;
        //-------------------------------
        case "SOimport":
             app.innerHTML = "<h2>Err 404 SOimport WEB đang quá trình xây dựng</h2>";
           /*  SOimportRender();//🔴 */
            break;
    //>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> ADMINISTRATOR
        //----------------------------- ManagerAccount \ Danh sách tài khoản
        case "ListAccount":
            ListAccountRender();//🔴🟢
            break; 
        //----------------------------- ManagerAccount \ Phân quyền
        case "PermissionAccount":

            PermissionAccountRender();//🔴
            break;  
         //---------------------------- ManagerAccount \ Thêm người dùng mới
        case "AddNewAccount":
            AddNewAccountRender();//🔴🟢
            break;
        case "DbMNG1":
            DbMNG1();//🔴
            break;
        case "DbMNG2":
            DbMNG2();//🔴
            break;
        case "DbMNG3":
            DbMNG3();//🔴
            break;

   
    /* 
    🔋  DEFAULT */    
        default:
            app.innerHTML = "<h2>DEFAULT Err 404 WEB đang quá trình xây dựng</h2>";
    }
}/*❌❌❌❌❌*/

/*⚪*/  window.addEventListener("hashchange", HANDLEROUTER); 


   