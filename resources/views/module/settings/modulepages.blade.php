<?php
if(isset($data))
{
    $pageinfo=array("Edit System Module Pages Info","Edit System Module Pages Record","","SUL");
}
else
{
    $pageinfo=array("System Module Pages Info","Add System Module Pages Record","","SUL");
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

                        <a href="{{route('settings.modulepages.export.excel')}}" class="fa fa-file-excel-o fa-2x"  title="Export Excel" data-original-title="Export Excel">&nbsp;</a>
                        <a href="{{route('settings.modulepages.export.pdf')}}" class="fa fa-file-pdf-o fa-2x"  title="Export Pdf" data-original-title="Export Pdf">&nbsp;</a>  
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
                            @if(isset($data))
                            <form name="ModulePages" action="{{url('Settings/ModulePages/Update/'.$data['id'])}}" method="post">
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
                                <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="l30">Module Name</label>
                                            <select  class="form-control" name="system_module_id">
                                                <option value="">Select Module</option>
                                                @if(isset($module))
                                                @foreach($module as $row)
                                                    <option 
                                                    @if($data['system_module_id']==$row->id) 
                                                        selected="selected" 
                                                    @endif
                                                        value="{{$row->id}}">{{$row->name}}
                                                    </option>
                                                @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="l30">Module Pages Name</label>
                                            <input type="text" name="name" class="form-control" placeholder="Type Module Pages Name" id="l30" value="{{$data['name']}}">
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
							<form name="ModulePages" action="{{url('Settings/ModulePages/Add')}}" method="post">
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
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="l30">Module Name</label>
                                            <select class="form-control" name="system_module_id">
                                                <option value="">Select Module</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="l30">Sub Module Name</label>
                                            <select class="form-control" name="system_sub_module_id">
                                                <option value="">Select Sub Module</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="l30">Choose Routes/Page Link</label>
                                            <select class="form-control" name="link">
                                                <option value="">Select Routes / Path</option>
                                                @foreach($routes as $ro)
                                                    @if($ro->getActionMethod()=="OutTimeIndex" || $ro->getActionMethod()=="index" || $ro->getActionMethod()=="listShow" || $ro->getActionMethod()=="reportShow" || $ro->getActionMethod()=="ShiftSwapLoopIndex")
                                                        <option value="{{$ro->uri}}">{{$ro->uri}}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="l30">Module Pages Name</label>
                                            <input type="text" name="name" class="form-control" placeholder="Type Module Pages Name" id="l30">
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
                        <div class="col-xl-12">
                            <!--Vertical Form Starts Here-->
                            <!-- kendo table code start from here-->
                            <div class="row">
                                <div id="grid" class="col-md-12"></div>
                            </div>
                            <script id="action_template" type="text/x-kendo-template">
                                <a class="k-button k-button-icontext k-grid-edit" href="{{url('Settings/ModulePages/Edit')}}/#=id#"><span class="k-icon k-edit"></span> Edit</a>
                                <a class="k-button k-button-icontext k-grid-delete" onclick="javascript:deleteClick(#=id#);" ><span class="k-icon k-delete"></span> Delete</a>
                            </script>

                            <script type="text/javascript">
                                $(document).ready(function(){
                                    $("select[name='company_id']").change(function(){
                                        if($(this).val()!='')
                                        {
                                            $.post("<?=url('Settings/SubModule/Get/Module/Json')?>",{'company_id':$(this).val(),'_token':'<?=csrf_token()?>'},function(data){ 
                                                var total=data.length;
                                                if(total!=0)
                                                {
                                                    var str='';
                                                    str +='<option selected="selected" value="">Select Module</option>';
                                                    $.each(data,function(index,val){
                                                            //console.log(index,val);
                                                            //console.log(val.year);
                                                        str +='<option value="'+val.id+'">'+val.name+'</option>';
                                                    });
                                                        //console.log("Data Found");
                                                    $("select[name='system_module_id']").html(str);
                                                }
                                                    //console.log(data);
                                            });
                                        }
                                    });


                                    $("select[name='system_module_id']").change(function(){
                                        if($(this).val()!='')
                                        {
                                            $.post("<?=url('Settings/ModulePages/Get/SubModule/Json')?>",
                                            {
                                                'company_id':$("select[name='company_id']").val(),
                                                'system_module_id':$(this).val(),
                                                '_token':'<?=csrf_token()?>'
                                            },
                                            function(data){ 
                                                var total=data.length;
                                                if(total!=0)
                                                {
                                                    var str='';
                                                    str +='<option selected="selected" value="">Select Sub Module</option>';
                                                    $.each(data,function(index,val){
                                                        str +='<option value="'+val.id+'">'+val.name+'</option>';
                                                    });

                                                    $("select[name='system_sub_module_id']").html(str);
                                                }

                                            });
                                        }
                                    });

                                    

                                });
                            </script>

                            <script type="text/javascript">
                                function deleteClick(id) {
                                    var c = confirm("Do you want to delete?");
                                    if (c === true) {
                                        $.ajax({
                                            type: "POST",
                                            dataType: "json",
                                            url: "ModulePages/Delete",
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
                                                url: "<?=url('Settings/ModulePages/Json')?>",
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
                                                    sub_module_name:{type: "string"},
                                                    link:{type: "string"},
                                                    is_active:{type: "boolean"},
                                                    module_name:{type: "string"},
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
                                        {field: "name", title: "Module Pages Name ", width: "100px"},
                                        {field: "link", title: "Page Link", width: "80px"},
                                        {field: "sub_module_name", title: "Sub-Module Name ", width: "80px"},
                                        {field: "module_name", title: "Module Name ", width: "80px"},
                                        
                                        {field: "is_active", title: "Is Active ?", width: "60px"},
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