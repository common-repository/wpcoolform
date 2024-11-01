<?php
namespace wcf_coolform;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
class WcfFormDocGenerator {

    /**
     * this function is called on click on download in the form table.
     * Fills in a concrete word file and starts a download
     * @param type $id
     */
    function createConcreteForm($id) {
        wcflog("create concrete form coorectamente");
        if (empty($id)) {
            return;
        }
        if (!WP_CF_PRO) {
            return;
        }
        $sql = new WCFSQLHandler();
        //$form = $sql->getFormular($id)
        $formData = $sql->getOneFormData($id);
        $formId = $formData[0]['form_id'];
        $form = $sql->getFormular($formId);
        $type = $form[0]['type'];
        $formWordTemplatePath = getPathWordfile($formId);
        $resultPath = createResultPath();
        // colHeadlines:
        $ini = new WcfIniFilesHandler($formId);
        $xml = new WcfXmlHandler($ini->getXMLIniFile());
        
        $fields = $xml->getFields();
        $cols = array();
        foreach ($fields as $field) {
            $cols["column" . $field->getId()] = $field->getName();
        }
        
        $keys = array_keys($cols);
        $reps = array();
        foreach ($keys as $key) {
            $name = $cols[$key];
            $wert = $formData[0][$key];
            $reps["". $name] = $wert;
        }
        // spezialfelder
        $df = $this->getDateFormat($formId);
        $specials = array();
        $ts = $form[0]['ts'];
        $tsdate = date($df, strtotime($ts));
        $specials["[wcfs.date]"] = $tsdate;
        // und noch die images
        $formData = $sql->getOneFormDataImages($id);
        $images = array();
        if (!empty($formData)) {
            $inipath = $ini->getIniFileImages();
            foreach (file($inipath) as $line) {
                list($col, $name, $path) = split("=", trim($line));
                $theLocation = $formData[0][$col];
                $images[$path] = $theLocation;
            }
        }
        $zip = new WcfZipHandler();
        $zip->action($formWordTemplatePath, $resultPath, $reps, $images,$specials);
        return $resultPath;
    }

    
    /**
     * 
     * akt nur wcf.Now
     * 
     * @param type $formId
     * @param array $replaces
     */
    function getDateFormat($formId) {
        // date
        $sql = new WCFSQLHandler();
        $df = $sql->getParam($formId, 'date_format');
        if (empty($df)) {
            return "Y/m/d";
        }
        $df = str_replace("yy", "Y", $df);
        
        return $df;
    }
    /**
     * 
     * @param type $formId
     * @param type $concreteFormId
     * @return type
     */
    function getReplaces($concreteFormId) {
        // read the concrete Form:
        $sql = new WCFSQLHandler();
        $formData = $sql->getOneFormData($concreteFormId);
        $formId = $formData[0]['form_id'];
        // get replaces
        $ini = new WcfIniFilesHandler($formId);
        $inipath = $ini->getIniFile();
        $cols = array();
        foreach (file($inipath) as $line) {
            list($col, $name) = split("=", $line);
            $cols[$col] = $name;
        }
        $replaces = array();
        $keys = array_keys($cols);
        foreach ($keys as $key) {
            $replaces[trim($cols[$key])] = trim($formData[0][$key]);
        }
        return $replaces;
    }

}
