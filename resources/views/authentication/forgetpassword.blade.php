<?php
$pageinfo=array("HRMS Forget Password","Forget Password Form","Please Provide Your Email","SUL");
?>
@extends('login_layout.master')
@section('content')

<div class="cat__pages__login__block cat__pages__login__block--extended">
    <div class="row mt-5 mb-5 pt-5 pb-5">
        <div class="col-xl-12">
            <div class="cat__pages__login__block__inner mt-5 mb-5">
                <form id="form-validation" name="form-validation" method="POST">
                  <img src="{{url('modules/images/SUL_LOGO_Black_Update.png')}}" class="img-responsive" style="width: 180px; height: 53px; margin-left: 33.33%;"/>
                  <hr/>
                    <h2 class="text-center">
                        <strong>{{$pageinfo[1]}}</strong>
                    </h2>
                    <br />
                    <div class="form-group">
                      <code id="emailHelp" class="text-danger">Please Write Email or Username Here.</code>
                        <input id="validation-email"
                                   class="form-control mt-1"
                                   placeholder="Email or Username"
                                   name="validation[email]"
                                   type="text"
                                   data-validation="[EMAIL]">

                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-success">Recover</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
@endsection
