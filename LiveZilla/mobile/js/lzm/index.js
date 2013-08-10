/****************************************************************************************
 * LiveZilla index.js
 *
 * Copyright 2013 LiveZilla GmbH
 * All rights reserved.
 * LiveZilla is a registered trademark.
 *
 ***************************************************************************************/

var lzm_commonConfig = {};
var lzm_commonTools = {};
var lzm_commonDisplay = {};
var lzm_commonStorage = {};
var lzm_commonTranslation = {};
var loopCounter = 0;
var defaultProfile = {};

var urlGetObject = {};
var browserReference = null;

var serverUrl = '';
var serverProtocol = '';


var runningFromApp = false;
var localDbPrefix = '';
var pollInAppBrowser = false;
var fileTransfer = null;
var dataDir = null;

/**************************************************** File Download **************************************************/
function onFileSystemSuccess(fileSystem) {
    switch (device.platform.toLowerCase()) {
        case 'android':
            dataDir = fileSystem.root.fullPath + '/Download';
            break;
        default:
            fileSystem.root.getDirectory("net.livezilla.mobile",{create:true},onFileSystemGotDir,onFileSystemError);
            break;
    }
    console.log(device.platform);
    console.log(fileSystem.root.fullPath);
}

function onFileSystemGotDir(d) {
    dataDir = d.fullPath;
}

function onFileSystemError(e){
    console.log("ERROR");
    console.log(JSON.stringify(e));
}

var injectedCode = '(function() {' +
    'var tmpFilesToDownload = filesToDownload; ' +
    'filesToDownload = []; ' +
    'return tmpFilesToDownload; ' +
    '})()';

function pollForDownloads() {
    browserReference.executeScript({
        code: injectedCode
    }, function(data) {
        for (var i=0; i<data[0].length; i++) {
            downloadFile(data[0][i]);
        }
    });
}

function downloadFile(uri) {
    var fileName = 'lzm-download.bin';
    if (uri.indexOf('?') != -1) {
        var uriParams = uri.split('?')[1];
        if (uriParams.indexOf('&') != -1) {
            uriParams = uriParams.split('&');
        } else {
            uriParams = [uriParams]
        }
        for (var i=0; i<uriParams.length; i++) {
            if (uriParams[i].indexOf('file') != -1 && uriParams[i].indexOf('=') != -1) {
                fileName = uriParams[i].split('=')[1];
            }
        }
    }
    uri = encodeURI(uri);
    fileTransfer.download(
        uri,
        dataDir + '/' + fileName,
        function(entry) {
            console.log("download complete: " + entry.fullPath);
            console.log(entry.getMetadata());
        },
        function(error) {
            console.log("download error source " + error.source);
            console.log("download error target " + error.target);
            console.log("upload error code" + error.code);
        },
        true
    );
}
/**************************************************** File Download **************************************************/

function openBrowser(url) {
    browserReference = window.open(url, '_blank', 'location=no,toolbar=no,allowInlineMediaPlayback=yes');
    browserReference.addEventListener('loadstop', stopLoadingInAppBrowser);
    browserReference.addEventListener('exit', closeInAppBrowser);
    pollInAppBrowser = setInterval(function() {
        pollForDownloads()
    }, 2000);
}

function submitLoginForm(loginData, acid) {
    var targetUrl = 'chat.php?acid=' + acid;

    for (var key in loginData) {
        $('#data-submit-form').append('<input type="hidden" id="' + key + '" name="' + key + '" value="' + loginData[key] + '" />');
    }
    $('#data-submit-form').attr('action', targetUrl);
    $('#data-submit-form').trigger('create');
    $('#data-submit-form').submit();
}

function stopLoadingInAppBrowser(event) {
    if (event.url.indexOf("logout.html") != -1) {
        if (event.url.indexOf('?') != -1) {
            var urlGetLine = event.url.split('?')[1];
            var tmpArray = (urlGetLine.indexOf('&') != -1) ? urlGetLine.split('&') : [];
            for (var i = 0; i < tmpArray.length; i++) {
                urlGetObject[tmpArray[i].split('=')[0]] = tmpArray[i].split('=')[1];
                //make sure the get parameters of logout.html are set properly
                //console.log(tmpArray[i].split('=')[0] + ' = ' + tmpArray[i].split('=')[1]);
                lzm_commonStorage.saveValue(tmpArray[i].split('=')[0], tmpArray[i].split('=')[1]);
                lzm_commonStorage.loadProfileData();
            }
        }
        browserReference.close();
    }
}

function closeInAppBrowser(event) {
    clearInterval(pollInAppBrowser);
    pollInAppBrowser = false;
    browserReference.removeEventListener('loadstop', stopLoadingInAppBrowser);
    browserReference.removeEventListener('exit', closeInAppBrowser);
    document.location.reload(true);
}

function getUserStatusLogo(status) {
    var userStatusLogo;
    status = (typeof status != 'undefined' && status != 'undefined') ? status : 0;
    //console.log(status);
    for (var j= 0; j<lzm_commonConfig.lz_user_states.length; j++) {
        if (status == lzm_commonConfig.lz_user_states[j].index) {
            userStatusLogo = lzm_commonConfig.lz_user_states[j].icon;
        }
    }
    //console.log(userStatusLogo);
    return userStatusLogo;
}

function t(myString, replacementArray) {
    return lzm_commonTranslation.translate(myString, replacementArray);
}

function fillStringsFromTranslation(selectedIndex) {
    if (loopCounter > 49 || lzm_commonTranslation.translationArray.length != 0) {
        $('#username-text').html(t('Username:'));
        $('#password-text').html(t('Password:'));
        $('#username').attr('placeholder', t('Username'));
        $('#password').attr('placeholder', t('Password'));
        $('#save_login-text span.ui-btn-text').text(t('Save login data'));
        $('#login_btn span.ui-btn-text').text(' ' + t('Log in') + ' ').trigger('create');
        $('#configure_btn span.ui-btn-text').text(t('Profiles'));
        $('#headline1').html(t('LiveZilla Mobile <!--begin_color-->beta<!--end_color-->',[['<!--begin_color-->','<span style="color: #ff6c00;font-weight: bold;">'],['<!--end_color-->','</span>']]));
        lzm_commonDisplay.fillProfileSelectList(lzm_commonStorage.storageData, runningFromApp, selectedIndex);

        var selectedStatus = 0;
        if (typeof defaultProfile.user_status != 'undefined') {
            selectedStatus = defaultProfile.user_status;
        }
        fillUserStatusSelect(selectedStatus);
    } else {
        loopCounter++;
        setTimeout(function () {
            fillStringsFromTranslation(selectedIndex);
        }, 50);
    }
}

function fillUserStatusSelect(selectedStatus) {
    var userStatusHtml = '';
    for (var i = 0; i < lzm_commonConfig.lz_user_states.length; i++) {
        var selectOption = '';
        if (typeof selectedStatus != 'undefined' && selectedStatus != '' && selectedStatus != null &&
            selectedStatus != 'undefined' && selectedStatus != 'null' &&
            String(lzm_commonConfig.lz_user_states[i].index) == String(selectedStatus)) {
            selectOption = ' selected="selected"';
        }
        if (lzm_commonConfig.lz_user_states[i].index != 2) {
            userStatusHtml += '<option value="' + lzm_commonConfig.lz_user_states[i].index + '"' + selectOption + '>' +
                t(lzm_commonConfig.lz_user_states[i].text) + '</option>';
        }
    }
    $('#user_status').html(userStatusHtml).selectmenu('refresh');
}

/**
 * Poll the server once using the data object for login
 * After the server accepted the login, do not use this again
 * @param serverProtocol
 * @param serverUrl
 */
function pollServerlogin(serverProtocol, serverUrl, serverPort, loginName, password, status, loginId, isApp, isWeb, b64login,
                         b64password, b64status, b64index, b64profile, b64port, b64protocol, b64url, b64loginid,
                         b64volume, b64away, b64playNewMessageSound, b64playNewChatSound, b64repeatNewChatSound, b64language,
                         localDbPrefix, ignoreSignedOn) {
    ignoreSignedOn = (typeof ignoreSignedOn != 'undefined') ? ignoreSignedOn : false;
    var p_acid = lzm_commonTools.pad(Math.floor(Math.random() * 99999).toString(10), 5);
    var acid = lzm_commonTools.pad(Math.floor(Math.random() * 1048575).toString(16), 5);

    var loginDataObject = {
        p_user_status: status,
        p_user: loginName,
        p_pass: password,
        p_acid: p_acid,
        p_request: 'intern',
        p_action: 'login',
        p_get_management: 1,
        p_version: lzm_commonConfig.lz_version,
        p_clienttime: Math.floor($.now()/1000),
        p_app: isApp,
        p_web: isWeb,
        p_loginid: loginId
    };
    if (ignoreSignedOn) {
        loginDataObject.p_iso = 1;
    }
    if (serverUrl.indexOf(':') == -1) {
        var urlParts = serverUrl.split('/');
        serverUrl = serverProtocol + urlParts[0] + ':' + serverPort;
        for (var i = 1; i < urlParts.length; i++) {
            serverUrl += '/' + urlParts[i];
        }
    } else if (serverUrl.indexOf(serverProtocol) == -1) {
        serverUrl = serverProtocol + serverUrl;
    }
    var postUrl = serverUrl + '/server.php?acid=' + acid;
    //console.log(loginDataObject);
    //console.log(postUrl);
    $.ajax({
        type: "POST",
        url: postUrl,
        //crossDomain: true,
        data: loginDataObject,
        timeout: lzm_commonConfig.pollTimeout,
        success: function (data) {
            var xmlDoc = $.parseXML(data);
            var error_value = -1;
            $(xmlDoc).find('validation_error').each(function () {
                if (error_value == -1) {
                    error_value = lz_global_base64_url_decode($(this).attr('value'));
                }
            });
            switch (String(error_value)) {
                case "0":
                    alert(t('Wrong username or password!'));
                    $('#login_btn').removeClass('ui-disabled');
                    break;
                case "2":
                    if (confirm(t('The operator <!--op_login_name--> is already logged in!',[['<!--op_login_name-->', loginName]]) + '\n' +
                    t('Do you want to log off the other instance?'))) {
                        pollServerlogin(serverProtocol, serverUrl, serverPort, loginName, password, status, loginId, isApp, isWeb, b64login,
                            b64password, b64status, b64index, b64profile, b64port, b64protocol, b64url, b64loginid,
                            b64volume, b64away, b64playNewMessageSound, b64playNewChatSound, b64repeatNewChatSound, b64language,
                            localDbPrefix, true);
                    } else {
                        $('#login_btn').removeClass('ui-disabled');
                    }
                    break;
                case "3":
                    alert(t("You've been logged off by another operator!"));
                    $('#login_btn').removeClass('ui-disabled');
                    break;
                case "4":
                    alert(t('Session timed out!'));
                    $('#login_btn').removeClass('ui-disabled');
                    break;
                case "5":
                    alert(t('You have to change your password!'));
                    $('#login_btn').removeClass('ui-disabled');
                    break;
                case "9":
                    alert(t('You are not an administrator!'));
                    $('#login_btn').removeClass('ui-disabled');
                    break;
                case "10":
                    alert(t('This LiveZilla server has been deactivated by the administrator.') + '\n' +
                        t('If you are the administrator, please activate this server under LiveZilla Server Admin -> Server Configuration -> Server.'));
                    $('#login_btn').removeClass('ui-disabled');
                    break;
                case "13":
                    alert(t('There are problems with the database connection!'));
                    $('#login_btn').removeClass('ui-disabled');
                    break;
                case "14":
                    alert(t('This server requires secure connection (SSL). Please activate HTTPS in the server profile and try again.'));
                    $('#login_btn').removeClass('ui-disabled');
                    break;
                case "11":
                case "-1":
                    if (isApp) {
                        openBrowser(serverUrl + '/mobile/chat.php?login=' + b64login + '&password=' + b64password + '&status=' + b64status +
                            '&index=' + b64index + '&profile=' + b64profile + '&port=' + b64port + '&protocol=' + b64protocol + '&url=' + b64url +
                            '&acid=' + acid + '&app=1' + '&loginid=' + b64loginid +
                            '&volume=' + b64volume + '&away_after=' + b64away +
                            '&play_incoming_message_sound=' + b64playNewMessageSound +
                            '&play_incoming_chat_sound=' + b64playNewChatSound +
                            '&repeat_incoming_chat_sound=' + b64repeatNewChatSound +
                            '&language=' + b64language +
                            '&local_db_prefix=' + localDbPrefix);
                    } else if (isWeb) {
                        submitLoginForm({
                            login: b64login,
                            password: b64password,
                            status: b64status,
                            index: b64index,
                            profile: b64profile,
                            port: b64port,
                            protocol: b64protocol,
                            url: b64url,
                            web: 1,
                            loginid: b64loginid,
                            volume: b64volume,
                            away_after: b64away,
                            play_incoming_message_sound: b64playNewMessageSound,
                            play_incoming_chat_sound: b64playNewChatSound,
                            repeat_incoming_chat_sound: b64repeatNewChatSound,
                            language: b64language,
                            local_db_prefix: localDbPrefix
                        }, acid);
                    }
                    break;
                default:
                    alert('Validation Error : ' + error_value);
                    break;
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            $('#login_btn').removeClass('ui-disabled');
            if (jqXHR.statusText == 'timeout') {
                alert(t('The server did not respond for more then <!--number_of_seconds--> seconds.',
                    [['<!--number_of_seconds-->',lzm_commonConfig.pollTimeout / 1000]]));
            } else {
                //console.log('Status-Text : ' + textStatus);
                //console.log(jqXHR);
                //console.log('Error-Text : ' + errorThrown);
                alert(t('The server returned an error') + '\n' +
                    t('Error code : <!--http_error-->',[['<!--http_error-->',jqXHR.status]]) +
                    '\n' + t('Error text : <!--http_error_text-->',[['<!--http_error_text-->',jqXHR.statusText]]));
            }
        },
        dataType: 'text'
    });
}

function hasStorage() {
    var returnValue;
    try {
        localStorage.setItem('test', 'test');
        returnValue = localStorage.getItem('test');
        localStorage.removeItem('test');
    } catch(e) {
        returnValue = false;
    }
    return returnValue;
}

$(document).ready(function () {
    if (runningFromApp) {
        $('#configure_btn').css({display: 'block'});
        $('#server_profile_selection').css({display: 'block'});
    }
    // Detect if the local storage is working
    if (!runningFromApp && hasStorage() != 'test') {
        lzm_commonTools = new CommonToolsClass();
        var urlParts = lzm_commonTools.getUrlParts();
        lzm_commonTranslation = new CommonTranslationClass(urlParts.protocol, urlParts.urlBase + ':' +
            urlParts.port + urlParts.urlRest, false);

        var errorHtml = '<h1>' + t('Browser settings incorrect') + '</h1>' +
            '<p>' + t('Your browser seems to have its local storage/cookies disabled.') + '<br />' +
            t('Since the local storage is needed for this application, you have to enable the local storage/cookies in your browser settings and reload this page!') + '</p>';
        $('body').html(errorHtml);
    } else {

    // initiate the lzm classes needed
    lzm_commonConfig = new CommonConfigClass();
    lzm_commonTools = new CommonToolsClass();
    if (!runningFromApp) {
        localDbPrefix = md5(lzm_commonTools.getUrlParts()['urlRest']).substr(0,10);
        //console.log(localDbPrefix);
    }
    lzm_commonStorage = new CommonStorageClass(localDbPrefix);
    lzm_commonDisplay = new CommonDisplayClass(runningFromApp);
    if (!runningFromApp) {
        var urlParts = lzm_commonTools.getUrlParts();
        lzm_commonTranslation = new CommonTranslationClass(urlParts.protocol, urlParts.urlBase + ':' +
            urlParts.port + urlParts.urlRest, false);
    } else {
        lzm_commonTranslation = new CommonTranslationClass('', '', true);
    }

    // load the storage values and fill the profile select list
    lzm_commonStorage.loadProfileData();
    var selectedIndex = (typeof lzm_commonStorage.loadValue('last_chosen_profile') != 'undefined' &&
        lzm_commonStorage.loadValue('last_chosen_profile') != 'undefined' &&
        lzm_commonStorage.loadValue('last_chosen_profile') != null) ?
        lzm_commonStorage.loadValue('last_chosen_profile') : -1;
    fillStringsFromTranslation(selectedIndex);

    var thisServerProfileSelection = $('#server_profile_selection');
    var thisLoginData = $('.login-data');
    var thisSaveLoginQuestion = $('#save-login-question');
    var thisSaveLogin = $('#save_login');

    $('#login_btn').click(function () {
        $('#login_btn').addClass('ui-disabled');
        var selectedIndex = (thisServerProfileSelection.val() != -1) ? thisServerProfileSelection.val() : 0;
        var chosenProfile = lzm_commonStorage.getProfileByIndex(selectedIndex);
        lzm_commonStorage.saveValue('last_chosen_profile', selectedIndex);

        // create a fake ip address...
        var loginId;
        if (typeof chosenProfile.fake_mac_address == 'undefined' || chosenProfile.fake_mac_address == '' ||
            chosenProfile.fake_mac_address == 'undefined' || chosenProfile.fake_mac_address == 'null' || chosenProfile.fake_mac_address == null) {
            var randomHex = String(md5(String(Math.random())));
            loginId = randomHex.toUpperCase().substr(0,2);
            for (var i=1; i<6; i++) {
                loginId += '-' + randomHex.toUpperCase().substr(2*i,2);
            }
            chosenProfile.fake_mac_address = loginId;
        } else {
            loginId = chosenProfile.fake_mac_address;
        }

        chosenProfile.user_status = $('#user_status').val();

        var login = lz_global_base64_url_encode($('#username').val());
        var password = lz_global_base64_url_encode($('#password').val());
        var userStatus = lz_global_base64_url_encode($('#user_status').val());
        var index = lz_global_base64_url_encode(selectedIndex);
        var profile = lz_global_base64_url_encode(chosenProfile.server_profile);
        var port = lz_global_base64_url_encode(chosenProfile.server_port);
        var protocol = lz_global_base64_url_encode(chosenProfile.server_protocol);
        var url = lz_global_base64_url_encode(chosenProfile.server_url);
        var volume = lz_global_base64_url_encode(60);
        var awayAfter = lz_global_base64_url_encode(0);
        var b64playNewMessageSound = lz_global_base64_url_encode(1);
        var b64playNewChatSound = lz_global_base64_url_encode(1);
        var b64repeatNewChatSound = lz_global_base64_url_encode(1);
        var b64loginId = lz_global_base64_url_encode(loginId);
        var b64language = lz_global_base64_url_encode(lzm_commonTranslation.language);
        if (typeof chosenProfile.user_volume != 'undefined' && chosenProfile.user_volume != null &&
            chosenProfile.user_volume != 'null' && chosenProfile.user_volume != 'undefined') {
            volume = lz_global_base64_url_encode(chosenProfile.user_volume);
        }
        if (typeof chosenProfile.user_away_after != 'undefined' && chosenProfile.user_away_after != null &&
            chosenProfile.user_away_after != 'null' && chosenProfile.user_away_after != 'undefined') {
            awayAfter = lz_global_base64_url_encode(chosenProfile.user_away_after);
        }
        if (typeof chosenProfile.play_incoming_message_sound != 'undefined' && chosenProfile.play_incoming_message_sound != null &&
            chosenProfile.play_incoming_message_sound != 'null' && chosenProfile.play_incoming_message_sound != 'undefined') {
            b64playNewMessageSound = lz_global_base64_url_encode(chosenProfile.play_incoming_message_sound);
        }
        if (typeof chosenProfile.play_incoming_chat_sound != 'undefined' && chosenProfile.play_incoming_chat_sound != null &&
            chosenProfile.play_incoming_chat_sound != 'null' && chosenProfile.play_incoming_chat_sound != 'undefined') {
            b64playNewChatSound = lz_global_base64_url_encode(chosenProfile.play_incoming_chat_sound);
        }
        if (typeof chosenProfile.repeat_incoming_chat_sound != 'undefined' && chosenProfile.repeat_incoming_chat_sound != null &&
            chosenProfile.repeat_incoming_chat_sound != 'null' && chosenProfile.repeat_incoming_chat_sound != 'undefined') {
            b64repeatNewChatSound = lz_global_base64_url_encode(chosenProfile.repeat_incoming_chat_sound);
        }

        if (typeof chosenProfile.user_volume == 'undefined' || chosenProfile.user_volume == null ||
            chosenProfile.user_volume == 'null' || chosenProfile.user_volume == 'undefined')
            chosenProfile.user_volume = 60;
        if (selectedIndex == 0) {
            if (thisSaveLogin.prop('checked') == true) {
                chosenProfile.login_name = $('#username').val();
                chosenProfile.login_passwd = $('#password').val();
            } else {
                chosenProfile.login_name = '';
                chosenProfile.login_passwd = '';
            }
        }
        chosenProfile.language = lzm_commonTranslation.language;
        chosenProfile.index = selectedIndex;
        lzm_commonStorage.saveProfile(chosenProfile);

        var isApp = (runningFromApp) ? 1 : 0;
        var isWeb = 1 - isApp;
        pollServerlogin(chosenProfile.server_protocol, chosenProfile.server_url, chosenProfile.server_port,
            $('#username').val(), $('#password').val(), $('#user_status').val(), loginId, isApp, isWeb, login,
            password, userStatus, index, profile, port, protocol, url, b64loginId, volume, awayAfter,
            b64playNewMessageSound, b64playNewChatSound, b64repeatNewChatSound, b64language, localDbPrefix, false);
    });

    $('#configure_btn').click(function () {
        lzm_commonStorage.saveValue('last_chosen_profile', thisServerProfileSelection.val());
        window.location.href = 'configure.html';
    });

    lzm_commonTools.createDefaultProfile(runningFromApp, thisServerProfileSelection.val());

    if (!runningFromApp) {
        $('#user_status').parent().addClass('ui-disabled');
        defaultProfile = lzm_commonStorage.getProfileByIndex(0);
        if (defaultProfile.login_name != '') {
            $('#username').val(defaultProfile.login_name);
            $('#password').val(defaultProfile.login_passwd);
            fillUserStatusSelect(defaultProfile.user_status);
            //console.log('User status : ' + defaultProfile.user_status);
            thisSaveLogin.prop('checked', true).checkboxradio("refresh");
            if (defaultProfile.login_passwd != '') {
                $('#login_btn').removeClass('ui-disabled');
                $('#user_status').parent().removeClass('ui-disabled');
            }
        }
        thisLoginData.css('display', 'block');
        thisSaveLoginQuestion.css('display', 'block');
    } else {
        $('.login-input').addClass('ui-disabled');
        $('#user_status').parent().addClass('ui-disabled');
        if (selectedIndex != -1) {
            var dataSet = lzm_commonStorage.getProfileByIndex(selectedIndex);
            if (typeof dataSet.login_name != 'undefined') {
                $('#username').val(dataSet.login_name);
                $('#password').val(dataSet.login_passwd);
            }
            $('.login-input').removeClass('ui-disabled');
            if (dataSet.login_name != '' && dataSet.login_passwd != '') {
                $('#login_btn').removeClass('ui-disabled');
                $('#user_status').parent().removeClass('ui-disabled');
            }
        }
    }
    var userStatusLogo = getUserStatusLogo(defaultProfile.user_status);

    lzm_commonDisplay.createLayout(userStatusLogo);

    thisServerProfileSelection.change(function () {
        var selectedValue;
        if (!runningFromApp) {
            selectedValue = (thisServerProfileSelection.val() != -1) ? thisServerProfileSelection.val() : 0;
        } else {
            selectedValue = thisServerProfileSelection.val();
        }
        //console.log(selectedValue);
        if (selectedValue != -1) {
            var dataSet = lzm_commonStorage.getProfileByIndex(selectedValue);
            //console.log(dataSet);
            //console.log('Saved volume : ' + dataSet.user_volume);
            //console.log('Saved away time : ' + dataSet.user_away_after);

            if (typeof dataSet.login_name != 'undefined') {
                $('#username').val(dataSet.login_name);
                $('#password').val(dataSet.login_passwd);
            }
            fillUserStatusSelect(dataSet.user_status);
            if (thisServerProfileSelection.val() != -1) {
                thisLoginData.css('display', 'block');
                thisSaveLoginQuestion.css('visibility', 'hidden');
            } else {
                if (runningFromApp) {
                    thisLoginData.css('display', 'block');
                    thisSaveLoginQuestion.css('visibility', 'hidden');
                } else {
                    thisLoginData.css('display', 'block');
                    thisSaveLoginQuestion.css('visibility', 'visible');
                }
            }
            $('.login-input').removeClass('ui-disabled');
            $('#user_status').parent().removeClass('ui-disabled');
        } else {
            $('.login-input').addClass('ui-disabled');
            $('#login_btn').addClass('ui-disabled');
            $('#user_status').parent().addClass('ui-disabled');
            $('#username').val('');
            $('#password').val('');
        }
        var selectedUserStatus = (typeof dataSet != 'undefined') ? dataSet.user_status : 0;
        $('#user_status').parent().children('span').children('.ui-icon').css({'background-image': 'url("' + getUserStatusLogo(selectedUserStatus) + '")', 'background-position': 'center'});
        $('#user_status').parent().children('span').css({'text-align': 'left'});
        $('#user_status').parent().children('span').children('.ui-btn-text').css({'padding-left': '3px'});
        if (selectedValue != -1) {
            $('#username').keyup();
        }
    });

    $('#user_status').change(function() {
        var selectedUserStatus = $('#user_status').val();
        $('#user_status').parent().children('span').children('.ui-icon').css({'background-image': 'url("' + getUserStatusLogo(selectedUserStatus) + '")', 'background-position': 'center'});
        $('#user_status').parent().children('span').css({'text-align': 'left'});
        $('#user_status').parent().children('span').children('.ui-btn-text').css({'padding-left': '3px'});
    });

    $('.login-input').keyup(function () {
        if ($('#username').val() != '' && $('#password').val() != '') {
            $('#login_btn').removeClass('ui-disabled');
            $('#user_status').parent().removeClass('ui-disabled');
        } else {
            $('#login_btn').addClass('ui-disabled');
            $('#user_status').parent().addClass('ui-disabled');
        }
    });

    $(window).resize(function () {
        lzm_commonDisplay.createLayout();
        setTimeout(function() {
            var selectedUserStatus = $('#user_status').val();
            $('#user_status').parent().children('span').children('.ui-icon').css({'background-image': 'url("' + getUserStatusLogo(selectedUserStatus) + '")', 'background-position': 'center'});
        }, 4);
    });
    }
});
