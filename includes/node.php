<?php


# Counts number of nodes in a node set
function nod_count(&$node,$delimiter=','){
 if ($node=="") return 0;
 return count(split($delimiter,$node));
}

# Adds, deletes, or see if a node is in a node set
function nod_change(&$node,$element,$work=0,$delimiter=','){
 if ($work==0) return strstr($delimiter.$node.$delimiter,$delimiter.$element.$delimiter);
 switch($work):
 case 1:
  if (!nod_change($node,$element)) $node.=($node==''?'':$delimiter).$element;
  break;
 case -1:
  if ($element=='') {
   $temp=substr($node,0,strpos($node,$delimiter));
   nod_change($node,$temp,-1,$delimiter);
   return $temp;
  }
  $node=str_replace($delimiter.$element.$delimiter,$delimiter,$delimiter.$node.$delimiter);
  $node=substr($node,1,strlen($node)-2);
  break;
 endswitch; 
}

?>