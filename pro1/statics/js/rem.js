(function (doc, win) {
    let docEl = doc.documentElement
    resizeEvt = 'orientationchange' in window ? 'orientationchange' : 'resize',
    recal = function () {
        let clientWidth = docEl.clientWidth;
        if (!clientWidth) return;
        if (clientWidth >= 375) {
            docEl.style.fontSize = '100px';
        } else {
            docEl.style.fontSize = 100 * (clientWidth / 375) + 'px';
        }
    };

    if (!doc.addEventListener) return;
    win.addEventListener(resizeEvt, recal, false);
    doc.addEventListener('DOMContentLoaded', recal, false);
})(document, window);