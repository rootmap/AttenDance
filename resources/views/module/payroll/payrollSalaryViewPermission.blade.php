<?php
if (isset($data)) {
    $pageinfo = array("Edit Sallary View Permission Settings", "Edit Sallary View Permission Record", "", "SUL");
} else {
    $pageinfo = array("Add Sallary View Permission Settings", "Sallary View Permission Settings", "", "SUL");
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
                            
                            <form name="Branch" action="{{url('Payroll/SalaryViewPermissionSettings/Add')}}" method="post">
                                {{csrf_field()}}
                                <div class="row">
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="l30">Type Employee Code<span class="after" style="color:#EF5F5F"> *</span></label>
                                            <input type="text" class="form-control" name="emp_code" id="emp_code" placeholder="Employee Code"> 
											
											</div>
											
                                    </div>
									<div class="col-lg-3">
										<div class="form-group">
										 <label for="l30">&nbsp;</label>
										<button type="button" id="emp_code_generate" style="margin-top:27px;" class="btn btn-info" name="generate">Generate</button>
										</div>
									</div>

                                    <div class="col-md-12 emp_details">

                                    </div>

									

                                    <div class="col-lg-12">
                                        <table class="table table-bordered">



                                            <thead class="thead-inverse">
                                                <tr>
                                                    <th>
                                                        <label><input type="checkbox" name="selectAll" value="1"/> Select All Staff Grades</label>
                                                    </th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                <tr>
                                                    <td>
                                                        @if(isset($staffgrade))
                                                        @foreach($staffgrade as $rows)
                                                        <label class="btn btn-success mr-2 mb-2">
                                                            <input type="checkbox" class="staff_grade" name="staff_grade[]" value="{{$rows->id}}"> {{$rows->name}} 
                                                        </label>
                                                        @endforeach
                                                        @endif
                                                    </td>
                                                </tr>
                                            </tbody>


                                        </table>
                                    </div>
									
									<div class="col-md-12 emp_details_staff_grade"></div>


                                </div>


                                <div class="form-actions">
                                    <button type="submit"  class="btn btn-primary">Create</button>
                                    <button type="reset" class="btn btn-default">Cancel</button>
                                </div>
                            </form>
                            
                            <!--Vertical Form Ends Here-->
                        </div>
                        <div class="clearfix"></div>
                        

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
    $("input[name='selectAll']").change(function (e) {
        if (!$(this).is(":checked")) {
            $('.staff_grade').prop('checked', false);
        } else {
            $('.staff_grade').prop('checked', true);
        }

    });



//For Employee Details Showing On Select Employee
    $("#emp_code_generate").click(function () {

        var emp_code = $("#emp_code").val();
        if (emp_code == '')
        {
            emp_code = 0;
        }
        //alert(emp_code);

		$.post("<?= url('Payroll/SalaryViewPermissionSettings/Json') ?>", {'emp_code': emp_code, '_token': '<?= csrf_token() ?>'}, function (data) {
                var str = '';
				
				str += '<section class="card">' +
                            '<div class="card-header bg-faded">' +
                            '<span class="cat__core__title">' +
                            '  <h5 class="font-weight-bold"><i class="fa fa-briefcase text-primary" aria-hidden="true"></i> Existing Staff Grades</h5>' +
                            ' </span>' +
                            ' </div>' +
                            '  <div class="card-block">' +
                            ' <div class="row">' +
                            ' <div class="col-xs-center col-lg-12 col-md-12 col-xs-12">';
                
				str +=data+'</div>' +
                            '</div>' +
                            '</div>' +
                            '</section>';


                $(".emp_details_staff_grade").html(str);
            
            
        });

        $.post("<?= url('Jobcard/GetEpmloyeedetails') ?>", {'emp_code': emp_code, '_token': '<?= csrf_token() ?>'}, function (data) {
            var total = data.length;

            if (total != 0)
            {
                var str = '';

                $.each(data, function (index, val) {
                    // console.log(val.emp_email);
                    var name = '';
                    if (typeof (val.emp_name) == "undefined" || val.emp_name == null) {
                        name = 'Not Defined';
                    } else {
                        name = val.emp_name;
                    }
                    var phone = '';
                    if (typeof (val.emp_phone) == "undefined" || val.emp_phone == null) {
                        phone = 'xxxxxxxxxxx';
                    } else {
                        phone = val.emp_phone;
                    }
                    var job_location = '';
                    if (typeof (val.emp_job_location) == "undefined" || val.emp_job_location == null) {
                        job_location = 'Not Defined';
                    } else {
                        job_location = val.emp_phone;
                    }
                    var designation = '';
                    if (typeof (val.emp_designation) == "undefined" || val.emp_designation == null) {
                        designation = 'Not Defined';
                    } else {
                        designation = val.emp_phone;
                    }
                    var section = '';
                    if (typeof (val.emp_section) == "undefined" || val.emp_section == null) {
                        section = 'Not Defined';
                    } else {
                        section = val.emp_section;
                    }
                    var department = '';
                    if (typeof (val.emp_designation) == "undefined" || val.emp_designation == null) {
                        department = 'Not Defined';
                    } else {
                        department = val.emp_designation;
                    }
                    var supervisor = '';
                    if (typeof (val.emp_supervisor) == "undefined" || val.emp_supervisor == null) {
                        supervisor = 'Not Defined';
                    } else {
                        supervisor = val.emp_supervisor;
                    }


                    str += '<section class="card">' +
                            '<div class="card-header bg-faded">' +
                            '<span class="cat__core__title">' +
                            '  <h5 class="font-weight-bold"><i class="fa fa-briefcase text-primary" aria-hidden="true"></i> Employee Detail</h5>' +
                            ' </span>' +
                            ' </div>' +
                            '  <div class="card-block">' +
                            ' <div class="row">' +
                            ' <div class="col-xs-center col-lg-12 col-md-12 col-xs-12">' +
                            ' <table class="table">' +
                            '  <tbody>' +
                            ' <tr>' +
                            '  <td>' +
                            '  <i class="fa fa-address-book text-primary" aria-hidden="true"></i>' +
                            ' Name:' +
                            ' </td>' +
                            ' <td>' +
                            ' <span class="font-weight-bold">' + name + '</span>' +
                            ' </td>' +
                            ' <td>' +
                            '    <i class="fa fa-cubes text-primary" aria-hidden="true"></i>' +
                            '   Email:' +
                            ' </td>' +
                            ' <td>' +
                            '     <span class="font-weight-bold">' + val.emp_email + '</span>' +
                            ' </td>' +
                            ' <td>' +
                            '     <i class="fa fa-cubes text-primary" aria-hidden="true"></i>' +
                            '    Phone:' +
                            ' </td>' +
                            '  <td>' +
                            '   <span class="font-weight-bold">' + phone + '</span>' +
                            ' </td>' +
                            ' </tr>' +
                            ' <tr>' +
                            '   <td>' +
                            '    <i class="fa fa-building text-primary" aria-hidden="true"></i>' +
                            '    Company:' +
                            ' </td>' +
                            ' <td>' +
                            '    <span class="font-weight-bold">' + val.emp_company + '</span>' +
                            ' </td>' +
                            '  <td>' +
                            '      <i class="fa fa-address-book text-primary" aria-hidden="true"></i>' +
                            '     Designation:' +
                            ' </td>' +
                            ' <td>' +
                            '     <span class="font-weight-bold">' + department + '</span>' +
                            ' </td>' +
                            '  <td>' +
                            '      <i class="fa fa-cubes text-primary" aria-hidden="true"></i>' +
                            '     Department:' +
                            ' </td>' +
                            '  <td>' +
                            '       <span class="font-weight-bold">' + val.emp_department + '</span>' +
                            '   </td>' +
                            ' </tr>' +
                            ' <tr>' +
                            '     <td>' +
                            '         <i class="fa fa-cube text-primary" aria-hidden="true"></i>' +
                            '         Section:' +
                            '    </td>' +
                            '    <td>' +
                            '        <span class="font-weight-bold">' + section + '</span>' +
                            '     </td>' +
                            '    <td>' +
                            '       <i class="fa fa-map-marker text-primary" aria-hidden="true"></i>' +
                            '      Job Location:' +
                            '  </td>' +
                            '   <td>' +
                            '       <span class="font-weight-bold">' + job_location + '</span>' +
                            '   </td>' +
                            '   <td>' +
                            '      <i class="fa fa-user text-primary" aria-hidden="true"></i>' +
                            '      Supervisor:' +
                            '  </td>' +
                            '   <td>' +
                            '     <span class="font-weight-bold">' + supervisor + '</span>' +
                            '  </td>' +
                            ' </tr>' +
                            '</tbody>' +
                            '</table>' +
                            '</div>' +
                            '</div>' +
                            '</div>' +
                            '</section>';
//                                                    str += '<p  id="' + val.emp_code + '">' + i + '. ' + val.name + ' -' + val.emp_code + '</p>';
                });

                $(".emp_details").html(str);
            }
            else
            {
                $(".emp_details").html('0 Record Found');
            }
            //console.log(data);
        });
    });
</script>
@endsection
