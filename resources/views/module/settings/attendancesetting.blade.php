<?php
if(isset($data))
{
    $pageinfo=array("Edit Attendance Data Upload Settings","Edit Attendance Data Upload Settings","","SUL");
}
else
{
    $pageinfo=array("Attendance Data UploadSettings","Attendance Data Upload Settings","","SUL");
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
                            <form enctype="multipart/form-data" name="Company" action="{{url('Settings/AttendanceSettings/Update/'.$data['id'])}}" method="post">
                                {{csrf_field()}}
                                <div class="row">



                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label  for="l30" onclick="prefixis()"> 
                                                <input 
                                                @if($data['is_manual']==1)
                                                checked="checked"  
                                                @endif
                                                type="checkbox" value="1" name="is_manual" id="is_manual" />
                                                Is Manual
                                            </label>
                                        </div>
                                    </div>

                                    

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="l30"> 
                                                <input 
                                                @if($data['is_automatic']==1)
                                                checked="checked"  
                                                @endif
                                                type="checkbox" value="1" name="is_automatic" id="is_automatic" /> 
                                                Is Automatic
                                            </label>
                                            
                                        </div>
                                    </div>

                                </div>
                                <div class="row">

                                    <div class="col-lg-3 is_manual">
                                        <div class="form-group">
                                            <label for="l30"> Manual Import File Type </label>
                                            <select class="form-control" name="manual_file_type" id="manual_file_type ">
                                                <option value="">Please Select</option>
                                                <option 
                                                @if($data['manual_file_type']=="xls")
                                                selected="selected"  
                                                @endif
                                                value="xls">Excel (xls)</option>
                                                <option 
                                                @if($data['manual_file_type']=="xlsx")
                                                selected="selected"  
                                                @endif
                                                value="xlsx">Excel (xlsx)</option>
                                                <option 
                                                @if($data['manual_file_type']=="csv")
                                                selected="selected"  
                                                @endif
                                                value="csv">Excel (csv)</option>
                                                <option 
                                                @if($data['manual_file_type']=="txt")
                                                selected="selected"  
                                                @endif
                                                value="txt">Text File (txt)</option>
                                            </select> 
                                        </div>
                                    </div>

                                    <div class="col-lg-3 is_txt">
                                        <div class="form-group">
                                            <label for="l30"> Text Data Separator </label>
                                            <select class="form-control" 
                                            name="txt_data_separetor" 
                                            id="txt_data_separetor">
                                            <option value="">Please Select</option>
                                            <option 
                                            @if($data['txt_data_separetor']==";")
                                            selected="selected"  
                                            @endif
                                            value=";">Semicolon (;)</option>
                                            <option 
                                            @if($data['txt_data_separetor']==":")
                                            selected="selected"  
                                            @endif
                                            value=":">Colon (:)</option>
                                            <option 
                                            @if($data['txt_data_separetor']=="#")
                                            selected="selected"  
                                            @endif
                                            value="#">Hash (#)</option>
                                            <option 
                                            @if($data['txt_data_separetor']=="TAB")
                                            selected="selected"  
                                            @endif
                                            value="TAB">TAB (->)</option>
                                        </select> 
                                    </div>
                                </div>




                         </div>
                         <div class="row is_manual">
                                    <div class="col-lg-12">
                                     <table class="table">
                                         <thead>
                                             <tr>
                                                 <th>Machine ID</th>
                                                 <th>Employee Code Without Company Prefix</th>
                                                 <th>Date</th>
                                                 <th>Time</th>
                                             </tr>
                                         </thead>
                                         <tbody>
                                             <tr>
                                                 <td>[Index 0]</td>
                                                 <td>[Index 1]</td>
                                                 <td>[Index 2]</td>
                                                 <td id="is_colon">[Index 3]</td>
                                             </tr>
                                         </tbody>
                                     </table>
                                 </div>
                             </div>




                         <div class="form-actions">
                            <button type="submit"  class="btn btn-primary">Update</button>
                            <button type="reset" class="btn btn-default">Cancel</button>
                        </div>
                    </form>
                    @else
                    <form name="Company" enctype="multipart/form-data" action="{{url('Settings/AttendanceSettings/Add')}}" method="post">
                        {{csrf_field()}}
                        <div class="row">

                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label  for="l30" onclick="prefixis()"> 
                                        <input type="checkbox" name="is_manual" id="is_manual" value="1" />
                                        Is Manual
                                    </label>
                                </div>
                            </div>

                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="l30"> 
                                        <input type="checkbox" name="is_automatic" id="is_automatic" value="1" /> 
                                        Is Automatic
                                    </label>

                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-3 is_manual">
                                <div class="form-group">
                                    <label for="l30"> Manual Import File Type </label>
                                    <select class="form-control" name="manual_file_type" id="manual_file_type ">
                                        <option value="">Please Select</option>
                                        <option value="xls">Excel (xls)</option>
                                        <option value="xlsx">Excel (xlsx)</option>
                                        <option value="csv">Excel (csv)</option>
                                        <option value="txt">Text File (txt)</option>
                                    </select> 
                                </div>
                            </div>

                            <div class="col-lg-3 is_txt">
                                <div class="form-group">
                                    <label for="l30"> Text Data Separator </label>
                                    <select class="form-control" 
                                    name="txt_data_separetor" 
                                    id="txt_data_separetor">
                                    <option value="">Please Select</option>
                                    <option value=";">Semicolon (;)</option>
                                    <option value=":">Colon (:)</option>
                                    <option value="#">Hash (#)</option>
                                    <option value="TAB">TAB (->)</option>
                                </select> 
                            </div>
                        </div>
                    </div>
                    <div class="row is_manual">
                        <div class="col-lg-12">
                         <table class="table">
                             <thead>
                                 <tr>
                                     <th>Machine ID</th>
                                     <th>Employee Code Without Company Prefix</th>
                                     <th>Date</th>
                                     <th>Time</th>
                                 </tr>
                             </thead>
                             <tbody>
                                 <tr>
                                     <td>[Index 0]</td>
                                     <td>[Index 1]</td>
                                     <td>[Index 2]</td>
                                     <td id="is_colon">[Index 3]</td>
                                 </tr>
                             </tbody>
                         </table>
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
                <a class="k-button k-button-icontext k-grid-edit" href="{{url('Settings/AttendanceSettings/Edit')}}/#=id#"><span class="k-icon k-edit"></span> Edit</a>
                <a class="k-button k-button-icontext k-grid-delete" onclick="javascript:deleteClick(#=id#);" ><span class="k-icon k-delete"></span> Delete</a>
            </script>

            <script type="text/javascript">
                function deleteClick(id) {
                    var c = confirm("Do you want to delete?");
                    if (c === true) {
                        $.ajax({
                            type: "POST",
                            dataType: "json",
                            url: "AttendanceSettings/Delete",
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
                                url: "<?=url('Settings/AttendanceSettings/Json')?>",
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
                                    is_manual: {type: "boolean"},
                                    manual_file_type: {type: "string"},
                                    txt_data_separetor: {type: "string"},
                                    is_automatic: {type: "boolean"},
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
                                        {field: "company_id", title: "Company", width: "40px", filterable: false},
                                        {field: "is_manual", title: "Is Manual?", width: "80px"},
                                        {field: "manual_file_type", title: "File Type", width: "80px"},
                                        {field: "txt_data_separetor", title: "Data Separetor", width: "100px"},
                                        {field: "is_automatic", title: "Is Automatic?", width: "40px"},
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

    $('.is_txt').fadeOut('fast');
    @if(isset($data))
    @if($data['manual_file_type']=="txt")
    $('.is_txt').fadeIn('slow');
    @endif
    @endif
    if(document.getElementById("is_manual").checked)
    {
        $(".is_manual").fadeIn('slow');
    }
    else
    {
        $(".is_manual").fadeOut('fast');
    }


    
}

function DefineIndexTable()
{
    var idv=$("select[name='txt_data_separetor']").val();
    if(idv==":")
    {
        $("#is_colon").html("[Index 3][Index 4][Index 5]");
    }
    else
    {
        $("#is_colon").html("[Index 3]");
    }
}

prefixis();
DefineIndexTable();

$(document).ready(function(){

    $("select[name='manual_file_type']").change(function(){
        var file_type=$(this).val();
        if(file_type=="txt")
        {
            $('.is_txt').fadeIn('slow');
        }
        else
        {
            $('.is_txt').fadeOut('slow');
        }
    });

    $("select[name='txt_data_separetor']").change(function(){
        var idv=$(this).val();
        if(idv==":")
        {
            $("#is_colon").html("[Index 3][Index 4][Index 5]");
        }
        else
        {
            $("#is_colon").html("[Index 3]");
        }
    });

});
</script>

@include('include.coreKendo')
@endsection