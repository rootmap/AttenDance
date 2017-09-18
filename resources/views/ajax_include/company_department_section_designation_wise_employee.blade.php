<script>
    $("select[name='designation_id']").change(function(){
        if($(this).val()!='')
        {
            var company_id=$("select[name='company_id']").val();
            var department_id=$("select[name='department_id']").val();
            var section_id=$("select[name='section_id']").val();
            $.post("<?=url('Filter/Designation/Get/Employee/Json')?>",
                {'company_id':company_id,'department_id':department_id,'section_id':section_id,'designation_id':$(this).val(),'_token':'<?=csrf_token()?>'},
                function(data){
                var total=data.length;
                if(total!=0)
                {
                    var str='';
                    str +='<option selected="selected" value="">Select Employee</option>';
                    $.each(data,function(index,val){
                        str +='<option value="'+val.emp_code+'">'+val.name+'</option>';
                    });
                        //console.log("Data Found");
                    $("select[name='emp_code']").html(str);
                }
                else
                {
                    var str='';
                    str +='<option selected="selected" value="">0 Record Found</option>';
                    $("select[name='designation_id']").html(str);
                    //console.log(data);
                }
            });
        }
    });
</script>
