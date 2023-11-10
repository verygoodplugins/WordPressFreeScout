/**
 * Module's JavaScript.
 */
 var wordpress_customer_email = '';

 // Initialize the JS
function initPMPro(customer_email, load) {
    wordpress_customer_email = customer_email;

    $(document).ready(function(){

        if (load) {
            wordpressLoadOrders();
        }

        $('.wordpress-refresh').click(function(e) {
            wordpressLoadOrders();
            e.preventDefault();
        });
    });
}
 
 function wordpressLoadOrders()
 {
     $('#wordpress-orders').addClass('wordpress-loading');
 
     fsAjax({
             action: 'orders',
             customer_email: wordpress_customer_email,
             mailbox_id: getGlobalAttr('mailbox_id')
         }, 
         laroute.route('wordpress.ajax'), 
         function(response) {
             if (typeof(response.status) != "undefined" && response.status == 'success'
                 && typeof(response.html) != "undefined" && response.html
             ) {
                 $('#wordpress-orders').html(response.html);
                 $('#wordpress-orders').removeClass('wordpress-loading');
 
                 $('.wordpress-refresh').click(function(e) {
                     wordpressLoadOrders();
                     e.preventDefault();
                 });
             } else {
                 //showAjaxError(response);
             }
         }, true
     );
 }