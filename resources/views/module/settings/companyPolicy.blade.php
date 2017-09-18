<?php
if (isset($data)) {
    $pageinfo = array("Edit Company Policy Settings", "Edit Company Policy Record", "", "SUL");
} else {
    $pageinfo = array("Company Policy Settings", "Add Company Policy Record", "", "SUL");
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
                            <form name="COMPCY" action="{{url('Settings/Company-Policy/Update/'.$data['id'])}}" method="post">
                                {{csrf_field()}}
                                <div class="row">

                                    
                                    
                                    
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="l30">Policy Heading</label>
                                            <input type="text" name="policy_heading" class="form-control" placeholder="Type Policy Heading" id="l30" value="{{$data['policy_heading']}}">
                                        </div>
                                    </div>
									
									<div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="l30">Policy Publish Date</label>
                                            <input type="text" name="policy_publish_date" class="form-control" placeholder="Type Day Short Code" id="l30" value="{{$data['policy_publish_date']}}">
                                        </div>
                                    </div>

                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="l30">Policy Description</label>
                                            <textarea type="text" name="policy_description" class="form-control summernote" placeholder="Type Description / Detail" id="l30">{{$data['policy_description']}}</textarea>
                                        </div>
                                    </div>

									

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <strong>
												<input type="checkbox" name="policy_status" value="1" id="policy_status" <?php if ($data['policy_status'] == "1") { ?> checked="checked" <?php } ?> /> 
                                                Is Active Policy
											</strong>
                                        </div>
                                    </div>
                                </div>


                                <div class="form-actions">
                                    <button type="submit"  class="btn btn-primary">Update</button>
                                    <button type="reset" class="btn btn-default">Cancel</button>
                                </div>
                            </form>
                            @else
                            <form name="Daytype" action="{{url('Settings/Company-Policy/Add')}}" method="post">
                                {{csrf_field()}}
                                <div class="row">
                                    
                                  


                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="l30">Policy Heading</label>
                                            <input type="text" name="policy_heading" class="form-control" value="{{old('policy_heading')}}" placeholder="Type Heading" id="l30" />
                                        </div>
                                    </div>
									
									<div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="l30">Policy Publish Date</label>
                                            <input type="text" name="policy_publish_date" class="form-control" value="{{old('policy_publish_date')}}" placeholder="Type/Select Published Date" id="l30">
                                        </div>
                                    </div>

                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="l30">Policy Description</label>
                                            <textarea type="text" name="policy_description" class="form-control summernote" placeholder="Type Description / Detail" id="l30">{{old('policy_description')}}</textarea>
                                        </div>
                                    </div>

									

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <strong>
												<input type="checkbox" name="policy_status" value="1" id="policy_status" /> 
                                                Is Active Policy
											</strong>
											
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
                                <a class="k-button k-button-icontext k-grid-edit" href="{{url('Settings/Company-Policy/Edit')}}/#=id#"><span class="k-icon k-edit"></span> Edit</a>
                                <a class="k-button k-button-icontext k-grid-delete" onclick="javascript:deleteClick(#=id#);" ><span class="k-icon k-delete"></span> Delete</a>
                            </script>

                            <script type="text/javascript">
                                function deleteClick(id) {
                                    var c = confirm("Do you want to delete?");
                                    if (c === true) 
									{
                                        $.ajax({
                                            type: "POST",
                                            dataType: "json",
                                            url: "Company-Policy/Delete",
                                            data: {id: id, '_token': '<?= csrf_token() ?>'},
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
                                                url: "<?= url('Settings/Company-Policy/Json') ?>",
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
                                                    policy_heading: {type: "string"},
                                                    policy_publish_date: {type: "string"},
                                                    policy_status: {type: "boolean"},
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
                                            pageSizes:[10, 20, 50, 100, 200, 400]
                                        },
                                        sortable: true,
                                        groupable: true,
                                        columns: [
                                            {field: "id", title: "#", width: "40px", filterable: false},
                                            {field: "policy_heading", title: "Heading ", width: "80px"},
                                            {field: "policy_publish_date", title: "Posted ", width: "80px"},
                                            {field: "policy_status", title: "Is Active", width: "80px"},
                                            {field: "created_at", title: "Created ", width: "100px", },
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
<link rel="stylesheet" type="text/css" href="{{url('vendors/summernote/dist/summernote.css')}}">
<script src="{{url('vendors/summernote/dist/summernote.min.js')}}"></script>
<script>
$('.summernote').summernote({
  height: 250   //set editable area's height
});
</script>
<?= MenuPageController::genarateKendoDatePicker(array("policy_publish_date")) ?>
@endsection
