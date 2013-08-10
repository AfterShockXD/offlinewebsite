<?php 
include("../classes/config.php");
include("../classes/functions.php");
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
  <script src="js/admin.js"></script>
  <!-- InstanceEndEditable -->
</head>
<body>
   
  <div class="navbar navbar-static-top">
    		<div  class="navbar-inner ">
            	<a  class="brand" href="../index.php"><strong>Gamers Connected</strong></a>
            	<ul  class="nav">
                    <li class="divider-vertical"></li>
                    <li class="active"><a href="adminmain.php">Admin</a></li>
                    <li class="divider-vertical"></li>
                    <li><a href="Administrators.php">Administrators</a></li>
                    <li class="divider-vertical"></li>
                    <li><a href="Events.php">Events</a></li>
                    <li class="divider-vertical"></li>
                    <li><a href="Members.php">Members</a></li>
                    <li class="divider-vertical"></li>
                    <li><a href="Servers.php">Servers</a></li>
                    <li class="divider-vertical"></li>
                    <li><a href="logout.php">logout</a></li>
                    <li class="divider-vertical"></li>
                 </ul>
      		</div>
     </div>
       
       <div style="padding-top:5px" class="container">
	   <!-- InstanceBeginEditable name="main" --><div class="span2"></div>
       <div class="span4"><form class="form-horizontal" id="frmlogin" action='' method="POST">
  <fieldset>
    <div id="legend">
      <legend class="">Login</legend>
    </div>
    <div class="control-group">
      <!-- Username -->
      <label class="control-label"  for="uname">Email</label>
      <div class="controls">
        <input type="text" id="uname" name="uname" placeholder="" class="input-xlarge" validate="empty">
      </div>
    </div>
 
    <div class="control-group">
      <!-- Password-->
      <label class="control-label" for="pword">Password</label>
      <div class="controls">
        <input type="password" id="pword" name="pword" placeholder="" class="input-xlarge"  validate="empty">
      </div>
    </div>
 
 
    <div class="control-group">
      <!-- Button -->
      <div class="controls">
        <button class="btn btn-success" id="btn_login">Login</button>
      </div>
    </div>
  </fieldset>
</form>
<div align="center" id="responsetxt" style="width:300px"></div>
</div>

	   
	   
	   
	   <!-- InstanceEndEditable -->
       

		</div>
    
</body>
<!-- InstanceEnd --></html>