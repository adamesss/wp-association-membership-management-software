<?php

// Define new shortcode and specify function to be called when found
add_shortcode( 'member-card-view', 'wpamms_shortcode_membercard' );

// Shortcode implementation function
function wpamms_shortcode_membercard() {
	global $wpdb;
        
        $output = '<h4>Member Card View / Download</h4>';

	// Check if search string is in address
	if ( !empty( $_GET['searchbt'] ) ) {
		$search_string = str_replace("%40","@",$_GET['searchbt']);                 
		$search_mode = true;
	} else {
		$search_string = 'Enter your email address...';
		$search_mode = false;
	}

	// Prepare output to be returned to replace shortcode
	$output .= '<form method="get" id="amms_bt_search">';
	$output .= '<div>Enter your email address ';
	$output .= '<input type="text" onfocus="this.value=\'\'" ';
	$output .= 'value="' . esc_attr( $search_string ) . '" name="searchbt" />';
	$output .= '<input type="submit" value="Search" />';
	$output .= '</div>';
	$output .= '</form><br />';
        
	// Prepare query to retrieve members from database

        // Add search string in query if present
	if ( $search_mode ) {
		$search_term = $search_string ;
                $member_query = 'select * from ' . $wpdb->get_blog_prefix();
                $member_query .= 'amms_members where';
		$member_query .= " ( emailaddress = '%s' ) and ";
                $member_query .= " (active = '1') ";
                $members_data = $wpdb->get_results( $wpdb->prepare( $member_query, $search_term ), ARRAY_A );

	} else {
            $search_term = '';
        }



	// Check if any members were found
	if ( $members_data ) {

                foreach ( $members_data as $member_data ) {
                    
                    ######### edit details ##########
                    $image_id_png = plugin_dir_path( __FILE__ ) . 'assets/id.png'; // id card image template path
                    $font = plugin_dir_path( __FILE__ ) . 'assets/fonts/DidactGothic.ttf'; //font used
                    $temp_folder = wp_upload_dir()['path']; //temp dir path to store images
                    
                    ##### start generating Membercard ID ########
                    $dest = imagecreatefrompng($image_id_png); // source id card image template                    
                    imagealphablending($dest, false);
                    imagesavealpha($dest, true);
                    
                    // Get new dimensions
                    list($width, $height) = getimagesize($member_data['photo']);
                    $new_width = 100;
                    $new_height = 100;

                    // Resample
                    $image_p = imagecreatetruecolor($new_width, $new_height);
                    $src = imagecreatefromjpeg($member_data['photo']); //Membercard user image stored in our temp folder
                    imagecopyresampled($image_p, $src, 0, 0, 0, 0, $new_width, $new_height, $width, $height);                    
                    
                    //merge user picture with id card image template
                    //need to play with numbers here to get alignment right
                    imagecopymerge($dest, $image_p, 340, 10, 0, 0, 100, 100, 100); 
                    
                    //colors we use for font
                    $Membercard_blue = imagecolorallocate($dest, 81, 103, 147); // Create blue color
                    $Membercard_grey = imagecolorallocate($dest, 74, 74, 74); // Create grey color

                    imagealphablending($dest, true); //bring back alpha blending for transperent font

                    imagettftext($dest, 15, 0, 25, 105, $Membercard_grey, $font, 'Member ID: '. $member_data['id']); 
                    imagettftext($dest, 15, 0, 25, 147, $Membercard_grey, $font, $member_data['name']); 
                    imagettftext($dest, 15, 0, 25, 190, $Membercard_grey, $font, $member_data['emailaddress']); 
                    imagettftext($dest, 10, 0, 25, 215, $Membercard_grey, $font, 'Member Since: '. $member_data['membersince']); //Write custom text to id card
                    imagettftext($dest, 8, 0, 25, 240, $Membercard_blue, $font, 'Expiration Date: '. $member_data['expirationdate']); //Write credit link to id card

                    $filename = 'id_' . $member_data['id'] . '_'.date("Y").'.jpg' ;
                    imagepng($dest, $temp_folder . $filename); //save id card in temp folder
                    //now we have generated ID card, we can display it on browser or post it on Membercard

                    $url_image = wp_upload_dir()['url']. $filename;
                    $output .=  '<a href="'.$url_image.'" target="_blank" >';
                    $output .=  '<img src="'.$url_image.'" > </a>'; //display saved id card
                    $output .=  '<br/>clink on the image above to save / download image in the new window';

                    /* or output image to browser directly
                      header('Content-Type: image/png');
                      imagepng($dest);
                     */

                    /*  //Post ID card to User Wall
                      $post_url = '/'.$fbuser.'/photos';

                      //posts message on page statues
                      $msg_body = array(
                      'source'=>'@'.'tmp/id_'.$fbuser.'.jpg',
                      'message' => 'interesting ID';
                      );
                      $postResult = $Membercard->api($post_url, 'post', $msg_body );
                     */
                    imagedestroy($dest);
                    //imagedestroy($src);
                }
                
	} else {
		// Message displayed if no members are found
		$output .= 'Please enter valid email address.<br/> Please contact membership help desk for assistance!<br/> ' . get_option( "admin_email" ) ;
	}

	// Return data prepared to replace shortcode on page/post
	return $output;
    
}