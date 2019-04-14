<?php

	if(!$_index_table_included ) {
		$_index_table_included = 0;
	?>
<script type="text/javascript">
<!--
	$(document).ready(function(){
		$('.index-cat').each(function(){
			var $sAll = $('<a href="#">Select All</a>').click(function(){
				var parent = $(this).parents('td:first');
				$('.'+parent.data('ref')+' input[type="checkbox"]').prop('checked', true);
				return false;
			});
			var $sNone = $('<a href="#">Select None</a>').click(function(){
				var parent = $(this).parents('td:first');
				$('.'+parent.data('ref')+' input[type="checkbox"]').prop('checked', false);
				return false;
			});

			$(this).html(' - ').prepend( $sAll ).append( $sNone );
		});
	});
//-->
</script>
		
		
		
	<?php 
	}
	
	$_index_table_included++;
	$Index = $index[$oldrecord[$i]];
	if( !is_array($Index)) {
		$Index = array();
	}
	$IndexTds = 5;

	
	echo '<table width="60%" border="0" cellpadding="5" cellspacing="0">';
	
	foreach($Categories as $category) {
		if( !isset( $SubCategories[ $category['id'] ] ) ) {
			$SubCategories[ $category['id'] ] = array();
			$q = mysql_query("SELECT id, title FROM category_sub WHERE cat_id='{$category['id']}' ORDER BY rank DESC");
			if($q && mysql_num_rows($q)) {
				while( $r = mysql_fetch_assoc($q)) {
					$SubCategories[ $category['id'] ][ $r['id'] ] = $r;
				}
			}
		}
		
		$IndexCat = &$Index[ $category['id'] ];
		$IndexCatClass = "index-{$_index_table_included}-{$category['id']}";
		$IndexCatDefault = ($action=='add' && !$_POST) ? true : false;
		if( !is_array( $IndexCat )) {
			$IndexCat = array();
		}
		$checked = checked($IndexCat[0], 'yes');
		?><tr><td colspan="<?php echo $IndexTds; ?>" data-ref="<?php echo $IndexCatClass; ?>" ><b>
			<input type="checkbox" name="index[<?php echo $i; ?>][<?php echo $category['id']; ?>][0]" <?php echo $checked; ?> value="yes" /> 
			<?php echo $category['title']; ?>
			</b><span class="index-cat"></span></td></tr><?php 
		
		echo "<tr class='{$IndexCatClass}'>";
		
		$x = 0;
		$count = count( $SubCategories[ $category['id'] ] );
		foreach($SubCategories[ $category['id'] ] as $sub) {
			$x++;
			$checked = checked2($IndexCat[ $sub['id'] ], 'yes', $IndexCatDefault);
			?><td>
				<input type="checkbox" name="index[<?php echo $i; ?>][<?php echo $category['id']; ?>][<?php echo $sub['id']; ?>]" <?php echo $checked; ?> value="yes" /> 
				<?php echo $sub['title']; ?>
				</td><?php 
	
			if( !($x%$IndexTds) && $x != $count ) {
				echo "</tr><tr class='{$IndexCatClass}'>";
			}
		}
		
		echo "</tr>";
	}

	echo '</table>';
