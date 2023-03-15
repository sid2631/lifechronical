//shotcode siddharth

function demo() {
    //fetch data from wpbs_ib_stripe_order
    global $wpdb;
    
    $results = $wpdb->get_results( "SELECT *, sum(product_price) as price FROM {$wpdb->prefix}ib_stripe_order group by user_id");
    
    $first_donation= $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}ib_stripe_order where id=1");
    
    $recent_donation= $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ib_stripe_order ORDER BY 'id' 'DESC' ");
     
    

    
       foreach($recent_donation as  $recent){
            $rid=$recent->user_id;
            $ruser_info= get_userdata($rid);
            $ruser_name = $ruser_info->display_name;
            $rpprice =  $recent->product_price;
        }
    
       foreach($first_donation as $first){
             $fid=$first->user_id;
             $fuser_info= get_userdata($fid);
             $fuser_name = $fuser_info->display_name;
             $fpprice =  $first->product_price;
         }
         
       foreach($results as $result){
           $uid = $result->user_id;
           $user_info = get_userdata($uid);
           $user_name = $user_info->display_name;
           $a = $result->price;
           ?>
           
            <p class="id_<?php echo $uid ?>">Name: <?php echo $user_name ?> price: <?php echo $a; ?>  </p>
           <?php
       }
       
       
        ?>
        <p class="id_<?php echo $fid ?>">Name first: <?php echo $fuser_name ?> first price: <?php echo  $fpprice ?>  </p>
       <?php
       
       ?>
       <p class="id_<?php echo $rid ?>">Name last donate: <?php echo $ruser_name ?>  price last donate: <?php echo  $rpprice ?>  </p>
       <?php
        

    //design
?>
<div class="main">
    
  <div class="recent">
    <div class="symbol">
      <a href="#" class="fa fa-facebook"></a>
      <div class="recentinfo">
        <p class="para1">
          Sami Sengören
        </p>
        <p class="para2">
         <strong>$100</strong> recent donation 
        </p>
      </div>
    </div>
  </div>

  <div class="recent">
    <div class="symbol">
      <a href="#" class="fa fa-facebook"></a>
      <div class="recentinfo">
        <p class="para1">
          Sami Sengören
        </p>
        <p class="para2">
         <strong>$100</strong> top donation 
        </p>
      </div>
    </div>
  </div>

  <div class="recent">
    <div class="symbol">
      <a href="#" class="fa fa-facebook"></a>
      <div class="recentinfo">
        <p class="para1">
          Sami Sengören
        </p>
        <p class="para2">
         <strong>$100</strong> first donation 
        </p>
      </div>
    </div>
  </div>
  
</div>
      
<style>
  .symbol{
    display:flex;
    padding-top:15px;
  }
  
  .para1{
    font-family: Bitter;
    font-style: normal;
    font-weight: bold;
    font-size: 18px;
    margin-left:15px;
    padding-top:12px;
    padding-bottom:2px
  }
  
  .para2{
    font-family: Ubuntu;
    font-style: normal;
    font-weight: normal;
    font-size: 12px;
    margin-left:15px;
  }
  
  .fa {
      padding: 20px;
      font-size: 30px;
      width: 50px;
      text-align: center;
      text-decoration: none;
      margin: 5px 2px;
}
</style>
        
<?php
   
}
add_shortcode('donate', 'demo');
?>
