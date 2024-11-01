<?php
namespace wcf_coolform;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
class WcfXmlHandler {

    var $inpath;
    var $xmlElement = null;
    var $fields;
    var $containers;

    public function __construct($infile) {
        $this->inpath = $infile;
        $this->read();
    }

    public function init() {
        $dom = new \DOMDocument;
        $dom->loadXML('<wcf></wcf>');
        $xml = simplexml_import_dom($dom);
        $xml->addChild('header', 'brot');
        $xml->header->addAttribute('wurst', 'kaese');
        echo "wurstsalat ohne " . $xml->header['wurst'];
        $this->xmlElement = $xml;
        echo "ola mundo";
        echo "hier:" . $this->xmlElement->asXML();
        $this->write();


        // change attribute value:
        //$xmlelement -> attributes('xlink', true) -> href = 'value'; // Works!
    }

    private function getRoot() {
        $dom = new \DOMDocument;
        $dom->loadXML('<wcf></wcf>');
        $xml = simplexml_import_dom($dom);
        $xml->addChild('fields');
        $xml->addChild('containers');
        return $xml;
    }

    public function getSimpleXMLElement() {
        if (!isset($this->xmlElement)) {
            $this->read();
        }
        return $this->xmlElement;
    }

    public function iterateovernodes() {
        foreach ($movies->movie->characters->character as $character) {
            echo $character->name, ' played by ', $character->actor, PHP_EOL;
        }
    }

    public function read() {
        if (file_exists($this->inpath)) {
        $xmlString = file_get_contents($this->inpath);
        } else {
            $xmlString = "";
        }
        if (empty($xmlString)) {
            $this->fields = array();
            return;
        }
        $this->xmlElement = new \SimpleXMLElement($xmlString);
        $this->readFields();
        $this->readContainers();
    }

    public function write() {
        if (isset($this->xmlElement)) {
            $xmlStr = $this->xmlElement->asXML();
            //wcflog("und ff xml initil geschrieben: " . $xmlStr);
            file_put_contents($this->inpath, $xmlStr);
        } else {
            wcflog("xmlelement nicht gesetzt");
        }
    }

    public function getContainers() {
        if (!isset($this->containers)) {
            $this->containers = array();
        }
        return $this->containers;
    }

    public function setContainers($containers) {
        $this->containers = $containers;
    }

    public function getFields() {
        if (!isset($this->fields)) {
            $this->fields = array();
        }
        return $this->fields;
    }

    public function setFields($fields) {
        $this->fields = $fields;
    }

    public function writeContainers() {
        $containers = $this->getContainers();
        $xml = $this->xmlElement;
        foreach ($containers as $container) {
            $xf = $xml->containers->addChild('container');
            $xf->addChild('id', $container->getId());
            $xf->addChild('headline', $container->getHeadline());
            $xf->addChild('columns', $container->getColumns());
        }
        $this->xmlElement = $xml;
        $this->write();
    }

    public function writeFields() {
        $fields = $this->getFields();
        $xml = $this->getRoot();
        foreach ($fields as $field) {
            //wcflog("neues feld: " . $field->getName());
            $xf = $xml->fields->addChild('field');
            $xf->addChild('id', $field->getId());
            $xf->addChild('name', $field->getName());
            //wcflog("feld beim erzeugen gelesen: " . $field->getName());
            $xf->addChild('label', $field->getLabel());
            $xf->addChild('order', $field->getOrder());
            $xf->addChild('mandatory', $field->getMandatory());
            $xf->addChild('placeholder', $field->getPlaceholder());
            $xf->addChild('column', $field->getColumn());
            $xf->addChild('tooltip', $field->getTooltip());
            $xf->addChild('imagePath', $field->getImagePath());
            $xf->addChild('imageNumber', $field->getImageNumber());
            $xf->addChild('confirmationEmail', $field->getConfirmationEmail());
            $xf->addChild('containerColumn', $field->getContainerColumn());
            $xf->addChild('containerRang', $field->getContainerRang());
            $xf->addChild('container', $field->getContainer());
            
            $tip = $field->getType();
            if (isset($tip)) {
                $typeval = $tip->getType();
            } else {
                $typeval = "text";
            }
            $xf->addChild('type', $typeval);
            $xv = $xf->addChild('values');
            $values = $tip->getValues();
            $count = count($values);
            for ($i = 0; $i < $count; $i++) {
                $xv->addChild('value', $values[$i]);
            }
        }
        $this->xmlElement = $xml;
        $this->write();
    }

    public function writeBoth($fields,$containers) {
        $this->setContainers($containers);
        $this->setFields($fields);
        
        $this->writeFields();
        $this->writeContainers();
        //$this->write();
    }
    
    
    
    public function readContainers() {
        $this->containers = array();
        if (!isset($this->xmlElement)) {
            return $this->containers;
        }
        //wcflog($this->xmlElement->asXML());
        $containers = $this->xmlElement->containers->container;
        foreach ($containers as $containerxml) {
            $container = new WcfContainer();
            $container->setColumns($containerxml->columns);
            $container->setHeadline($containerxml->headline);
            $container->setId($containerxml->id);
            
            
            array_push($this->containers, $container);
        }
        return $this->containers;
    }
    
    
    public function readFields() {
        $this->fields = array();
        if (!isset($this->xmlElement)) {
            return $this->fields;
        }
        //wcflog($this->xmlElement->asXML());
        $fields = $this->xmlElement->fields->field;
        foreach ($fields as $fieldXML) {
            //foreach ($this->xmlElement->xpath('//field') as $fieldXML) {
            $field = new WcfField($fieldXML->id);
            $field->setName($fieldXML->name);
            $field->setColumn($fieldXML->column);
            $field->setContainerColumn($fieldXML->containerColumn);
            $field->setContainerRang($fieldXML->containerRang);
            $field->setContainer($fieldXML->container);
            $field->setOrder($fieldXML->order);
            $field->setMandatory($fieldXML->mandatory);
            $field->setPlaceholder($fieldXML->placeholder);
            $field->setTooltip($fieldXML->tooltip);
            $field->setConfirmationEmail($fieldXML->confirmationEmail);
            $field->setImagePath($fieldXML->imagePath);
            $field->setImageNumber($fieldXML->imageNumber);
            $type = new WcfType();
            $type->setType($fieldXML->type);
            $values = array();
            if (isset($fieldXML->values)) {
                foreach ($fieldXML->values->value as $value) {
                    array_push($values, $value);
                }
                $type->setValues($values);
            }
            $field->setType($type);
            array_push($this->fields, $field);
        }
        return $this->fields;
    }

}

/**
 * einige beispiele:
 * 
 * 
$movies = new SimpleXMLElement($xmlstr);

foreach ($movies->xpath('//character') as $character) {
    echo $character->name, ' gespielt von ', $character->actor, PHP_EOL;
}
 * 
 * 
 * 
$rating = $movies->movie[0]->addChild('rating', 'PG');
$rating->addAttribute('type', 'mpaa');
 */