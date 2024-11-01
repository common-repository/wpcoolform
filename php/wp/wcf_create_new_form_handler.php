<?php
namespace wcf_coolform;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * called on form upload.
 * 
 * 
 */

/**
 * Neues Formular ajax ...
 */
function create_form_action_callback() {
    $handler = new WCFNewFormFormHandler();
    $handler->createNewForm();
}

/**
 * deletes a form 
 */
function delete_form_action_callback() {
    $handler = new WCFNewFormFormHandler();
    $handler->deleteForm();
    echo "index.php";
    wp_die();
}

/**
 * kapselt den anlegeprozess.
 * 
 */
class WCFNewFormFormHandler {

    function saveWordfile($destination) {
        if (array_key_exists('doc_file', $_FILES)) {
            $userfilename = $_FILES['doc_file']['name'];
            if (endsWith($userfilename, ".docx")) {
                $type = "docx";
            } else if (endsWith($userfilename, ".odt")) {
                $type = "odt";
            } else {
                $type = "false";
            }
            if ($type === "false") {
                return 'false';
            } else {
                move_uploaded_file($_FILES['doc_file']['tmp_name'], $destination);
            }
            return $type;
        }
        return 'false';
    }

    /**
     * ajax callback 
     * action, form_id und doc_file
     * 
     */
    function uploadNewForm() {
        $form_id = wcfpost('form_id');
        $wordfile = getPathWordfile($form_id);
        $type = $this->saveWordfile($wordfile);
        if ($type === 'false') {
            echo tr('error_upload_file');
            wp_die();
        } else {
            echo tr('message_success_saving_file');
        }
        wp_die();
    }

    function createNewForm() {
        $formname = wcfpost('formname');
        $redirect = wcfpost('redirect');
        // processing:
        $id = $this->createNewId();
        $ini = new WcfIniFilesHandler($id);
        $formFolder = getPathFormFolder($id);
        $success = $this->createDir($formFolder);
        if (!$success) {
            echo "false";
            wp_die();
        }
        $wordfile = getPathWordfile($id);
        $type = $this->saveWordfile($wordfile);
        if ($type === 'false') {
            // no word file .. no problem do nothing
            //echo "false";
            //wp_die();
        } else if (WP_CF_PRO) {
            $zippi = new WcfZipHandler;
            $matches = $zippi->getVars($wordfile);
            // ein test
            $xml = new WcfXmlHandler($ini->getXMLIniFile());
            $fields = $zippi->getVarsAsFields($wordfile);
            if ($type == "docx") {
                $imageFields = $zippi->getImageVarsAsFields($wordfile);
                foreach ($imageFields as $f) {
                    array_push($fields, $f);
                }
            }
            $xml->setFields($fields);
            $xml->writeFields();
            // test ende


            // kann irgendwann geloescht werden.
            $imagearray = array();
            if ($type == "docx") {
                $imagearray = $zippi->getImageVars($wordfile);
            }
            // bilder
            $i = 0;
            $content = "";
            foreach ($imagearray as $iname => $inumber) {
                $line = 'column' . $i . "=" . $iname . '=' . $inumber . "\n";
                $content .= $line;
                $i++;
            }
            file_put_contents($ini->getIniFileImages(), $content);
        }
        // save in db:
        $th = new WCFSQLHandler();
        $th->insertIntoFormularTable($formname, $id, "", 0, "", $type, $redirect, "", "");
        // und ende ...
        //echo 'http://localhost/wordpress/wp-admin/admin.php?page=set_' . $id;
        echo admin_url('admin.php') . "?page=set_" . $id;
//        echo SETTINGS_URL;
        wp_die();
    }

    function deleteForm() {
        global $wpdb;
        $formId = wcfpost('formdata');

        // cleanup:
        // 1. datenbank loeschen
        $handler = new WCFSQLHandler();
        $handler->deleteFormular($formId);


        // verz loeschen
        //$dir = "wp-admin/" .$this->getFormFolder($formId);
        //rmdir($dir);
        // eintraege loeschen
    }

    /**
     * creates a directory and returns false if that fails.
     * 
     * @param type $dir
     * @return boolean
     */
    function createDir($dir) {
        wp_mkdir_p($dir);
        if (!file_exists($dir)) {
            return false;
        }
        return true;
    }

    /**
     * creates a new random id for a new form.
     * 
     * @return type
     */
    private function createNewId() {
        $ctim = $this->currentTimeInMillis();
        return $ctim . rand(10, 99);
    }

    /**
     * helper to get the current time in millis.
     * 
     * @return type
     */
    private function currentTimeInMillis() {
        $microtime = microtime();
        $comps = explode(' ', $microtime);
        // Note: Using a string here to prevent loss of precision
        // in case of "overflow" (PHP converts it to a double)
        return sprintf('%d%03d', $comps[1], $comps[0] * 1000);
    }

}
