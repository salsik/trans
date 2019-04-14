<?php 

	
		$Index = $index[$i];
		if(!is_array($Index)) {
			$Index = array();
		}
		
		$_region_id = intval( $region_id[ $i ] );
		$IndeX = 0;
		if( $_region_id > 0 ) {
			foreach($Index as $cat=>$catIndex) {

				if($catIndex=='yes') {

					$IndeX++;

					$cat = intval($cat);
					
					$sql = "SELECT * 
						FROM resellers_index 
						WHERE region_id = '{$_region_id}' 
							AND cat_id ='{$cat}' 
						";
					
					if ($action=="editexe") {
						$sql .= " AND index_id <> '". intval( $ids[$i]) ."' ";
					}
					$sql .= " LIMIT 1";
		
					$qq = mysql_query( $sql );
//				echo "$sql " . mysql_error();
					if( $qq && mysql_num_rows( $qq )) {
						$regionIndex = mysql_fetch_assoc( $qq );
						$_reseller = getDataByID('resellers', $regionIndex['index_id']);
						
						$_cat = getDataByID('category', $regionIndex['cat_id']);
						
						$Errors[] = "Category \"{$_cat['title']}\" already selected for company \"{$_reseller['title']}\" in this region!!";
						
						break;
					}
		
				}
			}
		}

		if( !$IndeX ) {
			$Errors[] = "Missing Categories!!";
		}
