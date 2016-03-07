<?php

// Define new shortcode and specify function to be called when found
add_shortcode( 'member-tracker-list', 'wpamms_shortcode_list' );

// Shortcode implementation function
function wpamms_shortcode_list() {
	global $wpdb;

	// Check if search string is in address
	if ( !empty( $_GET['searchbt'] ) ) {
		$search_string = $_GET['searchbt'];
		$search_mode = true;
	} else {
		$search_string = 'Search...';
		$search_mode = false;
	}

	// Prepare output to be returned to replace shortcode
	$output = '';

	$output .= '<form method="get" id="amms_bt_search">';
	$output .= '<div>Search members ';
	$output .= '<input type="text" onfocus="this.value=\'\'" ';
	$output .= 'value="' . esc_attr( $search_string ) . '" name="searchbt" />';
	$output .= '<input type="submit" value="Search" />';
	$output .= '</div>';
	$output .= '</form><br />';

        
        $output .= '<h4>ACTIVE Member</h4>';
	$output .= '<table>';
        
	// Prepare query to retrieve members from database
	$member_query = 'select * from ' . $wpdb->get_blog_prefix();
	$member_query .= 'amms_members where';

        // Add search string in query if present
	if ( $search_mode ) {
		$search_term = '%' . $search_string . '%';
		$member_query .= " (name like '%s' ";
		$member_query .= "or institution like '%s' ";
		$member_query .= "or department like '%s' ";
		$member_query .= "or city like '%s' ";
		$member_query .= "or province like '%s' ";
		$member_query .= "or researchfocus like '%s' ) and ";
	} else {
            $search_term = '';
        }

	$member_query .= " (active = '1') ORDER BY DATEDIFF(`expirationdate`, CURDATE() ) ASC";

	$members_data = $wpdb->get_results( $wpdb->prepare( $member_query, $search_term, $search_term, $search_term, $search_term, $search_term, $search_term ), ARRAY_A );

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
		$output .= '<td colspan=3>No Members to Display</td>';
	}

	$output .= '</table><br /><br /><br />';

        
        $output .= '<h4>EXPIRED Member</h4>';
	$output .= '<table>';
        
	// Prepare query to retrieve members from database
	$member_query = 'select * from ' . $wpdb->get_blog_prefix();
	$member_query .= 'amms_members where';

        // Add search string in query if present
	if ( $search_mode ) {
		$search_term = '%' . $search_string . '%';
		$member_query .= " (name like '%s' ";
		$member_query .= "or institution like '%s' ";
		$member_query .= "or department like '%s' ";
		$member_query .= "or city like '%s' ";
		$member_query .= "or province like '%s' ";
		$member_query .= "or researchfocus like '%s' ) and ";
	} else {
            $search_term = '';
        }

	$member_query .= " (active = '0') ORDER BY DATEDIFF(`expirationdate`, CURDATE() ) DESC";

	$members_data = $wpdb->get_results( $wpdb->prepare( $member_query, $search_term, $search_term, $search_term, $search_term, $search_term, $search_term ), ARRAY_A );

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
		$output .= '<td colspan=3>No Members to Display</td>';
	}

	$output .= '</table><br /><br /><br />';

	// Return data prepared to replace shortcode on page/post
	return $output;
}