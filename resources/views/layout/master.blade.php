<?php 
$newUrl=Request::path();
echo MenuPageController::userModulePagePermissionCheck($newUrl);
?>
<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    
    @yield('extraHeader')
    
    @include('include.coreCssHeader')
    <!-- VENDORS -->
    <!-- v2.0.0 -->


</head>
<body class="cat__config--vertical cat__menu-left--colorful cat__menu-left--visible cat__theme--light">
    @include('include.coreSideLeftMenu')
    @include('include.coreModuleMenu')
    <div id="cat__content" class="cat__content">
        <!-- START: dashboard alpha -->
        @yield('content')
        <!-- END: dashboard alpha -->
        <!-- END: page scripts -->
        @include('include.copyright')
    </div>
    @include('include.coreJsFooter')
    @yield('extraFooter')
</body>
</html>