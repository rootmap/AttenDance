<?php
if(isset($data))
{
    $pageinfo=array("Edit System Module Info","Edit System Module Record","","SUL");
}
else
{
    $pageinfo=array("System Module Info","Add System Module Record","","SUL");
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

                        <a href="{{route('settings.module.export.excel')}}" class="fa fa-file-excel-o fa-2x"  title="Export Excel" data-original-title="Export Excel">&nbsp;</a>
                        <a href="{{route('settings.module.export.pdf')}}" class="fa fa-file-pdf-o fa-2x"  title="Export Pdf" data-original-title="Export Pdf">&nbsp;</a>  
                    </div>

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
                            <form name="module" action="{{url('Settings/Module/Update/'.$data['id'])}}" method="post">
                                {{csrf_field()}}
                                <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label for="l30">Select Company</label>
                                        <select class="form-control col-md-5" name="company_id">
                                            <option value="">Select Company</option>
                                            @if(isset($company))
                                            @foreach($company as $row)
                                            <option 
                                            @if($data['company_id']==$row->id) 
                                                selected="selected" 
                                            @endif
                                            value="{{$row->id}}">{{$row->name}}</option>
                                            @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="l30">Module Name</label>
                                            <input type="text" name="name" class="form-control" placeholder="Type Module Name" id="l30" value="{{$data['name']}}">
                                        </div>
                                    </div>

                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="l30">Module Icon</label>
                                            <input type="text" name="icon" class="form-control" placeholder="Type Module Icon Class Name" id="l30" value="{{$data['icon']}}">
                                        </div>
                                    </div>

                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="l30">
                                            <input
                                            @if($data['is_active']==1) 
                                                checked="checked" 
                                            @endif
                                             type="checkbox" value="1" name="is_active"> Is Active</label>
                                            
                                        </div>
                                    </div>


                                </div>


                                <div class="form-actions">
                                    <button type="submit"  class="btn btn-primary">Update</button>
                                    <button type="reset" class="btn btn-default">Cancel</button>
                                </div>
                            </form>
                            @else
							<form name="Module" action="{{url('Settings/Module/Add')}}" method="post">
                                {{csrf_field()}}
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="l30">Company Name</label>
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
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="l30">Module Name</label>
                                            <input type="text" name="name" class="form-control" placeholder="Type Module Name" id="l30">
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="l30">Module Icon</label>
                                            <input type="text" name="icon" class="form-control" placeholder="Type Module Icon Class Name" id="l30">
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="l30">
                                            <input type="checkbox" value="1" name="is_active"> Is Active</label>
                                            
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
                                <a class="k-button k-button-icontext k-grid-edit" href="{{url('Settings/Module/Edit')}}/#=id#"><span class="k-icon k-edit"></span> Edit</a>
                                <a class="k-button k-button-icontext k-grid-delete" onclick="javascript:deleteClick(#=id#);" ><span class="k-icon k-delete"></span> Delete</a>
                            </script>

                            <script type="text/javascript">
                                function deleteClick(id) {
                                    var c = confirm("Do you want to delete?");
                                    if (c === true) {
                                        $.ajax({
                                            type: "POST",
                                            dataType: "json",
                                            url: "Module/Delete",
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
                                                url: "<?=url('Settings/Module/Json')?>",
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
                                                    icon: {type: "string"},
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
                                        {field: "name", title: "Module Name ", width: "80px"},
                                        {field: "icon", title: "Module Icon ", width: "80px"},
                                        {field: "created_at", title: "Created ", width: "100px",},
                                        {
                                            title: "Action", width: "120px",
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