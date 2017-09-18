<script src="{{url('js/jquery-migrate-3.0.0.js')}}"></script>

<link rel="stylesheet" href="{{url('kendoui/styles/kendo.common.min.css')}}"  />
<link rel="stylesheet" href="{{url('kendoui/styles/kendo.metro.min.css')}}"  />

<script type="text/javascript" src="{{url('kendoui/js/kendo.web.min.js')}}"></script>
<script type="text/javascript">
function gridDataBound(e) {
    var grid = e.sender;
    if (grid.dataSource.total() == 0) {
        var colCount = grid.columns.length;
        $(e.sender.wrapper)
                .find('tbody')
                .append('<tr class="kendo-data-row"><td colspan="' + colCount + '" class="no-data" style="text-align:center; font-family:Impact, Charcoal, sans-serif; font-size:19px;">There is no data to show in the grid.</td></tr>');
    }
}

function gridDataBoundJobcard(e) {
    var grid = e.sender;
    if (grid.dataSource.total() == 0) {
        var colCount = grid.columns.length;
        $(e.sender.wrapper)
                .find('tbody')
                .append('<tr class="kendo-data-row"><td colspan="' + colCount + '" class="no-data" style="text-align:center; font-family:Impact, Charcoal, sans-serif; font-size:19px;">There is no data to show in the grid.</td></tr>');
    }
    else
    {
        var items = this._data;
        var global_key = [];
        var tableRows = $(this.table).find('tr');
        var t1 = "00:00";
        var t3 = "00:00";
        var mins = 0;
        var minstfour = 0;
        var hrs = 0;
        var hrstfour = 0;
        tableRows.each(function (index) {
            var row = $(this);
            var item = items[index];
            if (global_key.length == 0)
            {
                global_key.push({'name': item.day_status, 'value': 0});
            }
            else
            {
                var raw_status = 0;
                $.each(global_key, function (key, val) {
                    if (val.name == item.day_status)
                    {
                        raw_status = 1;

                    }
                });

                if (raw_status == 0)
                {
                    global_key.push({'name': item.day_status, 'value': 0});
                }

                if (item.day_status == "Late IN")
                {
                    var raw_status_reinip = 0;
                    $.each(global_key, function (key, val) {
                        if (val.name == "P")
                        {
                            raw_status_reinip = 1;

                        }
                    });

                    if (raw_status_reinip == 0)
                    {
                        global_key.push({'name': 'P', 'value': 0});
                    }

                    $.each(global_key, function (key, val) {
                        if (val.name == "P")
                        {
                            val.value += 1;

                        }
                    });
                }

                if (item.day_status == "Late OUT")
                {
                    var raw_status_reinip = 0;
                    $.each(global_key, function (key, val) {
                        if (val.name == "P")
                        {
                            raw_status_reinip = 1;

                        }
                    });

                    if (raw_status_reinip == 0)
                    {
                        global_key.push({'name': 'P', 'value': 0});
                    }

                    $.each(global_key, function (key, val) {
                        if (val.name == "P")
                        {
                            val.value += 1;

                        }
                    });
                }

            }

            $.each(global_key, function (key, val) {
                if (val.name == item.day_status)
                {
                    val.value += 1;

                }
            });


            t1 = t1.split(':');
            if (item.total_ot == null)
            {
                var dd="00:00:00";
                var t2 = dd.split(':');
            }
            else
            {
                var t2 = item.total_ot.split(':');
            }
            
            mins = Number(t1[1]) + Number(t2[1]);
            minhrs = Math.floor(parseInt(mins / 60));
            hrs = Number(t1[0]) + Number(t2[0]) + minhrs;
            mins = mins % 60;
            t1 = hrs.padDigit() + ':' + mins.padDigit();

            t3 = t3.split(':');
            
            if (item.total_time == null)
            {
                var dd="00:00:00";
                var t4 = dd.split(':');
            }
            else
            {
                var t4 = item.total_time.split(':');
            }
            

            minstfour = Number(t3[1]) + Number(t4[1]);
            minhrstfour = Math.floor(parseInt(minstfour / 60));
            hrs = Number(t3[0]) + Number(t4[0]) + minhrstfour;
            minstfour = mins % 60;
            t3 = hrs.padDigit() + ':' + mins.padDigit();






        });




        //$('#timeSum').text(t1);

        var foohtml = '';
        var foohtmlSum = '';
        $.each(global_key, function (key, val) {
            foohtml += '<li class="breadcrumb-item"><span>' + val.name + ': ' + val.value + '</span></li>';
        });
        $("#FooterJobCard").html(foohtml);
        foohtmlSum = '<li class="breadcrumb-item"><span>Total Working Time = ' + t3 + '</span></li>';
        foohtmlSum += '<li class="breadcrumb-item"><span>Total OT = ' + t1 + '</span></li>';
        $("#FooterJobCardSum").html(foohtmlSum);
    }
}

Number.prototype.padDigit = function () {
    return (this < 10) ? '0' + this : this;
}

$("#addTimes").on('click', function () {

});

$("#tabstrip").kendoTabStrip({
    animation: {
        open: {
            effects: "fadeIn"
        }
    }
});
</script>

<?php

function KendoDropDownArr($arr = array()) {
    $htstr = '';
    if (count($arr) != 0) {
        foreach ($arr as $fid) {
            $field = "'" . $fid . "'";
            $htstr .='<script>
			            $("select[name=' . $field . ']").kendoDropDownList({
			                optionLabel: " Please Select  "
			            }).data("kendoDropDownList").select(0);
			           </script>';
        }
    }

    return $htstr;
}
?>
<style type="text/css" media="screen">
    span.k-edit,span.k-delete{
        margin-top: -3px !important;
    }

    span.k-i-arrow-s{
        margin-top: -5px !important;
    }
</style>
