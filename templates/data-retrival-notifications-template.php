<?php 


?> 

<h2>Data Retrieval Notifications</h2>
      <div class="data-retrival-plugin grid">
    <?php
     if(is_array($notifications) && !empty($notifications)){
    foreach ($notifications as $notification) :
 
    date_default_timezone_set('America/New_York');
    // format date time
    $created_on_formatted_date_time = date('F j, Y, g:i a', strtotime($notification['post_data']['post_modified']));

    // subtract 5 hours from  date time
    $created_on_formatted_date_time = date('F j, Y, g:i a', strtotime('-5 hours', strtotime($created_on_formatted_date_time)));
 
  
          ?>
          <div  class="data-retrival-plugin ">
        <div class="  notification">
            <h3>  <a target ="_blank" href="<?php echo $notification['edit_post'] ?>">  <?php echo esc_html($notification['post_data']['post_title']); ?> (Updated on <?php echo $created_on_formatted_date_time ?>) </a></h3>
            <h4>Created by <?php echo  $notification['post_data']['post_author'] ?></h4>
            <!-- Meta Data Accordion -->
            <button class="accordion">Meta Data</button>
            <div class="meta-accordion">
                <?php foreach ($notification['meta_data'] as $key =>  $change) : ?>
                    <p><?php echo esc_html($key); ?>: <?php echo esc_html($change); ?></p>
                <?php endforeach; ?>
            </div>
            <!-- Taxonomy Accordion -->
            <button class="accordion">Taxonomies</button>
            <div class="taxonomy-accordion">
                <?php foreach ($notification['taxonomies'] as $key =>  $taxonomy) : ?>
                    <p><?php echo esc_html($taxonomy['name']); ?> (<?php echo esc_html($taxonomy['taxonomy']); ?>)</p>
                <?php endforeach; ?>
            </div>
        </div>
        </div>
    <?php endforeach; 
    }else{
        echo "<h3> $notifications</h3>";
    }

     
    ?>
    </div>
    <script>
        // Add JavaScript to handle accordion functionality
        var accordions = document.querySelectorAll(".accordion");

        accordions.forEach(function (accordion) {
            accordion.onclick = function(){
                let panel = this.nextElementSibling;
                // toggle active state
               panel.classList.toggle("active");
            }

            // accordion.addEventListener("click", function () {
            //     this.classList.toggle("active");
            //     var panel = this.nextElementElement;
            //     console.log(panel, 'panel');
            //     if (panel.style.display === "block") {
            //         panel.style.display = "none";
            //     } else {
            //         panel.style.display = "block";
            //     }
            // });
        });
    </script>