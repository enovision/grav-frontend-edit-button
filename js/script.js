var frontEditButton_refreshMe = false;

function onBlur () {
    frontEditButton_refreshMe = true;
}
function onFocus () {
    if (frontEditButton_refreshMe) {
        window.location.reload(true);
    }
}

if (/*@cc_on!@*/false) { // check for Internet Explorer
    document.onfocusin = onFocus;
    document.onfocusout = onBlur;
} else {
    window.onfocus = onFocus;
    window.onblur = onBlur;
}
