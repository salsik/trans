<?php

	if(!$_index_table_included ) {
		$_index_table_included = 0;
	}
	
	$_index_table_included++;
	$Index = $index[$oldrecord[$i]];
	if( !is_array($Index)) {
		$Index = array();
	}
	$IndexTds = 5;
	
	$IndexCatDefault = false;
	
	?>
	<div style="margin-left: 210px;">
	<?php 
	$IndexCat = $Index[ -1 ];
	$checked = checked2($IndexCat, 'yes', $IndexCatDefault);
	?>
	<label>
		<input type="checkbox" name="index[<?php echo $i; ?>][-1]" <?php echo $checked; ?> value="yes" onClick="checkallcats(this)" />
		All Categories.
	</label>
	<div class="clear"></div>
	<?php 

	foreach($Categories as $k=>$category) {

		$IndexCat = $Index[ $category['id'] ];
		$checked = checked2($IndexCat, 'yes', $IndexCatDefault);
		?>
		<label>
			<input type="checkbox"  class="boxes" name="index[<?php echo $i; ?>][<?php echo $category['id']; ?>]" <?php echo $checked; ?> value="yes" /> 
			<?php echo $category['title']; ?>
		</label>
		<?php 
	}
?>
	</div>

	<script>

    function checkallcats(cb){
        
   			//alert(cb.checked) ;
   			var boxes = document.getElementsByClassName("boxes");
   			
   			if (cb.checked==true)
  			{
	        	for(var i = 0; i < boxes.length; i++)
		        {
		            boxes.item(i).checked = true;
		        }
			}
			else
			{
				for(var i = 0; i < boxes.length; i++)
		        {
		            boxes.item(i).checked = false;
		        }


			}
 
    }
</script>


