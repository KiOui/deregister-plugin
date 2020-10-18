<?php

require_once ABSPATH."wp-content/plugins/deregister-plugin/assets/Subscription.php";

class EmailReader
{

    private $MAIL_VERIFICATION = "verification_mail.html";
    private $MAIL_CONFIRMATION = "confirmation_mail.html";
    private $MAIL_CONFIRMATION_FORWARD = "confirmation_mail_forward.html";
    private $MAIL_REQUEST = "request_mail.html";
    private $MAIL_COMPANIES = "standard_mail.txt";
    private $LETTER_COMPANIES = "standard_letter.txt";

    private $REPLACE_SUBSCRIPTION_NAME = "__subscription__";
    private $REPLACE_FIRST_NAME_UI = "__firstname_ui__";
    private $REPLACE_LAST_NAME_UI = "__lastname_ui__";
    private $REPLACE_FIRSTNAME = "__firstname__";
    private $REPLACE_LASTNAME = "__lastname__";
    private $REPLACE_ADDRESS = "__address__";
    private $REPLACE_POSTALCODE = "__postalcode__";
    private $REPLACE_RESIDENCE = "__residence__";
    private $REPLACE_SENDTO = "__sendto__";
    private $REPLACE_TOKEN = "__verification_link__";
    private $REPLACE_EMAIL = "__email_address__";
    private $REPLACE_EXTRA_INFO = "__extra_information__";
    private $REPLACE_NAME = "__name__";

    private $REPLACE_SEND_CONFIRMATION_EMAIL = "__send_confirmation_emails__";
    private $REPLACE_UNSEND_CONFIRMATION_EMAIL = "__unsend_confirmation_emails__";

    private $SENTENCE_SEND_EMAILS = "<br>De volgende emails zijn verzonden:<br>";
    private $SENTENCE_UNSEND_EMAILS = "<br>De volgende emails zijn niet verzonden omdat wij (nog) geen emailadres van deze partij hebben:<br>";

    private $REPLACE_SEND_CONFIRMATION_LETTER = "__send_letters__";
    private $REPLACE_UNSEND_CONFIRMATION_LETTER = "__unsend_letters__";

    private $SENTENCE_SEND_LETTERS = "<br>De volgende brieven zijn gegenereerd:<br>";
    private $SENTENCE_UNSEND_LETTERS = "<br>De volgende brieven zijn niet gegenereerd omdat wij (nog) niet voldoende gegevens van de desbetreffende partij hebben:<br>";

    private $REPLACE_FORWARD = "__forward__";

    private $PRE_FIRST_NAME = "Voornaam: ";
    private $PRE_LAST_NAME = "Achternaam: ";
    private $PRE_ADDRESS = "Adres: ";
    private $PRE_POSTALCODE = "Postcode: ";
    private $PRE_RESIDENCE = "Woonplaats: ";

    private $REDIRECT = "Doorsturen naar: __sendto__\n\n";

    private $base_folder;
    private $user_information;

    /**
     * EmailReader constructor.
     * @param $base_folder string absolute path to folder where templates for letters and emails are stored
     * @param $user_information UserInformation information the user entered
     */
    function __construct($base_folder, $user_information) {
        $this->base_folder = $base_folder;
        $this->user_information = $user_information;
    }

    /**
     * @param $failed_deliveries_email array the failed deliveries for emails
     * @param $succeeded_deliveries_email array the succeeded deliveries for emails
     * @param $failed_deliveries_letter array the failed deliveries for letters
     * @param $succeeded_deliveries_letter array the succeeded deliveries for letters
     * @return bool|mixed False if the template could not be loaded, the template with replaced strings otherwise
     */
    function get_confirmation_mail($failed_deliveries_email, $succeeded_deliveries_email, $failed_deliveries_letter, $succeeded_deliveries_letter) {
        $template = file_get_contents($this->base_folder . '/' . $this->MAIL_CONFIRMATION);

        if (!$template) {
            return False;
        }

        return $this->replace_information_confirmation_mail($template, $failed_deliveries_email, $succeeded_deliveries_email, $failed_deliveries_letter, $succeeded_deliveries_letter);
    }

    /**
     * @param $failed_deliveries_email array the failed deliveries for emails
     * @param $succeeded_deliveries_email array the succeeded deliveries for emails
     * @param $failed_deliveries_letter array the failed deliveries for letters
     * @param $succeeded_deliveries_letter array the succeeded deliveries for letters
     * @return false|string False if the template could not be loaded, the template with replaced strings otherwise
     */
    function get_confirmation_forward_mail($failed_deliveries_email, $succeeded_deliveries_email, $failed_deliveries_letter, $succeeded_deliveries_letter)
    {
        $template = file_get_contents($this->base_folder . '/' . $this->MAIL_CONFIRMATION_FORWARD);

        if (!$template) {
            return False;
        }

        return $this->replace_information_confirmation_mail($template, $failed_deliveries_email, $succeeded_deliveries_email, $failed_deliveries_letter, $succeeded_deliveries_letter);
    }

    /**
     * @param $template
     * @param $failed_deliveries_email array the failed deliveries for emails
     * @param $succeeded_deliveries_email array the succeeded deliveries for emails
     * @param $failed_deliveries_letter array the failed deliveries for letters
     * @param $succeeded_deliveries_letter array the succeeded deliveries for letters
     * @return string the template with replaced strings
     */
    function replace_information_confirmation_mail($template, $failed_deliveries_email, $succeeded_deliveries_email, $failed_deliveries_letter, $succeeded_deliveries_letter) {

        $template = str_replace($this->REPLACE_FIRSTNAME, $this->user_information->get_first_name(), $template);

        if (sizeof($failed_deliveries_email) == 0) {
            $template= str_replace($this->REPLACE_UNSEND_CONFIRMATION_EMAIL, '', $template);
        }
        else {
            $template = str_replace($this->REPLACE_UNSEND_CONFIRMATION_EMAIL, $this->SENTENCE_UNSEND_EMAILS . $this->REPLACE_UNSEND_CONFIRMATION_EMAIL, $template);
            foreach ($failed_deliveries_email as $failed) {
                $template = str_replace($this->REPLACE_UNSEND_CONFIRMATION_EMAIL, $failed->get_name() . "<br>" . $this->REPLACE_UNSEND_CONFIRMATION_EMAIL, $template);
            }
            $template = str_replace($this->REPLACE_UNSEND_CONFIRMATION_EMAIL, "", $template);
        }

        if (sizeof($succeeded_deliveries_email) == 0) {
            $template = str_replace($this->REPLACE_SEND_CONFIRMATION_EMAIL, '', $template);
        }
        else {
            $template = str_replace($this->REPLACE_SEND_CONFIRMATION_EMAIL, $this->SENTENCE_SEND_EMAILS . $this->REPLACE_SEND_CONFIRMATION_EMAIL, $template);
            foreach ($succeeded_deliveries_email as $succeeded) {
                $template = str_replace($this->REPLACE_SEND_CONFIRMATION_EMAIL, $succeeded->get_name() . "<br>" . $this->REPLACE_SEND_CONFIRMATION_EMAIL, $template);
            }
            $template = str_replace($this->REPLACE_SEND_CONFIRMATION_EMAIL, "", $template);
        }

        if (sizeof($failed_deliveries_letter) == 0) {
            $template= str_replace($this->REPLACE_UNSEND_CONFIRMATION_LETTER, '', $template);
        }
        else {
            $template = str_replace($this->REPLACE_UNSEND_CONFIRMATION_LETTER, $this->SENTENCE_UNSEND_LETTERS . $this->REPLACE_UNSEND_CONFIRMATION_LETTER, $template);
            foreach ($failed_deliveries_letter as $failed) {
                $template = str_replace($this->REPLACE_UNSEND_CONFIRMATION_LETTER, $failed->get_name() . "<br>" . $this->REPLACE_UNSEND_CONFIRMATION_LETTER, $template);
            }
            $template = str_replace($this->REPLACE_UNSEND_CONFIRMATION_LETTER, "", $template);
        }

        if (sizeof($succeeded_deliveries_letter) == 0) {
            $template = str_replace($this->REPLACE_SEND_CONFIRMATION_LETTER, '', $template);
        }
        else {
            $template = str_replace($this->REPLACE_SEND_CONFIRMATION_LETTER, $this->SENTENCE_SEND_LETTERS . $this->REPLACE_SEND_CONFIRMATION_LETTER, $template);
            foreach ($succeeded_deliveries_letter as $succeeded) {
                $template = str_replace($this->REPLACE_SEND_CONFIRMATION_LETTER, $succeeded->get_name() . "<br>" . $this->REPLACE_SEND_CONFIRMATION_LETTER, $template);
            }
            $template = str_replace($this->REPLACE_SEND_CONFIRMATION_LETTER, "", $template);
        }

        return $template;
    }

    /**
     * @param $verification_link string the verification link
     * @return false|string False if the template could not be loaded, the template with replaced strings otherwise
     */
    function get_verification_mail($verification_link) {
        $template = file_get_contents($this->base_folder . '/' . $this->MAIL_VERIFICATION);

        if (!$template) {
            return False;
        }

        $template = str_replace($this->REPLACE_FIRSTNAME, $this->user_information->get_first_name(), $template);
        $template = str_replace($this->REPLACE_TOKEN, $verification_link, $template);

        return $template;
    }

    /**
     * @param $item Subscription the item to generate a mail for
     * @param $mail_companies boolean determines if the mails are directly send to the companies
     * @return false|string False if the template could not be loaded, the template with replaced strings otherwise
     */
    function get_companies_mail($item, $mail_companies) {
        $template = file_get_contents($this->base_folder . '/' . $this->MAIL_COMPANIES);

        if (!$template) {
            return False;
        }

        $template = str_replace($this->REPLACE_SUBSCRIPTION_NAME, $item->get_name(), $template);
        $template = str_replace($this->REPLACE_FIRSTNAME, $this->user_information->get_first_name(), $template);
        $template = str_replace($this->REPLACE_FIRST_NAME_UI, $this->PRE_FIRST_NAME . $this->user_information->get_first_name() . "\n", $template);

        if ($this->user_information->has_last_name()) {
            $template = str_replace($this->REPLACE_LAST_NAME_UI, $this->PRE_LAST_NAME . $this->user_information->get_last_name() . "\n", $template);
            $template = str_replace($this->REPLACE_LASTNAME, $this->user_information->get_last_name(), $template);
        }
        else {
            $template = str_replace($this->REPLACE_LAST_NAME_UI, '', $template);
            $template = str_replace($this->REPLACE_LASTNAME, '', $template);
        }

        if ($this->user_information->has_valid_address()) {
            $template = str_replace($this->REPLACE_ADDRESS, $this->PRE_ADDRESS . $this->user_information->get_address() . "\n", $template);
            $template = str_replace($this->REPLACE_POSTALCODE, $this->PRE_POSTALCODE . $this->user_information->get_postalcode() . "\n", $template);
            $template = str_replace($this->REPLACE_RESIDENCE, $this->PRE_RESIDENCE . $this->user_information->get_residence() . "\n", $template);
        }
        else {
            $template = str_replace($this->REPLACE_ADDRESS, '', $template);
            $template = str_replace($this->REPLACE_POSTALCODE, '', $template);
            $template = str_replace($this->REPLACE_RESIDENCE, '', $template);
        }

        if (!$mail_companies) {
            $template = str_replace($this->REPLACE_FORWARD, str_replace($this->REPLACE_SENDTO, $item->get_email(), $this->REDIRECT), $template);
        }
        else {
            $template = str_replace($this->REPLACE_FORWARD, '', $template);
        }

        return $template;
    }

    /**
     * @param $item Subscription the item to generate a letter for
     * @return false|string False if the template could not be loaded, the template with replaced strings otherwise
     */
    function get_companies_letter($item) {
        $template = file_get_contents($this->base_folder.  '/' . $this->LETTER_COMPANIES);

        if (!$template) {
            return False;
        }

        $template = str_replace($this->REPLACE_SUBSCRIPTION_NAME, $item->get_name(), $template);
        $template = str_replace($this->REPLACE_FIRSTNAME, $this->user_information->get_first_name(), $template);
        $template = str_replace($this->REPLACE_FIRST_NAME_UI, $this->PRE_FIRST_NAME . $this->user_information->get_first_name() . "\n", $template);

        if ($this->user_information->has_last_name()) {
            $template = str_replace($this->REPLACE_LAST_NAME_UI, $this->PRE_LAST_NAME . $this->user_information->get_last_name() . "\n", $template);
            $template = str_replace($this->REPLACE_LASTNAME, $this->user_information->get_last_name(), $template);
        }
        else {
            $template = str_replace($this->REPLACE_LAST_NAME_UI, '', $template);
            $template = str_replace($this->REPLACE_LASTNAME, '', $template);
        }

        if ($this->user_information->has_valid_address()) {
            $template = str_replace($this->REPLACE_ADDRESS, $this->PRE_ADDRESS . $this->user_information->get_address() . "\n", $template);
            $template = str_replace($this->REPLACE_POSTALCODE, $this->PRE_POSTALCODE . $this->user_information->get_postalcode() . "\n", $template);
            $template = str_replace($this->REPLACE_RESIDENCE, $this->PRE_RESIDENCE . $this->user_information->get_residence() . "\n", $template);
        }
        else {
            $template = str_replace($this->REPLACE_ADDRESS, '', $template);
            $template = str_replace($this->REPLACE_POSTALCODE, '', $template);
            $template = str_replace($this->REPLACE_RESIDENCE, '', $template);
        }

        return $template;
    }

    function get_request_mail($subscription, $extra_information) {
        $template = file_get_contents($this->base_folder . '/' . $this->MAIL_REQUEST);

        if (!$template) {
            return False;
        }

        $template = str_replace($this->REPLACE_SUBSCRIPTION_NAME, $subscription, $template);
        $template = str_replace($this->REPLACE_NAME, $this->user_information->get_first_name(), $template);
        $template = str_replace($this->REPLACE_EMAIL, $this->user_information->get_email(), $template);
        $template = str_replace($this->REPLACE_EXTRA_INFO, $extra_information, $template);

        return $template;
    }
}