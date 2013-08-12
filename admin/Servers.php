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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertSQL = sprintf("INSERT INTO tblservers (SName, Game, IP, Prizes, `Start time`, `End time`, Active) VALUES (%s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['SName'], "text"),
                       GetSQLValueString($_POST['Game'], "text"),
                       GetSQLValueString($_POST['IP'], "text"),
                       GetSQLValueString($_POST['Prizes'], "text"),
                       GetSQLValueString($_POST['Start_time'], "text"),
                       GetSQLValueString($_POST['End_time'], "text"),
                       GetSQLValueString(isset($_POST['Active']) ? "true" : "", "defined","1","0"));

  mysql_select_db($database_loclahost, $loclahost);
  $Result1 = mysql_query($insertSQL, $loclahost) or die(mysql_error());

  $insertGoTo = "Servers.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo));
}

$maxRows_Server = 10;
$pageNum_Server = 0;
if (isset($_GET['pageNum_Server'])) {
  $pageNum_Server = $_GET['pageNum_Server'];
}
$startRow_Server = $pageNum_Server * $maxRows_Server;

mysql_select_db($database_loclahost, $loclahost);
$query_Server = "SELECT * FROM tblservers ORDER BY id ASC";
$query_limit_Server = sprintf("%s LIMIT %d, %d", $query_Server, $startRow_Server, $maxRows_Server);
$Server = mysql_query($query_limit_Server, $loclahost) or die(mysql_error());
$row_Server = mysql_fetch_assoc($Server);

if (isset($_GET['totalRows_Server'])) {
  $totalRows_Server = $_GET['totalRows_Server'];
} else {
  $all_Server = mysql_query($query_Server);
  $totalRows_Server = mysql_num_rows($all_Server);
}
$totalPages_Server = ceil($totalRows_Server/$maxRows_Server)-1;
 
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
  <?php $nav="servers"; ?>
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
                    <li <?php if ($nav == "events") echo "class='active'"; ?>><a href="gallery.php">Gallary</a></li>
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
       
	   
		
	   
       
       <ul class="nav nav-tabs" id="myTab">
  <li class="active"><a href="#home">View</a></li>
  <li><a href="#profile">Add</a></li>
  <li><a href="#messages">Edit</a></li>
  <li><a href="#settings">Remove</a></li>
</ul>
 
<div class="tab-content">
  <div class="tab-pane active" id="home">
  <table align="center" class="table table-condensed well well-large">
<tr>
    <td>ID</td>
    <td>Server Name</td>
    <td>Game</td>
    <td>IP</td>
    <td>Active</td>
    <td>Prizes</td>
    <td>Start time - End time</td>
  </tr>
  <?php do { ?>
  <tr>
    <td><?php echo $row_Server['id']; ?></td>
    <td><?php echo $row_Server['SName']; ?></td>
    <td><?php echo $row_Server['Game']; ?></td>
    <td><?php echo $row_Server['IP']; ?></td>
    <td><?php if ($row_Server ['Active'] == 1)
echo '<span class="label label-success">Active</span>';
else
echo '<span class="label label-important">inactive</span>'; ?></td>
    <td><?php echo $row_Server['Prizes']; ?></td>
    <td><?php echo $row_Server['Start time']; ?> - <?php echo $row_Server['End time']; ?></td>
  </tr>
  <?php } while ($row_Server = mysql_fetch_assoc($Server)); ?>
</table>
  
  
  
  
  </div>
  <div class="tab-pane" id="profile">
    <form method="post" name="form1" action="<?php echo $editFormAction; ?>">
      <table align="center">
        <tr valign="baseline">
          <td nowrap align="right">Server Name:</td>
          <td><input type="text" name="SName" value="" size="32"></td>
        </tr>
        <tr valign="baseline">
          <td nowrap align="right">Game:</td>
          <td><input type="text" name="Game" value="" size="32"></td>
        </tr>
        <tr valign="baseline">
          <td nowrap align="right">IP:</td>
          <td><input type="text" name="IP" value="" size="32"></td>
        </tr>
        <tr valign="baseline">
          <td nowrap align="right">Prizes:</td>
          <td><input type="text" name="Prizes" value="" size="32"></td>
        </tr>
        <tr valign="baseline">
          <td nowrap align="right">Start time:</td>
          <td><input type="text" name="Start_time" value="" size="32"></td>
        </tr>
        <tr valign="baseline">
          <td nowrap align="right">End time:</td>
          <td><input type="text" name="End_time" value="" size="32"></td>
        </tr>
        <tr valign="baseline">
          <td nowrap align="right">Active:</td>
          <td><input type="checkbox" name="Active" value="" ></td>
        </tr>
        
        <tr valign="baseline">
        
          <td nowrap align="right">&nbsp;</td>
          <td><input class="btn btn-large btn-inverse" type="submit" value="Insert record"></td>
        </tr>
      </table>
      <input type="hidden" name="MM_insert" value="form1">
    </form>
    <p>&nbsp;</p>
  </div>
  <div class="tab-pane" id="messages">
  
  
  
  
  .ccc..</div>
  <div class="tab-pane" id="settings">
  
  
  
  
  .aaa..</div>
</div>
 
<script>
  $(function () {
    $('#myTab a:first').tab('show');
  })
  
  $('#myTab a').click(function (e) {
				  e.preventDefault();
				  $(this).tab('show');
					})
  
</script>
	   
	   
	   <!-- InstanceEndEditable -->
       

		</div>
    
</body>
<!-- InstanceEnd -->
<table border="0">
  
  
</table>
</html>
<?php
mysql_free_result($Server);
?>
