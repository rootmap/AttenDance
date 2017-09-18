<?php
if (isset($data)) {
    $pageinfo = array("Forgot Password", "Forgot Password Data", "", "SUL","Filter list","Filtered Report");
} else {
    $pageinfo = array("Forgot Password", "Forgot Password", "", "SUL","Forgot Password","Filtered Report");
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
                        <strong>{{$pageinfo[4]}}</strong>
                        <!--<small class="text-muted">All cards are draggable and sortable!</small>-->
                    </h5>
                </div>





                <div class="card-block">

                    <div class="row">
                        <div class="col-xl-12">
                            <!--Vertical Form Starts Here-->
                            <!-- kendo table code start from here-->


                            <div class="row">
                                <!--Added For Leave User Data Filtering-->
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="l30">Email</label>
                                        <input type="email" name="email"  class="form-control" placeholder="Your email" id="">
                                    </div>
                                </div>

                                <div class="col-lg-8">
                                    <div class="form-group">
                                        <label for="l30">Message (Optional)</label>
                                        <!-- <input type="" name="confirmPass"  class="form-control" placeholder="Please confirm your password" id=""> -->
                                        <textarea rows="4" cols="50" class="form-control" placeholder="write yiur message"> </textarea>
                                    </div>
                                </div>


                            </div>
                            <div class="form-actions">
                                <button type="button" name="filter"  class="btn btn-primary">Reset</button>
                            </div>

                            <!-- kendo table code end fro here-->
                            <!--Vertical Form Ends Here-->
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>


<div class="row">
    <div class="col-lg-12">
        <div class="cat__core__sortable" id="left-col">
            <section class="card" order-id="card-1">








                <div class="card-block">

                    <div class="row">
                        <div class="col-xl-12">

                            <script type="text/javascript">

                                $(document).ready(function () {

                                    $("button[name='filter']").click(function(){
                                        //  alert('success');
                                        // var company_id=$("select[name='company_id']").val();
                                        var newpass =$("input[name='newPass']").val();
                                        var confirmPass =$("input[name='confirmPass']").val();
                                        alert(newpass);

                                        // var param={'employee_code':employee_code,
                                        //            'start_date':s_date,
                                        //            'end_date':e_date,
                                        //            '_token':'<?=csrf_token()?>'};
                                        // var link="Filter/LeaveUserData/List";
                                        //
                                        // KendoManualInitialized(link,param);

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
<?=MenuPageController::genarateKendoDatePicker(array("starts","ends"))?>
@include('ajax_include.company_wise_department')
@include('ajax_include.department_wise_section')
@include('ajax_include.section_wise_designation')
@include('ajax_include.company_department_section_designation_wise_employee')
@endsection
