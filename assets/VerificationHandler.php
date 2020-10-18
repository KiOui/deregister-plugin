<?php

require_once ABSPATH."wp-content/plugins/deregister-plugin/assets/DatabaseHandler.php";
require_once ABSPATH."wp-content/plugins/deregister-plugin/assets/Settings.php";
require_once ABSPATH."wp-content/plugins/deregister-plugin/fpdf/fpdf.php";
require_once ABSPATH."wp-content/plugins/deregister-plugin/assets/EmailReader.php";
require_once ABSPATH."wp-content/plugins/deregister-plugin/assets/LetterGenerator.php";
require_once ABSPATH."wp-content/plugins/deregister-plugin/assets/UserInformation.php";
require_once ABSPATH."wp-content/plugins/deregister-plugin/assets/FolderHandler.php";

class VerificationHandler {

    private $user_information;

    private $cookie_list;
    private $SUBJECT = "Kanikervanaf: ";

    /**
     * VerificationHandler constructor.
     * @param $firstname string first name of the user
     * @param $lastname string last name of the user
     * @param $address string address of the user
     * @param $postalcode string postal code of the user
     * @param $residence string residence of the user
     * @param $email string email address of the user
     * @param $cookie_list array an array of items stored in the cookie
     * @throws Exception if mail template can not be read
     */
    function __construct($firstname, $lastname, $address, $postalcode, $residence, $email, $cookie_list) {

        $this->user_information = new UserInformation($email, $firstname, $lastname, $address, $postalcode, $residence);

        $this->cookie_list = array();
        $dbHandler = new DatabaseHandler();

        foreach ($cookie_list as $item) {
            $subscription = $dbHandler->get_post_object($item);
            if ($subscription) {
                $this->cookie_list[] = $subscription;
            }
        }
    }

    /**
     * Updates the amounts used of the list that succeeded sending
     * @param $update_list array list of the items that need updating (as objects Subscription)
     */
    function update_amounts_used($update_list)
    {
        $dbHandler = new DatabaseHandler();
        foreach ($update_list as $item) {
            $dbHandler->update_amount_used($item->get_id());
        }
    }

    /**
     * Sends the emails according to the subscription providers stored in the cookie_list variable, also generates letters
     * @return boolean True if mails were send
     */
    function send_emails() {
        $settings = new Settings();
        $mail_companies = $settings->get_mailto_companies();
        $failed_list_mails = array();
        $mail_reader = new EmailReader(dirname(__FILE__) . '/mails', $this->user_information);
        foreach ($this->cookie_list as $item) {
            if (!$item->has_email()) {
            	$failed_list_mails[] = $item;
            }
            else {
                $template = $mail_reader->get_companies_mail($item, $mail_companies);
                if (!$this->mail_to($item, $template, $mail_companies)) {
                    $failed_list_mails[] = $item;
                }
	        }
        }
        $this->update_amounts_used($this->cookie_list);


        $attachments = array();

        $failed_list_letters = array();
        foreach ($this->cookie_list as $item) {
            if (!$item->has_address()) {
                $failed_list_letters[] = $item;
            }
            else {
                $letter_generator = new LetterGenerator($this->user_information);
                $pdf = $letter_generator->generate_letter_string($item);
                if ($pdf) {
                	$folder_writer = new FolderHandler();
                	$file = $folder_writer->store_file($pdf, "pdf");
                	if ($file) {
                		$attachments[] = $file;
                	}
                	else {
                		$failed_list_letters[] = $item;
                	}
                }
                else {
                    $failed_list_letters[] = $item;
                }
            }
        }



        return $this->send_confirmation_mail($attachments, $failed_list_mails, array_diff($this->cookie_list,
            $failed_list_mails), $failed_list_letters, array_diff($this->cookie_list, $failed_list_letters),
            $mail_companies);
    }

    /**
     * Sends one email to the user
     * @param $item Subscription string the name of the subscription provider the email is about
     * @param $template string the email to send
     * @param $mail_companies boolean if True the emails will be send to the companies
     * @return boolean True if the mail delivery succeeded, False otherwise
     */
    function mail_to($item, $template, $mail_companies)
    {
        $headers_mail = array();
        $headers_mail[] = "From: kanikervanaf.nl <noreply@kanikervanaf.nl>";

        if ($mail_companies) {
            $headers_mail[] = "Reply-To: " . $this->user_information->get_first_name() . ' ' . $this->user_information->get_last_name() . '<' . $this->user_information->get_email() . '>';
            $headers_mail[] = "Cc: " . $this->user_information->get_email();
            return wp_mail($item->get_email(), $this->SUBJECT . $item->get_name(), $template, $headers_mail);
        } else {
            return wp_mail($this->user_information->get_email(), $this->SUBJECT . $item->get_name(), $template, $headers_mail);
        }

    }

    /**
     * @param $pdf_attachments array Absolute paths as string to attach to the verification email
     * @param $failed_deliveries_mail array All failed email deliveries as Subscription objects
     * @param $succeeded_deliveries_mail array All succeeded email deliveries as Subscription objects
     * @param $failed_deliveries_letter array All failed generated letters as Subscription objects
     * @param $succeeded_deliveries_letter array All succeeded generated letters as Subscription objects
     * @param $mail_companies boolean Whether or not to mail companies directly, True for direct mails to companies
     * @return boolean True if verification mail was successfully send
     */
    function send_confirmation_mail($pdf_attachments, $failed_deliveries_mail, $succeeded_deliveries_mail,
                                    $failed_deliveries_letter, $succeeded_deliveries_letter, $mail_companies) {

        $email_reader = new EmailReader(dirname(__FILE__) . '/mails', $this->user_information);

        if ($mail_companies) {
            $template_mail = $email_reader->get_confirmation_mail($failed_deliveries_mail, $succeeded_deliveries_mail, $failed_deliveries_letter, $succeeded_deliveries_letter);
        }
        else {
            $template_mail = $email_reader->get_confirmation_forward_mail($failed_deliveries_mail, $succeeded_deliveries_mail, $failed_deliveries_letter, $succeeded_deliveries_letter);
        }

        $headers_mail = array();
        $headers_mail[] = "From: kanikervanaf.nl <noreply@kanikervanaf.nl>";
        $headers_mail[] = "Content-Type: text/html";

        $retvalue = wp_mail($this->user_information->get_email(), "Kanikervanaf: Mails verzonden", $template_mail, $headers_mail, $pdf_attachments);

        $folder_handler = new FolderHandler();
        $folder_handler->remove_files($pdf_attachments);

        return $retvalue;
    }
}