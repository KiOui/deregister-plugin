<?php


class MailForm {

    /**
     * Creates the mail form
     * @return string the mail form
     */
	function create_form() {
	    $this->include_scripts();
	    ob_start();
	    ?>
		<form>
			Uw e-mail: <input type='email' name='email' id='email'/><br>
			Voornaam: <input type='text' name='firstname' id='firstname'/><br>
			Achternaam: <input type='text' name='lastname' id='lastname'/><br>
            Adres: <input type='text' name='address' id='address'/><br>
            Postcode: <input type='text' name='postalcode' id='postalcode'/><br>
            Plaats: <input type='text' name='residence' id='residence'/><br>
		</form>
		<?php
		return ob_get_clean();
	}

    /**
     * Includes scripts used by the mail form
     */
	function include_scripts() {
		wp_enqueue_script("general", "/wp-content/plugins/deregister-plugin/js/general.js", array("jquery"));
		wp_localize_script('general', 'ajax_vars', array('ajax_url'=>admin_url('admin-ajax.php')));
		wp_enqueue_script("details", "/wp-content/plugins/deregister-plugin/js/details.js", array("jquery"));
	}

}