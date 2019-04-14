<?php

# Pagination use these tow functions

// Display the footer pagination
function dispPages ($total,$PageSize,$p,$Param){
 if ($total>0){
  if (!is_numeric($p) || $p<1) $p=1;
  $NumOfPages=ceil($total/$PageSize);
  if ($NumOfPages>1){
   if ($p>1) echo "<a href='$PHP_SELF?p=",$p-1,"&$Param'>Back</a>";
   for ($i=1;$i<=$NumOfPages;$i++)
    if ($i!=intval($p)) echo " <a href='$PHP_SELF?p=$i&$Param'>$i</a> ";  else echo " <b>[$i]</b> ";
   if ($p<$NumOfPages) echo "<a href='$PHP_SELF?p=",$p+1,"&$Param'>Next</a>";
  }
 }
}

//Make the needed query for pagination
function makePages ($SQL,$PageSize,$p, $orderBy = ''){
 if (!is_numeric($p) || $p==0) $p=1; else $p=intval(abs($p));
 $PageStart=($p-1)*($PageSize);
 
 $orderBy = ($orderBy) ? " ORDER BY $orderBy " : '';
 
 $SQL=$SQL." $orderBy LIMIT $PageStart,$PageSize";
 return $SQL;
}


?>