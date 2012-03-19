// AJAX
var jsAjax;

function createAjax() {
    if( jsAjax ) {
        return;
    }
    
    try {
        jsAjax = new XMLHttpRequest();
    }
    catch (e){
        try {
            jsAjax = new ActiveXObject("Microsoft.XMLHttp");
        }
        catch (e){
        }
    }
}

function realLeft(el) {
    var l = 0;
    while( el ) {
        l += el.offsetLeft;
        el = el.offsetParent;
    }
    return l;
}

function realTop(el) {
    var t = 0;
    while( el ) {
        t += el.offsetTop;
        el = el.offsetParent;
    }
    return t;
}

function hasClass(el, cl) {
    return (el.className.indexOf(cl) != -1);
}

function addClass(el, cl) {
    if( !hasClass(el, cl) ) {
        el.className = (el.className + ' ' + cl);
    }
}

function removeClass(el, cl) {
    el.className = el.className.replace(new RegExp('(^|\\s)' + cl + '(?:\\s|$)'),'$1');
}

// Autocomplete
var timeOut = 250;
var textElement = null;
var dataElement = null;
var timerID = 0;
var autoShown = false;
var selectedItem = -1;

function hideAutoComplete() {
    if( timerID ) {
        clearTimeout(timerID);
    }
    
    var ul = $('autocomplete');
    
    if( ul ) {
        ul.style.display = 'none';
    }
    
    autoShown = false;
}

function handleKey(e, fn) {
    var code;
    
    code = e.keyCode;
    
    if( autoShown ) {
        var ul = $('autocomplete');
        
        // Enter
        if( code == 13 ) {
            if( selectedItem > -1 ) {
                ul.childNodes[selectedItem].onmousedown();
                hideAutoComplete();
            }
            else {
                fn();
            }
            return false;
        }
        // Tab
        else if( code == 9 ) {
            if( selectedItem > -1 ) {
                ul.childNodes[selectedItem].onmousedown();
                hideAutoComplete();
            }
        }
        // Up or Down arrow
        else if( code == 38 || code == 40 ) {
            var newItem = selectedItem;
            
            if( code == 38 ) {
                newItem--;
            }
            else {
                newItem++;
            }
            if( newItem < 0 ) {
                newItem = ul.childNodes.length - 1;
            }
            else if( newItem >= ul.childNodes.length ) {
                newItem = 0;
            }
            ul.childNodes[newItem].onmouseover();
        }
    }
    else {
        if( code == 13 ) {
            fn();
            return false;
        }
    }
    
    return true;
}

function showAutoComplete(el, e, dataEl, controller, task) {
    if( e.keyCode == 37 ||  // arrows
        e.keyCode == 38 ||
        e.keyCode == 39 ||
        e.keyCode == 40 ||
        e.keyCode == 13 ||  // enter
        e.keyCode == 33 ||  // page up
        e.keyCode == 34 ||  // page down
        e.keyCode == 35 ||  // end
        e.keyCode == 36 ||  // home
        e.keyCode == 45 ||  // insert
        e.keyCode == 16 ||  // shift
        e.keyCode == 17 ||  // shift
        e.keyCode == 20 ||  // caps lock
        e.keyCode == 144 )  // num lock
    {
        return;
    }
    
    textElement = el;
    dataElement = $(dataEl);
    
    if (dataElement) {
        dataElement.value = '';
    }
    
    if( timerID ) {
        clearTimeout(timerID);
    }
    
    if( el.value.length < 1 ) {
        hideAutoComplete();
        return;
    }
    
    timerID = setTimeout('searchWords(\''+controller+'\', \''+task+'\');', timeOut);
}

function searchWords(controller, task) {
    createAjax();
    
    if( !jsAjax ) {
        return;
    }
    
    try {
        var params = 'option=com_sef&controller='+controller+'&task='+task+'&tmpl=component&req=' + textElement.value;
        jsAjax.open('POST', 'index.php', true);
        jsAjax.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded;');
        jsAjax.onreadystatechange = handleSearch;
        jsAjax.send(params);
    }
    catch (e) {
        
    }
}

function handleSearch() {
    if( jsAjax.readyState == 4 ) {
        if( jsAjax.status == 200 ) {
            try {
                // Create the response object from JSON syntax
                var response = eval('(' + jsAjax.responseText + ')');
            }
            catch (e){
                return;
            }
            
            showAuto(response);
        }
    }
}

function showAuto(data) {
    if( data.length == 0 ) {
        return;
    }
    
    var ul = $('autocomplete');
    
    if( !ul ) {
        return;
    }
    
    // Remove all the nodes
    while( ul.childNodes.length > 0 ) {
        ul.removeChild(ul.childNodes[0]);
    }
    selectedItem = -1;
    
    // Build new nodes
    for( var i = 0, n = data.length; i < n; i++ ) {
        var newLi = document.createElement('li');
        newLi.innerHTML = data[i].text;
        newLi.data = data[i].data;
        newLi.index = i;
        
        newLi.onmouseover = function() {
            if( selectedItem > -1 ) {
                removeClass($('autocomplete').childNodes[selectedItem], 'selected');
            }
            addClass(this, 'selected');
            selectedItem = this.index;
        }
        newLi.onmousedown = function() {
            textElement.value = this.innerHTML;
            if( dataElement ) { dataElement.value = this.data; }
        }
        
        ul.appendChild(newLi);
    }
    
    // List position
    ul.style.left = realLeft(textElement) + 'px';
    ul.style.top = (realTop(textElement) + textElement.offsetHeight) + 'px';
    ul.style.width = (textElement.clientWidth) + 'px';
    ul.style.display = 'block';
    
    autoShown = true;
}