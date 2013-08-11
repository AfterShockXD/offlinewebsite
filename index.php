<?php
include("classes/config.php");
include("classes/functions.php");
?>
<!DOCTYPE html>
<html>
    <head>
        <!--Start of Timer 1-->
        <style type="text/css">

            #holder {
                position: relative;   /* leave as "relative" to keep timer centered on
                                         your page, or change to "absolute" then change
                                         the values of the "top" and "left" properties to
                                         position the timer */
                top: 0px;            /* change to position the timer; must also change
                                         position to "absolute" above */
                left: 0px;  	      /* change to position the timer; must also change
                                         position to "absolute" above */
                width: 270px;
                height: 5px;
                border: none;
                margin: 0px auto;
            }

            #title, #note {
                color: #FF0000;	      /* this determines the color of the DAYS, HRS, MIN,
                                         SEC labels under the timer and the color of the
                                         note that displays after reaching the target date
                                         and time; if using the blue digital images,
                                         change to #52C6FF; for the red images,
                                         change to #FF6666; for the white images,
                                         change to #BBBBBB; for the yellow images,
                                         change to #FFFF00 */
            }

            #note {
                position: absolute;
                top: 0px;
                height: 0px;
                width: 0px;
                margin: 0 auto;
                padding: 0px;
                text-align: center;
                font-family: Arial;
                font-size: 18px;
                font-weight: bold;    /* options are normal, bold, bolder, lighter */
                font-style: normal;   /* options are normal or italic */
                z-index: 0;
            }

            .title {
                border: none;
                padding: 6px;
                margin: 0px;
                width: 0px;
                text-align: center;
                font-family: Arial;
                font-size: 10px;
                font-weight: normal;    /* options are normal, bold, bolder, lighter */
                background-color: transparent;
            }

            #timer {
                position: absolute;
                top: 0px;
                left: 0px;
                margin: 0px auto;
                text-align: center;
                width: 260px;
                height: 40px;
                border: none;
                padding: 10px 5px 20px 5px;
                background: #000000;      /* may change to another color, or to "transparent" */
                border-radius: 20px;
                box-shadow: 0 0 10px #000000;  /* change to "none" if you don't want a shadow */
            }
        </style>

        <script type="text/javascript">
            /*
             on 5/30/2006 to count down to a specific date AND time,
             on 10/20/2007 to a new format, on 1/10/2010 to include
             time zone offset, and on 7/12/2012 to digital numbers.
             */

            /*
             CHANGE THE ITEMS BELOW TO CREATE YOUR COUNTDOWN TARGET DATE AND ANNOUNCEMENT
             ONCE THE TARGET DATE AND TIME ARE REACHED.
             */
            var note = "The lan is over! Please join us again!";	/* -->Enter what you want the script to
             display when the target date and time
             are reached, limit to 25 characters */
            var year = 2013;      /* -->Enter the count down target date YEAR */
            var month = 08;       /* -->Enter the count down target date MONTH */
            var day = 17;         /* -->Enter the count down target date DAY */
            var hour = 10;         /* -->Enter the count down target date HOUR (24 hour clock) */
            var minute = 00;      /* -->Enter the count down target date MINUTE */
            var tz = +2;          /* -->Offset for your timezone in hours from UTC (see
             http://wwp.greenwichmeantime.com/index.htm to find
             the timezone offset for your location) */

            //-->    DO NOT CHANGE THE CODE BELOW!    <--
            d1 = new Image();
            d1.src = "img/digital-numbers/1.png";
            d2 = new Image();
            d2.src = "img/digital-numbers/2.png";
            d3 = new Image();
            d3.src = "img/digital-numbers/3.png";
            d4 = new Image();
            d4.src = "img/digital-numbers/4.png";
            d5 = new Image();
            d5.src = "img/digital-numbers/5.png";
            d6 = new Image();
            d6.src = "img/digital-numbers/6.png";
            d7 = new Image();
            d7.src = "img/digital-numbers/7.png";
            d8 = new Image();
            d8.src = "img/digital-numbers/8.png";
            d9 = new Image();
            d9.src = "img/digital-numbers/9.png";
            d0 = new Image();
            d0.src = "img/digital-numbers/0.png";
            bkgd = new Image();
            bkgd.src = "img/digital-numbers/bkgd.gif";

            var montharray = new Array("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec");

            function countdown(yr, m, d, hr, min) {
                theyear = yr;
                themonth = m;
                theday = d;
                thehour = hr;
                theminute = min;
                var today = new Date();
                var todayy = today.getYear();
                if (todayy < 1000) {
                    todayy += 1900;
                }
                var todaym = today.getMonth();
                var todayd = today.getDate();
                var todayh = today.getHours();
                var todaymin = today.getMinutes();
                var todaysec = today.getSeconds();
                var todaystring1 = montharray[todaym] + " " + todayd + ", " + todayy + " " + todayh + ":" + todaymin + ":" + todaysec;
                var todaystring = Date.parse(todaystring1) + (tz * 1000 * 60 * 60);
                var futurestring1 = (montharray[m - 1] + " " + d + ", " + yr + " " + hr + ":" + min);
                var futurestring = Date.parse(futurestring1) - (today.getTimezoneOffset() * (1000 * 60));
                var dd = futurestring - todaystring;
                var dday = Math.floor(dd / (60 * 60 * 1000 * 24) * 1);
                var dhour = Math.floor((dd % (60 * 60 * 1000 * 24)) / (60 * 60 * 1000) * 1);
                var dmin = Math.floor(((dd % (60 * 60 * 1000 * 24)) % (60 * 60 * 1000)) / (60 * 1000) * 1);
                var dsec = Math.floor((((dd % (60 * 60 * 1000 * 24)) % (60 * 60 * 1000)) % (60 * 1000)) / 1000 * 1);
                if (dday <= 0 && dhour <= 0 && dmin <= 0 && dsec <= 0) {
                    document.getElementById('note').innerHTML = note;
                    document.getElementById('note').style.display = "block";
                    document.getElementById('countdown').style.display = "none";
                    clearTimeout(startTimer);
                    return;
                }
                else {
                    document.getElementById('note').style.display = "none";
                    document.getElementById('timer').style.display = "block";
                    startTimer = setTimeout("countdown(theyear,themonth,theday,thehour,theminute)", 500);
                }
                convert(dday, dhour, dmin, dsec);
            }

            function convert(d, h, m, s) {
                if (!document.images)
                    return;
                if (d <= 9) {
                    document.images.day1.src = bkgd.src;
                    document.images.day2.src = bkgd.src;
                    document.images.day3.src = eval("d" + d + ".src");
                }
                else if (d <= 99) {
                    document.images.day1.src = bkgd.src;
                    document.images.day2.src = eval("d" + Math.floor(d / 10) + ".src");
                    document.images.day3.src = eval("d" + (d % 10) + ".src");
                }
                else {
                    document.images.day1.src = eval("d" + Math.floor(d / 100) + ".src");
                    var day = d.toString();
                    day = day.substr(1, 1);
                    day = parseInt(day);
                    document.images.day2.src = eval("d" + day + ".src");
                    document.images.day3.src = eval("d" + (d % 10) + ".src");
                }
                if (h <= 9) {
                    document.images.h1.src = d0.src;
                    document.images.h2.src = eval("d" + h + ".src");
                }
                else {
                    document.images.h1.src = eval("d" + Math.floor(h / 10) + ".src");
                    document.images.h2.src = eval("d" + (h % 10) + ".src");
                }
                if (m <= 9) {
                    document.images.m1.src = d0.src;
                    document.images.m2.src = eval("d" + m + ".src");
                }
                else {
                    document.images.m1.src = eval("d" + Math.floor(m / 10) + ".src");
                    document.images.m2.src = eval("d" + (m % 10) + ".src");
                }
                if (s <= 9) {
                    document.images.s1.src = d0.src;
                    document.images.s2.src = eval("d" + s + ".src");
                }
                else {
                    document.images.s1.src = eval("d" + Math.floor(s / 10) + ".src");
                    document.images.s2.src = eval("d" + (s % 10) + ".src");
                }
            }
        </script>
        <!--End Of timer 1 Head-->

    <title>Home Page</title>
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta charset="utf-8">
    </head>
    <body onload="countdown(year, month, day, hour, minute)">

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
<div class="hero-unit">
      <h1>Welcome</h1>
      <p>To Gamers Connected, this is the official offline website where you can be up to date on whats happening at the event and where you can get some support if you are stuck. All the Game Servers Ip's Will be on this website under the Servers TAB, This website has an built in Support system where you can chat with admin to help you.</p>
      <p>

          <!-- LiveZilla Chat Button Link Code (ALWAYS PLACE IN BODY ELEMENT) --><a href="javascript:void(window.open('http://127.0.0.1/offlinewebsite/LiveZilla/chat.php?acid=42e4e','','width=590,height=760,left=0,top=0,resizable=yes,menubar=no,location=no,status=yes,scrollbars=yes'))" class="lz_cbl"><img src="http://127.0.0.1/offlinewebsite/LiveZilla/image.php?acid=4d7eb&amp;id=3&amp;type=inlay" width="200" height="50" style="border:0px;" alt="LiveZilla Live Chat Software"></a><!-- http://www.LiveZilla.net Chat Button Link Code --><!-- LiveZilla Tracking Code (ALWAYS PLACE IN BODY ELEMENT) --><div id="livezilla_tracking" style="display:none"></div><script type="text/javascript">
            var script = document.createElement("script");
            script.async = true;
            script.type = "text/javascript";
            var src = "http://127.0.0.1/offlinewebsite/LiveZilla/server.php?acid=294b6&request=track&output=jcrpt&nse=" + Math.random();
            setTimeout("script.src=src;document.getElementById('livezilla_tracking').appendChild(script)", 1);</script><noscript><img src="http://127.0.0.1/offlinewebsite/LiveZilla/server.php?acid=294b6&amp;request=track&amp;output=nojcrpt" width="0" height="0" style="visibility:hidden;" alt=""></noscript><!-- http://www.LiveZilla.net Tracking Code -->

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
                                  <h4 align="center">1st Prize</h4>
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
    <div class="well well-large container">
      <p>Registerd Users: <span style="width:5%;" class="label label-info">30</span></p>
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
	<div class="container well well-large">
      <!-- Table And Content goes here-->
 <table width="100%" border="0">
  <tr>
    <td width="200px"><h2 align="center" style="height: 70px;">Time remaining:</h2></td>
    <td>
        <!--Start of timer 1-->
                <div class="pull-right" id="holder">
                    <div   id="timer" style="left: -60px; top:-30px;">
                        <div id="note"></div>
                        <div id="countdown">
                            <img height=21 src="img/digital-numbers/bkgd.gif" width=16 name="day1">
                            <img height=21 src="img/digital-numbers/bkgd.gif" width=16 name="day2">
                            <img height=21 src="img/digital-numbers/bkgd.gif" width=16 name="day3">
                            <img height=21 id="colon1" src="img/digital-numbers/colon.png" width=9 name="d1">
                            <img height=21 src="img/digital-numbers/bkgd.gif" width=16 name="h1">
                            <img height=21 src="img/digital-numbers/bkgd.gif" width=16 name="h2">
                            <img height=21 id="colon2" src="img/digital-numbers/colon.png" width=9 name="g1">
                            <img height=21 src="img/digital-numbers/bkgd.gif" width=16 name="m1">
                            <img height=21 src="img/digital-numbers/bkgd.gif" width=16 name="m2">
                            <img height=21 id="colon3" src="img/digital-numbers/colon.png" width=9 name="j1">
                            <img height=21 src="img/digital-numbers/bkgd.gif" width=16 name="s1">
                            <img height=21 src="img/digital-numbers/bkgd.gif" width=16 name="s2">
                            <div id="title">
                                <div class="title" style="position: absolute; top: 36px; left: 42px">DAYS</div>
                                <div class="title" style="position: absolute; top: 36px; left: 105px">HRS</div>
                                <div class="title" style="position: absolute; top: 36px; left: 156px">MIN</div>
                                <div class="title" style="position: absolute; top: 36px; left: 211px">SEC</div>
                            </div>
                        </div>
                    </div>
                </div>
<!-- End Of timer --></td>
  </tr>
  <tr>
    <td align="center"><input class="span2 container-fluid" type="text" placeholder="Name"></td>
    <td align="center"><input class="span2 container-fluid" type="text" placeholder="Email"></td>
  </tr>
  <tr>
    <td align="center"><input class="span2 container-fluid" type="text" placeholder="Username"></td>
    <td align="center"><input class="span2 container-fluid" type="password" placeholder="Password"></td>
  </tr>
  <tr>
    <td align="center">

        <!--Start of poll-->

                    <div style="width:500px" class="container-fluid">
                    <strong>Sign up progression:</strong><span class="pull-right">40%</span>
                    <div class="progress progress-striped active">
                    <div class="bar" style="width:40%;"></div>
                    </div></div></div></div>
                <!-- End Of Poll --></td>
        <td align="center"><button type="submit" class="btn">Sign in &raquo;</button></td>
  </tr>
</table>





    </div>




		<!--Add your Second Div-->


                <!-- End Of timer -->
                </body>
                <footer>
                    <hr>
                    <p align="center">Created by Jp Ellis and Jason Zwanepoel</p>
                    <p align="center">&COPY; Gamers Connected 2013 <p>
                </footer>
                </html>