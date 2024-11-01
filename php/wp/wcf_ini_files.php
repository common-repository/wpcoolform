<?php
namespace wcf_coolform;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class WcfIniFilesHandler {

    var $formId;

    function __construct($form_id) {
        $this->formId = $form_id;
    }

    
    /**
     * expects something like:
     * 
     *  $content = "column0;column1;column2;column3;column4;column5;";
     * 
     * @param type $contents
     */
    function writeTableIniFile($contents) {
        file_put_contents($this->getTableIniFile(), $contents);
    }

    /**
     * im table ini file stehen alle anzuzeigenden spalten der tabelle.
     * 
     * returns a string array with all columns to show in the data page
     * of a form.
     * @return type
     */
    function readTableIniFile() {
        if (!file_exists($this->getTableIniFile())) {
            $content = "column0;column1;column2;column3;column4;column5;";
        } else {
            $content = file_get_contents($this->getTableIniFile());
        }
        return explode(";", $content);
    }

    function writeFormFieldsSettingsIni($parameter) {
        if (!isset($parameter)) {
            return;
        }
        $content = "";
        $zeilen = array();
        $cnt = 99;
        foreach ($parameter as $column => $colParams) {
            $mand = isset($colParams['mandatory']) ? $colParams['mandatory'] : "";
            $type = isset($colParams['type']) ? $colParams['type'] : "Text";
            $format = isset($colParams['format']) ? $colParams['format'] : tr('normal');
            $lines = isset($colParams['lines']) ? $colParams['lines'] : "1";
            $label = isset($colParams['label']) ? $colParams['label'] : "";
            $rang = isset($colParams['rang']) ? $colParams['rang'] : $cnt++;
            $values = isset($colParams['values']) ? $colParams['values'] : "";
            $confirmation = isset($colParams['confirmation']) ? $colParams['confirmation'] : "";
            $line = $column . "=" . $mand . ";" . $type . ";" . $lines . ";" . $label . ";" . $values . ";" . $confirmation. ";" . $format. "\n";
            $zeilen[$rang] = $line;
        }
        ksort($zeilen);
        foreach ($zeilen as $rang => $line) {
            $content .=$line;
        }
        file_put_contents($this->getPathIniFileFieldSettings(), $content);
    }

    
    function readFormFieldsSettingsIni() {
        // file exists?
        if (!file_exists($this->getPathIniFileFieldSettings())) {
            $this->createStandardFormFieldsSettingsIni();
        }
        $ret = array();
        foreach (file($this->getPathIniFileFieldSettings()) as $line) {
            $line = trim($line);
            list($col, $params) = explode("=", $line."=");
            list($mandatory, $type, $lines, $label, $values,$confirmation,$format) = explode(";", $params);
            $parameter = array('mandatory' => $mandatory, 'type' => $type, 'lines' => $lines, 'label' => $label, 'values' => $values, 'confirmation' => $confirmation,'format' => $format);
            $ret[$col] = $parameter;
        }
        return $ret;
    }

    private function createStandardFormFieldsSettingsIni() {
        $set = $this->getPathIniFileFieldSettings();
        $fields = $this->readFormFieldsIni();
        $lines = "";
        foreach ($fields as $col => $name) {
            $lines .= $col . "=false;String;1;" . $name . ";;;;;;;;;;\n";
        }
        file_put_contents($set, $lines);
    }

    function readFormFieldsImageIni() {
        // todo.. isn tripel
        return $this->readKeyValues($this->getIniFileImages());
    }

    function readFormFieldsIni() {
        $ret = array();
        $xml = new WcfXmlHandler($this->getXMLIniFile());
        $fields = $xml->getFields();
        foreach ($fields as $field) {
            $col = "column" . $field->getId();
            $name = $field->getName();
            $ret[$col] = $name;
        }
        return $ret;//$this->readKeyValues($this->getIniFile());
    }

    private function readKeyValues($path) {
        $ret = array();
        foreach (file($path) as $line) {
            list($col, $name) = explode("=", trim($line)."=");
            $ret[$col] = $name;
        }
        return $ret;
    }

    /**
     * returns the path of the ini file for the given id.
     * 
     * 
     * @param type $id
     * @return type
     */
    function getIniFileImages() {
        return $this->getFormFolder() . "/" . $this->formId . "_images.ini";
    }

    function getPathIniFileFieldSettings() {
        return $this->getFormFolder() . "/" . $this->formId . "_field_settings.ini";
    }

    function getIniFile() {
        return $this->getFormFolder() . "/" . $this->formId . ".ini";
    }
    
    function getXMLIniFile() {
        return $this->getFormFolder() . "/" . $this->formId . ".ini.xml";
    }

    function getTableIniFile() {
        return $this->getFormFolder() . "/" . $this->formId . "_table.ini";
    }

    /**
     * returns the root folder for a formular.
     * 
     * @param type $id
     * @return type
     */
    private function getFormFolder() {
        return getPathWcfForms() . $this->formId;
    }

}
