<?php require_once('../Connections/loclahost.php'); 
	  require_once('../Connections/loclahost.php');
	  require_once('Recordsets/insertmembers.php');
	  require_once('Recordsets/memberlist.php');
 ?>

<?php
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
  <?php $nav="members"; ?>
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
                   <!-- <li <?php if ($nav == "events") echo "class='active'"; ?>><a href="gallery.php">Gallary</a></li>
                    <li class="divider-vertical"></li> -->
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
          <li class="active"><a href="#home"><i class="icon-th-list"></i> Members</a></li>
          <li><a href="#profile"><i class="icon-plus"></i> Create Member</a></li>
      </ul>
 
    <div class="tab-content well well-small">
      <div class="tab-pane active" id="home">
      <div class="navbar navbar-static-top">
        		<div class="navbar-inner">
               	  <div style="float:left;"><a class="brand" href="#">Member List</a></div>
                  <div class="badge badge-inverse" style="float:right; vertical-align:middle; margin:10px;">Ammount</div>
                </div></div>
        <table align="center" class="table table-condensed">
          <tr class="info">
            <td><strong>Username</strong></td>
            <td><strong>First Name</strong></td>
            <td><strong>Last Name</strong></td>
            <td><strong>Active</strong></td>
          </tr>
          <?php do { ?>
            <tr class="success">
              <td><?php echo $row_Usersregisers['uname']; ?></td>
              <td><?php echo $row_Usersregisers['fname']; ?></td>
              <td><?php echo $row_Usersregisers['lname']; ?></td>
              <td><?php if ($row_Usersregisers['active'] == 1) echo '<span class="label label-success">Active</span>'; else echo '<span class="label label-important">inactive</span>'; ?></td>
            </tr>
            <?php } while ($row_Usersregisers = mysql_fetch_assoc($Usersregisers)); ?>
        </table>
      </div>
      <div class="tab-pane" id="profile">
        <form method="post" name="form1" action="<?php echo $editFormAction; ?>">
          <table align="center">
            <tr valign="baseline">
              <td nowrap align="right">User Name:</td>
              <td><input type="text" name="uname" value="" size="32"></td>
            </tr>
            <tr valign="baseline">
              <td nowrap align="right">First Name:</td>
              <td><input type="text" name="fname" value="" size="32"></td>
            </tr>
            <tr valign="baseline">
              <td nowrap align="right">Last Name:</td>
              <td><input type="text" name="lname" value="" size="32"></td>
            </tr>
            <tr valign="baseline">
              <td nowrap align="right">Password:</td>
              <td><input type="password" name="password" value="123pass" size="32"></td>
            </tr>
            <tr valign="baseline">
              <td nowrap align="right">Active:</td>
              <td><input type="checkbox" name="active" value="" checked></td>
            </tr>
            <tr valign="baseline">
              <td nowrap align="right">&nbsp;</td>
              <td><input class="btn btn-primary" type="submit" value="Add member"></td>
            </tr>
          </table>
          <input type="hidden" name="MM_insert" value="form1">
        </form>
      </div>
    </div>
 
<script>
  $(function () {
    $('#myTab a:fist').tab('show');
  })
  
  $('#myTab a').click(function (e) {
  e.preventDefault();
  $(this).tab('show');
})
</script>
	   
	   
	   <!-- InstanceEndEditable -->
       

		</div>
    
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($Usersregisers);
?>
