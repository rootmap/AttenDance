<?php
if (isset($data)) {
    $pageinfo = array("Edit Weekend OT Policy Settings", "Edit Weekend OT Policy Record", "Weekend OT Policy Report", "SUL");
} else {
    $pageinfo = array("Weekend OT Policy Settings", "Add Weekend OT Policy Record", "Weekend OT Policy Report", "SUL");
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
                            <form enctype="multipart/form-data" name="Company" action="{{url('Settings/WeekendOTPolicy/Update/'.$data['id'])}}" method="post">
                                {{csrf_field()}}

                                <div class="row">
                                    @if(empty($logged_emp_com))
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="l30">Company Name</label>
                                            <select class="required form-control" name="company_id" required>
                                                @if(isset($company))
                                                @foreach($company as $row)
                                                <option <?php if ($data->company_id == $row->id) { ?> selected="selected" <?php } ?> value="{{$row->id}}">{{$row->name}}</option>
                                                @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                    @else
                                    <input type="hidden" name="company_id" value="{{$logged_emp_com}}" class="form-control" placeholder="Type Designation Title" id="l30">
                                    @endif
                                </div>
                                <div class="row">
                                    <div class="form-check col-md-12">

                                        <label class="form-check-label">
                                            <!--<input class="form-check-input is_ot_count_as_total_working_hourE" name="is_ot_will_start_after_fix_hour" id="exampleRadios1" value="1" checked="" type="radio">-->
                                            <input 
                                                @if($data->is_ot_count_as_total_working_hour==1)
                                                checked="checked"  
                                                @endif 
                                                type="radio" name="is_ot_will_start_after_fix_hour" class="form-check-input is_ot_count_as_total_working_hourE"  value="1"  id="exampleRadios1"> 
                                                Is OT Count as Total Working Hour
                                        </label>
                                    </div>
                                    <div class="form-check col-md-12">
                                        <label class="form-check-label">
                                            <!--<input class="form-check-input is_ot_will_start_after_fix_hourE" name="is_ot_will_start_after_fix_hour" id="exampleRadios2" value="2" type="radio">-->
                                            <input 
                                                @if($data->is_ot_will_start_after_fix_hour==1)
                                                checked="checked"  
                                                @endif 
                                                type="radio" name="is_ot_will_start_after_fix_hour" class="form-check-input is_ot_will_start_after_fix_hourE" value="2"  id="exampleRadios2"> 
                                                Is OT Will Start After Fix Hour
                                        </label>
                                    </div>

                                    <div class="col-lg-3 hour_afterE">
                                        <div class="form-group">
                                            <label for="l30">Hour After</label>
                                            <input type="text" name="hour_after" value="{{$data->hour_after}}"  class="form-control timepicker-init" placeholder="Hour After">
                                        </div>
                                    </div>
									
									
									<div class="form-check col-md-12">
                                        <label class="form-check-label">
                                            <input class="form-check-input is_standard_max_ot_hour" 
											@if($data->is_standard_max_ot_hour==1)
                                                checked="checked"  
                                                @endif 
											name="is_standard_max_ot_hour" id="exampleRadios2" value="1" type="checkbox">
                                            Is Standard Max OT Hour Active
                                        </label>
                                    </div>

                                    <div class="col-lg-3 standard_max_ot_hour">
                                        <div class="form-group">
                                            <label for="l30">Standard Max OT Hour</label>
                                            <input type="text" name="standard_max_ot_hour" value="{{$data->standard_max_ot_hour}}" class="form-control timepicker-init" placeholder="Standard Max OT Hour">
                                        </div>
                                    </div>

                                </div>


                                <div class="form-actions">
                                    <button type="submit"  class="btn btn-primary">Update</button>
                                    <button type="reset" class="btn btn-default">Cancel</button>
                                </div>
                            </form>
                            @else
                            <form name="AttendancePolicy" enctype="multipart/form-data" action="{{url('Settings/WeekendOTPolicy/Add')}}" method="post">
                                {{csrf_field()}}
                                <div class="row">
                                    @if(empty($logged_emp_com))
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="l30">Company Name</label>
                                            <select class="form-control" id="company" name="company_id">
                                                <option selected="selected" value="">Select Company</option>
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
                                </div>

                                <div class="row">
                                    <div class="form-check col-md-12">
                                        <label class="form-check-label">
                                            <input class="form-check-input is_ot_count_as_total_working_hour" name="is_ot_will_start_after_fix_hour" id="exampleRadios1" value="1" checked="" type="radio">
                                            Is OT Count as Total Working Hour
                                        </label>
                                    </div>
                                    <div class="form-check col-md-12">
                                        <label class="form-check-label">
                                            <input class="form-check-input is_ot_will_start_after_fix_hour" name="is_ot_will_start_after_fix_hour" id="exampleRadios2" value="2" type="radio">
                                            Is OT Will Start After Fix Hour
                                        </label>
                                    </div>

                                    <div class="col-lg-3 hour_after">
                                        <div class="form-group">
                                            <label for="l30">Hour After</label>
                                            <input type="text" name="hour_after"  class="form-control timepicker-init" placeholder="Hour After">
                                        </div>
                                    </div>
									<div class="form-check col-md-12">
                                        <label class="form-check-label">
                                            <input class="form-check-input is_standard_max_ot_hour" name="is_standard_max_ot_hour" id="exampleRadios2" value="1" type="checkbox">
                                            Is Standard Max OT Hour Active
                                        </label>
                                    </div>

                                    <div class="col-lg-3 standard_max_ot_hour">
                                        <div class="form-group">
                                            <label for="l30">Standard Max OT Hour</label>
                                            <input type="text" name="standard_max_ot_hour"  class="form-control timepicker-init" placeholder="Standard Max OT Hour">
                                        </div>
                                    </div>


                                </div>

                                <div class="form-actions">
                                    <button type="submit"  class="btn btn-primary">Create</button>
                                    <button type="reset" class="btn btn-default">Cancel</button>
                                </div>
                            </form>
                            @endif

                            <!--Vertical Form Ends Here-->
                        </div>


                        <div class="row">
                            <div class="col-lg-12">
                                <div class="cat__core__sortable" id="left-col">

                                    <div class="card-header">

                                        <div class="pull-right cat__core__sortable__control">

                                            <a href="javascript:void(0)" id="export_excel" class="fa fa-file-excel-o fa-2x"  title="Export Excel" data-original-title="Export Excel">&nbsp;</a>
                                            <a href="javascript:void(0)"  id="export_pdf" class="fa fa-file-pdf-o fa-2x"  title="Export Pdf" data-original-title="Export Pdf">&nbsp;</a>
                                        </div>

                                        <h5 class="mb-0 text-black">
                                            <strong>{{$pageinfo[2]}}</strong>
                                            <!--<small class="text-muted">All cards are draggable and sortable!</small>-->
                                        </h5>

                                    </div>
                                    <div class="row">

                                        <div id="grid" class="col-md-12"></div>
                                    </div>

                                    <script id="action_template" type="text/x-kendo-template">
                                        <a class="k-button k-button-icontext k-grid-edit" href="{{url('Settings/WeekendOTPolicy/Edit')}}/#=id#"><span class="k-icon k-edit"></span> Edit</a>
                                        <a class="k-button k-button-icontext k-grid-delete" onclick="javascript:deleteClick(#=id#);" ><span class="k-icon k-delete"></span> Delete</a>
                                    </script>

                                    <script type="text/javascript">
                                        function deleteClick(id) {
                                            var c = confirm("Do you want to delete?");
                                            if (c === true) {
                                                $.ajax({
                                                    type: "POST",
                                                    dataType: "json",
                                                    url: "WeekendOTPolicy/Delete",
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
                                            var dataSource = new kendo.data.DataSource({
                                                transport: {
                                                    read: {
                                                        url: "<?= url('Settings/WeekendOTPolicy/Json') ?>",
                                                        type: "GET",
                                                        datatype: "json"

                                                    }
                                                },
                                                autoSync: false,
                                                schema: {
                                                    data: "data",
                                                    total: "total",
                                                    model: {
                                                        id: "id",
                                                        fields: {
                                                            id: {type: "number"},
                                                            name: {type: "string"},
                                                            is_ot_count_as_total_working_hour: {type: "boolean"},
                                                            is_ot_will_start_after_fix_hour: {type: "boolean"},
                                                            hour_after: {type: "string"},
                                                            is_standard_max_ot_hour: {type: "boolean"},
                                                            standard_max_ot_hour: {type: "string"},
                                                            created_at: {type: "string"}
                                                        }
                                                    }
                                                },
                                                pageSize: 10,
                                                serverPaging: false,
                                                serverFiltering: false,
                                                serverSorting: false
                                            });
                                            $("#grid").kendoGrid({
                                                dataBound: gridDataBound,
                                                dataSource: dataSource,
                                                filterable: true,
                                                pageable: {
                                                    refresh: true,
                                                    input: true,
                                                    numeric: false,
                                                    pageSizes: true,
                                                    pageSizes:[10, 20, 50, 100, 200, 400]
                                                },
                                                sortable: true,
                                                groupable: true,
                                                columns: [
                                                    {field: "id", title: "#", width: "40px", filterable: false},
                                                    //{field: "company_id", title: "Company Name", width: "80px"},
                                                    {field: "is_ot_count_as_total_working_hour", title: "Is OT Count as Total Working Hour", width: "120px"},
                                                    {field: "is_ot_will_start_after_fix_hour ", title: "Is OT Will Start  After Fix Hour", width: "110px"},
                                                    {field: "hour_after ", title: "Hour After", width: "60px"},
													{field: "is_standard_max_ot_hour ", title: "Is Standard OT", width: "110px"},
                                                    {field: "standard_max_ot_hour ", title: "Max Hour", width: "60px"},
                                                    {field: "created_at", title: "Created ", width: "60px", },
                                                    {
                                                        title: "Action", width: "100px",
                                                        template: kendo.template($("#action_template").html())
                                                    }
                                                ],
                                            });


                                            $("#export_excel").click(function () {

                                                var emp_code = $("input[name='emp_code']").val();
                                                if (emp_code == '')
                                                {
                                                    emp_code = 0;
                                                }
                                                var start_date = $("input[name='start_date']").val();
                                                if (start_date == '')
                                                {
                                                    start_date = 0;
                                                }
                                                var end_date = $("input[name='end_date']").val();
                                                if (end_date == '')
                                                {
                                                    end_date = 0;
                                                }
                                                var param = {'emp_code': emp_code, 'start_date': start_date, 'end_date': end_date, '_token': '<?= csrf_token() ?>'};
                                                var link = "<?= url('/') ?>/Settings/WeekendOTPolicy/Export/Excel";
                                                window.location.href = link;

                                            });

                                            $("#export_pdf").click(function () {

                                                var emp_code = $("input[name='emp_code']").val();
                                                if (emp_code == '')
                                                {
                                                    emp_code = 0;
                                                }
                                                var start_date = $("input[name='start_date']").val();
                                                if (start_date == '')
                                                {
                                                    start_date = 0;
                                                }
                                                var end_date = $("input[name='end_date']").val();
                                                if (end_date == '')
                                                {
                                                    end_date = 0;
                                                }
                                                var param = {'emp_code': emp_code, 'start_date': start_date, 'end_date': end_date, '_token': '<?= csrf_token() ?>'};
                                                var link = "<?= url('/') ?>/Settings/WeekendOTPolicy/Export/Pdf";
                                                window.location.href = link;

                                            });
                                        });

                                    </script>
                                    <!-- kendo table code end fro here-->
                                    <!--Vertical Form Ends Here-->
                                </div>

                            </div>
                        </div>

                    </div>
                </div>
            </section>


        </div>

        @endsection


        @section('extraFooter')
        @include('include.coreKendo')
        <script src="{{url('vendors/jquery-validation/dist/jquery.validate.js')}}"></script>

        <link rel="stylesheet" type="text/css" href="{{url('vendors/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css')}}">
        <link rel="stylesheet" type="text/css" href="{{url('vendors/fullcalendar/dist/fullcalendar.min.css')}}">

        <script src="{{url('vendors/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js')}}"></script>
        <script src="{{url('vendors/fullcalendar/dist/fullcalendar.min.js')}}"></script>
        <script>
                                        $(function () {
                                            $('.timepicker-init').datetimepicker({
                                                widgetPositioning: {
                                                    vertical: 'bottom'
                                                },
                                                icons: {
                                                    time: "fa fa-clock-o",
                                                    date: "fa fa-calendar",
                                                    up: "fa fa-arrow-up",
                                                    down: "fa fa-arrow-down",
                                                    previous: 'fa fa-arrow-left',
                                                    next: 'fa fa-arrow-right'
                                                },
                                                format: 'HH:mm:ss'
                                            });
                                        });
        </script>
        <script>
            $('.hour_after').hide('slow');
			$('.standard_max_ot_hour').hide('slow');
            $('.is_ot_will_start_after_fix_hour').change(function () {

                if (this.checked) {
                    $('.hour_after').show('slow');
                } else {
                    $('.hour_after').hide('slow');
                }
            });
            $('.is_ot_count_as_total_working_hour').change(function () {

                if (this.checked) {
                    $('.hour_after').hide('slow');
                } else {

                    $('.hour_after').show('slow');
                }
            });
			
			
			$('.is_standard_max_ot_hour').change(function () {

                if (this.checked) {
                    $('.standard_max_ot_hour').show('slow');
                } else {
                    $('.standard_max_ot_hour').hide('slow');
                }
            });


        </script>


        @endsection
