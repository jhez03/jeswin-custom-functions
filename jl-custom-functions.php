<?php
/*
Plugin Name: Jeswin Custom Functions
Description: Customized functions
Version: 1.0.0
Author: Jeswin Libay
*/

//------------------------------------------------------------------------------------------------------------------------------
if(!defined('WPINC'))
    exit('<span style="font-family:arial; font-size:20px">Invalid page access</span>');
//------------------------------------------------------------------------------------------------------------------------------

add_action('wp_head', 'jl_checkout_functions',9);
function jl_checkout_functions(){
    
    if (is_checkout()) {

	  echo "<script>jQuery(document).ready(function( $ ){
	  		  document.cookie = 'woocommerce_multicurrency_forced_currency=USD';
	          jQuery('#billing_country').on('change', function() {
	            if(this.value=='US') {
	            	document.cookie = 'woocommerce_multicurrency_forced_currency=USD';
	            }
	                if(this.value=='CA') {
	                	document.cookie = 'woocommerce_multicurrency_forced_currency=CAD';
	                }
	          });
	        
	      });</script>
	      ";	
	      if (!is_user_logged_in()) {
	     	echo "<style>
	     		@media only screen and (min-width: 1025px){

	     			.select2-dropdown{
	     				top: -167px !important;
	     			}

	     		}
	     		</style>";
	     }
	}

       
}
add_action('wp_footer', 'jl_promo_badge',99);

function jl_promo_badge(){
	global $product;
	if (!is_product()) {
		return false;
	}
	$coupon_code = '$100 off at checkout!';
	$coupon = new WC_Coupon($coupon_code);
	if ($coupon->is_valid_for_product($product)) {
	?>
	<script type="text/javascript">
		(function($) {
			$(document).ready(function($){

				if (localStorage.getItem('discount_button')) {
			    	$('.woocommerce-product-gallery .flex-viewport').prepend("<div class=\"jl-promo-bug2 jl-flipped\"><span class=\"jl-flip-back\"><i class=\"fas fa-check-circle\"></i> Discount automatically applied at checkout</span></div>");
			    }else{
			    	$('.woocommerce-product-gallery .flex-viewport').prepend("<div class=\"jl-promo-bug2 jl-flipped\"><span class=\"jl-flip-front\"><i class=\"fas fa-tag\"></i> Get an Extra $100 Off at Checkout</span></div>");
			    }
		    	 
				$('.jl-promo-bug2 .jl-flip-front').click(function () {
		
			        $('.jl-flip-front').css({
			        	'display':'none',
			        	'color'	: '#ff0000'
			        })
			        $('.woocommerce-product-gallery .flex-viewport').prepend("<div class=\"jl-promo-bug2 jl-flipped\"><span class=\"jl-flip-back\"><i class=\"fas fa-check-circle\"></i> Discount automatically applied at checkout</span></div>");
			        localStorage.setItem('discount_button', true);
			       
			        
			    })
		
		    	 
		    })
		})( jQuery );
	</script>
	<?php
		}

}
//Enable shortcode in page result
add_filter('the_excerpt', 'do_shortcode');
//cart refresh ajax
add_action( 'wp_ajax_jl_wc_fragment_refresh', 'jl_wc_fragment_refresh' );
add_action( 'wp_ajax_nopriv_jl_wc_fragment_refresh', 'jl_wc_fragment_refresh' );
function jl_wc_fragment_refresh(){
	global $woocommerce;
	$cart_url = $woocommerce->cart->get_cart_url();
	$cart_count = sprintf(_n('%d item', '%d items', $woocommerce->cart->cart_contents_count, 'astra-child'), $woocommerce->cart->cart_contents_count);
	$cart_total = $woocommerce->cart->get_cart_total();
	$data = array(
		'cart_url' => $cart_url,
		'cart_count' => $cart_count,
		'cart_total' => $cart_total
	);
	wp_send_json(
            array(
                'cart_url' => $cart_url,
				'cart_count' => $cart_count,
				'cart_total' => $cart_total
            )
        );
	die;
}

function jl_custom_enqueue() {   
   // wp_enqueue_script( 'mulberry-script', 'https://app-staging.getmulberry.com/plugin/static/js/mulberry.js','', false, false );
    //wp_enqueue_script( 'jl-custom-functions-script', get_stylesheet_directory_uri() . '/assets/js/jl_ajax_handler.js','', false, true );
    wp_localize_script( 
    'jl-custom-functions-script', 
    'ajax_object', 
    array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) 
  );

}
add_action('wp_enqueue_scripts', 'jl_custom_enqueue');
function jl_estimate_order_tax( $order_id ) {
	if (is_admin()) {
		$order = wc_get_order( $order_id );

		if ( $order->has_shipping_address() ) {
			$country_code = $order->get_shipping_country( 'edit' );
			$state        = $order->get_shipping_state( 'edit' );
		} else {
			$country_code = $order->get_billing_country( 'edit' );
			$state        = $order->get_billing_state( 'edit' );
		}

		$tax_totals = wc_avatax()->get_order_handler()->estimate_tax( $order );
		apply_filters( 'woocommerce_order_get_tax_totals', $tax_totals, 99 );
	}
		
}
function jl_order_update( $order_id ) {
	if (is_admin()) {
		?>
        <script type="text/javascript">
	    ( function ($) {
	        jQuery(document).ready(function( $ ){
	        	$('.wc-order-totals-items').load(location.href + " .wc-order-totals-items");
	        })

	    })(jQuery)
	    </script>
        <?php

	}
		
}
if (is_admin()) {
	add_action('woocommerce_admin_order_items_after_line_items', 'jl_estimate_order_tax');
	add_action('woocommerce_order_status_processing', 'jl_estimate_order_tax');
	add_action('wc_avatax_after_order_tax_calculated', 'jl_order_update');
}
add_action('woocommerce_before_order_notes' , 'jl_fedex_checkout_logo');
function jl_fedex_checkout_logo(){
	?>
	<p><i>Free ground shipping within the US via <img src="https://www.mattressinsider.com/wp-content/uploads/elementor/thumbs/fedex-opvv2uidklpwtu2x3kc0ihvnwahhdnval4y5klz84u.png" title="FedEx" alt="FedEx" class="lazyloaded" data-ll-status="loaded" style="height:2em; vertical-align:text-bottom;"></i></p>
	<?php
}
add_action( 'woocommerce_after_add_to_cart_button', 'jl_cart_call_to_order' );

function jl_cart_call_to_order(){
	?>
	<div class="jl-mobile-only" style="margin: 20px auto;text-align: center;">- or tap below to -</div>
	<a class="jl-call-to-order-button jl-mobile-only" href="tel:1-888-460-8911"><span>Call to Order</span></a>
	<?php
}
/**Smart Offers**/


//add_action('wp_head','so_process_offer_action');
add_action('wp_ajax_jl_so_process_offer_action','jl_so_process_offer_action');
function jl_so_process_offer_action(){
			global $sa_smart_offers, $current_user;

			$so_offer  = new SO_Offer();
			$so_offers = new SO_Offers();
			$so_init = new SO_Init();
	if ( isset( $_POST['jl_so_action'] ) && $_POST['jl_so_action'] == 'accept'){
	  //check if offer is already in cart 
   $product_id = wc_get_product_id_by_sku('LMM-PLW');  
	   foreach( WC()->cart->get_cart() as $cart_item ) {
	      $product_in_cart = $cart_item['product_id'];
	      if ( $product_in_cart === $product_id ) $in_cart = true;
	   }
   if( $in_cart ) return false;
	$offer_id = wp_parse_args($_POST['jl_so_offer_url']);
	$offer_id = $offer_id['so_offer_id'];
$current_offer_id = apply_filters( 'sa_so_wpml_get_current_lang_offer_id', $offer_id );

				$source = ( ! empty( $_POST['source'] ) ) ? $_POST['source'] : null;

				list($where, $where_url) = $so_offers->get_page_details();
				$page                    = $where . '_page';

				list($accepted_session_variable, $accepted_ids_in_session) = $so_offers->get_accepted_offer_ids_from_session();
				list($skipped_session_variable, $skipped_ids_in_session)   = $so_offers->get_skipped_offer_ids_from_session();

				$skip_offer_id_variable                                 = ( $where == 'any' ) ? str_replace( array( '/', '-', '&', '=', ':' ), '', $where_url ) . '_skip_offer_id' : $where . '_skip_offer_id';
				list($offer_id_on_skipping, $skipped_offer_id_variable) = $so_offers->get_offer_id_on_skipping( $skip_offer_id_variable );

				$parent_offer_id_variable         = ( $where == 'any' ) ? str_replace( array( '/', '-', '&', '=', ':' ), '', $where_url ) . '_parent_offer_id' : $where . '_parent_offer_id';
				$check_parent_offer_id_set_or_not = SO_Session_Handler::check_session_set_or_not( $parent_offer_id_variable );

				if ( ! $check_parent_offer_id_set_or_not ) {
					SO_Session_Handler::so_set_session_variables( $parent_offer_id_variable, $current_offer_id );
				}
		$variation_data  = ( isset( $_POST['variation_id'] ) || isset( $_POST['quantity'] ) ) ? $_POST : array();
					$parent_offer_id = '';

					if ( $offer_id_on_skipping != '' ) {
						$check_parent_offer_id = SO_Session_Handler::check_session_set_or_not( $parent_offer_id_variable );
						$parent_offer_id       = ( $check_parent_offer_id ) ? SO_Session_Handler::so_get_session_value( $parent_offer_id_variable ) : '';
					}

					SO_Session_Handler::so_delete_session( $parent_offer_id_variable );
					SO_Session_Handler::so_delete_session( $skip_offer_id_variable );

					SO_Session_Handler::so_set_session_variables( 'sa_smart_offers_accepted_offer_ids', $current_offer_id );

					// Update stats
					$so_offer->update_accept_skip_count( $current_offer_id, 'accepted' );

					// Validate offer before add to cart.
					$offer_ids = array( $current_offer_id );
					$is_valid  = $so_init->is_offer_valid( $page, $offer_ids );
						
						// Adds to cart
					$so_offer->action_on_accept_offer( $current_offer_id, $page, $parent_offer_id, $variation_data );
	}
				 				
			
}
add_action( 'woocommerce_before_cart', 'bbloomer_find_product_in_cart' );
    
function bbloomer_find_product_in_cart() {
  
   $product_id = wc_get_product_id_by_sku('LMM-PLW');  
   $in_cart = false;
   $term  = array('cot-mattress','custom-mattress-toppers','custom-mattresses','giant-mattress','mbuilder','residential-mattresses','specialty-mattresses','rv-mattress','sofa-mattress','truck-mattress'); 
   foreach( WC()->cart->get_cart() as $cart_item ) {
      $product_in_cart = $cart_item['product_id'];
      $terms = get_the_terms($product_in_cart, 'product_cat');
      if( has_term( $term, 'product_cat',$cart_item['product_id'] ) ) {
	  $mattress_included = true;
	}
      if ( $product_in_cart === $product_id ) $in_cart = true;
   }
   if ( $in_cart ) {	 
	   if(!$mattress_included){
 
		wc_print_notice('Please add a mattress to your order to qualify for your free pillow offer.','error');
	   }
  
   }
  
}
add_action('woocommerce_before_checkout_form','bbloomer_find_product_in_checkout');
function bbloomer_find_product_in_checkout(){
   $product_id = wc_get_product_id_by_sku('LMM-PLW');  
   $in_cart = false;
   $term  = array('cot-mattress','custom-mattress-toppers','custom-mattresses','giant-mattress','mbuilder','residential-mattresses','specialty-mattresses','rv-mattress','sofa-mattress','truck-mattress'); 
   foreach( WC()->cart->get_cart() as $cart_item ) {
      $product_in_cart = $cart_item['product_id'];
      $terms = get_the_terms($product_in_cart, 'product_cat');
      if( has_term( $term, 'product_cat',$cart_item['product_id'] ) ) {
	  $mattress_included = true;
	}
      if ( $product_in_cart === $product_id ) $in_cart = true;
   }
   if ( $in_cart ) {	 
	   if(!$mattress_included){
 
		wp_redirect(home_url('/all-mattresses.html'));
		exit;
	   }
  
   }
}
add_action( 'init', 'jl_script_plugin_enqueuer' );
function jl_script_plugin_enqueuer() {
   wp_register_script( "jl_plugin_ajax_script", WP_PLUGIN_URL.'/jeswin-custom-functions/custom_ajax.js', array('jquery') );
   wp_localize_script( 'jl_plugin_ajax_script', 'jlAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));        

   wp_enqueue_script( 'jquery' );
   wp_enqueue_script( 'jl_plugin_ajax_script' );

}
//add custom meta if pop up is shown to a customer
add_action('wp_ajax_jl_add_so_meta_data' , 'jl_add_so_session_meta_data');
function jl_add_so_session_meta_data(){
	// Early initialize customer session
    if ( isset(WC()->session) && ! WC()->session->has_session() ) {
        WC()->session->set_customer_session_cookie( true );
    }
    WC()->session->set('jl_so_data', array('viewed' => 'yes'));
    wp_die();
}
add_action('woocommerce_checkout_create_order', 'jl_add_so_meta_data');
function jl_add_so_meta_data($order){
   $data = WC()->session->get( 'jl_so_data' ) ; //Get custom data from session
   if (isset($data['viewed'])){
	$order->update_meta_data( '_jl_so_viewed' , $data['viewed'] );
   }
  WC()->session->__unset( 'jl_so_data' ); // Remove session variable
}
add_action( 'woocommerce_admin_order_data_after_billing_address', 'jl_display_so_meta' );
function jl_display_so_meta( $order ) {
    if ( $so_viewed = $order->get_meta( '_jl_so_viewed' ) )
        echo '<p><strong>Is pillow offer viewed? :</strong> ' . $so_viewed . '</p>';

}
?>
