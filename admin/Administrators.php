<?php require_once('../Connections/loclahost.php'); ?>
<?php
 
session_start();
include("../classes/config.php");
include("../classes/functions.php");
require_once('Recordsets/ActiveStaff.php');
require_once('Recordsets/aadnewadmin.php');
//var_dump($_SESSION);

//6exit(); 
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
  <?php $nav="administrators"; ?>
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
                
<div class="accordion well well-large" id="accordion2">
  <div class="accordion-group">
    <div class="accordion-heading">
      <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseOne">
        All Staff <i class="icon-th-list pull-right"></i>
      </a>
    </div>
    <div id="collapseOne" class="accordion-body collapse in">
      <div class="accordion-inner">
        <table class="table table-hover table-bordered">
				<tr class="success">
                 <td width="33%"><strong>Name</strong></td>
                 <td width="33%"><strong>Surname</strong></td>
                 <td width="33%"><strong>Status</strong></td>
              	</tr>
                <?php do { ?>
                <tr class="info">
                 <td width="33%"><?php echo $row_admin['fname']; ?></td>
                 <td width="33%"><?php echo $row_admin['lname']; ?></td>
                 <td width="33%"><?php if ($row_admin ['status'] == 1)
echo '<span class="label label-success">Active</span>';
else
echo '<span class="label label-important">inactive</span>'; ?></td>
              	</tr>
                <?php } while ($row_admin = mysql_fetch_assoc($admin)); ?> 
		  </table>
      </div>
    </div>
  </div>
  <div class="accordion-group">
    <div class="accordion-heading">
      <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseTwo">
        Add Staff <i class=" icon-user pull-right"></i>
      </a>
    </div>
    <div id="collapseTwo" class="accordion-body collapse">
      <div class="accordion-inner">
        <form method="post" name="form1" action="<?php echo $editFormAction; ?>">
          <table align="center">
            <tr valign="baseline">
              <td nowrap align="right">First Name:</td>
              <td><input type="text" name="fname" value="" size="32"></td>
            </tr>
            <tr valign="baseline">
              <td nowrap align="right">Last Name:</td>
              <td><input type="text" name="lname" value="" size="32"></td>
            </tr>
            <tr valign="baseline">
              <td nowrap align="right">Email:</td>
              <td><input type="text" name="email" value="" size="32"></td>
            </tr>
            <tr valign="baseline">
              <td nowrap align="right">Password:</td>
              <td><input type="text" name="password" value="" size="32"></td>
            </tr>
            <tr valign="baseline">
              <td nowrap align="right">Status:</td>
              <td><input type="checkbox" name="status" value="" ></td>
            </tr>
            <tr valign="baseline">
              <td nowrap align="right">&nbsp;</td>
              <td><input class="btn" type="submit" value="Add Admin"></td>
            </tr>
          </table>
          <input type="hidden" name="MM_insert" value="form1">
        </form>
        <p>&nbsp;</p>
      </div>
    </div>
  </div>
<div class="accordion-group">
    <div class="accordion-heading">
      <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseThree">
        Edit/Remove Staff <i class="icon-edit pull-right"></i>
      </a>
    </div>
    <div id="collapseThree" class="accordion-body collapse">
      <div class="accordion-inner">
        
      </div>
    </div>
  </div>  
</div>


	   <!-- InstanceEndEditable -->
       

		</div>
    
</body>
<!-- InstanceEnd -->

</html>
<?php
mysql_free_result($admin);
?>
