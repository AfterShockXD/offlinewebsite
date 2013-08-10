<?php
/****************************************************************************************
 * LiveZilla searchMissingTranslations.php
 *
 * Copyright 2013 LiveZilla GmbH
 * All rights reserved.
 * LiveZilla is a registered trademark.
 *
 ***************************************************************************************/

?>
<!DOCTYPE HTML>
<html>
<head>
    <title>
        Livezilla Mobile
    </title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
</head>
<body>
<p>
<?php
require_once './commonTranslationFunctions.php';
// When called with get parameter 'parse=1':
// Search the livezilla mobile client's source code for strings
// using the t() function which have no according strings in the translation files
// When called with get parameter 'sync=1':
// Add those entries to the localized files, that are missing from them but do
// exist in the orig file

require 'langmobileorig.php';

if (isset($_GET['parse']) && $_GET['parse'] == 1) {
    $sourceCodeFiles = getAllSourceCodeFiles();
    $translationStrings = array();

    foreach ($sourceCodeFiles as $aFile) {
        foreach (parseSourceCodeFile($aFile) as $aString) {
            if (!in_array($aString, $translationStrings)) {
                array_push($translationStrings, $aString);
            }
        }
    }

    $missingTranslationStrings = findMissingStrings($translationStrings, $LZLANG);
    $supernumeraryTranslationStrings = findSupernumeraryStrings($translationStrings, $LZLANG);

    addMissingStringsToOrigLangFile('langmobileorig.php', $missingTranslationStrings, $LZLANG);
    echo "Missing strings added.<br><br>";
} else {
    $missingTranslationStrings = array();
}

if (isset($_GET['sync']) && $_GET['sync'] == 1) {
    syncTranslationFiles($missingTranslationStrings, $LZLANG);
    echo "Localization files synced.<br><br>";
}

if ((!isset($_GET['parse']) || $_GET['parse'] != 1) && (!isset($_GET['sync']) || $_GET['parse'] != 1)) {
    echo "Parameters invalid or no parameter given";
}

?>
</p>
</body>
</html>
