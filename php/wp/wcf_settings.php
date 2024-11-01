<?php
namespace wcf_coolform;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
class WcfSettingsPage {

    var $option_tmp_dir = 'tmp_dir';
    var $option_upload_dir = 'upload_dir';
    var $option_activation_key = 'wcf_activation_key';

    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_plugin_page'));
        add_action('admin_init', array($this, 'page_init'));
    }

    /**
     * Add options page
     */
    public function add_plugin_page() {
        // This page will be under "Settings"
        add_options_page(
                'Settings Admin', 'WpCoolForm', 'manage_options', 'my-setting-admin', array($this, 'create_admin_page')
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page() {
        // Set class property
        $this->options = get_option('my_option_name');


        echo file_get_contents(getPathSettingsHeader());
        ?>
        <form method="post" action="options.php">
            <?php
            // This prints out all hidden setting fields
            settings_fields('my_option_group');
            do_settings_sections('my-setting-admin');
            submit_button();
            ?>
        </form>

        <?php
        echo file_get_contents(getPathSettingsFooter());
    }

    /**
     * Register and add settings
     */
    public function page_init() {
        register_setting(
                'my_option_group', // Option group
                'my_option_name', // Option name
                array($this, 'sanitize') // Sanitize
        );


        add_settings_section(
                'setting_section_id', // ID
                tr('wcf_settings_title'), // Title
                array($this, 'print_section_info'), // Callback
                'my-setting-admin' // Page
        );


        add_settings_field(
                $this->option_upload_dir, tr('title_upload_dir'), array($this, 'upload_dir_callback'), 'my-setting-admin', 'setting_section_id'
        );

        add_settings_field(
                $this->option_tmp_dir, tr('title_tmp_dir'), array($this, 'tmp_dir_callback'), 'my-setting-admin', 'setting_section_id'
        );

        add_settings_field(
                $this->option_activation_key, tr('title_activation_key'), array($this, 'activation_key_callback'), 'my-setting-admin', 'setting_section_id'
        );
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize($input) {
        $new_input = array();
        if (isset($input[$this->option_upload_dir]))
            $new_input[$this->option_upload_dir] = sanitize_text_field($input[$this->option_upload_dir]);
        if (isset($input[$this->option_tmp_dir]))
            $new_input[$this->option_tmp_dir] = sanitize_text_field($input[$this->option_tmp_dir]);
        if (isset($input[$this->option_activation_key]))
            $new_input[$this->option_activation_key] = sanitize_text_field($input[$this->option_activation_key]);
        return $new_input;
    }

    /**
     * Print the Section text
     */
    public function print_section_info() {
        print tr('settings_section_info');
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function upload_dir_callback() {
        printf(
                '<input type="text" id="' . $this->option_upload_dir . '" name="my_option_name[' . $this->option_upload_dir . ']" value="%s" />', isset($this->options[$this->option_upload_dir]) ? esc_attr($this->options[$this->option_upload_dir]) : ''
        );
    }

    public function tmp_dir_callback() {
        printf(
                '<input type="text" id="' . $this->option_tmp_dir . '" name="my_option_name[' . $this->option_tmp_dir . ']" value="%s" />', isset($this->options[$this->option_tmp_dir]) ? esc_attr($this->options[$this->option_tmp_dir]) : ''
        );
    }

    public function activation_key_callback() {
        printf(
                '<input type="text" id="' . $this->option_activation_key . '" name="my_option_name[' . $this->option_activation_key . ']" value="%s" />', isset($this->options[$this->option_activation_key]) ? esc_attr($this->options[$this->option_activation_key]) : ''
        );
    }

}

if (is_admin())
    $my_settings_page = new WcfSettingsPage();