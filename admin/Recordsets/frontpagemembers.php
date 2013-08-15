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

$maxRows_Rusers = 10;
$pageNum_Rusers = 0;
if (isset($_GET['pageNum_Rusers'])) {
  $pageNum_Rusers = $_GET['pageNum_Rusers'];
}
$startRow_Rusers = $pageNum_Rusers * $maxRows_Rusers;

$colname_Rusers = "1";
if (isset($_GET['1'])) {
  $colname_Rusers = $_GET['1'];
}
mysql_select_db($database_loclahost, $loclahost);
$query_Rusers = sprintf("SELECT uname, fname, lname FROM tblmemers WHERE active = %s ORDER BY id DESC", GetSQLValueString($colname_Rusers, "int"));
$query_limit_Rusers = sprintf("%s LIMIT %d, %d", $query_Rusers, $startRow_Rusers, $maxRows_Rusers);
$Rusers = mysql_query($query_limit_Rusers, $loclahost) or die(mysql_error());
$row_Rusers = mysql_fetch_assoc($Rusers);

if (isset($_GET['totalRows_Rusers'])) {
  $totalRows_Rusers = $_GET['totalRows_Rusers'];
} else {
  $all_Rusers = mysql_query($query_Rusers);
  $totalRows_Rusers = mysql_num_rows($all_Rusers);
}
$totalPages_Rusers = ceil($totalRows_Rusers/$maxRows_Rusers)-1;
?>