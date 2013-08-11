<?php
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  if (PHP_VERSION < 6) {
    $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  }

  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
  }
  return $theValue;
}
}

$maxRows_ActiveEvent = 5;
$pageNum_ActiveEvent = 0;
if (isset($_GET['pageNum_ActiveEvent'])) {
  $pageNum_ActiveEvent = $_GET['pageNum_ActiveEvent'];
}
$startRow_ActiveEvent = $pageNum_ActiveEvent * $maxRows_ActiveEvent;

$colname_ActiveEvent = "1";
if (isset($_GET['Active'])) {
  $colname_ActiveEvent = $_GET['Active'];
}
mysql_select_db($database_loclahost, $loclahost);
$query_ActiveEvent = sprintf("SELECT * FROM tblservers WHERE Active = %s ORDER BY id ASC", GetSQLValueString($colname_ActiveEvent, "int"));
$query_limit_ActiveEvent = sprintf("%s LIMIT %d, %d", $query_ActiveEvent, $startRow_ActiveEvent, $maxRows_ActiveEvent);
$ActiveEvent = mysql_query($query_limit_ActiveEvent, $loclahost) or die(mysql_error());
$row_ActiveEvent = mysql_fetch_assoc($ActiveEvent);

if (isset($_GET['totalRows_ActiveEvent'])) {
  $totalRows_ActiveEvent = $_GET['totalRows_ActiveEvent'];
} else {
  $all_ActiveEvent = mysql_query($query_ActiveEvent);
  $totalRows_ActiveEvent = mysql_num_rows($all_ActiveEvent);
}
$totalPages_ActiveEvent = ceil($totalRows_ActiveEvent/$maxRows_ActiveEvent)-1;
 

?>