<script>
$(function ()
{
    if ($("input[name='company_id']").length==0)
    {
        
        $("select[name='company_id']").change(function(){
            if($(this).val()!='')
            {
                $.post("<?=url('Filter/Company/Get/Branch/Json')?>",{'company_id':$(this).val(),'_token':'<?=csrf_token()?>'},function(data){ 
                    var total=data.length;
                    if(total!=0)
                    {
                        var str='';
                        str +='<option selected="selected" value="">Select Branch</option>';
                        $.each(data,function(index,val){
                            str +='<option value="'+val.id+'">'+val.name+'</option>';
                        });
                            //console.log("Data Found");
                        $("select[name='branch_id']").html(str);
                    }
                    else
                    {
                        var str='';
                        str +='<option selected="selected" value="">0 Record Found</option>';
                        $("select[name='branch_id']").html(str);
                        //console.log(data);
                    }
                        //console.log(data);
                });
            }
        });
    }
    else
    {
        var com=$("input[name='company_id']").val();
        $.post("<?=url('Filter/Company/Get/Branch/Json')?>",{'company_id':com,'_token':'<?=csrf_token()?>'},function(data){ 
                    var total=data.length;
                    if(total!=0)
                    {
                        var str='';
                        str +='<option selected="selected" value="">Select Branch</option>';
                        $.each(data,function(index,val){
                            str +='<option value="'+val.id+'">'+val.name+'</option>';
                        });
                            //console.log("Data Found");
                        $("select[name='branch_id']").html(str);
                    }
                    else
                    {
                        var str='';
                        str +='<option selected="selected" value="">0 Record Found</option>';
                        $("select[name='branch_id']").html(str);
                        //console.log(data);
                    }
                        //console.log(data);
                });
    }

});
</script>