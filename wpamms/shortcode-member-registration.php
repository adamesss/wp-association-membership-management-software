<?php

// Define new shortcode and specify function to be called when found
add_shortcode( 'member-registration-form', 'wpamms_shortcode_registration' );

// Shortcode implementation function
function wpamms_shortcode_registration() {

	// Prepare output to be returned to replace shortcode
	$output = '<h2>Form Membership Registration</h2>';

        $valid = false;
        if( isset( $_POST['ammp_registration_captcha'] ) ) {

            // Variable used to determine if submission is valid
            // Check if captcha text was entered
                    if (empty($_POST['ammp_registration_captcha'])) {
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
                                            $_REQUEST['ammp_registration_captcha'] .
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
        
        
	// Check if registration form has been submitted
	if (    $valid && isset( $_POST['ammsname'] )  && isset( $_POST['institution'] )  && isset( $_POST['department'] )  && isset( $_POST['address'] )  && isset( $_POST['city'] )  && isset( $_POST['province'] )  && isset( $_POST['postalcode'] )  && isset( $_POST['emailaddress'] )  && isset( $_POST['phonenumber'] )  && isset( $_POST['gender'] )  && isset( $_POST['researchfocus'] )  && isset( $_POST['photo'] )  && isset( $_POST['paymentreceipt'] )   ) {
            
                check_admin_referer( 'wpamms_member_registration' );

                    // Place all user submitted values in an array
                    $member_data = array();         
                    $member_data['id'] = '';
                    $member_data['name'] = $_POST['ammsname'] ;
                    $member_data['institution'] = $_POST['institution'] ;
                    $member_data['department'] = $_POST['department'] ;
                    $member_data['address'] = $_POST['address'] ;
                    $member_data['city'] = $_POST['city'];
                    $member_data['province'] = $_POST['province'] ;
                    $member_data['postalcode'] = $_POST['postalcode'] ;
                    $member_data['emailaddress'] = $_POST['emailaddress'] ;
                    $member_data['phonenumber'] = $_POST['phonenumber'] ;
                    $member_data['gender'] = $_POST['gender'] ;
                    $member_data['researchfocus'] = $_POST['researchfocus'] ;
                    $member_data['photo'] = $_POST['photo'] ;
                    $member_data['paymentreceipt'] = $_POST['paymentreceipt'] ;
                    $member_data['membersince'] = date("Y-m-d") ;
                    $member_data['expirationdate'] = '' ;
                    $member_data['active'] = '-1' ;

                    if ( ! function_exists( 'wp_handle_upload' ) ) {
                        require_once( ABSPATH . 'wp-admin/includes/file.php' );
                    }

                    if( file_exists($_FILES['filePaymentReceiptToUpload']['tmp_name']) && is_uploaded_file($_FILES['filePaymentReceiptToUpload']['tmp_name'])) {

                        $uploadedfile = $_FILES['filePaymentReceiptToUpload'];
                        $uploadedfile['name'] = date("Y-m-d-H-i-s") . '_' . $uploadedfile['name'];
                        $upload_overrides = array( 'test_form' => false );
                        $movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
                        if ( $movefile && !isset( $movefile['error'] ) ) {
                            //echo "File is valid, and was successfully uploaded.\n";
                            //var_dump( $movefile);
                            $member_data['paymentreceipt'] = $movefile['url'];
                        } else {
                            wp_die('Upload PAYMENT RECEIPT Failed! if error persist, contact membership help desk for assistance! ' . get_option( "admin_email" ) );
                        }

                    }

                    if( file_exists($_FILES['filePhotoToUpload']['tmp_name']) && is_uploaded_file($_FILES['filePhotoToUpload']['tmp_name'])) {

                        $uploadedfile = $_FILES['filePhotoToUpload'];
                        $uploadedfile['name'] = date("Y-m-d-H-i-s") . '_' . $uploadedfile['name'];
                        $upload_overrides = array( 'test_form' => false );
                        $movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
                        if ( $movefile && !isset( $movefile['error'] ) ) {
                            //echo "File is valid, and was successfully uploaded.\n";
                            //var_dump( $movefile);
                            $member_data['photo'] = $movefile['url'];
                        } else {
                            wp_die('Upload PHOTO Failed! if error persist, contact membership help desk for assistance! ' . get_option( "admin_email" ) );
                        }

                    }

                    // Get access to global database access class
                    global $wpdb;
                    // Call the wpdb insert method
                    $wpdb->insert($wpdb->get_blog_prefix() . 'amms_members', $member_data );

                    if($wpdb->insert_id != false && $wpdb->insert_id >= 1) {
                        $output .= '<h3>SUCCESS.... The Registration Form Had Been Submitted.</h3>';
                        $output .= '<h4>Please Wait 2 x 24 hours (work days) for Admin Verification Process</h4>';
                        $output .= 'In Case Any Problem Occurs, Please Contact Membership Help Desk at = '. get_option( "admin_email" );

                        $multiple_recipients = array(
                            get_option( 'admin_email' ),
                            $member_data['emailaddress']
                        );
                        $subj = 'New Membership Registration ('.$member_data['name'].') at '.$member_data['membersince'].' Needs Admin Verification Process';
                        $body = 'Membership Registration Form ';
                        $body .= 'name = ' . $member_data['name'] . ' "\n" ';
                        $body .= 'institution = ' . $member_data['institution']  . ' "\n" ';
                        $body .= 'department = ' . $member_data['department']  . ' "\n" ';
                        $body .= 'address = ' . $member_data['address']  . ' "\n" ';
                        $body .= 'city = ' . $member_data['city']   . ' "\n" ';
                        $body .= 'province = ' . $member_data['province']   . ' "\n" ';
                        $body .= 'postalcode = ' . $member_data['postalcode']   . ' "\n" ';
                        $body .= 'emailaddress = ' . $member_data['emailaddress']   . ' "\n" ';
                        $body .= 'phonenumber = ' . $member_data['phonenumber']   . ' "\n" ';
                        $body .= 'gender = ' . $member_data['gender']   . ' "\n" ';
                        $body .= 'researchfocus = ' . $member_data['researchfocus']   . ' "\n" ';
                        $body .= 'photo = ' . $member_data['photo']   . ' "\n" ';
                        $body .= 'paymentreceipt = ' . $member_data['paymentreceipt']   . ' "\n" ';
                        $body .= 'registration date = ' . $member_data['membersince']   . ' "\n\n\n" ';
                        $body .= 'In Case Any Problem Occurs, Please Contact Membership Help Desk at = '. get_option( "admin_email" );
                        $headers = 'From: '.get_site_url(). ' Membership Administrator <'. get_option( "admin_email" ) . '>' . "\r\n";
                        wp_mail( $multiple_recipients, $subj, $body, $headers );

                    } else {
                        $output .= '<h3>FAILED.... Something Wrong!</h3>';
                        $output .= '<h4>Please Try Again! Fill All Required Colunm! <br/>if error persist, contact membership help desk for assistance! ' . get_option( "admin_email" ) . '</h4>';
                    } 
            
	} else {
        
            if( ($valid == false) && isset( $_POST['ammsname'] ) ) {
                $output .= '<h4>' . $abortmessage . ' <br/>if error persist, contact membership help desk for assistance! ' . get_option( "admin_email" ) . '</h4>';
            }
            
            // Include form from html file
            $output .= '<form method="post" enctype="multipart/form-data">';

            $output .= wp_nonce_field( 'wpamms_member_registration' ); 
            
            $output .= '<table>';
            $output .= file_get_contents('registration-form.html', true);
            
            $output .= '<tr>';
                $output .= '<td>Re-type the following text<br />';
                    $output .= '<img src="' . plugins_url("EasyCaptcha/easycaptcha.php", __FILE__ ) . '" />';
                $output .= '</td>';
                $output .= '<td><input type="text" name="ammp_registration_captcha" required/></td>';
            $output .= '</tr>';

            $output .= '</table>';
            $output .= '<input type="submit" value="Submit" class="button-primary"/>';
            $output .= '</form>';
        }
        
	// Return data prepared to replace shortcode on page/post
	return $output;
}