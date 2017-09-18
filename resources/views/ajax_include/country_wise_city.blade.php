<script>
$(function ()
    {
    $("select[name='country_id']").change(function(){
        if($(this).val()!='')
        {
            $.post("<?=url('Filter/Country/Get/City/Json')?>",{'country_id':$(this).val(),'_token':'<?=csrf_token()?>'},function(data){ 
                var total=data.length;
                if(total!=0)
                {
                    var str='';
                    str +='<option selected="selected" value="">Select City</option>';
                    $.each(data,function(index,val){
                        str +='<option value="'+val.id+'">'+val.name+'</option>';
                    });
                        //console.log("Data Found");
                    $("select[name='city_id']").html(str);
                }
                else
                {
                    var str='';
                    str +='<option selected="selected" value="">0 Record Found</option>';
                    $("select[name='city_id']").html(str);
                    //console.log(data);
                }
                    //console.log(data);
            });
        }
    });
});
</script>