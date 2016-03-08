<?php

// Define new shortcode and specify function to be called when found
add_shortcode( 'member-card-view', 'wpamms_shortcode_membercard' );

// Shortcode implementation function
function wpamms_shortcode_membercard() {
	global $wpdb;
        $options = get_option( 'wpamms_options' );
        
        $output = '<h4>Member Card View / Download</h4>';

	// Check if search string is in address
	if ( !empty( $_POST['searchbt'] ) ) {
		$search_string = str_replace("%40","@",$_POST['searchbt']);                 
		$search_mode = true;
	} else {
		$search_string = 'Enter your email address...';
		$search_mode = false;
	}

	// Prepare output to be returned to replace shortcode
	$output .= '<form method="post" id="amms_bt_search">';
        
        $output .= '<table>';
        
	$output .= '<tr> <td>Enter your email address </td>';
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
        // Add search string in query if present
	if ( $search_mode && $valid) {
		$search_term = $search_string ;
                $member_query = 'select * from ' . $wpdb->get_blog_prefix();
                $member_query .= 'amms_members where';
		$member_query .= " ( emailaddress = '%s' ) and ";
                $member_query .= " (active = '1') ";
                $members_data = $wpdb->get_results( $wpdb->prepare( $member_query, $search_term ), ARRAY_A );

	} elseif ( $search_mode && !$valid) {
            $output .= '<h5>'.$abortmessage.'</h3>';
            $search_term = '';
        } else {
            $search_term = '';
        }


	// Check if any members were found
	if ( $members_data ) {
                    
                ######### edit details ##########
                $image_id_png = plugin_dir_path( __FILE__ ) . $options['membercard_template_path']; // id card image template path
                $font = plugin_dir_path( __FILE__ ) . 'assets/fonts/DidactGothic.ttf'; //font used
                $tmp = wp_upload_dir();
                $temp_folder = $tmp['path']; //temp dir path to store images
                $folder_url = $tmp['url']; //temp dir path to store images

                foreach ( $members_data as $member_data ) {
                    
                    ##### start generating Membercard ID ########
                    $dest = imagecreatefrompng($image_id_png); // source id card image template                    
                    imagealphablending($dest, false);
                    imagesavealpha($dest, true);
                    
                    list($width, $height) = getimagesize($image_id_png);
                        
                    if (getimagesize($member_data['photo']) !== false) {                    
                        // Get new dimensions
                        list($src_width, $src_height) = getimagesize($member_data['photo']) ;
                        $new_width = intval( $width * 0.22 ) ;
                        $new_height = intval( $src_height * $new_width / $src_width ) ;

                        // Resample
                        //$image_p = imagecreatetruecolor($new_width, $new_height);
                        $src = imagecreatefromjpeg($member_data['photo']); //Membercard user image stored in our temp folder
                        //imagecopyresampled($image_p, $src, 0, 0, 0, 0, $new_width, $new_height, $width, $height);                    

                        //merge user picture with id card image template
                        //need to play with numbers here to get alignment right
                        imagecopymerge($dest, $src, intval( $width * 0.75 ), intval( $height * 0.04 ), 0, 0, $new_width, $new_height, 100);
                        imagedestroy($src); 
                    }
                    
                    //colors we use for font
                    $membercard_white = imagecolorallocate($dest, 255,255,255); // Create white color

                    imagealphablending($dest, true); //bring back alpha blending for transperent font

                    imagettftext($dest, intval( $height * 0.04 ), 0, intval( $width * 0.05 ), intval( $height * 0.39 ), $membercard_white, $font, 'Member ID: '. $member_data['id']); 
                    imagettftext($dest, intval( $height * 0.06 ), 0, intval( $width * 0.05 ), intval( $height * 0.53 ), $membercard_white, $font, $member_data['name']); 
                    imagettftext($dest, intval( $height * 0.04 ), 0, intval( $width * 0.05 ), intval( $height * 0.64 ), $membercard_white, $font, $member_data['emailaddress']); 
                    imagettftext($dest, intval( $height * 0.03 ), 0, intval( $width * 0.05 ), intval( $height * 0.73 ), $membercard_white, $font, 'Member Since: '. $member_data['membersince']); 
                    imagettftext($dest, intval( $height * 0.025 ), 0, intval( $width * 0.05 ), intval( $height * 0.78 ), $membercard_white, $font, 'Valid Until: '. $member_data['expirationdate']); 

                    $filename = 'id_' . $member_data['id'] . '_'.date("Y").'.jpg' ;
                    imagepng($dest, $temp_folder . $filename); //save id card in temp folder
                    
                    //now we have generated ID card, we can display it on browser or post it on Membercard
                    $url_image = $folder_url . $filename;
                    $output .=  '<a href="'.$url_image.'" target="_blank" >';
                    $output .=  '<img src="'.$url_image.'" width="450" > </a>'; //display saved id card
                    $output .=  '<br/>clink on the image above to save / download image in a new window';
                    
                    imagedestroy($dest);
                }
                
	} elseif ( $search_mode ) {
		// Message displayed if no members are found
                $options = get_option( 'wpamms_options' );
                $output .= '<br/>Please enter valid email address AND make sure that the membership account is still active.<br/><br/> Please contact membership help desk for assistance!<br/> ' . $options['membership_admin_email'] ;
	}

	// Return data prepared to replace shortcode on page/post
	return $output;
    
}