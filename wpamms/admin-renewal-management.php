<?php

// Register function to be called when admin menu is constructed
add_action( 'admin_menu', 'wpamms_adminrenewal_menu' );

// Add new menu item under SettReneings menu for Member Tracker
function wpamms_adminrenewal_menu() {
	add_menu_page( 'Renewal Management',
		'Renewal Management', 'manage_options',
		'wpamms-renewal',
		'wpamms_renewalmanagement_page' );
}

// Function to render plugin admin page
function wpamms_renewalmanagement_page() {
	global $wpdb;
	?>
	<!-- Top-level menu -->
	<div id="wpamms-general" class="wrap">
	<h2>Renewal Management
		<a class="add-new-h2" 
			href="<?php echo add_query_arg( array ( 'page' => 'wpamms-renewal', 'id' => 'new'), admin_url('admin.php')); ?>">Add New Renewal</a></h2>

	<!-- Display renewal list if no parameter sent in URL -->
	<?php if ( empty( $_GET['id'] ) ) { ?>

            <h3>Manage Renewal Entries</h3>
            <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
            <input type="hidden" name="action" value="delete_amms_renewal" />

            <!-- Adding security through hidden referrer field -->
            <?php wp_nonce_field( 'amms_renewal_deletion' ); ?>

            
            <h4>NEW RENEWAL (Needs To Be Confirmed As Soon As Possible!). After Confirming Renewal, Update Member's Status on Members Management Page!</h4>
            Click on NAME to confirm / modify data <br/>
            <?php
                    $renewal_query = 'select * from ';
                    $renewal_query .= $wpdb->get_blog_prefix();
                    $renewal_query .= "amms_renewal WHERE confirmed = '-1' ORDER BY renewaldate ASC";

                    //$renewals_data = $wpdb->get_results( $wpdb->prepare( $renewal_query ), ARRAY_A );
                    $renewals_data = $wpdb->get_results( $renewal_query , ARRAY_A );
            ?>

            <table class="wp-list-table widefat fixed" >
                <thead><tr>
                    <th style="width: 20px"></th>
                    <th>Name</th>
                    <th>Email Address</th>
                    <th>Payment Receipt</th>
                    <th>Renewal Date</th>
                    <th>Renewal Confirmed?</th>
                </tr></thead>

            <?php 
                    // Display renewals if query returned results
                    if ( $renewals_data ) {
                            foreach ( $renewals_data as $renewal_data ) {
                                    echo '<tr style="background: #FFF">';
                                    echo '<td><input type="checkbox" name="renewals_id[]" value="';
                                        echo esc_attr( $renewal_data['id'] ) . '" /></td>';
                                    echo '<td><a href="' . add_query_arg( array( 'page' => 'wpamms-renewal', 'id' => $renewal_data['id'] ), admin_url( 'admin.php' ) );
                                        echo '">' . $renewal_data['name'] . '</a></td>';
                                    echo '<td>' . $renewal_data['emailaddress'] . ' </td>';
                                      
                                    if( $renewal_data['paymentreceipt'] != '') {
                                        echo '<td><a onclick="ammspopup(';
                                        echo "'". $renewal_data['paymentreceipt']."'";
                                        echo ')">Payment Receipt</a> </td>';
                                    } else
                                        echo '<td></td>';
                                        
                                    echo '<td>' . $renewal_data['renewaldate'] . '</td>';

                                    if($renewal_data['confirmed'] == 1)
                                        echo '<td>true</td></tr>';
                                    else
                                        echo '<td><b>FALSE</b></td></tr>';
                            }
                    } else {
                            echo '<tr style="background: #FFF">';
                            echo '<td colspan=6>No Member Found</td></tr>';
                    }
            ?>
            </table><br />

            <input type="submit" value="Delete Selected" class="button-primary" onclick="return confirm('Are you sure you want to Remove?');"/>


            
            <h4>CONFIRMED Renewal</h4>
            <?php
                    $renewal_query = 'select * from ';
                    $renewal_query .= $wpdb->get_blog_prefix();
                    $renewal_query .= "amms_renewal  WHERE confirmed = '1' ORDER BY renewaldate DESC";

                    //$renewals_data = $wpdb->get_results( $wpdb->prepare( $renewal_query ), ARRAY_A );
                    $renewals_data = $wpdb->get_results( $renewal_query , ARRAY_A );
            ?>

            <table class="wp-list-table widefat fixed" >
                <thead><tr>
                    <th style="width: 20px"></th>
                    <th>Name</th>
                    <th>Email Address</th>
                    <th>Payment Receipt</th>
                    <th>Renewal Date</th>
                    <th>Renewal Confirmed?</th>
                </tr></thead>

            <?php 
                    // Display renewals if query returned results
                    if ( $renewals_data ) {
                            foreach ( $renewals_data as $renewal_data ) {
                                    echo '<tr style="background: #FFF">';
                                    echo '<td><input type="checkbox" name="renewals_id[]" value="';
                                        echo esc_attr( $renewal_data['id'] ) . '" /></td>';
                                    echo '<td><a href="' . add_query_arg( array( 'page' => 'wpamms-renewal', 'id' => $renewal_data['id'] ), admin_url( 'admin.php' ) );
                                        echo '">' . $renewal_data['name'] . '</a></td>';
                                    echo '<td>' . $renewal_data['emailaddress'] . ' </td>';
                                      
                                    if( $renewal_data['paymentreceipt'] != '') {
                                        echo '<td><a onclick="ammspopup(';
                                        echo "'". $renewal_data['paymentreceipt']."'";
                                        echo ')">Payment Receipt</a> </td>';
                                    } else
                                        echo '<td></td>';
                                        
                                    echo '<td>' . $renewal_data['renewaldate'] . '</td>';

                                    if($renewal_data['confirmed'] == 1)
                                        echo '<td>true</td></tr>';
                                    else
                                        echo '<td><b>FALSE</b></td></tr>';
                            }
                    } else {
                            echo '<tr style="background: #FFF">';
                            echo '<td colspan=6>No Member Found</td></tr>';
                    }
            ?>
            </table><br />
            
            
             <h4>FAILED Renewal Payment</h4>
            <?php
                    $renewal_query = 'select * from ';
                    $renewal_query .= $wpdb->get_blog_prefix();
                    $renewal_query .= "amms_renewal WHERE confirmed = '0' ORDER BY renewaldate DESC";

                    //$renewals_data = $wpdb->get_results( $wpdb->prepare( $renewal_query ), ARRAY_A );
                    $renewals_data = $wpdb->get_results( $renewal_query , ARRAY_A );
            ?>

            <table class="wp-list-table widefat fixed" >
                <thead><tr>
                    <th style="width: 20px"></th>
                    <th>Name</th>
                    <th>Email Address</th>
                    <th>Payment Receipt</th>
                    <th>Renewal Date</th>
                    <th>Renewal Confirmed?</th>
                </tr></thead>

            <?php 
                    // Display renewals if query returned results
                    if ( $renewals_data ) {
                            foreach ( $renewals_data as $renewal_data ) {
                                    echo '<tr style="background: #FFF">';
                                    echo '<td><input type="checkbox" name="renewals_id[]" value="';
                                        echo esc_attr( $renewal_data['id'] ) . '" /></td>';
                                    echo '<td><a href="' . add_query_arg( array( 'page' => 'wpamms-renewal', 'id' => $renewal_data['id'] ), admin_url( 'admin.php' ) );
                                        echo '">' . $renewal_data['name'] . '</a></td>';
                                    echo '<td>' . $renewal_data['emailaddress'] . ' </td>';
                                      
                                    if( $renewal_data['paymentreceipt'] != '') {
                                        echo '<td><a onclick="ammspopup(';
                                        echo "'". $renewal_data['paymentreceipt']."'";
                                        echo ')">Payment Receipt</a> </td>';
                                    } else
                                        echo '<td></td>';
                                        
                                    echo '<td>' . $renewal_data['renewaldate'] . '</td>';

                                    if($renewal_data['confirmed'] == 1)
                                        echo '<td>true</td></tr>';
                                    else
                                        echo '<td><b>FALSE</b></td></tr>';
                            }
                    } else {
                            echo '<tr style="background: #FFF">';
                            echo '<td colspan=6>No Member Found</td></tr>';
                    }
            ?>
            </table><br />
        
	
	<input type="submit" value="Delete Selected" class="button-primary" onclick="return confirm('Are you sure you want to Remove?');"/>
	</form>
	
	<?php } elseif ( isset( $_GET['id'] ) && ( $_GET['id'] == "new" || is_numeric( $_GET['id'] ) ) ) {

		// Display renewal creation and editing form if renewal is new
		// or numeric id was sent       
		$renewal_id = $_GET['id'];
		$renewal_data = array();
		$mode = 'new';

		// Query database if numeric id is present
		if ( is_numeric( $renewal_id ) ) {
			$renewal_query = 'select * from ' . $wpdb->get_blog_prefix();
			$renewal_query .= 'amms_renewal where id = ' . $renewal_id;

			//$renewal_data = $wpdb->get_row( $wpdb->prepare( $renewal_query ), ARRAY_A );
			$renewal_data = $wpdb->get_row( $renewal_query , ARRAY_A );

			if ( $renewal_data ) $mode = 'edit';
                } else {
                    $renewal_data['id'] = '';
                    $renewal_data['name'] = '';
                    $renewal_data['emailaddress'] = '';
                    $renewal_data['paymentreceipt'] = '';
                    $renewal_data['renewaldate'] = '';
                    $renewal_data['confirmed'] = '';
                }

		// Display title based on current mode
		if ( $mode == 'new' ) {
			echo '<h3>Add New Renewal</h3>';
		} elseif ( $mode == 'edit' ) {
			echo '<h3>Edit Renewal #' . $renewal_data['id'] . ' - ';
			echo $renewal_data['name'] . '</h3>';
		}
		?>
            
                <h4>After Confirming Renewal, Update Member's Status on Members Management Page!</h4>

		<form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>" enctype="multipart/form-data">
		<input type="hidden" name="action" value="save_wpamms_renewal" />
		<input type="hidden" name="id" value="<?php echo esc_attr( $renewal_id ); ?>" />

		<!-- Adding security through hidden referrer field -->
		<?php wp_nonce_field( 'wpamms_renewal_add_edit' ); ?>

		<!-- Display renewal editing form, with previous values if available -->
		<table>
			<tr>
				<td style="width: 150px">Name</td>
				<td><input type="text" name="name" class="amms_tooltip" title="Please enter renewal's NAME (max 255 characters!)" size="120" value="<?php echo esc_attr( $renewal_data['name'] ); ?>"/></td>
			</tr>
			<tr>
				<td>Email Address</td>
				<td><input type="text" name="emailaddress" class="amms_tooltip" title="Please enter renewal's EMAIL ADDRESS (max 255 characters!)" size="120" value="<?php echo esc_attr( $renewal_data['emailaddress'] ); ?>"/></td>
			</tr>
			<tr>
				<td>Payment Receipt</td>
				<td><input type="text" name="paymentreceipt" size="80" class="amms_tooltip" title="Enter an image URL of renewal's PAYMENT RECEIPT, or click 'Choose a File' button to upload a new one " value="<?php echo esc_attr( $renewal_data['paymentreceipt'] ); ?>" />
                                    <input name="filePaymentReceiptToUpload" type="file" accept="image/*" />
                                    <br/>(Enter an image URL of renewal's PAYMENT RECEIPT, or click "Choose a File" button to upload a new one)</td>
			</tr>
			<tr>
				<td>Renewal Date </td>
				<td><input type="text" name="renewaldate" id="ammsrenewaldate" class="amms_tooltip" title="Please enter a date (RENEWAL DATE)" value="<?php echo esc_attr( $renewal_data['renewaldate'] ); ?>" />
                                    <br/>(format= YYYY-MM-DD)</td>
                                    <!-- JavaScript function to display calendar button -->
                                    <!-- and associate date selection with field -->
                                    <script type='text/javascript'>
                                        jQuery( document ).ready( function() {
                                            jQuery( '#ammsrenewaldate' ).datepicker( { 
                                            dateFormat: 'yy-mm-dd', showOn: 'both',
                                            constrainInput: true} );
                                        } );
                                    </script>
			</tr>
			<tr>
				<td>RENEWAL Status Confirmed? </td>
				<td>
					<select name="confirmed" class="amms_tooltip" title="Please choose renewal's Membership Status">
					<?php
						$renewal_statuses = array( 0 => 'false', 1 => 'true' );
						foreach ( $renewal_statuses as $status_id => $status ) {
							echo '<option value="' . $status_id . '" ';
							selected( $renewal_data['confirmed'], $status_id );
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