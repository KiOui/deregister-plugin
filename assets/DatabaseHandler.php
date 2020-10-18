<?php

require_once ABSPATH."wp-content/plugins/deregister-plugin/assets/Subscription.php";

class DatabaseHandler {

    private $SEARCH_QUERY = "
    SELECT object_id as ID, nickname as NICKNAME, name as NAME FROM (
	        SELECT t.name AS nickname, r.object_id, m.meta_value, p.post_title as name
	        FROM __data_terms__ t, __data_term_taxonomy__ x, __data_term_relationships__ r, __data_meta__ m, __data_posts__ p 
	        WHERE x.taxonomy = 'deregister_search_term' AND t.term_id = x.term_id AND r.term_taxonomy_id = x.term_taxonomy_id AND m.post_id = r.object_id AND p.ID = m.post_id AND m.meta_key = 'deregister_used' AND p.post_status = 'publish'

        UNION

	        SELECT p.post_title as nickname, p.ID as object_id, m.meta_value, p.post_title as name
	        FROM __data_meta__ m, __data_posts__ p 
	        WHERE p.ID = m.post_id AND meta_key='deregister_used' AND post_status= 'publish'
    ) a 
    WHERE UPPER(a.nickname) LIKE UPPER('%s') 
    ORDER BY CAST(meta_value AS SIGNED) DESC;
    ";
    private $REMOVE_STATEMENT_ALL = "DELETE FROM __database__ WHERE expires_at < '%s';";
    private $REMOVE_STATEMENT = "DELETE FROM __database__ WHERE session_id = '%s';";
    
    /**
     * @param $post_id int ID of the post to convert to an object
     * @return bool|Subscription False if the ID is not a post id, a Subscription object otherwise
     */
    function get_post_object($post_id) {
        $name = $this->get_name($post_id);
        if ($name) {
            return new Subscription(get_post_custom($post_id), $post_id, $name);
        }
        else {
            return False;
        }
    }

    /**
     * @param $post_id int ID of the post to increase the amount it is used
     */
    function update_amount_used($post_id) {
        $post_object = $this->get_post_object($post_id);
        $post_object->increase_used();
    }

    /**
     * Returns an array with posts
     * @return array all posts of type sub_provider as array of ids
     */
    function get_all_posts_sorted_usage($category_id) {
    	if ($category_id) {
    		$all_posts = get_posts(
    			array(
    				'post_type' => "sub_provider", 
    				'numberposts' => -1,
    				'tax_query' => array(
    					array(
    						'taxonomy' => 'deregister_category',
    						'field' => 'term_id',
    						'terms' => $category_id
    					)
    				),
                    'meta_key' => 'deregister_used',
                    'orderby' => 'meta_value_num',
                    'order' => 'DESC'
    			)
    		);
    	}
    	else {
    		$all_posts = get_posts(
    			array(
    				'post_type' => "sub_provider", 
    				'numberposts' => -1,
                    'meta_key' => 'deregister_used',
                    'orderby' => 'meta_value_num',
                    'order' => 'DESC'
    			)
    		);
    	}
        $retvalue = array();
        foreach ($all_posts as $post) {
            $retvalue[] = $post->ID;
        }
        return $retvalue;
    }

    function get_all_posts_sorted_name($category_id) {
        if ($category_id) {
            $all_posts = get_posts(
                array(
                    'post_type' => "sub_provider",
                    'numberposts' => -1,
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'deregister_category',
                            'field' => 'term_id',
                            'terms' => $category_id
                        )
                    ),
                    'order' => 'ASC',
                    'orderby' => 'title'
                )
            );
        }
        else {
            $all_posts = get_posts(
                array(
                    'post_type' => "sub_provider",
                    'numberposts' => -1,
                    'order' => 'ASC',
                    'orderby' => 'title'
                )
            );
        }
        $retvalue = array();
        foreach ($all_posts as $post) {
            $retvalue[] = $post->ID;
        }
        return $retvalue;
    }

    /**
     * Return an array of all categories
     * @param $parent string | False the id of parent category
     * @return array: the array containing all categories
     */
    function get_categories($parent) {
    	if (!$parent) {
    		$args = array(
            'taxonomy' => 'deregister_category',
            'parent' => 0,
            'hide_empty' => False,
            );
    		$categories = get_terms($args);
    	}
    	else {
    		$args = array(
            'taxonomy' => 'deregister_category',
            'parent' => $parent,
            'hide_empty' => False,
	        );
    		$categories = get_terms($args);
    	}
    	foreach ($categories as $category) {
    		$retvalue[] = $category->to_array();
    	}
        return $retvalue;
    }

    function get_category_information($category_id) {
        try {
            $information = get_term($category_id, "deregister_category");
            return $information->to_array();
        }
        catch (Error $e) {
            return False;
        }
    }

    /**
     * Returns all posts of a specific category
     * @param $category_id: the category to return the posts for
     * @return array: an array containing all posts of a specific category
     */
    function get_all_category($category_id) {
        $all_posts_category = $this->get_all_posts_sorted_usage($category_id);
        return $all_posts_category;
    }

    function convert_categories_to_json_compatible($categories) {
        $retvalue = array();
        foreach ($categories as $category) {
            $retvalue[] = array("name"=>$category["name"], "id"=>$category["term_id"]);
        }
        return $retvalue;
    }

    function get_posts_of_category($category_id, $maximum, $page) {
        $posts = $this->get_all_posts_sorted_name($category_id);
        $post_json = $this->convert_item_list_to_json_compatible($this->convert_ids_to_objects($posts));
        return $post_json;
    }

    function get_subcategories($category_id) {
        $all_sub_categories = $this->convert_categories_to_json_compatible($this->get_categories($category_id));
        return $all_sub_categories;
    }

    function get_parents($category_id) {
        $category_information = $this->get_category_information($category_id);
        if ($category_information == False) {
            return False;
        }
        else if ($category_information['parent'] == 0) {
            return array(array("name"=>$category_information["name"], "id"=>$category_information["term_id"]));
        }
        else {
            $parent_category_details = $this->get_parents($category_information["parent"]);
            if ($parent_category_details == False) {
                $parent_category_details = array();
            }
            else {
                $parent_category_details[] = array("name" => $category_information["name"], "id" => $category_information["term_id"]);
            }
            return $parent_category_details;
        }
    }

    /**
     * Returns the title of a post_id
     * @param $postid: the postid to return the title for
     * @return mixed: either False or a string containing the post title
     */
    function get_name($postid) {
        try {
            return get_the_title($postid);
        } catch (Error $e) {
            return False;
        }
    }

    /**
     * Returns the first x items of a specific category
     * @param $category: the category to return the posts for
     * @return array: an array containing x or less posts of a specific category
     */
    function get_first_x_category($category_id, $maximum) {
        $one_category = $this->get_all_category($category_id);
        if ($maximum > 0) {
            $one_category = array_slice($one_category, 0, $maximum);
        }
        return $one_category;
    }

    /**
     * @param $list array list of posts to convert to json object
     * @return array list of json compatible variables
     */
    function convert_item_list_to_json_compatible($list) {
        $retvalue = array();

        foreach ($list as $item) {
            $retvalue[] = array($item->get_name(), $item->get_id(), $item->get_price(), $item->has_email(), $item->has_address() || $item->has_correspondence_address());
        }
        return $retvalue;
    }

    /**
     * Returns the top x of each category
     * @return array: an array containing multiple arrays containing the top five of each category
     */
    function get_top($maximum) {
        $categories = $this->get_categories(False);
        $topfives = array();
        foreach ($categories as $category) {
            $top_category = $this->get_first_x_category($category['term_id'], $maximum);
            if (sizeof($top_category) > 0) {
                $topfives = array_merge($topfives, array($category['name']=>array("top"=>$this->convert_item_list_to_json_compatible($this->convert_ids_to_objects($top_category)), "id"=>$category['term_id'])));
            }
        }
        return $topfives;
    }

    /**
     * @param $post_id int the post id to check
     * @return bool True if the post exists, False otherwise
     */
    function exists($post_id) {
        if (get_post_status($post_id) == "publish") {
            return get_post_type($post_id) == 'sub_provider';
        }
        else {
            return False;
        }
    }

    /**
     * @throws Exception
     */
    function remove_expired_tokens() {
        global $wpdb;
        $current_date = new DateTime('now');
        $wpdb->query(
            $wpdb->prepare(
                str_replace('__database__', $wpdb->prefix . 'deregister_sessions', $this->REMOVE_STATEMENT_ALL),
                $current_date->format('Y-m-d H:i:s')
            )
        );
    }

    /**
     * @param $token string the token entry to remove
     */
    function remove_token($token) {
        global $wpdb;
        $wpdb->query(
            $wpdb->prepare(
                str_replace('__database__', $wpdb->prefix . 'deregister_sessions', $this->REMOVE_STATEMENT),
                $token
            )
        );
    }

    /**
     * @param $ids array list of ints to convert to objects
     * @return array objects for all ids in the list
     */
    function convert_ids_to_objects($ids) {
        $objects = array();
        foreach ($ids as $id) {
            $objects[] = $this->get_post_object($id);
        }
        return $objects;
    }

    /**
     * @param $searchfor string post to search for
     * @param $maximum int maximum amount of posts to return
     * @return array list of posts that match searchfor
     */
    function search_json($searchfor, $maximum) {
        $items = $this->search($searchfor);
        if ($maximum > 0) {
            $items = array_slice($items, 0, $maximum);
        }
        $items = $this->convert_ids_to_objects($items);

        return $this->convert_item_list_to_json_compatible($items);
    }
    /**
     * Search for all posts containing searchfor
     * @param $searchfor: the substring to search for
     * @return array: an array containing all ids of posts that match searchfor
     */
    function search($searchfor) {
        global $wpdb;
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                str_replace('__data_term_relationships__', $wpdb->term_relationships, str_replace('__data_term_taxonomy__', $wpdb->term_taxonomy, str_replace('__data_terms__', $wpdb->terms, str_replace('__data_meta__', $wpdb->postmeta, str_replace('__data_posts__', $wpdb->posts, $this->SEARCH_QUERY))))),
                '%'.$searchfor.'%'
            ), ARRAY_A
        );
        if ($rows == null) {
            return array();
        }
        else {
            $ids = array();
            foreach ($rows as $row) {
                $ids[] = $row['ID'];
            }
            $ids = array_unique($ids);
            return $ids;
        }
    }
}
