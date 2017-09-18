<div id="cat__top-bar" class="cat__top-bar">
	<!-- Navigation starts here -->
	<nav class="navbar navbar-fixed-top navbar-toggleable-sm navbar-default" style="padding: 0rem!important;"><!--style="padding: 0rem 0rem !important;"-->
		<a href="javascript:void(0);" class="navbar-brand cat__top-bar__logo hidden-sm-down">
			<?php $logged_emp_company_logo=MenuPageController::loggedUser('company_logo'); ?>
			<img src="{{url('upload/company_logo')}}/{{$logged_emp_company_logo}}"  class="img-responsive"/>
			<span class="font-weight-bold text-primary text-right" style="line-height:1rem !important;">SUL HRMS</span>
		</a>

		<button id="topnavtoggler" class="custom-toggler navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#topnav" style="border: 1px solid #e4e9f0;">
			<!-- <span class="navbar-toggler-icon"></span> -->
			<span> </span>
			<span> </span>
			<span> </span>
		</button>

		<a href="javascript:void(0);"  class="navbar-brand cat__top-bar__logo visible-sm-down hidden-sm-up" style="margin-top: -4.5rem; margin-left: 4rem;">
			<?php $logged_emp_company_logo=MenuPageController::loggedUser('company_logo'); ?>
			<img src="{{url('upload/company_logo')}}/{{$logged_emp_company_logo}}"  class="img-responsive"/>
		</a>

        <div class="navbar-collapse collapse" id="topnav">
        	<ul class="navbar-nav nav__top-bar__left">
                @if(!empty(MenuPageController::showMenuSite()))
                    @foreach(MenuPageController::showMenuSite() as $menu)
                        <li class="nav-item dropdown cat__top-bar__item text-center">
                            <a class="dropdown-toggle" href="http://example.com" id="navbarDropdownMenuLink" data-toggle="dropdown" data-hover="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="{{$menu->icon}} clearfix"></i>
                                <span class="clearfix"><strong>{{$menu->name}}</strong></span>
                            </a>

                            @if(!empty(MenuPageController::showSubMenuSite($menu->id)))
                            <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                                @foreach(MenuPageController::showSubMenuSite($menu->id) as $sub_menu)
                                <div class="dropdown-header">{{$sub_menu->name}}</div>
                                @if(!empty(MenuPageController::showSitePage($menu->id,$sub_menu->id)))
                                    @foreach(MenuPageController::showSitePage($menu->id,$sub_menu->id) as $page)
                                        <a class="dropdown-item" href="{{url($page->link)}}">{{$page->name}} </a>
                                    @endforeach
                                @endif
                                @endforeach
                            </div>
                            @endif
                        </li>
                    @endforeach
                @endif



        		<li class="nav-item dropdown cat__top-bar__item text-center border__right_1">
							<?php 
							$emp_code=MenuPageController::loggedUser('emp_code'); 
							$emp_fname=MenuPageController::loggedUser('first_name'); 
							$emp_lname=MenuPageController::loggedUser('last_name');?>
        			<a  href="javascript:void(0);" id="navbarDropdownMenuLink"  >
        				<i class="icmn-user clearfix"></i>
								<span class="clearfix"><strong>User Logged : {{$emp_fname}}</strong></span>
        			</a>
        		</li>
        	</ul>
        	<ul class="navbar-nav ml-auto mt-3 nav__top-bar__right">
        		<li class="nav-item dropdown dropdown text-center cat__top-bar__avatar-dropdown">
        			<a class="dropdown-toggle" href="javascript:void(0);" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<?php $emp_photo=MenuPageController::loggedUser('image');?>
								<span class="cat__top-bar__avatar" href="javascript:void(0);">
									@if(empty($emp_photo))
        					<img src="{{url('modules/pages/common/img/login/default-avatar.png')}}"/>
									@else
									<img src="{{url('upload/employee_image')}}/{{$emp_photo}}"/>
									@endif
        				</span>

        			</a>
        			<div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownMenuLink">

        				<a class="dropdown-item" href="{{url('/Employee/Employeeinfo/ProfileDetail')}}/{{$emp_code}}"><i class="dropdown-icon icmn-cogs"></i> Profile</a>
        				<a class="dropdown-item" href="{{url('/Settings/User/ChangePassword')}}"><i class="dropdown-icon icmn-pencil"></i> Change Password</a>
        				<div class="dropdown-divider"></div>
        				<a class="dropdown-item" href="javascript:MakeSystemOut();">
        					<i class="dropdown-icon icmn-exit"></i>
        					Logout
        				</a>
								<script type="text/javascript">
									function MakeSystemOut()
									{
										$("#logoutSystem").click();
									}
								</script>
								<form action="{{url('/logout')}}" method="post" style="display:none !important;">
									<input type="hidden" name="_token" value="{{csrf_token()}}">
									<input type="submit" id="logoutSystem" name="logout" style="opacity: 0;">
								</form>

        			</div>
        		</li>
        	</ul>
        </div>
    </nav>
    <!-- ./ Navigation Ends Here -->
</div>
