<?php
namespace wcf_coolform;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * creates a new form page for wordpress.
 */
class WpFormPageCreator {

    var $formId;

    /**
     * generates a new form page for word press and reutrns the post id.
     * 
     * @param type $formId
     * @return type
     */
    function generatePage($formId) {
        $this->formId = $formId;
        $sql = new WCFSQLHandler();
        //$ini = new WcfIniFilesHandler($formId);
        $form = $sql->getFormular($formId);
        $content = $this->generateCode($formId);
        $postId = $this->createPage($form[0]['form_name'], $content);
        $sql->updateFormPost($formId, $postId);
        return $postId;
    }

    /**
     * generates the formular input code.
     * 
     * 
     * @param type $formId
     * @return type
     */
    function generateCode($formId) {
        $sql = new WCFSQLHandler();
        $ini = new WcfIniFilesHandler($formId);
        $imageCols = $ini->readFormFieldsImageIni();
        $form = $sql->getFormular($formId);
        $fields = $ini->readFormFieldsIni();
        return $this->generateFormContent($form[0]['form_name'], $formId, array_values($fields), $imageCols);
    }

    function displayRecaptcha($sitekey) {
        $ret = '<div class="g-recaptcha" data-sitekey="' . $sitekey . '"></div>';
    }

    /**
     * this function creates a wordpress page.
     * 
     * @param type $formname
     * @param type $id
     * @param type $fieldnames
     * @return type
     */
    function generateFormContent($formname, $id, $fieldnames, $imgCols) {
        $formHandler = new WCFSQLHandler();
        $theFormRows = $formHandler->getFormular($id);
        $useCaptcha = $theFormRows[0]['use_captchas'] == 1;
        $usereCaptcha = $theFormRows[0]['use_recaptchas'] == 1;
        $sitekey = $theFormRows[0]['recaptcha_key'];
        $sitesecret = $theFormRows[0]['recaptcha_secret'];
        $wcf_css = $formHandler->getParam($id, "wcf_css");
        $inline_css = $formHandler->getParam($id, "inline_css");
        $ht = new WcfHtmlHelper();
        $header = getPathNewFormPostHeaderTemplate();
        $footer = getPathNewFormPostFooterTemplate();
        $contentHeader = file_get_contents($header);

        $theAjaxUrl = admin_url('admin-ajax.php');
        $contentHeader = str_replace("ajaxurl", $theAjaxUrl, $contentHeader);

        $contentFooter = file_get_contents($footer);
        $form = ""; 

        // TODO: insert with enqueue ...
        //$form .= "<style>$inline_css</style>";
        if (empty($wcf_css)) {
            $form .= "<div class='wcf_css_1'>";
        } else
        if ($wcf_css !== 'wcf_css_none') {
            $form .= "<div class='$wcf_css'>";
        }
        $form .= "<form method='POST' action='" . admin_url('admin-post.php') . "' name='" . $id . "' enctype='multipart/form-data' class='form-horizontal' role='form' id='wcf_user_form'>";
        $action = $useCaptcha ? "handle_wcf_form_ajax" : "handle_wcf_form_standard";
        $form .= '<input type="hidden" name="action" value="' . $action . '">';
        $form .= '<input type="hidden" name="key" value="[wcf_key_nonce]">';
        $form .= '<input type="hidden" name="form_id" value="' . $id . '">';
        if ($formHandler->getParam($id, 'hidden_form_data')) {
            $form .= "[wcf_hidden_pres]";
        }
        $i = 0;

        //$form .= $this->generateFormFields($id);
        $form .= $this->generateFormFieldsXML($id);
        $i = 0;
        /* obsolet
        if (!empty($imgCols)) {
            $form .= "Images:<p>";
            $form .= '<fieldset>';
            foreach ($imgCols as $col => $name) {
                $form .= "<p><label for='img_" . $id . "_" . $name . "'>" . $name . ":</label></p>";
                $form .= "<input type='file' name='img_" . $id . "_" . $name . "' id='img_" . $id . "_" . $name . "' accept='image/x-png, image/gif, image/jpeg'><p>";

                $i++;
            }
            $form .= '</fieldset>';
        }
        */
        if ($useCaptcha) {
            $form .= $ht->addPTag("[wcf_captcha]");
            $form .= $ht->addPTag("[wcf_captcha_input_field]");
        } else if ($usereCaptcha) {
            $form .= $this->displayRecaptcha($sitekey);
        }
        $form .= '<button onclick="return resetForm();" id="wcf_submit">Formular zurücksetzen</button>';
        if ($useCaptcha) {
            $form .= '<button onclick="return submitForm();" id="wcf_submit">Formular anlegen</button>';
        } else {
            $form .= '<button onclick="return submitForm();" id="wcf_submit">Formular anlegen</button>';
        }

        $form .= "</form>";
        if ($wcf_css !== 'wcf_css_none') {
            $form .= "</div>";
        }
        return $contentHeader . $form . $contentFooter;
        //return $this->createPage($formname, $content);
    }

    /**
     * TODO: umstellen auf xml => mein trainjob ...
     * 
     * @param type $id
     * @return string
     */
    function generateFormFieldsXML($id) {
        $form = "";
        $ini = new WcfIniFilesHandler($id);
        $xml = new WcfXmlHandler($ini->getXMLIniFile());
        $fields = $xml->getFields();
        $containers = $xml->getContainers();
        foreach ($containers as $container) {
            $form .= $this->container2html($id, $container, $fields);
        }
        return $form;
    }

    /**
     * setzt einen konkreten container um.
     * 
     * 
     * @param type $container
     * @param type $fields
     * @return type
     */
    function container2html($formId, $container, $fields) {
        $form = "";
        $containerid = $container->getId();
        $headline = $container->getHeadline();
        $cols = $container->getColumns();
        list($hl, $conti, $colis, $ci) = explode("_", trim($containerid) . "_");

        if (startsWith($containerid, "headline_")) {
            $outerContainerId = str_replace("headline_", "", $containerid);
        } else {
            $outerContainerId = $containerid;
        }
        $form .= "<div class='container" . $cols . "'>";
        // hier die headline
        $form .= "<fieldset><legend>" . $headline . "</legend>";
        $form .= $this->getBlocks($formId, $containerid, $fields);

        $form .= '</fieldset><br style="clear: left;" />';
        $form .= "</div>";
        return $form;
    }

    function getBlocks($formId, $containerid, $fields) {
        $html = "";
        list($hl, $conti, $colis, $ci) = explode("_", trim($containerid) . "_");
        if ($colis === "1") {
            $html .= $this->addBlock("block-1-1", "container_1_1_" . $ci, $fields);
        } else if ($colis === "2") {
            $html .= $this->addBlock("block-2-1", "container_2_1_" . $ci, $fields);
            $html .= $this->addBlock("block-2-2", "container_2_2_" . $ci, $fields);
        } else if ($colis === "3") {
            $html .= $this->addBlock("block-3-1", "container_3_1_" . $ci, $fields);
            $html .= $this->addBlock("block-3-2", "container_3_2_" . $ci, $fields);
            $html .= $this->addBlock("block-3-3", "container_3_3_" . $ci, $fields);
        }
        return $html;
    }

    private function addBlock($blockClass, $fieldContainerId, $fields) {
        $html = "<div class='" . $blockClass . "'>";
        for ($i = 0; $i < count($fields); $i++) {
            if ($fieldContainerId == $fields[$i]->getContainer()) {
                $html .= $this->field2html($fields[$i]);
            }
        }
        $html .= "</div>";
        return $html;
    }

    /**
     * setzt ein konkretes feld um
     * @param type $field
     * @return string
     */
    function field2html($field) {
        $html = "";
        // Tooltip:
        $tooltip = $field->getTooltip();
        $html .= "<div title='". $tooltip . "'>";
        $mandatory = $field->getMandatory();
        $col = "column" . $field->getId();
        if ($mandatory == 'true') {
            $mandatory = " required";
        } else {
            $mandatory = "";
        }
        $label = $field->getLabel();
        $html .= "<label class='control-label col-sm-2' for='" . $this->formId . "_" . $col . "'>" . $label . ":</label>";

        $type = $field->getType()->getHtmlType();
        wcflog("schreibe html, hier der type: " . $type);
        if ($type === "choicebox") {
            $html .= "<select class='" . $field->getCSS() . "'  name='" . $this->formId . "_" . $col . "'" . $mandatory . ">";
            $vals = $field->getType()->getValues();
            foreach ($vals as $val) {
                $html .= "<option>" . $val . "</option>";
            }
            $html .= "</select>";
        } else if ($type === "radio") {
            $selected = " selected";
            $vals = $field->getType()->getValues();
            foreach ($vals as $val) {
                $html .= "<input class='" . $field->getCSS() . "' type='" . $type . "' name='" . $this->formId . "_" . $col . "' value='" . $val . "' $selected>";
                $html .= "" . $val . "<br/>";
                $selected = "";
            }
        } else if ($type === "textarea") {
            $html .= "<textarea name='" . $this->formId . "_" . $col . "' $mandatory>$val</textarea>";
        } else if ($type === "file") {
            $idname = "img_".$this->formId."_".$field->getName();
            $html .= "<input type='file' id='".$idname."' name='".$idname."' accept='image/x-png, image/x-gif,, image/x-jpeg'>";
        } else {
            wcflog("genau diesen typ schreibe ich: " . $type);
            $placeholder = $field->getPlaceholder();
            $html .= "<input class='" . $field->getCSS() . "' type='" . $type . "' placeholder='" . $placeholder . "' name='" . $this->formId . "_" . $col . "'" . $mandatory . ">";
        }
        $html .= "</div>";
        return $html;
    }

    
    /**
     * scheinbar tote funktion ...
     * 
     * @param type $id
     * @return string
     */
    function generateFormFields($id) {
        $ini = new WcfIniFilesHandler($id);
        $fielddefs = $ini->readFormFieldsSettingsIni();
        $form = "";
        // 1. Block oeffnen:
        $form .= '<fieldset>';
        foreach ($fielddefs as $col => $vals) {
            $mandatory = $vals['mandatory'];
            if ($mandatory === 'true') {
                $mandatory = " required";
            } else {
                $mandatory = "";
            }
            $html = new WcfHtmlHelper();


            // TODO: alle typinformationen an eine stelle sonst potentielle fehlerquelle!!!


            $type = $vals['type'];
            if ($type === 'Email') {
                $type = 'email';
            } else if ($type === 'Url') {
                $type = 'url';
            } else if ($type === tr('number')) {
                $type = 'number';
            } else if ($type === tr('password')) {
                $type = 'password';
            } else if ($type === tr('date')) {
                $type = 'date';
            } else if ($type === tr('radio')) {
                $type = 'radio';
            } else {
                $type = "text";
            }
            $lines = $vals['lines'];
            $label = $vals['label'];
            $values = $vals['values'];
            $format = $vals['format'];
            if (tr('normal') != $format) {
                // letzten block schliessen
                $form .= '</fieldset>';
                // neuen Block oeffnen
                $form .= '<fieldset>';
            }
            if (tr('single_block') === $format) {
                $form .= "<div class='wcf-single-block'>";
            } else if (tr('new_block') === $format) {
                $form .= "<div class='wcf-new-block'>";
            } else {
                $form .= "<div class='wcf-label-input'>";
            }
            $form .= "<label class='control-label col-sm-2' for='" . $id . "_" . $col . "'>" . $label . ":</label>";

            if (!empty($values)) {
                $form .= "<select name='" . $id . "_" . $col . "' id='" . $id . "_" . $col . "'" . $mandatory . ">";
                $vals = split(",", $values);
                foreach ($vals as $option) {
                    $form .= "<option value='" . $option . "'>" . $option . "</option>";
                }
                $form .= "</select><p></p>";
            } else if (!empty($lines) && $lines > 1) {
                $form .= "<textarea name='" . $id . "_" . $col . "'" . $mandatory . " cols='" . $lines . "'></textarea>";
            } else {
                $form .= "<input type='" . $type . "' name='" . $id . "_" . $col . "'" . $mandatory . ">";
            }
            $form .= "</div>";
            if (tr('single_block') === $format) {
                // letzten block schliessen
                $form .= '</fieldset>';
                // neuen Block oeffnen
                $form .= '<fieldset">';
            }
        }
        // letzten block schliessen.
        $form .= "</fieldset>";
        //wcflog("the generated form: " . $form);
        return $form;
    }

    function generateFormFieldsOld($id, $fieldnames) {
        $ini = new WcfIniFilesHandler($id);
        $fielddefs = $ini->readFormFieldsSettingsIni();
        $form = "";
        $i = 0;
        foreach ($fieldnames as $field) {
            if (strpos($field, "#") !== false) {
                $fieldname = substr($field, 0, strpos($field, "#"));
                $ss = substr($field, strpos($field, "#") + 1, strlen($field) - 1);
                $vals = split(";", $ss);
                $form .= "<div class='form entry'><div class='form label'><label for='" . $id . "_column" . $i . "'>" . $fieldname . ":</label></div><div class='form input'><select name='" . $id . "_column" . $i . "' id='" . $id . "_column" . $i . "'>";
                foreach ($vals as $option) {
                    $form .= "<option value='" . $option . "'>" . $option . "</option>";
                }
                $form .= "</select></div></div></p>";
            } else {
                $form .= "<div class='form entry'><div class='form label'><label for='" . $id . "_column" . $i . "'>" . $field . ":</label></div><div class='form input'><input type='text' name='" . $id . "_column" . $i . "' id='" . $id . "_column" . $i . "'></div></div><p>";
            }
            $i++;
        }
        return $form;
    }

    /**
     * todo. radio und auswahl fehlen noch, checkbox auch
     * alles ein bisschen schicker.
     * 
     * 
     * @param type $id
     * @param type $fieldnames
     * @param type $values
     * @return string
     */
    function generateFormFieldsWithValuesNew($id, $fieldnames, $values) {
        $form = "";
        foreach ($fieldnames as $field) {
            $key = 'column' . $field->getId();
            
            $form .= "<div class='form entry'><div class='form label'><label for='" . $id . "_column" . $field->getId() . "'>" . $field->getLabel() . ":</label></div><div class='form input'><input type='text' value='" . $values[$key] . "' name='" . $id . "_column" . $field->getId() . "' id='" . $id . "_column" . $field->getId() . "'></div></div><p>";
        }
        return $form;
    }

    function generateFormFieldsWithValues($id, $fieldnames, $values) {
        $form = "";
        $i = 0;
        foreach ($fieldnames as $field) {
            $key = 'column' . $i;
            if (strpos($field, "#") !== false) {
                $fieldname = substr($field, 0, strpos($field, "#"));
                $ss = substr($field, strpos($field, "#") + 1, strlen($field) - 1);
                $vals = split(";", $ss);
                $form .= "<div class='form entry'><div class='form label'><label for='" . $id . "_column" . $i . "'>" . $fieldname . ":</label></div><div class='form input'><select name='" . $id . "_column" . $i . "' id='" . $id . "_column" . $i . "'>";
                foreach ($vals as $option) {
                    if ($option === $values[$key]) {
                        $form .= "<option value='" . $option . "' selected>" . $option . "</option>";
                    } else {
                        $form .= "<option value='" . $option . "'>" . $option . "</option>";
                    }
                }
                $form .= "</select></div></div></p>";
            } else {
                $form .= "<div class='form entry'><div class='form label'><label for='" . $id . "_column" . $i . "'>" . $field . ":</label></div><div class='form input'><input type='text' value='" . $values[$key] . "' name='" . $id . "_column" . $i . "' id='" . $id . "_column" . $i . "'></div></div><p>";
            }
            $i++;
        }
        return $form;
    }

    /**
     * creates a new wordpress form page.
     * 
     * 
     * @global type $user_ID
     * @param type $formname
     * @param type $content
     * @return string
     */
    private function createPage($formname, $content) {
        global $user_ID;
        $page['post_type'] = 'page';
        $page['post_content'] = $content;
        $page['post_parent'] = 0;
        $page['post_author'] = $user_ID;
        $page['post_status'] = 'publish';
        $page['post_title'] = $formname;
        $pageid = wp_insert_post($page);
        if ($pageid == 0) {
            return 'false';
        }
        return $pageid;
    }

}
