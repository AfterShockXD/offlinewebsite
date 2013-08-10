/****************************************************************************************
 * LiveZilla configure.js
 *
 * Copyright 2013 LiveZilla GmbH
 * All rights reserved.
 * LiveZilla is a registered trademark.
 *
 ***************************************************************************************/

// variables used or lzm class objects
var lzm_commonConfig = {};
var lzm_commonTools = {};
var lzm_commonDisplay = {};
var lzm_commonStorage = {};
var lzm_commonTranslation = {};

var runningFromApp = false;
var localDbPrefix = '';
var loopCounter = 0;

function t(myString, replacementArray) {
    return lzm_commonTranslation.translate(myString, replacementArray);
}

function fillStringsFromTranslation(selectedIndex) {
    if (loopCounter > 49 || lzm_commonTranslation.translationArray.length != 0) {
        lzm_commonDisplay.fillProfileSelectList(lzm_commonStorage.storageData, runningFromApp, selectedIndex);
        $('#back_btn span.ui-btn-text').text(t('Cancel'));
        $('#new_profile_btn span.ui-btn-text').text(t('New profile'));
        $('#edit_profile_btn span.ui-btn-text').text(t('Edit profile'));
        $('#del_profile_btn span.ui-btn-text').text(t('Delete profile'));
        $('#headline1').html(t('Server profiles'));

        $('#save_new_profile span.ui-btn-text').text(t('Save profile'));
        $('#save_login-text span.ui-btn-text').text(t('Save login data'));
        $('#server_profile-text').html(t('Profile name'));
        $('#server_protocol-text').html(t('Server Protocol'));
        $('#server_url-text').html(t('Server Url'));
        $('#server_port-text').html(t('Port'));
        $('#login_name-text').html(t('Username:'));
        $('#login_passwd-text').html(t('Password:'));

        $('#save_edit_profile span.ui-btn-text').text(t('Save profile'));
        $('#edit_save_login-text span.ui-btn-text').text(t('Save login data'));
        $('#edit_server_profile-text').html(t('Profile name'));
        $('#edit_server_protocol-text').html(t('Server Protocol'));
        $('#edit_server_url-text').html(t('Server Url'));
        $('#edit_server_port-text').html(t('Port'));
        $('#edit_login_name-text').html(t('Username:'));
        $('#edit_login_passwd-text').html(t('Password:'));
    } else {
        loopCounter++;
        setTimeout(function() {fillStringsFromTranslation(selectedIndex);}, 50);
    }
}

$(document).ready(function () {
    // initiate the lzm classes needed
    lzm_commonConfig = new CommonConfigClass();
    lzm_commonTools = new CommonToolsClass();
    if (!runningFromApp) {
        localDbPrefix = md5(lzm_commonTools.getUrlParts()['urlRest']).substr(0,10);
        //console.log(localDbPrefix);
    }
    lzm_commonStorage = new CommonStorageClass(localDbPrefix);
    // load the storage values and fill the profile select list
    lzm_commonStorage.loadProfileData();
    var selectedIndex = (typeof lzm_commonStorage.loadValue('last_chosen_session') != 'undefined' &&
        lzm_commonStorage.loadValue('last_chosen_profile') != 'undefined' &&
        lzm_commonStorage.loadValue('last_chosen_profile') != null) ?
        lzm_commonStorage.loadValue('last_chosen_profile') : -1;
    var chosenProfile = {language: ''};
    if (selectedIndex != -1) {
        chosenProfile = lzm_commonStorage.getProfileByIndex(selectedIndex);
    }

    lzm_commonDisplay = new CommonDisplayClass();
    if (!runningFromApp) {
        var urlParts = lzm_commonTools.getUrlParts();
        lzm_commonTranslation = new CommonTranslationClass(urlParts.protocol, urlParts.urlBase + ':' +
            urlParts.port + urlParts.urlRest, false, chosenProfile.language);
    } else {
        lzm_commonTranslation = new CommonTranslationClass('', '', true, chosenProfile.language);
    }
    fillStringsFromTranslation(selectedIndex);

    // read the rul of this file and split it into the protocol and the base url of this installation
    var thisUrlParts = lzm_commonTools.getUrlParts();
    var thisProtocol = thisUrlParts.protocol;
    var thisUrl = thisUrlParts.urlBase + thisUrlParts.urlRest;
    var thisPort = thisUrlParts.port;


    var unsafed_data = false;

    var thisLoginData = $('.login_data');

    var thisChangeConfig = $('.change-config');
    var thisEditLoginName = $('#edit_login_name');
    var thisEditLoginPassword = $('#edit_login_passwd');
    var thisEditSaveLogin = $('#edit_save_login');
    var thisEditServerProfile = $('#edit_server_profile');
    var thisEditServerUrl = $('#edit_server_url');
    var thisEditServerPort = $('#edit_server_port');

    var thisServerProfile = $('#server_profile');
    var thisServerUrl = $('#server_url');
    var thisServerPort = $('#server_port');
    var thisSaveLogin = $('#save_login');
    var thisLoginName = $('#login_name');
    var thisLoginPassword = $('#login_passwd');

    if (selectedIndex != -1) {
        thisChangeConfig.removeClass('ui-disabled');
    }

    if (runningFromApp == false) {
        $('.server-data').css({display: 'none'});
        thisSaveLogin.prop('checked', true).checkboxradio("refresh");
        $('#login_name').textinput('enable');
        $('#login_passwd').textinput('enable');
    }

    $('#back_btn').click(function () {
        window.location.href = "./index.html";
    });

    $('#server_profile_selection').change(function () {
        if ($(this).val() != -1) {
            thisChangeConfig.removeClass('ui-disabled');
        } else {
            thisChangeConfig.addClass('ui-disabled');
        }
        $('#new_profile_form').css('display', 'none');
        $('#edit_profile_form').css('display', 'none');
    });

    $('.data-input').change(function() {
        unsafed_data = true;
    });

    $('#new_profile_btn').click(function () {
        $('#no-profile').prop('selected', 'true');
        $('#server_profile_selection').selectmenu('refresh');
        $('#edit_profile_btn').addClass('ui-disabled');
        $('#del_profile_btn').addClass('ui-disabled');


        if (!runningFromApp)
            $('#server_url').val(thisUrl);
        var protocolHtml = '';
        for (var i = 0; i < lzm_commonConfig.lz_server_protocols.length; i++) {
            if (thisProtocol == lzm_commonConfig.lz_server_protocols[i].name) {
                protocolHtml += '<option value="' + lzm_commonConfig.lz_server_protocols[i].name + '" selected="selected">' + lzm_commonConfig.lz_server_protocols[i].name + '</option>';
            } else {
                protocolHtml += '<option value="' + lzm_commonConfig.lz_server_protocols[i].name + '">' + lzm_commonConfig.lz_server_protocols[i].name + '</option>';
            }
        }
        $('#server_protocol').html(protocolHtml).selectmenu('refresh');
        $('#server_protocol').change(function() {
            for (var j=0; j<lzm_commonConfig.lz_server_protocols.length; j++) {
                if ($('#server_protocol').val() == lzm_commonConfig.lz_server_protocols[j].name) {
                    $('#server_port').val(lzm_commonConfig.lz_server_protocols[j].port);
                }
            }
        });
        $('#server_port').val(thisPort);

        $('#new_profile_form').css('display', 'block');
        $('#edit_profile_form').css('display', 'none');
    });

    $('#edit_profile_btn').click(function () {
        var dataSet = lzm_commonStorage.getProfileByIndex($('#server_profile_selection').val());
        $('#profile_index').val(dataSet.index);
        $('#edit_server_profile').val(dataSet.server_profile);

        var protocolHtml = '';
        for (var i = 0; i < lzm_commonConfig.lz_server_protocols.length; i++) {
            if (dataSet.server_protocol == lzm_commonConfig.lz_server_protocols[i].name) {
                protocolHtml += '<option value="' + lzm_commonConfig.lz_server_protocols[i].name +
                    '" selected="selected">' + lzm_commonConfig.lz_server_protocols[i].name + '</option>';
            } else {
                protocolHtml += '<option value="' + lzm_commonConfig.lz_server_protocols[i].name + '">' +
                    lzm_commonConfig.lz_server_protocols[i].name + '</option>';
            }
        }
        $('#edit_server_protocol').html(protocolHtml).selectmenu('refresh');
        $('#edit_server_protocol').change(function() {
            for (var j=0; j<lzm_commonConfig.lz_server_protocols.length; j++) {
                if ($('#edit_server_protocol').val() == lzm_commonConfig.lz_server_protocols[j].name) {
                    $('#edit_server_port').val(lzm_commonConfig.lz_server_protocols[j].port);
                }
            }
        });
        $('#edit_server_url').val(dataSet.server_url);
        $('#edit_server_port').val(dataSet.server_port);
        if (runningFromApp == false || dataSet.login_name != '' || dataSet.login_passwd != '') {
            thisEditLoginName.val(dataSet.login_name);
            thisEditLoginPassword.val(dataSet.login_passwd);
            thisEditSaveLogin.prop('checked', true).checkboxradio("refresh");
            thisLoginData.textinput('enable');
        } else {
            thisEditLoginName.val('');
            thisEditLoginPassword.val('');
            thisEditSaveLogin.prop('checked', false).checkboxradio("refresh");
            thisLoginData.textinput('disable');
        }
        $('#edit_profile_form').css('display', 'block');
        $('#new_profile_form').css('display', 'none');
    });

    $('#del_profile_btn').click(function () {
        lzm_commonStorage.deleteProfile($('#server_profile_selection').val());
        $('#new_profile_form').css('display', 'none');
        $('#edit_profile_form').css('display', 'none');
        lzm_commonDisplay.fillProfileSelectList(lzm_commonStorage.storageData, runningFromApp, -1);
        $('#edit_profile_form').css('display', 'none');
        $('#new_profile_form').css('display', 'none');

        $('#no-profile').prop('selected', 'true');
        $('#server_profile_selection').selectmenu('refresh');
        $('#edit_profile_btn').addClass('ui-disabled');
        $('#del_profile_btn').addClass('ui-disabled');
        lzm_commonStorage.saveValue('last_chosen_profile', -1);
        window.location.href = "./index.html";
    });

    $('.save_login').click(function () {
        if ($(this).prop('checked') == true) {
            thisLoginData.textinput('enable');
        } else {
            thisLoginData.textinput('disable');
        }
    });

    $('#save_new_profile').click(function () {
        unsafed_data = false;
        var dataSet = {};
        dataSet.index = -1;
        dataSet.server_profile = thisServerProfile.val();
        dataSet.server_protocol = $('#server_protocol').val();
        dataSet.server_url = thisServerUrl.val();
        dataSet.server_port = thisServerPort.val();
        if (thisSaveLogin.prop('checked') == true) {
            dataSet.login_name = thisLoginName.val();
            dataSet.login_passwd = thisLoginPassword.val();
        } else {
            dataSet.login_name = '';
            dataSet.login_passwd = '';
        }

        //console.log(dataSet);
        var safedIndex = lzm_commonStorage.saveProfile(dataSet);
        lzm_commonDisplay.fillProfileSelectList(lzm_commonStorage.storageData, runningFromApp, safedIndex);

        thisServerProfile.val('');
        thisServerUrl.val('');
        thisServerPort.val('80');
        thisLoginName.val('');
        thisLoginPassword.val('');
        thisSaveLogin.prop('checked', false).checkboxradio("refresh");
        thisLoginData.textinput('disable');
        $('#new_profile_form').css('display', 'none');
        lzm_commonStorage.saveValue('last_chosen_profile', safedIndex);
        window.location.href = "./index.html";
    });

    $('#save_edit_profile').click(function () {
        unsafed_data = false;
        var dataSet = {};
        dataSet.index = $('#profile_index').val();
        dataSet.server_profile = thisEditServerProfile.val();
        dataSet.server_protocol = $('#edit_server_protocol').val();
        dataSet.server_url = thisEditServerUrl.val();
        dataSet.server_port = thisEditServerPort.val();
        if (thisEditSaveLogin.prop('checked') == true) {
            dataSet.login_name = thisEditLoginName.val();
            dataSet.login_passwd = thisEditLoginPassword.val();
        } else {
            dataSet.login_name = '';
            dataSet.login_passwd = '';
        }

        var safedIndex = lzm_commonStorage.saveProfile(dataSet);
        lzm_commonDisplay.fillProfileSelectList(lzm_commonStorage.storageData, runningFromApp, safedIndex);

        thisEditServerProfile.val('');
        thisEditServerUrl.val('');
        thisEditServerPort.val('');
        thisEditLoginName.val('');
        thisEditLoginPassword.val('');
        thisEditSaveLogin.prop('checked', false).checkboxradio("refresh");
        thisLoginData.textinput('disable');
        $('#edit_profile_form').css('display', 'none');
        $('#edit_profile_btn').addClass('ui-disabled');
        $('#del_profile_btn').addClass('ui-disabled');
        lzm_commonStorage.saveValue('last_chosen_profile', safedIndex);
        window.location.href = "./index.html";
    });

});
