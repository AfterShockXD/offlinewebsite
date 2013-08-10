<?php
include("classes/config.php");
include("classes/functions.php");
?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/front.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Home Page</title>
<!-- Test -->
<!-- InstanceEndEditable -->
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet" media="screen">
    <!--scripts-->

    <script src="js/jquery-1.9.1.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <link rel="stylesheet" type="text/css" href="css/bootstrap.css">
    <style type="text/css">
    body {
	background-image: url(img/Subtle-Grey-Tileable-Pattern-For-Website-Background.jpg);
}
    body,td,th {
	color: #333333;
}
    </style>
  <meta charset="utf-8">
  <!-- InstanceBeginEditable name="head" -->

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <meta charset="utf-8">
<!-- InstanceEndEditable -->
</head>
<body>

  <div class="navbar navbar-static-top">
    		<div  class="navbar-inner ">
            	<a  class="brand" href="#"><strong>Gamers Connected</strong></a>
            	<ul class="nav">
                    <li class="divider-vertical"></li>
                    <li class="active"><a href="index.php">Home</a></li>
                    <li class="divider-vertical"></li>
                    <li><a href="gallery.php">Gallery</a></li>
                    <li class="divider-vertical"></li>
                    <li><a href="FAQ.php">FAQ</a></li>
                    <li class="divider-vertical"></li>
                    <li><a href="serverlist.php">Servers</a></li>
                    <li class="divider-vertical"></li>
                    <li><a href="downloads.php">Downloads</a></li>
                    <li class="divider-vertical"></li>
            	</ul>

              <ul class="nav pull-right">
  				<li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                  Account
                  <b class="caret"></b>
                </a>
                <ul class="dropdown-menu">
               		<div align="center"><img style="width:100px; height:100px;" src="img/png.png" class="img-circle"  ></div>
               		<div align="center"><strong>Name Surname</strong></div>


                      <li class="divider"></li>
                      <li><a href="#"><i class="icon-lock"></i> Login</a></li>
                      <li><a href="#"><i class="icon-user"></i> Register</a></li>
                      <li><a href="#"><i class="icon-cog"></i> Edit Account</a></li>
                      <li class="divider"></li>
                      <li><a href="admin/index.php"><i class="icon-star-empty"></i> Staff login</a></li>
                </ul>
                </li>
              </ul>
    		</div>
     </div>

       <div style="padding-top:5px" class="container">
	   <!-- InstanceBeginEditable name="main" -->

	<div class="hero-unit">
 		<h1>Welcome</h1>
  		<p>To Gamers Connected, this is an official offline website where you can be up to date on whats happening at the event and where you can get some support if you are stuck.All the Game Servers Ip's Will be on this website under the Servers TAB, This website has an built in Support system where you can chat with admin to help you.</p>
  		<p>

        <!-- LiveZilla Chat Button Link Code (ALWAYS PLACE IN BODY ELEMENT) --><a href="javascript:void(window.open('http://127.0.0.1/offlinewebsite/LiveZilla/chat.php?acid=42e4e','','width=590,height=760,left=0,top=0,resizable=yes,menubar=no,location=no,status=yes,scrollbars=yes'))" class="lz_cbl"><img src="http://127.0.0.1/offlinewebsite/LiveZilla/image.php?acid=4d7eb&amp;id=3&amp;type=inlay" width="200" height="50" style="border:0px;" alt="LiveZilla Live Chat Software"></a><!-- http://www.LiveZilla.net Chat Button Link Code --><!-- LiveZilla Tracking Code (ALWAYS PLACE IN BODY ELEMENT) --><div id="livezilla_tracking" style="display:none"></div><script type="text/javascript">
        var script = document.createElement("script");script.async=true;script.type="text/javascript";var src = "http://127.0.0.1/offlinewebsite/LiveZilla/server.php?acid=294b6&request=track&output=jcrpt&nse="+Math.random();setTimeout("script.src=src;document.getElementById('livezilla_tracking').appendChild(script)",1);</script><noscript><img src="http://127.0.0.1/offlinewebsite/LiveZilla/server.php?acid=294b6&amp;request=track&amp;output=nojcrpt" width="0" height="0" style="visibility:hidden;" alt=""></noscript><!-- http://www.LiveZilla.net Tracking Code -->

  		</p>
	</div>

    <div class="container">
      <div class="row-fluid">
          <div class="span6">
          	<h2>Recent Prize Winners:</h2>
          	    <ul class="thumbnails">
                  <li class="span4">
                    <div class="thumbnail">
                      <img src="img/png.png" alt="">
                      <h4 align="center">1St Prize</h4>
                      <p>This will be updated as soon as someone wins.</p>
                    </div>
                  </li>
                  <li class="span4">
                    <div class="thumbnail">
                      <img src="img/png.png" alt="">
                      <h4 align="center">2nd Prize</h4>
                      <p>This will be updated as soon as someone wins.</p>
                    </div>
                  </li>
                  <li class="span4">
                    <div class="thumbnail">
                      <img  src="img/png.png" alt="" width="300px" height="200px">
                      <h4 align="center">3rd Prize</h4>
                      <p>This will be updated as soon as someone wins.</p>
                    </div>
                  </li>
                </ul>
            <h2></h2>

          </div>
          <div class="span6">
          <h2>Upcoming Games:</h2>
          <table class="table table-hover" width="103%" border="0">
            <tr class="info">
              <td width="33%"><strong>Time</strong></td>
              <td width="33%"><strong>Game</strong></td>
              <td width="33%"><strong>Prizes</strong></td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
          </table>
          <p>&nbsp;</p>





          </div>
	</div>
    <div>
         <div class="well well-large">
          <p>Registerd Users: <span style="width:5%; text-align:center" class="label label-info">30</span></p>
          <table class="table table-condensed">
              <tr class="success">
                      <td width="33%"><strong>Username</strong></td>
                      <td width="33%"><strong>First Name</strong></td>
                      <td width="33%"><strong>Last Name</strong></td>
                    </tr>
                    <tr>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                    </tr>
                    <tr>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                    </tr>
                    <tr>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                    </tr>
                    <tr>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                    </tr>
            </table>
        </div>

<!-- InstanceEndEditable -->


		</div>

</body>
<!-- InstanceEnd --></html>