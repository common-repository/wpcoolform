<?php

namespace wcf_coolform;

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * defines and creates the admin backend menu for wordpress.
 * 
 * 
 * 
 */

class TJC_Wp_Menu {

    public static function createAdminMenu($content) {
        //wcf_main_menu_item();
        //add_menu_page("WpCoolForm", "WpCoolForm", "manage_options", "wcf_main_menu_item", array('TJC_Wp_Menu', "welcomePage"));
        add_menu_page("WpCoolForm", "WpCoolForm", "manage_options", "wcf_main_menu_item", array('wcf_coolform\TJC_Wp_Menu', "wcf_new_form_menu_item_output"));

        //add_menu_page("Settings", "WCF Form Settings", "manage_options", "wcf_settings_menu_item", array('TJC_Wp_Menu', "settingsPage"));
        add_menu_page("Settings", "WCF Form Settings", "manage_options", "wcf_settings_menu_item", array('wcf_coolform\TJC_Wp_Menu', "wcf_new_form_menu_item_output"));

        add_menu_page(tr('headline_wcf_form_data'), tr('menu_wcf_form_data'), "manage_options", "wcf_data_menu_item", array('wcf_coolform\TJC_Wp_Menu', "dataPage"));
        //wcf_new_form_menu_item();
        add_submenu_page("wcf_main_menu_item", "WpCoolForm", tr('new_formular'), "manage_options", "wcf_new_form_menu_item", array('wcf_coolform\TJC_Wp_Menu', "wcf_new_form_menu_item_output"));
        // FAQ
        add_submenu_page("wcf_main_menu_item", "WpCoolForm", "FAQ", "manage_options", "wcf_faq", array('wcf_coolform\TJC_Wp_Menu', "wcf_show_faq"));
        // just a page ohne menueeintrag:
        // select:
        $tableHandler = new WCFSQLHandler();
        $rows = $tableHandler->selectFormularTable();
        foreach ($rows as $row) {
            if (isset($row->post_id) && $row->post_id !== "0") {
                add_submenu_page("wcf_data_menu_item", "WpCoolForm", TJC_Wp_Menu::unquote($row->form_name), "manage_options", TJC_Wp_Menu::unquote($row->form_id), array('wcf_coolform\TJC_Wp_Menu', "wcf_open_form"));
            }
            add_submenu_page("wcf_settings_menu_item", "WpCoolForm", TJC_Wp_Menu::unquote($row->form_name), "manage_options", "set_" . TJC_Wp_Menu::unquote($row->form_id), array('wcf_coolform\TJC_Wp_Menu', "wcf_open_form_settings"));
        }
    }

    /**
     * echoes the settings page.
     * 
     */
    static function settingsPage() {
        $path = getPathSettingsPage();
        $content = file_get_contents($path);
        $content = str_replace(WCF_LOGO_PL, WCF_LOGO, $content);
        $content = str_replace("[IMG]", WCF_IMG, $content);
        echo $content;
    }

    /**
     * echoes the welcome page.
     * 
     */
    public static function welcomePage($content) {
        $path = getPathWelcomePage();
        $content = file_get_contents($path);
        $content = str_replace(WCF_LOGO_PL, WCF_LOGO, $content);
        $content = str_replace("[IMG]", WCF_IMG, $content);
        echo $content;
    }

    /**
     * echoes the data page.
     * 
     */
    static function dataPage($content) {
        $path = getPathDataPage();
        $content = file_get_contents($path);
        $content = str_replace(WCF_LOGO_PL, WCF_LOGO, $content);
        echo $content;
    }

    /**
     * 
     */
    static function wcf_new_form_menu_item_output($content) {
        $path = getPathNewFormPage();
        $content = file_get_contents($path);
        $replacement = "";
        if (WP_CF_PRO) {
            $image = '<p><img style="border:3px solid black;" src="[IMG]example_placeholder.jpg"></p>';
            $hint = '<p>' . tr('hint_form_doc_upload') . '</p>';
            $replacement = $image . $hint . '<p>Wordfile (.docx or .odt): <p><input type="file" name="doc_file" id="file" accept=""><p></p>';
        }
        $content = str_replace('[WP_FORM_UPLOAD]', $replacement, $content);
        $content = str_replace(WCF_LOGO_PL, WCF_LOGO, $content);
        $content = str_replace("[IMG]", WCF_IMG, $content);
        echo $content;
    }

    /**
     * 
     */
    static function wcf_show_faq($content) {
        $path = getPathFAQ();
        $content = file_get_contents($path);
        $content = str_replace(WCF_LOGO_PL, WCF_LOGO, $content);
        echo $content;
    }

    /**
     * zeigt die Settings für ein form an und wird aus dem main menue heraus aufgerufen.
     */
    static function wcf_open_form_settings($content) {


        $page = wcfget('page');
        if (array_key_exists('save_form', $_GET)) {
            $save = wcfget('save_form');
        }

        $id = str_replace("set_", "", $page);
        $factory = getFactory();
        $handler = $factory->getSettingsHandler();
        $handler->setFormId($id);
        if (isset($save)) {
            $handler->saveFormSettings();
        }
        $handler->showSettingsPage();
    }

    /**
     * zeigt die form daten an und wird aus dem sub main menue heraus aufgerufen.
     * 
     */
    static function wcf_open_form() {
        $action = wcfget('action');
        $id = wcfget('id');
        $page = wcfget('page');
        $saveId = wcfpost('save_id');
        $changeShownColumns = wcfget('change_table_columns_shown');
        $ini = new WcfIniFilesHandler($page);

        wcflog("hier geht srein");
        if (isset($changeShownColumns) && !empty($changeShownColumns)) {
            wcflog("ich change die columns");
            $cols = "";
            foreach ($_GET as $key => $val) {
                if (startsWith($key, 'show_')) {
                    $cols .= $val . ";";
                }
            }
            $ini->writeTableIniFile($cols);
        }
        if ($action == 'edit' && empty($saveId)) {
            wcflog("ich edite die columns");
            echo tr('label_formular') . ":<p>";
            echo "<form method='POST'>";
            $xml = new WcfXmlHandler($ini->getXMLIniFile());
            $fields = $xml->getFields();


            $pageGenerator = new WpFormPageCreator();
            $sql = new WCFSQLHandler();
            $rows = $sql->getOneFormData($id);
            $formFields = $pageGenerator->generateFormFieldsWithValuesNew($page, $fields, $rows[0]);
            echo $formFields;
            echo "<input type='hidden' name='action' value='save'>";
            echo "<input type='hidden' name='save_id' value='" . $id . "'>";
            echo "<input type='submit' name='submit' value='" . tr('cancel') . "'>";
            echo "<input type='submit' name='submit' value='" . tr('save') . "'>";
            echo "</form>";
        } else {
            wcflog("ich save die columns");
            if (!empty($saveId) && $_POST['submit'] === tr('save')) {
                // hier dann auch noch speichern ...
                $sql = new WCFSQLHandler();
                $inputs = readHtmlFormInputs($page);
                $sql->saveOneFormData($id, $inputs);
            }
            $gen = new Wp_List_Table_Creator();
            $gen->createFormPage($page);
        }
    }

    public static function unquote($str) {
        return str_replace("'", "", $str);
    }

}
