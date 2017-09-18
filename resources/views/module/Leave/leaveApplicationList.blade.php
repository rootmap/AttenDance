<?php
if(isset($data))
{
    $pageinfo=array("Leave Application List","Leave Application Records","","SUL");
}
else
{
    $pageinfo=array("Leave Application List","Leave Application Records","","SUL");
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

              <a href="{{route('leave.leaveApplicationList.export.excel')}}" class="fa fa-file-excel-o fa-2x"  title="Export Excel" data-original-title="Export Excel">&nbsp;</a>
              <a href="{{route('leave.leaveApplicationList.export.pdf')}}" class="fa fa-file-pdf-o fa-2x"  title="Export Pdf" data-original-title="Export Pdf">&nbsp;</a>
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
                                <a class="k-button k-button-icontext k-grid-edit" href="{{url('/Leave/LeaveApplication/Detail')}}/#=id#"><span class="fa fa-eye"></span> View Detail</a>
                                <a style="display:none;" class="k-button k-button-icontext k-grid-edit" href="{{url('/Leave/LeaveApplication/Edit')}}/#=id#"><span class="k-icon k-edit"></span> Edit</a>
                                <a style="display:none;" class="k-button k-button-icontext k-grid-delete" onclick="javascript:deleteClick(#=id#);" ><span class="k-icon k-delete"></span> Delete</a>
                            </script>


                            <script type="text/javascript">
                                function deleteClick(id) {
                                    var c = confirm("Do you want to delete?");
                                    if (c === true) {
                                        $.ajax({
                                            type: "POST",
                                            dataType: "json",
                                            url: "LeaveUserData/Delete",
                                            data: {id: id,'_token':'<?=csrf_token()?>'},
                                            success: function (result) {
                                                $(".k-i-refresh").click();
                                            }
                                        });
                                    }
                                }

                            </script>
                            <script type="text/javascript">
                                $(document).ready(function () {
                                    var dataSource = new kendo.data.DataSource({
                                        transport: {
                                            read: {
                                                url: "<?=url('/Leave/LeaveApplication/LeaveApplicationList/Json')?>",
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
                                                    emp_name: {type: "string"},
                                                    leave_title:{type: "string"},
                                                    start_date:{type: "string"},
                                                    end_date:{type: "string"},
                                                    total_days_applied:{type: "string"},
                                                    is_half_day:{type: "boolean"},
                                                    half_day:{type: "string"},
                                                    leave_status:{type: "string"},
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
                                        {field: "id", title: "#", width: "40px", filterable: false},
                                        {field: "emp_name", title: "Employee Name ", width: "100px"},
                                        {field: "leave_title", title: "Leave Title", width: "80px"},
                                        {field: "start_date", title: "Start Date", width: "80px"},
                                        {field: "end_date", title: "End Date", width: "80px"},
                                        {field: "total_days_applied", title: "Total Days", width: "80px", filterable: false},
                                        {field: "is_half_day", title: "Is Half Day Leave", width: "80px", filterable: false},
                                        {field: "half_day", title: "Day Part", width: "80px", filterable: false},
                                        {field: "leave_status", title: "Leave Status", width: "80px", filterable: false},
                                        {field: "created_at", title: "Created ", width: "100px",},
                                        {
                                            title: "Action", width: "160px",
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

@section('extraHeader')
<!-- <link rel="stylesheet" type="text/css" href="{{url('vendors/select2/dist/css/select2.min.css')}}"> -->
@endsection
@section('extraFooter')
<!-- <script src="{{url('vendors/select2/dist/js/select2.full.min.js')}}"></script> -->

@include('include.coreKendo')
<?=MenuPageController::genarateKendoDatePicker(array("leave_starts","leave_end"))?>
@endsection
