<?php


class Subscription
{

    private $type;
    private $price;
    private $mail;
    private $mail_answer_number;
    private $mail_postal_code;
    private $mail_city;
    private $correspondence_address;
    private $correspondence_postal_code;
    private $correspondence_city;
    private $phone_number;
    private $phone_number_free;
    private $used;

    function __construct($post_meta, $post_id, $sub_name) {
        $this->post_id = $post_id;
        $this->sub_name = $sub_name;
        $types = wp_get_post_terms($post_id, 'deregister_category');
        $this->type = array();
        foreach ($types as $type) {
            $this->type[] = $type->to_array()['term_id'];
        }
        if ($post_meta['deregister_mail']) {
            $this->mail = $post_meta['deregister_mail'][0];
        }
        else {
            $this->mail = "";
        }
        if ($post_meta['deregister_mail_answer_number']) {
            $this->mail_answer_number = $post_meta['deregister_mail_answer_number'][0];
        }
        else {
            $this->mail_answer_number = "";
        }
        if ($post_meta['deregister_mail_postal_code']) {
            $this->mail_postal_code = $post_meta['deregister_mail_postal_code'][0];
        }
        else {
            $this->mail_postal_code = "";
        }
        if ($post_meta['deregister_mail_city']) {
            $this->mail_city = $post_meta['deregister_mail_city'][0];
        }
        else {
            $this->mail_city = "";
        }
        if ($post_meta['deregister_correspondance_address']) {
            $this->correspondence_address = $post_meta['deregister_correspondance_address'][0];
        }
        else {
            $this->correspondence_address = "";
        }
        if ($post_meta['deregister_correspondance_postal_code']) {
            $this->correspondence_postal_code = $post_meta['deregister_correspondance_postal_code'][0];
        }
        else {
            $this->correspondence_postal_code = "";
        }
        if ($post_meta['deregister_correspondance_city']) {
            $this->correspondence_city = $post_meta['deregister_correspondance_city'][0];
        }
        else {
            $this->correspondence_city = "";
        }
        if ($post_meta['deregister_phonenumber']) {
            $this->phone_number = $post_meta['deregister_phonenumber'][0];
        }
        else {
            $this->phone_number = "";
        }
        if ($post_meta['deregister_phonenumber_remove_subscription']) {
            $this->phone_number_free = $post_meta['deregister_phonenumber_remove_subscription'][0];
        }
        else {
            $this->phone_number_free = "";
        }

        if ($post_meta['deregister_used']) {
            try {
                $this->used = intval($post_meta['deregister_used'][0]);
            }
            catch (Exception $e) {
                $this->used = 1;
            }
        }
        else {
            $this->used = 1;
        }

        if ($post_meta['deregister_price']) {
            try {
                $this->price = floatval($post_meta['deregister_price'][0]);
            }
            catch (Exception $e) {
                $this->price = 0;
            }
        }
        else {
            $this->price = 0;
        }
    }
    
    function to_json() {

        $types = $this->get_type();
        $type_names = array();
        foreach ($types as $type) {
            $type_names[] = get_term($type, 'deregister_category', ARRAY_A)['name'];
        }
        
        $json_array = array(
                           "name"=>$this->sub_name,
                           "price"=>$this->price,
                           "mail"=>$this->mail,
                           "mail_answer_number"=>$this->mail_answer_number,
                           "mail_postal_code"=>$this->mail_postal_code,
                           "mail_city"=>$this->mail_city,
                           "correspondence_address"=>$this->correspondence_address,
                           "correspondence_postal_code"=>$this->correspondence_postal_code,
                           "correspondence_city"=>$this->correspondence_city,
                           "phone_number"=>$this->phone_number,
                           "phone_number_free"=>$this->phone_number_free,
                           "used"=>$this->used,
                            "categories"=>$type_names
                           );
        return $json_array;
    }

    /**
     * @return int price of this item
     */
    function get_price() {
        return $this->price;
    }

    function get_type() {
        return $this->type;
    }

    function in_category($category_id) {
        foreach ($this->type as $type) {
            if ($type == $category_id) {
                return True;
            }
        }
        return False;
    }

    function get_id() {
        return $this->post_id;
    }

    function get_name() {
        return $this->sub_name;
    }

    function get_email() {
        return $this->mail;
    }

    function has_email() {
        return $this->mail != "";
    }

    function has_address() {
        return $this->has_correspondence_address() || $this->has_mail_address();
    }

    function has_correspondence_address() {
        return $this->correspondence_address != "" && $this->correspondence_postal_code != "";
    }

    function has_mail_address() {
        return $this->mail_answer_number != "" && $this->mail_postal_code != "";
    }

    function get_correspondence_address() {
        return array("address"=>$this->correspondence_address, "postal_code"=>$this->correspondence_postal_code, "city"=>$this->correspondence_city);
    }

    function get_mail_address() {
        return array("address"=>$this->mail_answer_number, "postal_code"=>$this->mail_postal_code, "city"=>$this->mail_city);
    }

    function increase_used() {
        update_post_meta($this->post_id, 'deregister_used', $this->used+1);
        $this->used = $this->used + 1;
    }

    function get_amount_used() {
        return $this->used;
    }

    function __toString()
    {
        return strval($this->type);
    }

    function is_valid() {
        return $this->has_email() || $this->has_correspondence_address() || $this->has_mail_address();
    }
}
