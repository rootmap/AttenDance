<?php
if (isset($data)) {
    $pageinfo = array("Edit Salary Component Settings", "Edit Salary Component Record", "", "SUL");
} else {
    $pageinfo = array("Salary Component Settings", "Add Salary Component Record", "", "SUL");
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
                            <form enctype="multipart/form-data" name="Company" action="{{url('Payroll/SalaryComponent/Update/'.$data['id'])}}" method="post">
                                {{csrf_field()}}
                                <div class="row">
                                    <div class="col-lg-12 px-0">
                                        @if(empty($logged_emp_com))
                                        <div class="col-md-3">
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
                                        @else
                                        <input type="hidden" name="company_id" value="{{$logged_emp_com}}" class="form-control" placeholder="Type Designation Title" id="l30">
                                        @endif
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="l30">Header Title</label>
                                            <input type="text" name="header_title" class="form-control" value="{{$data->header_title}}" placeholder="Type Header Title" id="l30">
                                        </div>
                                    </div>
									<div class="col-lg-1"></div>
                                    <div class="col-lg-5 ">
                                        <label for="l30">Header Display On:</label>
                                        <div class="form-check px-4">
											<div class="form-group">
                                            <label  for="l30">
                                                <input type="checkbox" 
												@if($data->DisplayOnSalarySheet=='Show in salary sheet')
                                                    checked="checked"
                                                @endif 
												name="is_salary_sheet" id="is_salary_sheet" value="1"/>
                                                Show in Salary Sheet?
                                            </label>
                                        </div>
                                        </div>
										<label for="l30">Show In (+/-):</label>
                                        <div class="form-check px-4">
                                            <label class="form-check-label">
                                                <input class="form-check-input"
												@if($data->headerDisplayOn=='+')
                                                    checked="checked"
                                                @endif 
												name="headerDisplayOn" id="exampleRadios2" value="+" type="radio">
                                                Show in Addition?
                                            </label>
                                        </div>
                                        <div class="form-check px-4">
                                            <label class="form-check-label">
                                                <input 
												@if($data->headerDisplayOn=='-')
                                                    checked="checked"
                                                @endif 
												class="form-check-input" name="headerDisplayOn" id="exampleRadios3" value="-" type="radio">
                                                Show in Deduction?
                                            </label>
                                        </div>
                                    </div>
									
									
									
									
                                    
                                    <div class="col-lg-6 mb-4">
                                        <div class="form-group">
                                            <label for="l30">Display Order</label>
                                            <select name="display_order" class="form-control">
                                                <option value="">Please Select Order</option>
                                                <option value="first">At The Begaining</option>
                                                @if(isset($componentList))
                                                @foreach($componentList as $com)
                                                <!--<option value="{{$com->display_order}}">After {{$com->header_title}}</option>-->
                                                <option <?php if ($data->display_order == $com->display_order) { ?> selected="selected"  <?php } ?> value="{{$com->display_order}}">After {{$com->header_title}}</option>
                                                @endforeach
                                                @endif
                                                <option value="last">After All Field</option>

                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6"></div>

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <!--                                            <label  for="l30">
                                                                                            <input type="checkbox" name="is_monthly" id="is_active" />
                                                                                            Is Monthly (Yes)
                                                                                        </label>-->
                                            <label>
                                                <input
                                                    @if($data->is_monthly==1)
														checked="checked"
                                                    @endif
                                                    type="checkbox" value="1" name="is_monthly"> Is Monthly (Yes)</label>
                                        </div>
                                    </div>
                                    
									
									<div class="col-lg-3">
                                        <div class="form-group">
                                            <label  for="l30">
                                                <input type="checkbox" 
												@if($data->is_calculative==1)
                                                    checked="checked"
                                                @endif 
												name="is_calculative" id="is_calculative" value="1" />
                                                Is Calculative
                                            </label>
                                        </div>
                                    </div>
									
									<div class="col-lg-3">
                                        <div class="form-group">
                                            <label  for="l30">
                                                <input 
												@if($data->is_gross==1)
                                                    checked="checked"
                                                @endif 
												type="checkbox" name="is_gross" id="is_gross" value="1" />
                                                Is Gross
                                            </label>
                                        </div>
                                    </div>
									
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label>
                                                <input
                                                    @if($data->is_optional==1)
														checked="checked"
                                                    @endif 
                                                    type="checkbox" value="1" name="is_optional"> Is Optional (Yes)</label>
                                        </div>
                                    </div>
                                </div>




                                <div class="form-actions">
                                    <button type="submit"  class="btn btn-primary">Update</button>
                                    <button type="reset" class="btn btn-default">Cancel</button>
                                </div>
                            </form>
                            @else
                            <form name="SalaryComponent" enctype="multipart/form-data" action="{{url('Payroll/SalaryComponent/Add')}}" method="post">
                                {{csrf_field()}}
                                <div class="row">
                                    <div class="col-lg-12 px-0">
                                        @if(empty($logged_emp_com))
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label for="l30">Company Name</label>
                                                <select class="form-control" id="company" name="company_id">
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
                                        <input type="hidden" name="company_id" value="{{$logged_emp_com}}" class="form-control" placeholder="Type Designation Title" id="l30">
                                        @endif
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="l30">Header Title</label>
                                            <input type="text" name="header_title" class="form-control" placeholder="Type Header Title" id="l30">
                                        </div>
                                    </div>
                                    <div class="col-lg-1"></div>
                                    <div class="col-lg-5 ">
                                        <label for="l30">Header Display On:</label>
                                        <div class="form-check px-4">
											<div class="form-group">
                                            <label  for="l30">
                                                <input type="checkbox" name="is_salary_sheet" id="is_salary_sheet" value="1"/>
                                                Show in Salary Sheet?
                                            </label>
                                        </div>
                                        </div>
										<label for="l30">Show In (+/-):</label>
                                        <div class="form-check px-4">
                                            <label class="form-check-label">
                                                <input class="form-check-input" name="headerDisplayOn" id="exampleRadios2" value="+" type="radio">
                                                Show in Addition?
                                            </label>
                                        </div>
                                        <div class="form-check px-4">
                                            <label class="form-check-label">
                                                <input class="form-check-input" name="headerDisplayOn" id="exampleRadios3" value="-" type="radio">
                                                Show in Deduction?
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 mb-4">
                                        <div class="form-group">
                                            <label for="l30">Display Order</label>
                                            <select name="display_order" class="form-control">
                                                <option value="">Please Select Order</option>
                                                <option value="first">At The Begaining</option>
                                                @if(isset($componentList))
                                                @foreach($componentList as $com)
                                                <option value="{{$com->id}}">After {{$com->header_title}}</option>

                                                @endforeach
                                                @endif
                                                <option value="last">After All Field</option>

                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6"></div>
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label  for="l30">
                                                <input type="checkbox" name="is_monthly" id="is_monthly" value="1"/>
                                                Is Monthly (Yes)
                                            </label>
                                        </div>
                                    </div>
									
									<div class="col-lg-3">
                                        <div class="form-group">
                                            <label  for="l30">
                                                <input type="checkbox" name="is_calculative" id="is_calculative" value="1" />
                                                Is Calculative
                                            </label>
                                        </div>
                                    </div>
									
									<div class="col-lg-3">
                                        <div class="form-group">
                                            <label  for="l30">
                                                <input type="checkbox" name="is_gross" id="is_gross" value="1" />
                                                Is Gross
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label  for="l30">
                                                <input type="checkbox" name="is_optional" id="is_optional" value="1" />
                                                Is Optional (Yes)
                                            </label>
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
                                <a class="k-button k-button-icontext k-grid-edit" href="{{url('Payroll/SalaryComponent/Edit')}}/#=id#"><span class="k-icon k-edit"></span> Edit</a>
                                <a class="k-button k-button-icontext k-grid-delete" onclick="javascript:deleteClick(#=id#);" ><span class="k-icon k-delete"></span> Delete</a>
                            </script>

                            <script type="text/javascript">
                                function deleteClick(id) {
                                    var c = confirm("Do you want to delete?");
                                    if (c === true) {
                                        $.ajax({
                                            type: "POST",
                                            dataType: "json",
                                            url: "SalaryComponent/Delete",
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
                                                url: "<?= url('Payroll/SalaryComponent/Json') ?>",
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
                                                    company: {type: "string"},
                                                    header_title: {type: "string"},
                                                    headerDisplayOn: {type: "string"},
                                                    is_monthly: {type: "boolean"},
                                                    is_optional: {type: "boolean"},
                                                    is_gross: {type: "boolean"},
                                                    is_calculative: {type: "boolean"},
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
                                            //{field: "name", title: "Company Name ", width: "80px"},
                                            {field: "header_title", title: "Header", width: "80px"},
                                            {field: "headerDisplayOn", title: "Display On ", width: "100px"},
                                            {field: "is_monthly", title: "Monthly?", width: "40px"},
                                            {field: "is_optional", title: "Optional?", width: "40px"},
											{field: "is_gross", title: "Gross?", width: "40px"},
											{field: "is_calculative", title: "Calculative?", width: "40px"},
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

        @if (isset($data))

                if (document.getElementById("is_company_prefix").checked)
        {


            $(".is_company_prefix").fadeIn('slow');
            $("#company_prefix").focus();



        }
        else
        {
            $(".is_company_prefix").fadeOut('fast');
        }

        @ else

                if (document.getElementById("is_company_prefix").checked)
        {

            var c = confirm("Please set your company prefix.");
            if (c)
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
