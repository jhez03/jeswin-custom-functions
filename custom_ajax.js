jQuery( function($) {

   jQuery("#jl_tax_exempt_cb").change( function(e) {
      e.preventDefault();

      post_id = jQuery('#post_ID').val();
      checked = this.checked;
      jQuery.ajax({
         type : "post",
         dataType : "json",
         url : myAjax.ajaxurl,
         data : {action: "jl_update_avatax_exemption", post_id : post_id, checked : checked},
         success: function(response) {
            
         }
      })   

   })
   /**Smart Offers***/
   jQuery('body').on('click', '#jl_so_accept', function(e) {
       e.preventDefault();
       $('body .dialog-widget-content').css({
		background: '#fff',
	       opacity: 0.6
       })
	   console.log('test');
       var product_id = $(this).attr("data-product_id");
       var qty = '1';
       var so_offer_url = $(this).attr("data-url");
       $.ajax({
           type: 'POST',
           url: jlAjax.ajaxurl,
           data: {
               'action': 'jl_so_process_offer_action',
               'product_id': product_id,
               'quantity': qty,
	       'jl_so_action': 'accept',
	       'jl_so_offer_url': so_offer_url
           },
           success: function(result) {
	   	console.log(result)
               jQuery(document.body).trigger('wc_fragment_refresh');
               $('body .dialog-close-button').trigger('click');
           },
           error: function(error) {}
       });
   }); 
   jQuery( document ).on( 'elementor/popup/show', ( event, id, instance ) => {
   $('#elementor-popup-modal-393205').css({
	display: 'none'
   })
	if(id == 393205){
	
       $.ajax({
           type: 'POST',
           url: jlAjax.ajaxurl,
           data: {
               'action': 'jl_add_so_meta_data',
           },
           success: function(result) {
	   	console.log(result)
           },
           error: function(error) {}
       });
	}
   } );
})
