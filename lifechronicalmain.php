//design
?>
<div class="main">
    
  <div class="recent">
    <div class="symbol">
      <a href="#" class="fa fa-facebook"></a>
      <div class="recentinfo">
        <p class="para1">
         <?php echo $ruser_name ?>        
        </p>
        <p class="para2">
         <strong>$<?php echo  $rpprice ?></strong> recent donation 
        </p>
      </div>
    </div>
  </div>

  <div class="max">
    <div class="symbol">
      <a href="#" class="fa fa-facebook"></a>
      <div class="recentinfo">
        <p class="para1">
          Sami Sengören
        </p>
        <p class="para2">
         <strong> $<?php echo $max_price  ?></strong> top donation 
        </p>
      </div>
    </div>
  </div>

  <div class="first">
    <div class="symbol">
      <a href="#" class="fa fa-facebook"></a>
      <div class="recentinfo">
        <p class="para1">
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
</style>
        
<?php
   
}
add_shortcode('donate', 'demo');
?>
