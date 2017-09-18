<?php
$pageinfo=array("HRMS Reset Password","Reset Password Form","Please Provide Your Email","SUL");
?>
@extends('login_layout.master')
@section('content')

<div class="cat__pages__login__block cat__pages__login__block--extended">
    <div class="row mt-5 mb-5 pt-5">
        <div class="col-xl-12">
            <div class="cat__pages__login__block__inner mt-5 mb-5">
              <img src="{{url('modules/images/SUL_LOGO_Black_Update.png')}}" class="img-responsive" style="width: 180px; height: 53px; margin-left: 33.33%;"/>
              <hr/>
                <h4 class="text-uppercase">
                    <strong>{{$pageinfo[1]}}</strong>
                </h4>
                <small>Please Type Your New Password Twice and Click Reset</small>
                <form id="form-validation" name="form-validation" method="POST">
                    <div class="form-group">
                        <input id="validation-password"
                               class="form-control password"
                               name="validation[password]"
                               type="password" data-validation="[L>=6]"
                               data-validation-message="$ must be at least 6 characters"
                               placeholder="New Password">
                    </div>
                    <div class="form-group">
                        <input type="password" class="form-control" placeholder="Repeat New Password">
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-danger">Reset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
