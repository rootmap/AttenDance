<script>
$(function ()
    {
    $("select[name='section_id']").change(function(){
        if($(this).val()!='')
        {

            var company_id=0;
            if($("input[name='company_id']").length==0)
            {
                company_id = $("select[name='company_id']").val();
            }
            else
            {
                company_id = $("input[name='company_id']").val();
            }


            var department_id=$("select[name='department_id']").val();
            $.post("<?=url('Filter/Section/Get/Designation/Json')?>",
                {'company_id':company_id,'department_id':department_id,'section_id':$(this).val(),'_token':'<?=csrf_token()?>'},
                function(data){
                var total=data.length;
                if(total!=0)
                {
                    var str='';
                    str +='<option selected="selected" value="">Select Designation</option>';
                    $.each(data,function(index,val){
                        str +='<option value="'+val.id+'">'+val.name+'</option>';
                    });
                        //console.log("Data Found");
                    $("select[name='designation_id']").html(str);
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
});
</script>
