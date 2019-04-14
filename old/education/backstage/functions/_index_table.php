<?php

	if(!$_index_table_included ) {
		$_index_table_included = 0;
	?>
		<script type="text/javascript">
		<!--
			function createClassesList( Selector, Classes, Selected ) {
		
				var Element = $(Selector);
				Element.empty();

				var $i = Element.data('i');

				if( typeof Selected == 'undefined') {
					Selected = {};
				}

				$.each(Classes, function(i, v){
					var text = $('<span />').text(' ' + v.title);
					var label = $('<label />');
					var checkbox = $('<input type="checkbox" name="class_ids['+$i+'][]"  />');

					if( findInClassesIDs(v.id, Selected) ) {
						checkbox.prop('checked', true);
					}
					checkbox.addClass('class_id');

					checkbox.val( v.id );
					label.append(checkbox);
					label.append(text);
					label.appendTo( Element );
				});
			}
			function findInClassesIDs(id, list) {
				var found = false;
				$.each(list, function(i, v){
					if( v == id) {
						found = true;
						return true;
					}
				});
				return found;
			}

			function getCurrentValues( Selector ){
				var values = [];
				$('input', Selector ).each(function(){
					if( $(this).attr('type') == 'checkbox' && $(this).prop('checked') ) {
						values.push( $(this).attr('value') );
					}
				});

				return values;
			}

		<?php if( isAdmin ) { ?>
			$(document).ready(function(){
				$(".school_id").change(function(){
	
					var parent = $(this).parents('.module_content:first');
					var id = $(this).val();
	
					if( !id ) {
						return ;
					}
	
					var box = $(".index-box", parent);
					var loader = $('<div class="loader" ></div>');
	
					box.append( loader );

					var Selected = getCurrentValues( box );
	
					$.get('_get.php?from=classes&school_id='+id, function(response){
						response = jQuery.parseJSON(response);

						createClassesList( box, response ); // , Selected
					});
				});
			});
		<?php } ?>

		//-->
		</script>
	<?php 
	}
	
	$_index_table_included++;
	$Index = $class_ids[$oldrecord[$i]];
	if( !is_array($Index)) {
		$Index = array();
	}

	$_allSelected = (in_array('-1', $Index)) ? ' checked="CHECKED" ' : '';
	?>
		<label><input type="checkbox" name="class_ids[<?php echo $i; ?>][]" value="-1" <?php echo $_allSelected; ?>><span> [All Classes]</span></label>
		<div class="clear"></div>
		<div class="index-box" id="index-<?php echo $_index_table_included; ?>" data-i="<?php echo $i; ?>"></div>
	<?php 

	if(isSchool) {
		$indexClasses = $Classes;
	}
	else if(isTeacher) {
		$indexClasses = $Classes;
	}
	else if( $_index_school ) {
		$indexClasses = get_school_classes( $_index_school, true);
	}
	else {
		$indexClasses = false;
	}
	
//	var_dump($_index_school);
//	var_dump($indexClasses);
//	var_dump($Index);
	
	
	if( $indexClasses ) {
		?>
		<script type="text/javascript">
		createClassesList( 
				'#index-<?php echo $_index_table_included; ?>', 
				<?php echo json_encode( array_values($indexClasses) ); ?> , 
				<?php echo json_encode( array_values($Index ) ); ?> 
				);
		</script>
		<?php 
	}
