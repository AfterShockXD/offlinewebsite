<!DOCTYPE html>
<html>
<head>
    <title>GCON - Gallery</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet" media="screen">
    <style type="text/css">
    body {
	background-image: url(img/Subtle-Grey-Tileable-Pattern-For-Website-Background.jpg);
}
    </style>
<meta charset="utf-8">


</script>
 <!-- Gallery start -->

 <style>

            /* Demo styles */

            body{border-top:4px solid #000;}
            .content{color:#777;font:12px/1.4 "helvetica neue",arial,sans-serif;width:620px;margin:20px auto;}
            /*h1{font-size:12px;font-weight:normal;color:#ddd;margin:0;}
            p{margin:0 0 20px}
            a {color:#22BCB9;text-decoration:none;}
            .cred{margin-top:20px;font-size:11px;}

            /* This rule is read by Galleria to define the gallery height: */
            #galleria{height:320px}

        </style>

        <!-- load jQuery -->
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.js"></script>

        <!-- load Galleria -->
        <script src="galleria/galleria-1.2.9.min.js"></script>

<!-- end -->

  </head>
  <body>
    <div class="navbar navbar-static-top">
    		<div  class="navbar-inner ">
            	<a  class="brand" href="#"><strong>Gamers Connected</strong></a>
            	<ul class="nav">
                <li class="divider-vertical"></li>
             	<li ><a href="index.php">Home</a></li>
                <li class="divider-vertical"></li>
              	<li class="active"><a href="gallery.php">Gallery</a></li>
                <li class="divider-vertical"></li>
              	<li><a href="FAQ.php">FAQ</a></li>
                <li class="divider-vertical"></li>
                <li><a href="serverlist.php">Servers</a></li>
                <li class="divider-vertical"></li>
                <li><a href="downloads.php">Downloads</a></li>
                <li class="divider-vertical"></li>
            	</ul>
                <!--DropDown-->
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

                </ul>
                </li>
              </ul>
    		</div>
  </div>

  <!--End of Nav-->



    <div class="content">
        <h1>Apocolypse 26/04/2013</h1>

        <!-- Adding gallery images. We use resized thumbnails here for better performance, but it’s not necessary -->

        <div id="galleria">
        <a href="../img/WIP.jpg">
                <img
                    src="../img/WIP.jpg",
                    data-big="../img/WIP.jpg""
                    data-title="Unknown"
                    data-description="Apocalypse(26 April 2013)"
                >
            </a>

           <!-- <a href="../img/lanold/640x640uploads~images~26 April 2013 Apocalypse~Apocalypse26.jpg">
                <img
                    src="../img/lanold/640x640uploads~images~26 April 2013 Apocalypse~Apocalypse26.jpg",
                    data-big="../img/lanold/640x640uploads~images~26 April 2013 Apocalypse~Apocalypse26.jpg""
                    data-title="Unknown"
                    data-description="Apocalypse(26 April 2013)"
                >
            </a>-->


        </div>
        <div>&numsp;</div>
        <h1>Images:</h1>
    </div>

    <script>

    // Load the classic theme
    Galleria.loadTheme('galleria/themes/classic/galleria.classic.min.js');

   Galleria.run('#galleria', {
    autoplay: 7000, // will move forward every 7 seconds
    transition: 'fade',
    imageCrop: false,
    clicknext: false,
    fullscreenDoubleTap: true,
    lightbox: true,

});
    Galleria.ready(function(options) {

    // 'this' is the gallery instance
    // 'options' is the gallery options'

});


    </script>








  <script src="http://code.jquery.com/jquery.js"></script>
  <script src="js/bootstrap.min.js"></script>
</body>
</html>