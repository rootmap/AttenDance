<?php
if (isset($data)) {
    $pageinfo = array("Edit Swap Loop Settings", "Edit Swap Loop Record", "", "SUL");
} else {
    $pageinfo = array("Swap Loop Settings", "Add New Swap Loop Record", "", "SUL");
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
                            <form name="gender" action="{{url('Settings/Swap/Loop/Update/'.$data['id'])}}" method="post">
                                {{csrf_field()}}
                                <div class="row">

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Company</label>
                                            <select name="company_id" class="form-control">
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
                                    
                                    
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Shift 1st Loop</label>
                                            <select name="shift_start" class="form-control">
                                                <option value="">Please Select</option>
                                                @foreach($shift as $sft)
                                                <option 
												@if($data['shift_start']==$sft->id)
													selected="selected"
												@endif 
												value="{{$sft->id}}">{{$sft->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
									
									<div class="col-md-3">
                                        <div class="form-group">
                                            <label>Shift 2nd Loop</label>
                                            <select name="shift_end" class="form-control">
                                                <option value="">Please Select</option>
                                                @foreach($shift as $sft)
                                                <option 
												@if($data['shift_end']==$sft->id)
													selected="selected"
												@endif 
												value="{{$sft->id}}">{{$sft->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
									
									<div class="col-md-3">
                                        <div class="form-group">
                                            <label>Swap After Days</label>
                                            <select name="swap_after_days" class="form-control">
                                                <option value="">Please Select</option>
                                                @for($i=1; $i<=60; $i++)
                                                <option 
												@if($data['swap_after_days']==$i)
													selected="selected"
												@endif 
												value="{{$i}}">{{$i}}</option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>
									
									<div class="col-md-3">
                                        <div class="form-group">
                                            <label>Shift Swap Start Day</label>
                                            <select name="start_day_name" class="form-control">
                                                <option value="">Please Select</option>
                                                @foreach($WeekDays as $dn)
                                                <option 
												@if($data['start_day_name']==$dn)
													selected="selected"
												@endif 
												value="{{$dn}}">{{$dn}}</option>
                                                @endforeach
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
                            <form name="SwapLoop" action="{{url('Settings/Swap/Loop/Add')}}" method="post">
                                {{csrf_field()}}
                                <div class="row">
                                    
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
                                    
                                    
                                    <div class="col-md-3 is_document_upload">
                                        <div class="form-group">
                                            <label>Shift 1st Loop</label>
                                            <select name="shift_start" class="form-control">
                                                <option value="">Please Select</option>
                                                @foreach($shift as $sft)
                                                <option value="{{$sft->id}}">{{$sft->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
									
									<div class="col-md-3 is_document_upload">
                                        <div class="form-group">
                                            <label>Shift 2nd Loop</label>
                                            <select name="shift_end" class="form-control">
                                                <option value="">Please Select</option>
                                                @foreach($shift as $sft)
                                                <option value="{{$sft->id}}">{{$sft->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
									
									<div class="col-md-3">
                                        <div class="form-group">
                                            <label>Swap After Days</label>
                                            <select name="swap_after_days" class="form-control">
                                                <option value="">Please Select</option>
                                                @for($i=1; $i<=60; $i++)
                                                <option value="{{$i}}">{{$i}}</option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>
									
									<div class="col-md-3">
                                        <div class="form-group">
                                            <label>Shift Swap Start Day</label>
                                            <select name="start_day_name" class="form-control">
                                                <option value="">Please Select</option>
                                                @foreach($WeekDays as $dn)
                                                <option value="{{$dn}}">{{$dn}}</option>
                                                @endforeach
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
                        <div class="clearfix"></div>
                        <div class="col-xl-12">
                            <!--Vertical Form Starts Here-->
                            <!-- kendo table code start from here-->
                            <div class="row">
                                <div id="grid" class="col-md-12"></div>
                            </div>
                            <script id="action_template" type="text/x-kendo-template">
                                <a class="k-button k-button-icontext k-grid-edit" href="{{url('Swap/Loop')}}/#=shift_start_id#/#=shift_end_id#"><span class="k-icon k-edit"></span> Process Auto : Date</a>
								<a class="k-button k-button-icontext k-grid-edit" href="{{url('Settings/Swap/Loop/Edit')}}/#=id#"><span class="k-icon k-edit"></span> Edit</a>
                                <a class="k-button k-button-icontext k-grid-delete" onclick="javascript:deleteClick(#=id#);" ><span class="k-icon k-delete"></span> Delete</a>
                            </script>

                            <script type="text/javascript">
                                function deleteClick(id) {
                                var baseUrl = "<?= url('Settings/Swap/Loop/Delete') ?>";
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
                                url: "<?= url('Settings/Swap/Loop/Json') ?>",
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
														company_name: {type: "string"},
														shift_start: {type: "string"},
														shift_start_id: {type: "number"},
														shift_end_id: {type: "number"},
														shift_end: {type: "string"},
														swap_after_days: {type: "number"},
														start_day_name: {type: "string"},
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
                                        {field: "id", title: "#", width: "40px"},
                                        {field: "company_name", title: "Company Name"},
                                        {field: "shift_start", title: "Shift Start"},
                                        {field: "shift_end", title: "Shift End"},
                                        {field: "swap_after_days", title: "Shift Swap After Days"},
                                        {field: "start_day_name", title: "Start Day Name"},
                                        {field: "created_at", title: "Created at"},
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
