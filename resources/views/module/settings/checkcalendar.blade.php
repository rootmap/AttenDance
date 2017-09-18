<?php
if (isset($data)) {
    $pageinfo = array("Calendar Records", "Calendar Record", "", "SUL");
} else {
    $pageinfo = array("Check Existing Calendar", "Check Calendar Record", "", "SUL");
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

                            <div class="row">
                                <div  class="col-md-12">
                                    <table id="grid" class="table table-border">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Year</th>
                                                <th>Date</th>
                                                <th>Day Title</th>
                                                <th>Day Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($data as $row)
                                            <tr>
                                                <td>{{$row->id}}</td>
                                                <td>{{$row->year}}</td>
                                                <td>{{$row->date}}</td>
                                                <td>{{$row->day_title}}</td>
                                                <td>{{$row->title}}</td>
                                                <td>
                                                    <a class="k-button k-button-icontext k-grid-edit" href="{{url('Settings/Calendar/Edit')}}/#=id#"><span class="k-icon k-edit"></span> Edit</a>
                                                    <a class="k-button k-button-icontext k-grid-delete" onclick="javascript:deleteClick(#=id#);" ><span class="k-icon k-delete"></span> Delete</a>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            
                            @endif

                            @if(isset($data))
                            <br>
                            <br>
                            <h3> Please Modify Your Search Selection If You Need Different Report </h3>
                            <hr />
                            @endif

                            <form name="Calendar" action="{{url('Settings/calender/show')}}" method="post">
                                {{csrf_field()}}
                                <div class="row">

                                    <style type="text/css" media="screen">
                                        .graddonlabel{
                                           width: 150px;
                                       }
                                   </style>

                                   <div class="col-lg-12">
                                    <div class="form-group col-lg-12">
                                        <label for="l30" class="col-md-12">Select Company</label>
                                        <select class="form-control col-md-5" name="company_id">
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
                                   <div class="input-group col-lg-12" style="margin-bottom: 2% !important;"> 
                                       <span class="input-group-addon graddonlabel">Year </span>

                                       <select id="lunch" class="selectpicker form-control" data-live-search="true" title="Please Select Day Status" name="year">
                                           <option value="">Please Select Year</option>
                                       </select>
                                   </div>
                               </div>

                               <div class="col-lg-6">
                                <div class="input-group col-lg-12" style="margin-bottom: 2% !important;"> 
                                    <span class="input-group-addon graddonlabel">Month </span>

                                    <select id="lunch" class="selectpicker form-control" data-live-search="true" title="Please Select Day Status" name="month">
                                        <option value="">Please Select Month</option>
                                    </select>
                                </div>
                            </div>


                        </div>


                        <div class="form-actions">
                            <button type="submit"  class="btn btn-primary">View Calender Day</button>
                            <button type="reset" class="btn btn-default">Cancel</button>
                        </div>
                    </form>
                    
                    
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

<script>
    $(document).ready(function () {
        $("#grid").kendoGrid({
            toolbar: ["excel"],
            excel: {
                fileName: "Kendo UI Grid Export.xlsx"
            },
            pageable: {
                refresh: true,
                input: true,
                numeric: false,
                pageSize: 30,
                pageSizes: false,
                pageSizes: [30, 60, 365],
            },
            sortable: true,
            groupable: true
        });
    });
</script>
<script type="text/javascript">
    $(document).ready(function(){
        $("select[name='company_id']").change(function(){
            if($(this).val()!='')
            {
                $.post("<?=url('Settings/Calendar/Get/Year/Json')?>",{'company_id':$(this).val(),'_token':'<?=csrf_token()?>'},function(data){ 
                    var total=data.length;
                    if(total!=0)
                    {
                        var str='';
                        str +='<option selected="selected" value="">Select Year</option>';
                        $.each(data,function(index,val){
                                //console.log(index,val);
                                //console.log(val.year);
                            str +='<option value="'+val.year+'">'+val.year+'</option>';
                        });
                            //console.log("Data Found");
                        $("select[name='year']").html(str);
                    }
                        //console.log(data);
                });
            }
        });

        $("select[name='year']").change(function(){
            if($(this).val()!='')
            {
                $.post("<?=url('Settings/Calendar/Get/Month/Json')?>",
                {
                    'company_id':$("select[name='company_id']").val(),
                    'year':$(this).val(),
                    '_token':'<?=csrf_token()?>'
                },
                function(data){ 
                    var total=data.length;
                    if(total!=0)
                    {
                        var str='';
                        str +='<option selected="selected" value="">Select Month</option>';
                        str +='<option value="all">All Month</option>';
                        $.each(data,function(index,val){
                            str +='<option value="'+val.month_number+'">'+val.name+'</option>';
                        });

                        $("select[name='month']").html(str);
                    }

                });
            }
        });

    });
</script>
@endsection