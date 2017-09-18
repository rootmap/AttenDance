<!DOCTYPE html>
<html>
    <head lang="en">
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        @include('login_include.header')
    </head>
    <body>
        <!-- START: pages/login-beta -->
        <div class="cat__pages__login" style="background-image: url({{url('modules/pages/common/img/login/4.jpg')}}); background-size: cover; background-position: center center; width: 100% !important; height: 100% !important; position: absolute; top: 0;
left: 0; overflow: hidden !important;">
            @yield('content')
            <!-- <div class="cat__footer" style="/*position: absolute; bottom: 0; left: 0;*/ /*width: 100% !important; border-top-left-radius: 0px; border-top-right-radius: 0px;*/">
            <div class="row">
                <div class="col-md-4">
                    <strong>
                        <a href="http://systechunimax.com/" target="_blank" class="text-primary">Systech Unimax Ltd.</a> Copyright © 2017. All rights reserved.
                    </strong>
                </div>
                <div class="col-md-4 text-center">
                    <strong>SUL - HRMS (Current Version - 2.0.0)</strong>
                </div>
                <div class="col-md-4">
                    <a href="http://systechunimax.com/" target="_blank" class="cat__footer__company">
                        <img class="img-responsive" src="{{url('modules/images/sul-f-logo-BLACK.png')}}" title="Systech Unimax Ltd.">
                    </a>
                </div>
            </div>
        </div> -->
        </div>
        <!-- END: pages/login-beta -->

        <!-- START: page scripts -->
        <script>
            $(function () {

                // Form Validation
                $('#form-validation').validate({
                    submit: {
                        settings: {
                            inputContainer: '.form-group',
                            errorListClass: 'form-control-error',
                            errorClass: 'has-danger'
                        }
                    }
                });

                // Show/Hide Password
                $('.password').password({
                    eyeClass: '',
                    eyeOpenClass: 'icmn-eye',
                    eyeCloseClass: 'icmn-eye-blocked'
                });

                // Switch to fullscreen
                $('.switch-to-fullscreen').on('click', function () {
                    $('.cat__pages__login').toggleClass('cat__pages__login--fullscreen');
                })

                // Change BG
                $('.random-bg-image').on('click', function () {
                    var min = 1, max = 5,
                            next = Math.floor($('.random-bg-image').data('img')) + 1,
                            final = next > max ? min : next;

                    $('.random-bg-image').data('img', final);
                    $('.cat__pages__login').data('img', final).css('backgroundImage', 'url(<?=url('modules/pages/common/img/login/')?>' + final + '.jpg)');
                })

            });
        </script>
        <!-- END: page scripts -->

    </body>
</html>
