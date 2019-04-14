<?php
class lister {
	
	function lister ( $link ) {

		?>
<style> .sortable { cursor: move; } </style>
<script language="JavaScript" type="text/javascript">
$(document).ready(function() { 
	$(".sortable").sortable({ 
		opacity: 0.9, 
		cursor: 'move', 
		update: function(event, ui){ 
			var ids = 'doupdate=yes';

			$(this).find('tr').each(function()
			{
				ids = ids + '&trContainer[]=' + this.id.replace('tr_', '');
			});
			$.post( '<?php echo $link; ?>', ids );
		}, 
		items: 'tr'
	});
});
</script>
		<?php
	}
}