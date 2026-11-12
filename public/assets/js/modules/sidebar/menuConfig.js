/* 
🟢🟢🟢 MENU > MenuPermission > Router.RENDERCONTENT(key)
`````````````````````````````````````````````````````````````````````````````````  */
        export const MenuPermission = {

            Home: 3,
            HomeReport: 3,
            listVPP: 3,
            createVPP: 3,
            managerVPP:1,

            listYCMH: 3,
            danhsachYCMH: 3,
            createYCMH: 3,

            SOmanager: 3,
            SOimport: 3,
            
            Account: 3,
            AccountPassword: 3,

            ToDoList:3,
            ChuKy: 3,

            ListAccount: 1,
            AddNewAccount: 1,
            PermissionAccount: 1,

            DbMNG1: 1,
            DbMNG2: 1,
            DbMNG3: 1,
            
        };


/* 
🟢🟢🟢 MENU > MenuPermission > Router.RENDERCONTENT(key)
`````````````````````````````````````````````````````````````````````````````````  */
export const MENU = [

    {/*🍒🍒🍒*/
        title: "GENERAL",
        roles: [0,1,2,3],
        items: [
            {
                name: "Trang chủ", /* 🍀 */
                icon: "",
                children: [
                    { name: "Dashboard", key: "Home" },
                    { name: "Report", key: "HomeReport" }
                ]
            },
            //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
            {
                name: "Yêu cầu văn phòng phẩm", /* 🍀 */
                icon: "",
                children: [
                    { name: "Danh sách phiếu", key: "listVPP" },
                    { name: "Tạo phiếu VPP", key: "createVPP" },
                    { name: "Quản lý xuất nhập", key: "managerVPP" }
                ]
            },
            //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
             {
                name: "Yêu cầu mua hàng", /* 🍀 */
                icon: "",
                children: [
                    { name: "Danh sách YCMH", key: "listYCMH" },
                    { name: "Tạo phiếu YCMH", key: "createYCMH" }
                    
                ]
            }
        ]
    },

    {/*🍒🍒🍒*/
        title: "APP",
        roles: [0,1,2],
        items: [
            {
                name: "Sales Order", /* 🍀 */
                icon: "",
                children: [
                    { name: "Quản lý SO", key: "SOmanager" },
                    { name: "Import SO", key: "SOimport" }
                ]
            },
            //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        ]
    },


    {/*🍒🍒🍒*/
        title: "USER & FOLLOW WORK",    
        roles: [0,1,2,3],
        items: [
            {
                name: "ITV-Follow Work", /* 🍀 */
                icon: "",
                children: [
                    { name: "To do list", key: "ToDoList" },
                    { name: "Quản lý chữ ký", key: "ChuKy" }
                ]
            },

            //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
            {
                name: "Tài khoản", /* 🍀 */
                icon: "",
                children: [
                    { name: "Thông tin người dùng", key: "Account" },
                    { name: "Thay đổi mật khẩu", key: "AccountPassword" }
                ]
            },
            
        ]   
    },


    {/*🍒🍒🍒*/
        title: "Administrator",
        roles: [0,1],
        items: [
            {
                name: "Manager Account", /* 🍀 */
                icon: "",
                key: "AccountManager",
                children: [
                    { name: "Danh sách tài khoản", key: "ListAccount" },
                    { name: "Thêm người dùng mới", key: "AddNewAccount" },
                    { name: "Phân quyền người dùng", key: "PermissionAccount" },

                ]
            },

            //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
            {
                name: "Manager Database", /* 🍀 */
                icon: "",
                key: "DbManager",
                children: [
                    { name: "IMPORT", key: "DbMNG1" },
                    { name: "MANAGER", key: "DbMNG2" },
                    { name: "DbMNG3", key: "DbMNG3" },

                ]
            }
        ]   
    }



];


