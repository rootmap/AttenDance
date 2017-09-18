<?php
    $pageinfo = array("Company Policies", "Company Policies", "", "SUL");
?>
@extends('layout.master')
@section('content')
<nav class="cat__core__top-sidebar cat__core__top-sidebar--bg">
	<div class="pull-right hidden-sm-down">
		<a href="Javascript:void(0);" id="cmbtn" class="btn btn-icon btn-outline-inverse btn-rounded mr-2 mb-2">
			<i class="fa fa-chevron-up" aria-hidden="true"></i>
		</a>
	</div>
	<span class="cat__core__title d-block mb-2">
		<strong><span class="icmn-info"></span> {{$pageinfo[1]}}</strong>
		<small class="text-muted">{{$pageinfo[2]}}</small>
	</span>
</nav>
@include('include.msg')
<div class="row">
    <div class="col-lg-12">
        <div class="cat__core__sortable" id="left-col">
            <section class="card" order-id="card-1">

                <div class="card-block">
                    <div class="row">
					
					
						@if(isset($data))
							@foreach($data as $row)
								<div class="col-xl-12">
									<!--Vertical Form Starts Here-->
									
									   <!-- Blockquotes -->
										<h4 style="margin-bottom:-13px; margin-left:3px;" class="text-black">
											<p class="text-muted"><code>Policy Name : {{$row->policy_heading}} | Published Date : <?php echo date('M d Y',strtotime($row->policy_publish_date)); ?></code></p>
										</h4>
										<div class="row">
											<div class="col-lg-12 mb-2">
												<blockquote class="blockquote">
													
													<p class="mb-0">
													{!!$row->policy_description!!}
													</p>
													<div class="clearfix"></div>
													<footer class="blockquote-footer">Posted : {{$row->created_at}}</footer>
												</blockquote>
											</div>
										</div>
										
										<!-- End Blockquotes -->
								   
									<!--Vertical Form Ends Here-->
								</div>
							@endforeach
						@endif
						
						
                        
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
