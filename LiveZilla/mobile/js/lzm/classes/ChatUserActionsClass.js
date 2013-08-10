/****************************************************************************************
 * LiveZilla ChatUserActionsClass.js
 *
 * Copyright 2013 LiveZilla GmbH
 * All rights reserved.
 * LiveZilla is a registered trademark.
 *
 ***************************************************************************************/

function ChatUserActionsClass(lzm_commonTools, lzm_chatPollServer, lzm_chatDisplay, lzm_chatServerEvaluation,
                              lzm_commonTranslation, lzm_commonStorage, lzm_chatInputEditor, chosenProfile) {
    //console.log('ChatUserActionsClass initiated');

    // variables defined here, controlling the application
    this.open_chats = [];
    this.active_chat = '';
    this.active_chat_reco = '';
    this.forwardData = {};

    // variables passed to this class as parameters
    this.lzm_commonTools = lzm_commonTools;
    this.lzm_chatPollServer = lzm_chatPollServer;
    this.lzm_chatDisplay = lzm_chatDisplay;
    this.lzm_chatServerEvaluation = lzm_chatServerEvaluation;
    this.lzm_commonTranslation = lzm_commonTranslation;
    this.lzm_commonStorage = lzm_commonStorage;
    this.chosenProfile = chosenProfile;
    this.lzm_chatInputEditor = lzm_chatInputEditor;

    this.userLanguage = '';

    this.ChatInputValues = {};
}


// ****************************** Internal chats ****************************** //
/**
 * start a chat with another operator
 * @param id
 * @param userid
 * @param name
 */
ChatUserActionsClass.prototype.chatInternalWith = function (id, userid, name) {
    var thisClass = this;
    thisClass.saveChatInput(thisClass.active_chat_reco);
    this.lzm_chatDisplay.selected_view = 'mychats';

    var i;
    var thisUser = { id:'', b_id:'', b_chat:{ id:'' } };
    if (id == 'everyoneintern') {
        thisUser = {id: 'everyoneintern', b_id: '', b_chat: {id: ''}, name: name, logo: 'img/lz_group.png'};
    } else {
        if (id != userid || id != name || userid != name ) {
            for (i = 0; i < this.lzm_chatServerEvaluation.internal_users.length; i++) {
                if (this.lzm_chatServerEvaluation.internal_users[i].id == id) {
                    this.lzm_chatServerEvaluation.internal_users[i].logo = this.lzm_chatServerEvaluation.internal_users[i].status_logo;
                    thisUser = this.lzm_chatServerEvaluation.internal_users[i];
                    thisUser.b_id = '';
                    thisUser.b_chat = {id: ''};
                    break;
                }
            }
        } else {
            for (i = 0; i < this.lzm_chatServerEvaluation.internal_departments.length; i++) {
                if (this.lzm_chatServerEvaluation.internal_departments[i].id == id) {
                    thisUser = this.lzm_chatServerEvaluation.internal_departments[i];
                    thisUser.b_id = '';
                    thisUser.b_chat = {id: ''};
                    break;
                }
            }
        }
    }
    this.setActiveChat(id, id, name, thisUser);
    var loadedValue = thisClass.loadChatInput(thisClass.active_chat_reco);

    this.lzm_chatDisplay.toggleVisibility();
    this.lzm_chatInputEditor.init(loadedValue);

    if (typeof this.lzm_chatServerEvaluation.chatObject[this.lzm_chatDisplay.active_chat] == 'undefined') {
        this.lzm_chatServerEvaluation.chatObject[this.lzm_chatDisplay.active_chat] = {
            status:'read', type:'internal', id: id, b_id:'', data:[]
        };
    }
    this.lzm_chatServerEvaluation.chatObject[this.lzm_chatDisplay.active_chat]['status'] = 'read';

    this.lzm_chatDisplay.createChatWindowLayout(true);
    this.lzm_chatDisplay.showInternalChat(this.lzm_chatServerEvaluation.internal_departments,
        this.lzm_chatServerEvaluation.internal_users, this.lzm_chatServerEvaluation.external_users,
        this.lzm_chatServerEvaluation.chats, this.lzm_chatServerEvaluation.chatObject,
        this.lzm_chatPollServer.thisUser, this.lzm_chatServerEvaluation.global_errors,
        this.lzm_chatPollServer.chosenProfile, loadedValue);

    $('#add-qrd').click(function() {
        showQrd(id, 'chat');
    });

    this.lzm_chatDisplay.removeSoundPlayed(id);
};

ChatUserActionsClass.prototype.leaveInternalChat = function(id, userid, name) {
    this.deleteChatInput(this.active_chat_reco);
    this.lzm_chatServerEvaluation.chatObject[this.lzm_chatDisplay.active_chat].status = 'left';
    this.setActiveChat('','','',{ id:'', b_id:'', b_chat:{ id:'' } });
    //this.lzm_chatDisplay.closedChats.push(id);
    this.lzm_chatDisplay.createActiveChatPanel(this.lzm_chatServerEvaluation.external_users,
        this.lzm_chatServerEvaluation.internal_users, this.lzm_chatServerEvaluation.internal_departments,
        this.lzm_chatServerEvaluation.chatObject, false);
    /*if (this.lzm_chatServerEvaluation.rec_posts.length > 0) {
        this.lzm_chatPollServer.stopPolling();
        this.lzm_chatPollServer.addToOutboundQueue('p_rec_posts', this.lzm_chatServerEvaluation.rec_posts.join('><'), 'nonumber');
        this.lzm_chatPollServer.pollServer(this.lzm_chatPollServer.fillDataObject(), 'shout');
        this.lzm_chatServerEvaluation.rec_posts = [];
    }*/
    this.lzm_chatDisplay.finishLeaveChat();
    this.lzm_chatDisplay.showLeaveChat(this.lzm_chatServerEvaluation.internal_departments,
        this.lzm_chatServerEvaluation.internal_users, this.lzm_chatServerEvaluation.external_users,
        this.lzm_chatServerEvaluation.chats, this.lzm_chatServerEvaluation.chatObject, this.lzm_chatPollServer.thisUser,
        this.lzm_chatServerEvaluation.global_errors, this.lzm_chatPollServer.chosenProfile);
    //this.lzm_chatDisplay.noUserSwitchBackground();
};

// ****************************** External chats ****************************** //
/**
 * Invite a visitor on the website to a chat
 * @param id
 * @param b_id
 */
ChatUserActionsClass.prototype.inviteExternalUser = function (id, b_id) {
    this.lzm_chatPollServer.stopPolling();
    var text = this.lzm_chatInputEditor.grabHtml();
    this.lzm_chatInputEditor.setHtml('');
    $('#chat-buttons').css({display: 'none'});
    $('#chat-action').css({display: 'none'});
    $('#chat-progress').css({display: 'none'});

    this.lzm_chatPollServer.addToOutboundQueue('p_requests_va', id, 'nonumber');
    this.lzm_chatPollServer.addToOutboundQueue('p_requests_vb', b_id, 'nonumber');
    this.lzm_chatPollServer.addToOutboundQueue('p_requests_vc', this.lzm_chatServerEvaluation.myName, 'nonumber');
    this.lzm_chatPollServer.addToOutboundQueue('p_requests_vd', this.lzm_chatServerEvaluation.myUserId, 'nonumber');
    this.lzm_chatPollServer.addToOutboundQueue('p_requests_ve', lz_global_base64_url_encode(text), 'nonumber');
    this.lzm_chatPollServer.addToOutboundQueue('p_requests_vf', this.lzm_chatServerEvaluation.myGroup, 'nonumber');

    this.lzm_chatPollServer.pollServer(this.lzm_chatPollServer.fillDataObject(), 'shout');

    var new_chat = {};
    new_chat.id = md5(String(Math.random())).substr(0, 32);
    new_chat.rp = 'whatever';
    new_chat.sen = this.lzm_chatServerEvaluation.myId;
    new_chat.rec = 'whatever';
    new_chat.reco = id + '~' + id + '_OVL';
    var tmpdate = new Date();
    new_chat.date = (tmpdate.getTime() / 1000);
    new_chat.date_human = this.lzm_commonTools.pad(tmpdate.getDate(), 2) + '.' +
        this.lzm_commonTools.pad((tmpdate.getMonth() + 1), 2) + '.' + tmpdate.getFullYear();
    new_chat.time_human = this.lzm_commonTools.pad(tmpdate.getHours(), 2) + ':' +
        this.lzm_commonTools.pad(tmpdate.getMinutes(), 2) + ':' + this.lzm_commonTools.pad(tmpdate.getSeconds(), 2);
    new_chat.text = text;
    this.lzm_chatServerEvaluation.addNewChat(new_chat);
};

ChatUserActionsClass.prototype.getChatPM = function(visitorId, browserId, pmId) {
    var i, j;
    var chatGroup = '', visitorName = '', visitorEmail = '', visitorCompany = '', visitorPhone = '', pm = {}, fallbackPm = {}, userPm = {}, userFallbackPm = {};
    var  chatLang = this.lzm_chatServerEvaluation.defaultLanguage.toUpperCase();
    for (i=0; i<this.lzm_chatServerEvaluation.external_users.length; i++) {
        if (visitorId == this.lzm_chatServerEvaluation.external_users[i].id) {
            for (j=0; j<this.lzm_chatServerEvaluation.external_users[i].b.length; j++) {
                if (browserId == this.lzm_chatServerEvaluation.external_users[i].b[j].id) {
                    if (typeof this.lzm_chatServerEvaluation.external_users[i].b[j].chat != 'undefined' &&
                        typeof this.lzm_chatServerEvaluation.external_users[i].b[j].chat.gr != 'undefined') {
                        chatGroup = this.lzm_chatServerEvaluation.external_users[i].b[j].chat.gr;
                    }
                    if (typeof this.lzm_chatServerEvaluation.external_users[i].b[j].cname != 'undefined') {
                        visitorName = this.lzm_chatServerEvaluation.external_users[i].b[j].cname;
                    }
                    if (typeof this.lzm_chatServerEvaluation.external_users[i].b[j].cemail != 'undefined') {
                        visitorEmail = this.lzm_chatServerEvaluation.external_users[i].b[j].cemail;
                    }
                    if (typeof this.lzm_chatServerEvaluation.external_users[i].b[j].ccompany != 'undefined') {
                        visitorCompany = this.lzm_chatServerEvaluation.external_users[i].b[j].ccompany;
                    }
                    if (typeof this.lzm_chatServerEvaluation.external_users[i].b[j].cphone != 'undefined') {
                        visitorPhone = this.lzm_chatServerEvaluation.external_users[i].b[j].cphone;
                    }
                    break;
                }
            }
            if (typeof this.lzm_chatServerEvaluation.external_users[i].lang != 'undefined' &&
                this.lzm_chatServerEvaluation.external_users[i].lang != '') {
                chatLang = this.lzm_chatServerEvaluation.external_users[i].lang;
                //chatLang = (chatLang.indexOf('-') != -1) ? chatLang.split('-')[0] : chatLang;
            }
            break;
        }
    }
    for (i=0; i<this.lzm_chatServerEvaluation.internal_users.length; i++) {
        if (lzm_chatServerEvaluation.myId == this.lzm_chatServerEvaluation.internal_users[i].id &&
            typeof this.lzm_chatServerEvaluation.internal_users[i].pm != 'undefined' &&
            this.lzm_chatServerEvaluation.internal_users[i].pm.length > 0) {
            for (j=0; j<this.lzm_chatServerEvaluation.internal_users[i].pm.length; j++) {
                if (chatLang == this.lzm_chatServerEvaluation.internal_users[i].pm[j].lang) {
                    userPm = this.lzm_chatServerEvaluation.internal_users[i].pm[j];
                }
                if ('EN' == this.lzm_chatServerEvaluation.internal_users[i].pm[j].lang) {
                    userFallbackPm = this.lzm_chatServerEvaluation.internal_users[i].pm[j];
                }
            }
            break;
        }
    }
    for (i=0; i<this.lzm_chatServerEvaluation.internal_departments.length; i++) {
        if (chatGroup == '' || chatGroup == this.lzm_chatServerEvaluation.internal_departments[i].id) {
            for (j=0; j<this.lzm_chatServerEvaluation.internal_departments[i].pm.length; j++) {
                if (chatLang == this.lzm_chatServerEvaluation.internal_departments[i].pm[j].lang) {
                    pm = this.lzm_chatServerEvaluation.internal_departments[i].pm[j];
                }
                if ('EN' == this.lzm_chatServerEvaluation.internal_departments[i].pm[j].lang) {
                    fallbackPm = this.lzm_chatServerEvaluation.internal_departments[i].pm[j];
                }
            }
            break;
        }
    }

    fallbackPm = (typeof userFallbackPm[pmId] != 'undefined' && userFallbackPm[pmId] != '') ? userFallbackPm : fallbackPm;
    pm = (typeof userPm[pmId] != 'undefined' && userPm[pmId] != '') ? userPm : pm;
    pm = (typeof pm[pmId] != 'undefined' && pm[pmId] != '') ? pm : fallbackPm;
    if (typeof pm[pmId] != 'undefined') {
        pm[pmId] = pm[pmId].replace(/%external_name%/, visitorName)
            .replace(/%external_email%/, visitorEmail)
            .replace(/%external_phone%/, visitorPhone)
            .replace(/%external_company%/, visitorCompany)
            .replace(/%name%/, this.lzm_chatServerEvaluation.myName)
            .replace(/%operator_name%/, this.lzm_chatServerEvaluation.myName);
    }
    //console.log(pm);
    return pm;
};

ChatUserActionsClass.prototype.saveChatInput = function(active_chat_reco, text) {
    //console.log('Trying to save chat input for ' + active_chat_reco);
    //console.log(text);
    if (typeof active_chat_reco != 'undefined' && active_chat_reco != '') {
        //console.log('Save input of chat with : ' +active_chat_reco);
        var chatInput = '';
        if (typeof text != 'undefined' && text != '') {
            chatInput = text;
        } else {
            var tmpInput = this.lzm_chatInputEditor.grabHtml();
            //console.log('Read text is ' + tmpInput);
            chatInput = tmpInput.replace(/^ */,'').replace(/ *$/,'');
        }
        if (chatInput != '') {
            this.ChatInputValues[active_chat_reco] = chatInput;
            //console.log('Safed value : ' + this.ChatInputValues[active_chat_reco]);
        }
    }
};

ChatUserActionsClass.prototype.loadChatInput = function(active_chat_reco) {
    var rtValue = '';
    if (typeof active_chat_reco != 'undefined' && active_chat_reco != '' && typeof this.ChatInputValues[active_chat_reco] != 'undefined') {
        //console.log('Load input of chat with : ' +active_chat_reco);
        rtValue = this.ChatInputValues[active_chat_reco];
    }
    //console.log('Loaded value : ' + rtValue);
    return rtValue;
};

ChatUserActionsClass.prototype.deleteChatInput = function(active_chat_reco) {
    if (typeof active_chat_reco != 'undefined' && active_chat_reco != '' && typeof this.ChatInputValues[active_chat_reco] != 'undefined') {
        //console.log('Delete input of chat with : ' +active_chat_reco);
        delete this.ChatInputValues[active_chat_reco];
    }
};

/**
 * View the data of an external user and provide the operator with the choice to
 * invite the visitor to a chat,
 * accept or decline to take an incoming chat
 * @param id
 * @param b_id
 * @param chat_id
 */
ChatUserActionsClass.prototype.viewUserData = function (id, b_id, chat_id, freeToChat) {
    var thisClass = this;
    thisClass.open_chats = thisClass.lzm_chatDisplay.openChats;
    thisClass.saveChatInput(thisClass.active_chat_reco);

    freeToChat = (typeof freeToChat == 'undefined' && freeToChat != false) ? true : false;

    var thisUser = { id:'', b_id:'', b_chat:{ id:'' } };
    for (var i = 0; i < thisClass.lzm_chatServerEvaluation.external_users.length; i++) {
        if (thisClass.lzm_chatServerEvaluation.external_users[i].id == id) {
            thisUser = thisClass.lzm_chatServerEvaluation.external_users[i];
            //console.log(thisClass.lzm_chatServerEvaluation.external_users[i]);
            if (thisClass.lzm_chatServerEvaluation.external_users[i].b_id != b_id) {
                for (var j=0; j<thisClass.lzm_chatServerEvaluation.external_users[i].b.length; j++) {
                    if (thisClass.lzm_chatServerEvaluation.external_users[i].b[j].id == b_id) {
                        thisUser.b_id = thisClass.lzm_chatServerEvaluation.external_users[i].b[j].id;
                        thisUser.b_chat = thisClass.lzm_chatServerEvaluation.external_users[i].b[j].chat;
                        break;
                    }
                }
            }
            break;
        }
    }
    thisClass.lzm_chatDisplay.selected_view = 'mychats';

    if (chat_id == 0) {
        chat_id = thisUser.b_chat.id;
    }

    var active_chat = id;
    var active_chat_reco = id + '~' + b_id;
    var active_chat_realname = '';
    if (thisUser.b_cname != '') {
        active_chat_realname = thisUser.b_cname;
    } else {
        active_chat_realname = thisUser.id;
    }
    //console.log (thisUser.id + ' --- ' + thisUser.b_id + ' --- ' + thisUser.b_chat.id);
    thisClass.setActiveChat(active_chat, active_chat_reco, active_chat_realname, thisUser);

    thisClass.lzm_chatDisplay.toggleVisibility();

    if ($.inArray(id + '~' + b_id, thisClass.open_chats) != -1) {
        thisClass.lzm_chatDisplay.showActiveVisitorChat(thisUser, thisClass.lzm_chatServerEvaluation.external_forwards,
            thisClass.lzm_chatServerEvaluation.chatObject);
        var loadedValue = thisClass.loadChatInput(thisClass.active_chat_reco);
        thisClass.lzm_chatInputEditor.init(loadedValue);
        //console.log('Open chat - initializing editor');
        thisClass.chatExternalWith(id, b_id, chat_id, 0);
        thisClass.lzm_chatDisplay.removeSoundPlayed(id + '~' + b_id);
    } else {
        thisClass.lzm_chatInputEditor.removeEditor();
        //console.log('Not an pen chat - removing editor');
        thisClass.lzm_chatDisplay.showPassiveVisitorChat(thisUser, thisClass.lzm_chatServerEvaluation.external_forwards,
            thisClass.lzm_chatServerEvaluation.internal_users, thisClass.lzm_chatServerEvaluation.chatObject, id, b_id,
            chat_id, freeToChat);
        thisClass.lzm_chatDisplay.showPassiveVisitorChat(thisUser, thisClass.lzm_chatServerEvaluation.external_forwards,
            thisClass.lzm_chatServerEvaluation.internal_users, thisClass.lzm_chatServerEvaluation.chatObject, id, b_id,
            chat_id, freeToChat);
        thisClass.lzm_chatDisplay.createActiveChatPanel(thisClass.lzm_chatServerEvaluation.external_users,
            thisClass.lzm_chatServerEvaluation.internal_users, thisClass.lzm_chatServerEvaluation.internal_departments,
            thisClass.lzm_chatServerEvaluation.chatObject, false);
        thisClass.lzm_chatDisplay.createChatHtml(thisClass.lzm_chatServerEvaluation.chats, thisClass.lzm_chatServerEvaluation.chatObject,
            thisUser, thisClass.lzm_chatServerEvaluation.internal_users, thisClass.lzm_chatServerEvaluation.external_users, active_chat_reco);

        $('#accept-chat').click(function () {
            if ($.inArray(id + '~' + b_id, this.open_chats) == -1) {
                var new_chat = {};
                new_chat.id = md5(String(Math.random())).substr(0, 32);
                new_chat.rp = '';
                new_chat.sen = '0000000';
                new_chat.rec = '';
                new_chat.reco = active_chat_reco;
                var tmpdate = new Date();
                new_chat.date = (tmpdate.getTime() / 1000);
                new_chat.date_human = thisClass.lzm_commonTools.pad(tmpdate.getDate(), 2) + '.' +
                    thisClass.lzm_commonTools.pad((tmpdate.getMonth() + 1), 2) + '.' +
                    tmpdate.getFullYear();
                new_chat.time_human = thisClass.lzm_commonTools.pad(tmpdate.getHours(), 2) + ':' +
                    thisClass.lzm_commonTools.pad(tmpdate.getMinutes(), 2) + ':' +
                    thisClass.lzm_commonTools.pad(tmpdate.getSeconds(), 2);
                new_chat.text = t('<!--this_op_name--> has accepted the chat!',
                    [['<!--this_op_name-->',thisClass.lzm_chatServerEvaluation.myName]]);

                thisClass.lzm_chatServerEvaluation.addNewChat(new_chat);
                //thisClass.lzm_chatInputEditor.setHtml('');

                thisClass.lzm_chatPollServer.stopPolling();

                thisClass.lzm_chatPollServer.addToOutboundQueue('p_accepted_chat_0_va', id, 'nonumber');
                thisClass.lzm_chatPollServer.addToOutboundQueue('p_accepted_chat_0_vb', b_id, 'nonumber');
                thisClass.lzm_chatPollServer.addToOutboundQueue('p_accepted_chat_0_vc', chat_id, 'nonumber');
                thisClass.lzm_chatPollServer.addToOutboundQueue('p_accepted_chat_0_vd', 'AcceptChat', 'nonumber');

                thisClass.lzm_chatPollServer.pollServer(thisClass.lzm_chatPollServer.fillDataObject(), 'shout');

                thisClass.open_chats.push(id + '~' + b_id);
                thisClass.lzm_chatDisplay.openChats = thisClass.open_chats;
                var pm = thisClass.getChatPM(id, b_id, 'wel');
                if (typeof pm.aw != 'undefined' && pm.aw == 1) {
                    if (typeof pm.edit != 'undefined' && pm.edit == 0) {
                        setTimeout(function() {sendChat(pm['wel']);}, 1000);
                    } else {
                        thisClass.ChatInputValues[id + '~' + b_id] = pm['wel'];
                    }
                }
            }
            thisClass.viewUserData(id, b_id, chat_id, freeToChat);
        });
        $('#decline-chat').click(function () {
            thisClass.refuseExternalChat(id, b_id, chat_id, 0);
            thisClass.lzm_chatDisplay.removeSoundPlayed(id + '~' + b_id);
            thisClass.viewUserData(id, b_id, chat_id, freeToChat);
        });
        $('#forward-chat').click(function () {
            thisClass.lzm_chatDisplay.createOperatorInviteHtml('forward', thisClass.lzm_chatServerEvaluation.internal_departments,
                thisClass.lzm_chatServerEvaluation.internal_users, thisClass.lzm_chatPollServer.thisUser,
                thisClass.lzm_chatPollServer.chosenProfile, id, b_id, chat_id);
            thisClass.lzm_chatDisplay.showForwardMessages('forward');
        });
    }
    thisClass.lzm_chatDisplay.createChatWindowLayout(true);
};

/**
 * Chat with an external user
 * @param id
 * @param b_id
 * @param chat_id
 * @param chat_no
 */
ChatUserActionsClass.prototype.chatExternalWith = function (id, b_id, chat_id, chat_no) {
    var thisClass = this;
    thisClass.removeForwardFromList(id, b_id);

    if (thisClass.lzm_chatServerEvaluation.chatObject[thisClass.lzm_chatServerEvaluation.active_chat_reco]['status'] == 'new') {
        thisClass.lzm_chatServerEvaluation.chatObject[thisClass.lzm_chatServerEvaluation.active_chat_reco]['status'] = 'read';
    }
    $('#chat-action').css('display', 'block');
    $('#chat-progress').css('display', 'block');
    thisClass.lzm_chatDisplay.createChatHtml(thisClass.lzm_chatServerEvaluation.chats, thisClass.lzm_chatServerEvaluation.chatObject,
        thisClass.lzm_chatPollServer.thisUser, thisClass.lzm_chatServerEvaluation.internal_users,
        thisClass.lzm_chatServerEvaluation.external_users, thisClass.lzm_chatServerEvaluation.active_chat_reco);
    thisClass.lzm_chatDisplay.createActiveChatPanel(thisClass.lzm_chatServerEvaluation.external_users,
        thisClass.lzm_chatServerEvaluation.internal_users, thisClass.lzm_chatServerEvaluation.internal_departments,
        thisClass.lzm_chatServerEvaluation.chatObject, false);

    var thisInviteOperator = $('#invite-operator');
    var thisForwardChat = $('#forward-chat');
    var thisAddQrd = $('#add-qrd');

    thisInviteOperator.click(function () {
        thisClass.lzm_chatDisplay.createOperatorInviteHtml('invite', thisClass.lzm_chatServerEvaluation.internal_departments,
            thisClass.lzm_chatServerEvaluation.internal_users, thisClass.lzm_chatPollServer.thisUser,
            thisClass.lzm_chatPollServer.chosenProfile, id, b_id, chat_id);
        thisClass.lzm_chatDisplay.showForwardMessages('invite');
    });

    thisForwardChat.click(function () {
        thisClass.lzm_chatDisplay.createOperatorInviteHtml('forward', thisClass.lzm_chatServerEvaluation.internal_departments,
            thisClass.lzm_chatServerEvaluation.internal_users, thisClass.lzm_chatPollServer.thisUser,
            thisClass.lzm_chatPollServer.chosenProfile, id, b_id, chat_id);
        thisClass.lzm_chatDisplay.showForwardMessages('forward');
    });

    thisAddQrd.click(function() {
        showQrd(id + '~' + b_id, 'chat');
    });
};

/**
 * Decline to chat with an external chat (and thus remove the highlighting)
 * @param id
 * @param b_id
 * @param chat_id
 * @param chat_no
 */
ChatUserActionsClass.prototype.refuseExternalChat = function (id, b_id, chat_id, chat_no) {
    this.removeForwardFromList(id, b_id);

    this.lzm_chatServerEvaluation.chatObject[this.active_chat_reco].status = 'declined';

    this.lzm_chatPollServer.stopPolling();

    this.lzm_chatPollServer.addToOutboundQueue('p_accepted_chat_0_va', id, 'nonumber');
    this.lzm_chatPollServer.addToOutboundQueue('p_accepted_chat_0_vb', b_id, 'nonumber');
    this.lzm_chatPollServer.addToOutboundQueue('p_accepted_chat_0_vc', chat_id, 'nonumber');
    this.lzm_chatPollServer.addToOutboundQueue('p_accepted_chat_0_vd', 'DeclineChat', 'nonumber');

    this.lzm_chatPollServer.pollServer(this.lzm_chatPollServer.fillDataObject(), 'shout');

    /*var new_chat = {};
    new_chat.id = md5(String(Math.random())).substr(0, 32);
    new_chat.rp = '';
    new_chat.sen = '0000000';
    new_chat.rec = '';
    new_chat.reco = this.lzm_chatServerEvaluation.active_chat_reco;
    var tmpdate = new Date();
    new_chat.date = (tmpdate.getTime() / 1000);
    new_chat.date_human = this.lzm_commonTools.pad(tmpdate.getDate(), 2) + '.' +
        this.lzm_commonTools.pad((tmpdate.getMonth() + 1), 2) + '.' + tmpdate.getFullYear();
    new_chat.time_human = this.lzm_commonTools.pad(tmpdate.getHours(), 2) + ':' +
        this.lzm_commonTools.pad(tmpdate.getMinutes(), 2) + ':' + this.lzm_commonTools.pad(tmpdate.getSeconds(), 2);
    new_chat.text = t('<!--this_op_name--> has declined the chat!',
        [['<!--this_op_name-->',this.lzm_chatServerEvaluation.myName]]);

    this.lzm_chatServerEvaluation.addNewChat(new_chat);*/

    //this.setActiveChat('', '', '', { id:'', b_id:'', b_chat:{ id:'' } });
    this.lzm_chatDisplay.showRefusedChat(this.lzm_chatServerEvaluation.internal_departments,
        this.lzm_chatServerEvaluation.internal_users, this.lzm_chatServerEvaluation.external_users,
        this.lzm_chatServerEvaluation.chats, this.lzm_chatServerEvaluation.chatObject, this.lzm_chatPollServer.thisUser,
        this.lzm_chatServerEvaluation.global_errors, this.lzm_chatPollServer.chosenProfile);
    //this.lzm_chatDisplay.noUserSwitchBackground();
};

/**
 * Leave an open external chat
 * @param id
 * @param b_id
 * @param chat_id
 * @param chat_no
 */
ChatUserActionsClass.prototype.leaveExternalChat = function (id, b_id, chat_id, chat_no) {
    this.deleteChatInput(id * '~' + b_id);
    //this.lzm_chatServerEvaluation.deletePropertyFromChatObject(this.active_chat);
    this.removeForwardFromList(id, b_id);
    if ($.inArray(id + '~' + b_id, this.lzm_chatDisplay.openChats) != -1 && this.lzm_chatServerEvaluation.chatObject[id + '~' + b_id].status != 'left') {
        this.lzm_chatServerEvaluation.chatObject[id + '~' + b_id].status = 'left';
        this.lzm_chatPollServer.stopPolling();

        /*if (this.lzm_chatServerEvaluation.rec_posts.length > 0) {
            this.lzm_chatPollServer.addToOutboundQueue('p_rec_posts', this.lzm_chatServerEvaluation.rec_posts.join('><'), 'nonumber');
        }*/
        this.lzm_chatPollServer.addToOutboundQueue('p_accepted_chat_0_va', id, 'nonumber');
        this.lzm_chatPollServer.addToOutboundQueue('p_accepted_chat_0_vb', b_id, 'nonumber');
        this.lzm_chatPollServer.addToOutboundQueue('p_accepted_chat_0_vc', chat_id, 'nonumber');
        this.lzm_chatPollServer.addToOutboundQueue('p_accepted_chat_0_vd', 'CloseChat', 'nonumber');

        this.lzm_chatPollServer.pollServer(this.lzm_chatPollServer.fillDataObject(), 'shout');
    }

    var new_chat = {};
    new_chat.id = md5(String(Math.random())).substr(0, 32);
    new_chat.rp = '';
    new_chat.sen = '0000000';
    new_chat.rec = '';
    new_chat.reco = this.lzm_chatServerEvaluation.active_chat_reco;
    var tmpdate = new Date();
    new_chat.date = (tmpdate.getTime() / 1000);
    new_chat.date_human = this.lzm_commonTools.pad(tmpdate.getDate(), 2) + '.' +
        this.lzm_commonTools.pad((tmpdate.getMonth() + 1), 2) + '.' + tmpdate.getFullYear();
    new_chat.time_human = this.lzm_commonTools.pad(tmpdate.getHours(), 2) + ':' +
        this.lzm_commonTools.pad(tmpdate.getMinutes(), 2) + ':' + this.lzm_commonTools.pad(tmpdate.getSeconds(), 2);
    new_chat.text = t('<!--this_op_name--> has left the chat!',
        [['<!--this_op_name-->',this.lzm_chatServerEvaluation.myName]]);

    this.lzm_chatServerEvaluation.addNewChat(new_chat);
    this.lzm_chatInputEditor.setHtml('');
    var tmp_openchats = [];
    for (var i = 0; i < this.open_chats.length; i++) {
        if (this.open_chats[i] != id + '~' + b_id) {
            tmp_openchats.push(this.open_chats[i]);
        }
    }
    this.open_chats = tmp_openchats;
    this.lzm_chatDisplay.openChats = this.open_chats;

    this.setActiveChat('', '', '', { id:'', b_id:'', b_chat:{ id:'' } });

    this.lzm_chatDisplay.showLeaveChat(this.lzm_chatServerEvaluation.internal_departments,
        this.lzm_chatServerEvaluation.internal_users, this.lzm_chatServerEvaluation.external_users,
        this.lzm_chatServerEvaluation.chats, this.lzm_chatServerEvaluation.chatObject, this.lzm_chatPollServer.thisUser,
        this.lzm_chatServerEvaluation.global_errors, this.lzm_chatPollServer.chosenProfile);
    //this.lzm_chatDisplay.noUserSwitchBackground();
};

/**
 * Invite another operator to join in a chat
 * @param guest_id
 * @param guest_b_id
 * @param chat_id
 * @param invite_id
 * @param invite_name
 * @param invite_group
 * @param chat_no
 */
ChatUserActionsClass.prototype.inviteOtherOperator = function (guest_id, guest_b_id, chat_id, invite_id, invite_name, invite_group, chat_no) {
    this.lzm_chatDisplay.createOperatorInviteHtml('invite', this.lzm_chatServerEvaluation.internal_departments,
        this.lzm_chatServerEvaluation.internal_users, this.lzm_chatPollServer.thisUser,
        this.lzm_chatPollServer.chosenProfile);
    this.lzm_chatPollServer.stopPolling();

    var pForwardsVObject = {
        a_: chat_id,
        b_: invite_id,
        c_: '',
        d_: this.lzm_chatServerEvaluation.myId,
        e_: invite_group,
        f_: guest_id,
        g_: guest_b_id,
        h_: 1
    };
    this.lzm_chatPollServer.addToOutboundQueue('p_forwards_v', pForwardsVObject);

    this.lzm_chatPollServer.pollServer(this.lzm_chatPollServer.fillDataObject(), 'shout');

    var new_chat = {};
    new_chat.id = md5(String(Math.random())).substr(0, 32);
    new_chat.rp = '';
    new_chat.sen = '0000000';
    new_chat.rec = '';
    new_chat.reco = active_chat_reco;
    var tmpdate = new Date();
    new_chat.date = (tmpdate.getTime() / 1000);
    new_chat.date_human = pad(tmpdate.getDate(), 2) + '.' + pad((tmpdate.getMonth() + 1), 2) + '.' + tmpdate.getFullYear();
    new_chat.time_human = pad(tmpdate.getHours(), 2) + ':' + pad(tmpdate.getMinutes(), 2) + ':' + pad(tmpdate.getSeconds(), 2);
    new_chat.text = t('<!--that_user--> was invited!', [['<!--that_user-->',invite_name]]);

    this.lzm_chatServerEvaluation.addNewChat(new_chat);

    this.lzm_chatDisplay.finishOperatorInvitation();
};

/**
 * forward a chat to another operator
 * @param id
 * @param b_id
 * @param chat_id
 * @param forward_id
 * @param forward_name
 * @param forward_group
 * @param forward_text
 * @param chat_no
 */
ChatUserActionsClass.prototype.forwardChat = function () {
    if (typeof this.forwardData.id != 'undefined') {
        this.deleteChatInput(this.active_chat_reco);
        //this.lzm_chatServerEvaluation.deletePropertyFromChatObject(this.active_chat);
        this.lzm_chatServerEvaluation.chatObject[this.active_chat_reco].status = 'left';
        //this.lzm_chatDisplay.closedChats.push(this.lzm_chatServerEvaluation.active_chat_reco);
        this.removeForwardFromList(this.forwardData.id, this.forwardData.b_id);
        this.lzm_chatDisplay.createOperatorInviteHtml('forward', this.lzm_chatServerEvaluation.internal_departments,
            this.lzm_chatServerEvaluation.internal_users, this.lzm_chatPollServer.thisUser,
            this.lzm_chatPollServer.chosenProfile);
        this.lzm_chatPollServer.stopPolling();

        var pForwardsVObject = {
            a_: this.forwardData.chat_id,
            b_: this.forwardData.forward_id,
            c_: this.forwardData.forward_text,
            d_: this.lzm_chatServerEvaluation.myId,
            e_: this.forwardData.forward_group,
            f_: this.forwardData.id,
            g_: this.forwardData.b_id,
            h_: 0
        };
        this.lzm_chatPollServer.addToOutboundQueue('p_forwards_v', pForwardsVObject);

        this.lzm_chatPollServer.pollServer(this.lzm_chatPollServer.fillDataObject(), 'shout');

        var new_chat = {};
        new_chat.id = md5(String(Math.random())).substr(0, 32);
        new_chat.rp = '';
        new_chat.sen = '0000000';
        new_chat.rec = '';
        new_chat.reco = this.lzm_chatServerEvaluation.active_chat_reco;
        var tmpdate = new Date();
        new_chat.date = (tmpdate.getTime() / 1000);
        new_chat.date_human = this.lzm_commonTools.pad(tmpdate.getDate(), 2) + '.' +
            this.lzm_commonTools.pad((tmpdate.getMonth() + 1), 2) + '.' + tmpdate.getFullYear();
        new_chat.time_human = this.lzm_commonTools.pad(tmpdate.getHours(), 2) + ':' +
            this.lzm_commonTools.pad(tmpdate.getMinutes(), 2) + ':' + this.lzm_commonTools.pad(tmpdate.getSeconds(), 2);
        new_chat.text = t('Chat was forwarded to <!--that_op_name-->!', [['<!--that_op_name-->',this.forwardData.forward_name]]);
        this.lzm_chatServerEvaluation.addNewChat(new_chat);

        var tmp_openchats = [];
        for (var i = 0; i < this.open_chats.length; i++) {
            if (this.open_chats[i] != this.forwardData.id + '~' + this.forwardData.b_id) {
                tmp_openchats.push(this.open_chats[i]);
            }
        }
        this.open_chats = tmp_openchats;
        this.lzm_chatDisplay.openChats = this.open_chats;

        this.setActiveChat('', '', '', { id:'', b_id:'', b_chat:{ id:'' } });

        this.lzm_chatDisplay.finishChatForward();
        //this.lzm_chatDisplay.noUserSwitchBackground();
    }
};

ChatUserActionsClass.prototype.selectOperatorForForwarding = function (id, b_id, chat_id, forward_id, forward_name, forward_group, forward_text, chat_no) {
    this.forwardData = {id:id, b_id:b_id, chat_id:chat_id, forward_id:forward_id, forward_name:forward_name,
        forward_group:forward_group, forward_text:forward_text, chat_no:chat_no};
    this.lzm_chatDisplay.highlightChosenOperator(forward_id,forward_group);
};

ChatUserActionsClass.prototype.handleUploadRequest = function(fuprId, fuprName, id, b_id, type, chatId) {
    var numericType = 0;
    if (type == 'allow') {
        numericType = 2;
    } else if (type == 'deny') {
        numericType = 0;
    }
    this.lzm_chatPollServer.stopPolling();
    this.lzm_chatPollServer.addToOutboundQueue('p_permissions_va', fuprId, 'nonumber');
    this.lzm_chatPollServer.addToOutboundQueue('p_permissions_vb', numericType, 'nonumber');
    this.lzm_chatPollServer.addToOutboundQueue('p_permissions_vc', chatId, 'nonumber');

    var date = $.now();
    var tmpdate = new Date(date);
    var new_chat = {id: md5(String(Math.random())).substr(0, 32),
        date: Math.floor(date / 1000),
        date_human: this.lzm_commonTools.getHumanDate(tmpdate, 'date', this.userLanguage),
        time_human: this.lzm_commonTools.getHumanDate(tmpdate, 'time', this.userLanguage),
        rec: '', rp: '', sen: '0000000',
        text: t('The visitor was allowed to upload <!--file_name--> to the server.',
            [['<!--file_name-->','<b>' + fuprName + '</b>']]) + ' ' +
            t('As soon as the file has been uploaded to the server you will get the possibility to download the file.'),
        reco: id + '~' + b_id
    };
    this.lzm_chatServerEvaluation.chats.push(new_chat);

    this.lzm_chatServerEvaluation.chatObject[this.active_chat_reco].fuprDone = fuprId;
    this.lzm_chatPollServer.pollServer(this.lzm_chatPollServer.fillDataObject(), 'shout');
};

ChatUserActionsClass.prototype.replaceLinks = function(myText) {
    var i, replacement;
    var links = myText.match(/href="#" onclick="openLink\('.*?'\)"/);
    if (typeof links != 'undefined' && links != null) {
        for (i=0; i<links.length; i++) {
            var address = links[i].replace(/href="#" onclick="openLink\('/,'').replace(/'\)"/,'');
            var replacement = 'href="' + address + '" target="_blank"';
            myText = myText.replace(links[i],replacement).replace(/class="lz_chat_link" /,'');
        }
    }
    return myText;
};

// ****************************** Some more general tools ****************************** //
/**
 * Send a chat message to the server
 * @param new_chat
 */
ChatUserActionsClass.prototype.sendChatMessage = function (new_chat) {
    chatText = this.replaceLinks(new_chat.text);
    this.lzm_chatPollServer.stopPolling();
    var pPostsVObject = {
        a: chatText,
        b:new_chat.reco,
        c:new_chat.id,
        d: '',
        e: ''
    };
    this.lzm_chatPollServer.addToOutboundQueue('p_posts_v', pPostsVObject);

    this.lzm_chatPollServer.pollServer(this.lzm_chatPollServer.fillDataObject(), 'shout');
};

/**
 * Set the active_chat, active_chat_realname and thisUser properties of Display and Evaluation classes
 * @param active_chat
 * @param active_chat_reco
 * @param active_chat_realname
 * @param thisUser
 */
ChatUserActionsClass.prototype.setActiveChat = function (active_chat, active_chat_reco, active_chat_realname, thisUser) {
    this.lzm_chatDisplay.active_chat = active_chat;
    this.lzm_chatServerEvaluation.active_chat = active_chat;
    this.active_chat = active_chat;
    this.lzm_chatDisplay.active_chat_reco = active_chat_reco;
    this.lzm_chatServerEvaluation.active_chat_reco = active_chat_reco;
    this.active_chat_reco = active_chat_reco;
    this.lzm_chatDisplay.active_chat_realname = active_chat_realname;
    this.lzm_chatPollServer.thisUser = thisUser;
    this.lzm_chatDisplay.thisUser = thisUser;
};

/**
 * remove an external forward from the list
 * @param id
 * @param b_id
 */
ChatUserActionsClass.prototype.removeForwardFromList = function (id, b_id) {
    var tmp_external_forwards = [];
    var tmp_extForwardIdList = [];
    var removeExternalForwardId = [];
    for (var extFwdIndex = 0; extFwdIndex < this.lzm_chatServerEvaluation.external_forwards.length; extFwdIndex++) {
        if (this.lzm_chatServerEvaluation.external_forwards[extFwdIndex].u != id + '~' + b_id) {
            tmp_external_forwards.push(this.lzm_chatServerEvaluation.external_forwards[extFwdIndex]);
        } else {
            removeExternalForwardId.push(this.lzm_chatServerEvaluation.external_forwards[extFwdIndex].id);
        }
    }
    for (var extFwdIdIndex = 0; extFwdIdIndex < this.lzm_chatServerEvaluation.extForwardIdList.length; extFwdIdIndex++) {
        if ($.inArray(this.lzm_chatServerEvaluation.extForwardIdList[extFwdIdIndex], removeExternalForwardId) == -1) {
            tmp_extForwardIdList.push(this.lzm_chatServerEvaluation.extForwardIdList[extFwdIdIndex]);
        }
    }
    this.lzm_chatServerEvaluation.external_forwards = tmp_external_forwards;
    this.lzm_chatServerEvaluation.extForwardIdList = tmp_extForwardIdList;
};

ChatUserActionsClass.prototype.manageTranslations = function() {
    var thisClass = this;
    this.lzm_commonTranslation.listAvailableLanguages();
    var foo = 0;
    var counter = 0;
    foo = setInterval(function(){
        counter++;
        if (thisClass.lzm_commonTranslation.availableLanguages.length > 0 || counter >= 100) {
            clearInterval(foo);
            thisClass.lzm_chatDisplay.createTranslationManagement(thisClass.lzm_commonTranslation.availableLanguages,
            thisClass.lzm_commonTranslation.language);
        }
    },50);
};

ChatUserActionsClass.prototype.editTranslations = function(existingLanguage, newLanguage) {
    var thisClass = this;
    var foo = 0;
    var counter = 0;
    thisClass.lzm_commonTranslation.manageTranslationArray = [];
    var languageToEdit = '';
    if (existingLanguage != '') {
        languageToEdit = existingLanguage;
    } else if (newLanguage != '' && newLanguage.match(/^[a-zA-Z]{2}$/) != null) {
        languageToEdit = newLanguage.toLowerCase();
    } else {
        languageToEdit = thisClass.lzm_commonTranslation.language;
    }

    thisClass.lzm_commonTranslation.fillTranslationArray(false, languageToEdit, 'manage');
    foo = setInterval(function(){
        counter++;
        if (thisClass.lzm_commonTranslation.manageTranslationArray.length > 0 || counter >= 100) {
            clearInterval(foo);
            thisClass.lzm_chatDisplay.editTranslations(languageToEdit,
                thisClass.lzm_commonTranslation.manageTranslationArray,
                thisClass.lzm_commonTranslation.language);
            //console.log(thisClass.lzm_commonTranslation.manageTranslationArray[0]);
        }
    },50);
};

ChatUserActionsClass.prototype.manageUsersettings = function() {
    this.lzm_chatDisplay.createUsersettingsManagement();
};

ChatUserActionsClass.prototype.saveUserSettings = function(settings) {
    //console.log(settings);
    this.chosenProfile.user_volume = settings.volume;
    this.lzm_chatDisplay.volume = settings.volume;
    this.chosenProfile.user_away_after = settings.awayAfterTime;
    this.lzm_chatDisplay.awayAfterTime = settings.awayAfterTime;
    this.chosenProfile.play_incoming_message_sound = settings.playNewMessageSound;
    this.lzm_chatDisplay.playNewMessageSound = settings.playNewMessageSound;
    this.chosenProfile.play_incoming_chat_sound = settings.playNewChatSound;
    this.lzm_chatDisplay.playNewChatSound = settings.playNewChatSound;
    this.chosenProfile.repeat_incoming_chat_sound = settings.repeatNewChatSound;
    this.lzm_chatDisplay.repeatNewChatSound = settings.repeatNewChatSound;

    this.lzm_commonStorage.loadProfileData();
    //console.log(this.lzm_commonStorage.getProfileByIndex(this.chosenProfile.index));
    //console.log(this.chosenProfile);
    var tmpProfile = this.lzm_commonTools.clone(this.chosenProfile);
    if (this.chosenProfile.server_url.indexOf(':') != -1) {
        var tmpUrlArray = this.chosenProfile.server_url.split(':');
        var tmpUrl = tmpUrlArray[0];
        tmpUrlArray = tmpUrlArray[1].split('/');
        for (var i=1; i< tmpUrlArray.length; i++) {
            tmpUrl += '/' + tmpUrlArray[i];
        }
        tmpProfile.server_url = tmpUrl;
    }
    //console.log(tmpProfile);
    this.lzm_commonStorage.saveProfile(tmpProfile);

};

ChatUserActionsClass.prototype.editQrd = function() {
    var thisClass = this;
    var resource = {};
    for (var i=0; i<thisClass.lzm_chatServerEvaluation.resources.length; i++) {
        if (thisClass.lzm_chatServerEvaluation.resources[i].rid == thisClass.lzm_chatDisplay.selectedResource) {
            resource = thisClass.lzm_chatServerEvaluation.resources[i];
            break;
        }
    }
    var newRid = resource.rid;
    var newPid = resource.pid;
    var newRank = resource.ra;
    newType = resource.ty;
    var newTitle, newType, newText, newSize, newTags;

    thisClass.lzm_chatDisplay.editQrd(resource);

    var editHtmlResource = $('.qrd-edit-html-resource');
    var editLinkResource = $('.qrd-edit-link-resource');
    var editFolderResource = $('.qrd-edit-folder-resource');
    switch(Number(newType)) {
        case 0: // Folder
            editHtmlResource.css('display', 'none');
            editLinkResource.css('display', 'none');
            editFolderResource.css('display', 'block');
            break;
        case 1: // HTML resource
            editLinkResource.css('display', 'none');
            editFolderResource.css('display', 'none');
            editHtmlResource.css('display', 'block');
            break;
        case 2: // URL
            editFolderResource.css('display', 'none');
            editHtmlResource.css('display', 'none');
            editLinkResource.css('display', 'block');
    }

    $('#save-edited-qrd').click(function() {
        var editTitle = $('#qrd-edit-title').val();
        var editTags = $('#qrd-edit-tags').val();
        //console.log('Save clicked');
        newTitle = editTitle;
        switch (Number(newType)) {
            case 0:
                newText = editTitle;
                newTags = '';
                newSize = newTitle.length;
                break;
            case 1:
                newText = $('#qrd-edit-text').val();
                newSize = newText.length + newTitle.length;
                newTags = editTags;
                break;
            case 2:
                newText = $('#qrd-edit-url').val();
                newSize = newText.length + newTitle.length;
                newTags = editTags;
                break;
        }
        $('#qrd-edit').remove();
        thisClass.lzm_chatPollServer.pollServerResource({
            rid: newRid,
            pid: newPid,
            ra: newRank,
            ti: newTitle,
            ty: newType,
            text: newText,
            si: newSize,
            t: newTags,
            di: 0
        });
        $('#resource-' + newRid).find('span.qrd-title-span').html(newTitle);
    });

    $('#cancel-edited-qrd').click(function() {
        //console.log('Cancel clicked');
        $('#qrd-edit').remove();
    });
};

ChatUserActionsClass.prototype.previewQrd = function (chatPartner) {
    var thisClass = this;
    var resource = {};
    for (var i=0; i<thisClass.lzm_chatServerEvaluation.resources.length; i++) {
        if (thisClass.lzm_chatServerEvaluation.resources[i].rid == thisClass.lzm_chatDisplay.selectedResource) {
            resource = thisClass.lzm_chatServerEvaluation.resources[i];
            break;
        }
    }
    thisClass.lzm_chatDisplay.previewQrd(resource, chatPartner);

    $('#cancel-preview-qrd').click(function() {
        //console.log('Cancel clicked');
        $('#preview-qrd').removeClass('ui-disabled');
        $('#qrd-preview').remove();
    });
};

ChatUserActionsClass.prototype.addQrd = function() {
    var thisClass = this;
    var resource = {};
    for (var i=0; i<thisClass.lzm_chatServerEvaluation.resources.length; i++) {
        if (thisClass.lzm_chatServerEvaluation.resources[i].rid == thisClass.lzm_chatDisplay.selectedResource) {
            resource = thisClass.lzm_chatServerEvaluation.resources[i];
            break;
        }
    }
    var newRid = md5(Math.random().toString());
    var newPid = resource.rid;
    var newRank = Number(resource.ra) + 1;
    var newTitle, newType, newText, newSize, newTags;

    thisClass.lzm_chatDisplay.addQrd();

    var typeSelection = $('#qrd-add-type');
    var addHtmlResource = $('.qrd-add-html-resource');
    var addLinkResource = $('.qrd-add-link-resource');
    var addFolderResource = $('.qrd-add-folder-resource');
    typeSelection.change(function() {
        switch (Number(typeSelection.val())) {
            case 0: // Folder
                addHtmlResource.css('display', 'none');
                addLinkResource.css('display', 'none');
                addFolderResource.css('display', 'block');
                break;
            case 1: // HTML resource
                addLinkResource.css('display', 'none');
                addFolderResource.css('display', 'none');
                addHtmlResource.css('display', 'block');
                break;
            case 2: // URL
                addFolderResource.css('display', 'none');
                addHtmlResource.css('display', 'none');
                addLinkResource.css('display', 'block');
        }
    });

    $('#save-new-qrd').click(function() {
        var addTitle = $('#qrd-add-title').val();
        var addTags = $('#qrd-add-tags').val();
        //console.log('Save clicked');
        newTitle = addTitle;
        newType = typeSelection.val();
        switch (Number(typeSelection.val())) {
            case 0:
                newText = addTitle;
                newTags = '';
                newSize = newTitle.length;
                break;
            case 1:
                newText = $('#qrd-add-text').val();
                newSize = newText.length + newTitle.length;
                newTags = addTags;
                break;
            case 2:
                newText = $('#qrd-add-url').val();
                newSize = newText.length + newTitle.length;
                newTags = addTags;
                break;
        }
        $('#qrd-add').remove();
        thisClass.lzm_chatPollServer.pollServerResource({
            rid: newRid,
            pid: newPid,
            ra: newRank,
            ti: newTitle,
            ty: newType,
            text: newText,
            si: newSize,
            t: newTags,
            di: 0
        });
        var onclickAction = 'onclick="handleResourceClickEvents(\'' + newRid + '\')"';
        var newEntryHtml = '<div id="resource-' + newRid + '" class="resource-div" ' +
            'style="padding-left: ' + (20 * newRank) + 'px; margin: 4px 0px;">';
        if (newType == 0) {
            newEntryHtml += '<span id="resource-' + newRid + '-open-mark" style=\'display: inline-block; width: 7px; ' +
                'height: 7px; border: 1px solid #aaa; background-color: #f1f1f1; ' +
                thisClass.lzm_chatDisplay.addBrowserSpecificGradient('background-image: url("img/plus.png")') + '; ' +
                'background-position: center; background-repeat: no-repeat; margin-right: 4px; cursor: pointer;\'';
            newEntryHtml += ' onclick="handleResourceClickEvents(\'' + newRid + '\')"';
            newEntryHtml += '></span>';
        } else {
            newEntryHtml += '<span style="display: inline-block; width: 9px; height: 9px; margin-right: 4px;"></span>';
        }
        newEntryHtml += '<span style=\'background-image: url("' + thisClass.lzm_chatDisplay.getResourceIcon(newType) + '"); ' +
            'background-position: left center; background-repeat: no-repeat; padding: 2px;\'>' +
            '<span class="qrd-title-span" style="padding-left: 20px; cursor: pointer;" ' + onclickAction + '>' +
            newTitle + '</span>' +
            '</span></div>';
        if (newType == 0) {
            newEntryHtml += '<div id="folder-' + newRid + '" style="display: none;"></div>';
        }
        $('#folder-' + newPid).append(newEntryHtml);
    });

    $('#cancel-new-qrd').click(function() {
        //console.log('Cancel clicked');
        $('#qrd-add').remove();
    });

};

ChatUserActionsClass.prototype.deleteQrd = function() {
    var resource = {};
    for (var i=0; i<this.lzm_chatServerEvaluation.resources.length; i++) {
        if (this.lzm_chatServerEvaluation.resources[i].rid == this.lzm_chatDisplay.selectedResource) {
            resource = this.lzm_chatServerEvaluation.resources[i];
            break;
        }
    }
    resource.di = 1;
    this.lzm_chatPollServer.pollServerResource(resource);
    $('#resource-' + resource.rid).remove();
    if (resource.ty == 0) {
        $('#folder-' + resource.rid).remove();
    }
};
