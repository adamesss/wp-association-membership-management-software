<?php

define( "VERSION", "1.0" );

// Register action hook function to be called when the admin pages are
// starting to be prepared for display
add_action( 'admin_init', 'wpamms_adminsettings_init' );

// Function to register the Settings for this plugin
// and declare the fields to be displayed
function wpamms_adminsettings_init() {
	// Register our setting group with a validation function
	// so that $_POST handling is done automatically for us
	register_setting( 'wpamms_settings',
		'wpamms_options','wpamms_validate_options' );

	// Add a new settings section within the group
	add_settings_section( 'wpamms_main_section',
		'Main Settings', 'wpamms_main_setting_section_callback',
		'wpamms_settings_section' );

	// Add the fields with the names and function to use for our new
	// settings, put them in our new section
	add_settings_field( 'membership_admin_email', 'Membership Administrator Email Address',
		'wpamms_display_text_field', 'wpamms_settings_section',
		'wpamms_main_section', array( 'name' => 'membership_admin_email' ) );
        
	add_settings_field( 'short_tittle', 'Association/Membership Tittle (keep it short!)',
		'wpamms_display_text_field', 'wpamms_settings_section',
		'wpamms_main_section', array( 'name' => 'short_tittle' ) );

	add_settings_field( 'send_email_notification', 'Send Email Notification?',
		'wpamms_display_check_box', 'wpamms_settings_section',
		'wpamms_main_section', array('name' => 'send_email_notification')); 
        
	add_settings_field( 'membercard_template_path', 'Template (png) For Membership Card',
		'wpamms_display_text_field_disabled', 'wpamms_settings_section',
		'wpamms_main_section', array( 'name' => 'membercard_template_path' ) );
}

// Validation function to be called when data is posted by user
// No validation done at this time. Straight return of values.
function wpamms_validate_options( $input ) {
	$input['version'] = VERSION;
	return $input;
}

// Function to display text at the beginning of the main section
function wpamms_main_setting_section_callback() { ?>
	<p>This is the WP-AMMS main configuration section.</p>
<?php }

// Function to render a text input field
function wpamms_display_text_field( $data = array() ) {
	extract( $data );
	$options = get_option( 'wpamms_options' ); 
	?>
        <input type="text" size="100" name="wpamms_options[<?php echo $name; ?>]" value="<?php echo esc_html( $options[$name] ); ?>"/><br />

<?php }

// Function to render a text input field
function wpamms_display_text_field_disabled( $data = array() ) {
	extract( $data );
	$options = get_option( 'wpamms_options' ); 
	?>
        <input type="text" size="100" name="wpamms_options[<?php echo $name; ?>]" value="<?php echo esc_html( $options[$name] ); ?>" /><br />
        <b>Warning: don't change this value, unless you know what you are doing!<br/>
            You may change/edit the template for membercard (png image file), which is stored at "assets/membercard-template.png"</b><br/>
        In addition, you may need to modify some source code at 'shortcode-membercard.php' to define your own layout for your custom membercard<br/><br/>
        Don't wanna be bothered doing this cumbersome process? Relax, and let us do the hard job. We offer professional service at affordable cost to help you handling this issue.
        Contact me : Hatma Suryotrisongko ( suryotrisongko@gmail.com ) <br /><br />

<?php }

// Function to render a check box
function wpamms_display_check_box( $data = array() ) {
	extract ( $data );
	$options = get_option( 'wpamms_options' ); 
	?>
	<input type="checkbox" name="wpamms_options[<?php echo $name; ?>]" <?php if ( $options[$name] ) echo ' checked="checked" '; ?>/>
<?php }

// Register action hook to be called when the administration menu is
// being constructed
add_action( 'admin_menu', 'wpamms_settings_menu' );

// Function called when the admin menu is constructed to add a new menu item
// to the structure
function wpamms_settings_menu() {
    
        add_menu_page( 'WP-AMMS Membership',
            'WP-AMMS Membership', 'manage_options',
            'wpamms', 'wpamms_config_page' );
}

// Function called to render the contents of the plugin
// configuration page
function wpamms_config_page() { ?>
	<div id="wpamms-general" class="wrap">
	<h2>WP-AMMS Configuration - Wordpress Plugin: Association / Membership Management Software</h2>
        <p>Features: Member Registration with Admin Member Management/Activation, Email Notifications, Cron Job (Automatic Scheduler) for Renewal Reminder, Renewal Procedures with Admin Confirmation, Member List, Member Card Generation, etc.<br/>
            We would like to welcome any critics, bug report, suggestion, feature request, etc. Please drop me email -> Hatma Suryotrisongko (suryotrisongko@gmail.com)<br/>
        </p>
	<form name="wpamms_options_form_settings_api" method="post" action="options.php">

        <?php if (isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true') {
            ?>
            <div id='message' class='updated fade'><p><strong>Settings Saved</strong></p></div>
        <?php } ?>
            
	<?php settings_fields( 'wpamms_settings' ); ?>
	<?php do_settings_sections( 'wpamms_settings_section' ); ?> 

	<input type="submit" value="Submit" class="button-primary" />
	</form>
	</div>
<?php }