<?php
/****************************************************************************************
 * LiveZilla commonTranslationFunctions.php
 *
 * Copyright 2013 LiveZilla GmbH
 * All rights reserved.
 * LiveZilla is a registered trademark.
 *
 ***************************************************************************************/

function syncTranslationFiles($missingTranslationStrings, $LZLANG_ORIG) {
    $origStrings = array_merge($LZLANG_ORIG,$missingTranslationStrings);
    $LZLANG = array();
    $langFileDirectory = dirname(__FILE__);
    $filenameBase = 'langmobile';
    $langFiles = scandir($langFileDirectory);
    foreach ($langFiles as $aFile) {
        $newStrings = array();
        $existingStrings = array();
        if (preg_match('/^' . $filenameBase . '([a-z]{2}|[a-z]{3})\.php$/', $aFile) != 0) {
            require $aFile;
            foreach ($origStrings as $key => $value) {
                if (!array_key_exists($key, $LZLANG) || preg_match('/mobile_automatically_added_entry/', $key) != 0) {
                    $newStrings[$key] = '';
                }
            }
            foreach ($LZLANG as $key => $value) {
                if (preg_match('/mobile_automatically_added_entry/', $key) == 0) {
                    $existingStrings[$key] = $value;
                }
            }
            addMissingStringsToOrigLangFile($aFile, $newStrings, $existingStrings);
        }
    }

}

function addMissingStringsToOrigLangFile($filename, $missingTranslationStrings, $LZLANG) {
    $langFileDirectory = dirname(__FILE__);
    $origLangFileHeader = '<?php' . "\n" .
        "\n" .
        '/*********************************************************************************' . "\n" .
        '/* LiveZilla ' . $filename . "\n" .
        '/* ' . "\n" .
        '/* Copyright 2013 LiveZilla GmbH' . "\n" .
        '/* All rights reserved.' . "\n" .
        '/* LiveZilla is a registered trademark.' . "\n" .
        '/*' . "\n" .
        '/* Please report errors here: http://www.livezilla.net/forum/' . "\n" .
        '/* DO NOT REMOVE/ALTER <!--placeholders-->' . "\n" .
        '/* ' . "\n" .
        '/********************************************************************************/' . "\n" .
        "\n" .
        '$LZLANG = Array();' . "\n" .
        "\n";
    $origLangFileSeparator = '//' . "\n" .
        '// Strings added by searchMissingTranslations script' . "\n" .
        '//' . "\n";
    $origLangFileFooter = "\n" .
        '?>' . "\n" .
        "\n";

    if (is_writable($langFileDirectory . '/' . $filename)) {
        $handle = fopen($langFileDirectory.'/'.$filename, "w");
        fwrite($handle, $origLangFileHeader);
        foreach ($LZLANG as $key => $value) {
            if (preg_match('/mobile_automatically_added_entry/', $key) == 0) {
                fwrite($handle, '$LZLANG["' . $key . '"] = \'' . str_replace("'","\'",$value) . "';\n");
                //error_log('$LZLANG["' . $key . '"] = \'' . addslashes($value) . "';\n",3,'/tmp/lzm-translation.log');
            } else {
                array_unshift($missingTranslationStrings, $value);
            }
        }
        fwrite($handle, $origLangFileSeparator);
        $counter = 0;
        foreach ($missingTranslationStrings as $key => $value) {
            if (preg_match('/mobile_automatically_added_entry/', $key) != 0
                || preg_match('/^[0-9]*$/', $key) != 0) {
                fwrite($handle, '$LZLANG["mobile_automatically_added_entry_' . $counter . '"] = \'' . str_replace("'","\'",$value) . "';\n");
                //error_log('$LZLANG["mobile_automatically_added_entry_' . $counter . '"] = \'' .addslashes($value) . "';\n",3,'/tmp/lzm-translation.log');
            } else {
                fwrite($handle, '$LZLANG["' . $key . '"] = \'' . str_replace("'","\'",$value) . "';\n");
                //error_log('$LZLANG["' . $key . '"] = \'' .addslashes($value) . "';\n",3,'/tmp/lzm-translation.log');
            }
            $counter++;
        }
        fwrite($handle, $origLangFileFooter);
        fclose($handle);
    } else {
        echo "File not writable";
    }
}

function findMissingStrings($translationStrings, $LZLANG)
{
    $missingTranslationStrings = array();
    $counter = 0;
    foreach ($translationStrings as $aString) {
        if (!in_array($aString, $LZLANG)) {
            $missingTranslationStrings['mobile_automatically_added_entry_' . $counter] = $aString;
            $counter++;
        }
    }
    return $missingTranslationStrings;
}

function findSupernumeraryStrings($translationStrings, $LZLANG)
{
    $supernumeraryTranslationStrings = array();
    $counter = 0;
    foreach ($LZLANG as $aString) {
        if (!in_array($aString, $translationStrings)) {
            array_push($supernumeraryTranslationStrings, $aString);
            $counter++;
        }
    }
    return $supernumeraryTranslationStrings;
}


function getAllSourceCodeFiles()
{
    $currentDir = dirname(__FILE__);
    $sourceCodeDirs = array($currentDir . '/../../js/lzm/', $currentDir . '/../../js/lzm/classes/');
    $sourceCodeFiles = array();

    for ($i = 0; $i < count($sourceCodeDirs); $i++) {
        $tmpSourceCodeFiles = scandir($sourceCodeDirs[$i]);
        foreach ($tmpSourceCodeFiles as $aFile) {
            if (preg_match('/\.js$/', $aFile) != 0) {
                array_push($sourceCodeFiles, $sourceCodeDirs[$i] . $aFile);
            }
        }
    }
    return $sourceCodeFiles;
}

function parseSourceCodeFile($aFile)
{
    $fileContents = file_get_contents($aFile);
    preg_match_all('/(?<![a-zA-Z])t\(\'.*?\'.*?\)/s', $fileContents, $foundMatches);

    $translationStrings = array();
    foreach ($foundMatches[0] as $aString) {
        $tmpArray = explode("'", $aString);
        $tmpString = $tmpArray[1];
        array_push($translationStrings, $tmpString);
    }

    return $translationStrings;
}
?>
