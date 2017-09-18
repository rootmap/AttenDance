<?php
if(isset($allbasicinfo))
{
    $pageinfo=array("Employee Profile Detail","Employee Profile Information","","SUL");
}
else
{
    $pageinfo=array("Employee Profile Detail","Employee Profile Information","","SUL");
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
          <div class="pull-right cat__core__sortable__control">

              <a href="javascript:void(0);" class="fa fa-print fa-2x avoid-this" id="print-btn" onclick="javascript:printout();"  title="Print" data-original-title="Print">&nbsp;</a>
              <a href="javascript:void(0);" class="fa fa-file-pdf-o fa-2x avoid-this" id="export_pdf"  title="Export Pdf" data-original-title="Export Pdf">&nbsp;</a>
          </div>
					<h5 class="mb-0 text-black">
						<strong>{{$pageinfo[0]}}</strong>
						<!--<small class="text-muted">All cards are draggable and sortable!</small>-->
					</h5>
				</div>
				<div class="card-block">
					<div class="row"  id="output">
            <!--Page Detail Content Starts Here-->
            @if(isset($allbasicinfo))
            @if(!empty($allbasicinfo))
            <div class="col-xl-6">
                <section class="card">
                  <div class="card-header bg-faded">
                      <span class="cat__core__title">
                          <h5 class="font-weight-bold"><i class="fa fa-user-secret text-primary" aria-hidden="true"></i> Basic Information</h5>
                          <input type="hidden" id="emp_prof_code" value="{{$allbasicinfo[0]->emp_code}}" />
                      </span>
                  </div>
                  <div class="card-block">
                    <div class="row">
                      <div class="col-xs-center col-lg-12 col-md-12 col-xs-12">
                         <table class="table">
                          <tbody>
                            <tr>
                              <td rowspan="5" width="300px;">
                                @if(!empty($allbasicinfo[0]->emp_photo))
                                <div class="card pb-2 pt-2">
                                  <img style="max-width:250px; margin:0 auto;" class="card-img-top pb-2" src="{{url('upload/employee_image')}}/{{$allbasicinfo[0]->emp_photo}}" alt="employee_img">
                                  <div class="card-footer">
                                      <h5 class="font-weight-bold text-primary text-uppercase"> {{$allbasicinfo[0]->emp_name}}</h5>
                                  </div>
                                </div>
                                @else
                                <div class="card pb-2 pt-2">
                                  <img style="max-height:300px; max-width:250px; margin:0 auto;" class="card-img-top pb-2" src="{{url('uploads/person.png')}}" alt="employee_img">
                                  <div class="card-footer">
                                      <h5 class="font-weight-bold text-primary text-uppercase"> {{$allbasicinfo[0]->emp_name}}</h5>
                                  </div>
                                </div>
                                @endif
                              </td>
                              <td>
                                <i class="fa fa-address-card text-primary" aria-hidden="true"></i>
                                Employee code:
                              </td>
                              <td>
                                <span class="font-weight-bold">{{$allbasicinfo[0]->emp_code}}</span>
                              </td>

                            </tr>
                            <tr>
                              <td>
                                <i class="fa fa-calendar text-primary" aria-hidden="true"></i>
                                Date Of Birth:
                              </td>
                              <td>
                                  <?php 
                                    $date2 = $allbasicinfo[0]->emp_birthdate;
                                    $birth_date = date('jS F, Y', strtotime($date2));
                                    ?>
                                <span class="font-weight-bold">{{$birth_date}}</span>
                              </td>
                            </tr>
                            <tr>
                              <td>
                                <i class="fa fa-tint text-primary" aria-hidden="true"></i>
                                Blood Group:
                              </td>
                              <td>
                                <span class="font-weight-bold">{{$allbasicinfo[0]->emp_blood_group}}</span>
                              </td>
                            </tr>
                            <tr>
                              <td>
                                <i class="fa fa-male text-primary" aria-hidden="true"></i>/<i class="fa fa-female text-primary" aria-hidden="true"></i>
                                Gender:
                              </td>
                              <td>
                                <span class="font-weight-bold">{{$allbasicinfo[0]->emp_gender}}</span>
                              </td>
                            </tr>
                            <tr>
                              <td>
                                <i class="fa fa-star text-primary" aria-hidden="true"></i>
                                Marital Status:
                              </td>
                              <td>
                                <span class="font-weight-bold">{{$allbasicinfo[0]->emp_marital_status}}</span>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>
                </section>
              </div>

              <!--Starts another section of information-->
              <div class="col-xl-6">
                  <section class="card">
                    <div class="card-header bg-faded">
                        <span class="cat__core__title">
                            <h5 class="font-weight-bold"><i class="fa fa-address-card text-primary" aria-hidden="true"></i> Contact Information</h5>
                        </span>
                    </div>
                    <div class="card-block">
                      <div class="row">
                        <div class="col-xs-center col-lg-12 col-md-12 col-xs-12">
                           <table class="table">
                            <tbody>
                              <tr>
                                <td class="pt-4 pb-4">
                                  <i class="fa fa-envelope text-primary" aria-hidden="true"></i>
                                  Email:
                                </td>
                                <td class="pt-4 pb-4">
                                  <span class="font-weight-bold">{{$allbasicinfo[0]->emp_email}}</span>
                                </td>
                              </tr>
                              <tr>
                                <td class="pt-4 pb-4">
                                  <i class="fa fa-phone text-primary" aria-hidden="true"></i>
                                  Phone:
                                </td>
                                <td class="pt-4 pb-4">
                                  <span class="font-weight-bold">{{$allbasicinfo[0]->emp_phone}}</span>
                                </td>
                              </tr>
                              <tr>
                                <td class="pt-4 pb-4">
                                  <i class="fa fa-map-marker text-primary" aria-hidden="true"></i>
                                  Address:
                                </td>
                                <td class="pt-4 pb-4">
                                  <span class="font-weight-bold">{{$allbasicinfo[0]->emp_address}}</span>
                                </td>
                              </tr>
                              <tr>
                                <td class="pt-4 pb-4">
                                  <i class="fa fa-thumb-tack text-primary" aria-hidden="true"></i>
                                  City:
                                </td>
                                <td class="pt-4 pb-4">
                                  <span class="font-weight-bold">{{$allbasicinfo[0]->emp_city}}</span>
                                </td>
                              </tr>
                              <tr>
                                <td class="pt-4 pb-4">
                                  <i class="fa fa-globe text-primary" aria-hidden="true"></i>
                                  Country:
                                </td>
                                <td class="pt-4 pb-4">
                                  <span class="font-weight-bold">{{$allbasicinfo[0]->emp_country}}</span>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </div>
                  </section>
                </div>
              <!--Ends another section of information-->



              <!--Starts another section of information-->
                <div class="col-xl-12">
                    <section class="card">
                      <div class="card-header bg-faded">
                          <span class="cat__core__title">
                              <h5 class="font-weight-bold"><i class="fa fa-briefcase text-primary" aria-hidden="true"></i> Job Detail</h5>
                          </span>
                      </div>
                      <div class="card-block">
                        <div class="row">
                          <div class="col-xs-center col-lg-12 col-md-12 col-xs-12">
                             <table class="table">
                              <tbody>
                                <tr>
                                  <td>
                                    <i class="fa fa-address-book text-primary" aria-hidden="true"></i>
                                    Designation:
                                  </td>
                                  <td>
                                    <span class="font-weight-bold">{{$allbasicinfo[0]->emp_designation}}</span>
                                  </td>
                                <td>
                                    <i class="fa fa-cubes text-primary" aria-hidden="true"></i>
                                    Department:
                                  </td>
                                  <td>
                                    <span class="font-weight-bold">{{$allbasicinfo[0]->emp_department}}</span>
                                  </td>
                                </tr>
                                <tr>
                                  <td>
                                    <i class="fa fa-cube text-primary" aria-hidden="true"></i>
                                    Section:
                                  </td>
                                  <td>
                                    <span class="font-weight-bold">{{$allbasicinfo[0]->emp_section}}</span>
                                  </td>
                                <td>
                                    <i class="fa fa-map-marker text-primary" aria-hidden="true"></i>
                                    Job Location:
                                  </td>
                                  <td>
                                    <span class="font-weight-bold">{{$allbasicinfo[0]->emp_job_location}}</span>
                                  </td>
                                </tr>
                                <tr>
                                  <td>
                                    <i class="fa fa-building text-primary" aria-hidden="true"></i>
                                    Company:
                                  </td>
                                  <td>
                                    <span class="font-weight-bold">{{$allbasicinfo[0]->emp_company}}</span>
                                  </td>
                                  <td>
                                    <i class="fa fa-user text-primary" aria-hidden="true"></i>
                                    Supervisor:
                                  </td>
                                  <td>
                                    <span class="font-weight-bold">{{$allbasicinfo[0]->emp_supervisor_name}}</span>
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          </div>
                        </div>
                      </div>
                    </section>
                  </div>



                    <div class="clearfix"></div>

                <!--Ends another section of information-->

                <!--starts education info-->
                @if(!empty($eduinfo))
                <div class="col-xl-12">
                  <section class="card">
                    <div class="card-header bg-faded">
                        <span class="cat__core__title">
                            <h5 class="font-weight-bold"><i class="fa fa-graduation-cap text-primary" aria-hidden="true"></i> Educational Qualification:</h5>
                        </span>
                    </div>
                    <div class="card-block">
                      <div class="row">
                        <!--Table Starts Here-->
                          <table class="table">
                           <thead>
                             <tr class="bg-faded">
                               <th>Certification Name</th>
                               <th>Institute</th>
                               <th>Istitute Address</th>
                               <th>Result</th>
                               <th>From</th>
                               <th>To</th>
                               <th>Certificate</th>
                             </tr>
                           </thead>
                           <tbody>
                             @foreach($eduinfo as $edu)
                             <tr>
                               <td>{{$edu->certification_name}}</td>
                               <td>{{$edu->institute}}</td>
                               <td>{{$edu->institute_add}}</td>
                               <td>{{$edu->result}}</td>
                               <td>{{$edu->start_date}}</td>
                               <td>{{$edu->end_date}}</td>
                               <td>
                                 <a href="javascript:void(0);" data-toggle="modal" data-target="#certificate_{{$edu->id}}">{{$edu->cirtificateupload}}</a>
                                 <div class="modal fade bd-example-modal-lg avoid-this" id="certificate_{{$edu->id}}" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                      <div class="modal-content">
                                        <div class="modal-header">
                                          <h5 class="modal-title" id="exampleModalLabel">{{$edu->certification_name}}</h5>
                                          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                          </button>
                                        </div>
                                        <div class="modal-body">
                                          <div class="container-fluid">
                                            <div class="row">
                                              <img style="max-width:750px; margin:0 auto;" class="img-responsive" src="{{url('upload/educational_certificate')}}/{{$edu->cirtificateupload}}" alt="employee_img">
                                            </div>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                  </div>

                               </td>
                             </tr>
                             @endforeach
                           </tbody>
                         </table>
                         <!--Table Ends Here-->
                      </div>
                    </div>
                </section>
              </div>
              @endif
              <!--ends education info-->


              <div class="clearfix"></div>
              <!--starts job experience info-->
              @if(!empty($jobexinfo))
              <div class="col-xl-12">
                <section class="card">
                  <div class="card-header bg-faded">
                      <span class="cat__core__title">
                          <h5 class="font-weight-bold"><i class="fa fa-certificate text-primary" aria-hidden="true"></i> Job Experience History:</h5>
                      </span>
                  </div>
                  <div class="card-block">
                    <div class="row">
                      <!--Table Starts Here-->
                        <table class="table">
                         <thead>
                           <tr class="bg-faded">
                             <th>Company Name</th>
                             <th>Company Address</th>
                             <th>Designation</th>
                             <th>Responsibility</th>
                             <th>From</th>
                             <th>To</th>
                             <th>Certificate</th>
                           </tr>
                         </thead>
                         <tbody>
                           @foreach($jobexinfo as $jobex)
                           <tr>
                             <td>{{$jobex->company_name}}</td>
                             <td>{{$jobex->company_address}}</td>
                             <td>{{$jobex->desigantion}}</td>
                             <td>{{$jobex->responsibility}}</td>
                             <td>{{$jobex->start_date}}</td>
                             <td>{{$jobex->end_date}}</td>
                             <td>
                               <a href="javascript:void(0);" data-toggle="modal" data-target="#jobexcertificate_{{$jobex->id}}">{{$jobex->cirtificateupload}}</a>
                               <div class="modal fade bd-example-modal-lg avoid-this" id="jobexcertificate_{{$edu->id}}" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
                                  <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                      <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLabel">{{$jobex->company_name}}</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                          <span aria-hidden="true">&times;</span>
                                        </button>
                                      </div>
                                      <div class="modal-body">
                                        <div class="container-fluid">
                                          <div class="row">
                                            <img style="max-width:750px; margin:0 auto;" class="img-responsive" src="{{url('upload/experience_certificate')}}/{{$jobex->cirtificateupload}}" alt="employee_img">
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                </div>

                             </td>
                           </tr>
                           @endforeach
                         </tbody>
                       </table>
                       <!--Table Ends Here-->
                    </div>
                  </div>
              </section>
            </div>
            @endif
            <!--ends job experience info-->

              @endif
            @endif
            <!--Page Detail Content Ends Here-->
          </div>

          <div id="editor"></div>
        </div>
  </section>


  </div>
  </div>

  </div>
@endsection

@section('extraHeader')
<!-- <link rel="stylesheet" type="text/css" href="{{url('vendors/select2/dist/css/select2.min.css')}}"> -->

@section('extraFooter')



<script>



//new print function
function printout() {

      var html = "";
      var id = "output";
      $('link').each(function() { // find all <link tags that have
        if ($(this).attr('rel').indexOf('stylesheet') !=-1) { // rel="stylesheet"
          html += '<link rel="stylesheet" href="'+$(this).attr("href")+'" />';
        }
      });
      html += '<body onload="window.focus(); window.print()">'+$("#"+id).html()+'</body>';
      var w = window.open("","print");
      if (w) { w.document.write(html); w.document.close() }

    }
  </script>


 <script type="text/javascript">
     $("#export_pdf").click(function(){

         var emp_code = $("#emp_prof_code").val();

         if(emp_code=='')
         {
             emp_code=0;
         }
         var param={'emp_code':emp_code,'_token':'<?=csrf_token()?>'};
         var link="<?=url('/')?>/Employee/Employeeinfo/ProfileDetail/ExportPDF/"+emp_code;

         window.location.href=link;

     });

 </script>





@include('include.coreKendo')

@endsection
