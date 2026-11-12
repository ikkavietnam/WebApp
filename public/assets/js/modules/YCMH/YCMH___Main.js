
//LOAD FUNCTION
import { YCMH_List_LOAD }       from "./YCMH__List_1_Load.js";


/*LOAD UI
🟡 --●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--● */
export async function YCMH_INIT() {
    //LIST ORDER REQUEST
        await YCMH_List_LOAD();
    //CONTENTS
        YCMH_CONTAINER();
    
}//🔐

function YCMH_CONTAINER() {
    const  YCMH_Pages = document.getElementById("CONTAINER_TOTAL");
    if (YCMH_Pages) {
        YCMH_Pages.innerHTML=`
            <div id="TOTAL_FRAME">
                <h5>CLICK CHỌN PHIẾU ĐỂ HIỂN THỊ CHI TIẾT</h5>
                <br>
                <h5 style="font-style:italic;color: #ea726b">(Danh mục này dành để phát triển sau)</h5>
            </div>
        `;
    }
}
