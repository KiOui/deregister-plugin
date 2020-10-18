<?php


class ButtonDisabler {

    /**
     * Creates the mail form
     * @return string the mail form
     */
	function create_disabler() {
	    $this->include_scripts();
	}

    /**
     * Includes scripts used by the mail form
     */
	function include_scripts() {
		wp_enqueue_script("buttons", "/wp-content/plugins/deregister-plugin/js/buttons.js", array("jquery"));
		wp_localize_script('buttons', 'ajax_vars', array('ajax_url'=>admin_url('admin-ajax.php')));
		wp_enqueue_script("general", "/wp-content/plugins/deregister-plugin/js/general.js", array("jquery"));
		wp_localize_script('general', 'ajax_vars', array('ajax_url'=>admin_url('admin-ajax.php')));
	}

}