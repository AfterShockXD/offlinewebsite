<?php 
include("../classes/config.php");
include("../classes/functions.php");
?>
<!DOCTYPE html>
<html>
<head>
<title>Home Page</title>
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
	background-image: url(../img/BackGround.jpg);
}
    body,td,th {
	color: #333333;
}
    </style>
  <meta charset="utf-8">
<script src="js/admin.js"></script>
</head>
<body>
<div style="padding-top:15%" class="container">
	<div class="span2"></div>
       <div class="span4  well well-large" style="width:700px">
       <form class="form-horizontal" id="frmlogin" action='' method="POST">
         <fieldset>
           <div id="legend">
             <div class="navbar navbar-static-top">
        		<div class="navbar-inner">
               	  <div><a class="brand pull-left" href="#">Admin Core Login</a></div>
                  <div class="badge badge-inverse" style="float:right; vertical-align:middle; margin:10px;"></div>
                </div>
                <br />
           </div>
           <div align="center" id="responsetxt" style="width:700px"></div>
           <div class="control-group input-prepend">
             <!-- Username -->
             <label class="control-label"  for="uname">Email</label>
             <div class="controls">
              <span class="add-on"><i class="icon-envelope"></i></span> <input type="text" id="uname" name="uname" placeholder="" class="input-xlarge" validate="empty">
             </div>
           </div>
           
           <div class="control-group input-prepend">
             <!-- Password-->
             <label class="control-label " for="pword">Password</label>
             <div class="controls">
              <span class="add-on"><i class="icon-lock"></i></span>  <input type="password" id="pword" name="pword" placeholder="" class="input-xlarge"  validate="empty">
             </div>
           </div>
           
           
           <div class="control-group">
             <!-- Button -->
             <div class="controls">
               <button style="width:100px" class="btn btn-success" id="btn_login">Login</button>
             </div>
           </div>
         </fieldset>
  </form>
  <div align="center" id="responsetxt" style="width:300px"></div>
</div>

	   
	   
	   </div>
    
</body>
</html>