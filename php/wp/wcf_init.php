<?php
namespace wcf_coolform;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
// download:

function restrict_wcf_downloads() {
    global $wpdb;
    $formId = wcfget('download_form_id');
    $newFormPage = wcfget('create_new_form_page_form_id');
    $formData = wcfget('download_form_data_id');
    $concreteForm = wcfget('download_concrete_form');

    if (isset($formId)) {
        downloadFormPage($formId);
        return;
    } else
    if (isset($newFormPage)) {
        $generator = new WpFormPageCreator();
        $postId = $generator->generatePage($newFormPage);
        wp_redirect(getUrlFromPostId($postId));
    } else
    if (isset($formData)) {
        $go = new WcfFormActions();
        $go->downloadFormDataAsCSV($formData);
        return;
    } else

    if (isset($concreteForm) && WP_CF_PRO) {
        $docgen = new WcfFormDocGenerator();
        $resultPath = $docgen->createConcreteForm($concreteForm);
        $go = new WcfFormActions();
        $go->downloadConcreteForm($resultPath, getFormTypeByUserFormId($concreteForm));
        return;
    }
}

function downloadFormPage($formId) {
    if (empty($formId)) {
        return;
    }
    $formId = filter_var($formId, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
    $handler = new WCFSQLHandler();
    $form = $handler->getFormular($formId);
    // der Pfad:
    $path = getPathWcfForms() . $formId . "/wordfile";
    //echo $path;
    $action = new WcfFormActions();
    $action->downloadConcreteForm($path, $form[0]['type']);
    exit;
}

/**
 * returns the form type of a concrete form (docx or odt.
 * @param type $formId
 */
function getFormTypeByFormId($formId) {
    $sql = new WCFSQLHandler();
    $form = $sql->getFormular($formId);
    $type = $form[0]['type'];
}

/**
 * returns the form type by the concrete user form id.
 * @param type $id
 * @return type
 */
function getFormTypeByUserFormId($id) {
    $sql = new WCFSQLHandler();
    $formData = $sql->getOneFormData($id);
    $formId = $formData[0]['form_id'];
    $form = $sql->getFormular($formId);
    return $form[0]['type'];
}




function readHtmlFormInputs($formId) {
    $input = array();
    $ini = new WcfIniFilesHandler($formId);
    $xml = new WcfXmlHandler($ini->getXMLIniFile());
    $fields = $xml->getFields();
    foreach ($fields as $field) {
        $col = "column" . $field->getId();
        $name = $field->getName();
        $marker = $formId . "_" . $col;
        if (isset($_POST[$marker])) {
            $input[$col] = savepost($marker);
        }
    }
    $input['custom_id'] = getRandomString();
    return $input;
}

function downloadFile($formId) {
    $formId = filter_var($formId, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
    $handler = new WCFSQLHandler();
    $rows = $handler->getFormData($formId, "*");
    header('Content-Description: File Transfer');
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename=' . "formdata.csv");
    header('Content-Transfer-Encoding: utf-8');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    ob_clean();
    flush();
    $first = true;
    foreach ($rows as $row) {
        if ($first) {
            echo implode(";", array_keys($row));
            echo "\n";
            $first = false;
        }
        echo implode(";", array_values($row));
        echo "\n";
    }
}

/**
 * 
 * @param type $receiver
 * @param type $forminputId
 */
function sendFormData($receiver, $concreteFormId, $formId) {
    // 1. create form
    $docgen = new WcfFormDocGenerator();
    $resultPath = $docgen->createConcreteForm($concreteFormId);
    // 2. get subject and body
    $sqlh = new WCFSQLHandler();
    $rows = $sqlh->getFormular($formId);
    $subject = $rows[0]['subject'];
    $body = $rows[0]['body'];
    // 3. replace placeholders
    $replaces = $docgen->getReplaces($concreteFormId);
    foreach ($replaces as $key => $value) {
        $subject = str_replace("[wcf." . $key . "]", $value, $subject);
        $body = str_replace("[wcf." . $key . "]", $value, $body);
    }
    // 4. send email
    $attachments = array($resultPath);
    wcflog("this is what i send to $receiver:");
    wcflog("the subject: $subject");
    wcflog("the body: $body");
    wp_mail($receiver, $subject, $body, $headers = '', $attachments);
}

