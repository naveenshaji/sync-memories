<?php
	
	if ( !defined('K_COUCH_DIR') ) die(); // cannot be loaded directly
    
    ///////////EDIT BELOW THIS////////////////////////////////////////
    
    // Names of the required templates
    $t['members_tpl'] = 'members/index.php';
    $t['login_tpl'] = 'members/login.php';
    $t['lost_password_tpl'] = 'members/login.php';
    $t['registration_tpl'] = 'members/login.php';
    
    // Email address used for sending the password-recovery and account-activation emails
    $t['email_from'] = 'no-reply@easynot.es';
       
    // Text strings used for error messages and emails
    // login
    $t['prompt_username'] = 'Please enter your email';
    $t['prompt_password'] = 'Please enter your password';
    $t['invalid_credentials'] = 'Invalid email or password';
    $t['account_locked'] = 'Account locked';
    $t['account_disabled'] = 'Account disabled';

    // forgot_password
    $t['submit_error'] = 'Please enter your email address';
    $t['no_such_user'] = 'No such user exists';
    $t['reset_password_email_subject'] = 'Password reset requested';
    $t['reset_password_email_msg_0'] = 'A request was received to reset your password for the following site and username';
    $t['user_name'] = 'Username';
    $t['reset_password_email_msg_1'] = 'To confirm that the request was made by you please visit the following address, otherwise just ignore this email.';
    $t['email_failed'] = 'E-Mail could not be sent.';

    // reset_password
    $t['invalid_key'] = 'Invalid key';
    $t['new_password_email_subject'] = 'Your new password';
    $t['new_password_email_msg_0'] = 'Your password has been reset for the following site and username';
    $t['new_password'] = 'New Password';
    $t['new_password_email_msg_1'] = 'Once logged in you can change your password.';

    // registration
    $t['activation_email_subject'] = 'New Account Confirmation';
    $t['activation_email_msg_0'] = 'Please click the following link to activate your account:';
    $t['activation_email_msg_1'] = 'Thanks,';
    $t['activation_email_msg_2'] = 'easyNOTES';