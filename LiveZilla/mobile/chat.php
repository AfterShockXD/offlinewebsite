<?php
/****************************************************************************************
 * LiveZilla chat.php
 *
 * Copyright 2013 LiveZilla GmbH
 * All rights reserved.
 * LiveZilla is a registered trademark.
 *
 ***************************************************************************************/

$LZM_URL = dirname($_SERVER['PHP_SELF']);
$LZM_PATH = dirname($_SERVER['SCRIPT_FILENAME']);
require $LZM_PATH.'/php/common/functions.php';

$index = !empty($_REQUEST['index']) ? $_REQUEST['index'] : '';
$login_name = !empty($_REQUEST['login']) ? $_REQUEST['login'] : '';
$login_passwd = !empty($_REQUEST['password']) ? $_REQUEST['password'] : '';
$server_port = !empty($_REQUEST['port']) ? $_REQUEST['port'] : '';
$server_profile = !empty($_REQUEST['profile']) ? $_REQUEST['profile'] : '';
$server_protocol = !empty($_REQUEST['protocol']) ? $_REQUEST['protocol'] : '';
$server_url = !empty($_REQUEST['url']) ? $_REQUEST['url'] : '';
$status = !empty($_REQUEST['status']) ? $_REQUEST['status'] : '';
$app = !empty($_REQUEST['app']) ? $_REQUEST['app'] : 0;
$web = !empty($_REQUEST['web']) ? $_REQUEST['web'] : 0;
$volume = !empty($_REQUEST['volume']) ? $_REQUEST['volume'] : 60;
$awayAfter = !empty($_REQUEST['away_after']) ? $_REQUEST['away_after'] : 0;
$playIncomingMessageSound = !empty($_REQUEST['play_incoming_message_sound']) ? $_REQUEST['play_incoming_message_sound'] : 1;
$playIncomingChatSound = !empty($_REQUEST['play_incoming_chat_sound']) ? $_REQUEST['play_incoming_chat_sound'] : 1;
$repeatIncomingChatSound = !empty($_REQUEST['repeat_incoming_chat_sound']) ? $_REQUEST['repeat_incoming_chat_sound'] : 1;
$language = !empty($_REQUEST['language']) ? $_REQUEST['language'] : '';
$loginId = !empty($_REQUEST['loginid']) ? $_REQUEST['loginid'] : '';
$localDbPrefix = !empty($_REQUEST['local_db_prefix']) ? $_REQUEST['local_db_prefix'] : '';

$mobileInformation = getMobileInformation();
$messageInternal = readHtmlTemplate('messageinternal.tpl');
$messageExternal = readHtmlTemplate('messageexternal.tpl');
$messageAdd = readHtmlTemplate('messageadd.tpl');
$messageAddAlt = readHtmlTemplate('messageaddalt.tpl');
$messageRepost = readHtmlTemplate('messagerepost.tpl');
$messageHeader = readHtmlTemplate('header.tpl');
?>
<!DOCTYPE HTML>
<html>
<head>
    <title>
        Livezilla Mobile
    </title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta http-equiv='cache-control' content='no-cache'>
    <meta http-equiv='expires' content='0'>
    <meta http-equiv='pragma' content='no-cache'>

    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="google" content="notranslate">

    <link rel="stylesheet" type="text/css" href="./js/jquery_mobile/jquery.mobile-1.3.0.min.css"/>
    <link rel="stylesheet" type="text/css" href="./css/livezilla.css"/>
    <link rel="shortcut icon" href="../images/favicon.ico" type="image/x-icon">

    <script type="text/javascript" src="./js/jquery-1.8.3.min.js"></script>
    <script type="text/javascript" src="./js/jquery_mobile/jquery.mobile-1.3.0.min.js"></script>
    <script type="text/javascript" src="./js/jquery.blockUI.js"></script>

    <script type="text/javascript" src="js/md5.js"></script>
    <script type="text/javascript" src="js/jsglobal.js"></script>
    <script type="text/javascript" src="js/wyzz/wyzz.js"></script>

    <script type="text/javascript">
        var chosenProfile = {};
        var userStatus = 0;
        var isMobile = <?php echo $mobileInformation['isMobile']; ?>;
        var isTablet = <?php echo $mobileInformation['isTablet']; ?>;
        var localDbPrefix = <?php echo "'".$localDbPrefix."'"; ?>;
        var mobileOS = <?php echo "'".$mobileInformation['mobileOS']."'"; ?>;
        var messageTemplates = {'internal': <?php echo "'".$messageInternal."'"; ?>,
            'external': <?php echo "'".$messageExternal."'"; ?>,
            'add': <?php echo "'".$messageAdd."'"; ?>,
            'addalt': <?php echo "'".$messageAddAlt."'"; ?>,
            'repost': <?php echo "'".$messageRepost."'"; ?>,
            'header': <?php echo "'".$messageHeader."'"; ?>
        };
        var web = <?php echo $web; ?>;
        var app = <?php echo $app; ?>;

        $(document).ready(function() {
            var volume = lz_global_base64_url_decode(<?php echo "'".$volume."'"; ?>);
            var server_url = lz_global_base64_url_decode(<?php echo "'".$server_url."'"; ?>);
            var server_port = lz_global_base64_url_decode(<?php echo "'".$server_port."'"; ?>);
            var loginId = lz_global_base64_url_decode(<?php echo "'".$loginId."'"; ?>);
            var language = lz_global_base64_url_decode(<?php echo "'".$language."'"; ?>);
            var urlParts = server_url.split('/');
            var urlBase = urlParts[0];
            var urlRest = '';
            for (var i=1; i<urlParts.length; i++) {
                urlRest += '/' + urlParts[i];
            }
            server_url = urlBase + ':' + server_port + urlRest;

            chosenProfile = {
                index: lz_global_base64_url_decode(<?php echo "'".$index."'"; ?>),
                login_name: lz_global_base64_url_decode(<?php echo "'".$login_name."'"; ?>),
                login_passwd: lz_global_base64_url_decode(<?php echo "'".$login_passwd."'"; ?>),
                server_port: server_port,
                server_profile: lz_global_base64_url_decode(<?php echo "'".$server_profile."'"; ?>),
                server_protocol: lz_global_base64_url_decode(<?php echo "'".$server_protocol."'"; ?>),
                server_url: server_url,
                user_volume: volume,
                user_away_after: lz_global_base64_url_decode(<?php echo "'".$awayAfter."'"; ?>),
                play_incoming_message_sound: lz_global_base64_url_decode(<?php echo "'".$playIncomingMessageSound."'"; ?>),
                play_incoming_chat_sound: lz_global_base64_url_decode(<?php echo "'".$playIncomingChatSound."'"; ?>),
                repeat_incoming_chat_sound: lz_global_base64_url_decode(<?php echo "'".$repeatIncomingChatSound."'"; ?>),
                fake_mac_address: loginId,
                language: language,
                login_id: loginId
            };
            userStatus = lz_global_base64_url_decode(<?php echo "'".$status."'"; ?>);
            if (isMobile && mobileOS == 'iOS') {
                $('#chat_page').css({'overflow-y': 'visible'});
            }

        });
    </script>
    <script type="text/javascript" src="./js/lzm/classes/CommonConfigClass.js"></script>
    <script type="text/javascript" src="./js/lzm/classes/CommonToolsClass.js"></script>
    <script type="text/javascript" src="./js/lzm/classes/CommonStorageClass.js"></script>
    <script type="text/javascript" src="./js/lzm/classes/ChatServerEvaluationClass.js"></script>
    <script type="text/javascript" src="./js/lzm/classes/ChatPollServerClass.js"></script>
    <script type="text/javascript" src="./js/lzm/classes/ChatUserActionsClass.js"></script>
    <script type="text/javascript" src="./js/lzm/classes/ChatDisplayClass.js"></script>
    <script type="text/javascript" src="./js/lzm/classes/CommonTranslationClass.js"></script>
    <script type="text/javascript" src="./js/lzm/classes/ChatLinkClass.js"></script>
    <script type="text/javascript" src="./js/lzm/classes/ChatEditorClass.js"></script>
    <script type="text/javascript" src="js/lzm/chat.js"></script>
</head>
<body style="overflow-y: hidden;">

<audio id="sound-message" preload='auto'>
    <source src="sounds/message.ogg" type="audio/ogg">
    <source src="sounds/message.mp3" type="audio/mpeg">
</audio>

<audio id="sound-ringtone" preload='auto'>
    <source src="sounds/ringtone.ogg" type="audio/ogg">
    <source src="sounds/ringtone.mp3" type="audio/mpeg">
</audio>

<div id="chat_page" data-role="page" style="overflow-y: hidden;">
    <article id="content_chat" data-role="content">
        <div id="debugging-messages"></div>

        <div data-role="controlgroup" data-type="horizontal" style="margin: 5px 10px 5px 10px; z-index: 100;" id="user-control-panel">
            <a href="#" data-role="button" id="userstatus-button" class="lzm-button">&nbsp;</a>
            <a href="#" data-role="button" data-icon="arrow-d" data-iconpos="left" id="usersettings-button">&nbsp;</a>
            <a href="#" data-role="button" id="blank-button">&nbsp;</a>
        </div>
        <div id="userstatus-menu" class="mouse-menu" style="display:none;"></div>
        <div id="usersettings-menu" class="mouse-menu" style="display:none;"></div>

        <div class="lz-menu" style="width: 100%; z-index: 100;" id="view-select-panel">
            <fieldset data-role="controlgroup" data-type="horizontal" id="view-select">
                <input name="view-select" class="view-select" type="radio" value="mychats" id="radio-mychats" data-mini="true">
                <label id="radio-mychats-text" for="radio-mychats">&nbsp;</label>
                <input name="view-select" class="view-select" type="radio" value="internal" id="radio-internal" data-mini="true">
                <label id="radio-internal-text" for="radio-internal">&nbsp;</label>
                <input name="view-select" class="view-select" type="radio" value="external" id="radio-external" data-mini="true">
                <label id="radio-external-text" for="radio-external">&nbsp;</label>
                <input name="view-select" class="view-select" type="radio" value="qrd" id="radio-qrd" data-mini="true">
                <label id="radio-qrd-text" for="radio-qrd">&nbsp;</label>
            </fieldset>
        </div>
        <div class="lz-menu" style="width: 100%; z-index: 100;" id="view-select-panel2">
            <fieldset data-role="controlgroup" data-type="horizontal" id="view-select2">
                <input name="view-select2" class="view-select2" type="radio" value="left" id="radio-left" data-mini="true">
                <label id="radio-left-text" for="radio-left">&nbsp;</label>
                <input name="view-select2" class="view-select2" type="radio" value="this" id="radio-this" data-mini="true">
                <label id="radio-this-text" for="radio-this">&nbsp;</label>
                <input name="view-select2" class="view-select2" type="radio" value="right" id="radio-right" data-mini="true">
                <label id="radio-right-text" for="radio-right">&nbsp;</label>
            </fieldset>
        </div>

        <div class="lz-main" style="text-align:center;" id="chatframe">
            <div id="chat" style="display:block;">

                <div id="chat-container">
                    <div id="chat-container-headline"></div>
                <div style="margin: 5px 5px 5px 5px;" id="active-chat-panel">
                    <div id="switch-center-page" style="display: none;"></div>
                </div>
                <div id="chat-table">
                    <div id="chat-logo" style="text-align: center; display: block;">
                            <iframe id="logo-page"></iframe>
                    </div>
                    <div id="chat-progress" style="text-align: left; display: none;"></div>
                    <div id="chat-buttons" style="display: none;"></div>
                    <div id="chat-action" style="display: none;">
                        <div id="chat-input-controls"></div>
                        <div id="chat-input-body">
                            <label for="chat-input" style="display: none;">Chat-Input</label>
                            <textarea data-role="none" id="chat-input" onkeypress="return catchEnterButtonPressed(event);" onblur="doMacMagicStuff()"></textarea><br>
                        </div>
                    </div>
                    <div id="chat-title" style="display: none;"></div>
                </div>
                </div>

                <div id="translation-container" style="display: none; z-index: 20;">
                    <div id="translation-container-headline"></div>
                    <div id="translation-container-headline2"></div>
                    <div id="translation-container-footline"></div>
                </div>
                <div id="usersettings-container" style="display: none; z-index: 20;">
                    <div id="usersettings-container-headline"></div>
                    <div id="usersettings-container-headline2"></div>
                    <div id="usersettings-container-body"></div>
                    <div id="usersettings-container-footline"></div>
                </div>
                <div id="qrd-tree">
                    <div id="qrd-tree-headline"></div>
                    <div id="qrd-tree-headline2"></div>
                    <div id="qrd-tree-body"></div>
                </div>
                <div id="operator-list">
                    <div id="operator-list-headline"></div>
                    <div id="operator-list-headline2"></div>
                    <div id="operator-list-body"></div>
                </div>
                <div id="visitor-list">
                    <div id="visitor-list-headline"></div>
                    <div id="visitor-list-headline2"></div>
                    <div id="visitor-list-table-div"></div>
                </div>
                <div id="operator-forward-list" style="display:none;">
                    <div id="operator-forward-list-headline"></div>
                    <div id="operator-forward-list-headline2"></div>
                    <div id="operator-forward-list-body">
                        <div id="fwd-container"></div>
                    </div>
                    <div id="operator-forward-list-footline"></div>
                </div>

            </div>
            <div id="visitor-info" style="display:block;">
                <div id="visitor-info-headline"></div>
                <div id="visitor-info-headline2"></div>
                <div id="visitor-info-body"></div>
                <div id="visitor-browser-history-body"></div>
                <div id="visitor-info-footline"></div>
            </div>
            <div id="errors" style="text-align:left;display:none;"></div>
        </div>

        <div id="iframe-container" style="display: none;">
            <div id="iframe-container-headline">
                <div id="iframe-url" style="display: none;"></div>
                <div id="iframe-close-button" style="display: none;" title="close"></div>
                <div id="iframe-hide-button" style="display: none;" title="minimize"></div>
            </div>
        </div>
        <div id="test-length-div" style="visibility:hidden;"></div>
    </article>
</div>

</body>
</html>