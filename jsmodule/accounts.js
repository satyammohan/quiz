$(document).ready(function () {
    callauto("debit", "index.php?module=accounts&func=showparty&ce=0", ["id_party_debit"]);
    callauto("credit", "index.php?module=accounts&func=showparty&ce=0", ["id_party_credit"]);
    $("#amt").priceField();
    $("#debit").focus();
    $("#no").blur(function () {
        myno = $("#no").val();
        id_voucher = $("#id_voucher").val()
        $.post("index.php?module=accounts&func=check", {no: myno, id_voucher: id_voucher}, function (data) {
            if (data >= 1) {
                $("#msgbox").fadeTo(900, 0.1, function () {
                    $(this).html('Voucher no. already in use...').removeClass().addClass('msgerror').fadeTo(900, 1);
                    setTimeout(function () { $("#no").focus(); }, 1);
                })
            } else {
                $("#msgbox").html('').hide();
            }
        })
    })
})