<?php

// Register function to be called when admin menu is constructed
add_action( 'admin_menu', 'wpamms_adminmembers_menu' );

// Add new menu item under Settings menu for Member Tracker
function wpamms_adminmembers_menu() {
	add_submenu_page( 'wpamms', 'Member Management',
		'Member Management', 'manage_options',
		'wpamms-members',
		'wpamms_membermanagement_page' );
}

// Function to render plugin admin page
function wpamms_membermanagement_page() {
	global $wpdb;
	?>
	<!-- Top-level menu -->
	<div id="wpamms-general" class="wrap">
	<h2>Member Management
		<a class="add-new-h2" 
			href="<?php echo add_query_arg( array ( 'page' => 'wpamms-members', 'id' => 'new'), admin_url('admin.php')); ?>">Add New Member</a></h2>

	<!-- Display member list if no parameter sent in URL -->
	<?php if ( empty( $_GET['id'] ) ) { ?>

            <h3>Manage Member Entries</h3>
            <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
            <input type="hidden" name="action" value="delete_amms_member" />

            <!-- Adding security through hidden referrer field -->
            <?php wp_nonce_field( 'amms_member_deletion' ); ?>

            
            <h4>NEW REGISTRATIONS (Needs To Be Validated As Soon As Possible!)</h4>
            Click on member's NAME to validate / modify data <br/>
            <?php
                    $member_query = 'select * from ';
                    $member_query .= $wpdb->get_blog_prefix();
                    $member_query .= "amms_members WHERE active = '-1' ORDER BY membersince ASC";

                    //$members_data = $wpdb->get_results( $wpdb->prepare( $member_query ), ARRAY_A );
                    $members_data = $wpdb->get_results( $member_query , ARRAY_A );
            ?>

            <table class="wp-list-table widefat fixed" >
                <thead><tr>
                    <th style="width: 20px"></th>
                    <th style="width: 80px">Name</th>
                    <th>Member ID | Institution | Department | Address | City | Province | Postal Code | Email Address | Phone Number | Gender | Research Focus</th>
                    <th style="width: 80px">Photo | Proof of Payment</th>
                    <th style="width: 80px">Member Since</th>
                    <th style="width: 80px">Expiration Date</th>
                    <th style="width: 85px">Membership Status Active?</th>
                </tr></thead>

            <?php 
                    // Display members if query returned results
                    if ( $members_data ) {
                            foreach ( $members_data as $member_data ) {
                                    echo '<tr style="background: #FFF">';
                                    echo '<td><input type="checkbox" name="members_id[]" value="';
                                        echo esc_attr( $member_data['id'] ) . '" /></td>';
                                    echo '<td><a href="' . add_query_arg( array( 'page' => 'wpamms-members', 'id' => $member_data['id'] ), admin_url( 'admin.php' ) );
                                        echo '">' . $member_data['name'] . '</a></td>';
                                    echo '<td>' . $member_data['memberid'] . ' | ' 
                                                . $member_data['institution'] . ' | ' 
                                                . $member_data['department'] . ' | ' 
                                                . $member_data['address'] . ' | ' 
                                                . $member_data['city'] . ' | ' 
                                                . $member_data['province'] . ' | ' 
                                                . $member_data['postalcode'] . ' | ' 
                                                . $member_data['emailaddress'] . ' | ' 
                                                . $member_data['phonenumber'] . ' | ' 
                                                . $member_data['gender'] . ' | ' 
                                                . $member_data['researchfocus'] . ' </td>';

                                    if( $member_data['photo'] != '') {
                                        echo '<td><a onclick="ammspopup(';
                                        echo "'". $member_data['photo']."'";
                                        echo ')">Photo</a> | ';
                                    } else 
                                        echo '<td>';
                                      
                                    if( $member_data['paymentreceipt'] != '') {
                                        echo '<a onclick="ammspopup(';
                                        echo "'". $member_data['paymentreceipt']."'";
                                        echo ')">Proof of Payment</a> </td>';
                                    } else
                                        echo '</td>';
                                        
                                    echo '<td>' . $member_data['membersince'] . '</td>';
                                    echo '<td>' . $member_data['expirationdate'] . '</td>';

                                    if($member_data['active'] == 1)
                                        echo '<td>true</td></tr>';
                                    else
                                        echo '<td><b>FALSE</b></td></tr>';
                            }
                    } else {
                            echo '<tr style="background: #FFF">';
                            echo '<td colspan=7>No Member Found</td></tr>';
                    }
            ?>
            </table><br />

            <input type="submit" value="Delete Selected" class="button-primary" onclick="return confirm('Are you sure you want to Remove?');"/>


            
            <h4>ACTIVE Member</h4>
            Click on member's NAME to validate / modify data <br/>
            <?php
                    $member_query = 'select * from ';
                    $member_query .= $wpdb->get_blog_prefix();
                    $member_query .= "amms_members WHERE active = '1' ORDER BY DATEDIFF(`expirationdate`, CURDATE() ) ASC";

                    //$members_data = $wpdb->get_results( $wpdb->prepare( $member_query ), ARRAY_A );
                    $members_data = $wpdb->get_results( $member_query , ARRAY_A );
            ?>

            <table class="wp-list-table widefat fixed" >
                <thead><tr>
                    <th style="width: 20px"></th>
                    <th style="width: 80px">Name</th>
                    <th>Member ID | Institution | Department | Address | City | Province | Postal Code | Email Address | Phone Number | Gender | Research Focus</th>
                    <th style="width: 80px">Photo | Proof of Payment</th>
                    <th style="width: 80px">Member Since</th>
                    <th style="width: 80px">Expiration Date</th>
                    <th style="width: 85px">Membership Status Active?</th>
                </tr></thead>

            <?php 
                    // Display members if query returned results
                    if ( $members_data ) {
                            foreach ( $members_data as $member_data ) {
                                    echo '<tr style="background: #FFF">';
                                    echo '<td><input type="checkbox" name="members_id[]" value="';
                                        echo esc_attr( $member_data['id'] ) . '" /></td>';
                                    echo '<td><a href="' . add_query_arg( array( 'page' => 'wpamms-members', 'id' => $member_data['id'] ), admin_url( 'admin.php' ) );
                                        echo '">' . $member_data['name'] . '</a></td>';
                                    echo '<td>' . $member_data['memberid'] . ' | ' 
                                                . $member_data['institution'] . ' | ' 
                                                . $member_data['department'] . ' | ' 
                                                . $member_data['address'] . ' | ' 
                                                . $member_data['city'] . ' | ' 
                                                . $member_data['province'] . ' | ' 
                                                . $member_data['postalcode'] . ' | ' 
                                                . $member_data['emailaddress'] . ' | ' 
                                                . $member_data['phonenumber'] . ' | ' 
                                                . $member_data['gender'] . ' | ' 
                                                . $member_data['researchfocus'] . ' </td>';

                                    if( $member_data['photo'] != '') {
                                        echo '<td><a onclick="ammspopup(';
                                        echo "'". $member_data['photo']."'";
                                        echo ')">Photo</a> | ';
                                    } else 
                                        echo '<td>';
                                      
                                    if( $member_data['paymentreceipt'] != '') {
                                        echo '<a onclick="ammspopup(';
                                        echo "'". $member_data['paymentreceipt']."'";
                                        echo ')">Proof of Payment</a> </td>';
                                    } else
                                        echo '</td>';
                                        
                                    echo '<td>' . $member_data['membersince'] . '</td>';
                                    echo '<td>' . $member_data['expirationdate'] . '</td>';

                                    if($member_data['active'] == 1)
                                        echo '<td>true</td></tr>';
                                    else
                                        echo '<td><b>FALSE</b></td></tr>';
                            }
                    } else {
                            echo '<tr style="background: #FFF">';
                            echo '<td colspan=7>No Member Found</td></tr>';
                    }
            ?>
            </table><br />
            
            
             <h4>EXPIRED Member</h4>
            Click on member's NAME to validate / modify data <br/>
            <?php
                    $member_query = 'select * from ';
                    $member_query .= $wpdb->get_blog_prefix();
                    $member_query .= "amms_members WHERE active = '0' ORDER BY DATEDIFF(`expirationdate`, CURDATE() ) DESC";

                    //$members_data = $wpdb->get_results( $wpdb->prepare( $member_query ), ARRAY_A );
                    $members_data = $wpdb->get_results( $member_query , ARRAY_A );
            ?>

            <table class="wp-list-table widefat fixed" >
                <thead><tr>
                    <th style="width: 20px"></th>
                    <th style="width: 80px">Name</th>
                    <th>Member ID | Institution | Department | Address | City | Province | Postal Code | Email Address | Phone Number | Gender | Research Focus</th>
                    <th style="width: 80px">Photo | Proof of Payment</th>
                    <th style="width: 80px">Member Since</th>
                    <th style="width: 80px">Expiration Date</th>
                    <th style="width: 85px">Membership Status Active?</th>
                </tr></thead>

            <?php 
                    // Display members if query returned results
                    if ( $members_data ) {
                            foreach ( $members_data as $member_data ) {
                                    echo '<tr style="background: #FFF">';
                                    echo '<td><input type="checkbox" name="members_id[]" value="';
                                        echo esc_attr( $member_data['id'] ) . '" /></td>';
                                    echo '<td><a href="' . add_query_arg( array( 'page' => 'wpamms-members', 'id' => $member_data['id'] ), admin_url( 'admin.php' ) );
                                        echo '">' . $member_data['name'] . '</a></td>';
                                    echo '<td>' . $member_data['memberid'] . ' | ' 
                                                . $member_data['institution'] . ' | ' 
                                                . $member_data['department'] . ' | ' 
                                                . $member_data['address'] . ' | ' 
                                                . $member_data['city'] . ' | ' 
                                                . $member_data['province'] . ' | ' 
                                                . $member_data['postalcode'] . ' | ' 
                                                . $member_data['emailaddress'] . ' | ' 
                                                . $member_data['phonenumber'] . ' | ' 
                                                . $member_data['gender'] . ' | ' 
                                                . $member_data['researchfocus'] . ' </td>';

                                    if( $member_data['photo'] != '') {
                                        echo '<td><a onclick="ammspopup(';
                                        echo "'". $member_data['photo']."'";
                                        echo ')">Photo</a> | ';
                                    } else 
                                        echo '<td>';
                                      
                                    if( $member_data['paymentreceipt'] != '') {
                                        echo '<a onclick="ammspopup(';
                                        echo "'". $member_data['paymentreceipt']."'";
                                        echo ')">Proof of Payment</a> </td>';
                                    } else
                                        echo '</td>';

                                    echo '<td>' . $member_data['membersince'] . '</td>';
                                    echo '<td>' . $member_data['expirationdate'] . '</td>';

                                    if($member_data['active'] == 1)
                                        echo '<td>true</td></tr>';
                                    else
                                        echo '<td><b>FALSE</b></td></tr>';
                            }
                    } else {
                            echo '<tr style="background: #FFF">';
                            echo '<td colspan=7>No Member Found</td></tr>';
                    }
            ?>
            </table><br />
        
	
	<input type="submit" value="Delete Selected" class="button-primary" onclick="return confirm('Are you sure you want to Remove?');"/>
	</form>
            
        <br/><br/>
            
	<!-- Form to upload new members in csv format -->
	<form method="post" 
          action="<?php echo admin_url( 'admin-post.php' ); ?>" 
          enctype="multipart/form-data">
        
	<input type="hidden" name="action" value="import_wpamms_members" />

	<!-- Adding security through hidden referrer field -->
	<?php wp_nonce_field( 'wpamms_import' ); ?>

	<h3>Import Members</h3>
	Import Members from CSV File
	(<a href="<?php echo plugins_url( 'amms-importtemplate.csv', __FILE__ ); ?>">Template</a>)
	<input name="importmembersfile" type="file" required/> <br /><br />

	<input type="submit" value="Import" class="button-primary"/>

	</form>
	
	<?php } elseif ( isset( $_GET['id'] ) && ( $_GET['id'] == "new" || is_numeric( $_GET['id'] ) ) ) {

		// Display member creation and editing form if member is new
		// or numeric id was sen       
		$member_id = $_GET['id'];
		$member_data = array();
		$mode = 'new';

		// Query database if numeric id is present
		if ( is_numeric( $member_id ) ) {
			$member_query = 'select * from ' . $wpdb->get_blog_prefix();
			$member_query .= 'amms_members where id = ' . $member_id;

			//$member_data = $wpdb->get_row( $wpdb->prepare( $member_query ), ARRAY_A );
			$member_data = $wpdb->get_row( $member_query , ARRAY_A );

			if ( $member_data ) $mode = 'edit';
                } else {
                    $member_data['id'] = '';
                    $member_data['name'] = '';
                    $member_data['memberid'] = '';
                    $member_data['institution'] = '';
                    $member_data['department'] = '';
                    $member_data['address'] = '';
                    $member_data['city'] = '';
                    $member_data['province'] = '';
                    $member_data['postalcode'] = '';
                    $member_data['emailaddress'] = '';
                    $member_data['phonenumber'] = '';
                    $member_data['gender'] = '';
                    $member_data['researchfocus'] = '';
                    $member_data['photo'] = '';
                    $member_data['paymentreceipt'] = '';
                    $member_data['membersince'] = '';
                    $member_data['expirationdate'] = '';
                    $member_data['active'] = '';
                }

		// Display title based on current mode
		if ( $mode == 'new' ) {
			echo '<h3>Add New Member</h3>';
		} elseif ( $mode == 'edit' ) {
			echo '<h3>Edit Member #' . $member_data['id'] . ' - ';
			echo $member_data['name'] . '</h3>';
		}
		?>

		<form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>" enctype="multipart/form-data">
		<input type="hidden" name="action" value="save_wpamms_member" />
		<input type="hidden" name="id" value="<?php echo esc_attr( $member_id ); ?>" />

		<!-- Adding security through hidden referrer field -->
		<?php wp_nonce_field( 'wpamms_members_add_edit' ); ?>

		<!-- Display member editing form, with previous values if available -->
		<table>
			<tr>
				<td style="width: 150px">Name</td>
                                <td><input type="text" name="name" class="amms_tooltip" title="Please enter member's NAME (max 255 characters!)" size="120" value="<?php echo esc_attr( $member_data['name'] ); ?>" required /></td>
			</tr>
			<tr>
				<td style="width: 150px">Member ID</td>
                                <td><input type="text" name="memberid" class="amms_tooltip" title="Please enter member's ID (max 255 characters!)" size="120" value="<?php echo esc_attr( $member_data['memberid'] ); ?>" required /></td>
			</tr>
			<tr>
				<td>Institution</td>
				<td><input type="text" name="institution" class="amms_tooltip" title="Please enter member's INSTITUTION (max 255 characters!)" size="120" value="<?php echo esc_attr( $member_data['institution'] ); ?>" required /></td>
			</tr>
			<tr>
				<td>Department</td>
				<td><input type="text" name="department" class="amms_tooltip" title="Please enter member's DEPARTMENT (max 255 characters!)" size="120" value="<?php echo esc_attr( $member_data['department'] ); ?>" required /></td>
			</tr>
			<tr>
				<td>Address</td>
				<td><input type="text" name="address" class="amms_tooltip" title="Please enter member's ADDRESS (max 255 characters!)" size="120" value="<?php echo esc_attr( $member_data['address'] ); ?>" required /></td>
			</tr>
			<tr>
				<td>City</td>
				<td><input type="text" name="city" class="amms_tooltip" title="Please enter CITY (max 255 characters!)" size="120" value="<?php echo esc_attr( $member_data['city'] ); ?>" required /></td>
			</tr>
			<tr>
				<td>Province</td>
				<td><input type="text" name="province" class="amms_tooltip" title="Please enter PROVINCE (max 255 characters!)" size="120" value="<?php echo esc_attr( $member_data['province'] ); ?>" required /></td>
			</tr>
			<tr>
				<td>Postal Code</td>
				<td><input type="text" name="postalcode" class="amms_tooltip" title="Please enter member's POSTAL CODE" size="120" value="<?php echo esc_attr( $member_data['postalcode'] ); ?>" required /></td>
			</tr>
			<tr>
				<td>Email Address</td>
				<td><input type="text" name="emailaddress" class="amms_tooltip" title="Please enter member's EMAIL ADDRESS (max 255 characters!)" size="120" value="<?php echo esc_attr( $member_data['emailaddress'] ); ?>" required /></td>
			</tr>
			<tr>
				<td>Phone Number</td>
				<td><input type="text" name="phonenumber" class="amms_tooltip" title="Please enter member's PHONE NUMBER (max 255 characters!)" size="120" value="<?php echo esc_attr( $member_data['phonenumber'] ); ?>" required /></td>
			</tr>
			<tr>
				<td>Gender</td>
				<td>
					<select name="gender" class="amms_tooltip" title="Please choose member's GENDER" >
					<?php
						$member_genders = array( 'Male' => 'Male', 'Female' => 'Female' );
						foreach ( $member_genders as $gender_id => $gender ) {
							echo '<option value="' . $gender_id . '" ';
							selected( $member_data['gender'], $gender );
                                                        echo '>' . $gender;
						}
					?>
					</select>
				</td>
			</tr>
			<tr>
				<td>Research Focus</td>
				<td><textarea name="researchfocus" class="amms_tooltip" title="Please enter member's RESEARCH FOCUS" cols="120"><?php echo esc_textarea( $member_data['researchfocus'] ); ?></textarea></td>
			</tr>
			<tr>
				<td>Photo</td>
				<td><input type="text" name="photo" size="80" class="amms_tooltip" title="Enter an image URL of member's PHOTO, or click 'Choose a File' button to upload a new one " value="<?php echo esc_attr( $member_data['photo'] ); ?>" />
                                    <input name="filePhotoToUpload" type="file" accept="image/*" />
                                    <br/>(Enter an image URL of member's PHOTO, or click "Choose a File" button to upload a new one)</td>
			</tr>
			<tr>
				<td>Proof of Payment</td>
				<td><input type="text" name="paymentreceipt" size="80" class="amms_tooltip" title="Enter an image URL of member's Proof of Payment, or click 'Choose a File' button to upload a new one " value="<?php echo esc_attr( $member_data['paymentreceipt'] ); ?>" />
                                    <input name="filePaymentReceiptToUpload" type="file" accept="image/*" />
                                    <br/>(Enter an image URL of member's Proof of Payment, or click "Choose a File" button to upload a new one)</td>
			</tr>
			<tr>
				<td>Member Since</td>
				<td><input type="text" name="membersince" id="ammsmembersince" class="amms_tooltip" title="Please enter a date (MEMBER SINCE)" value="<?php echo esc_attr( $member_data['membersince'] ); ?>"  required />
                                    <br/>(format= YYYY-MM-DD)</td>
                                    <!-- JavaScript function to display calendar button -->
                                    <!-- and associate date selection with field -->
                                    <script type='text/javascript'>
                                        jQuery( document ).ready( function() {
                                            jQuery( '#ammsmembersince' ).datepicker( { 
                                            dateFormat: 'yy-mm-dd', showOn: 'both',
                                            constrainInput: true} );
                                        } );
                                    </script>
			</tr>
			<tr>
				<td>Expiration Date </td>
				<td><input type="text" name="expirationdate" id="ammsexpirationdate" class="amms_tooltip" title="Please enter a date (EXPIRATION DATE)" value="<?php echo esc_attr( $member_data['expirationdate'] ); ?>"  required />
                                    <br/>(format= YYYY-MM-DD)</td>
                                    <!-- JavaScript function to display calendar button -->
                                    <!-- and associate date selection with field -->
                                    <script type='text/javascript'>
                                        jQuery( document ).ready( function() {
                                            jQuery( '#ammsexpirationdate' ).datepicker( { 
                                            dateFormat: 'yy-mm-dd', showOn: 'both',
                                            constrainInput: true} );
                                        } );
                                    </script>
			</tr>
			<tr>
				<td>Membership Status Active? </td>
				<td>
					<select name="active" class="amms_tooltip" title="Please choose member's Membership Status">
					<?php
						$member_statuses = array( 0 => 'false', 1 => 'true' );
						foreach ( $member_statuses as $status_id => $status ) {
							echo '<option value="' . $status_id . '" ';
							selected( $member_data['active'], $status_id );
                                                         echo '>' . $status;
						}
					?>
					</select>
				</td>
			</tr>
		</table>
		<input type="submit" value="Submit" class="button-primary"/>
		</form>

	<?php } ?>
	</div>
<?php }