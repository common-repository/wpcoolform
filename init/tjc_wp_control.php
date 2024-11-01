<?php
namespace wcf_coolform;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * receives all wordpress hooks.
 * 
 * 
 * 
 */
class TJC_Wp_Controll {

    /**
     * this method adds wordpress menus hooks, filters and scripts.
     * 
     * 
     */
    public static function wp_init() {

// the admin menu
        add_action("admin_menu", array('wcf_coolform\TJC_Wp_Menu', 'createAdminMenu'));

        /**
         * das tut, es landen halt alle forms hier, muss ueber eine id 
         * korrekt gefiltert werden.
         * 
         */
        //add_action('template_redirect', 'handleUserForm');
// called on activation
        //register_activation_hook(__FILE__, 'wcf_activate');
        // the ajax callbacks ..
        add_action('wp_ajax_create_form_action', 'wcf_coolform\create_form_action_callback');
        add_action('wp_ajax_delete_form_action', 'wcf_coolform\delete_form_action_callback');
        add_action('wp_ajax_upload_form_action', 'wcf_coolform\upload_form_action_callback');
        add_action('wp_ajax_handle_settings_form_action', 'wcf_coolform\handle_settings_form_action_callback');


        add_action('admin_post_nopriv_handle_wcf_form_standard', array('wcf_coolform\TJC_Wp_Controll', 'handle_wcf_form_standard'));
        add_action('admin_post_handle_wcf_form_standard', array('wcf_coolform\TJC_Wp_Controll', 'handle_wcf_form_standard'));
        add_action('wp_ajax_handle_wcf_form_standard', array('wcf_coolform\TJC_Wp_Controll', 'handle_wcf_form_standard'));
        add_action('wp_ajax_nopriv_handle_wcf_form_standard', array('wcf_coolform\TJC_Wp_Controll', 'handle_wcf_form_standard'));

        add_action('wp_ajax_handle_wcf_form_ajax', array('wcf_coolform\TJC_Wp_Controll', 'handle_wcf_form_ajax'));
        add_action('wp_ajax_nopriv_handle_wcf_form_ajax', array('wcf_coolform\TJC_Wp_Controll', 'handle_wcf_form_ajax'));



        add_filter('the_content', array('wcf_coolform\TJC_Wp_Controll', 'add_nonce_to_wcf_form'));
        add_filter('the_content', array('wcf_coolform\TJC_Wp_Controll', 'add_captcha_to_wcf_form'));
        add_filter('the_content', array('wcf_coolform\TJC_Wp_Controll', 'add_form_to_post'));
        add_filter('the_content', array('wcf_coolform\TJC_Wp_Controll', 'add_hidden_pres'));
        add_filter('the_content', array('wcf_coolform\TJC_Wp_Controll', 'add_special_fields'));
        add_filter('the_content', array('wcf_coolform\TJC_Wp_Controll', 'add_form_replacements'));

        add_action('template_redirect', 'wcf_coolform\restrict_wcf_downloads');

        add_action('wp_loaded', array('wcf_coolform\TJC_Wp_Controll', 'actions_on_wp_loaded'));
    }

    static function actions_on_wp_loaded($content) {
        add_action('wp_enqueue_scripts', array('wcf_coolform\TJC_Wp_Controll', 'enqueue_wcf_scripts'));
        add_action('admin_enqueue_scripts', array('wcf_coolform\TJC_Wp_Controll', 'enqueue_wcf_admin_scripts'));
    }

    static function add_form_replacements($content) {
        $gen = new WpFormPageCreator();
        $tableHandler = new WCFSQLHandler();
        $rows = $tableHandler->selectFormularTable();
        foreach ($rows as $row) {
            if (strpos($content, '[wcf_' . $row->form_id . "]")) {
                $rep = $gen->generateCode($row->form_id);
                $content = str_replace('[wcf_' . $row->form_id . "]", $rep, $content);
            }
        }
        return $content;
    }

    /**
     * hier wird das form code replacement eingetütet.
     * 
     * 
     * @param type $content
     * @return type
     */
    static function add_form_to_post($content) {
        // TODO
        return $content;
    }

    static function add_special_fields($content) {
        $custom = wcfget('pre_');//cleaninput($_GET['pre_']);
        $content = str_replace("[wcfs.custom]", $custom, $content);
        return $content;
    }

    static function add_hidden_pres($content) {
        $rep = "";
        foreach ($_GET as $k => $v) {
            if (startsWith($k, "pre_")) {
                $kk = cleaninput($k);
                $vv = cleaninput($v);
                $rep .= "<input type='hidden' name='$kk' value='$vv'>";
            }
        }
        return str_replace("[wcf_hidden_pres]", $rep, $content);
    }

    static function add_captcha_to_wcf_form($content) {
        $handler = new WcfCaptchaHandler();
        list($theCode, $theInputField) = $handler->createCaptcha();
        $content = str_replace("[wcf_captcha]", $theCode, $content);
        $content = str_replace("[wcf_captcha_input_field]", $theInputField, $content);
        return $content;
    }

    /**
     * is called on user form submit
     */
    public static function handle_wcf_form_ajax() {
        TJC_Wp_Controll::handle_wcf_form(true);
    }

    public static function handle_wcf_form_standard() {
        TJC_Wp_Controll::handle_wcf_form(false);
    }

    /**
     * wird aus userform heraus ausgerufen.
     */
    public static function handle_wcf_form($ajax) {
        check_referrer();
        $id = savepost('form_id');
        $sqlh = new WCFSQLHandler();
        $rows = $sqlh->getFormular($id);
        $redirect = $rows[0]['redirect'];
        $pageId = $rows[0]['post_id'];
        $nonce = savepost('key');

        if (!wp_verify_nonce($nonce, 'wcf-nonce')) {
            // This nonce is not valid.
            die('Security check');
        }
        // verify captcha:
        $useCaptchas = $rows[0]['use_captchas'] == 1;
        $useRecaptchas = $rows[0]['use_recaptchas'] == 1;
        if ($useCaptchas) {
            $captcha = savepost('wcf_captcha');
            $session = savepost('wcf_session');
            $captchas = new WcfCaptchaHandler();
            $captchaOk = $captchas->validate($session, $captcha);
            if (!$captchaOk) {
                // auf error seite
                // meldung das captcha nicht ok war ????
                // write cookie captcha not ok
                list($newSession, $newCaptcha) = $captchas->createOnlyCaptcha();
                $wrongCaptchaMessage = tr('wrong_captcha_message');
                echo '{"redirect":"false","wcf_session":"' . $newSession . '","wcf_captcha":"' . $newCaptcha . '","message":"' . $wrongCaptchaMessage . '"}';
                wp_die();
                return;
            }
        } else if ($useRecaptchas) {
            if (isset($_POST['g-recaptcha-response'])) {
                $recaptchakey = $rows[0]['recaptcha_key'];
                $recaptchasecret = $rows[0]['recaptcha_secret'];
                $response = wp_remote_get("https://www.google.com/recaptcha/api/siteverify?secret=" . $recaptchasecret . "&response=" . wcfpost('g-recaptcha-response'));
                $response = json_decode($response["body"], true);
                if (true == $response["success"]) {
                    //return $commentdata;
                } else {
                    echo '{"redirect":"false","wcf_session":"' . $newSession . '","wcf_captcha":"' . $newCaptcha . '","message":"' . $wrongCaptchaMessage . '"}';
                    return null;
                }
            } else {
                echo '{"redirect":"false","wcf_session":"' . $newSession . '","wcf_captcha":"' . $newCaptcha . '","message":"' . $wrongCaptchaMessage . '"}';
                return null;
            }
        }


        $receiver = $rows[0]['receiver_email'];

        $input = readHtmlFormInputs($id);
        $input['form_id'] = $id;
        $input['ts'] = date("Y-m-d H:i:s");
        $forminputId = $sqlh->insertFormData($input);
        // handle Images:
        $ff = new WcfIniFilesHandler($id);
        $inipath = $ff->getIniFileImages();
        $imgrows = array();
        foreach (file($inipath) as $line) {
            list($col, $name, $imgPath) = split("=", trim($line));
            $tagname = 'img_' . $id . '_' . $name;
            if (array_key_exists($tagname, $_FILES)) {
                $userfilename = $_FILES[$tagname]['name'];
                $ic = new WcfImageConverter($userfilename);
                $imageType = $ic->getImageType();
                if ($imageType !== "false") {
                    $dir = getPathFormImageFolder($id);
                    $fl = $dir . "/" . generateRandomString() . "." . $imageType;
                    move_uploaded_file($_FILES[$tagname]['tmp_name'], $fl);
                    $imgrows[$col] = $fl;
                }
            }
        }
        $imgrows["fd_id"] = $forminputId;
        $imginputId = $sqlh->insertFormDataImages($imgrows);

        // hier muss noch gemailt werden:
        if (!empty($receiver)) {
            sendFormdata($receiver, $forminputId, $id);
        }


        // Bestaetigungsfelder suchen:
        $ini = new WcfIniFilesHandler($id);
        $xml = new WcfXmlHandler($ini->getXMLIniFile());
        $fields = $xml->getFields();

        foreach ($fields as $field) {
            if (!$field->getType()->isEmail()) {
                continue;
            }
            if (!$field->isConfirmationEmail()) {
                continue;
            }
            $col = "column" . $field->getId();
            if (isset($input[$col])) {
                sendFormdata($input[$col], $forminputId, $id);
            } 
        }

        // hier ende
        $red = "";
        if (!empty($redirect)) {
            $red = $redirect;
        } else {
            $red = get_page_link($pageId);
        }



        $append = $sqlh->getParam($id, "append_form_data");
        if ($append === 'true') {
            $parameterString = "?";
            foreach ($_POST as $k => $v) {
                $kk = cleaninput($k);
                $vv = cleaninput($v);
                $parameterString .= "&pre_" . $kk . "=" . $vv;
            }
        } else {
            $parameterString = "";
        }
        if ($ajax) {
            echo '{"redirect":"' . $redirect . $parameterString . '"}';
            wp_die();
        } else {
            echo '{"redirect":"' . $redirect . $parameterString . '"}';
            wp_die();
            //wp_redirect($red . $parameterString);
        }
        return;
    }

    /**
     * scripts for the backend area.
     * 
     * 
     */
    static function enqueue_wcf_admin_scripts($content) {
        wp_register_style('wcf_admin_css', WCF_PLUGIN_URL . '/css/wcfadmin.css');
        wp_enqueue_style('wcf_admin_css');


        wp_register_style('font_awesome', WCF_PLUGIN_URL . '/css/font-awesome/css/font-awesome.min.css');
        wp_enqueue_style('font_awesome');

        wp_register_script('wcf_admin_js', WCF_PLUGIN_URL . '/js/wcfadmin.js', array(), '', true);
        wp_enqueue_script("wcf_admin_js");
    }

    /**
     * loads scripts to all pages on the front.
     */
    static function enqueue_wcf_scripts() {

        wp_register_style('wcf_client_css', WCF_PLUGIN_URL . '/css/wcfclient.css');
        wp_enqueue_style('wcf_client_css');
        wp_enqueue_script('wcf_client_js', WCF_PLUGIN_URL . '/js/wcfclient.js', array(), '', true);
        wp_enqueue_script("wcf_client_js");
        wp_localize_script('wcf_client_js', 'WCFAjax', array('ajaxurl' => admin_url('admin-ajax.php')));



        // reCaptcha TODO: checken vor einbindung
        wp_register_script("recaptcha", "https://www.google.com/recaptcha/api.js");
        wp_enqueue_script("recaptcha");

        //$plugin_url = plugin_dir_url(__FILE__);
        //wp_enqueue_style("no-captcha-recaptcha", $plugin_url ."style.css");
    }

    /**
     * creates a nonce for a user form.
     * 
     * @param type $content
     * @return type
     */
    static function add_nonce_to_wcf_form($content) {
        $noncence = wp_create_nonce('wcf-nonce');
        $content = str_replace("[wcf_key_nonce]", $noncence, $content);
        return $content;
    }

}
