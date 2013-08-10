/****************************************************************************************
 * LiveZilla CommonConfigClass.js
 *
 * Copyright 2013 LiveZilla GmbH
 * All rights reserved.
 * LiveZilla is a registered trademark.
 *
 ***************************************************************************************/

function CommonConfigClass() {
    this.lz_version = '5.0.1.0'; // version of the lz client for compatibility reasons with the server
    this.lz_reload_interval = 5000; // time between polling the server in miliseconds
    this.lz_user_states = [
        {index: 0, text:'Available',icon:'img/lz_online.png'},
        {index: 1, text:'Busy',icon:'img/lz_busy.png'},
        {index: 2, text:'Offline',icon:'img/lz_offline.png'},
        {index: 3, text:'Away',icon:'img/lz_away.png'}
    ];
    this.lz_server_protocols = [{name: 'http://', port: 80},{name:'https://', port: 443}]; // server protocols

    this.largeDisplayThreshold = 1000000;
    this.smallDisplayThreshold = 1000000;
    this.pollTimeout = 20000;
    this.noAnswerTimeBeforeLogout = 60000;
}