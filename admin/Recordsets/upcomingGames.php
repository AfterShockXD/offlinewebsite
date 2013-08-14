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

$maxRows_UGames = 5;
$pageNum_UGames = 0;
if (isset($_GET['pageNum_UGames'])) {
  $pageNum_UGames = $_GET['pageNum_UGames'];
}
$startRow_UGames = $pageNum_UGames * $maxRows_UGames;

$colname_UGames = "1";
if (isset($_GET['Active'])) {
  $colname_UGames = $_GET['Active'];
}
mysql_select_db($database_loclahost, $loclahost);
$query_UGames = sprintf("SELECT Game, Prizes, `Start time`, `End time` FROM tblservers WHERE Active = %s ORDER BY `Start time` DESC", GetSQLValueString($colname_UGames, "int"));
$query_limit_UGames = sprintf("%s LIMIT %d, %d", $query_UGames, $startRow_UGames, $maxRows_UGames);
$UGames = mysql_query($query_limit_UGames, $loclahost) or die(mysql_error());
$row_UGames = mysql_fetch_assoc($UGames);

if (isset($_GET['totalRows_UGames'])) {
  $totalRows_UGames = $_GET['totalRows_UGames'];
} else {
  $all_UGames = mysql_query($query_UGames);
  $totalRows_UGames = mysql_num_rows($all_UGames);
}
$totalPages_UGames = ceil($totalRows_UGames/$maxRows_UGames)-1;
?>