<?php
namespace wcf_coolform;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Neues Formular ajax ...
 */
function upload_form_action_callback() {
    $handler = new WCFNewFormFormHandler();
    $handler->uploadNewForm();
}

/**
 * wird beim form speichern aufgerufen.
 * 
 * 
 */
function handle_settings_form_action_callback() {
    $form_id = wcfpost('form_id');
    $settingsHandler = new WCFFormSettingsHandler();
    $settingsHandler->setFormId($form_id);
    $settingsHandler->saveFormSettings();
    wp_die();
}

/**
 * 
 * @param type $a
 * @param type $b
 */
function rang_sort($a, $b) {
    return $a['wcf_rang'] > $b['wcf_rang'] ? -1 : 1;
}

/**
 * all functions to create a form settings page.
 * 
 * 
 */
class WCFFormSettingsHandler {

    var $formId;
    var $html = null;
    var $sql = null;
    var $fields;
    var $containers;

    /**
     * displays the settings page in the backend admin area of wordpress.
     */
    public function showSettingsPage() {

        echo $this->getSettingsHeader();

        
        echo $this->getPreCFForms();
        echo $this->getFormStart();
        echo $this->getCFForms();
        echo $this->getFormEnd();
    }

    protected function getFormularUploads() {

        return $this->addBox($this->getFormularUploadForm());
    }

    protected function getPreCFForms() {
        $ret = $this->addBox($this->getGenerateForm());
        if (WP_CF_PRO) {
            $ret .= $this->getFormularUploads();
        }
        return $ret;
    }

    /**
     * creates form inputs for hidden fields.
     * wurden vom vorgaenger uebergeben. hierdurch koennen verkettete forms 
     * erzeugt werden.
     * @return string
     */
    protected function addHiddenPres() {
        $ret = "";
        foreach ($_GET as $k => $v) {
            if (startsWith(sep($k), "pre_")) {
                $kk = cleaninput($k);
                $vv = cleaninput($v);
                $ret .= '<input type="hidden" name="' . $kk . '" value="' . $vv . '">';
            }
        }
        return $ret;
    }

    protected function getCFForms() {
        $ret = $this->addHiddenPres();
        $ret .= $this->addBox($this->getFormularForm());
        $ret .= $this->addBox($this->getEmailSettingsForm());
        $ret .= $this->addBox($this->getRecaptchaSettingsForm());
        //$ret .= $this->addBox($this->getSpecialFields());
        $ret .= $this->addBox($this->getDateField());
        //$ret .= $this->addBox($this->getExportImport());
        $ret .= $this->addBox($this->getDesign());
        if (CB_CO_PRO) {
            $ret .= $this->getCoolBillForm();
        }
        //$ret .= $this->addBigBox($this->getSettingsForm());
        $ret .= $this->addBigBox($this->getFormBuilder());
        return $ret;
    }

    /**
     * can be overwritten for cool bill.
     * 
     * @return type
     */
    protected function getCoolBillForm() {
        return null;
    }
    
    
    
    protected function html() {
        if (!isset($this->html)) {
            $this->html = new WcfHtmlHelper();
        }
        return $this->html;
    }
    
    protected function sql() {
        if (!isset($this->sql)) {
            $this->sql = new WCFSQLHandler();
        }
        return $this->sql;
    }

    function setFormId($id) {
        $this->formId = $id;
    }

    function getSettingsHeader() {        
        $ret = "";
        $sql = new WCFSQLHandler();
        $fdata = $sql->getFormular($this->formId);
        $header_template = file_get_contents(getPathFormSettingsHeader());
        $header_template = str_replace("[wcf_id]", "[wcf_" . $this->formId . "]", $header_template);
        $header_template = str_replace("[FORM_ID]", $this->formId, $header_template);
        $header_template = str_replace("[PAGE_URL]", get_site_url(), $header_template);
        if (isset($fdata[0]['post_id']) && !empty($fdata[0]['post_id'])) {
            $header_template = str_replace("[FORM_PAGE_URL]", get_page_link($fdata[0]['post_id']), $header_template);
        }
        $header_template = str_replace("[WCF_LOGO]", WCF_LOGO, $header_template);

        $ret .= $header_template;
        $ret .= "<p>";
        return $ret;
    }

    function getToggleIcon() {
        return '<span class="dashicons dashicons-sort"></span>&nbsp;&nbsp;';
    }

    function getSpecialFields() {
        $sql = new WCFSQLHandler();
        //$form = $handler->getFormular($this->formId);
        $invoice = $sql->getParam($this->formId, 'invoice_no');
        $random_id = $sql->getParam($this->formId, 'random_id');

        $ht = new WcfHtmlHelper();
        $ret = $ht->addH2Tag($this->getToggleIcon() . tr('headline_settings_special'), 'wcf-special');
        $ret .= '<div class="wcf-special-toggle">' . $ht->addPTag("*" . tr('hint_special_key_replacement'));

        $retm = $ht->addTrTag($ht->addTdTag(tr('label_invoice_no') . ":") . $ht->addTdTag("<input type='number' name='invoice_no' value='" . $invoice . "'>"));
        $retm .= $ht->addTrTag($ht->addTdTag(tr('label_random_id') . ":") . $ht->addTdTag("<input type='text' name='random_id' value='" . $random_id . "'>"));
        $retm = $ht->addTableTag($retm);
        $retm .= $ht->addPTag(tr('hint_custom_field'));
        return $ret . $retm . $ht->addPTag('<input type="submit" value="' . tr('save') . '">') . "</div>";
    }

    /**
     * creates the separate new wordfile upload form.
     * 
     * @return string
     */
    function getFormularUploadForm() {
        $ht = $this->html();
        $ret = $ht->addH2Tag($this->getToggleIcon() . tr('headline_formular_upload'), 'wcf-file-upload');
        $ret .= '<div class="wcf-file-upload-toggle">' . $ht->addPTag("*" . tr('hint_formular_upload'));

        $ret .= "<form method='POST'  enctype='multipart/form-data' id='upload_form' >";
        $ret .= ' <input type="hidden" name="action" value="upload_form_action">';
        $ret .= ' <input type="hidden" name="form_id" value="' . $this->formId . '">';
        $ret .= tr('label_form_upload') . ":" . '<input type="file" id="doc_file" name="doc_file">';
        $ret .= $ht->addPTag("<input type='submit' value='" . tr('label_upload') . "' onclick='return ajax_upload();' name='upload_form'>");
        $ret .= "</form></div>";
        return $ret;
    }

    function getDesign() {
        $sql = new WCFSQLHandler();
        $html = $this->html();
        $wcf_css = $sql->getParam($this->formId, 'wcf_css');
        $inline_css = $sql->getParam($this->formId, 'inline_css');
        $ret = $html->addH2Tag($this->getToggleIcon() . tr('headline_design'), 'wcf-design');
        //$ret .= '<div class="wcf-design-toggle">' . $html->addDivTagTitle(tr('label_design'), tr('title_design'));
        $ret .= '<div class="wcf-design-toggle">' . $html->addPTag(tr('label_design'));


        /**

            // hier koennen verschiedene css designvorlagen angeboten werden
         * // evtl. in version 2.
            
        $ret .= "<ul>";
        $checked = "";
        // CSS 1
        if ($wcf_css === 'wcf_css_1') {
            $checked = 'checked';
        } else {
            $checked = '';
        }
        $label = $html->addLabelTag('Design 1');
        $ret .= $html->addLiTag($label . "<input type='radio' name='wcf_css' value='wcf_css_1' " . $checked . ">");

        // CSS 2
        if ($wcf_css === 'wcf_css_2') {
            $checked = 'checked';
        } else {
            $checked = '';
        }
        $label = $html->addLabelTag('Design 2');
        $ret .= $html->addLiTag($label . "<input type='radio' name='wcf_css' value='wcf_css_2' " . $checked . ">");

        // CSS 3
        if ($wcf_css === 'wcf_css_3') {
            $checked = 'checked';
        } else {
            $checked = '';
        }
        $label = $html->addLabelTag('Design 3');
        $ret .= $html->addLiTag($label . "<input type='radio' name='wcf_css' value='wcf_css_3' " . $checked . ">");

        // none
        if ($wcf_css === 'wcf_css_none') {
            $checked = 'checked';
        } else {
            $checked = '';
        }
        $label = $html->addLabelTag(tr('no_css'));
        $ret .= $html->addLiTag($label . "<input type='radio' name='wcf_css' value='wcf_css_none' " . $checked . ">");

        */

        $ret .= $html->addLabelTag(tr('inline_css'));
        $ret .= $html->addTextarea('inline_css', $inline_css);

        return $ret . $this->getSubmitButton() . "</div>";
    }

    function getDateField() {
        $checked = "";
        $sql = new WCFSQLHandler();
        $df = $sql->getParam($this->formId, 'date_format');
        $html = $this->html();
        $ret = $html->addH2Tag($this->getToggleIcon() . tr('headline_date_format'), 'wcf-date-format');
        $ret .= '<div class="wcf-date-format-toggle">' . $html->addDivTagTitle(tr('label_date_format'), tr('title_date_format'));
        $ret .= "<ul>";

        if ($df === 'd.m.yy') {
            $checked = 'checked';
        }
        $label = $html->addLabelTag('19.06.2015');
        $ret .= $html->addLiTag($label . "<input type='radio' name='date_format' value='d.m.yy' " . $checked . ">");

        if ($df === 'd.m.y') {
            $checked = 'checked';
        } else {
            $checked = '';
        }
        $label = $html->addLabelTag('19.06.15');
        $ret .= $html->addLiTag($label . "<input type='radio' name='date_format' value='d.m.y' " . $checked . ">");

        if ($df === 'm/d/yy') {
            $checked = 'checked';
        } else {
            $checked = '';
        }
        $label = $html->addLabelTag('06/19/2015');
        $ret .= $html->addLiTag($label . "<input type='radio' name='date_format' value='m/d/yy' " . $checked . ">");

        if ($df === 'm/d/y') {
            $checked = 'checked';
        } else {
            $checked = '';
        }
        $label = $html->addLabelTag('06/19/15');
        $ret .= $html->addLiTag($label . "<input type='radio' name='date_format' value='m/d/y' " . $checked . ">");

        if ($df === 'd/m/yy') {
            $checked = 'checked';
        } else {
            $checked = '';
        }
        $label = $html->addLabelTag('19/12/2015');
        $ret .= $html->addLiTag($label . "<input type='radio' name='date_format' value='d/m/yy' " . $checked . ">");

        if ($df === 'd/m/y') {
            $checked = 'checked';
        } else {
            $checked = '';
        }
        $label = $html->addLabelTag('19/12/15');
        $ret .= $html->addLiTag($label . "<input type='radio' name='date_format' value='d/m/y' " . $checked . ">");


        $ret .= "</ul>";
        $ret .= $this->getSubmitButton();
        $ret .= "</div>";
        return $ret;
    }

    function getExportImport() {
        $html = $this->html();
        $ret = $html->addH2Tag($this->getToggleIcon() . tr('headline_export_import'), 'wcf-export-import');
        $ret .= '<div class="wcf-export-import-toggle">' . $html->addDivTagTitle(tr('label_export_import'), tr('title_export_import'));

        return $ret . "</div>";
    }

    function getGenerateForm() {
        $sql = new WCFSQLHandler();
        $form = $sql->getFormular($this->formId);

        $html = $this->html();
        $ret = $html->addH2Tag($this->getToggleIcon() . tr('headline_generate_form'), 'wcf-generate-form');
        $ret .= '<div class="wcf-generate-form-toggle">' . $html->addDivTagTitle(tr('label_generate_form'), tr('title_generate_form'));


        $ret .= "<a href='" . get_site_url() . "?create_new_form_page_form_id=" . $this->formId . "' class='button'>" . tr('generate_form') . "</a>";
        if (isset($form[0]['post_id']) && !empty($form[0]['post_id'])) {
            $ret .= "&nbsp;&nbsp;<a href='" . get_page_link($form[0]['post_id']) . "' class='button'>" . tr('show_form') . "</a>";
        }
        $ret .= $html->addPTag(tr('hint2_generate_form') . '[wcf_' . $this->formId . ']');
        return $ret . "</div>";
    }

    function getRecaptchaSettingsForm() {
        $handler = new WCFSQLHandler();
        $form = $handler->getFormular($this->formId);
        $ht = new WcfHtmlHelper();


        $reth = $ht->addH2Tag($this->getToggleIcon() . tr('headline_formular_recaptcha'), 'wcf-captcha');
        $reth .= '<div class="wcf-captcha-toggle">' . $ht->addPTag("*" . tr('hint_formular_recaptcha'));
        $useCaptchas = $form[0]['use_recaptchas'] == 1;
        if ($useCaptchas) {
            $checked = " checked";
        } else {
            $checked = "";
        }
        $ret = $ht->addTrTag($ht->addTdTag(tr('label_use_recaptchas') . ":") . $ht->addTdTag("<input type='checkbox' name='use_recaptchas' value='true' " . $checked . ">"));
        $ret .= $ht->addTrTag($ht->addTdTag(tr('label_form_recaptcha_key') . ":") . $ht->addTdTag("<input type='text' name='recaptcha_key' value='" . $form[0]['recaptcha_key'] . "'>"));
        $ret .= $ht->addTrTag($ht->addTdTag(tr('label_form_recaptcha_secret') . ":") . $ht->addTdTag("<input type='text' name='recaptcha_secret' value='" . $form[0]['recaptcha_secret'] . "'>"));


        $useCaptchas = $form[0]['use_captchas'] == 1;
        if ($useCaptchas) {
            $checked = " checked";
        } else {
            $checked = "";
        }

        $ret .= $ht->addTrTag($ht->addTdTag(tr('label_use_captchas') . ":") . $ht->addTdTag("<input type='checkbox' name='use_captchas' value='true' " . $checked . ">"));

        $ret = $ht->addTableTag($ret);
        //return $reth . $ret . $ht->addPTag('<input type="submit" value="' . tr('save') . '"></div>');
        return $reth . $ret . $this->getSubmitButton() . '</div>';
    }

    function getEmailSettingsForm() {
        $handler = new WCFSQLHandler();
        $form = $handler->getFormular($this->formId);
        $ht = new WcfHtmlHelper();
        $ret = $ht->addH2Tag($this->getToggleIcon() . tr('headline_settings_email'), 'wcf-email');
        $ret .= '<div class="wcf-email-toggle">' . $ht->addPTag("*" . tr('hint_email_key_replacement'));

        $retm = $ht->addTrTag($ht->addTdTag(tr('label_receiver') . ":") . $ht->addTdTag("<input type='email' name='receiver_email' value='" . $form[0]['receiver_email'] . "'>"));
        $retm .= $ht->addTrTag($ht->addTdTag(tr('label_subject') . ":") . $ht->addTdTag("<input type='text' name='subject' value='" . $form[0]['subject'] . "'>"));
        $retm .= $ht->addTrTag($ht->addTdTag(tr('label_message') . ":") . $ht->addTdTag("<textarea name='body'>" . $form[0]['body'] . "</textarea>"));
        //$ret .= $ht->addTrTag($ht->addTdTag("Worddatei:") . $ht->addTdTag("<input type='file' name='doc_file'>"));
        $retm = $ht->addTableTag($retm);
        //return $ret . $retm . $ht->addPTag('<input type="submit" value="' . tr('save') . '"></div>');
        return $ret . $retm . $this->getSubmitButton() . '</div>';
    }

    function getFormularForm() {
        $handler = new WCFSQLHandler();
        $append = $handler->getParam($this->formId, 'append_form_data');
        $hidden = $handler->getParam($this->formId, 'hidden_form_data');
        $form = $handler->getFormular($this->formId);
        $ht = new WcfHtmlHelper();
        $reth = $ht->addH2Tag($this->getToggleIcon() . tr('headline_formular_data'), 'wcf-formdata');
        $reth .= '<div class="wcf-formdata-toggle">' . $ht->addPTag("*" . tr('hint_formular_data'));

        if ($append === 'true') {
            $checked = "checked";
        } else {
            $checked = "";
        }
        $ret = $ht->addTrTag($ht->addTdTag(tr('label_form_name') . ":") . $ht->addTdTag("<input type='text' name='form_name' value='" . $form[0]['form_name'] . "'>"));
        $ret .= $ht->addTrTag($ht->addTdTag(tr('label_url_redirection') . ":") . $ht->addTdTag("<input type='url' name='redirect' value='" . $form[0]['redirect'] . "'>"));
        $ret .= $ht->addTrTag($ht->addTdTag(tr('label_append_form_data') . ":") . $ht->addTdTag("<input type='checkbox' name='append_form_data' value='true' " . $checked . ">"));

        if ($hidden === 'true') {
            $checked = "checked";
        } else {
            $checked = "";
        }
        $ret .= $ht->addTrTag($ht->addTdTag(tr('label_hidden_form_data') . ":") . $ht->addTdTag("<input type='checkbox' name='hidden_form_data' value='true' " . $checked . ">"));

        $ret = $ht->addTableTag($ret);
        $rets = $ht->addPTag(tr('hint_hidden_form_data'));

        //return $reth . $ret . $rets . $ht->addPTag('<input type="submit" value="' . tr('save') . '"></div>');
        return $reth . $ret . $rets . $this->getSubmitButton() . "</div>";
    }

    function getSecurityForm() {
        /*
          $retm .= $ht->addH2Tag(tr('headline_settings_securtiy'));

          $rets = $ht->addTrTag($ht->addTdTag(tr('label_role_write') . ":") . $ht->addTdTag("<input type='text' name='role_write' value='" . $form[0]['role_write'] . "'>"));
          $rets .= $ht->addTrTag($ht->addTdTag(tr('label_role_read') . ":") . $ht->addTdTag("<input type='text' name='role_read' value='" . $form[0]['role_read'] . "'>"));
          $rets = $ht->addTableTag($rets);
         */
    }

    function getSettingsForm() {

        $ret = "<h2>" . tr('label_formular_fields') . "</h2>";
        $ret .= '<p>*' . tr('hint_order_drag_drop') . '</P>';
        return $ret . $this->getSettingsTable();
    }

    function getFormEnd() {
        $hint = "<p>**: " . tr('hint_possible_values') . "</p>";
        //return "<input type='submit' name='" . tr('change') . "'></form>";
        return "</form>";
    }

    function getFormStart() {
        $ret = "<form method='POST'  enctype='multipart/form-data' id='save_form_form'>";
        $ret .= "<input type='hidden' name='page' value='set_" . $this->formId . "' >";
        $ret .= "<input type='hidden' id='unique_form_id' name='form_id' value='" . $this->formId . "' >";
        $ret .= "<input type='hidden' name='save_form' value='true' >";
        $ret .= "<input type='hidden' name='action' value='handle_settings_form_action' >";

        return $ret;
    }

    /**
     * baut den neuen Form Builder auf.
     * 
     * 
     * @return type
     */
    function getFormBuilder() {
         $ini = new WcfIniFilesHandler($this->formId);
        $xmlFile = $ini->getXMLIniFile();
        $xml = new WcfXmlHandler($xmlFile);
        $fields = $xml->getFields();
        $containers = $xml->getContainers();
        $pagePrefix = "admin.php?page=set_";
        $formBuilder = new WcfFormBuilder($this->formId,$fields,$containers,$pagePrefix);
        return  $formBuilder->getFormBuilder() . $this->getSubmitButton();
    }

    function getSettingsTable() {
        $ht = new WcfHtmlHelper();
        $tab = $ht->addThTagTitle(tr('name'), tr('tooltip_name')) . $ht->addThTagTitle(tr('mandatory'), tr('tooltip_mandatory')) . $ht->addThTagTitle(tr('type'), tr('tooltip_type')) .
                $ht->addThTagTitle(tr('lines'), tr('tooltip_lines')) . $ht->addThTagTitle(tr('label'), tr('tooltip_label')) . $ht->addThTagTitle(tr('values'), tr('tooltip_values')) .
                $ht->addThTagTitle(tr('col_format'), tr('tooltip_format')) .
                $ht->addThTagTitle(tr('col_send_mail'), tr('tooltip_col_send_mail')) . $ht->addThTagTitle(tr('delete'), tr('tooltip_delete'));
        $tab = $ht->addTrTag($tab);
        $inis = new WcfIniFilesHandler($this->formId);
        $colNames = $inis->readFormFieldsIni();
        $colDefs = $inis->readFormFieldsSettingsIni();
        $i = 0;
        $odd = true;
        uasort($colDefs, "rang_sort");
        foreach ($colDefs as $col => $colDef) {


            /*
             * hier nach rang noch sortieren
             */



            $line = $ht->addTdTag("<span class='dashicons dashicons-randomize'></span>&nbsp;&nbsp;&nbsp;&nbsp;" . $colNames[$col]);
            $theHidden = "<input type='hidden' name='" . $col . "_rang' value='" . $i . "' class='wcf_rang'>";
            $line .= $ht->addTdTag($theHidden . $this->getInputMandatory($col . "_mandatory", $colDef['mandatory']));
            $line .= $ht->addTdTag($this->getInputType($col . "_type", $colDef['type']));
            $line .= $ht->addTdTag($this->getInputLines($col . "_lines", $colDef['lines']));
            $line .= $ht->addTdTag($this->getInputLabel($col . "_label", $colDef['label']));
            $line .= $ht->addTdTag($this->getInputValues($col . "_values", $colDef['values']));
            // Format
            $line .= $ht->addTdTag($this->getInputFormat($col . "_format", $colDef['format']));
            // bestaetigungsmail
            $line .= $ht->addTdTag($this->getInputConfirmation($col . "_confirmation", $colDef['confirmation']));
            $line .= $ht->addTdTag($this->getInputRemove($col . "_remove"));
            $tab .= $ht->addTrTagO($line, $odd) . "\n";
            $odd = !$odd;
            $i++;
        }
        $tab .= $ht->addTrTag($ht->addTdTag(tr('add_field')));
        // add new field:
        $line = $ht->addTdTag($this->getInputLabel("_new_name", ""));
        $line .= $ht->addTdTag($this->getInputMandatory("_new_mandatory", ""));
        $line .= $ht->addTdTag($this->getInputType("_new_type", ""));
        $line .= $ht->addTdTag($this->getInputLines("_new_lines", "1"));
        $line .= $ht->addTdTag($this->getInputLabel("_new_label", ""));
        $line .= $ht->addTdTag($this->getInputValues("_new_values", ""));
        $line .= $ht->addTdTag($this->getInputFormat("_new_format", ""));
        $line .= $ht->addTdTag($this->getInputConfirmation("_new_confirmation", ""));
        $line .= $ht->addTdTag("");
        $tab .= $ht->addTrTag($line);
        return $ht->addTableTag($tab) . $this->getSubmitButton();
    }

    function getSubmitButton() {
        $ht = new WcfHtmlHelper();
        return $ht->addPTag('<input type="submit" value="' . tr('save') . '"  onclick="return ajax_save_settings_form();" >');
    }

    function getInputMandatory($name, $mandy) {
        if ($mandy === 'true') {
            $checked = " checked";
        } else {
            $checked = "";
        }
        return "<input type='checkbox' name='" . $name . "' value='true'" . $checked . ">";
    }

    function getInputRemove($name) {
        return "<input type='checkbox' name='" . $name . "' value='true'>";
    }

    function getInputConfirmation($name, $mandy) {
        if ($mandy === 'true') {
            $checked = " checked";
        } else {
            $checked = "";
        }
        return "<input type='checkbox' name='" . $name . "' value='true'" . $checked . ">";
    }

    function getInputType($name, $type) {
        $ht = new WcfHtmlHelper();
        $ret = $ht->addOptionTag('Text', 'Text', 'Text' === $type);
        $ret .= $ht->addOptionTag('Url', 'Url', 'Url' === $type);
        $ret .= $ht->addOptionTag(tr('number'), tr('number'), tr('number') === $type);
        $ret .= $ht->addOptionTag('Email', 'Email', 'Email' === $type);
        $ret .= $ht->addOptionTag(tr('password'), tr('password'), tr('password') === $type);
        $ret .= $ht->addOptionTag(tr('date'), tr('date'), tr('date') === $type);
        $ret .= $ht->addOptionTag(tr('choice'), tr('choice'), tr('choice') === $type);
        $ret .= $ht->addOptionTag(tr('time'), tr('time'), tr('time') === $type);
        $ret .= $ht->addOptionTag(tr('radio'), tr('radio'), tr('radio') === $type);
        return $ht->addSelectTag($ret, $name);
    }

    function getInputFormat($name, $type) {
        $ht = new WcfHtmlHelper();
        $ret = $ht->addOptionTag(tr('normal'), tr('normal'), tr('normal') === $type);
        $ret .= $ht->addOptionTag(tr('new_block'), tr('new_block'), tr('new_block') === $type);
        $ret .= $ht->addOptionTag(tr('single_block'), tr('single_block'), tr('single_block') === $type);
        return $ht->addSelectTag($ret, $name);
    }

    function getInputLines($name, $lines) {
        return "<input type='number' name='" . $name . "' value='" . $lines . "'>";
    }

    function getInputLabel($name, $label) {
        return "<input type='text' name='" . $name . "' value='" . $label . "'>";
    }

    function getInputValues($name, $values) {
        return "<input type='text' name='" . $name . "' value='" . $values . "'>";
    }

    /**
     * hier wird der form builder scheiss gespeichert.
     */
    protected function saveFormBuilderSettings() {
        $this->containers = array();
        $this->fields = array();
        foreach ($_POST as $key => $value) {

            $key = cleaninput($key);
            $value = cleaninput($value);
            if (startsWith($key, 'headline_container')) {

                $container = new WcfContainer();
                $container->setHeadline($value);
                list($hl, $conty, $cols, $id) = explode("_", trim($key) . "_");
                $container->setColumns($cols);
                $container->setId($key);
                if (!realEmpty($id)) {
                    array_push($this->containers, $container);
                }
            } else
            // handle Field
            if (startsWith($key, 'container_')) {
                //wcflog("container_column gefunden");
                list($feld, $formId, $fieldId) = explode("_", trim($key) . "_");
                $field = $this->getFieldById($fieldId);
                if (!isset($field)) {
                    $field = new WcfField($fieldId);
                    array_push($this->fields, $field);
                }
                $field->setContainer($value);
            } else
            if (startsWith($key, 'containerRang_')) {
                //wcflog("container_column gefunden");
                list($feld, $formId, $fieldId) = explode("_", trim($key) . "_");
                $field = $this->getFieldById($fieldId);
                if (!isset($field)) {
                    $field = new WcfField($fieldId);
                    array_push($this->fields, $field);
                }
                $field->setContainerRang($value);
            } else
            if (startsWith($key, 'containerColumn_')) {
                //wcflog("container_column gefunden");
                list($feld, $formId, $fieldId) = explode("_", trim($key) . "_");
                $field = $this->getFieldById($fieldId);
                if (!isset($field)) {
                    $field = new WcfField($fieldId);
                    array_push($this->fields, $field);
                }
                $field->setContainerColumn($value);
            } else
            if (startsWith($key, 'name_')) {
                list($feld, $formId, $fieldId) = explode("_", trim($key) . "_");
                $field = $this->getFieldById($fieldId);
                if (!isset($field)) {
                    $field = new WcfField($fieldId);
                    array_push($this->fields, $field);
                }
                $field->setName($value);
            } else
            if (startsWith($key, 'label_')) {
                list($feld, $formId, $fieldId) = explode("_", trim($key) . "_");
                $field = $this->getFieldById($fieldId);
                if (!isset($field)) {
                    $field = new WcfField($fieldId);
                    array_push($this->fields, $field);
                }
                $field->setLabel($value);
            } else
            if (startsWith($key, 'placeholder_')) {
                list($feld, $formId, $fieldId) = explode("_", trim($key) . "_");
                $field = $this->getFieldById($fieldId);
                if (!isset($field)) {
                    $field = new WcfField($fieldId);
                    array_push($this->fields, $field);
                }
                $field->setPlaceholder($value);
            } else

            if (startsWith($key, 'tooltip_')) {
                list($feld, $formId, $fieldId) = explode("_", trim($key) . "_");
                $field = $this->getFieldById($fieldId);
                if (!isset($field)) {
                    $field = new WcfField($fieldId);
                    array_push($this->fields, $field);
                }
                $field->setTooltip($value);
            } else
            if (startsWith($key, 'mandatory_')) {
                list($feld, $formId, $fieldId) = explode("_", trim($key) . "_");
                $field = $this->getFieldById($fieldId);
                if (!isset($field)) {
                    $field = new WcfField($fieldId);
                    array_push($this->fields, $field);
                }
                $field->setMandatory($value);
            } else if (startsWith($key, 'confirmationEmail_')) {
                list($feld, $formId, $fieldId) = explode("_", trim($key) . "_");
                $field = $this->getFieldById($fieldId);
                if (!isset($field)) {
                    $field = new WcfField($fieldId);
                    array_push($this->fields, $field);
                }
                $field->setConfirmationEmail($value);
            } else
            if (startsWith($key, 'type_')) {
                list($feld, $formId, $fieldId) = explode("_", trim($key) . "_");
                $field = $this->getFieldById($fieldId);
                if (!isset($field)) {
                    $field = new WcfField($fieldId);
                    array_push($this->fields, $field);
                }
                $tip = new WcfType();
                $tip->setType($value);
                $field->setType($tip);
            } else
            if (startsWith($key, 'val_')) {
                list($feld, $varid, $formId, $fieldId) = explode("_", trim($key) . "_");
                $field = $this->getFieldById($fieldId);
                if (!isset($field)) {
                    $field = new WcfField($fieldId);
                    array_push($this->fields, $field);
                }
                $type = $field->getType();
                $type->addValue($value);
            }
        }
        // und ab hier dann xml basteln.
        $ini = new WcfIniFilesHandler($this->formId);

        $xml = new WcfXmlHandler($ini->getXMLIniFile());
        $xml->writeBoth($this->fields, $this->containers);
    }

    private function getFieldById($id) {
        if (!isset($this->fields))
            return null;
        $cnt = count($this->fields);
        for ($i = 0; $i < $cnt; $i++) {
            $tf = $this->fields[$i];
            if ($tf->getId() === $id) {
                return $tf;
            }
        }
        return null;
    }

     function saveFormSettings() {
         wcflog("in parent::saveformsettings()");
        $this->saveFormBuilderSettings();
        $inis = new WcfIniFilesHandler($this->formId);
        $colNames = $inis->readFormFieldsIni();
        $colDefs = $inis->readFormFieldsSettingsIni();
        $formSets = array();
        foreach ($colNames as $col => $name) {

            $rang = cleaninput(sep($col . '_rang'));
            $mandatory = cleaninput(sep($col . '_mandatory'));
            $type = cleaninput(sep($col . '_type'));
            $format = cleaninput(sep($col . '_format'));
            $lines = cleaninput(sep($col . '_lines'));
            $label = cleaninput(sep($col . '_label'));
            $values = cleaninput(sep($col . '_values'));
            $confirmation = cleaninput(sep($col . '_confirmation'));
            // der delete:
            $delete = cleaninput(sep($col . '_remove'));

            if (!$delete) {
                $formSets[$col] = $this->getParams($name, $mandatory, $type, $lines, $label, $values, $rang, $confirmation, $format);
            }
        }

        // set email stuff
        $sql = new WCFSQLHandler();
        $receiver = cleaninput(sep('receiver_email'));
        $subject = cleaninput(sep('subject'));
        $body = cleaninput(sep('body'));
        // set captcha stuff
        $useCaptchas = cleaninput(sep('use_captchas')) === 'true' ? 1 : 0;
        $useRecaptchas = cleaninput(sep('use_recaptchas')) === 'true' ? 1 : 0;
        $recaptcha_key = cleaninput(sep('recaptcha_key'));
        $recaptcha_secret = cleaninput(sep('recaptcha_secret'));
        $formName = cleaninput(sep('form_name'));
        $redirect = cleaninput(sep('redirect'));



        $sql->updateForm($this->formId, $receiver, $subject, $body, $formName, $redirect, $useCaptchas, $useRecaptchas, $recaptcha_key, $recaptcha_secret);

        $dateFormat = cleaninput(sep('date_format'));
        $sql->saveParam($this->formId, 'date_format', $dateFormat);


        $invoice = cleaninput(sep('invoice_no'));
        $sql->saveParam($this->formId, 'invoice_no', $invoice);

        $rand = cleaninput(sep('random_id'));
        $sql->saveParam($this->formId, 'random_id', $rand);

        $append = cleaninput(sep('append_form_data'));
        $sql->saveParam($this->formId, 'append_form_data', $append);

        $hidden = cleaninput(sep('hidden_form_data'));
        $sql->saveParam($this->formId, 'hidden_form_data', $hidden);

        $css = cleaninput(sep('wcf_css'));
        $sql->saveParam($this->formId, 'wcf_css', $css);

        $incss = cleaninput(sep('inline_css'));
        $sql->saveParam($this->formId, 'inline_css', $incss);
        
        if (CB_CO_PRO) {
            $this->saveCBSettings();
        }
    }

    
    
    
    function addBox($str) {
        if (!isset($str)) {
            return "";
        }
        return "<div id='wcf_box' style='background-color:#FFFFFF;max-width: 600px;margin-left: 20px;margin-top:20px;padding-left: 20px;padding-top:20px;padding-bottom: 20px;padding-right: 20px;'>" . $str . "</div>";
    }

    function addBigBox($str) {
        return "<div id='wcf_box' style='background-color:#FFFFFF;max-width: 1200px;margin-left: 20px;margin-top:20px;padding-left: 20px;padding-top:20px;padding-bottom: 20px;padding-right: 20px;'>" . $str . "</div>";
    }

    function getParams($name, $mandatory, $type, $lines, $label, $values, $rang, $confirmation, $format) {
        $params = array();
        // rang
        $params['rang'] = $rang;
        // mandatory
        if (isset($mandatory)) {
            $mandatory = $mandatory === 'true' ? 'true' : 'false';
        } else {
            $mandatory = 'false';
        }
        $params['mandatory'] = $mandatory;
        // type
        if (empty($type)) {
            $type = 'Text';
        }
        $params['type'] = $type;
        // format
        if (empty($format)) {
            $format = tr('normal');
        }
        $params['format'] = $format;
        // lines
        if (empty($lines)) {
            $lines = '1';
        }
        $params['lines'] = $lines;
        // label
        if (empty($label)) {
            $label = $name;
        }
        $params['label'] = $label;
        // values:
        if (empty($values)) {
            $values = "";
        }
        $params['values'] = $values;
        if (isset($confirmation)) {
            $confirmation = $confirmation === 'true' ? 'true' : 'false';
        } else {
            $confirmation = 'false';
        }
        $params['confirmation'] = $confirmation;
        return $params;
    }

}
