<?php 
include("../classes/config.php");
include("../classes/functions.php");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Bootstrap 101 Template</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap -->
    <link href="../css/bootstrap.min.css" rel="stylesheet" media="screen">
    <style type="text/css">
    body {
	background-image: url(img/AdminBack.jpg);
	background-repeat:no-repeat;
	background-attachment:fixed;
	background-position:center;
}
    </style>
    <script src="js/admin.js"></script>
    <script src="../js/validate.js"></script>
<meta charset="utf-8">
  </head>
  <body>
  <div style="padding-top:15%"></div>
    <div class="container">
	<div class="row">
	  <div class="span4 offset4 well">
			<legend>Please Sign In</legend>
          	<div class="alert alert-error">
                <a class="close" data-dismiss="alert" href="#">×</a>Incorrect Username or Password!
            </div>
			<form method="POST" id="frmlogin" action="" accept-charset="UTF-8">
			<input type="text" id="uname" class="span4" name="username" placeholder="Username">
			<input type="password" id="pword" class="span4" name="password" placeholder="Password">
            <label class="checkbox">
            	<input type="checkbox" name="remember" value="1"> Remember Me
            </label>
			<button type="submit" name="submit" class="btn btn-info btn-block">Sign in</button>
			</form>    
		</div>
	</div>
</div>
  <script src="http://code.jquery.com/jquery.js"></script>
  <script src="../js/bootstrap.min.js"></script>
</body>
</html>