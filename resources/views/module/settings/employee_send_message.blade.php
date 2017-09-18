<?php
if (isset($data)) {
    $pageinfo = array("Employee send message", "Employee send message Data", "", "SUL","Filter list","Filtered Report");
} else {
    $pageinfo = array("Employee send message", "Employee send message", "", "SUL","Employee send message","Filtered Report");
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
                        <strong>Basic Information</strong>
                        <!-- <strong>{{$pageinfo[4]}}</strong> -->
                    </h5>
                </div>

                <div class="card-block">
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="row">
                                <!--Added For Leave User Data Filtering-->
                                <div class="col-lg-6">
                                    <div class="form-group">

                                      <table class="table table-striped">
                                          <thead>

                                          </thead>
                                          <tbody>

                                          <tr>
                                              <td>
                                                  <h5>Employee Code :</h5>
                                              </td>
                                              <td>
                                                  <h5>#061166511</h5>
                                              </td>
                                          </tr>

                                          <tr>
                                              <td>
                                                  <h5>Gender :</h5>
                                              </td>
                                              <td>
                                                  <h5>Male</h5>
                                              </td>
                                          </tr>

                                          <tr>
                                              <td>
                                                  <h5>Date of Birth :</h5>
                                              </td>
                                              <td>
                                                  <h5>12/12/2012</h5>
                                              </td>
                                          </tr>

                                          </tbody>
                                      </table>


                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="form-group">

                                      <table class="table table-striped">
                                          <thead>

                                          </thead>
                                          <tbody>

                                          <tr>
                                              <td>
                                                  <h5>Marital Status :</h5>
                                              </td>
                                              <td>
                                                  <h5>Single</h5>
                                              </td>
                                          </tr>

                                          <tr>
                                              <td>
                                                  <h5>Blood Group :</h5>
                                              </td>
                                              <td>
                                                  <h5>A+</h5>
                                              </td>
                                          </tr>

                                          <tr>
                                              <td>
                                                  <h5>Wedding Date :</h5>
                                              </td>
                                              <td>
                                                  <h5>21/2/2021</h5>
                                              </td>
                                          </tr>

                                          </tbody>
                                      </table>


                                    </div>
                                </div>


                            </div>
                            <!-- <div class="form-actions">
                                <button type="button" name="filter"  class="btn btn-primary">Reset</button>
                            </div> -->

                        </div>
                    </div>
                </div>
            </section>


            <!-- [card 2 start] -->
            <section class="card" order-id="card-1">
              <div class="card-header">
                    <h5 class="mb-0 text-black">
                        <strong>Send Marrage/Wedding Day Grettings</strong>
                        <!-- <strong>{{$pageinfo[4]}}</strong> -->
                    </h5>
                </div>

                <div class="card-block">
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="row">

                                <div class="col-lg-6">
                                    <div class="form-group">
                                       <label for="l30">Employee Name: <span style="color:red;">*</span> </label>
                                       <input type="text" name="emp_name"  class="form-control" placeholder="Employee Name" id="">
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                  <div class="form-group">
                                     <label for="l30">Employee Email: <span style="color:red;">*</span></label>
                                     <input type="email" name="emp_mail"  class="form-control" placeholder="Employee Email" id="">
                                  </div>
                                </div>

                                <div class="col-lg-12">
                                  <div class="form-group">
                                     <label for="l30">Subject: <span style="color:red;">*</span> </label>
                                     <input type="text" name="subject"  class="form-control" placeholder="Email subject" id="">
                                  </div>
                                </div>

                                <div class="col-lg-12">
                                  <div class="form-group">
                                     <label for="l30">Message: <span style="color:red;">*</span> </label> <br/>
                                     <code>REVNAME</code> Use REVNAME for Receiver Name,<code>REVDEP</code> Use for Department,<code>REVDES</code> User for designation
                                     <!-- <input type="text" name="subject"  class="form-control" placeholder="Email subject" id=""> -->
                                     <table cellspacing="4" cellpadding="0" class="k-widget k-editor k-header" role="presentation" style="width: 100%;">
                                       <tbody>
                                         <tr role="presentation">
                                           <td class="k-editor-toolbar-wrap k-secondary" role="presentation">
                                             <ul class="k-editor-toolbar" role="toolbar" aria-controls="message" data-role="editortoolbar">
                                               <li class="k-editor-button k-group-start" role="presentation">
                                                 <a href="" role="button" class="k-tool-icon k-bold" unselectable="on" title="Bold" aria-pressed="false">Bold</a>
                                               </li>
                                               <li class="k-editor-button" role="presentation">
                                                 <a href="" role="button" class="k-tool-icon k-italic" unselectable="on" title="Italic" aria-pressed="false">Italic</a>
                                               </li>
                                               <li class="k-editor-button" role="presentation">
                                                 <a href="" role="button" class="k-tool-icon k-underline" unselectable="on" title="Underline" aria-pressed="false">Underline</a>
                                               </li>
                                               <li class="k-editor-button k-group-end" role="presentation">
                                                 <a href="" role="button" class="k-tool-icon k-strikethrough" unselectable="on" title="Strikethrough" aria-pressed="false">Strikethrough</a>
                                               </li>
                                               <li class="k-editor-button k-group-start" role="presentation">
                                                 <a href="" role="button" class="k-tool-icon k-justifyLeft" unselectable="on" title="Align text left" aria-pressed="false">Justify Left</a>
                                               </li>
                                               <li class="k-editor-button" role="presentation">
                                                 <a href="" role="button" class="k-tool-icon k-justifyCenter" unselectable="on" title="Center text" aria-pressed="false">Justify Center</a>
                                               </li>
                                               <li class="k-editor-button" role="presentation">
                                                 <a href="" role="button" class="k-tool-icon k-justifyRight" unselectable="on" title="Align text right" aria-pressed="false">Justify Right</a>
                                               </li>
                                               <li class="k-editor-button k-group-end" role="presentation">
                                                 <a href="" role="button" class="k-tool-icon k-justifyFull" unselectable="on" title="Justify" aria-pressed="false">Justify Full</a>
                                               </li>
                                               <li class="k-editor-button k-group-start" role="presentation">
                                                 <a href="" role="button" class="k-tool-icon k-insertUnorderedList" unselectable="on" title="Insert unordered list" aria-pressed="false">Remove Link</a>
                                               </li>
                                               <li class="k-editor-button" role="presentation">
                                                 <a href="" role="button" class="k-tool-icon k-insertOrderedList" unselectable="on" title="Insert ordered list" aria-pressed="false">Remove Link</a>
                                               </li>
                                               <li class="k-editor-button k-group-end" role="presentation">
                                                 <a href="" role="button" class="k-tool-icon k-indent" unselectable="on" title="Indent">Indent</a>
                                               </li>
                                               <li class="k-editor-button" role="presentation" style="display: none;">
                                                 <a href="" role="button" class="k-tool-icon k-outdent k-state-disabled" unselectable="on" title="Outdent">Outdent</a>
                                               </li>
                                               <li class="k-editor-button k-group-start" role="presentation">
                                                 <a href="" role="button" class="k-tool-icon k-createLink" unselectable="on" title="Insert hyperlink">Create Link</a>
                                               </li>
                                               <li class="k-editor-button" role="presentation" style="display: none;">
                                                 <a href="" role="button" class="k-tool-icon k-unlink k-state-disabled" unselectable="on" title="Remove hyperlink">Remove Link</a>
                                               </li
                                               ><li class="k-editor-button k-group-end" role="presentation">
                                                 <a href="" role="button" class="k-tool-icon k-insertImage" unselectable="on" title="Insert image">Insert Image</a>
                                               </li><li class="k-editor-button k-group-start" role="presentation">
                                                   <a href="" role="button" class="k-tool-icon k-subscript" unselectable="on" title="Subscript" aria-pressed="false">Subscript</a>
                                                 </li><li class="k-editor-button k-group-end" role="presentation">
                                                   <a href="" role="button" class="k-tool-icon k-superscript" unselectable="on" title="Superscript" aria-pressed="false">Superscript</a></li><li class="k-editor-button k-group-start k-group-end" role="presentation"><a href="" role="button" class="k-tool-icon k-createTable" data-popup="" unselectable="on" title="Create table">Create table</a></li><li class="k-editor-button" role="presentation" style="display: none;">
                                                   <a href="" role="button" class="k-tool-icon k-addRowAbove k-state-disabled" unselectable="on" title="Add row above">Add row above</a></li><li class="k-editor-button" role="presentation" style="display: none;"><a href="" role="button" class="k-tool-icon k-addRowBelow k-state-disabled" unselectable="on" title="Add row below">Add row below</a></li><li class="k-editor-button" role="presentation" style="display: none;"><a href="" role="button" class="k-tool-icon k-addColumnLeft k-state-disabled" unselectable="on" title="Add column on the left">Add column on the left</a></li><li class="k-editor-button" role="presentation" style="display: none;"><a href="" role="button" class="k-tool-icon k-addColumnRight k-state-disabled" unselectable="on" title="Add column on the right">Add column on the right</a></li><li class="k-editor-button" role="presentation" style="display: none;">
                                                   <a href="" role="button" class="k-tool-icon k-deleteRow k-state-disabled" unselectable="on" title="Delete row">Delete row</a></li><li class="k-editor-button" role="presentation" style="display: none;"><a href="" role="button" class="k-tool-icon k-deleteColumn k-state-disabled" unselectable="on" title="Delete column">Delete column</a></li><li class="k-editor-button k-group-start k-group-end" role="presentation"><a href="" role="button" class="k-tool-icon k-viewHtml" unselectable="on" title="View HTML">View HTML</a>
                                                   </li>
                                                   <li class="k-group-break">

                                                   </li>
                                                   <li class="k-editor-selectbox k-group-start">
                                                     <span class="k-widget k-dropdown k-header" unselectable="on" role="listbox" aria-haspopup="true" aria-expanded="false" tabindex="0" aria-owns="" aria-disabled="false" aria-readonly="false" aria-busy="false" style="width: 92px;"><span unselectable="on" class="k-dropdown-wrap k-state-default">
                                                     <span unselectable="on" class="k-input">Format</span><span unselectable="on" class="k-select"><span unselectable="on" class="k-icon k-i-arrow-s">select</span></span></span><select title="Format" class="k-formatting k-decorated" data-role="selectbox" unselectable="on" style="width: 92px; display: none;"><option value="p" selected="selected">Paragraph</option><option value="blockquote">Quotation</option><option value="h1">Heading 1</option><option value="h2">Heading 2</option>
                                                     <option value="h3">Heading 3</option><option value="h4">Heading 4</option><option value="h5">Heading 5</option>
                                                     <option value="h6">Heading 6</option></select>
                                                   </span></li><li class="k-editor-combobox"><span class="k-widget k-combobox k-header" unselectable="on"><span tabindex="-1" unselectable="on" class="k-dropdown-wrap k-state-default"><input class="k-input k-fontName" type="text" autocomplete="off" role="combobox" aria-expanded="false" tabindex="0" aria-disabled="false" aria-readonly="false" aria-autocomplete="list" aria-owns="" aria-busy="false" unselectable="on" style="width: 100%;"><span tabindex="-1" unselectable="on" class="k-select">
                                                     <span unselectable="on" class="k-icon k-i-arrow-s" role="button" tabindex="-1">select</span>
                                                   </span>
                                                   </span><select title="Font Name" class="k-fontName" data-role="combobox" aria-disabled="false" aria-readonly="false" unselectable="on" style="display: none;"><option value="inherit" unselectable="on" selected="selected">(inherited font)</option><option value="Arial,Helvetica,sans-serif" unselectable="on">Arial</option><option value="'Courier New',Courier,monospace" unselectable="on">Courier New</option><option value="Georgia,serif" unselectable="on">Georgia</option><option value="Impact,Charcoal,sans-serif" unselectable="on">Impact</option>
                                                     <option value="'Lucida Console',Monaco,monospace" unselectable="on">Lucida Console</option><option value="Tahoma,Geneva,sans-serif" unselectable="on">Tahoma</option><option value="'Times New Roman',Times,serif" unselectable="on">Times New Roman</option><option value="'Trebuchet MS',Helvetica,sans-serif" unselectable="on">Trebuchet MS</option><option value="Verdana,Geneva,sans-serif" unselectable="on">Verdana</option>
                                                   </select>
                                                   </span></li><li class="k-editor-combobox">
                                                     <span class="k-widget k-combobox k-header" unselectable="on"><span tabindex="-1" unselectable="on" class="k-dropdown-wrap k-state-default">
                                                       <input class="k-input k-fontSize" type="text" autocomplete="off" role="combobox" aria-expanded="false" tabindex="0" aria-disabled="false" aria-readonly="false" aria-autocomplete="list" aria-owns="" aria-busy="false" unselectable="on" style="width: 100%;"><span tabindex="-1" unselectable="on" class="k-select">
                                                       <span unselectable="on" class="k-icon k-i-arrow-s" role="button" tabindex="-1">select</span></span></span><select title="Font Size" class="k-fontSize" data-role="combobox" aria-disabled="false" aria-readonly="false" unselectable="on" style="display: none;"><option value="inherit" unselectable="on" selected="selected">
                                                         (inherited size)
                                                       </option><option value="xx-small" unselectable="on">1 (8pt)</option><option value="x-small" unselectable="on">2 (10pt)
                                                     </option><option value="small" unselectable="on">3 (12pt)</option><option value="medium" unselectable="on">4 (14pt)</option><option value="large" unselectable="on">5 (18pt)</option><option value="x-large" unselectable="on">6 (24pt)</option><option value="xx-large" unselectable="on">7 (36pt)</option></select></span></li><li class="k-editor-colorpicker" role="presentation"><div class="k-colorpicker k-foreColor" data-role="colorpicker" style="display: none;"></div>
                                                       <span class="k-widget k-colorpicker k-header" tabindex="0" title="Color" unselectable="on"><span class="k-picker-wrap k-state-default" unselectable="on"><span class="k-tool-icon k-foreColor" unselectable="on"><span class="k-selected-color" unselectable="on" style="background-color: rgb(0, 0, 0);"></span></span><span class="k-select" unselectable="on"><span class="k-icon k-i-arrow-s" unselectable="on"></span></span></span></span></li><li class="k-editor-colorpicker k-group-end" role="presentation"><div class="k-colorpicker k-backColor" data-role="colorpicker" style="display: none;"></div><span class="k-widget k-colorpicker k-header" tabindex="0" title="Background color" unselectable="on"><span class="k-picker-wrap k-state-default" unselectable="on"><span class="k-tool-icon k-backColor" unselectable="on"><span class="k-selected-color" unselectable="on" style="background-color: rgb(0, 0, 0);"></span></span><span class="k-select" unselectable="on"><span class="k-icon k-i-arrow-s" unselectable="on"></span></span></span></span></li></ul>
                                               </td>
                                             </tr>
                                             <tr>
                                               <td class="k-editable-area">
                                                 <iframe src='javascript:""' frameborder="0" class="k-content">

                                                 </iframe>
                                                   <textarea type="text" value="" name="message" placeholder="Write Your Message Here" class="k-textbox k-content k-raw-content" id="message" style="width: 100%; display: none;" data-role="editor" autocomplete="off"></textarea>
                                                 </td>
                                               </tr>
                                             </tbody>
                                           </table>
                                  </div>
                                </div>


                            </div>
                            <div class="form-actions">
                                <button type="button" name="filter"  class="btn btn-primary">Send Message</button>
                            </div>

                        </div>
                    </div>
                </div>
            </section>
            <!-- [card 2 end] -->

        </div>
    </div>
</div>



@endsection
@section('extraFooter')
@include('include.coreKendo')
<?=MenuPageController::genarateKendoDatePicker(array("starts","ends"))?>
@include('ajax_include.company_wise_department')
@include('ajax_include.department_wise_section')
@include('ajax_include.section_wise_designation')
@include('ajax_include.company_department_section_designation_wise_employee')
@endsection
