<?php

/*
Plugin Name: WPAMMS (Wordpress Plugin: Association Membership Management Software)
Plugin URI: http://aisindo.org
Description: Wordpress Plugin: Association Membership Management Software. Features: Member Registration with Admin Member Management/Activation, Notifications Email, Cron Job Reminder Renewal, Renewal Procedures with Admin Confirmation, Member List, etc.
Version: 1.0.0
Author: Hatma Suryotrisongko 
Author URI: aisindo.org/leadership-teams/committees/#IT-Manager
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
}

// Register function to be called when plugin is activated
register_activation_hook( __FILE__, 'wpamms_activate' );


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

require_once 'admin-member-management.php';
require_once 'admin-renewal-management.php';
require_once 'adminmembers-add-edit-delete-import.php';
require_once 'adminrenewal-add-edit-delete-import.php';
require_once 'admin-cron-job-notification.php';
require_once 'shortcode-display-member-list.php';
require_once 'shortcode-member-registration.php';
require_once 'shortcode-membercard.php';
require_once 'shortcode-member-renewal.php';

add_action('wp_enqueue_scripts', 'amms_load_scripts');
add_action('admin_enqueue_scripts', 'amms_load_scripts');

function amms_load_scripts() {
    wp_enqueue_script('jquery');
    add_thickbox();
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_style('datepickercss', plugins_url('css/ui-lightness/ jquery-ui-1.8.17.custom.css', __FILE__), array(), '1.8.17');
    wp_enqueue_script('tiptipjs', plugins_url('tiptip/jquery.tipTip.js', __FILE__), array(), '1.3');
    wp_enqueue_style('tiptip', plugins_url('tiptip/tipTip.css', __FILE__), array(), '1.3');
}

add_action('wp_footer', 'amms_footer_code');
add_action('admin_footer', 'amms_footer_code');

function amms_footer_code() {
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