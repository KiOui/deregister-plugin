<?php

class SubscriptionMetaBox {

    private $prefix;
    private $custom_meta_fields;
    private $post_type;

    /**
     * SubscriptionMetaBox constructor.
     * Initializes custom_meta_fields and prefix with their respective values.
     * custom_meta_fields is used to create the custom meta fields in the Subscription post type
     * @param $post_type: the post type to add this custom meta box to.
     */
    function __construct($post_type) {
        $this->prefix = 'deregister_';
        $this->custom_meta_fields = array(
            array(
                'label'=> 'Kosten per jaar',
                'desc'  => 'De kosten van het abonnement per jaar, dit is het minimale bedrag bij meerdere abonnementen. Als dit niet bekend is dit vak leeg laten.',
                'id'    => $this->prefix.'price',
                'type'  => 'number-any'
            ),
            array(
                'label'=> 'E-mail adres klantenservice',
                'desc'  => 'E-mail adres waar de gebruiker de mail naar moet doorsturen.',
                'id'    => $this->prefix.'mail',
                'type'  => 'text'
            ),
            array(
                'label'=> 'Antwoordnummer klantenservice',
                'desc'  => 'Het antwoordnummer waar een brief naartoe kan worden gestuurd, als dit niet bekend is dit vak leeg laten.',
                'id'    => $this->prefix.'mail_answer_number',
                'type'  => 'number'
            ),
            array(
                'label'=> 'Postcode antwoordnummer',
                'desc'  => 'De postcode van de klantenservice, als dit niet bekend is dit vak leeg laten.',
                'id'    => $this->prefix.'mail_postal_code',
                'type'  => 'text'
            ),
            array(
                'label'=> 'Plaats antwoordnummer',
                'desc'  => 'De postcode van de klantenservice, als dit niet bekend is dit vak leeg laten.',
                'id'    => $this->prefix.'mail_city',
                'type'  => 'text'
            ),
            array(
                'label'=> 'Correspondentieadres abonnement',
                'desc'  => 'Het correspondentieadres van de klantenservice, als dit niet bekend is dit vak leeg laten.',
                'id'    => $this->prefix.'correspondance_address',
                'type'  => 'text'
            ),
            array(
                'label'=> 'Postcode correspondentieadres',
                'desc'  => 'Het correspondentieadres van de klantenservice, als dit niet bekend is dit vak leeg laten.',
                'id'    => $this->prefix.'correspondance_postal_code',
                'type'  => 'text'
            ),
            array(
                'label'=> 'Plaats correspondentieadres',
                'desc'  => 'Het correspondentieadres van de klantenservice, als dit niet bekend is dit vak leeg laten.',
                'id'    => $this->prefix.'correspondance_city',
                'type'  => 'text'
            ),
            array(
                'label'=> 'Telefoonnummer klantenservice (eventueel betaald)',
                'desc'  => 'Het telefoonnummer van de klantenservice, als dit niet bekend is dit vak leeg laten.',
                'id'    => $this->prefix.'phonenumber',
                'type'  => 'text'
            ),
            array(
                'label'=> 'Telefoonnummer/opzegnummer (gratis)',
                'desc'  => 'Het opzegnummer van het abonnement (alleen gratis nummers), als dit niet bekend is dit vak leeg laten.',
                'id'    => $this->prefix.'phonenumber_remove_subscription',
                'type'  => 'text'
            ),
            array(
                'label'=> 'Aantal keer gebruikt',
                'desc'  => 'Het aantal keer dat dit type abonnement gebruikt is, als er een nieuw abonnement gemaakt wordt is dit meestal 0.',
                'id'    => $this->prefix.'used',
                'type'  => 'number',
                'default' => '1'
            )
        );
        $this->post_type = $post_type;
    }
     
    function create_hierarchical_taxonomy() {
      $labels = array(
        'name' => _x( 'Categorieën', 'Abonnement categorieën' ),
        'singular_name' => _x( 'Categorie', 'Abonnement categorie' ),
        'search_items' =>  __( 'Zoek abonnement categorieën' ),
        'all_items' => __( 'Alle abonnement categorieën' ),
        'parent_item' => __( 'Bovenliggende abonnement categorie' ),
        'parent_item_colon' => __( 'Bovenliggende abonnement categorie:' ),
        'edit_item' => __( 'Bewerk abonnement categorie' ),
        'update_item' => __( 'Werk abonnement categorie bij' ),
        'add_new_item' => __( 'Voeg nieuwe abonnement categorie toe' ),
        'new_item_name' => __( 'Nieuwe abonnement categorie naam' ),
        'menu_name' => __( 'Abonnement categorieën' ),
      );
      register_taxonomy($this->prefix . 'category', array($this->post_type), array(
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array( 'slug' => 'subscription_category' ),
      ));
    }

    function create_tags() {
        // Add new taxonomy, NOT hierarchical (like tags)
        $labels = array(
            'name' => _x( 'Zoektermen', 'Abonnement zoektermen' ),
            'singular_name' => _x( 'Zoekterm', 'Abonnement zoekterm' ),
            'search_items' =>  __( 'Zoek zoektermen' ),
            'popular_items' => __( 'Populaire zoektermen' ),
            'all_items' => __( 'Alle zoektermen' ),
            'parent_item' => null,
            'parent_item_colon' => null,
            'edit_item' => __( 'Bewerk zoekterm' ), 
            'update_item' => __( 'Werk zoekterm bij' ),
            'add_new_item' => __( 'Voeg nieuwe zoekterm toe' ),
            'new_item_name' => __( 'Nieuwe zoekterm naam' ),
            'separate_items_with_commas' => __( 'Scheid zoektermen met komma\'s' ),
            'add_or_remove_items' => __( 'Zoektermen toevoegen of verwijderen' ),
            'choose_from_most_used' => __( 'Kies uit meestgebruikte zoektermen' ),
            'menu_name' => __( 'Zoektermen' ),
        ); 

        register_taxonomy($this->prefix . 'search_term', array($this->post_type), array(
            'hierarchical' => false,
            'labels' => $labels,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array( 'slug' => $this->prefix . 'subscription_search_term' ),
        ));
    }

    function add_post_type() {
        $this->create_hierarchical_taxonomy();
        $this->create_tags();
        register_post_type($this->post_type, [
            'public' => True, 
            'label' => 'Abonnement',
            'taxonomies' => array($this->prefix . 'category'),
        ]);

        add_action('add_meta_boxes', array($this, 'add_custom_meta_box'));
        add_action('save_post', array($this, 'save_custom_meta'));
        //add_action( 'init', array($this, 'create_hierarchical_taxonomy'));
    }

    function add_custom_meta_box() {
        add_meta_box(
            'custom_meta_box', // $id
            'Abonnement eigenschappen', // $title 
            array($this, 'show_custom_meta_box'), // $callback
            $this->post_type, // $page
            'normal', // $context
            'high'); // $priority
    }

    /**
     * Creates HTML for the custom meta box
     */
    function show_custom_meta_box() {
        global $post;
        // Use nonce for verification
        echo '<input type="hidden" name="custom_meta_box_nonce" value="'.wp_create_nonce(basename(__FILE__)).'" />';
             
            // Begin the field table and loop
            echo '<table class="form-table">';
            foreach ($this->custom_meta_fields as $field) {
                // get value of this field if it exists for this post
                $meta = get_post_meta($post->ID, $field['id'], true);
                // begin a table row with
                echo '<tr>
                        <th><label for="'.$field['id'].'">'.$field['label'].'</label></th>
                        <td>';
                        switch($field['type']) {
                        	case 'number':
                                echo '<input type="number" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$meta.'" size="30" />
                                    <br /><span class="description">'.$field['desc'].'</span>';
                            break;
                            case 'number-any':
                                echo '<input type="number" step="any" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$meta.'" size="30" />
                                    <br /><span class="description">'.$field['desc'].'</span>';
                            break;
                            case 'text':
                                echo '<input type="text" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$meta.'" size="30" />
                                    <br /><span class="description">'.$field['desc'].'</span>';
                            break;
                            case 'text-required':
                                echo '<input type="text" required name="'.$field['id'].'" id="'.$field['id'].'" value="'.$meta.'" size="30" />
                                    <br /><span class="description">'.$field['desc'].'</span>';
                            break;
                            case 'textarea':
                                echo '<textarea name="'.$field['id'].'" id="'.$field['id'].'" cols="60" rows="4">'.$meta.'</textarea>
                                    <br /><span class="description">'.$field['desc'].'</span>';
                            break;
                            case 'checkbox':
                                echo '<input type="checkbox" name="'.$field['id'].'" id="'.$field['id'].'" ',$meta ? ' checked="checked"' : '','/>
                                    <label for="'.$field['id'].'">'.$field['desc'].'</label>';
                            break;
                            case 'select':
                                echo '<select name="'.$field['id'].'" id="'.$field['id'].'">';
                                foreach ($field['options'] as $option) {
                                    echo '<option', $meta == $option['value'] ? ' selected="selected"' : '', ' value="'.$option['value'].'">'.$option['label'].'</option>';
                                }
                                echo '</select><br /><span class="description">'.$field['desc'].'</span>';
                                break;
                            case 'checkbox_group':
                                foreach ($field['options'] as $option) {
                                    echo '<input type="checkbox" value="'.$option['value'].'" name="'.$field['id'].'[]" id="'.$option['value'].'"',$meta && in_array($option['value'], $meta) ? ' checked="checked"' : '',' /> 
                                            <label for="'.$option['value'].'">'.$option['label'].'</label><br />';
                                }
                                echo '<span class="description">'.$field['desc'].'</span>';
                                break;
                        } //end switch
                echo '</td></tr>';
            } // end foreach
            echo '</table>'; // end table
        }

    /**
     * Saves custom meta tag data
     * @param $post_id: the post id to save the data for
     * @return mixed: post_id if save fails
     */
    function save_custom_meta($post_id) {
         
        // verify nonce
        if (!wp_verify_nonce($_POST['custom_meta_box_nonce'], basename(__FILE__))) 
            return $post_id;
        // check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return $post_id;
        // check permissions
        if ('page' == $_POST['post_type']) {
            if (!current_user_can('edit_page', $post_id))
                return $post_id;
            } elseif (!current_user_can('edit_post', $post_id)) {
                return $post_id;
        }
         
        // loop through fields and save the data
        foreach ($this->custom_meta_fields as $field) {
            $old = get_post_meta($post_id, $field['id'], true);
            $new = $_POST[$field['id']];
            if (array_key_exists('default', $field) && $new == '') {
                $new = $field['default'];
            }
            if ($new && $new != $old) {
                update_post_meta($post_id, $field['id'], $new);
            } elseif ('' == $new && $old) {
                delete_post_meta($post_id, $field['id'], $old);
            }
        } // end foreach
    }

}