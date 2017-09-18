<?php
if (isset($data)) {
    $pageinfo = array("Edit Calendar Settings", "Edit Calendar Record", "", "SUL");
} else {
    $pageinfo = array("Calendar Settings", "Add Calendar Record", "", "SUL");
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

                            <form name="StaffGrade" action="{{url('Settings/Calendar/Update/'.$data[0]->id)}}" method="post">
                                {{csrf_field()}}
                                <div class="row">
                                    <input type="hidden" name="company_id" value="{{$logged_emp_com}}" class="form-control" placeholder="Type Designation Title" id="l30">
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="l30">Date</label>
                                            <label class="input-group datepicker-only-init">
                                                <div class="form-control font-weight-bold">{{$data[0]->date}}</div>
                                                <span class="input-group-addon" style="">
                                                    <i class="icmn-calendar"></i>
                                                </span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-lg-4">
                                        <label for="l30">Please Select Day Status</label>
                                        <div class="input-group col-lg-12" style="margin-bottom: 2% !important;">
                                            <span class="input-group-addon graddonlabel">{{$data[0]->day_title}} </span>

                                            <select id="lunch" class="selectpicker form-control" data-live-search="true" title="Please Select Day Status" name="day_type_id">
                                                @if(isset($daytype))
                                                @foreach($daytype as $dt)
                                                <option <?php if($data[0]->day_type_id==$dt->id){ ?> selected="selected" <?php } ?> value="{{$dt->id}}" name="sat">{{$dt->title}}</option>
                                                @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>

                                    <!--                                    <div class="col-lg-6">
                                                                            <div class="form-group">
                                                                                <label for="l30">Is Active</label>
                                                                                <select class="form-control" name="is_active">
                                                                                    <option value="">Select Status</option>
                                                                                    <option <?php //if ($data['is_active'] == "Active") {  ?> selected="selected" <?php //}  ?> value="Active">Active</option>
                                                                                    <option <?php //if ($data['is_active'] == "Inactive") {  ?> selected="selected" <?php //}  ?> value="Inactive">Inactive</option>
                                                                                </select>
                                                                            </div>
                                                                        </div>-->
                                </div>


                                <div class="form-actions">
                                    <button type="submit"  class="btn btn-primary">Update</button>
                                    <button type="reset" class="btn btn-default">Cancel</button>
                                </div>
                            </form>
                            @else
                            <form name="Calendar" action="{{url('Settings/Calendar/Add')}}" method="post">
                                {{csrf_field()}}
                                <div class="row">

                                    <style type="text/css" media="screen">
                                        .graddonlabel{
                                            width: 150px;
                                        }
                                    </style>

                                    
                                    <div class="col-lg-12">
                                        <div class="form-group col-lg-12">
                                            <label for="l30" class="col-md-12">Select Company</label>
                                            <select class="form-control col-md-5" name="company_id">
                                                <option value="">Select Company</option>
                                                @if(isset($company))
                                                @foreach($company as $row)
                                                <option value="{{$row->id}}">{{$row->name}}</option>
                                                @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>

                                    
                                    <div class="col-lg-6">
                                        <div class="input-group col-lg-12" style="margin-bottom: 2% !important;">
                                            <span class="input-group-addon graddonlabel">Saturday </span>

                                            <select id="lunch" class="selectpicker form-control" data-live-search="true" title="Please Select Day Status" name="saturday">
                                                <option value="">Please Select Day Status</option>
                                                @if(isset($daytype))
                                                @foreach($daytype as $dt)
                                                <option value="{{$dt->id}}" name="sat">{{$dt->title}}</option>
                                                @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="input-group col-lg-12" style="margin-bottom: 2% !important;">
                                            <span class="input-group-addon graddonlabel">Sunday</span>

                                            <select id="lunch" class="selectpicker form-control" data-live-search="true" title="Please Select Day Status" name="sunday">
                                                <option value="">Please Select Day Status</option>
                                                @if(isset($daytype))
                                                @foreach($daytype as $dt)
                                                <option value="{{$dt->id}}">{{$dt->title}}</option>
                                                @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="input-group col-lg-12" style="margin-bottom: 2% !important;">
                                            <span class="input-group-addon graddonlabel">Monday</span>

                                            <select id="lunch" class="selectpicker form-control" data-live-search="true" title="Please Select Day Status" name="monday">
                                                <option value="">Please Select Day Status</option>
                                                @if(isset($daytype))
                                                @foreach($daytype as $dt)
                                                <option value="{{$dt->id}}">{{$dt->title}}</option>
                                                @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="input-group col-lg-12" style="margin-bottom: 2% !important;">
                                            <span class="input-group-addon graddonlabel">Tuesday</span>

                                            <select id="lunch" class="selectpicker form-control" data-live-search="true" title="Please Select Day Status" name="tuesday">
                                                <option value="">Please Select Day Status</option>
                                                @if(isset($daytype))
                                                @foreach($daytype as $dt)
                                                <option value="{{$dt->id}}">{{$dt->title}}</option>
                                                @endforeach
                                                @endif

                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="input-group col-lg-12" style="margin-bottom: 2% !important;">
                                            <span class="input-group-addon graddonlabel">Wednesday</span>

                                            <select id="lunch" class="selectpicker form-control" data-live-search="true" title="Please Select Day Status" name="wednesday">
                                                <option value="">Please Select Day Status</option>
                                                @if(isset($daytype))
                                                @foreach($daytype as $dt)
                                                <option value="{{$dt->id}}">{{$dt->title}}</option>
                                                @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="input-group col-lg-12" style="margin-bottom: 2% !important;">
                                            <span class="input-group-addon graddonlabel">Thursday</span>

                                            <select id="lunch" class="selectpicker form-control" data-live-search="true" title="Please Select Day Status" name="thursday">
                                                <option value="">Please Select Day Status</option>
                                                @if(isset($daytype))
                                                @foreach($daytype as $dt)
                                                <option value="{{$dt->id}}">{{$dt->title}}</option>
                                                @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="input-group col-lg-12" style="margin-bottom: 2% !important;">
                                            <span class="input-group-addon graddonlabel">Friday</span>

                                            <select id="lunch" class="selectpicker form-control" data-live-search="true" title="Please Select Day Status" name="friday">
                                                <option value="">Please Select Day Status</option>
                                                @if(isset($daytype))
                                                @foreach($daytype as $dt)
                                                <option value="{{$dt->id}}">{{$dt->title}}</option>
                                                @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="input-group col-lg-12" style="margin-bottom: 2% !important;">
                                            <span class="input-group-addon graddonlabel">Year</span>

                                            <select id="lunch" class="selectpicker form-control" data-live-search="true" title="Please Select Year" name="year">
                                                <option value="">Please Select Year</option>

                                                @for($i=date('Y')+10; $i>=date('Y')-2; $i--)
                                                <option value="{{$i}}">{{$i}}</option>
                                                @endfor

                                            </select>
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
                        <div class="col-xl-12">
                            <!--Vertical Form Starts Here-->
                            <!-- kendo table code start from here-->
                            <div class="row">
                                <div id="grid" class="col-md-12"></div>
                            </div>
                            <script id="action_template" type="text/x-kendo-template">
                                <a class="k-button k-button-icontext k-grid-edit" href="{{url('Settings/Calendar/Edit')}}/#=id#"><span class="k-icon k-edit"></span> Edit</a>
                                <a style="display:none !important;" class="k-button k-button-icontext k-grid-delete" onclick="javascript:deleteClick(#=id#);" ><span class="k-icon k-delete"></span> Delete</a>
                            </script>

                            <script type="text/javascript">
                                function deleteClick(id) {
                                    var c = confirm("Do you want to delete?");
                                    if (c === true) {
                                        $.ajax({
                                            type: "POST",
                                            dataType: "json",
                                            url: "Calendar/Delete",
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
                                                url: "<?= url('Settings/Calendar/Json') ?>",
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
                                                    year: {type: "string"},
                                                    company_id: {type: "string"},
                                                    date: {type: "string"},
                                                    day_title: {type: "string"},
                                                    title: {type: "string"},
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
                                            {field: "company_id", title: "Company", width: "80px"},
                                            {field: "year", title: "Year ", width: "80px"},
                                            {field: "date", title: "Date ", width: "80px"},
                                            {field: "day_title", title: "Day Name ", width: "80px"},
                                            {field: "title", title: "Day Status ", width: "80px"},
                                            {field: "is_active", title: "Is Active", width: "80px"},
                                            {field: "created_at", title: "Created ", width: "100px", },
                                            {
                                                title: "Action", width: "80px",
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


<link rel="stylesheet" type="text/css" href="http://192.168.1.36:8000/vendors/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css">
<link rel="stylesheet" type="text/css" href="http://192.168.1.36:8000/vendors/fullcalendar/dist/fullcalendar.min.css">

<script src="http://192.168.1.36:8000/vendors/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js"></script>
<script src="http://192.168.1.36:8000/vendors/fullcalendar/dist/fullcalendar.min.js"></script>
<script> 
    $(document).ready(function () {

        $('input[name=date]').datetimepicker({
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
//console.log($(this).val());
            calculateLeave();
        });

    });</script>
@endsection
