/**
 * Module's JavaScript.
 */
 var pmpro_customer_email = '';

 // Initialize the JS
function initPMPro(customer_email, load) {
    pmpro_customer_email = customer_email;

    $(document).ready(function(){

        if (load) {
            pmproLoadOrders();
        }

        $('.pmpro-refresh').click(function(e) {
            pmproLoadOrders();
            e.preventDefault();
        });
    });
}
 
 function pmproLoadOrders()
 {
     $('#pmpro-orders').addClass('pmpro-loading');
 
     fsAjax({
             action: 'orders',
             customer_email: pmpro_customer_email,
             mailbox_id: getGlobalAttr('mailbox_id')
         }, 
         laroute.route('pmpro.ajax'), 
         function(response) {
             if (typeof(response.status) != "undefined" && response.status == 'success'
                 && typeof(response.html) != "undefined" && response.html
             ) {
                 $('#pmpro-orders').html(response.html);
                 $('#pmpro-orders').removeClass('pmpro-loading');
 
                 $('.pmpro-refresh').click(function(e) {
                     pmproLoadOrders();
                     e.preventDefault();
                 });
             } else {
                 //showAjaxError(response);
             }
         }, true
     );
 }