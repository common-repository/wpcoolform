<?php
namespace wcf_coolform;

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

class TJC_Init {

    function init() {
        $this->predefines();
        $this->includesCO();
        $this->includesCFCO();
        $this->includes();
        $this->includes_pro();
        $this->includes_cb_co();
        $this->defines();
        $control = new TJC_Wp_Controll();
        $control->wp_init();
        $this->runUpdater();
        //$menu = new TJC_Wp_Menu();
        //$menu->createAdminMenu();
    }

    /**
     * called on plugin activation.
     * 
     * 
     * 
     */
    public static function onActivate($param) {
        $init = new TJC_Init();
        $init->init();
        $sql = new WCFSQLHandler();
        $sql->onActivate();
    }

    public static function onUninstall($param) {
        if (!defined('WP_UNINSTALL_PLUGIN'))
            exit();
        $init = new TJC_Init();
        $init->init();
        $sql = new WCFSQLHandler();
        $sql->onUninstall();
    }

    private function includes_cb_co() {
        if (!file_exists(CBCOINIT)) {
            define('CB_CO_PRO', false);
        } else {
            include_once CBCOINIT . 'wcb_form_settings_handler.php';
            include_once CBCOINIT . 'tjc_factory.php';
            // nur ein test, muss hier wieder auf true
            define('CB_CO_PRO', false);
        }
    }

    /**
     * checks if the pro version is available.
     * 
     * @return type
     */
    private function includes_pro() {
        if (!file_exists(PROINIT)) {
            define('WP_CF_PRO', false);
        } else {
            include_once PROINIT . 'WcfZipHandler.php';
            define('WP_CF_PRO', true);
        }
    }

    private function includes() {
        include_once 'tjc_factory.php';
        include_once 'tjc_wp_control.php';
        include_once 'tjc_wp_menu.php';
        include_once PINIT . 'wcf_init.php';
        include_once PINIT . 'wcf_settings.php';
        include_once PINIT . 'wcf_updater.php';
        include_once PINIT . 'wcf_form_doc_generator.php';
        include_once PINIT . 'wcf_create_new_form_handler.php';
        include_once PINIT . 'wcf_formular_table_handler.php';
        include_once PINIT . 'wcf_captcha_handler.php';
        include_once PINIT . 'wcf_ini_files.php';
        include_once PINIT . 'wcf_page_generator.php';
        include_once PINIT . 'wcf_util.php';

        include_once PINIT . 'wcf_List_table.php';
        include_once PINIT . 'wcf_i18n.php';
        include_once PINIT . 'wcf_form_settings_handler.php';
        $pathUpdateChecker = CF_ROOT_FOLDER . 'plugin-updates/plugin-update-checker.php';
        if (file_exists($pathUpdateChecker)) {
            include_once $pathUpdateChecker;
        }
    }

    private function includesCO() {
        include_once COINIT . 'tjc_co_utils.php';
    }

    private function includesCFCO() {
        include_once CFCOINIT . 'wcf_field_et.php';
        include_once CFCOINIT . 'wcf_xml_handler.php';
        include_once CFCOINIT . 'wcf_html_helper.php';
        include_once CFCOINIT . 'wcf_image_handler.php';
        include_once CFCOINIT . 'wcf_form_builder.php';
    }

    private function runUpdater() {
        if (file_exists(CF_ROOT_FOLDER . 'plugin-updates/plugin-update-checker.php')) {
            $MyUpdateChecker = \PucFactory::buildUpdateChecker(
                            'http://localhost/wpcoolform.json', __FILE__, 'WpCoolForm'
            );
        }
    }

    /**
     * defines needed by include()
     */
    private function predefines() {
        define('COINIT', CF_ROOT_FOLDER . "php/co/");
        define('PINIT', CF_ROOT_FOLDER . "php/wp/");
        define('PROINIT', CF_ROOT_FOLDER . "php/cf_pro/");
        define('CBCOINIT', CF_ROOT_FOLDER . "php/cb_co/");
        define('WPPROINIT', CF_ROOT_FOLDER . "php/wpcf_pro/");
        define('CFCOINIT', CF_ROOT_FOLDER . "php/cf_co/");
    }

    /**
     * all plugin specific defines.
     * 
     * 
     */
    private function defines() {
        define('WCF_LOGO_PL', '[WCF_LOGO]');
        define('WCF_PATH_HTML', CF_ROOT_FOLDER . "html/");
        define('WCF_PLUGIN_URL', CF_ROOT_URL);
        define('WCF_IMG', WCF_PLUGIN_URL . '/img/');
        define('WCF_LOGO', WCF_PLUGIN_URL . '/img/logo.png');
        define('WCF_DD_ICON', WCF_PLUGIN_URL . '/img/dd_icon.png');
        define('TRANS_DE', CF_ROOT_FOLDER . "lang/de-DE/de_DE.ini");
        define('TRANS_EN', CF_ROOT_FOLDER . "lang/en-GB/en_EN.ini");
        define('SETTINGS_URL', "admin.php?page=wcf_settings_menu_item");
        define('PATH_CAPTCHAS', WCF_PLUGIN_URL . "/php/util/");
        define('SETTINGS_CSS', WCF_PATH_HTML . "settingsCSS");
        define('SETTINGS_JS', WCF_PATH_HTML . "settingsJS");
    }

}

function getFactory() {
    if (CB_CO_PRO) {
        return new TJC_CB_Factory();
    } else {
        return new TJC_Factory();
    }
}
