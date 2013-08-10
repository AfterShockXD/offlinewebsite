/****************************************************************************************
 * LiveZilla CommonTranslationClass.js
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
function CommonTranslationClass(protocol, url, runningFromApp, language) {
    this.translationArray = [];
    this.manageTranslationArray = [];
    this.protocol = protocol;
    this.url = url;
    this.availableLanguages = [];
    this.language = 'en';
    if (typeof language != 'undefined' && language != '') {
        this.language = language
    } else if (typeof navigator.language != 'undefined') {
        this.language = navigator.language;
    } else if (typeof navigator.userLanguage != 'undefined') {
        this.language = navigator.userLanguage;
    }
    if (this.language.indexOf('-') != -1) {
        this.language = this.language.split('-')[0];
    } else if (this.language.indexOf('_') != -1) {
        this.language = this.language.split('_')[0];
    }
    this.fillTranslationArray(runningFromApp, this.language, 'default');
}

CommonTranslationClass.prototype.translate = function(translateString, placeholderArray) {
    var translatedString = translateString;
    var notInArray = true;
    for (var stringIndex=0; stringIndex<this.translationArray.length; stringIndex++) {
        if (this.translationArray[stringIndex]['orig'] == translateString) {
            if (this.translationArray[stringIndex][this.language] != null)
                translatedString =  this.translationArray[stringIndex][this.language];
            notInArray = false;
            break;
        }
    }

    if (typeof placeholderArray != 'undefined') {
        for (var i=0; i<placeholderArray.length; i++) {
            translatedString = this.stringReplace(translatedString, placeholderArray[i][0], placeholderArray[i][1]);
        }
    }

    return translatedString;
};

CommonTranslationClass.prototype.stringReplace = function(myString, placeholder, replacement) {
    return myString.replace(placeholder, replacement);
} ;

CommonTranslationClass.prototype.fillTranslationArray = function(fromApp, language, type) {
    var thisClass = this;
    if (typeof language == 'undefined' || language == '') {
        language = thisClass.language;
    }

    if (!fromApp) {
    $.ajax({
        type: "GET",
        url: thisClass.protocol + thisClass.url + '/mobile/php/translation/index.php',
        data: {
            g_language: language
        },
        success: function (data) {
            if (typeof type == 'undefined' || type != 'manage') {
                thisClass.translationArray = data;
            } else {
                thisClass.manageTranslationArray = data;
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log('Status-Text : ' + textStatus);
            console.log(jqXHR);
            console.log('Error-Text : ' + errorThrown);
        },
        dataType: 'json'
    });
    } else {
        thisClass.translationArray = [
            {"orig":"No profile selected","de":"Kein Profil ausgewählt"},
            {"orig":"Profiles","de":"Profile"},
            {"orig":"New profile","de":"Profil anlegen"},
            {"orig":"Edit profile","de":"Profil ändern"},
            {"orig":"Delete profile","de":"Profil löschen"},
            {"orig":"Profile name","de":"Profilname"},
            {"orig":"Server profiles","de":"Server-Profile"},
            {"orig":"Server Protocol","de":"Server-Protokoll"},
            {"orig":"Server Url","de":"Server-Url"},
            {"orig":"Port","de":"Port"},
            {"orig":"Delete this server profile?","de":"Dieses Server-Profil löschen?"},
            {"orig":"Save this new profile?","de":"Das neue Profil speichern?"},
            {"orig":"Save changes?","de":"Änderungen speichern?"},
            {"orig":"Leave profile configuration?","de":"Die Profil-Einstellungen verlassen?"},
            {"orig":"This will discard unsaved configuration changes!","de":"Dies verwirft ungesicherte Änderungen."},
            {"orig":"Username","de":"Anmeldename"},
            {"orig":"Password","de":"Passwort"},
            {"orig":"Username:","de":"Anmeldename:"},
            {"orig":"Password:","de":"Passwort:"},
            {"orig":"Save login data","de":"Anmelde-Daten speichern"},
            {"orig":"Log in","de":"Anmelden"},
            {"orig":"Back","de":"Zurück"},
            {"orig":"available","de":"verfügbar"},
            {"orig":"busy","de":"beschäftigt"},
            {"orig":"offline","de":"abgemeldet"},
            {"orig":"away","de":"abwesend"},
            {"orig":"Save profile","de":"Profil speichern"},
            {"orig":"The server did not respond for more then <!--number_of_seconds--> seconds.","de":"Der Server antwortet nicht seit mehr als <!--number_of_seconds--> Sekunden."},
            {"orig":"The server returned an error","de":"Der Server gab eine Fehlermeldung zurück"},
            {"orig":"Error code : <!--http_error-->","de":"Fehler-Code: <!--http_error-->"},
            {"orig":"Error text : <!--http_error_text-->","de":"Fehler-Text: <!--http_error_text-->"},
            {"orig":"The operator <!--op_login_name--> is already logged in!","de":"Der Operator <!--op_login_name--> ist bereits angemeldet."},
            {"orig":"Wrong username or password!","de":"Benutzername oder Passwort falsch."},
            {"orig":"Do you want to log off the other instance?","de":"Möchten Sie sich trotzdem anmelden und den anderen Benutzer abmelden?"},
            {"orig":"This server requires secure connection (SSL). Please activate HTTPS in the server profile and try again.","de":"Dieser Server erlaubt keine unverschlüsselten Verbindungen. Bitte aktivieren Sie SSL (HTTPS) im Serverprofil."},
            {"orig":"Session timed out!","de":"Die Session ist abgelaufen."},
            {"orig":"You've been logged off by another operator!", "de":"Sie wurden durch einen anderen Operator abgemeldet."},
            {"orig":"You have to change your password!","de":"Sie müssen Ihr Passwort ändern."},
            {"orig":"You are not an administrator!","de":"Sie sind kein Administrator."},
            {"orig":"This LiveZilla server has been deactivated by the administrator.","de":"Dieser LiveZilla-Server wurde vom Administrator deaktiviert."},
            {"orig":"If you are the administrator, please activate this server under LiveZilla Server Admin -> Server Configuration -> Server.","de":"Wenn Sie der Administrator sind, aktivieren Sie diesen Server bitte unter LiveZilla Server Admin -> Server Konfiguration -> Server."},
            {"orig":"There are problems with the database connection!","de":"Es bestehen Probleme mit der Datenbank-Verbindung."},
            {"orig":"Available","de":"Verfügbar"},
            {"orig":"Busy","de":"Beschäftigt"},
            {"orig":"Away","de":"Abwesend"},
            {"orig":"Cancel","de":"Abbrechen"}
        ];
    }
};

CommonTranslationClass.prototype.listAvailableLanguages = function() {
    var thisClass = this;
    var availableLanguages = [];
    var foo = 0;
    $.ajax({
        type: "GET",
        url: thisClass.protocol + thisClass.url + '/mobile/php/translation/index.php',
        data: {
            g_available: 'list'
        },
        success: function (data) {
            thisClass.availableLanguages = data;
            //console.log(data);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log('Status-Text : ' + textStatus);
            console.log(jqXHR);
            console.log('Error-Text : ' + errorThrown);
        },
        dataType: 'json'
    });
};

CommonTranslationClass.prototype.saveTranslations = function(language,stringObjects) {
    var thisClass = this;
    $.ajax({
        type: "POST",
        url: thisClass.protocol + thisClass.url + '/mobile/php/translation/index.php',
        data: {
            p_language: language,
            p_translations: JSON.stringify(stringObjects)
        },
        success: function (data) {
            if (data)
                thisClass.fillTranslationArray(false);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log('Status-Text : ' + textStatus);
            console.log(jqXHR);
            console.log('Error-Text : ' + errorThrown);
        },
        dataType: 'text'
    });
};