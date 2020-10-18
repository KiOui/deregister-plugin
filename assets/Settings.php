<?php
    
    class Settings {

        function register_settings() {
            register_setting('deregister-options', 'deregister-mailto-companies', array(
                'sanitize_callback'=>array($this, 'sanitize_boolean_default_false'),
                'type'=>'boolean',
                'default'=>'false'
            ));
            register_setting('deregister-options', 'deregister-captcha-site-key', array(
                'sanitize_callback'=>array($this, 'sanitize_captcha'),
                'type'=>'string',
                'default'=>''
            ));
            register_setting('deregister-options', 'deregister-captcha-secret-key', array(
                'sanitize_callback'=>array($this, 'sanitize_captcha'),
                'type'=>'string',
                'default'=>''
            ));
            register_setting('deregister-options', 'deregister-administrator-email', array(
                'sanitize_callback'=>array($this, 'sanitize_email'),
                'type'=>'string',
                'default'=>''
            ));
        }

        function sanitize_boolean_default_false($input) {
            $filtered = filter_var($input, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if (is_null($filtered)) {
                return False;
            }
            else {
                return $filtered;
            }
        }

        function sanitize_captcha($input) {
            return sanitize_text_field($input);
        }

        function sanitize_email($input) {
            $filtered = filter_var($input, FILTER_VALIDATE_EMAIL);
            if ($filtered) {
                return $filtered;
            }
            else {
                return '';
            }
        }

        function add_menu() {
            add_options_page('Subscription settings', 'Subscription menu', 'manage_options', 'subscription-settings-page', array($this, 'create_settings_page'));
        }

        function create_settings_page() {
            ?>
            <div class="wrap">
            <h1>Subscription plugin settings</h1>

            <form method="post" action="options.php">
                <?php settings_fields( 'deregister-options' ); ?>
                <?php do_settings_sections( 'deregister-options' ); ?>
                <table class="form-table">
                    <tr valign="top">
                    <th scope="row">Mail companies directly</th>
                    <td><input type="checkbox" name="deregister-mailto-companies" 
                        <?php 
                            if (get_option('deregister-mailto-companies')) {
                                echo 'checked';
                            }
                        ?>
                        />
                    </td>
                    </tr>
                    <tr valign="top">
                    <th scope="row">Captcha site key</th>
                    <td><input type="text" name="deregister-captcha-site-key" value="<?php echo esc_attr( get_option('deregister-captcha-site-key') ); ?>" /></td>
                    </tr>
                     
                    <tr valign="top">
                    <th scope="row">Captcha secret key</th>
                    <td><input type="text" name="deregister-captcha-secret-key" value="<?php echo esc_attr( get_option('deregister-captcha-secret-key') ); ?>" /></td>
                    </tr>

                    <tr valign="top">
                    <th scope="row">Administrator email adres</th>
                    <td><input type="text" name="deregister-administrator-email" value="<?php echo esc_attr( get_option('deregister-administrator-email') ); ?>" /></td>
                    </tr>
                </table> 
                <?php submit_button(); ?>
            </form>
            </div>
            <?php
        }

        function get_captcha_site_key() {
            if (empty(get_option('deregister-captcha-secret-key')) || empty(get_option('deregister-captcha-site-key'))) {
                return False;
            }
            else {
                return get_option('deregister-captcha-site-key');
            }
        }

        function get_captcha_secret_key() {
            if (empty(get_option('deregister-captcha-secret-key')) || empty(get_option('deregister-captcha-site-key'))) {
                return False;
            }
            else {
                return get_option('deregister-captcha-secret-key');
            }
        }

        function get_mailto_companies() {
            if (empty(get_option('deregister-mailto-companies'))) {
                return False;
            }
            else {
                return get_option('deregister-mailto-companies');
            }
        }

        function get_admin_email() {
            if (empty(get_option('deregister-administrator-email'))) {
                return get_option('admin_email');
            }
            else {
                return get_option('deregister-administrator-email');
            }
        }
    }