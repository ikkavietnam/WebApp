
import { APPCONFIG }    from '../../app.config.js';


/* loadedStyles
🟡============================================================================== */
const MODULE_STYLE_ID = 'ikka-module-css';

export function loadModuleCSS(cssPath,CssMode) {
    let link = document.getElementById(MODULE_STYLE_ID);

    if (!link) {
        // Nếu chưa có thẻ link đại diện cho module, tạo mới
        link = document.createElement('link');
        link.id = MODULE_STYLE_ID;
        link.rel = 'stylesheet';
        document.head.appendChild(link);
    }

    if (CssMode && CssMode === "IkkaTestMode") {
        //MÔI TRƯỜNG TESTTING
        const version = Date.now();
        link.href = `${cssPath}?v=${version}`;
        
    } else {
        //ĐƯA VÀO MÔI TRƯỜNG PRODUCTION
        link.href = `${cssPath}?v=${APPCONFIG.VERSION}`;

    }

}

/*
 * Gỡ bỏ hoàn toàn CSS module nếu cần về trang trắng/dashboard mặc định
 */
export function unloadModuleCSS() {
  const link = document.getElementById(MODULE_STYLE_ID);
  if (link) {
    link.remove();
  }
}