<?php
/****************************************************************************************
 * LiveZilla index.php
 *
 * Copyright 2013 LiveZilla GmbH
 * All rights reserved.
 * LiveZilla is a registered trademark.
 *
 ***************************************************************************************/

$langFileLocation = '.';
$LZLANG = Array();

if (isset($_GET['g_language'])) {
    $language = ($_GET['g_language'] != '') ? $_GET['g_language'] : 'en';
    require ($langFileLocation . '/langmobileorig.php');
    $LZLANGEN = $LZLANG;
    if (file_exists($langFileLocation . '/langmobile' . $language . '.php')) {
        require ($langFileLocation . '/langmobile' . $language . '.php');
    }

    $stringArray = Array();
    foreach ($LZLANGEN as $key => $value) {
        if (preg_match('/^mobile_/', $key) != 0) {
            if (array_key_exists($key, $LZLANG) && $LZLANG[$key] != null && $LZLANG[$key] != '') {
                array_push($stringArray, array('orig' => $value, $language => $LZLANG[$key]));
                //error_log(print_r(array('en' => $value, $language => $LZLANG[$key]), true), 3, '/tmp/lzm-translation.log');
            } else {
                array_push($stringArray, array('orig' => $value, $language => $value));
            }
        }

    }

    exit(json_encode($stringArray));

} elseif (isset($_GET['g_available'])) {
    $availableLanguages = array();
    foreach (scandir($langFileLocation) as $aFile) {
        if (preg_match('/^lang\.mobile\.[a-zA-Z]{2}\.php$/', $aFile) != 0) {
            $fileNameParts = explode('.', $aFile);
            array_push($availableLanguages, $fileNameParts[2]);
        }
    }
    exit(json_encode($availableLanguages));

} /*elseif (!empty($_POST['p_language']) && !empty($_POST['p_translations'])) {
    $language = $_POST['p_language'];
    $stringObjects = json_decode($_POST['p_translations']);
    //error_log('Saving translations for : '.$language."\n");
    //error_log('Translations transmitted :'."\n".print_r($stringObjects, true)."\n");

    require ($langFileLocation . '/langmobileorig.php');
    $LZLANGEN = $LZLANG;
    if (file_exists($langFileLocation . '/langmobile' . $language . '.php')) {
        require ($langFileLocation . '/langmobile' . $language . '.php');
    } else {
        $LZLANG = Array();
    }

    foreach ($stringObjects as $anObject) {
        $thisKey = array_search($anObject->en, $LZLANGEN);
        if ($thisKey) {
            //error_log($thisKey.' - '.$anObject->$language);
            $LZLANG[$thisKey] = $anObject->$language;
        }
    }

    require_once './commonTranslationFunctions.php';

    addMissingStringsToOrigLangFile('langmobile'.$language.'.php', $LZLANG, Array());
    exit(json_encode(true));

} */ else {
    die(json_encode('No get or post parameter given'));
}
?>