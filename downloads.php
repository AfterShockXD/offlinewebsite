<?php require_once('Connections/loclahost.php'); 
	session_start();
	if (!isset($_SESSION['uid'])) header('location: memberlogin.php');
	//var_dump($_SESSION);

	//exit();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Downloads</title>
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
                <li><a href="serverlist.php">Servers</a></li>
                <li class="divider-vertical"></li>
                <li class="active"><a href="downloads.php">Downloads</a></li>
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
  
  <div style="padding-top:10%" class="container">
  <div style="float:left">
  <ul class="thumbnails">
  <li class="span4">
    <div class="thumbnail well">
      <img src="img/Dc++image.png" alt="">
      <h3>Strong DC++</h3>
      <p>StrongDC++ is a client for sharing files in Direct Connect network by using NMDC and ADC protocols</p>
      <br />
      <div align="center"><button class="btn-large btn-danger">Download</button></div>
    </div>
  </li>
</ul>
</div>
 <div style="float:left; padding-left:4px; width:300px; height:399px">
  <ul class="thumbnails">
  <li class="span4">
    <div class="thumbnail well">
      <img src="img/TeamSpeak3.png" alt="">
      <h3>Teamspeak 3 Client</h3>
      <p>TeamSpeak 3 continues the legacy of the original TeamSpeak communication system </p>
      <br />
      <br />
      <div align="center"><button class="btn-large btn-danger">Download</button></div>
    </div>
  </li>
</ul>
</div>
   <div style="float:left; padding-left:4px;">
  <ul class="thumbnails">
  <li class="span4">
    <div class="thumbnail well">
      <img src="img/Avast.png" alt="">
      <h3>Free Avast Antivirus</h3>
      <p>Free Antivirus and anti-spyware protection for Windows 8, 7, Vista, and XP. Best free antivirus with better detection</p>
      <br />
      <div align="center"><button class="btn-large btn-danger">Download</button></div>
    </div>
  </li>
</ul>
</div>
  
  
  
  
  
  </div>
   
  <script src="http://code.jquery.com/jquery.js"></script>
  <script src="js/bootstrap.min.js"></script>
</body>
</html>