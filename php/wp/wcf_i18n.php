<?php
namespace wcf_coolform;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
function isGerman() {
    return false;
}

function tr($str) {
    foreach (file(transFile()) as $line) {
        if (startsWith($line, '#')) {
            continue;
        }
        list($key, $val) = explode("=", trim($line) . "=");
        if ($key === $str) {
            return trim($val);
        }
    }
    return $str;
}

function transFile() {
    if (isGerman()) {
        return TRANS_DE;
    }
    return TRANS_EN;
}

// template files:


function getTemplateEnding() {
    return isGerman() ? "_de_DE" : "_en_EN";
}

function getPathWelcomePage() {
    return WCF_PATH_HTML . "welcome_page_template" . getTemplateEnding();
}

function getPathSettingsPage() {
    return WCF_PATH_HTML . "settings_page_template" . getTemplateEnding();
}

function getPathDataPage() {
    return WCF_PATH_HTML . "data_page_template" . getTemplateEnding();
}

function getPathNewFormPage() {
    return WCF_PATH_HTML . "new_form_template" . getTemplateEnding();
}

function getPathFormPageHeaderTemplate() {
    return WCF_PATH_HTML . "form_page_header_template" . getTemplateEnding();
}

function getPathFormPageFooterTemplate() {
    return WCF_PATH_HTML . "form_page_footer_template" . getTemplateEnding();
}

function getPathNewFormPostHeaderTemplate() {
    return WCF_PATH_HTML . "new_form_post_header_template" . getTemplateEnding();
}

function getPathNewFormPostFooterTemplate() {
    return WCF_PATH_HTML . "new_form_post_footer_template" . getTemplateEnding();
}

function getPathFAQ() {
    return WCF_PATH_HTML . "faq_template" . getTemplateEnding();
}

function getPathSettingsHeader() {
    return WCF_PATH_HTML . "settings_header_template" . getTemplateEnding();
}

function getPathSettingsFooter() {
    return WCF_PATH_HTML . "settings_footer_template" . getTemplateEnding();
}

function getPathFormSettingsHeader() {
    return WCF_PATH_HTML . "form_settings_header_template" . getTemplateEnding();
}

function getPathFormSettingsFooter() {
    return WCF_PATH_HTML . "form_settings_footer_template" . getTemplateEnding();
}
