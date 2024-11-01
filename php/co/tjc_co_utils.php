<?php
namespace wcf_coolform;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function getRandomString($length = 10) {
    return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
}

function realEmpty($str) {
    if (!isset($str))
        return true;
    if (trim($str) === "") {
        return true;
    }
    return false;
}

function cleaninput($str) {
    if (empty($str)) {
        return $str;
    }
    return sanitize_text_field($str);
}

function endsWith($haystack, $needle) {
    // search forward starting from end minus needle length characters
    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
}

/**
 * returns true if haystack starts with needle.
 * 
 * @param type $haystack
 * @param type $needle
 * @return type
 */
function startsWith($haystack, $needle) {
    // search backwards starting from haystack length characters from the end
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
}

function cleanSpecialChars($formname) {
    $string = str_replace(' ', '-', $formname); // Replaces all spaces with hyphens.
    return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
}

function sep($key) {
    //return $_GET[$key];
    if (array_key_exists($key, $_POST)) {
        return $_POST[$key];
    }
    return null;
}

function sepget($key) {
    //return $_GET[$key];
    $ret = "";
    if (array_key_exists($key, $_GET)) {
        $ret = $_GET[$key]; //wcfpost($key);
    } else {
        $ret = null; //wcfpost($key);
    }
    return $ret;
}

function wcfget($key) {
    //return sepget($key);
    return saveget($key);
    //return $_GET[$key];
    //return cleaninput(sepget($key));
}

function saveget($key) {
    //return $_GET[$key];
    return cleaninput(sepget($key));
}

function savepost($key) {
    //return $_POST[$key];
    return cleaninput(sep($key));
}

function wcfpost($key) {
    return cleaninput(sep($key));
    //return $_POST[$key];
    //return sep($key);
    //return cleaninput($_POST[$key]);
}

/**
 * helper to get the current time in millis.
 * 
 * @return type
 */
function currentTimeInMillis() {
    $microtime = microtime();
    $comps = explode(' ', $microtime);
    // Note: Using a string here to prevent loss of precision
    // in case of "overflow" (PHP converts it to a double)
    return sprintf('%d%03d', $comps[1], $comps[0] * 1000);
}
