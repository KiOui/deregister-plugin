<?php

require_once ABSPATH."wp-content/plugins/deregister-plugin/assets/DatabaseHandler.php";

class MailFormHandler {

    private $SUBJECT = "Kanikervanaf: Verifieer uw email adres";

    function handle_ajax($list, $details) {
        $user_information = $this->set_parameters($details["first_name"], $details["second_name"], $details["address"], $details["postal_code"], $details["residence"], str_replace(' ', '', $details["email"]));
        if (!$user_information) {
            return False;
        }
        else {
            return $this->store_data($list, $user_information);
        }
    }

    function read_email() {
        $retvalue = file_get_contents(dirname(__FILE__) . '/mails/verification_mail.html');
        return $retvalue;
    }
    
    /**
     * Set parameters of this class.
     */
    function set_parameters($firstname, $lastname, $address, $postalcode, $residence, $email) {
        if (is_null($firstname) || is_null($email)) {
            return False;
        }
        $firstname = $this->validate_input($firstname);
        $email = str_replace(' ', '', $email);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return False;
        }

        if (is_null($lastname)) {
            $lastname = "";
        }
        else {
            $lastname = $this->validate_input($lastname);
        }
        if (is_null($address)) {
            $address = "";
        }
        else {
            $address = $this->validate_input($address);
        }
        if (is_null($postalcode)) {
            $postalcode = "";
        }
        else {
            $postalcode = $this->validate_input($postalcode);
        }
        if (is_null($residence)) {
            $residence = "";
        }
        else {
            $residence = $this->validate_input($residence);
        }

        return new UserInformation($email, $firstname, $lastname, $address, $postalcode, $residence);
    }

    function validate_input($string) {
    	return trim(stripslashes(htmlspecialchars($string)));
    }

    /**
     * This function creates an email to send to the user for verification.
     * @param $token string The token to use in the URL send within the email.
     * @return mixed returns a string (with an email message).
     */
    function create_mail($token, $user_information) {
        $email_reader = new EmailReader(dirname(__FILE__) . '/mails', $user_information);
        $link = get_site_url() . "/verificatie?token=__token__";
        $link = str_replace("__token__", $token, $link);
        $template = $email_reader->get_verification_mail($link);
        if ($template === False) {
            return False;
        }
        return $template;
    }

    /**
     * Stores data that the user entered in the sessions database together with a session token. This token will be
     * emailed to the user.
     * NOTE: Wordpress will die afterwards.
     * @param $user_information UserInformation
     * @param $list List of items
     * @return True
     */
    function store_data($list, $user_information) {
        global $wpdb;
        $list = $this->clean_list($list);
        if (sizeof($list) == 0) {
            $url = get_site_url();
            header("Location: $url");
            return False;
        }
        else {
            $token = $this->random_token();
            $table_name = $wpdb->prefix . 'deregister_sessions';
            try {
                $expires = $this->expires();
            } catch (Exception $e) {
                $expires = 0;
            }
            $wpdb->insert($table_name, array('session_id'=>$token,
                'firstname'=>$user_information->get_first_name(),
                'lastname'=>$user_information->get_last_name(),
                'address'=>$user_information->get_address(),
                'postalcode'=>$user_information->get_postalcode(),
                'residence'=>$user_information->get_residence(),
                'email'=>$user_information->get_email(),
                'expires_at'=>$expires,
                'subscriptions'=>implode(',', $list)));
            $mail_to_send = $this->create_mail($token, $user_information);
            if ($mail_to_send === False) {
                //Mail error, mail niet gevonden!
                return False;
            }
            return $this->mail_confirmation($user_information, $mail_to_send);
        }
    }

    function clean_list($list) {
        $dbHandler = new DatabaseHandler();
        $list = array_unique($list);
        $newlist = [];
        foreach ($list as $item) {
            if ($dbHandler->exists($item)) {
                $newlist[] = $item;
            }
        }
        return $newlist;
    }

    /**
     * Sends the confirmation email
     * @param $mail string The email to be send
     * @return True if the confirmation mail was successfully send
     */
    function mail_confirmation($user_information, $mail) {
        $headers_mail[] = "Content-Type: text/html; charset=UTF-8";
        #$separator = md5(time());
        #$headers_mail[] = "Content-Type: multipart/mixed; boundary=\"".$separator."\"";
        $headers_mail[] = "From: kanikervanaf.nl <noreply@kanikervanaf.nl>";
        #$mail = "--".$separator.PHP_EOL."Content-Type: text/html; charset=UTF-8".PHP_EOL.PHP_EOL.$mail.PHP_EOL.PHP_EOL."--".$separator."--".PHP_EOL;

        return wp_mail($user_information->get_email(), $this->SUBJECT, $mail, $headers_mail);
    }

    /**
     * Returns a formatted datetime string (15 minutes from now)
     * @return string a formatted datetime string
     * @throws Exception if format cannot happen
     */
    function expires() {
        $date = new DateTime('now');
        $date->add(new DateInterval("PT900S"));
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * Generates a random token for user identification purposes.
     * The token has to be unique in the sessions database.
     * Token generation will be tried 10 times before wp_die() will be called.
     * @return string a random token of 16 bytes in hexadecimal notation
     */
    function random_token() {
        global $wpdb;
        for ($i = 0; $i < 10; $i++) {
            $rand_token = openssl_random_pseudo_bytes(16);
            $token = bin2hex($rand_token);
            $table_name = $wpdb->prefix . 'deregister_sessions';
            $row = $wpdb->get_row("SELECT session_id FROM $table_name WHERE session_id = '$token';");
            if (!$row) {
                return $token;
            }
        }
        wp_die();
    }
}
