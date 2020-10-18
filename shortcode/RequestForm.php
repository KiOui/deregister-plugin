<?php

class RequestForm {

    function __construct() {
        $settings = new Settings();
        $this->site_key = $settings->get_captcha_site_key();
    }

	function include_scripts() {
        wp_enqueue_script("deregister-request-form", "/wp-content/plugins/deregister-plugin/js/deregister-request-form.js", array("jquery"));
        wp_localize_script('deregister-request-form', 'ajax_vars', array('ajax_url'=>admin_url('admin-ajax.php')));
        wp_localize_script('deregister-request-form', 'deregister_request_form_vars', array('action'=>'deregister_request_form_submit'));

        if ($this->site_key) {
            wp_enqueue_script("deregister-google-recaptcha", "https://www.google.com/recaptcha/api.js?render=" . $this->site_key, array());
            wp_enqueue_script("deregister-google-recaptcha-integration", "/wp-content/plugins/deregister-plugin/js/deregister-recaptcha-integration.js", array('deregister-google-recaptcha'));
        }
	}

    function include_styles() {
        wp_enqueue_style("deregister-reqeust-form", "/wp-content/plugins/deregister-plugin/css/deregister-request-form.css");
    }

    function create_shortcode() {
    	$this->include_scripts();
        $this->include_styles();
    	$sent = filter_var($_GET['sent'], FILTER_VALIDATE_BOOLEAN);
        ob_start();
        ?>
            <div class="deregister-request-form">
            <div style="display: none;" class="deregister-loading-icon" id="deregister-loading-icon-id"><img src="/wp-content/plugins/deregister-plugin/gif/loader.gif"></div>
        <?php
    	if ($sent) {
    		?>
    		  <div class="deregister-alert-succeeded">De verzoek mail is verzonden</div>
    		<?php
    	}
    	?>
        	<p class="deregister-title">Naam van het abonnement</p>
            <input type='text' name='subscription_name' id='deregister_request_form_requested_subscription'/><br>
            <p class="deregister-title">Uw naam</p>
            <input type='email' name='email' id='deregister_request_form_name'/><br>
            <p class="deregister-title">Uw e-mail adres</p>
            <input type='text' name='name' id='deregister_request_form_email'/><br>
            <p class="deregister-title">Eventuele opmerkingen</p>
        <?php
        $settings = array('media_buttons' => false, 'quicktags' => false, 'teeny' => true, 'textarea_rows' => 5);
        wp_editor('', 'deregister_request_form_editor', $settings);
        if ($this->site_key) {
    	   ?>
                <input type='submit' value='Verzenden' onclick='deregister_captcha_send("<?php echo $this->site_key; ?>", "deregister_request_form_submit", deregister_request_form_send_captcha)'/>
            <?php
        }
        else {
            ?>
                <input type='submit' value='Verzenden' onclick='deregister_request_form_send()'/>
            <?php
        }
        ?>
            </div>
        <?php
        return ob_get_clean();
    }
}