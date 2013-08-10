<!--
/****************************************************************************************
 * LiveZilla index.php
 *
 * Copyright 2013 LiveZilla GmbH
 * All rights reserved.
 * LiveZilla is a registered trademark.
 *
 ***************************************************************************************/
-->
<!DOCTYPE HTML>
<html>
<head>
    <title>
        Livezilla Mobile
    </title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=0.6, maximum-scale=1">

    <link rel="stylesheet" type="text/css" href="./js/jquery_mobile/jquery.mobile-1.3.0.min.css"/>
    <link rel="stylesheet" type="text/css" href="./css/livezilla.css"/>
    <link rel="shortcut icon" href="../images/favicon.ico" type="image/x-icon">

    <script type="text/javascript" src="./js/jquery-1.8.3.min.js"></script>
    <script type="text/javascript" src="./js/jquery_mobile/jquery.mobile-1.3.0.min.js"></script>

    <script type="text/javascript" src="./js/jsglobal.js"></script>
    <script type="text/javascript" src="./js/md5.js"></script>

    <script type="text/javascript" src="./js/lzm/classes/CommonConfigClass.js"></script>
    <script type="text/javascript" src="./js/lzm/classes/CommonToolsClass.js"></script>
    <script type="text/javascript" src="./js/lzm/classes/CommonStorageClass.js"></script>
    <script type="text/javascript" src="./js/lzm/classes/CommonDisplayClass.js"></script>
    <script type="text/javascript" src="./js/lzm/classes/CommonTranslationClass.js"></script>
    <script type="text/javascript" src="./js/lzm/index.js"></script>
</head>
<body>

<div id="login_page" data-role="page">
    <header id="header_login" data-role="header" data-position="fixed">
        <h1 id="headline1" style="margin: 0.6em;"></h1>
        <!--<a href="#" data-role="button" data-icon="gear" id="configure_btn" data-mini="true" class="ui-btn-right" style="display: none;">&nbsp;</a>-->
    </header>
    <article id="content_login" data-role="content">
        <div id="logo-container"></div>
        <div class="lzg-form" id="input-container">

            <div class="login-data" style="display: block;" id=login-container>
                <div id="username-container" data-role="none" style="margin-bottom:0.5em; padding-bottom: 0.2em;">
                    <label id="username-text" for="username"></label>
                    <input type="text" name="username" id="username" class="login-input" placeholder="Username" autocapitalize="off" autocorrect="off" />
                </div>
                <div id="password-container" data-role="none" style="margin-bottom:0.9em; padding-bottom: 0.2em;">
                    <label id="password-text" for="password"></label>
                    <input type="password" name="password" id="password" class="login-input" placeholder="Password" />
                </div>

                <fieldset data-role="controlgroup" id="save-login-question" style="display:none;">
                    <input type="checkbox" name="save_login" id="save_login" class="save_login" data-mini="true" />
                    <label id="save_login-text" for="save_login">&nbsp;</label>
                </fieldset>

                <div id="profile-container" data-role="none" style="margin-bottom:0.1em; padding-bottom: 0.2em;">
                    <select name="server_profile_selection" id="server_profile_selection" data-mini="true" style="display: none;">
                    </select>
                    <a href="#" data-role="button" id="configure_btn" data-mini="true" data-inline="true" data-icon="gear">&nbsp;</a>
                </div>
            </div>

            <div class="login-data" style="display: block; margin-top: 20px;">
                <select name="user_status" id="user_status" data-mini="true" data-inline="true" data-iconpos="left">
                </select>
                <a href="#" data-role="button" id="login_btn" data-mini="true" data-inline="true" class="ui-disabled">&nbsp;</a>
            </div>
        </div>
        <form id="data-submit-form" method="post" data-ajax="false">
        </form>
    </article>
</div>

</body>
</html>
