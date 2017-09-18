<?php
if (isset($data)) {
    $pageinfo = array("Employee Sorted Data", "Employee Sorted Data", "", "SUL","Filter list","Filtered Report");
} else {
    $pageinfo = array("Employee Data List", "Employee Data List", "", "SUL","Filter list","Filtered Report");
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
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label for="l30">Company Name</label>
                                        <select class="form-control" name="company_id">
                                            <option selected="selected" value="">Select Company</option>
                                            @if(isset($company))
                                            @foreach($company as $row)
                                            <option value="{{$row->id}}">{{$row->name}}</option>
                                            @endforeach
                                            @endif

                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label for="l30">Department</label>
                                        <select class="form-control" name="department_id">
                                            <option value="">Select Company</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label for="l30">Section</label>
                                        <select class="form-control" name="section_id">
                                            <option value="">Select Section</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label for="l30">Designation</label>
                                        <select class="form-control" name="designation_id">
                                            <option value="">Select Designation</option>
                                        </select>
                                    </div>
                                </div>

                            </div>
                            <div class="form-actions">
                                <button type="button" name="filter"  class="btn btn-primary">Filter Record</button>
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
                                <a class="k-button k-button-icontext k-grid-edit" href="{{url('/Employee/Employeeinfo/ProfileDetail')}}/#=emp_code#"><span class="fa fa-eye"></span> View Profile</a>
                                <a class="k-button k-button-icontext k-grid-edit" href="{{url('Employee/Employeeinfo/Edit')}}/#=id#"><span class="k-icon k-edit"></span> Edit</a>
                                <a class="k-button k-button-icontext k-grid-delete" onclick="javascript:deleteClick(#=id#);"><span class="k-icon k-delete"></span> Delete</a>
                            </script>

                            <script type="text/javascript">
                                function deleteClick(id) {
                                    var c = confirm("Do you want to delete?");
                                    if (c === true) {
                                        $.ajax({
                                            type: "POST",
                                            dataType: "json",
                                            url: "Employee/Employeeinfo/Delete",
                                            data: {
                                                id: id,
                                                '_token': '<?= csrf_token() ?>'
                                            },
                                            success: function (result) {
                                                $(".k-i-refresh").click();
                                            }
                                        });
                                    }
                                }
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
                                                    name: {
                                                        type: "string"
                                                    },
                                                    created_at: {
                                                        type: "string"
                                                    }
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
                                            title: "#",
                                            width: "40px",
                                            filterable: false
                                        },
                                        {
                                            field: "emp_code",
                                            title: "Code ",
                                            width: "40px"
                                        },
                                        {
                                            field: "name",
                                            title: "Full Name ",
                                            width: "80px"
                                        },
                                        {
                                            field: "email",
                                            title: "Email ",
                                            width: "80px"
                                        },
                                        {
                                            field: "phone",
                                            title: "Phone ",
                                            width: "80px"
                                        },
                                        {
                                            field: "department",
                                            title: "Department ",
                                            width: "80px"
                                        },
                                        {
                                            field: "created_at",
                                            title: "Created ",
                                            width: "100px",
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
                                       // alert('success');
                                        var company_id=$("select[name='company_id']").val();
                                        var department_id=$("select[name='department_id']").val();
                                        var section_id=$("select[name='section_id']").val();
                                        var designation_id=$("select[name='designation_id']").val();

                                        var param={'company_id':company_id,'department_id':department_id,'section_id':section_id,'designation_id':designation_id,'_token':'<?=csrf_token()?>'};
                                        var link="Filter/Employee/List";

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



                                        var param={'company_id':company_id,'department_id':department_id,'section_id':section_id,'designation_id':designation_id,'_token':'<?=csrf_token()?>'};
                                        var link="<?=url('/')?>/Export/Employee/Excel/"+company_id+"/"+department_id+"/"+section_id+"/"+designation_id;

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



                                        var param={'company_id':company_id,'department_id':department_id,'section_id':section_id,'designation_id':designation_id,'_token':'<?=csrf_token()?>'};
                                        var link="<?=url('/')?>/Export/Employee/Pdf/"+company_id+"/"+department_id+"/"+section_id+"/"+designation_id;

                                        window.location.href=link;

                                    });

                                    var dataSource = new kendo.data.DataSource({
                                        transport: {
                                            read: {
                                                url: "<?= url('Employee/Employeeinfo/Json') ?>",
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
                                                    name: {
                                                        type: "string"
                                                    },
                                                    created_at: {
                                                        type: "string"
                                                    }
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
                                            title: "#",
                                            width: "40px",
                                            filterable: false
                                        },
                                        {
                                            field: "emp_code",
                                            title: "Code ",
                                            width: "40px"
                                        },
                                        {
                                            field: "name",
                                            title: "Full Name ",
                                            width: "80px"
                                        },
                                        {
                                            field: "email",
                                            title: "Email ",
                                            width: "80px"
                                        },
                                        {
                                            field: "phone",
                                            title: "Phone ",
                                            width: "80px"
                                        },
                                        {
                                            field: "department",
                                            title: "Department ",
                                            width: "80px"
                                        },
                                        {
                                            field: "created_at",
                                            title: "Created ",
                                            width: "100px",
                                        },
                                        {
                                            title: "Action",
                                            width: "120px",
                                            template: kendo.template($("#action_template").html())
                                        }
                                        ],
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
@section('extraFooter')
@include('include.coreKendo')
@include('ajax_include.company_wise_department')
@include('ajax_include.department_wise_section')
@include('ajax_include.section_wise_designation')
@endsection
