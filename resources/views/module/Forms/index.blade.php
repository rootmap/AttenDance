<?php
$pageinfo=array("Forms","Forms Feature","Page Note","SUL");
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
							<!--Vertical Form Starts Here-->
							<form>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label for="l30">Default Input</label>
                                    <input type="text" class="form-control" placeholder="Email Address" id="l30">
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label for="l31">Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" placeholder="Password" id="l31">
                                        <span class="input-group-addon">
                                            <i class="icmn-key"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label for="l32">Left Icon</label>
                                    <div class="form-input-icon">
                                        <i class="icmn-pie-chart font-green"></i>
                                        <input type="text" class="form-control" placeholder="Left icon" id="l32">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label for="l33">Right Icon</label>
                                    <div class="form-input-icon form-input-icon-right">
                                        <i class="icmn-download"></i>
                                        <input type="text" class="form-control" placeholder="Right icon" id="l33">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label for="l34">Input with Icon</label>
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="icmn-search"></i>
                                        </span>
                                        <input class="input-error form-control" type="text" value="" id="l34">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label for="l35">Input With Spinner</label>
                                    <div class="form-input-icon form-input-icon-right">
                                        <i class="icmn-spinner11 util-spin"></i>
                                        <input type="password" class="form-control" placeholder="Password" id="l35">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="l36">Disabled</label>
                                    <input type="text" class="form-control" placeholder="Disabled" disabled="" id="l36">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="l37">Readonly</label>
                                    <input type="text" class="form-control" placeholder="Readonly" readonly="" id="l37">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="l38">Textarea</label>
                            <textarea class="form-control" rows="3" id="l38"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="l39">File input</label>
                            <br />
                            <input type="file" id="l39">
                            <br />
                            <small class="text-muted">Technical information for user</small>
                        </div>
                        <div class="form-actions">
                            <button type="button" class="btn btn-primary">Submit</button>
                            <button type="button" class="btn btn-default">Cancel</button>
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
