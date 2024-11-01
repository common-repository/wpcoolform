<?php
namespace wcf_coolform;


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


class TJC_Factory {

    public function getSettingsHandler() {
        return new WCFFormSettingsHandler();
    }

}
