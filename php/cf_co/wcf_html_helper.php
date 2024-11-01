<?php
namespace wcf_coolform;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class CLCF_HTMLNode {

    var $tag;
    var $childs;
    var $attrs;
    var $content;
    var $readonly = false;

    function __construct($name) {
        $this->tag = $name;
    }

    public function getReadonly() {
        return $this->readonly;
    }

    public function setReadonly($readonly) {
        $this->readonly = $readonly;
        return $this;
    }

    function isRoot() {
        return $this->tag === "ROOT";
    }

    public function getTag() {
        return $this->tag;
    }

    public function getChilds() {
        if (!isset($this->childs)) {
            $this->childs = array();
        }
        return $this->childs;
    }

    public function getAttrs() {
        if (!isset($this->attrs)) {
            $this->attrs = array();
        }
        return $this->attrs;
    }

    public function getContent() {
        return $this->content;
    }

    public function setTag($tag) {
        $this->tag = $tag;
        return $this;
    }

    public function addChild($node) {
        $childs = $this->getChilds();
        array_push($childs, $node);
        $this->setChilds($childs);
        return $this;
    }

    public function setChilds($childs) {
        $this->childs = $childs;
        return $this;
    }

    public function addAttr($name, $value) {
        $atz = $this->getAttrs();
        $atz[$name] = $value;
        $this->setAttrs($atz);
        return $this;
    }

    public function setAttrs($attrs) {
        $this->attrs = $attrs;
        return $this;
    }

    public function setContent($content) {
        $this->content = $content;
        return $this;
    }

    function html() {
        if ($this->tag === "content") {
            return $this->content;
        }
        $ret = "<" . $this->tag;
        foreach ($this->getAttrs() as $attr => $val) {
            //    for ($i = 0 ; $i < count($this->getAttrs());$i++) {
            $ret .= " " . $attr . "='" . $val . "'";
        }
        if ($this->getReadonly()) {
            $ret .= " readonly";
        }
        $ret .= ">";
        // childs
        for ($i = 0; $i < count($this->getChilds()); $i++) {
            $ret .= $this->childs[$i]->html();
        }
        if ($this->tag === "input") {
            return $ret;
        } else {
            return $ret . "</" . $this->tag . ">";
        }
    }

}

/**
 * convenience class for adding tags around a string.
 * 
 * 
 */
class WcfHtmlHelper {

    function addTag($tagname, $attrVals, $str, $required = false) {
        $atrstr = "";
        foreach ($attrVals as $name => $value) {
            $atrstr .= " " . $name . "='" . $value . "'";
        }
        if ($required) {
            $requiredstr = " required";
        } else {
            $requiredstr = "";
        }
        return "<" . $tagname . $atrstr . $requiredstr . ">";
    }

    function addTableTag($str) {
        return "<table border=0 class='sortable'>" . $str . "</table>";
    }

    function addTextarea($tagName, $inline_css) {
        return "<textarea name='$tagName'>$inline_css</textarea>";
    }

    function addLabelTag($str) {
        return "<label>" . $str . "</label>";
    }

    function addStyleTag($str) {
        return "<style>" . $str . "</style>";
    }

    function addPTag($str) {
        return "<p>" . $str . "</p>";
    }

    function addTrTag($str) {
        return "<tr class='item'>" . $str . "</tr>";
    }

    function addTrTagO($str, $odd) {
        $cls = $odd ? "odd" : "even";
        return "<tr class='item " . $cls . "'>" . $str . "</tr>";
    }

    /*
    function addScriptTag($str) {
        return "<script>" . $str . "</script>" . "\n";
    }
     */

    function addThTagTitle($str, $title) {
        return "<th>" . $this->addDivTagTitle($str, $title) . "</th>";
    }

    function addThTag($str) {
        return "<th>" . $str . "</th>";
    }

    function addTdTag($str) {
        return "<td>" . $str . "</td>";
    }

    function addH2Tag($str, $clz = null) {
        if (!isset($clz)) {
            $clz = "second headline";
        }
        return "<h2 class='" . $clz . "'>" . $str . "</h2>";
    }

    function addLiTag($str) {
        return "<li>" . $str . "</li>";
    }

    function addOptionTag($str, $value, $selected = false) {
        $selstr = $selected ? " selected" : "";
        return "<option value='" . $value . "'" . $selstr . ">" . $str . "</option>";
    }

    function addSelectTag($str, $name) {
        return "<select name='" . $name . "'>" . $str . "</select>";
    }

    function addDivTag($str, $clazz = null, $id = null) {
        return "<div" . $this->getClassAtr($clazz) . $this->getIdAtr($id) . ">" . $str . "</div>";
    }

    function addDivTagTitle($content, $title) {
        return "<div title='" . $title . "'>" . $content . "</div>";
    }

    function getClassAtr($class) {
        if (empty($class)) {
            return "";
        } else {
            return " class='" . $class . "'";
        }
    }

    function getIdAtr($id) {
        if (empty($id)) {
            return "";
        } else {
            return " id='" . $id . "'";
        }
    }

}
