<?php
if(isset($data))
{
    $pageinfo=array("Edit Supervisor Settings","Edit supervisor Record","","SUL");
}
else
{
    $pageinfo=array("Supervisor","Add supervisor Record","","SUL");
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
						<div class="col-xl-12">
							<!--Vertical Form Starts Here-->
                            @if(isset($data))
                            <form name="Branch" action="{{url('Settings/Supervisor/Update/'.$data['id'])}}" method="post">
                                {{csrf_field()}}
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="l30">Branch Name</label>
                                            <input type="text" name="name" class="form-control" placeholder="Type Branch Name" id="l30" value="{{$data['name']}}">
                                        </div>
                                    </div>

                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="l30">Company Name</label>
                                            <select class="form-control" name="company_id">
                                                <!--<option value="">Select Company</option>-->
                                                @if(isset($company))
                                                    @foreach($company as $row)
                                                    <option <?php if($data['company_id']==$row->id){ ?> selected="selected" <?php } ?> value="{{$row->id}}">{{$row->name}}</option>
                                                    @endforeach
                                                @endif

                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="l30">Is Active</label>
                                            <select class="form-control" name="is_active">
                                                <!--<option value="">Select Status</option>-->
                                                <option <?php if($data['is_active']=="Active"){ ?> selected="selected" <?php } ?> value="Active">Active</option>
                                                <option <?php if($data['is_active']=="Inactive"){ ?> selected="selected" <?php } ?> value="Inactive">Inactive</option>
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
							<form name="Branch" action="{{url('Settings/Supervisor/Add')}}" method="post">
                                {{csrf_field()}}
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="l30">Supervisor Name</label>
                                            <input type="text" name="name" class="form-control" placeholder="Type Branch Name" id="l30">
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="l30" class="col-md-12">Company Name</label>
                                            <select class="form-control" name="company_id">
                                                <option value="">Select Company</option>
                                                @if(isset($company))
                                                    @foreach($company as $row)
                                                    <option value="{{$row->id}}">{{$row->name}}</option>
                                                    @endforeach
                                                @endif

                                            </select>
                                        </div>
                                    </div>
                                   
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="l30" class="col-md-12">Department Name</label>
                                            <select class="form-control" name="department_id">
                                                <option value="">Select Department</option>
                                                    @foreach($department as $row)
                                                    <option value="{{$row->id}}">{{$row->name}}</option>
                                                    @endforeach
                                            </select>
                                        </div>
                                    </div>
                                     <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="l30" class="col-md-12">Section Name</label>
                                            <select class="form-control" name="section_id">
                                                <option value="">Select Section</option>
                                               
                                                    @foreach($section as $row)
                                                    <option value="{{$row->id}}">{{$row->name}}</option>
                                                    @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="l30" class="col-md-12">Branch Name</label>
                                            <select class="form-control" name="branch_id">
                                                <option value="">Select Branch</option>
                                                    @foreach($branch as $row)
                                                    <option value="{{$row->id}}">{{$row->name}}</option>
                                                    @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="l30" class="col-md-12">Designation Name</label>
                                            <select class="form-control" name="designation_id">
                                                <option value="">Select Designation</option>
                                                    @foreach($designation as $row)
                                                    <option value="{{$row->id}}">{{$row->name}}</option>
                                                    @endforeach

                                            </select>
                                        </div>
                                    </div>
                                    

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="l30" class="col-md-12">Is Active</label>
                                            <select class="form-control" name="is_active">
                                                <option value="">Select Status</option>
                                                <option value="Active">Active</option>
                                                <option value="Inactive">Inactive</option>
                                            </select>
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
                        <div class="col-xl-12">
                            <!--Vertical Form Starts Here-->
                            <!-- kendo table code start from here-->
                            <div class="row">
                                <div id="grid" class="col-md-12"></div>
                            </div>
                            <script id="action_template" type="text/x-kendo-template">
                                <a class="k-button k-button-icontext k-grid-edit" href="{{url('Settings/Supervisor/Edit')}}/#=id#"><span class="k-icon k-edit"></span> Edit</a>
                                <a class="k-button k-button-icontext k-grid-delete" onclick="javascript:deleteClick(#=id#);" ><span class="k-icon k-delete"></span> Delete</a>
                            </script>

                            <script type="text/javascript">
                                function deleteClick(id) {
                                    var c = confirm("Do you want to delete?");
                                    if (c === true) {
                                        $.ajax({
                                            type: "POST",
                                            dataType: "json",
                                            url: "Supervisor/Delete",
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
                                                url: "<?=url('Settings/Supervisor/Json')?>",
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
                                        {field: "name", title: "Department Name ", width: "80px"},
                                        {field: "company_id", title: "Company Name ", width: "80px"},
                                        {field: "is_active", title: "Is Active", width: "80px"},
                                        
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