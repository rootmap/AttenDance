<?php
$pageinfo = array("Attendance Raw Data List", "Attendance Reproces", "", "SUL", "Attendance Reproces", "Filter Attendance Data");
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





                <div class="card-block" style="">

                    <div class="row">
                        <div class="col-xl-12">
                            <!--Vertical Form Starts Here-->
                            <!-- kendo table code start from here-->


                            <div class="row">
                                <!--Added For Leave User Data Filtering-->
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="l30">Employee</label>
                                        <select class="form-control select-search" id="emp_code" name="emp_code">
                                            <option value="">Select Employee</option>
                                            @if(isset($employee))
                                            @foreach($employee as $erow)
                                            <option value="{{$erow['emp_code']}}">{{$erow['name']}}</option>
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

                            </div>
                            <div class="form-actions">
                                <button type="button" name="filter" id="filter" class="btn btn-primary">{{$pageinfo[5]}}</button>
                            </div>

                            <!-- kendo table code end fro here-->
                            <!--Vertical Form Ends Here-->
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>


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
                        <div class="col-xl-12">
                            <!--Vertical Form Starts Here-->
                            <!-- kendo table code start from here-->
                            <div class="row">
                                <div id="grid" class="col-md-12"></div>
                            </div>
							
							<button class="btn btn-danger pull-right" id="reprocess_flag" style="margin-top:10px !important;"  type="button">Re-Process Data</button>
							
                            <script id="action_template" type="text/x-kendo-template">
                                //<a class="k-button k-button-icontext k-grid-edit" href="{{url('Employee/Employeeinfo/Edit')}}/#=id#"><span class="k-icon k-edit"></span> Edit</a>-->
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
                                        autoSync: true,
                                        schema: {
                                            data: "data",
                                            total: "total",
                                            model: {
                                                id: "id",
                                                fields: {
                                                    id: {
                                                        type: "string"
                                                    },
                                                    raw_emp_code: {
                                                        type: "string"
                                                    },
                                                    machine_id: {
                                                        type: "string"
                                                    },
                                                    raw_date: {
                                                        type: "string"
                                                    },
                                                    raw_time : {
                                                        type: "string"
                                                    },
                                                    is_read: {
                                                        type: "boolean"
                                                    },
                                                    created_at: {
                                                        type: "string"
                                                    },
                                                    updated_at: {
                                                        type: "string"
                                                    },
                                                }
                                            }
                                        },
                                        pageSize: 31,
                                        serverPaging: false,
                                        serverFiltering: false,
                                        serverSorting: false
                                    });

                                    $("#grid").kendoGrid({
                                        dataBound: gridDataBound,
                                        dataSource: dataSource,
                                        filterable: true,
                                        pageable: {
                                            refresh: true,
                                            input: true,
                                            numeric: false,
                                            pageSizes: true,
                                            pageSizes: [5, 20, 31, 62, 93, 400, 1000]
                                        },
                                        sortable: true,
                                        groupable: true,
                                        columns: [
                                            {
                                                field: "id",
                                                title: "#",
                                                width: "50px",
                                                filterable: false
                                            },
                                            {
                                                field: "raw_emp_code",
                                                title: "Employee"
                                            },
                                            {
                                                field: "machine_id",
                                                title: "Machine ID"
                                            },
                                            {
                                                field: "raw_date",
                                                title: "Date"
                                            },
                                            {
                                                field: "raw_time",
                                                title: "Time"
                                            },
                                            {
                                                field: "is_read",
                                                title: "Log Process Complete"
                                            },
                                            {
                                                field: "created_at",
                                                title: "Created Date"
                                            },
                                            {
                                                field: "updated_at",
                                                title: "Updated Date"
                                            }
                                        ],
                                    });
                                }

                                $(document).ready(function () {

                                    $("button[name='filter']").click(function () {

                                        var employee_code = $("select[name='emp_code']").val();
                                        var s_date = $("input[name='start_date']").val();
                                        var e_date = $("input[name='end_date']").val();
                                       //alert(employee_code);

                                        var param = {'emp_code': employee_code,
                                            'start_date': s_date,
                                            'end_date': e_date,
                                            '_token': '<?= csrf_token() ?>'};
                                        var link = "AttendanceReprocess/Filter";

                                        KendoManualInitialized(link, param);

                                    });

                                });



                            </script>
                            <!-- kendo table code end fro here-->
                            <!--Vertical Form Ends Here-->
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
@section('extraHeader')
<link rel="stylesheet" type="text/css" href="{{url('vendors/select2/dist/css/select2.min.css')}}">
@endsection 
@section('extraFooter')
 <!--<script src="{{url('vendors/bootstrap-select/dist/js/bootstrap-select.min.js')}}"></script>-->
<script src="{{url('vendors/select2/dist/js/select2.full.min.js')}}"></script> 
<script type="text/javascript">
                                $(document).ready(function () {
                                    $(".select-search").select2();
									$("#reprocess_flag").click(function(){
										var emp_code=$("select[name='emp_code']").val();
										var start_date=$("input[name='start_date']").val();
										var end_date=$("input[name='end_date']").val();
										
										var emp_code_length=emp_code.length;
										var start_date_length=start_date.length;
										var end_date_length=end_date.length;
										
										if(emp_code_length!=0 && start_date_length!=0 && end_date_length!=0)
										{
											//alert(emp_code_length);
											var url="<?=url('Attendance/Jobcard/Modify/Process/Log')?>";
											var param={'raw_emp_code':emp_code,'start_date':start_date,'end_date':end_date,'_token':'<?=csrf_token()?>'};
											$.post(url,param,function(data){
												if(data==1)
												{
													alert('Data Process is Started Sucessfully.');
													$('span.k-i-refresh').click();
													
													
													
												}
												else if(data==2)
												{
													alert('Please Fillup All Field.');
												}
												else if(data==3)
												{
													alert('Failed To Process Please Check All Field.');
												}
												else
												{
													alert('Failed, Please Contact With System Admin.');
												}
											});
										}
										else
										{
											alert('Please Fillup All Field.');
										}
										
									});
                                });
</script>
@include('include.coreKendo')
<?= MenuPageController::genarateKendoDatePicker(array("start_date", "end_date")) ?>
@endsection
