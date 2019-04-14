<?php 

if( defined('index_table')) {
	$Index = &$index[$i];
	if(!is_array($Index)) {
		$Index = array();
	}
	$IndeX = 0;
	foreach($Index as $cat=>$catIndex) {
		if($Categories[ $cat ] && $catIndex=='yes') {
			$IndeX++;
			break;
		}
	}
	if( !$IndeX ) {
		if( !$Index[ -1 ]) {
			$Errors[] = "Missing Categories!!";
		}
	}


	do {
		if( $Errors ) {
			break;
		}

		if( !defined('index_table_competitions')) {
			break;
		}

		$sqlCompetition = (array) $sqlCompetition;
		if( !$sqlCompetition['reseller_id'] || !$sqlCompetition['month'] || !$sqlCompetition['year'] ) {
			break;
		}
		
		$sqlCompetition['reseller_id'] = intval( $sqlCompetition['reseller_id'] );
		$sqlCompetition['month'] = intval( $sqlCompetition['month'] );
		$sqlCompetition['year'] = intval( $sqlCompetition['year'] );

		$editID = ($action == 'addexe') ? 0 : intval( $ids[$i] );
		$_checkSql = ($action == 'editexe') ? " AND id <> '".intval( $ids[$i] )."' " : '';
		
		$query = "SELECT * FROM `competitions_cat_index` WHERE index_id IN (
			SELECT id FROM `competitions` 
				WHERE reseller_id = '{$sqlCompetition['reseller_id']}' 
				AND month = '{$sqlCompetition['month']}' 
				AND year = '{$sqlCompetition['year']}' 
				{$_checkSql}
		) ";
	
		if( $Index[ -1 ] ) {
			
			$_query = "{$query} LIMIT 1";

			$qq = mysql_query( $_query );
			if( $qq && mysql_num_rows($qq)) {
				$Errors[] = "Can't set \"All Categories\" for this quiz, you already have quiz for this month!";
				break;
			}
		}
		else {
			
			$_query = "{$query} AND cat_id='-1' LIMIT 1";

			$qq = mysql_query( $_query );
			if( $qq && mysql_num_rows($qq)) {
				$Errors[] = "Can't save this quiz, you already have quiz set for \"All Categories\" in this month!";
				break;
			}

			foreach($Index as $cat=>$catIndex) {
				if($Categories[ $cat ] && $catIndex=='yes') {
					$_cat = $Categories[ $cat ];
					$_query = "{$query} AND cat_id = '{$_cat['id']}' LIMIT 1";

					$qq = mysql_query( $_query );
					if( $qq && mysql_num_rows($qq)) {
						$Errors[] = "Can't set \"{$_cat['title']}\" for this quiz, you already have quiz for this category in this month!";
						break;
					}
				}
			}
		}
		
	} while( false );
}