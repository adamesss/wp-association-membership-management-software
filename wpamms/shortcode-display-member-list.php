<?php

// Define new shortcode and specify function to be called when found
add_shortcode( 'member-tracker-list', 'wpamms_shortcode_list' );

// Shortcode implementation function
function wpamms_shortcode_list() {
	global $wpdb;
        $output = '<h3>Member List</h3>';

	// Check if search string is in address
	if ( !empty( $_POST['searchbt'] ) ) {
		$search_string = $_POST['searchbt'];
		$search_mode = true;
	} else {
		$search_string = 'Search...';
		$search_mode = false;
	}

	// Prepare output to be returned to replace shortcode
	$output .= '<form method="post" id="amms_bt_search">';
        
        $output .= '<table>';
        
	$output .= '<tr> <td>Search Member: </td>';
	$output .= '<td><input type="text" onfocus="this.value=\'\'" ';
	$output .= 'value="' . esc_attr( $search_string ) . '" name="searchbt" required /></td>';
	$output .= '</tr>';
        
        $output .= '<tr>';
            $output .= '<td>Re-type the following text<br />';
                $output .= '<img src="' . plugins_url("EasyCaptcha/easycaptcha.php", __FILE__ ) . '" />';
            $output .= '</td>';
            $output .= '<td><input type="text" name="ammp_renewal_captcha" required /></td>';
        $output .= '</tr>';

        $output .= '</table>';
	$output .= '<input type="submit" value="Search" />';
	$output .= '</form><br />';
        
        
        $valid = false;
        if( isset( $_POST['ammp_renewal_captcha'] ) ) {

            // Variable used to determine if submission is valid
            // Check if captcha text was entered
                    if (empty($_POST['ammp_renewal_captcha'])) {
                        $abortmessage = 'Captcha code is missing. Try again and ';
                        $abortmessage .= 'provide the code.';
                        //wp_die($abortmessage);
                        //exit;
                    } else {
            // Check if captcha cookie is set
                        if (isset($_COOKIE['Captcha'])) {
                            list( $hash, $time ) = explode('.', $_COOKIE['Captcha']);
            // The code under the md5's first section needs to match
            // the code entered in easycaptcha.php
                            if (md5('SDUVOIAUBDVOBODFBY' .
                                            $_REQUEST['ammp_renewal_captcha'] .
                                            $_SERVER['REMOTE_ADDR'] . $time) != $hash) {
                                $abortmessage = ' Captcha code is wrong. ';
                                $abortmessage .= 'try to get it right or reload ';
                                $abortmessage .= 'to get a new captcha code.';
                                //wp_die($abortmessage);
                                //exit;
                            } elseif (( time() - 5 * 60) > $time) {
                                $abortmessage = 'Captcha timed out. Please try again ';
                                $abortmessage .= '(reload the page and submit again)';
                                //wp_die($abortmessage);
                                //exit;
                            } else {
            // Set flag to accept and store user input
                                $valid = true;
                            }
                        } else {
                            $abortmessage = 'No captcha cookie given. Make sure ';
                            $abortmessage .= 'cookies are enabled.';
                            //wp_die($abortmessage);
                            //exit;
                        }
                    }
        
        }

        
	// Prepare query to retrieve members from database
	$member_query = 'select * from ' . $wpdb->get_blog_prefix();
	$member_query .= 'amms_members where';

        // Add search string in query if present
	if ( $search_mode && $valid) {
		$search_term = '%' . $search_string . '%';
		$member_query .= " (name like '%s' ";
		$member_query .= "or memberid like '%s' ";
		$member_query .= "or institution like '%s' ";
		$member_query .= "or department like '%s' ";
		$member_query .= "or city like '%s' ";
		$member_query .= "or province like '%s' ";
		$member_query .= "or researchfocus like '%s' ) and ";
	} elseif ( $search_mode && !$valid) {
            $output .= '<h5>'.$abortmessage.'</h3>';
            $search_term = '';
        } else {
            $search_term = '';
        }

	$member_query .= " (active = '1') ORDER BY DATEDIFF(`expirationdate`, CURDATE() ) ASC";

	$members_data = $wpdb->get_results( $wpdb->prepare( $member_query, $search_term, $search_term, $search_term, $search_term, $search_term, $search_term, $search_term ), ARRAY_A );

        
        $output .= '<h4>ACTIVE Member</h4>';
	$output .= '<table>';
        
	// Check if any members were found
	if ( $members_data ) {
                
                $output .= '<thead><tr>';
                    $output .= '<th>Name</th>';
                    $output .= '<th>Institution | Department | City | Province | Research Focus</th>';
                    $output .= '<th>Status</th>';
                $output .= '</tr></thead>';

		// Create row in table for each member
                foreach ( $members_data as $member_data ) {
                         $output .= '<tr style="background: #FFF">';
                         $output .= '<td><b>' . $member_data['name'] . '</b><br/><br/>';

                        if( $member_data['photo'] != '') {
                            $output .=  '<a onclick="ammspopup(';
                            $output .=  "'". $member_data['photo']."'";
                            $output .=  ')">Photo</a> <br/><br/>';
                        }  
                            
                        $output .= 'Member ID: ' . $member_data['memberid'] . '<br/>';
                        $output .= 'Member Since: ' . $member_data['membersince'] . '</td>';
                        
                         $output .= '<td>' . $member_data['institution'] . ' , ' 
                                    . $member_data['department'] . ' <br/><br/>' 
                                    . $member_data['city'] . ' , ' 
                                    . $member_data['province'] . ' <br/><br/>' 
                                    . $member_data['researchfocus'] . ' </td>';

                        if($member_data['active'] == 1)
                             $output .= '<td>Active Member<br/><br/>';
                        else
                             $output .= '<td><b>EXPIRED</b><br/><br/>';

                         $output .= 'Expiration Date: ' . $member_data['expirationdate'] . '</td></tr>';
                }
                
	} else {
		// Message displayed if no members are found
		$output .= '<tr style="background: #FFF">';
		$output .= '<td colspan=3>No Members to Display</td></tr>';
	}

	$output .= '</table><br /><br /><br />';
        
        
	// Prepare query to retrieve members from database
	$member_query = 'select * from ' . $wpdb->get_blog_prefix();
	$member_query .= 'amms_members where';

        // Add search string in query if present
	if ( $search_mode && $valid) {
		$search_term = '%' . $search_string . '%';
		$member_query .= " (name like '%s' ";
		$member_query .= "or memberid like '%s' ";
		$member_query .= "or institution like '%s' ";
		$member_query .= "or department like '%s' ";
		$member_query .= "or city like '%s' ";
		$member_query .= "or province like '%s' ";
		$member_query .= "or researchfocus like '%s' ) and ";
	} elseif ( $search_mode && !$valid) {
            $output .= '<h5>'.$abortmessage.'</h3>';
            $search_term = '';
        } else {
            $search_term = '';
        }

	$member_query .= " (active = '0') ORDER BY DATEDIFF(`expirationdate`, CURDATE() ) DESC";

	$members_data = $wpdb->get_results( $wpdb->prepare( $member_query, $search_term, $search_term, $search_term, $search_term, $search_term, $search_term, $search_term ), ARRAY_A );


        $output .= '<h4>EXPIRED Member</h4>';
	$output .= '<table>';
        
	// Check if any members were found
	if ( $members_data ) {
                
                $output .= '<thead><tr>';
                    $output .= '<th>Name</th>';
                    $output .= '<th>Institution | Department | City | Province | Research Focus</th>';
                    $output .= '<th>Status</th>';
                $output .= '</tr></thead>';

		// Create row in table for each member
                foreach ( $members_data as $member_data ) {
                         $output .= '<tr style="background: #FFF">';
                         $output .= '<td><b>' . $member_data['name'] . '</b><br/><br/>';

                        if( $member_data['photo'] != '') {
                            $output .=  '<a onclick="ammspopup(';
                            $output .=  "'". $member_data['photo']."'";
                            $output .=  ')">Photo</a> <br/><br/>';
                        }  
                        $output .= 'Member ID: ' . $member_data['memberid'] . '<br/>';
                         $output .= 'Member Since: ' . $member_data['membersince'] . '</td>';
                        
                         $output .= '<td>' . $member_data['institution'] . ' , ' 
                                    . $member_data['department'] . ' <br/><br/>' 
                                    . $member_data['city'] . ' , ' 
                                    . $member_data['province'] . ' <br/><br/>' 
                                    . $member_data['researchfocus'] . ' </td>';

                        if($member_data['active'] == 1)
                             $output .= '<td>Active Member<br/><br/>';
                        else
                             $output .= '<td><b>EXPIRED</b><br/><br/>';

                         $output .= 'Expiration Date: ' . $member_data['expirationdate'] . '</td></tr>';
                }
                
	} else {
		// Message displayed if no members are found
		$output .= '<tr style="background: #FFF">';
		$output .= '<td colspan=3>No Members to Display</td></tr>';
	}

	$output .= '</table><br /><br /><br />';

	// Return data prepared to replace shortcode on page/post
	return $output;
}