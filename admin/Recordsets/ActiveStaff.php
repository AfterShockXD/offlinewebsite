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

$maxRows_activestaff = 5;
$pageNum_activestaff = 0;
if (isset($_GET['pageNum_activestaff'])) {
  $pageNum_activestaff = $_GET['pageNum_activestaff'];
}
$startRow_activestaff = $pageNum_activestaff * $maxRows_activestaff;

$colname_activestaff = "1";
if (isset($_GET['Active'])) {
  $colname_activestaff = $_GET['Active'];
}
mysql_select_db($database_loclahost, $loclahost);
$query_activestaff = sprintf("SELECT id, fname, lname, status FROM tbladmin WHERE status = %s ORDER BY id ASC", GetSQLValueString($colname_activestaff, "int"));
$query_limit_activestaff = sprintf("%s LIMIT %d, %d", $query_activestaff, $startRow_activestaff, $maxRows_activestaff);
$activestaff = mysql_query($query_limit_activestaff, $loclahost) or die(mysql_error());
$row_activestaff = mysql_fetch_assoc($activestaff);

if (isset($_GET['totalRows_activestaff'])) {
  $totalRows_activestaff = $_GET['totalRows_activestaff'];
} else {
  $all_activestaff = mysql_query($query_activestaff);
  $totalRows_activestaff = mysql_num_rows($all_activestaff);
}
$totalPages_activestaff = ceil($totalRows_activestaff/$maxRows_activestaff)-1;
?>