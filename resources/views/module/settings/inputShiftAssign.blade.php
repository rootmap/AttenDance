<?php
if (isset($data)) {
    $pageinfo = array("Edit Uploaded File", "Edit Uploaded File Record", "", "SUL");
} else {
    $pageinfo = array("Import Assign Employee to Shift  File ", "Import Assign Employee to Shift  File", "", "SUL");
}
?>
@extends('layout.master')
@section('content')
@include('include.coreBarcum')
<div class="row">
    <div class="col-lg-12">
        <div class="cat__core__sortable" id="left-col">
            <section class="card" order-id="card-1">

                <div class="card-block">
                    <div class="row">
                        <div class="col-xl-12">
                            <!--Vertical Form Starts Here-->

                            <form name="InputShiftAssign" enctype="multipart/form-data"  action="{{url('Settings/InputShiftAssign/Add')}}" method="post">
                                {{csrf_field()}}
                                <div class="row">
                                        <label for="l30">Please Browse & Select Your Attendance File</label>
                                        <div class="col-lg-12">
                                            <input name="request_file" type="file" class="dropify" data-height="300" />
                                        </div>
										<div class=" col-lg-6">
											<div class="form-group">
												<label for="l30">Select Company</label>
												<select class="form-control" id="ExCompany" name="company_id">
													<option value="">Select Company</option>
													@foreach($company as $row)
													<option value="{{$row->id}}">{{$row->name}}</option>
													@endforeach
												</select>
											</div>
										</div>
										<div class="col-lg-6">
                                                    <label for="l30">Start Day<span class="after" style="color:#EF5F5F">*</span></label>
                                                    <label class="input-group datepicker-only-init">
                                                        <input type="text" name="fstart_date" class="form-control required"   placeholder="Type Start Day"/>
                                                        <span class="input-group-addon" style="">
                                                            <i class="icmn-calendar"></i>
                                                        </span>
                                                    </label>
                                                </div>
                                                <div class="col-lg-6">
                                                    <label for="l30">End Day<span class="after" style="color:#EF5F5F">*</span></label>
                                                    <label class="input-group datepicker-only-init">
                                                        <input type="text" name="fend_date" class="form-control required"  placeholder="Type End Day"/>
                                                        <span class="input-group-addon" style="">
                                                            <i class="icmn-calendar"></i>
                                                        </span>
                                                    </label>
                                                </div>

                                                <div class="col-lg-6">
                                                    <div class="form-group">
                                                        <label for="l30">Shift</label>
                                                        <select class="form-control" id="shift_id" name="shift_id">
                                                            <option value="">Select Shift</option>
															@foreach($shift as $row)
																<option value="{{$row->id}}">{{$row->name}}</option>
															@endforeach
                                                        </select>
                                                    </div>
                                                </div>
										
                                </div>


                                <div class="form-actions">
                                    <button type="submit"  class="btn btn-primary">Upload Employee File</button>
                                    <button type="reset" class="btn btn-default">Cancel</button>
                                </div>
                            </form>

                            <!--Vertical Form Ends Here-->
                        </div>

                        </section>


                    </div>
                </div>

        </div>
        @endsection
        @section('extraFooter')
<?= MenuPageController::genarateKendoDatePicker(array("fstart_date", "start_date", "fend_date", "end_date")) ?>
        @include('include.coreKendo')
        <link rel="stylesheet" type="text/css" href="{{url('vendors/dropify/dist/css/dropify.min.css')}}">
        <script src="{{url('vendors/dropify/dist/js/dropify.min.js')}}"></script>
        <script>
$(function () {


    $('.dropify').dropify();




});
        </script>
        @endsection