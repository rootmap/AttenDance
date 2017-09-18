<?php
if(isset($data))
{
    $pageinfo=array("Edit Uploaded File","Edit Uploaded File Record","","SUL");
}
else
{
    $pageinfo=array("Import Employee File ","Import Employee File","","SUL");
}

?>
@extends('layout.master')
@section('content')
@include('include.coreBarcum')
<div class="row">
	<div class="col-lg-12">
		<div class="cat__core__sortable" id="left-col">
			<section class="card" order-id="card-1">

              <div class="card-block">
                 <div class="row">
                  <div class="col-xl-4">
                   <!--Vertical Form Starts Here-->

                   <form name="Employee" enctype="multipart/form-data"  action="{{url('Settings/ImportEmpoleeInfo/Add')}}" method="post">
                    {{csrf_field()}}
                    <div class="row">
                        <div class="col-lg-12">
                            <label for="l30">Please Browse & Select Your Attendance File</label>
                            <div class="col-lg-12">
                                <input name="employee" type="file" class="dropify" data-height="300" />
                            </div>

                        </div>
                    </div>


                    <div class="form-actions">
                        <button type="submit"  class="btn btn-primary">Upload Employee File</button>
                        <button type="reset" class="btn btn-default">Cancel</button>
                    </div>
                </form>

                <!--Vertical Form Ends Here-->
            </div>

        </section>


    </div>
</div>

</div>
@endsection
@section('extraFooter')

@include('include.coreKendo')
<link rel="stylesheet" type="text/css" href="{{url('vendors/dropify/dist/css/dropify.min.css')}}">
<script src="{{url('vendors/dropify/dist/js/dropify.min.js')}}"></script>
<script>
    $(function() {


        $('.dropify').dropify();

      
        

    });
</script>
@endsection