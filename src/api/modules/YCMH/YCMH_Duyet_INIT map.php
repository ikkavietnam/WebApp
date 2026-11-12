<?php
/* 

    
    │
    ├──🟡1-XỬ lÝ INPUT DATA
    │   └── 🚩InstanceID
    │   
    ├──🟡2-TRUY VẤN LẤY TOÀN BỘ BẢNG] 💭InstanceID
    │   ├──🔶2.1 LOAD TIẾN TRÌNH        ✅$dataINS  🔎Refby: InstanceID
    │   │   ├── Check Exists = false    > 💲KIỂM TRA TỒN TẠI ? 
    │   │   ├── Check Status = done     > 💲KIỂM TRA TRẠNG THÁI
    │   │   └── Check permission        > KIỂM TRA QUYỀN HẠN Người dùng có nằm trong bảng tiến trình không ? CÓ > CHẶN || KHÔNG > bypass
    │   │ 
    │   ├──🔶2.2 LOAD QUY TRÌNH DUYỆT   ✅$dataApp  🔎Refby: InstanceID
    │   ├──🔶2.3 LOAD YCMH HEADER       ✅$dataMHHD 🔎Refby: $dataINS['DocumentID'] = mhhd_name
    │   ├──🔶2.4 LOAD YCMH DETAIL       ✅$dataMHDT 🔎Refby: $dataMHHD['mhhd_id']
    │   ├──🔶2.5 LOAD MHHD_ATTACK       ✅$dataATT  🔎Refby: $dataMHHD['mhhd_id']
    │   └──🔶2.6 CLOSE CONNECTION
    │   
    ├──🔴ROUNTER 🔎Refby: $dataINS['ApprovalStatus']
    │   ├──🔶TH1 Status === approved HOÀN THÀNH 
    │   │    └── ⏩SENDMAIL_HOAN_THANH           
    │   ├──🔶TH1 Status === approved HỦY
    │   │    └── ⏩SENDMAIL_HUY   
    │   └──🔶TH3 Status === approved IN PROCESS 
    │        └── Continue
    │
    ├──🟡3-BUID DATA 
    │   ├── 🔶3.1 INSTANCES
    │   │    ├── INSTANCE_ID        //dataINS-InstanceID
    │   │    ├── DOCUMENT_ID        //dataINS-DocumentID
    │   │    ├── CURRENT_STEP       //dataINS-CurrentStep
    │   │    └── CURRENT_TYPE       //WF_YCMH  ✍🏼
    │   │    
    │   │
    │   ├── 🔶3.2 APP
    │   │    ├── $EmailList      = []; // Email Người duyệt bước hiện tại
    │   │    ├── $NameList       = []; // Tên tương ứng Email
    │   │    ├── $CURRENT_USER   = ''; // Nối tên những người nhận được Email phê duyệt
    │   │    ├── $APPROVERS_LIST = ''; // Render body bảng quy trình
    │   │    ├── $APPROVAL_TYPE  = ''; // Kiểu duyệt AND OR SIGNLE
    │   │    ├── $EmailUpdateStatus  = []; // Danh sách Email những người đã phê duyệt 
    │   │    └── $UPDATE_LIST        = ''; // Render Body bảng quy trình
    │   │
    │   ├── 🔶3.3 MHHD
    │   │    ├── CREATE_BY       CreateBy
    │   │    ├── MHHD_NOTE       mhhd_note
    │   │    ├── CREATE_DEPT     mhhd_department
    │   │    └── CREATE_TIME     CreateTime
    │   │
    │   ├──	🔶3.4 MHDT
    │   │    └── TABLE_DETAIL_ROWS
    │   │    
    ├──🟡4-LOAD FORM EMAIL
    ├──🟡5-SETUP EMAIL & SEND


            "ok"    => true,
            "Us"    => $Us,
            "dataINS" => $dataINS,
            "dataHD"  => $dataHD,
            
            "nguoikhoitao"  => $nguoikhoitao,
            "dataDT"        => $dataDT,
            "Note"          => $Note,
            "dataApp"       => $dataApp,
            "dataATT"       => $dataATT,
            "dataORDER"    => $dataORDER





InstanceID ->   INS-InstanceID
                INS-DocumentID      -> MHHD 
                INS-WfType
                INS-CurrentStep
                INS-ApprovalStatus
        





INSTANCE_ID
DOCUMENT_ID
CURRENT_STEP
DOCUMENT_TYPE
InstanceStatus






{{FONT_PATH}}   #
{{LOGO_SRC}}    #
{{NGAY_TAO}}    #1✔️
{{NGUOI_TAO}}   #2✔️
{{MA_NV}}       #3✔️
{{BO_PHAN}}     #4✔️    
{{DELIVERY}}    #5✔️
{{SO_PHIEU}}            #6✔️   DOCUMENT_ID
{{TABLE_DETAIL_ROWS}}   #8✔️
{{GHI_CHU}}     7✔️     
{{ATT_FILE}}            #9
{{APPROVAL_ROWS}}       #10✔️
