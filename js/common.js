$(function() {
    jQuery.browser={};
    jQuery.browser.msie=false; 
    jQuery.browser.version=0;
    if(navigator.userAgent.match(/MSIE ([0-9]+)./)){ 
        jQuery.browser.msie=true;
        jQuery.browser.version=RegExp.$1;
    }
    $(".print").click( function() {
        $('.print_content').jqprint();
        return false;
    });
    $(".excel").click( function () {
        flname = $("#excelfile").val() ? $("#excelfile").val() : "accounts";
        $("#report").table2excel({
            exclude: ".noExl",
            name: "Worksheet",
            filename: flname
        });
    });
});
function tbl_handler() {
    $('#dataTable').DataTable({
        "dom": '<"top"fl>rt<"bottom"ip><"clear">'
    } );
    /*l - Length changing, f - Filtering input, t - The Table!, i - Information, p - Pagination, r - pRocessing*/
}
function jumpnext(id, nextid) {
    var parts = id.match(/(\D+)(\d+)$/);
    setTimeout(function() {
        $("#"+nextid + parts[2]).focus();
    }, 1);
}
$(document).ready(function() {
    $('.openPopup').on('click',function(){
        var dataURL = $(this).attr('data-href');
        $('.modal-body').load(dataURL,function(){
            $('#myModal').modal( { show:true } );
        });
    });
    tbl_handler();
    if (document.getElementById("start_date")  != null ) {
        document.getElementById("start_date").min = sdate;
        document.getElementById("start_date").max = edate;
        document.getElementById("end_date").min = sdate;
        document.getElementById("end_date").max = edate;
    }
});
function update_status(tbl, id, row_status, list_status) {
    let url = "index.php?module=" + tbl + "&func=update_flag&table=" + tbl + "&id=" + id + "&row_status=" + row_status + "&list_status=" + list_status;
    window.location.href = url;
}
function cancelthis(msg, url, id) {
    if (confirm(msg)) {
        $.post(url, { id:id, ce:0 }, function(res) {
            $(".modal-body").html(res);
            $('#myModal').modal('show');
        });
    }
}
function update_status(tbl, id, row_status, list_status, ce=1) {
    let ceval = (ce==1) ? "" : "&ce=0";
    window.location.href = "index.php?module=" + tbl + "&func=update_flag&table=" + tbl + "&id=" + id + "&row_status=" + row_status + "&list_status=" + list_status + ceval;
}
function update_listing(tbl, id) {
    if (id > 1)
        window.location.href = "index.php?module=" + tbl + "&func=listing";
    else
        window.location.href = "index.php?module=" + tbl + "&func=listing&status=" + id;
}
function callauto(id, url, hid, headers='') {
    $("#" + id).autocomplete({
        delay: 1000,
        autoFocus: true,
        open: function() {
            if (headers) {
                col_names = "";
                $.each( headers, function( key, value ) {
                    firstcol = (key==0) ? "ACFirst" : "ACColumn";
                    col_names += "<div class='" + firstcol +"'>"+value+"</div>";
                });
                $('ul.ui-autocomplete').prepend("<li><div class='ACRow ACtitle'>"+col_names+"</div></li>");
            }
        },
        source: function(request, response) {
            var name = $("#" + id).val();
            $.ajax({
                url: url,
                dataType: "json",
                data: { filter: name },
                    success: function(data) {
                    response(jQuery.map(data, function(item) {
                        if (typeof (hid) == "string") {
                            return { value: item.name, key: item.id };
                        } else {
                            return item;
                        }
                    }));
                }
            });
        },
        select: function(event, ui) {
            if (typeof (hid) == "string") {
                $("#" + hid).val(ui.item.key);
            } else {
                for (j in hid) {
                    $("#" + hid[j]).val(ui.item["col" + j]).trigger('change');
                }
            }
        }
    });
}