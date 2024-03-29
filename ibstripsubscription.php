<?php

/**
 * Trigger this file on Plugin install
 * @package iBStripe
 */

 

 //If this file is called directly, abort!!!

defined( 'ABSPATH' ) or die('Access denied!');

define('IB_STRIPE_DB_VERSION', '1.0');
define('IB_STRIPE_API_KEY', get_option('ib-stripe-secret-key-settings'));
define('IB_STRIPE_PUBLISHABLE_KEY', get_option('ib-stripe-publish-key-settings'));
define('IB_STRIPE_DONATE_API_KEY', get_option('ib-stripe-donate-secret-key-settings'));
define('IB_STRIPE_DONATE_PUBLISHABLE_KEY', get_option('ib-stripe-donate-publish-key-settings'));


class IB_Stripe_Settings {

    function __construct() {

        add_action('admin_menu', array($this, 'ib_stripe_create_admin_menu'));

        add_filter( 'page_template', array($this, 'ib_stripe_set_page_template'));

        //add_action('template_redirect', array($this, 'ib_stripe_restrict_page_to_open_directly') );

        add_action( 'wp_ajax_nopriv_ib_stripe_submit_registration',array($this, 'ib_stripe_submit_registration_handler'));
        add_action( 'wp_ajax_ib_stripe_submit_registration', array($this, 'ib_stripe_submit_registration_handler') );

        add_action( 'wp_ajax_nopriv_ib_stripe_payment_processing',array($this, 'ib_stripe_payment_processing_method'));
        add_action( 'wp_ajax_ib_stripe_payment_processing', array($this, 'ib_stripe_payment_processing_method') );
        
        // AJAX hook for saving details of current selected card
        add_action( 'wp_ajax_nopriv_ib_stripe_save_current_selected_card',array($this, 'ib_stripe_save_current_selected_card_handler'));
        add_action( 'wp_ajax_ib_stripe_save_current_selected_card', array($this, 'ib_stripe_save_current_selected_card_handler') );
        
        // AJAX hook for donation payment click
        add_action( 'wp_ajax_nopriv_ib_stripe_donation_payment_processing',array($this, 'ib_stripe_donation_payment_processing_handler'));
        add_action( 'wp_ajax_ib_stripe_donation_payment_processing', array($this, 'ib_stripe_donation_payment_processing_handler') );

    }

    //Function to trigger on plugin activate
    function ib_stripe_functions_on_activate() {

        self::ib_stripe_create_page('life_checkout_registration', 'Life Checkout');

        self::ib_stripe_create_page('life_checkout_payment', 'Life Payment');

        self::ib_stripe_create_page('life_thank_you', 'Thank you');

        self::ib_stripe_db_install();

    }
    
    function ib_stripe_donation_payment_processing_handler() {
       
        header("location: ".home_url().'/life-payment?value='.$_POST['value'].'&interval='.$_POST['interval'].'&anonymous='.$_POST['anonymous']);
    }

    function ib_stripe_submit_registration_handler() {
        
        /*if ( is_user_logged_in() ) {
            $response['redirectTo'] = home_url().'/life-payment';
            wp_send_json_success( $response );
        }*/
        
        // Checkin and starting session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
 
        $response = array();

        // Saving data to session
        // $_SESSION["user_reg_data"] = $_POST['reg_data'];
        
        $regUserData = $_POST['reg_data'];
 
        if( email_exists($regUserData['reg_email']) == true ){
            $alreadyUser = get_user_by('email', $regUserData['reg_email']);
            $response['registered'] = true;
            wp_send_json_success( $response );
        	
        } else {
            // Creating and logging user
            $userdata = array(
                'user_pass'             => $regUserData["password"],   //(string) The plain-text user password.
                'user_login'            => $regUserData['reg_email'],   //(string) The user's login username.
                'user_email'            => $regUserData['reg_email'],   //(string) The user email address.
                'display_name'          => $regUserData['fName'],   //(string) The user's display name. Default is the user's username.
                'first_name'            => $regUserData['fName'],   //(string) The user's first name. For new users, will be used to build the first part of the user's display name if $display_name is not specified.
                'last_name'             => $regUserData['lName'],   //(string) The user's last name. For new users, will be used to build the second part of the user's display name if $display_name is not specified.
            );
            $address = array(
                'city' => $regUserData['cityBirth'],
                'country' => $regUserData['contryName'],  
            );
            $user_id = wp_insert_user( $userdata ) ;
            update_usermeta( $user_id, 'address_city', $regUserData['cityBirth'] );
            update_usermeta( $user_id, 'address_country', $regUserData['contryName'] );

             // On success.
            if ( ! is_wp_error( $user_id ) ) {
                self::auto_login($user_id);
                $response['redirectTo'] = home_url().'/life-payment';
                $response['registered'] = false;
                wp_send_json_success( $response );
            }
        }

    }

    function auto_login( $user_id ) {
        // log in automatically
        if ( !is_user_logged_in() ) {
            clean_user_cache($user_id);
            wp_clear_auth_cookie();
            wp_set_current_user($user_id);
            wp_set_auth_cookie($user_id, true, false);
            
            $user = get_user_by('id', $user_id);
            update_user_caches($user);
            $_SESSION["current_user"] = $user;
        }     
    }
    
    // Saving details of current selected card
    function ib_stripe_save_current_selected_card_handler() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        
        $response = $_POST;
        $response['card_holder'] = $_SESSION['current_user']->first_name." ".$_SESSION['current_user']->last_name;
        $response['email'] = $_SESSION['current_user']->user_email;

        $_SESSION['selected_card'] = $response;
        
    }


    function ib_stripe_payment_processing_method(){
         if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
      
        $user_wp_data = $_SESSION["current_user"];
        $card_data = $_SESSION['selected_card'];
        // $user_data = $_SESSION["user_reg_data"];
        $response = array();
       

        if(isset( $_SESSION["donate_amount"]) && $_SESSION["donate_interval"] == 0 ){
            
            $amount = ((int)$_SESSION["donate_amount"]) * 100;
            $donate_key = IB_STRIPE_DONATE_API_KEY;
	     	$data = self::ib_stripe_customer_create($user_wp_data,$card_data,$donate_key);
	 	    // var_dump($data);
	 		if(empty($data['api_error']) && $data['customer']){
	 			
	 			$charge = self::ib_stripe_charge_create($data,$card_data,$amount);
	            if( $charge ){
	                var_dump($charge);
	                unset($_SESSION["donate_amount"]);
	                unset($_SESSION["donate_interval"]);
	                unset($_SESSION["anonymous"]);
	                $response['authenticationUrl'] = "";
	                $response['status'] = $charge->status;
	                $response['redirect'] = true;
	                $response['redirect'] = home_url().'/thank-you';
	                
	            }else{
	                $response['redirect'] = false;
	            }
 			}
     
        } elseif (isset( $_SESSION["donate_amount"]) && $_SESSION["donate_interval"] == 1 ) {
              $anonymous = $_SESSION["anonymous"];
            $donate_key = IB_STRIPE_DONATE_API_KEY;
            $data = self::ib_stripe_customer_create($user_wp_data,$card_data,$donate_key);
	 	    // var_dump($data);
	 		if(empty($data['api_error']) && $data['customer']){

	 			$amount = ((int)$_SESSION["donate_amount"]) * 100;	
	 			$price = self::ib_stripe_product_price_create($amount);
	 			
	 			$stripe = new \Stripe\StripeClient(
		          IB_STRIPE_DONATE_API_KEY
		        );  
	 			
	 			$subscription_data = $stripe->subscriptions->create([
	              'customer' => $data['customer']->id,
	              'items' => [
	                ['price' => $price->id],
	              ],
	              'default_payment_method' => $card_data['stripeToken']['card']['id'],
	            ]);
	            
	            if( $subscription_data ){
	                
	                $data_url = self::ib_stripe_authentication_handler($subscription_data->latest_invoice,$donate_key);
                    $product = array();
	            $product['id'] = $_SESSION["product_id"] ;
	            $product['price'] = $_SESSION["price"] ;
	            $product['time'] = $_SESSION["interval"] ;
	            $product['title'] = $_SESSION['product_title'];
                     $insertData = [
	                    'user_id' => $user_wp_data->id,
	                    'subscription_id' => $subscription_data->id,
	                    'product_id' => $product['id'],
	                    'product_title' => $product['title'],
	                    'product_price' => $product['price'],
	                    'order_frequency' => $product['time'],
	                    'customer_id' => $subscription_data->customer,
	                    'payment_id' => $subscription_data->default_payment_method,
	                    'invoice_id' => $subscription_data->latest_invoice,
	                    'response_data' => json_encode($subscription_data),
	                ];

	                  update_user_meta( $user_wp_data->id, 'anonymous', $anonymous );
	                
	                if ( self::ib_stripe_order_create($insertData) ){
	                  //  update_user_meta( $user_wp_data->id, 'anonymous', $anonymous );
	                    
	                    $response['redirect'] = home_url().'/thank-you';
	                }
	                
	                
	                unset($_SESSION["donate_amount"]);
	                unset($_SESSION["donate_interval"]);
	                unset($_SESSION["anonymous"]);

	                $response['authenticationUrl'] = $data_url->next_action->use_stripe_sdk->stripe_js;
	              
	                $response['status'] = $data_url->status;
	                $response['redirect'] = true;
	                $response['redirect'] = home_url().'/thank-you';

	            }else{
	                $response['redirect'] = false;
	            }

 			}
            
        } 
        elseif(isset($_SESSION["product_id"])){
         
        	$subscription_key = IB_STRIPE_API_KEY;
            $data = self::ib_stripe_customer_create($user_wp_data,$card_data,$subscription_key);
	 	    // var_dump($data);
	 		if(empty($data['api_error']) && $data['customer']){
	 		    
	 		    $anonymous = $_SESSION["anonymous"];
	 		     
	 			$product = array();
	            $product['id'] = $_SESSION["product_id"] ;
	            $product['price'] = $_SESSION["price"] ;
	            $product['time'] = $_SESSION["interval"] ;
	            $product['title'] = $_SESSION['product_title'];
	            
	            $subscription_data = self::ib_stripe_subscription_create($data, $product, $card_data,$subscription_key);
	            
	            if($subscription_data){
	                $data_url = self::ib_stripe_authentication_handler($subscription_data->latest_invoice,$subscription_key);                    
	                $response['authenticationUrl'] = $data_url->next_action->use_stripe_sdk->stripe_js;
	                $response['status'] = $data_url->status;
	                $response['redirect'] = true;
	                
	                $insertData = [
	                    'user_id' => $user_wp_data->id,
	                    'subscription_id' => $subscription_data->id,
	                    'product_id' => $product['id'],
	                    'product_title' => $product['title'],
	                    'product_price' => $product['price'],
	                    'order_frequency' => $product['time'],
	                    'customer_id' => $subscription_data->customer,
	                    'payment_id' => $subscription_data->default_payment_method,
	                    'invoice_id' => $subscription_data->latest_invoice,
	                    'response_data' => json_encode($subscription_data),
	                ];

	                if ( self::ib_stripe_order_create($insertData) ){
	                  
	                    
	                    $response['redirect'] = home_url().'/thank-you';
	                }
	            }
	            else{
	              $response['redirect'] = false;
	            }
	 		}
        }

  /*      
        $stripe = new \Stripe\StripeClient(
            IB_STRIPE_API_KEY
        );

        $paymentMethod = $stripe->paymentMethods->create([
            'type' => 'card',
            'card' => [
              'number' => '4242424242424242',
              'exp_month' => 7,
              'exp_year' => 2022,
              'cvc' => '314',
            ],
            'billing_details' => [
                'address' => [
                        'city' => $user_data['cityBirth'],
                        'country' => $user_data['contryName'],
                    ],
                'email' => $user_data['reg_email'],
                'name' => ($user_data['fName']." ".$user_data['lName'])
            ]
        ]);
    
        $customer = $stripe->customers->create([
            'description' => 'test customer',
            'email' => $user_data['reg_email'],
            'name' => ($user_data['fName']." ".$user_data['lName']),
            'payment_method' => $paymentMethod->id
        ]);
   
        $subscription = $stripe->subscriptions->create([
          'customer' => $customer->id,
          'items' => [
            [
                'price_data' => [
                    'product' => 'prod_K9ZLJcgLSYXfr0',
                    'currency' => 'USD',
                    'recurring' => [
                        'interval' => 'year'
                    ],
                    'unit_amount' => 14
                ]
            ]
          ],
          'default_payment_method' => $paymentMethod->id
        ]);
        print_r($subscription);
        echo "==========================";

        if($subscription->id != ""){
            $response['redirect'] = true;
        }
        else{
            $response['redirect'] = false;   
        }
*/

        wp_send_json_success( $response );

    }


    function ib_stripe_customer_create($user_data, $card_data, $key){
    	// Set API key 
	    \Stripe\Stripe::setApiKey($key); 
	    $city = get_user_meta($user_data->id , $key = 'address_city', true);
        $country = get_user_meta($user_data->id , $key = 'address_country',true);    
        // var_dump($city,$country);
	    // Add customer to stripe 
	    try {  
	        $data['customer'] = \Stripe\Customer::create(array(
	        	'name' =>  $user_data->first_name." ".$user_data->last_name,
	            'email' => $user_data->user_email, 
	            'source'  => $card_data['stripeToken']['id'], 
                'address' => [
                    'city' => $city,
                    'country' => $country,
                    'line1' => $city.' '.$country,
                ],

	        )); 
	    }catch(Exception $e) {  
	        $data['api_error'] = $e->getMessage();  
	    } 
	     return $data;
	}
	

	function ib_stripe_subscription_create($data,$product,$card_data,$key){
		\Stripe\Stripe::setApiKey($key);
		$prod_price = ($product['price']*100); 
        // var_dump($product['price']);
        // Charge a credit or a debit card 
          
            $subscription = \Stripe\Subscription::create(array( 
                'customer' => $data['customer']->id,
		          'items' => [
		            [
		                'price_data' => [
		                    'product' => $product['id'],
		                    'currency' => 'USD',
		                    'recurring' => [
		                        'interval' => $product['time'],
		                    ],
		                    'unit_amount' => $prod_price,
		                ]
		            ]
		          ],
		          'default_payment_method' => $card_data['stripeToken']['card']['id']
            )); 
        
        return $subscription; 
         
	}

	function ib_stripe_charge_create($data,$card_data,$amount){
		
		$stripe = new \Stripe\StripeClient(
          IB_STRIPE_DONATE_API_KEY
        );
        
        $charge = $stripe->charges->create([
          'customer' => $data['customer']->id,      
          'amount' => $amount,
          'currency' => 'usd',
          'source' => $card_data['stripeToken']['card']['id'],
          'description' => 'Donation Test Charge ',
        ]);

        return $charge;
	}

	function ib_stripe_product_price_create($amount){
		//Static Product ID of Donation at the time of interval
        $prod_id ='prod_KDYr9k0ocUFX1b';

        $stripe = new \Stripe\StripeClient(
          IB_STRIPE_DONATE_API_KEY
        );
        
        $price = $stripe->prices->create([
          'unit_amount' => $amount,
          'currency' => 'usd',
          'recurring' => ['interval' => 'month'],
          'product' => $prod_id,
        ]);

        return $price;
	}

    function ib_stripe_authentication_handler($invoice_id,$key){

        $stripe = new \Stripe\StripeClient(
          $key
        );

        $invoice = $stripe->invoices->retrieve(
          $invoice_id,
          []
        );
        
        $pay = $stripe->paymentIntents->retrieve(
          $invoice->payment_intent,
          []
        );
    
        // var_dump($pay);
        return $pay;

    }

    
    // Adding order details to database
    function ib_stripe_order_create($data) {

        global $wpdb;        
        $table_name = $wpdb->prefix . 'ib_stripe_order';
        
        $inserted = $wpdb->insert( 
            $table_name, 
            $data
        );  

        if($inserted) return true;
        else return false;

    }


    // Add Rcord to DB
    // function ib_stripe_insert_user($subscriptionID,$customerID,$userID,$paymentID,$invoiceID,$responseData){
      
    //     global $mydb;
    //     $mydb = new wpdb("ytosrnhj_wp67" , "Q9)Y21mp!S" , "ytosrnhj_wp67" , "localhost" );

    //     $table_name = $mydb->prefix . 'ib_stripe_payment_response';

    //     $mydb->insert($table_name, array('subscription_id' => $subscriptionID, 'customer_id' => $customerID, 'user_id' => $userID, 'payment_id' => $paymentID, 'invoice_id' => $invoiceID, 'response_data' => $responseData) ); 
    
    // }

    // Configuring database table
    function ib_stripe_db_install() {
        global $wpdb;

        $table_name = $wpdb->prefix . ' ib_stripe_order';
        
        $charset_collate = $wpdb->get_charset_collate();

        $installed_ver = get_option( "_IB_STRIPE_DB_VERSION" );

        if ( $installed_ver != IB_STRIPE_DB_VERSION ) {

            $sql = "CREATE TABLE $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                user_id varchar(60) NOT NULL,
                subscription_id varchar(60) NOT NULL,
                product_id varchar(60) NOT NULL,
                product_title varchar(60) NOT NULL,
                product_price varchar(60) NOT NULL,
                order_frequency varchar(60) NOT NULL,
                customer_id varchar(60) NOT NULL,
                payment_id varchar(60) NOT NULL,
                invoice_id varchar(60) NOT NULL,
                response_data LONGTEXT NOT NULL,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id)
            );";

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );

            update_option( "_IB_STRIPE_DB_VERSION", IB_STRIPE_DB_VERSION );

        } else {

            $sql = "CREATE TABLE $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                user_id varchar(60) NOT NULL,
                subscription_id varchar(60) NOT NULL,
                product_id varchar(60) NOT NULL,
                product_title varchar(60) NOT NULL,
                product_price varchar(60) NOT NULL,
                order_frequency varchar(60) NOT NULL,
                customer_id varchar(60) NOT NULL,
                payment_id varchar(60) NOT NULL,
                invoice_id varchar(60) NOT NULL,
                response_data LONGTEXT NOT NULL,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id)
            ) $charset_collate;";

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );

            add_option( '_IB_STRIPE_DB_VERSION', IB_STRIPE_DB_VERSION );

        }
    }
    

    // Creating admin menu

    function ib_stripe_create_admin_menu() {            

        add_menu_page(

            __( 'IB Stripe Module', 'ib-stripe-module' ),

            __( 'IB Stripe', 'ib-stripe-module' ),

            'manage_options', 

            'ib-stripe-module', 

            array( $this, 'ib_stripe_admin_page_callback_function'),

            'dashicons-media-spreadsheet',

            3

        );

        add_submenu_page( 
        
        	'ib-stripe-module',
        
        	'Stripe Keys',

            'Stripe Keys',
        
        	'manage_options',
        
        	'ib-stripe-module-stripe-keys',
        
        	array( $this, 'ib_stripe_admin_sub_menu_page_callback_function') 
        
        );        

    }

    // Callback function for admin sub menu page
    
    function ib_stripe_admin_sub_menu_page_callback_function(){

    	$admin_template_file = IB_STRIPE_PLUGIN_PATH . 'templates/admin/ib-stripe-admin-stripe-keys.php';

        if( file_exists($admin_template_file) ) {

            include( $admin_template_file );

        }    	
    }

    // Callback function for admin menu page

    function ib_stripe_admin_page_callback_function() {

        $admin_template_file = IB_STRIPE_PLUGIN_PATH . 'templates/admin/ib-stripe-admin-results.php';

        if( file_exists($admin_template_file) ) {

            include( $admin_template_file );

        }

    }
    

    // Creating Checkout page

    function ib_stripe_create_page($page_name, $page_title)

    {

        $pageExist = get_option($page_name); 

        if(!$pageExist){

            //post status and options

            $post = array(

                  'comment_status' => 'closed',

                  'ping_status' =>  'closed' ,

                  'post_author' => 1,

                  'post_date' => date('Y-m-d H:i:s'),

                  'post_status' => 'publish' ,

                  'post_title' => $page_title,

                  'post_type' => 'page',

            );  

            //insert page and save the id

            $newPageId = wp_insert_post( $post, false );

            //save the id in the database

            update_option( $page_name, $newPageId );

        }

    }

    

    // Setting page template to checkout page

    function ib_stripe_set_page_template( $page_template )

    {

        if ( is_page( 'life-checkout' ) ) {

            $page_template = IB_STRIPE_PLUGIN_PATH.'templates/frontend/life-checkout-registration.php';

            return $page_template;

        }

        if ( is_page( 'thank-you' ) ) {

            $page_template = IB_STRIPE_PLUGIN_PATH.'templates/frontend/life-thank-you.php';

            return $page_template;

        }

        if ( is_page( 'life-payment' ) ) {

            $page_template = IB_STRIPE_PLUGIN_PATH.'templates/frontend/life-checkout-payment.php';

            return $page_template;

        }else{

            return $page_template;

        }

    }

    

    // Redirect to home page for restricted page

    // function ib_stripe_restrict_page_to_open_directly(){

    //     if ( ! is_page('life-payment')) {

    //         return;

    //     }

    //     if (wp_get_referer() == home_url().'/life-checkout/') {

    //         return;

    //     }

    //     wp_redirect( get_home_url() );

    // }



}
