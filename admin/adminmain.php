<?php require_once('../Connections/loclahost.php'); ?>
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
 
session_start();
include("../classes/config.php");
include("../classes/functions.php");
//var_dump($_SESSION);

//exit(); 
if (!isset($_SESSION['uid'])) header('location: index.php');
?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/admin.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Home Page</title>
<!-- InstanceEndEditable -->
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap -->
    <link href="../css/bootstrap.min.css" rel="stylesheet" media="screen">
    <!--scripts-->
    <?php $nav=""; ?>
    <script src="../js/jquery-1.9.1.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/validate.js"></script>
    <link rel="stylesheet" type="text/css" href="../css/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="../css/main.css">
    <style type="text/css">
    body {
	background-image: url(../img/Subtle-Grey-Tileable-Pattern-For-Website-Background.jpg);
}
    body,td,th {
	color: #333333;
}
    </style>
  <meta charset="utf-8">
  <!-- InstanceBeginEditable name="head" -->
  <?php $nav="admin"; ?>
  <!-- InstanceEndEditable -->
</head>
<body>
   
  <div class="navbar navbar-static-top">
    		<div  class="navbar-inner ">
            	<a  class="brand" href="../index.php"><strong>Gamers Connected</strong></a>
            	<ul  class="nav">
                    <li class="divider-vertical"></li>
                    <li <?php if ($nav == "admin") echo "class='active'"; ?>><a href="adminmain.php">Admin</a></li>
                    <li class="divider-vertical"></li>
                    <li <?php if ($nav == "administrators") echo "class='active'"; ?>><a href="administrators.php">Administrators</a></li>
                    <li class="divider-vertical"></li>
                    <li <?php if ($nav == "events") echo "class='active'"; ?>><a href="events.php">Events</a></li>
                    <li class="divider-vertical"></li>
                    <li <?php if ($nav == "members") echo "class='active'"; ?>><a href="members.php">Members</a></li>
                    <li class="divider-vertical"></li>
                    <li <?php if ($nav == "servers") echo "class='active'"; ?>><a href="servers.php">Servers</a></li>
                    <li class="divider-vertical"></li>
                 </ul>
                 
                 <ul class="nav pull-right">
                  <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                      Account
                      <b class="caret"></b>
                    </a>
                    <ul class="dropdown-menu"><br />
                      <div align="center"><img src="../img/Alien%201.bmp" class="img-circle" ></div><br />
                      <div align="center"><strong><span class="label label-inverse"><?php  echo $_SESSION['name']; ?></span></strong></div>
                      <li class="divider"></li>
                      <li><a href="#"><span class="badge badge-important">0</span> Notifications</a> </li>
                      <li class="divider"></li>
                      <li><a href="#"><i class="icon-envelope"></i> Messages<span class="badge badge-info pull-right">2</span></a> </li>
                      <li><a href="#"><i class="icon-cog"></i> Settings</a></li>
                      <li><a href="logout.php"><i class="icon-lock"></i> logout</a></li>
                    </ul>
                  </li>
                </ul>
                 
                 
      		</div>
     </div>
       
       <div style="padding-top:5px" class="container">
	   <!-- InstanceBeginEditable name="main" -->
        <!--Registed Members Div-->
       <div style="padding-top:2%" class="container">
        <div class="span12 well well-small">
        	<div class="navbar navbar-static-top">
        		<div class="navbar-inner">
               	  <div style="float:left;"><a class="brand" href="#">Registered Members</a></div>
                  <div class="badge badge-inverse" style="float:right; vertical-align:middle; margin:10px;">10</div>
                </div>
             <table class="table table-hover table-bordered">
				<tr class="success">
                 <td width="33%"><strong>Name</strong></td>
                 <td width="33%"><strong>Surname</strong></td>
                 <td width="33%"><strong>Status</strong></td>
              	</tr>
                <tr class="info">
                 <td width="33%">Spaceholder</td>
                 <td width="33%">Spaceholder</td>
                 <td width="33%">Spaceholder</td>
              	</tr>
			 </table>  
       		</div>
      	 </div>
       
       <!--Active Admin-->
       <div class="span12 well well-small">
        	<div class="navbar navbar-static-top">
        		<div class="navbar-inner">
               	  <div style="float:left;"><a class="brand" href="#">Active Staff</a></div>
                  <div class="badge badge-inverse" style="float:right; vertical-align:middle; margin:10px;">10</div>
                </div>
              <table class="table table-hover table-bordered">
				<tr class="success">
                 <td width="33%"><strong>Name</strong></td>
                 <td width="33%"><strong>Surname</strong></td>
                 <td width="33%"><strong>Status</strong></td>
              	</tr>
                <tr class="info">
                 <td width="33%">Spaceholder</td>
                 <td width="33%">Spaceholder</td>
                 <td width="33%">Spaceholder</td>
              	</tr>
			 </table> 
       		</div>
      	 </div>
          <!--Active Active Servers-->
       <div class="span12 well well-small">
        	<div class="navbar navbar-static-top">
        		<div class="navbar-inner">
               	  <div style="float:left;"><a class="brand" href="#">Active Server</a></div>
                  <div class="badge badge-inverse" style="float:right; vertical-align:middle; margin:10px;">10</div>
                </div>
 			 <table class="table table-hover table-bordered">
				<tr class="success">
                 <td width="33%"><strong>Server Name</strong></td>
                 <td width="33%"><strong>Game</strong></td>
                 <td width="33%"><strong>Internet Protocol</strong></td>
              	</tr>
                <?php do { ?>
                <tr class="info">
                 <td width="33%"><?php echo $row_ActiveEvent['SName']; ?></td>
                 <td width="33%"><?php echo $row_ActiveEvent['Game']; ?></td>
                 <td width="33%"><?php echo $row_ActiveEvent['IP']; ?></td>
             	</tr>
                <?php } while ($row_ActiveEvent = mysql_fetch_assoc($ActiveEvent)); ?>
			 </table>
       		</div>
      	 </div>
	   </div>
	   
	   
	   
	   <!-- InstanceEndEditable -->
       

		</div>
    
</body>
<!-- InstanceEnd -->

</html>
<?php
mysql_free_result($ActiveEvent);
?>
