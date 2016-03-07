<?php

// Register function to be called when administration pages init takes place
add_action( 'admin_init', 'wpamms_adminrenewal_init' );

// Register functions to be called when renewals are saved
function wpamms_adminrenewal_init() {
	add_action('admin_post_save_wpamms_renewal',
		'process_wpamms_renewal');

	add_action('admin_post_delete_amms_renewal',
		'delete_amms_renewal');
}

// Function to be called when new renewals are created or existing renewals
// are saved
function process_wpamms_renewal() {
	// Check if user has proper security level
	if ( !current_user_can( 'manage_options' ) )
		wp_die( 'Not allowed' );

	// Check if nonce field is present for security
	check_admin_referer( 'wpamms_renewal_add_edit' );

	global $wpdb;

	// Place all user submitted values in an array
	$member_data = array();
	$member_data['id'] = ( isset( $_POST['id'] ) ? $_POST['id'] : '' );
	$member_data['name'] = ( isset( $_POST['name'] ) ? $_POST['name'] : '' );
	$member_data['emailaddress'] = ( isset( $_POST['emailaddress'] ) ? $_POST['emailaddress'] : '' );
	$member_data['paymentreceipt'] = ( isset( $_POST['paymentreceipt'] ) ? $_POST['paymentreceipt'] : '' );
	$member_data['renewaldate'] = ( isset( $_POST['renewaldate'] ) ? $_POST['renewaldate'] : '' );
	$member_data['confirmed'] = ( isset( $_POST['confirmed'] ) ? $_POST['confirmed'] : '' );
            
        if ( ! function_exists( 'wp_handle_upload' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
        }

        if( file_exists($_FILES['filePaymentReceiptToUpload']['tmp_name']) && is_uploaded_file($_FILES['filePaymentReceiptToUpload']['tmp_name'])) {

            $uploadedfile = $_FILES['filePaymentReceiptToUpload'];
            $uploadedfile['name'] = date("Y-m-d-H-i-s") . '_' . $uploadedfile['name'];
            $upload_overrides = array( 'test_form' => false );
            $movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
            if ( $movefile && !isset( $movefile['error'] ) ) {
                //echo "File is valid, and was successfully uploaded.\n";
                //var_dump( $movefile);
                $member_data['paymentreceipt'] = $movefile['url'];
            } else {
                wp_die("Upload PAYMENT RECEIPT Failed! " );
            }
            
        }
        
        
	// Call the wpdb insert or update method based on value
	// of hidden member_id field
	if ( isset( $_POST['id'] ) && $_POST['id'] == 'new' ) {
                $member_data['id'] = ''; 
		$wpdb->insert($wpdb->get_blog_prefix() . 'amms_renewal', $member_data );
        } elseif ( isset( $_POST['id'] ) && is_numeric( $_POST['id'] ) ) {
		$wpdb->update( $wpdb->get_blog_prefix() . 'amms_renewal', $member_data, array( 'id' => $_POST['id'] ) );
        }
        
        $multiple_recipients = array(
            $member_data['emailaddress'],
            get_option( 'admin_email' )
        );
        $subj = '[Notification} Your Renewal at '.get_site_url().' has been updated';
        $body = 'Your Membership Renewal Data :';
        $body .= 'name = ' . $member_data['name'] . ' "\n" ';
        $body .= 'emailaddress = ' . $member_data['emailaddress']   . ' "\n" ';
        $body .= 'paymentreceipt = ' . $member_data['paymentreceipt']   . ' "\n" ';
        $body .= 'renewal date = ' . $member_data['renewaldate']   . ' "\n" ';
        
        if($member_data['confirmed'] == 1)
            $body .= 'renewal status = CONFIRMED "\n\n\n" ';
        else 
            $body .= 'renewal status = FAILED "\n\n\n" ';
        
        $body .= 'In Case Any Incorrect Data, Please Report to Membership Help Desk at = '. get_option( "admin_email" ) . ' "\n\n\n" ';
        $body .= 'Your Membership Card Is Available To Be Downloaded at '.get_site_url(). '. If You Need Assistance, Please Contact Help Desk at = '. get_option( "admin_email" ) . ' "\n\n\n" ';
        $headers = 'From: '.get_site_url(). ' Membership Administrator <'. get_option( "admin_email" ) . '>' . "\r\n";
        wp_mail( $multiple_recipients, $subj, $body, $headers );        
        
	// Redirect the page to the admin form
	wp_redirect( add_query_arg( 'page', 'wpamms-renewal', admin_url( 'admin.php' ) ) );
	exit;
}

// Function to be called when deleting renewals
function delete_amms_renewal() {
	// Check that user has proper security level
	if ( !current_user_can( 'manage_options' ) )
		wp_die( 'Not allowed' );

	// Check if nonce field is present
	check_admin_referer( 'amms_renewal_deletion' );

	if ( !empty( $_POST['renewals_id'] ) ) {
		// Retrieve array of renewals IDs to be deleted
		$renewals_to_delete = $_POST['renewals_id'];

		global $wpdb;

		foreach ( $renewals_to_delete as $renewal_to_delete ) {
			$query = 'DELETE from ' . $wpdb->get_blog_prefix() . 'amms_renewal ';
			$query .= 'WHERE id = ' . intval( $renewal_to_delete );
			//$wpdb->query( $wpdb->prepare( $query ) );
			$wpdb->query( $query );
		}
	}

	// Redirect the page to the admin page
	wp_redirect( add_query_arg( 'page', 'wpamms-renewal', admin_url( 'admin.php' ) ) );
	exit;
}