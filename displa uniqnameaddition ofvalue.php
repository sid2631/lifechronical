function demo() {
    //fetch data from wpbs_ib_stripe_order
    global $wpdb;
    
    $results = $wpdb->get_results( "SELECT *, sum(product_price) as price FROM wpbs_ib_stripe_order group by user_id");

   foreach($results as $result){
       $uid = $result->user_id;
       $user_info = get_userdata($uid);
       $user_name = $user_info->display_name;
       $a = $result->price;
       ?>
        <p class="id_<?php echo $uid ?>">Name: <?php echo $user_name ?> price: <?php echo  $a ?>  </p>
       <?php
   }

     
    
   
}
add_shortcode('donate', 'demo');
?>
