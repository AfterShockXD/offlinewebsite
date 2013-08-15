<?php require_once('Connections/loclahost.php');

	session_start();
	if (!isset($_SESSION['uid'])) header('location: memberlogin.php');
	//var_dump($_SESSION);

	//exit();
?>
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

$colname_ActvServers = "1";
if (isset($_GET['Active'])) {
  $colname_ActvServers = $_GET['Active'];
}
mysql_select_db($database_loclahost, $loclahost);
$query_ActvServers = sprintf("SELECT SName, Game, IP, Active FROM tblservers WHERE Active = %s ORDER BY id ASC", GetSQLValueString($colname_ActvServers, "int"));
$ActvServers = mysql_query($query_ActvServers, $loclahost) or die(mysql_error());
$row_ActvServers = mysql_fetch_assoc($ActvServers);
$totalRows_ActvServers = mysql_num_rows($ActvServers);
?>


<!DOCTYPE html>
<html>
<head>
    <title>Servers</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet" media="screen">
    <style type="text/css">
    body {
	background-image: url(img/Subtle-Grey-Tileable-Pattern-For-Website-Background.jpg);
}
    </style>
  <meta charset="utf-8">
</head>
  <body>
   <div class="navbar navbar-static-top">
    		<div  class="navbar-inner ">
            
<a  class="brand" href="#"><strong>Gamers Connected</strong></a>
            	<ul class="nav">
                <li class="divider-vertical"></li>
             	<li ><a href="index.php">Home</a></li>
                <li class="divider-vertical"></li>
              	<li><a href="gallery.php">Gallery</a></li>
                <li class="divider-vertical"></li>
              	<li ><a href="FAQ.php">FAQ</a></li>
                <li class="divider-vertical"></li>
                <li class="active"><a href="serverlist.php">Servers</a></li>
                <li class="divider-vertical"></li>
                <li><a href="downloads.php">Downloads</a></li>
                <li class="divider-vertical"></li>
            	</ul>
                <!--DropDown-->
              <ul class="nav pull-right">
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                          <?php  echo $_SESSION['name']; ?>
                            <b class="caret"></b>
                        </a>
                        <ul class="dropdown-menu">
                            <div align="center"><img style="width:100px; height:100px;" src="img/png.png" class="img-circle"  ></div>
                            <div align="center"><strong><?php  echo $_SESSION['name']; ?></strong></div>


                            <li class="divider"></li>
                            <li><a href="#"><i class="icon-cog"></i> Edit Account</a></li>
                            <li><a href="admin/logout.php"><i class="icon-off"></i> Logout</a></li>
                            <li class="divider"></li>
                            <li><a href="admin/index.php"><i class="icon-star-empty"></i> Staff login</a></li>
                        </ul>
                </li>
              </ul>
    		</div>
  </div>
   
   
 <div style="padding-top:2%" class="container">
  <table class="table table-hover table-bordered">
  <tr class="success">
    <td width="20%"><strong>Server Name</strong></td>
    <td width="20%"><strong>Game </strong></td>
    <td width="20%"><strong>IP</strong></td>
    <td width="10%"><strong>Active / Inactive</strong></td>
    
    <td width="20%"><strong>Request to Acive</strong></td>
  </tr> 
  <?php do { ?>
  <tr class="info">
  <td><?php echo $row_ActvServers['SName']; ?></td>
  <td><?php echo $row_ActvServers['Game']; ?></td>
  <td><?php echo $row_ActvServers['IP']; ?></td>
  <td><?php if ($row_ActvServers['Active'] == 1)
echo '<span class="label label-success">Active</span>';
else
echo '<span class="label label-important">inactive</span>'; ?></td>
  <td><a href="#"><i class="icon-upload"></i> <!-- LiveZilla Text Chat Link Code (ALWAYS PLACE IN BODY ELEMENT) --><script type="text/javascript" id="lz_textlink" src="http://127.0.0.1/offlinewebsite/LiveZilla/image.php?acid=ec46b&amp;tl=1&amp;srv=aHR0cDovLzEyNy4wLjAuMS9vZmZsaW5ld2Vic2l0ZS9MaXZlWmlsbGEvY2hhdC5waHA,YWNpZD0xYmMzOA__&amp;tlont=UmVxdWVzdA__&amp;tloft=UmVxdWVzdCAob2ZmbGluZSk_"></script><!-- http://www.LiveZilla.net Text Chat Link Code --><!-- LiveZilla Tracking Code (ALWAYS PLACE IN BODY ELEMENT) --><div id="livezilla_tracking" style="display:none"></div><script type="text/javascript">
var script = document.createElement("script");script.async=true;script.type="text/javascript";var src = "http://127.0.0.1/offlinewebsite/LiveZilla/server.php?acid=7556d&request=track&output=jcrpt&nse="+Math.random();setTimeout("script.src=src;document.getElementById('livezilla_tracking').appendChild(script)",1);</script><noscript><img src="http://127.0.0.1/offlinewebsite/LiveZilla/server.php?acid=7556d&amp;request=track&amp;output=nojcrpt" width="0" height="0" style="visibility:hidden;" alt=""></noscript><!-- http://www.LiveZilla.net Tracking Code --></a></td>
  </tr>
   <?php } while ($row_ActvServers = mysql_fetch_assoc($ActvServers)); ?>
 </table>
 </div>  
   
   
  <script src="http://code.jquery.com/jquery.js"></script>
  <script src="js/bootstrap.min.js"></script>
</body>
</html>
<?php
mysql_free_result($ActvServers);
?>
