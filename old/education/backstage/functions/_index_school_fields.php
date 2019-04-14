<?php 

	if( defined('index_filter_by') && index_filter_by == 'albums') {
		$_index_show_classes = true;
		$_index_show_albums = true;
	}
	else {
		$_index_show_classes = true;
		$_index_show_albums = false;
	}



	if(!$_index_table_included ) {
		$_index_table_included = 0;
	?>
		<script type="text/javascript">
		<!--
			var tasks = [];
			var task = 0;
		<?php if( $_index_show_classes ) { ?>
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
		<?php } ?>

		<?php if( $_index_show_albums ) { ?>
			function createAlbumList( Selector, Albums, Selected ) {

				var Element = $(Selector);
				Element.empty();
	
				$('<option value="" >-- Select --</option>').appendTo( Element );
	
				$.each(Albums, function(i, v){
					var option = $('<option />');
	
					if( v.id == Selected ) {
						option.prop('selected', true);
					}
	
					option.text(v.title);
					option.val(v.id);
					option.appendTo( Element );
				});
			}
		<?php } ?>

			function getCurrentValues( Selector ){
				var values = [];
				$('input', Selector ).each(function(){
					if( $(this).attr('type') == 'checkbox' && $(this).prop('checked') ) {
						values.push( $(this).attr('value') );
					}
				});

				return values;
			}

			function clearLoading(t) {
				tasks[t].t--;
				if( tasks[t].t < 1) {
					tasks[t].loader.remove();
					tasks[t].loader1.remove();
					tasks[t].loader2.remove();
				}
			}

		<?php if( isAdmin ) { ?>
			$(document).ready(function(){
				$(".school_id").change(function(){
	
					var parent = $(this).parents('.module_content:first');
					var id = $(this).val();
	
					if( !id ) {
						return ;
					}
					task++;
					var t = task;
					tasks[t] = {t:0};
	
//					var box = $(".index-box", parent);
					tasks[t].loader = $('<div class="loader" ></div>');
					tasks[t].loader1 = $('<div class="loader" ></div>');
					tasks[t].loader2 = $('<div class="loader" ></div>');
					
					$(".school_box", parent).append( tasks[t].loader );
					$(".index-box-out1", parent).append( tasks[t].loader1 );
					$(".index-box-out2", parent).append( tasks[t].loader2 );

//					var Selected = getCurrentValues( box );

					<?php if( $_index_show_classes ) { ?>
						tasks[t].t++;
						$.get('_get.php?from=classes&school_id='+id, function(response){
							response = jQuery.parseJSON(response);

							var element = $(".index-box", parent);
							createClassesList( element, response ); // , Selected
							clearLoading(t);
						});
					<?php } ?>
					<?php if( $_index_show_albums ) { ?>
						tasks[t].t++;
						$.get('_get.php?from=albums&school_id='+id, function(response){
							response = jQuery.parseJSON(response);

							var element = $(".album-box", parent);
							createAlbumList( element, response ); // , Selected
							clearLoading(t);
						});
					<?php } ?>
				});
			});
		<?php } ?>

		//-->
		</script>
	<?php 
	}

	$_index_table_included++;
	$Index = $class_ids[$oldrecord[$i]];
//	$Index = $index[$oldrecord[$i]];
	if( !is_array($Index)) {
		$Index = array();
	}
?>

	<?php if( isAdmin ) { ?>
		<fieldset>
			<label>School</label>
			<div style="margin-left: 210px;" class="school_box loader_box">
				<select name="school_id[<?php echo $i; ?>]" class="school_id" >
					<option value="" >-- Select --</option>
					<?php 
						$_index_school = 0;
						$sid = nor($school_id[$oldrecord[$i]], $School['id']);
						foreach ($Schools as $school) {
							$Selected = ( $school['id'] == $sid ) ? ' selected="selected" ' : '';
							if( $Selected ) {
								$_index_school = $school['id'];
							}
							?><option value="<?php echo $school['id'];?>" <?php echo $Selected; ?> ><?php echo $school['title'];?></option><?php 
						}
					?>
				</select>
			</div>
		</fieldset>
	<?php } ?>
	<?php if( $_index_show_classes ) { ?>
		<fieldset>
			<label>Classes</label>
			<div style="margin-left: 210px;" class="loader_box index-box-out1">
			<?php 
				$_allSelected = (in_array('-1', $Index)) ? ' checked="CHECKED" ' : '';
			?>
				<label><input type="checkbox" name="class_ids[<?php echo $i; ?>][]" value="-1" <?php echo $_allSelected; ?>><span> [All Classes]</span></label>
				<div class="clear"></div>
				<div class="index-box" id="index-<?php echo $_index_table_included; ?>" data-i="<?php echo $i; ?>"></div>
			</div>
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
		?>
		</fieldset>
	<?php } ?>
	
	<?php if( $_index_show_albums ) { ?>
		<fieldset>
			<label>Albums</label>
			<div style="margin-left: 210px;" class="loader_box index-box-out2">
				<select name="gallery_cat_id[<?php echo $i; ?>]" class="album-box" id="index-album-<?php echo $_index_table_included; ?>" data-i="<?php echo $i; ?>"></select>
			</div>
		<?php 
	
			if(isSchool) {
				$indexAlbums = get_school_albums( isSchool, true);
			}
			else if(isTeacher) {
				$indexAlbums = get_school_albums( $Admin['school_id'], true);
			}
			else if( $_index_school ) {
				$indexAlbums = get_school_albums( $_index_school, true);
			}
			else {
				$indexAlbums = false;
			}
		
			if( $indexAlbums ) {
				?>
				<script type="text/javascript">
				createAlbumList( 
						'#index-album-<?php echo $_index_table_included; ?>', 
						<?php echo json_encode( array_values($indexAlbums) ); ?> , 
						<?php echo json_encode( intval( $gallery_cat_id[$oldrecord[$i]] ) ); ?> 
						);
				</script>
				<?php 
			}
		?>
		</fieldset>
	<?php } ?>
