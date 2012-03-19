function jsShowLinks(el, word) {
    var div = $('jsLinks_'+word);
    if (!div) return;
    
    div.style.display = 'block';
    
    var left = jsRealLeft(el);
    
    // Check if the div is not outside the right part of the window
    var cw = document.documentElement.clientWidth;
    if( div.offsetWidth + left > cw ) {
        left = (cw - div.offsetWidth);
    }
    
    div.style.left = left + 'px';
    div.style.top = jsRealTop(el) + 'px';

}
                    
function jsHideDiv(e, el) {
    if (!e) {
        e = window.event;
    }
    
    // Check that mouse really left the div
    var tg = e.srcElement || e.target;
    if (tg != el) {
        return;
    }
    
    var relTg = e.relatedTarget || e.toElement;
    while (relTg != tg && relTg.nodeName != 'BODY') {
        relTg = relTg.parentNode;;
    }
    if (relTg == tg) {
        return;
    }
    
    // Handle mouse out
    el.style.display = 'none';
    el.style.left = '0px';
    el.style.top = '0px;'
}
                    
function jsRealLeft(el) {
    var l = 0;
    while( el ) {
        l += el.offsetLeft;
        el = el.offsetParent;
    }
    return l;
}

function jsRealTop(el) {
    var t = 0;
    while( el ) {
        t += el.offsetTop;
        el = el.offsetParent;
    }
    return t;
}

function jsOnDomReady(fn) {
    if (document.addEventListener) {
        document.addEventListener('DOMContentLoaded', fn, false);
    }
    else if (document.attachEvent) {
        document.attachEvent('onreadystatechange', function() { readyState(fn); });
    }
}
                    
function readyState(fn) {
    if (document.readyState == 'complete' || document.readyState == 'loaded') {
        fn();
    }
}
