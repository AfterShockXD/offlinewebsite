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
    <?php $nav="events"; ?>
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
  <li class="active"><a href="#home">Home</a></li>
  <li><a href="#profile">Profile</a></li>
  <li><a href="#messages">Messages</a></li>
  <li><a href="#settings">Settings</a></li>
</ul>

<div class="tab-content">
  <div class="tab-pane active" id="home">



  </div>
  <div class="tab-pane" id="profile">




  </div>
  <div class="tab-pane" id="messages">




  </div>
  <div class="tab-pane" id="settings">



  </div>
</div>
<script>
$('#myTab a').click(function (e) {
  e.preventDefault();
  $(this).tab('show');
})

  $(function () {
    $('#myTab a:last').tab('show');
  })
</script>



	   <!-- InstanceEndEditable -->


		</div>

</body>
<!-- InstanceEnd --></html>