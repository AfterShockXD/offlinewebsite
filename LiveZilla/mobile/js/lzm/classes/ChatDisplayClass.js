/****************************************************************************************
 * LiveZilla ChatDisplayClass.js
 *
 * Copyright 2013 LiveZilla GmbH
 * All rights reserved.
 * LiveZilla is a registered trademark.
 *
 ***************************************************************************************/

/**
 * Class controlling the page layout and the creation of the html parts
 * @constructor
 */
function ChatDisplayClass(now, lzm_commonConfig, lzm_commonTools, lzm_chatInputEditor, web, app, messageTemplates,
                          userConfigData) {
    //console.log('ChatDisplayClass initiated');
    this.debuggingDisplayMode = 'none';

    // variables controlling the behaviour of the chat page
    this.senderType = '';
    this.myLoginId = '';
    this.myId = '';
    this.myName = '';
    this.active_chat = '';
    this.active_chat_reco = '';
    this.active_chat_realname = '';
    this.user_status = 0;
    this.selected_view = 'mychats';
    this.lastActiveChat = '';
    this.displayWidth = 'large';
    this.infoCaller = '';
    this.infoUser = {};
    this.thisUser = {};
    this.editThisTranslation = '';
    this.chatActivity = false;
    this.soundPlayed = [];
    this.isRinging = {};
    this.VisitorListCreated = false;
    this.ShowVisitorInfo = false;
    this.ShowVisitorId = '';
    this.newExternalUsers = [];
    this.changedExternalUsers = [];
    this.userLanguage = '';
    this.closedChats = [];
    this.openedResourcesFolder = ['1'];
    this.selectedResource = '';

    this.serverIsDisabled = false;
    this.lastDiabledWarningTime = 0;
    this.askBeforeUnload = true;

    // Values from the user's configuration
    this.awayAfterTime = userConfigData['awayAfter'];
    this.volume = userConfigData['userVolume'];
    this.playNewMessageSound = userConfigData['playIncomingMessageSound'];
    this.playNewChatSound = userConfigData['playIncomingChatSound'];
    this.repeatNewChatSound = userConfigData['repeatIncomingChatSound'];

    this.showUserstatusHtml = false;
    this.showUsersettingsHtml = false;
    this.settingsDialogue = false;
    this.showBrowserHistory = ['', ''];
    this.showOpInviteList = false;
    this.windowWidth = 0;
    this.windowHeight = 0;
    this.chatPanelHeight = 0;
    this.visitorListHeight = 140;
    this.visitorSortBy = 'time';
    this.qrdSearchResults = [];

    this.chatLeftByOperators = {};

    this.validationErrorCount = 0;

    // variables passed to this class as parameters
    this.now = now;
    this.lzm_commonConfig = lzm_commonConfig;
    this.lzm_commonTools = lzm_commonTools;
    this.lzm_chatInputEditor = lzm_chatInputEditor;
    this.lzm_chatTimeStamp = {};
    this.isApp = app;
    this.isWeb = web;
    this.messageTemplates = messageTemplates;

    this.OperatorListHeadlineCss = {};
    this.OperatorListHeadline2Css = {};
    this.OperatorListBodyCss = {};
    this.VisitorListHeadlineCss = {};
    this.VisitorListHeadline2Css = {};
    this.visitorListTableCss = {};
    this.VisitorInfoHeadlineCss = {};
    this.VisitorInfoHeadline2Css = {};
    this.VisitorInfoFootlineCss = {};
    this.VisitorInfoBodyCss = {};
    this.OperatorForwardListHeadlineCss = {};
    this.OperatorForwardListHeadline2Css = {};
    this.OperatorForwardListBodyCss = {};
    this.OperatorForwardListFootlineCss = {};
    this.fwdContainerCss = {};
    this.TranslationContainerHeadlineCss = {};
    this.TranslationContainerHeadline2Css = {};
    this.TranslationContainerFootlineCss = {};
    this.UsersettingsContainerHeadlineCss = {};
    this.UsersettingsContainerHeadline2Css = {};
    this.UsersettingsContainerBodyCss = {};
    this.UsersettingsContainerFootlineCss = {};
    this.QrdTreeHeadlineCss = {};
    this.QrdTreeHeadline2Css = {};
    this.QrdTreeBodyCss = {};
    this.QrdTreeFootlineCss = {};
    this.settingsContainerCss = {};
    this.activeChatPanelHeight = 26;
    this.activeChatPanelLineCounter = 1;
    this.openChats = [];
    this.templateCloseButton = '<div id="%BTNID%" %BTNONCLICK%' +
        ' style=\'background-image: ' + this.addBrowserSpecificGradient('url("img/205-close.png")') + ';' +
        ' background-repeat: no-repeat; background-position: center; display: none;' +
        ' left: %BTNLEFT%px; top: %BTNTOP%px; width: 16px; %BTNDEFAULTCSS%\'></div>';

        this.browserName = 'other';
    if ($.browser.chrome)
        this.browserName = 'chrome';
    else if ($.browser.mozilla)
        this.browserName = 'mozilla';
    else if ($.browser.msie)
        this.browserName = 'ie';
    else if ($.browser.safari)
        this.browserName = 'safari';
    else if ($.browser.opera)
        this.browserName = 'opera';
    if ($.browser.version.indexOf('.') != -1) {
        this.browserVersion = $.browser.version.split('.')[0];
        this.browserMinorVersion = $.browser.version.split('.')[1];
    } else {
        this.browserVersion = $.browser.version;
        this.browserMinorVersion = 0;
    }
    // workarround for IE 11
    if (this.browserName == 'mozilla' && this.browserVersion == 11) {
        this.browserName = 'ie';
    }
}

// ****************************** Visibility functions ****************************** //
/**
 * Create the layout of the page depending on the window size
 */
ChatDisplayClass.prototype.createChatWindowLayout = function (recreate) {
    // Definitions for jquery selectors
    var thisBody = $('body');
    var thisChatPage = $('#chat_page');
    var thisActiveChatPanel = $('#active-chat-panel');
    var thisContentChat = $('#content_chat');
    var thisChat = $('#chat');
    var thisVisitorInfo = $('#visitor-info');
    var thisOperatorList = $('#operator-list');
    var thisChatContainer = $('#chat-container');
    var thisChatTable = $('#chat-table');
    var thisChatAction = $('#chat-action');
    var thisChatTitle = $('#chat-title');
    var thisChatButtons = $('#chat-buttons');
    var thisChatProgress = $('#chat-progress');
    var thisChatInput = $('#chat-input');
    var thisChatInputBody = $('#chat-input-body');
    var thisChatInputControls = $('#chat-input-controls');
    var thisChatLogo = $('#chat-logo');
    var thisVisitorList = $('#visitor-list');
    var thisOperatorForwardList = $('#operator-forward-list');
    var thisQrdTree = $('#qrd-tree');
    var thisSendBtn = $('#send-btn');
    var thisBlankChatBtn = $('#blank-chat-btn');
    var thisViewSelectPanel = $('#view-select-panel');
    var thisViewSelectPanel2 = $('#view-select-panel2');
    var thisUserControlPanel = $('#user-control-panel');
    //var thisSwitchCenterPage = $('#switch-center-page');
    var windowWidth = $(window).width();
    var windowHeight = $(window).height();

    // Do only do layout changes, when they are neccessary
    if (recreate || windowWidth != this.windowWidth || windowHeight != this.windowHeight ||
        this.activeChatPanelHeight < (this.chatPanelHeight - 5) ||
        this.activeChatPanelHeight > (this.chatPanelHeight + 5)) {
        this.windowWidth = windowWidth;
        this.windowHeight = windowHeight;
        this.chatPanelHeight = this.activeChatPanelHeight;

        var userControlPanelPosition = thisUserControlPanel.position();
        var userControlPanelHeight = thisUserControlPanel.height();
        var userControlPanelWidth = thisUserControlPanel.width();
        var viewSelectPanelHeight = thisViewSelectPanel.height();

        var articleWidth = thisContentChat.width();
        var chatTableHeight = 0;
        var visitorInfoTop = 0;
        var chatContainerTop = 0;
        var chatContainerHeight = 0;

        // variable declarations, if neccessary
        var visitorInfoWidth = 0;
        var visitorInfoLeft = 0;
        var thisVisitorInfoVisibility = '';
        var operatorListTop = 0;
        var operatorListDisplay = '';
        var operatorListLeft = 0;
        var chatTableTop = 0;
        var visitorListDisplay = '';
        var visitorListTop = 0;
        var visitorListWidth = 0;
        var visitorListLeft = 0;
        var visitorListHeight = 0;
        var chatWindowWidth = 0;
        var chatContainerWidth = 0;
        var chatContainerLeft = 0;
        var viewSelectPanelDisplay = '';
        var viewSelectPanel2Display = '';
        var chatWindowHeight = windowHeight - (userControlPanelPosition.top + userControlPanelHeight) - 20;
        var chatWindowTop = userControlPanelPosition.top + userControlPanelHeight + 10;
        var visitorInfoHeight = 0;
        var operatorForwardListTop = 0;
        var operatorForwardListLeft = 0;
        var operatorListWidth = 200;
        var chatButtonTop;
        var viewPageSwitcher = '';
        //var thisSwitchCenterPageRight = 0;

        if (articleWidth >= this.lzm_commonConfig.largeDisplayThreshold) {
            // definitions for large windows only
            this.displayWidth = 'large';
            visitorInfoWidth = 280;
            thisVisitorInfoVisibility = 'block';
            chatWindowWidth = userControlPanelWidth - visitorInfoWidth - 18;
            visitorInfoLeft = userControlPanelPosition.left + chatWindowWidth + 10;
            chatContainerWidth = chatWindowWidth - operatorListWidth - 35;
            visitorListWidth = userControlPanelWidth - 12;
            operatorListDisplay = 'block';
            visitorListDisplay = 'block';
            viewSelectPanelDisplay = 'none';
            visitorListHeight = this.visitorListHeight;
            chatContainerTop = 5;
            operatorListTop = 5;
            operatorListLeft = 5;
            visitorInfoTop = chatWindowTop + 5;
            visitorInfoHeight = chatWindowHeight - visitorListHeight - 30;
            chatTableHeight = visitorInfoHeight - this.activeChatPanelHeight - 10 - 22;
            visitorListTop = chatWindowHeight - visitorListHeight - 5;
            chatContainerLeft = operatorListWidth + 25;
            chatContainerHeight = visitorInfoHeight;
            chatTableTop = this.activeChatPanelHeight + 10 + 22;
            operatorForwardListTop = chatContainerTop;
            operatorForwardListLeft = chatContainerLeft;
            visitorListLeft = 5;
            chatButtonTop = (chatTableHeight - 40 + 5);
        }
        if (articleWidth < this.lzm_commonConfig.smallDisplayThreshold) {
            // small definitions
            this.displayWidth = 'small';
            operatorListDisplay = 'none';
            visitorListDisplay = 'none';
            viewSelectPanelDisplay = 'block';
            operatorListLeft = 10;
            chatWindowWidth = userControlPanelWidth - 8;
            chatContainerLeft = 10;
            chatContainerWidth = chatWindowWidth - 10;
            chatContainerHeight = chatWindowHeight - viewSelectPanelHeight - 35;
            visitorListHeight = chatContainerHeight;
            visitorListLeft = chatContainerLeft;
            operatorListWidth = chatContainerWidth;
            visitorListWidth = chatContainerWidth;
            chatTableHeight = chatContainerHeight - this.activeChatPanelHeight - 10 - 22;
            visitorInfoHeight = chatContainerHeight;
            visitorInfoWidth = chatContainerWidth;
            visitorInfoLeft = userControlPanelPosition.left + chatContainerLeft;
            chatTableTop = this.activeChatPanelHeight + 5 + 22 -4;
            operatorForwardListTop = chatContainerTop;
            operatorForwardListLeft = chatContainerLeft;
            thisVisitorInfoVisibility = 'none';
            chatButtonTop = (chatTableHeight - 61/*40 + 10 -55 + 7 - 14 + 31*/);
        }
        //alert(articleWidth);
        if (articleWidth < 420) {
            viewSelectPanelDisplay = 'none';
            viewSelectPanel2Display = 'block'
            var viewSelectPanel2Height = $('#view-select-panel2').height();
            chatContainerTop = viewSelectPanel2Height + 10;
        } else {
            viewSelectPanelDisplay = 'block';
            viewSelectPanel2Display = 'none'
            chatContainerTop = viewSelectPanelHeight + 10;
        }
        operatorListTop = chatContainerTop;
        visitorListTop = chatContainerTop;
        visitorInfoTop = chatWindowTop + chatContainerTop;
        operatorForwardListTop = chatContainerTop;

        // all size definitions
        var background = '#ffffff';
        var borderStyle = '1px solid #ddd';
        var roundedStyle = '4px';
        var shadowStyle = '4px 4px 2px #eee';
        var bodyColor = thisBody.css('background').split(')')[0] + ')';
        if (this.active_chat == '') {
            thisVisitorInfo.html('<div id="visitor-info-headline"><h3>' + t('Visitor information') + '</h3></div>' +
                '<div id="visitor-info-headline2"></div>').trigger('create');
        }

        var chatTableWidth = chatContainerWidth;

        // put together the css objects
        var thisChatContainerCss = {position: 'absolute', width: chatContainerWidth + 'px', height: chatContainerHeight + 'px',
            left: chatContainerLeft + 'px', top: chatContainerTop + 'px', background: background, padding: '5px 5px 5px 5px',
            border: borderStyle, '-moz-border': borderStyle, '-webkit-border': borderStyle,
            //'box-shadow': shadowStyle, '-moz-box-shadow': shadowStyle, '-webkit-box-shadow': shadowStyle,
            'border-radius': roundedStyle, '-moz-border-radius': roundedStyle, '-webkit-border-radius': roundedStyle
        };
        var thisChatContainerHeadlineCss = {position: 'absolute', left: '0px', top: '0px',
            width: chatContainerWidth + 'px', height: '22px',
            'border-top-left-radius': '4px', 'border-top-right-radius': '4px',
            'border-bottom': '1px solid #ddd', background: '#f5f5f5',
            'background-image': this.addBrowserSpecificGradient(''),
            'font-weight': 'bold', 'font-size': '10px', 'line-height': '0px',
            'text-align': 'left', 'padding-left': '10px'};
        var thisTranslationContainerCss = this.lzm_commonTools.clone(thisChatContainerCss);
        thisTranslationContainerCss['overflow-y'] = 'auto';
        this.TranslationContainerHeadlineCss = this.lzm_commonTools.clone(thisChatContainerHeadlineCss);
        this.TranslationContainerHeadline2Css = this.createSecondHeadlineCssFromFirst(this.TranslationContainerHeadlineCss);
        this.TranslationContainerFootlineCss = this.createFootlineCssFromHeadline(this.TranslationContainerHeadlineCss, chatContainerWidth, chatContainerHeight, roundedStyle);
        var thisUsersettingsContainerCss = this.lzm_commonTools.clone(thisChatContainerCss);
        thisUsersettingsContainerCss['overflow-x'] = 'hidden';
        this.UsersettingsContainerHeadlineCss = this.lzm_commonTools.clone(thisChatContainerHeadlineCss);
        this.UsersettingsContainerHeadline2Css = this.createSecondHeadlineCssFromFirst(this.UsersettingsContainerHeadlineCss);
        this.UsersettingsContainerBodyCss = this.lzm_commonTools.clone(thisChatContainerCss);
        this.UsersettingsContainerBodyCss.top = chatContainerTop + 7;
        this.UsersettingsContainerBodyCss.left = chatContainerLeft - 10;
        this.UsersettingsContainerBodyCss.height = chatContainerHeight - 75;
        this.UsersettingsContainerBodyCss['overflow-y'] = 'auto';
        delete this.UsersettingsContainerBodyCss['border'];
        delete this.UsersettingsContainerBodyCss['-moz-border'];
        delete this.UsersettingsContainerBodyCss['-webkit-border'];
        delete this.UsersettingsContainerBodyCss['border-radius'];
        delete this.UsersettingsContainerBodyCss['-moz-border-radius'];
        delete this.UsersettingsContainerBodyCss['-webkit-border-radius'];
        this.UsersettingsContainerFootlineCss = this.createFootlineCssFromHeadline(this.UsersettingsContainerHeadlineCss, chatContainerWidth, chatContainerHeight, roundedStyle, 'small');
        /***********************************************************************************************************************************************************************/
        var thisQrdTreeCss = this.lzm_commonTools.clone(thisChatContainerCss);
        //thisQrdTreeCss['overflow-x'] = 'hidden';
        this.QrdTreeHeadlineCss = this.lzm_commonTools.clone(thisChatContainerHeadlineCss);
        this.QrdTreeHeadline2Css = this.createSecondHeadlineCssFromFirst(this.QrdTreeHeadlineCss);
        this.QrdTreeHeadline2Css['font-size'] = '11px';
        this.QrdTreeHeadline2Css['font-weight'] = 'none';
        this.QrdTreeBodyCss = this.lzm_commonTools.clone(this.UsersettingsContainerBodyCss);
        this.QrdTreeBodyCss['overflow-x'] = 'auto';
        this.QrdTreeFootlineCss = this.createFootlineCssFromHeadline(this.QrdTreeHeadlineCss, chatContainerWidth, chatContainerHeight, roundedStyle, 'small');
        /***********************************************************************************************************************************************************************/
        var thisOperatorListCss = {width: operatorListWidth + 'px', height: chatContainerHeight + 'px', padding: '5px',
            position: 'absolute', display: operatorListDisplay, left: operatorListLeft + 'px', top: operatorListTop, background: background,
            border: borderStyle, '-moz-border': borderStyle, '-webkit-border': borderStyle,
            //'box-shadow': shadowStyle, '-moz-box-shadow': shadowStyle, '-webkit-box-shadow': shadowStyle,
            'border-radius': roundedStyle, '-moz-border-radius': roundedStyle, '-webkit-border-radius': roundedStyle};
        this.OperatorListHeadlineCss = this.lzm_commonTools.clone(thisChatContainerHeadlineCss);
        this.OperatorListHeadlineCss.width = operatorListWidth+'px';
        this.OperatorListHeadline2Css = this.createSecondHeadlineCssFromFirst(this.OperatorListHeadlineCss);
        this.OperatorListBodyCss = {position: 'absolute', top: '48px', width: (operatorListWidth + 5)+'px',
            height: (chatContainerHeight-38)+'px', 'overflow-y': 'auto'};
        var thisChatTableCss = {width: chatTableWidth + 'px', height: chatTableHeight + 'px',
            padding: '5px 5px 5px 5px', position: 'absolute', left: '0px', display: 'block',
            top: chatTableTop + 'px'};
        var thisVisitorListCss = {width: visitorListWidth + 'px', height: visitorListHeight + 'px', padding: '5px 5px 5px 5px',
            position: 'absolute', left: visitorListLeft + 'px', display: visitorListDisplay, top: visitorListTop + 'px',
            border: borderStyle, '-moz-border': borderStyle, '-webkit-border': borderStyle,
            //'box-shadow': shadowStyle, '-moz-box-shadow': shadowStyle, '-webkit-box-shadow': shadowStyle,
            'border-radius': roundedStyle, '-moz-border-radius': roundedStyle, '-webkit-border-radius': roundedStyle,
            'overflow': 'hidden', background: background};
        this.VisitorListHeadlineCss = this.lzm_commonTools.clone(thisChatContainerHeadlineCss);
        this.VisitorListHeadlineCss.width = visitorListWidth+'px';
        this.VisitorListHeadline2Css = this.createSecondHeadlineCssFromFirst(this.VisitorListHeadlineCss);
        var visitorListTableWidth = (this.displayWidth != 'small') ? $('#visitor-list').width() - 20 : $('#visitor-list').width();
        this.visitorListTableCss = {position: 'absolute',
            width: visitorListTableWidth+'px', height: ($('#visitor-list').height() - 48)+'px',
            'top': '48px', 'left': '0px', overflow: 'auto', padding: '5px'};
        var thisOperatorForwardListCss = {width: chatContainerWidth + 'px', height: chatContainerHeight + 'px',
            padding: '5px 5px 5px 5px', position: 'absolute', left: operatorForwardListLeft + 'px',
            border: borderStyle, '-moz-border': borderStyle, '-webkit-border': borderStyle,
            //'box-shadow': shadowStyle, '-moz-box-shadow': shadowStyle, '-webkit-box-shadow': shadowStyle,
            'border-radius': roundedStyle, '-moz-border-radius': roundedStyle, '-webkit-border-radius': roundedStyle,
            top: operatorForwardListTop + 'px', 'overflow': 'hidden', 'z-index': 10, background: background};
            //top: chatTableTop + 'px', 'overflow': 'hidden', 'z-index': 10, background: background};
        thisOperatorForwardListCss.display = this.showOpInviteList ? 'block' : 'none';
        this.OperatorForwardListHeadlineCss = this.lzm_commonTools.clone(thisChatContainerHeadlineCss);
        this.OperatorForwardListHeadline2Css = this.createSecondHeadlineCssFromFirst(this.OperatorForwardListHeadlineCss);
        this.OperatorForwardListFootlineCss = this.createFootlineCssFromHeadline(this.OperatorForwardListHeadlineCss, chatContainerWidth, chatContainerHeight, roundedStyle, 'small');
        var fwdContHeight = thisOperatorForwardList.height() - 39;
        var fwdContWidth = 800;
        var fwdContLeft = (thisOperatorForwardList.width() +12 - fwdContWidth) / 2;
        if (thisOperatorForwardList.width() < 820) {
            fwdContWidth = thisOperatorForwardList.width() -10;
            fwdContLeft = (thisOperatorForwardList.width() - fwdContWidth);
        }
        this.OperatorForwardListBodyCss = {position: 'absolute', top: '49px', height: fwdContHeight+'px',
            'overflow-y': 'hidden', width: (thisOperatorForwardList.width() + 10)+'px',
            left: '0px',
            'border-bottom-left-radius': roundedStyle, '-moz-border-left-radius': roundedStyle, '-webkit-border-left-radius': roundedStyle,
            'border-bottom-right-radius': roundedStyle, '-moz-border-right-radius': roundedStyle, '-webkit-border-right-radius': roundedStyle
        };
        this.fwdContainerCss = {position: 'absolute', top: '20px', width: fwdContWidth+'px',
            left: fwdContLeft+'px'};
        var thisVisitorInfoCss = {padding: '5px 5px 5px 5px', width: visitorInfoWidth + 'px', height: visitorInfoHeight + 'px',
            position: 'absolute', left: visitorInfoLeft + 'px', background: background,
            border: borderStyle, '-moz-border': borderStyle, '-webkit-border': borderStyle,
            //'box-shadow': shadowStyle, '-moz-box-shadow': shadowStyle, '-webkit-box-shadow': shadowStyle,
            'border-radius': roundedStyle, '-moz-border-radius': roundedStyle, '-webkit-border-radius': roundedStyle,
            top: visitorInfoTop + 'px', 'overflow-y': 'auto', display: thisVisitorInfoVisibility};
        this.VisitorInfoHeadlineCss = this.lzm_commonTools.clone(thisChatContainerHeadlineCss);
        this.VisitorInfoHeadlineCss.width = visitorInfoWidth+'px';
        this.VisitorInfoHeadline2Css = this.createSecondHeadlineCssFromFirst(this.VisitorInfoHeadlineCss);
        this.VisitorInfoFootlineCss = this.createFootlineCssFromHeadline(this.VisitorInfoHeadlineCss, visitorInfoWidth, visitorInfoHeight, roundedStyle, 'small');
        this.VisitorInfoBodyCss = this.lzm_commonTools.clone(this.OperatorListBodyCss);
        this.VisitorInfoBodyCss.width = (visitorInfoWidth + 5)+'px';
        this.VisitorInfoBodyCss.height = (chatContainerHeight - 65)+'px';
        this.VisitorInfoBodyCss.top = '49px';
        this.VisitorInfoBodyCss['overflow-y'] = 'scroll';
        var thisChatCss = {width: chatWindowWidth + 'px', height: chatWindowHeight + 'px', padding: '5px 5px 5px 5px',
            position: 'absolute', left: (userControlPanelPosition.left) + 'px',
            top: (chatWindowTop) + 'px'};
        var thisChatTitleCss = {width: (chatTableWidth) + 'px'};
        var thisChatButtonsCss = {width: (chatTableWidth + 10)+'px', height: '25px', background: '#f4f4f4',
            'position': 'absolute', 'top': chatButtonTop+'px', left: '0px', 'padding-bottom': '5px',
            'text-align': 'left'};
        var thisChatInputControlsCss = {position: 'absolute', width: (chatTableWidth + 10) + 'px', height: '0px',
            border: '0px', '-moz-border': '0px', '-webkit-border': '0px',
            left: '0px', top: '0px', 'text-align': 'left', 'font-size': '12px',
            margin: '14px 5px'};
        var thisChatInputBodyCss = {position: 'absolute', width: (chatTableWidth + 10) + 'px', height: '50px',
            border: '0px', '-moz-border': '0px', '-webkit-border': '0px',
            'border-bottom-left-radius': '4px', 'border-bottom-right-radius': '4px',
            left: '0px', top: '0px', 'text-align': 'left', 'font-size': '12px',
            'overflow-y': 'hidden'};
        var thisChatActionCss = {width: (chatTableWidth + 10)+'px', height: '50px', 'line-height': '0px', 'overflow': 'hidden',
            'border-bottom-left-radius': '4px', 'border-bottom-right-radius': '4px',
            'top': (chatButtonTop + 30)+'px', left: '0px',
            position: 'absolute', 'background-color': '#ffffff'};
        var thisChatProgressCss = {position: 'absolute', left: '0px', top: '0px', width: (chatTableWidth + 10)+'px',
            height: (chatTableHeight - thisChatAction.height() - thisChatTitle.height() - thisChatButtons.height() + 14) + 'px',
            'overflow-y': 'scroll', 'overflow-x': 'hidden'};
        var thisChatLogoCss = {width: chatTableWidth, height: chatTableHeight, 'overflow': 'hidden'};
        var thisLogoPageCss = {width: chatTableWidth, height: chatTableHeight, border: '0px', background: '#ffffff',
            'border-radius': roundedStyle, '-moz-border-radius': roundedStyle, '-webkit-border-radius': roundedStyle};
        var thisViewSelectPanelCss = {display: viewSelectPanelDisplay, 'margin-left': '10px'};
        var thisViewSelectPanel2Css = {display: viewSelectPanel2Display, 'margin-left': '10px'};
        var thisActiveChatPanelCss = {position: 'absolute', left: '0px', top: (5 + 18)+'px', height: this.activeChatPanelHeight+'px',
            width: (chatTableWidth + 10) + 'px', 'text-align': 'left', background: '#ededed',
            margin: '0px'};

        // actually apply the css objects
        //thisChatPage.css({'background-image': this.addBrowserSpecificGradient('', 'background')});
        //thisBody.css({'background-image': this.addBrowserSpecificGradient('', 'background')});
        thisBody.css({background: '#e0e0e0'});
        thisChatContainer.css(thisChatContainerCss);
        $('#chat-container-headline').html('<h3>' + t('Chats') + '</h3>').css(thisChatContainerHeadlineCss);
        $('#translation-container').css(thisTranslationContainerCss);
        $('#translation-container-headline').css(this.TranslationContainerHeadlineCss);
        $('#translation-container-headline2').css(this.TranslationContainerHeadline2Css);
        $('#usersettings-container').css(thisUsersettingsContainerCss);
        $('#usersettings-container-headline').css(this.UsersettingsContainerHeadlineCss);
        $('#usersettings-container-headline2').css(this.UsersettingsContainerHeadline2Css);
        $('#usersettings-container-body').css(this.UsersettingsContainerBodyCss);
        $('#usersettings-container-footline').css(this.UsersettingsContainerFootlineCss);
        thisQrdTree.css(thisQrdTreeCss);
        $('#qrd-tree-headline').css(this.QrdTreeHeadlineCss);
        $('#qrd-tree-headline2').css(this.QrdTreeHeadline2Css);
        $('#qrd-tree-body').css(this.QrdTreeBodyCss);
        $('#qrd-tree-footline').css(this.QrdTreeFootlineCss);
        thisChatInputControls.css(thisChatInputControlsCss);
        thisChatInputBody.css(thisChatInputBodyCss);
        thisChatAction.css(thisChatActionCss);
        thisChatTitle.css(thisChatTitleCss);
        thisChatButtons.css(thisChatButtonsCss);
        thisChatProgress.css(thisChatProgressCss);
        thisOperatorList.css(thisOperatorListCss);
        $('#operator-list-headline').css(this.OperatorListHeadlineCss);
        $('#operator-list-headline2').css(this.OperatorListHeadline2Css);
        $('#operator-list-body').css(this.OperatorListBodyCss);
        thisChatTable.css(thisChatTableCss);
        thisOperatorForwardList.css(thisOperatorForwardListCss);
        $('#operator-forward-list-headline').css(this.OperatorForwardListHeadlineCss);
        $('#operator-forward-list-headline2').css(this.OperatorForwardListHeadline2Css);
        thisActiveChatPanel.css(thisActiveChatPanelCss);
        thisViewSelectPanel.css(thisViewSelectPanelCss);
        thisViewSelectPanel2.css(thisViewSelectPanel2Css);
        $('#radio-internal-text').css({width: Math.floor((chatContainerWidth + 10) / 4)+'px'});
        $('#radio-external-text').css({width: Math.floor((chatContainerWidth + 10) / 4)+'px'});
        $('#radio-qrd-text').css({width: Math.floor((chatContainerWidth + 10) / 4)+'px'});
        $('#radio-mychats-text').css({width: chatContainerWidth + 4 - 3 * Math.floor((chatContainerWidth + 10) / 4)+'px'});
        $('#radio-left-text').css({width: '48px',
            'background-image': this.addBrowserSpecificGradient('', 'darkViewSelect'),
            'border': '1px solid #666'});
        $('#radio-left-text span.ui-icon').css({'background-image': 'url(\'js/jquery_mobile/images/icons-18-white.png\')',
            'background-position': '-144px -1px', 'background-repeat': 'no-repeat', 'background-color': 'rgba(0,0,0,.4)',
            'border-radius': '9px', 'width': '18px', 'height': '18px', 'display': 'block', 'left': '12px'});
        $('#radio-this-text').css({width: (chatContainerWidth + 7 - 96)+'px',
            'background-image': this.addBrowserSpecificGradient('', 'darkViewSelect'),
            'border': '1px solid #666'});
        $('#radio-this-text span.ui-btn-text').css({'text-shadow': '0 1px 0 #777','color': '#ffffff'});
        $('#radio-right-text').css({width: '48px',
            'background-image': this.addBrowserSpecificGradient('', 'darkViewSelect'),
            'border': '1px solid #666'});
        $('#radio-right-text span.ui-icon').css({'background-image': 'url(\'js/jquery_mobile/images/icons-18-white.png\')',
            'background-position': '-108px -1px', 'background-repeat': 'no-repeat', 'background-color': 'rgba(0,0,0,.4)',
            'border-radius': '9px', 'width': '18px', 'height': '18px', 'display': 'block', 'left': '18px'});
        thisVisitorList.css(thisVisitorListCss);
        $('#visitor-list-headline').css(this.VisitorListHeadlineCss);
        $('#visitor-list-headline2').css(this.VisitorListHeadline2Css);
        $('#visitor-list-table-div').css(this.visitorListTableCss);
        thisVisitorInfo.css(thisVisitorInfoCss);
        $('#visitor-info-headline').css(this.VisitorInfoHeadlineCss);
        $('#visitor-info-headline2').css(this.VisitorInfoHeadline2Css);
        $('#visitor-info-body').css(this.VisitorInfoBodyCss);
        $('#visitor-info-footline').css(this.VisitorInfoFootlineCss);
        thisChat.css(thisChatCss);

        if (typeof thisChatLogo != 'undefined') {
            thisChatLogo.css(thisChatLogoCss);
            $('#logo-page').css(thisLogoPageCss);
        }

        $('#debugging-messages').css({
            position: 'fixed',
            top: Math.floor(0.3 * $(window).height())+'px',
            left: Math.floor(0.3 * $(window).width())+'px',
            width: Math.floor(0.4 * $(window).width())+'px',
            height: Math.floor(0.4 * $(window).height())+'px',
            padding: '10px',
            'background-color': '#ffffc6',
            opacity: '0.9',
            display: this.debuggingDisplayMode,
            'z-index': 1000
        });
    }

    var thisShowVisitorInfo = $('#show-visitor-info');
    var thisAcceptChat = $('#accept-chat');
    var thisLeaveChat = $('#leave-chat');
    var thisDeclineChat = $('#decline-chat');
    var thisForwardChat = $('#forward-chat');
    if (typeof thisShowVisitorInfo != 'undefined' && typeof thisAcceptChat != 'undefined' &&
        typeof thisDeclineChat != 'undefined' && typeof thisLeaveChat != 'undefined' &&
        typeof thisForwardChat != 'undefined' && typeof thisSendBtn != 'undefined') {
        if (thisShowVisitorInfo.width() + thisAcceptChat.width() + thisDeclineChat.width() +
            thisLeaveChat.width() + thisForwardChat.width() + thisSendBtn.width() +
            thisBlankChatBtn.width() > chatTableWidth) {
            thisBlankChatBtn.css('width', '0px');
            thisChatTitle.trigger('create');
        }
    }

    this.toggleVisibility();
};

ChatDisplayClass.prototype.createInputControlPanel = function() {
    var panelHtml = '<span id="editor-bold-btn" class="chat-button-line chat-button-left chat-button-right" ' +
        'style="margin-left: 4px; padding-left: 10px; padding-right: 10px; cursor:pointer; background-image: ' +
        this.addBrowserSpecificGradient('') + ';" onclick="lzm_chatInputEditor.bold();"><b>B</b></span>' +
        '<span id="editor-italic-btn" class="chat-button-line chat-button-left chat-button-right" ' +
        'style="margin-left: 4px; padding-left: 10px; padding-right: 10px; cursor:pointer; background-image: ' +
        this.addBrowserSpecificGradient('') + ';" onclick="lzm_chatInputEditor.italic();"><i>I</i></span>' +
        '<span id="editor-underline-btn" class="chat-button-line chat-button-left chat-button-right" ' +
        'style="margin-left: 4px; padding-left: 10px; padding-right: 10px; cursor:pointer; background-image: ' +
        this.addBrowserSpecificGradient('') + ';" onclick="lzm_chatInputEditor.underline();"><u>U</u></span>' +
        '<span class="chat-button-line chat-button-left chat-button-right" id="add-qrd" title="' + t('Resources') + '" ' +
        'style=\'padding-left: 10px; padding-right: 10px; cursor:pointer; background-image: ' +
        this.addBrowserSpecificGradient('url("img/607-cardfile.png")') + '; ' +
        'background-position: center; background-repeat: no-repeat;\'>&nbsp;&nbsp;</span>'/* +
        '<span class="chat-button-line chat-button-left chat-button-right" id="force-resize-now" ' +
        'style="padding-left: 10px; padding-right: 10px; cursor:pointer; background-image: ' +
        this.addBrowserSpecificGradient('') + ';" onclick="forceResizeNow();">Now</span>'*/;
    return panelHtml;
};

ChatDisplayClass.prototype.changeViewSelectButtonDesign = function(activeView) {
    var views = ['mychats', 'internal', 'external', 'qrd'];
    for (var i=0; i<views.length; i++) {
        if (views[i] != activeView) {
            if (views[i] != 'mychats' || !this.chatActivity || (!this.settingsDialogue && this.selected_view == 'mychats')) {
                $('#radio-' + views[i] + '-text').css({
                    'background-image': this.addBrowserSpecificGradient('', 'darkViewSelect'),
                    'border': '1px solid #666'
                });
                $('#radio-' + views[i] + '-text').find('span').find('.ui-btn-text').css({
                    'text-shadow': '0 1px 0 #666',
                    'color': '#ffffff'
                });
            }
        } else {
            $('#radio-' + views[i] + '-text').css({
                'background-image': this.addBrowserSpecificGradient('', 'selectedViewSelect'),
                'border': '1px solid #2373a5'
            });
            $('#radio-' + views[i] + '-text').find('span').find('.ui-btn-text').css({
                'text-shadow': '0 1px 0 #2373a5',
                'color': '#ffffff'
            });
        }
    }
};

/**
 * Toggle the visibility of the chat, operator list and visitor list and info depending on selected view
 */
ChatDisplayClass.prototype.toggleVisibility = function () {
    var thisOperatorList = $('#operator-list');
    var thisChat = $('#chat');
    var thisErrors = $('#errors');
    var thisOperatorForwardList = $('#operator-forward-list');
    var thisChatTable = $('#chat-table');
    var thisActiveChatPanel = $('#active-chat-panel');
    var thisVisitorList = $('#visitor-list');
    var thisVisitorInfo = $('#visitor-info');
    var thisQrdTree = $('#qrd-tree');
    var thisRadioSelectedView = $('#radio-' + this.selected_view);

    $('.view-select').each(function () {
        $(this).prop('checked', false);
        $(this).checkboxradio("refresh");
    });
    thisRadioSelectedView.prop('checked', true);
    thisRadioSelectedView.checkboxradio("refresh");

    if (this.displayWidth == 'small') {
        this.changeViewSelectButtonDesign(this.selected_view);
        switch (this.selected_view) {
            case 'mychats':
                $('#chat-container-headline').html('<h3>' + t('Chats') + '</h3>');
                $('#radio-this-text span.ui-btn-text').text(t('Chats'));
                thisOperatorList.css('display', 'none');
                thisChat.css('display', 'block');
                if (this.ShowVisitorInfo) {
                    thisVisitorInfo.css('display', 'block');
                } else {
                    thisChatTable.css('display', 'block');
                }
                if (this.thisUser.id == '') {
                    $('#chat-logo').css('display', 'none');
                    $('#chat-progress').css('display', 'none');
                    $('#chat-action').css('display', 'none');
                    $('#chat-title').css('display', 'none');
                    $('#chat-buttons').css('display', 'none');
                } else {
                    $('#chat-logo').css('display', 'none');
                    $('#chat-progress').css('display', 'block');
                    $('#chat-action').css('display', 'block');
                    $('#chat-title').css('display', 'block');
                    $('#chat-buttons').css('display', 'block');
                }
                this.VisitorListCreated = false;
                $('#visitor-list-table').remove();
                //console.log('Visitor list removed');
                thisErrors.css('display', 'none');
                if (!this.showOpInviteList) {
                    thisOperatorForwardList.css('display', 'none');
                }
                thisActiveChatPanel.css('display', 'block');
                thisVisitorList.css('display', 'none');
                thisQrdTree.css('display', 'none');
                if (this.thisUser.id == '')
                    this.switchCenterPage('home');
                break;
            case 'internal':
                $('#radio-this-text span.ui-btn-text').text(t('Operators'));
                thisOperatorList.css('display', 'block');
                thisChat.css('display', 'block');
                thisChatTable.css('display', 'none');
                thisErrors.css('display', 'none');
                this.VisitorListCreated = false;
                $('#visitor-list-table').remove();
                //console.log('Visitor list removed');
                if (!this.showOpInviteList) {
                    thisOperatorForwardList.css('display', 'none');
                }
                thisActiveChatPanel.css('display', 'none');
                thisVisitorList.css('display', 'none');
                thisQrdTree.css('display', 'none');
                break;
            case 'external':
                $('#radio-this-text span.ui-btn-text').text(t('Visitors'));
                thisOperatorList.css('display', 'none');
                thisChat.css('display', 'block');
                thisChatTable.css('display', 'none');
                thisErrors.css('display', 'none');
                if (!this.showOpInviteList) {
                    thisOperatorForwardList.css('display', 'none');
                }
                thisActiveChatPanel.css('display', 'none');
                thisVisitorList.css('display', 'block');
                thisQrdTree.css('display', 'none');
                break;
            case 'qrd':
                $('#radio-this-text span.ui-btn-text').text(t('Resources'));
                thisOperatorList.css('display', 'none');
                thisChat.css('display', 'block');
                thisChatTable.css('display', 'none');
                thisErrors.css('display', 'none');
                if (!this.showOpInviteList) {
                    thisOperatorForwardList.css('display', 'none');
                }
                thisActiveChatPanel.css('display', 'none');
                thisVisitorList.css('display', 'none');
                thisQrdTree.css('display', 'block');
                break;
            /*case 'error':
                thisOperatorList.css('display', 'none');
                thisChat.css('display', 'none');
                thisChatTable.css('display', 'none');
                thisErrors.css('display', 'block');
                if (!this.showOpInviteList) {
                    thisOperatorForwardList.css('display', 'none');
                }
                thisActiveChatPanel.css('display', 'none');
                thisVisitorList.css('display', 'none');
                thisQrdTree.css('display', 'none');
                break;
            case 'foo':
                thisOperatorList.css('display', 'none');
                thisChat.css('display', 'none');
                thisChatTable.css('display', 'none');
                thisErrors.html('<h1>FOOOOOOOO</h1>');
                thisErrors.css('display', 'block');
                if (!this.showOpInviteList) {
                    thisOperatorForwardList.css('display', 'none');
                }
                thisActiveChatPanel.css('display', 'none');
                thisVisitorList.css('display', 'none');
                thisQrdTree.css('display', 'none');
                break;
            case 'startpage':
                $('#chat-container-headline').html('<h3>' + t('Start page') + '</h3>');
                thisOperatorList.css('display', 'none');
                thisChat.css('display', 'block');
                thisChatTable.css('display', 'block');
                $('#chat-logo').css('display', 'block');
                $('#chat-progress').css('display', 'none');
                $('#chat-action').css('display', 'none');
                $('#chat-title').css('display', 'none');
                $('#chat-buttons').css('display', 'none');
                thisErrors.css('display', 'none');
                if (!this.showOpInviteList) {
                    thisOperatorForwardList.css('display', 'none');
                }
                thisActiveChatPanel.css('display', 'none');
                thisVisitorList.css('display', 'none');
                thisQrdTree.css('display', 'none');
                break;*/
        }
    }
};

/**
 * Logout on a validation error alerting the user.
 * @param validationError
 */
ChatDisplayClass.prototype.logoutOnValidationError = function (validationError, isWeb, isApp, chosenProfile) {
    if (this.validationErrorCount > 0) {
        this.validationErrorCount = 0;
        this.askBeforeUnload = false;
        var noLogout = false;
        switch (validationError) {
            case '0':
                alert(t('Wrong username or password!'));
                break;
            case '2':
                /*if (confirm(t('The operator <!--op_login_name--> is already logged in!',[['<!--op_login_name-->', this.myLoginId]]) + '\n' +
                    t('Do you want to log off the other instance?'))) {
                    noLogout = true;
                    tryNewLogin(true);
                }*/
                alert(t('The operator <!--op_login_name--> is already logged in!',[['<!--op_login_name-->', this.myLoginId]]));
                break;
            case '3':
                alert(t("You've been logged off by another operator!"));
                break;
            case "4":
                alert(t('Session timed out!'));
                break;
            case "5":
                alert(t('You have to change your password!'));
                break;
            case "9":
                alert(t('You are not an administrator!'));
                break;
            case "10":
                alert(t('This LiveZilla server has been deactivated by the administrator.') + '\n' +
                    t('If you are the administrator, please activate this server under LiveZilla Server Admin -> Server Configuration -> Server.'));
                break;
            case "13":
                alert(t('There are problems with the database connection!'));
                break;
            case "14":
                alert(t('This server requires secure connection (SSL). Please activate HTTPS in the server profile and try again.'));
                break;
            case "15":
                alert(t('Your account has been deactivated by an administrator.'));
            default:
                alert('Validation Error : ' + validationError);
                break;
        }
        if (!noLogout) {
            if (isWeb == 1) {
                window.location.href = 'index.php';
            } else if (isApp == 1) {
                window.location.href = 'logout.html?' +
                    'user_volume_' + chosenProfile.index + '=' + chosenProfile.user_volume +
                    '&user_away_after_' + chosenProfile.index + '=' + chosenProfile.user_away_after +
                    '&play_incoming_message_sound_' + chosenProfile.index + '=' + chosenProfile.play_incoming_message_sound +
                    '&play_incoming_chat_sound_' + chosenProfile.index + '=' + chosenProfile.play_incoming_chat_sound +
                    '&repeat_incoming_chat_sound_' + chosenProfile.index + '=' + chosenProfile.repeat_incoming_chat_sound +
                    '&language_' + chosenProfile.index + '=' + chosenProfile.language;
            }
        }
    } else {
        this.validationErrorCount++;
        tryNewLogin();
    }
};

/**
 * Create the html code for the debugging error view
 */
ChatDisplayClass.prototype.createErrorHtml = function (global_errors) {
    var errorHtmlString = '';
    for (var errorIndex = 0; errorIndex < global_errors.length; errorIndex++) {
        errorHtmlString += '<p>' + global_errors[errorIndex] + '</p>';
    }
    $('#errors').html(errorHtmlString);
};

ChatDisplayClass.prototype.setExternalUserList = function(type, list) {
    if (type == 'changed') {
        this.changedExternalUsers = list;
    } else if (type == 'new') {
        this.newExternalUsers = list;
    }
};

ChatDisplayClass.prototype.getExternalUserList = function(type) {
    var list;
    if (type == 'changed') {
        list = this.changedExternalUsers;
    } else if (type == 'new') {
        list = this.newExternalUsers;
    }
    return list;
};

/**
 * Update the visitor list
 * @param external_users
 * @param chatObject
 * @param internal_users
 */
ChatDisplayClass.prototype.updateVisitorList = function (external_users, chatObject, internal_users) {
    if (!this.VisitorListCreated) {
        this.createVisitorList(external_users, chatObject, internal_users);
    } else {
        var thisVisitorList = $('#visitor-list');
        var visitorListWidth = thisVisitorList.width();
        external_users.sort(this.visitorSortFunction);
        var i = 0, lineCounter = 0, lineStyle;
        for (i=external_users.length-1; i>=0; i--) {
            var existingLine = $('#visitor-list-row-' + external_users[i].id).html();
            var lineIsExisting = (typeof existingLine != 'undefined') ? true : false;
            var htmlString, thisLine, cssObject;
            if (external_users[i].is_active && lineIsExisting && $.inArray(external_users[i].id, this.changedExternalUsers) != -1) {
                //console.log(this.changedExternalUsers);
                thisLine = this.createVisitorListLine(external_users[i], chatObject, internal_users, visitorListWidth, false);
                htmlString = thisLine[0];
                cssObject = thisLine[1];
                if (existingLine != htmlString) {
                    $('#visitor-list-row-' + external_users[i].id).html(htmlString).css(cssObject);
                }
            } else if (external_users[i].is_active && !lineIsExisting) {
                htmlString = this.createVisitorListLine(external_users[i], chatObject, internal_users, visitorListWidth, true)[0];
                $('#visitor-list-body').prepend(htmlString);
            } else if (!external_users[i].is_active && lineIsExisting) {
                $('#visitor-list-row-' + external_users[i].id).remove();
            }
        }
        for (i=0; i<external_users.length; i++) {
            if (external_users[i].is_active) {
                if (lineCounter % 2 == 0) {
                    lineStyle = {'background-color': '#f8f8ff'};
                } else {
                    lineStyle = {'background-color': '#ffffff'};
                }
                if (typeof chatObject[external_users[i].id + '~' + external_users[i].b_id] == 'undefined' ||
                    chatObject[external_users[i].id + '~' + external_users[i].b_id].status != 'new') {
                    $('#visitor-list-row-' + external_users[i].id).css(lineStyle);
                }
                lineCounter++;
            }
        }
        this.newExternalUsers = [];
        this.changedExternalUsers = [];

        thisVisitorList.trigger('create');
    }
};

ChatDisplayClass.prototype.createVisitorStrings = function(type, aUser) {
    var visitorStringList = [];
    if (type.indexOf('.') != -1) {
        type = type.split('.');
    } else {
        type = [type];
    }
    if (aUser.b.length > 0) {
        for (var i=0; i<aUser.b.length; i++) {
            if (type.length == 1) {
                if (typeof aUser.b[i][type[0]] != 'undefined' && aUser.b[i][type[0]] != '' &&
                    $.inArray(aUser.b[i][type[0]], visitorStringList) == -1) {
                    visitorStringList.push(aUser.b[i][type[0]]);
                }
            } else {
                if (typeof aUser.b[i][type[0]][type[1]] != 'undefined' && aUser.b[i][type[0]][type[1]] != '' &&
                    $.inArray(aUser.b[i][type[0]][type[1]], visitorStringList) == -1) {
                    visitorStringList.push(aUser.b[i][type[0]][type[1]]);
                }
            }
        }
    }
    return visitorStringList.join(', ');
};

ChatDisplayClass.prototype.createVisitorListLine = function(aUser, chatObject, internal_users, visitorListWidth, newLine) {
    var extUserHtmlString = ''
    var thisUserName = aUser.id;
    if (aUser.b_cname != '') {
        thisUserName = aUser.b_cname + ' --- ' + aUser.id;
    }
    var userStyle, userStyleObject;
    if (this.isApp == 1) {
        userStyle = ' style="line-height: 22px !important;"';
        userStyleObject = {'font-weight': 'normal', 'line-height': '22px !important'};
    } else {
        userStyle = '';
        userStyleObject = {'font-weight': 'normal'};
    }
    var tableRowTitle = '';
    var freeToChat = true;
    if (typeof chatObject[aUser.id + '~' + aUser.b_id] != 'undefined' &&
        chatObject[aUser.id + '~' + aUser.b_id]['status'] == 'new') {
        //console.log(chatObject[aUser.id + '~' + aUser.b_id]);
        if (this.isApp == 1) {
            userStyle = ' style="line-height: 22px !important;font-weight:bold; background:#FFCC73;"';
            userStyleObject = {'font-weight': 'bold', 'background': '#FFCC73', 'line-height': '22px !important'};
        } else {
            userStyle = ' style="font-weight:bold; background:#FFCC73;"';
            userStyleObject = {'font-weight': 'bold', 'background': '#FFCC73'};
        }
    }
    var onclickAction = '';
    /*var onclickAction = ' onclick="viewUserData(\'' + aUser.id + '\',\'' + aUser.b_id +
     '\',\'' + aUser.b_chat.id + '\', ' + freeToChat + ')"';*/

    var visitorName = (this.createVisitorStrings('cname', aUser).length > 32) ?
        this.createVisitorStrings('cname', aUser).substring(0, 32) + '...' : this.createVisitorStrings('cname', aUser);
    var visitorEmail = (this.createVisitorStrings('cemail', aUser).length > 32) ?
        this.createVisitorStrings('cemail', aUser).substring(0, 32) + '...' : this.createVisitorStrings('cemail', aUser);
    var visitorCity = (aUser.city.length > 32) ? aUser.city.substring(0, 32) + '...' : aUser.city;
    var visitorRegion = (aUser.region.length > 32) ? aUser.region.substring(0, 32) + '...' : aUser.region;
    var visitorISP = (aUser.isp.length > 32) ? aUser.isp.substring(0, 32) + '...' : aUser.isp;
    var visitorCompany = (this.createVisitorStrings('ccompany', aUser).length > 32) ?
        this.createVisitorStrings('ccompany', aUser).substring(0, 32) + '...' : this.createVisitorStrings('ccompany', aUser);
    var visitorSystem = (aUser.sys.length > 32) ? aUser.sys.substring(0, 32) + '...' : aUser.sys;
    var visitorBrowser = (aUser.bro.length > 32) ? aUser.bro.substring(0, 32) + '...' : aUser.bro;
    var visitorResolution = (aUser.res.length > 32) ? aUser.res.substring(0, 32) + '...' : aUser.res;
    var visitorChatId = (this.createVisitorStrings('chat.id', aUser).length > 32) ?
        this.createVisitorStrings('chat.id', aUser).substring(0, 32) + '...' : this.createVisitorStrings('chat.id', aUser);
    var visitorHost = (aUser.ho.length > 32) ? aUser.ho.substring(0,32) + '...' : aUser.ho;
    var lastVisitedDate = new Date(aUser.vl * 1000);
    var visitorLastVisited = this.lzm_commonTools.getHumanDate(lastVisitedDate, 'full', this.userLanguage);
    var visitorVisitCount = aUser.vts;
    var visitorSearchStrings = (this.createVisitorStrings('ss', aUser).length > 32) ?
        this.createVisitorStrings('ss', aUser).substring(0, 32) + '...' : this.createVisitorStrings('ss', aUser);


    var visitorOnlineSince = this.calculateTimeDifferenece(aUser, 'lastOnline', false);
    var visitorLastActivity = this.calculateTimeDifferenece(aUser, 'lastActive', false);

    var chatQuestion = '';
    if (typeof aUser.b_chat.eq != 'undefined') {
        chatQuestion = aUser.b_chat.eq.substr(0, 32);
        if (aUser.b_chat.eq.length > 32) {
            chatQuestion += '...';
        }
    }

    if (newLine) {
        extUserHtmlString += '<tr' + userStyle + tableRowTitle + ' id="visitor-list-row-' + aUser.id + '">';
    }

    var numberOfActiveInstances = 0;
    var activeInstanceNumber = 0;
    for (var j = 0; j < aUser.b.length; j++) {
        if (aUser.b[j].is_active && aUser.b[j].h2.length > 0) {
            numberOfActiveInstances++;
            activeInstanceNumber = j;
        }
    }
    var browserInfoString = t('<!--number--> browser opened',[['<!--number-->',numberOfActiveInstances]]);
    if (numberOfActiveInstances == 1) {
        browserInfoString = aUser.b[activeInstanceNumber].h2[aUser.b[activeInstanceNumber].h2.length - 1].url;
    }
    extUserHtmlString += '<td style="background-image: url(\'./php/common/flag.php?cc=' + aUser.ctryi2 + '\'); ' +
        'background-position: center; background-repeat: no-repeat;"></td>';
    extUserHtmlString += '<td style="cursor:pointer; background-image: url(\'./img/215-info.png\');' +
        ' background-position: center; background-repeat: no-repeat;" title="' + t('Show information') + '"' +
        ' onclick="showThisInfoButtonPressed(\'' + aUser.id + '\'); toggleVisitorInfo(\'#visitor-list\',\'' + aUser.id + '\');"' +
        ' title="' + t('Show information') + '" id="info-button-' + aUser.id + '">' +
        '&nbsp;</td>';
    extUserHtmlString += '<td' + onclickAction + '>' + visitorOnlineSince.replace(/-/g,'&#8209;').replace(/ /g,'&nbsp;') + '</td>';
    extUserHtmlString += '<td' + onclickAction + '>' + visitorLastActivity.replace(/-/g,'&#8209;').replace(/ /g,'&nbsp;') + '</td>';
    extUserHtmlString += '<td' + onclickAction + '>' + visitorName.replace(/-/g,'&#8209;').replace(/ /g,'&nbsp;') + '</td>';
    extUserHtmlString += '<td class="userlist external-user-' + aUser.id + '">' + aUser.ctryi2.replace(/-/g,'&#8209;').replace(/ /g,'&nbsp;') + '</td>';
    extUserHtmlString += '<td' + onclickAction + '>' + aUser.lang.replace(/-/g,'&#8209;').replace(/ /g,'&nbsp;') + '</td>';
    extUserHtmlString += '<td' + onclickAction + '>' + visitorRegion.replace(/-/g,'&#8209;').replace(/ /g,'&nbsp;') + '</td>';
    extUserHtmlString += '<td' + onclickAction + '>' + visitorCity.replace(/-/g,'&#8209;').replace(/ /g,'&nbsp;') + '</td>';
    extUserHtmlString += '<td' + onclickAction + '>' + visitorSearchStrings.replace(/-/g,'&#8209;').replace(/ /g,'&nbsp;') + '</td>';
    extUserHtmlString += '<td' + onclickAction + '>' + visitorHost.replace(/-/g,'&#8209;').replace(/ /g,'&nbsp;') + '</td>';
    extUserHtmlString += '<td' + onclickAction + '>' + aUser.ip.replace(/-/g,'&#8209;').replace(/ /g,'&nbsp;') + '</td>';
    extUserHtmlString += '<td' + onclickAction + '>' + visitorEmail.replace(/-/g,'&#8209;').replace(/ /g,'&nbsp;') + '</td>';
    extUserHtmlString += '<td' + onclickAction + '>' + visitorCompany.replace(/-/g,'&#8209;').replace(/ /g,'&nbsp;') + '</td>';
    extUserHtmlString += '<td' + onclickAction + '>' + visitorBrowser.replace(/-/g,'&#8209;').replace(/ /g,'&nbsp;') + '</td>';
    extUserHtmlString += '<td' + onclickAction + '>' + visitorResolution.replace(/-/g,'&#8209;').replace(/ /g,'&nbsp;') + '</td>';
    extUserHtmlString += '<td' + onclickAction + '>' + visitorSystem.replace(/-/g,'&#8209;').replace(/ /g,'&nbsp;') + '</td>';
    extUserHtmlString += '<td' + onclickAction + '>' + visitorLastVisited.replace(/-/g,'&#8209;').replace(/ /g,'&nbsp;') + '</td>';
    extUserHtmlString += '<td' + onclickAction + '>' + visitorISP.replace(/-/g,'&#8209;').replace(/ /g,'&nbsp;') + '</td>';

    if (newLine) {
        extUserHtmlString += '</tr>';
    }

    return [extUserHtmlString, userStyleObject];
};

/**
 * Create the visitor list
 */
ChatDisplayClass.prototype.createVisitorList = function (external_users, chatObject, internal_users) {
    //console.log('Create visitor list');
    this.VisitorListCreated = true;
    var thisVisitorList = $('#visitor-list');
    var visitorListWidth = thisVisitorList.width();
    external_users.sort(this.visitorSortFunction);
    var resizeVisitorDivCss = {width: '30px', height: '18px', 'background-color': '#ddd',
        'background-image': 'url("img/408-up_down.png")', 'background-repeat': 'no-repeat', 'background-position': 'center',
        position: 'absolute', cursor: 'move',
        top: '2px', left: ($('#visitor-list-headline').width()/2 - 15)+'px',
        'border-radius': '8px', '-moz-border-radius': '8px', '-webkit-border-radius': '8px'};
    var resizeVisitorAlternativeDivCss = {position: 'absolute', top: ($('#visitor-list-headline').height() + 1)+'px',
        left: ($('#visitor-list').width() - 20 + 10)+'px', width: '20px',
        height: ($('#visitor-list').height() - $('#visitor-list-headline').height() + 10 - 1)+'px',
        'border-bottom-right-radius': '4px',
        'background-color': '#f5f5f5'};
    resizeVisitorDivCss.display = 'none';
    resizeVisitorAlternativeDivCss.display = 'none';

    var extUserHtmlString = '<div id="visitor-list-headline"><h3>' + t('Visitors') + '</h3>' +
        '<div title="' + t('Change size') + '" id="resize-visitor-list" draggable="true" ondragend="testDrag();"></div>' +
        '</div><div id="visitor-list-headline2"></div><div id="visitor-list-table-div">' +
        '<table id="visitor-list-table" class="visitor-list-table" style="width: 100%;"><thead><tr>';
    extUserHtmlString += '<th>&nbsp;&nbsp;&nbsp;</th>';
    if (this.isApp == 0) {
        extUserHtmlString += '<th style="width: 18px;">&nbsp;&nbsp;&nbsp;</th>';
    } else {
        extUserHtmlString += '<th style="width: 24px !important;" id="debugging-info-th">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>';
    }
    extUserHtmlString += '<th>' + t('Online').replace(/-/g,'&#8209;').replace(/ /g,'&nbsp;') + '</th>';
    extUserHtmlString += '<th>' + t('Last Activity').replace(/-/g,'&#8209;').replace(/ /g,'&nbsp;') + '</th>';
    extUserHtmlString += '<th>' + t('Name').replace(/-/g,'&#8209;').replace(/ /g,'&nbsp;') + '</th>';
    extUserHtmlString += '<th>' + t('Country').replace(/-/g,'&#8209;').replace(/ /g,'&nbsp;') + '</th>';
    extUserHtmlString += '<th>' + t('Language').replace(/-/g,'&#8209;').replace(/ /g,'&nbsp;') + '</th>';
    extUserHtmlString += '<th>' + t('Region').replace(/-/g,'&#8209;').replace(/ /g,'&nbsp;') + '</th>';
    extUserHtmlString += '<th>' + t('City').replace(/-/g,'&#8209;').replace(/ /g,'&nbsp;') + '</th>';
    extUserHtmlString += '<th>' + t('Search string').replace(/-/g,'&#8209;').replace(/ /g,'&nbsp;') + '</th>';
    extUserHtmlString += '<th>' + t('Host').replace(/-/g,'&#8209;').replace(/ /g,'&nbsp;') + '</th>';
    extUserHtmlString += '<th>' + t('IP address').replace(/-/g,'&#8209;').replace(/ /g,'&nbsp;') + '</th>';
    extUserHtmlString += '<th>' + t('Email').replace(/-/g,'&#8209;').replace(/ /g,'&nbsp;') + '</th>';
    extUserHtmlString += '<th>' + t('Company').replace(/-/g,'&#8209;').replace(/ /g,'&nbsp;') + '</th>';
    extUserHtmlString += '<th>' + t('Browser').replace(/-/g,'&#8209;').replace(/ /g,'&nbsp;') + '</th>';
    extUserHtmlString += '<th>' + t('Resolution').replace(/-/g,'&#8209;').replace(/ /g,'&nbsp;') + '</th>';
    extUserHtmlString += '<th>' + t('Operating system').replace(/-/g,'&#8209;').replace(/ /g,'&nbsp;') + '</th>';
    extUserHtmlString += '<th>' + t('Last visit').replace(/-/g,'&#8209;').replace(/ /g,'&nbsp;') + '</th>';
    extUserHtmlString += '<th>' + t('ISP').replace(/-/g,'&#8209;').replace(/ /g,'&nbsp;') + '</th>';
    extUserHtmlString += '</tr></thead><tbody id="visitor-list-body">';

    var lineStyle = {}, lineCounter=0;
    for (var i = 0; i < external_users.length; i++) {
        //console.log('Create line');
        if (external_users[i].is_active) {
            extUserHtmlString += this.createVisitorListLine(external_users[i], chatObject, internal_users, visitorListWidth, true)[0];
        }
    }
    extUserHtmlString += '</tbody></table></div>' +
        '<div id="resize-visitor-list-alternative"><div title="' + t('Magnify') + '" id="rvla-larger"></div>' +
        '<div title="' + t('Demagnify') + '" id="rvla-smaller"></div></div>';

    thisVisitorList.html(extUserHtmlString).trigger('create');
    for (var i = 0; i < external_users.length; i++) {
        //console.log('Create line');
        if (external_users[i].is_active) {
            if (lineCounter % 2 == 0) {
                lineStyle = {'background-color': '#f8f8ff'};
            } else {
                lineStyle = {'background-color': '#ffffff'};
            }
            if (typeof chatObject[external_users[i].id + '~' + external_users[i].b_id] == 'undefined' ||
                chatObject[external_users[i].id + '~' + external_users[i].b_id].status != 'new') {
                $('#visitor-list-row-' + external_users[i].id).css(lineStyle);
            }
            lineCounter++;
        }
    }
    $('#visitor-list-headline').css(this.VisitorListHeadlineCss);
    $('#visitor-list-headline2').css(this.VisitorListHeadline2Css);
    $('#resize-visitor-list').css(resizeVisitorDivCss);
    $('#resize-visitor-list-alternative').css(resizeVisitorAlternativeDivCss);
    $('#rvla-larger').css({position: 'absolute', width: $('#resize-visitor-list-alternative').width()+'px',
        top: '0px', left: '0px', height: ($('#resize-visitor-list-alternative').height()/2)+'px',
        'background-image': 'url("img/button_rsfplus.gif")', 'background-repeat': 'no-repeat',
        'background-position': 'center bottom', cursor: 'pointer'});
    $('#rvla-smaller').css({position: 'absolute', width: $('#resize-visitor-list-alternative').width()+'px',
        top: ($('#resize-visitor-list-alternative').height()/2)+'px', left: '0px',
        height: ($('#resize-visitor-list-alternative').height()/2)+'px',
        'background-image': 'url("img/button_rsfminus.gif")', 'background-repeat': 'no-repeat',
        'background-position': 'center top', cursor: 'pointer'});
    var visitorListTableWidth = (this.displayWidth != 'small') ? $('#visitor-list').width() - 20 : $('#visitor-list').width();
    this.visitorListTableCss = {position: 'absolute',
        width: visitorListTableWidth+'px', height: ($('#visitor-list').height() - 48)+'px',
        'top': '48px', 'left': '0px', overflow: 'auto', padding: '5px'};
    $('#visitor-list-table-div').css(this.visitorListTableCss);
    $('#rvla-larger').click(function() {testDrag(30)});
    $('#rvla-smaller').click(function() {testDrag(-30)});

    /*if (typeof this.showBrowserHistory[1] != 'undefined' && this.showBrowserHistory[1] == 'on') {
        this.toggleBrowserHistory(this.showBrowserHistory[0], this.showBrowserHistory[1]);
    }*/
};

ChatDisplayClass.prototype.calculateTimeDifferenece = function(aUser, type, includeSeconds) {
    var tmpBegin, tmpTimeDifference, tmpDiffSeconds, tmpDiffMinutes, tmpDiffHours, tmpDiffDays, tmpRest, returnString = '';
    if (type=='lastOnline') {
        tmpBegin = $.now();
        for (var i=0; i<aUser.b.length; i++) {
            if (aUser.b[i].h2.length > 0) {
                tmpBegin = aUser.b[i].h2[0].time * 1000;
                break;
            }
        }
    } else {
        tmpBegin = 0;
        for (var i=0; i<aUser.b.length; i++) {
            if (aUser.b[i].h2.length > 0) {
                var newestH = aUser.b[i].h2.length - 1;
                tmpBegin = Math.max(aUser.b[i].h2[newestH].time * 1000, tmpBegin);
            }
        }
    }
    tmpTimeDifference = ($.now() - tmpBegin) / 1000;
    tmpDiffSeconds = tmpTimeDifference % 60;
    tmpRest = Math.floor(tmpTimeDifference / 60);
    tmpDiffMinutes = tmpRest % 60;
    tmpRest = Math.floor(tmpRest / 60);
    tmpDiffHours = tmpRest % 24;
    tmpDiffDays = Math.floor(tmpRest / 24);

    if (tmpDiffDays > 0) {
        returnString += tmpDiffDays + ' ';
    }
    returnString += this.lzm_commonTools.pad(tmpDiffHours, 2) + ':' + lzm_commonTools.pad(tmpDiffMinutes, 2);
    if (typeof includeSeconds != 'undefined' && includeSeconds) {
        returnString += ':' + lzm_commonTools.pad(tmpDiffSeconds, 2);
    }
    return returnString;
};

ChatDisplayClass.prototype.calculateTimeSpan = function(beginTime, endTime) {
    var secondsSpent = endTime.getSeconds() - beginTime.getSeconds();
    var minutesSpent = endTime.getMinutes() - beginTime.getMinutes();
    var hoursSpent = endTime.getHours() - beginTime.getHours();
    var daysSpent = endTime.getDate() - beginTime.getDate();
    if (daysSpent < 0) {
        var currentMonth = endTime.getMonth();
        var monthLength = 31;
        if ($.inArray(currentMonth, [3,5,8,10]) != -1) {
            monthLength = 30;
        }
        if (currentMonth == 1) {
            monthLength = 28;
        }
        daysSpent = (monthLength - beginTime.getDate()) + endTime.getDate();
    }
    if (secondsSpent < 0) {
        secondsSpent += 60;
        minutesSpent -= 1;
    }
    if (minutesSpent < 0) {
        minutesSpent += 60;
        hoursSpent -= 1;
    }
    if (hoursSpent < 0) {
        hoursSpent += 24;
        daysSpent -= 1;
    }
    var timeSpan = this.lzm_commonTools.pad(hoursSpent, 2) + ':' + this.lzm_commonTools.pad(minutesSpent, 2) + ':' +
        this.lzm_commonTools.pad(secondsSpent, 2);
    if (daysSpent > 0) {
        timeSpan = daysSpent + '.' + timeSpan;
    }
    return timeSpan;
};

/**
 * Create the visitor's browser history
 * @param visitor
 * @param toggle
 */
ChatDisplayClass.prototype.createBrowserHistory = function (visitor) {
    /*this.showBrowserHistory = [visitor, toggle];
    var thisRow = $('#visitor-list-row-' + visitor.id);
    if (typeof thisRow != 'undefined' && typeof thisRow[0] != 'undefined') {
        var numberOfColumns = thisRow[0].cells.length;
        var browserHistoryHtml = '<tr class="browser-history-row"><td colspan="' + numberOfColumns + '">' +*/
    var browserHistoryHtml = '<table class="browser-history visitor-list-table" style="width: 100%;"><tr>' +
        '<th nowrap>' + t('Browser') + '</th>' +
        '<th nowrap>' + t('Time') + '</th>' +
        '<th nowrap>' + t('Time span') + '</th>' +
        '<th nowrap>' + t('Area') + '</th>' +
        '<th nowrap>' + t('Title') + '</th>' +
        '<th nowrap>' + t('Url') + '</th>' +
        '<th nowrap>' + t('Referer') + '</th>' +
        '</tr>';
    var lineCounter = 0;
    var browserCounter = 1;
    for (var i = 0; i < visitor.b.length; i++) {
        if (visitor.b[i].id.indexOf('OVL') == -1) {
            for (var j = 0; j < visitor.b[i].h2.length; j++) {
                var browserIcon = 'img/300-web2_gray.png';
                var lineStyle = '';
                if (lineCounter % 2 == 0) {
                    lineStyle = ' style="background-color: #f8f8ff;"';
                }
                var beginTime = this.lzm_chatTimeStamp.getLocalTimeObject(visitor.b[i].h2[j].time * 1000);
                //new Date(visitor.b[i].h2[j].time * 1000);
                var beginTimeHuman = this.lzm_commonTools.pad(beginTime.getHours(), 2) + ':' +
                    this.lzm_commonTools.pad(beginTime.getMinutes(), 2);
                var endTime = new Date();
                if (visitor.b[i].h2.length > j + 1) {
                    endTime = this.lzm_chatTimeStamp.getLocalTimeObject(visitor.b[i].h2[j + 1].time * 1000);
                //new Date(visitor.b[i].h2[j + 1].time * 1000);
                }
                var endTimeHuman = this.lzm_commonTools.pad(endTime.getHours(), 2) + ':' +
                    this.lzm_commonTools.pad(endTime.getMinutes(), 2);
                var timeSpan = this.calculateTimeSpan(beginTime, endTime);
                var referer = '';
                if (i == 0) {
                    referer = visitor.b[i].ref;
                }
                if (j > 0) {
                    referer = visitor.b[i].h2[j - 1].url
                }
                if (visitor.b[i].is_active && j == visitor.b[i].h2.length - 1) {
                    browserIcon = 'img/300-web2.png';
                }
                browserHistoryHtml += '<tr' + lineStyle + '>' +
                    '<td nowrap><span style=\'background-image: url("' + browserIcon + '"); background-position: left center; background-repeat: no-repeat;' +
                    'margin-left: 3px; padding-left: 23px; font-weight: bold;\'>' +
                    (browserCounter) + '</span></td>' +
                    '<td nowrap>' + beginTimeHuman + ' - ' + endTimeHuman + '</td>' +
                    '<td nowrap>' + timeSpan + '</td>' +
                    '<td nowrap>' + visitor.b[i].h2[j].code + '</td>' +
                    '<td nowrap>' + visitor.b[i].h2[j].title + '</td>' +
                    '<td nowrap><a class="lz_chat_link" href="#" onclick="openLink(\'' + visitor.b[i].h2[j].url + '\')">' + visitor.b[i].h2[j].url + '</a></td>' +
                    '<td nowrap><a class="lz_chat_link" href="#" onclick="openLink(\'' + referer + '\')">' + referer + '</a></td>' +
                    '</tr>';
                lineCounter++;
            }
            browserCounter++;
        }
    }
    browserHistoryHtml += '</table></td></tr>';
    $('#visitor-browser-history-body').html(browserHistoryHtml).css(this.VisitorInfoBodyCss);
};

/**
 * Toggle the visibility of the visitor information
 * @param caller
 */
ChatDisplayClass.prototype.toggleVisitorInfo = function (external_users, chatObject, internal_users) {
    //console.log(this.infoCaller);
    var thisChatTable = $('#chat-table');
    var thisVisitorList = $('#visitor-list');
    var thisVisitorInfo = $('#visitor-info');

    //console.log(this.infoCaller + ' - ' + thisVisitorInfo.css('display') + ' - ' + this.ShowVisitorInfo);
    if (this.infoCaller != '') {
        if (thisVisitorInfo.css('display') == 'none' || this.ShowVisitorInfo) {
            this.ShowVisitorInfo = true;
            if (this.infoCaller == '#chat-table') {
                saveChatInput(this.active_chat_reco);
                this.lzm_chatInputEditor.removeEditor();
                //this.selected_view = 'external'
            }
            thisChatTable.css('display', 'none');
            if (this.displayWidth == 'small') {
                thisVisitorList.css('display', 'none');
            }
            thisVisitorInfo.css('display', 'block');
        } else {
            this.ShowVisitorInfo = false;
            thisChatTable.css('display', 'none');
            if (this.displayWidth == 'small') {
                thisVisitorList.css('display', 'none');
            }
            if (this.infoCaller == '#visitor-list') {
                this.updateVisitorList(external_users, chatObject, internal_users);
            } else if (this.infoCaller == '#chat-table') {
                this.lzm_chatInputEditor.init(loadChatInput(this.active_chat_reco));
                //this.selected_view = 'mychats';
            }
            thisVisitorInfo.css('display', 'none');
            $(this.infoCaller).css('display', 'block');
            this.infoCaller = '';
        }
    }
};

/**
 * Create the operator list
 */
ChatDisplayClass.prototype.createOperatorList = function (internal_departments, internal_users, chatObject, chosen_profile) {
    internal_users.sort(this.operatorSortFunction);
    // create the left hand side menu for the internal chat view
    var dptLogo = 'img/lz_group.png';
    if (typeof chatObject['everyoneintern'] != 'undefined' && chatObject['everyoneintern']['status'] == 'new') {
        dptLogo = 'img/217-quote.png';
    }
    var intUserHtmlString = '<div id="operator-list-headline"><h3>' + t('Operators') + '</h3></div>' +
        '<div id="operator-list-headline2"></div>' +
        '<div id="operator-list-body"><table id="operator-list-table">';
    intUserHtmlString += '<tr><th colspan="2" style="text-align: left; cursor: pointer;" ' +
        'onclick="chatInternalWith(\'everyoneintern\',\'everyoneintern\',\'' + t('All operators') + '\');">' +
        '<img src="' + dptLogo + '" width="14px" height="14px" />&nbsp;&nbsp;' + t('All operators') +
        '</th></tr>';
    for (var i = 0; i < internal_departments.length; i++) {
        if (typeof internal_departments[i].id != 'undefined') {
            dptLogo = 'img/lz_group.png';
            if (typeof chatObject[internal_departments[i].id] != 'undefined' && chatObject[internal_departments[i].id]['status'] == 'new') {
                dptLogo = 'img/217-quote.png';
            }
            intUserHtmlString += '<tr><th colspan="2" style="text-align: left; cursor: pointer;" ' +
                'onclick="chatInternalWith(\'' + internal_departments[i].id + '\',\'' + internal_departments[i].id +
                '\',\'' + internal_departments[i].id + '\');">' +
                '<img src="' + dptLogo + '" width="14px" height="14px" />&nbsp;&nbsp;' + internal_departments[i].id +
                '</th></tr>';
            for (var j = 0; j < internal_users.length; j++) {
                var intUserStyle = 'style="text-align: left; padding: 2px 0px 0px 2px;" ';
                //if ($.inArray(internal_users[j].id, incoming_chats) != -1 && active_chat != internal_users[j].id) {
                if (typeof chatObject[internal_users[j].id] != 'undefined' && chatObject[internal_users[j].id]['status'] == 'new') {
                    internal_users[j].logo = 'img/217-quote.png';
                    intUserStyle = 'style="color: #ED9831; font-weight: bold; text-align: left; padding: 2px 0px 0px 2px;" ';
                }
                if ($.inArray(internal_departments[i].id, internal_users[j].groups) != -1) {
                    var onclickAction = '';
                    if (internal_users[j].userid != chosen_profile.login_name &&
                        (typeof internal_users[j].isbot == 'undefined' || internal_users[j].isbot != 1)) {
                        onclickAction = ' onclick="chatInternalWith(\'' + internal_users[j].id + '\',\'' + internal_users[j].userid +
                        '\',\'' + internal_users[j].name + '\');"';
                        var tmpIntUserStyle = intUserStyle.replace(/"$/, '').replace(/" *$/, '');
                        intUserStyle = tmpIntUserStyle + ' cursor: pointer;"';
                    }
                    intUserHtmlString += '<tr><td>&nbsp;&nbsp;</td>' +
                        '<td class="userlist internal-user-' + internal_users[j].id + '" ' + intUserStyle + onclickAction;
                    intUserHtmlString += '>' +
                        '<img src="' + internal_users[j].logo + '" width="14px" height="14px" />' +
                        '&nbsp;&nbsp;' + internal_users[j].name + '</td></tr>';
                }
            }
        }
    }
    intUserHtmlString += '</table></div>';
    $('#operator-list').html(intUserHtmlString);
    $('#operator-list-headline').css(this.OperatorListHeadlineCss);
    $('#operator-list-headline2').css(this.OperatorListHeadline2Css);
    $('#operator-list-body').css(this.OperatorListBodyCss);
    $('#operator-list-table').css({'margin': '6px 2px 2px 2px'});
};

ChatDisplayClass.prototype.switchCenterPage = function(target) {
    if (target == 'home') {
        $('#chat-logo').css('display', 'block');
        $('#chat-action').css('display', 'none');
        $('#chat-buttons').css('display', 'none');
        $('#chat-title').css('display', 'none');
        $('#chat-input-body').css('display', 'none');
        $('#chat-progress').css('display', 'none');
        $('#switch-center-page').css('background-image', this.addBrowserSpecificGradient('url("img/612-home_gray.png")','darkViewSelect'));
        //console.log(this.addBrowserSpecificGradient('','blue'))
    } else {
        $('#chat-logo').css('display', 'none');
        $('#chat-action').css('display', 'block');
        if (this.active_chat != '') {
            $('#chat-buttons').css('display', 'block');
            $('#chat-input-body').css('display', 'block');
            $('#chat-progress').css('display', 'block');
        }
        $('#switch-center-page').css('background-image', this.addBrowserSpecificGradient('url("img/612-home_gray.png")'));
    }
};

/**
 * Create the panel listing all active chats of the logged in operator
 */
ChatDisplayClass.prototype.createActiveChatPanel = function (external_users, internal_users, internal_departments, chatObject, updateVisitorListNow) {
    updateVisitorListNow = (typeof updateVisitorListNow == 'undefined') ? true : false;
    if (updateVisitorListNow && this.selected_view == 'external' && !this.ShowVisitorInfo) {
        this.updateVisitorList(external_users, chatObject, internal_users);
    }
    var extUserObject = {}, intUserObject = {}, intDepartmentObject = {};
    for (var i=0; i< external_users.length; i++) {
        extUserObject[external_users[i].id] = external_users[i];
    }
    for (var j=0; j<internal_users.length; j++) {
        intUserObject[internal_users[j].id] = internal_users[j];
    }
    for (var k=0; k<internal_departments.length; k++) {
        intDepartmentObject[internal_departments[k].id] = internal_departments[k];
    }

    var thisActiveChatPanel = $('#active-chat-panel');
    var newActivityCss = '';
    var isActiveDataTheme = '';
    var isActiveCss = '';
    var onclickAction = '';
    var senderName = '';
    var senderId = '';
    var senderBId = '';
    var senderChatId = '';
    var senderUserId = '';
    var activeCounter = 0;
    var lineCounter = 1;
    var thisActiveChatPanelWidth = thisActiveChatPanel.width();
    var chatPanelDivsPerLine = Math.floor((thisActiveChatPanelWidth - 26 - 26) / (100+10));

    var defaultCss = ' height: 14px; position: absolute; padding: 3px 5px 3px 21px; text-align: center; font-size: 11px; ' +
        'overflow: hidden; cursor: pointer; border: 1px solid #ccc; border-radius: 4px;';
    this.templateCloseButton = '<div id="%BTNID%" %BTNONCLICK%' +
        ' style=\'background-image: ' + this.addBrowserSpecificGradient('url("img/205-close.png")') + ';' +
        ' background-repeat: no-repeat; background-position: center; display: none;' +
        ' left: %BTNLEFT%px; top: %BTNTOP%px; width: 16px; %BTNDEFAULTCSS%\'></div>';
    var closeButton = this.templateCloseButton.replace(/%BTNID%/g,'close-active-chat').
        replace(/%BTNONCLICK%/g, 'onclick="leaveChat();" ').
        replace(/%BTNDEFAULTCSS%/g , defaultCss.replace(/padding: 3px 5px 3px 21px;/,'padding: 3px;')).
        replace(/%BTNLEFT%/g, thisActiveChatPanelWidth - 26).
        replace(/%BTNTOP%/g, 2);
    var homeButton = '<div id="switch-center-page" onclick="switchCenterPage(\'home\');" ' +
        'style=\'background-image: ' + this.addBrowserSpecificGradient('url("img/612-home_gray.png")') + ';' +
        ' background-repeat: no-repeat; background-position: center;' +
        ' left: 2px; top: 2px; width: 16px; ' + defaultCss.replace(/padding: 3px 5px 3px 21px;/,'padding: 3px;') + '\'></div>';
    var activityHtml = homeButton + closeButton;
    //console.log(activityHtml);

    var newIncomingChats = [];
    this.chatActivity = false;
    //console.log('Set chatActivity to false');
    var thisDivLeft = [30];
    var thisLine = 0;
    var thisLineIsFull = false;
    for (var sender in chatObject) {
        var senderIsActive = false;
        if (chatObject[sender].type == 'external' && chatObject[sender].status == 'new' && $.inArray(sender, this.openChats) == -1) {
            newIncomingChats.push(sender);
        }
        // (chatObject[sender].status != 'left' || sender == this.active_chat_reco) &&
        if ((chatObject[sender].status != 'left' && chatObject[sender].status != 'declined') || $.inArray(sender, this.closedChats) == -1) {
            if (chatObject[sender].type == 'external') {
                var senderParts = sender.split('~');
                if (typeof extUserObject[senderParts[0]] != 'undefined') {
                        if (typeof chatObject[sender].name == 'undefined' && extUserObject[senderParts[0]].b_cname != '') {
                            senderName = extUserObject[senderParts[0]].b_cname;
                        } else if (typeof chatObject[sender].name == 'undefined' || chatObject[sender].name == '') {
                                senderName = extUserObject[senderParts[0]].unique_name;
                        } else {
                            senderName = chatObject[sender].name;
                        }
                        senderId = senderParts[0];
                        senderBId = senderParts[1];
                        //senderChatId = extUserObject[senderParts[0]].b_chat.id;
                        senderChatId = chatObject[sender].chat_id;
                        onclickAction = ' onclick="viewUserData(\'' + senderId + '\', \'' + senderBId + '\', \'' +
                            senderChatId + '\', true)"';
                        senderIsActive = extUserObject[senderParts[0]].is_active;
                }
            } else {
                if (typeof intUserObject[sender] != 'undefined') {
                        senderId = intUserObject[sender].id;
                        senderUserId = intUserObject[sender].userid;
                        senderName = intUserObject[sender].name;
                        onclickAction = ' onclick="chatInternalWith(\'' + senderId + '\', \'' + senderUserId + '\', \'' +
                            senderName + '\')"';
                        senderIsActive = intUserObject[sender].is_active;
                }
                if (typeof intDepartmentObject[sender] != 'undefined') {
                        senderId = intDepartmentObject[sender].id;
                        senderUserId = intDepartmentObject[sender].id;
                        senderName = intDepartmentObject[sender].id;
                        onclickAction = ' onclick="chatInternalWith(\'' + senderId + '\', \'' + senderUserId + '\', \'' +
                            senderName + '\')"';
                        senderIsActive = intDepartmentObject[sender].is_active;
                }
                if (sender == 'everyoneintern') {
                    senderId = 'everyoneintern';
                    senderUserId = 'everyoneintern';
                    senderName = t('All operators');
                    onclickAction = ' onclick="chatInternalWith(\'' + senderId + '\', \'' + senderUserId + '\', \'' +
                        senderName + '\')"';
                    senderIsActive = true;
                }
            }

            var buttonLogo = 'img/lz_offline.png';
            if (sender == 'everyoneintern' || typeof intDepartmentObject[sender] != 'undefined') {
                buttonLogo = 'img/lz_group.png';
            } else if (typeof intUserObject[sender] != 'undefined') {
                buttonLogo = intUserObject[sender].status_logo;
            } else if (typeof extUserObject[sender.split('~')[0]] != 'undefined' &&
                extUserObject[sender.split('~')[0]].is_active &&
                chatObject[sender]['status'] != 'left' &&
                chatObject[sender]['status'] != 'declined') {
                buttonLogo = 'img/lz_online.png';
            }
            if (chatObject[sender]['status'] == 'new' ||
                (typeof chatObject[sender].fupr != 'undefined' &&
                    (typeof chatObject[sender].fuprDone == 'undefined' ||
                        chatObject[sender].fuprDone != chatObject[sender].fupr.id))) {
                if (sender == this.active_chat_reco) {
                    defaultCss += ' background:#5285AF; color:#FFF; text-shadow: 0 0px #fff; ' + this.addBrowserSpecificGradient('text','darkViewSelect') + ';';
                } else {
                    defaultCss += ' background:#FFCC73;color:#000; ' + this.addBrowserSpecificGradient('text','orange') + ';';
                }
                //this.playSound('message', sender);
                this.chatActivity = true;
                //console.log('Set chatActivity to true');
            } else {
                //console.log(sender + ' --- ' + this.active_chat_reco);
                if (sender == this.active_chat_reco) {
                    defaultCss += ' background:#5285AF; color:#FFF; text-shadow: 0 0px #fff; ' + this.addBrowserSpecificGradient('text','darkViewSelect') + ';';
                } else {
                    defaultCss += ' background:#DDD; color:#000; ' + this.addBrowserSpecificGradient('text','') + ';';
                }
            }
            if (chatObject[sender].type == 'external' || (chatObject[sender].status != 'left')) {
                var thisDivTop = 2 + thisLine * 24;
                var displaySenderName = senderName;
                if (typeof senderName != 'undefined' && senderName.length > 15){
                    displaySenderName = senderName.substring(0, 15) + '...';
                }
                var thisButtonHtml = '<div' + onclickAction + ' style=\'left:' + thisDivLeft[thisLine]+'px; top: ' + thisDivTop+'px;' + defaultCss + '\'>' +
                    '<span style=\'width: 20px; ' +
                    'position: absolute; left: 0px; top: 3px; ' +
                    'background-image: url("' + buttonLogo + '"); background-size: 14px; background-position: center; background-repeat: no-repeat;\'>&nbsp;</span>' +
                    displaySenderName + '</div>';
                var testLengthDiv = $('#test-length-div');
                testLengthDiv.html(thisButtonHtml).trigger('create');
                var thisButtonLength = testLengthDiv.children('div').width() + 32;
                var thisLineRight = (thisLine == 0) ? 26 : 2;
                //console.log((thisDivLeft[thisLine]) + ' - ' + thisButtonLength + ' - ' + (thisActiveChatPanelWidth - thisLineRight));
                if ((thisDivLeft[thisLine] + thisButtonLength) >= (thisActiveChatPanelWidth - thisLineRight)) {
                    thisLine++;
                    thisDivTop = 2 + thisLine * 24;
                    thisDivLeft.push(2);
                    thisButtonHtml = '<div' + onclickAction + ' style=\'left:' + thisDivLeft[thisLine] + 'px; top: ' + thisDivTop+'px;' + defaultCss + '\'>' +
                        '<span style=\'width: 20px; ' +
                        'position: absolute; left: 0px; top: 3px; ' +
                        'background-image: url("' + buttonLogo + '"); background-size: 14px; background-position: center; background-repeat: no-repeat;\'>&nbsp;</span>' +
                        displaySenderName + '</div>';
                }
                activeCounter++;
                thisDivLeft[thisLine] += thisButtonLength;
                activityHtml += thisButtonHtml;
                this.activeChatPanelHeight = 26 * (thisLine + 1);
            }

        }
    }
    //console.log(newIncomingChats);
    if (newIncomingChats.length > 0) {
        this.startRinging(newIncomingChats);
    } else {
        this.stopRinging(newIncomingChats);
    }
    thisActiveChatPanel.html(activityHtml).trigger('create');
    //  && chatObject[this.active_chat_reco].status == 'left'
    if (this.active_chat_reco != '' && /*(this.active_chat_reco.indexOf('~') == -1 || $.inArray(this.active_chat_reco, this.openChats) != -1 ||*/
        (typeof chatObject[this.active_chat_reco] != 'undefined' && chatObject[this.active_chat_reco].status != 'new'))/*)*/ {
        $('#close-active-chat').css({display: 'block'});
    }
    this.createChatWindowLayout(false);
    if (this.chatActivity && (this.settingsDialogue || this.selected_view != 'mychats')) {
        $('#radio-mychats-text').css({'border': '1px solid #ec8f00', background: '#ffcc73', 'background-image': this.addBrowserSpecificGradient('', 'orange'), color: '#000'});
        $('#radio-left-text').css({'border': '1px solid #ec8f00', background: '#ffcc73', 'background-image': this.addBrowserSpecificGradient('', 'orange'), color: '#000'});
    } else {
        $('#radio-mychats-text').css({'border': '1px solid #666', background: '#898989', 'background-image': this.addBrowserSpecificGradient('', 'darkViewSelect'), color: '#fff'});
        $('#radio-left-text').css({'border': '1px solid #666', background: '#898989', 'background-image': this.addBrowserSpecificGradient('', 'darkViewSelect'), color: '#fff'});

    }
    this.createChatWindowLayout(false);
};

/**
 * Create the html code for the chat window
 */
ChatDisplayClass.prototype.createChatHtml = function (chats, chatObject, thisUser, internal_users, external_users, active_chat_reco) {
    var intUserObject = {};
    var extUserObject = {};
    for (var j=0; j<internal_users.length; j++) {
        intUserObject[internal_users[j].id] = internal_users[j];
    }
    for (var k=0; k<external_users.length; k++) {
        extUserObject[external_users[k].id] = external_users[k];
    }
    var now = new Date();
    var time_human = this.lzm_commonTools.pad(now.getHours(), 2) + ':' +
        this.lzm_commonTools.pad(now.getMinutes(), 2) + ':' + this.lzm_commonTools.pad(now.getSeconds(), 2);

    var incomingChatStyle = '';
    var outgoingChatStyle = '';
    var incomingForwardedStyle = '';
    var outgoingForwardedStyle = '';

    var tUquestion ='';
    var tUname ='';
    var tUgroup = '';
    var tUoperators = '';
    var tUemail = '';
    var tUcompany = '';
    var tUphone = '';
    if (active_chat_reco.indexOf('~') != -1 && typeof thisUser.b != 'undefined') {
        for (var i=0; i<thisUser.b.length; i++) {
            if (thisUser.b[i].id == active_chat_reco.split('~')[1]) {
                if (thisUser.id != '') {
                    if (thisUser.b[i].cname != '' && typeof thisUser.b[i].cname != 'undefined') {
                        this.active_chat_realname = thisUser.b[i].cname;
                    } else {
                        if (thisUser.name != '' && typeof thisUser.name != 'undefined') {
                            this.active_chat_realname = thisUser.name;
                        } else {
                            if (thisUser.unique_name != '' && typeof thisUser.unique_name != 'undefined') {
                                this.active_chat_realname = thisUser.unique_name;
                            } else {
                                this.active_chat_realname = thisUser.id;
                            }
                        }
                    }
                }
                if (typeof chatObject[active_chat_reco] == 'undefined' || typeof chatObject[active_chat_reco].eq == 'undefined' || chatObject[active_chat_reco].eq == '') {
                    tUquestion = (typeof thisUser.b[i].chat != 'undefined' && typeof thisUser.b[i].chat.eq != 'undefined') ? thisUser.b[i].chat.eq : '';
                } else {
                    tUquestion = chatObject[active_chat_reco].eq;
                }
                if (typeof chatObject[active_chat_reco] == 'undefined' || typeof chatObject[active_chat_reco].name == 'undefined') {
                    tUname = (typeof thisUser.b[i].cname != 'undefined' && thisUser.b[i].cname != '') ? thisUser.b[i].cname : thisUser.unique_name;
                } else {
                    tUname = (chatObject[active_chat_reco].name != '') ? chatObject[active_chat_reco].name : thisUser.unique_name;
                }
                tUemail = (typeof thisUser.b[i].cemail != 'undefined') ? thisUser.b[i].cemail : '';
                tUcompany = (typeof thisUser.b[i].ccompany != 'undefined') ? thisUser.b[i].ccompany : '';
                tUphone = (typeof thisUser.b[i].cphone != 'undefined') ? thisUser.b[i].cphone : '';
                if (typeof thisUser.id != 'undefined' && thisUser.id != '') {
                    if (typeof extUserObject[thisUser.id] != 'undefined') {
                        tUgroup = (typeof extUserObject[thisUser.id].b[i].chat != 'undefined') ? extUserObject[thisUser.id].b[i].chat.gr : '';
                        for (var intUserIndex=0; intUserIndex<internal_users.length; intUserIndex++) {
                            if (typeof extUserObject[thisUser.id].b[i].chat != 'undefined' && typeof extUserObject[thisUser.id].b[i].chat.pn != 'undefined' &&
                                typeof extUserObject[thisUser.id].b[i].chat.pn.memberIdList != 'undefined' &&
                                $.inArray(internal_users[intUserIndex].id, extUserObject[thisUser.id].b[i].chat.pn.memberIdList) != -1) {
                                tUoperators +=  internal_users[intUserIndex].name + ', ';
                            }
                        }
                    }
                    tUoperators = tUoperators.replace(/, *$/,'');
                }
            }
        }
    }
    //console.log('header created for : ' + active_chat_reco);
    var headerTemplate = this.messageTemplates['header'].replace(/<!--new_chat_request_label-->/g,t('Chat request to'));
    headerTemplate = headerTemplate.replace(/<!--group_name-->/g,tUgroup);
    headerTemplate = headerTemplate.replace(/<!--receivers-->/g,tUoperators);
    headerTemplate = headerTemplate.replace(/<!--name_label-->/g,t('Name'));
    headerTemplate = headerTemplate.replace(/<!--user-->/g,tUname);
    headerTemplate = headerTemplate.replace(/<!--email_label-->/g,t('Email'));
    headerTemplate = headerTemplate.replace(/<!--email-->/g,tUemail);
    headerTemplate = headerTemplate.replace(/<!--company_label-->/g,t('Company'));
    headerTemplate = headerTemplate.replace(/<!--company-->/g,tUcompany);
    headerTemplate = headerTemplate.replace(/<!--phone_label-->/g,t('Phone'));
    headerTemplate = headerTemplate.replace(/<!--phone-->/g,tUphone);
    headerTemplate = headerTemplate.replace(/<!--question_label-->/g,t('Question'));
    headerTemplate = headerTemplate.replace(/<!--question-->/g,tUquestion);
    headerTemplate = headerTemplate.replace(/<!--custom_fields-->/g,'');

    var chatHtmlString = (typeof thisUser.b_id != 'undefined' && thisUser.b_id != '') ? headerTemplate : '';
    var messageText = '';
    var previousMessageSender = '';
    for (var chatIndex = 0; chatIndex < chats.length; chatIndex++) {
        if (chats[chatIndex].reco == this.myId && (chats[chatIndex].sen_id == this.active_chat_reco ||
            chats[chatIndex].sen_id + '~' + chats[chatIndex].sen_b_id == this.active_chat_reco)) {
            //console.log(chats[chatIndex].text);
            var thisSenderName = '';
            if (chats[chatIndex].rec == chats[chatIndex].sen_id) {
                if (typeof intUserObject[chats[chatIndex].sen] != 'undefined') {
                    thisSenderName = intUserObject[chats[chatIndex].sen].name.substring(0, 29);
                }
            } else {
                thisSenderName = (typeof tUname == 'undefined' || tUname == '') ? this.active_chat_realname : tUname;
            }
            thisSenderName = thisSenderName.substring(0, 29);
            if (previousMessageSender != chats[chatIndex].sen) {
                if (chats[chatIndex].rp == 1) {
                    messageText = this.messageTemplates['repost'].replace(/<!--name-->/g,thisSenderName);
                } else {
                    messageText = this.messageTemplates['internal'].replace(/<!--name-->/g,thisSenderName);
                }
            } else {
                messageText = this.messageTemplates['add'].replace(/<!--name-->/g,this.active_chat_realname.substring(0, 29));
            }
            messageText = messageText.replace(/<!--time-->/g, chats[chatIndex].time_human);
            messageText = messageText.replace(/<!--message-->/g, chats[chatIndex].text);
            messageText = messageText.replace(/<!--dir-->/g, 'ltr');
            chatHtmlString += messageText;
            previousMessageSender = chats[chatIndex].sen;
        } else if (chats[chatIndex].sen == this.myId && chats[chatIndex].reco == this.active_chat_reco) {
            if (previousMessageSender != chats[chatIndex].sen) {
                if (chats[chatIndex].rp == 1)
                    messageText = this.messageTemplates['repost'].replace(/<!--name-->/g,this.myName.substring(0, 29));
                else
                    messageText = this.messageTemplates['external'].replace(/<!--name-->/g,this.myName.substring(0, 29));
            } else
                messageText = this.messageTemplates['add'].replace(/<!--name-->/g,this.myName.substring(0, 29));
            messageText = messageText.replace(/<!--time-->/g, chats[chatIndex].time_human);
            messageText = messageText.replace(/<!--message-->/g, chats[chatIndex].text);
            messageText = messageText.replace(/<!--dir-->/g, 'ltr');
            chatHtmlString += messageText;
            previousMessageSender = chats[chatIndex].sen;
        } else if (chats[chatIndex].sen == '0000000' && chats[chatIndex].reco == this.active_chat_reco) {
            if (previousMessageSender != chats[chatIndex].sen) {
                if (chats[chatIndex].rp == 1)
                    messageText = this.messageTemplates['repost'].replace(/<!--name-->/g,t('System').substring(0, 29));
                else
                    messageText = this.messageTemplates['external'].replace(/<!--name-->/g,t('System').substring(0, 29));
            } else
                messageText = this.messageTemplates['add'].replace(/<!--name-->/g,t('System').substring(0, 29));
            messageText = messageText.replace(/<!--time-->/g, chats[chatIndex].time_human);
            messageText = messageText.replace(/<!--message-->/g, chats[chatIndex].text);
            messageText = messageText.replace(/<!--dir-->/g, 'ltr');
            chatHtmlString += messageText;
            previousMessageSender = chats[chatIndex].sen;
        } else if (chats[chatIndex].sen != this.myId && chats[chatIndex].reco == this.active_chat_reco && this.active_chat != this.active_chat_reco) {
            var forwardingOpName = '';
            if (typeof intUserObject[chats[chatIndex].sen] != 'undefined') {
                forwardingOpName = intUserObject[chats[chatIndex].sen].name;
            }
            if (previousMessageSender != chats[chatIndex].sen) {
                if (chats[chatIndex].rp == 1)
                    messageText = this.messageTemplates['repost'].replace(/<!--name-->/g,forwardingOpName.substring(0, 29));
                else
                    messageText = this.messageTemplates['external'].replace(/<!--name-->/g,forwardingOpName.substring(0, 29));
            } else
                messageText = this.messageTemplates['add'].replace(/<!--name-->/g,forwardingOpName.substring(0, 29));
            messageText = messageText.replace(/<!--time-->/g, chats[chatIndex].time_human);
            messageText = messageText.replace(/<!--message-->/g, chats[chatIndex].text);
            messageText = messageText.replace(/<!--dir-->/g, 'ltr');
            chatHtmlString += messageText;
            previousMessageSender = chats[chatIndex].sen;
        } else if (chats[chatIndex].reco != this.myId && chats[chatIndex].sen_id == this.active_chat && this.active_chat != this.active_chat_reco) {
            if (previousMessageSender != chats[chatIndex].sen) {
                if (chats[chatIndex].rp == 1)
                    messageText = this.messageTemplates['repost'].replace(/<!--name-->/g,this.active_chat_realname.substring(0, 29));
                else
                    messageText = this.messageTemplates['external'].replace(/<!--name-->/g,this.active_chat_realname.substring(0, 29));
            } else
                messageText = this.messageTemplates['add'].replace(/<!--name-->/g,this.active_chat_realname.substring(0, 29));
            messageText = messageText.replace(/<!--time-->/g, chats[chatIndex].time_human);
            messageText = messageText.replace(/<!--message-->/g, chats[chatIndex].text);
            messageText = messageText.replace(/<!--dir-->/g, 'ltr');
            chatHtmlString += messageText;
            previousMessageSender = chats[chatIndex].sen;
        }
    }
    var thisChatProgress = $('#chat-progress');
    thisChatProgress.html(chatHtmlString);
    thisChatProgress.scrollTop(thisChatProgress[0].scrollHeight);

    //console.log(thisUser);
    $('#chat-action').css('visibility', 'visible');
    $('#chat-buttons').css('visibility', 'visible');
};

/**
 * (Re)create the complete html structure depending on the contents of the javascript variables
 */
ChatDisplayClass.prototype.createHtmlContent = function (internal_departments, internal_users, external_users, chats, chatObject, thisUser, global_errors, chosen_profile, active_chat_reco) {

    // make the user aware of new incoming messages
    this.createActiveChatPanel(external_users, internal_users, internal_departments, chatObject, false);

    // create the visitor and operator lists
    this.createOperatorList(internal_departments, internal_users, chatObject, chosen_profile);
    if (this.selected_view == 'external' && !this.ShowVisitorInfo) {
        this.updateVisitorList(external_users, chatObject, internal_users);
    }

    // fill the chat window with content
    this.createChatHtml(chats, chatObject, thisUser, internal_users, external_users, active_chat_reco);

    // fill the error view with content
    //this.createErrorHtml(global_errors);
};

/**
 * custom sort function for sorting the operators by their status
 * @param a
 * @param b
 * @return {Number}
 */
ChatDisplayClass.prototype.operatorSortFunction = function (a, b) {
    var returnValue;
    if (a.name > b.name) {
        returnValue = 1
    } else if (a.name == b.name) {
        returnValue = 0;
    } else {
        returnValue = -1;
    }
    return returnValue;
};

ChatDisplayClass.prototype.visitorSortFunction = function(a, b) {
    // lzm_chatServerEvaluation.external_users[0].b[0].h2[0].time
    var returnValue = 0;
    switch (this.visitorSortBy) {
        case 'name':

            break;
        case 'city':
            if (a.city > b.city) {
                returnValue = 1;
            } else if (a.city == b.city) {
                returnValue = 0;
            } else {
                returnValue = -1;
            }
            break;
        case 'country':

            break;
        default:
            if (typeof a.b == 'undefined' || a.b.length == 0 || typeof a.b[0].h2 == 'undefined' || a.b[0].h2.length == 0 || typeof a.b[0].h2[0].time == 'undefined') {
                returnValue = 1;
            } else if (typeof b.b == 'undefined' || b.b.length == 0 || typeof b.b[0].h2 == 'undefined' || b.b[0].h2.length == 0 || typeof b.b[0].h2[0].time == 'undefined') {
                returnValue = -1;
            } else if (a.b[0].h2[0].time < b.b[0].h2[0].time) {
                returnValue = 1;
            } else if (a.b[0].h2[0].time > b.b[0].h2[0].time) {
                returnValue = -1;
            }
            break;
    }
    return returnValue;
};

/**
 * Create the view for inviting other operators to a chat and/or transfering a chat to another operator
 * @param type
 */
ChatDisplayClass.prototype.createOperatorInviteHtml = function (type, internal_departments, internal_users, thisUser, chosen_profile, id, b_id, chat_id) {
    saveChatInput(lzm_chatDisplay.active_chat_reco);
    this.lzm_chatInputEditor.removeEditor();
    internal_users.sort(this.operatorSortFunction);
    //console.log(internal_users);
    var activeOperatorCounter = 0;
    var thisOpFwdList = $('#operator-forward-list');
    var opFwdListWidth = thisOpFwdList.width();
    var defaultCss = ' height: 14px; position: absolute; padding: 3px; text-align: center; ' +
        'overflow: hidden; cursor: pointer; border: 1px solid #ccc; border-radius: 4px;';
    if (!this.showOpInviteList) {
        this.showOpInviteList = true;
        if (typeof thisUser.b_chat != 'undefined') {
            var opInviteHtmlString = '<div id="operator-forward-list-headline"><h3 id="op-invite-headline"></h3></div>' +
                '<div id="operator-forward-list-headline2"></div>' +
                //closeButton +
                '<div id="operator-forward-list-body"><div id="fwd-container">';
            opInviteHtmlString += '<select id="fwdGroupSelect" data-mini="true">' +
                '<option value="">' + t('--- Choose a group ---') + '</option>';
            for (var intDepIndex = 0; intDepIndex < internal_departments.length; intDepIndex++) {
                if (typeof internal_departments[intDepIndex].id != 'undefined') {
                    opInviteHtmlString += '<option value="' + internal_departments[intDepIndex].id + '">' +
                    internal_departments[intDepIndex].id + '</option>';
                }
            }
            opInviteHtmlString += '</select>'
            opInviteHtmlString += '<div id="fwdOperatorSelectDiv"><select id="fwdOperatorSelect" data-mini="true">' +
                '<option value="">' + t('--- No group chosen ---') + '</option></select></div>';
            opInviteHtmlString += '<div id="op-fwd-action">' +
                '<textarea id="forward-text" style="height: 5em;" placeholder="' + t('Send this text to the other operator') + '"></textarea></div>';
            opInviteHtmlString += '</div></div>' +
                '<div id="operator-forward-list-footline">' +

                '<span id="fwd-button" class="chat-button-line chat-button-left chat-button-right ui-disabled" ' +
                'style="margin-left: 4px; padding-left: 10px; padding-right: 10px; cursor:pointer; background-image: ' +
                this.addBrowserSpecificGradient('') + ';" onclick="forwardChat();">&nbsp;' + t('Ok') + '&nbsp;</span>' +
                '<span id="cancel-usersettings" class="chat-button-line chat-button-left chat-button-right" ' +
                'style="margin-left: 4px; padding-left: 10px; padding-right: 10px; cursor:pointer; background-image: ' +
                this.addBrowserSpecificGradient('') + ';" onclick="viewUserData(\'' + id + '\', \'' + b_id + '\', \'' + chat_id + '\', true);">' +
                '&nbsp;' + t('Cancel') + '&nbsp;</span>' +

                /*'<a href="#" id="fwd-button" data-role="button" data-mini="true" data-inline="true" class="ui-disabled" ' +
                'onclick="forwardChat();">' + t('Ok') + '</a>' +
                '<a href="#" onclick="viewUserData(\'' + id + '\', \'' + b_id + '\', \'' + chat_id + '\', true);" data-role="button" ' +
                'id="cancel-usersettings" data-mini="true" data-inline="true">&nbsp;' + t('Cancel') + '&nbsp;</a>' +*/
                '</div>';
            thisOpFwdList.html(opInviteHtmlString).trigger('create');
            $('#operator-forward-list-headline').css(this.OperatorForwardListHeadlineCss);
            $('#operator-forward-list-headline2').css(this.OperatorForwardListHeadline2Css);
            $('#operator-forward-list-footline').css(this.OperatorForwardListFootlineCss);
            $('#operator-forward-list-body').css(this.OperatorForwardListBodyCss);
            $('#fwd-container').css(this.fwdContainerCss);
            var thisBackBtn = $('#back-to-chat-button');
            thisBackBtn.css({display: 'block'});
            $('#fwdGroupSelect').change(function() {
                var selectedGroupId = $('#fwdGroupSelect').val();
                //console.log('Group chanage - chosen group : ' + selectedGroupId);
                var opChooseHtml = '';
                var numberOfAvailableOp = 0;
                if (selectedGroupId != '') {
                    opChooseHtml = '<select id="fwdOperatorSelect" data-mini="true">' +
                        '<option value="">' + t('--- Choose an operator ---') + '</option>';
                    for (var intUserIndex = 0; intUserIndex < internal_users.length; intUserIndex++) {
                        //console.log(internal_users[intUserIndex].userid + ' --- ' + chosen_profile.login_name);
                        if (internal_users[intUserIndex].userid != chosen_profile.login_name &&
                            $.inArray(selectedGroupId, internal_users[intUserIndex].groups) != -1 &&
                            (typeof internal_users[intUserIndex].isbot == 'undefined' ||
                                    internal_users[intUserIndex].isbot != 1) &&
                            (internal_users[intUserIndex].status != 2 && internal_users[intUserIndex].status != 3)) {
                            opChooseHtml += '<option value="' + internal_users[intUserIndex].userid + '">' +
                                internal_users[intUserIndex].name + '</option>';
                            numberOfAvailableOp++;
                        }
                    }
                    opChooseHtml += '</select>';
                    if (numberOfAvailableOp == 0) {
                        opChooseHtml = '<select id="fwdOperatorSelect" data-mini="true">' +
                            '<option value="">' + t('--- No operators in this group available ---') + '</option></select>';
                    }
                } else {
                    opChooseHtml = '<select id="fwdOperatorSelect" data-mini="true">' +
                        '<option value="">' + t('--- No group chosen ---') + '</option></select>';
                }
                $('#fwdOperatorSelectDiv').html(opChooseHtml).trigger('create');
                $('#fwdOperatorSelect').change(function() {
                    var selectedOpUserId = $('#fwdOperatorSelect').val();
                    var forward_id, forward_name, forward_group, forward_text, chat_no;
                    for (var intUserIndex2=0; intUserIndex2<internal_users.length; intUserIndex2++) {
                        if (internal_users[intUserIndex2].userid == selectedOpUserId) {
                            forward_id = internal_users[intUserIndex2].id;
                            forward_name = internal_users[intUserIndex2].name;
                            forward_group = selectedGroupId;
                            forward_text = $('#forward-text').val();
                            chat_no = 0;
                        }
                    }
                    //console.log('Operator change - selected operator : ' + selectedOpUserId);
                    if (selectedOpUserId != '') {
                        selectOperatorForForwarding(id, b_id, chat_id, forward_id, forward_name, forward_group, forward_text, chat_no)
                        $('#fwd-button').removeClass('ui-disabled');
                    }
                });
            });
        }
    }
};

/**
 * Highlight the selected operator in the operator forwarding list
 * @param forward_id
 * @param forward_group
 */
ChatDisplayClass.prototype.highlightChosenOperator = function (forward_id, forward_group) {
    $('.operator-invite-row').each(function () {
        $(this).css({background: '#FFFFFF'});
    });
    $('#op-invite-row-' + forward_id + '-' + forward_group).css({background: '#DCF0FF'});
};

/**
 * Create the user control panel - aka the one on the top of the page.
 */
ChatDisplayClass.prototype.createUserControlPanel = function (user_status, myName, myUserId) {
    var userStatusCSS = {'background-repeat': 'no-repeat', 'background-position': 'center'};
    for (var i = 0; i < this.lzm_commonConfig.lz_user_states.length; i++) {
        if (Number(user_status) == this.lzm_commonConfig.lz_user_states[i].index) {
            userStatusCSS['background-image'] = this.addBrowserSpecificGradient('url("' + this.lzm_commonConfig.lz_user_states[i].icon + '")');
            break;
        }
    }

    var userSettingsHtml = '<span class="ui-btn-inner">' +
        '<span class="ui-icon ui-icon-arrow-d ui-icon-shadow"> </span><span class="ui-btn-text" style="margin-left: -7px;">';
    if (myName != '') {
        userSettingsHtml += myName + '&nbsp;';
    } else {
        userSettingsHtml += myUserId + '&nbsp;';
    }
    userSettingsHtml += '</span></span>';

    var mainArticleWidth = $('#content_chat').width();
    var thisUserstatusButton = $('#userstatus-button');
    var thisUsersettingsButton = $('#usersettings-button');
    var thisBlankButton = $('#blank-button');
    var userstatusButtonWidth = 50;
    var usersettingsButtonWidth = 150;
    if (mainArticleWidth > 350) {
        usersettingsButtonWidth = 250;
    } else if (mainArticleWidth > 325) {
        usersettingsButtonWidth = 225;
    } else if (mainArticleWidth > 300) {
        usersettingsButtonWidth = 200;
    } else if (mainArticleWidth > 275) {
        usersettingsButtonWidth = 175;
    }
    var blankButtonWidth = mainArticleWidth - userstatusButtonWidth - usersettingsButtonWidth - 20 - 10;

    thisUserstatusButton.css(userStatusCSS);
    thisUsersettingsButton.html(userSettingsHtml);

    thisUserstatusButton.width(userstatusButtonWidth);
    thisUsersettingsButton.width(usersettingsButtonWidth);
    thisBlankButton.width(blankButtonWidth);

    $('#user-control-panel').trigger('create');
};

/**
 * create and show the user settings menu, when the corresponding button is clicked
 */
ChatDisplayClass.prototype.showUsersettingsMenu = function () {
    $('#userstatus-menu').css('display', 'none');
    this.showUserstatusHtml = false;

    //calculate position
    var headerHeight = $('#header_chat').height();
    var userControlpanelHeight = $('#user-control-panel').height();
    var topOffset = headerHeight + userControlpanelHeight + 18;
    var leftOffset = 78;

    var thisUsersettingsMenu = $('#usersettings-menu');
    var usersettingsMenuHtml = '<table>';
    usersettingsMenuHtml += '<tr><td onclick="manageUsersettings();">' + t('Options') + '</td></tr>' +
        //'<tr><td onclick="stopPolling();">' + t('Stop polling') + '</td></tr>' +
        //'<tr><td onclick="manageTranslations();">' + t('Manage translations') + '</td></tr>' +
        '<tr><td onclick="logout(true);">' + t('Log out') + '</td></tr>';
    usersettingsMenuHtml += '</table>';
    thisUsersettingsMenu.html(usersettingsMenuHtml);
    thisUsersettingsMenu.css({display: 'block', position: 'absolute', top: topOffset + 'px', left: leftOffset + 'px',
        'z-index': '50', background: '#E6E6E6'});
};

/**
 * create and show the user status menu, when the corresponding button is clicked
 */
ChatDisplayClass.prototype.showUserstatusMenu = function (user_status, myName, myUserId) {
    $('#usersettings-menu').css('display', 'none');
    this.showUsersettingsHtml = false;

    //calculate position
    var headerHeight = $('#header_chat').height();
    var userControlpanelHeight = $('#user-control-panel').height();
    var topOffset = headerHeight + userControlpanelHeight + 18;
    var leftOffset = 30;

    var thisUserstatusMenu = $('#userstatus-menu');
    var userstatusMenuHtml = '<table>';
    for (var statusIndex = 0; statusIndex < this.lzm_commonConfig.lz_user_states.length; statusIndex++) {
        if (this.lzm_commonConfig.lz_user_states[statusIndex].index != 2) {
            var momentaryStatusStyle = '';
            if (this.lzm_commonConfig.lz_user_states[statusIndex].index == user_status) {
                momentaryStatusStyle = ' style="background:#DCF0FF"';
            }
            userstatusMenuHtml += '<tr' + momentaryStatusStyle + '><td ' +
                'onclick="setUserStatus(' + this.lzm_commonConfig.lz_user_states[statusIndex].index + ', \'' + myName + '\', \'' + myUserId + '\')">' +
                '&nbsp;<img src="' + this.lzm_commonConfig.lz_user_states[statusIndex].icon + '" width="14px" ' +
                'height="14px">&nbsp;&nbsp;&nbsp;' + t(this.lzm_commonConfig.lz_user_states[statusIndex].text) + '</td></tr>'
        }
    }
    //userstatusMenuHtml += '<tr><td></td></tr>' +
    userstatusMenuHtml += '</table>';
    thisUserstatusMenu.html(userstatusMenuHtml);
    thisUserstatusMenu.css({display: 'block', position: 'absolute', top: topOffset + 'px', left: leftOffset + 'px',
        'z-index': '50', background: '#E6E6E6'});
};

/**
 * set the user status corresponding to the selected value
 * @param statusValue
 */
ChatDisplayClass.prototype.setUserStatus = function (statusValue, myName, myUserId) {
    $('#userstatus-menu').css('display', 'none');
    this.showUserstatusHtml = false;
    this.user_status = statusValue;
    this.createUserControlPanel(this.user_status, myName, myUserId);
};

// ****************************** Display methods called from user actions ****************************** //
/**
 * Hide the operator list for invitations and show the chat window again
 */
ChatDisplayClass.prototype.finishOperatorInvitation = function () {
    this.lzm_chatInputEditor.setHtml('');
    $('#chat').css('display', 'block');
    $('#operator-forward-list').css('display', 'none');
};

/**
 * Hide the operator list and the controls for chatting and show the main chat window again
 */
ChatDisplayClass.prototype.finishChatForward = function () {
    this.showOpInviteList = false;
    this.lzm_chatInputEditor.setHtml();
    $('#invite-operator').css('display', 'none');
    $('#forward-chat').css('display', 'none');
    $('#leave-chat').css('display', 'none');
    $('#chat-action').css('display', 'none');
    $('#chat-title').css('display', 'none');
    $('#operator-forward-list').css('display', 'none');
    $('#chat-table').css('display', 'block');
    $('#chat-buttons').css('display', 'none');
};

/**
 * Show/hide the neccessary controls when leaving a chat
 */
ChatDisplayClass.prototype.finishLeaveChat = function () {
    $('#chat-table').css('display', 'block');
    $('#chat-logo').css('display', 'none');
    $('#chat-progress').css('display', 'none');
    $('#chat-action').css('display', 'none');
    $('#chat-title').css('display', 'none');
    $('#chat-buttons').css('display', 'none');
};

/**
 * Clear the visitor information and hide/show the neccessary parts for chatting with another operator
 */
ChatDisplayClass.prototype.showInternalChat = function (internal_departments, internal_users, external_users, chats, chatObject, thisUser, global_errors, chosen_profile, loadedValue) {
    var name = '';
    if (typeof thisUser.name != 'undefined') {
        name = thisUser.name;
    } else {
        name = thisUser.userid;
    }
    $('#visitor-info').html('<div id="visitor-info-headline"><h3>' + t('Visitor information') + '</h3></div>' +
        '<div id="visitor-info-headline2"></div>').trigger('create');

    $('#chat').css('display', 'block');
    $('#chat-logo').css('display', 'none');
    $('#errors').css('display', 'none');
    if (!this.showOpInviteList) {
        $('#operator-forward-list').css('display', 'none');
    }
    $('#chat-input-body').css('display', 'block');

    this.createChatHtml(chats, chatObject, thisUser, internal_users, external_users, thisUser.id);
    this.createActiveChatPanel(external_users, internal_users, internal_departments, chatObject, false);


    $('#chat-progress').css('display', 'block');
    $('#chat-action').css('display', 'block');
    $('#active-chat-panel').css('display', 'block');

    var thisChatTitle = $('#chat-title');
    var thisChatButtons = $('#chat-buttons');
    var chatButtonsHtml = '<div style="margin: 7px 0px;">';
    chatButtonsHtml += this.createInputControlPanel();
    chatButtonsHtml += '</div>';
    thisChatTitle.html('').trigger('create').css('display', 'none');
    thisChatButtons.html(chatButtonsHtml).trigger('create').css('display', 'block');

    $('.lzm-button').mouseenter(function() {
        $(this).css('background-image', $(this).css('background-image').replace(/linear-gradient\(.*\)/,'linear-gradient(#f6f6f6,#e0e0e0)'));
    });
    $('.lzm-button').mouseleave(function() {
        $(this).css('background-image', $(this).css('background-image').replace(/linear-gradient\(.*\)/,'linear-gradient(#ffffff,#f1f1f1)'));
    });
};

ChatDisplayClass.prototype.updateShowVisitor = function(external_users) {
    var rtValue = false;
    if (typeof this.infoUser.id != 'undefined' && this.infoUser.id != '') {
        for (var i=0; i<external_users.length; i++) {
            if (this.infoUser.id == external_users[i].id) {
                var tmpInfoUser = external_users[i];
                for (var key in this.infoUser) {
                    if ((typeof tmpInfoUser[key] == 'boolean') || (typeof tmpInfoUser[key] == 'number') || (typeof tmpInfoUser[key] == 'string') && tmpInfoUser[key] != '') {
                        this.infoUser[key] = tmpInfoUser[key];
                        rtValue = true;
                    }
                }
                break;
            }
        }
    }
    return rtValue;
};

ChatDisplayClass.prototype.createVisitorInformation = function(internal_users, thisUser) {
    var visitorInfoHtml = '';
    if (typeof thisUser.id != 'undefined' && thisUser.id != '' && typeof thisUser.b_id != 'undefined') {

        var thisChatQuestion = '';
        var thisChatId = '';
        if (typeof thisUser.b_chat != 'undefined') {
            thisChatId = thisUser.b_chat.id;
            thisChatQuestion = (typeof thisUser.b_chat.eq != 'undefined') ? thisUser.b_chat.eq : '';
        }
        var visitorName = this.createVisitorStrings('cname', thisUser);
        var visitorEmail = this.createVisitorStrings('cemail', thisUser);
        var visitorCompany = this.createVisitorStrings('ccompany', thisUser);
        var visitorSearchString = this.createVisitorStrings('ss', thisUser);
        var lastVisitedDate = new Date(thisUser.vl * 1000);
        var visitorLastVisit = this.lzm_commonTools.getHumanDate(lastVisitedDate, 'full', this.userLanguage);
        visitorInfoHtml += '<table id="visitor-info-table" style="width: 100%;">';
        var visitorStatus = t('<!--status_style_begin-->Online<!--status_style_end-->',[
            ['<!--status_style_begin-->','<span style="color:#00aa00; font-weight: bold;">'],['<!--status_style_end-->','</span>']
        ]);
        if (typeof thisUser.is_active != 'undefined' && thisUser.is_active == false) {
            visitorStatus = t('<!--status_style_begin-->Offline<!--status_style_end-->',[
                ['<!--status_style_begin-->','<span style="color:#aa0000; font-weight: bold;">'],['<!--status_style_end-->','</span>']
            ]);
        }
        var visitorInfoArray = [
            {title: t('Status'), content: visitorStatus},
            {title: t('Name'), content: visitorName},
            {title: t('Country'), content: '<span style="background: url(\'./php/common/flag.php?cc=' + thisUser.ctryi2 + '\') left no-repeat; padding-left: 23px;">' + thisUser.ctryi2 + '</span>'},
            {title: t('Language'), content: thisUser.lang},
            {title: t('Region'), content: thisUser.region},
            {title: t('City'), content: thisUser.city},
            {title: t('Search string'), content: visitorSearchString},
            {title: t('Host'), content: thisUser.ho},
            {title: t('IP address'), content: thisUser.ip},
            {title: t('Email'), content: visitorEmail},
            {title: t('Company'), content: visitorCompany},
            {title: t('Browser'), content: thisUser.bro},
            {title: t('Resolution'), content: thisUser.res},
            {title: t('Operating system'), content: thisUser.sys},
            {title: t('Last visit'), content: visitorLastVisit},
            {title: t('ISP'), content: thisUser.isp},
            {title: t('Question'), content: thisChatQuestion}
        ];
        if (typeof thisUser.b_chat != 'undefined' && typeof thisUser.b_chat.dcp != 'undefined') {
            var thisChatPartner = thisUser.b_chat.dcp;
            for (var i=0; i<internal_users.length; i++) {
                if (internal_users[i].id == thisUser.b_chat.dcp) {
                    thisChatPartner = internal_users[i].name;
                    break;
                }
            }
            visitorInfoArray.push({title: t('Chating with'), content: thisChatPartner});
        }
        var lineStyle, lineCounter = 0;
        for (var j=0; j<visitorInfoArray.length; j++) {
            if (lineCounter % 2 == 0) {
                lineStyle = ' style="background-color: #f8f8ff;"';
            } else {
                lineStyle = '';
            }
            visitorInfoHtml += '<tr' + lineStyle + '>' +
                '<th style="text-align: left; width: 100px;" nowrap>' + visitorInfoArray[j].title + '</th>' +
                '<td style="text-align: left;">' + visitorInfoArray[j].content + '</td>' +
                '</tr>';
            lineCounter++;
        }
        visitorInfoHtml += '</table>';

        $('#visitor-info-body').html(visitorInfoHtml).css(this.VisitorInfoBodyCss);
    }
};

/**
 * Show the information about the selected visitor
 * @param thisUser
 */
ChatDisplayClass.prototype.showVisitorInformation = function (internal_users, thisUser, visibleTab) {
    this.ShowVisitorInfo = true;
    this.ShowVisitorId = thisUser.id;
    this.VisitorListCreated = false;
    $('#visitor-list-table').remove();
    thisUser = (typeof this.infoUser.id != 'undefined' && this.infoUser.id != '') ? this.infoUser : thisUser;
    visibleTab = (typeof visibleTab != 'undefined') ? visibleTab : 'info';

    var visitorInfoHtml = '<div id="visitor-info-headline"><h3>' + t('Visitor information') + '</h3></div>' +
        '<div id="visitor-info-headline2">';
    var buttonCss = ' height: 14px; position: absolute; padding: 3px 5px; text-align: center; font-size: 11px; ' +
        'overflow: hidden; cursor: pointer; border: 1px solid #ccc; border-radius: 4px; font-weight: normal;';
    var infoButtonCss, historyButtonCss, infoOnclickAction = '', historyOnclickAction = '';
    if (visibleTab == 'info') {
        infoButtonCss = buttonCss + ' background:#5285AF; color:#FFF; text-shadow: 0 0px #fff; ' + this.addBrowserSpecificGradient('text','darkViewSelect') + ';';
        historyButtonCss = buttonCss + ' background:#DDD; color:#000; ' + this.addBrowserSpecificGradient('text','') + ';';
        historyOnclickAction = ' onclick="showVisitorInformation(\'history\');"';
    } else {
        historyButtonCss = buttonCss + ' background:#5285AF; color:#FFF; text-shadow: 0 0px #fff; ' + this.addBrowserSpecificGradient('text','darkViewSelect') + ';';
        infoButtonCss = buttonCss + ' background:#DDD; color:#000; ' + this.addBrowserSpecificGradient('text','') + ';';
        infoOnclickAction = ' onclick="showVisitorInformation(\'info\');"';
    }
    var testLengthDiv = $('#test-length-div');
    var infoButtonHtml = '<div style="left: 2px; top: 2px;' + infoButtonCss + '"' + infoOnclickAction + '>' + t('Details') + '</div>'
    testLengthDiv.html(infoButtonHtml).trigger('create');
    var historyButtonLeft = testLengthDiv.children('div').width() + 18;
    var historyButtonHtml = '<div style="left: ' + historyButtonLeft + 'px; top: 2px;' + historyButtonCss + '"' + historyOnclickAction + '>' + t('History') + '</div>';
    visitorInfoHtml += infoButtonHtml + historyButtonHtml;
    visitorInfoHtml += '</div>' +
        '<div id="visitor-info-body"></div>' +
        '<div id="visitor-browser-history-body"></div>' +
        '<div id="visitor-info-footline">' +
        '<span id="cancel-visitorinfo" class="chat-button-line chat-button-left chat-button-right" ' +
        'style="margin-left: 4px; padding-left: 10px; padding-right: 10px; cursor:pointer; background-image: ' +
        this.addBrowserSpecificGradient('') + ';" onclick="toggleVisitorInfo(\'' + this.infoCaller + '\');">&nbsp;' + t('Close') + '&nbsp;</span>' +
        //'<a href="#" onclick="toggleVisitorInfo(\'' + this.infoCaller + '\');" data-role="button" id="cancel-visitorinfo" data-mini="true" data-inline="true">&nbsp;' + t('Close') + '&nbsp;</a>' +
        '</div>';
    var thisVisitorInfo = $('#visitor-info');
    var visitorInfoWidth = thisVisitorInfo.width();


    thisVisitorInfo.html(visitorInfoHtml).trigger('create');
    $('#visitor-info-headline').css(this.VisitorInfoHeadlineCss);
    //console.log(this.VisitorInfoHeadline2Css);
    delete this.VisitorInfoHeadline2Css['line-height'];
    delete this.VisitorInfoHeadline2Css['font-size'];
    $('#visitor-info-headline2').css(this.VisitorInfoHeadline2Css);
    $('#visitor-info-footline').css(this.VisitorInfoFootlineCss);

    this.createVisitorInformation(internal_users, thisUser);
    this.createBrowserHistory(thisUser);
    if (visibleTab == 'info') {
        $('#visitor-info-body').css({display: 'block'});
        $('#visitor-browser-history-body').css({display: 'none'});
    } else {
        $('#visitor-info-body').css({display: 'none'});
        $('#visitor-browser-history-body').css({display: 'block'});
    }
};

/**
 * Show the needed controls for chatting in an already active chat
 * @param thisUser
 * @param external_forwards
 */
ChatDisplayClass.prototype.showActiveVisitorChat = function (thisUser, external_forwards, chatObject) {
    this.showOpInviteList = false;
    var thisChatAction = $('#chat-action');
    var thisChatInput = $('#chat-input-body');
    var thisChatProgress = $('#chat-progress');
    var thisChatTable = $('#chat-table');
    var thisChatTitle = $('#chat-title');
    var thisChatButtons = $('#chat-buttons');
    var thisChatLogo = $('#chat-logo');
    thisChatTitle.html('').trigger('create').css('display', 'none');

    thisChatTable.css('display', 'block');
    thisChatAction.css('display', 'block');
    thisChatInput.css('display', 'block');
    thisChatProgress.css('display', 'block');
    thisChatLogo.css('display', 'none');
    $('#active-chat-panel').css({display: 'block'});
    var openChatHtmlString = '';
    if (typeof chatObject[thisUser.id + '~' + thisUser.b_id] != 'undefined') {
    openChatHtmlString += '<div style="margin: 7px 0px;">';
    openChatHtmlString += this.createInputControlPanel() +
        '<span class="chat-button-line chat-button-left chat-button-right" id="show-visitor-info" title="' + t('Show information') + '" onclick="toggleVisitorInfo(\'#chat-table\', \'' +
        this.thisUser.id + '\')"' +
        'style=\'padding: 3px 20px 3px 20px; cursor:pointer; margin-left: 4px; margin-top: -3px;' +
        'background-image: ' + this.addBrowserSpecificGradient('url("img/215-info.png")') + '; background-repeat: no-repeat; background-position: center;\'>' +
        '</span>'/* +
        '<span class="chat-button-line chat-button-left chat-button-right" id="forward-chat" title="' + t('Forward') +
        '" style=\'padding: 3px 20px 3px 20px; cursor:pointer; margin-left: 4px; margin-top: -3px; ' +
        'background-image: ' + this.addBrowserSpecificGradient('url("img/291-switch_to_employees.png")') + '; background-repeat: no-repeat; background-position: center;\'>' +
        '</span>'*/;
    openChatHtmlString += '</div>';
    }
    thisChatButtons.html(openChatHtmlString).trigger("create");
    thisChatButtons.css('display', 'block');

    $('.lzm-button').mouseenter(function() {
        $(this).css('background-image', $(this).css('background-image').replace(/linear-gradient\(.*\)/,'linear-gradient(#f6f6f6,#e0e0e0)'));
    });
    $('.lzm-button').mouseleave(function() {
        $(this).css('background-image', $(this).css('background-image').replace(/linear-gradient\(.*\)/,'linear-gradient(#ffffff,#f1f1f1)'));
    });
};

/**
 * Show the needed controls for an inactive chat, like accept, decline or invite
 * @param thisUser
 * @param external_forwards
 * @param internal_users
 * @param chatObject
 * @param id
 * @param b_id
 * @param chat_id
 */
ChatDisplayClass.prototype.showPassiveVisitorChat = function (thisUser, external_forwards, internal_users, chatObject,
                                                              id, b_id, chat_id, freeToChat) {
    this.lzm_chatInputEditor.setHtml('');
    this.showOpInviteList = false;
    var thisChatAction = $('#chat-action');
    var thisChatInput = $('#chat-input-body');
    var thisChatProgress = $('#chat-progress');
    var thisChatTable = $('#chat-table');
    var thisChatTitle = $('#chat-title');
    var thisChatButtons = $('#chat-buttons');
    var thisChatLogo = $('#chat-logo');

    thisChatAction.css('display', 'none');
    thisChatInput.css('display', 'none');
    thisChatProgress.css('display', 'block');
    thisChatLogo.css('display', 'none');
    thisChatTitle.css('display', 'none');
    $('#active-chat-panel').css({display: 'block'});

    // fill the external user window with content
    var chatForwardingHtmlString = '';
    for (var extFwdIndex = 0; extFwdIndex < external_forwards.length; extFwdIndex++) {
        if (external_forwards[extFwdIndex].u == id + '~' + b_id) {
            var fwdByName = '';
            for (var intUserIndex = 0; intUserIndex < internal_users.length; intUserIndex++) {
                if (external_forwards[extFwdIndex].s == internal_users[intUserIndex].id) {
                    fwdByName = internal_users[intUserIndex].name;
                    break;
                }
            }
            chatForwardingHtmlString += '<p>' + t('Forwarded by <!--fwd_operator-->', [
                ['<!--fwd_operator-->', fwdByName]
            ]);
            if (external_forwards[extFwdIndex].t != '') {
                chatForwardingHtmlString += ' ' + t('with comment<br><!--fwd_comment-->', [
                    ['<!--fwd_comment-->', external_forwards[extFwdIndex].t]
                ]) + '</p>';
            }

            thisChatTitle.html('').trigger('create'); // FIXME: war chatForwardingHtmlString
            thisChatTitle.css({display: 'none'});  // FIXME: War block
            break;
        }
    }
    var noOpenChatHtmlString = '';
    //console.log(id + '~' + b_id);
    if (typeof chatObject[id + '~' + b_id] != 'undefined') {
        var disabledClass = '';
        if (chatObject[id + '~' + b_id].status == 'left' || chatObject[id + '~' + b_id].status == 'declined') {
            disabledClass = 'ui-disabled ';
        }
        noOpenChatHtmlString += '<div style="margin: 7px 0px;">';
        noOpenChatHtmlString += '<span class="chat-button-line chat-button-left chat-button-right" id="show-visitor-info" title="' + t('Show information') + '" onclick="toggleVisitorInfo(\'#chat-table\', \'' +
            this.thisUser.id + '\')"' +
            'style=\'margin-left: 4px; padding: 3px 20px 3px 20px; cursor:pointer; background-image: ' + this.addBrowserSpecificGradient('url("img/215-info.png")') + '; background-repeat: no-repeat; background-position: center;\'>' +
            '</span>' +
            '<span class="' + disabledClass + 'chat-button-line chat-button-left chat-button-right" id="accept-chat" title="' + t('Accept') + '" style="margin-left: 4px; padding-left: 10px; padding-right: 10px; cursor:pointer; background-image: ' +
            this.addBrowserSpecificGradient('') + ';">' +
            '<span style="background-image: url(\'img/200-ok2.png\'); background-repeat: no-repeat; background-position: left center; padding-left: 20px;">' + t('Accept') + '</span>' +
            '</span>' +
            '<span class="' + disabledClass + 'chat-button-line chat-button-left chat-button-right" id="decline-chat" title="' + t('Decline') +
            '" style=\'margin-left: 4px; padding: 3px 20px 3px 20px; cursor:pointer; background-image: ' + this.addBrowserSpecificGradient('url("img/201-delete2.png")') + '; background-repeat: no-repeat; background-position: center;\'>' +
            '</span>'/* +
            '<span class="chat-button-line chat-button-left chat-button-right" id="forward-chat" title="' + t('Forward') +
            '" style=\'margin-left: 4px; padding: 3px 20px 3px 20px; cursor:pointer; background-image: ' + this.addBrowserSpecificGradient('url("img/291-switch_to_employees.png")') + '; background-repeat: no-repeat; background-position: center;\'>' +
            '</span>'*/;
        noOpenChatHtmlString += '</div>';
        //console.log(noOpenChatHtmlString);
        thisChatButtons.html(noOpenChatHtmlString).trigger("create");
        var thisForwardChat = $('#forward-chat');
        var thisSendButton = $('#send-btn');
        var thisLeaveChat = $('#leave-chat');
        thisChatAction.css('display', 'none');
        thisChatProgress.css('display', 'block');
        thisChatButtons.css('display', 'block');
    } else {
        thisChatButtons.html(noOpenChatHtmlString).trigger("create");
    }

    $('.lzm-button').mouseenter(function() {
        $(this).css('background-image', $(this).css('background-image').replace(/linear-gradient\(.*\)/,'linear-gradient(#f6f6f6,#e0e0e0)'));
    });
    $('.lzm-button').mouseleave(function() {
        $(this).css('background-image', $(this).css('background-image').replace(/linear-gradient\(.*\)/,'linear-gradient(#ffffff,#f1f1f1)'));
    });
};

ChatDisplayClass.prototype.showExternalChat = function () {
    var thisInviteOperator = $('#invite-operator');
    var thisForwardChat = $('#forward-chat');
    var thisLeaveChat = $('#leave-chat');
    $('#decline-chat').css('display', 'none');
    $('#accept-chat').css('display', 'none');
    thisLeaveChat.css('display', 'block');
    thisInviteOperator.css('display', 'block');
    thisForwardChat.css('display', 'block');
};

ChatDisplayClass.prototype.showForwardMessages = function (type) {
    var thisInviteOpHeadline = $('#op-invite-headline');
    if (type == 'invite') {
        thisInviteOpHeadline.html(t('Invite operator to chat'));
    } else {
        thisInviteOpHeadline.html(t('Forward chat to operator'));
    }
    $('#operator-forward-list').css('display', 'block');
    $('#chat-table').css('display', 'none');
};

/**
 * Show/Hide the neccessary controls when refused a chat
 */
ChatDisplayClass.prototype.showRefusedChat = function (internal_departments, internal_users, external_users, chats, chatObject, thisUser, global_errors, chosen_profile) {
    this.createActiveChatPanel(external_users, internal_users, internal_departments, chatObject, false);
    this.createHtmlContent(internal_departments, internal_users, external_users, chats, chatObject, thisUser,
        global_errors, chosen_profile, thisUser.id + '~' + thisUser.b_id);
    $('#visitor-info').html('');
    $('#chat-action').css('display', 'block');
    $('#chat-progress').css('display', 'block');
    //this.lzm_chatInputEditor.setHtml('');
};

/**
 * Show/Hide the neccessary controls when left a chat
 */
ChatDisplayClass.prototype.showLeaveChat = function (internal_departments, internal_users, external_users, chats, chatObject, thisUser, global_errors, chosen_profile) {
    this.createActiveChatPanel(external_users, internal_users, internal_departments, chatObject, false);
    this.createHtmlContent(internal_departments, internal_users, external_users, chats, chatObject, thisUser,
        global_errors, chosen_profile, thisUser.id + '~' + thisUser.b_id);
    $('#visitor-info').html('');

    $('#chat-action').css('display', 'none');
    $('#chat-title').css('display', 'none');
};

// ****************************** More common tools ****************************** //
/**
 * Catch enter and control+enter buttons pressed in the textareas and act accordingly
 * @param e
 * @return {Boolean}
 */
ChatDisplayClass.prototype.catchEnterButtonPressed = function (e) {
    var thisChatInput = $('#chat-input');
    if (e.which == 13 || e.keyCode == 13) {
        sendChat();
        return false;
    }
    if (e.which == 10 || e.keyCode == 10) {
        var tmp = thisChatInput.val();
        thisChatInput.val(tmp + '\n');
    }
    return true;
};

/***********************************************************************************************************************************************************************/
/**
 * Create the canned resources view
 * @param resources
 * @param caller
 */
ChatDisplayClass.prototype.createQrdTree = function(resources, caller, chatPartner, external_users, internal_users, internal_departments) {
    var thisClass = this;
    var chatPartnerName = '';
    if (typeof chatPartner != 'undefined') {
        if (chatPartner.indexOf('~') != -1) {
            for (i=0; i<external_users.length; i++) {
                if (chatPartner.split('~')[0] == external_users[i].id) {
                    for (j=0; j<external_users[i].b.length; j++) {
                        if (chatPartner.split('~')[1] == external_users[i].b[j].id) {
                            chatPartnerName = (external_users[i].b[j].cname != '') ?
                                external_users[i].b[j].cname : external_users[i].unique_name;
                        }
                    }
                    break;
                }
            }
        } else {
            if (chatPartner == 'everyoneintern') {
                chatPartnerName = t('All operators');
            } else {
                for (i=0; i<internal_users.length; i++) {
                    if (chatPartner == internal_users[i].id) {
                        chatPartnerName = internal_users[i].name;
                        break;
                    }
                }
                if (chatPartnerName == '') {
                    for (i=0; i<internal_departments.length; i++) {
                        chatPartnerName = internal_departments[i].id;
                        break;
                    }
                }
            }
        }
    } else {
        chatPartner = '';
    }
    if (chatPartnerName.length > 13) {
        chatPartnerName = chatPartnerName.substr(0,10) + '...';
    }
    resources.sort(thisClass.resourceSortFunction);
    var allResources = resources;
    var thisQrdTree = $('#qrd-tree');

    var tmpResources = [], rank = 1, topLayerResource, i, counter = 0, alreadyUsedIds = [];
    for (i=0; i<resources.length; i++) {
        resources[i].ti = resources[i].ti.replace(/%%_Files_%%/, t('Files')).replace(/%%_External_%%/, t('External'));
        if (resources[i].ra == 0) {
            topLayerResource = resources[i];
            //console.log(resources[i].rid);
        } else {
            tmpResources.push(resources[i]);
        }
    }
    resources = tmpResources;
    var onclickAction = '';
    if  (caller == 'view-select-panel') {
        onclickAction = 'onclick="handleResourceClickEvents(\'' + topLayerResource.rid + '\')"';
    }
    var resourceHtml = '<div id="resource-' + topLayerResource.rid + '" class="resource-div" style="margin: 4px 0px;">' +
        '<span id="resource-' + topLayerResource.rid + '-open-mark" style=\'display: inline-block; width: 7px; ' +
        'height: 7px; border: 1px solid #aaa; background-color: #f1f1f1; ' +
        thisClass.addBrowserSpecificGradient('background-image: url("img/minus.png")') + '; ' +
        'background-position: center; background-repeat: no-repeat; margin-right: 4px; cursor: pointer;\' ' +
        'onclick="handleResourceClickEvents(\'' + topLayerResource.rid + '\')"></span>' +
        '<span style=\'background-image: url("' + thisClass.getResourceIcon(topLayerResource.ty) + '"); ' +
        'background-position: left center; background-repeat: no-repeat; padding: 2px;\'>' +
        '<span style="padding-left: 20px; cursor: pointer;" ' + onclickAction + '>' +
        topLayerResource.ti + '</span>' +
        '</span></div><div id="folder-' + topLayerResource.rid + '" style="display: none;"></div>';

    var qrdTreeHtml = '<div id="qrd-tree-headline"><h3>' + t('Resources') + '</h3></div>' +
        '<div id="qrd-tree-headline2">' +
        '<label for="search-qrd" data-role="none">' + t('Search:') + '</label>' +
        '<input type="text" id="search-qrd" data-role="none" style="margin: 2px 10px;" />' +
        '</div>' +
        '<div id="qrd-tree-body" style="text-align: left;">' +
        resourceHtml +
        '</div>' +
        '<div id="qrd-tree-footline">';
    if (caller == 'view-select-panel') {
        qrdTreeHtml += /*'<span id="add-qrd" class="ui-disabled chat-button-line chat-button-left chat-button-right qrd-change-buttons" ' +
            'style="margin-left: 4px; padding-left: 10px; padding-right: 10px; cursor:pointer; background-image: ' +
            this.addBrowserSpecificGradient('') + ';" onclick="addQrd();">' + t('') + '</span>' +
            '<span id="edit-qrd" class="ui-disabled chat-button-line chat-button-left chat-button-right qrd-change-buttons" ' +
            'style="margin-left: 4px; padding-left: 10px; padding-right: 10px; cursor:pointer; background-image: ' +
            this.addBrowserSpecificGradient('') + ';" onclick="editQrd();">' + t('') + '</span>' +*/
            '<span id="preview-qrd" class="ui-disabled chat-button-line chat-button-left chat-button-right qrd-change-buttons" ' +
            'style=\'margin-left: 4px; padding-left: 10px; padding-right: 10px; cursor:pointer; background-image: ' +
            this.addBrowserSpecificGradient('url("img/078-preview.png")') + '; background-position: center; background-repeat: no-repeat;\' onclick="previewQrd();"></span>'/* +     ' + t('Preview') + '
            '<span id="delete-qrd" class="ui-disabled chat-button-line chat-button-left chat-button-right qrd-change-buttons" ' +
            'style="margin-left: 4px; padding-left: 10px; padding-right: 10px; cursor:pointer; background-image: ' +
            this.addBrowserSpecificGradient('') + ';" onclick="deleteQrd();">' + t('') + '</span>'*/;
    } else {
        qrdTreeHtml += '<span id="send-qrd-preview" class="ui-disabled chat-button-line chat-button-left chat-button-right qrd-change-buttons" ' +
            'style="margin-left: 4px; padding-left: 10px; padding-right: 10px; cursor:pointer; background-image: ' +
            this.addBrowserSpecificGradient('') + ';" onclick="sendQrdPreview(\'' + chatPartner + '\');">' +
            t('To <!--chat-partner-->',[['<!--chat-partner-->',chatPartnerName]]) + '</span>' +
            '<span id="preview-qrd" class="ui-disabled chat-button-line chat-button-left chat-button-right qrd-change-buttons" ' +
            'style=\'margin-left: 4px; padding-left: 10px; padding-right: 10px; cursor:pointer; background-image: ' +
            this.addBrowserSpecificGradient('url("img/078-preview.png")') + '; background-position: center; background-repeat: no-repeat;\' onclick="previewQrd(\'' + chatPartner + '\');"></span>' +
            '<span id="cancel-qrd" class="chat-button-line chat-button-left chat-button-right" ' +
            'style="margin-left: 4px; padding-left: 10px; padding-right: 10px; cursor:pointer; background-image: ' +
            this.addBrowserSpecificGradient('') + ';" onclick="cancelQrd();">' + t('Cancel') + '</span>';
    }
    qrdTreeHtml += '</div>';
    thisQrdTree.html(qrdTreeHtml).trigger('create');
    $('#search-qrd').keypress(function(e) {
        if (e.which == 13 || e.keycode == 13 || e.charCode == 13) {
            //console.log('Enter pressed');
            thisClass.highlightSearchResults(allResources, true);
        }
    });

    while (resources.length > 0 && counter < 1000) {
        tmpResources = [];
        alreadyUsedIds = [];
        for (i=0; i<resources.length; i++) {
            if (rank == resources[i].ra) {
                onclickAction = 'onclick="handleResourceClickEvents(\'' + resources[i].rid + '\')"';
                resourceHtml = '<div id="resource-' + resources[i].rid + '" class="resource-div" ' +
                    'style="padding-left: ' + (20 * resources[i].ra) + 'px; margin: 4px 0px;">';
                if (resources[i].ty == 0) {
                    resourceHtml += '<span id="resource-' + resources[i].rid + '-open-mark" style=\'display: inline-block; width: 7px; ' +
                        'height: 7px; border: 1px solid #aaa; background-color: #f1f1f1; ' +
                        thisClass.addBrowserSpecificGradient('background-image: url("img/plus.png")') + '; ' +
                        'background-position: center; background-repeat: no-repeat; margin-right: 4px; cursor: pointer;\'';
                    if (resources[i].ty == 0) {
                        resourceHtml += ' onclick="handleResourceClickEvents(\'' + resources[i].rid + '\');"';
                    }
                    resourceHtml += '></span>';
                } else {
                    resourceHtml += '<span style="display: inline-block; width: 9px; height: 9px; margin-right: 4px;"></span>';
                }
                resourceHtml += '<span style=\'background-image: url("' + thisClass.getResourceIcon(resources[i].ty) + '"); ' +
                    'background-position: left center; background-repeat: no-repeat; padding: 2px;\'>' +
                    '<span class="qrd-title-span" style="padding-left: 20px; cursor: pointer;" ' + onclickAction + '>' +
                    resources[i].ti + '</span>' +
                    '</span></div>';
                if (resources[i].ty == 0) {
                    resourceHtml += '<div id="folder-' + resources[i].rid + '" style="display: none;"></div>';
                }
                $('#folder-' + resources[i].pid).append(resourceHtml);
                alreadyUsedIds.push(resources[i].rid);
            }
        }
        for (i=0; i<resources.length; i++) {
            if ($.inArray(resources[i].rid, alreadyUsedIds) == -1) {
                tmpResources.push(resources[i]);
            }
        }
        rank++;
        resources = tmpResources;
        //console.log(resources);
        counter++;
    }

    for (i=0; i<allResources.length; i++) {
        if ($('#folder-' + allResources[i].rid).html() == "") {
            $('#resource-' + allResources[i].rid + '-open-mark').css({background: 'none', border: 'none', width: '9px', height: '9px'})
        }
    }

    $('#qrd-tree-headline').css(thisClass.QrdTreeHeadlineCss);
    $('#qrd-tree-headline2').css(thisClass.QrdTreeHeadline2Css);
    $('#qrd-tree-body').css(thisClass.QrdTreeBodyCss);
    $('#qrd-tree-footline').css(thisClass.QrdTreeFootlineCss);

    for (i=0; i<thisClass.openedResourcesFolder.length; i++) {
        handleResourceClickEvents(thisClass.openedResourcesFolder[i], true);
    }
};

ChatDisplayClass.prototype.highlightSearchResults = function(resources, isNewSearch) {
    if (isNewSearch) {
        var searchString = $('#search-qrd').val().toLowerCase();
        var i, j;
        this.qrdSearchResults = [];
        for (i=0; i<resources.length; i++) {
            if (resources[i].text.toLowerCase().indexOf(searchString) != -1 ||
                resources[i].ti.toLowerCase().indexOf(searchString) != -1) {
                this.qrdSearchResults.push(resources[i]);
            }
        }
    }
    //console.log(this.qrdSearchResults);

    if (isNewSearch) {
        var openedResourceFolders = this.openedResourcesFolder;
        $('.resource-div').css({'background-color': '#FFFFFF', color: '#000000'});
        for (i=0; i<openedResourceFolders.length; i++) {
            openOrCloseFolder(openedResourceFolders[i], false);
        }
    }
    for (i=0; i<this.qrdSearchResults.length; i++) {
        $('#resource-' + this.qrdSearchResults[i].rid).css({'background-color': '#FFFFC6', color: '#000000', 'border-radius': '4px'});
        var parentId = this.qrdSearchResults[i].pid, counter = 0;
        if (isNewSearch) {
            while (parentId != 0 && counter < 1000) {
                for (j=0; j<resources.length; j++) {
                    if(resources[j].ty == 0 && resources[j].rid == parentId) {
                        openOrCloseFolder(resources[j].rid, true);
                        parentId = resources[j].pid;
                    }
                }
                counter++;
            }
        }
    }
};

ChatDisplayClass.prototype.previewQrd = function(resource, chatPartner) {
    var resourceTitle, resourceText;
    switch(Number(resource.ty)) {
        case 1:
            resourceTitle = t('HTML Resource: <!--resource_title-->',[['<!--resource_title-->',resource.ti]]);
            resourceText = resource.text;
            break;
        case 2:
            resourceTitle = t('URL: <!--resource_title-->',[['<!--resource_title-->',resource.ti]]);
            var resourceLink = '<a href="#" class="lz_chat_link" onclick="openLink(\'' + resource.text + '\')" ' +
                'style="line-height: 16px;" data-role="none">' + resource.text + '</a>';
            resourceText = '<p>' + t('Title: <!--resource_title-->',[['<!--resource_title-->',resource.ti]]) + '</p>' +
                '<p>' + t('URL: <!--resource_text-->',[['<!--resource_text-->',resourceLink]]) + '</p>';
            break;
        default:
            resourceTitle = t('File: <!--resource_title-->',[['<!--resource_title-->',resource.ti]]);
            resourceText = '<p>' + t('File name: <!--resource_title-->',[['<!--resource_title-->',resource.ti]]) + '</p>';
            break;
    }
    var qrdPreviewHtml = '<div id="qrd-preview"><div id="qrd-preview-headline">' +
        resourceTitle +
        '</div><div id="qrd-preview-body">' +
        // HTML Resource textarea
        '<div>' +
        resourceText +
        '</div>' +
        '</div>' +
        '<div id="qrd-preview-footline">' +
        '<span id="cancel-preview-qrd" class="chat-button-line chat-button-left chat-button-right" ' +
        'style="margin-left: 4px; padding-left: 10px; padding-right: 10px; cursor:pointer; background-image: ' +
        this.addBrowserSpecificGradient('') + ';">' + t('Close') + '</span>' +
        '</div>' +
        '</div>';

    var qrdTreeBody = $('#qrd-tree-body');
    qrdTreeBody.append(qrdPreviewHtml).trigger('create');

    var bodyWidth = qrdTreeBody.width();
    var bodyHeight = qrdTreeBody.height();
    var thisLeft = Math.floor(0.025 * bodyWidth) + $('#chat').position().left + $('#qrd-tree').position().left + 5;
    var thisTop = Math.floor(0.025 * bodyHeight) + $('#chat').position().top + $('#qrd-tree').position().top + 54;
    var thisWidth = Math.floor(0.95 * bodyWidth);
    var thisHeight = Math.floor(0.95 * bodyHeight);

    var thisCss = {
        position: 'fixed', 'z-index': '10',
        left: thisLeft+'px', top: thisTop+'px', width: thisWidth+'px', height: thisHeight+'px',
        border: '2px solid #ccc', 'border-radius': '4px',
        'box-shadow': '10px 10px 5px #888888'
    };
    var thisHeadlineCss = {
        position: 'absolute', left: '0px', top: '0px', 'border-bottom': '1px solid #ccc',
        width: (thisWidth - 5)+'px', height: '22px',
        'border-top-left-radius': '4px', 'border-top-right-radius': '4px',
        padding: '4px 0px 0px 5px', 'font-weight': 'bold', 'text-shadow': 'none',
        'background-color': '#f5f5f5'
    };
    var thisBodyCss = {
        position: 'absolute', left: '0px', top: '27px',
        width: (thisWidth - 10)+'px', height: (thisHeight - 27 - 28 - 8)+'px',
        padding: '4px 5px 4px 5px', 'text-shadow': 'none',
        'background-color': '#FFFFC6'
    };
    var thisFootlineCss = {
        position: 'absolute', left: '0px', top: (thisHeight - 28)+'px', 'border-top': '1px solid #ccc',
        width: (thisWidth - 4)+'px', height: '21px', 'text-align': 'right',
        padding: '6px 2px 0px 2px',
        'border-bottom-left-radius': '4px', 'border-bottom-right-radius': '4px',
        'background-color': '#f5f5f5'
    };
    $('#qrd-preview').css(thisCss);
    $('#qrd-preview-headline').css(thisHeadlineCss);
    $('#qrd-preview-body').css(thisBodyCss);
    $('#qrd-preview-footline').css(thisFootlineCss);
};

ChatDisplayClass.prototype.editQrd = function(resource) {
    var qrdEditHtml = '<div id="qrd-edit">' +
        '<div id="qrd-edit-headline">' + t('Edit Resource') + '</div>' +
        '<div id="qrd-edit-body">' +
        // Title input
        '<div class="qrd-edit-html-resource qrd-edit-folder-resource qrd-edit-link-resource" style="display: none;">' +
        '<label for="qrd-edit-title">' + t('Title') + '</label>' +
        '<input type="text" id="qrd-edit-title" value="' + resource.ti + '" />' +
        '</div>' +
        // Tags input
        '<div class="qrd-edit-html-resource qrd-edit-link-resource" style="display: none;">' +
        '<label for="qrd-edit-tags">' + t('Tags') + '</label>' +
        '<input type="text" id="qrd-edit-tags" value="' + resource.t + '" />' +
        '</div>' +
        // HTML Resource textarea
        '<div class="qrd-edit-html-resource" style="display: none;">' +
        '<label for="qrd-edit-text">' + t('Text') + '</label>' +
        '<textarea id="qrd-edit-text">' + resource.text + '</textarea>' +
        '</div>' +
        // URL input
        '<div class="qrd-edit-link-resource" style="display: none;">' +
        '<label for="qrd-edit-url">URL</label>' +
        '<input type="text" id="qrd-edit-url" value="' + resource.text + '" />' +
        '</div>' +
        '</div>' +
        '<div id="qrd-edit-footline">' +
        '<span id="save-edited-qrd" class="chat-button-line chat-button-left chat-button-right" ' +
        'style="margin-left: 4px; padding-left: 10px; padding-right: 10px; cursor:pointer; background-image: ' +
        this.addBrowserSpecificGradient('') + ';">' + t('Ok') + '</span>' +
        '<span id="cancel-edited-qrd" class="chat-button-line chat-button-left chat-button-right" ' +
        'style="margin-left: 4px; padding-left: 10px; padding-right: 10px; cursor:pointer; background-image: ' +
        this.addBrowserSpecificGradient('') + ';">' + t('Cancel') + '</span>' +
        '</div>' +
        '</div>';

    var qrdTreeBody = $('#qrd-tree-body');
    qrdTreeBody.append(qrdEditHtml).trigger('create');

    var bodyWidth = qrdTreeBody.width();
    var bodyHeight = qrdTreeBody.height();
    var thisLeft = Math.floor(0.025 * bodyWidth);
    var thisTop = Math.floor(0.025 * bodyHeight);
    var thisWidth = Math.floor(0.95 * bodyWidth);
    var thisHeight = Math.floor(0.95 * bodyHeight);

    var thisCss = {
        position: 'fixed', 'z-index': '10',
        left: thisLeft+'px', top: thisTop+'px', width: thisWidth+'px', height: thisHeight+'px',
        border: '2px solid #ccc', 'border-radius': '4px',
        'box-shadow': '10px 10px 5px #888888'
    };
    var thisHeadlineCss = {
        position: 'absolute', left: '0px', top: '0px', 'border-bottom': '1px solid #ccc',
        width: (thisWidth - 5)+'px', height: '22px',
        'border-top-left-radius': '4px', 'border-top-right-radius': '4px',
        padding: '4px 0px 0px 5px', 'font-weight': 'bold', 'text-shadow': 'none',
        'background-color': '#f5f5f5', opacity: '0.9'
    };
    var thisBodyCss = {
        position: 'absolute', left: '0px', top: '27px',
        width: (thisWidth - 10)+'px', height: (thisHeight - 27 - 28 - 8)+'px',
        padding: '4px 5px 4px 5px', 'text-shadow': 'none',
        'background-color': '#FFFFC6', opacity: '0.9'
    };
    var thisFootlineCss = {
        position: 'absolute', left: '0px', top: (thisHeight - 28)+'px', 'border-top': '1px solid #ccc',
        width: (thisWidth - 4)+'px', height: '21px', 'text-align': 'right',
        padding: '6px 2px 0px 2px',
        'border-bottom-left-radius': '4px', 'border-bottom-right-radius': '4px',
        'background-color': '#f5f5f5', opacity: '0.9'
    };
    $('#qrd-edit').css(thisCss);
    $('#qrd-edit-headline').css(thisHeadlineCss);
    $('#qrd-edit-body').css(thisBodyCss);
    $('#qrd-edit-footline').css(thisFootlineCss);
};

ChatDisplayClass.prototype.addQrd = function() {
    var qrdAddHtml = '<div id="qrd-add">' +
        '<div id="qrd-add-headline">' + t('Add new Resource') + '</div>' +
        '<div id="qrd-add-body">' +
        // Type selection
        '<label for="qrd-add-type">' + t('Type') + '</label>' +
        '<select id="qrd-add-type">' +
        '<option value="-1">' + t('-- Choose a type ---') + '</option>' +
        '<option value="0">' + t('Folder') + '</option>' +
        '<option value="1">' + t('HTML Resource') + '</option>' +
        '<option value="2">' + t('Link') + '</option>' +
        '</select>' +
        // Title input
        '<div class="qrd-add-html-resource qrd-add-folder-resource qrd-add-link-resource" style="display: none;">' +
        '<label for="qrd-add-title">' + t('Title') + '</label>' +
        '<input type="text" id="qrd-add-title" />' +
        '</div>' +
        // Tags input
        '<div class="qrd-add-html-resource qrd-add-link-resource" style="display: none;">' +
        '<label for="qrd-add-tags">' + t('Tags') + '</label>' +
        '<input type="text" id="qrd-add-tags" />' +
        '</div>' +
        // HTML Resource textarea
        '<div class="qrd-add-html-resource" style="display: none;">' +
        '<label for="qrd-add-text">' + t('Text') + '</label>' +
        '<textarea id="qrd-add-text"></textarea>' +
        '</div>' +
        // URL input
        '<div class="qrd-add-link-resource" style="display: none;">' +
        '<label for="qrd-add-url">URL</label>' +
        '<input type="text" id="qrd-add-url" />' +
        '</div>' +
        '</div>' +
        '<div id="qrd-add-footline">' +
        '<span id="save-new-qrd" class="chat-button-line chat-button-left chat-button-right" ' +
        'style="margin-left: 4px; padding-left: 10px; padding-right: 10px; cursor:pointer; background-image: ' +
        this.addBrowserSpecificGradient('') + ';">' + t('Ok') + '</span>' +
        '<span id="cancel-new-qrd" class="chat-button-line chat-button-left chat-button-right" ' +
        'style="margin-left: 4px; padding-left: 10px; padding-right: 10px; cursor:pointer; background-image: ' +
        this.addBrowserSpecificGradient('') + ';">' + t('Cancel') + '</span>' +
        '</div>' +
        '</div>';

    var qrdTreeBody = $('#qrd-tree-body');
    qrdTreeBody.append(qrdAddHtml).trigger('create');

    var bodyWidth = qrdTreeBody.width();
    var bodyHeight = qrdTreeBody.height();
    var thisLeft = Math.floor(0.025 * bodyWidth);
    var thisTop = Math.floor(0.025 * bodyHeight);
    var thisWidth = Math.floor(0.95 * bodyWidth);
    var thisHeight = Math.floor(0.95 * bodyHeight);

    var thisCss = {
        position: 'fixed', 'z-index': '10',
        left: thisLeft+'px', top: thisTop+'px', width: thisWidth+'px', height: thisHeight+'px',
        border: '2px solid #ccc', 'border-radius': '4px',
        'box-shadow': '10px 10px 5px #888888'
    };
    var thisHeadlineCss = {
        position: 'absolute', left: '0px', top: '0px', 'border-bottom': '1px solid #ccc',
        width: (thisWidth - 5)+'px', height: '22px',
        'border-top-left-radius': '4px', 'border-top-right-radius': '4px',
        padding: '4px 0px 0px 5px', 'font-weight': 'bold', 'text-shadow': 'none',
        'background-color': '#f5f5f5', opacity: '0.9'
    };
    var thisBodyCss = {
        position: 'absolute', left: '0px', top: '27px',
        width: (thisWidth - 10)+'px', height: (thisHeight - 27 - 28 - 8)+'px',
        padding: '4px 5px 4px 5px', 'text-shadow': 'none',
        'background-color': '#FFFFC6', opacity: '0.9'
    };
    var thisFootlineCss = {
        position: 'absolute', left: '0px', top: (thisHeight - 28)+'px', 'border-top': '1px solid #ccc',
        width: (thisWidth - 4)+'px', height: '21px', 'text-align': 'right',
        padding: '6px 2px 0px 2px',
        'border-bottom-left-radius': '4px', 'border-bottom-right-radius': '4px',
        'background-color': '#f5f5f5', opacity: '0.9'
    };
    $('#qrd-add').css(thisCss);
    $('#qrd-add-headline').css(thisHeadlineCss);
    $('#qrd-add-body').css(thisBodyCss);
    $('#qrd-add-footline').css(thisFootlineCss);
};

ChatDisplayClass.prototype.getResourceIcon = function(type) {
    var resourceIcon;
    switch(type) {
        case '0':
            resourceIcon = 'img/001-folder.png';
            break;
        case '1':
            resourceIcon = 'img/058-doc_new.png';
            break;
        case '2':
            resourceIcon = 'img/054-doc_web16c.png';
            break;
        default:
            resourceIcon = 'img/622-paper_clip.png';
            break;
    }
    return resourceIcon;
};

ChatDisplayClass.prototype.resourceSortFunction = function(a, b) {
    var returnValue = 0;
        if (a.ti.toLowerCase() < b.ti.toLowerCase()) {
            returnValue = -1;
        } else {
            returnValue = 1;
        }
    return returnValue;
};
/***********************************************************************************************************************************************************************/

/**
 * Create the user settings view
 */
ChatDisplayClass.prototype.createUsersettingsManagement = function() {
    this.showUsersettingsHtml = false;
    $('#usersettings-menu').css({'display': 'none'});
    $('#translation-container').css({'display': 'none'});

    var thisUsersettingsContainer = $('#usersettings-container');
    var sliderWidth = Math.min(thisUsersettingsContainer.width()-20,400);
    var defaultCss = ' height: 14px; position: absolute; padding: 3px; text-align: center; ' +
        'overflow: hidden; cursor: pointer; border: 1px solid #ccc; border-radius: 4px;';
    var newMessageSoundChecked = (this.playNewMessageSound == 1) ? ' checked="checked"' : '';
    var newChatSoundChecked = (this.playNewChatSound == 1) ? ' checked="checked"' : '';
    var repeatChatSoundChecked = (this.repeatNewChatSound == 1) ? ' checked="checked"' : '';
    var usersettingsManagementHtml = '<div id="usersettings-container-headline"><h3>' + t('Client configuration') + '</h3></div>' +
        '<div id="usersettings-container-headline2"></div>' +
        '<div id="usersettings-container-body">' +
        '<div id="settings-container">' +
        '<div id="usersetting-div" style="text-align: left;" data-role="collapsible-set" data-mini="true" data-theme="c" data-content-theme="d">' +
        '<div data-role="collapsible">' +
        '<h3>' + t('Notifications') + '</h3>' +
        '<p>' +
        '<p>' + t('Volume') + '</p>' +
        '<div style="width: ' + sliderWidth +'px">' +
        '<input value="' + this.volume + '" type="range" name="volume-slider" id="volume-slider" min="0" max="100" ' +
        'step="5" data-highlight="true" style="display:none;" /></div>' +
        '<div style="padding: 5px 0px;">' +
        '<input type="checkbox" value="1" data-role="none" id="sound-new-message"' + newMessageSoundChecked + ' />' +
        '<label for="sound-new-message">' + t('New Message') + '</label>' +
        '</div>' +
        '<div style="padding: 5px 0px;">' +
        '<span><input type="checkbox" value="1" data-role="none" id="sound-new-chat"' + newChatSoundChecked + ' />' +
        '<label for="sound-new-chat">' + t('New external Chat') + '</label></span><br />' +
        '<span style="padding-left: 20px;"><input type="checkbox" value="1" data-role="none" id="sound-repeat-new-chat"' + repeatChatSoundChecked + ' />' +
        '<label for="sound-repeat-new-chat">' + t('Keep ringing until allocated') + '</label></span>' +
        '</div>' +
        '</p>' +
        '</div>' +
        //'<div data-role="collapsible">' +
        //'<h3>' + t('Online status') + '</h3>' +
        //'<p>' +
        //'<p>' + t('Show me Away after <!--number_of_minutes--> minutes of inactivity',
        //[['<!--number_of_minutes-->','<input value="' + this.awayAfterTime + '" type="text" id="away-after-time" data-role="none" style="width: 3em;" />']]) +
        //'</p>' +
        //'</p>' +
        //'</div>' +
        '<input type="hidden" value="0" id="away-after-time">' +
        '</div></div>' +
        '</div>' +
        '<div id="usersettings-container-footline">' +
        '<span id="save-usersettings" class="chat-button-line chat-button-left chat-button-right" ' +
        'style="margin-left: 4px; padding-left: 10px; padding-right: 10px; cursor:pointer; background-image: ' +
        this.addBrowserSpecificGradient('') + ';" onclick="">&nbsp;' + t('Ok') + '&nbsp;</span>' +
        '<span id="cancel-usersettings" class="chat-button-line chat-button-left chat-button-right" ' +
        'style="margin-left: 4px; padding-left: 10px; padding-right: 10px; cursor:pointer; background-image: ' +
        this.addBrowserSpecificGradient('') + ';" onclick="finishSettingsDialogue();">&nbsp;' + t('Cancel') + '&nbsp;</span>' +
        '</div>';

    thisUsersettingsContainer.html(usersettingsManagementHtml).trigger('create');
    $('#volume-slider').next().css('margin-left','4px');
    //var thisCloseButton = $('#close-user-settings-management');
    //thisCloseButton.css({position: 'absolute', left: (thisUsersettingsContainer.width() - thisCloseButton.width() - 5) + 'px', top: '5px'});
    //thisCloseButton.css({display: 'block'});
    thisUsersettingsContainer.css({display: 'block'});
    $('#usersettings-container-headline').css(this.UsersettingsContainerHeadlineCss);
    $('#usersettings-container-headline2').css(this.UsersettingsContainerHeadline2Css);
    $('#usersettings-container-footline').css(this.UsersettingsContainerFootlineCss);
    var settingsContHeight = thisUsersettingsContainer.height() - 92;
    var settingsContWidth = 800;
    var settingsContLeft = (thisUsersettingsContainer.width() +12 - settingsContWidth) / 2;
    if (thisUsersettingsContainer.width() < 820) {
        settingsContWidth = thisUsersettingsContainer.width() -10;
        settingsContLeft = (thisUsersettingsContainer.width() - settingsContWidth);
    }
    var settingsContainerCss = {position: 'absolute', top: '5px', width: settingsContWidth+'px',
        left: settingsContLeft+'px'};
    //console.log(settingsContainerCss);
    $('#usersettings-container-body').css(this.UsersettingsContainerBodyCss);
    $('#settings-container').css(settingsContainerCss);

    $('#save-usersettings').click(function () {
        saveUserSettings();
        thisUsersettingsContainer.css({display: 'none'});
    });
};

ChatDisplayClass.prototype.createTranslationManagement = function (availableLanguages, browserLanguage) {
    this.showUsersettingsHtml = false;
    $('#usersettings-menu').css({'display': 'none'});
    $('#usersettings-container').css({'display': 'none'});

    if (this.editThisTranslation == '') {
        var thisTranslationContainer = $('#translation-container');

        var translationManagementHtml = '<div id="translation-container-headline"><h3>' + t('Translation management') + '</h3></div>' +
            '<div id="translation-container-headline2"></div>' +
            '<a href="#" data-role="button" data-icon="delete" data-iconpos="notext" onclick="finishSettingsDialogue()" ' +
            'id="close-translation-management">' + t('Leave') + '</a>' +
            '<p style="height: 48px;margin: 0px; padding: 0px;">&nbsp;</p>' +
            '<p>' + t('You can add missing translations or change already existing translations here.') + '</p>' +
            '<p>' + t('Please select one of the existing languages from the list below or enter a new one, if your language does not already exist.') +
            '<br>' + t('The translation names must comply with the 2 letter language codes, as defined in <!--iso_639_1-->',
                [
                    ['<!--iso_639_1-->', '<a href="http://en.wikipedia.org/wiki/List_of_ISO_639-1_codes" target="_blank">ISO 639-1</a>']
                ]) + '</p>' +
            '<p>' + t('According to your browser, your language is set to <!--browser_language_setting-->',
                [['<!--browser_language_setting-->', '"' + browserLanguage+ '"']]) + '</p>';
        if (availableLanguages.length > 0) {
            translationManagementHtml += '<select id="existing-language">' +
                '<option value="">-- ' + t('Select a language') + ' --</option>';
            for (var langIndex = 0; langIndex < availableLanguages.length; langIndex++) {
                translationManagementHtml += '<option value="' + availableLanguages[langIndex] + '">' +
                    availableLanguages[langIndex] +
                    '</option>';
            }
            translationManagementHtml += '</select>'
        }
        translationManagementHtml += '<input type="text" id="new-language" placeholder="' +
            t('Enter new language name') + '" />' +
            '<a href="#" data-role="button" id="edit-translations" onclick="editTranslations();">' + t('Edit translations') + '</a>';


        thisTranslationContainer.html(translationManagementHtml).trigger('create');
        var thisCloseButton = $('#close-translation-management');
        thisCloseButton.css({position: 'absolute', left: (thisTranslationContainer.width() - thisCloseButton.width() - 5) + 'px', top: '5px'});
        thisTranslationContainer.css({display: 'block'});
        $('#translation-container-headline').css(this.TranslationContainerHeadlineCss);
        $('#translation-container-headline2').css(this.TranslationContainerHeadline2Css);

        $('#existing-language').change(function() {
            if ($('#existing-language').val() != '') {
                $('#new-language').css({display: 'none'});
                $('#new-language').val('');
            } else {
                $('#new-language').css({display: 'block'});
            }
        });

        thisCloseButton.click(function () {
            thisTranslationContainer.css({display: 'none'});
        });
    } else {
        this.editTranslations();
    }
};

ChatDisplayClass.prototype.editTranslations = function (languageToEdit, translationsArray, browserLanguage) {
    var numberOfStrings = 0;
    var thisTranslationContainer = $('#translation-container');
    if (this.editThisTranslation != '') {
        thisTranslationContainer.css({display: 'block'});
    } else {
        this.editThisTranslation = languageToEdit;
        var htmlComment = t('<!--beginn_comment-->comment<!--end_comment-->',
            [['<!--beginn_comment-->','&lt;!--'],['<!--end_comment-->','--&gt;']]);
        var translationManagementHtml = '<div id="translation-container-headline"><h3>' + t('Translation management') + '</h3></div>' +
            '<div id="translation-container-headline2"></div>' +
            '<a href="#" data-role="button" data-icon="delete" data-iconpos="notext" onclick="finishSettingsDialogue()" ' +
            'id="close-translation-management" ' +
            'onclick="toggleVisitorInfo(\'' + this.infoCaller + '\')">' + t('Leave') + '</a>' +
            '<p style="height: 48px;margin: 0px; padding: 0px;">&nbsp;</p>' +
            '<p>' + t('Below you will find a list of the original English strings and the translations in the chosen language <!--chosen_language-->.',
                [['<!--chosen_language-->', '"' + browserLanguage + '"']]) +
            '<br>' + t('The latter can be edited by you.') +
            '<br>' + t('Please do not change the html comments ( <!--html_comment--> )',
            [['<!--html_comment-->',htmlComment]]) + '</p>' +
            '<div id="translations-div" style="overflow-y: auto; text-align: left"><hr>';
        numberOfStrings = translationsArray.length;

        for (var i = 0; i < numberOfStrings; i++) {
            var origString = translationsArray[i]['orig'].replace(/</g,'&lt;');
            origString = origString.replace(/>/g,'&gt;');
            var transString = translationsArray[i][languageToEdit].replace(/</g,'&lt;');
            transString = transString.replace(/>/g,'&gt;');
            transString = transString.replace(/"/g, '&quot;')
            if (origString == transString)
                transString = '';
            translationManagementHtml += origString + '<br>' +
                '<input type="hidden" id="orig-string-' + i + '" value="' + origString + '" />' +
                '<input type="text" id="trans-string-' + i + '" value="' + transString + '" /><br><hr>';
        }
        //translationManagementHtml += '<div data-role="controlgroup" data-type="horizontal">' +
        translationManagementHtml += '<a href="#" data-role="button" data-mini="true" data-inline="true" ' +
            'id="save-translations" onclick="saveTranslations(' + numberOfStrings + ');">' +
            t('Save translations') + '</a>' +
            '<a href="#" data-role="button" data-mini="true" data-inline="true"' +
            ' id="cancel-translations" onclick="cancelTranslations();">' +
            t('Cancel') + '</a>' +
            //'</div></div>';
            '</div>';
        thisTranslationContainer.html(translationManagementHtml).trigger('create');
        var thisCloseButton = $('#close-translation-management');
        thisCloseButton.css({position: 'absolute', left: (thisTranslationContainer.width() - thisCloseButton.width() - 5) + 'px', top: '5px'});
        $('#translations-div').css({width: thisTranslationContainer.width()+'px',
            height: (thisTranslationContainer.height() - $('#translations-div').position().top)+'px'});
        thisTranslationContainer.css({display: 'block'});
        $('#translation-container-headline').css(this.TranslationContainerHeadlineCss);
        $('#translation-container-headline2').css(this.TranslationContainerHeadline2Css);

        thisCloseButton.click(function () {
            thisTranslationContainer.css({display: 'none'});
        });
    }
};

ChatDisplayClass.prototype.playSound = function(name, sender) {
    var thisClass = this;
    $('#sound-'+name)[0].volume = thisClass.volume / 100;
    if ($.inArray(sender, thisClass.soundPlayed) == -1) {
        //console.log('Sound played --- ' + sender);
        $('#sound-'+name)[0].play();
    }
    thisClass.addSoundPlayed(sender);
    setTimeout(function() {thisClass.removeSoundPlayed(sender);}, 2000);
};

ChatDisplayClass.prototype.addSoundPlayed = function(sender) {
    if ($.inArray(sender,this.soundPlayed) == -1) {
        this.soundPlayed.push(sender);
    }
};

ChatDisplayClass.prototype.removeSoundPlayed = function(sender) {
    if ($.inArray(sender,this.soundPlayed) != -1) {
        var tmpSoundPlayed = [];
        for (var i=0; i<this.soundPlayed.length; i++) {
            if (this.soundPlayed[i] != sender) {
                tmpSoundPlayed.push(this.soundPlayed[i]);
            }
        }
        this.soundPlayed = tmpSoundPlayed;
    }
};

ChatDisplayClass.prototype.startRinging = function(senderList) {
    if (this.playNewChatSound == 1) {
        var startRinging = false;
        for (var i = 0; i<senderList.length; i++) {
            if (typeof this.isRinging[senderList[i]] == 'undefined' || !this.isRinging[senderList[i]]) {
                startRinging = true;
                this.isRinging[senderList[i]] = true;
            }
        }
        if (startRinging) {
            //console.log('Start ringing');
            this.ring(senderList);
        }
    }
};

ChatDisplayClass.prototype.ring = function (senderList) {
    var thisClass = this;
    var audio = $('#sound-ringtone')[0]
    var playRingSound = false;
    for (var i=0; i<senderList.length; i++) {
        if (typeof this.isRinging[senderList[i]] != 'undefined' && this.isRinging[senderList[i]]) {
            playRingSound = true;
        }
    }
    if (playRingSound) {
        //console.log('Ring ' + Math.floor(100 * Math.random()));
        audio.volume = this.volume / 100;
        audio.play();
        if (thisClass.repeatNewChatSound == 1) {
            setTimeout(function() {
                thisClass.ring(senderList);
            }, 5000);
        }
    }
};

ChatDisplayClass.prototype.stopRinging = function(senderList) {
    var stopRinging = false;
    for (var key in this.isRinging) {
        if ($.inArray(key, senderList) == -1) {
            stopRinging = true;
            delete this.isRinging[key];
        }
    }
    if (stopRinging) {
        //console.log('stop ringing');
    }
};

ChatDisplayClass.prototype.showDisabledWarning = function() {
    if (this.serverIsDisabled && ($.now() - this.lastDiabledWarningTime >= 90000)) {
        if (confirm(t('This LiveZilla server has been deactivated by the administrator.') +
            t('Do you want to logout now?'))) {
            logout(false);
        } else {
            this.lastDiabledWarningTime = $.now();
        }
    }
};

ChatDisplayClass.prototype.addBrowserSpecificGradient = function(imageString, color) {
    //console.log(imageString);
    //console.log(color);
    //console.log(this.browserName + '-' + this.browserVersion);
    var a, b;
    switch (color) {
        case 'orange':
            a = '#FFF6E6';
            b = '#FFA200';
            break;
        case 'darkgray':
            a = '#F6F6F6';
            b = '#E0E0E0';
            break;
        case 'blue':
            a = '#5393c5';
            b = '#6facd5';
            break;
        case 'background':
            a = '#e9e9e9';
            b = '#dddddd';
            break;
        case 'darkViewSelect':
            a = '#999999';
            b = '#797979';
            break;
        case 'selectedViewSelect':
            a = '#6facd5';
            b = '#5393c5';
            break;
        default:
            a = '#FFFFFF';
            b = '#F1F1F1';
            break;
    }
    var gradientString = imageString;
    var cssTag = '';
    switch (this.browserName) {
        case 'ie':
            cssTag = '-ms-linear-gradient';
            break;
        case 'safari':
            cssTag = '-webkit-linear-gradient';
            break;
        case 'chrome':
            if (this.browserVersion >= 25)
                cssTag = 'linear-gradient';
            else
                cssTag = '-webkit-linear-gradient';
            break;
        case 'opera':
            cssTag = '-o-linear-gradient';
            break;
        case 'mozilla':
            cssTag = '-moz-linear-gradient';
            break;
        default:
            cssTag = 'linear-gradient';
            break;
    }
    if ((this.browserName == 'ie' && this.browserVersion >= 10) ||
        (this.browserName == 'chrome' && this.browserVersion >= 18) ||
        (this.browserName == 'safari' && this.browserVersion >= 5) ||
        (this.browserName == 'opera' && this.browserVersion >= 12) ||
        (this.browserName == 'mozilla' && this.browserVersion >= 10)){
        switch (imageString) {
            case '':
                gradientString = cssTag + '(' + a + ',' + b + ')';
                break;
            case 'text':
                gradientString = 'background-image: ' + cssTag + '(' + a + ',' + b + ')';
                break;
            default:
                gradientString += ', ' + cssTag + '(' + a + ',' + b + ')';
                break;
        }
    }
    //console.log(gradientString);
    return gradientString
};

ChatDisplayClass.prototype.createSecondHeadlineCssFromFirst = function(cssObject) {
    var returnObject =  this.lzm_commonTools.clone(cssObject);
    returnObject.background = '#ededed';
    returnObject.top = '22px';
    returnObject.height = '26px';
    delete returnObject['background-image'];
    delete returnObject['border-top-left-radius'];
    delete returnObject['border-top-right-radius'];
    returnObject['border-top'] = returnObject['border-bottom'];
    delete returnObject['border-bottom'];

    return returnObject;
};

ChatDisplayClass.prototype.createFootlineCssFromHeadline = function(cssObject, parentWidth, parentHeight, roundedStyle, buttonSize) {
    var returnObject =  this.lzm_commonTools.clone(cssObject);
    returnObject.background = '#ededed';
    returnObject['text-align'] = 'right';
    returnObject['border-bottom-left-radius'] = roundedStyle;
    returnObject['border-bottom-right-radius'] = roundedStyle;
    if (typeof buttonSize == 'undefined' || buttonSize != 'small') {
        returnObject['padding-top'] = '5px';
        returnObject['padding-right'] = '10px';
        returnObject.top = (parentHeight - 42 + 5)+'px';
        returnObject.height = '42px';
        returnObject.width = (parentWidth - 10)+'px';
    } else {
        returnObject['padding-top'] = '6px';
        returnObject['padding-right'] = '2px';
        returnObject['padding-bottom'] = '0px';
        returnObject['padding-left'] = '2px';
        returnObject.top = (parentHeight - 20 + 4)+'px';
        returnObject.height = '20px';
        returnObject.width = (parentWidth + 6)+'px';
        returnObject['font-weight'] = 'none';
        returnObject['font-size'] = '11px';
    }
    delete returnObject['line-height'];
    delete returnObject['background-image'];
    delete returnObject['border-top-left-radius'];
    delete returnObject['border-top-right-radius'];
    delete returnObject['border-bottom'];

    return returnObject;
};