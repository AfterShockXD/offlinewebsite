<?php
/****************************************************************************************
 * LiveZilla functions.php
 *
 * Copyright 2013 LiveZilla GmbH
 * All rights reserved.
 * LiveZilla is a registered trademark.
 *
 ***************************************************************************************/

if (empty($LZM_URL))
    $LZM_URL = '/livezilla5/mobile';
if (empty($LZM_PATH))
    $LZM_PATH = '/var/www/html/livezilla5/mobile';
$LZM_IMAGE_FOLDER = $LZM_URL.'/img/';

require $LZM_PATH.'/php/Mobile_Detect/Mobile_Detect.php';

function getMobileInformation() {
    $detect = new Mobile_Detect();

    $isMobile = "false";
    $isTablet = "false";
    $mobileOS = '';
    if ($detect->isMobile()) {
        $isMobile = "true";
        if ($detect->isTablet()) {
            $isTablet = "true";
        }
        if ($detect->isiOs()) {
            $mobileOS = 'iOS';
        } elseif ($detect->isAndroidOS()) {
            $mobileOS = 'Android';
        } else {
            $mobileOS = 'Other mobile OS';
        }
    }
    return Array('isMobile' => $isMobile, 'isTablet' => $isTablet, 'mobileOS' => $mobileOS);
}

function readHtmlTemplate($fileName) {
    global $LZM_IMAGE_FOLDER;
    global $LZM_PATH;
    $fileContents = file_get_contents($LZM_PATH.'/templates/'.$fileName);
    return str_replace(
        array('./images/',"\n","\r","\t","margin-top:6px;", 'cellpadding="4"'),
        array($LZM_IMAGE_FOLDER,'','','','margin-top:2px;', 'style="margin-left: 14px"'),
        $fileContents
    );
}

?>