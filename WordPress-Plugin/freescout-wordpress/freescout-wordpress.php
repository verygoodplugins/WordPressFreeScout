<?php
/*
Plugin Name: FreeScout WordPress Helper
Description: Registers a REST route to get user and order data by email.
Version: 1.0.1
Author: verygoodplugins
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Register a new route for the REST API.
 *
 * @since 1.0.0
 */
function freescout_register_route() {

	register_rest_route( 'freescout/v1', '/email', array(
		'methods'             => 'GET',
		'callback'            => 'freescout_email_to_user_callback',
		'permission_callback' => 'is_user_logged_in',
		'args'                => array(
			'emails' => array(
				'required' => true,
				'validate_callback' => function ( $param, $request, $key ) {
					return is_array( $param );
				}
			),
		),
	));
}

add_action( 'rest_api_init', 'freescout_register_route' );

/**
 * The callback function for our route. Returns user ID by email.
 *
 * @since 1.0.0
 *
 * @param WP_REST_Request $request The request object.
 *
 */
function freescout_email_to_user_callback( $request ) {

	$emails = $request->get_param( 'emails' );

	foreach ( $emails as $email ) {

		$user = get_user_by( 'email', sanitize_email( $email ) );

		if ( $user ) {
			break;
		}

	}

	if ( ! $user && ! empty( $request->get_param( 'first_name' ) ) && ! empty( $request->get_param( 'last_name' ) ) ) {

		// Try by name.

		$args = array(
			'number'     => 1,
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key'     => 'first_name',
					'value'   => sanitize_text_field( $request->get_param( 'first_name' ) ),
					'compare' => 'LIKE',
				),
				array(
					'key'     => 'last_name',
					'value'   => sanitize_text_field( $request->get_param( 'last_name' ) ),
					'compare' => 'LIKE',
				),
			),
		);

		$users = get_users($args);

		if ( $users ) {
			$user = $users[0];
		}

	}

	if ( ! $user ) {
		return new WP_Error( 'no_user', 'No user found with the provided email', array( 'status' => 404 ) );
	}

	$data = array(
		'ID'                 => $user->ID,
		'first_name'         => $user->first_name,
		'last_name'          => $user->last_name,
		'edit_url'           => admin_url( 'user-edit.php?user_id=' . $user->ID ),
		'registered'         => date( 'm/d/Y', strtotime( $user->user_registered ) ),
		'last_license_check' => get_user_meta( $user->ID, 'last_license_check', true ),
		'version'            => get_user_meta( $user->ID, 'wpf_version', true ),
		'current_version'    => get_post_meta( 207, '_edd_sl_version', true ),
		'active_crm'         => get_user_meta( $user->ID, 'active_crm', true ),
		'integrations'       => explode( ',', get_user_meta( $user->ID, 'active_integrations', true ) ),
	);

	// WP Fusion.

	if ( function_exists( 'wp_fusion' ) ) {

		$data['crm_name']     = wp_fusion()->crm->name;
		$data['crm_edit_url'] = wp_fusion()->crm->get_contact_edit_url( wpf_get_contact_id( $user->ID ) );
		$data['tags']         = wp_fusion()->user->get_tags( $user->ID );

	}

	// EDD.

	if ( function_exists( 'edd_get_payments' ) ) {

		$orders = edd_get_payments( array(
			'user'   => $user->ID,
			'number' => 50,  // safe limit.
			'status' => array( 'publish', 'edd_subscription' ),
		) );

		$data['edd_orders'] = array();

		foreach ( $orders as $order ) {

			// Purchase amount.
			$purchase_amount = edd_currency_filter( edd_format_amount( $order->total ) );

			// URL to edit the order in WP admin.
			$edit_order_url = admin_url( 'edit.php?post_type=download&page=edd-payment-history&view=view-order-details&id=' . $order->ID );

			// Cart items.
			$cart_items = edd_get_payment_meta_cart_details( $order->ID, true );

			$products = array();

			foreach ( $cart_items as $item ) {

				// Product name and price ID name.

				$products[] = array(
					'name'  => $item['name'],
					'price' => edd_currency_filter( edd_format_amount( $item['price'] ) ),
				);

			}

			$data['edd_orders'][] = array(
				'ID'              => $order->ID,
				'purchase_date'   => $order->post_date,
				'purchase_amount' => $purchase_amount,
				'is_refunded'     => ( $order->post_status === 'refunded' ),
				'status'          => $order->post_status,
				'is_renewal'      => edd_get_payment_meta( $order->ID, '_edd_sl_is_renewal', true ),
				'payment_method'  => edd_get_payment_gateway( $order->ID ),
				'edit_order_url'  => $edit_order_url,
				'products'        => $products,
			);
		}
	}

	// EDD software licensing.

	if ( function_exists( 'edd_software_licensing' ) ) {

		$licenses = edd_software_licensing()->get_license_keys_of_user( $user->ID );

		$data['licenses'] = array();

		foreach ( $licenses as $license ) {

			$data['licenses'][] = array(
				'ID'          => $license->ID,
				'license_key' => $license->key,
				'product_id'  => $license->download_id,
				'product'     => get_the_title( $license->download_id ),
				'expires'     => ! empty( $license->expiration ) ? date( 'm/d/Y', intval( $license->expiration ) ): false,
				'is_active'   => $license->status === 'active',
				'edit_url'    => admin_url( 'edit.php?post_type=download&page=edd-licenses&view=overview&license_id=' . $license->ID ),
				'sites'       => array_map( 'untrailingslashit', $license->sites ),
			);

		}

	}

    $data['edd_subscriptions'] = array();

    if(class_exists('EDD_Recurring_Subscriber'))
    {
        $subscriptions_data = array();

        $subscriber    = new EDD_Recurring_Subscriber( $user->ID, true );

        $subscriptions = $subscriber->get_subscriptions( 0, array( 'active', 'expired', 'cancelled', 'failing', 'trialling' ) );

        if ( $subscriptions ) {

            foreach ( $subscriptions as $subscription ) {

                $subscription_data = array();

                // Subscription ID
                $subscription_data['subscription_id'] = '#' . $subscription->id;

                // Product details
                $subscription_data['product_name'] = get_the_title( $subscription->product_id );

                // Subscription status
                $subscription_data['status'] = $subscription->get_status_label();

                // Expiration date
                $expiration_date = ! empty( $subscription->expiration ) ? date_i18n( get_option( 'date_format' ), strtotime( $subscription->expiration ) ) : __( 'N/A', 'edd-recurring' );
                $subscription_data['expiration_date'] = $expiration_date;

                // Billing frequency
                $frequency = EDD_Recurring()->get_pretty_subscription_frequency( $subscription->period );
                $subscription_data['billing_frequency'] = edd_currency_filter( edd_format_amount( $subscription->recurring_amount ), edd_get_payment_currency_code( $subscription->parent_payment_id ) ) . ' / ' . $frequency;


                // Initial amount
                $subscription_data['initial_amount'] = edd_currency_filter( edd_format_amount( $subscription->initial_amount ), edd_get_payment_currency_code( $subscription->parent_payment_id ) );

                // Times billed
                $subscription_data['times_billed'] = $subscription->get_times_billed() . ' / ' . ( ( $subscription->bill_times == 0 ) ? __( 'Until cancelled', 'edd-recurring' ) : $subscription->bill_times );

                // Subscription detail screen link
                $subscription_detail_url = admin_url( 'edit.php?post_type=download&page=edd-subscriptions&id=' . $subscription->id );
                $subscription_data['detail_link'] = $subscription_detail_url;

                // Add subscription data to the array
                $subscriptions_data[] = $subscription_data;
            }
        }

        $data['edd_subscriptions'] = $subscriptions_data;
    }



	return new WP_REST_Response( $data, 200 );

}
