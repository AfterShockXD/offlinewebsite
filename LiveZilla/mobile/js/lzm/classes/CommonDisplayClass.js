/****************************************************************************************
 * LiveZilla CommonDisplayClass.js
 *
 * Copyright 2013 LiveZilla GmbH
 * All rights reserved.
 * LiveZilla is a registered trademark.
 *
 ***************************************************************************************/

/**
 * Class containing some methods used in several html frontends
 * @constructor
 */
function CommonDisplayClass(isApp) {
    //console.log('CommonDisplayClass initiated');
    this.isApp = isApp;
}

CommonDisplayClass.prototype.createLayout = function(userStatusLogo) {
    var windowWidth = $(window).width();
    var windowHeight = $(window).height();
    var headerHeight = $('#header_login').height();
    var thisInputContainer = $('#input-container');
    var thisLogoContainer = $('#logo-container');
    var thisLoginPage = $('#login_page');
    var thisLoginContainer = $('#login-container');
    var thisProfileConfigureButton = $('#configure_btn');
    var thisUserStatus = $('#user_status').parent();
    var thisLoginButton = $('#login_btn');
    var thisServerProfileSelection = $('#server_profile_selection').parent().parent();
    var borderStyle = '1px solid #ddd';
    var roundedStyle = '10px';
    var shadowStyle = '4px 4px 2px #eee';

    var inputContainerWidth = (windowWidth >= 1000) ? 800 : windowWidth - 40;
    var inputContainerLeft = Math.floor((windowWidth - inputContainerWidth) / 2) - 10;
    var logoWidth = (inputContainerWidth - 60 > 271) ? 271 : Math.floor(271*((inputContainerWidth - 60)/271));
    var logoHeight = (inputContainerWidth - 60 > 271) ? 84 : Math.floor(84*((inputContainerWidth - 60)/271));
    var logoLeft = (windowWidth - logoWidth) / 2;

    var logoContainerCss = {
        position: 'absolute',
        left: logoLeft,
        top: headerHeight + 30,
        height: logoHeight,
        width: logoWidth,
        background: '#ffffff',
        'background-image': 'url("img/logo.png")',
        'background-position': 'center',
        'background-repeat': 'no-repeat',
        'background-size': 'contain'
    };
    var inputContainerCss = {position: 'absolute',
        width: inputContainerWidth,
        height: windowHeight - headerHeight - logoHeight - 60,
        left: inputContainerLeft,
        top: logoHeight + headerHeight + 40,
        background: '#ffffff',
        padding: '10px',
        'border-radius': roundedStyle, '-moz-border-radius': roundedStyle, '-webkit-border-radius': roundedStyle,
        'overflow-y': 'auto',
        'overflow-x': 'hidden'
    };
    var loginContainerCss = {padding: '10px 20px', 'border-radius': '4px', border: borderStyle,
        'border-radius': roundedStyle, '-moz-border-radius': roundedStyle, '-webkit-border-radius': roundedStyle,
        'box-shadow': shadowStyle, '-moz-box-shadow': shadowStyle, '-webkit-box-shadow': shadowStyle};

    thisLoginPage.css({background: '#ffffff'});
    thisLoginContainer.css(loginContainerCss);
    thisInputContainer.css(inputContainerCss);
    thisLogoContainer.css(logoContainerCss);

    var iconButtonWidth, loginButtonWidth, profileSelectWidth, buttonDistance, statusButtonWidth;
    var profileConfigLeft, userStatusLeft, profileSelectLeft, loginButtonLeft;
    var profileButtonDisplay;
    if (this.isApp) {
        iconButtonWidth = 28;
        loginButtonWidth = 130;
        statusButtonWidth = 130;
        buttonDistance = 5;
        profileSelectWidth = inputContainerWidth - iconButtonWidth - buttonDistance - 45;

        profileConfigLeft = inputContainerWidth - iconButtonWidth - 48;
        profileSelectLeft = 0;
        userStatusLeft = inputContainerWidth - loginButtonWidth - statusButtonWidth + 5;
        loginButtonLeft = inputContainerWidth - loginButtonWidth + 5;

        profileButtonDisplay = 'block';
    } else {
        iconButtonWidth = 28;
        loginButtonWidth = 130;
        statusButtonWidth = 130;
        buttonDistance = 5;
        profileSelectWidth = 0;

        userStatusLeft = inputContainerWidth - loginButtonWidth - statusButtonWidth;
        loginButtonLeft = inputContainerWidth - loginButtonWidth;
        profileConfigLeft = 0;
        profileSelectLeft = 0;

        profileButtonDisplay = 'none';
    }

    var loginButtonCss = {position: 'absolute', width: loginButtonWidth+'px', left: loginButtonLeft+'px'};
    var userStatusCss = {position: 'absolute', width: statusButtonWidth+'px', left: userStatusLeft+'px'};
    var serverProfileSelectionCss = {width: profileSelectWidth+'px',
        left: profileSelectLeft+'px', display: profileButtonDisplay};
    var profileButtonCss = {width: iconButtonWidth+'px',
        left: profileConfigLeft+'px', display: profileButtonDisplay,
        'margin-top': '-35px'};
    thisServerProfileSelection.css(serverProfileSelectionCss);
    thisProfileConfigureButton.css(profileButtonCss);
    thisUserStatus.parent().css(userStatusCss);
    thisUserStatus.css({'width': statusButtonWidth+'px',
        background: '#fff', 'background-image': 'none', 'border': 'none',
        'box-shadow': 'none', '-moz-box-shadow': 'none', '-webkit-box-shadow': 'none'});
    thisUserStatus.children('span').css({'text-align': 'left'});
    thisUserStatus.children('span').children('.ui-icon').css({'background-image': 'url("' + userStatusLogo + '")',
        'background-position': 'center',
        'box-shadow': 'none', '-moz-box-shadow': 'none', '-webkit-box-shadow': 'none',
        'background-size': '18px 18px'});
    thisUserStatus.children('span').children('.ui-btn-text').css({'padding-left': '3px'});
    thisProfileConfigureButton.children('span').children('.ui-btn-text').html('&nbsp;');
    thisLoginButton.css(loginButtonCss);
};

/**
 * Fill the profile select list
 */
CommonDisplayClass.prototype.fillProfileSelectList = function(storageData, runningFromApp, selectedIndex) {
    selectedIndex = (typeof selectedIndex != 'undefined') ? selectedIndex : -1;
    var htmlString = '<option data-placeholder="true" value="-1" id="no-profile">-- ' + t('No profile selected') + ' --</option>';
    storageData.sort(this.sortProfiles);
    var selectedString = '';
    for (var i=0; i<storageData.length; i++) {
        selectedString = '';
        if (storageData[i].index == selectedIndex) {
            selectedString = ' selected="selected"';
        }
        if (storageData[i].index != 0) {
            htmlString += '<option value="' + storageData[i].index + '"' + selectedString + '>' + storageData[i].server_profile + '</option>';
        }
    }
    var thisServerProfileSelection = $('#server_profile_selection');
    thisServerProfileSelection.html(htmlString).selectmenu("refresh");
    if (typeof runningFromApp != 'undefined' && runningFromApp == false)
        thisServerProfileSelection.parent().parent().css({display: 'none'});

};

/**
 * Helper function for sorting the profiles shown in the select lists
 * @param a
 * @param b
 * @return {Boolean}
 */
CommonDisplayClass.prototype.sortProfiles = function(a,b) {
    return (a['server_profile'].toLowerCase() > b['server_profile'].toLowerCase());
};
