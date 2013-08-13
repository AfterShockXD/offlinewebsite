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

$colname_activestaff = "1";
if (isset($_GET['status'])) {
  $colname_activestaff = $_GET['status'];
}
mysql_select_db($database_loclahost, $loclahost);
$query_activestaff = sprintf("SELECT fname, lname, status FROM tbladmin WHERE status = %s ORDER BY id ASC", GetSQLValueString($colname_activestaff, "text"));
$activestaff = mysql_query($query_activestaff, $loclahost) or die(mysql_error());
$row_activestaff = mysql_fetch_assoc($activestaff);
$totalRows_activestaff = mysql_num_rows($activestaff);
?>