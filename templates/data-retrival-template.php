<?php 
 $data = json_decode(get_option('moxcar_post_retrieval_copy'), true);
//  find item with id of 5979 inside of data with the key of post_data
//  filter array

 
 
 
  
?>



<div class="data-retrieval-container">
    <h2>Data Retrieval Settings</h2>

    <form method="post" action="">
        <h3>Update Retrieval Date</h3>
        <input type="hidden" name="action" value="update_retrieval_date">
        <label for="retrieval_date">Data Retrieval Date:</label>
        <input  type="datetime-local"  id="retrieval_date" name="retrieval_date" value="<?php echo esc_attr(get_option('moxcar_post_retrieval_date')); ?>">
        <input type="submit" value="Update Retrieval Date">
        <!-- nonce  -->
        <?php wp_nonce_field('update_retrieval_date', 'update_retrieval_date_nonce'); ?>
    </form>

    <form method="post" action="">
        <h3>Select Post Types</h3>
        <input type="hidden" name="action" value="update_post_types">
        <?php
        $post_types = get_post_types(array('public' => true), 'objects');

        // sort by name
        usort($post_types, function ($a, $b) {
            return strcmp($a->label, $b->label);
        });
          

        $selected_post_types = get_option('moxcar_post_retrieval_posts', array());

        foreach ($post_types as $post_type) {
            $checked = in_array($post_type->name, $selected_post_types) ? 'checked' : '';
            echo '<label><input type="checkbox" name="selected_post_types[]" value="' . $post_type->name . '" ' . $checked . ' /> ' . $post_type->label . '</label><br>';
        }
        ?>

        <!-- nonce -->
        <?php wp_nonce_field('update_post_types', 'update_post_types_nonce'); ?>
        <input type="submit" value="Update Post Types">
    </form>

        <!-- save copy -->
        <form method="post" action="">
        <h3>Save Copy</h3>
        <input type="hidden" name="action" value="save_copy">
        <!-- nonce -->
        <?php wp_nonce_field('save_copy', 'save_copy_nonce'); ?>
        <input type="submit" value="Save Copy">
    </form>


    <!-- retrieve data button -->
    <form method="post" action="">
        <h3>Retrieve Data</h3>
        <input type="hidden" name="action" value="retrieve_data">
        <!-- nonce -->
        <?php wp_nonce_field('retrieve_data', 'retrieve_data_nonce'); ?>
        <input type="submit" value="Retrieve Data">
    </form>

  
    <!-- import data button -->
    <form method="post" action=""  
        enctype="multipart/form-data"
    >
        <h3>Import JSON</h3>
        <!-- file  filetype only json-->
        <input type="file" name="import_file" accept=".json">
        <input type="hidden" name="action" value="import_json">
        <!-- nonce -->
        <?php wp_nonce_field('import_data', 'import_data_nonce'); ?>
        <input type="submit" value="Import Data">
    </form>


  



</div>

<?php 

?>