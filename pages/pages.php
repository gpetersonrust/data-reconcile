<?php
 

 
 class DataReconcile {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_page'));
    }

    public function add_admin_page() {
        add_menu_page(
            'Data Retrieval Settings',
            'Data Retrieval',
            'manage_options',
            'data-retrieval-settings',
            array($this, 'data_retrieval_page'),
            'dashicons-admin-generic'
        );

        // notifications page for data retrieval
        add_submenu_page(
            'data-retrieval-settings',
            'Data Retrieval Notifications',
            'Notifications',
            'manage_options',
            'data-retrieval-notifications',
            array($this, 'data_retrieval_notifications_page')
        );
    }

    public function data_retrieval_page() {
        if (isset($_POST['action'])) {
            if ($_POST['action'] === 'update_retrieval_date') {
                if (isset($_POST['retrieval_date'])) {
                    if (!wp_verify_nonce($_POST['update_retrieval_date_nonce'], 'update_retrieval_date')) {
                        die('Security check');
                    }
                    $date = sanitize_text_field($_POST['retrieval_date']);
                    update_option('moxcar_post_retrieval_date', $date);
                }
            } elseif ($_POST['action'] === 'update_post_types') {
                if (!wp_verify_nonce($_POST['update_post_types_nonce'], 'update_post_types')) {
                    die('Security check');
                }
                $selected_post_types = isset($_POST['selected_post_types']) ? $_POST['selected_post_types'] : array();
                update_option('moxcar_post_retrieval_posts', $selected_post_types);
            } elseif ($_POST['action'] === 'retrieve_data') {
                if (!wp_verify_nonce($_POST['retrieve_data_nonce'], 'retrieve_data')) {
                    die('Security check');
                }
                $this->data_retrieval_download();
            } else if ($_POST['action'] === 'import_json') {
                if (!wp_verify_nonce($_POST['import_data_nonce'], 'import_data')) {
                    die('Security check');
                }
                $file = $_FILES['import_file'];
                $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                if ($file_ext !== 'json') {
                    die('Invalid file type');
                }
                $file_contents = file_get_contents($file['tmp_name']);
                $post_objects = json_decode($file_contents, true);
                foreach ($post_objects as $post_object) {
                    $post_data = json_decode($post_object['post_data'], true);
                    $meta_data = json_decode($post_object['meta_data'], true);
                    $grouped_taxonomies = json_decode($post_object['grouped_taxonomies'], true);
                    $this->create_post_with_meta_and_taxonomy($post_data, $meta_data, $grouped_taxonomies);
                }
            } else if($_POST['action'] === 'save_copy') {
                if (!wp_verify_nonce($_POST['save_copy_nonce'], 'save_copy')) {
                    die('Security check');
                }
                $results = $this->retrieve_and_download_data();
                $json_data = json_encode($results);
                // stringify to save as option in database
                update_option('moxcar_post_retrieval_copy', $json_data);
 
            }
        }
        // Load your template here (HTML and form)
        include(DATA_RECONCILE_DIR_PATH . 'templates/data-retrival-template.php');
        $content = ob_get_clean(); // Get the content from the output buffer
        echo $content; // Output the content
    }

    public function data_retrieval_download() {
        $results = $this->retrieve_and_download_data();
        if (!empty($results)) {
            $json_data = json_encode($results);
            $data_uri = 'data:application/json;charset=utf-8,' . rawurlencode($json_data);
            echo '<a href="' . $data_uri . '" download="data.json">Download JSON Data</a>';
        } else {
            echo json_encode(array('message' => 'No results found'));
        }
        if ($wpdb->last_error) {
            echo 'Database Error: ' . $wpdb->last_error;
        }
    }

    public function create_post_with_meta_and_taxonomy($post_data, $meta_data, $taxonomy_data) {
        $existing_post = get_post($post_data['ID']); 
        // check if post matches post type and post_title to ensure it's not a false positive
        if ($existing_post && $existing_post->post_type !== $post_data['post_type']) {
            $existing_post = null;
        }

        if($existing_post && $existing_post->post_title !== $post_data['post_title']) {
            $existing_post = null;
        }
        if ($existing_post) {
            $post_id = $existing_post->ID;
            // update post
            wp_update_post(array(
                'ID' => $post_id,
                'post_title' => $post_data['post_title'],
                'post_content' => $post_data['post_content'],
                'post_type' => $post_data['post_type'],
                'post_author' => $post_data['post_author'],
                'post_date' => $post_data['post_date'],
                // update modified date
                'post_modified' => $post_data['post_modified'],
                'post_status' => 'publish',
            ));
        } else {
            $post_id = wp_insert_post(array(
                'post_title' => $post_data['post_title'],
                'post_content' => $post_data['post_content'],
                'post_type' => $post_data['post_type'],
                'post_author' => $post_data['post_author'],
                'post_date' => $post_data['post_date'],
                'post_status' => 'publish',
            ));
        }
        foreach ($meta_data as $meta_key => $meta_value) {
            update_post_meta($post_id, $meta_key, $meta_value);
        }
        foreach ($taxonomy_data as $taxonomy_item) {
            $term_name = $taxonomy_item['name'];
            $taxonomy = $taxonomy_item['taxonomy'];
            $term = term_exists($term_name, $taxonomy);
            if (!$term) {
                $term = wp_insert_term($term_name, $taxonomy);
            }
            if (!is_wp_error($term)) {
                wp_set_post_terms($post_id, $term['term_id'], $taxonomy);
            }
        }
        return $post_id;
    }

    public function retrieve_and_download_data( ) {
        global $wpdb;
        $date = get_option('moxcar_post_retrieval_date');
        $post_types = get_option('moxcar_post_retrieval_posts', array());
        
        $post_types_str = implode("','", $post_types);
        $post_type_placeholders = array_fill(0, count($post_types), '%s');
        $post_type_placeholders = implode(', ', $post_type_placeholders);
        
        $query_args = array_merge($post_types, array($date));
        $in_clause = '(' . $post_type_placeholders . ')';
        
        $query = $wpdb->prepare(
            "SELECT post_data.post_data, meta_data.meta_data, grouped_taxonomies.grouped_taxonomies
            FROM (
                SELECT p.ID, JSON_OBJECT(
                    'ID', p.ID,
                    'post_title', p.post_title,
                    'post_author', p.post_author,
                    'post_content', p.post_content,
                    'post_type', p.post_type,
                    'post_date', p.post_date,
                    'post_modified', p.post_modified
                ) AS post_data
                FROM {$wpdb->prefix}posts AS p
                WHERE p.post_type IN  ('$post_types_str')
                AND  p.post_modified > %s
            ) AS post_data
            LEFT JOIN (
                SELECT pm.post_id, JSON_OBJECTAGG(
                    CASE WHEN pm.meta_key IS NOT NULL THEN pm.meta_key ELSE 'undefined' END, pm.meta_value
                ) AS meta_data
                FROM {$wpdb->prefix}postmeta AS pm
                GROUP BY pm.post_id
            ) AS meta_data ON post_data.ID = meta_data.post_id
            LEFT JOIN (
                SELECT p.ID, JSON_ARRAYAGG(
                    JSON_OBJECT('taxonomy', tt.taxonomy, 'name', t.name)
                ) AS grouped_taxonomies
                FROM {$wpdb->prefix}posts AS p
                LEFT JOIN {$wpdb->prefix}term_relationships AS tr ON p.ID = tr.object_id
                LEFT JOIN {$wpdb->prefix}term_taxonomy AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                LEFT JOIN {$wpdb->prefix}terms AS t ON tt.term_id = t.term_id
                WHERE p.post_type IN  ('$post_types_str')
                GROUP BY p.ID
            ) AS grouped_taxonomies ON post_data.ID = grouped_taxonomies.ID",
            $date
        );

        $results = $wpdb->get_results($query, ARRAY_A);

        return $results;
    }

    public function generate_notifications() {
        $results = $this->retrieve_and_download_data();

        
    
        // Iterate through the retrieved data and generate notifications
        $notifications = array();
        // get saved copies from database
        $saved_copies = get_option('moxcar_post_retrieval_copy');
        $saved_copies = json_decode($saved_copies, true);
     if(!$saved_copies) {
        return "No saved copies found please save a copy first!";
     }
   
    
        foreach ($results as $row) {
             $row['post_data'] = json_decode($row['post_data'], true);
                $row['meta_data'] = json_decode($row['meta_data'], true);
                $row['grouped_taxonomies'] = json_decode($row['grouped_taxonomies'], true);
                   $pd_ID =   $row['post_data']['ID'];
              
        
            $saved_copy_to_compare =  array_filter($saved_copies, function($item) use ($pd_ID) {
                $post_data = json_decode( $item['post_data'], true);
                $ID = $post_data['ID'];
                 
                return $ID == $pd_ID;
               })[0];

            
                $notification = $this->compare_data($saved_copy_to_compare, $row);

            
            $notification && $notifications[] = $notification;
        }
    
        return $notifications;
    }
    

    public function data_retrieval_notifications_page() {
        $notifications = $this->generate_notifications();
       print_r($notifications);
        if(  is_array($notifications)) {
            usort($notifications, function($a, $b) {
                return strtotime($b['post_modified']) - strtotime($a['post_modified']);
            });
        }
        // sort notifications by date
       
        // Load your template here (HTML and form)
        include(DATA_RECONCILE_DIR_PATH . 'templates/data-retrival-notifications-template.php');
        $content = ob_get_clean(); // Get the content from the output buffer
        echo $content; // Output the content
    }

    public   function compare_data($saved_copy, $check_against_copy){
      
        $anything_changed = false; 
        $notification_array = array();
      //  start with post_data 
       $saved_copy_post_data =    json_decode($saved_copy['post_data'], true);
    
       $check_against_copy_post_data =    $check_against_copy['post_data'];

       
     
       foreach ($saved_copy_post_data as $key => $value) {
           $saved_copy_value = $saved_copy_post_data[$key];
              $check_against_copy_value = $check_against_copy_post_data[$key];

                echo "$key  - $saved_copy_value - $check_against_copy_value <br>";
              //  compared saved copy value to check against copy value
              if($saved_copy_value != $check_against_copy_value) {
                  $anything_changed = true;
                  $notification_array['post_data'][$key] = $value;
              }
             
            
       }
      
      //  meta_data
      $saved_copy_meta_data =    json_decode($saved_copy['meta_data'], true);
      $check_against_copy_meta_data =   $check_against_copy['meta_data'];
  
      // taxonomies grouped_taxonomie
      $saved_copy_grouped_taxonomies =    json_decode($saved_copy['grouped_taxonomies'], true);
      $check_against_copy_grouped_taxonomies =    $check_against_copy['grouped_taxonomies'];
      
        
      //  compare meta data
      foreach ($saved_copy_meta_data as $key => $value) {
          $saved_copy_value = $saved_copy_meta_data[$key];
             $check_against_copy_value = $check_against_copy_meta_data[$key];
             //  compared saved copy value to check against copy value
             if($saved_copy_value != $check_against_copy_value) {
                 $anything_changed = true;
                 $notification_array['meta_data'][$key] = $value;
             }
          }
  
        
  
          //  compare taxonomies
  
          foreach ($saved_copy_grouped_taxonomies as $key => $value) {
              $saved_copy_value = $saved_copy_grouped_taxonomies[$key];
                 $check_against_copy_value = $check_against_copy_grouped_taxonomies[$key];
                 print_r($check_against_copy_value);
                 if($saved_copy_value != $check_against_copy_value) {
                     $anything_changed = true;
                     $notification_array['grouped_taxonomies'][$key] = $value;
                 }
              }
              
       if($anything_changed) {
          // add post_data title, id , author, modified date to notification array just in case we need it
          $notification_array['post_data']['post_title'] = $saved_copy_post_data['post_title'];
          $notification_array['post_data']['ID'] = $saved_copy_post_data['ID'];
          $notification_array['post_data']['post_author'] = $saved_copy_post_data['post_author'];
        //   convert post author   $notification_array['post_data']['post_author']  into a name or display name
        $notification_array['post_data']['post_author']  =  get_the_author_meta('display_name',  $saved_copy_post_data['post_author'] ) ;
          $notification_array['post_data']['post_modified'] = $saved_copy_post_data['post_modified'];
          $notification_array['edit_post'] = get_edit_post_link($saved_copy_post_data['ID']);
  
   
          return $notification_array;
       } else {
          return false;
       }
      }

}

// Instantiate the DataReconcile class when the plugin is loaded
function data_reconcile_init() {
    $data_reconcile = new DataReconcile();
}
add_action('plugins_loaded', 'data_reconcile_init');
