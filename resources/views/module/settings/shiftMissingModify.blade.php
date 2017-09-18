<?php
$pageinfo = array("Modify Shift Missing Detail", "Modify Shift Missing Detail", "", "SUL", "Modify Shift Missing Detail", "Save Changes");
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
                        <strong>{{$pageinfo[4]}}</strong>
                        <!--<small class="text-muted">All cards are draggable and sortable!</small>-->
                    </h5>
                </div>





                <div class="card-block">

                    <div class="row">
                        <div class="col-xl-12">
                            <!--Vertical Form Starts Here-->
                            <!-- kendo table code start from here-->

							<form action="{{url('Shift/missing/report/Update/'.$data->id)}}" name="dd" method="post">
								{{csrf_field()}}
                            <div class="row">
                                <!--Added For Leave User Data Filtering-->
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="l30">Employee Code</label>
                                        <input readonly type="text" name="emp_code"  class="form-control" placeholder="Type Employee Code" id="" value="{{$data->emp_code}}">
                                        <!-- <select class="form-control" name="emp_code">
                                            <option value="">Select Employee</option>
                                        </select> -->
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="l30">Start date</label>
                                        <label class="input-group datepicker-only-init">
                                            <input type="text" id="start_date" name="start_date" class="form-control" placeholder="Type Start Day"  value="{{$data->date}}"/>
                                            <span class="input-group-addon" style="">
                                                <i class="icmn-calendar"></i>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="l30">End date</label>
                                        <label class="input-group datepicker-only-init">
                                            <input type="text" id="end_date" name="end_date" class="form-control" placeholder="Type End Day" value="{{$data->date}}" />
                                            <span class="input-group-addon" style="">
                                                <i class="icmn-calendar"></i>
                                            </span>
                                        </label>
                                    </div>
                                </div>
								<div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="l30">Shift</label>
                                            <select id="shift" name="shift_id" class="form-control">
												<option value="0">Please Select Shift</option>
												@foreach($shift as $sh)
													<option 
													@if($sh->id==$data->shift_id)
														selected="selected" 
													@endif
													value="{{$sh->id}}">{{$sh->name}}</option>
												@endforeach
											</select>
                                    </div>
                                </div>

                            </div>
                            <div class="form-actions">
                                <button type="submit" name="filter"  class="btn btn-primary">{{$pageinfo[5]}}</button>
                            </div>
							
							
							</form>

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
<?= MenuPageController::genarateKendoDatePicker(array("start_date", "end_date")) ?>
@endsection
