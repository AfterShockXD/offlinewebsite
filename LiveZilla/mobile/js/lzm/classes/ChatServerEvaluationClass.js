/****************************************************************************************
 * LiveZilla ChatServerEvaluationClass.js
 *
 * Copyright 2013 LiveZilla GmbH
 * All rights reserved.
 * LiveZilla is a registered trademark.
 *
 ***************************************************************************************/

function ChatServerEvaluationClass(lzm_commonTools, chosenProfile, lzm_chatTimeStamp) {

    // load the configuration file
    this.lzm_commonConfig = new CommonConfigClass();
    this.lzm_commonTools = lzm_commonTools;
    this.lzm_chatTimeStamp = lzm_chatTimeStamp;

    // variables filled from the server response
    this.myName = '';
    this.myId = '';
    this.myGroup = '';
    this.myUserId = '';
    this.chosen_profile = {};
    this.serverUrl = chosenProfile.server_url;
    this.serverProtocol = chosenProfile.server_protocol;
    this.loginTime = $.now();

    this.global_configuration = {};
    this.extForwardIdList = [];
    this.external_forwards = [];
    this.chats = [];
    this.active_chat = '';
    this.active_chat_reco = '';
    this.external_users = [];
    this.newExternalUsers = [];
    this.changedExternalUsers = [];
    this.login_data = {};
    this.extUserIdList = [];
    this.internal_departments = [];
    this.internal_users = [];
    this.global_errors = [];
    this.wps = [];
    this.chatIdList = [];
    this.browserChatIdList = [];
    this.chatObject = {};
    this.chatPartners = {};
    this.rec_posts = [];
    //this.incoming_chats = [];
    this.external_c = [];
    this.fuprs = [];
    this.fuprIdList = [];
    this.fuprDownloadIdList = [];
    this.settingsDialogue = false;
    this.resources = [];
    this.resourceIdList = [];
    this.resourceLastEdited = 0;

    this.pollFrequency = 0;
    this.timeoutClients = 0;
    this.siteName = '';
    this.defaultLanguage = '';

    this.userLanguage = '';

    this.new_ext_u = false;
    this.new_ext_f = false;
    this.new_ext_c = false;
    this.new_usr_p = false;
    this.new_int_d = false;
    this.new_int_u = false;
    this.new_gl_e = false;
}

/**
 * Add a new chat created by a local method to the chats array
 * @param new_chat
 */
ChatServerEvaluationClass.prototype.addNewChat = function (new_chat) {
    new_chat.text = this.replaceLinks(new_chat.text);
    this.chats.push(new_chat);
    return true;
};

/**
 * Get the server's response for the login
 * @param xmlDoc
 */
ChatServerEvaluationClass.prototype.getLogin = function (xmlDoc) {
    var thisClass = this;
    $(xmlDoc).find('login').each(function () {
        var login = $(this);
        login.children('login_return').each(function () {
            var myReturn = $(this);
            var myAttributes = myReturn[0].attributes;
            for (var attrIndex = 0; attrIndex < myAttributes.length; attrIndex++) {
                thisClass.login_data[myAttributes[attrIndex].nodeName] = lz_global_base64_url_decode(myAttributes[attrIndex].nodeValue);
            }

        });
        thisClass.myName = thisClass.login_data.name;
        thisClass.myId = thisClass.login_data.sess;
        if (typeof thisClass.login_data != 'undefined' && typeof thisClass.login_data.timediff != 'undefined') {
            thisClass.lzm_chatTimeStamp.setTimeDifference(thisClass.login_data.timediff);
        }
    });
};

/**
 * Get the global configuration from the server's response and create an objet with its contents
 * @param xmlDoc
 */
ChatServerEvaluationClass.prototype.getGlobalConfiguration = function (xmlDoc) {
    var thisClass = this;
    var myHash = '';
    $(xmlDoc).find('gl_c').each(function () {
        var gl_c = $(this);
        if (typeof thisClass.global_configuration.toplevel == 'undefined') {
            thisClass.global_configuration.toplevel = [];
        }
        if (typeof thisClass.global_configuration.site == 'undefined') {
            thisClass.global_configuration.site = {};
        }
        if (typeof thisClass.global_configuration.php_cfg_vars == 'undefined') {
            thisClass.global_configuration.php_cfg_vars = {};
        }
        $(gl_c).children('conf').each(function () {
            var conf = $(this);
            var new_conf = {};
            new_conf.key = lz_global_base64_url_decode(conf.attr('key'));
            new_conf.value = lz_global_base64_url_decode(conf.attr('value'));
            new_conf.subkeys = {};
            $(conf).find('sub').each(function () {
                new_conf.subkeys[lz_global_base64_url_decode($(this).attr('key'))] = lz_global_base64_url_decode($(this).text());
            });
            thisClass.global_configuration.toplevel.push(new_conf);
        });
        $(gl_c).children('site').each(function () {
            var site = $(this);
            var index = lz_global_base64_url_decode(site.attr('index'));
            if (typeof thisClass.global_configuration.site[index] == 'undefined') {
                thisClass.global_configuration.site[index] = [];
            }
            $(site).find('conf').each(function () {
                var conf = $(this);
                var new_conf = {};
                new_conf.key = lz_global_base64_url_decode(conf.attr('key'));
                new_conf.value = lz_global_base64_url_decode(conf.attr('value'));
                new_conf.subkeys = {};
                $(conf).find('sub').each(function () {
                    new_conf.subkeys[lz_global_base64_url_decode($(this).attr('key'))] = lz_global_base64_url_decode($(this).text());
                    //console.log(lz_global_base64_url_decode($(this).attr('key')) + ' --- ' + lz_global_base64_url_decode($(this).text()));
                });
                thisClass.global_configuration.site[index].push(new_conf);
            });
        });
        $(gl_c).children('php_cfg_vars').each(function () {
            thisClass.global_configuration.php_cfg_vars['post_max_size'] = lz_global_base64_url_decode($(this).attr('post_max_size'));
            thisClass.global_configuration.php_cfg_vars['upload_max_filesize'] = lz_global_base64_url_decode($(this).attr('upload_max_filesize'));
        });

        myHash = lz_global_base64_url_decode(gl_c.attr('h'));

        for (var i=0; i<thisClass.global_configuration.site[0].length; i++) {
            if (thisClass.global_configuration.site[0][i].key == 'poll_frequency_clients') {
                thisClass.pollFrequency = thisClass.global_configuration.site[0][i].value;
            }
            if (thisClass.global_configuration.site[0][i].key == 'timeout_clients') {
                thisClass.timeoutClients = thisClass.global_configuration.site[0][i].value;
            }
            if (thisClass.global_configuration.site[0][i].key == 'gl_site_name') {
                thisClass.siteName = thisClass.global_configuration.site[0][i].value;
                $('title').html(thisClass.siteName);
            }
            if (thisClass.global_configuration.site[0][i].key == 'gl_default_language') {
                thisClass.defaultLanguage =thisClass.global_configuration.site[0][i].value;
            }
        }
    });

    return myHash;
};

ChatServerEvaluationClass.prototype.debuggingReadKeyValuePairFromConfig = function(keyPart) {
    var i;
    console.log('Top level');
    for (i=0; i<this.global_configuration.toplevel.length; i++) {
        if (this.global_configuration.toplevel[i].key.indexOf(keyPart) != -1 && this.global_configuration.toplevel[i].value != '') {
            var index = this.lzm_commonTools.pad(i, 4, 0);
            console.log(index + ' : ' + this.global_configuration.toplevel[i].key + ' - ' + this.global_configuration.toplevel[i].value);
        }
    }
    for (var key in this.global_configuration.site) {
        console.log('');
        console.log('Site ' + key);
        for (i=0; i<this.global_configuration.site[key].length; i++) {
            var index = this.lzm_commonTools.pad(i, 4, 0);
            if (this.global_configuration.site[key][i].key.indexOf(keyPart) != -1 && this.global_configuration.site[key][i].value != '') {
                console.log(index + ' : ' + this.global_configuration.site[key][i].key + ' - ' + this.global_configuration.site[key][i].value);
            }
        }
    }
};

/**
 * Get the requests for forwardings of chats from the server's xml response
 * @param xmlDoc
 */
ChatServerEvaluationClass.prototype.getExternalForward = function (xmlDoc) {
    var thisClass = this;
    var myHash = '';
    $(xmlDoc).find('ext_f').each(function () {
        //console.log('new ext f');
        thisClass.new_ext_f = true;
        var ext_f = $(this);
        $(ext_f).find('fw').each(function () {
            var fw = $(this);
            var new_forward = {};
            var myAttributes = fw[0].attributes;
            for (var attrIndex = 0; attrIndex < myAttributes.length; attrIndex++) {
                new_forward[myAttributes[attrIndex].nodeName] = lz_global_base64_url_decode(myAttributes[attrIndex].nodeValue);
            }
            if ($.inArray(new_forward.id, thisClass.extForwardIdList) == -1) {
                thisClass.extForwardIdList.push(new_forward.id);
                thisClass.external_forwards.push(new_forward);

                var fwdByName = new_forward.s;
                for (var intUserIndex=0; intUserIndex< thisClass.internal_users.length; intUserIndex++) {
                    if (new_forward.s == thisClass.internal_users[intUserIndex].id) {
                        fwdByName = thisClass.internal_users[intUserIndex].name;
                        break;
                    }
                }

                var new_chat = {};
                new_chat.id = md5(String(Math.random())).substr(0, 32);
                new_chat.rp = '';
                new_chat.sen = '0000000';
                new_chat.rec = '';
                new_chat.reco = new_forward.u;
                var tmpdate = new Date();
                new_chat.date = (tmpdate.getTime() / 1000);
                new_chat.date_human = thisClass.lzm_commonTools.pad(tmpdate.getDate(), 2) + '.' +
                    thisClass.lzm_commonTools.pad((tmpdate.getMonth() + 1), 2) + '.' + tmpdate.getFullYear();
                new_chat.time_human = thisClass.lzm_commonTools.pad(tmpdate.getHours(), 2) + ':' +
                    thisClass.lzm_commonTools.pad(tmpdate.getMinutes(), 2) + ':' + thisClass.lzm_commonTools.pad(tmpdate.getSeconds(), 2);
                new_chat.text = t('Forwarded by <!--fwd_operator-->', [['<!--fwd_operator-->', fwdByName]]);
                if (new_forward.t != '') {
                    new_chat.text += ' ' + t('with comment <!--fwd_comment-->', [['<!--fwd_comment-->', new_forward.t]]);
                }

                thisClass.addNewChat(new_chat);
            } else {
                for (var i = 0; i < thisClass.external_forwards.length; i++) {
                    for (var key in thisClass.external_forwards[i]) {
                        if (new_forward[key] != '' && typeof new_forward[key] != 'undefined') {
                            thisClass.external_forwards[i][key] = new_forward[key];
                        }
                    }
                }
            }
            for (var chatIndex = 0; chatIndex < thisClass.chats.length; chatIndex++) {
                if (new_forward.r == thisClass.myId && (thisClass.chats[chatIndex].sen == new_forward.u ||
                    thisClass.chats[chatIndex].reco == new_forward.u)) {
                    if (thisClass.settingsDialogue || thisClass.chats[chatIndex].sen_id != thisClass.active_chat) {
                        if (thisClass.chats[chatIndex].sen != '0000000' &&
                            thisClass.chats[chatIndex].sen != thisClass.myId &&
                            (thisClass.chats[chatIndex].sen.indexOf('~') != -1)) {
                            if (typeof thisClass.chatObject[thisClass.chats[chatIndex].sen] == 'undefined') {
                                thisClass.chatObject[thisClass.chats[chatIndex].sen] = {
                                    status: 'new', type: 'external', data: [], id: thisClass.chats[chatIndex].sen_id, b_id: thisClass.chats[chatIndex].sen_b_id
                                };
                            }
                            thisClass.chatObject[thisClass.chats[chatIndex].sen]['status'] = 'new';
                            thisClass.chatObject[thisClass.chats[chatIndex].sen]['data'].push(thisClass.chats[chatIndex]);
                        }
                    }
                }
            }
        });
        myHash = lz_global_base64_url_decode(ext_f.attr('h'));
    });
    return myHash;
};

/**
 * Get the external users from the server's xml response
 * @param xmlDoc
 */
ChatServerEvaluationClass.prototype.getExternalUsers = function (xmlDoc) {
    var thisClass = this;
    var myHash = '';
    $(xmlDoc).find('ext_u').each(function () {
        var ext_u = $(this);
        thisClass.new_ext_u = true;

        // Get the user data
        $(ext_u).find('v').each(function () {
            var v = $(this);
            thisClass.addExtUserV(v);
        });
        $(ext_u).find('cd').each(function () {
            var cd = $(this);
            thisClass.addExtUserCd(cd);
        });

        myHash = lz_global_base64_url_decode(ext_u.attr('h'));
    });
    return myHash;
};

/**
 * Add the external user's cd value
 * @param cd
 */
ChatServerEvaluationClass.prototype.addExtUserCd = function (cd) {
    var thisClass = this;
    var cdId = lz_global_base64_url_decode(cd.attr('id'));
    var thisVisitor = {};
    var externalUserIndex = 0;
    var i = 0;
    for (i = 0; i < thisClass.external_users.length; i++) {
        if (thisClass.external_users[i].id == cdId) {
            externalUserIndex = i;
            break;
        }
    }

    var bdExists = false;
    $(cd).find('bd').each(function () {
        var bd = $(this);
        thisClass.addExtUserCdBd(bd, externalUserIndex);
        bdExists = bdExists || true;
    });

    var userIsActive = false;
    if (bdExists) {
        for (i = 0; i < thisClass.external_users[externalUserIndex].b.length; i++) {
            userIsActive = userIsActive || thisClass.external_users[externalUserIndex].b[i].is_active;
            if (typeof thisClass.chatObject[thisClass.external_users[externalUserIndex].id + '~' + thisClass.external_users[externalUserIndex].b[i].id] != 'undefined') {
                if (!thisClass.external_users[externalUserIndex].b[i].is_active) {
                    markVisitorAsLeft(thisClass.external_users[externalUserIndex].id, thisClass.external_users[externalUserIndex].b[i].id);
                    //thisClass.chatObject[thisClass.external_users[externalUserIndex].id + '~' + thisClass.external_users[externalUserIndex].b[i].id].status = 'left';
                    //console.log(thisClass.chatObject[thisClass.external_users[externalUserIndex].id + '~' + thisClass.external_users[externalUserIndex].b[i].id].status);
                }
            }
        }
    }
    thisClass.external_users[externalUserIndex].is_active = userIsActive
    if (!userIsActive) {
        //console.log(thisClass.external_users[externalUserIndex].id + '~' + thisClass.external_users[externalUserIndex].id + '_OVL');
        if (typeof thisClass.chatObject[thisClass.external_users[externalUserIndex].id + '~' + thisClass.external_users[externalUserIndex].id + '_OVL'] != 'undefined') {
            //console.log(thisClass.chatObject[thisClass.external_users[externalUserIndex].id + '~' + thisClass.external_users[externalUserIndex].id + '_OVL']);
            //thisClass.chatObject[thisClass.external_users[externalUserIndex].id + '~' + thisClass.external_users[externalUserIndex].id + '_OVL'].status = 'left';
            markVisitorAsLeft(thisClass.external_users[externalUserIndex].id, thisClass.external_users[externalUserIndex].id + '_OVL');
        }
    }
};

/**
 * Add the external user's bd value to its cd
 * @param bd
 */
ChatServerEvaluationClass.prototype.addExtUserCdBd = function (bd, externalUserIndex) {
    var thisClass = this;
    var bdId = lz_global_base64_url_decode(bd.attr('id'));
    for (var i = 0; i < thisClass.external_users[externalUserIndex].b.length; i++) {
        if (thisClass.external_users[externalUserIndex].b[i].id == bdId) {
            thisClass.external_users[externalUserIndex].b[i].is_active = false;
            //console.log(thisClass.external_users[externalUserIndex].id + '~' + bdId + ' has left!');
            if (typeof thisClass.chatObject[thisClass.external_users[externalUserIndex].id + '~' + bdId] != 'undefined') {
                //thisClass.chatObject[thisClass.external_users[externalUserIndex].id + '~' + bdId].status = 'left';
                markVisitorAsLeft(thisClass.external_users[externalUserIndex].id, bdId);
            }
            break;
        }
    }
};

/**
 * Add the values of the b object to the external user
 * @param b
 * @return {Object}
 */
ChatServerEvaluationClass.prototype.addExtUserVB = function (b, id, unique_name) {
    var thisClass = this;
    var new_b = {};
    var myAttributes = b[0].attributes;
    for (var attrIndex = 0; attrIndex < myAttributes.length; attrIndex++) {
        new_b[myAttributes[attrIndex].nodeName] = lz_global_base64_url_decode(myAttributes[attrIndex].nodeValue);
    }
    new_b.h = {time: '', url: '', title: '', code: '', cp: ''};
    new_b.h2 = [];
    new_b.fupr = {};
    new_b.is_active = true;
    new_b.chat = {id: ''};
    $(b).find('h').each(function () {
        var h = $(this);
        new_b.h = thisClass.addExtUserVBH(h);
        new_b.h2.push(thisClass.addExtUserVBH(h));
    });
    $(b).find('chat').each(function () {
        var chat = $(this);
        new_b.chat = thisClass.addExtUserVBChat(chat, id, new_b.id);
    });
    //console.log(new_b.id + ' --- ' + new_b.chat.id);
    $(b).find('fupr').each(function () {
        var fupr = $(this);
        var name = (new_b.cname != '') ? new_b.cname : unique_name;
        thisClass.addExtUserVBFupr(fupr, id, new_b.id, name, new_b.chat.id);
    });
    return new_b;
};

/**
 * Add the chat object to the external user's b data
 * @param chat
 * @return {Object}
 */
ChatServerEvaluationClass.prototype.addExtUserVBChat = function (chat, id, b_id) {
    var thisClass = this;
    var new_chat = {};
    var myAttributes = chat[0].attributes;
    for (var attrIndex = 0; attrIndex < myAttributes.length; attrIndex++) {
        new_chat[myAttributes[attrIndex].nodeName] = lz_global_base64_url_decode(myAttributes[attrIndex].nodeValue);
    }
    new_chat.pn = {acc: '', member: {}};

    $(chat).find('pn').each(function () {
        //console.log(this);
        new_chat.pn.acc = lz_global_base64_url_decode($(this).attr('acc'));
        new_chat.pn.member = [];
        new_chat.pn.memberIdList = [];

        $(this).find('member').each(function () {
            var myAttributes = $(this)[0].attributes;
            var new_member = {};
            for (var attrIndex = 0; attrIndex < myAttributes.length; attrIndex++) {
                new_member[myAttributes[attrIndex].nodeName] = lz_global_base64_url_decode(myAttributes[attrIndex].nodeValue);
            }
            //console.log(new_member);
            new_chat.pn.member.push(new_member);
            new_chat.pn.memberIdList.push(new_member.id);
        });
        //console.log(new_chat.pn);
        //console.log(new_chat.pn.member);
        /*if (new_chat.pn.member.length == 0)
            new_chat.pn.member = [{id: '', st: '', dec: ''}];*/
    });
    return new_chat;
};

ChatServerEvaluationClass.prototype.addExtUserVBFupr = function (fupr, id, b_id, name, chat_id) {
    var new_fupr = {};
    var myAttributes = fupr[0].attributes;
    for (var attrIndex = 0; attrIndex < myAttributes.length; attrIndex++) {
        new_fupr[myAttributes[attrIndex].nodeName] = lz_global_base64_url_decode(myAttributes[attrIndex].nodeValue);
    }

    var fuprIndex = $.inArray(new_fupr.id, this.fuprIdList);
    var date = $.now();
    var tmpdate = new Date(date);

    var new_chat;
    if (fuprIndex == -1) {
        this.fuprs.push(new_fupr);
        this.fuprIdList.push(new_fupr.id);
        new_chat = {id: md5(String(Math.random())).substr(0, 32),
            date: Math.floor(date / 1000),
            date_human: this.lzm_commonTools.getHumanDate(tmpdate, 'date', this.userLanguage),
            time_human: this.lzm_commonTools.getHumanDate(tmpdate, 'date', this.userLanguage),
            rec: '', rp: '', sen: '0000000',
            text: t('The visitor requested to upload the file <!--request_upload_this-->',
                [['<!--request_upload_this-->','<b>' + new_fupr.fn + '</b>']]) + '<br>' +
                t('Do you want to allow this?') + '&nbsp;&nbsp;&nbsp;'+
                '<a class="lz_chat_accept" href="#" id="allow-upload" ' +
                'onclick="handleUploadRequest(\'' + new_fupr.id + '\', \''+ new_fupr.fn +'\', \''+ id +'\', \''+ b_id +'\', \'allow\', \'' + chat_id + '\')">' +
                t('Accept') + '</a>&nbsp;&nbsp;&nbsp;&nbsp;' +
                '<a class="lz_chat_decline" href="#" id="deny-upload" ' +
                'onclick="handleUploadRequest(\'' + new_fupr.id + '\', \''+ new_fupr.fn +'\', \''+ id +'\', \''+ b_id +'\', \'deny\', \'' + chat_id + '\')">' +
                t('Decline') + '</a>',
            reco: id + '~' + b_id};
        this.chats.push(new_chat);
    } else {
        this.fuprs[fuprIndex] = new_fupr;
        if (typeof new_fupr.download != 'undefined' && new_fupr.download == '1' &&
            $.inArray(new_fupr.id, this.fuprDownloadIdList) == -1) {
            this.fuprDownloadIdList.push(new_fupr.id);
            var downloadLink = '<a class="lz_chat_file" target="_blank" href="' + this.serverProtocol + this.serverUrl + '/getfile.php?' +
                'acid=' + this.lzm_commonTools.pad(Math.floor(Math.random() * 1048575).toString(16), 5) +
                '&id=' + new_fupr.fid + '">';
            new_chat = {id: md5(String(Math.random())).substr(0, 32),
                date: Math.floor(date / 1000),
                date_human: this.lzm_commonTools.getHumanDate(tmpdate, 'date', this.userLanguage),
                time_human: this.lzm_commonTools.getHumanDate(tmpdate, 'date', this.userLanguage),
                rec: '', rp: '', sen: '0000000',
                text: t('You can download the file <!--download_file_name--> provided by the visitor <!--download_link_begin-->here<!--download_link_end-->',
                        [['<!--download_file_name-->','<b>' + new_fupr.fn + '</b>'],
                            ['<!--download_link_begin-->',downloadLink],['<!--download_link_end-->','</a>']]),
                reco: id + '~' + b_id};
            this.chats.push(new_chat);
        }
    }
};

/**
 * Add the values of the h object to the external user
 * @param h
 * @return {Object}
 */
ChatServerEvaluationClass.prototype.addExtUserVBH = function (h) {
    var new_h = {};
    var myAttributes = h[0].attributes;
    for (var attrIndex = 0; attrIndex < myAttributes.length; attrIndex++) {
        new_h[myAttributes[attrIndex].nodeName] = lz_global_base64_url_decode(myAttributes[attrIndex].nodeValue);
    }
    return new_h;
};

ChatServerEvaluationClass.prototype.checkIfBExists = function (id, b_id) {
    var returnValue = false;
    var thisVisitor = {};
    if (this.external_users.length > 0) {
        for (var i = 0; i < this.external_users.length; i++) {
            if (id == this.external_users[i].id) {
                thisVisitor = this.external_users[i];
                break;
            }
        }
        if (thisVisitor.b.length > 0) {
            for (var j = 0; j < thisVisitor.b.length; j++) {
                if (b_id == thisVisitor.b[j].id) {
                    returnValue = true;
                    break;
                }
            }
        }
    }
    //console.log(id + '~' + b_id + ' --- ' + returnValue);
    return returnValue;
};

ChatServerEvaluationClass.prototype.updateB = function (existingBs, newB) {
    //console.log(newB);
    for (var i = 0; i < existingBs.length; i++) {
        if (newB.id == existingBs[i].id) {
            newB.hasChanged = false;
            for (var key in newB) {
                if (key == 'chat' && (newB[key].id == existingBs[i][key].id)) {
                    var newChat = {};
                    for (var chatKey in newB[key]) {
                        if (chatKey == 'pn') {
                            //console.log(newB[key][chatKey]);
                            newChat[chatKey] = {};
                            newChat[chatKey].acc = newB[key][chatKey].acc;
                            //console.log(newB[key][chatKey].member);
                            if (typeof existingBs[i][key][chatKey] != 'undefined') {
                                newChat[chatKey].member = existingBs[i][key][chatKey].member;
                                newChat[chatKey].memberIdList = existingBs[i][key][chatKey].memberIdList;
                                if (newChat[chatKey].member.length > 0) {
                                for (var j=0; j<newChat[chatKey].member.length; j++) {
                                    for (var k=0; k<newB[key][chatKey].member.length; k++) {
                                        if (newB[key][chatKey].member[k].id == newChat[chatKey].member[j].id) {
                                            newChat[chatKey].member[j] = newB[key][chatKey].member[k];
                                            newChat[chatKey].memberIdList[j] = newB[key][chatKey].member[k].id;
                                        }
                                    }
                                }
                                } else {
                                    newChat[chatKey].member = newB[key][chatKey].member;
                                }
                            } else {
                                newChat[chatKey] = newB[key][chatKey];
                            }
                        } else {
                            newChat[chatKey] = newB[key][chatKey];
                        }
                    }
                    existingBs[i][key] = newChat;
                } else {
                    if ((typeof newB[key] == 'string' && newB[key] != '') ||
                        (typeof newB[key] == 'object' && newB[key] instanceof Array && newB[key].length != 0) ||
                        (typeof newB[key] == 'boolean') ||
                        (typeof newB[key] == 'object' && !(newB[key] instanceof Array)) && !$.isEmptyObject(newB[key])) {
                        existingBs[i][key] = newB[key];
                        existingBs[i].hasChanged = true;
                    }
                }
            }
            break;
        }
    }
    //console.log(existingBs);
    return existingBs;
};

ChatServerEvaluationClass.prototype.createUniqueName = function(idString) {
    //console.log(idString);
    var mod = 111;
    var digit;
    for (var i=0; i<idString.length; i++) {
        digit = 0;
        if (!isNaN(parseInt(idString.substr(i,1)))) {
            digit = parseInt(idString.substr(i,1));
            //console.log(i + ' --- ' + digit);
            mod = (mod + (mod* (16+digit)) % 1000);
            if (mod % 10 == 0) {
                mod += 1;
            }
        }
    }
    var result = String(mod).substr(String(mod).length-4,4);
    //console.log(result);
    return result;
};

ChatServerEvaluationClass.prototype.setExternalUserList = function(type, list) {
    if (type == 'changed') {
        this.changedExternalUsers = list;
    } else if (type == 'new') {
        this.newExternalUsers = list;
    }
};

ChatServerEvaluationClass.prototype.getExternalUserList = function(type) {
    var list;
    if (type == 'changed') {
        list = this.changedExternalUsers;
    } else if (type == 'new') {
        list = this.newExternalUsers;
    }
    return list;
};

/**
 * Add an external user to the users array
 * @param v
 */
ChatServerEvaluationClass.prototype.addExtUserV = function (v) {
    var thisClass = this;
    var new_user = {};
    var bIndex;
    var myAttributes = v[0].attributes;
    for (var attrIndex = 0; attrIndex < myAttributes.length; attrIndex++) {
        new_user[myAttributes[attrIndex].nodeName] = lz_global_base64_url_decode(myAttributes[attrIndex].nodeValue);
    }
    var b_stringEntries = ['b_id', 'b_ol', 'b_olc', 'b_ss', 'b_ka', 'b_ref', 'b_cname', 'b_cemail', 'b_cphone', 'b_ccompany',
        'b_h_time', 'b_h_url', 'b_h_title', 'b_h_code', 'b_h_cp'];
    for (bIndex = 0; bIndex < b_stringEntries.length; bIndex++) {
        new_user[b_stringEntries[bIndex]] = '';
    }
    if (typeof new_user.ip != 'undefined') {
        new_user.unique_name = t('Visitor <!--visitor_number-->',[['<!--visitor_number-->',thisClass.createUniqueName(new_user.id + new_user.ip)]]);
    }
    new_user.b = [];
    var b_idList = [];
    new_user.b_chat = {id: ''};
    new_user.is_active = true;
    //console.log('');
    //console.log('Found the following browsers:');
    $(v).find('b').each(function () {
        var b = $(this);

        var tmp_b = thisClass.addExtUserVB(b, new_user.id, new_user.unique_name);

        // Deprecated (but still used) old ext user b variant
        new_user.b_id = tmp_b.id;
        new_user.b_ol = tmp_b.ol;
        new_user.b_olc = tmp_b.olc;
        new_user.b_ss = tmp_b.ss;
        new_user.b_ka = tmp_b.ka;
        new_user.b_ref = tmp_b.ref;
        new_user.b_cname = tmp_b.cname;
        new_user.b_cemail = tmp_b.cemail;
        new_user.b_cphone = tmp_b.cphone;
        new_user.b_ccompany = tmp_b.ccompany;
        new_user.b_chat = tmp_b.chat;
        new_user.b_h_time = tmp_b.h.time;
        new_user.b_h_url = tmp_b.h.url;
        new_user.b_h_title = tmp_b.h.title;
        new_user.b_h_code = tmp_b.h.code;
        new_user.b_h_cp = tmp_b.h.cp;
        new_user.b.push(tmp_b);
        b_idList.push(tmp_b.id);
        //console.log('Tmp : ' + tmp_b.id);
        //console.log(new_user.id + '~' + tmp_b.id + ' --- ' + tmp_b.chat.id);
    });
    var externalUserId = new_user.id;

    // check if it's a new user. if yes add him if no update the user's data
    if ($.inArray(externalUserId, thisClass.extUserIdList) == -1) {
        //console.log('New user ' + externalUserId);
        thisClass.extUserIdList.push(externalUserId);
        thisClass.external_users.push(new_user);
        thisClass.newExternalUsers.push(externalUserId);

    } else {
        var userHasChanged = false;
        for (var i = 0; i < thisClass.external_users.length; i++) {
            if (thisClass.external_users[i].id == externalUserId) {
                //console.log('Update existing user ' + externalUserId)
                for (var key in thisClass.external_users[i]) {
                    if (key != 'b_chat' && key != 'b' &&
                        (typeof new_user[key] != 'undefined' && new_user[key] != '')) {
                        thisClass.external_users[i][key] = new_user[key];
                        userHasChanged = true;
                    }
                }
                if (new_user.b_chat.id != '') {
                    thisClass.external_users[i].b_chat = new_user.b_chat;
                }
                if (typeof thisClass.external_users[i].b == 'undefined') {
                    thisClass.external_users[i].b = [];
                }

                //console.log(new_user.b.length + ' --- ' + b_idList);
                if (new_user.b.length > 0) {
                    for (var j = 0; j < new_user.b.length; j++) {
                        //console.log('Usr : ' + new_user.b[j].id);
                        //if (new_user.b[j].id.indexOf('_OVL') == -1 &&
                        if (thisClass.checkIfBExists(new_user.id, new_user.b[j].id) == false) {
                            //console.log('New browser ' + new_user.b[j].id);
                            //console.log(new_user.b[j]);
                            thisClass.external_users[i].b.push(new_user.b[j]);
                            userHasChanged = true;
                        } else {
                            //console.log('Update browser ' + new_user.b[j].id);
                            thisClass.external_users[i].b = thisClass.updateB(thisClass.external_users[i].b, new_user.b[j]);
                            if (thisClass.external_users[i].b.hasChanged) {
                                userHasChanged = true;
                            }
                        }
                    }
                }
                new_user.b = thisClass.external_users[i].b;
            }
        }
        if (userHasChanged) {
            thisClass.changedExternalUsers.push(externalUserId);
        }
    }
    if (new_user.b.length > 0) {
        var lastBIndex = new_user.b.length - 1;
        for (bIndex=0; bIndex<new_user.b.length; bIndex++) {
            if (typeof new_user.b[bIndex].chat != 'undefined' && new_user.b[bIndex].chat.id != '' &&
                typeof thisClass.chatObject[new_user.id + '~' + new_user.b[bIndex].id] == 'undefined') {
                //console.log('Chat request from ' + new_user.id + '~' + new_user.b[bIndex].id);
                var chatNotDeclined = true;
                for (var l=0; l<new_user.b[bIndex].chat.pn.member.length; l++) {
                    //console.log(new_user.b[bIndex].chat.pn.member[l].id + ' - ' + thisClass.myId);
                    //console.log(new_user.b[bIndex].chat.pn.member[l].dec + ' - 0');
                    if (new_user.b[bIndex].chat.pn.member[l].id == thisClass.myId && new_user.b[bIndex].chat.pn.member[l].dec == 1) {
                        chatNotDeclined = false;
                    }
                }
                if ((new_user.b[bIndex].chat.at == 0 || (new_user.b[bIndex].chat.at * 1000) > thisClass.loginTime) &&
                    $.inArray(thisClass.myId, new_user.b[bIndex].chat.pn.memberIdList) != -1 &&
                    chatNotDeclined) {
                    //console.log(new_user);
                    thisClass.chatObject[new_user.id + '~' + new_user.b[bIndex].id] = {
                        status: 'new', type: 'external', data: [], id: new_user.id, b_id: new_user.b[bIndex].id
                    };
                    //console.log('Chat object ' + new_user.id + '~' + new_user.b[bIndex].id + ' due to new user.b.chat');
                    //playIncomingMessageSound(new_user.id + '~' + new_user.b[bIndex].id);
                    thisClass.browserChatIdList.push(new_user.b[bIndex].chat.id);
                }
            }
            if (typeof thisClass.chatObject[new_user.id + '~' + new_user.b[bIndex].id] != 'undefined' &&
                typeof new_user.b[bIndex].chat != 'undefined' &&
                (typeof thisClass.chatObject[new_user.id + '~' + new_user.b[bIndex].id].chat_id == 'undefined' ||
                thisClass.chatObject[new_user.id + '~' + new_user.b[bIndex].id].chat_id == '')) {
                //console.log('Set chat id of chat object ' +  new_user.id + '~' + new_user.b[bIndex].id + ' to ' + new_user.b[bIndex].chat.id);
                thisClass.chatObject[new_user.id + '~' + new_user.b[bIndex].id].chat_id = new_user.b[bIndex].chat.id;
            }

            //console.log(new_user.b[bIndex].chat.pn);
            /*if (typeof thisClass.chatObject[new_user.id + '~' + new_user.b[bIndex].id] != 'undefined' && typeof new_user.b[bIndex].chat.pn != 'undefined' &&
                typeof new_user.b[bIndex].chat.pn.memberIdList != 'undefined' && $.inArray(thisClass.myId, new_user.b[bIndex].chat.pn.memberIdList) == -1) {
                //delete thisClass.chatObject[new_user.id + '~' + new_user.b[bIndex].id];
                //console.log(new_user);
                console.log(thisClass.myId + ' : ' + new_user.b[bIndex].chat.pn.memberIdList.join(','));
                console.log('Remove chat object ' + new_user.id + '~' + new_user.b[bIndex].id);
                removeFromOpenChats(new_user.id + '~' + new_user.b[bIndex].id, true, true, new_user.b[bIndex].chat.pn.member);
            }*/

            if (typeof thisClass.chatObject[new_user.id + '~' + new_user.b[bIndex].id] != 'undefined' && typeof new_user.b[bIndex].chat.pn != 'undefined' &&
                new_user.b[bIndex].chat.pn.acc == 1) {
                //console.log (new_user.b[bIndex].chat.pn);
                for (var n=0; n<new_user.b[bIndex].chat.pn.member.length; n++) {
                    if (new_user.b[bIndex].chat.pn.member[n].id != thisClass.myId && new_user.b[bIndex].chat.pn.member[n].st == 0) {
                        removeFromOpenChats(new_user.id + '~' + new_user.b[bIndex].id, true, true, new_user.b[bIndex].chat.pn.member);
                        break;
                    } else if (new_user.b[bIndex].chat.pn.member[n].id == thisClass.myId && new_user.b[bIndex].chat.pn.member[n].st == 0) {
                        addOpLeftMessageToChat(new_user.id + '~' + new_user.b[bIndex].id, new_user.b[bIndex].chat.pn.member);
                    }
                }
            }


            if (typeof new_user.b[bIndex].chat.pn != 'undefined' && typeof new_user.b[bIndex].chat.pn.member != 'undefined' &&
                (typeof new_user.b[bIndex].chat.pn.memberIdList != 'undefined' && $.inArray(thisClass.myId, new_user.b[bIndex].chat.pn.memberIdList) != -1)) {

                if (typeof thisClass.chatPartners[new_user.id + '~' + new_user.b[bIndex].id] == 'undefined') {
                    thisClass.chatPartners[new_user.id + '~' + new_user.b[bIndex].id] = {past: [], present: []};
                }
                thisClass.chatPartners[new_user.id + '~' + new_user.b[bIndex].id].past = thisClass.chatPartners[new_user.id + '~' + new_user.b[bIndex].id].present;
                thisClass.chatPartners[new_user.id + '~' + new_user.b[bIndex].id].present = [];
                var tmpPast = [];
                for (var m=0; m<new_user.b[bIndex].chat.pn.member.length; m++) {
                    if ($.inArray(new_user.b[bIndex].chat.pn.member[m].id, thisClass.chatPartners[new_user.id + '~' + new_user.b[bIndex].id].past) != -1) {
                        tmpPast.push(new_user.b[bIndex].chat.pn.member[m].id);
                    }
                    //console.log(new_member.dec + ' - ' + new_member.id);
                    if (new_user.b[bIndex].chat.pn.member[m].dec == 0) {
                        thisClass.chatPartners[new_user.id + '~' + new_user.b[bIndex].id].present.push(new_user.b[bIndex].chat.pn.member[m].id);
                    }
                }
                thisClass.chatPartners[new_user.id + '~' + new_user.b[bIndex].id].past = tmpPast;
                addDeclinedMessageToChat(new_user.id , new_user.b[bIndex].id, thisClass.chatPartners[new_user.id + '~' + new_user.b[bIndex].id]);
            }


        }
        for (var bIndex=0; bIndex<new_user.b.length; bIndex++) {
            if (typeof thisClass.chatObject[new_user.id + '~' + new_user.b[bIndex].id] != 'undefined') {
                if (typeof new_user.b[bIndex].chat != 'undefined' && typeof new_user.b[bIndex].chat.eq != 'undefined') {
                    thisClass.chatObject[new_user.id + '~' + new_user.b[bIndex].id].eq = new_user.b[bIndex].chat.eq;
                }
                if (typeof new_user.b[bIndex].cname != 'undefined') {
                    thisClass.chatObject[new_user.id + '~' + new_user.b[bIndex].id].name = new_user.b[bIndex].cname;
                }
                if (new_user.b[bIndex].chat == 'undefined' || new_user.b[bIndex].chat.id == '') {
                    //thisClass.chatObject[new_user.id + '~' + new_user.b[bIndex].id].status = 'left';
                    markVisitorAsLeft(new_user.id, new_user.b[bIndex].id)
                }
            }
        }
        for (var k=0; k<new_user.b.length; k++) {
            //console.log(new_user.b[k].chat.id);
            if (new_user.b[k].chat.id != '' && $.inArray(new_user.b[k].chat.id, thisClass.browserChatIdList) == -1 &&
                typeof thisClass.chatObject[new_user.id + '~' + new_user.b[k].id] != 'undefined') {
                var member = [];
                if (typeof new_user.b[k].chat.pn != 'undefined') {
                    member = new_user.b[k].chat.pn.member;
                }
                markVisitorAsBack(new_user.id, new_user.b[k].id, new_user.b[k].chat.id, member);
                //thisClass.chatObject[new_user.id + '~' + new_user.b[lastBIndex].id].status = 'new';
            }
        }
    }
};

/**
 * Get validation errors from the server response. If there are any, log out.
 * @param xmlDoc
 */
ChatServerEvaluationClass.prototype.getValidationError = function (xmlDoc) {
    var error_value = -1;
    $(xmlDoc).find('validation_error').each(function () {
        if (error_value == -1) {
            error_value = lz_global_base64_url_decode($(this).attr('value'));
        }
    });
    return error_value;
};

/**
 * Get the ext_c values from the server's xml report
 * @param xmlDoc
 */
ChatServerEvaluationClass.prototype.getExtC = function (xmlDoc) {
    var thisClass = this;
    $(xmlDoc).find('ext_c').each(function () {
        console.log('Get the external comment');
        thisClass.new_ext_c = true;
        var ext_c = $(this);
        $(ext_c).children('c').each(function () {
            console.log('Comment');
            var new_c = {};
            var myAttributes = val[0].attributes;
            for (var attrIndex = 0; attrIndex < myAttributes.length; attrIndex++) {
                console.log(myAttributes[attrIndex].nodeName + ' --- ' + lz_global_base64_url_decode(myAttributes[attrIndex].nodeValue));
                new_c[myAttributes[attrIndex].nodeName] = lz_global_base64_url_decode(myAttributes[attrIndex].nodeValue);
            }
            $(this).children('chtml').each(function () {
                console.log($(this).text());
                new_c.chtml = $(this).text();
            });
            thisClass.external_c.push(new_c);
        });
    });
};

ChatServerEvaluationClass.prototype.getResources = function (xmlDoc) {
    var thisClass = this;
    if ($.inArray('1', thisClass.resourceIdList) == -1) {
        var publicFolder = {
            di: "0",
            ed: "0",
            eid: "0000000",
            oid: "0000000",
            pid: "0",
            ra: "0",
            rid: "1",
            si: "6",
            t: "",
            text: t('Public'),
            ti: t('Public'),
            ty: "0"
        };
        thisClass.resources.push(publicFolder);
        thisClass.resourceIdList.push('1');
    }
    $(xmlDoc).find('r').each(function () {
        //console.log('');
        //console.log('');
        //console.log('Get resource');
        //console.log('');
        thisClass.new_r = true;
        var new_r = {};
        var myAttributes = $(this)[0].attributes;
        for (var attrIndex = 0; attrIndex < myAttributes.length; attrIndex++) {
            //console.log(myAttributes[attrIndex].nodeName + ' --- ' + lz_global_base64_url_decode(myAttributes[attrIndex].nodeValue));
            new_r[myAttributes[attrIndex].nodeName] = lz_global_base64_decode(myAttributes[attrIndex].nodeValue);
        }
        //console.log($(this).text());
        new_r.text = lz_global_base64_decode($(this).text());
        //console.log(new_r);

        if ($.inArray(new_r.rid, thisClass.resourceIdList) == -1) {
            if (new_r.di == 0) {
                thisClass.resources.push(new_r);
                thisClass.resourceIdList.push(new_r.rid);
                //console.log('New resource --- ' + new_r.rid);
            }
        } else {
            var deleteResource;
            var tmpResources = [], tmpResourceIdList = [];
            for (var i = 0; i<thisClass.resources.length; i++) {
                deleteResource = false;
                if (new_r.rid == thisClass.resources[i].rid) {
                    if (new_r.di == 0) {
                        thisClass.resources[i] = new_r;
                        //console.log('Changed resource --- ' + new_r.rid);
                        //console.log(new_r);
                    } else {
                        deleteResource = true;
                        //console.log('Deleted resource --- ' + new_r.rid);
                    }
                }
                if (!deleteResource) {
                    tmpResources.push(thisClass.resources[i]);
                    tmpResourceIdList.push(thisClass.resources[i].rid);
                }
                if (thisClass.resources[i].di == 1) {
                    //console.log(thisClass.resources[i]);
                }
            }
            thisClass.resources = tmpResources;
            thisClass.resourceIdList = tmpResourceIdList;
        }

        thisClass.resourceLastEdited = Math.max(thisClass.resourceLastEdited, new_r.ed);
    });

    return thisClass.resourceLastEdited;
};

ChatServerEvaluationClass.prototype.debuggingSearchForId = function(type, id) {
    var returnArray = [];
    for (var i=0; i<this.resources.length; i++) {
        if (this.resources[i][type] == id) {
            returnArray.push(this.resources[i]);
        }
    }
    return returnArray;
};

/**
 * Get the usr_p values (aka chats) from the server response
 * @param xmlDoc
 */
ChatServerEvaluationClass.prototype.getUsrP = function (xmlDoc) {
    var thisClass = this;
    $(xmlDoc).find('usr_p').each(function () {
        thisClass.new_usr_p = true;
        var usr_p = $(this);
        $(usr_p).find('val').each(function () {
            var val = $(this);
            thisClass.addUsrP(val);
        });
    });
};

/**
 * Get the departments from the server response
 * @param xmlDoc
 */
ChatServerEvaluationClass.prototype.getDepartments = function (xmlDoc) {
    var thisClass = this;
    var myHash = '';
    $(xmlDoc).find('int_d').each(function () {
        thisClass.new_int_d = true;
        var int_d = $(this);
        thisClass.internal_departments = [];
        $(int_d).find('v').each(function () {
            var v = $(this);
            thisClass.internal_departments.push(thisClass.addDepartment(v));
        });

        myHash = lz_global_base64_url_decode(int_d.attr('h'));
    });
    return myHash;
};

/**
 * Get the internal users from the server response
 * @param xmlDoc
 */
ChatServerEvaluationClass.prototype.getInternalUsers = function (xmlDoc) {
    var thisClass = this;
    var myHash = '';
    $(xmlDoc).find('int_r').each(function () {
        thisClass.new_int_u = true;
        var int_r = $(this);
        thisClass.internal_users = [];
        $(int_r).find('v').each(function () {
            var v = $(this);
            thisClass.internal_users.push(thisClass.addInternalUser(v));
        });

        myHash = lz_global_base64_url_decode(int_r.attr('h'));
    });
    return myHash;
};

/**
 * Get the global errors from the server response
 * @param xmlDoc
 */
ChatServerEvaluationClass.prototype.getGlobalErrors = function (xmlDoc) {
    var thisClass = this;
    var myHash = '';
    $(xmlDoc).find('gl_e').each(function () {
        thisClass.new_gl_e = true;
        var gl_e = $(this);
        thisClass.global_errors = [];
        $(gl_e).find('val').each(function () {
            var val = $(this);
            thisClass.global_errors.push(lz_global_base64_url_decode(val.attr('err')));
        });

        myHash = lz_global_base64_url_decode(gl_e.attr('h'));
    });
    return myHash;
};

/**
 * Get the internal wp from the server response
 *
 * What is WP?
 *
 * @param xmlDoc
 */
ChatServerEvaluationClass.prototype.getIntWp = function (xmlDoc) {
    var thisClass = this;
    var myHash = '';
    $(xmlDoc).find('int_wp').each(function () {
        var int_wp = $(this);
        thisClass.wps = [];
        $(int_wp).find('v').each(function () {
            var v = $(this);
            thisClass.wps.push(thisClass.addWP(v));
        });

        myHash = lz_global_base64_url_decode(int_wp.attr('h'));
    });
    return myHash;
};

/**
 * Add a wp to the array
 *
 * What is WP?
 *
 * @param v
 */
ChatServerEvaluationClass.prototype.addWP = function (v) {
    var new_wp = {};
    var myAttributes = v[0].attributes;
    for (var attrIndex = 0; attrIndex < myAttributes.length; attrIndex++) {
        new_wp[myAttributes[attrIndex].nodeName] = lz_global_base64_url_decode(myAttributes[attrIndex].nodeValue)
    }
    return new_wp;
};

/**
 * add a usrP (aka chat) to the arrays
 * @param val
 */
ChatServerEvaluationClass.prototype.addUsrP = function (val) {
    var thisClass = this;
    var new_chat = {};
    var myAttributes = val[0].attributes;
    for (var attrIndex = 0; attrIndex < myAttributes.length; attrIndex++) {
        new_chat[myAttributes[attrIndex].nodeName] = lz_global_base64_url_decode(myAttributes[attrIndex].nodeValue);
    }
    var tmpdate = thisClass.lzm_chatTimeStamp.getLocalTimeObject(new_chat.date * 1000);
    // new Date(new_chat.date * 1000);
    new_chat.date_human = thisClass.lzm_commonTools.getHumanDate(tmpdate, 'date', thisClass.userLanguage);
    new_chat.time_human = thisClass.lzm_commonTools.getHumanDate(tmpdate, 'time', thisClass.userLanguage);
    if (new_chat.sen.indexOf('~') != -1) {
        new_chat.sen_id = new_chat.sen.split('~')[0];
        new_chat.sen_b_id = new_chat.sen.split('~')[1];
    } else {
        new_chat.sen_id = new_chat.sen;
        new_chat.sen_b_id = '';
    }
    if (new_chat.rec != '' && new_chat.rec != new_chat.sen) {
        new_chat.sen_id = new_chat.rec;
        new_chat.sen_b_id = '';
    }
    var thisText = lz_global_base64_url_decode(val.text());
    if (new_chat.sen_b_id != '') {
        thisText = thisClass.addLinks(thisClass.escapeHtml(thisText));
    } else {
        //console.log(thisText);
        thisText = thisClass.replaceLinks(thisText);
    }
    new_chat.text = thisText;

    /*var extUserBChatDcp = '';
    var extUserBChatPnMemberIdList = [];
    var extUserBChatPnMember = [];
    for (var extUserIndex = 0; extUserIndex < thisClass.external_users.length; extUserIndex++) {
        if (new_chat.sen_id == thisClass.external_users[extUserIndex].id) {
            console.log(thisClass.external_users[extUserIndex].b.length);
            for (var browserIndex=0; browserIndex<thisClass.external_users[extUserIndex].b.length; browserIndex++) {
                console.log(thisClass.external_users[extUserIndex].b[browserIndex].id + ' --- ' + new_chat.sen_b_id);
                if (thisClass.external_users[extUserIndex].b[browserIndex].id == new_chat.sen_b_id) {
                    console.log(thisClass.external_users[extUserIndex].b[browserIndex]);
                    extUserBChatDcp = thisClass.external_users[extUserIndex].b[browserIndex].dcp;
                    if (typeof thisClass.external_users[extUserIndex].b[browserIndex].pn != 'undefined') {
                        extUserBChatPnMemberIdList = thisClass.external_users[extUserIndex].b[browserIndex].pn.memberIdList;
                        extUserBChatPnMember = thisClass.external_users[extUserIndex].b[browserIndex].pn.member;
                        console.log(extUserBChatPnMember);
                    }
                }
            }
            break;
        }
    }
    var forwardedToMe = false;
    for (var extFwdIndex = 0; extFwdIndex < thisClass.external_forwards.length; extFwdIndex++) {
        if (thisClass.external_forwards[extFwdIndex].u == new_chat.sen) {
            forwardedToMe = true;
            break;
        }
    }*/
    if ($.inArray(new_chat.id, thisClass.chatIdList) == -1) {
        //console.log(new_chat.id + ' --- ' + new_chat.text);
        thisClass.chatIdList.push(new_chat.id);
        thisClass.chats.push(new_chat);
        var thisSen;
        //console.log(thisClass.myId);
        //console.log(extUserBChatPnMemberIdList);
        if (new_chat.reco == thisClass.myId /*&& (thisClass.settingsDialogue || new_chat.sen != thisClass.active_chat_reco)*/ /*&&
            ((extUserBChatPnMemberIdList.length == 0 || $.inArray(thisClass.myId, extUserBChatPnMemberIdList) != -1) || forwardedToMe || new_chat.sen.indexOf('~') == -1)*/) {
            if (new_chat.sen != '0000000' && new_chat.sen != thisClass.myId) {
                thisSen = new_chat.sen;
                if (new_chat.rec != '' && new_chat.rec != new_chat.sen) {
                    thisSen = new_chat.rec;
                }
                if (typeof thisClass.chatObject[thisSen] == 'undefined') {
                    /*thisClass.chatObject[thisSen] = {
                        status: 'new', data: [], id: new_chat.sen_id, b_id: new_chat.sen_b_id
                    };
                    console.log('Chat object ' + thisSen + ' due to new usr_p');
                    console.log (thisClass.chatObject[thisSen]);
                    if (new_chat.sen.indexOf('~') != -1) {
                        thisClass.chatObject[thisSen]['type'] = 'external';
                        for (var i=0; i<thisClass.external_users.length; i++) {
                            if (new_chat.sen_id == thisClass.external_users[i].id) {
                                for (var j=0; j<thisClass.external_users[i].b.length; j++) {
                                    if (new_chat.sen_b_id == thisClass.external_users[i].b[j].id) {
                                        if (typeof thisClass.external_users[i].b[j].chat != 'undefined' &&
                                            typeof thisClass.external_users[i].b[j].chat.id != 'undefined' &&
                                            thisClass.external_users[i].b[j].chat.id != 'undefined' != '') {
                                            thisClass.browserChatIdList.push(thisClass.external_users[i].b[j].chat.id);
                                        }
                                    }
                                }
                                break;
                            }
                        }
                    } else {*/
                    if (new_chat.sen.indexOf('~') == -1) {
                        thisClass.chatObject[thisSen] = {
                            status: 'new', data: [], id: new_chat.sen_id, b_id: new_chat.sen_b_id
                        };
                        console.log('Chat object ' + thisSen + ' due to new usr_p');
                        console.log (thisClass.chatObject[thisSen]);
                        thisClass.chatObject[thisSen]['type'] = 'internal';
                    }
                } else {
                    thisClass.chatObject[thisSen]['data'].push(new_chat);
                }
                if (typeof thisClass.chatObject[thisSen] != 'undefined') {
                    if ((thisClass.settingsDialogue || new_chat.sen != thisClass.active_chat_reco) && new_chat.rp != 1) {
                        console.log(thisClass.chatObject[thisSen]['status']);
                        thisClass.chatObject[thisSen]['status'] = 'new';
                    } /*else {
                        thisClass.chatObject[thisSen]['status'] = 'read';
                    }*/
                }

            }
        }
        //console.log (new_chat.reco + ' --- ' + new_chat.rp + ' --- ' + thisSen);
        //console.log(thisClass.chatObject[thisSen]);
        if (new_chat.reco == thisClass.myId && new_chat.rp != 1 && typeof thisClass.chatObject[thisSen] != 'undefined')
        /*($.inArray(thisClass.myId, extUserBChatPnMemberIdList) != -1 || forwardedToMe || new_chat.sen.indexOf('~') == -1))*/ {
            playIncomingMessageSound(new_chat.sen, new_chat.id);
        }
        thisClass.rec_posts.push(new_chat.id);
        //thisClass.incoming_chats.push(new_chat.sen);
    } /*else {
        //console.log(new_chat.id + ' --- ' + new_chat.text);
        if (typeof thisClass.chatObject[new_chat.sen] != 'undefined' &&
            thisClass.chatObject[new_chat.sen].type == 'external' &&
            extUserBChatPnMemberIdList.length > 0 && $.inArray(thisClass.myId, extUserBChatPnMemberIdList) == -1 &&
            !forwardedToMe) {
            //delete thisClass.chatObject[new_chat.sen];
            //console.log(thisClass.myId + ' : ' + extUserBChatPnMemberIdList.join(','));
            //console.log('Remove chat object ' + new_chat.sen);
            removeFromOpenChats(new_chat.sen, true, true, extUserBChatPnMember);
        }
    }*/
};

/**
 * add hyperlinks to urls and mailadresses found in chat posts
 * @param myText
 * @returns {*}
 */
ChatServerEvaluationClass.prototype.addLinks = function(myText) {
    var i, j, replacement;
    var webSites = myText.match(/(www\.|(http|https):\/\/)[.a-z0-9-]+\.[a-z0-9\/_:@=.+?,##%&~-]*[^.|'|# |!|\(|?|,| |>|<|;|\)]/gi);
    var existingLinks = myText.match(/(<a href.*?onclick.*?openLink.*?<\/a>|<a href.*?getfile.php.*?<\/a>)/);
    if (typeof webSites != 'undefined' && webSites != null) {
        for (i=0; i<webSites.length; i++) {
            var replaceLink = true;
            if (typeof existingLinks != 'undefined' && existingLinks != null) {
                for (j=0;j<existingLinks.length; j++) {
                    if (existingLinks[j].indexOf(webSites[i])) {
                        replaceLink = false;
                    }
                }
            }
            if (replaceLink) {
                if (webSites[i].toLowerCase().indexOf('http') != 0) {
                    replacement = '<a class="lz_chat_link" href="#" onclick="openLink(\'http://' + webSites[i] + '\')">' + webSites[i] + '</a>';
                } else {
                    replacement = '<a class="lz_chat_link" href="#" onclick="openLink(\'' + webSites[i] + '\')">' + webSites[i] + '</a>';
                }
                myText = myText.replace(webSites[i], replacement);
            }
        }
    }

    var mailAddresses = myText.match(/[\w\.-]{1,}@[\w\.-]{2,}\.\w{2,3}/gi);
    if (typeof mailAddresses != 'undefined' && mailAddresses != null) {
        for (i=0; i<mailAddresses.length; i++) {
            replacement = '<a class="lz_chat_mail" href="mailto:' + mailAddresses[i] + '">' + mailAddresses[i] + '</a>';
            myText = myText.replace(mailAddresses[i], replacement);
        }
    }
    return myText;
};

ChatServerEvaluationClass.prototype.replaceLinks = function(myText) {
    var i, replacement;
    var links = myText.match(/<a.*?href.*?<\/a>/);
    if (typeof links != 'undefined' && links != null) {
        for (i=0; i<links.length; i++) {
            //console.log(links[i]);
            if (links[i].indexOf('mailto') == -1) {
                var address = links[i].match(/href=".*?"/);
                if (typeof address == 'undefined' || address == null) {
                    address = links[i].match(/href='.*?'/)[0].replace(/^href='/,'').replace(/'$/, '');
                } else {
                    address = address[0].replace(/^href="/,'').replace(/"$/, '');
                }
                address = address.replace(/ *$/,'').replace(/"*$/,'');
                var shownText = links[i].match(/>.*?<\/a>/);
                if (typeof shownText == 'undefined' || shownText == null) {
                    shownText = links[i].match(/href='.*?'/);
                }
                shownText = shownText[0].replace(/^>/,'').replace(/<\/a>$/,'');
                if (links[i].indexOf('lz_chat_file') == -1) {
                    replacement = '<a class="lz_chat_link" href="#" onclick="openLink(\'' + address + '\')">' + shownText + '</a>';
                } else {
                    replacement = '<a class="lz_chat_file" href="#" onclick="downloadFile(\'' + address + '\')">' + shownText + '</a>';
                }
                if (address != '#') {
                    myText = myText.replace(links[i], replacement);
                }
            }
        }
    }
    return myText;
};

/**
 * Escape html included in chat posts as a security meassure
 * @param myText
 * @returns {XML}
 */
ChatServerEvaluationClass.prototype.escapeHtml = function(myText) {
    // Replace surrounding font tags as the Windows client sends those
    myText = myText.replace(/^<font.*?>/g,'').replace(/<\/font>$/,'');

    // replace < and > by their html entities
    myText = myText.replace(/</g,'&lt;').replace(/>/g,'&gt;')

    // replace line endings by their html equivalents
    myText = myText.replace(/\n/g, '').replace(/\r/, '');

    myText = myText.replace(/&lt;br \/&gt;/g, '<br />');

    return myText;
};

/**
 * add a department to the array
 * @param v
 */
ChatServerEvaluationClass.prototype.addDepartment = function (v) {
    var thisClass = this;
    var new_department = {};
    var myAttributes = v[0].attributes;
    for (var attrIndex = 0; attrIndex < myAttributes.length; attrIndex++) {
        new_department[myAttributes[attrIndex].nodeName] = lz_global_base64_url_decode(myAttributes[attrIndex].nodeValue)
    }
    new_department.logo = 'img/lz_group.png';
    new_department.status_logo = new_department.logo;
    new_department.is_active = true;
    new_department.pm = [];

    $(v).find('pm').each(function () {
        var pm = $(this);
        new_department.pm.push(thisClass.addPM(pm));
    });

    return new_department;
};

/**
 * Add an internal user to the array
 * @param v
 */
ChatServerEvaluationClass.prototype.addInternalUser = function (v) {
    var thisClass = this;
    var new_user = {};
    var myAttributes = v[0].attributes;
    for (var attrIndex = 0; attrIndex < myAttributes.length; attrIndex++) {
        new_user[myAttributes[attrIndex].nodeName] = lz_global_base64_url_decode(myAttributes[attrIndex].nodeValue)
    }
    var userStatusIndex = 0;
    for (var i=0; i<thisClass.lzm_commonConfig.lz_user_states.length; i++) {
        if (thisClass.lzm_commonConfig.lz_user_states[i].index == new_user.status) {
            userStatusIndex = i;
            break;
        }
    }
    new_user.logo = thisClass.lzm_commonConfig.lz_user_states[userStatusIndex].icon;
    if (typeof new_user.isbot != 'undefined' && new_user.isbot == 1) {
        new_user.logo = 'img/643-ic.png';
    }
    new_user.status_logo = new_user.logo;
    new_user.groups = [];
    new_user.is_active = true;

    $(v).find('gr').each(function () {
        var gr = $(this);
        new_user.groups.push(lz_global_base64_url_decode(gr.text()));
    });

    $(v).find('pm').each(function () {
        var pm = $(this);
        if (typeof new_user.pm == 'undefined') {
            new_user.pm = [];
        }
        new_user.pm.push(thisClass.addPM(pm));
    });

    // set the values for the logged in user
    if (new_user.userid == thisClass.chosen_profile.login_name) {
        thisClass.myGroup = new_user.groups[0];
    }

    return new_user;
};

/**
 * Add the predefined messages to the internal departments or internal users
 *
 * @param pm
 */
ChatServerEvaluationClass.prototype.addPM = function (pm) {
    var newPm = {};
    var myAttributes = pm[0].attributes;
    for (var attrIndex = 0; attrIndex < myAttributes.length; attrIndex++) {
        newPm[myAttributes[attrIndex].nodeName] = lz_global_base64_url_decode(myAttributes[attrIndex].nodeValue)
    }

    return newPm;
};

/**
 * Delete a property from the chat object
 * @param propertyName
 */
ChatServerEvaluationClass.prototype.deletePropertyFromChatObject = function (propertyName) {
    delete this.chatObject[propertyName];
};


/**
 * Timestamp class, adding or substracting the time difference between client and server
 * @param timeDifference
 * @constructor
 */
function ChatTimestampClass(timeDifference) {
    this.timeDifference = timeDifference * 1000;
    //console.log('Timestamp object created - time diff is ' + this.timeDifference);
}

ChatTimestampClass.prototype.logTimeDifference = function() {
    console.log('Timestamp object present - time diff is ' + this.timeDifference);
};

ChatTimestampClass.prototype.setTimeDifference = function(timeDifference) {
    this.timeDifference = timeDifference * 1000;
};

ChatTimestampClass.prototype.getLocalTimeObject = function(timeStamp) {
    var tmpDateObject = new Date(parseInt(timeStamp) - parseInt(this.timeDifference));
    return tmpDateObject;
};
