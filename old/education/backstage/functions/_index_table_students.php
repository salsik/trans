<?php

	if(!$_index_table_students_included ) {
		$_index_table_students_included = 0;
	?>
		<script type="text/javascript">
		<!--
		function createStudentsRow( Selector, data, i, Selected ) {
			var Element = $(Selector).get(0);

			var text = $('<span />').text(' ' + data.title);
			var label = $('<label />');
			var checkbox = $('<input type="checkbox" name="student_ids['+i+'][]"  />');

			if( Selected ) {
				checkbox.prop('checked', true);
			}

			checkbox.val( data.id );
			label.append(checkbox);
			label.append(text);

			label.appendTo( Element );
		}
		function createStudentsList( Selector, Students, i, Selected ) {

			var Element = $(Selector).get(0);
//			$(Element).empty();

			var $i = i;

			if( typeof Selected == 'undefined') {
				Selected = {};
			}

			$.each(Students, function(i, v){
				var select = findInStudentsIDs(v.id, Selected);
				createStudentsRow( $(Element), v, $i, select );
			});
		}
			function findInStudentsIDs(id, list) {
				var found = false;
				$.each(list, function(i, v){
					if( v == id) {
						found = true;
						return true;
					}
				});
				return found;
			}

			function getCurrentStudentValues( Selector ){
				var values = [];
				$('input', Selector ).each(function(){
					if( $(this).attr('type') == 'checkbox' && $(this).prop('checked') ) {
						values.push( $(this).attr('value') );
					}
				});

				return values;
			}

				$(".class_id").live('change', function(){

					var parent = $(this).parents('.module_content:first');
					var index_table_students_table = $('.index_table_students_table', parent).get(0);
					var id = $(this).val();
					var title = $(this).parents('label:first').find('span:first').text();
					var indexParent = $(this).parents('.index-box:first');

					var $i = indexParent.data('i');
					if( !id ) {
						return ;
					}

					var students_list = false; var Index = 0;

					$('.students_list', parent).each(function(){
						if( $(this).hasClass('students_list_' + id) ) {
							students_list = $(this);
						}
						Index++;
					});
					
					if( this.checked ) {
						if( students_list !== false ) {
							students_list.show();
							return ;
						}
					}
					else {
						if( students_list !== false ) {
							students_list.hide();
							return ;
						}
						return ;
					}

					students_list = $('<div></div>');
					students_list.addClass('students_list');
					students_list.addClass('students_list_' + id);
					students_list.append('<div><b>'+title+'</b></div>');
					students_list.appendTo( index_table_students_table );

					var box = $(".students_list_box", parent);
					var loader = $('<div class="loader" ></div>');

					box.append( loader );

					var Selected = getCurrentStudentValues( box );
	
					$.get('_get.php?from=students_list&class_id='+id, function(response){
						response = jQuery.parseJSON(response);

						createStudentsList( students_list, response, $i ); // , Selected

						loader.remove();
					});
				});

		//-->
		</script>
	<?php 
	}
	
	$_index_table_students_included++;
	$Index = $class_ids[$oldrecord[$i]];
	if( !is_array($Index)) {
		$Index = array();
	}

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
	
	if( !is_array( $student_ids[$i] )) {
		$student_ids[$i] = array();
	}
	
	$_allSelected = (in_array('-1', $student_ids[$i])) ? ' checked="CHECKED" ' : '';
?>
<div class="index_table_students_table">
	<label><input type="checkbox" name="student_ids[<?php echo $i; ?>][]" value="-1" <?php echo $_allSelected; ?>><span> [All Students]</span></label>
	<div class="clear"></div>
<?php 
	// loop the classes
	foreach($Index as $k=>$v) {
		if( $indexClasses[ $v ] && isset( $limitationStudentsList )) {
			$_students = array();
			$qqq = mysql_query("SELECT id, title FROM students WHERE class_id='{$v}' {$limitationStudentsList} ");
			if($qqq && mysql_num_rows( $qqq )) {
				while($stu = mysql_fetch_assoc( $qqq )) {
					$_students[] = $stu;
				}
			}
			
			?>
			<div class="students_list students_list_<?php echo $v; ?> students_list_<?php echo $i; ?>_<?php echo $v; ?>">
				<div><b><?php echo $indexClasses[ $v ]['title']; ?></b></div>
			
			</div>
			<script type="text/javascript">
				createStudentsList( 
					'.students_list_<?php echo $i; ?>_<?php echo $v; ?>', 
					<?php echo json_encode( $_students ); ?> , 
					<?php echo json_encode( $i ); ?> , 
					<?php echo json_encode( array_values( $student_ids[$i] ) ); ?> 
				);
			</script>
			<?php 
		}
	}
?>
</div>
