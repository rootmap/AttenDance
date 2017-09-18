<nav class="cat__menu-left">
    <div class="cat__menu-left__lock cat__menu-left__action--menu-toggle">
        <div class="cat__menu-left__pin-button">
            <div><!-- --></div>
        </div>
    </div>
    <div class="cat__menu-left__logo bg-inverse text-center">
        <a href="javascript:void(0);">
            <?php //$logged_emp_company_prefix=MenuPageController::loggedUser('company_prefix'); ?>
            <h3 class="font-weight-bold text-white" style="margin-top:.4rem !important;">SUL HRMS</h3>
        </a>
    </div>
    <?php
    $curpath = Request::path();
    //exit();
    ?>
    <?php
    $mod_id = 0;
    $menufixdata = MenuPageController::showSitePageByUrlCategory($curpath);
    //$mod_id=$menufixdata[0]->id;
    if (count($menufixdata) != 0) {
        $mod_id = $menufixdata[0]->system_module_id;
    }
    ?>

    <div class="cat__menu-left__inner">
        <ul class="cat__menu-left__list cat__menu-left__list--root">
            <a href="javascript:void(0);" class="hidden-sm-down">
                <?php $logged_emp_company_logo = MenuPageController::loggedUser('company_logo'); ?>
                <img src="{{url('upload/company_logo')}}/{{$logged_emp_company_logo}}"  class="img-responsive" style="max-height:90px; width:100%; margin-top:-10px;"/>
            </a>
            <hr/>
            @if(!empty(MenuPageController::showMenuSite($mod_id)) && $mod_id!=0)
            @foreach(MenuPageController::showMenuSite($mod_id) as $menu)
            <li class="cat__menu-left__item">
                <a href="javascript: void(0);">
                    <span class="cat__menu-left__icon {{$menu->icon}}"></span>
                    {{$menu->name}}
                </a>

                @if(!empty(MenuPageController::showSubMenuSite($menu->id)))
                <!-- level 1 -->
                <ul class="cat__menu-left__list">
                    @foreach(MenuPageController::showSubMenuSite($menu->id) as $sub_menu)
                    <li class="cat__menu-left__item cat__menu-left__submenu">
                        <a href="javascript: void(0);">
                            {{$sub_menu->name}}
                        </a>
                        @if(!empty(MenuPageController::showSitePage($menu->id,$sub_menu->id)))
                        <!-- level 2 -->
                        <ul class="cat__menu-left__list">
                            @foreach(MenuPageController::showSitePage($menu->id,$sub_menu->id) as $page)
                            <li class="cat__menu-left__item">
                                <a href="{{url($page->link)}}">
                                    {{$page->name}}
                                </a>
                            </li>
                            @endforeach
                        </ul>
                        @endif
                    </li>
                    @endforeach
                </ul>
                @endif


            </li>
			
            <li class="cat__menu-left__divider"><!-- --></li>
            @endforeach
            @endif
            @if($mod_id==0)
            <li class="cat__menu-left__item">
                <a href="{{url('Leave/LeaveApplication/ApplyForLeave')}}">
                    <span class="cat__menu-left__icon icmn-clipboard"></span>
                    Apply New Leave
                </a>
            </li>
            <li class="cat__menu-left__divider"><!-- --></li>
			<li class="cat__menu-left__item">
                <a href="{{url('Leave/LeaveApplication/LeaveApplicationList/Pending')}}">
                    <span class="cat__menu-left__icon icmn-clipboard"></span>
                    User HOD
                </a>
            </li>
            <li class="cat__menu-left__divider"><!-- --></li>
			<li class="cat__menu-left__item">
                <a href="javascript:void(0);">
                    <span class="cat__menu-left__icon icmn-file-text"></span>
                    Pay slip
                </a>
            </li>
            <li class="cat__menu-left__divider"><!-- --></li>
			<li class="cat__menu-left__item">
                <a href="{{url('Company-Policy')}}">
                    <span class="cat__menu-left__icon icmn-point-right"></span>
                    Company Policy
                </a>
            </li>
            <li class="cat__menu-left__divider"><!-- --></li>
			@elseif($mod_id==3)
            <li class="cat__menu-left__item">
                <a href="javascript:void(0);">
                    <span class="cat__menu-left__icon icmn-file-text"></span>
                    Pay slip
                </a>
            </li>
            <li class="cat__menu-left__divider"><!-- --></li>
			<li class="cat__menu-left__item">
                <a href="{{url('Company-Policy')}}">
                    <span class="cat__menu-left__icon icmn-point-right"></span>
                    Company Policy
                </a>
            </li>
            <li class="cat__menu-left__divider"><!-- --></li>
			@else
			<li class="cat__menu-left__item">
                <a href="{{url('Leave/LeaveApplication/ApplyForLeave')}}">
                    <span class="cat__menu-left__icon icmn-clipboard"></span>
                    Apply New Leave
                </a>
            </li>
            <li class="cat__menu-left__divider"><!-- --></li>	
			<li class="cat__menu-left__item">
                <a href="{{url('Leave/LeaveApplication/LeaveApplicationList/Pending')}}">
                    <span class="cat__menu-left__icon icmn-clipboard"></span>
                    User HOD
                </a>
            </li>
            <li class="cat__menu-left__divider"><!-- --></li>
			<li class="cat__menu-left__item">
                <a href="javascript:void(0);">
                    <span class="cat__menu-left__icon icmn-file-text"></span>
                    Pay slip
                </a>
            </li>
            <li class="cat__menu-left__divider"><!-- --></li>
			<li class="cat__menu-left__item">
				<a href="{{url('Company-Policy')}}">
					<span class="cat__menu-left__icon icmn-point-right"></span>
					Company Policy
				</a>
            </li>
            <li class="cat__menu-left__divider"><!-- --></li>
            @endif
			
			
			
			
			
        </ul>
    </div>
</nav>
