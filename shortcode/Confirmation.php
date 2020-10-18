<?php


class Confirmation {

    /**
     * Creates the mail form
     * @return string the mail form
     */
	function create_confirmation() {
	    $this->include_scripts();
	}

    /**
     * Includes scripts used by the mail form
     */
	function include_scripts() {
		wp_enqueue_script("confirmation", "/wp-content/plugins/deregister-plugin/js/confirmation.js", array("jquery"));
		wp_localize_script('confirmation', 'ajax_vars', array('ajax_url'=>admin_url('admin-ajax.php')));
	}

}