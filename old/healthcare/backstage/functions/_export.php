<?php 

include_once ('../includes/excel_classes/PHPExcel.php');

if( !defined('_export_functions')) {
	
	define('_export_functions', true);

	class export_excel {
		
		public $data = array();
		private $fields = array();
		private $range = '';
		private $range1 = array();
		private $range2 = array();
		private $rangeMax = '';
		private $callback = array();
		private $useTitles = true;
		
		private $backgroundColor = '';
		private $backgroundFunc = NULL;
		private $i = 0;
	
		function __construct($title = '') {
			$this->data['title'] = $title;
			
			
			$this->range1 = range('A', 'Z');
			$this->range2 = range('A', 'Z');
		}
		function useTitles($useTitles = true) {
			$this->useTitles = $useTitles;
		}
	
		function addFields($fields) {
			if( !is_array($fields)) {
				return false;
			}
			
			foreach( $fields as $k=>$v) {
				$this->addField($k, $v);
			}
			return true;
		}
		function addField($field, $value = '', $callback = '', $parameter = '') {
			
			if( preg_match('/^[a-z_][a-z0-9_]*$/i', $field)) {
				
				if( !is_callable( $callback )) {
					$callback = '';
					$parameter = '';
				}
			
//				$callback = trim($callback);
//				if( $callback ) {
//					if( !isset( $this->callback[$callback] ) ) {
//						$this->callback[$callback] = false;
//						if( preg_match('/^[a-z_][a-z0-9_]*$/i', $callback)) {
//							$this->callback[$callback] = true;
//						}
//					}
//				}
//				if( !$this->callback[$callback]) {
//					$callback = '';
//					$parameter = '';
//				}
	
				if(!$this->range1) {
					$this->range1 = range('A', 'Z');
					$this->range = array_shift($this->range2);
				}
				$this->rangeMax = $this->range . array_shift($this->range1);
				$this->fields[ $this->rangeMax ] = array($field, $value, $callback, $parameter);
			}
		}
		function setBackground($color) {
			
			if( preg_match('/^#[a-f0-9]{6}$/i', $color)) {
				$this->backgroundColor = $color;
			} else if( preg_match('/^[a-z_][a-z0-9_]*$/i', $color)) {
				if( is_callable( $color )) {
					$this->backgroundFunc = $color;
				}
			} else {
				$this->backgroundColor = null;
				$this->backgroundFunc = null;
			}
		}
		
		function export( $export, $format = '', $ext = '' ) {
			
			ob_clean();
			
			
			$objPHPExcel = new PHPExcel();
			$objPHPExcel->getProperties()->setCreator("Techram")
			 ->setLastModifiedBy("Techram")
			 ->setTitle( $this->data['title'] )
			 ->setSubject( $this->data['title'] )
			 ->setDescription( $this->data['title'] );
//			 ->setKeywords("Contacts")
//			 ->setCategory("List");
	
			
			if($this->useTitles) {
				$this->i++;
				$eval = '$objPHPExcel->setActiveSheetIndex(0)';
				foreach($this->fields as $k=>$v) {
					$eval .= '->setCellValue(\''.$k.'1\', $this->fields['.$k.'][1])';
				}
				$eval .= ';';
		
				eval( $eval );

				$objPHPExcel->getActiveSheet()
					->getStyle("A1:{$this->rangeMax}1")
					->getFont()
					->setBold(true);
			}
			
			$objPHPExcel->setActiveSheetIndex(0);
			
			if( is_string( $export ))
			{
				$q = mysql_query( $export );

				if($q && mysql_num_rows($q)) {
		
					while ($row = mysql_fetch_assoc($q)) {
						$this->exportRow($objPHPExcel, $row );
					}
				}
			} else if( is_array( $export )) {
				foreach( $export as $row) {
					$row = (array) $row;
					$this->exportRow($objPHPExcel, $row );
				}
			}

			$date = date("d-m-Y", time());
		
			foreach($this->fields as $k=>$v) {
				$objPHPExcel->getActiveSheet()->getColumnDimension($k)->setAutoSize(true);
			}
				
			$objPHPExcel->getActiveSheet()
				->getStyle("A1:{$this->rangeMax}{$this->i}")
				->getAlignment()
				->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP)
				->setWrapText(true);
				
			header('Cache-Control: max-age=0');
			if( strtoupper($format) == 'CSV') {
				$ext = ($ext=='txt') ? 'txt' : 'CSV';
				
				header('Content-Type: text/csv');
				header("Content-Disposition: attachment;filename=\"{$this->data['title']} - {$date}.{$ext}\"");

				$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
			} else {
				$ext = ($ext=='xls') ? 'xls' : 'xlsx';
				
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header("Content-Disposition: attachment;filename=\"{$this->data['title']} - {$date}.{$ext}\"");

				$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			}
			
			$objWriter->save('php://output');
			exit;
		}
		
		private function exportRow(&$objPHPExcel, &$row ) {
		
			$this->i++;
			
			$eval = '$objPHPExcel->setActiveSheetIndex(0)';
			foreach($this->fields as $k=>$v) {
				$function = $this->fields[$k][2];
				if( $function == 'date' ) {
					$eval .= '->setCellValue(\''.$k.$this->i.'\', '.$function.'( $this->fields['.$k.'][3], $row[ $this->fields['.$k.'][0] ]) )';
				}
//				else if( $this->fields[$k][2] && $this->fields[$k][3] ) {
				else if( $this->fields[$k][3] == '--row--') {
					$eval .= '->setCellValue(\''.$k.$this->i.'\', '.$function.'( $row[ $this->fields['.$k.'][0] ], $row) )';
				}
				else if( $this->fields[$k][3] ) {
					$eval .= '->setCellValue(\''.$k.$this->i.'\', '.$function.'( $row[ $this->fields['.$k.'][0] ], $this->fields['.$k.'][3]) )';
				}
				else if( $this->fields[$k][2] ) {
					$eval .= '->setCellValue(\''.$k.$this->i.'\', '.$function.'( $row[ $this->fields['.$k.'][0] ]) )';
				}
				else {
					$eval .= '->setCellValue(\''.$k.$this->i.'\', $row[ $this->fields['.$k.'][0]  ])';
				}
			}

			$eval .= ';';
		
			eval( $eval );
			
			
			$backgroundFunc = $this->backgroundFunc;
			if($this->backgroundColor) {
				$background = $this->backgroundColor;
			} else if( $backgroundFunc ) {
				$background = $backgroundFunc( $row );
			} else {
				$background = '';
			}
			if($background) {
				$objPHPExcel->getActiveSheet()->getStyle('A'.$this->i.':'.$this->rangeMax.$this->i)->getFill() 
					->setFillType(PHPExcel_Style_Fill::FILL_SOLID) 
					->getStartColor()->setARGB( 'ff' . trim( trim($background), '#' ));
			}
		}
	}

	class export_field {

		function __construct($title, $field, $sql) {

			ob_clean();

			header('Cache-Control: max-age=0');
			header('Content-Type: text/csv');
			header("Content-Disposition: attachment;filename=\"{$title}.txt\"");
	
			$q = mysql_query( $sql );
	
			if($q && mysql_num_rows($q)) {
	
				while ($row = mysql_fetch_assoc($q)) {
					echo $row[$field]."\r\n";
				}
			}

			exit;
		}
	}
	
	
	class export_list { 
		
		public $data = array();
		private $range = array();
		private $rangeMax = 0;
		private $i = 0;
		private $objPHPExcel = false;
	
		function __construct($title = '') {
			ob_clean();

			$this->data['title'] = $title;
			
			
			$this->range = range('A', 'Z');
			$a = range('A', 'Z');
			foreach($a as $v) {
				foreach($a as $vv) {
					$this->range[] = $v.$vv;
				}
			}
			
			
			$this->objPHPExcel = new PHPExcel();
			$this->objPHPExcel->getProperties()->setCreator("Techram")
			 ->setLastModifiedBy("Techram")
			 ->setTitle( $this->data['title'] )
			 ->setSubject( $this->data['title'] )
			 ->setDescription( $this->data['title'] );
//			 ->setKeywords("Contacts")
//			 ->setCategory("List");
	
			$this->i = 0;
		}

		function addRow($row, $opt = array()) {
			ob_clean();
			if( !is_array($row)) {
				return false;
			}
			if( !is_array($opt)) {
				$opt = array();
			}
			if( $opt['bold'] ) {
				$opt['bold'] = array_filter( array_map('intval', explode(',', $opt['bold']) ));
			}

			$this->i++;
			$i=0;
		
			$f = 'A';
			$eval = '$this->objPHPExcel->setActiveSheetIndex(0)';
			foreach( $row as $k=>$v) {
				$f = $this->range[$i];
				$eval .= '->setCellValue(\''.$f.$this->i.'\', $row['.$k.'])';
				$i++;
			}
			$eval .= ';';
			$this->rangeMax = max($this->rangeMax, $i);
		
			eval( $eval );

			if( $opt['style'] == 'bold') {
				$this->objPHPExcel->getActiveSheet()
					->getStyle("A{$this->i}:{$f}{$this->i}")
					->getFont()
					->setBold(true);
			} else if( $opt['bold'] ) {
				foreach($opt['bold'] as $bold) {
					$bold--;
					$f = $this->range[$bold];
					$this->objPHPExcel->getActiveSheet()
						->getStyle("{$f}{$this->i}")
						->getFont()
						->setBold(true);
				}
			}
			
			return true;
		}

		function export( $format = '', $ext = '' ) {
			ob_clean();
			
			
			$this->objPHPExcel->setActiveSheetIndex(0);
	
			$date = date("d-m-Y", time());
		
			for($i=0; $i<=$this->rangeMax; $i++) {
				$f = $this->range[$i];
				$this->objPHPExcel->getActiveSheet()->getColumnDimension($f)->setAutoSize(true);
			}
				
			$this->objPHPExcel->getActiveSheet()
				->getStyle("A1:{$f}{$this->i}")
				->getAlignment()
				->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP)
				->setWrapText(true);
				
			header('Cache-Control: max-age=0');
			if( strtoupper($format) == 'CSV') {
				$ext = ($ext=='txt') ? 'txt' : 'CSV';
				
				header('Content-Type: text/csv');
				header("Content-Disposition: attachment;filename=\"{$this->data['title']} - {$date}.{$ext}\"");

				$objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'CSV');
			} else {
				$ext = ($ext=='xls') ? 'xls' : 'xlsx';
				
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header("Content-Disposition: attachment;filename=\"{$this->data['title']} - {$date}.{$ext}\"");

				$objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel2007');
			}

			$objWriter->save('php://output');
			exit;
		}
	}
}
