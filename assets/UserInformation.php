<?php


class UserInformation
{

    private $first_name;
    private $last_name;
    private $address;
    private $postalcode;
    private $residence;
    private $email_address;

    function __construct($email_address, $first_name, $last_name, $address, $postalcode, $residence)
    {
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->address = $address;
        $this->postalcode = $postalcode;
        $this->residence = $residence;
        $this->email_address = $email_address;
    }

    function get_email() {
        return $this->email_address;
    }

    function has_valid_address() {
        return $this->address != '' && $this->postalcode != '' && $this->residence != '';
    }

    function get_address() {
        return $this->address;
    }

    function get_postalcode() {
        return $this->postalcode;
    }

    function get_residence() {
        return $this->residence;
    }

    function has_last_name() {
        return $this->last_name != '';
    }

    function get_last_name() {
        return $this->last_name;
    }

    function get_first_name() {
        return $this->first_name;
    }
}