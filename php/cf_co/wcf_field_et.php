<?php
namespace wcf_coolform;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
class WcfSettings {

    var $id;
    var $formName;
    var $email;

}

class WcfContainer {

    var $headline;
    var $id;
    var $columns;

    public function getHeadline() {
        return $this->headline;
    }

    public function getId() {
        return $this->id;
    }

    public function getColumns() {
        return $this->columns;
    }

    public function setHeadline($headline) {
        $this->headline = $headline;
        return $this;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function setColumns($columns) {
        $this->columns = $columns;
        return $this;
    }

}

class WcfField {

    var $id;
    var $name;
    var $label;
    var $column;
    var $mandatory = false;
    var $tooltip;
    var $placeholder;
    var $type;
    var $order;
    var $confirmationEmail;
    var $imagePath;
    var $imageNumber = -1;
    var $container;
    var $containerColumn;
    var $containerRang;

    function __construct($id) {
        $this->id = $id;
    }

    public function getContainerColumn() {
        return $this->containerColumn;
    }

    public function setContainerColumn($containerColumn) {
        $this->containerColumn = $containerColumn;
        return $this;
    }

    public function getContainer() {
        return $this->container;
    }

    public function getContainerRang() {
        return $this->containerRang;
    }

    public function setContainer($container) {
        $this->container = $container;
        return $this;
    }

    public function setContainerRang($containerRang) {
        $this->containerRang = $containerRang;
        return $this;
    }

    public function getImageNumber() {
        return $this->imageNumber;
    }

    public function setImageNumber($imageNumber) {
        $this->imageNumber = $imageNumber;
        return $this;
    }

    public function getConfirmationEmail() {
        return $this->confirmationEmail;
    }

    public function setConfirmationEmail($confirmationEmail) {
        $this->confirmationEmail = $confirmationEmail;
        return $this;
    }

    public function isConfirmationEmail() {
        return $this->confirmationEmail == "true";
    }

    public function getOrder() {
        return $this->order;
    }

    public function setOrder($order) {
        $this->order = $order;
        return $this;
    }

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getLabel() {
        return isset($this->label) ? $this->label : $this->name;
    }

    public function getColumn() {
        return $this->column;
    }

    public function getMandatory() {
        return $this->mandatory;
    }

    public function getTooltip() {
        return $this->tooltip;
    }

    public function getPlaceholder() {
        return $this->placeholder;
    }

    public function getType() {
        if (!isset($this->type)) {
            $this->type = new WcfType();
        }
        return $this->type;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function setLabel($label) {
        $this->label = $label;
        return $this;
    }

    public function setColumn($column) {
        $this->column = $column;
        return $this;
    }

    public function setMandatory($mandatory) {
        $this->mandatory = $mandatory;
        return $this;
    }

    public function setTooltip($tooltip) {
        $this->tooltip = $tooltip;
        return $this;
    }

    public function setPlaceholder($placeholder) {
        $this->placeholder = $placeholder;
        return $this;
    }

    public function setType($type) {
        $this->type = $type;
        return $this;
    }

    public function getImagePath() {
        return $this->imagePath;
    }

    public function setImagePath($imagePath) {
        $this->imagePath = $imagePath;
        return $this;
    }

    public function getCSS() {
        $conti = $this->getContainer();
        if (startsWith($conti, "container_1")) {
            return "wcf_css_1";
        } else if (startsWith($conti, "container_2")) {
            return "wcf_css_2";
        } else if (startsWith($conti, "container_3")) {
            return "wcf_css_3";
        }
        return "";
    }

}

class WcfType {

    var $type;
    var $values;

    public function getType() {
        if (!isset($this->type)) {
            $this->type = "text";
        }
        return $this->type;
    }

    public function isEmail() {
        wcflog("in isEmail, wir haben $this->type");
        if ($this->type == "Email") {
            return true;
        }
        if ($this->type == "email") {
            return true;
        }
        return false;
    }

    public function getHtmlType() {
        if ($this->type === "Email") {
            return "email";
        } else if ($this->type == "URL") {
            return "url";
        } else if ($this->type == "file") {
            return "file";
        } else if ($this->type == "Number") {
            return "number";
        } else if ($this->type == "Choicebox") {
            return "choicebox";
        } else if ($this->type == "Radio") {
            return "radio";
        } else if ($this->type == "Textarea") {
            return "textarea";
        } else {
            // todo!!! returns the input type="" value ...
            return "text";
        }
    }

    public function getValues() {
        return $this->values;
    }

    public function setType($type) {
        $this->type = $type;
        return $this;
    }

    public function setValues($values) {
        $this->values = $values;
        return $this;
    }

    public function addValue($value) {
        if (!isset($this->values)) {
            $this->values = array();
        }
        array_push($this->values, $value);
        return $this;
    }

}
