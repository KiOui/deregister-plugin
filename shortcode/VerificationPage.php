<?php

require_once ABSPATH."wp-content/plugins/deregister-plugin/assets/VerificationHandler.php";
require_once ABSPATH."wp-content/plugins/deregister-plugin/assets/DatabaseHandler.php";

class VerificationPage {

    private $SUCCEEDED = "Uw email adres is geverifieerd! De mails zijn naar uw email adres verzonden!";
    private $ERROR_MAIL_SERVER = "Onze mailserver heeft een foutcode terug gestuurd. Probeer het later opnieuw.";
    private $ERROR_TOKEN_EXPIRED = "U heeft te lang gewacht met het verifieren van uw mailadres. Wij kunnen geen mails versturen omdat uw gegevens al van onze servers zijn verwijderd.";
    private $ERROR_DATABASE = "Er is een probleem opgetreden bij onze database. Probeer het later opnieuw.";
    private $ERROR_TOKEN_INVALID = "De token die u probeert te verifieren is niet bij ons bekend.";

    /**
     * Verifies given token via GET request parameter and creates emails to send to the user.
     */
	function create_verification() {
		global $wpdb;
		if (isset($_GET['token'])) {
			$token = $_GET['token'];
			$table_name = $wpdb->prefix . 'deregister_sessions';
			$row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE session_id = '%s';", $token), ARRAY_A);
			if (is_null($row)) {
				return $this->ERROR_TOKEN_INVALID;
			}
			else {
				try {
					if ($this->check_date(new DateTime($row['expires_at']))) {
						$subscription_list = explode(',', $row['subscriptions']);
						$verificationHandler = new VerificationHandler($row['firstname'], $row['lastname'], $row['address'], $row['postalcode'], $row['residence'], $row['email'], $subscription_list);
						if ($verificationHandler->send_emails()) {
							$dbHandler = new DatabaseHandler();
							$dbHandler->remove_expired_tokens();
							$dbHandler->remove_token($token);
                            return $this->SUCCEEDED;
						}
						else {
							return $this->ERROR_MAIL_SERVER;
						}
					}
					else {
					    return $this->ERROR_TOKEN_EXPIRED;
					}
				} catch (Exception $e) {
					return $this->ERROR_DATABASE;
				}
			}
		}
	}

    /**
     * Checks if a token is already expired
     * @param $date_to_check DateTime the date of the token
     * @return bool True if the token has not expired, False otherwise
     * @throws Exception if the DateTime can't be compared
     */
	function check_date($date_to_check) {
		$date = new DateTime('now');
		return $date_to_check > $date;
	}
}