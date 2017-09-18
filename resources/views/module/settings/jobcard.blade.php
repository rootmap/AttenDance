<?php

    $pageinfo = array("Admin Job Card Report", "Admin Job Card Report", "", "SUL","Filter Admin Jobcard","Genarate Report");
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


                            <div class="row">
                                <!--Added For Leave User Data Filtering-->
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="l30">Employee Code</label>
                                        <input type="text" name="emp_code"  class="form-control" placeholder="Type Employee Code" id="">
                                        <!-- <select class="form-control" name="emp_code">
                                            <option value="">Select Employee</option>
                                        </select> -->
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
                                <button type="button" name="filter"  class="btn btn-primary">{{$pageinfo[5]}}</button>
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

                    <div class="pull-right cat__core__sortable__control">

                        <a href="#" id="export_excel" class="fa fa-file-excel-o fa-2x"  title="Export Excel" data-original-title="Export Excel">&nbsp;</a>
                        <a href="#"  id="export_pdf" class="fa fa-file-pdf-o fa-2x"  title="Export Pdf" data-original-title="Export Pdf">&nbsp;</a>
                    </div>

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
                            <script id="action_template" type="text/x-kendo-template">
                                <a class="k-button k-button-icontext k-grid-edit" href="{{url('Employee/Employeeinfo/Edit')}}/#=id#"><span class="k-icon k-edit"></span> Edit</a>
                            </script>

                           

                            <script type="text/javascript">



                                function KendoManualInitialized(link,data)
                                {
                                    var dataSource = new kendo.data.DataSource({
                                        transport: {
                                            read: {
                                                url: "<?= url('"+link+"') ?>",
                                                type: "POST",
                                                data:data,
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
                                                    id: {
                                                        type: "number"
                                                    },

                                                    in_time: {
                                                        type: "string"
                                                    },

                                                    out_time: {
                                                        type: "string"
                                                    },
                                                    total_time: {
                                                        type: "string"
                                                    },
                                                    total_ot: {
                                                        type: "string"
                                                    },
                                                    day_status: {
                                                        type: "string"
                                                    },
                                                }
                                            }
                                        },
                                        pageSize: 10,
                                        serverPaging: true,
                                        serverFiltering: true,
                                        serverSorting: true
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
                                            pageSizes: [10, 20, 50, 100, 200, 400]
                                        },
                                        sortable: true,
                                        groupable: true,
                                         columns: [
                                         {
                                            field: "id",
                                            title: "# NO",
                                            width: "50px",
                                            filterable: false
                                        },

                                        {
                                            field: "in_time",
                                            title: "In Time ",
                                            width: "50px"
                                        },

                                        {
                                          field: "out_time",
                                          title: "Out Time",
                                          width: "50px"
                                        },
                                        {
                                          field: "total_time",
                                          title: "Total Hour",
                                          width: "50px"
                                        },
                                        {
                                          field: "total_ot",
                                          title: "Over Time",
                                          width: "50px"
                                        },
                                        {
                                          field: "day_status",
                                          title: "Status",
                                          width: "50px"
                                        },
                                        {
                                            title: "Action",
                                            width: "120px",
                                            template: kendo.template($("#action_template").html())
                                        }
                                        ],
                                    });
                                }

                                $(document).ready(function () {

                                    $("button[name='filter']").click(function(){
                                     
                                        var employee_code=$("input[name='emp_code']").val();
                                        var s_date=$("input[name='start_date']").val();
                                        var e_date=$("input[name='end_date']").val();
                                        // alert(employee_code);

                                        var param={'emp_code':employee_code,
                                                   'start_date':s_date,
                                                   'end_date':e_date,
                                                   '_token':'<?=csrf_token()?>'};
                                        var link="Jobcard/AdminJson";

                                        KendoManualInitialized(link,param);

                                    });

                                    $("#export_excel").click(function(){

                                        var company_id=$("select[name='company_id']").val();

                                        if(company_id=='')
                                        {
                                            company_id=0;
                                        }
                                        var department_id=$("select[name='department_id']").val();
                                        if(department_id=='')
                                        {
                                            department_id=0;
                                        }
                                        var section_id=$("select[name='section_id']").val();
                                        if(section_id=='')
                                        {
                                            section_id=0;
                                        }
                                        var designation_id=$("select[name='designation_id']").val();
                                        if(designation_id=='')
                                        {
                                            designation_id=0;
                                        }
                                        var designation_id=$("select[name='designation_id']").val();
                                        if(designation_id=='')
                                        {
                                            designation_id=0;
                                        }
                                        var employee_code=$("select[name='emp_code']").val();
                                        if(employee_code=='')
                                        {
                                            employee_code=0;
                                        }
                                        var leave_policy_id=$("select[name='leave_policy_id']").val();
                                        if(leave_policy_id=='')
                                        {
                                            leave_policy_id=0;
                                        }
                                        var year=$("select[name='year']").val();
                                        if(year=='')
                                        {
                                            year=0;
                                        }



                                        var param={'emp_code':emp_code,'start_date':start_date,'end_date':end_date,'_token':'<?=csrf_token()?>'};
                                        var link="<?=url('/')?>/Export/Jobcard/Excel/"+emp_code+"/"+start_date+"/"+end_date;

                                        window.location.href=link;

                                    });

                                    $("#export_pdf").click(function(){

                                        var company_id=$("select[name='company_id']").val();

                                        if(company_id=='')
                                        {
                                            company_id=0;
                                        }
                                        var department_id=$("select[name='department_id']").val();
                                        if(department_id=='')
                                        {
                                            department_id=0;
                                        }
                                        var section_id=$("select[name='section_id']").val();
                                        if(section_id=='')
                                        {
                                            section_id=0;
                                        }
                                        var designation_id=$("select[name='designation_id']").val();
                                        if(designation_id=='')
                                        {
                                            designation_id=0;
                                        }
                                        var employee_code=$("select[name='emp_code']").val();
                                        if(employee_code=='')
                                        {
                                            employee_code=0;
                                        }
                                        var leave_policy_id=$("select[name='leave_policy_id']").val();
                                        if(leave_policy_id=='')
                                        {
                                            leave_policy_id=0;
                                        }
                                        var year=$("select[name='year']").val();
                                        if(year=='')
                                        {
                                            year=0;
                                        }



                                        var param={'company_id':company_id,'department_id':department_id,'section_id':section_id,'designation_id':designation_id,'employee_code':employee_code,'leave_policy_id':leave_policy_id,'year':year,'_token':'<?=csrf_token()?>'};
                                        var link="<?=url('/')?>/Export/LeaveUserData/Pdf/"+company_id+"/"+department_id+"/"+section_id+"/"+designation_id+"/"+employee_code+"/"+leave_policy_id+"/"+year;

                                        window.location.href=link;

                                    });

                                    //For Deafult Data View
                                    var dataSource = new kendo.data.DataSource({
                                        transport: {
                                            read: {
                                                url: "<?= url('/Jobcard/AdminJson') ?>",
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
                                                    id: {
                                                        type: "number"
                                                    },

                                                    in_time: {
                                                        type: "string"
                                                    },

                                                    out_time: {
                                                        type: "string"
                                                    },
                                                    total_time: {
                                                        type: "string"
                                                    },
                                                    total_ot: {
                                                        type: "string"
                                                    },
                                                    day_status: {
                                                        type: "string"
                                                    },
                                                }
                                            }
                                        },
                                        pageSize: 10,
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
                                            pageSizes: [10, 20, 50, 100, 200, 400]
                                        },
                                        sortable: true,
                                        groupable: true,
                                        columns: [
                                         {
                                            field: "id",
                                            title: "# NO",
                                            width: "50px",
                                            filterable: false
                                        },

                                        {
                                            field: "in_time",
                                            title: "In Time ",
                                            width: "50px"
                                        },

                                        {
                                          field: "out_time",
                                          title: "Out Time",
                                          width: "50px"
                                        },
                                        {
                                          field: "total_time",
                                          title: "Total Hour",
                                          width: "50px"
                                        },
                                        {
                                          field: "total_ot",
                                          title: "Over Time",
                                          width: "50px"
                                        },
                                        {
                                          field: "day_status",
                                          title: "Status",
                                          width: "50px"
                                        },
                                        {
                                            title: "Action",
                                            width: "120px",
                                            template: kendo.template($("#action_template").html())
                                        }
                                        ],
                                    });
                                    //Ends For Default Data View
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
@section('extraFooter')
@include('include.coreKendo')
<?=MenuPageController::genarateKendoDatePicker(array("start_date","end_date"))?>
@endsection
