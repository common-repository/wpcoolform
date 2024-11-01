<?php
namespace wcf_coolform;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
class WcfFormBuilder {

    var $formid;
    var $tabcounter = 0;
    var $fields;
    var $containers;
    var $containerFields;
    var $pagePrefix;

    public function __construct($form_id,$fields,$containers,$pagePrefix) {
        $this->formid = $form_id;
        $this->fields = $fields;
        $this->containers = $containers;
        $this->pagePrefix = $pagePrefix;
    }

    /**
     * hier muessen die felder eingelesen werden.
     * 
     * 
     * @return type
     */
    private function getFields() {
        return $this->fields;
    }

    private function getContainers() {
        return $this->containers;
    }

    public function getFormBuilder() {
        $table = new CLCF_HTMLNode('table');
        // thead tag with all headlines
        $thead = new CLCF_HTMLNode("thead");
        $thead->addChild($this->getTrHeadline());
        $table->addChild($thead);
        // the table content.
        $tbody = new CLCF_HTMLNode("tbody");
        $tr = new CLCF_HTMLNode("tr");
        $tr->addChild($this->getTdLeftColumnNodeList());
        $tr->addChild($this->getTdMiddleColumnDropField());
        $tr->addChild($this->getTdRightColumnSettings());
        $table->addChild($tbody->addChild($tr));
        $ret = "<h1>Form Builder</h1>";
        $ret .= "<ul><li>" . tr('hint_form_builder') . "</li>";
        $ret .= "<li>" . tr('hint_form_builder_1') . "</li>";
        $ret .= "<li>" . tr('hint_form_builder_2') . "</li>";
        $ret .= "<li>" . tr('hint_form_builder_3') . "</li></ul>";

        $ret .= $table->html();
        $noDisplay = $this->getNoDisplayNode("hidden_containers");

        $noDisplay->addChild($this->getContainerNode("container_one", "block1", ""))
                ->addChild($this->getContainerNode("container_two", "block2", ""))
                ->addChild($this->getContainerNode("container_three", "block3", ""))
        ;


        $ret .= $noDisplay->html();

        return $ret;
    }

    private function getNoDisplayNode($id) {
        $div = new CLCF_HTMLNode("div");
        $div->addAttr("style", "display: none;")
                ->addAttr("id", $id)
        ;
        return $div;
    }

    /**
     * the example containers to add on button click.
     * 
     * 
     * wie laeuft das ganze?
     * 
     * bei click werden die container aufs tableau kopiert.
     * der container kriegt eine eindeutige id, sowie einen counter.
     * 
     * die columns muessen auch gekennzeichnet werden. zb contaiernxyz_col1 .._col2.
     * 
     * ein kommentar ist einfach auch nur ein spezialkontainer
     * 
     * wird jetzt ein feld in einen container kopiert erhaelt es den container,
     * die column sowie den rang, also 1. 2. oder n. element.
     * 
     * wo speichern? 
     * jeder container erhaelt hidden inputs. diese muessen dann bei drop entsprechend
     * via js gesetzt werden.
     * 
     * 
     * 
     */
    private function getContainerNode($containerid, $blocktype, $headline) {
        $div = new CLCF_HTMLNode("div");
        $div->addAttr("id", $containerid)
                ->addAttr("name", $blocktype)
                ->addAttr("class", "container")
                ->addAttr("draggable", "true")
                ->addAttr("ondragstart", "drag(event)")
                ->addAttr("tabindex", $this->tabcounter++);
        $div->addChild($this->getBlockHeadline($containerid, $headline))
                ->addChild($this->getRowBlock($blocktype, $containerid));
        return $div;
    }

    /**
     * die container id sieht so aus:
     * 
     * headline_container_2_1
     * 
     * 2 ist die anzahl cols und 1 ist die container id
     * 
     * 
     * @param type $id
     * @param type $headline
     * @param type $cols
     */
    private function getConcreteContainerNode($containerid, $headline, $cols) {
        $blocktype = "false";
        // containerid: headline_container_1_14455423
        //wcflog("getConcreteContainerNode, die id: " . $containerid);
        list($hl, $conti, $colis, $ci) = explode("_", trim($containerid) . "_");

        if (startsWith($containerid, "headline_")) {
            $outerContainerId = str_replace("headline_", "", $containerid);
        } else {
            $outerContainerId = $containerid;
        }

        if ($colis === "1") {
            $blocktype = "block1";

            $div = new CLCF_HTMLNode("div");

            $div->addAttr("id", $outerContainerId)
                    ->addAttr("name", $outerContainerId)
                    ->addAttr("class", "container")
                    ->addAttr("draggable", "true")
                    ->addAttr("ondragstart", "drag(event)")
                    //->addAttr("ondragover", "allowDrop(event)")
                    //->addAttr("ondrop", "drop(event)")
                    ->addAttr("tabindex", $this->tabcounter++);

            $div->addChild($this->getBlockHeadline($containerid, $headline));
            $div->addChild($this->getRowBlock($blocktype, $containerid));
            return $div;
        }
        if ($colis === "2") {
            $blocktype = "block2";
            $div = new CLCF_HTMLNode("div");
            $div->addAttr("id", $outerContainerId)
                    ->addAttr("name", $outerContainerId)
                    ->addAttr("class", "container")
                    ->addAttr("draggable", "true")
                    ->addAttr("ondragstart", "drag(event)")
                    //->addAttr("ondragover", "allowDrop(event)")
                    //->addAttr("ondrop", "drop(event)")
                    ->addAttr("tabindex", $this->tabcounter++);

            $div->addChild($this->getBlockHeadline($containerid, $headline));
            $div->addChild($this->getRowBlock($blocktype, $containerid));
            return $div;
        } else if ($colis === "3") {
            $blocktype = "block3";
            $div = new CLCF_HTMLNode("div");
            $div->addAttr("id", $outerContainerId)
                    ->addAttr("name", $outerContainerId)
                    ->addAttr("class", "container")
                    ->addAttr("draggable", "true")
                    ->addAttr("ondragstart", "drag(event)")
                    //->addAttr("ondragover", "allowDrop(event)")
                    //->addAttr("ondrop", "drop(event)")
                    ->addAttr("tabindex", $this->tabcounter++);

            $div->addChild($this->getBlockHeadline($containerid, $headline));
            $div->addChild($this->getRowBlock($blocktype, $containerid));
            return $div;
        }

        if ($blocktype !== "false") {
            
        }
        return $this->getContainerNode($containerid, $blocktype, $headline);
    }

    /**
     * the headline for a column block in the form.
     * 
     * 
     * @return \CLCF_HTMLNode
     */
    private function getBlockHeadline($containerId, $headline) {
        if (!startsWith($containerId, "headline_")) {
            $containerId = "headline_" . $containerId;
        }
        $div = new CLCF_HTMLNode("div");
        $div->addAttr("class", "blockhead");
        $in = new CLCF_HTMLNode("input");
        $in->addAttr("type", "text")->addAttr("placeholder", tr("headline"))
                ->addAttr("name", $containerId)
                ->addAttr("value", $headline);
        $div->addChild($in);
        return $div;
    }

    private function getFieldsForContainer($containerId, $containerNode) {
        $fields = $this->getFields();
        for ($i = 0; $i < count($fields); $i++) {
            // alle die keinen container haben hier anzeigen:
            if ($containerId == $fields[$i]->getContainer()) {
                $containerNode->addChild($this->getFieldNode($fields[$i]));
            }
        }
    }

    private function getEmptyText() {
        $t = new CLCF_HTMLNode("div");
        $t->addAttr("class", "invisible");

        $ct = new CLCF_HTMLNode("content");
        $ct->setContent(" +++++ Drag Fields +++++");
        return $t->addChild($ct);
    }

    /**
     * blocktype ist block1, block2 oder block3
     * 
     * 
     * 
     * @param type $blocktype
     * @return type
     */
    private function getRowBlock($blocktype, $containerId) {
        // headline_container_1_14455423
        list($hl, $conti, $type, $contId) = explode("_", $containerId . "____");
        $divouter = new CLCF_HTMLNode("div");
        $divouter->addAttr("width", "100%");
        $divrow = new CLCF_HTMLNode("div");
        $divrow->addAttr("class", "row");
        if ($blocktype === "block1") {
            $divblock = new CLCF_HTMLNode("div");
            $divblock->addAttr("class", $blocktype)->addAttr("ondrop", "drop(event)")->addAttr("ondragover", "allowDrop(event)")
                    ->addAttr("name", "container_1_1_" . $contId);
            // hier nudelt er jetzt die contaienrteile durch und klebt die richtigen dran
            $divblock->addChild($this->getEmptyText());
            $this->getFieldsForContainer("container_1_1_" . $contId, $divblock);
            $divrow->addChild($divblock);
            $divouter->addChild($divrow);
        } else if ($blocktype === "block2") {
            $divblock = new CLCF_HTMLNode("div");
            $divblock->addAttr("class", $blocktype)->addAttr("ondrop", "drop(event)")->addAttr("ondragover", "allowDrop(event)")
                    ->addAttr("name", "container_2_1_" . $contId);
            $divblock->addChild($this->getEmptyText());
            $this->getFieldsForContainer("container_2_1_" . $contId, $divblock);
            $divrow->addChild($divblock);
            $divblock2 = new CLCF_HTMLNode("div");
            $divblock2->addChild($this->getEmptyText());
            $divblock2->addAttr("class", $blocktype)->addAttr("ondrop", "drop(event)")->addAttr("ondragover", "allowDrop(event)")
                    ->addAttr("name", "container_2_2_" . $contId);
            $this->getFieldsForContainer("container_2_2_" . $contId, $divblock2);
            $divrow->addChild($divblock2);
            $divouter->addChild($divrow);
        } else if ($blocktype === "block3") {
            $divblock = new CLCF_HTMLNode("div");
            $divblock->addAttr("class", $blocktype)->addAttr("ondrop", "drop(event)")->addAttr("ondragover", "allowDrop(event)")
                    ->addAttr("name", "container_3_1_" . $contId);
            $divblock->addChild($this->getEmptyText());
            $this->getFieldsForContainer("container_3_1_" . $contId, $divblock);

            $divrow->addChild($divblock);
            $divblock2 = new CLCF_HTMLNode("div");
            $divblock2->addAttr("class", $blocktype)->addAttr("ondrop", "drop(event)")->addAttr("ondragover", "allowDrop(event)")
                    ->addAttr("name", "container_3_2_" . $contId);
            $divblock2->addChild($this->getEmptyText());
            $this->getFieldsForContainer("container_3_2_" . $contId, $divblock2);

            $divrow->addChild($divblock2);
            $divblock3 = new CLCF_HTMLNode("div");
            $divblock3->addAttr("class", $blocktype)->addAttr("ondrop", "drop(event)")->addAttr("ondragover", "allowDrop(event)")
                    ->addAttr("name", "container_3_3_" . $contId);
            $divblock3->addChild($this->getEmptyText());
            $this->getFieldsForContainer("container_3_3_" . $contId, $divblock3);
            $divrow->addChild($divblock3);
            $divouter->addChild($divrow);
        }

        return $divouter;
    }

    /**
     * the right side of the form builder. 
     * 
     * Home of the settings panel.
     * 
     */
    private function getTdRightColumnSettings() {
        $td = new CLCF_HTMLNode("td");
        $td->addAttr("valign", "top");
        $outestdiv = new CLCF_HTMLNode("div");
        $outestdiv->addAttr("id", "outer_settings_div");
        $fieldset = new CLCF_HTMLNode("fieldset");
        $legend = new CLCF_HTMLNode('legend');
        $legend->addChild($this->getContentNode(tr("settings")));
        $fieldset->addChild($legend);
        //$this->getInput($type, $id, $name, $value);
        // hier wird dynamisch das zu bearbeitende feld vorgehalten.
        $fieldset->addChild($this->getInput("hidden", "chosen_element", "chosen_element", "nix"));
        // name
        $fieldset->addChild($this->getLabelId("Name:","label_name"));
        $fieldset->addChild($this->getInput("text", "input_name", "input_name", "")
                        ->addAttr("onkeyup", 'changeField("name")'));
        // Beschriftung
        $fieldset->addChild($this->getLabelId("Label:","label_label"));
        $fieldset->addChild($this->getInput("text", "input_label", "input_label", "")
                        ->addAttr("onkeyup", 'changeField("label")'));
        // Tooltip:
        $fieldset->addChild($this->getLabelId("Tooltip:","label_tooltip"));
        $fieldset->addChild($this->getInput("text", "input_tooltip", "input_tooltip", "")
                        ->addAttr("onkeyup", 'changeField("tooltip")'));
        // Placeholder:
        $fieldset->addChild($this->getLabelId("Placeholder:","label_placeholder"));
        $fieldset->addChild($this->getInput("text", "input_placeholder", "input_placeholder", "")
                        ->addAttr("onkeyup", 'changeField("placeholder")'));
        // Pflichtfeld
        $fieldset->addChild($this->getLabelId(tr("mandatory_field") . ":","label_mandatory"));
        $fieldset->addChild($this->getInput("checkbox", "input_mandatory", "input_mandatory", "")
                        ->addAttr("onclick", 'changeField("mandatory")')
                        ->addAttr("value", 'true'));
        // Typ
        $fieldset->addChild($this->getInput("hidden", "input_type_hidden", "input_type_hidden", ""));
        $fieldset->addChild($this->getLabelId("Typ:","label_typ"));

        $select = new CLCF_HTMLNode("select");
        $select->addAttr("onchange", "checkRadio(this.value)")
                ->addAttr("id", "input_type");
        $select->addChild($this->getOption("Text"));
        $select->addChild($this->getOption("Number"));
        $select->addChild($this->getOption("Email"));
        $select->addChild($this->getOption("URL"));
        $select->addChild($this->getOption("Choicebox"));
        $select->addChild($this->getOption("Radio"));
        $select->addChild($this->getOption("Textarea"));
        $fieldset->addChild($select);

        // und jetzt kommt der versteckte kram ...
        $valuesDiv = $this->getNoDisplayNode("values_radio");
        $lblNode = $this->getLabel(tr('values') . ":");
        $valuesDiv->addChild($lblNode);

        $ifirst = $this->getInput("text", "val_0", "radio0", "");
        $valuesDiv->addChild($ifirst->addAttr("onkeyup", 'changeType("val_0","val_1")'));
        for ($i = 1; $i < 20; $i++) {
            $ifirst = $this->getInput("hidden", "val_" . $i, "radio" . $i, "");
            $succ = $i + 1;
            $valuesDiv->addChild($ifirst->addAttr("onkeyup", 'changeType("val_' . $i . '","val_' . $succ . '")'));
        }
        $fieldset->addChild($valuesDiv);
        // confirm email
        $confirmDiv = $this->getNoDisplayNode("confirm_email_value");
        $confirmDiv->addChild($this->getLabel(tr('confirmation_email') . ":"));
        $iconf = $this->getInput("checkbox", "input_confirmationEmail", "input_confirmationEmail", "");
        $confirmDiv->addChild($iconf->addAttr("onclick", 'changeField("confirmationEmail")')->addAttr("type", "checkbox"));
        $fieldset->addChild($confirmDiv);
        $outestdiv->addChild($fieldset);
        return $td->addChild($outestdiv);
    }

    private function getOption($name) {
        $option = new CLCF_HTMLNode("option");
        return $option->addChild($this->getContentNode($name));
    }

    private function getLabel($content) {
        $label = new CLCF_HTMLNode("label");
        return $label->addChild($this->getContentNode($content));
    }
    
    private function getLabelId($content,$id) {
        $label = new CLCF_HTMLNode("label");
        return $label->addAttr("id",$id)->addChild($this->getContentNode($content));
    }

    /**
     * the column in the middle of the form builder.
     * 
     * the drop target for the form fields.
     * 
     */
    private function getTdMiddleColumnDropField() {
        $td = new CLCF_HTMLNode("td");
        $td->addAttr('valign', "top")->addAttr("id", "middle_td");
        // button bar
        $div = new CLCF_HTMLNode('div');
        $div->addAttr("class", "button_bar");



        // old
        //$div->addChild($this->getAhrefBB("return addOne();", "fa fa-columns fa-2x", "#"));
        //$div->addChild($this->getAhrefBB("return addTwo();", "fa fa-columns fa-2x", "#"));
        //$div->addChild($this->getAhrefBB("return addThree();", "fa fa-columns fa-2x", "#"));
        //$div->addChild($this->getAhrefBB("addNewComment();", "fa fa-font fa-2x", "#"));
        //$div->addChild($this->getAhrefBB("showPreview();", "fa fa-eye fa-2x", "#"));
        // new
        $div->addChild($this->getAhrefIcon("return addOne();",  WCF_IMG . "1.gif", "#"));
        $div->addChild($this->getAhrefIcon("return addTwo();", WCF_IMG . "12_12.gif", "#"));
        $div->addChild($this->getAhrefIcon("return addThree();", WCF_IMG . "13_13_13.gif", "#"));
        //$div->addChild($this->getAhrefBB("addNewComment();", "fa fa-font fa-2x", "#"));
        //$div->addChild($this->getAhrefBB("showPreview();", "fa fa-eye fa-2x", "#"));


        // kritisch XXX
        $div->addChild($this->getAhrefBB($this->pagePrefix . $this->formid, "fa fa-undo fa-2x", ""));
        $div->addChild($this->getTrashTag());
        $td->addChild($div);

        // tableau
        $tableau = new CLCF_HTMLNode('div');
        $tableau->addAttr("id", "tableau")
                ->addAttr("ondrop", "drop(event)")
                ->addAttr("ondragover", "allowDrop(event);");

        // wo haengen die container dran?

        $contis = $this->getContainers();
        //wcflog("male contaienr" . count($contis));
        for ($i = 0; $i < count($contis); $i++) {
            //wcflog("male einen conkreten");
            $tableau->addChild($this->createContainer($contis[$i]));
        }

        $td->addChild($tableau);
        return $td;
    }

    private function createContainer($container) {
        // return empty div.
        if (!isset($container))
            return new CLCF_HTMLNode("div");
        $id = $container->getId();
        $headline = $container->getHeadline();
        $cols = $container->getColumns();
        //hier gehts gleich weiter, headline nicht vergessen und zug felder adden.

        return $this->getConcreteContainerNode($id, $headline, $cols);
    }

    /**
     * erzeugt ein a href mit Kindknoten <i> fuer font awesome Fonts, welche 
     * bei click die in onclick benannte funktion ausfuehrt.
     * 
     * @param type $onclick
     * @param type $iclass
     * @param type $href
     * @return type
     */
    private function getAhrefBB($onclick, $iclass, $href) {
        $a = new CLCF_HTMLNode("a");
        $a->addAttr("href", $href)->addAttr("onclick", $onclick);
        return $a->addChild($this->getITag($iclass));
    }

    private function getAhrefIcon($onclick, $iconpath, $href) {
        $a = new CLCF_HTMLNode("a");
        $a->addAttr("href", $href)->addAttr("onclick", $onclick);
        return $a->addChild($this->getImgTag($iconpath));
    }

    /**
     * the left side of the form builder. 
     * contains all existing nodes.
     * 
     */
    private function getTdLeftColumnNodeList() {
        $td = new CLCF_HTMLNode("td");
        $td->addAttr("valign", "top");
        $td->addChild($this->getButtonBar())->addChild($this->getTrashTag());
        // the elements
        $elements = new CLCF_HTMLNode("div");
        $elements->addAttr("id", "elements");
        $fields = $this->getFields();
        //wcflog("hier male ich die felder gleich ...");
        for ($i = 0; $i < count($fields); $i++) {
            // alle die keinen container haben hier anzeigen:

            if (realEmpty($fields[$i]->getContainer())) {
                $elements->addChild($this->getFieldNode($fields[$i]));
            } 
        }
        $td->addChild($elements);
        return $td;
    }

    /**
     * returns a hashmap with 
     * @return type
     */
    private function getContainerFields() {
        if (!isset($this->containerFields)) {
            $this->containerFields = array();
        }
        return $this->containerFields;
    }


    /**
     * creates one input field node div element.
     * 
     * 
     * @param type $field
     */
    private function getFieldNode($field) {

        $fieldId = $this->formid . "_" . $field->getId();
        $div = new CLCF_HTMLNode("div");
        $div->addAttr("class", "drag_field");
        $div->addAttr("id", $fieldId);
        $div->addAttr("draggable", "true");
        $div->addAttr("ondragstart", "drag(event)");
        $div->addAttr("tabindex", $this->tabcounter++);
        $div->addAttr("onclick", 'fieldSelected("' . $fieldId . '")');
        $label = new CLCF_HTMLNode("label");
        $label->addAttr("class", "exmpl")->addAttr("id", "lbl_" . $fieldId);
        $label->addChild($this->getContentNode($field->getName()));
        $div->addChild($label);
        $itype = new CLCF_HTMLNode("input");
        $itype->addAttr("type", $field->getType()->getType())->addAttr("class", "exmpl")->setReadonly(true);
        $div->addChild($itype);

        $div->addChild($this->getInput("hidden", "containerColumn_" . $fieldId, "containerColumn_" . $fieldId, $field->getContainerColumn()));
        $div->addChild($this->getInput("hidden", "containerRang_" . $fieldId, "containerRang_" . $fieldId, $field->getContainerRang()));
        $div->addChild($this->getInput("hidden", "container_" . $fieldId, "container_" . $fieldId, $field->getContainer()));
        $div->addChild($this->getInput("hidden", "name_" . $fieldId, "name_" . $fieldId, $field->getName()));
        $div->addChild($this->getInput("hidden", "label_" . $fieldId, "label_" . $fieldId, $field->getLabel()));
        $div->addChild($this->getInput("hidden", "placeholder_" . $fieldId, "placeholder_" . $fieldId, $field->getPlaceholder()));
        $div->addChild($this->getInput("hidden", "tooltip_" . $fieldId, "tooltip_" . $fieldId, $field->getTooltip()));
        $div->addChild($this->getInput("hidden", "mandatory_" . $fieldId, "mandatory_" . $fieldId, $field->getMandatory()));
        $div->addChild($this->getInput("hidden", "imageNumber_" . $fieldId, "imageNumber_" . $fieldId, $field->getImageNumber()));
        $div->addChild($this->getInput("hidden", "confirmationEmail_" . $fieldId, "confirmationEmail_" . $fieldId, $field->getConfirmationEmail()));
        $div->addChild($this->getInput("hidden", "order_" . $fieldId, "order_" . $fieldId, $field->getOrder()));
        $div->addChild($this->getInput("hidden", "id_" . $fieldId, "id_" . $fieldId, $field->getId()));
        $div->addChild($this->getInput("hidden", "type_" . $fieldId, "type_" . $fieldId, $field->getType()->getType()));

        // the values:
        $i = 0;
        $values = $field->getType()->getValues();
        $valueCount = count($values);
        for ($i = 0; $i < $valueCount; $i++) {
            $div->addChild($this->getInput("hidden", "val_" . $i . "_" . $fieldId, "val_" . $i . "_" . $fieldId, $values[$i]));
        }
        return $div;
    }

    private function getInput($type, $id, $name, $value) {
        $itype = new CLCF_HTMLNode("input");
        return $itype->addAttr("type", $type)->addAttr("id", $id)->addAttr("name", $name)->addAttr("value", $value);
    }

    /**
     * add new input field on top of the node list. on the left side.
     * 
     * 
     * @return type
     */
    private function getButtonBar() {
        $div = new CLCF_HTMLNode("div");
        $div->addAttr("class", "button_bar");
        $a = new CLCF_HTMLNode("a");
        $a->addAttr("href", "#");
        $a->addAttr("onclick", "return addNewInputField();");
        $i = new CLCF_HTMLNode("i");
        $i->addAttr("class", "fa fa-plus-square fa-2x");
        return $div->addChild($a->addChild($i));
    }

    private function getITag($clazz) {
        $i = new CLCF_HTMLNode("i");
        return $i->addAttr("class", $clazz);
    }

    private function getImgTag($imgpath) {
        $i = new CLCF_HTMLNode("img");
        return $i->addAttr("src", $imgpath)->addAttr("hspace", "20");
    }

    
    /**
     * 
     * <div   class="trash_div" draggable="true" ondrop="dropTrash(event)"  ondragover="allowDrop(event)" ><i class="fa fa-trash fa-2x"></i>
      </div>
     * 
     */
    private function getTrashTag() {
        $div = new CLCF_HTMLNode("div");
        $div->addAttr("class", "trash_div");
        $div->addAttr("draggable", "true");
        $div->addAttr("ondrop", "dropTrash(event);");
        $div->addAttr("ondragover", "allowDrop(event)");
        $i = new CLCF_HTMLNode("i");
        $i->addAttr("class", "fa fa-trash fa-2x");
        return $div->addChild($i);
    }

    /**
     * the headline tr node.
     * 
     * @return type
     */
    private function getTrHeadline() {
        $tr = new CLCF_HTMLNode("tr");
        $tr->addChild($this->getThHeadline(tr("input_fields")));
        $tr->addChild($this->getThHeadline("Formular"));
        return $tr->addChild($this->getThHeadline(tr("settings")));
    }

    /**
     * returns a th headline node.
     * 
     * @param type $headline
     * @return type
     */
    private function getThHeadline($headline) {
        $th = new CLCF_HTMLNode("th");
        return $th->addChild($this->getContentNode($headline));
    }

    /**
     * generates a new content node.
     * 
     * 
     * @param type $content
     * @return type
     */
    private function getContentNode($content) {
        $node = new CLCF_HTMLNode("content");
        return $node->setContent($content);
    }
}
