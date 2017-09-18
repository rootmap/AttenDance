<?php
$pageinfo = array("Dashboard", "Dashboard", "", "SUL");
use Carbon\Carbon;
?>
@extends('layout.master')
@section('content')
@include('include.coreBarcum')
<div class="row">
	@if($logged_role_name=='General User')
	
	<div class="col-lg-6">
		<div class="cat__core__sortable" id="left-col">
		  @if(isset($all_basic_info))
			  @if(!empty($all_basic_info))
				<section class="card" order-id="card-1">
					<div class="card-header">
						<div class="pull-right cat__core__sortable__control">
							<i class="icmn-minus mr-2 cat__core__sortable__collapse" data-toggle="tooltip" data-placement="left" title="" data-original-title="Collapse"></i>
							<i class="icmn-plus mr-2 cat__core__sortable__uncollapse" data-toggle="tooltip" data-placement="left" title="" data-original-title="Uncollapse"></i>
							<i class="icmn-cross cat__core__sortable__close" data-toggle="tooltip" data-placement="left" title="" data-original-title="Remove"></i>
						</div>
						<h5 class="mb-0 text-black">
							<strong>Account Information</strong>
							<!--<small class="text-muted">All cards are draggable and sortable!</small>-->
						</h5>
					</div>
					
					<div class="card-block">
						@foreach($all_basic_info as $basic)
						<div class="row">
							<div class="col-xl-7">
								<div class="cat__core__widget cat__core__widget__2">
									<div class="cat__core__widget__2__head" style="background-image: url('modules/dummy-assets/common/img/photos/7.jpeg');">

									</div>
									<div class="cat__core__widget__2__content">
										<div class="cat__core__widget__2__left">
											<a class="cat__core__avatar cat__core__avatar--90 cat__core__avatar--border-white" href="javascript:void(0);">
											  @if(!empty($basic->emp_photo))
												<img src="{{url('upload/employee_image')}}/{{$basic->emp_photo}}" alt="No Image Found">
											  @else
											  <img src="{{url('uploads/person.png')}}" alt="No Image Found">
											  @endif
											</a>
											<br />
											<strong>{{$basic->emp_name}}</strong>
											<br />
											<span class="text-muted">{{$basic->emp_email}}</span>
										</div>
									</div>
								</div>
							</div>
							<div class="col-xl-5">
								<strong>Company:</strong>
								<p class="text-muted mb-3">{{$basic->emp_company}}</p>
								<strong>Designation:</strong>
								<p class="text-muted mb-3">{{$basic->emp_designation}}</p>
								<strong>Deapartment:</strong>
								<p class="text-muted mb-3">{{$basic->emp_department}}</p>
								<strong>Date Of Joining:</strong>
								<?php 
								$date1 = $basic->emp_join_date;
								$date2 = $basic->emp_birthdate;
								$join_date = date('jS F, Y', strtotime($date1));
								$birth_date = date('jS F, Y', strtotime($date2));
								?>
								<p class="text-muted mb-3">{{$join_date}}</p>
								<strong>Date Of Birth:</strong>
								<p class="text-muted mb-3">{{$birth_date}}</p>
							</div>
						</div>
						@endforeach
					</div>
				</section>
			  @endif
		  @endif
		  </div>
	</div>
	<div class="col-lg-6">
		<section class="card" order-id="card-3">
			<div class="card-header">
				<div class="pull-right cat__core__sortable__control">
					<i class="icmn-minus mr-2 cat__core__sortable__collapse" data-toggle="tooltip" data-placement="left" title="" data-original-title="Collapse"></i>
					<i class="icmn-plus mr-2 cat__core__sortable__uncollapse" data-toggle="tooltip" data-placement="left" title="" data-original-title="Uncollapse"></i>
					<i class="icmn-cross cat__core__sortable__close" data-toggle="tooltip" data-placement="left" title="" data-original-title="Remove"></i>
				</div>
				<h5 class="mb-0 text-black">
					<strong>Leave Summary</strong>
				</h5>
			</div>
			<div class="card-block">
				<div id="grid_summary"></div>
				<input type="hidden" id="emp_code" name="emp_code" value="{{$logged_emp_code}}"/>
			</div>
		</section>
		
		<script type="text/javascript">
                    //For Leave Summary and History

                        $(document).ready(function () {
                            var emp_code = $("#emp_code").val();
                            //alert(emp_code);
                            //for summary kendo grid
                            var dataSource = new kendo.data.DataSource({
                                transport: {
                                    read: {
                                        url: "<?=url('Leave/LeaveApplication/LeaveApplicationSummary/Json')?>/"+emp_code,
                                        type: "GET",
                                        datatype: "json"

                                    }
                                },
                                autoSync: false,
                                schema: {
                                    data: "data",
                                    total: "total",
                                    model: {
                                        id: "id",
                                        fields: {
                                            leave_title:{type: "string"},
                                            total_days:{type: "string"},
                                            availed_days:{type: "string"},
                                            remaining_days:{type: "string"}
                                        }
                                    }
                                },
                                pageSize: 10,
                                serverPaging: false,
                                serverFiltering: false,
                                serverSorting: false
                            });
                            $("#grid_summary").kendoGrid({
                                dataBound:gridDataBound,
                                dataSource: dataSource,
                                filterable: true,
                                pageable: {
                                    refresh: true,
                                    input: true,
                                    numeric: false,
                                    pageSizes: true,
                                    pageSizes:[10, 20, 50, 100, 200, 400]
                                },
                                sortable: true,
                                groupable: true,
                                columns: [
                                  {field: "leave_title", title: "Leave Title", width: "80px"},
                                  {field: "total_days", title: "Total Days", width: "80px", filterable: false},
                                  {field: "availed_days", title: "Availed Days", width: "80px", filterable: false},
                                  {field: "remaining_days", title: "Remaining Days", width: "80px", filterable: false}
                                ],
                            });
                        });

                    //Ends For Leave Summary and History
                    </script>
		
		
	</div>
	
	<div class="col-lg-12">
        <div class="cat__core__sortable">
			<section class="card" order-id="card-12">
                <div class="card-header">
                    <div class="pull-right cat__core__sortable__control">
                        <i class="icmn-minus mr-2 cat__core__sortable__collapse" data-toggle="tooltip" data-placement="left" title="" data-original-title="Collapse"></i>
                        <i class="icmn-plus mr-2 cat__core__sortable__uncollapse" data-toggle="tooltip" data-placement="left" title="" data-original-title="Uncollapse"></i>
                        <i class="icmn-cross cat__core__sortable__close" data-toggle="tooltip" data-placement="left" title="" data-original-title="Remove"></i>
                    </div>
                    <h5 class="mb-0 text-black">
                        <strong>Calendar</strong>
                    </h5>
                </div>
                <div class="card-block">
                    <div class="example-calendar-block"></div>
                </div>
            </section>
		</div>
	<div>	
	@else
    <div class="col-lg-6">
        <div class="cat__core__sortable" id="left-col">
          @if(isset($all_basic_info))
          @if(!empty($all_basic_info))
            <section class="card" order-id="card-1">
                <div class="card-header">
                    <div class="pull-right cat__core__sortable__control">
                        <i class="icmn-minus mr-2 cat__core__sortable__collapse" data-toggle="tooltip" data-placement="left" title="" data-original-title="Collapse"></i>
                        <i class="icmn-plus mr-2 cat__core__sortable__uncollapse" data-toggle="tooltip" data-placement="left" title="" data-original-title="Uncollapse"></i>
                        <i class="icmn-cross cat__core__sortable__close" data-toggle="tooltip" data-placement="left" title="" data-original-title="Remove"></i>
                    </div>
                    <h5 class="mb-0 text-black">
                        <strong>Account Information</strong>
                        <!--<small class="text-muted">All cards are draggable and sortable!</small>-->
                    </h5>
                </div>
                
                <div class="card-block">
                    @foreach($all_basic_info as $basic)
                    <div class="row">
                        <div class="col-xl-7">
                            <div class="cat__core__widget cat__core__widget__2">
                                <div class="cat__core__widget__2__head" style="background-image: url('modules/dummy-assets/common/img/photos/7.jpeg');">

                                </div>
                                <div class="cat__core__widget__2__content">
                                    <div class="cat__core__widget__2__left">
                                        <a class="cat__core__avatar cat__core__avatar--90 cat__core__avatar--border-white" href="javascript:void(0);">
                                          @if(!empty($basic->emp_photo))
                                            <img src="{{url('upload/employee_image')}}/{{$basic->emp_photo}}" alt="No Image Found">
                                          @else
                                          <img src="{{url('uploads/person.png')}}" alt="No Image Found">
                                          @endif
                                        </a>
                                        <br />
                                        <strong>{{$basic->emp_name}}</strong>
                                        <br />
                                        <span class="text-muted">{{$basic->emp_email}}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-5">
                            <strong>Company:</strong>
                            <p class="text-muted mb-3">{{$basic->emp_company}}</p>
                            <strong>Designation:</strong>
                            <p class="text-muted mb-3">{{$basic->emp_designation}}</p>
                            <strong>Deapartment:</strong>
                            <p class="text-muted mb-3">{{$basic->emp_department}}</p>
                            <strong>Date Of Joining:</strong>
                            <?php 
                            $date1 = $basic->emp_join_date;
                            $date2 = $basic->emp_birthdate;
                            $join_date = date('jS F, Y', strtotime($date1));
                            $birth_date = date('jS F, Y', strtotime($date2));
                            ?>
                            <p class="text-muted mb-3">{{$join_date}}</p>
                            <strong>Date Of Birth:</strong>
                            <p class="text-muted mb-3">{{$birth_date}}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>
          @endif
          @endif
            <section class="card" order-id="card-3">
                <div class="card-header">
                    <div class="pull-right cat__core__sortable__control">
                        <i class="icmn-minus mr-2 cat__core__sortable__collapse" data-toggle="tooltip" data-placement="left" title="" data-original-title="Collapse"></i>
                        <i class="icmn-plus mr-2 cat__core__sortable__uncollapse" data-toggle="tooltip" data-placement="left" title="" data-original-title="Uncollapse"></i>
                        <i class="icmn-cross cat__core__sortable__close" data-toggle="tooltip" data-placement="left" title="" data-original-title="Remove"></i>
                    </div>
                    <h5 class="mb-0 text-black">
                        <strong>Leave Summary</strong>
                    </h5>
                </div>
                <div class="card-block">
                    <div id="grid_summary"></div>
                    <input type="hidden" id="emp_code" name="emp_code" value="{{$logged_emp_code}}"/>
                </div>
            </section>
            <section class="card" order-id="card-3">
                <div class="card-header">
                    <div class="pull-right cat__core__sortable__control">
                        <i class="icmn-minus mr-2 cat__core__sortable__collapse" data-toggle="tooltip" data-placement="left" title="" data-original-title="Collapse"></i>
                        <i class="icmn-plus mr-2 cat__core__sortable__uncollapse" data-toggle="tooltip" data-placement="left" title="" data-original-title="Uncollapse"></i>
                        <i class="icmn-cross cat__core__sortable__close" data-toggle="tooltip" data-placement="left" title="" data-original-title="Remove"></i>
                    </div>
                    <h5 class="mb-0 text-black">
                        <strong>Leave History</strong>
                    </h5>
                </div>
                <div class="card-block">
                    <div id="grid_history"></div>
                    <script type="text/javascript">
                    //For Leave Summary and History

                        $(document).ready(function () {
                            var emp_code = $("#emp_code").val();
                            //alert(emp_code);
                            //for summary kendo grid
                            var dataSource = new kendo.data.DataSource({
                                transport: {
                                    read: {
                                        url: "<?=url('/')?>/Leave/LeaveApplication/LeaveApplicationSummary/Json/"+emp_code,
                                        type: "GET",
                                        datatype: "json"

                                    }
                                },
                                autoSync: false,
                                schema: {
                                    data: "data",
                                    total: "total",
                                    model: {
                                        id: "id",
                                        fields: {
                                            leave_title:{type: "string"},
                                            total_days:{type: "string"},
                                            availed_days:{type: "string"},
                                            remaining_days:{type: "string"}
                                        }
                                    }
                                },
                                pageSize: 10,
                                serverPaging: false,
                                serverFiltering: false,
                                serverSorting: false
                            });
                            $("#grid_summary").kendoGrid({
                                dataBound:gridDataBound,
                                dataSource: dataSource,
                                filterable: true,
                                pageable: {
                                    refresh: true,
                                    input: true,
                                    numeric: false,
                                    pageSizes: true,
                                    pageSizes:[10, 20, 50, 100, 200, 400]
                                },
                                sortable: true,
                                groupable: true,
                                columns: [
                                  {field: "leave_title", title: "Leave Title", width: "80px"},
                                  {field: "total_days", title: "Total Days", width: "80px", filterable: false},
                                  {field: "availed_days", title: "Availed Days", width: "80px", filterable: false},
                                  {field: "remaining_days", title: "Remaining Days", width: "80px", filterable: false}
                                ],
                            });

                            //For History Kendo grid
                            var dataSource2 = new kendo.data.DataSource({
                                transport: {
                                    read: {
                                        url: "<?=url('/')?>/Leave/LeaveApplication/LeaveApplicationHistory/Json/"+emp_code,
                                        type: "GET",
                                        datatype: "json"

                                    }
                                },
                                autoSync: false,
                                schema: {
                                    data: "data",
                                    total: "total",
                                    model: {
                                        id: "id",
                                        fields: {
                                          leave_title:{type: "string"},
                                          start_date:{type: "string"},
                                          end_date:{type: "string"},
                                          total_days_applied:{type: "string"},
                                          leave_status:{type: "string"}
                                        }
                                    }
                                },
                                pageSize: 10,
                                serverPaging: false,
                                serverFiltering: false,
                                serverSorting: false
                            });
                            $("#grid_history").kendoGrid({
                                dataBound:gridDataBound,
                                dataSource: dataSource2,
                                filterable: true,
                                pageable: {
                                    refresh: true,
                                    input: true,
                                    numeric: false,
                                    pageSizes: true,
                                    pageSizes:[10, 20, 50, 100, 200, 400]
                                },
                                sortable: true,
                                groupable: true,
                                columns: [
                                  {field: "leave_title", title: "Leave Title", width: "80px"},
                                  {field: "start_date", title: "Start Date", width: "80px"},
                                  {field: "end_date", title: "End Date", width: "80px"},
                                  {field: "total_days_applied", title: "Total Days", width: "80px", filterable: false},
                                  {field: "leave_status", title: "Leave Status", width: "80px", filterable: false}
                                ],
                            });
                        });

                    //Ends For Leave Summary and History
                    </script>
                </div>
            </section>
            <!-- <section class="card" order-id="card-4">
                <div class="card-header">
                    <div class="pull-right cat__core__sortable__control">
                        <i class="icmn-minus mr-2 cat__core__sortable__collapse" data-toggle="tooltip" data-placement="left" title="" data-original-title="Collapse"></i>
                        <i class="icmn-plus mr-2 cat__core__sortable__uncollapse" data-toggle="tooltip" data-placement="left" title="" data-original-title="Uncollapse"></i>
                        <i class="icmn-cross cat__core__sortable__close" data-toggle="tooltip" data-placement="left" title="" data-original-title="Remove"></i>
                    </div>
                    <h5 class="mb-0 text-black">
                        <strong>Live Chat</strong>
                        <span class="text-muted">(Send Message To HR)</span>
                    </h5>
                </div>
                <div class="card-block">
                    <div class="cat__apps__messaging">
                        <div class="height-400 custom-scroll cat__core__scrollable">
                            <div class="cat__apps__chat-block">
                                <div class="cat__apps__chat-block__item clearfix">
                                    <div class="cat__apps__chat-block__avatar">
                                        <a class="cat__core__avatar" href="javascript:void(0);">
                                            <img src="modules/dummy-assets/common/img/avatars/3.jpg" alt="Alternative text to the image" />
                                        </a>
                                    </div>
                                    <div class="cat__apps__chat-block__content">
                                        <strong>David Scott</strong>
                                        <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum is simply dummy text of the printing and typesetting industry</p>
                                    </div>
                                </div>
                                <div class="cat__apps__chat-block__item clearfix">
                                    <div class="cat__apps__chat-block__avatar">
                                        <a class="cat__core__avatar" href="javascript:void(0);">
                                            <img src="modules/dummy-assets/common/img/avatars/3.jpg" alt="Alternative text to the image" />
                                        </a>
                                    </div>
                                    <div class="cat__apps__chat-block__content">
                                        <strong>Chris Smith</strong>
                                        <p>Lorem Ipsum is simply dummy text of the printing</p>
                                        <img class="cat__apps__profile__wall__message-img" src="modules/dummy-assets/common/img/photos/4.jpeg" />
                                        <img class="cat__apps__profile__wall__message-img" src="modules/dummy-assets/common/img/photos/3.jpeg" />
                                    </div>
                                </div>
                                <div class="cat__apps__chat-block__item cat__apps__chat-block__item--right clearfix">
                                    <div class="cat__apps__chat-block__avatar">
                                        <a class="cat__core__avatar" href="javascript:void(0);">
                                            <img src="modules/dummy-assets/common/img/avatars/4.jpg" alt="Alternative text to the image" />
                                        </a>
                                    </div>
                                    <div class="cat__apps__chat-block__content">
                                        <strong>Donald Trump</strong>
                                        <p>Ok. Thanks!</p>
                                    </div>
                                </div>
                                <div class="cat__apps__chat-block__item cat__apps__chat-block__item--right clearfix">
                                    <div class="cat__apps__chat-block__avatar">
                                        <a class="cat__core__avatar" href="javascript:void(0);">
                                            <img src="modules/dummy-assets/common/img/avatars/4.jpg" alt="Alternative text to the image" />
                                        </a>
                                    </div>
                                    <div class="cat__apps__chat-block__content">
                                        <strong>Donald Trump</strong>
                                        <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum is simply dummy</p>
                                    </div>
                                </div>
                                <div class="cat__apps__chat-block__item clearfix">
                                    <div class="cat__apps__chat-block__avatar">
                                        <a class="cat__core__avatar" href="javascript:void(0);">
                                            <img src="modules/dummy-assets/common/img/avatars/3.jpg" alt="Alternative text to the image" />
                                        </a>
                                    </div>
                                    <div class="cat__apps__chat-block__content">
                                        <strong>David Scott</strong>
                                        <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum is simply dummy text of the printing and typesetting industry</p>
                                    </div>
                                </div>
                                <div class="cat__apps__chat-block__item cat__apps__chat-block__item--right clearfix">
                                    <div class="cat__apps__chat-block__avatar">
                                        <a class="cat__core__avatar" href="javascript:void(0);">
                                            <img src="modules/dummy-assets/common/img/avatars/4.jpg" alt="Alternative text to the image" />
                                        </a>
                                    </div>
                                    <div class="cat__apps__chat-block__content">
                                        <strong>Donald Trump</strong>
                                        <p>Ok. Thanks!</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group mt-4 mb-0">
                            <textarea class="form-control adjustable-textarea" placeholder="Type and press enter"></textarea>
                            <div class="mt-3">
                                <button class="btn btn-sm btn-primary width-100">
                                    <i class="fa fa-send mr-2"></i>
                                    Send
                                </button>
                                <button class="btn btn-link">
                                    Attach File
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section> -->
        </div>
    </div>
    <div class="col-lg-6">
        <div class="cat__core__sortable" id="right-col">
            <section class="card" order-id="card-6">
                <div class="card-header">
                    <div class="pull-right cat__core__sortable__control">
                        <i class="icmn-minus mr-2 cat__core__sortable__collapse" data-toggle="tooltip" data-placement="left" title="" data-original-title="Collapse"></i>
                        <i class="icmn-plus mr-2 cat__core__sortable__uncollapse" data-toggle="tooltip" data-placement="left" title="" data-original-title="Uncollapse"></i>
                        <i class="icmn-cross cat__core__sortable__close" data-toggle="tooltip" data-placement="left" title="" data-original-title="Remove"></i>
                    </div>
                    <h5 class="mb-0 mr-3 d-inline-block text-black">
                        <strong>Report Quick Links</strong>
                        <!--<small class="text-muted">(All Notifications)</small>-->
                    </h5>
                </div>
                <div class="card-block">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="cat__core__widget cat__core__widget__3 bg-default">
                                <div class="carousel slide" id="carousel-1" data-ride="carousel">
                                    <ol class="carousel-indicators">
                                        <li data-target="#carousel-1" data-slide-to="0" class="active"></li>
                                        <li data-target="#carousel-1" data-slide-to="1" class=""></li>
                                    </ol>
                                    <div class="carousel-inner" role="listbox">
                                        <div class="carousel-item active">
                                            <a href="{{url('AdditionalOTReport')}}" class="cat__core__widget__3__body text-white">
                                                <div class="cat__core__widget__3__icon">
                                                    <i class="icmn-accessibility"></i>
                                                </div>
                                                <h2>Additional OT Report</h2>
                                                <p>View Report</p>
                                            </a>
                                        </div>
                                        <div class="carousel-item">
                                            <a href="{{url('AttendanceReport')}}" class="cat__core__widget__3__body text-white">
                                                <div class="cat__core__widget__3__icon">
                                                    <i class="icmn-download"></i>
                                                </div>
                                                <h2>Attendance Report</h2>
                                                <p>View Report</p>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="cat__core__widget cat__core__widget__3 bg-default">
                                <div class="carousel slide" id="carousel-2" data-ride="carousel">
                                    <ol class="carousel-indicators">
                                        <li data-target="#carousel-2" data-slide-to="0" class="active"></li>
                                        <li data-target="#carousel-2" data-slide-to="1" class=""></li>
                                    </ol>
                                    <div class="carousel-inner" role="listbox">
                                        <div class="carousel-item active">
                                            <a href="{{url('OTReport')}}" class="cat__core__widget__3__body text-white">
                                                <div class="cat__core__widget__3__icon">
                                                    <i class="icmn-books"></i>
                                                </div>
                                                <h2>OT Report</h2>
                                                <p>View Report</p>
                                            </a>
                                        </div>
                                        <div class="carousel-item">
                                            <a href="{{url('AdditionalOTSummary')}}" class="cat__core__widget__3__body text-white">
                                                <div class="cat__core__widget__3__icon">
                                                    <i class="icmn-download"></i>
                                                </div>
                                                <h2>Additional OT Summary</h2>
                                                <p>View Summary</p>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="cat__core__widget cat__core__widget__3 bg-default">
                                <div class="carousel slide" id="carousel-1" data-ride="carousel">
                                    <ol class="carousel-indicators">
                                        <li data-target="#carousel-1" data-slide-to="0" class="active"></li>
                                        <li data-target="#carousel-1" data-slide-to="1" class=""></li>
                                    </ol>
                                    <div class="carousel-inner" role="listbox">
                                        <div class="carousel-item active">
                                            <a href="{{url('AttendanceSummary')}}" class="cat__core__widget__3__body text-white">
                                                <div class="cat__core__widget__3__icon">
                                                    <i class="icmn-accessibility"></i>
                                                </div>
                                                <h2>Attendance Summary</h2>
                                                <p>View Summary</p>
                                            </a>
                                        </div>
                                        <div class="carousel-item">
                                            <a href="{{url('OTSummary')}}" class="cat__core__widget__3__body text-white">
                                                <div class="cat__core__widget__3__icon">
                                                    <i class="icmn-download"></i>
                                                </div>
                                                <h2>OT Summary</h2>
                                                <p>View Summary</p>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="cat__core__widget cat__core__widget__3 bg-default">
                                <div class="carousel slide" id="carousel-2" data-ride="carousel">
                                    <ol class="carousel-indicators">
                                        <li data-target="#carousel-2" data-slide-to="0" class="active"></li>
                                        <li data-target="#carousel-2" data-slide-to="1" class=""></li>
                                    </ol>
                                    <div class="carousel-inner" role="listbox">
                                        <div class="carousel-item active">
                                            <a href="{{url('Jobcard/User')}}" class="cat__core__widget__3__body text-white">
                                                <div class="cat__core__widget__3__icon">
                                                    <i class="icmn-books"></i>
                                                </div>
                                                <h2>User Jobcard Report</h2>
                                                <p>View Report</p>
                                            </a>
                                        </div>
                                        <div class="carousel-item">
                                            <a href="{{url('Leave/LeaveApplication/LeaveApplicationList/Pending')}}" class="cat__core__widget__3__body text-white">
                                                <div class="cat__core__widget__3__icon">
                                                    <i class="icmn-download"></i>
                                                </div>
                                                <h2>Leave Approval Pending</h2>
                                                <p>View List</p>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="cat__core__margin-fix"><!-- --></div>

                </div>
            </section>


            <section class="card" order-id="card-9">
                <div class="card-header">
                    <div class="pull-right cat__core__sortable__control">
                        <i class="icmn-minus mr-2 cat__core__sortable__collapse" data-toggle="tooltip" data-placement="left" title="" data-original-title="Collapse"></i>
                        <i class="icmn-cross cat__core__sortable__close" data-toggle="tooltip" data-placement="left" title="" data-original-title="Remove"></i>
                    </div>
                    <h5 class="mb-0 text-black">
                        <strong>System Information</strong>
                    </h5>
                </div>
                <div class="card-block">
                    <div class="row">
                        <div class="col-xl-6">
                            <div class="cat__core__step cat__core__step--success mb-4">
                                <span class="cat__core__step__digit">
                                    <i class="icmn-database"><!-- --></i>
                                </span>
                                <div class="cat__core__step__desc">
                                    <span class="cat__core__step__title">Employees</span>
                                    <p>Total: <span id="total_employee"></span></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6">
                            <div class="cat__core__step cat__core__step--primary mb-4">
                                <span class="cat__core__step__digit">
                                    <i class="icmn-users"><!-- --></i>
                                </span>
                                <div class="cat__core__step__desc">
                                    <span class="cat__core__step__title">Users</span>
                                    <p>Total: <span id="total_user"></span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xl-6">
                            <div class="cat__core__step cat__core__step--danger mb-4">
                                <span class="cat__core__step__digit">
                                    <i class="icmn-bullhorn"><!-- --></i>
                                </span>
                                <div class="cat__core__step__desc">
                                    <span class="cat__core__step__title">Leave</span>
                                    <p>Applications: <span id="total_leave"></span></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6">
							<a href="{{url('Shift/missing/report')}}">
                            <div class="cat__core__step cat__core__step--default mb-4">
                                <span class="cat__core__step__digit">
                                    <i class="icmn-price-tags"><!-- --></i>
                                </span>
                                <div class="cat__core__step__desc">
                                    <span class="cat__core__step__title">Shift Missing</span>
                                    <p>Log Pending: <span id="shift_missing_log_pending"></span></p>
                                </div>
                            </div>
							</a>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xl-6">
                            <div class="cat__core__step cat__core__step--secondary mb-4">
                                <span class="cat__core__step__digit">
                                    <i class="icmn-price-tags"><!-- --></i>
                                </span>
                                <div class="cat__core__step__desc">
                                    <span class="cat__core__step__title">Log</span>
                                    <p>Total: <span id="total_log"></span></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6">
                            <div class="cat__core__step cat__core__step--warning mb-4">
                                <span class="cat__core__step__digit">
                                    <i class="icmn-bullhorn"><!-- --></i>
                                </span>
                                <div class="cat__core__step__desc">
                                    <span class="cat__core__step__title">Pending</span>
                                    <p style="font-size: 1rem !important">Process: <span id="pending_processed_data"></span></p>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="cat__core__margin-fix"><!-- --></div>
                </div>
            </section>

            <section class="card" order-id="card-12">
                <div class="card-header">
                    <div class="pull-right cat__core__sortable__control">
                        <i class="icmn-minus mr-2 cat__core__sortable__collapse" data-toggle="tooltip" data-placement="left" title="" data-original-title="Collapse"></i>
                        <i class="icmn-plus mr-2 cat__core__sortable__uncollapse" data-toggle="tooltip" data-placement="left" title="" data-original-title="Uncollapse"></i>
                        <i class="icmn-cross cat__core__sortable__close" data-toggle="tooltip" data-placement="left" title="" data-original-title="Remove"></i>
                    </div>
                    <h5 class="mb-0 text-black">
                        <strong>Calendar</strong>
                    </h5>
                </div>
                <div class="card-block">
                    <div class="example-calendar-block"></div>
                </div>
            </section>
        </div>
    </div>

	
	@endif
</div>

@endsection

@section('extraFooter')
@include('include.coreKendo')
<style type="text/css" media="screen">
	span.k-edit,span.k-delete{
		margin-top: -3px !important;
	}

	span.k-i-arrow-s{
		margin-top: -5px !important;
	}
</style>
<link rel="stylesheet" type="text/css" href="{{url('vendors/fullcalendar/dist/fullcalendar.min.css')}}">
<script src="{{url('vendors/fullcalendar/dist/fullcalendar.min.js')}}"></script>
<script type="text/javascript">
$(document).ready(function () {
    //get current date
    var today = new Date();
    var dd = today.getDate();
    var mm = today.getMonth()+1; //January is 0!

    var yyyy = today.getFullYear();
    if(dd<10){
        dd='0'+dd;
    }
    if(mm<10){
        mm='0'+mm;
    }
    var today = yyyy+'-'+mm+'-'+dd;
    ///////////////////////////////////////////////////////////
    // calendar
    $('.example-calendar-block').fullCalendar({
        aspectRatio: 2,
        height: '100%',
        header: {
            left: 'prev, next',
            center: 'title',
            right: 'month, agendaWeek, agendaDay'
        },
        buttonIcons: {
            prev: 'none fa fa-arrow-left',
            next: 'none fa fa-arrow-right',
            prevYear: 'none fa fa-arrow-left',
            nextYear: 'none fa fa-arrow-right'
        },
        editable: true,
        eventLimit: true, // allow "more" link when too many events
        viewRender: function (view, element) {
            if (!('ontouchstart' in document.documentElement) && jQuery().jScrollPane) {
                $('.fc-scroller').jScrollPane({
                    autoReinitialise: true,
                    autoReinitialiseDelay: 100
                });
            }
        },
        defaultDate: today,
        events: [
			<?php  
			$i=0;
			$html='';
			foreach($holiday_data as $row):
				$day_note=$row->day_note;
				if(empty($day_note))
				{
					$day_note='Weekend';
				}
				else
				{
					$day_note=$day_note;
				}
				$row_date=$row->date;
				if($i!=0)
				{
					$html .=",{ title: '".$day_note."',start: '".$row_date."',end: '".$row_date."',className: 'fc-state-active'	}";
				}
				else
				{
					$html .="{ title: '".$day_note."',start: '".$row_date."',end: '".$row_date."',className: 'fc-state-active'	}";
				}
				
			$i++;	
			endforeach;
			echo $html;
			?>
        ],
        eventClick: function (calEvent, jsEvent, view) {
            if (!$(this).hasClass('event-clicked')) {
                $('.fc-event').removeClass('event-clicked');
                $(this).addClass('event-clicked');
            }
        }
    });
	
	$(".fc-scroller .fc-day-grid-container").css('overflow-y','none');
	

});
</script>

<script>
    $(function () {

        var processLogUrl = "<?= url('AttendanceLogProcessCheck') ?>";
        setInterval(function () {
            $.ajax({
                method: "GET",
                url: processLogUrl
            })
            .done(function (data) {
                //console.log(data);
                $("#pending_processed_data").html(data);
            });


        }, 2000);
         var user = "<?= url('TotalUser') ?>";
        $.ajax({
                method: "GET",
                url: user
            })
            .done(function (data) {
                //console.log(data);
                $("#total_user").html(data);
            });

             var user = "<?= url('TotalUser') ?>";
        $.ajax({
                method: "GET",
                url: user
            })
            .done(function (data) {
                //console.log(data);
                $("#total_user").html(data);
            });

             var user = "<?= url('TotalEmployee') ?>";
        $.ajax({
                method: "GET",
                url: user
            })
            .done(function (data) {
                //console.log(data);
                $("#total_employee").html(data);
            });

             var user = "<?= url('TotalLeave') ?>";
        $.ajax({
                method: "GET",
                url: user
            })
            .done(function (data) {
                //console.log(data);
                $("#total_leave").html(data);
            });

             var user = "<?= url('Shift/missing/Log/Pending') ?>";
        $.ajax({
                method: "GET",
                url: user
            })
            .done(function (data) {
                //console.log(data);
                $("#shift_missing_log_pending").html(data);
            });

             var user = "<?= url('TotalLog') ?>";
        $.ajax({
                method: "GET",
                url: user
            })
            .done(function (data) {
                //console.log(data);
                $("#total_log").html(data);
            });





    });
</script>
@endsection
