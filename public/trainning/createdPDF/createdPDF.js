


import { apiFetch } from "../../assets/js/core/request/fetch_Api.js";




const InsFinalStatus = 'Approved';
const InstanceID    =165;

const pdfPayload = {
    InsFinalStatus,
    InstanceID,
};

//const KQ = await apiFetch("/api/view/ycmh/createdPDF", {
const KQ = await apiFetch("/api/modules/YCMH__WF_SENDMAIL", {
    method: "POST",
    body: InstanceID, //KHÔNG CẦN Stringify 
});   
console.log(KQ)
const ElMsg = document.getElementById('Msg');
const ElID1 = document.getElementById('ID1');
const ElID2 = document.getElementById('ID2');

ElID1.innerHTML =`
    ${KQ.msg}
`;

ElID2.innerHTML =`
    ${KQ.MsgPhp}
`;
