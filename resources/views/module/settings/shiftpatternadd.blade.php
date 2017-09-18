<?php
if (isset($data)) {
    $pageinfo = array("Edit Shift Pattern Settings", "Edit Shift Pattern  Record", "", "SUL");
} else {
    $pageinfo = array("Add New Shift Pattern Settings", "Add New Shift Pattern Record", "", "SUL");
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
                            <form name="Role" action="{{url('Settings/ShiftPattern/Update/'.$data['id'])}}" method="post">
                                {{csrf_field()}}
                                <div class="row">

                                    @if(empty($logged_emp_com))
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="l30">Company Name</label>
                                            <select class="form-control" name="company_id">
                                                <option value="">Select Company</option>
                                                @if(isset($company))
                                                @foreach($company as $row)
                                                <option 
                                                    @if ($data['company_id'] == $row->id) 
                                                    selected="selected" 
                                                    @endif

                                                    value="{{$row->id}}">{{$row->name}}</option>
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
                                            <label for="l30">Shift Pattern Name</label>
                                            <input type="text" name="name" class="form-control" placeholder="Type Shift Name" value="{{$data['name']}}" id="l30" >
                                        </div>
                                    </div>



                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="l30"> Start In Time Pattern </label>
                                            <input type="text" name="start_in_time_pattern" class="form-control timepicker-init"  value="{{$data['start_in_time_pattern']}}" placeholder="Type Range Start Time" id="l30" >
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="l30"> End In Time Pattern </label>
                                            <input type="text" name="end_in_time_pattern" class="form-control timepicker-init"  value="{{$data['end_in_time_pattern']}}" placeholder="Type Range End Time" id="l30" >
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="l30"> Start Out Time Pattern </label>
                                            <input type="text" name="start_out_time_pattern" class="form-control timepicker-init"  value="{{$data['start_out_time_pattern']}}" placeholder="Type Range Start Time" id="l30" >
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="l30"> End Out Time Pattern </label>
                                            <input type="text" name="end_out_time_pattern" class="form-control timepicker-init"  value="{{$data['end_out_time_pattern']}}" placeholder="Type Range End Time" id="l30" >
                                        </div>
                                    </div>



                                    <div class="col-lg-3">                                       
                                        <div class="form-group">
                                            <label for="l30" class="col-md-12"> Pattern Match Shift </label>
                                            <select class="form-control" name="shift_id">
                                                <!--<option value="">Select Shift</option>-->
                                                @if(isset($shift))
                                                @foreach($shift as $row)
                                                <option <?php if ($data->shift_id == $row->id) { ?> selected="selected" <?php } ?> value="{{$row->id}}">{{$row->name}}</option>
                                                @endforeach
                                                @endif

                                            </select>
                                        </div>
                                    </div>




                                </div>


                                <div class="form-actions">
                                    <button type="submit"  class="btn btn-primary">Update</button>
                                    <button type="reset" class="btn btn-default">Cancel</button>
                                </div>
                            </form>
                            @else
                            <form name="Role" action="{{url('Settings/ShiftPattern/Add')}}" method="post">
                                {{csrf_field()}}
                                <div class="row">

                                    @if(empty($logged_emp_com))
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="l30" class="col-md-12">Company Name</label>
                                            <select class="form-control" name="company_id">
                                                <option value="">Select Company</option>
                                                @foreach($company as $row)
                                                <option value="{{$row->id}}">{{$row->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    @else
                                    <input type="hidden" name="company_id" value="{{$logged_emp_com}}" class="form-control" placeholder="Type Designation Title" id="l30">
                                    @endif

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="l30">Shift Pattern Name</label>
                                            <input type="text" name="name" class="form-control" placeholder="Type Shift Name" id="l30" >
                                        </div>
                                    </div>



                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="l30"> Start In Time Pattern </label>
                                            <input type="text" name="start_in_time_pattern" class="form-control timepicker-init" placeholder="Type Range Start Time" id="l30" >
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="l30"> End In Time Pattern </label>
                                            <input type="text" name="end_in_time_pattern" class="form-control timepicker-init" placeholder="Type Range End Time" id="l30" >
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="l30"> Start Out Time Pattern </label>
                                            <input type="text" name="start_out_time_pattern" class="form-control timepicker-init" placeholder="Type Range Start Time" id="l30" >
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="l30"> End Out Time Pattern </label>
                                            <input type="text" name="end_out_time_pattern" class="form-control timepicker-init" placeholder="Type Range End Time" id="l30" >
                                        </div>
                                    </div>



                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="l30" class="col-md-12"> Pattern Match Shift </label>
                                            <select class="form-control" name="shift_id">
                                                <option value="">Select Shift</option>
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

                    </div>
                </div>
            </section>


        </div>
    </div>

</div>

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

        $('.timepicker-init-withoutamp').datetimepicker({
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
            format: 'mm'
        });
    });


</script>
@endsection
@section('extraFooter')
@include('include.coreKendo')
@include('ajax_include.company_wise_shift')
<link rel="stylesheet" type="text/css" href="{{url('vendors/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css')}}">
<link rel="stylesheet" type="text/css" href="{{url('vendors/fullcalendar/dist/fullcalendar.min.css')}}">

<script src="{{url('vendors/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js')}}"></script>
<script src="{{url('vendors/fullcalendar/dist/fullcalendar.min.js')}}"></script>


@endsection