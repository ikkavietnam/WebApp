
//LOAD FUNCTION
import { getCoreTable} from '../../core/Utils/helpers.js';
import { MakeResizable } from "../../core/layout/RESIZABLE.js";


//NEW FETCH API 
import {apiFetch}     from "../../core/request/fetch_Api.js";
import {showApiError,ConverErrToDataSwal} from "../../core/request/fetch_ApiShowError.js";


//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ 




//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
/*YCMH_Container_LOAD
🟡 --●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--●--● */
export async function YCMH_Container_LOAD() {

    const  YCMH_Pages = document.getElementById("CONTAINER_TOTAL");
        if (YCMH_Pages) {
            YCMH_Pages.innerHTML=`
                <div style="margin:auto; width:100%;display: flex;">
                    <h5>CLICK CHỌN PHIẾU ĐỂ HIỂN THỊ CHI TIẾT</h5>
                </div>
            `;
        }
    

}
