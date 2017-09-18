
<?php
if (isset($data)) {
    $pageinfo = array("Edit Manual Job Card Entry", "Edit Manual Job Card Entry Record", "", "SUL");
} else {
    $pageinfo = array("Add Manual Job Card Entry", " Manual Job Card Entry", "Manual Job Card Entry Data List", "SUL");
}
?>

@extends('layout.master')
@section('content')
@include('include.coreBarcum')
<div class="row">
    <div class="col-lg-12">
        <div class="cat__core__sortable" id="left-col">
            <section class="card" order-id="card-1">
                <div class="card-header">
                    <h5 class="mb-0 text-black">
                        <strong>{{$pageinfo[0]}}</strong>
                        <!--<small class="text-muted">All cards are draggable and sortable!</small>-->
                    </h5>
                </div>
                <div class="card-block">
                    <div class="row">
                        <div class="col-xl-12">
                            <!--Vertical Form Starts Here-->
                            @if(isset($data))
                            <form name="ManualJobcard" action="{{url('ManualJobcard/Update/'.$data['id'])}}" method="post">
                                {{csrf_field()}}
                                <div class="input_fields_containerEdu">
                                    <div class="row append">
                                        @if(empty($logged_emp_com))
                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label for="l30" class="col-md-12">Company Name</label>
                                                <select class="form-control" name="company_id">
                                                    @if(isset($company))
                                                    @foreach($company as $row)
                                                    <option <?php if ($data['company_id'] == $row->id) { ?> selected="selected" <?php } ?> value="{{$row->id}}">{{$row->name}}</option>
                                                    @endforeach
                                                    @endif

                                                </select>
                                            </div>
                                        </div>
                                        @else
                                        <input type="hidden" name="Hcompany_id" value="{{$logged_emp_com}}" class="form-control" placeholder="Type Designation Title" id="l30">
                                        @endif

                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label for="l30">Employee Code</label>
                                                <input type="text" name="emp_code" value="{{$data->emp_code}}" class="form-control" placeholder="Type Employee Code" id="l30">
                                            </div>
                                        </div>
                                        <div class="col-lg-3">

                                            <!--<div class="form-group">
                                                <label for="l30">Day Type</label>
                                                <select class="form-control" name="daytype_id">
                                                    <option value="">Select Day Type</option>
                                                    @if(isset($dayType))
                                                    @foreach($dayType as $row)

                                                    <option <?php //if ($data->day_type == $row->day_short_code) {         ?> selected="selected" <?php //}         ?> value="{{$row->day_short_code}}">{{$row->title}}</option>
                                                    @endforeach
                                                    @endif

                                                </select>
                                            </div>-->
                                            <div class="form-group">
                                                <label for="l30">Day Type</label>
                                                <select class="form-control" name="daytype_id">

                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label for="l30">Date</label>
                                                <label class="input-group datepicker-only-init">
                                                    <div  class="form-control">{{$data->date}}</div>
                                                    <input type="hidden" id="date" name="date" value="{{$data->date}}" class="form-control required" placeholder="Type End Day">
                                                    <span class="input-group-addon" style="">
                                                        <i class="icmn-calendar"></i>
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <!--<a href="#" class="btn btn-success add_more_buttonEdu">Add More</a>-->

                                <div class="form-actions">
                                    <button type="submit"  class="btn btn-primary">Update</button>
                                    <button type="reset" class="btn btn-default">Cancel</button>
                                </div>
                            </form>
                            @else
                            <form name="ManualJobcard" action="{{url('ManualJobcard/Add')}}" method="post">
                                {{csrf_field()}}

                                <div class="input_fields_containerEdu">
                                    <div class="row append">
                                        @if(empty($logged_emp_com))
                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label for="l30" class="col-md-12">Company Name</label>
                                                <select class="form-control" name="company_id">
                                                    <option value="">Select Company</option>
                                                    @if(isset($company))
                                                    @foreach($company as $row)
                                                    <option value="{{$row->id}}">{{$row->name}}</option>
                                                    @endforeach
                                                    @endif

                                                </select>
                                            </div>
                                        </div>
                                        @else
                                        <input type="hidden" name="company_id" value="{{$logged_emp_com}}" class="form-control" placeholder="Type Designation Title" id="l30">
                                        @endif
                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label for="l30">Employee Code</label>
                                                <input type="text" name="emp_code" class="form-control" placeholder="Type Employee Code" id="l30">
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            <!--                                        <div class="form-group">
                                                                                        <label for="l30">Day Type</label>
                                                                                        <select class="form-control" name="daytype_id[]">
                                                                                            <option value="">Select Day Type</option>
                                                                                            @if(isset($dayType))
                                                                                            @foreach($dayType as $row)
                                                                                            <option value="{{$row->day_short_code}}">{{$row->title}}</option>
                                                                                            @endforeach
                                                                                            @endif
                                            
                                                                                        </select>
                                                                                    </div>-->
                                            <div class="form-group">
                                                <label for="l30">Day Type</label>
                                                <select class="form-control" name="daytype_id">

                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-2">
                                            <div class="form-group">
                                                <label for="l30">Date</label>
                                                <label class="input-group datepicker-only-init">
                                                    <input type="text" id="date" name="date" class="form-control required" placeholder="Type End Day">
                                                    <span class="input-group-addon" style="">
                                                        <i class="icmn-calendar"></i>
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>


                                </div>
                                <!--<a href="#"  class="btn btn-success add_more_buttonEdu">Add More</a>-->

                                <div class="form-actions">
                                    <button type="submit"  class="btn btn-primary">Create</button>
                                    <button type="reset" class="btn btn-default">Cancel</button>
                                </div>
                            </form>
                            @endif
                            <!--Vertical Form Ends Here-->
                        </div>
                        <div class="col-xl-12">
                            <!--Vertical Form Starts Here-->
                            <!-- kendo table code start from here-->
                            <div class="row">

                                <div class="col-xl-11 ">
                                    <strong>{{$pageinfo[2]}}</strong>
                                </div>
                                <div class="col-xl-1 pull-right">
                                    <a href="javascript:void(0)" id="export_excel" class="fa fa-file-excel-o fa-2x"  title="Export Excel" data-original-title="Export Excel">&nbsp;</a>
                                    <a href="javascript:void(0)"  id="export_pdf" class="fa fa-file-pdf-o fa-2x"  title="Export Pdf" data-original-title="Export Pdf">&nbsp;</a>  
                                </div>

                                <div id="grid" class="col-md-12 form-actions"></div>
                            </div>
                            <script id="action_template" type="text/x-kendo-template">
                                <a class="k-button k-button-icontext k-grid-edit" href="{{url('ManualJobcard/Edit')}}/#=id#"><span class="k-icon k-edit"></span> Edit</a>
                                <a style="display:none !important;" class="k-button k-button-icontext k-grid-delete" onclick="javascript:deleteClick(#=id#);" ><span class="k-icon k-delete"></span> Delete</a>
                            </script>

                            <script type="text/javascript">
                                function deleteClick(id) {
                                    var c = confirm("Do you want to delete?");
                                    if (c === true) {
                                        $.ajax({
                                            type: "POST",
                                            dataType: "json",
                                            url: "ManualJobcard/Delete",
                                            data: {id: id, '_token': '<?= csrf_token() ?>'},
                                            success: function (result) {
                                                $(".k-i-refresh").click();
                                            }
                                        });
                                    }
                                }

                            </script>
                            <script type="text/javascript">

                                $(document).ready(function () {
                                    $("#export_excel").click(function () {
                                        var link = "<?= url('/') ?>/Export/ManualJobcard/List/Excel";
                                        window.location.href = link;
                                    });

                                    $("#export_pdf").click(function () {
                                        var link = "<?= url('/') ?>/Export/ManualJobcard/List/Pdf";
                                        window.location.href = link;
                                    });
                                    var dataSource = new kendo.data.DataSource({
                                        transport: {
                                            read: {
                                                url: "<?= url('ManualJobcard/Json') ?>",
                                                type: "GET",
                                                datatype: "json"

                                            }
                                        },
                                        schema: {
                                            data: "data",
                                            total: "total",
                                            model: {
                                                id: "id",
                                                fields: {
                                                    id: {type: "number"},
                                                    company_id: {type: "string"},
                                                    emp_code: {type: "string"},
                                                    day_type: {type: "string"},
                                                    date: {type: "string"},
                                                    created_at: {type: "string"}
                                                }
                                            }
                                        },
                                        pageSize: 20,
                                        height: 550,
                                        groupable: true,
                                        sortable: true
                                      
                                    });
                                    $("#grid").kendoGrid({
                                        dataBound: gridDataBound,
                                        dataSource: dataSource,
                                        filterable: true,
                                        pageable: {
                                            refresh: true,
                                            pageSizes: [5,50,100,500,1000],
                                            buttonCount: 5
                                        },
                                        sortable: true,
                                        groupable: true,
                                        columns: [
                                            {field: "id", title: "#", width: "40px", filterable: false},
                                            {field: "company_id", title: "Company Name ", width: "80px"},
                                            {field: "emp_code", title: "Employee Code ", width: "80px"},
                                            {field: "day_type", title: "Day Status ", width: "80px"},
                                            {field: "date", title: "Date", width: "80px"},
                                            {field: "created_at", title: "Created ", width: "70px", },
                                            {
                                                title: "Action", width: "100px",
                                                template: kendo.template($("#action_template").html())
                                            }
                                        ],
                                    });
                                });

                            </script>
                            <!-- kendo table code end fro here-->
                            <!--Vertical Form Ends Here-->
                        </div>
                    </div>
                </div>
            </section>


        </div>
    </div>

</div>
@endsection
@section('extraFooter')
@include('include.coreKendo')
<?= MenuPageController::genarateKendoDatePicker(array("date")) ?>
<link rel="stylesheet" type="text/css" href="http://192.168.1.36:8000/vendors/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css">
<link rel="stylesheet" type="text/css" href="http://192.168.1.36:8000/vendors/fullcalendar/dist/fullcalendar.min.css">

<script src="http://192.168.1.36:8000/vendors/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js"></script>
<script src="http://192.168.1.36:8000/vendors/fullcalendar/dist/fullcalendar.min.js"></script>
<script> $(document).ready(function () {

                                    $('#date').datetimepicker({
                                        format: 'YYYY-MM-DD',
                                        icons: {
                                            time: 'fa fa-clock-o',
                                            date: 'fa fa-calendar',
                                            up: 'fa fa-chevron-up',
                                            down: 'fa fa-chevron-down',
                                            previous: 'fa fa-chevron-left',
                                            next: 'fa fa-chevron-right',
                                            today: 'fa fa-screenshot',
                                            clear: 'fa fa-trash',
                                            close: 'fa fa-remove'
                                        }
                                    }).on('dp.change', function () {


                                    });

                                });
                                var max_fields_limit = 10; //set limit for maximum input fields
                                var x = 1; //initialize counter for text box
                                $('.add_more_buttonEdu').click(function (e) { //click event on add more fields button having class add_more_button
                                    e.preventDefault();
                                    if (x < max_fields_limit) { //check conditions
                                        x++; //counter increment
                                        $('.input_fields_containerEdu').append('<div class="row append">' +
                                                '@if(empty($logged_emp_com) || !isset($logged_emp_com) || $logged_emp_com=="Undefined")' +
                                                '<div class="col-lg-3">' +
                                                '<div class="form-group">' +
                                                '<label for="l30" class="col-md-12">Company Name</label>' +
                                                '<select class="form-control" name="company_id[]">' +
                                                '<option value="">Select Company</option>' +
                                                '@if(isset($company))' +
                                                '@foreach($company as $row)' +
                                                '<option value="{{$row->id}}">{{$row->name}}</option>' +
                                                '@endforeach' +
                                                '@endif' +
                                                '</select>' +
                                                '</div>' +
                                                '</div>' +
                                                '@else' +
                                                '<input type="hidden" name="company_id[]" value="{{$logged_emp_com}}" class="form-control" placeholder="" id="l30">' +
                                                '@endif' +
                                                '<div class="col-lg-3">' +
                                                '<div class="form-group">' +
                                                '<label for="l30">Employee Code</label>' +
                                                '<input type="text" name="emp_code[]" class="form-control" placeholder="Type Employee Code" id="l30">' +
                                                ' </div>' +
                                                ' </div>' +
                                                ' <div class="col-lg-3">' +
                                                ' <div class="form-group">' +
                                                '<label for="l30">Day Type</label>' +
                                                '<select class="form-control" name="daytype_id[]">' +
                                                '</select>' +
                                                ' </div>' +
                                                '</div>' +
                                                '<div class="col-lg-2">' +
                                                '<div class="form-group">' +
                                                ' <label for="l30">Date</label>' +
                                                '<label class="input-group datepicker-only-init">' +
                                                '<input type="text" id="date" name="date[]" class="form-control required" placeholder="Type End Day">' +
                                                '<span class="input-group-addon" style="">' +
                                                '<i class="icmn-calendar"></i>' +
                                                '</span>' +
                                                ' </label>' +
                                                '</div>' +
                                                '</div>' +
                                                '<a href="#" class="remove_fieldEdu btn btn-danger" style="margin-top:3% !important;max-height:40px"><i class="fa fa-close"></i></a>' +
                                                '</div>'); //add input field
                                    }
                                });
                                $('.input_fields_containerEdu').on("click", ".remove_fieldEdu", function (e) { //user click on remove text links
                                    e.preventDefault();
                                    $(this).parent('div').remove();
                                    x--;
                                });



</script> 

<script>
    function day() {
        //  setInterval(function () {
        $.post("<?= url('Settings/AttendanceJobcardPolicy/Json') ?>",
                {'_token': '<?= csrf_token() ?>'},
        function (data) {
            var total = data.length;
            if (total != 0)
            {
                var str = '';
                str += '<option selected="selected" value="">Select Day Type</option>';
                $.each(data, function (index, val) {
                    str += '<option value="' + val.day_short_code + '">' + val.leave_title + '</option>';
                    // console.log(val.day_short_code);
                });
                // console.log(data);
                $("select[name='daytype_id']").html(str);
            }

        });

        // }, 1000);

    }
    day();

</script>
@endsection