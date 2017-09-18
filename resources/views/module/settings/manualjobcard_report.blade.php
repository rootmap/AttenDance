<?php
if (isset($data)) {
    $pageinfo = array("Manual Jobcard Report Data", "Manual Jobcard Report Data", "", "SUL", "Filter list", "Filtered Report");
} else {
    $pageinfo = array("Manual Jobcard Report Data", "Manual Jobcard Report Data", "", "SUL", "Filter list", "Filtered Report");
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
                                @if(empty($logged_emp_com))
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
                                @else
                                <input type="hidden" name="company_id" value="{{$logged_emp_com}}" class="form-control" id="l30">
                                @endif

                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label for="l30">Start Date</label>
                                        <input class="form-control" id="start_date" name="start_date" placeholder="End date" />
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label for="l30">End Date</label>
                                        <input class="form-control" id="end_date" name="end_date" placeholder="End date" />
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

                        <a href="javascript:void(0)" id="export_excel" class="fa fa-file-excel-o fa-2x"  title="Export Excel" data-original-title="Export Excel">&nbsp;</a>
                        <a href="javascript:void(0)"  id="export_pdf" class="fa fa-file-pdf-o fa-2x"  title="Export Pdf" data-original-title="Export Pdf">&nbsp;</a>  
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

                            <script type="text/javascript">
                                function deleteClick(id) {
                                    var c = confirm("Do you want to delete?");
                                    if (c === true) {
                                        $.ajax({
                                            type: "POST",
                                            dataType: "json",
                                            url: "Delete",
                                            data: {id: id, '_token': '<?= csrf_token() ?>'},
                                            success: function (result) {
                                                $(".k-i-refresh").click();
                                            }
                                        });
                                    }
                                }

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
                                        autoSync: false,
                                        schema: {
                                            data: "data",
                                            total: "total",
                                            model: {
                                                id: "id",
                                                fields: {
                                                    id: {type: "number"},
                                                    company_id: {type: "string"},
                                                    emp_code: {type: "string"},
                                                    day_type: {type: "string"},
                                                    date: {type: "string"},
                                                    created_at: {type: "string"}
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
                                            {field: "id", title: "#", width: "40px", filterable: false},
                                            {field: "company_id", title: "Company Name ", width: "80px"},
                                            {field: "emp_code", title: "Employee Code ", width: "80px"},
                                            {field: "day_type", title: "Day Status ", width: "80px"},
                                            {field: "date", title: "Date", width: "80px"},
                                            {field: "created_at", title: "Created ", width: "70px", },
                                        ],
                                    });
                                }

                                $(document).ready(function () {

                                    $("button[name='filter']").click(function () {

                                        var company_id = $("select[name='company_id']").val();

                                        var start_date = $("input[name='start_date']").val();
                                        var end_date = $("input[name='end_date']").val();

                                        var param = {'company_id': company_id, 'start_date': start_date, 'end_date': end_date, '_token': '<?= csrf_token() ?>'};
                                        var link = "Filter/Datewise/ManualJobcard/Report";
                                        // alert(start_date);
                                        KendoManualInitialized(link, param);

                                    });

                                    $("#export_excel").click(function () {

                                        var company_id = $("select[name='company_id']").val();

                                        if (company_id == '')
                                        {
                                            company_id = 0;
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



                                        var param = {'company_id': company_id, '_token': '<?= csrf_token() ?>'};
                                        var link = "<?= url('/') ?>/Export/Datewise/ManualJobcard/Excel/" + company_id + "/" + start_date + "/" + end_date;

                                        window.location.href = link;

                                    });

                                    $("#export_pdf").click(function () {

                                        var company_id = $("select[name='company_id']").val();

                                        if (company_id == '')
                                        {
                                            company_id = 0;
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



                                        var param = {'company_id': company_id, '_token': '<?= csrf_token() ?>'};
                                        var link = "<?= url('/') ?>/Export/Datewise/ManualJobcard/Pdf/" + company_id + "/" + start_date + "/" + end_date;

                                        window.location.href = link;

                                    });

                                    var dataSource = new kendo.data.DataSource({
                                        transport: {
                                            read: {
                                                url: "<?= url('/Filter/ManualJobcard/Json') ?>",
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
                                                    id: {type: "number"},
                                                    company_id: {type: "string"},
                                                    emp_code: {type: "string"},
                                                    day_type: {type: "string"},
                                                    date: {type: "string"},
                                                    created_at: {type: "string"}
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
                                            {field: "id", title: "#", width: "40px", filterable: false},
                                            {field: "company_id", title: "Company Name ", width: "80px"},
                                            {field: "emp_code", title: "Employee Code ", width: "80px"},
                                            {field: "day_type", title: "Day Status ", width: "80px"},
                                            {field: "date", title: "Date", width: "80px"},
                                            {field: "created_at", title: "Created ", width: "70px", },
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
<?= MenuPageController::genarateKendoDatePicker(array("start_date", "end_date")) ?>
@endsection 