<?php
if (isset($data)) {
    $pageinfo = array("Edit Leave Policy Settings", "Edit Leave Policy Record", "", "SUL");
} else {
    $pageinfo = array("Leave Policy Settings", "Add Leave Policy Record", "", "SUL");
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

                        <a href="{{route('settings.leavePolicy.export.excel')}}" class="fa fa-file-excel-o fa-2x"  title="Export Excel" data-original-title="Export Excel">&nbsp;</a>
                        <a href="{{route('settings.leavePolicy.export.pdf')}}" class="fa fa-file-pdf-o fa-2x"  title="Export Pdf" data-original-title="Export Pdf">&nbsp;</a>
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
                            <form name="gender" action="{{url('Settings/LeavePolicy/Update/'.$data['id'])}}" method="post">
                                {{csrf_field()}}
                                <div class="row">

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

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Leave Title</label>
                                            <input type="text" name="leave_title" class="form-control" value="{{$data['leave_title']}}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Leave Short Code</label>
                                            <input type="text" name="leave_short_code" class="form-control" value="{{$data['leave_short_code']}}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Total Days</label>
                                            <input type="number" name="total_days" class="form-control" value="{{$data['total_days']}}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>
                                                <input
                                                    @if($data['is_applicable_for_all']==1)
                                                    checked="checked"
                                                    @endif
                                                    type="checkbox" value="1" name="is_applicable_for_all"> Is Applicable For All</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>
                                                <input
                                                    @if($data['is_leave_cut_applicable']==1)
                                                    checked="checked"
                                                    @endif
                                                    type="checkbox" value="1" name="is_leave_cut_applicable"> Is Leave Cut Applicable</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>
                                                <input
                                                    @if($data['is_carry_forward']==1)
                                                    checked="checked"
                                                    @endif
                                                    type="checkbox" value="1" name="is_carry_forward" id="is_carry_forward"> Is Carry Forward</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3 is_carry_forward">
                                        <div class="form-group">
                                            <label>Max Carry Forward Days</label>
                                            <input type="number" name="max_carry_forward_days" class="form-control"  value="{{$data['max_carry_forward_days']}}">
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>
                                                <input
                                                    @if($data['is_carry_forward']==1)
                                                    checked="checked"
                                                    @endif
                                                    type="checkbox" value="1" name="is_carry_forward" id="is_carry_forward"> Is Carry Forward</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3 is_carry_forward">
                                        <div class="form-group">
                                            <label>Max Carry Forward Days</label>
                                            <input type="number" name="max_carry_forward_days" class="form-control"  value="{{$data['max_carry_forward_days']}}">
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>
                                                <input
                                                    @if($data['is_document_upload']==1)
                                                    checked="checked"
                                                    @endif
                                                    type="checkbox" value="1" id="is_document_upload" name="is_document_upload"> Is Document Upload</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3 is_document_upload">
                                        <div class="form-group">
                                            <label>Document Upload After Days</label>
                                            <select name="document_upload_after_days" class="form-control">
                                                <option value="">Please Select</option>
                                                @for($i=1; $i<=10; $i++)
                                                <option
                                                    @if($data['document_upload_after_days']==$i)
                                                    selected="selected"
                                                    @endif
                                                    value="{{$i}}">{{$i}}</option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>
                                                <input
                                                @if($data['is_holiday_deduct']==1)
                                                checked="checked"
                                                @endif
                                                type="checkbox" value="1" id="is_holiday_deduct" name="is_holiday_deduct"> Is Holiday/Weekend Deduct</label>
                                        </div>
                                    </div>


                                </div>


                                <div class="form-actions">
                                    <button type="submit"  class="btn btn-primary">Update</button>
                                    <button type="reset" class="btn btn-default">Cancel</button>
                                </div>
                            </form>
                            @else
                            <form name="gender" action="{{url('Settings/LeavePolicy/Add')}}" method="post">
                                {{csrf_field()}}
                                <div class="row">
                                    @if(empty($logged_emp_com))
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Company</label>
                                            <select name="company_id" class="form-control">
                                                <option value="">Select Company</option>
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
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Leave Title</label>
                                            <input value="{{ old('leave_title') }}" type="text" name="leave_title" class="form-control" placeholder="Type Leave Title">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Leave Short Code</label>
                                            <input value="{{ old('leave_short_code') }}" type="text" name="leave_short_code" class="form-control" placeholder="Type Leave Short Code">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Total Days</label>
                                            <input value="{{ old('total_days') }}" type="number" name="total_days" class="form-control" placeholder="Type Total Days">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>
                                                <input type="checkbox" value="1" name="is_applicable_for_all"> Is Applicable For All</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>
                                                <input type="checkbox" value="1" name="is_leave_cut_applicable"> Is Leave Cut Applicable</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>
                                                <input type="checkbox" value="1" id="is_carry_forward" name="is_carry_forward"> Is Carry Forward</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3 is_carry_forward">
                                        <div class="form-group">
                                            <label>Max Carry Forward Days</label>
                                            <input value="{{ old('max_carry_forward_days') }}" type="number" name="max_carry_forward_days" class="form-control" placeholder="Type Max Carry Forward Days">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>
                                                <input type="checkbox" value="1" id="is_document_upload" name="is_document_upload"> Is Document Upload</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3 is_document_upload">
                                        <div class="form-group">
                                            <label>Document Upload After Days</label>
                                            <select name="document_upload_after_days" class="form-control">
                                                <option value="">Please Select</option>
                                                @for($i=1; $i<=10; $i++)
                                                <option value="{{$i}}">{{$i}}</option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>
                                                <input type="checkbox" value="1" id="is_holiday_deduct" name="is_holiday_deduct"> Is Holiday/Weekend Deduct</label>
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
                        <div class="clearfix"></div>
                        <div class="col-xl-12">
                            <!--Vertical Form Starts Here-->
                            <!-- kendo table code start from here-->
                            <div class="row">
                                <div id="grid" class="col-md-12"></div>
                            </div>
                            <script id="action_template" type="text/x-kendo-template">
                                <a class="k-button k-button-icontext k-grid-edit" href="{{url('Settings/LeavePolicy/Edit')}}/#=id#"><span class="k-icon k-edit"></span> Edit</a>
                                <a style="display:none;" class="k-button k-button-icontext k-grid-delete" onclick="javascript:deleteClick(#=id#);" ><span class="k-icon k-delete"></span> Delete</a>
                            </script>

                            <script type="text/javascript">
                                function deleteClick(id) {
                                var baseUrl = "<?= url('Settings/LeavePolicy/Delete') ?>";
                                        var c = confirm("Do you want to delete?");
                                        if (c === true) {
                                $.ajax({
                                type: "POST",
                                        dataType: "json",
                                        url: baseUrl,
                                        data: {id: id, '_token':'<?= csrf_token() ?>'},
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
                                url: "<?= url('Settings/LeavePolicy/Json') ?>",
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
                                                                leave_title: {type: "string"},
                                                                leave_short_code: {type: "string"},
                                                                total_days: {type: "number"},
                                                                is_applicable_for_all: {type: "boolean"},
                                                                is_leave_cut_applicable: {type: "boolean"},
                                                                is_carry_forward: {type: "boolean"},
                                                                max_carry_forward_days: {type: "number"},
                                                                is_document_upload: {type: "boolean"},
                                                                document_upload_after_days	: {type: "number"},
                                                                is_holiday_deduct: {type: "boolean"},
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
                                        {field: "leave_title", title: "Title", width: "120px"},
                                        {field: "leave_short_code", title: "Code", filterable: false, width: "60px"},
                                        {field: "total_days", title: "Total Days", filterable: false, width: "90px"},
                                        {field: "is_applicable_for_all", title: "Is For All", filterable: false, width: "60px"},
                                        {field: "is_leave_cut_applicable", title: "Is Cut Applicable", filterable: false, width: "100px"},
                                        {field: "is_carry_forward", title: "Carry Forward", filterable: false, width: "90px"},
                                        {field: "max_carry_forward_days", title: "Max Days", filterable: false, width: "60px"},
                                        {field: "is_document_upload", title: "Is Document Upload", filterable: false},
                                        {field: "document_upload_after_days", title: "Needed After Days", filterable: false},
                                        {field: "is_holiday_deduct", title: "Is Holiday Deduct", filterable: false},
                                        {
                                        title: "Action", width: "160px",
                                                template: kendo.template($("#action_template").html())
                                        }
                                        ],
                                });
                                });</script>
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
<script type="text/javascript">
    $(document).ready(function(){
        @if (isset($data))
        @if ($data['is_carry_forward'] == 1)
        $(".is_carry_forward").fadeIn('slow');
        @ else
        $(".is_carry_forward").fadeOut('slow');
        @endif

        @if ($data['is_document_upload '] == 1)
        $(".is_document_upload").fadeIn('slow');
        @ else
        $(".is_document_upload").fadeOut('slow');
        @endif
        @ else
        $(".is_carry_forward").fadeOut('fast');
        $(".is_document_upload").fadeOut('fast');
        $("#is_carry_forward").prop('checked', false);
        $("#is_document_upload").prop('checked', false);
        @endif


        $("input[name='is_carry_forward']").click(function(){
            if (document.getElementById('is_carry_forward').checked)
            {
            $(".is_carry_forward").fadeIn('slow');
            }
            else
            {
            $(".is_carry_forward").fadeOut('fast');
            }
            });
                    $("input[name='is_document_upload']").click(function(){
            if (document.getElementById('is_document_upload').checked)
            {
            $(".is_document_upload").fadeIn('slow');
            }
            else
            {
            $(".is_document_upload").fadeOut('fast');
            }
          });
    });

</script>
@endsection
