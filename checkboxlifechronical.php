function demo() {
    //fetch data from wpbs_ib_stripe_order
    global $wpdb;  
    
    $results = $wpdb->get_results( "SELECT *, sum(product_price) as price FROM {$wpdb->prefix}ib_stripe_order group by user_id");
    
    $first_donation= $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}ib_stripe_order where id=1");
    
    $recent_donation= $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ib_stripe_order ORDER BY 'id' 'DESC' ");
    
     
      $emp=[];
      $user_mx=[];
      

      
      
     
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
       
        



//design
?>



<div class="main">
    
  <label for="myCheck">Checkbox:</label> 
<input type="checkbox" id="myCheck" onclick="myFunction()">



  
  <div class="recent"  >
    <div class="symbol">
      <a href="#" class="fa fa-user"></a>
      <div class="recentinfo">
        <p class="para1" id="text">
         <?php echo $ruser_name ?>  <p hidden id="ht" class="para1"> <?php echo "anonomus"; ?></p>
        
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


<script>
function myFunction() {
  var checkBox = document.getElementById("myCheck");
  var text = document.getElementById("text");
  var textt = document.getElementById("text2");
  var texttt = document.getElementById("text3");
  
  var htx=document.getElementById("ht");
  
  //text
  if (checkBox.checked == false){
      text.style.display = "block";
      htx.style.display="none";
    }  else{
        htx.style.display="block";
         text.style.display = "none";
    }
  
  
  //textt
  
  if (checkBox.checked == false){
      textt.style.display = "block";
    } else {
     textt.style.display = "none";
  }
   
   //texttt
   if (checkBox.checked == false){
      texttt.style.display = "block";
    } else {
     texttt.style.display = "none";
  }
  
}


  
 
  
</script>
<?php
   
}
add_shortcode('donate', 'demo');
?>
