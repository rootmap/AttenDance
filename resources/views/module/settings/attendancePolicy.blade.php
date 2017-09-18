<?php
if (isset($data)) {
    $pageinfo = array("Edit Attendance Policy Settings", "Edit Attendance Policy Record", "", "SUL");
} else {
    $pageinfo = array("Attendance Policy Settings", "Add Attendance Policy Record", "", "SUL");
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
                            <form enctype="multipart/form-data" name="Company" action="{{url('Settings/AttendancePolicy/Update/'.$data['id'])}}" method="post">
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

                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="l30">Policy Title<span class="after" style="color:#EF5F5F"> *</span></label>
                                            <input type="text" name="title" class="form-control required" value="{{$data->policy_title}}" placeholder="Type Policy Title" id="l30" required>
                                        </div>
                                    </div>

                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="l30">Office Start Time <span class="after" style="color:#EF5F5F"> *</span></label>
                                            <input type="text" name="start_time" id="start_time" class="form-control timepicker-init required" value="{{$data->office_start_time}}" placeholder="Type Office Start Time" required>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="l30">Office End Time <span class="after" style="color:#EF5F5F"> *</span></label>
                                            <input type="text" name="end_time" id="end_time" class="form-control timepicker-init required" value="{{$data->office_end_time}}" placeholder="Type Office End Time"  required>
                                        </div>
                                    </div>

                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="l30">Entry Buffer Time</label>
                                            <input type="text" name="entry_buffer_time" class="form-control timepicker-init-withoutamp" value="{{$data->entry_buffer_time}}" placeholder="Type Buffer Minute" id="l30">
                                        </div>
                                    </div>

                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="l30">Total Hours<span class="after" style="color:#EF5F5F"> *</span></label>
                                            <input type="text" name="total_hours" class="form-control required" value="{{$data->total_hours}}" placeholder="Type Weekend Day"  id="total_hours" required>
                                        </div>
                                    </div>

                                    <!--                                    <div class="col-lg-4">
                                                                            <div class="form-group">
                                    
                                                                                <label for="l30" class="col-md-12">Half Day Work</label>
                                                                                <select class="form-control" name="half_day_work">
                                                                                    <option value="">Select Day</option>
                                                                                    @if(isset($calendar))
                                                                                    @foreach($calendar as $row)
                                                                                    <option <?php if ($data->half_day_work == $row->name) { ?> selected="selected" <?php } ?> value="{{$row->name}}">{{$row->name}}</option>
                                                                                    @endforeach
                                                                                    @endif
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                         <div class="form-check col-lg-12">
                                                                            <div class="form-group">
                                                                                <input
                                                                                    @if($data->is_ot_applicable==1)
                                                                                    checked="checked"
                                                                                    @endif
                                                                                    type="checkbox" value="1" name="is_ot_applicable" id="is_ot_applicable"> Is OT Applicable</label>
                                                                            </div>
                                                                        </div>-->
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label><input
                                                    @if($data->is_halfday_applicable==1)
                                                    checked="checked"
                                                    @endif
                                                    type="checkbox" value="1" name="is_half_day_applicable"> Is Half Day Applicable</label>
                                            <!--<label> <input  type="checkbox" value="1" id="is_half_day_applicable" name="is_half_day_applicable"> Is Half Day Applicable</label>-->
                                        </div>
                                    </div>
                                    <div class="row col-lg-12">
                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <label for="l30" class="col-md-12">Half Day Work</label>
                                                <select class="form-control" name="half_day_work">
                                                    <option value="">Select Day</option>
                                                    @if(isset($calendar))
                                                    @foreach($calendar as $row)
                                                    <option <?php if ($data->half_day_name == $row->name) { ?> selected="selected" <?php } ?> value="{{$row->name}}">{{$row->name}}</option>
                                                    @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <label for="l30">Half Day Office End Time </label>
                                                <input value="{{$data->half_day_office_end_time}}" id="half_day_office_end_time" type="text" name="half_day_office_end_time" class="form-control timepicker-init" placeholder="Type Office End Time" id="l30" >
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <label for="l30">Half Day Total Working Hour </label>
                                                <input value="{{$data->half_day_total_working_hour}}" type="text" id="half_day_totla_working_hour" name="half_day_totla_working_hour" class="form-control" placeholder="Type Office End Time" id="l30" >
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-check col-lg-12">
                                        <div class="form-group">
                                            <label><input
                                                    @if($data->is_ot_applicable==1)
                                                    checked="checked"
                                                    @endif
                                                    type="checkbox" value="1" name="is_ot_applicable"> Is OT Applicable</label>
                                            <!--<label><input type="checkbox" value="1" id="is_ot_applicable" name="is_ot_applicable"> Is OT Applicable</label>-->
                                        </div>
                                    </div>
                                    <!--<div class="ot_show col-lg-12">-->
                                    <div class="col-lg-6 ">
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label><input
                                                        @if($data->is_ot_buffer_time==1)
                                                        checked="checked"
                                                        @endif
                                                        type="checkbox" value="1"  name="is_active_ot_buffer_time"> Is Active OT Buffer Time</label>
                                                    <!--<label><input type="checkbox" value="1" id="is_active_ot_buffer_time" name="is_active_ot_buffer_time"> Is Active OT Buffer Time</label>-->
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label for="l30">OT Buffer Time</label>
                                                <input value="{{$data->ot_buffer_time}}" type="text"  name="ot_buffer_min" class="form-control timepicker-init-withoutamp" placeholder="Type OT Buffer Minute" id="l30">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label><input
                                                        @if($data->is_ot_max_active==1)
                                                        checked="checked"
                                                        @endif
                                                        type="checkbox" value="1"  name="is_max_ot_applicable"> Is Max OT Active</label>
                                                    <!--<label><input type="checkbox" value="1" id="is_max_ot_applicable" name="is_max_ot_applicable"> Is Max OT Active</label>-->
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label for="l30">Max OT Hour</label>
                                                <input value="{{$data->max_ot_hour}}" type="text"  name="max_ot_hour" class="form-control timepicker-init-withoutamp" placeholder="Type OT Buffer Minute" id="l30">
                                            </div>
                                        </div>
                                    </div>
                                    <!--</div>-->

                                    <div class="form-check col-lg-12">
                                        <div class="form-group">
                                            <label><input
                                                    @if($data->is_active==1)
                                                    checked="checked"
                                                    @endif
                                                    type="checkbox" value="1" id="is_active" name="is_active"> Is Active Policy</label>
                                             <!--<label><input type="checkbox" value="1" id="is_active" name="is_active"> Is Active Policy</label>-->
                                        </div>
                                    </div>



                                </div>

                        </div>




                        <div class="form-actions">
                            <button type="submit"  class="btn btn-primary">Update</button>
                            <button type="reset" class="btn btn-default">Cancel</button>
                        </div>
                        </form>
                        @else
                        <form name="AttendancePolicy" enctype="multipart/form-data" action="{{url('Settings/AttendancePolicy/Add')}}" method="post">
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
                                </div>@else
                                <input type="hidden" name="company_id" value="{{$logged_emp_com}}" class="form-control" placeholder="Type Designation Title" id="l30">
                                @endif

                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="l30">Policy Title<span class="after" style="color:#EF5F5F"> *</span></label>
                                        <input value="{{ old('title') }}" type="text" name="title" class="form-control required" placeholder="Type Policy Title" id="l30" required>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="l30">Office Start Time <span class="after" style="color:#EF5F5F"> *</span></label>
                                        <input value="{{ old('start_time') }}" type="text" name="start_time" id="start_time" class="form-control timepicker-init required"  placeholder="Type Office Start Time" required>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="l30">Office End Time <span class="after" style="color:#EF5F5F"> *</span></label>
                                        <input value="{{ old('end_time') }}" type="text" name="end_time" id="end_time" class="form-control timepicker-init required" placeholder="Type Office End Time"  required>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="l30">Entry Buffer Time (in-Minute)</label>
                                        <input value="{{ old('entry_buffer_time') }}" type="text" name="entry_buffer_time" class="form-control timepicker-init-withoutamp" placeholder="Type Buffer Minute" id="l30">
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="l30">Total Hours<span class="after" style="color:#EF5F5F"> *</span></label>
                                        <input value="{{ old('total_hours') }}" type="text" name="total_hours" class="form-control required" placeholder="Type Weekend Day"  id="total_hours" required>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="form-group">

                                        <label>
                                            <input type="checkbox" value="1" id="is_half_day_applicable" name="is_half_day_applicable"> Is Half Day Applicable</label>
                                    </div>
                                </div>
                                <div class="row col-lg-12" id="halfday_show">
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="l30" class="col-md-12">Half Day Work</label>
                                            <select class="form-control" name="half_day_work">
                                                <option value="">Select Day</option>
                                                @if(isset($calendar))
                                                @foreach($calendar as $row)
                                                <option value="{{$row->name}}">{{$row->name}}</option>
                                                @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="l30">Half Day Office End Time </label>
                                            <input value="{{ old('half_day_office_end_time') }}" id="half_day_office_end_time" type="text" name="half_day_office_end_time" class="form-control timepicker-init" placeholder="Type Office End Time" id="l30" >
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="l30">Half Day Total Working Hour </label>
                                            <input value="{{ old('half_day_totla_working_hour') }}" type="text" id="half_day_totla_working_hour" name="half_day_totla_working_hour" class="form-control" placeholder="Type Office End Time" id="l30" >
                                        </div>
                                    </div>
                                </div>
                                <div class="form-check col-lg-12">
                                    <div class="form-group">
                                        <label>
                                            <input type="checkbox" value="1" id="is_ot_applicable" name="is_ot_applicable"> Is OT Applicable</label>
                                    </div>
                                </div>
                                <!--<div class="ot_show col-lg-12">-->
                                <div class="col-lg-6 ot_show">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>
                                                <input type="checkbox" value="1" id="is_active_ot_buffer_time" name="is_active_ot_buffer_time"> Is Active OT Buffer Time</label>
                                        </div>
                                    </div>
                                    <div class="col-lg-6" id="ot_buffer_min">
                                        <div class="form-group">
                                            <label for="l30">OT Buffer Time</label>
                                            <input value="{{ old('ot_buffer_min') }}" type="text"  name="ot_buffer_min" class="form-control timepicker-init-withoutamp" placeholder="Type OT Buffer Minute" id="l30">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6 ot_show">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>
                                                <input type="checkbox" value="1" id="is_max_ot_applicable" name="is_max_ot_applicable"> Is Max OT Active</label>
                                        </div>
                                    </div>
                                    <div class="col-lg-6" id="max_ot_hour">
                                        <div class="form-group">
                                            <label for="l30">Max OT Hour</label>
                                            <input value="{{ old('max_ot_hour') }}" type="text"  name="max_ot_hour" class="form-control max_ot_hour" placeholder="Type OT Buffer Minute" id="l30">
                                        </div>
                                    </div>
                                </div>
                                <!--</div>-->

                                <div class="form-check col-lg-12">
                                    <div class="form-group">

                                        <label>
                                            <input type="checkbox" value="1" id="is_active" name="is_active"> Is Active Policy</label>
                                    </div>
                                </div>
                            </div>
                    </div>




                    <div class="form-actions col-lg-12">
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
@endsection


@section('extraFooter')
@include('include.coreKendo')
<script src="{{url('vendors/jquery-validation/dist/jquery.validate.js')}}"></script>

<link rel="stylesheet" type="text/css" href="{{url('vendors/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css')}}">
<link rel="stylesheet" type="text/css" href="{{url('vendors/fullcalendar/dist/fullcalendar.min.css')}}">

<script src="{{url('vendors/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js')}}"></script>
<script src="{{url('vendors/fullcalendar/dist/fullcalendar.min.js')}}"></script>
<script>

var start;
var end;
var half_day_office_end_time;


$('#start_time').datetimepicker({
   format: 'HH:mm:ss', //use this format if you want the 12hours timpiecker with AM/PM toggle
    widgetPositioning: {
        vertical: 'bottom'
    },
    icons: {
        time: "fa fa-clock-o",
        date: "fa fa-calendar",
        up: "fa fa-chevron-up",
        down: "fa fa-chevron-down",
        previous: 'fa fa-chevron-left',
        next: 'fa fa-chevron-right',
        today: 'fa fa-screenshot',
        clear: 'fa fa-trash',
        close: 'fa fa-remove'
    }

}).on("dp.change", function () {
    start = $(this).val();
    halfDayTotalHour();
    TimeDiff();
});

$('#end_time').datetimepicker({
    format: 'HH:mm:ss', //use this format if you want the 12hours timpiecker with AM/PM toggle
            widgetPositioning: {
                vertical: 'bottom'
            },
    icons: {
        time: "fa fa-clock-o",
        date: "fa fa-calendar",
        up: "fa fa-chevron-up",
        down: "fa fa-chevron-down",
        previous: 'fa fa-chevron-left',
        next: 'fa fa-chevron-right',
        today: 'fa fa-screenshot',
        clear: 'fa fa-trash',
        close: 'fa fa-remove'
    }

}).on("dp.change", function () {
    end = $(this).val();
//console.log(end);
    TimeDiff()
});


$("#half_day_office_end_time").datetimepicker({
    format: 'HH:mm:ss', //use this format if you want the 12hours timpiecker with AM/PM toggle
            widgetPositioning: {
                vertical: 'bottom'
            },
    icons: {
        time: "fa fa-clock-o",
        date: "fa fa-calendar",
        up: "fa fa-chevron-up",
        down: "fa fa-chevron-down",
        previous: 'fa fa-chevron-left',
        next: 'fa fa-chevron-right',
        today: 'fa fa-screenshot',
        clear: 'fa fa-trash',
        close: 'fa fa-remove'
    }

}).on("dp.change", function () {
    half_day_office_end_time = $(this).val();
    halfDayTotalHour();
});


function  TimeDiff() {
    var startTime = moment(start, "HH:mm:ss a");
    var endTime = moment(end, "HH:mm:ss a");
    var duration = moment.duration(endTime.diff(startTime));
    var hours = parseInt(duration.asHours());
    if (hours < 0) {
        hours = 0;
        //console.log(hours)
    }
    var minutes = parseInt(duration.asMinutes()) - hours * 60;
    if (minutes < 0) {
        minutes = 0;
        // console.log(minutes)
    }
        if (isNaN(hours)) {
            if ($('#total_hours').val() > 0) {

            } else {
                document.getElementById("total_hours").value = 0;
            }
        } else {
            document.getElementById("total_hours").value = hours + ':' + minutes;

        }
    }

function  halfDayTotalHour() {
    var startTime = moment(start, "HH:mm:ss a");
    var half_day_end_time = moment(half_day_office_end_time, "HH:mm:ss a");
    var duration = moment.duration(half_day_end_time.diff(startTime));
    var hours = parseInt(duration.asHours());
    if (hours < 0) {
        hours = 0;
        //console.log(hours)
    }
    var minutes = parseInt(duration.asMinutes()) - hours * 60;
    if (minutes < 0) {
        minutes = 0;
        // console.log(minutes)
    }
    if (isNaN(hours)) {
        if ($('#half_day_totla_working_hour').val() > 0) {

        } else {
            document.getElementById("half_day_totla_working_hour").value = 0;
        }
    } else {
        document.getElementById("half_day_totla_working_hour").value = hours + ':' + minutes;
    }

}

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
    format: 'HH:mm:ss A'
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
    format: 'HH:mm:ss A'
});

$('.max_ot_hour').datetimepicker({
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
    format: 'HH:mm:ss A'
});
</script>

<script>
    $(document).ready(function () {
        $('.ot_show').hide('slow');
        $('#is_ot_applicable').change(function () {

            if (this.checked) {
                $('.ot_show').show('slow');
            } else {
                $('.ot_show').hide('slow');
            }
        });
        $('#ot_buffer_min').hide('slow');
        $('#is_active_ot_buffer_time').change(function () {

            if (this.checked) {
                $('#ot_buffer_min').show('slow');
            } else {
                $('#ot_buffer_min').hide('slow');
            }
        });
        $('#max_ot_hour').hide('slow');
        $('#is_max_ot_applicable').change(function () {

            if (this.checked) {
                $('#max_ot_hour').show('slow');
            } else {
                $('#max_ot_hour').hide('slow');
            }
        });
        $('#halfday_show').hide('slow');
        $('#is_half_day_applicable').change(function () {

            if (this.checked) {
                $('#halfday_show').show('slow');
            } else {
                $('#halfday_show').hide('slow');
            }
        });
    });
</script>
@endsection
