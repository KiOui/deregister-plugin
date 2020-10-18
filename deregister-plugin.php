<?php
/**
* @package deregister-plugin
*/
/*
Plugin Name: deregister-plugin
Description: Plugin used for deregistering from subscriptions
*/


defined('ABSPATH') or die("You can't run this standalone script!");

require_once ABSPATH.'wp-admin/includes/upgrade.php';

require_once ABSPATH."wp-content/plugins/deregister-plugin/shortcode/SearchBar.php";
require_once ABSPATH."wp-content/plugins/deregister-plugin/metaboxes/SubscriptionMetaBox.php";
require_once ABSPATH."wp-content/plugins/deregister-plugin/assets/AjaxHandler.php";
require_once ABSPATH."wp-content/plugins/deregister-plugin/shortcode/MailForm.php";
require_once ABSPATH."wp-content/plugins/deregister-plugin/assets/MailFormHandler.php";
require_once ABSPATH."wp-content/plugins/deregister-plugin/shortcode/ItemList.php";
require_once ABSPATH."wp-content/plugins/deregister-plugin/shortcode/Categories.php";
require_once ABSPATH."wp-content/plugins/deregister-plugin/shortcode/VerificationPage.php";
require_once ABSPATH."wp-content/plugins/deregister-plugin/shortcode/Confirmation.php";
require_once ABSPATH."wp-content/plugins/deregister-plugin/shortcode/ButtonDisabler.php";
require_once ABSPATH."wp-content/plugins/deregister-plugin/shortcode/Scroll.php";
require_once ABSPATH."wp-content/plugins/deregister-plugin/shortcode/RequestForm.php";
require_once ABSPATH."wp-content/plugins/deregister-plugin/assets/Settings.php";

class DeregisterPlugin {


    private $post_type_name = 'sub_provider';
    private $category_type_name = 'sub_category';
    private $shortcode_search = 'subscription_search_bar';
    private $shortcode_form = 'subscription_form';
    private $shortcode_list = 'subscription_list';
    private $shortcode_categories = 'subscription_categories';
    private $shortcode_verification = 'subscription_verification';
    private $shortcode_confirmation = 'subscription_confirmation';
    private $shortcode_disabler = 'subscription_button_disabler';
    private $shortcode_scroll = 'subscription_scroll';
    private $shortcode_request = 'subscription_request_form';

    function __construct() {
        add_action('init', array($this, 'custom_post_type'));
    }

    function activate() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'deregister_sessions';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table_name (
            session_id varchar(256) NOT NULL,
            firstname text NOT NULL,
            lastname text NOT NULL,
            address text NOT NULL,
            postalcode text NOT NULL,
            residence text NOT NULL,
            email text NOT NULL,
            expires_at datetime NOT NULL,
            subscriptions text NOT NULL,
            PRIMARY KEY  (session_id)
        );";
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        //dbDelta($sql);
        $wpdb->query($sql);
        $this->custom_post_type();
        flush_rewrite_rules();
    }

    function deactivate() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'deregister_sessions';
        $sql = "DROP TABLE IF EXISTS $table_name";
        $wpdb->query($sql);
    }

    function ajax_actions() {
        $ajaxhandler = new AjaxHandler();
        //We need to specify both the nopriv (not logged in user) and priv (logged in user) hooks
        add_action('wp_ajax_nopriv_deregister_categories', array($ajaxhandler, "query"));
        add_action('wp_ajax_deregister_categories', array($ajaxhandler, "query"));
        add_action( 'wp_ajax_nopriv_submit_request_form', array($ajaxhandler, 'handle_subscription_request'));
        add_action( 'wp_ajax_submit_request_form', array($ajaxhandler, 'handle_subscription_request'));
    }

    function shortcodes() {
    	$searchBar = new SearchBar();
    	add_shortcode($this->shortcode_search, array($searchBar, 'create_search'));
    	$mailForm = new MailForm();
    	add_shortcode($this->shortcode_form, array($mailForm, 'create_form'));
        $basket = new ItemList();
        add_shortcode($this->shortcode_list, array($basket, 'create_list'));
        $categories = new Categories();
        add_shortcode($this->shortcode_categories, array($categories, 'create_categories'));
        $verification = new VerificationPage();
        add_shortcode($this->shortcode_verification, array($verification, 'create_verification'));
        $confirmation = new Confirmation();
        add_shortcode($this->shortcode_confirmation, array($confirmation, 'create_confirmation'));
        $disabler = new ButtonDisabler();
        add_shortcode($this->shortcode_disabler, array($disabler, 'create_disabler'));
        $scroll = new Scroll();
        add_shortcode($this->shortcode_scroll, array($scroll, 'create_scroll'));
        $request = new RequestForm();
        add_shortcode($this->shortcode_request, array($request, 'create_shortcode'));
    }

    function custom_post_type() {
        $metabox = new SubscriptionMetaBox($this->post_type_name);
        $metabox->add_post_type();
    }

    function settings() {
        if (is_admin()) {
            $settings = new Settings();
            add_action('admin_menu', array($settings, 'add_menu'));
            add_action('admin_init', array($settings, 'register_settings'));
        }
    }

    function add_taxonomy_to_filter() {
        add_action('restrict_manage_posts', array($this, 'deregister_filter_post_type_by_taxonomy'));
        add_filter('parse_query', array($this, 'deregister_convert_id_to_term_in_query'));
    }

    function deregister_filter_post_type_by_taxonomy()
    {
        global $typenow;
        $post_type = 'sub_provider';
        $taxonomy = 'deregister_category';
        if ($typenow == $post_type) {
            $selected = isset($_GET[$taxonomy]) ? $_GET[$taxonomy] : '';
            $info_taxonomy = get_taxonomy($taxonomy);
            wp_dropdown_categories(array(
                'show_option_all' => sprintf(__('Alle %s', 'textdomain'), $info_taxonomy->label),
                'taxonomy' => $taxonomy,
                'name' => $taxonomy,
                'orderby' => 'name',
                'selected' => $selected,
                'show_count' => true,
                'hide_empty' => true,
            ));
        };
    }

    function deregister_convert_id_to_term_in_query($query)
    {
        global $pagenow;
        $post_type = 'sub_provider';
        $taxonomy = 'deregister_category';
        $q_vars = &$query->query_vars;
        if ($pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == $post_type && isset($q_vars[$taxonomy]) && is_numeric($q_vars[$taxonomy]) && $q_vars[$taxonomy] != 0) {
            $term = get_term_by('id', $q_vars[$taxonomy], $taxonomy);
            $q_vars[$taxonomy] = $term->slug;
        }
    }
}

function deregister_remove() {

}


if (class_exists('DeregisterPlugin')) {
    $pluginHandler = new DeregisterPlugin();
    $pluginHandler->shortcodes();
    $pluginHandler->ajax_actions();
    $pluginHandler->settings();
    $pluginHandler->add_taxonomy_to_filter();


    register_activation_hook(__FILE__, array($pluginHandler, 'activate'));

    register_deactivation_hook(__FILE__, array($pluginHandler, 'deactivate'));

    register_uninstall_hook(__FILE__, 'deregister_remove');
}