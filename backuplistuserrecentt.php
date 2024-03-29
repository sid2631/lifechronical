function demo() {
    ob_start();
    //fetch data from wpbs_ib_stripe_order
    global $wpdb;  
    
    $results = $wpdb->get_results( "SELECT *, sum(product_price) as price FROM {$wpdb->prefix}ib_stripe_order group by user_id");
    
    $first_donation= $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}ib_stripe_order where id=1");
    
    $recent_donation= $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ib_stripe_order ORDER BY 'id' 'DESC' ");
   //$sql = "SELECT * FROM `wpbs_ib_stripe_order` ORDER BY `wpbs_ib_stripe_order`.`id` DESC";
   $sql="SELECT *, sum(product_price) as price FROM {$wpdb->prefix}ib_stripe_order group by user_id  ";
   $ten_recent_donation=$wpdb-> get_results( $sql);
    
      $emp=[];
      $user_mx=[];
        $sqlmeta="SELECT * FROM `{$wpdb->prefix}usermeta`";
       $meta=$wpdb->get_results($sqlmeta);
      
      
     
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
           array_push($user_mx,$user_name);
           $a = $result->price;
           array_push($emp,$a);
           
           
       }
       
       
        $max_price= max($emp);
         
        $result_mx = array_combine($emp, $user_mx);
        
         foreach($result_mx as $re_mx){
                  
        
         }
         
         $max_name=max($result_mx);
       
// top 10 recent donation 




//design
?>



<div class="main">
    



  
  <div class="recent"  >
    <div class="symbol">
      <a href="#" class="fa fa-user"></a>
      <div class="recentinfo">
        <p class="para1" id="text">
         <?php echo $ruser_name ?>  
        
        </p>
        
        <p class="para2">
         <strong >$<?php echo  $rpprice ?></strong> recent donation 
        </p>
      </div>
    </div>
  </div>

  <div class="max">
    <div class="symbol">
      <a href="#" class="fa fa-user"></a>
      <div class="recentinfo">
        <p class="para1" id="text2">
          <?php echo $max_name ?> 
        </p>
        <p class="para2">
         <strong> $<?php echo $max_price  ?></strong> top donation 
        </p>
      </div>
    </div>
  </div>


 

  <div class="first">
    <div class="symbol">
      <a href="#" class="fa fa-user"></a>
      <div class="recentinfo">
        <p class="para1" id="text3">
            <?php echo $fuser_name ?> 
        </p>
        <p class="para2">
         <strong>$<?php echo  $fpprice ?></strong> first donation 
        </p>
      </div>
    </div>
  </div>
  
</div>
      
      <div class="ten-recent-don">
          <h1>all recent doonation</h1>
          
          <?php foreach($ten_recent_donation as $ten):
                   $tid = $ten->user_id;
                   $tser_info = get_userdata($tid);
                  $tser_name = $tser_info->display_name;
                  $tpprice =  $ten->price;
           ?>
            <div class="max">
            <div class="symbol">
              <a href="#" class="fa fa-address-book"></a>
              <div class="recentinfo">
                <p class="para1" id="text2" value="<?php echo $tid ?>">
                  <?php echo $tser_name ?> 
                </p>
                <p class="para2">
                 <strong> $<?php echo $tpprice  ?></strong> recent donation  
                </p>
              </div>
            </div>
          </div>
          <?php   endforeach;    ?>
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

a.fa.fa-user {
    color: lightgreen;
}


</style>

<?php
 return ob_get_clean(); 
}
add_shortcode('donate', 'demo');
