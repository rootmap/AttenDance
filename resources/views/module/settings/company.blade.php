<?php
if(isset($data))
{
    $pageinfo=array("Edit Company Settings","Edit Company Record","","SUL");
}
else
{
    $pageinfo=array("Company Settings","Add Company Record","","SUL");
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
                            <form enctype="multipart/form-data" name="Company" action="{{url('Settings/Company/Update/'.$data['id'])}}" method="post">
                                {{csrf_field()}}
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="l30">Company Title</label>
                                            <input type="text" name="name" class="form-control" placeholder="Type Company Name" id="l30" value="{{$data['name']}}">
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="l30">Address</label>
                                            <input type="text" name="address" class="form-control" placeholder="Type Address" id="l30" value="{{$data['address']}}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="l30">Phone</label>
                                            <input type="text" name="phone" class="form-control" placeholder="Type Phone" id="l30" value="{{$data['phone']}}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="l30">Company Email</label>
                                            <input type="text" name="companyemail" class="form-control" placeholder="Type Email" id="l30" value="{{$data['company_email']}}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="l30">HR Email</label>
                                            <input type="text" name="HRemail" class="form-control" placeholder="Type HR_Email" id="l30" value="{{$data['hr_email']}}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="l30">Leave Email</label>
                                            <input type="text" name="Leaveemail" class="form-control" placeholder="Type Leave_Email" id="l30" value="{{$data['leave_email']}}">
                                        </div>
                                    </div>
                                     <div class="col-lg-6">
                                        <div class="form-group">

                                            <label for="l30">Change Company Logo </label>
                                            <input type="file" name="logo" class="form-control" id="l30">
                                            @if(!empty($data['company_logo']))
                                                <img  style="max-height:100px; margin-top: 10px;" src="{{url('upload/company_logo/'.$data['company_logo'])}}" class="img-responsive">
                                            @endif
                                            <input type="hidden" name="ex_logo" value="{{$data['company_logo']}}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="l30">Employee Code Length</label>
                                            <input type="number" name="emp_code_length" class="form-control" placeholder="Type Employee Code Length" id="l30" value="{{$data['emp_code_length']}}">
                                        </div>
                                    </div>


                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label  for="l30">
                                                <input
                                                @if($data['is_active']==1)
                                                    checked="checked"
                                                @endif

                                                type="checkbox" value="1" name="is_active" id="is_active" />
                                                Is Company Active
                                            </label>
                                        </div>
                                    </div>





                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label  for="l30">
                                                <input
                                                @if($data['is_company_staffgrade']==1)
                                                    checked="checked"
                                                @endif

                                                type="checkbox" value="1" name="is_company_staffgrade" id="is_company_staffgrade" />
                                                Is Staff Grade Active
                                            </label>
                                    </div>
                                    </div>

                                    <div class="col-lg-3">
                                    <div class="form-group">
                                            <label class="" onclick="prefixis()"  for="l30">
                                                <input
                                                @if($data['is_company_prefix']==1)
                                                    checked="checked"
                                                @endif

                                                type="checkbox" value="1" name="is_company_prefix" id="is_company_prefix" />
                                                Is Company Prefix Active
                                            </label>

                                        </div>
                                    </div>





                                    <div class="col-lg-6 is_company_prefix">
                                        <div class="form-group">
                                            <label for="l30">
                                                Company Prefix
                                            </label>
                                            <input type="text" name="company_prefix" id="company_prefix" class="form-control" placeholder="Type Company Prefix" id="l30" value="{{$data['company_prefix']}}">
                                        </div>
                                    </div>

                                </div>




                                <div class="form-actions">
                                    <button type="submit"  class="btn btn-primary">Update</button>
                                    <button type="reset" class="btn btn-default">Cancel</button>
                                </div>
                            </form>
                            @else
							<form name="Company" enctype="multipart/form-data" action="{{url('Settings/Company/Add')}}" method="post">
                                {{csrf_field()}}
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="l30">Company Title</label>
                                            <input value="{{ old('name') }}" type="text" name="name" class="form-control" placeholder="Type Company Name" id="l30">
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="l30">Address</label>
                                            <input value="{{ old('address') }}" type="text" name="address" class="form-control" placeholder="Type Address" id="l30">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="l30">Phone</label>
                                            <input value="{{ old('phone') }}" type="text" name="phone" class="form-control" placeholder="Type Phone" id="l30">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="l30">Company Email</label>
                                            <input value="{{ old('companyemail') }}" type="text" name="companyemail" class="form-control" placeholder="Type Email" id="l30">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="l30">HR Email</label>
                                            <input value="{{ old('HRemail') }}" type="text" name="HRemail" class="form-control" placeholder="Type HR_Email" id="l30">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="l30">Leave Email</label>
                                            <input value="{{ old('Leaveemail') }}" type="text" name="Leaveemail" class="form-control" placeholder="Type Leave_Email" id="l30">
                                        </div>
                                    </div>
                                     <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="l30">Company Logo</label>
                                            <input type="file" name="logo" class="form-control" id="l30">
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="l30">Employee Code Length</label>
                                            <input value="{{ old('emp_code_length') }}" type="number" name="emp_code_length" class="form-control" placeholder="Type Employee Code Length" id="l30">
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label  for="l30">
                                                <input type="checkbox" name="is_active" id="is_active" />
                                                Is Company Active
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label  for="l30">
                                                <input type="checkbox" name="is_company_staffgrade" id="is_company_staffgrade" />
                                                Is Staff Grade Active
                                            </label>
                                    </div>
                                    </div>

                                    <div class="col-lg-3">
                                    <div class="form-group">
                                            <label class="" onclick="prefixis()"  for="l30">
                                                <input type="checkbox" name="is_company_prefix" id="is_company_prefix" />
                                                Is Company Prefix Active
                                            </label>

                                        </div>
                                    </div>

                                    <div class="col-lg-6 is_company_prefix">
                                        <div class="form-group">
                                            <label for="l30">
                                                Company Prefix
                                            </label>
                                            <input type="text" name="company_prefix" id="company_prefix" class="form-control" placeholder="Type Company Prefix" id="l30">
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
                                <a class="k-button k-button-icontext k-grid-edit" href="{{url('Settings/Company/Edit')}}/#=id#"><span class="k-icon k-edit"></span> Edit</a>
                                <a class="k-button k-button-icontext k-grid-delete" onclick="javascript:deleteClick(#=id#);" ><span class="k-icon k-delete"></span> Delete</a>
                            </script>

                            <script type="text/javascript">
                                function deleteClick(id) {
                                    var c = confirm("Do you want to delete?");
                                    if (c === true) {
                                        $.ajax({
                                            type: "POST",
                                            dataType: "json",
                                            url: "Company/Delete",
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
                                                url: "<?=url('Settings/Company/Json')?>",
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
                                                    emp_code_length: {type: "number"},
                                                    company_prefix: {type: "string"},
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
                                        filterable: false,
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
                                        {field: "name", title: "Company Name ", width: "80px"},
                                        {field: "phone", title: "Phone", width: "80px"},
                                        {field: "company_email", title: "Company Email ", width: "100px"},
                                        {field: "emp_code_length", title: "Length ", width: "40px"},
                                        {field: "company_prefix", title: "Prefix ", width: "40px"},
                                        {field: "is_active", title: "Status ", width: "40px"},
                                        {title: "Created At", width: "80px", template: "#= kendo.toString(kendo.parseDate(created_at, 'yyyy-MM-dd'), 'dd/MM/yyyy') #"},
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
    <script>
       function prefixis()
       {
            //alert("working");

            @if(isset($data))

            if(document.getElementById("is_company_prefix").checked)
            {


                        $(".is_company_prefix").fadeIn('slow');
                        $("#company_prefix").focus();



            }
            else
            {
                $(".is_company_prefix").fadeOut('fast');
            }

            @else

            if(document.getElementById("is_company_prefix").checked)
            {

                    var c=confirm("Please set your company prefix.");
                    if(c)
                    {
                        $(".is_company_prefix").fadeIn('slow');
                        $("#company_prefix").focus();
                    }
                    else
                    {
                        $("#is_company_prefix").prop('checked', false);
                        $(".is_company_prefix").fadeOut('fast');
                    }


            }
            else
            {
                $(".is_company_prefix").fadeOut('fast');
            }

            @endif
       }

       prefixis();
    </script>
    @include('include.coreKendo')

@endsection
