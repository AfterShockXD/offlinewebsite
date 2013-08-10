<?php 

include("../classes/config.php");
include("file:///C|/wamp/www/classes/functions.php");
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
  <!-- InstanceEndEditable -->
</head>
<body>
   
  <div class="navbar navbar-static-top">
    		<div  class="navbar-inner ">
            	<a  class="brand" href="../index.php"><strong>Gamers Connected</strong></a>
            	<ul  class="nav">
                    <li class="divider-vertical"></li>
                    <li <?php if ($nav == "admin") echo "class='active'"; ?>><a href="../adminmain.php">Admin</a></li>
                    <li class="divider-vertical"></li>
                    <li <?php if ($nav == "administrators") echo "class='active'"; ?>><a href="../Administrators.php">Administrators</a></li>
                    <li class="divider-vertical"></li>
                    <li <?php if ($nav == "events") echo "class='active'"; ?>><a href="../Events.php">Events</a></li>
                    <li class="divider-vertical"></li>
                    <li <?php if ($nav == "members") echo "class='active'"; ?>><a href="../Members.php">Members</a></li>
                    <li class="divider-vertical"></li>
                    <li <?php if ($nav == "servers") echo "class='active'"; ?>><a href="../Servers.php">Servers</a></li>
                    <li class="divider-vertical"></li>
                    <li><a href="../logout.php">logout</a></li>
                    <li class="divider-vertical"></li>
                 </ul>
      		</div>
     </div>
       
       <div style="padding-top:5px" class="container">
	   <!-- InstanceBeginEditable name="main" -->
       
	   
	   
	   
	   <!-- InstanceEndEditable -->
       

		</div>
    
</body>
<!-- InstanceEnd --></html>