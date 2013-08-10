/****************************************************************************************
 * LiveZilla CommonToolsClass.js
 *
 * Copyright 2013 LiveZilla GmbH
 * All rights reserved.
 * LiveZilla is a registered trademark.
 *
 ***************************************************************************************/

function CommonToolsClass() {
    //console.log('CommonToolsClass initiated');
}

/**
 * Pad the given number with leading zeors so it has the given length
 * @param number
 * @param length
 * @return {String}
 */
CommonToolsClass.prototype.pad = function (number, length, paddingSymbol, paddingSide) {
    if (typeof paddingSymbol == 'undefined' || paddingSymbol == '') {
        paddingSymbol = '0';
    } else if (paddingSymbol == '&nbsp;') {
        paddingSymbol = '°'
    }
    if (typeof paddingSide == 'undefined' || paddingSide == '')
        paddingSide = 'l';
    var str = String(number);
    while (str.length < length) {
        if (paddingSide == 'l')
            str = paddingSymbol + str;
        else
            str = str + paddingSymbol;
    }
    str=str.replace(/°/g,"&nbsp;");
    return str;
};

/**
 * Clone a javascript object
 * @param originalObject
 * @return {*}
 */
CommonToolsClass.prototype.clone = function (originalObject) {
    if (null == originalObject || "object" != typeof originalObject) return originalObject;
    copyObject = {};
    for (key in originalObject) {
        if (typeof originalObject[key] == 'object') {
            if (originalObject[key] instanceof Array) {
                copyObject[key] = [];
                for (var i=0; i< originalObject[key].length; i++) {
                    copyObject[key].push(originalObject[key][i]);
                }
            } else {
                copyObject[key] = this.clone(originalObject[key]);
            }
        } else {
            copyObject[key] = originalObject[key];
        }
    }
    return copyObject;
};

CommonToolsClass.prototype.getUrlParts = function () {
    var thisUrlParts = document.URL.split('://');
    var thisProtocol = thisUrlParts[0] + '://';

    thisUrlParts = thisUrlParts[1].split('/');
    var thisUrlRest = '';
    var urlOffset = 1
    if (thisUrlParts[thisUrlParts.length - 1].indexOf('html') != -1 || thisUrlParts[thisUrlParts.length - 1].indexOf('php') != -1 || thisUrlParts[thisUrlParts.length - 1] == '') {
        urlOffset = 2;
    }
    for (var i = 1; i < (thisUrlParts.length - urlOffset); i++) {
        thisUrlRest += '/' + thisUrlParts[i];
    }

    var thisUrlBase = '';
    var thisPort = '';
    if (thisUrlParts[0].indexOf(':') == -1) {
        thisUrlBase = thisUrlParts[0];
        if (thisProtocol == 'https://') {
            thisPort = '443';
        } else {
            thisPort = '80';
        }
    } else {
        thisUrlParts = thisUrlParts[0].split(':');
        thisUrlBase = thisUrlParts[0];
        thisPort = thisUrlParts[1];
    }
    return {protocol:thisProtocol, urlBase:thisUrlBase, urlRest:thisUrlRest, port:thisPort};
};

CommonToolsClass.prototype.createDefaultProfile = function (runningFromApp, chosenProfile) {

    if (runningFromApp == false && (chosenProfile == -1 || chosenProfile == null)) {
        this.storageData = [];
        var indexes = lzm_commonStorage.loadValue('indexes');
        var indexList = [];
        if (indexes != null && indexes != '') {
            indexList = indexes.split(',');
        }
        if ($.inArray('0', indexList) == -1) {
            var thisUrlParts = lzm_commonTools.getUrlParts();
            var dataSet = {};
            dataSet.index = 0;
            dataSet.server_profile = 'Default profile';
            dataSet.server_protocol = thisUrlParts.protocol;
            dataSet.server_url = thisUrlParts.urlBase + thisUrlParts.urlRest;
            dataSet.server_port = thisUrlParts.port;
            dataSet.login_name = '';
            dataSet.login_passwd = '';
            //dataSet.user_volume = 60;
            if (indexes != null && indexes != '') {
                lzm_commonStorage.saveValue('indexes', '0,' + indexes);
            } else {
                lzm_commonStorage.saveValue('indexes', '0');
            }
            lzm_commonStorage.saveProfile(dataSet);
        }
    }
};

CommonToolsClass.prototype.getHumanDate = function(dateObject, returnType, language) {
    var time =  this.pad(dateObject.getHours(), 2) + ':' +
        this.pad(dateObject.getMinutes(), 2) + ':' +
        this.pad(dateObject.getSeconds(), 2);
    var date = '';
    switch (language) {
        case 'de':
            date = this.pad(dateObject.getDate(), 2) + '.' +
                this.pad((dateObject.getMonth() + 1), 2) + '.' +
                dateObject.getFullYear();
            break;
        default:
            date = dateObject.getFullYear() + '-' +
                this.pad((dateObject.getMonth() + 1), 2) + '-' +
                this.pad(dateObject.getDate(), 2)
    }

    var returnValue = '';
    switch (returnType) {
        case 'time':
            returnValue = time;
            break;
        case 'date':
            returnValue = date;
            break;
        default:
            returnValue = date + ' ' + time;
    }
    return returnValue;
};

