<script type="text/javascript">
	
$(document).ready(function(){
		///////////////////////////////////////////////////////////
        // card controls
        $('.cat__core__sortable__collapse, .cat__core__sortable__uncollapse').on('click', function () {
        	$(this).closest('.card').toggleClass('cat__core__sortable__collapsed');
        });
        $('.cat__core__sortable__close').on('click', function () {
        	$(this).closest('.card').remove();
        	$('.tooltip').remove();
        });

        // header double click
        $('.cat__core__sortable .card-header').on('dblclick', function () {
        	$(this).closest('.card').toggleClass('cat__core__sortable__collapsed');
        });

});

</script>