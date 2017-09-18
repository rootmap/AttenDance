<?php
//param 1= Page Title & Module Feature Name
//param 2=Module Title
//param 3=Module Special Message or Note
//param 4=Company Short code eg. For Systech Unimax Ltd = SUL
$pageinfo=array("Blank Name","Blank Feature","Page Note","SUL");
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
						<i class="icmn-minus mr-2 cat__core__sortable__collapse" data-toggle="tooltip" data-placement="left" title="" data-original-title="Collapse"></i>
						<i class="icmn-plus mr-2 cat__core__sortable__uncollapse" data-toggle="tooltip" data-placement="left" title="" data-original-title="Uncollapse"></i>
						<i class="icmn-cross cat__core__sortable__close" data-toggle="tooltip" data-placement="left" title="" data-original-title="Remove"></i>
					</div>
					<h5 class="mb-0 text-black">
						<strong>{{$pageinfo[0]}}</strong>
						<!--<small class="text-muted">All cards are draggable and sortable!</small>-->
					</h5>
				</div>
				<div class="card-block">
					<div class="row">
						<div class="col-xl-12">
							<h1>You Can Start From Here!</h1>
						</div>
					</div>
				</div>
			</section>


		</div>
	</div>

</div>
@endsection

@section('extraFooter')
	@include('include.cardControlJs')
@endsection