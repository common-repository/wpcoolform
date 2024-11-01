<?php
namespace wcf_coolform;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function getUrlFromPostId($postId) {
    return get_site_url() . "/index.php?page_id=" . $postId;
}


function check_referrer() {
    if (!isset($_SERVER['HTTP_REFERER']) || $_SERVER['HTTP_REFERER'] == '') {
        wp_die(__('Please do not access this file directly.'));
    }
}




function getPathLogfile() {
    return getPathWcfForms() . "wcf_log.log";
}

function wcflog($str) {
    file_put_contents(getPathLogfile(), $str . "\n", FILE_APPEND);
}

function getPathWcfForms() {
    $tmpDir = get_option('upload_dir', "wp-admin/uploads/wpcoolform/");
    if (startsWith($tmpDir, "/")) {
        return $tmpDir;
    }
    return ABSPATH . $tmpDir;
}

function getPathFormFolder($formId) {
    return getPathWcfForms() . $formId;
}

function getPathWordfile($formId) {
    return getPathWcfForms() . $formId . "/wordfile";
}

function getPathDownloads() {
    $tmpDir = get_option('tmp_dir', 'wp-admin/downloads/WpCoolForm');
    if (startsWith($tmpDir, "/")) {
        return $tmpDir;
    }
    return ABSPATH . $tmpDir;
}

function createResultPath() {
    $path = getPathDownloads();
    if (!file_exists($path)) {
        mkdir($path, 0700, true);
    }
    return $path . "/" . generateRandomString(20);
}

function getPathFormImageFolder($id) {
    // $upload_dir = wp_upload_dir();
    $path = getPathWcfForms() . $id . "/images";
    if (!file_exists($path)) {
        mkdir($path, 0700, true);
    }
    return $path;
}
