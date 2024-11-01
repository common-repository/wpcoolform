<?php
namespace wcf_coolform;

require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
global $wpdb;

class WCFSQLHandler {

    function onActivate() {
        $handler = new WCFSQLHandler();
        $handler->createWcfSessionsTable();
        $handler->createFormularTable();
        $handler->createFormDataTable();
        $handler->createFormDataImageTable();
        $handler->createWcfKeyValueTable();
    }
    
    
    function onUninstall(){
         $handler = new WCFSQLHandler();
         $handler->dropFormularDataImageTable();
         $handler->dropFormularDataTable();
         $handler->dropFormularTable();
         $handler->dropKeyValueTable();
         $handler->dropWcfSessionsTable();
    }

    function createWcfSessionsTable() {
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        global $wpdb;
        $tablename = $this->getWcfSessionsTableName();
        $create = "create table IF NOT EXISTS " . $tablename . " (";
        $create = $create . "`id` int(20) NOT NULL AUTO_INCREMENT,";
        $create = $create . "`ts` DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,";
        $create = $create . "`key` text,";
        $create = $create . "`value` text,";
        $create = $create . "`cookie` text,";
        $create = $create . "`last_ok` text,";
        $create = $create . "PRIMARY KEY (id)";
        $create = $create . ");";
        //db_delta($create);
        $results = $wpdb->query($create);
    }

    function createWcfKeyValueTable() {
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        global $wpdb;
        $tablename = $this->getWcfKeyValuesTableName();
        $create = "create table IF NOT EXISTS " . $tablename . " (";
        $create = $create . "`id` int(20) NOT NULL AUTO_INCREMENT,";
        $create = $create . "`key` text,";
        $create = $create . "`value` text,";
        $create = $create . "`form_id` text,";
        $create = $create . "PRIMARY KEY (id)";
        $create = $create . ");";
        //db_delta($create);
        $results = $wpdb->query($create);
    }

    function createFormularTable() {
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        global $wpdb;
        $tablename = $this->getWcfFormTableName();
        $create = "create table IF NOT EXISTS " . $tablename . " (";
        $create = $create . "`id` int(20) NOT NULL AUTO_INCREMENT,";
        $create = $create . "`ts` DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,";
        $create = $create . "`form_name` text,";
        $create = $create . "`form_id` text NOT NULL,";
        $create = $create . "`receiver_email` text,";
        $create = $create . "`type` text,";
        $create = $create . "`redirect` text,";
        $create = $create . "`subject` text,";
        $create = $create . "`body` text,";
        $create = $create . "`use_captchas` int(1),";
        $create = $create . "`use_recaptchas` int(1),";
        $create = $create . "`recaptcha_key` text,";
        $create = $create . "`recaptcha_secret` text,";
        $create = $create . "`role_read` text,";
        $create = $create . "`role_write` text,";
        $create = $create . "`post_id` int(20),";
        $create = $create . "PRIMARY KEY (id)";
        $create = $create . ");";
        //db_delta($create);
        $results = $wpdb->query($create);
    }

    function getFormular($id) {
        global $wpdb;
        $tablename = $this->getWcfFormTableName();
        $sql = "SELECT * FROM " . $tablename . " where form_id='" . $id . "';";
        return $wpdb->get_results($sql, ARRAY_A);
    }

    function selectFormularTable() {
        global $wpdb;
        $tablename = $this->getWcfFormTableName();
        $sql = "SELECT * FROM " . $tablename;
        return $wpdb->get_results($sql);
    }

    function updateFormPost($form_id, $post_id) {
        global $wpdb;
        $tablename = $this->getWcfFormTableName();
        $wpdb->update($tablename, array('post_id' => $post_id), array('form_id' => $form_id));
    }

    function updateForm($form_id, $receiver, $subject, $body, $formname, $redirect, $useCaptchas, $useRecaptchas, $recaptchaKey, $recaptchaSecret) {
        global $wpdb;


        $tablename = $this->getWcfFormTableName();
        $wpdb->update($tablename, array('receiver_email' => $receiver, 'subject' => $subject, 'body' => $body, 'redirect' => $redirect,
            'form_name' => $formname, 'use_captchas' => $useCaptchas, 'use_recaptchas' => $useRecaptchas, 'recaptcha_key' => $recaptchaKey,
            'recaptcha_secret' => $recaptchaSecret), array('form_id' => $form_id));
    }

    function insertIntoFormularTable($formname, $formid, $receiver, $useCaptchas, $postId, $type, $redirect, $subject, $body) {
        global $wpdb;
        $wpdb->insert(
                $this->getWcfFormTableName(), array(
            'form_name' => $formname,
            'ts' => date("Y-m-d H:i:s"),
            'form_id' => $formid,
            'type' => $type,
            'receiver_email' => $receiver,
            'use_captchas' => $useCaptchas,
            'post_id' => $postId,
            'redirect' => $redirect,
            'subject' => $subject,
            'body' => $body
                ), array(
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%d',
            '%d',
            '%s',
            '%s',
            '%s'
                )
        );
    }

    function quote($str) {
        return "'" . $str . "'";
    }

    function deleteFormular($formid) {
        global $wpdb;
        $wpdb->delete($this->getWcfFormTableName(), array('form_id' => $formid), array('%s'));
    }

    function deleteFormularData($id) {
        global $wpdb;
        $wpdb->delete($this->getWcfFormDataTableName(), array('id' => $id), array('%d'));
    }

    function createFormDataImageTable() {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        global $wpdb;
        $create = "create table  IF NOT EXISTS " . $this->getWcfFormDataImageTableName() . " (";
        $create = $create . "`id` int(20) NOT NULL AUTO_INCREMENT,";
        $create = $create . "`fd_id` text NOT NULL,";
        $create = $create . "`column0` text,";
        $create = $create . "`column1` text,";
        $create = $create . "`column2` text,";
        $create = $create . "`column3` text,";
        $create = $create . "`column4` text,";
        $create = $create . "`column5` text,";
        $create = $create . "`column6` text,";
        $create = $create . "`column7` text,";
        $create = $create . "`column8` text,";
        $create = $create . "`column9` text,";
        $create = $create . "`column10` text,";
        $create = $create . "PRIMARY KEY (id)";
        $create = $create . ");";
        //db_delta($create);
        $this->lastError();
        $results = $wpdb->query($create);
    }

    /**
     * inserts an image path to the database.
     * 
     * @global type $wpdb
     * @param type $hashArray
     * @return type
     */
    function insertFormDataImage($hashArray) {
        global $wpdb;
        $formats = array();
        $keys = array_keys($hashArray);
        foreach ($keys as $key) {
            array_push($formats, "%s");
        }
        $erg = $wpdb->insert($this->getWcfFormDataImageTableName(), $hashArray, $formats);
        return $erg;
    }

    function createFormDataTable() {
        global $wpdb;
        $create = "create table  IF NOT EXISTS " . $this->getWcfFormDataTableName() . " (";
        $create = $create . "`id` int(20) NOT NULL AUTO_INCREMENT,";
        $create = $create . "`form_id` text NOT NULL,";
        $create = $create . "`ts` DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,";
        $create = $create . "`custom_id` text,";
        $create = $create . "`column0` text,";
        $create = $create . "`column1` text,";
        $create = $create . "`column2` text,";
        $create = $create . "`column3` text,";
        $create = $create . "`column4` text,";
        $create = $create . "`column5` text,";
        $create = $create . "`column6` text,";
        $create = $create . "`column7` text,";
        $create = $create . "`column8` text,";
        $create = $create . "`column9` text,";
        $create = $create . "`column10` text,";
        $create = $create . "`column11` text,";
        $create = $create . "`column12` text,";
        $create = $create . "`column13` text,";
        $create = $create . "`column14` text,";
        $create = $create . "`column15` text,";
        $create = $create . "`column16` text,";
        $create = $create . "`column17` text,";
        $create = $create . "`column18` text,";
        $create = $create . "`column19` text,";
        $create = $create . "`column20` text,";
        $create = $create . "`column21` text,";
        $create = $create . "`column22` text,";
        $create = $create . "`column23` text,";
        $create = $create . "`column24` text,";
        $create = $create . "`column25` text,";
        $create = $create . "`column26` text,";
        $create = $create . "`column27` text,";
        $create = $create . "`column28` text,";
        $create = $create . "`column29` text,";
        $create = $create . "`column30` text,";
        $create = $create . "`column31` text,";
        $create = $create . "`column32` text,";
        $create = $create . "`column33` text,";
        $create = $create . "`column34` text,";
        $create = $create . "`column35` text,";
        $create = $create . "`column36` text,";
        $create = $create . "`column37` text,";
        $create = $create . "`column38` text,";
        $create = $create . "`column39` text,";
        $create = $create . "`column40` text,";
        $create = $create . "`column41` text,";
        $create = $create . "`column42` text,";
        $create = $create . "`column43` text,";
        $create = $create . "`column44` text,";
        $create = $create . "`column45` text,";
        $create = $create . "`column46` text,";
        $create = $create . "`column47` text,";
        $create = $create . "`column48` text,";
        $create = $create . "`column49` text,";
        $create = $create . "`column50` text,";
        $create = $create . "`column51` text,";
        $create = $create . "`column52` text,";
        $create = $create . "`column53` text,";
        $create = $create . "`column54` text,";
        $create = $create . "`column55` text,";
        $create = $create . "`column56` text,";
        $create = $create . "`column57` text,";
        $create = $create . "`column58` text,";
        $create = $create . "`column59` text,";
        $create = $create . "`column60` text,";
        $create = $create . "`column61` text,";
        $create = $create . "`column62` text,";
        $create = $create . "`column63` text,";
        $create = $create . "`column64` text,";
        $create = $create . "`column65` text,";
        $create = $create . "`column66` text,";
        $create = $create . "`column67` text,";
        $create = $create . "`column68` text,";
        $create = $create . "`column69` text,";
        $create = $create . "`column70` text,";
        $create = $create . "`column71` text,";
        $create = $create . "`column72` text,";
        $create = $create . "`column73` text,";
        $create = $create . "`column74` text,";
        $create = $create . "`column75` text,";
        $create = $create . "`column76` text,";
        $create = $create . "`column77` text,";
        $create = $create . "`column78` text,";
        $create = $create . "`column79` text,";
        $create = $create . "PRIMARY KEY (id)";
        $create = $create . ");";
        //db_delta($create);
        $results = $wpdb->query($create);
    }

    /**
     * die keys sind column1, column2, ...
     * die values sind die Usereingaben in das Formular.
     * 
     * @global type $wpdb
     * @param type $hashArray
     */
    function insertFormData($hashArray) {
        global $wpdb;
        $formats = array();
        $keys = array_keys($hashArray);
        foreach ($keys as $key) {
            array_push($formats, "%s");
        }
        $erg = $wpdb->insert($this->getWcfFormDataTableName(), $hashArray, $formats);
        $formid = $hashArray['form_id'];
        $ts = $hashArray['ts'];
        $sql = 'select * from ' . $this->getWcfFormDataTableName() . " where form_id='" . $formid . "' and ts='" . $ts . "';";
        $rows = $wpdb->get_results($sql, ARRAY_A);
        foreach ($rows as $row) {
            $retid = $row['id'];
            break;
        }
        return $retid;
    }

    function insertFormDataImages($hashArray) {
        global $wpdb;
        $formats = array();
        $keys = array_keys($hashArray);
        foreach ($keys as $key) {
            array_push($formats, "%s");
        }
        $erg = $wpdb->insert($this->getWcfFormDataImageTableName(), $hashArray, $formats);
        return $erg;
    }

    function lastError() {
        global $wpdb;
        wcflog("last db error: " . $wpdb->last_error);
    }

    /**
     * macht ein select auf form data anhand formId.
     * $cols ist ein String mit den gewünschten Cols, zB.
     * id,column0,column1,column2
     * 
     * wichtig: hinten kein komma!
     * 
     * @global type $wpdb
     * @param type $id
     * @param type $cols
     * @return type
     */
    function getFormData($formId, $cols) {
        global $wpdb;
        $tablename = $this->getWcfFormDataTableName();
        $sql = "SELECT " . $cols . " FROM " . $tablename . " where form_id='" . $formId . "';";
        return $wpdb->get_results($sql, ARRAY_A);
    }

    function saveOneFormData($id, $colVals) {
        global $wpdb;
        wcflog("saveOneFormData wurde aufgerufen");
        $tablename = $this->getWcfFormDataTableName();
        $wpdb->update($tablename, $colVals, array('id' => $id)
        );
    }

    function deleteParam($formId) {
        // todo
    }

    function isParam($formId, $key) {
        global $wpdb;
        $tablename = $this->getWcfKeyValuesTableName();
        $sql = "SELECT * FROM " . $tablename . " where `form_id`='" . $formId . "' and `key` = '" . $key . "';";
        $erg = $wpdb->get_results($sql, ARRAY_A);
        foreach ($erg as $k => $v) {
            return true;
        }
        return false;
    }

    function getParams($formId) {
        global $wpdb;
        $tablename = $this->getWcfKeyValuesTableName();
        $sql = "SELECT * FROM " . $tablename . " where `form_id`='" . $formId . "';";
        return $wpdb->get_results($sql, ARRAY_A);
    }

    function getParam($formId, $key) {
        global $wpdb;
        $tablename = $this->getWcfKeyValuesTableName();
        $sql = "SELECT * FROM " . $tablename . " where `form_id`='" . $formId . "' and `key` = '" . $key . "';";
        $erg = $wpdb->get_results($sql, ARRAY_A);

        if (count($erg) < 1) {
            return "";
        }
        $val = $erg[0]['value'];
        return $val;
    }

    function saveParam($formid, $key, $value) {
        if (empty($formid)) {
            return;
        }
        if ($this->isParam($formid, $key)) {
            $this->_updateParam($formid, $key, $value);
        } else {
            $this->_insertParam($formid, $key, $value);
        }
    }

    private function _insertParam($formid, $key, $value) {
        global $wpdb;
        $tablename = $this->getWcfKeyValuesTableName();
        $erg = $wpdb->insert($tablename, array('value' => $value, 'form_id' => $formid, 'key' => $key), array('%s', '%s', '%s'));
    }

    private function _updateParam($formid, $key, $value) {
        global $wpdb;
        $tablename = $this->getWcfKeyValuesTableName();
        $wpdb->update($tablename, array('value' => $value), array('form_id' => $formid, 'key' => $key)
        );
    }

    function getOneFormData($id) {
        global $wpdb;
        $tablename = $this->getWcfFormDataTableName();
        $sql = "SELECT * FROM " . $tablename . " where id='" . $id . "';";
        return $wpdb->get_results($sql, ARRAY_A);
    }

    function getOneFormDataImages($id) {
        global $wpdb;
        $tablename = $this->getWcfFormDataImageTableName();
        $sql = "SELECT * FROM " . $tablename . " where fd_id='" . $id . "';";
        return $wpdb->get_results($sql, ARRAY_A);
    }

    function dropFormularTable() {
        global $wpdb;
        $drop = "drop table " . $this->getWcfFormTableName() . ";";
        $wpdb->query($drop);
    }

    function dropFormularDataTable() {
        global $wpdb;
        $drop = "drop table " . $this->getWcfFormDataTableName() . ";";
        $wpdb->query($drop);
    }

    function dropFormularDataImageTable() {
        global $wpdb;
        $drop = "drop table " . $this->getWcfFormDataImageTableName() . ";";
        $wpdb->query($drop);
    }

    function dropKeyValueTable() {
        global $wpdb;
        $drop = "drop table " . $this->getWcfKeyValuesTableName() . ";";
        $wpdb->query($drop);
    }

    function dropWcfSessionsTable() {
        global $wpdb;
        $drop = "drop table " . $this->getWcfSessionsTableName() . ";";
        $wpdb->query($drop);
    }

    function getWcfFormTableName() {
        global $wpdb;
        $prefix = $wpdb->get_blog_prefix();
        return $prefix . 'wcf_formular';
    }

    function getWcfFormDataTableName() {
        global $wpdb;
        $prefix = $wpdb->get_blog_prefix();
        return $prefix . 'wcf_form_data';
    }

    function getWcfFormDataImageTableName() {
        global $wpdb;
        $prefix = $wpdb->get_blog_prefix();
        return $prefix . 'wcf_form_data_image';
    }

    function getSessionsValue($key) {
        global $wpdb;
        $tablename = $this->getWcfSessionsTableName();
        $sql = "SELECT * FROM " . $tablename . " where `key`='" . $key . "';";
        $resultset = $wpdb->get_results($sql, ARRAY_A);
        return $resultset[0]['value'];
    }

    /**
     * hier muss noch ein delete rein, last ok ist deprecated.s

     * @global type $wpdb
     * @param type $session
     */
    function invalidateSession($session) {
        global $wpdb;
        $tablename = $this->getWcfSessionsTableName();
        $wpdb->delete($tablename, array('key' => $session), array('%s'));
    }

    function getSessionsKey($value) {
        global $wpdb;
        $tablename = $this->getWcfSessionsTableName();
        $sql = "SELECT key FROM " . $tablename . " where `value`='" . $value . "';";
        $resultset = $wpdb->get_results($sql, ARRAY_A);
        $needle = "false";
        foreach ($resultset as $row) {
            if (isset($row['key']) && !empty($row['key'])) {
                $needle = $row['key'];
            }
        }
        return $needle;
    }

    function dropSessionKey($key) {
        global $wpdb;
        $wpdb->delete($this->getWcfSessionsTableName(), array('key' => $key), array('%s'));
    }

    function cleanAllSessions() {
        global $wpdb;
        $wpdb->delete($this->getWcfSessionsTableName());
    }

    function cleanExpiredSessions() {
        global $wpdb;
        // TODO
        $wpdb->delete($this->getWcfSessionsTableName());
    }

    function saveSession($session, $value) {
        global $wpdb;
        $erg = $wpdb->insert($this->getWcfSessionsTableName(), array('key' => $session, 'value' => $value, 'ts' => date("Y-m-d H:i:s")), array('%s', '%s', '%s'));
        return $erg;
    }

    function getWcfSessionsTableName() {
        global $wpdb;
        $prefix = $wpdb->get_blog_prefix();
        return $prefix . 'wcf_sessions';
    }

    function getWcfKeyValuesTableName() {
        global $wpdb;
        $prefix = $wpdb->get_blog_prefix();
        return $prefix . 'wcf_key_values';
    }

}
