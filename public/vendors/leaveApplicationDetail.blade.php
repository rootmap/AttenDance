<?php
if(isset($data))
{
    $pageinfo=array("Leave Application Detail","Leave Application Detail","","SUL");
}
else
{
    $pageinfo=array("Leave Application Detail","Leave Application Detail","","SUL");
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
						<strong>{{$pageinfo[0]}}</strong>
						<!--<small class="text-muted">All cards are draggable and sortable!</small>-->
					</h5>
				</div>
				<div class="card-block">
					<div class="row">
            <!--Page Detail Content Starts Here-->
            @if(isset($data))
            @if(!empty($data))

                <div class="text-md-center text-center col-lg-2 col-md-2 col-xs-12 col-md-center ">
                  @foreach($data as $rows)
                  <input type="hidden" id="employee_code" name="employee_code" value="{{$rows->emp_code}}"/>
                  <input type="hidden" id="master_id" name="master_id" value="{{$rows->id}}"/>
                  @if(!empty($rows->emp_photo))
                  <div class="card pb-2 pt-2">
                    <img class="card-img-top" src="{{url('upload/$rows->emp_photo')}}" alt="employee_img">
                  </div>
                  <!-- <img class="img-responsive rounded border-1" src="{{url('upload/$row->emp_photo')}}" alt="employee_img" -->
                  style="max-width:190px;"><hr><br>
                  @else
                  <div class="card pb-2 pt-2">
                    <img class="card-img-top" src="{{url('uploads/person.png')}}" alt="employee_img">
                  </div>
                  <!-- <img class="img-responsive rounded border-1" src="{{url('uploads/person.png')}}" alt="employee_img"
                  style="max-width:190px;"><hr><br> -->
                  @endif
                  @endforeach

                  @if(!empty($appstatus))
                  @foreach($appstatus as $stat)
                  <button type="button" class="btn btn-success mr-2 mb-2" onclick="javascript:LeaveApprove({{$stat->master_id}});"><i class="fa fa-check-square-o"></i> Approve</button>
                  <button type="button" class="btn btn-danger mr-2 mb-2" onclick="javascript:rejectLeave({{$stat->master_id}});"><i class="fa fa-window-close-o"></i> Reject</button>
                  @endforeach
                  @endif
                </div>
                @foreach($data as $row)
                <div class="col-xs-center col-lg-5 col-md-5 col-xs-12">
                   <table class="table">
                    <thead>
                      <tr>
                        <th colspan="4" class="bg-faded">
                          <h5 class="font-weight-bold"><i class="fa fa-user-secret text-primary" aria-hidden="true"></i> {{$row->emp_name}}</h5>
                        </th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td colspan="2"><i class="fa fa-address-card text-primary" aria-hidden="true"></i> Designation: <span class="font-weight-bold">{{$row->emp_designation}}</span></td>
                        <td colspan="2"><i class="fa fa-address-book text-primary" aria-hidden="true"></i> Employee code: <span class="font-weight-bold">{{$row->emp_code}}</span></td>
                      </tr>
                      <tr>
                        <td colspan="2"><i class="fa fa-envelope text-primary" aria-hidden="true"></i> Email:  <span class="font-weight-bold">{{$row->emp_email}}</span></td>
                        <td colspan="2"><i class="fa fa-phone text-primary" aria-hidden="true"></i> Phone: <span class="font-weight-bold">{{$row->emp_phone}}</span></td>
                      </tr>
                      <tr>
                        <td colspan="2"><i class="fa fa-building text-primary" aria-hidden="true"></i> Company: <span class="font-weight-bold">{{$row->company_name}}</span></td>
                        <td colspan="2"><i class="fa fa-cube text-primary" aria-hidden="true"></i> Department: <span class="font-weight-bold">{{$row->emp_department}}</span></td>
                      </tr>
                      <tr>
                        <td colspan="2"><i class="fa fa-file text-primary" aria-hidden="true"></i> Leave Type: <span class="font-weight-bold">{{$row->leave_title}}</span></td>
                        <td colspan="2"><i class="fa fa-calendar-check-o text-primary" aria-hidden="true"></i> Total Day(s): <span class="font-weight-bold">{{$row->total_days_applied}}</span></td>
                      </tr>
                      <tr>
                        <td colspan="2"><i class="fa fa-calendar text-primary" aria-hidden="true"></i> Leave Start Date: <span class="font-weight-bold">{{$row->start_date}}</span></td>
                        <td colspan="2"><i class="fa fa-calendar text-primary" aria-hidden="true"></i> Leave End Date: <span class="font-weight-bold">{{$row->end_date}}</span></td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                @endforeach
                <!--Leave Application Workflow Chain Approval Status Starts Here-->
                <div class="col-xs-center col-lg-5 col-md-5 col-xs-12">
                  <table class="table">
                    <thead>
                      <tr>
                        <th colspan="4" class="bg-faded">
                          <h5 class="font-weight-bold"><i class="fa fa-hourglass-start text-primary" aria-hidden="true"></i> Leave Application Review Status:</h5>
                        </th>
                      </tr>
                      <tr>
                        <th>Review Step</th>
                        <th>Supervisor Name</th>
                        <th>Supervisor Code</th>
                        <th>Review Status</th>
                      </tr>
                    </thead>
                    <tbody>
                      @if(!empty($status))
                        @foreach($status as $rowst)
                        <tr>
                          <th>{{$rowst->step}}</th>
                          <td>{{$rowst->sup_emp_name}}</td>
                          <td>{{$rowst->sup_emp_code}}</td>
                          <td>{{$rowst->approval_status}}</td>
                        </tr>
                        @endforeach
                      @endif
                    </tbody>
                  </table>
                </div>
                <!--./ends here-->
                <div class="clearfix"></div>
                <div class="col-xl-12"><br><hr><br></div>


                <div class="col-xl-6">


                  <section class="card">
                    <div class="card-header bg-faded">
                        <span class="cat__core__title">
                            <h5 class="font-weight-bold"><i class="fa fa-info-circle text-primary" aria-hidden="true"></i> This Applicant's Leave Application Details:</h5>
                        </span>
                    </div>
                    <div class="card-block">
                      <!-- kendo table code start from here-->
                      <div class="row">
                          <!-- <div id="grid" class="col-md-12"></div> -->
                          <div id="example" class="k-content">
                            <div id="tabstrip">
                                <ul>
                                    <li class="k-state-active">
                                        Summary
                                    </li>
                                    <li>
                                        History
                                    </li>
                                </ul>

                                <div>
                                    <div class="weather">
                                        <div id="example">
                                            <div id="grid_summary"></div>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <div class="weather">
                                        <div id="example">
                                            <div id="grid_history"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--Application History and Summery-->
                      </div>
                    </div>
                </section>

                    <!--For Leave Application Approval Function-->
                    <script type="text/javascript">
                        function LeaveApprove(id) {
                          swal({
                                  title: "Are you sure?",
                                  text: "This Leave Application Will be Approved Right Now!",
                                  type: "warning",
                                  showCancelButton: true,
                                  confirmButtonClass: "btn-warning",
                                  confirmButtonText: "Yes, Approve it",
                                  cancelButtonText: "Cancel",
                                  closeOnConfirm: false,
                                  closeOnCancel: false
                              },
                              function(isConfirm) {
                                  if (isConfirm) {
                                    $.ajax({
                                        type: "POST",
                                        dataType: "json",
                                        url: "/Leave/LeaveApplication/Approve",
                                        data: {id: id,'_token':'<?=csrf_token()?>'},
                                        success: function (result) {
                                          swal({
                                              title: "Approved!",
                                              text: "You Have Successfully Approved This Leave Application",
                                              type: "success",
                                              confirmButtonClass: "btn-success"
                                          });
                                          setTimeout(function(){ window.location.reload(); }, 1000);

                                        }
                                      });
                                  } else {
                                      swal({
                                          title: "Cancelled",
                                          type: "error",
                                          confirmButtonClass: "btn-warning"
                                      });
                                  }
                              });
                            }

                    </script>
                    <!--Ends Here-->
                    <!--For Leave Application Rejection Function-->
                    <script type="text/javascript">
                        function rejectLeave(id) {
                          swal({
                                  title: "Are you sure?",
                                  text: "This Leave Application Will be Rejected Right Now!",
                                  type: "warning",
                                  showCancelButton: true,
                                  confirmButtonClass: "btn-warning",
                                  confirmButtonText: "Yes, Reject it",
                                  cancelButtonText: "Cancel",
                                  closeOnConfirm: false,
                                  closeOnCancel: false
                              },
                              function(isConfirm) {
                                  if (isConfirm) {
                                    $.ajax({
                                        type: "POST",
                                        dataType: "json",
                                        url: "/Leave/LeaveApplication/Reject",
                                        data: {id: id,'_token':'<?=csrf_token()?>'},
                                        success: function (result) {
                                          swal({
                                              title: "Rejected!",
                                              text: "You Have Successfully Rejected This Leave Application",
                                              type: "success",
                                              confirmButtonClass: "btn-success"
                                          });
                                          setTimeout(function(){ window.location.reload(); }, 1000);
                                        }
                                      });
                                  } else {
                                      swal({
                                          title: "Cancelled",
                                          type: "error",
                                          confirmButtonClass: "btn-warning"
                                      });
                                  }
                              });
                            }

                    </script>
                    <!--Ends Here-->


                    <script type="text/javascript">
                        $(document).ready(function () {
                            var emp_code = $("#employee_code").val();
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
                                            remaining_days:{type: "string"},
                                            incash_balance:{type: "string"}
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
                                  {field: "remaining_days", title: "Remaining Days", width: "80px", filterable: false},
                                  {field: "incash_balance", title: "Incash Balance", width: "80px", filterable: false}
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
                                          is_half_day:{type: "boolean"},
                                          half_day:{type: "string"},
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
                                  {field: "is_half_day", title: "Is Half Day Leave", width: "80px", filterable: false},
                                  {field: "half_day", title: "Day Part", width: "80px", filterable: false},
                                  {field: "leave_status", title: "Leave Status", width: "80px", filterable: false}
                                ],
                            });
                        });

                    </script>
                    <!-- kendo table code ends from here-->
                </div>

                <!--Leave Comment System Starts Here-->
                <div class="col-xl-6">
                  <row>
                    <section class="card" order-id="card-4">
                            <div class="card-header bg-faded">
                              <span class="cat__core__title">
                                <h5 class="mb-2">
                                    <strong><i class="fa fa-comments text-primary" aria-hidden="true"></i> Leave Comments</strong>
                                    <span class="text-inverse">(Comment On This Leave Application)</span>
                                </h5>
                              </span>
                            </div>
                            <div class="card-block">
                                <div class="cat__apps__messaging">
                                    <div class="height-400 custom-scroll cat__core__scrollable">
                                        <div id="leave_chat_block" class="cat__apps__chat-block">
                                          @if(!empty($leaveComments))
                                          @foreach($leaveComments as $comm)
                                            <div class="cat__apps__chat-block__item clearfix">
                                                <div class="cat__apps__chat-block__avatar">
                                                    <a class="cat__core__avatar" href="javascript:void(0);">
                                                      @if(!empty($comm->image))
                                                        <img src="{{url('upload/$comm->image')}}" alt="Image Not Found" />
                                                        @else
                                                        <img src="{{url('uploads/person.png')}}" alt="Image Not Found" />
                                                        @endif
                                                    </a>
                                                </div>
                                                <div class="cat__apps__chat-block__content">
                                                    <strong>{{$comm->sup_emp_name}}</strong>
                                                    <p>{{$comm->comment}}</p>
                                                </div>
                                            </div>
                                            @endforeach
                                          @endif
                                            <!-- <div class="cat__apps__chat-block__item cat__apps__chat-block__item--right clearfix">
                                                <div class="cat__apps__chat-block__avatar">
                                                    <a class="cat__core__avatar" href="javascript:void(0);">
                                                        <img src="../../modules/dummy-assets/common/img/avatars/4.jpg" alt="Alternative text to the image" />
                                                    </a>
                                                </div>
                                                <div class="cat__apps__chat-block__content">
                                                    <strong>Donald Trump</strong>
                                                    <p>Ok. Thanks!</p>
                                                </div>
                                            </div> -->
                                        </div>
                                    </div>

                                    <?php
                                     $logged_emp_code=MenuPageController::loggedUser('emp_code');
                                    ?>

                                    @if(!empty($suppervisor))

                                    @if($logged_emp_code==$suppervisor[0]->sup_emp_code)
                                    <div class="form-group mt-4 mb-0">
                                        <textarea id="comment_text" name="comment_text" class="form-control adjustable-textarea" placeholder="Type And Press Send"></textarea>
                                        <div class="mt-3">
                                            <button  onclick="javascript:commentOnLeave();" class="btn btn-sm btn-success width-100">
                                                <i class="fa fa-send mr-2"></i>
                                                Send
                                            </button>
                                        </div>
                                    </div>
                                    @endif
                                    @endif
                                    <!--For Leave Application Comments Function-->
                                    <script type="text/javascript">
                                        function commentOnLeave() {
                                              var comments = $("#comment_text").val();
                                              var master_id = $("#master_id").val();
                                              if(master_id==""){
                                                swal({
                                                    title: "Something Went Wrong !!!",
                                                    text: "Please Reload Your Page And Try Again. Thanks!",
                                                    type: "error",
                                                    confirmButtonClass: "btn-warning"
                                                });
                                              } else {
                                                if(comments==""){
                                                  swal({
                                                      title: "Empty Comment !!!",
                                                      text: "You Can Not Post Empty Comment",
                                                      type: "error",
                                                      confirmButtonClass: "btn-warning"
                                                  });
                                                } else {
                                                  $.post("<?=url('/Leave/LeaveApplication/Comment')?>",
                                                      {comments: comments,master_id: master_id,'_token':'<?=csrf_token()?>'},
                                                      function(data){
                                                      var total=data.length;
                                                      if(total!=0)
                                                      {
                                                        var strc='';
                                                        //'+link+''+imf+'
                                                        // var imf=val.image?val.image:'person.png';
                                                        // var link=val.image?'<?=url('upload/')?>':'<?=url('uploads/')?>';
                                                         $.each(data,function(index,val){
                                                             strc +='<div class="cat__apps__chat-block__item clearfix">'
                                                             strc +='<div class="cat__apps__chat-block__avatar">'
                                                             strc +='<a class="cat__core__avatar" href="javascript:void(0);">'
                                                             if(val.image!=""){
                                                               strc +='<img src="<?=url('upload/')?>'+val.image+'" alt="Image Not Found" />'
                                                             } else {
                                                               strc +='<img src="<?=url('uploads/person.png')?>" alt="Image Not Found" />'
                                                             }

                                                             strc +='</a>'
                                                             strc +='</div>'
                                                             strc +='<div class="cat__apps__chat-block__content">'
                                                             strc +='<strong>'+val.sup_emp_name+'</strong>'
                                                             strc +='<p>'+val.comment+'</p>'
                                                             strc +='</div>'
                                                             strc +='</div>';
                                                         });
                                                         swal({
                                                              title: "Posted!",
                                                              text: "You Have Successfully Posted A Comment On This Leave Application",
                                                              type: "success",
                                                              confirmButtonClass: "btn-success"
                                                          });
                                                          $('#leave_chat_block').html(strc);
                                                          $("#comment_text").val("");
                                                      }
                                                  });
                                                }
                                              }
                                            }

                                    </script>
                                    <!--Ends Here-->
                                </div>
                            </div>
                        </section>
                  </row>
                <div>
                <!--Leave Comment System Ends Here-->




            @endif
            @endif
            <!--Page Detail Content Ends Here-->
          </div>
        </div>
  </section>


  </div>
  </div>

  </div>
@endsection

@section('extraHeader')
<!-- <link rel="stylesheet" type="text/css" href="{{url('vendors/select2/dist/css/select2.min.css')}}"> -->
@endsection
@section('extraFooter')
<!-- <script src="{{url('vendors/select2/dist/js/select2.full.min.js')}}"></script> -->

@include('include.coreKendo')

@endsection
