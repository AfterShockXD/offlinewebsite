<?php require_once('Connections/loclahost.php'); 
	session_start();
	if (!isset($_SESSION['uid'])) header('location: memberlogin.php');
	//var_dump($_SESSION);

	//exit();
?>
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



        <script type="text/javascript" src="js/swfobject/swfobject.js"></script>
        <script type="text/javascript">
            var flashvars = {};
            flashvars.cssSource = "css/piecemaker.css";
            flashvars.xmlSource = "piecemaker.xml";

            var params = {};
            params.play = "true";
            params.menu = "false";
            params.scale = "showall";
            params.wmode = "transparent";
            params.allowfullscreen = "true";
            params.allowscriptaccess = "always";
            params.allownetworking = "all";

            swfobject.embedSWF('piecemaker.swf', 'piecemaker', '960', '440', '10', null, flashvars,
                    params, null);

        </script>
        <!-- Gallery start -->

        <style>

            /* Demo styles */

            body{border-top:0px solid #000;}
            .content{color:#777;font:12px/1.4 "helvetica neue",arial,sans-serif;width:620px;margin:20px auto;}
            /*h1{font-size:12px;font-weight:normal;color:#ddd;margin:0;}
            p{margin:0 0 20px}
            a {color:#22BCB9;text-decoration:none;}
            .cred{margin-top:20px;font-size:11px;}

            /* This rule is read by Galleria to define the gallery height: */
            #galleria{height:360px}



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

        <!--End of Nav-->

        <div align="center" class="container">
                  <div id="piecemaker"></div>
               </div>


        <div class="content">
            <p>Gcon Featured images:</p>

            <!-- Adding gallery images. We use resized thumbnails here for better performance, but it’s not necessary -->

            <div id="galleria">
                <a href="../img/WIP.jpg">
                    <img
                        src="../img/WIP.jpg"
                        data-big="../img/WIP.jpg"
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
                showCounter: true
            });
            Galleria.ready(function(options) {

                // 'this' is the gallery instance
                // 'options' is the gallery options'

            });


        </script>
        <div class="container well well-large">
            <p align="left">Apocalypse 26/04/2013:</p>
            <script src="js/jquery-1.10.2.min.js"></script>
            <script src="js/lightbox-2.6.min.js"></script>
            <link href="css/lightbox.css" rel="stylesheet" />

            <table width="100%" border="0">
                <tr align="center">
                      <td><a href="img/lanold/Apocalypse1.JPG" data-lightbox="img/lanold/Apocolypse1.jpg"> <img src="img/lanold/Apocalypse1.JPG" alt="" width="300px" height="200px" /></a></td>
                      <td><a href="img/lanold/Apocalypse2.JPG" data-lightbox="img/lanold/Apocolypse2.jpg"> <img src="img/lanold/Apocalypse2.JPG" alt="" width="300px" height="200px" /></a></td>
                      <td><a href="img/lanold/Apocalypse3.JPG" data-lightbox="img/lanold/Apocolypse3.jpg"> <img src="img/lanold/Apocalypse3.JPG" alt="" width="300px" height="200px" /></a></td>
                    </tr>
                    <tr align="center">
                      <td><a href="img/lanold/Apocalypse4.JPG" data-lightbox="img/lanold/Apocalypse4.jpg"> <img src="img/lanold/Apocalypse4.JPG" alt="" width="300px" height="200px" /></a></td>
                      <td><a href="img/lanold/Apocalypse5.JPG" data-lightbox="img/lanold/Apocalypse5.jpg"> <img src="img/lanold/Apocalypse5.JPG" alt="" width="300px" height="200px" /></a></td>
                      <td><a href="img/lanold/Apocalypse6.jpg" data-lightbox="img/lanold/Apocalypse6.jpg"> <img src="img/lanold/Apocalypse6.jpg" alt="" width="300px" height="200px" /></a></td>
                    </tr>
                    <tr align="center">
                      <td><a href="img/lanold/Apocalypse7.jpg" data-lightbox="img/lanold/Apocalypse7.jpg"> <img src="img/lanold/Apocalypse7.jpg" alt="" width="300px" height="200px" /></a></td>
                      <td><a href="img/lanold/Apocalypse8.jpg" data-lightbox="img/lanold/Apocalypse8.jpg"> <img src="img/lanold/Apocalypse8.jpg" alt="" width="300px" height="200px" /></a></td>
                      <td><a href="img/lanold/Apocalypse9.jpg" data-lightbox="img/lanold/Apocalypse9.jpg"> <img src="img/lanold/Apocalypse9.jpg" alt="" width="300px" height="200px" /></a></td>
                    </tr>
                    <tr align="center">
                      <td><a href="img/lanold/Apocalypse10.jpg" data-lightbox="img/lanold/Apocalypse10.jpg"> <img src="img/lanold/Apocalypse10.jpg" alt="" width="300px" height="200px" /></a></td>
                      <td><a href="img/lanold/Apocalypse11.jpg" data-lightbox="img/lanold/Apocalypse11.jpg"> <img src="img/lanold/Apocalypse11.jpg" alt="" width="300px" height="200px" /></a></td>
                      <td><a href="img/lanold/Apocalypse12.jpg" data-lightbox="img/lanold/Apocalypse12.jpg"> <img src="img/lanold/Apocalypse12.jpg" alt="" width="300px" height="200px" /></a></td>
                    </tr>
                    <tr align="center">
                      <td><a href="img/lanold/Apocalypse13.jpg" data-lightbox="img/lanold/Apocalypse13.jpg"> <img src="img/lanold/Apocalypse13.jpg" alt="" width="300px" height="200px" /></a></td>
                      <td><a href="img/lanold/Apocalypse14.jpg" data-lightbox="img/lanold/Apocalypse14.jpg"> <img src="img/lanold/Apocalypse14.jpg" alt="" width="300px" height="200px" /></a></td>
                      <td><a href="img/lanold/Apocalypse15.jpg" data-lightbox="img/lanold/Apocalypse15.jpg"> <img src="img/lanold/Apocalypse15.jpg" alt="" width="300px" height="200px" /></a></td>
                    </tr>
                    <tr align="center">
                      <td><a href="img/lanold/Apocalypse16.jpg" data-lightbox="img/lanold/Apocalypse16.jpg"> <img src="img/lanold/Apocalypse16.jpg" alt="" width="300px" height="200px" /></a></td>
                      <td><a href="img/lanold/Apocalypse17.jpg" data-lightbox="img/lanold/Apocalypse17.jpg"> <img src="img/lanold/Apocalypse17.jpg" alt="" width="300px" height="200px" /></a></td>
                      <td><a href="img/lanold/Apocalypse18.jpg" data-lightbox="img/lanold/Apocalypse18.jpg"> <img src="img/lanold/Apocalypse18.jpg" alt="" width="300px" height="200px" /></a></td>
                    </tr>
                    <tr align="center">
                      <td><a href="img/lanold/Apocalypse19.jpg" data-lightbox="img/lanold/Apocalypse19.jpg"> <img src="img/lanold/Apocalypse19.jpg" alt="" width="300px" height="200px" /></a></td>
                      <td><a href="img/lanold/Apocalypse20.jpg" data-lightbox="img/lanold/Apocalypse20.jpg"> <img src="img/lanold/Apocalypse20.jpg" alt="" width="300px" height="200px" /></a></td>
                      <td><a href="img/lanold/Apocalypse21.jpg" data-lightbox="img/lanold/Apocalypse21.jpg"> <img src="img/lanold/Apocalypse21.jpg" alt="" width="300px" height="200px" /></a></td>
                    </tr>
                    <tr align="center">
                      <td><a href="img/lanold/Apocalypse22.jpg" data-lightbox="img/lanold/Apocalypse22.jpg"> <img src="img/lanold/Apocalypse22.jpg" alt="" width="300px" height="200px" /></a></td>
                      <!--<td><a href="img/lanold/Apocolypse20.jpg" data-lightbox="img/lanold/Apocolypse20.jpg"> <img src="img/lanold/Apocalypse20.jpg" alt="" width="300px" height="200px" /></a></td>
                      <td><a href="img/lanold/Apocolypse21.jpg" data-lightbox="img/lanold/Apocolypse21.jpg"> <img src="img/lanold/Apocalypse21.jpg" alt="" width="300px" height="200px" /></a></td>
                --></tr>
        </div>
    </table>








    <script src="http://code.jquery.com/jquery.js"></script>
    <script src="js/bootstrap.min.js"></script>
</body>
</html>