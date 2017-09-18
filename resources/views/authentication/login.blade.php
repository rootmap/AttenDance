<?php
$pageinfo=array("HRMS Login","Employee Login","Please Provide Your Credential","SUL");
?>
@extends('login_layout.master')
@section('content')

<div class="cat__pages__login__block cat__pages__login__block--extended">
    <div class="row pb-5">
        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="cat__pages__login__block__inner mb-5">
                <div class="cat__pages__login__block__form">
                    <?php
                    $logged_emp_company_logo=MenuPageController::loggedUser('company_logo'); ?>
                    @if(isset($logged_emp_company_logo))
                      <img src="{{url('upload/company_logo')}}/{{$logged_emp_company_logo}}"  class="img-responsive" style="width: 180px; height: 53px; margin-left: 22%;"/>
                    @else
                      <img src="{{url('modules/images/SUL_LOGO_Black_Update.png')}}" class="img-responsive" style="width: 180px; height: 53px; margin-left: 22%;"/>
                    @endif
                    <hr/>
                    <h4 class="text-uppercase mt-4">
                        <strong>{{$pageinfo[1]}}</strong>
                    </h4>
					@include('include.msg')
                    <br />
                    <form id="form-validation" action="{{ url('/login') }}" name="form-validation" method="POST">
                        {{csrf_field()}}
                        <div class="form-group">
                            <label class="form-label">Employee Code</label>
                            <input id="validation-text"
                            class="form-control"
                            placeholder="Employee Code"
                            name="username"
                            type="text"
                            data-validation="[text]">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Password</label>
                            <input id="validation-password"
                            class="form-control password"
                            name="password"
                            type="password" data-validation="[L>=6]"
                            data-validation-message="$ must be at least 6 characters"
                            placeholder="Password">
                        </div>
                        <div class="form-group">
                            <a href="javascript: void(0);" class="pull-right cat__core__link--blue cat__core__link--underlined">Forgot Password?</a>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="example6" checked>
                                    Remember me
                                </label>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary mr-3">Sign In</button>
                        </div>

                    </form>
                </div>
                <div class="cat__pages__login__block__sidebar">
                    <h4 class="cat__pages__login__block__sidebar__title">
                        <strong>How To Log In?</strong>
                    </h4>
                    <div class="cat__pages__login__block__sidebar__item text-justify">
                        If you are already registered in this system by administrator, please type your employee code and password in the respective fields to log in
                    </div>
                    <div class="cat__pages__login__block__sidebar__item text-justify">
                        Or, contact with system administrator for user registration.
                    </div>
                    <div class="cat__pages__login__block__sidebar__place">
                      <div class="form-group">
                        <strong>

                          <p>
                            <script type="text/javascript">
                            // $.ajax({ url:'http://maps.googleapis.com/maps/api/geocode/json?latlng=40.714224,-73.961452&sensor=true',
                            //        success: function(data){
                            //            alert(data.results[0].formatted_address);
                            //            //or you could iterate the components for only the city and state
                            //        }
                            //   });
                            </script>
                          </p>
                            <p><?php $mytime = Carbon\Carbon::now(new DateTimeZone('Asia/Dhaka'));
                                    $mytime->toDateTimeString();
                                    echo $mytime->format('l, jS F, Y h:i:s A');?></p><br/>
                            <a href="http://systechunimax.com/" target="_blank" class="text-white">SUL</a> HRMS (V2.0.0)
                        </strong>
                      </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extraFooter')

@endsection
