/****************************************************************************************
 * LiveZilla ChatPollServerClass.js
 *
 * Copyright 2013 LiveZilla GmbH
 * All rights reserved.
 * LiveZilla is a registered trademark.
 *
 ***************************************************************************************/

/**
 *
 * @constructor
 */
function ChatPollServerClass(lzm_commonConfig, lzm_commonTools, lzm_chatDisplay, lzm_chatServerEvaluation,
                             lzm_commonStorage, chosenProfile, userStatus, web, app) {
    //console.log('ChatPollServerClass initiated');

    // variables passed as arguments to this class
    this.lzm_commonConfig = lzm_commonConfig;
    this.lzm_commonTools = lzm_commonTools;
    this.lzm_chatDisplay = lzm_chatDisplay;
    this.lzm_chatServerEvaluation = lzm_chatServerEvaluation;
    this.lzm_commonStorage = lzm_commonStorage;
    this.chosenProfile = chosenProfile;
    this.user_status = userStatus;
    this.isWeb = web;
    this.isApp = app;
    this.pollIntervall = 0;
    this.errorCount = 0;
    this.lastServerAnswer = $.now();

    this.qrdRequestTime = 0;

    // create a fake ip address...
    if (typeof chosenProfile.login_id == 'undefined' || chosenProfile.login_id == '') {
        var randomHex = String(md5(String(Math.random())));
        this.loginId = randomHex.toUpperCase().substr(0,2);
        for (var i=1; i<6; i++) {
            this.loginId += '-' + randomHex.toUpperCase().substr(2*i,2);
        }
        chosenProfile.login_id = this.loginId;
    } else {
        this.loginId = chosenProfile.login_id;
    }
    window.name = this.loginId;

    // control variables for this class
    this.poll_regularly = 0;
    this.pollCounter = 0;
    this.dataObject = {};
    this.thisUser = { id: '', b_id: '', b_chat: { id: '' } };
    this.number_of_poll = 0;
    this.pollIsActive = false;
    this.shoutIsActive = false;
    this.lastUserAction = $.now();
    this.userDefinedStatus = userStatus;
    this.autoSleep = false;

    // queueing of the sent data
    this.outboundQueue = {};
    this.sendQueue = {};

    this.debuggingXmlAnswer = '';
}

ChatPollServerClass.prototype.addToOutboundQueue = function (myKey, myValue, type) {
    if (type != 'nonumber') {
        if (typeof this.outboundQueue[myKey] == 'undefined') {
            this.outboundQueue[myKey] = [];
        }
        this.outboundQueue[myKey].push(myValue);
    } else {
        this.outboundQueue[myKey] = myValue;
    }
};

ChatPollServerClass.prototype.createDataFromOutboundQueue = function (dataObject) {
    var newDataObject = this.lzm_commonTools.clone(dataObject);
    this.sendQueue = lzm_commonTools.clone(this.outboundQueue);
    for (var myKey in this.sendQueue) {
        if (typeof this.sendQueue[myKey] == 'object' && this.sendQueue[myKey] instanceof Array) {
            for (var i = 0; i < this.sendQueue[myKey].length; i++) {
                if (typeof this.sendQueue[myKey][i] == 'string') {
                    newDataObject[myKey + i] = this.sendQueue[myKey][i];
                } else if (typeof this.sendQueue[myKey][i] == 'object') {
                    for (var objKey in this.sendQueue[myKey][i]) {
                        newDataObject[myKey + objKey + i] = this.sendQueue[myKey][i][objKey];
                    }
                }
            }
        } else if (typeof this.sendQueue != 'undefined') {
            newDataObject[myKey] = this.sendQueue[myKey];
        }
    }
    return newDataObject;
};

ChatPollServerClass.prototype.cleanOutboundQueue = function (type) {
    if (typeof type != 'undefined' && (type == 'shout' || type == 'shout2')) {
        //console.log('Clean queue');
        //console.log(this.outboundQueue);
        //console.log(this.sendQueue);
        var myKey;
        var i;
        for (myKey in this.sendQueue) {
            if (typeof this.sendQueue[myKey] != 'string') {
                if (typeof this.sendQueue[myKey] != 'undefined' && this.sendQueue[myKey].length > 0) {
                    for (i = 0; i < this.sendQueue[myKey].length; i++) {
                        if (typeof this.sendQueue[myKey][i] == 'string') {
                            this.removePropertyFromDataObject(myKey + i);
                        } else if (typeof this.sendQueue[myKey][i] == 'object') {
                            for (var objKey in this.sendQueue[myKey][i]) {
                                this.removePropertyFromDataObject(myKey + objKey + i);
                            }
                        }
                    }
                }
            } else {
                this.removePropertyFromDataObject(myKey);
            }
        }
        //console.log('Data object cleaned');

        var tmpOutboundQueue = {};
        var outboundObjectOld = true;
        var numberOfElements = [];
        for (myKey in this.outboundQueue) {
            tmpOutboundQueue[myKey] = (typeof this.outboundQueue[myKey] == 'string') ? '' : [];
            if (typeof this.outboundQueue[myKey] != 'string') {
                if (typeof this.outboundQueue[myKey] != 'undefined' & this.outboundQueue[myKey].length > 0) {
                    for (i = 0; i < this.outboundQueue[myKey].length; i++) {
                        if (typeof this.sendQueue[myKey] != 'undefined') {
                            if (typeof this.outboundQueue[myKey][i] == 'object') {
                                outboundObjectOld = true;
                                for (objKey in this.outboundQueue[myKey][i]) {
                                    if (typeof this.sendQueue[myKey][i] == 'undefined' || this.outboundQueue[myKey][i][objKey] != this.sendQueue[myKey][i][objKey]) {
                                        outboundObjectOld = false;
                                    }
                                }
                                if (!outboundObjectOld) {
                                    tmpOutboundQueue[myKey].push(this.outboundQueue[myKey][i]);
                                }
                            } else {
                                if ($.inArray(this.outboundQueue[myKey][i], this.sendQueue[myKey]) == -1) {
                                    tmpOutboundQueue[myKey].push(this.outboundQueue[myKey][i])
                                }
                            }
                        }
                    }
                }
            } else {
                if (typeof this.sendQueue[myKey] != 'undefined' && this.outboundQueue[myKey] != this.sendQueue[myKey]) {
                    tmpOutboundQueue[myKey] = this.outboundQueue[myKey];
                }
            }
        }

        if (typeof tmpOutboundQueue != 'string') {
            for (myKey in tmpOutboundQueue) {
                if ((typeof tmpOutboundQueue[myKey] == 'string' && tmpOutboundQueue[myKey] == '') ||
                    (typeof tmpOutboundQueue[myKey] == 'object' && tmpOutboundQueue[myKey] instanceof Array && tmpOutboundQueue[myKey].length == 0)) {
                    delete tmpOutboundQueue[myKey];
                }
            }
        }

        //console.log(tmpOutboundQueue);
        this.outboundQueue = this.lzm_commonTools.clone(tmpOutboundQueue);
        //console.log('Outbound queue cleaned');
        this.sendQueue = {};
        //console.log('Send queue resetted');
        this.pollIsActive = false;
        //console.log('Poll active flag reasetted');
        this.startPolling();
    } else {
        this.pollIsActive = false;

    }
};

/**
 * Start polling the server, this will be done in intervals defined in config.js
 */
ChatPollServerClass.prototype.startPolling = function () {
    var thisClass = this;
    var pollIntervall = (thisClass.lzm_chatServerEvaluation.pollFrequency != 0) ?
        (thisClass.lzm_chatServerEvaluation.pollFrequency * 1000) : thisClass.lzm_commonConfig.lz_reload_interval;
    this.pollIntervall = pollIntervall;
    // poll once manually, then the setInterval function will fall in
    thisClass.pollServer(thisClass.fillDataObject(), 'regularly');
    if (thisClass.poll_regularly) {
        thisClass.stopPolling();
    }
    thisClass.poll_regularly = setInterval(function () {
        thisClass.pollServer(thisClass.fillDataObject(), 'regularly')
    }, pollIntervall);
};

/**
 * Stop polling the server again. Normally not needed as the only reason to stop polling is logout which will stop
 * polling the server anyhow
 */
ChatPollServerClass.prototype.stopPolling = function () {
    clearInterval(this.poll_regularly);
    this.poll_regularly = false;
};

ChatPollServerClass.prototype.logout = function () {
    this.stopPolling();
    this.user_status = 2;
    this.lzm_chatDisplay.user_status = 2;
    this.addToOutboundQueue('p_user_status', '2', 'nonumber');
    this.pollServer(this.fillDataObject(), 'logout');
};

ChatPollServerClass.prototype.pollServerResource = function(resource) {
    var thisClass = this;
    if (!thisClass.pollIsActive) {
        thisClass.pollIsActive = true;
        var p_acid = this.lzm_commonTools.pad(Math.floor(Math.random() * 99999).toString(10), 5);
        var acid = this.lzm_commonTools.pad(Math.floor(Math.random() * 1048575).toString(16), 5);
        var resourceDataObject = {
            p_process_resources: '',
            p_process_resources_va: resource.rid,
            p_process_resources_vb: lz_global_base64_encode(resource.text),
            p_process_resources_vc: resource.ty,
            p_process_resources_vd: lz_global_base64_encode(resource.ti),
            p_process_resources_ve: resource.di,
            p_process_resources_vf: resource.pid,
            p_process_resources_vg: resource.ra,
            p_process_resources_vh: resource.si,
            p_process_resources_vi: resource.t,
            p_acid: p_acid,
            p_user: thisClass.chosenProfile.login_name,
            p_pass: thisClass.chosenProfile.login_passwd,
            p_request: 'intern',
            p_action: 'send_resources',
            p_get_management: 1,
            p_loginid: thisClass.loginId,
            p_app: thisClass.isApp,
            p_web: thisClass.isWeb
        };
        var postUrl = lzm_chatPollServer.chosenProfile.server_protocol + lzm_chatPollServer.chosenProfile.server_url +
            '/server.php?acid=' + acid;

        //console.log(resourceDataObject.p_process_resources_vb);
        $.ajax({
            type: "POST",
            url: postUrl,
            //crossDomain: true,
            data: resourceDataObject,
            timeout: thisClass.lzm_commonConfig.pollTimeout,
            success: function (data) {
                //console.log('Resource submitted');
                thisClass.pollIsActive = false;
            },
            error: function (jqXHR, textStatus, errorThrown) {
                thisClass.pollIsActive = false;
                setTimeout(function() {
                    thisClass.pollServerResource(resource);
                }, 500);
            },
            dataType: 'text'
        });
    } else {
        setTimeout(function() {
                    thisClass.pollServerResource(resource);
                }, 500);
    }
};

/**
 * Poll the server once using the data object for login
 * After the server accepted the login, do not use this again
 * @param serverProtocol
 * @param serverUrl
 */
ChatPollServerClass.prototype.pollServerlogin = function (serverProtocol, serverUrl, logoutOtherInstance) {
    logoutOtherInstance = (typeof logoutOtherInstance != 'undefined') ? logoutOtherInstance : false;
    this.pollIsActive = true;
    var thisClass = this;
    var p_acid = this.lzm_commonTools.pad(Math.floor(Math.random() * 99999).toString(10), 5);
    var acid = this.lzm_commonTools.pad(Math.floor(Math.random() * 1048575).toString(16), 5);

    var loginDataObject = {
        p_user_status: thisClass.user_status,
        p_user: thisClass.chosenProfile.login_name,
        p_pass: thisClass.chosenProfile.login_passwd,
        p_acid: p_acid,
        p_request: 'intern',
        p_action: 'login',
        p_get_management: 1,
        p_version: thisClass.lzm_commonConfig.lz_version,
        p_clienttime: Math.floor($.now()/1000),
        p_app: thisClass.isApp,
        p_web: thisClass.isWeb,
        //p_ext_rse: this.qrdRequestTime,
        p_loginid: thisClass.loginId
    };
    if (logoutOtherInstance) {
        loginDataObject.p_iso = 1;
    }
    var postUrl = serverProtocol + serverUrl + '/server.php?acid=' + acid;
    //console.log(loginDataObject);
    //console.log(postUrl);
    $.ajax({
        type: "POST",
        url: postUrl,
        //crossDomain: true,
        data: loginDataObject,
        timeout: thisClass.lzm_commonConfig.pollTimeout,
        success: function (data) {
            //console.log(data);
            thisClass.lzm_chatServerEvaluation.chosen_profile = thisClass.chosenProfile;
            thisClass.lzm_chatServerEvaluation.myUserId = thisClass.chosenProfile.login_name;
            thisClass.lzm_chatDisplay.user_status = thisClass.user_status;
            thisClass.lzm_chatDisplay.myLoginId = thisClass.chosenProfile.login_name;
            //thisClass.lzm_chatServerEvaluation.lzm_chatTimeStamp.logTimeDifference();
            thisClass.lzm_chatDisplay.lzm_chatTimeStamp = thisClass.lzm_chatServerEvaluation.lzm_chatTimeStamp;
            thisClass.evaluateServerResponse(data);
            thisClass.startPolling();
        },
        error: function (jqXHR, textStatus, errorThrown) {
            if (jqXHR.statusText == 'timeout') {
                //console.log('Login timed out - trying again...');
                thisClass.pollServerlogin(serverProtocol, serverUrl)
            } else {
                console.log(postUrl);
                console.log(loginDataObject);
                console.log(jqXHR);
                //console.log('Status-Text : ' + textStatus);
                //console.log(jqXHR);
                //console.log('Error-Text : ' + errorThrown);
                thisClass.finishLogout('error', jqXHR);
            }
        },
        dataType: 'text'
    });
};

/**
 * Poll the livezilla server for information, the data send to the server uses the data object which will be altered
 * depending on the servers answer
 * @param dataObject
 * @param type
 */
ChatPollServerClass.prototype.pollServer = function (dataObject, type) {
    var thisClass = this;
    var thisTimeout = (typeof thisClass.lzm_chatServerEvaluation.timeoutClients != 'undefined' && thisClass.lzm_chatServerEvaluation.timeoutClients != 0) ?
        thisClass.lzm_chatServerEvaluation.timeoutClients * 1000 : thisClass.lzm_commonConfig.noAnswerTimeBeforeLogout;
    if (type == 'shout') {
        //console.log(thisClass.pollIsActive);
    }
    if (!thisClass.pollIsActive) {
        thisClass.pollIsActive = true;
        thisClass.pollCounter++;
        thisClass.doPoll(dataObject, type, thisTimeout);
    } else if (type == 'shout' || type == 'logout') {
        setTimeout(function () {
            //console.log('Wait a while and try again...');
            thisClass.pollServer(dataObject, type)
        }, 1000);
    }
};

ChatPollServerClass.prototype.doPoll = function(dataObject, type, serverTimeout) {
    var thisClass = this;
    var maxErrorCount = (typeof serverTimeout != 'undefined' && serverTimeout != 0) ? Math.ceil(serverTimeout / 5000) : 20;
    //console.log(dataObject);
    if (type == 'shout' || type == 'logout') {
        dataObject = thisClass.createDataFromOutboundQueue(dataObject);
        //console.log(dataObject);
    }
    //console.log(dataObject);
    var intervall = thisClass.lzm_chatDisplay.awayAfterTime * 60 * 1000;
    if (thisClass.lzm_chatDisplay.awayAfterTime != 0 && $.now() - this.lastUserAction >= intervall && !thisClass.autoSleep) {
        thisClass.autoSleep = true;
        thisClass.userDefinedStatus = this.user_status;
        thisClass.user_status = 3;
        thisClass.lzm_chatDisplay.user_status = 3;
        thisClass.lzm_chatDisplay.createUserControlPanel(thisClass.user_status, thisClass.lzm_chatServerEvaluation.myName,
            thisClass.lzm_chatServerEvaluation.myUserId);
    }
    if ($('#usersettings-button span.ui-btn-text').html() == '&nbsp;') {
        thisClass.lzm_chatDisplay.createUserControlPanel(thisClass.user_status, thisClass.lzm_chatServerEvaluation.myName,
            thisClass.lzm_chatServerEvaluation.myUserId);
    }
    $.ajax({
        type: "POST",
        url: thisClass.chosenProfile.server_protocol + thisClass.chosenProfile.server_url + '/server.php?acid=' +
            this.lzm_commonTools.pad(Math.floor(Math.random() * 1048575).toString(16), 5),
        //crossDomain: true,
        data: dataObject,
        timeout: thisClass.lzm_commonConfig.pollTimeout,
        success: function (data) {
            thisClass.errorCount = 0;
            thisClass.lastServerAnswer = $.now();
            if (type == 'logout' || type == 'logout2') {
                //console.log('logout');
                thisClass.finishLogout();
            } else {
                if (type == 'shout' || type == 'shout2') {
                    //console.log('shout');
                } else {
                    //console.log('normal');
                }
                thisClass.evaluateServerResponse(data, type);
                thisClass.number_of_poll++;
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            if (jqXHR.statusText == 'timeout') {
                if (type == 'shout' || type == 'logout') {
                    setTimeout(function () {
                        thisClass.doPoll(dataObject, type, serverTimeout);
                    }, 500);
                } else {
                    thisClass.stopPolling();
                    thisClass.pollIsActive = false;
                    setTimeout(function () {
                        thisClass.startPolling();
                    }, 5000);
                }
            } else {
                if (type == 'shout' || type == 'logout') {
                    setTimeout(function () {
                        thisClass.doPoll(dataObject, type, serverTimeout);
                    }, 500);
                } else {
                    //console.log(textStatus);
                    //console.log(jqXHR);
                    //console.log(errorThrown);
                    thisClass.lastServerAnswer = $.now();
                    if (thisClass.errorCount >= maxErrorCount) {
                        thisClass.finishLogout('error', jqXHR);
                    } else {
                        thisClass.stopPolling();
                        thisClass.pollIsActive = false;
                        setTimeout(function () {
                            thisClass.startPolling();
                        }, 5000);
                        thisClass.errorCount++;
                    }
                }
            }
        },
        dataType: 'text'
    });
};

ChatPollServerClass.prototype.wakeupFromAutoSleep = function() {
    this.lastUserAction = $.now();
    if (this.autoSleep) {
        this.autoSleep = false;
        this.user_status = this.userDefinedStatus;
        this.lzm_chatDisplay.user_status = this.userDefinedStatus;
        this.lzm_chatDisplay.createUserControlPanel(this.user_status, this.lzm_chatServerEvaluation.myName,
            this.lzm_chatServerEvaluation.myUserId);
    }
};

ChatPollServerClass.prototype.pollReport = function () {
    var thisClass = this;
    /*$.ajax({
        type: "POST",
        url: thisClass.chosenProfile.server_protocol + thisClass.chosenProfile.server_url + '/server.php?acid=' +
            this.lzm_commonTools.pad(Math.floor(Math.random() * 1048575).toString(16), 5),
        //crossDomain: true,
        data: {},
        timeout: thisClass.lzm_commonConfig.pollTimeout,
        success: function (data) {
            thisClass.evaluateServerResponse(data);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            if (jqXHR.statusText == 'timeout') {
                if (type == 'shout' || type == 'logout') {
                    thisClass.doPoll(dataObject, type);
                } else {
                    //console.log('Regular poll timed out');
                }
            } else {
                //console.log(textStatus);
                //console.log(jqXHR);
                //console.log(errorThrown);
            }
        },
        dataType: 'text'
    });*/
    //console.log('Poll reports');
    thisClass.pollCounter = 0;
    thisClass.evaluateServerResponse('');
};

/**
 * fill the data object with initial values and return it for usage in the poll server polling
 * @return {Object}
 */
ChatPollServerClass.prototype.fillDataObject = function () {
    // fill the data object with initial values
    if (this.lzm_chatDisplay.user_status != this.user_status) {
        this.user_status = this.lzm_chatDisplay.user_status;
        this.userDefinedStatus = this.lzm_chatDisplay.user_status;
    }
    this.dataObject.p_user_status = this.user_status;
    this.dataObject.p_user = this.lzm_chatServerEvaluation.chosen_profile.login_name;
    this.dataObject.p_pass = this.lzm_chatServerEvaluation.chosen_profile.login_passwd;
    this.dataObject.p_acid = this.lzm_commonTools.pad(Math.floor(Math.random() * 99999).toString(10), 5);
    this.dataObject.p_request = 'intern';
    this.dataObject.p_action = 'listen';
    this.dataObject.p_get_management = 1;
    this.dataObject.p_version = this.lzm_commonConfig.lz_version;
    this.dataObject.p_clienttime = Math.floor($.now()/1000);
    this.dataObject.p_app = this.isApp;
    this.dataObject.p_web = this.isWeb;
    this.dataObject.p_ext_rse = this.qrdRequestTime;
    this.dataObject.p_loginid = this.loginId;
    if (this.lzm_chatServerEvaluation.rec_posts.length > 0) {
        this.dataObject.p_rec_posts = this.lzm_chatServerEvaluation.rec_posts.join('><');
        this.lzm_chatServerEvaluation.rec_posts = [];
    } else {
        delete this.dataObject.p_rec_posts;
    }

    return this.dataObject;
};

/**
 * evaluate the server response and fill the data arrays and objects accordingly or do some action
 * upon server response
 * @param xmlString
 */
ChatPollServerClass.prototype.evaluateServerResponse = function (xmlString, type) {
    if (xmlString != '') {
        this.debuggingXmlAnswer = xmlString;
    }

    var thisClass = this;
    if (thisClass.lzm_chatServerEvaluation.login_data.timediff != thisClass.lzm_chatServerEvaluation.lzm_chatTimeStamp.timeDifference) {
        thisClass.lzm_chatServerEvaluation.lzm_chatTimeStamp.setTimeDifference(thisClass.lzm_chatServerEvaluation.login_data.timediff);
    }
    if (xmlString != '') {
        var xmlDoc = $.parseXML(xmlString);

        // listen - get the global hash
        var disabled;
        $(xmlDoc).find('listen').each(function () {
            var listen = $(this);
            thisClass.dataObject.p_gl_a = lz_global_base64_url_decode(listen.attr('h'));
            disabled = lz_global_base64_url_decode(listen.attr('disabled'));
        });
        if (disabled == 1) {
            lzm_chatDisplay.serverIsDisabled = true;
        } else {
            lzm_chatDisplay.serverIsDisabled = false;
        }

        var validationError = thisClass.lzm_chatServerEvaluation.getValidationError(xmlDoc);
        if ($.inArray(validationError, [-1, 11]) == -1) {
            thisClass.logout();
            thisClass.stopPolling();
            thisClass.lzm_chatDisplay.logoutOnValidationError(validationError, thisClass.isWeb, thisClass.isApp,
                thisClass.chosenProfile);
        }
        thisClass.lzm_chatServerEvaluation.getLogin(xmlDoc);
        thisClass.lzm_chatDisplay.myId = thisClass.lzm_chatServerEvaluation.myId;
        thisClass.lzm_chatDisplay.myName = thisClass.lzm_chatServerEvaluation.myName;
        var p_gl_c = thisClass.lzm_chatServerEvaluation.getGlobalConfiguration(xmlDoc);
        if (p_gl_c != '')
            thisClass.addPropertyToDataObject('p_gl_c', p_gl_c);
        var p_int_d = thisClass.lzm_chatServerEvaluation.getDepartments(xmlDoc);
        if (p_int_d != '')
            thisClass.addPropertyToDataObject('p_int_d', p_int_d);
        var p_int_r = thisClass.lzm_chatServerEvaluation.getInternalUsers(xmlDoc);
        if (p_int_r != '')
            thisClass.addPropertyToDataObject('p_int_r', p_int_r);
        var p_ext_u = thisClass.lzm_chatServerEvaluation.getExternalUsers(xmlDoc);
        if (p_ext_u != '')
            thisClass.addPropertyToDataObject('p_ext_u', p_ext_u);
        var p_ext_f = thisClass.lzm_chatServerEvaluation.getExternalForward(xmlDoc);
        if (p_ext_f != '')
            thisClass.addPropertyToDataObject('p_ext_f', p_ext_f);
        var p_gl_e = thisClass.lzm_chatServerEvaluation.getGlobalErrors(xmlDoc);
        if (p_gl_e != '')
            thisClass.addPropertyToDataObject('p_gl_e', p_gl_e);
        var p_int_wp = thisClass.lzm_chatServerEvaluation.getIntWp(xmlDoc);
        if (p_int_wp != '') {
            thisClass.addPropertyToDataObject('p_int_wp', p_int_wp);
        }
        thisClass.lzm_chatServerEvaluation.getUsrP(xmlDoc);


        thisClass.lzm_chatServerEvaluation.getExtC(xmlDoc);

        if (thisClass.lzm_chatServerEvaluation.myId != '') {
            if (thisClass.qrdRequestTime == 0) {
                var requestTime = thisClass.lzm_commonStorage.loadValue('qrd_request_time_' + thisClass.lzm_chatServerEvaluation.myId);
                thisClass.qrdRequestTime = (requestTime != null) ? JSON.parse(requestTime) : thisClass.qrdRequestTime;
                thisClass.lzm_chatServerEvaluation.resourceLastEdited = thisClass.qrdRequestTime;
                var resources = thisClass.lzm_commonStorage.loadValue('qrd_' + thisClass.lzm_chatServerEvaluation.myId);
                thisClass.lzm_chatServerEvaluation.resources = (resources != null) ? JSON.parse(resources) : thisClass.lzm_chatServerEvaluation.resources;
                var resourceIdList = thisClass.lzm_commonStorage.loadValue('qrd_id_list_' + thisClass.lzm_chatServerEvaluation.myId);
                thisClass.lzm_chatServerEvaluation.resourceIdList = (resourceIdList != null) ? JSON.parse(resourceIdList) : thisClass.lzm_chatServerEvaluation.resourceIdList;
            }
            thisClass.qrdRequestTime = Math.max(thisClass.qrdRequestTime, thisClass.lzm_chatServerEvaluation.getResources(xmlDoc));
            //thisClass.qrdRequestTime = 1374071137;
            //thisClass.lzm_chatServerEvaluation.getResources(xmlDoc);
        }

        // depending on the server response recreate parts of the html
        if (thisClass.lzm_chatServerEvaluation.new_ext_u) {
            var userUpdated = thisClass.lzm_chatDisplay.updateShowVisitor(thisClass.lzm_chatServerEvaluation.external_users);
            if (userUpdated && thisClass.lzm_chatDisplay.ShowVisitorInfo) {
                thisClass.lzm_chatDisplay.createVisitorInformation(thisClass.lzm_chatServerEvaluation.internal_users,
                    thisClass.lzm_chatDisplay.infoUser);
                thisClass.lzm_chatDisplay.createBrowserHistory(thisClass.lzm_chatDisplay.infoUser);
            }
        }
        if (thisClass.lzm_chatServerEvaluation.new_ext_f || thisClass.lzm_chatServerEvaluation.new_ext_u) {
            if (thisClass.lzm_chatDisplay.selected_view == 'external' && !thisClass.lzm_chatDisplay.ShowVisitorInfo) {
                    thisClass.lzm_chatDisplay.setExternalUserList('new', thisClass.lzm_chatServerEvaluation.getExternalUserList('new'));
                    thisClass.lzm_chatServerEvaluation.setExternalUserList('new', []);
                    thisClass.lzm_chatDisplay.setExternalUserList('changed', thisClass.lzm_chatServerEvaluation.getExternalUserList('changed'));
                    thisClass.lzm_chatServerEvaluation.setExternalUserList('changed', []);
                    thisClass.lzm_chatDisplay.updateVisitorList(thisClass.lzm_chatServerEvaluation.external_users,
                        thisClass.lzm_chatServerEvaluation.chatObject, lzm_chatServerEvaluation.internal_users);
            }
        }
        if (thisClass.lzm_chatServerEvaluation.new_usr_p || thisClass.lzm_chatServerEvaluation.new_ext_f ||
            thisClass.lzm_chatServerEvaluation.new_ext_u || thisClass.lzm_chatServerEvaluation.new_int_u ||
            thisClass.lzm_chatServerEvaluation.new_int_d) {
            if (thisClass.lzm_chatDisplay.selected_view == 'internal') {
                thisClass.lzm_chatDisplay.createOperatorList(thisClass.lzm_chatServerEvaluation.internal_departments,
                    thisClass.lzm_chatServerEvaluation.internal_users, thisClass.lzm_chatServerEvaluation.chatObject,
                    thisClass.lzm_chatServerEvaluation.chosen_profile);
            }
        }
        if (thisClass.lzm_chatServerEvaluation.new_usr_p || thisClass.lzm_chatServerEvaluation.new_ext_f ||
            thisClass.lzm_chatServerEvaluation.new_ext_u || thisClass.lzm_chatServerEvaluation.new_int_u ||
            thisClass.lzm_chatServerEvaluation.new_int_d) {
            if (thisClass.lzm_chatDisplay.selected_view == 'mychats') {
                thisClass.lzm_chatDisplay.createChatHtml(thisClass.lzm_chatServerEvaluation.chats,
                    thisClass.lzm_chatServerEvaluation.chatObject, thisClass.lzm_chatDisplay.thisUser,
                    thisClass.lzm_chatServerEvaluation.internal_users, thisClass.lzm_chatServerEvaluation.external_users,
                    thisClass.lzm_chatDisplay.active_chat_reco);
            }
            var updateVisitorListAsWell = (thisClass.lzm_chatDisplay.selected_view == 'external' && !thisClass.ShowVisitorInfo) ? true : false;
            thisClass.lzm_chatDisplay.createActiveChatPanel(thisClass.lzm_chatServerEvaluation.external_users,
                thisClass.lzm_chatServerEvaluation.internal_users, thisClass.lzm_chatServerEvaluation.internal_departments,
                thisClass.lzm_chatServerEvaluation.chatObject, updateVisitorListAsWell);
        }
        /*if (thisClass.lzm_chatServerEvaluation.new_gl_e) {
            thisClass.lzm_chatDisplay.createErrorHtml(thisClass.lzm_chatServerEvaluation.global_errors);
        }*/
        if (thisClass.lzm_chatServerEvaluation.new_ext_c) {
            console.log('New ext c');
        }

        thisClass.lzm_chatDisplay.createChatWindowLayout(false);
        thisClass.lzm_chatServerEvaluation.new_ext_u = false;
        thisClass.lzm_chatServerEvaluation.new_usr_p = false;
        thisClass.lzm_chatServerEvaluation.new_ext_f = false;
        thisClass.lzm_chatServerEvaluation.new_int_u = false;
        thisClass.lzm_chatServerEvaluation.new_int_d = false;
        thisClass.lzm_chatServerEvaluation.new_gl_e = false;
        if (thisClass.thisUser.id == '')
            thisClass.lzm_chatDisplay.switchCenterPage('home');

    }
    thisClass.cleanOutboundQueue(type);
    thisClass.lzm_chatDisplay.showDisabledWarning();
};

/**
 * Add a property to the data object for polling the server
 * @param propertyName
 * @param propertyValue
 */
ChatPollServerClass.prototype.addPropertyToDataObject = function (propertyName, propertyValue) {
    //console.log(propertyName + ' --- ' + propertyValue);
    this.dataObject[propertyName] = propertyValue;
};

/**
 * Remove a property from the data object for polling the server
 * @param propertyName
 */
ChatPollServerClass.prototype.removePropertyFromDataObject = function (propertyName) {
    delete this.dataObject[propertyName];
};

ChatPollServerClass.prototype.finishLogout = function(cause, jqXHR) {
    var thisClass = this;
    this.lzm_chatDisplay.askBeforeUnload = false;
    if (typeof cause != 'undefined' && cause == 'server timeout') {
        var thisTimeout = (typeof thisClass.lzm_chatServerEvaluation.timeoutClients != 'undefined' && thisClass.lzm_chatServerEvaluation.timeoutClients != 0) ?
            thisClass.lzm_chatServerEvaluation.timeoutClients : thisClass.lzm_commonConfig.noAnswerTimeBeforeLogout / 1000;
        $.blockUI({message: null});
        alert(t('The server did not respond for more then <!--number_of_seconds--> seconds.',
            [['<!--number_of_seconds-->', thisTimeout]]) +
            '\n\n' + t('Logging out!'));
    } else if (typeof cause != 'undefined' && cause == 'error') {
        $.blockUI({message: null});
        alert(t('The server returned an error') + '\n' +
            t('Error code : <!--http_error-->',[['<!--http_error-->',jqXHR.status]]) +
            '\n' + t('Error text : <!--http_error_text-->',[['<!--http_error_text-->',jqXHR.statusText]]) +
            '\n\n' + t('Logging out!'))
    }
    if (this.isWeb == 1) {
        window.location.href = 'index.php';
    } else if (this.isApp == 1) {
        window.location.href = 'logout.html' +
            '?user_volume_' + this.chosenProfile.index + '=' + this.chosenProfile.user_volume +
            '&user_away_after_' + this.chosenProfile.index + '=' + this.chosenProfile.user_away_after +
            '&play_incoming_message_sound_' + this.chosenProfile.index + '=' + this.chosenProfile.play_incoming_message_sound +
            '&play_incoming_chat_sound_' + this.chosenProfile.index + '=' + this.chosenProfile.play_incoming_chat_sound +
            '&repeat_incoming_chat_sound_' + this.chosenProfile.index + '=' + this.chosenProfile.repeat_incoming_chat_sound +
            '&language_' + this.chosenProfile.index + '=' + this.chosenProfile.language;
    }
};
