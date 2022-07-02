$.fn.getCaret = function() { // adapted from http://blog.vishalon.net/index.php/javascript-getting-and-setting-caret-position-in-textarea
    var ctrl = this[0];
    var CaretPos = 0;	// IE Support
    if (document.selection) {
        ctrl.focus();
        var Sel = document.selection.createRange();
        Sel.moveStart('character', -ctrl.value.length);
        CaretPos = Sel.text.length;
    } else if (ctrl.selectionStart || ctrl.selectionStart == '0') { // Firefox support
        CaretPos = ctrl.selectionStart;
    }
    return (CaretPos);
};

$.fn.priceField = function() {
    $(this).keydown(function(e) {
        var val = $(this).val();
        var code = (e.keyCode ? e.keyCode : e.which);
        var nums = ((code >= 96) && (code <= 105)) || ((code >= 48) && (code <= 57)); //keypad || regular
        var backspace = (code == 8);
        var specialkey = (e.metaKey || e.altKey || e.shiftKey);
        var arrowkey = ((code >= 37) && (code <= 40));
        var Fkey = ((code >= 112) && (code <= 123));
        var decimal = ((code == 110 || code == 190) && val.indexOf('.') == -1);

        // UGLY!!
        var misckey = (code == 9) || (code == 144) || (code == 145) || (code == 45) || (code == 46) || (code == 33) || (code == 34) || (code == 35) || (code == 36) || (code == 19) || (code == 20) || (code == 92) || (code == 93) || (code == 27);

        var properKey = (nums || decimal || backspace || specialkey || arrowkey || Fkey || misckey);
        var properFormatting = backspace || specialkey || arrowkey || Fkey || misckey || ((val.indexOf('.') == -1) || (val.length - val.indexOf('.') < 3) || ($(this).getCaret() < val.length - 2));

        if (!(properKey && properFormatting)) {
            return false;
        }
    });

    $(this).blur(function() {
        var val = $(this).val();
        if (val === '') {
            $(this).val('0.00');
        } else if (val.indexOf('.') == -1) {
            $(this).val(val + '.00');
        } else if (val.length - val.indexOf('.') == 1) {
            $(this).val(val + '00');
        } else if (val.length - val.indexOf('.') == 2) {
            $(this).val(val + '0');
        }
    });

    return $(this);
};