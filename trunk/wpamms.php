<?php

/*
Plugin Name: WP-AMMS (Wordpress Plugin: Association / Membership Management Software)
Plugin URI: https://wordpress.org/plugins/association-membership-management-software/
Description: Wordpress Plugin: Association / Membership Management Software. Features: Member Registration with Admin Member Management/Activation, Email Notifications, Cron Job (Automatic Scheduler) for Renewal Reminder, Renewal Procedures with Admin Confirmation, Member List, Member Card Generation, etc. IMPORTANT: after installing plugin, set Membership Administrator Email Address AND Association/Membership Tittle (to make sure email notification sent successfully). Developed by: IT Infrastructure & Security Laboratory, Information Systems Department, Faculty of Information Technology, Institut Teknologi Sepuluh Nopember. http://is.its.ac.id/labs/ikti/
Version: 1.0
Author: Hatma Suryotrisongko 
Author URI: http://is.its.ac.id/labs/ikti/researchers/#hatma
License: GPLv2
*/

// Activation Callback
function wpamms_activate() {
	// Get access to global database access class
	global $wpdb;

	// Check to see if WordPress installation is a network
	if ( is_multisite() ) {

		// If it is, cycle through all blogs, switch to them
		// and call function to create plugin table
		if ( isset( $_GET['networkwide'] ) && ( $_GET['networkwide'] == 1) ) {
			$start_blog = $wpdb->blogid;

			$blog_list = $wpdb->get_col( 'SELECT blog_id FROM ' . $wpdb->blogs );
			foreach ( $blog_list as $blog ) {
				switch_to_blog( $blog );

				// Send blog table prefix to table creation function
				wpamms_create_table( $wpdb->get_blog_prefix() );
			}
			switch_to_blog( $start_blog );
			return;
		}
	}

	// Create table on main blog in network mode or single blog
	wpamms_create_table( $wpdb->get_blog_prefix() );
        wpamms_set_default_options();
        wp_schedule_event(time(), 'daily', 'my_daily_event');

}

// Register function to be called when plugin is activated
register_activation_hook( __FILE__, 'wpamms_activate' );

register_deactivation_hook( __FILE__, 'wpamms_deactivate' );

function wpamms_deactivate() {
	wp_clear_scheduled_hook('my_daily_event');
}

add_action('my_daily_event', 'wpamms_do_this_daily');

function wpamms_do_this_daily() {
        $options = get_option( 'wpamms_options' );
	global $wpdb;

        $member_query = 'select *, DATEDIFF(`expirationdate`, CURDATE()) as activeduration from ';
        $member_query .= $wpdb->get_blog_prefix();
        $member_query .= "amms_members WHERE DATEDIFF(`expirationdate`, CURDATE()) IN (60,59,58,57,56,55,54,30,29,28,27,26,25,24,0,-1,-2,-3,-4,-5,-6)";

        $members_data = $wpdb->get_results( $member_query , ARRAY_A );
        
        // Check if any members were found
	if ( $members_data ) {

                foreach ( $members_data as $member_data ) {
                    
                      if( $member_data['activeduration'] >= 54 &&  $member_data['notification1'] == 1 ) {
                        continue;
                      }
                      if( $member_data['activeduration'] >= 24 &&  $member_data['notification2'] == 1 ) {
                        continue;
                      }
                      if( $member_data['activeduration'] >= -6 &&  $member_data['notification3'] == 1 ) {
                        continue;
                      }
                      
                    if ($options['send_email_notification']) { 
                        $multiple_recipients = array(
                            sanitize_email($member_data['emailaddress']),
                            sanitize_email($options['membership_admin_email'])
                        );

                        if ($member_data['activeduration'] <= 0) {
                            $subj = "[Expired] ".esc_html($options['short_tittle'])." Membership (id=".esc_html($member_data['memberid']).') has been expired. Renew Now';
                        } else {
                            $subj = "[Reminder] ".esc_html($options['short_tittle'])." Membership (id=".esc_html($member_data['memberid']).') is almost expired. Renew Now';
                        }

                        $body = "Dear " . esc_html($member_data['name']) . "\r\n";
                        $body .= esc_html($member_data['institution'])  . "\r\n";
                        $body .= esc_html($member_data['department'])  . "\r\n\r\n";

                        if ($member_data['activeduration'] >= 0) {
                            $body .= "Your memberships will remain active for the next ".$member_data['activeduration']." days \r\n";
                        } else {
                            $body .= "Your memberships had been expired \r\n";
                        }
                        
                        $body .= "Expiration date = " . esc_html($member_data['expirationdate'])   . "\r\n\r\n";
                        $body .= "Renew now please. \r\n\r\n";
                        $body .= "If you have any question, Please Contact Help Desk at = ". sanitize_email($options['membership_admin_email']) . "\r\n\r\n";

                        if ( wp_mail( $multiple_recipients, $subj, $body ) ) {
                            
                            if( $member_data['activeduration'] >= 54  ) {
                                $cronquery = "UPDATE " . $wpdb->get_blog_prefix() . "amms_members SET notification1 = '1' WHERE id = '".intval($member_data['id'])."' " ;
                                $wpdb->query( $cronquery );
                            } elseif( $member_data['activeduration'] >= 24 ) {
                                $cronquery = "UPDATE " . $wpdb->get_blog_prefix() . "amms_members SET notification2 = '1' WHERE id = '".intval($member_data['id'])."' " ;
                                $wpdb->query( $cronquery );
                            } elseif( $member_data['activeduration'] >= -6 ) {
                                $cronquery = "UPDATE " . $wpdb->get_blog_prefix() . "amms_members SET notification3 = '1' WHERE id = '".intval($member_data['id'])."' " ;
                                $wpdb->query( $cronquery );
                            }
                      
                        } 
                        
                    }
                } 
	}
        $cronquery = 'UPDATE ' . $wpdb->get_blog_prefix() . 'amms_members SET active = 0 WHERE active = 1 AND expirationdate <= CURDATE()';
        $wpdb->query( $cronquery );
}


// Function called upon plugin activation to initialize the options values
// if they are not present already
function wpamms_set_default_options() {
	if ( get_option( 'wpamms_options' ) === false ) {
		$new_options['membership_admin_email'] = "";
		$new_options['short_tittle'] = "";
		$new_options['membercard_template_path'] = "assets/membercard-template.png";
		$new_options['send_email_notification'] = true;
		$new_options['version'] = VERSION;
		add_option( 'wpamms_options', $new_options );         
	} else {
		$existing_options = get_option( 'wpamms_options' );
		if ($existing_options['version'] < VERSION) {            
			$existing_options['version'] = VERSION;
			update_option( 'wpamms_options', $existing_options );
		}
	}
}

// Register function to be called when new blogs are added
// to a network site
add_action( 'wpmu_new_blog', 'wpamms_new_network_site' );

function wpamms_new_network_site( $blog_id ) {
	global $wpdb;

	// Check if this plugin is active when new blog is created
	// Include plugin functions if it is
	if ( !function_exists( 'is_plugin_active_for_network' ) )
		require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

	// Select current blog, create new table and switch back to
	// main blog
	if ( is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
		$start_blog = $wpdb->blogid;
		switch_to_blog( $blog_id );

		// Send blog table prefix to table creation function
		wpamms_create_table( $wpdb->get_blog_prefix() );

		switch_to_blog( $start_blog );
	}
}

// Function to create new database table
function wpamms_create_table( $prefix ) {
	// Prepare SQL query to create database table
	// using received table prefix
	global $wpdb;

	$creation_query =
'CREATE TABLE ' . $prefix . 'amms_members (
id int(11) NOT NULL AUTO_INCREMENT,
name varchar(255) NOT NULL,
memberid varchar(255) NOT NULL,
institution varchar(255) NOT NULL,
department varchar(255) NOT NULL,
address varchar(255) NOT NULL,
city varchar(255) NOT NULL,
province varchar(255) NOT NULL,
postalcode varchar(255) NOT NULL,
emailaddress varchar(255) NOT NULL,
phonenumber varchar(255) NOT NULL,
gender varchar(255) NOT NULL,
researchfocus text NOT NULL,
photo text NOT NULL,
paymentreceipt text NOT NULL,
membersince DATE NOT NULL,
expirationdate DATE NOT NULL,
active BOOLEAN NOT NULL,
notification1 BOOLEAN NOT NULL,
notification2 BOOLEAN NOT NULL,
notification3 BOOLEAN NOT NULL,
PRIMARY KEY (id)
);';
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $creation_query );
	//$wpdb->query( $creation_query );
	
	$creation_query2 =
'CREATE TABLE ' . $prefix . 'amms_renewal (
id int(11) NOT NULL AUTO_INCREMENT,
name varchar(255) NOT NULL,
emailaddress varchar(255) NOT NULL,
paymentreceipt text NOT NULL,
renewaldate DATE NOT NULL,
confirmed BOOLEAN NOT NULL,
PRIMARY KEY (id)
);';
	//require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $creation_query2 );
	//$wpdb->query( $creation_query2 );
	
}

require_once 'admin-settings-configuration.php';
require_once 'admin-member-management.php';
require_once 'admin-renewal-management.php';
require_once 'adminmembers-add-edit-delete-import.php';
require_once 'adminrenewal-add-edit-delete-import.php';
require_once 'shortcode-display-member-list.php';
require_once 'shortcode-member-registration.php';
require_once 'shortcode-membercard.php';
require_once 'shortcode-member-renewal.php';

add_action('wp_enqueue_scripts', 'wpamms_load_scripts');
add_action('admin_enqueue_scripts', 'wpamms_load_scripts');

function wpamms_load_scripts() {
    wp_enqueue_script('jquery');
    add_thickbox();
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_style('datepickercss', plugins_url('css/ui-lightness/jquery-ui-1.8.17.custom.css', __FILE__), array(), '1.8.17');
    wp_enqueue_script('tiptipjs', plugins_url('tiptip/jquery.tipTip.js', __FILE__), array(), '1.3');
    wp_enqueue_style('tiptip', plugins_url('tiptip/tipTip.css', __FILE__), array(), '1.3');
}

add_action('wp_footer', 'wpamms_footer_code');
add_action('admin_footer', 'wpamms_footer_code');

function wpamms_footer_code() {
            ?>
        <script type="text/javascript">
            function ammspopup(p1) {
                tb_show( '', p1, null );
            };
            
            jQuery( document ).ready( function() {
                jQuery( '.amms_tooltip' ).each( function() {
                    jQuery( this ).tipTip();
                }
                );
            });
        </script>
    <?php
}