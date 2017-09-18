<?php
if (isset($data)) {
    $pageinfo = array("Edit Modify System Role Maping Settings", "Edit User Role Maping Record", "", "SUL");
} else {
    $pageinfo = array("Add System Role Maping", "System Role Maping", "", "SUL");
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
                            <form name="Branch" action="{{url('Settings/UserRoleMap/Update/'.$data['id'])}}" method="post">
                                {{csrf_field()}}
                                <div class="row">
                                    @if(empty($logged_emp_com))
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="l30">Company Name</label>
                                            <select class="form-control" name="company_id">
                                                <!--<option value="">Select Company</option>-->
                                                @if(isset($company))
                                                @foreach($company as $row)
                                                <option <?php if ($data['company_id'] == $row->id) { ?> selected="selected" <?php } ?> value="{{$row->id}}">{{$row->name}}</option>
                                                @endforeach
                                                @endif

                                            </select>
                                        </div>
                                    </div>
                                    @else
                                    <input type="hidden" name="company_id" value="{{$logged_emp_com}}" class="form-control" placeholder="Type Designation Title" id="l30">
                                    @endif
                                    







                                </div>


                                <div class="form-actions">
                                    <button type="submit"  class="btn btn-primary">Update</button>
                                    <button type="reset" class="btn btn-default">Cancel</button>
                                </div>
                            </form>
                            @else
                            <form name="Branch" action="{{url('Settings/UserRoleMap/Add')}}" method="post">
                                {{csrf_field()}}
                                <div class="row">
                                    @if(empty($logged_emp_com))
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="l30" class="col-md-12">Company Name</label>
                                            <select class="form-control" name="company_id">
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

                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="l30" class="col-md-12">System Access Role</label>
                                            <select class="form-control" name="system_access_role_id">
                                                <option selected="selected" value="">Select System Access Role</option>
                                                @if(isset($SystemAccessRole))
                                                    @foreach($SystemAccessRole as $row)
                                                        <option value="{{$row->id}}">{{$row->name}}</option>
                                                    @endforeach
                                                @endif

                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label style="margin-top: 40px;"><input type="checkbox" name="selectAll" id="selectAll" value="1"> All Permission</label>
                                        </div>
                                    </div>



                                    <div class="col-lg-12">
                                        <table class="table table-bordered">

                                            @if(MenuPageController::showRawMenuSite())
                                            @foreach(MenuPageController::showRawMenuSite() as $mod)

                                            <thead class="thead-inverse">
                                                <tr>
                                                    <th>
                                                        <label><input type="checkbox" name="module_<?php echo str_replace(' ','_',strtolower($mod->name)); ?>" value="1"> {{$mod->name}}</label>
                                                    </th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                <tr>
                                                    <td>

                                                       @foreach(MenuPageController::showRawSubMenuSite($mod->id) as $submod)
                                                       <table class="table table-bordered module_<?php echo str_replace(' ','_',strtolower($mod->name)); ?>">
                                                        <thead>
                                                            <tr>
                                                                <th colspan="5">
                                                                    <label>
                                                                    <input type="checkbox" name="sub_<?php echo str_replace('&','n',str_replace(' ','_',strtolower($submod->name))); ?>" value="1"> {{$submod->name}}</label>
                                                                </th>
                                                            </tr>
                                                        </thead>
                                                        @foreach(MenuPageController::showRawSitePage($mod->id,$submod->id) as $pagemod)
                                                        <tbody class="sub_<?php echo str_replace('&','n',str_replace(' ','_',strtolower($submod->name))); ?>">
                                                            
                                                            <tr class="page_<?php echo str_replace('&','n',str_replace(' ','_',strtolower($pagemod->name))); ?>">
                                                                <td width="200" style="font-style:italic;">
                                                                    <label><input type="checkbox" id="page_<?php echo str_replace('&','n',str_replace(' ','_',strtolower($pagemod->name))); ?>" name="page_id[]" value="{{$pagemod->id}}"> {{$pagemod->name}}</label>
                                                                </td>
                                                                <td>
                                                                 <label><input type="checkbox" name="page_create[]" value="1"> Create </label>
                                                             </td>
                                                             <td>
                                                                 <label><input type="checkbox" name="page_update[]" value="1"> Update </label>
                                                             </td>
                                                             <td>
                                                                 <label><input type="checkbox" name="page_view_list[]" value="1"> View List </label> 
                                                             </td>
                                                             <td>
                                                                 <label><input type="checkbox" name="page_delete[]" value="1"> Delete </label>
                                                             </td>
                                                         </tr>
                                                        
                                                     </tbody>
                                                      @endforeach
                                                 </table>
                                                 @endforeach

                                             </td>
                                         </tr>
                                     </tbody>
                                     @endforeach
                                     @endif

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
    $(document).ready(function(){
        $('#selectAll').click(function (e) {
            //$(this).closest('table').find('td input:checkbox').prop('checked', this.checked);
            $('table').find('td input:checkbox').prop('checked', this.checked);
            $('table').find('th input:checkbox').prop('checked', this.checked);
        });


        @if(MenuPageController::showMenuSite())
        @foreach(MenuPageController::showMenuSite() as $mod)
        $("input[name='module_<?php echo str_replace(' ','_',strtolower($mod->name)); ?>']").click(function (e) {
            $('.module_<?php echo str_replace(' ','_',strtolower($mod->name)); ?>').find('td input:checkbox').prop('checked', this.checked);
            $('.module_<?php echo str_replace(' ','_',strtolower($mod->name)); ?>').find('th input:checkbox').prop('checked', this.checked);
        });

            @foreach(MenuPageController::showSubMenuSite($mod->id) as $submod)
                $("input[name='sub_<?php echo str_replace('&','n',str_replace(' ','_',strtolower($submod->name))); ?>']").click(function (e) {
                        //$(this).closest('table').find('td input:checkbox').prop('checked', this.checked);
                        $('.sub_<?php echo str_replace('&','n',str_replace(' ','_',strtolower($submod->name))); ?>').find('td input:checkbox').prop('checked', this.checked);
                        $('.sub_<?php echo str_replace('&','n',str_replace(' ','_',strtolower($submod->name))); ?>').find('th input:checkbox').prop('checked', this.checked);
                });

                @foreach(MenuPageController::showSitePage($mod->id,$submod->id) as $pagemod)
                    $("#page_<?php echo str_replace('&','n',str_replace(' ','_',strtolower($pagemod->name))); ?>").click(function (e) {
                                $('.page_<?php echo str_replace('&','n',str_replace(' ','_',strtolower($pagemod->name))); ?>').find('td input:checkbox').prop('checked', this.checked);
                    });
                @endforeach
            @endforeach
        @endforeach
        @endif


});
</script>
@endsection
