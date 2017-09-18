<?php
if (isset($data)) {
    $pageinfo = array("Change Password", "Change Password Data", "", "SUL","Filter list","Filtered Report");
} else {
    $pageinfo = array("Change Password", "Reset Password", "", "SUL","Change Password","Filtered Report");
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
                          <form name="ChangePassword" id="ChangePassword" action="{{url('/Settings/User/ChangePassword/Save')}}" method="post" enctype="multipart/form-data">
                            {{csrf_field()}}
                            <!--Vertical Form Starts Here-->
                            <!-- kendo table code start from here-->
                            <div class="row">
                              <?php $logged_user_id=MenuPageController::loggedUser('user_id'); ?>

                                <input type="hidden" name="user_id" id="user_id" value="{{$logged_user_id}}">
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="l30">New Password</label>
                                        <input type="password" name="new_pass"  class="form-control" placeholder="Type New Password" value="{{ old('new_pass') }}"/>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="l30">Confirm Password</label>
                                        <input type="password" name="confirm_pass"  class="form-control" placeholder="Please confirm your password" value="{{ old('confirm_pass') }}"/>
                                    </div>
                                </div>


                            </div>
                            <div class="form-actions">
                                <button type="submit" name="changepass"  class="btn btn-primary">Change Password</button>
                            </div>

                            <!-- kendo table code end fro here-->
                            <!--Vertical Form Ends Here-->
                          </form>
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
