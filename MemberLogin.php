<?php 
include("classes/config.php");
include("classes/functions.php");
//var_dump($_SESSION);

//exit()
?>
<!DOCTYPE html>
<html>
<head>
<title>Home Page</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet" media="screen">
    <!--scripts-->
    <?php $nav=""; ?>
    <script src="js/jquery-1.9.1.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/validate.js"></script>
    <link rel="stylesheet" type="text/css" href="css/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="css/main.css">
    <style type="text/css">
    body {
	background-image: url(img/BackGround.jpg);
}
    body,td,th {
	color: #333333;
}
    </style>
  <meta charset="utf-8">
<script src="js/members.js"></script>
</head>
<body>
<div style="padding-top:15%" class="container">
	<div class="span2"></div>
       <div class="span4  well well-large" style="width:700px">
       <form class="form-horizontal" id="frmloginmem" action='' method="POST">
         <fieldset>
           <div id="legend">
             <div class="navbar navbar-static-top">
        		<div class="navbar-inner">
               	  <div><a class="brand pull-left" href="#">Gamers Connected Core Login</a></div>
                  <div class="badge badge-inverse" style="float:right; vertical-align:middle; margin:10px;"></div>
                </div>
                <br />
           </div>
           <div align="center" id="responsetxt" style="width:700px"></div>
           <div class="control-group input-prepend">
             <!-- Username -->
             <label class="control-label"  for="uname">Username</label>
             <div class="controls">
              <span class="add-on"><i class="icon-user"></i></span> <input type="text" id="uname" name="uname" placeholder="" class="input-xlarge" validate="empty">
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
               <button class="btn btn-success" id="btn_login">Login</button>
               <!-- LiveZilla Text Chat Link Code (ALWAYS PLACE IN BODY ELEMENT) --><script type="text/javascript" id="lz_textlink" src="http://127.0.0.1/offlinewebsite/LiveZilla/image.php?acid=10942&amp;tl=1&amp;srv=aHR0cDovLzEyNy4wLjAuMS9vZmZsaW5ld2Vic2l0ZS9MaXZlWmlsbGEvY2hhdC5waHA,YWNpZD0wNDExOA__&amp;tlont=TGl2ZSBIZWxwIChPbmxpbmUp&amp;tloft=TGl2ZSBIZWxwIChPZmZsaW5lKQ__"></script><!-- http://www.LiveZilla.net Text Chat Link Code --><!-- LiveZilla Tracking Code (ALWAYS PLACE IN BODY ELEMENT) --><div id="livezilla_tracking" style="display:none"></div><script type="text/javascript">
var script = document.createElement("script");script.async=true;script.type="text/javascript";var src = "http://127.0.0.1/offlinewebsite/LiveZilla/server.php?acid=44360&request=track&output=jcrpt&nse="+Math.random();setTimeout("script.src=src;document.getElementById('livezilla_tracking').appendChild(script)",1);</script><noscript><img src="http://127.0.0.1/offlinewebsite/LiveZilla/server.php?acid=44360&amp;request=track&amp;output=nojcrpt" width="0" height="0" style="visibility:hidden;" alt=""></noscript><!-- http://www.LiveZilla.net Tracking Code -->
             </div>
           </div>
         </fieldset>
  </form>
  <div align="center" id="responsetxt" style="width:300px"></div>
</div>

	   
	   
	   </div>
    
</body>
</html>