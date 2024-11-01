<?php

/*
  Plugin Name: WpCoolForm
  Plugin URI: http://wpcoolform.com/
  Description: Generate Website Forms with your favorite Office Program. Generate Wordfiles on your server.
  Version: 1.0.0
  Author: Thomas Klinkert, John Kreiling,  Croupers GbR
  Author URI: http://croupers.com
  License: GPLv2 or later
 */
namespace wcf_coolform;
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly


define("CF_ROOT_FOLDER", plugin_dir_path(__FILE__));
define("CF_ROOT_URL", plugins_url('', __FILE__));

include 'init/tjc_init.php';



add_action('init', 'wcf_coolform\cf_init', 1);
register_activation_hook(__FILE__, array('TJC_Init', 'onActivate'));
register_uninstall_hook(__FILE__, array('TJC_Init', 'onUninstall'));

/**
 * on startup.
 */
function cf_init() {

    $init = new TJC_Init();
    $init->init();
}
