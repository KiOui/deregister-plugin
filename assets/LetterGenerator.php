<?php

require_once ABSPATH."wp-content/plugins/deregister-plugin/fpdf/fpdf.php";
require_once ABSPATH."wp-content/plugins/deregister-plugin/assets/Subscription.php";
require_once ABSPATH."wp-content/plugins/deregister-plugin/assets/UserInformation.php";
require_once ABSPATH."wp-content/plugins/deregister-plugin/assets/EmailReader.php";

class LetterGenerator
{
    public static $EOL = PHP_EOL;
    private $LETTER_NAME = "letter-__letter_company__.pdf";
    private $REPLACE_COMPANY_NAME = "__letter_company__";

    private $user_information;

    /**
     * LetterGenerator constructor.
     * @param $user_information UserInformation
     */
    function __construct($user_information)
    {
        $this->user_information = $user_information;
    }

    /**
     * @param $item Subscription
     * @param $separator string
     * @return string
     */
    function generate_letter_email($item, $separator) {

        $email_reader = new EmailReader(dirname(__FILE__) . '/mails', $this->user_information);
        $template = $email_reader->get_companies_letter($item);

        if ($item->has_mail_address()) {
            $address = $item->get_mail_address();
        }
        else if ($item->has_correspondence_address()) {
            $address = $item->get_correspondence_address();
        }
        else {
            return False;
        }

        $filename = str_replace($this->REPLACE_COMPANY_NAME, $item->get_name(), $this->LETTER_NAME);
        $pdf = new FPDF('P', 'mm', 'A4');
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',14);
        $pdf->Write(7, $address['address']);
        $pdf->Ln();
        $pdf->Write(7, $address['postal_code']);
        $pdf->Ln();
        $pdf->Write(7, $address['city']);
        $pdf->Ln();
        $pdf->Ln();
        $pdf->SetFont('Arial', '', 12);
        $pdf->Write(6, $template);
        // encode data (puts attachment in proper format)
        $pdfdoc = $pdf->Output("", "S");
        $attachment = chunk_split(base64_encode($pdfdoc));

        // attachment
        $body = "--".$separator.LetterGenerator::$EOL;
        $body .= "Content-Type: application/pdf; name=\"".$filename."\"".LetterGenerator::$EOL;
        $body .= "Content-Transfer-Encoding: base64".LetterGenerator::$EOL;
        $body .= "Content-Disposition: attachment".LetterGenerator::$EOL.LetterGenerator::$EOL;
        $body .= $attachment.LetterGenerator::$EOL;

        return $body;
    }

    function generate_letter_string($item) {
        $email_reader = new EmailReader(dirname(__FILE__) . '/mails', $this->user_information);
        $template = $email_reader->get_companies_letter($item);

        if ($item->has_mail_address()) {
            $address = $item->get_mail_address();
            $address['address'] = 'Postbus ' . $address['address'];
        }
        else if ($item->has_correspondence_address()) {
            $address = $item->get_correspondence_address();
        }
        else {
            return False;
        }

        $filename = str_replace($this->REPLACE_COMPANY_NAME, $item->get_name(), $this->LETTER_NAME);
        $pdf = new FPDF('P', 'mm', 'A4');
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',14);
        $pdf->Write(7, $address['address']);
        $pdf->Ln();
        $pdf->Write(7, $address['postal_code']);
        $pdf->Ln();
        $pdf->Write(7, $address['city']);
        $pdf->Ln();
        $pdf->Ln();
        $pdf->SetFont('Arial', '', 12);
        $pdf->Write(6, $template);
        // encode data (puts attachment in proper format)
        return $pdf->Output("", "S");
    }
}