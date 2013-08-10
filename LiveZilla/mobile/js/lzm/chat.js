/****************************************************************************************
 * LiveZilla chat.js
 *
 * Copyright 2013 LiveZilla GmbH
 * All rights reserved.
 * LiveZilla is a registered trademark.
 *
 ***************************************************************************************/
var lzm_commonConfig = {};
var lzm_commonTools = {};
var lzm_commonStorage = {};
var lzm_chatTimeStamp = {};
var lzm_chatDisplay = {};
var lzm_chatServerEvaluation = {};
var lzm_chatPollServer = {};
var lzm_chatUserActions = {};
var lzm_chatLink = {};
var loopCounter = 0;
var lzm_chatInputEditor

var filesToDownload = [];

// debugging functions
function forceResizeNow() {
    lzm_chatDisplay.createChatWindowLayout(true);
}

// wrapper arround functions inside one of the classes...
//
function chatInputEnterPressed() {
    //console.log(lzm_chatInputEditor.grabHtml());
    sendChat(lzm_chatInputEditor.grabHtml());
    lzm_chatInputEditor.setHtml('');
}

function stopPolling() {
    lzm_chatPollServer.stopPolling();
}

function logout(askBeforeLogout) {
    lzm_chatDisplay.showUsersettingsHtml = false;
    $('#usersettings-menu').css({'display': 'none'});
    if (!askBeforeLogout || lzm_chatDisplay.openChats.length == 0 || confirm(t('There are still open chats, do you want to leave them?'))) {
        lzm_commonStorage.saveValue('qrd_' + lzm_chatServerEvaluation.myId, JSON.stringify(lzm_chatServerEvaluation.resources));
        lzm_commonStorage.saveValue('qrd_request_time_' + lzm_chatServerEvaluation.myId, JSON.stringify(lzm_chatServerEvaluation.resourceLastEdited));
        lzm_commonStorage.saveValue('qrd_id_list_' + lzm_chatServerEvaluation.myId, JSON.stringify(lzm_chatServerEvaluation.resourceIdList));
        lzm_chatDisplay.askBeforeUnload = false;
        $.blockUI({message: null});
        lzm_chatPollServer.logout();
    }
}

function inviteOtherOperator(guest_id, guest_b_id, chat_id, invite_id, invite_name, invite_group, chat_no) {
    lzm_chatUserActions.inviteOtherOperator(guest_id, guest_b_id, chat_id, invite_id, invite_name, invite_group,
        chat_no);
}

function openLastActiveChat() {
    var id, b_id, chat_id, userid, name;
    if (lzm_chatDisplay.lastActiveChat != '' &&
        typeof lzm_chatServerEvaluation.chatObject[lzm_chatDisplay.lastActiveChat] != 'undefined' &&
        (lzm_chatServerEvaluation.chatObject[lzm_chatDisplay.lastActiveChat].status == 'new' ||
            lzm_chatServerEvaluation.chatObject[lzm_chatDisplay.lastActiveChat].status == 'read')) {
        if (lzm_chatDisplay.lastActiveChat.indexOf('~') != -1) {
            id = lzm_chatDisplay.lastActiveChat.split('~')[0];
            b_id = lzm_chatDisplay.lastActiveChat.split('~')[1];
            chat_id = lzm_chatServerEvaluation.chatObject[lzm_chatDisplay.lastActiveChat].chat_id;
            viewUserData(id, b_id, chat_id, true);
        } else {
            id = lzm_chatDisplay.lastActiveChat;
            for (var i=0; i<lzm_chatServerEvaluation.internal_users.length; i++) {
                if (id == lzm_chatServerEvaluation.internal_users[i].id) {
                    userid = lzm_chatServerEvaluation.internal_users[i].userid;
                    name = lzm_chatServerEvaluation.internal_users[i].name;
                    break;
                }
            }
            userid = (typeof userid != 'undefined') ? userid : id;
            name = (typeof name != 'undefined') ? name : id;
            chatInternalWith(id, userid, name);
        }
    }
}

function chatInternalWith(id, userid, name) {
    lzm_chatDisplay.lastActiveChat = id;
    lzm_chatUserActions.chatInternalWith(id, userid, name);
}

function setUserStatus(statusValue, myName, myUserId) {
    lzm_chatDisplay.setUserStatus(statusValue, myName, myUserId);
}

function viewUserData(id, b_id, chat_id, freeToChat) {
    lzm_chatDisplay.switchCenterPage('chat');
    lzm_chatDisplay.lastActiveChat = id + '~' + b_id;
    lzm_chatUserActions.viewUserData(id, b_id, chat_id, freeToChat);
}

function inviteExternalUser(id, b_id) {
    lzm_chatUserActions.inviteExternalUser(id, b_id);
    lzm_chatDisplay.createChatWindowLayout(true);
}

function forwardChat() {
    lzm_chatUserActions.forwardData.forward_text = $('#forward-text').val();
    lzm_chatUserActions.forwardChat();
}

function selectOperatorForForwarding(id, b_id, chat_id, forward_id, forward_name, forward_group, forward_text, chat_no) {
    lzm_chatUserActions.selectOperatorForForwarding(id, b_id, chat_id, forward_id, forward_name, forward_group,
        forward_text, chat_no);
}

function catchEnterButtonPressed(e) {
    return lzm_chatDisplay.catchEnterButtonPressed(e);
}

function handleUploadRequest(fuprId, fuprName, id, b_id, type, chatId) {
    lzm_chatUserActions.handleUploadRequest(fuprId, fuprName, id, b_id, type, chatId);
}

function showThisInfoButtonPressed(userId) {
    var bgColor = $('#info-button-' + userId).css('background-color');
    $('#info-button-' + userId).css({'background-color': '#898989'});
}

function toggleVisitorInfo(caller, userId) {
    var thisUser = lzm_chatDisplay.thisUser;
    if (caller != 'show-info' && caller!= '') {
        lzm_chatDisplay.ShowVisitorInfo = false;
        lzm_chatDisplay.infoCaller = caller;
    }
    if (typeof userId != 'undefined' && lzm_chatDisplay.thisUser.id != userId) {
        for (var i=0; i<lzm_chatServerEvaluation.external_users.length; i++) {
            if (userId == lzm_chatServerEvaluation.external_users[i].id) {
                thisUser = lzm_chatServerEvaluation.external_users[i];
                //console.log(thisUser);
                break;
            }
        }
    }
    if (typeof userId != 'undefined' && thisUser.id != '') {
        lzm_chatDisplay.infoUser = thisUser;
        lzm_chatDisplay.showVisitorInformation(lzm_chatServerEvaluation.internal_users, thisUser, 'info');
    }
    if (typeof caller != 'undefined') {
        lzm_chatDisplay.toggleVisitorInfo(lzm_chatServerEvaluation.external_users, lzm_chatServerEvaluation.chatObject,
            lzm_chatServerEvaluation.internal_users);
    }
}

function showVisitorInformation(showTab) {
    lzm_chatDisplay.showVisitorInformation(lzm_chatServerEvaluation.internal_users, lzm_chatDisplay.infoUser, showTab);
}

function loadChatInput(active_chat_reco) {
    return lzm_chatUserActions.loadChatInput(active_chat_reco);
}

function saveChatInput(active_chat_reco, text) {
    lzm_chatUserActions.saveChatInput(active_chat_reco, text);
}

function doMacMagicStuff() {
    //alert(mobileOS);
    //if(isMobile && mobileOS == 'iOS') {
    //alert(app);
    if (app == 0) {
        $(window).trigger('resize');
        //alert('iOS detected');
        setTimeout(function() {
            lzm_chatDisplay.createHtmlContent(lzm_chatServerEvaluation.internal_departments,
                lzm_chatServerEvaluation.internal_users, lzm_chatServerEvaluation.external_users,
                lzm_chatServerEvaluation.chats, lzm_chatServerEvaluation.chatObject, lzm_chatPollServer.thisUser,
                lzm_chatServerEvaluation.global_errors,lzm_chatPollServer.chosenProfile,
                lzm_chatDisplay.active_chat_reco);
            lzm_chatDisplay.createChatWindowLayout(true);
        }, 10);
    }
    /*} else {
        setTimeout(function() {
            lzm_chatDisplay.createChatWindowLayout(true);
        }, 20);
    }*/
}

function sendChat(chatMessage) {
    if (typeof lzm_chatServerEvaluation.chatObject[lzm_chatDisplay.active_chat] != 'undefined' ||
        typeof lzm_chatServerEvaluation.chatObject[lzm_chatDisplay.active_chat_reco] != 'undefined') {
        lzm_chatUserActions.deleteChatInput(lzm_chatUserActions.active_chat_reco);
        chatMessage = (typeof chatMessage != 'undefined' && chatMessage != '') ? chatMessage : lzm_chatInputEditor.grabHtml();
        if (chatMessage != '') {
            var new_chat = {};
            new_chat.id = md5(String(Math.random())).substr(0, 32);
            new_chat.rp = '';
            new_chat.sen = lzm_chatServerEvaluation.myId;
            new_chat.rec = '';
            new_chat.reco = lzm_chatDisplay.active_chat_reco;
            var tmpdate = new Date();
            new_chat.date = (tmpdate.getTime() / 1000);
            new_chat.date_human = lzm_commonTools.pad(tmpdate.getDate(), 2) + '.' +
                lzm_commonTools.pad((tmpdate.getMonth() + 1), 2) + '.' +
                tmpdate.getFullYear();
            new_chat.time_human = lzm_commonTools.pad(tmpdate.getHours(), 2) + ':' +
                lzm_commonTools.pad(tmpdate.getMinutes(), 2) + ':' +
                lzm_commonTools.pad(tmpdate.getSeconds(), 2);
            var chatText = chatMessage.replace(/\n/g, "<br />");
            chatText = chatText.replace(/<script/g,'&lt;script').replace(/<\/script/g,'&lt;/script');
            chatText = lzm_chatServerEvaluation.addLinks(chatText);
            new_chat.text = chatText;
            //console.log(new_chat);
            lzm_chatInputEditor.setHtml('');
            lzm_chatUserActions.sendChatMessage(new_chat);

            lzm_chatServerEvaluation.addNewChat(new_chat);
            lzm_chatDisplay.createChatHtml(lzm_chatServerEvaluation.chats, lzm_chatServerEvaluation.chatObject,
                lzm_chatPollServer.thisUser, lzm_chatServerEvaluation.internal_users,
                lzm_chatServerEvaluation.external_users, lzm_chatDisplay.active_chat_reco);
            lzm_chatDisplay.createChatWindowLayout(true);
        }
    } else {
        inviteExternalUser(lzm_chatDisplay.thisUser.id, lzm_chatDisplay.thisUser.b_id);
    }
    if(isMobile || app == 1) {
        setTimeout(function() {doMacMagicStuff();}, 5);
    }
}

function createUserControlPanel() {
    var counter=1;
    var repeatThis = setInterval(function() {
        lzm_chatDisplay.createUserControlPanel(lzm_chatPollServer.user_status, lzm_chatServerEvaluation.myName,
            lzm_chatServerEvaluation.myUserId);
        counter++;
        if (counter >= 60 || lzm_chatServerEvaluation.myName != '' || lzm_chatServerEvaluation.myUserId != '') {
            clearInterval(repeatThis);
            $.unblockUI();
        }
    },250);
}

function testDrag(change) {
    var thisVisitorList = $('#visitor-list');
    if (typeof change == 'undefined' || change == '' || change == 0) {
        var y = window.event.pageY;
        lzm_chatDisplay.visitorListHeight = thisVisitorList.height() + $('#chat').position().top + thisVisitorList.position().top - y + 11;
    } else {
        var newHeight = lzm_chatDisplay.visitorListHeight + change;
        if (newHeight >= 62) {
            lzm_chatDisplay.visitorListHeight = newHeight;
        }
    }
    lzm_chatDisplay.createChatWindowLayout(true);
    if (lzm_chatDisplay.selected_view == 'external' && !lzm_chatDisplay.ShowVisitorInfo) {
        lzm_chatDisplay.createVisitorList(lzm_chatServerEvaluation.external_users, lzm_chatServerEvaluation.chatObject,
            lzm_chatServerEvaluation.internal_users);
    }
    lzm_chatDisplay.createChatHtml(lzm_chatServerEvaluation.chats, lzm_chatServerEvaluation.chatObject, lzm_chatDisplay.thisUser,
        lzm_chatServerEvaluation.internal_users, lzm_chatServerEvaluation.external_users, lzm_chatDisplay.active_chat_reco);
    return false;
}

function manageUsersettings() {
    saveChatInput(lzm_chatDisplay.active_chat_reco);
    lzm_chatInputEditor.removeEditor();
    if (lzm_chatDisplay.displayWidth == 'small') {
        lzm_chatServerEvaluation.settingsDialogue = true;
        lzm_chatDisplay.settingsDialogue = true;
    }
    lzm_chatUserActions.manageUsersettings();
}

function saveUserSettings() {
    finishSettingsDialogue();
    var settings = {
        volume: $('#volume-slider').val(),
        awayAfterTime: $('#away-after-time').val(),
        playNewMessageSound: $('#sound-new-message').prop('checked') ? 1 : 0,
        playNewChatSound: $('#sound-new-chat').prop('checked') ? 1 : 0,
        repeatNewChatSound: $('#sound-repeat-new-chat').prop('checked') ? 1 : 0
    };
    //console.log(settings);
    lzm_chatUserActions.saveUserSettings(settings);
    lzm_chatInputEditor.init(loadChatInput(lzm_chatDisplay.active_chat_reco));
}

function manageTranslations() {
    if (lzm_chatDisplay.displayWidth == 'small') {
        lzm_chatServerEvaluation.settingsDialogue = true;
        lzm_chatDisplay.settingsDialogue = true;
    }
    lzm_chatUserActions.manageTranslations();
}

function finishSettingsDialogue() {
    lzm_chatServerEvaluation.settingsDialogue = false;
    lzm_chatDisplay.settingsDialogue = false;
    $('#usersettings-container').css({display: 'none'});
    lzm_chatInputEditor.init(loadChatInput(lzm_chatDisplay.active_chat_reco));
}

function editTranslations() {
    lzm_chatUserActions.editTranslations($('#existing-language').val(), $('#new-language').val());
}

function saveTranslations(numberOfStrings) {
    finishSettingsDialogue();
    var stringObjects = [];
    for (var i=0; i<numberOfStrings; i++) {
        var thisStringObject = {en: $('#orig-string-'+i).val()};
        thisStringObject[lzm_chatDisplay.editThisTranslation] = $('#trans-string-'+i).val();
        stringObjects.push(thisStringObject);
    }
    lzm_t.saveTranslations(lzm_chatDisplay.editThisTranslation, stringObjects);
    lzm_chatDisplay.editThisTranslation = '';
    $('#translation-container').css('display', 'none');
}

function cancelTranslations() {
    finishSettingsDialogue();
    lzm_chatDisplay.editThisTranslation = '';
    $('#translation-container').css('display', 'none');
}

function t(translateString, placeholderArray) {
    return this.lzm_t.translate(translateString, placeholderArray);
}

function openOrCloseFolder(resourceId, onlyOpenFolders) {
    var folderDiv = $('#folder-' + resourceId);
    if (folderDiv.html() != "") {
        var markDiv = $('#resource-' + resourceId + '-open-mark');
        var bgCss;
        if (folderDiv.css('display') == 'none') {
            folderDiv.css('display', 'block');
            bgCss = {'background-image': lzm_chatDisplay.addBrowserSpecificGradient('url("img/minus.png")'),
                'background-repeat': 'no-repeat', 'background-position': 'center'};
            markDiv.css(bgCss);
            if ($.inArray(resourceId, lzm_chatDisplay.openedResourcesFolder) == -1) {
                lzm_chatDisplay.openedResourcesFolder.push(resourceId);
            }
        } else if (!onlyOpenFolders) {
            folderDiv.css('display', 'none');
            bgCss = {'background-image': lzm_chatDisplay.addBrowserSpecificGradient('url("img/plus.png")'),
                'background-repeat': 'no-repeat', 'background-position': 'center'};
            markDiv.css(bgCss);
            var tmpOpenedFolder = [];
            for (var i=0; i<lzm_chatDisplay.openedResourcesFolder.length; i++) {
                if (resourceId != lzm_chatDisplay.openedResourcesFolder[i]) {
                    tmpOpenedFolder.push(lzm_chatDisplay.openedResourcesFolder[i]);
                }
            }
            lzm_chatDisplay.openedResourcesFolder = tmpOpenedFolder;
        }
    }
}

function handleResourceClickEvents(resourceId, onlyOpenFolders) {
    onlyOpenFolders = (typeof onlyOpenFolders != 'undefined') ? onlyOpenFolders : false;
    lzm_chatDisplay.selectedResource = resourceId;
    var resource = {};
    for (var i=0; i<lzm_chatServerEvaluation.resources.length; i++) {
        if (lzm_chatServerEvaluation.resources[i].rid == resourceId) {
            resource = lzm_chatServerEvaluation.resources[i];
        }
    }
    $('.resource-div').css({'background-color': '#ffffff', 'color': '#000000'});
    lzm_chatDisplay.highlightSearchResults(lzm_chatServerEvaluation.resources, false);
    $('#resource-' + resourceId).css({'background-color': '#3399FF', 'color': '#ffffff', 'text-shadow': 'none',
        'border-radius': '4px'});
    //console.log(typeof resourceId + ': ' + resourceId + ' --- ' + typeof type + ': ' + type);
    $('.qrd-change-buttons').addClass('ui-disabled');
    switch (Number(resource.ty)) {
        case 0:
            openOrCloseFolder(resourceId, onlyOpenFolders);
            if ($.inArray(resourceId, ['1', '3', '5']) == -1) {
                if (resource.oid == lzm_chatServerEvaluation.myId) {
                    //$('#rename-qrd').removeClass('ui-disabled');
                    $('#edit-qrd').removeClass('ui-disabled');
                    $('#add-qrd').removeClass('ui-disabled');
                    $('#delete-qrd').removeClass('ui-disabled');
                }
            } else if (resourceId == 1) {
                $('#add-qrd').removeClass('ui-disabled');
            }
            break;
        case 1:
            if (resource.oid == lzm_chatServerEvaluation.myId) {
                //$('#rename-qrd').removeClass('ui-disabled');
                $('#edit-qrd').removeClass('ui-disabled');
                $('#delete-qrd').removeClass('ui-disabled');
                $('#view-qrd').removeClass('ui-disabled');
            }
            $('#preview-qrd').removeClass('ui-disabled');
            $('#send-qrd-preview').removeClass('ui-disabled');
            break;
        case 2:
            if (resource.oid == lzm_chatServerEvaluation.myId) {
                //$('#rename-qrd').removeClass('ui-disabled');
                $('#edit-qrd').removeClass('ui-disabled');
                $('#delete-qrd').removeClass('ui-disabled');
                $('#view-qrd').removeClass('ui-disabled');
            }
            $('#preview-qrd').removeClass('ui-disabled');
            $('#send-qrd-preview').removeClass('ui-disabled');
            break;
        default:
            if (resource.oid == lzm_chatServerEvaluation.myId) {
                //$('#rename-qrd').removeClass('ui-disabled');
                //$('#edit-qrd').removeClass('ui-disabled');
                $('#delete-qrd').removeClass('ui-disabled');
            }
            $('#send-qrd-preview').removeClass('ui-disabled');
            break;
    }
}

function addQrd() {
    lzm_chatUserActions.addQrd();
}

function deleteQrd() {
    if (confirm(t('Do you want to delete this entry including subentries irrevocably?'))) {
        lzm_chatUserActions.deleteQrd();
    }
}

function renameQrd() {
    // Perhaps not needed
}

function editQrd() {
    lzm_chatUserActions.editQrd();
}

function previewQrd(chatPartner) {
    chatPartner = (typeof chatPartner != 'undefined') ? chatPartner : '';
    $('#preview-qrd').addClass('ui-disabled');
    lzm_chatUserActions.previewQrd(chatPartner);
}

function showQrd(chatPartner, caller) {
    saveChatInput(lzm_chatDisplay.active_chat_reco);
    lzm_chatInputEditor.removeEditor();
    lzm_chatDisplay.createQrdTree(lzm_chatServerEvaluation.resources, caller, chatPartner,
        lzm_chatServerEvaluation.external_users, lzm_chatServerEvaluation.internal_users,
        lzm_chatServerEvaluation.internal_departments);
    lzm_chatDisplay.selected_view = 'qrd';
    lzm_chatDisplay.toggleVisibility();
    /*$('#chat').css('display', 'block');
    $('#chat-table').css('display', 'none');
    $('#qrd-tree').css('display', 'block');*/
}

function cancelQrd() {
    cancelQrdPreview(0);
    lzm_chatInputEditor.init(loadChatInput(lzm_chatDisplay.active_chat_reco));
    lzm_chatDisplay.selected_view = 'mychats';
    lzm_chatDisplay.toggleVisibility();
    /*$('#chat').css('display', 'block');
    $('#chat-table').css('display', 'block');
    $('#qrd-tree').css('display', 'none');*/
}

function cancelQrdPreview(animationTime) {
    $('#preview-qrd').removeClass('ui-disabled');
    $('#qrd-preview').remove();
}

function sendQrdPreview(chatPartner) {
    var resourceHtmlText;
    for (i=0; i<lzm_chatServerEvaluation.resources.length; i++) {
        if (lzm_chatDisplay.selectedResource == lzm_chatServerEvaluation.resources[i].rid) {
            resource = lzm_chatServerEvaluation.resources[i];
            break;
        }
    }
    switch (resource.ty) {
        case '1':
            resourceHtmlText = resource.text;
            break;
        case '2':
            var linkHtml = '<a href="#" onclick="openLink(\'' + resource.text + '\');" class="lz_chat_link" target="_blank">' + resource.ti + '</a>';
            resourceHtmlText = linkHtml;
            break;
        default:
            var urlFileName = encodeURIComponent(resource.ti.replace(/ /g, '+'));
            var acid = lzm_commonTools.pad(Math.floor(Math.random() * 1048575).toString(16), 5);
            var fileId = resource.text.split('_')[1];
            var thisServer = lzm_chatPollServer.chosenProfile.server_protocol + lzm_chatPollServer.chosenProfile.server_url;
            var fileHtml = '<a ' +
                'href="' + thisServer + '/getfile.php' +
                '?acid=' + acid +
                '&file=' + urlFileName +
                '&id=' + fileId + '" ' +
                'class="lz_chat_file" target="_blank">' + resource.ti + '</a>'
            resourceHtmlText = fileHtml;
            break;
    }

    saveChatInput(lzm_chatDisplay.active_chat_reco, resourceHtmlText);
    cancelQrd();
}

function addLeftMessageToChat(chat_reco) {
    var new_chat = {};
    new_chat.id = md5(String(Math.random())).substr(0, 32);
    new_chat.rp = '';
    new_chat.sen = '0000000';
    new_chat.rec = '';
    new_chat.reco = chat_reco;
    var tmpdate = new Date();
    new_chat.date = (tmpdate.getTime() / 1000);
    new_chat.date_human = lzm_commonTools.pad(tmpdate.getDate(), 2) + '.' +
        lzm_commonTools.pad((tmpdate.getMonth() + 1), 2) + '.' +
        tmpdate.getFullYear();
    new_chat.time_human = lzm_commonTools.pad(tmpdate.getHours(), 2) + ':' +
        lzm_commonTools.pad(tmpdate.getMinutes(), 2) + ':' +
        lzm_commonTools.pad(tmpdate.getSeconds(), 2);
    new_chat.text = t('The visitor has left the chat!');
    lzm_chatServerEvaluation.chats.push(new_chat);
}

function addOpLeftMessageToChat(chat_reco, members) {
    //console.log(chatPartners);
    for (var i=0; i<members.length; i++) {
        if (members[i].id != lzm_chatServerEvaluation.myId && members[i].st != 0 &&
            (typeof lzm_chatServerEvaluation.chatObject[chat_reco].accepted == 'undefined' || !lzm_chatServerEvaluation.chatObject[chat_reco].accepted)) {
            lzm_chatServerEvaluation.chatObject[chat_reco].accepted = true;
            for (var k=0; k<lzm_chatServerEvaluation.internal_users.length; k++) {
                if (lzm_chatServerEvaluation.internal_users[k].id == members[i].id) {
                    var new_chat = {};
                    new_chat.id = md5(String(Math.random())).substr(0, 32);
                    new_chat.rp = '';
                    new_chat.sen = '0000000';
                    new_chat.rec = '';
                    new_chat.reco = chat_reco;
                    var tmpdate = new Date();
                    new_chat.date = (tmpdate.getTime() / 1000);
                    new_chat.date_human = lzm_commonTools.pad(tmpdate.getDate(), 2) + '.' +
                        lzm_commonTools.pad((tmpdate.getMonth() + 1), 2) + '.' +
                        tmpdate.getFullYear();
                    new_chat.time_human = lzm_commonTools.pad(tmpdate.getHours(), 2) + ':' +
                        lzm_commonTools.pad(tmpdate.getMinutes(), 2) + ':' +
                        lzm_commonTools.pad(tmpdate.getSeconds(), 2);
                    new_chat.text = t('<!--this_op_name--> has left the chat!', [['<!--this_op_name-->', lzm_chatServerEvaluation.internal_users[k].name]]);
                    lzm_chatServerEvaluation.chats.push(new_chat);
                }
            }
        }
    }
}

function addDeclinedMessageToChat(id, b_id, chatPartners) {
    //console.log(chatPartners);
    for (var i=0; i<chatPartners.past.length; i++) {
        if ($.inArray(chatPartners.past[i], chatPartners.present) == -1) {
            for (var k=0; k<lzm_chatServerEvaluation.internal_users.length; k++) {
                if (lzm_chatServerEvaluation.internal_users[k].id == chatPartners.past[i]) {
                    var new_chat = {};
                    new_chat.id = md5(String(Math.random())).substr(0, 32);
                    new_chat.rp = '';
                    new_chat.sen = '0000000';
                    new_chat.rec = '';
                    new_chat.reco = id + '~' + b_id;
                    var tmpdate = new Date();
                    new_chat.date = (tmpdate.getTime() / 1000);
                    new_chat.date_human = lzm_commonTools.pad(tmpdate.getDate(), 2) + '.' +
                        lzm_commonTools.pad((tmpdate.getMonth() + 1), 2) + '.' +
                        tmpdate.getFullYear();
                    new_chat.time_human = lzm_commonTools.pad(tmpdate.getHours(), 2) + ':' +
                        lzm_commonTools.pad(tmpdate.getMinutes(), 2) + ':' +
                        lzm_commonTools.pad(tmpdate.getSeconds(), 2);
                    new_chat.text = t('<!--this_op_name--> has declined the chat!', [['<!--this_op_name-->', lzm_chatServerEvaluation.internal_users[k].name]]);
                    lzm_chatServerEvaluation.chats.push(new_chat);
                }
            }
        }
    }
}

function removeFromOpenChats(chat, deleteFromChat, resetActiveChat, member) {
    // if in openChats set ststus to 'left' and remove from openChats, otherwise delete from chatObject
    /*if (deleteFromChat && $.inArray(chat, lzm_chatDisplay.openChats) == -1 && lzm_chatServerEvaluation.chatObject[chat].status != 'left') {
        delete lzm_chatServerEvaluation.chatObject[chat];
    } else if (deleteFromChat && $.inArray(chat, lzm_chatDisplay.openChats) != -1) {
        lzm_chatServerEvaluation.chatObject[chat].status = 'left';
    }*/
    //console.log(memberIdList);
    var i, new_chat;

    var inChatWith = '';
    for (i=0; i<member.length; i++) {
        if (member[i].st == 0) {
            inChatWith = member[i].id;
        }
    }
    if (inChatWith != '' && lzm_chatServerEvaluation.chatObject[chat].status != 'left') {
        var opName = t('Another operator');
        for (i=0; i<lzm_chatServerEvaluation.internal_users.length; i++) {
            if (lzm_chatServerEvaluation.internal_users[i].id == inChatWith) {
                opName = lzm_chatServerEvaluation.internal_users[i].name;
                break;
            }
        }
        new_chat = {};
        new_chat.id = md5(String(Math.random())).substr(0, 32);
        new_chat.rp = '';
        new_chat.sen = '0000000';
        new_chat.rec = '';
        new_chat.reco = chat;
        var tmpdate = new Date();
        new_chat.date = (tmpdate.getTime() / 1000);
        new_chat.date_human = lzm_commonTools.pad(tmpdate.getDate(), 2) + '.' +
            lzm_commonTools.pad((tmpdate.getMonth() + 1), 2) + '.' +
            tmpdate.getFullYear();
        new_chat.time_human = lzm_commonTools.pad(tmpdate.getHours(), 2) + ':' +
            lzm_commonTools.pad(tmpdate.getMinutes(), 2) + ':' +
            lzm_commonTools.pad(tmpdate.getSeconds(), 2);
        new_chat.text = t('<!--this_op_name--> has accepted the chat!', [['<!--this_op_name-->',opName]]);
        lzm_chatServerEvaluation.chats.push(new_chat);
        new_chat = {};
        new_chat.id = md5(String(Math.random())).substr(0, 32);
        new_chat.rp = '';
        new_chat.sen = '0000000';
        new_chat.rec = '';
        new_chat.reco = chat;
        var tmpdate = new Date();
        new_chat.date = (tmpdate.getTime() / 1000);
        new_chat.date_human = lzm_commonTools.pad(tmpdate.getDate(), 2) + '.' +
            lzm_commonTools.pad((tmpdate.getMonth() + 1), 2) + '.' +
            tmpdate.getFullYear();
        new_chat.time_human = lzm_commonTools.pad(tmpdate.getHours(), 2) + ':' +
            lzm_commonTools.pad(tmpdate.getMinutes(), 2) + ':' +
            lzm_commonTools.pad(tmpdate.getSeconds(), 2);
        new_chat.text = t('<!--this_op_name--> has left the chat!', [['<!--this_op_name-->', lzm_chatServerEvaluation.myName]]);
        lzm_chatServerEvaluation.chats.push(new_chat);
    }
    if (deleteFromChat) {
        lzm_chatServerEvaluation.chatObject[chat].status = 'left';
    }
    //console.log('Removing ' + chat + ' from open chats');
    var tmpOpenchats = [];
    for (i=0; i<lzm_chatDisplay.openChats.length; i++) {
        if (chat != lzm_chatDisplay.openChats[i]) {
            tmpOpenchats.push(lzm_chatDisplay.openChats[i]);
        }
    }
    lzm_chatDisplay.openChats = tmpOpenchats;
    lzm_chatUserActions.open_chats = tmpOpenchats;
    if (resetActiveChat) {
        if (lzm_chatDisplay.active_chat_reco == chat) {
            setTimeout(function() {
                lzm_chatUserActions.viewUserData(chat.split('~')[0], chat.split('~')[1], 0, true);
            }, 20);
        }
    }
}

function markVisitorAsLeft(id, b_id) {
    if (lzm_chatServerEvaluation.chatObject[id + '~' + b_id].status != 'left') {
        addLeftMessageToChat(id + '~' + b_id);
    }
    lzm_chatServerEvaluation.chatObject[id + '~' + b_id].status = 'left';
    if (lzm_chatDisplay.active_chat_reco == id + '~' + b_id) {
        removeFromOpenChats(id + '~' + b_id, false, true, []);
    }
}

function markVisitorAsBack(id, b_id, chat_id, member) {
    var chatIsMine = false;
    for (var j=0; j<member.length; j++) {
        if (member[j].id == lzm_chatServerEvaluation.myId) {
            chatIsMine = true;
            break;
        }
    }
    //console.log(member);
    if (chatIsMine) {
        //console.log('Visitor ' + id + '~' + b_id + ' is back!');
        removeFromOpenChats(id + '~' + b_id, false, true, member);
        lzm_chatServerEvaluation.chatObject[id + '~' + b_id].status = 'new';
        var tmpClosedChats = [];
        for (var i=0; i<lzm_chatDisplay.closedChats.length; i++) {
            if (lzm_chatDisplay.closedChats[i] != id + '~' + b_id) {
                tmpClosedChats.push(lzm_chatDisplay.closedChats[i]);
            }
        }
        lzm_chatDisplay.closedChats = tmpClosedChats;

        var new_chat = {};
        new_chat.id = md5(String(Math.random())).substr(0, 32);
        new_chat.rp = '';
        new_chat.sen = '0000000';
        new_chat.rec = '';
        new_chat.reco = id + '~' + b_id;
        var tmpdate = new Date();
        new_chat.date = (tmpdate.getTime() / 1000);
        new_chat.date_human = lzm_commonTools.pad(tmpdate.getDate(), 2) + '.' +
            lzm_commonTools.pad((tmpdate.getMonth() + 1), 2) + '.' +
            tmpdate.getFullYear();
        new_chat.time_human = lzm_commonTools.pad(tmpdate.getHours(), 2) + ':' +
            lzm_commonTools.pad(tmpdate.getMinutes(), 2) + ':' +
            lzm_commonTools.pad(tmpdate.getSeconds(), 2);
        new_chat.text = t('The visitor is chatting with <!--this_op_name-->', [['<!--this_op_name-->', lzm_chatServerEvaluation.myName]]);
        lzm_chatServerEvaluation.chats.push(new_chat);

        lzm_chatServerEvaluation.browserChatIdList.push(chat_id);
    } else {
        markVisitorAsLeft(id, b_id);
    }
}

function playIncomingMessageSound(sender, chatId) {
    chatId = (typeof chatId != 'undefined') ? chatId : '';
    if (lzm_chatDisplay.playNewMessageSound == 1 /*&& $.inArray(sender, lzm_chatDisplay.openChats) != -1*/) {
        //console.log('Play sound : ' + sender + ' --- ' + chatId);
        lzm_chatDisplay.playSound('message', sender);
    }
}

function leaveChat() {
    lzm_chatInputEditor.removeEditor();
    if (lzm_chatDisplay.thisUser.b_id != '') {
        lzm_chatServerEvaluation.chatObject[lzm_chatDisplay.active_chat_reco].accepted = false;
        thisBId = lzm_chatDisplay.active_chat_reco.split('~')[1];
        for (var i=0; i<lzm_chatDisplay.thisUser.b.length; i++) {
            if (lzm_chatDisplay.thisUser.b[i].id == thisBId) {
                lzm_chatDisplay.thisUser.b_id = lzm_chatDisplay.thisUser.b[i].id;
                lzm_chatDisplay.thisUser.b_chat_id = lzm_chatDisplay.thisUser.b[i].chat.id;
                break;
            }
        }
        if (typeof lzm_chatServerEvaluation.chatObject[lzm_chatDisplay.active_chat_reco] != 'undefined') {
            if (lzm_chatServerEvaluation.chatObject[lzm_chatDisplay.active_chat_reco].status == 'declined') {
                //console.log('Declined chat ' + lzm_chatDisplay.active_chat_reco);
                lzm_chatDisplay.closedChats.push(lzm_chatDisplay.active_chat_reco);
                lzm_chatUserActions.setActiveChat('', '', '', { id:'', b_id:'', b_chat:{ id:'' } });
                //lzm_chatServerEvaluation.chatObject[lzm_chatDisplay.active_chat_reco].status = 'left';
                lzm_chatDisplay.createActiveChatPanel(lzm_chatServerEvaluation.external_users, lzm_chatServerEvaluation.internal_users,
                    lzm_chatServerEvaluation.internal_departments, lzm_chatServerEvaluation.chatObject, false);
                lzm_chatDisplay.createHtmlContent(lzm_chatServerEvaluation.internal_departments, lzm_chatServerEvaluation.internal_users,
                    lzm_chatServerEvaluation.external_users, lzm_chatServerEvaluation.chats, lzm_chatServerEvaluation.chatObject,
                    lzm_chatDisplay.thisUser, lzm_chatServerEvaluation.global_errors, lzm_chatServerEvaluation.chosen_profile,
                    lzm_chatDisplay.active_chat_reco);
            } else if (lzm_chatServerEvaluation.chatObject[lzm_chatDisplay.active_chat_reco].status == 'left' ||
                confirm(t('Do you really want to close this Chat?'))) {
                lzm_chatDisplay.closedChats.push(lzm_chatDisplay.active_chat_reco);
                lzm_chatUserActions.leaveExternalChat(lzm_chatDisplay.thisUser.id, lzm_chatDisplay.thisUser.b_id, lzm_chatDisplay.thisUser.b_chat.id, 0);
            }
        }
    } else {
        lzm_chatUserActions.leaveInternalChat(lzm_chatDisplay.thisUser.id, lzm_chatDisplay.thisUser.userid, lzm_chatDisplay.thisUser.name);
    }
}

function fillStringsFromTranslation() {
    if (loopCounter > 49 || lzm_t.translationArray.length != 0) {
        $('#radio-mychats-text span.ui-btn-text').text(t('Chats'));
        $('#radio-external-text span.ui-btn-text').text(t('Visitors'));
        $('#radio-internal-text span.ui-btn-text').text(t('Operators'));
        $('#radio-qrd-text span.ui-btn-text').text(t('Resources'));
        $('#radio-this-text span.ui-btn-text').text(t('Chats'));
        $('#radio-startpage-text span.ui-btn-text').text(t('Start page'));
        $('#radio-error-text span.ui-btn-text').text(t('Errors'));
        $('#radio-foo-text span.ui-btn-text').text(t('Foo'));
    } else {
        loopCounter++;
        setTimeout(function() {fillStringsFromTranslation();}, 50);
    }
}

function switchCenterPage(target) {
    lzm_chatUserActions.saveChatInput(lzm_chatUserActions.active_chat_reco);
    if (target == 'home') {
        lzm_chatInputEditor.removeEditor();
    }
    lzm_chatUserActions.setActiveChat('', '', '', { id:'', b_id:'', b_chat:{ id:'' } });
    lzm_chatDisplay.createActiveChatPanel(lzm_chatServerEvaluation.external_users,
        lzm_chatServerEvaluation.internal_users, lzm_chatServerEvaluation.internal_departments,
        lzm_chatServerEvaluation.chatObject, false);
    lzm_chatDisplay.switchCenterPage(target);
}

function openLink(url) {
    if (app == 1) {
        lzm_chatLink.openLinkInIframe(url);
    } else if (web == 1) {
        window.open(url, '_blank');
    }
}

function downloadFile(address) {
    if (app == 1) {
        filesToDownload.push(address);
    } else if (web == 1) {
        window.open(address, '_blank');
    }
}

function tryNewLogin(logoutOtherInstance) {
    lzm_chatPollServer.stopPolling();
    lzm_chatPollServer.pollServerlogin(lzm_chatPollServer.chosenProfile.server_protocol,lzm_chatPollServer.chosenProfile.server_url, logoutOtherInstance);
    //console.log('logging in again --- ' + logoutOtherInstance);
}

/**
 * Some stuff done on load of the chat page
 */
$(document).ready(function () {
    $.blockUI({message: null});

    // initiate lzm class objects
    lzm_commonConfig = new CommonConfigClass();
    lzm_commonTools = new CommonToolsClass();
    lzm_commonStorage = new CommonStorageClass(localDbPrefix);
    lzm_chatTimeStamp = new ChatTimestampClass(0);
    lzm_chatLink = new ChatLinkClass();
    var awayAfter = (typeof chosenProfile.user_away_after != 'undefined') ? chosenProfile.user_away_after : 0;
    var userConfigData = {
        userVolume: chosenProfile.user_volume,
        awayAfter: (typeof chosenProfile.user_away_after != 'undefined') ? chosenProfile.user_away_after : 0,
        playIncomingMessageSound: (typeof chosenProfile.play_incoming_message_sound != 'undefined') ? chosenProfile.play_incoming_message_sound : 0,
        playIncomingChatSound: (typeof chosenProfile.play_incoming_chat_sound != 'undefined') ? chosenProfile.play_incoming_chat_sound : 0,
        repeatIncomingChatSound: (typeof chosenProfile.repeat_incoming_chat_sound != 'undefined') ? chosenProfile.repeat_incoming_chat_sound : 0
    };
    lzm_chatInputEditor = new ChatEditorClass('chat-input', isMobile, (app == 1), (web == 1));
    lzm_chatDisplay = new ChatDisplayClass(new Date().getTime() / 1000, lzm_commonConfig, lzm_commonTools,
        lzm_chatInputEditor, web, app, messageTemplates, userConfigData);
    lzm_chatServerEvaluation = new ChatServerEvaluationClass(lzm_commonTools, chosenProfile, lzm_chatTimeStamp);
    lzm_chatPollServer = new ChatPollServerClass(lzm_commonConfig, lzm_commonTools, lzm_chatDisplay,
        lzm_chatServerEvaluation, lzm_commonStorage, chosenProfile, userStatus, web, app);
    lzm_t = new CommonTranslationClass(chosenProfile.server_protocol, chosenProfile.server_url, false, chosenProfile.language);
    lzm_chatUserActions = new ChatUserActionsClass(lzm_commonTools, lzm_chatPollServer, lzm_chatDisplay,
        lzm_chatServerEvaluation, lzm_t, lzm_commonStorage, lzm_chatInputEditor, chosenProfile);
    lzm_chatServerEvaluation.userLanguage = lzm_t.language;
    lzm_chatDisplay.userLanguage = lzm_t.language;
    lzm_chatUserActions.userLanguage = lzm_t.language;

    //lzm_chatDisplay.createInputControlPanel();
    lzm_chatDisplay.createChatWindowLayout(false);

    lzm_chatPollServer.pollServerlogin(lzm_chatPollServer.chosenProfile.server_protocol,
        lzm_chatPollServer.chosenProfile.server_url);

    createUserControlPanel();
    fillStringsFromTranslation();

    $('#logo-page').attr('src', 'http://start.livezilla.net?&product_version='+lzm_commonConfig.lz_version+'&web=' + web + '&app=' + app);

    // do things on window resize
    $(window).resize(function () {
        /*if ($('#debugging-messages').css('display') == 'none') {
            lzm_chatDisplay.debuggingDisplayMode = 'block';
            setTimeout(function() {
                lzm_chatDisplay.debuggingDisplayMode = 'none';
                $('#debugging-messages').css('display', lzm_chatDisplay.debuggingDisplayMode);
            }, 10000);
        }
        $('#debugging-messages').append($(window).width() + 'x' + $(window).height() + '<br />');*/
        setTimeout(function() {
            lzm_chatDisplay.createUserControlPanel(lzm_chatPollServer.user_status, lzm_chatServerEvaluation.myName,
                lzm_chatServerEvaluation.myUserId);
            lzm_chatDisplay.createChatWindowLayout(false);
            if (lzm_chatDisplay.selected_view == 'external' && !lzm_chatDisplay.ShowVisitorInfo) {
                lzm_chatDisplay.createVisitorList(lzm_chatServerEvaluation.external_users, lzm_chatServerEvaluation.chatObject,
                    lzm_chatServerEvaluation.internal_users);
            }
            if (lzm_chatDisplay.ShowVisitorInfo) {
                toggleVisitorInfo('show-info', lzm_chatDisplay.infoUser.id);
            }
            if (lzm_chatDisplay.selected_view == 'mychats') {
                lzm_chatDisplay.createActiveChatPanel(lzm_chatServerEvaluation.external_users, lzm_chatServerEvaluation.internal_users,
                    lzm_chatServerEvaluation.internal_departments, lzm_chatServerEvaluation.chatObject, false);
            }
            setTimeout(function() {
                lzm_chatDisplay.createChatWindowLayout(true);
            }, 10);
        }, 10);
    });

    $('.logout_btn').click(function () {
        logout(true);
    });

    $('#stop_polling').click(function () {
        stopPolling();
    });

    $('#userstatus-button').click(function () {
        var thisUserstatusMenu = $('#userstatus-menu');
        if ($('#chat-logo').css('display') == 'block') {
            //lzm_chatDisplay.switchCenterPage('anywhereButHome');
            lzm_chatDisplay.selected_view = 'internal';
            lzm_chatDisplay.toggleVisibility();
        }
        if (lzm_chatDisplay.showUserstatusHtml == false) {
            lzm_chatDisplay.showUserstatusMenu(lzm_chatPollServer.user_status, lzm_chatServerEvaluation.myName,
                lzm_chatServerEvaluation.myUserId);
            thisUserstatusMenu.css({'display':'block'});
            lzm_chatDisplay.showUserstatusHtml = true;
        } else {
            thisUserstatusMenu.css({'display':'none'});
            lzm_chatDisplay.showUserstatusHtml = false;
        }
    });

    $('#usersettings-button').click(function () {
        var thisUsersettingsMenu = $('#usersettings-menu');
        if ($('#chat-logo').css('display') == 'block') {
            //lzm_chatDisplay.switchCenterPage('anywhereButHome');
            lzm_chatDisplay.selected_view = 'internal';
            lzm_chatDisplay.toggleVisibility();
        }
        if (lzm_chatDisplay.showUsersettingsHtml == false) {
            lzm_chatDisplay.showUsersettingsMenu();
            thisUsersettingsMenu.css({'display':'block'});
            lzm_chatDisplay.showUsersettingsHtml = true;
        } else {
            thisUsersettingsMenu.css({'display':'none'});
            lzm_chatDisplay.showUsersettingsHtml = false;
        }
    });

    $('.view-select').change(function () {
        lzm_chatUserActions.saveChatInput(lzm_chatUserActions.active_chat_reco);
        lzm_chatInputEditor.removeEditor();
        lzm_chatDisplay.selected_view = $('.view-select:checked').val();
        //lzm_chatUserActions.setActiveChat('', '', '', { id:'', b_id:'', b_chat:{ id:'' } });
        lzm_chatDisplay.createHtmlContent(lzm_chatServerEvaluation.internal_departments,
            lzm_chatServerEvaluation.internal_users, lzm_chatServerEvaluation.external_users,
            lzm_chatServerEvaluation.chats, lzm_chatServerEvaluation.chatObject,
            lzm_chatPollServer.thisUser, lzm_chatServerEvaluation.global_errors, lzm_chatPollServer.chosenProfile,
            lzm_chatDisplay.active_chat_reco);
        if (lzm_chatDisplay.selected_view == 'qrd') {
            lzm_chatDisplay.createQrdTree(lzm_chatServerEvaluation.resources, 'view-select-panel');
        } else {
            cancelQrdPreview();
        }
        if (lzm_chatDisplay.selected_view != 'mychats') {
            lzm_chatUserActions.setActiveChat('', '', '', { id:'', b_id:'', b_chat:{ id:'' } });
        }
        if (lzm_chatDisplay.selected_view == 'external' && !lzm_chatDisplay.VisitorListCreated) {
            lzm_chatDisplay.updateVisitorList(lzm_chatServerEvaluation.external_users, lzm_chatServerEvaluation.chatObject,
                lzm_chatServerEvaluation.internal_users);
        }
        lzm_chatDisplay.ShowVisitorInfo = false;
        lzm_chatDisplay.toggleVisitorInfo(lzm_chatServerEvaluation.external_users, lzm_chatServerEvaluation.chatObject,
            lzm_chatServerEvaluation.internal_users);
        finishSettingsDialogue();
        lzm_chatDisplay.toggleVisibility();
        if (lzm_chatDisplay.selected_view == 'mychats') {
            openLastActiveChat();
        }
    });

    $('#view-select2').change(function () {
        lzm_chatUserActions.saveChatInput(lzm_chatUserActions.active_chat_reco);
        lzm_chatInputEditor.removeEditor();
        var choice = $('.view-select2:checked').val();
        var views = [{id: 'mychats', text: t('Chats')}, {id: 'internal', text: t('Operators')},
            {id: 'external', text: t('Visitors')}, {id: 'qrd', text: t('Resources')}];
        var selViewIndex = -1;
        for (var i=0; i<views.length; i++) {
            if (lzm_chatDisplay.selected_view == views[i].id) {
                selViewIndex = i;
                break;
            }
        }
        var newSelViewIndex = selViewIndex;
        if (choice == 'left') {
            newSelViewIndex = Math.max(selViewIndex - 1, 0);
        } else if (choice == 'right') {
            newSelViewIndex = Math.min(selViewIndex + 1, 3);
        }
        lzm_chatDisplay.selected_view = views[newSelViewIndex].id;
        //lzm_chatUserActions.setActiveChat('', '', '', { id:'', b_id:'', b_chat:{ id:'' } });
        lzm_chatDisplay.createHtmlContent(lzm_chatServerEvaluation.internal_departments,
            lzm_chatServerEvaluation.internal_users, lzm_chatServerEvaluation.external_users,
            lzm_chatServerEvaluation.chats, lzm_chatServerEvaluation.chatObject,
            lzm_chatPollServer.thisUser, lzm_chatServerEvaluation.global_errors, lzm_chatPollServer.chosenProfile,
            lzm_chatDisplay.active_chat_reco);
        if (lzm_chatDisplay.selected_view == 'qrd') {
            lzm_chatDisplay.createQrdTree(lzm_chatServerEvaluation.resources, 'view-select-panel');
        } else {
            cancelQrdPreview();
        }
        if (lzm_chatDisplay.selected_view != 'mychats') {
            lzm_chatUserActions.setActiveChat('', '', '', { id:'', b_id:'', b_chat:{ id:'' } });
        }
        if (lzm_chatDisplay.selected_view == 'external' && !lzm_chatDisplay.VisitorListCreated) {
            lzm_chatDisplay.updateVisitorList(lzm_chatServerEvaluation.external_users, lzm_chatServerEvaluation.chatObject,
            lzm_chatServerEvaluation.internal_users);
        }
        lzm_chatDisplay.ShowVisitorInfo = false;
        lzm_chatDisplay.toggleVisitorInfo(lzm_chatServerEvaluation.external_users, lzm_chatServerEvaluation.chatObject,
            lzm_chatServerEvaluation.internal_users);
        finishSettingsDialogue();
        lzm_chatDisplay.toggleVisibility();
        if (lzm_chatDisplay.selected_view == 'mychats') {
            openLastActiveChat();
        }
        setTimeout(function(){$('#radio-this-text span.ui-btn-text').text(views[newSelViewIndex].text);
            $('#radio-left-text span.ui-icon').css({'background-image': 'url(\'js/jquery_mobile/images/icons-18-white.png\')',
                'background-position': '-144px -1px', 'background-repeat': 'no-repeat', 'background-color': 'rgba(0,0,0,.4)',
                'border-radius': '9px', 'width': '18px', 'height': '18px', 'display': 'block', 'left': '12px'});
            $('#radio-right-text span.ui-icon').css({'background-image': 'url(\'js/jquery_mobile/images/icons-18-white.png\')',
                'background-position': '-108px -1px', 'background-repeat': 'no-repeat', 'background-color': 'rgba(0,0,0,.4)',
                'border-radius': '9px', 'width': '18px', 'height': '18px', 'display': 'block', 'left': '18px'});
        },5);
    });

    $('.lzm-button').mouseenter(function() {
        $(this).css('background-image', $(this).css('background-image').replace(/linear-gradient\(.*\)/,'linear-gradient(#f6f6f6,#e0e0e0)'));
    });

    $('.lzm-button').mouseleave(function() {
        $(this).css('background-image', $(this).css('background-image').replace(/linear-gradient\(.*\)/,'linear-gradient(#ffffff,#f1f1f1)'));
    });

    $('body').mouseover(function(){lzm_chatPollServer.wakeupFromAutoSleep();});

    $(window).on('beforeunload', function(){
        if (lzm_chatDisplay.askBeforeUnload)
            return t('Are you sure you want to leave or reload the client? You may lose data because of that.');
    });

    $('#iframe-close-button').click(function() {
        lzm_chatLink.closeLinkInIframe('close');
    });

    $('#iframe-hide-button').click(function() {
        lzm_chatLink.closeLinkInIframe('hide');
    });
});
