/****************************************************************************************
 * LiveZilla CommonStorageClass.js
 *
 * Copyright 2013 LiveZilla GmbH
 * All rights reserved.
 * LiveZilla is a registered trademark.
 *
 ***************************************************************************************/

function CommonStorageClass(localDbPrefix) {
    //console.log('CommonStorageClass initiated');

    // control variables for this class
    this.storageData = [];
    if (typeof localDbPrefix != 'undefined' && localDbPrefix != '') {
        this.localDbPrefix = localDbPrefix + '_';
    } else {
        this.localDbPrefix = '';
    }
}

/**
 * get all the data stored in the local storage and save it to the storageData array
 */
CommonStorageClass.prototype.loadProfileData = function() {
    this.storageData = [];
    var indexes = this.loadValue('indexes');
    if (indexes != null && indexes != '') {
        var indexList = indexes.split(',');
        for (var i = 0; i < indexList.length; i++) {
            var dataSet = {};
            dataSet.index = indexList[i];
            dataSet.server_profile = this.loadValue('server_profile_' + String(indexList[i]));
            dataSet.server_protocol = this.loadValue('server_protocol_' + String(indexList[i]));
            dataSet.server_url = this.loadValue('server_url_' + String(indexList[i]));
            dataSet.server_port = this.loadValue('server_port_' + String(indexList[i]));
            dataSet.login_name = this.loadValue('login_name_' + String(indexList[i]));
            dataSet.login_passwd = this.loadValue('login_passwd_' + String(indexList[i]));
            dataSet.user_volume = this.loadValue('user_volume_' + String(indexList[i]));
            dataSet.user_away_after = this.loadValue('user_away_after_' + String(indexList[i]));
            dataSet.fake_mac_address = this.loadValue('fake_mac_address_' + String(indexList[i]));
            dataSet.user_status = this.loadValue('user_status_' + String(indexList[i]));
            dataSet.play_incoming_message_sound = this.loadValue('play_incoming_message_sound_' + String(indexList[i]));
            dataSet.play_incoming_chat_sound = this.loadValue('play_incoming_chat_sound_' + String(indexList[i]));
            dataSet.repeat_incoming_chat_sound = this.loadValue('repeat_incoming_chat_sound_' + String(indexList[i]));
            dataSet.language = this.loadValue('language_' + String(indexList[i]));

            this.storageData.push(dataSet);
        }
    }
};

/**
 * Get the data set for the given index
 * @param myIndex
 * @return {*}
 */
CommonStorageClass.prototype.getProfileByIndex = function(myIndex) {
    for (var i = 0; i < this.storageData.length; i++) {
        if (this.storageData[i].index == myIndex) {
            return this.storageData[i];
        }
    }
    return null;
};

/**
 * Save a data set to the local storage. This will create new data in the storage if the index of the data set
 * equals -1 or update existing data else.
 * @param dataSet
 */
CommonStorageClass.prototype.saveProfile = function(dataSet) {
    var newIndex = -1;
    var indexes = this.loadValue('indexes');
    var indexList = [];
    if (indexes != null) {
        indexList = indexes.split(',');
        //console.log('Splitting received data ' + indexes);
    } else {
        //console.log('No indexes saved yet, starting with an empty array');
        indexList = [];
    }
    if (dataSet.index == -1) {
        if (indexList.length != 0) {
            //console.log(indexList);
            //console.log(indexList.length);
            //console.log(indexList[indexList.length - 1]);
            newIndex = Number(indexList[indexList.length - 1]) + 1;
            //console.log('Calculate new index ' + String(newIndex));
        } else {
            newIndex = 1;
            //console.log('No indexes saved yet, starting with ' + String(newIndex));
        }
        //console.log(indexList);
        indexList.push(String(newIndex));
        //console.log(indexList);
        this.saveValue('indexes',indexList.join(','));
    } else {
        newIndex = dataSet.index;
        //console.log('Using existing index ' + String(newIndex));
    }
    this.saveValue('server_profile_' + String(newIndex),dataSet.server_profile);
    this.saveValue('server_protocol_' + String(newIndex), dataSet.server_protocol);
    this.saveValue('server_url_' + String(newIndex),dataSet.server_url);
    this.saveValue('server_port_' + String(newIndex),dataSet.server_port);
    this.saveValue('login_name_' + String(newIndex),dataSet.login_name);
    this.saveValue('login_passwd_' + String(newIndex),dataSet.login_passwd);
    this.saveValue('user_volume_' + String(newIndex),dataSet.user_volume);
    this.saveValue('user_away_after_' + String(newIndex), dataSet.user_away_after);
    this.saveValue('fake_mac_address_' + String(newIndex), dataSet.fake_mac_address);
    this.saveValue('user_status_' + String(newIndex), dataSet.user_status);
    this.saveValue('play_incoming_message_sound_' + String(newIndex), dataSet.play_incoming_message_sound);
    this.saveValue('play_incoming_chat_sound_' + String(newIndex), dataSet.play_incoming_chat_sound);
    this.saveValue('repeat_incoming_chat_sound_' + String(newIndex), dataSet.repeat_incoming_chat_sound);
    this.saveValue('language_' + String(newIndex), dataSet.language);

    this.loadProfileData();

    return newIndex;
};

/**
 * Delete the data set for the given index from the storage
 * @param myIndex
 */
CommonStorageClass.prototype.deleteProfile = function(myIndex) {
    var indexes = this.loadValue('indexes');
    if (indexes != null) {
        var indexList = indexes.split(',');
    }
    var newIndexList = [];
    for (var i=0; i<indexList.length; i++) {
        if (Number(indexList[i]) != Number(myIndex)) {
            //console.log(Number(indexList[i]));
            //console.log(Number(myIndex));
            newIndexList.push(indexList[i]);
        }
    }
    //console.log(newIndexList);
    this.saveValue('indexes',newIndexList.join(','));
    this.deleteKeyValuePair('server_profile_' + String(myIndex));
    this.deleteKeyValuePair('server_url_' + String(myIndex));
    this.deleteKeyValuePair('server_port_' + String(myIndex));
    this.deleteKeyValuePair('login_name_' + String(myIndex));
    this.deleteKeyValuePair('login_passwd_' + String(myIndex));

    this.loadProfileData();
};

/**
 * get the value to a given key
 * @param myKey         // the key for which the array shall be retrieved
 * @return {*}          // the value of the given key
 */
CommonStorageClass.prototype.loadValue = function(myKey) {
    return window.localStorage.getItem(this.localDbPrefix + myKey);
};

/**
 * Save a key value pair to the local storage, this will create a new pair if thze key doesn't exist
 * or overwrite the value if the key does already exist
 * @param myKey
 * @param myValue
 */
CommonStorageClass.prototype.saveValue = function(myKey, myValue) {
    window.localStorage.setItem(this.localDbPrefix + myKey, myValue);
    //console.log('Saved value ' + myValue + ' for key ' + myKey);
};

/**
 * Delete the key value pair for the given key from the storage
 *
 * @param myKey         // key for which the key value pair shall be deleted
 */
CommonStorageClass.prototype.deleteKeyValuePair = function(myKey) {
    window.localStorage.removeItem(this.localDbPrefix + myKey);
    //console.log('Deleted key value pair for key ' + myKey);
};

/**
 * Clear all data from the local storage
 */
CommonStorageClass.prototype.clearLocalStorage = function() {
    window.localStorage.clear();
    //console.log('Cleared configuration');
};