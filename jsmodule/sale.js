var start = 1;
var rowcontent = "";
function getbatch(id) {
    var parts = id.match(/(\D+)(\d+)$/);
    itemid = $("#id_product__" + parts[2]).val();
    if (itemid != "") {
        if ($("#sale_id").val() == '') {
            callauto("batch_no__" + parts[2], "index.php?module=sales&func=getbatch&showall=0&ce=0&id=" + itemid, ["id_batch__" + parts[2], "exp_date__" + parts[2], "rate__" + parts[2]]);
        } else {
            callauto("batch_no__" + parts[2], "index.php?module=sales&func=getbatch&showall=1&ce=0&id=" + itemid, ["id_batch__" + parts[2], "exp_date__" + parts[2], "rate__" + parts[2]]);
        }
    } else {
        $("#roundof").focus();
    }
}
function addRow(tableID) {
    start = start + 1;
    rowcontent.find("input,select,a").attr("id", function () {
        var parts = this.id.split('__');
        return parts ? parts[0] + "__" + start : null;
    });
    $("#" + tableID + " tr:last").after("<tr>" + rowcontent.html() + "</tr>");
    product_dropdown();
    $("#item__" + start).focus();
}
function removeRow(obj) {
    if ($(obj).parents("tr").siblings("tr.tabRow").length > 0) {
        $(obj).closest("tr").remove();
    } else {
        alert("You can not remove this row.");
    }
    getValues();
}
function rowadd(obj, tid, id) {
    var parts = id.match(/(\D+)(\d+)$/);
    itemid = $("#id_product__" + parts[2]).val();
    if ($(obj).closest('tr').is(':last-child') && itemid!="") {
        addRow(tid);
    }
}
function findvalue(o_name, idx) {
    var obj1 = $("input[name='" + o_name + "[]']");
    var ret = obj1[idx].value
    return ret;
}
function selectboxval(o_name, idx) {
    var obj2 = $("select[name='" + o_name + "[]']");
    var ret2 = obj2[idx].value;
    return ret2;
}
function find_discount(d1, d1_type, amt, qty) {
    if (d1_type == 0) {
        disc_amt = eval((d1 / 100) * amt);
    } else if (d1_type == 1) {
        disc_amt = eval(d1 * qty);
    } else {
        disc_amt = d1;
    }
    return parseFloat(disc_amt).toFixed(2);
}
function getValues() {
    var totaldisc = 0, totalamt = 0, gst = 0, res = 0.00, cessamt = 0, billcase = 0, billqty = 0;
    var obj = $("input[name='amount[]']");
    var caseext = $("input[name='case[]']").length;
    for (var i = 0; i < obj.length; i++) {
        var qty = parseFloat(findvalue("quantity", i) * 1);
        billqty = billqty + qty;
        var mycase = parseFloat(caseext > 0 ? findvalue("case", i) : 0);
        billcase = billcase + mycase;

        var rate = findvalue("rate", i);
        var d3 = findvalue("discount3", i);
        var cess = findvalue("cess", i);
        var tax = findvalue("tax_per", i);
        var d3_type = selectboxval("discount_type3", i);
        var amt = obj[i].value = parseFloat((qty * rate)).toFixed(2);
        totalamt = parseFloat(totalamt) + parseFloat(amt);

        disc3_amt = find_discount(d3, d3_type, amt, qty);
        amt = parseFloat(amt) - parseFloat(disc3_amt);

        var goods_amount = $("input[name='goods_amount[]']");
        goods_amount[i].value = amt.toFixed(2);

        gstamt = parseFloat(amt * tax / 100).toFixed(2);
        itemcess = parseFloat(amt * cess / 100).toFixed(2);

        gst = parseFloat(gst) + parseFloat(gstamt);
        cessamt = parseFloat(cessamt) + parseFloat(itemcess);
        amt = parseFloat(amt) + parseFloat(gstamt) + parseFloat(itemcess);
        totaldisc = parseFloat(totaldisc) + parseFloat(disc3_amt);

        var taxfld = $("input[name='tax_amount[]']");
        taxfld[i].value = gstamt;
        var cessfld = $("input[name='cessamt[]']");
        cessfld[i].value = itemcess;
        var d3_amt = $("input[name='discount_amount3[]']");
        d3_amt[i].value = disc3_amt;
    }
    document.getElementById("totalamt").value = parseFloat(totalamt).toFixed(2);
    document.getElementById("tdiscount").value = parseFloat(totaldisc).toFixed(2);
    document.getElementById("gst").value = gst.toFixed(2);
    document.getElementById("totalcess").value = cessamt.toFixed(2);
    bill_ro = parseFloat(document.getElementById("roundof").value ? document.getElementById("roundof").value : 0.00);
    res = parseFloat((parseFloat(totalamt) + parseFloat(gst) - parseFloat(totaldisc) + parseFloat(cessamt)) + parseFloat(bill_ro));
    document.getElementById("total").value = res.toFixed(2);
    $("#billtotcase").html(billcase);
    $("#billtotqty").html(billqty);
}
function product_dropdown() {
    callauto("item__" + start, "index.php?module=sales&func=showproduct&ce=0", ["id_product__" + start, "rate__" + start, "tax_per__" + start, "id_taxmaster__" + start, "cess__" + start, "itemcase__" + start]);
}
function getQuantity(id) {
    var parts = id.match(/(\D+)(\d+)$/);
    var nocase = $("#" + id).val();
    var itemcase = $("#itemcase__" + parts[2]).val();
    if (nocase != 0 && itemcase != 0)
        $("#qty__" + parts[2]).val(itemcase * nocase);
}
$(document).ready(function () {
    $("#sidebarToggle").click();
    $("#sub").click(function () {
        act = ($("#sale_id").val() == '') ? "insert" : "update";
        if ($("#id_party").val()=="") {
            alert("Select Party.")
            $("#party").focus();
            return;
        }
        document.sales.action = 'index.php?module=sales&func=' + act;
        $("form").submit();
        return;
        $("#sub").unbind('click');
        if (act == 'insert') {
            myseries = $("#series :selected").text();
            $.post("index.php?module=sales&func=checkbillno", { series: myseries, invno: $("#inv").val() }, function (data) {
                if (data) $("#inv").val(data);
                $("form").submit();
            })
        } else {
            $("form").submit();
        }
    });
    $("#print").click(function () {
        $("#print").unbind('click');
        act = ($("#sale_id").val() == '') ? "insert" : "update&id=";
        frmaction = 'index.php?module=sales&ce=0&func=' + act;
        $.ajax({
            url: frmaction,
            type: 'POST',
            data: $('#sales').serialize(),
            success: function (res) {
                window.open("index.php?module=sales&func=prsale&id=" + res + "", "_new", 'scrollbars=yes,resizable=yes,width=600,height=450,top=50,left=250');
                location.reload();
            }
        });
    });
    $(window).keypress(function (event) {
        if (!(event.which == 115 && event.ctrlKey) && !(event.which == 19))
            return true;
        event.preventDefault();
        $("#sub").trigger("click");
        return false;
    });
    rowcontent = $("#mtable tr:first").clone(true);
    product_dropdown();
    callauto("party", "index.php?module=sales&func=showparty&ce=0", ["id_party", "address1", "address2", "gstin"]);
    $('input[type=text]').on("keydown", function (e) {
        var next_idx = $('input[type=text]').index(this) + 1;
        var tot_idx = $('body').find('input[type=text]').length;
        if (e.keyCode == 13) {
            if (tot_idx == next_idx)
                $('input[type=text]:eq(0)').focus();
            else
                $('input[type=text]:eq(' + next_idx + ')').focus();
        }
    });
    $("#series").blur(function () {
        myseries = $("#series :selected").text();
        $.post("index.php?module=sales&func=getsuffix", { series: myseries, taxbill: $("#taxtype").val() }, function (data) {
            $("#inv").val(data);
        })
    })
    $("#inv").blur(function () {
        $.post("index.php?module=sales&func=check", { invno: $("#inv").val() }, function (data) {
            if (data >= 1) {
                $("#msgbox").fadeTo(900, 0.1, function () {
                    $(this).html('This Invoice no. already in use...').removeClass().addClass('msgerror').fadeTo(900, 1);
                    setTimeout(function () { $("#inv").focus(); }, 1);
                })
            } else {
                $("#msgbox").hide();
            }
        })
    })
    $("#series").trigger("blur");
    $("#date").focus();
});
function getTaxRates(id) {
    curid = $("#" + id).val();
    var parts = id.match(/(\D+)(\d+)$/);
    $("#tax_per__" + parts[2]).val(taxrates[curid]);
}
function doInputs(obj, id) {
    if (obj.checked) {
        document.getElementById(id).style.display = 'block'
    } else {
        document.getElementById(id).style.display = 'none'
    }
}