<?php
if (isset($data)) {
    $pageinfo = array("Edit Shift Settings", "Edit Shift  Record", "", "SUL");
} else {
    $pageinfo = array("Add New Shift Settings", "Add New Shift Record", "", "SUL");
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
                            <form name="Role" action="{{url('Settings/Shift/Update/'.$data['id'])}}" method="post">
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
                                            <label for="l30">Shift Name</label>
                                            <input type="text" name="name" class="form-control" placeholder="Type Shift Name" value="{{$data['name']}}" id="l30" >
                                        </div>
                                    </div>



                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="l30"> Start Time </label>
                                            <input type="text" name="shift_start_time" class="form-control timepicker-init" value="{{$data['shift_start_time']}}" placeholder="Type Shift Name" id="l30" >
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="l30"> Start Buffer Time (in-Minute) </label>
                                            <input type="text" name="shift_start_buffer_time" class="form-control timepicker-init-withoutamp" placeholder="Type Shift Name"  value="{{$data['shift_start_buffer_time']}}"  id="l30" >
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="l30"> End Time </label>
                                            <input type="text"   value="{{$data['shift_end_time']}}"  name="shift_end_time" class="form-control timepicker-init" placeholder="Type Shift Name" id="l30" >
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="l30"> End Buffer Time (in-Minute) </label>
                                            <input  type="text"   value="{{$data['shift_end_buffer_time']}}"  name="shift_end_buffer_time" class="form-control timepicker-init-withoutamp" placeholder="Type Shift Name" id="l30" >
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="l30" class="col-md-12" style="margin-top: 40px;">
                                                <input
                                                    @if($data['is_night_shift']==1)
                                                    checked="checked"
                                                    @endif
                                                    type="checkbox" name="is_night_shift" value="1">
                                                    Is Night Shift
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="l30" class="col-md-12" style="margin-top: 40px;">
                                                <input
                                                    @if($data['is_roster_shift']==1)
                                                    checked="checked"
                                                    @endif
                                                    type="checkbox" name="is_roster_shift" value="1">
                                                    Is Roster Shift
                                            </label>
                                        </div>
                                    </div>




                                </div>


                                <div class="form-actions">
                                    <button type="submit"  class="btn btn-primary">Update</button>
                                    <button type="reset" class="btn btn-default">Cancel</button>
                                </div>
                            </form>
                            @else
                            <form name="Role" action="{{url('Settings/Shift/Add')}}" method="post">
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
                                            <label for="l30">Shift Name</label>
                                            <input value="{{ old('name') }}" type="text" name="name" class="form-control" placeholder="Type Shift Name" id="l30" >
                                        </div>
                                    </div>



                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="l30"> Start Time </label>
                                            <input value="{{ old('shift_start_time') }}" type="text" name="shift_start_time" class="form-control timepicker-init" placeholder="Type Shift Name" id="l30" >
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="l30"> Start Buffer Time (in-Minute) </label>
                                            <input value="{{ old('shift_start_buffer_time') }}" type="text" name="shift_start_buffer_time" class="form-control timepicker-init-withoutamp" placeholder="Type Shift Name" id="l30" >
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="l30"> End Time </label>
                                            <input value="{{ old('shift_end_time') }}" type="text" name="shift_end_time" class="form-control timepicker-init" placeholder="Type Shift Name" id="l30" >
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="l30"> End Buffer Time (in-Minute) </label>
                                            <input value="{{ old('shift_end_buffer_time') }}"  type="text" name="shift_end_buffer_time" class="form-control timepicker-init-withoutamp" placeholder="Type Shift Name" id="l30" >
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="l30" class="col-md-12" style="margin-top: 40px;">
                                                <input type="checkbox" name="is_night_shift" value="1">
                                                Is Night Shift
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="l30" class="col-md-12" style="margin-top: 40px;">
                                                <input type="checkbox" name="is_roster_shift" value="1">
                                                Is Roster Shift
                                            </label>
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
            format: 'HH:mm:ss'
        });
    });


</script>
@endsection
@section('extraFooter')
@include('include.coreKendo')
<link rel="stylesheet" type="text/css" href="{{url('vendors/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css')}}">
<link rel="stylesheet" type="text/css" href="{{url('vendors/fullcalendar/dist/fullcalendar.min.css')}}">

<script src="{{url('vendors/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js')}}"></script>
<script src="{{url('vendors/fullcalendar/dist/fullcalendar.min.js')}}"></script>


@endsection
