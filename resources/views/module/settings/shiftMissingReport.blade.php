<?php
$pageinfo = array("Shift Missing Report", "Shift Missing Report", "", "SUL", "Filter Missing Report", "Genarate Report");
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
                        <strong>{{$pageinfo[4]}}</strong>
                        <!--<small class="text-muted">All cards are draggable and sortable!</small>-->
                    </h5>
                </div>





                <div class="card-block">

                    <div class="row">
                        <div class="col-xl-12">
                            <!--Vertical Form Starts Here-->
                            <!-- kendo table code start from here-->


                            <div class="row" style="z-index:9999;">
                                <!--Added For Leave User Data Filtering-->
								<div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="l30">Select Company</label>
                                        <select id="company_id" name="company_id" class="form-control">
											<option value="">Select Company</option>
											@if(isset($company))
												@foreach($company as $comp)
													<option value="{{$comp->id}}">{{$comp->name}}</option>
												@endforeach
											@endif
										</select>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="l30">Start date</label>
                                        <label class="input-group datepicker-only-init">
                                            <input type="text" id="start_date" name="start_date" class="form-control" placeholder="Type Start Day"/>
                                            <span class="input-group-addon" style="">
                                                <i class="icmn-calendar"></i>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="l30">End date</label>
                                        <label class="input-group datepicker-only-init">
                                            <input type="text" id="end_date" name="end_date" class="form-control" placeholder="Type End Day"/>
                                            <span class="input-group-addon" style="">
                                                <i class="icmn-calendar"></i>
                                            </span>
                                        </label>
                                    </div>
                                </div>
								<div class="col-lg-3">
									<div class="form-group">
                                        <label for="l30">&nbsp;</label>
                                        <label class="input-group">
											<button type="button" name="filter"  class="btn btn-primary">{{$pageinfo[5]}}</button>
										</label>
                                    </div>
                                </div>
								<div class="col-lg-5">
									<div class="form-group">
                                        <label for="l30">&nbsp;</label>
                                        <label class="input-group">
											<button type="button" name="review_flag_change"  class="btn btn-success">Update Status Between Date Range</button>
										</label>
                                    </div>
                                </div>
								<div class="col-lg-3">
									<div class="form-group">
                                        <label for="l30">&nbsp;</label>
                                        <label class="input-group">
											<button type="button" name="review_flag_change_all"  class="btn btn-danger">Update All Review Status</button>
										</label>
                                    </div>
                                </div>

                            </div>
                            

                            <!-- kendo table code end fro here-->
                            <!--Vertical Form Ends Here-->
                            <div class="row">
                        <div class="col-xl-12">
                            <!--Vertical Form Starts Here-->
                            <!-- kendo table code start from here-->
                            <div class="row">
                                <div id="grid" class="col-md-12"></div>
                                <div class="col-mb-12">
                                    <ul class="breadcrumb breadcrumb--custom" id="FooterJobCard">
                                        <li class="breadcrumb-item"><span></span></li>
                                    </ul>
                                    <ul class="breadcrumb breadcrumb--custom" id="FooterJobCardSum">
                                        <li class="breadcrumb-item"><span></span></li>
                                    </ul>
                                </div>
                            </div>
                            <script id="action_template" type="text/x-kendo-template">
                                <a class="k-button k-button-icontext k-grid-edit" href="{{url('Shift/missing/report/Edit')}}/#=id#"><span class="k-icon k-edit"></span> Edit</a>
                            </script>



                            <script type="text/javascript">


                                function KendoManualInitialized(link, data)
                                {
                                    
                                    var dataSource = new kendo.data.DataSource({
                                        transport: {
                                            read: {
                                                url: "<?= url('"+link+"') ?>",
                                                type: "POST",
                                                data: data,
                                                datatype: "json"

                                            }

                                        },
                                        batch: true,
                                        autoSync: true,
                                        schema: {
                                            data: "data",
                                            total: "total",
                                            model: {
                                                id: "id",
                                                fields: {
                                                    id: { type: "number" },
                                                    emp_code: { type: "string",editable: true },
                                                    date: { type: "string",editable: false},
													shift: { type: "string",editable: false},
													review_status: { type: "string",editable: false},
													reviewed_emp_code: { type: "string",editable: false},
													created_at: { type: "string",editable: false},
													updated_at: { type: "string",editable: false}
                                                    
                                                }
                                            }
                                        },
                                        pageSize: 20,
                                        serverPaging: false,
                                        serverFiltering: false,
                                        serverSorting: false
                                    });
                                    $("#grid").kendoGrid({
                                        dataBound: gridDataBoundJobcard,
                                        dataSource: dataSource,
                                        filterable: true,
                                        pageable: {
                                            refresh: true,
                                            input: true,
                                            numeric: true,
                                            pageSizes: true,
                                            pageSizes: [5, 20, 31, 62, 93, 400, 1000, 33000]
                                        },
                                        sortable: true,
                                        groupable: true,
                                        columns: [
                                            { field: "id",title: "ID",width: "50px",filterable: false},
                                            { field: "emp_code",title: "emp_code",width: "50px",filterable: true},
                                            { field: "date",title: "Date",width: "50px",filterable: false},
                                            { field: "shift",title: "shift",width: "50px",filterable: false},
                                            { field: "review_status",title: "review_status",width: "50px",filterable: false},
                                            { field: "reviewed_emp_code",title: "reviewed_emp_code",width: "50px",filterable: false},
                                            { field: "created_at",title: "created_at",width: "50px",filterable: false},
                                            { field: "updated_at",title: "updated_at",width: "50px",filterable: false},
                                            {
                                                title: "Action",
                                                width: "60px",
                                                template: kendo.template($("#action_template").html())
                                            }
                                        ],
                                    });
                                }
                                

                                $(document).ready(function () {

                                    $("button[name='review_flag_change']").click(function () {
                                        var employee_code = $("input[name='emp_code']").val();
                                        var s_date = $("input[name='start_date']").val();
                                        var e_date = $("input[name='end_date']").val();
										var company_id = $("select[name='company_id']").val();
                                        var param = {'company_id': company_id,'start_date': s_date,'end_date': e_date,'_token': '<?= csrf_token() ?>'};
                                        var link = "<?=url('Shift/update/missing/report')?>";
                                        $.post(link,param,function(data){
											if(data==1)
											{
												$("button[name='filter']").click();
												alert('Review Status Changed Successfully.');
											}
											else
											{
												alert('Failed, Review Status Changed.');
											}
										});
                                    });
									
									$("button[name='review_flag_change_all']").click(function () {
                                        var param = {'_token': '<?= csrf_token() ?>'};
                                        var link = "<?=url('Shift/updateAll/missing/report')?>";
                                        $.post(link,param,function(data){
											if(data==1)
											{
												$("button[name='filter']").click();
												alert('Review Status Changed All Successfully.');
											}
											else
											{
												alert('Failed To Change Review Status.');
											}
										});
                                    });
									
									$("button[name='filter']").click(function () {

                                        var employee_code = $("input[name='emp_code']").val();
                                        var s_date = $("input[name='start_date']").val();
                                        var e_date = $("input[name='end_date']").val();
										var company_id = $("select[name='company_id']").val();

                                        var param = {'company_id': company_id,'start_date': s_date,'end_date': e_date,'_token': '<?= csrf_token() ?>'};
                                        var link = "Shift/missing/report";

                                        KendoManualInitialized(link, param);

                                    });

                                    $("#export_excel").click(function () {

                                        var emp_code = $("input[name='emp_code']").val();
                                        if (emp_code == '')
                                        {
                                            emp_code = 0;
                                        }
                                        var start_date = $("input[name='start_date']").val();
                                        if (start_date == '')
                                        {
                                            start_date = 0;
                                        }
                                        var end_date = $("input[name='end_date']").val();
                                        if (end_date == '')
                                        {
                                            end_date = 0;
                                        }
										var company_id = $("select[name='company_id']").val();
                                        if (company_id == '')
                                        {
                                            company_id = 0;
                                        }
                                        var param = {'emp_code': emp_code, 'start_date': start_date, 'end_date': end_date, '_token': '<?= csrf_token() ?>'};
                                        var link = "<?= url('/') ?>/ShiftMissing/Export/Excel/"+company_id+"/" + start_date + "/" + end_date;
										//alert(link);
										//return false;
                                        window.location.href = link;

                                    });

                                    $("#export_pdf").click(function () {

                                        var emp_code = $("input[name='emp_code']").val();
                                        if (emp_code == '')
                                        {
                                            emp_code = 0;
                                        }
                                        var start_date = $("input[name='start_date']").val();
                                        if (start_date == '')
                                        {
                                            start_date = 0;
                                        }
                                        var end_date = $("input[name='end_date']").val();
                                        if (end_date == '')
                                        {
                                            end_date = 0;
                                        }
										var company_id = $("select[name='company_id']").val();
                                        if (company_id == '')
                                        {
                                            company_id = 0;
                                        }
                                         var param = {'company_id': company_id, 'start_date': start_date, 'end_date': end_date, '_token': '<?= csrf_token() ?>'};
                                        var link = "<?= url('/') ?>/ShiftMissing/Export/Pdf/"+company_id+"/" + start_date + "/" + end_date;
										//alert(link);
										//return false;
                                        window.location.href = link;

                                    });
                                     


                                });

                                

                            </script>
                            <!-- kendo table code end fro here-->
                            <!--Vertical Form Ends Here-->
                        </div>
                    </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>


<div class="row" style="z-index:-1;">
    <div class="col-lg-12">
        <div class="cat__core__sortable" id="left-col">
            <section class="card" order-id="card-1">


                <div class="card-header">

                    <div class="pull-right cat__core__sortable__control">

                        <a href="javascript:void(0)" id="export_excel" class="fa fa-file-excel-o fa-2x"  title="Export Excel" data-original-title="Export Excel">&nbsp;</a>
                        <a href="javascript:void(0)"  id="export_pdf" class="fa fa-file-pdf-o fa-2x"  title="Export Pdf" data-original-title="Export Pdf">&nbsp;</a>
                    </div>

                  
                </div>





                

                    
              
            </section>
        </div>
    </div>
</div>
@endsection
@section('extraFooter')
@include('include.coreKendo')
<?= MenuPageController::genarateKendoDatePicker(array("start_date", "end_date")) ?>
@endsection
