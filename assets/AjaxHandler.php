<?php
    
    require_once ABSPATH."wp-content/plugins/deregister-plugin/assets/DatabaseHandler.php";
    require_once ABSPATH."wp-content/plugins/deregister-plugin/assets/MailFormHandler.php";
    require_once ABSPATH."wp-content/plugins/deregister-plugin/assets/Settings.php";
    require_once ABSPATH."wp-content/plugins/deregister-plugin/assets/EmailReader.php";
    require_once ABSPATH."wp-content/plugins/deregister-plugin/assets/UserInformation.php";
    
    class AjaxHandler {

        private $PAR_OPTION = "option";
        private $PAR_NAME = "name";
        private $PAR_MAXIMUM = "maximum";
        private $PAR_DETAILS = "details";
        private $PAR_LIST = "list";
        private $PAR_INDEX = "index";
        private $PAR_CATEGORY = "category";
        private $PAR_PAGE = "page";
        private $PAR_RETURN_CATEGORY = "show-category";

        private $ASK_CATEGORY = "category";
        private $ASK_SEARCH = "search";
        private $ASK_SEND = "send";
        private $ASK_TOP = "topfive";
        private $ASK_DETAILS = "details";
        private $ALL_POSTS = "all";
        private $GET_CHILD_CATEGORIES = "childs";
        private $ASK_CATEGORY_OF_ITEM = "category_of";
        private $ASK_ALL_DETAILS = "details_all";

        function __construct() {
            $this->settings = new Settings();
        }

        /**
         * Used to handle AJAX requests regarding database queries. The options are shown as private variables.
         */
        function query() {
            //$time_start = microtime(true);
            if (isset($_POST[$this->PAR_OPTION])) {
                $option = $_POST[$this->PAR_OPTION];
                if ($option == $this->ASK_CATEGORY) {
                    echo $this->get_categories();
                }
                else if ($option == $this->ASK_SEARCH) {
                    echo $this->search();
                }
                else if ($option == $this->ASK_SEND) {
                    echo $this->send_confirmation();
                }
                else if ($option == $this->ASK_TOP) {
                    echo $this->get_top();
                }
                else if ($option == $this->ASK_DETAILS) {
                    echo $this->get_details();
                }
                else if ($option == $this->ALL_POSTS) {
                    echo $this->get_all_posts();
                }
                else if ($option == $this->GET_CHILD_CATEGORIES) {
                    echo $this->get_child_categories();
                }
                else if ($option == $this->ASK_CATEGORY_OF_ITEM) {
                    echo $this->get_category();
                }
                else if ($option == $this->ASK_ALL_DETAILS) {
                    echo $this->get_all_post_details();
                }
                else {
                    echo json_encode(array("error"=>True, "errormsg"=>"Option unknown"));
                }
            }
            else {
                echo json_encode(array("error"=>True, "errormsg"=>"Option not specified"));
            }
            
            /*$time_end = microtime(true);
            $execution_time = ($time_end - $time_start);
            echo 'Total Execution Time:'.$execution_time.' Seconds';*/
            wp_die();
        }
        
        function get_details() {
            if (isset($_POST[$this->PAR_NAME])) {
                $page = get_page_by_title($_POST[$this->PAR_NAME], ARRAY_A, 'sub_provider');
                $dbHandler = new DatabaseHandler();
                $obj = $dbHandler->get_post_object($page['ID']);
                return json_encode($obj->to_json());
            }
            return json_encode(array("error"=>True, "errormsg"=>"No name specified"));
        }

        function get_all_post_details() {
            $dbHandler = new DatabaseHandler();
            $all_post_ids = $dbHandler->get_all_posts_sorted_name(False);
            $retvalue = array();
            foreach ($all_post_ids as $post_id) {
                $retvalue[] = $dbHandler->get_post_object($post_id)->to_json();
            }
            return json_encode($retvalue);
        }
        
        function get_all_posts() {
            $dbHandler = new DatabaseHandler();
            $all_post_ids = $dbHandler->get_all_posts_sorted_name(False);
            $retvalue = array();
            foreach ($all_post_ids as $post_id) {
                $retvalue[] = $dbHandler->get_post_object($post_id)->get_name();
            }
            return json_encode($retvalue);
        }

        function get_top() {
            $maximum = 0;
            if (isset($_POST[$this->PAR_MAXIMUM])) {
                $max_input = $_POST[$this->PAR_MAXIMUM];
                if (is_numeric($max_input)) {
                    $maximum = intval($max_input);
                }
            }
            else {
                $maximum = 0;
            }
            $dbHandler = new DatabaseHandler();
            return json_encode(array("error"=>False, "items"=>$dbHandler->get_top($maximum)));
        }
        
        function get_child_categories() {
            if (isset($_POST[$this->PAR_CATEGORY])) {
                $category = get_term_by('name', $_POST[$this->PAR_CATEGORY], 'deregister_category', ARRAY_A)['term_id'];
            }
            else {
                $category = False;
            }
            $dbHandler = new DatabaseHandler();
            $categories = $dbHandler->get_categories($category);
            $retvalue = array();
            foreach ($categories as $category) {
                $retvalue[] = $category["name"];
            }
            return json_encode($retvalue);
        }
        
        function get_category() {
            if (isset($_POST[$this->PAR_NAME])) {
                $page = get_page_by_title($_POST[$this->PAR_NAME], ARRAY_A, 'sub_provider');
                $dbHandler = new DatabaseHandler();
                $obj = $dbHandler->get_post_object($page['ID']);
                $types = $obj->get_type();
                $retvalue = array();
                foreach ($types as $type) {
                    $retvalue[] = get_term($type, 'deregister_category', ARRAY_A)['name'];
                }
                return json_encode($retvalue);
            }
            else {
                return json_encode(array("error"=>True, "errormsg"=>"No name specified"));
            }
        }

        function get_categories() {
            if (isset($_POST[$this->PAR_CATEGORY])) {
                $category = $_POST[$this->PAR_CATEGORY];
            }
            else {
                return json_encode(array("error"=>True, "errormsg"=>"No category specified"));
            }

            $maximum = -1;
            if (isset($_POST[$this->PAR_MAXIMUM])) {
                $max_input = $_POST[$this->PAR_MAXIMUM];
                if (is_numeric($max_input)) {
                    $maximum = intval($max_input);
                }
                else {
                    $maximum = -1;
                }
            }

            $show_category = False;
            if (isset($_POST[$this->PAR_RETURN_CATEGORY])) {
                $show_category = filter_var($_POST[$this->PAR_RETURN_CATEGORY], FILTER_VALIDATE_BOOLEAN);
            }

            $page = -1;
            if (isset($_POST[$this->PAR_PAGE])) {
                $options = array(
                    'options' => array(
                        'default' => 0,
                        'min_range' => 0
                    )
                );
                $page = filter_var($_POST[$this->PAR_PAGE], FILTER_VALIDATE_INT, $options);
            }

            $dbHandler = new DatabaseHandler();
            $posts = $dbHandler->get_posts_of_category($category, $maximum, $page);
            $category_info = $dbHandler->get_category_information($category);
            if ($category_info == False) {
                return json_encode(array("error" => True, "errormsg" => "Deze categorie bestaat niet."));
            }
            else {
                $category_name = $category_info["name"];
                $category_id = $category_info["term_id"];
                if ($show_category) {
                    return json_encode(array("error" => False, "name" => $category_name, "items" => $posts, "id" => $category_id, "categories" => $dbHandler->get_subcategories($category), "parents" => $dbHandler->get_parents($category)));
                } else {
                    return json_encode(array("error" => False, "items" => array($category_name => array("top" => $posts, "id" => $category_id))));
                }
            }
        }

        function search() {
            if (isset($_POST[$this->PAR_NAME])) {
                $name = $_POST[$this->PAR_NAME];
                $mHandler = new DatabaseHandler();
                if (!empty($_POST[$this->PAR_MAXIMUM])) {
                    $maximum = $_POST[$this->PAR_MAXIMUM];
                    if (is_numeric($maximum)) {
                        $maximum = intval($maximum);
                    }
                    else {
                        $maximum = 0;
                    }
                }
                else {
                    $maximum = 0;
                }
                $results = $mHandler->search_json($name, $maximum);
                if (isset($_POST[$this->PAR_INDEX])) {
                    $index = $_POST[$this->PAR_INDEX];
                    return json_encode(array("error"=>False, "items"=>$results, "index"=>$index));
                }
                else {
                    return json_encode(array("error"=>False, "items"=>$results));
                }
            }
            else {
                return json_encode(array("error"=>True, "errormsg"=>"No search name specified"));
            }
        }

        /**
         * @return false|string
         */
        function send_confirmation() {
            $mail = new MailFormHandler();
            if (isset($_POST[$this->PAR_LIST]) && isset($_POST[$this->PAR_DETAILS])) {
        		$list = $_POST[$this->PAR_LIST];
        		$details = $_POST[$this->PAR_DETAILS];
        		if ($mail->handle_ajax($list, $details)) {
    				return json_encode(array("error"=>False));
	            }
	            else {
	                return json_encode(array("error"=>True, "errormsg"=>"Error sending confirmation email"));
	            }
            }
            else {
            	return json_encode(array("error"=>True, "errormsg"=>"Parameters not set"));
            }
        }

        function handle_subscription_request() {
            if (empty($_POST['requested_subscription'])) {
                echo json_encode(array("error"=>True, "errormsg"=>"Vul alstublieft het abonnement wat u wilt aanvragen in"));
            }
            else {
                $captcha_verification = $this->verify_captcha('token', 'action-captcha');

                if (!$captcha_verification) {
                    echo json_encode(array("error"=>True, "errormsg"=>"Captcha verificatie gefaald, probeer het opnieuw"));
                    wp_die();
                }

                $post_editor = wp_kses_post($_POST['post_editor']);
                $client_name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
                $client_email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
                $subscription_requested = filter_var($_POST['requested_subscription'], FILTER_SANITIZE_STRING);

                $emailhandler = new EmailReader(dirname(__FILE__) . '/mails', new UserInformation($client_email, $client_name, '', '', '', ''));

                $mail_template = $emailhandler->get_request_mail($subscription_requested, $post_editor);

                if (!$mail_template) {
                    echo json_encode(array("error"=>True, "errormsg"=>"De aanvraag kon niet worden verstuurd want de template mail ontbreekt, neem contact op met sitebeheer"));
                }

                $admin_email = $this->settings->get_admin_email();

                $headers_mail = array();
                $headers_mail[] = "From: kanikervanaf.nl <noreply@kanikervanaf.nl>";
                $headers_mail[] = "Bcc: " . $client_email;
                $headers_mail[] = "Content-Type: text/html";

                $retvalue = wp_mail($admin_email, "Kanikervanaf: Abonnement aangevraagd", $mail_template, $headers_mail);

                if ($retvalue) {
                    echo json_encode(array("error"=>False));
                }
                else {
                    echo json_encode(array("error"=>True, "errormsg"=>"De aanvraag kon niet worden verstuurd, probeer het later opnieuw"));
                }
            }
            wp_die();
        }

        function verify_captcha($token_post_param, $action_post_param) {
            $private_key = $this->settings->get_captcha_secret_key();

            if ($private_key) {
                $token = $_POST[$token_post_param];
                $action = $_POST[$action_post_param];
                 
                // call curl to POST request
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL,"https://www.google.com/recaptcha/api/siteverify");
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('secret' => $private_key, 'response' => $token)));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($ch);
                curl_close($ch);
                $arrResponse = json_decode($response, true);

                if(!($arrResponse["success"] == '1' && $arrResponse["action"] == $action && $arrResponse["score"] >= 0.5)) {
                    return False;
                }
                else {
                    return True;
                }
            }
            else {
                return True;
            }
        }
    }
