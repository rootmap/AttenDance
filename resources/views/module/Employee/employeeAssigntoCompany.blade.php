<?php
if(isset($data))
{
    $pageinfo=array("Edit Role Assigned Companies Settings","Edit Role Assigned Companies Record","","SUL");
}
else
{
    $pageinfo=array("Assigned Role Companies Settings","Add Assigned Role Companies Record","","SUL");
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
						<div class="col-xl-4">
							<!--Vertical Form Starts Here-->
                            @if(isset($data))
                            <form name="RoleAndPermission_AssignedToCompany" action="{{url('RoleAndPermission/AssignedToCompany/Update/'.$data['id'])}}" method="post">
                                {{csrf_field()}}
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="l30">Employee Code</label>
                                            <input value="{{ old('employee_code') }}" type="text" name="employee_code" class="form-control" placeholder="Type Employee Code" id="l30">
                                        </div>
                                    </div>
									
									<div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="l30" class="col-md-12">Assign Role </label>
                                            <select class="form-control" name="role_id">
                                                <option value="">Select Role</option>
                                                @if(isset($role))
                                                    @foreach($role as $row)
                                                    <option value="{{$row->id}}">{{$row->name}}</option>
                                                    @endforeach
                                                @endif

                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="l30" class="col-md-12">Company Name</label>
                                            <select class="form-control" name="company_id" multiple>
                                                <option value="">Select Company</option>
                                                @if(isset($company))
                                                    @foreach($company as $row)
                                                    <option value="{{$row->id}}">{{$row->name}}</option>
                                                    @endforeach
                                                @endif

                                            </select>
                                        </div>
                                    </div>
                                </div>


                                <div class="form-actions">
                                    <button type="submit"  class="btn btn-primary">Update</button>
                                    <button type="reset" class="btn btn-default">Cancel</button>
                                </div>
                            </form>
                            @else
							<form name="Department" action="{{url('RoleAndPermission/AssignedToCompany/Add')}}" method="post">
                                {{csrf_field()}}
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="l30">Employee Code</label>
                                            <input value="{{ old('emp_code') }}" type="text" name="emp_code" class="form-control" placeholder="Type Employee Code" id="l30">
                                        </div>
                                    </div>
									
									<div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="l30" class="col-md-12">Assign Role </label>
                                            <select class="form-control" name="role_id">
                                                <option value="">Select Role</option>
                                                @if(isset($role))
                                                    @foreach($role as $row)
                                                    <option value="{{$row->id}}">{{$row->name}}</option>
                                                    @endforeach
                                                @endif

                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="l30" class="col-md-12" style="display:block; border-bottom:2px #000 solid; font-size:18px;">Company Name</label>
                                                @if(isset($company))
                                                    @foreach($company as $row)
													<label style="display:block;"> <input type="checkbox" name="company_id[]" value="{{$row->id}}"> {{$row->name}}</label>
                                                    @endforeach
                                                @endif
                                        </div>
                                    </div>
                                </div>


                                <div class="form-actions">
                                    <button type="submit"  class="btn btn-primary">Create</button>
                                    <button type="reset" class="btn btn-default">Cancel</button>
                                </div>
                            </form>
                            @endif
                            <!--Vertical Form Ends Here-->
                        </div>
                        <div class="col-xl-8">
                            <!--Vertical Form Starts Here-->
                            <!-- kendo table code start from here-->
                            <div class="row">
                                <div id="grid" class="col-md-12"></div>
                            </div>
                            <script id="action_template" type="text/x-kendo-template">
                                <a class="k-button k-button-icontext k-grid-delete" onclick="javascript:deleteClick(#=id#);" ><span class="k-icon k-delete"></span> Delete</a>
                            </script>

                            <script type="text/javascript">
                                function deleteClick(id) {
                                    var c = confirm("Do you want to delete?");
                                    if (c === true) {
                                        $.ajax({
                                            type: "POST",
                                            dataType: "json",
                                            url: "AssignedToCompany/Delete",
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
                                                url: "<?=url('RoleAndPermission/AssignedToCompany/Json')?>",
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
                                                    name: {type: "string"},
                                                    emp_code: {type: "string"},
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
                                        {field: "emp_code", title: "EMP Code", width: "80px"},
                                        {field: "name", title: "Company", width: "80px"},
                                        {field: "created_at", title: "Created ", width: "100px",},
                                        {
                                            title: "Action", width: "80px",
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

@endsection
