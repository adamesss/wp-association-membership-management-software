<?php

register_activation_hook(__FILE__, 'my_activation');

function my_activation() {
	wp_schedule_event(time(), 'daily', 'my_daily_event');
}

add_action('my_daily_event', 'do_this_daily');

function do_this_daily() {
        //SELECT *, DATEDIFF(`expirationdate`, CURDATE()) as activeduration FROM `wp_amms_members` WHERE DATEDIFF(`expirationdate`, CURDATE()) IN (60,30,0)
        $member_query = 'select *, DATEDIFF(`expirationdate`, CURDATE()) as activeduration from ';
        $member_query .= $wpdb->get_blog_prefix();
        $member_query .= "amms_members WHERE DATEDIFF(`expirationdate`, CURDATE()) IN (60,30,0)";

        //$members_data = $wpdb->get_results( $wpdb->prepare( $member_query ), ARRAY_A );
        $members_data = $wpdb->get_results( $member_query , ARRAY_A );
        
        // Check if any members were found
	if ( $members_data ) {

                foreach ( $members_data as $member_data ) {
                    
                    $multiple_recipients = array(
                        $member_data['emailaddress'],
                        get_option( 'admin_email' )
                    );
                    
                    if ($member_data['activeduration'] == 0) {
                        $subj = '[Expired] Today, Your Membership at '.get_site_url().' will be expired. Please Renew Now';
                        if($member_data['active'] == 1) {
                            $member_data['active'] = 0;
                        }
                        $wpdb->update( $wpdb->get_blog_prefix() . 'amms_members', $member_data, array( 'id' => $member_data['id'] ) );

                    } else {
                        $subj = '[Reminder} Your Membership at '.get_site_url().' is almost expired. Please Renew Now';
                    }
                    
                    $body .= 'Dear ' . $member_data['name'] . ' "\n" ';
                    $body .= '' . $member_data['institution']  . ' "\n" ';
                    $body .= '' . $member_data['department']  . ' "\n\n" ';
                    $body .= 'Your memberships will remain active for the next '.$member_data['activeduration'].' days \n';
                    $body .= 'expiration date = ' . $member_data['expirationdate']   . ' "\n\n" ';
                    $body .= 'Renew now please, by filling the renewal form at '.get_site_url().' "\n\n" ';
                    $body .= 'If You Need Assistance, Please Contact Help Desk at = '. get_option( "admin_email" ) . ' "\n\n\n" ';
                    $headers = 'From: '.get_site_url(). ' Membership Administrator <'. get_option( "admin_email" ) . '>' . "\r\n";
                     wp_mail( $multiple_recipients, $subj, $body, $headers );    

                } 
	}
}

register_deactivation_hook(__FILE__, 'my_deactivation');

function my_deactivation() {
	wp_clear_scheduled_hook('my_daily_event');
}