<span id="school_class_filter">
<?php 

$_index_school = 0;

if( defined('index_filter_by') && index_filter_by == 'albums' ) {
	$_index_filter_classes = 'classes_albums';
	$_index_filter_albums = true;
}
else {
	$_index_filter_classes = 'classes';
	$_index_filter_albums = false;
}

if( isAdmin ) { 
	?>
	School: <select name="school_id" id="school_id" >
		<option value="" >-- Select --</option>
		<?php 
			$_index_school = 0;
			foreach ($Schools as $school) {
				$Selected = ( $school['id'] == $School['id'] ) ? ' selected="selected" ' : '';
				if( $Selected ) {
					$_index_school = $school['id'];
				}
				?><option value="<?php echo $school['id'];?>" <?php echo $Selected; ?> ><?php echo $school['title'];?></option><?php 
			}
		?>
	</select>
	<?php 

}
?>
	Class: <select name="class_id" id="class-select-box" ></select>
	<?php if( $_index_filter_albums ) { ?>
	Album: <select name="album_id" id="album-select-box" ></select>
	<?php } ?>
</span>

<script type="text/javascript">
<!--
	function createOptionsSelectBox(Selector, Classes, Selected ) {
	
		
		var Element = $(Selector);
		Element.empty();
	
		$('<option value="" >-- Select --</option>').appendTo( Element );
	
		$.each(Classes, function(i, v){
			var option = $('<option />');
	
			if( v.id == Selected ) {
				option.prop('selected', true);
			}
	
			option.text(v.title);
			option.val(v.id);
			option.appendTo( Element );
		});
	}

<?php if( isAdmin ) { ?>
	$(document).ready(function(){
		$("#school_id").change(function(){

			var id = $(this).val();

			if( !id ) {
				return ;
			}

			var box = $("#school_class_filter");
			var loader = $('<div class="loader" ></div>');

			box.append( loader );

			$.get('_get.php?from=<?php echo $_index_filter_classes; ?>&school_id='+id, function(response){
				response = jQuery.parseJSON(response);

				loader.remove();

				if( typeof response.albums != 'undefined' ) {
					createOptionsSelectBox('#album-select-box', response.albums ); // , Selected
				}
				if( typeof response.classes != 'undefined' ) {
					createOptionsSelectBox('#class-select-box', response.classes ); // , Selected
				}
				else {
					createOptionsSelectBox('#class-select-box', response ); // , Selected
				}
			});
		});
	});
<?php } ?>

//-->
</script>
<?php 

$_school_id = 0;
if( isSchool ) { 
	$_school_id = $Admin['school_id'];
}
else if( isTeacher ) { 
	$_school_id = $Admin['school_id'];
}
else if( $_index_school ) { 
	$_school_id = $_index_school;
}

if( $_school_id ) {
	$_classes = get_school_classes( $_school_id );
	
	?>
	<script type="text/javascript">
	createOptionsSelectBox( '#class-select-box',
		<?php echo json_encode( $_classes); ?> , 
		<?php echo intval( $Class['id'] ); ?> 
		);
	</script>
	<?php 
}


if( $_index_filter_albums && $_school_id ) {
	$_albums = get_school_albums( $_school_id );
	
	?>
	<script type="text/javascript">
	createOptionsSelectBox( '#album-select-box',
		<?php echo json_encode( $_albums); ?> , 
		<?php echo intval( $Album['id'] ); ?> 
		);
	</script>
	<?php 
}

