<?php

    if ( !defined('K_COUCH_DIR') ) die(); // cannot be loaded directly
    
    class KMember{
        var $hasher;
        var $cookie_name;
        var $cookie_path;
        var $cookie_domain;
        var $secret_key;
        
        var $t = array();
        var $members_tpl;
        var $login_tpl;
        var $lost_password_tpl;
        var $registration_tpl;
        var $field_ids = null;
        var $tpl_id;
        
        function KMember(){
            global $FUNCS;
                
            $this->hasher = new PasswordHash(8, TRUE);
            
            $cookie_path = '/';
            if( ($pos = strpos(K_SITE_URL, $_SERVER['HTTP_HOST'])) !==false ){
                $given_site_dir = substr( K_SITE_URL, $pos + strlen($_SERVER['HTTP_HOST']) );
                if( substr(K_SITE_DIR, -(strlen($given_site_dir))) == $given_site_dir ){
                    $cookie_path = $given_site_dir;
                }
            }
            $this->cookie_name = 'couchcms_'. md5( K_SITE_URL . '_KMember' );
            $this->cookie_path = $cookie_path;
            $this->cookie_domain = $_SERVER['HTTP_HOST'];
            //$this->cookie_domain = preg_replace( '|^www\.(.*)$|', '.\\1', $_SERVER['HTTP_HOST'] ); //for all sub-domains too.
            
            // config
            $this->populate_config();
            
            // views
            $this->register_views();
            
        }
        
        function populate_config(){
        
            $t = array();
            if( file_exists(K_COUCH_DIR.'addons/member/config.php') ){
                require_once( K_COUCH_DIR.'addons/member/config.php' );
            }
            else{
                die( 
                      "<h3>Members module: 'config.php' not found. <br/>
                      Perhaps you forgot to rename the 'config.example.php' file in 'couch/addons/member' to 'config.php'?
                      </h3>" 
                   );
            }
            $this->t = array_map( "trim", $t );
            unset( $t );

            $this->members_tpl = ( $this->t['members_tpl'] ) ? $this->t['members_tpl'] : 'members.php';
            $this->login_tpl = ( $this->t['login_tpl'] ) ? $this->t['login_tpl'] : 'login.php';
            $this->lost_password_tpl = ( $this->t['lost_password_tpl'] ) ? $this->t['lost_password_tpl'] : 'lost-password.php';
            $this->registration_tpl = ( $this->t['registration_tpl'] ) ? $this->t['registration_tpl'] : 'register.php';
        }
        
        function register_views(){
            global $FUNCS;
            
            if( !defined('K_MEMBER_EMBED_PATH') ){
                
                if( defined('K_SNIPPETS_DIR') ){
                    // custom snippets folder -
                    // defined relative to the site (also should not contain any /./,/../ path stuff).
                    // We need to calculate relative path from the snippets folder to this module
                    $path = str_replace( '\\', '/', K_SNIPPETS_DIR );
                    $segments = explode( '/', $path );
                    $path = '';
                    foreach( $segments as $segment ){
                        if( $segment ) $path .= '../';
                    }
                    $path .= basename( K_COUCH_DIR ) . '/addons/member/views/';
                    
                }
                else{
                    $path = '../addons/member/views/';
                }
                define( 'K_MEMBER_EMBED_PATH', $path );
            }
            
            $FUNCS->register_admin_listview( $this->members_tpl, K_MEMBER_EMBED_PATH.'admin_list_view.html' );
            //$FUNCS->register_admin_pageview( $this->members_tpl, K_MEMBER_EMBED_PATH.'admin_page_view.html', 0 /* no advanced_settings */ );
            
        }
        
        // Checks for the cookie set for logged-in member (also sets a few useful variables)
        function check_login(){
            global $DB, $FUNCS, $CTX;
            
            $CTX->set( 'k_member_logged_in', '0', 'global' );
            $CTX->set( 'k_member_logged_out', '1', 'global' );
            $CTX->set( 'k_member_template', $this->members_tpl, 'global' );
            $CTX->set( 'k_member_login_template', $this->login_tpl, 'global' );
            $CTX->set( 'k_member_lost_password_template', $this->lost_password_tpl, 'global' );
            $CTX->set( 'k_member_registration_template', $this->registration_tpl, 'global' );
            
            if( $_COOKIE[$this->cookie_name] ){
                $cookie = $FUNCS->cleanXSS( $_COOKIE[$this->cookie_name] );
                list( $userid, $expiry, $hash ) = explode( ':', $cookie );
                if( time() < $expiry ){
                    if( $cookie == $this->create_cookie($userid, $expiry) ){// if cookies match
                        
                        // get user 
                        $user = $this->get_user( $userid, 1 );
                        if( is_object($user) && $user->member_active ){
                            $CTX->set( 'k_member_logged_in', '1', 'global' );
                            $CTX->set( 'k_member_logged_out', '0', 'global' );
                            $this->set_context( $user );
                            return;
                        }
                    }
                }
                
                // delete invalid cookie
                $this->delete_cookie();
            }
            
        }
        
        // Processes the login form
        function process_login( $only_email ){
            global $DB, $FUNCS, $CTX;
            
            $now = time();
            
            $username = $FUNCS->cleanXSS( trim($CTX->get('frm_member_name')) ); 
            $pwd = $FUNCS->cleanXSS( trim($CTX->get('frm_member_password')) );
            $remember = trim( $CTX->get('frm_member_remember') ) ? 1: 0;
            
            if( empty($username) || ($only_email && (!preg_match("/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,6}$/i", $username))) ){
                return $FUNCS->raise_error( $this->t['prompt_username'] );
            }
            if( empty($pwd) ){
                return $FUNCS->raise_error( $this->t['prompt_password'] );
            }
            
            // get user
            $user = $this->get_user( $username );
            if( is_null($user) ){
                return $FUNCS->raise_error( $this->t['invalid_credentials'] );
            }
          
            // ensure no more than 3 failed login attempts within 30 seconds
            if( ($user->member_failed_logins >= 3) && ($user->member_last_failed_login_time > ($now - 30)) ){ 
                return $FUNCS->raise_error( $this->t['account_locked'] );
            }
            
            // ensure account has been activated
            if( !$user->member_active ){
                return $FUNCS->raise_error( $this->t['account_disabled'] );
            }
            
            // finally check password match
            $check = $this->hasher->CheckPassword( $pwd, $user->member_pwd_hash );
            if( !$check ){
                // increment failed login counter 
                $this->increment_login_counter( $user->member_id, $now );
                return $FUNCS->raise_error( $this->t['invalid_credentials'] );
            }
            
            // All OK .. member can login.
            // reset failed login counter for this member
            if( $user->member_failed_logins ){
                $this->reset_login_counter( $user->member_id );
            }
            
            // set an access cookie for future visits of this user
            $this->set_cookie( (string)$user->member_id, $remember );
            
            // return success
            return 1;
        }
        
        function process_forgot_password( $send_mail ){
            global $DB, $FUNCS, $CTX;
            
            $email = $FUNCS->cleanXSS( trim($CTX->get('frm_member_email')) );
            if( !strlen($email) || !preg_match("/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,6}$/i", $email) ){
                return KFuncs::raise_error( $this->t['submit_error'] );
            } 
            
            // get user
            $user = $this->get_user( $email );
            if( is_null($user) ){
                return $FUNCS->raise_error( $this->t['no_such_user'] );
            }
            
            $key = $user->member_pwd_reset_hash;
            if( empty($key) ){
                $key = $FUNCS->generate_key( 32 );
                $field_ids = $this->get_ids();
                $sql = "UPDATE ".K_TBL_DATA_TEXT." SET value = '".$DB->sanitize($key)."' WHERE page_id='".$DB->sanitize($user->member_id)."' AND field_id='".$DB->sanitize($field_ids['member_pwd_reset_hash'])."'";
                $DB->_query( $sql );
            }
            
            // set info in context for other tags to use (specifically cms:send_mail)
            $this->set_context( $user );
            
            // generate the reset password link
            $link = $this->get_link( $this->lost_password_tpl ) . "?act=resetpwd&id=" . $user->member_id . "&key=" . $key;   
            $CTX->set( 'k_member_reset_pwd_link', $link, 'global' );
            
            $subject = $this->t['reset_password_email_subject'];
            $CTX->set( 'k_member_reset_pwd_email_subject', $subject, 'global' );
            
            $msg = $this->t['reset_password_email_msg_0'] . ": \r\n";
            $msg .= K_SITE_URL . "\r\n";
            $msg .= $this->t['user_name'] .': ' . $user->member_email . "\r\n\r\n"; 
            $msg .= $this->t['reset_password_email_msg_1'] . "\r\n";
            $msg .= $link ."\r\n";
            $CTX->set( 'k_member_reset_pwd_email_text', $msg, 'global' );
            
            if( $send_mail ){
                $rs = $this->send_mail( $user->member_email, $subject, $msg );
                if( !$rs ){
                    return KFuncs::raise_error( $this->t['email_failed'] );
                }
            }
            
            return;
            
        }
        
        function process_reset_password( $send_mail ){
            global $DB, $FUNCS, $CTX;
            
            //?act=resetpwd&id=276&key=eeQ8mRyspLShBNKcEgEkM3yeHtV0DoJg
            $id = $FUNCS->cleanXSS( $_GET['id'] );
            $key = $FUNCS->cleanXSS( $_GET['key'] );
            
            if( !$id || !$FUNCS->is_non_zero_natural($id) ){
                return $FUNCS->raise_error( $this->t['invalid_key'] );
            }
            if( !$key || !$FUNCS->is_alphanumeric($key) ){
                return $FUNCS->raise_error( $this->t['invalid_key'] );
            }
            
            // get user 
            $user = $this->get_user( $id, 1 );
            if( is_object($user) && $user->member_pwd_reset_hash==$key ){
                
                // generate a new password for the user
                $password = $FUNCS->generate_key( 12 );
                $hash = $this->hasher->HashPassword( $password );
                
                // update record
                $this->reset_password( $user->member_id, $hash );
                
                // set info in context for other tags to use (specifically cms:send_mail)
                $this->set_context( $user );
                $CTX->set( 'k_member_new_pwd', $password, 'global' );
                
                $subject = $this->t['new_password_email_subject'];
                $CTX->set( 'k_member_new_pwd_email_subject', $subject, 'global' );
                
                $msg = $this->t['new_password_email_msg_0'] . ": \r\n";
                $msg .= K_SITE_URL . "\r\n";
                $msg .= $this->t['user_name'] .': ' . $user->member_email . "\r\n\r\n";
                $msg .= $this->t['new_password'] .': ' . $password . "\r\n\r\n";
                $msg .= $this->t['new_password_email_msg_1'] . "\r\n";
                $CTX->set( 'k_member_new_pwd_email_text', $msg, 'global' );
                
                if( $send_mail ){
                    $rs = $this->send_mail( $user->member_email, $subject, $msg );
                    if( !$rs ){
                        return KFuncs::raise_error( $this->t['email_failed'] );
                    }
                }
                
                return;
                
            }
            else{
                return $FUNCS->raise_error( $this->t['invalid_key'] );
            }
            
        }
        
        function process_activation(){
            global $DB, $FUNCS, $CTX;
            
            //act=activate&id=313&key=jromiFOwSfcc9pooHRIxEP2Cz1H7yGxY
            $id = $FUNCS->cleanXSS( $_GET['id'] );
            $key = $FUNCS->cleanXSS( $_GET['key'] );
            
            if( !$id || !$FUNCS->is_non_zero_natural($id) ){
                return $FUNCS->raise_error( $this->t['invalid_key'] );
            }
            if( !$key || !$FUNCS->is_alphanumeric($key) ){
                return $FUNCS->raise_error( $this->t['invalid_key'] );
            }
            
            // get user 
            $user = $this->get_user( $id, 1 );
            if( is_object($user) && $user->member_activation_hash==$key ){
                $this->activate_account( $user->member_id );
                $this->set_context( $user );
            }
            else{
                return $FUNCS->raise_error( $this->t['invalid_key'] );
            }
        }
        
        function set_cookie( $userid, $remember=0 ){
            // create a httpOnly cookie
            $days_valid = ( $remember ) ? 14 : 1;
            $cookie_expiry = time() + (3600 * 24 * $days_valid);
            $cookie = $this->create_cookie( $userid, $cookie_expiry );
            if( version_compare(phpversion(), '5.2.0', '>=') ) {
                if( $remember ){
                    setcookie($this->cookie_name, $cookie, $cookie_expiry, $this->cookie_path, null, null, true);
                }
                else{
                    setcookie($this->cookie_name, $cookie, 0, $this->cookie_path, null, null, true);
                }
            }
            else{
                if( $remember ){
                    $date = gmstrftime("%a, %d-%b-%Y %H:%M:%S", $cookie_expiry ) .' GMT';
                    header("Set-Cookie: ".rawurlencode($this->cookie_name)."=".rawurlencode($cookie)."; expires=$date; path=$this->cookie_path; httpOnly");
                }
                else{
                    header("Set-Cookie: ".rawurlencode($this->cookie_name)."=".rawurlencode($cookie)."; path=$this->cookie_path; httpOnly");
                }
            }            
        }
        
        function create_cookie( $userid, $cookie_expiry ){
            global $FUNCS;
            
            // implementation of 'A Secure Cookie Protocol - Alex X. liu'
            $data = $userid . ':' . $cookie_expiry;
            $key = $FUNCS->hash_hmac( $data, $this->get_secret_key() );
            $hash = $FUNCS->hash_hmac( $data, $key );
            return $data . ':' . $hash;
        }
        
        function delete_cookie(){
            if( version_compare(phpversion(), '5.2.0', '>=') ) {
                setcookie( $this->cookie_name, ' ', time() - (3600 * 24 * 365), $this->cookie_path, null, null, true );
            }
            else{
                setcookie( $this->cookie_name, ' ', time() - (3600 * 24 * 365), $this->cookie_path, null, null );
            }
        }
        
        function get_secret_key(){
            global $FUNCS;
            
            if( empty($this->secret_key) ){
                
                $secret_key = $FUNCS->get_setting( 'member_secret_key' );
                if( empty($secret_key) ){
                    $secret_key = $FUNCS->generate_key( 64 );
                    $FUNCS->set_setting( 'member_secret_key', $secret_key );
                }
                $this->secret_key = $secret_key;
            }
            return $this->secret_key;
            
        }
        
        function set_context( $user ){
            global $CTX;
            
            $vars = array();
            $vars['k_member_id'] = $user->member_id;
            $vars['k_member_name'] = $user->member_name;
            $vars['k_member_title'] = $user->member_title;
            $vars['k_member_creation_date'] = $user->member_creation_date;
            $vars['k_member_creation_ip'] = $user->member_creation_ip;
            $vars['k_member_email'] = $user->member_email;
            $vars['k_member_active'] = $user->member_active;
            
            $CTX->set_all( $vars, 'global' );
        }
        
        function get_link( $masterpage ){
            global $FUNCS;
            
            if( K_PRETTY_URLS ){
                return K_SITE_URL . $FUNCS->get_pretty_template_link( $masterpage );
            }
            else{
                return K_SITE_URL . $masterpage;
            }
        }
        
        function get_ids(){
            global $DB;
            
            if( is_null($this->field_ids) ){
                $arr_field_names = array(
                    'member_email',
                    'member_pwd_hash',
                    'member_pwd_reset_hash',
                    'member_active',
                    'member_activation_hash',
                    'member_failed_logins',
                    'member_last_failed_login_time',
                );
                
                $sep = ''; $str='';
                foreach( $arr_field_names as $field_name ){
                    $str .= $sep . "'" .$field_name. "'";
                    $sep = ',';
                }
                $sql = "SELECT f.template_id as tpl_id, f.id as fid, f.name as fname
                FROM " .K_TBL_TEMPLATES . " t inner join " . K_TBL_FIELDS . " f on t.id = f.template_id
                WHERE t.name='" . $DB->sanitize( $this->members_tpl ). "'
                AND f.name in (".$str.")";
                
                $rs = $DB->raw_select( $sql );
                
                if( count($rs)!=count($arr_field_names) ){
                    die( "ERROR: Not all required fields defined in MEMBER module" );
                }
                else{
                    $this->tpl_id = $rs[0]['tpl_id'];
                    
                    $arr_field_names = array();
                    foreach( $rs as $rec ){
                        $arr_field_names[$rec['fname']] = $rec['fid'];
                    }
                    $this->field_ids = $arr_field_names;
                }
            }
            return $this->field_ids;
            
        }
        
        function get_user( $username, $is_id=0 ){
            global $DB, $FUNCS;
            
            $is_email = ( (!$is_id) && (strpos($username, '@')!==false) ) ? 1 : 0;
            $field_name = ( $is_id ) ? 'id' : 'page_name';
            
            // get user from database
            $field_ids = $this->get_ids();
            $sql = "SELECT id as member_id, page_name as member_name, page_title as member_title,
                    creation_date as member_creation_date, creation_IP as member_creation_ip,
                    t0.value as member_email, t1.value as member_pwd_hash,
                    t2.value as member_pwd_reset_hash, t3.value as member_active,
                    t4.value as member_failed_logins, t5.value as member_last_failed_login_time,
                    t6.value as member_activation_hash
                    FROM ".K_TBL_PAGES."
                    inner join ".K_TBL_DATA_TEXT." t0 on t0.page_id = id 
                    inner join ".K_TBL_DATA_TEXT." t1 on t1.page_id = id 
                    inner join ".K_TBL_DATA_TEXT." t2 on t2.page_id = id 
                    inner join ".K_TBL_DATA_NUMERIC." t3 on t3.page_id = id 
                    inner join ".K_TBL_DATA_NUMERIC." t4 on t4.page_id = id 
                    inner join ".K_TBL_DATA_NUMERIC." t5 on t5.page_id = id
                    inner join ".K_TBL_DATA_TEXT." t6 on t6.page_id = id
                    WHERE template_id='".$DB->sanitize($this->tpl_id)."' ";
            if( !$is_email ){
                $sql .= "AND (".$field_name."='".$DB->sanitize($username)."') ";
            }        
            $sql .= "AND publish_date < '".$DB->sanitize($FUNCS->get_current_desktop_time())."' 
                    AND NOT publish_date = '0000-00-00 00:00:00'  
                    AND ( t0.field_id='".$DB->sanitize($field_ids['member_email'])."'
                    AND t1.field_id='".$DB->sanitize($field_ids['member_pwd_hash'])."'
                    AND t2.field_id='".$DB->sanitize($field_ids['member_pwd_reset_hash'])."'
                    AND t3.field_id='".$DB->sanitize($field_ids['member_active'])."'
                    AND t4.field_id='".$DB->sanitize($field_ids['member_failed_logins'])."'
                    AND t5.field_id='".$DB->sanitize($field_ids['member_last_failed_login_time'])."'
                    AND t6.field_id='".$DB->sanitize($field_ids['member_activation_hash'])."' ) ";
            if( $is_email ){       
                $sql .= "AND ( t0.search_value = '".$DB->sanitize($username)."' ) ";
            }          
            $sql .= "LIMIT 0, 1";
            
            $rs = $DB->raw_select( $sql );
            if( count($rs) ){
                $arr = $rs[0];
                $obj = new stdClass();
                foreach( $arr as $key => $value ){
                    $obj->$key = $value;
                }
                $obj->member_active = (int)$obj->member_active;
                $obj->member_failed_logins = (int)$obj->member_failed_logins;
                $obj->member_last_failed_login_time = (int)$obj->member_last_failed_login_time;
                
                return $obj;
            }
            
            return null;
        }
        
        function increment_login_counter( $userid, $time ){
            global $DB;
            
            $field_ids = $this->get_ids();
            $DB->begin();
            $sql = "UPDATE ".K_TBL_DATA_NUMERIC." SET value = value+1 WHERE page_id='".$DB->sanitize($userid)."' AND field_id='".$DB->sanitize($field_ids['member_failed_logins'])."'";
            $DB->_query( $sql );
            $sql = "UPDATE ".K_TBL_DATA_NUMERIC." SET value = '".$DB->sanitize($time)."' WHERE page_id='".$DB->sanitize($userid)."' AND field_id='".$DB->sanitize($field_ids['member_last_failed_login_time'])."'";
            $DB->_query( $sql );
            $DB->commit();
        }
        
        function reset_login_counter( $userid ){
            global $DB;
            
            $field_ids = $this->get_ids();
            $DB->begin();
            $sql = "UPDATE ".K_TBL_DATA_NUMERIC." SET value = '0' WHERE page_id='".$DB->sanitize($userid)."' AND field_id='".$DB->sanitize($field_ids['member_failed_logins'])."'";
            $DB->_query( $sql );
            $sql = "UPDATE ".K_TBL_DATA_NUMERIC." SET value = '0' WHERE page_id='".$DB->sanitize($userid)."' AND field_id='".$DB->sanitize($field_ids['member_last_failed_login_time'])."'";
            $DB->_query( $sql );
            $DB->commit();
        }
        
        function reset_password( $userid, $hash ){
            global $DB;
            
            $field_ids = $this->get_ids();
            $DB->begin();
            $sql = "UPDATE ".K_TBL_DATA_TEXT." SET value = '".$DB->sanitize($hash)."' WHERE page_id='".$DB->sanitize($userid)."' AND field_id='".$DB->sanitize($field_ids['member_pwd_hash'])."'";
            $DB->_query( $sql );
            $sql = "UPDATE ".K_TBL_DATA_TEXT." SET value = '' WHERE page_id='".$DB->sanitize($userid)."' AND field_id='".$DB->sanitize($field_ids['member_pwd_reset_hash'])."'";
            $DB->_query( $sql );
            $DB->commit();
        }
        
        function activate_account( $userid ){
            global $DB;
            
            $field_ids = $this->get_ids();
            $DB->begin();
            $sql = "UPDATE ".K_TBL_DATA_NUMERIC." SET value = '1' WHERE page_id='".$DB->sanitize($userid)."' AND field_id='".$DB->sanitize($field_ids['member_active'])."'";
            $DB->_query( $sql );
            $sql = "UPDATE ".K_TBL_DATA_TEXT." SET value = '' WHERE page_id='".$DB->sanitize($userid)."' AND field_id='".$DB->sanitize($field_ids['member_activation_hash'])."'";
            $DB->_query( $sql );
            $DB->commit();
        }
        
        function send_mail( $to, $subject, $msg ){
            global $FUNCS;
            
            $from = $this->t['email_from'];
            if( !$from ){
                $site = preg_replace('|^(?:www\.)?(.*)$|', '\\1', $_SERVER['SERVER_NAME']);
                $from = 'no-reply@' . $site; 
            }
            
            $headers = array();
            $headers['MIME-Version']='1.0';
            $headers['Content-Type']='text/plain; charset='.K_CHARSET;
            return $FUNCS->send_mail( $from, $to, $subject, $msg, $headers );
        }
        
        function redirect( $dest ){
            global $FUNCS, $DB;
            
            // sanity checks
            $dest = $FUNCS->sanitize_url( trim($dest) );
            if( !strlen($dest) ){
                $dest = K_SITE_URL;
            }
            elseif( strpos(strtolower($dest), 'http')===0 ){
                if( strpos($dest, K_SITE_URL)!==0 ){ // we don't allow redirects external to our site
                    $dest = K_SITE_URL;
                }
            }
            
            $DB->commit( 1 ); 
            header( "Location: ".$dest );
            die();
        }
        
        // custom validators
        function validate_unique_email( $field ){
            global $KMEMBER;
            
            $email = trim( $field->get_data() );
            $page_id = ( $field->page_id ) ? $field->page_id : $field->page->id;
            
            $user = $KMEMBER->get_user( $email );
            if( is_object($user) && $user->member_email==$email && $user->member_id!=$page_id ){
                return KFuncs::raise_error( "E-mail already exists" );
            }
        }
        
        function validate_existing_password( $field ){
            global $KMEMBER, $FUNCS, $CTX;
            
            $password = trim( $field->get_data() );
            
            // expects to be called in context of a logged-in user
            $member_id = $CTX->get( 'k_member_id' );
            if( $member_id && $FUNCS->is_non_zero_natural($member_id) ){
                $user = $KMEMBER->get_user( $member_id, 1 );
                if( is_object($user) ){
                    $check = $KMEMBER->hasher->CheckPassword( $password, $user->member_pwd_hash );
                    if( $check ){
                        return;
                    }
                }
                
            }
          
            return KFuncs::raise_error( "Incorrect" );
        }
        ////////////////////////////// Tag handlers/////////////////////////////
        
        function define_fields_handler( $params, $node ){
            global $TAGS;
            
            if( count($node->children) ) {die("ERROR: Tag \"".$node->name."\" is a self closing tag");}
            
            $code = "
            <cms:editable name='member_css' type='message'>
                <style type=\"text/css\">
                    #k_element_member_pwd_hash, 
                    #k_element_member_pwd_reset_hash, 
                    #k_element_member_activation_hash, 
                    #k_element_member_failed_logins, 
                    #k_element_member_last_failed_login_time
                    { display:none; }
                </style>
            </cms:editable>
            <cms:editable label='Email Address' name='member_email' required='1' type='text' validator='email | KMember::validate_unique_email' />
            <cms:editable label='Password Hash' name='member_pwd_hash' type='text' />
            <cms:editable label='Password Reset Hash' name='member_pwd_reset_hash' type='text'/>
            <cms:editable label='Active' name='member_active' opt_selected='0' opt_values='Yes=1 | | No=0' search_type='integer' type='radio' />
            <cms:editable label='Activation Hash' name='member_activation_hash' type='text' />
            <cms:editable label='Failed Logins' name='member_failed_logins' search_type='integer' type='text' validator='non_negative_integer'>0</cms:editable>
            <cms:editable label='Last Failed Login Time' name='member_last_failed_login_time' search_type='integer' type='text' validator='non_negative_integer'>0</cms:editable>
            
            <cms:editable label='New Password' name='member_password' type='password_hasher' hash_field='member_pwd_hash' validator='min_len=5 | max_len=64 | matches_field=member_password_repeat' />
            <cms:editable label='Repeat New Password' name='member_password_repeat' type='password_hasher' />
            ";
            
            // hand over to cms:embed
            $params = array();
            $params[] = array('lhs'=>'code', 'op'=>'=', 'rhs'=>$code);
            $TAGS->embed( $params, $node );
            
            return;
            
        }
        
        function check_login_handler( $params, $node ){
            global $FUNCS, $CTX, $KMEMBER;
            
            if( count($node->children) ) {die("ERROR: Tag \"".$node->name."\" is a self closing tag");}
            
            $KMEMBER->check_login();
        }
        
        function process_login_handler( $params, $node ){
            global $FUNCS, $CTX, $DB, $KMEMBER, $PAGE;
            
            if( count($node->children) ) {die("ERROR: Tag \"".$node->name."\" is a self closing tag");}
            $PAGE->no_cache = 1;
            
            extract( $FUNCS->get_named_vars(
                array(
                    'only_email'=>'0',
                    'redirect'=>'' /* can be '0', '1', '2' or a link */
                ),
                $params)
            );
            $only_email = ( $only_email==1 ) ? 1 : 0;
            $redirect =  trim( $redirect );
            if( !strlen($redirect) ) $redirect='2'; // default expects a querystring param nemed 'redirect' 
            
            $res = $KMEMBER->process_login( $only_email );
            
            if( $FUNCS->is_error($res) ){
                $CTX->set( 'k_success', '' );
                $CTX->set( 'k_error', $res->err_msg );
                $CTX->set( 'k_member_login_error', $res->err_msg );
            }
            else{
                // which kind of redirection requested?
                if( $redirect=='0' ){ // no redirection
                    return; 
                }
                elseif( $redirect=='1' ){ // redirect to current page
                    $dest = $_SERVER["REQUEST_URI"];
                }
                elseif( $redirect=='2' ){ // link supplied as querystring parameter (this is default behaviour)
                    $dest = $_GET['redirect'];
                    $dest = $FUNCS->unhtmlentities( $dest, K_CHARSET ); 
                }
                else{ // link supplied as parameter to this tag
                    $dest = $redirect;
                }
                
                $KMEMBER->redirect( $dest );
                
            }
        }
        
        function process_logout_handler( $params, $node ){
            global $FUNCS, $CTX, $KMEMBER, $PAGE;
            
            if( count($node->children) ) {die("ERROR: Tag \"".$node->name."\" is a self closing tag");}
            $PAGE->no_cache = 1;
            
            if( $CTX->get('k_member_logged_in', 'global') ){
                $FUNCS->validate_nonce( 'member_logout_'.$CTX->get('k_member_id', 'global') );
                $KMEMBER->delete_cookie();
                
                $dest = $FUNCS->unhtmlentities( $_GET['redirect'], K_CHARSET ); 
                $KMEMBER->redirect( $dest );
            }
            
        }
        
        function process_forgot_password_handler( $params, $node ){
            global $FUNCS, $CTX, $KMEMBER, $PAGE;
            
            if( count($node->children) ) {die("ERROR: Tag \"".$node->name."\" is a self closing tag");}
            $PAGE->no_cache = 1;
            
            extract( $FUNCS->get_named_vars(
                array(
                    'send_mail'=>'1'
                ),
                $params)
            );
            $send_mail = ( $send_mail==0 ) ? 0 : 1;
            
            $res = $KMEMBER->process_forgot_password( $send_mail );
            
            if( $FUNCS->is_error($res) ){
                $CTX->set( 'k_success', '' );
                $CTX->set( 'k_error', $res->err_msg );
                $CTX->set( 'k_member_forgot_password_error', $res->err_msg );
            }
        }
        
        function process_reset_password_handler( $params, $node ){
            global $FUNCS, $CTX, $KMEMBER, $PAGE;
            
            if( count($node->children) ) {die("ERROR: Tag \"".$node->name."\" is a self closing tag");}
            $PAGE->no_cache = 1;
            
            extract( $FUNCS->get_named_vars(
                array(
                    'send_mail'=>'1'
                ),
                $params)
            );
            $send_mail = ( $send_mail==0 ) ? 0 : 1;
            
            $res = $KMEMBER->process_reset_password( $send_mail );
            
            if( $FUNCS->is_error($res) ){
                $CTX->set( 'k_success', '' );
                $CTX->set( 'k_error', $res->err_msg );
            }
            else{
                $CTX->set( 'k_success', '1' );
                $CTX->set( 'k_error', '' );
            }
            
        }
        
        function process_registration_handler( $params, $node ){
            global $FUNCS, $CTX, $KMEMBER, $TAGS, $PAGE;
            
            if( count($node->children) ) {die("ERROR: Tag \"".$node->name."\" is a self closing tag");}
            $PAGE->no_cache = 1;
            
            extract( $FUNCS->get_named_vars(
                array(
                    
                    '_auto_name'=>'1',
                    '_send_mail'=>'1',
                    'k_page_title'=>'',
                ),
                $params)
            );
            $_auto_name = ( $_auto_name==0 ) ? 0 : 1;
            $_send_mail = ( $_send_mail==0 ) ? 0 : 1;
            $k_page_title = trim( $k_page_title );
            
            // prepare to delegate to cms:db_persist
            require_once( K_COUCH_DIR.'addons/data-bound-form/data-bound-form.php' );
            
            // exclude params we'll use/set ourselves
            $arr_excluded_params = array(
                                         '_masterpage', '_mode', '_auto_name', '_send_mail',
                                         'k_page_title', 'k_page_name', 
                                         'member_email', 'member_password',
                                         'member_password_repeat', 'member_activation_hash'
                                        );
            $tmp = array();
            foreach( $params as $p ){
                if( !in_array($p['lhs'], $arr_excluded_params) ){
                    $tmp[] = $p;
                }
            }
            $params = $tmp;
            
            // set params using values submitted through form
            $params[] = array('lhs'=>'_masterpage', 'op'=>'=', 'rhs'=>$KMEMBER->members_tpl);
            $params[] = array('lhs'=>'_mode', 'op'=>'=', 'rhs'=>'create');
            
            $member_displayname = ( strlen($k_page_title) ) ? $k_page_title : trim( $CTX->get('frm_member_displayname') );
            $params[] = array('lhs'=>'k_page_title', 'op'=>'=', 'rhs'=>$member_displayname);
            
            $member_name = $CTX->get( 'frm_member_name' );
            if( is_null($member_name) && $_auto_name ){ // if name field not used in form and auto_name is enabled
                $member_name = md5( $KMEMBER->hasher->get_random_bytes(16) );
            }
            $params[] = array('lhs'=>'k_page_name', 'op'=>'=', 'rhs'=>trim( $member_name ));
            
            $member_email = trim( $CTX->get('frm_member_email') );
            $params[] = array('lhs'=>'member_email', 'op'=>'=', 'rhs'=>$member_email);
            
            $params[] = array('lhs'=>'member_password', 'op'=>'=', 'rhs'=>$CTX->get( 'frm_member_password' ));
            $params[] = array('lhs'=>'member_password_repeat', 'op'=>'=', 'rhs'=>$CTX->get( 'frm_member_password_repeat' ));
            
            $activation_key = $FUNCS->generate_key( 32 );
            $params[] = array('lhs'=>'member_activation_hash', 'op'=>'=', 'rhs'=>$activation_key);
            
            // call
            $node->name='db_persist';
            KDataBoundForm::db_persist( $params, $node );
            
            // if member succesfully created ..
            if( $CTX->get('k_success' )){
                $CTX->set( 'k_member_email', $member_email );
                
                $member_id = $CTX->get( 'k_last_insert_id' );
                $link = $KMEMBER->get_link( $KMEMBER->registration_tpl ) . "?act=activate&id=" . $member_id . "&key=" . $activation_key;   
                $CTX->set( 'k_member_activation_link', $link );
                
                $subject = $KMEMBER->t['activation_email_subject']; 
                $CTX->set( 'k_member_activation_email_subject', $subject );
                
                $msg .= $KMEMBER->t['activation_email_msg_0'] . "\r\n"; 
                $msg .= $link ."\r\n\r\n";
                $msg .= $KMEMBER->t['activation_email_msg_1'] ."\r\n";
                $msg .= $KMEMBER->t['activation_email_msg_2'];
                $CTX->set( 'k_member_activation_email_text', $msg );
                
                if( $_send_mail ){
                    $KMEMBER->send_mail( $member_email, $subject, $msg );
                }
            }
            
            // move results from our context to parent form's context
            $cur_level = count($CTX->ctx)-1;
            for( $x=$cur_level-1; $x>=0; $x-- ){
                if( $CTX->ctx[$x]['name']=='form' ){
                    $CTX->ctx[$x]['_scope_'] = array_merge( $CTX->ctx[$x]['_scope_'], $CTX->ctx[$cur_level]['_scope_'] );
                    break; 
                }
            }
            
            return;
        }
        
        function process_activation_handler( $params, $node ){
            global $FUNCS, $CTX, $KMEMBER, $PAGE;
            
            if( count($node->children) ) {die("ERROR: Tag \"".$node->name."\" is a self closing tag");}
            $PAGE->no_cache = 1;
            
            $res = $KMEMBER->process_activation();
            
            if( $FUNCS->is_error($res) ){
                $CTX->set( 'k_success', '' );
                $CTX->set( 'k_error', $res->err_msg );
            }
            else{
                $CTX->set( 'k_success', '1' );
                $CTX->set( 'k_error', '' );
            }
            
        }
        
        function login_link_handler( $params, $node ){
            global $FUNCS, $CTX, $KMEMBER;
            
            if( count($node->children) ) {die("ERROR: Tag \"".$node->name."\" is a self closing tag");}
            
            extract( $FUNCS->get_named_vars(
                array(
                    'redirect'=>'' 
                ),
                $params)
            );
            $redirect =  trim( $redirect );
            if( !strlen($redirect) ){ $redirect = $_SERVER["REQUEST_URI"]; }
            
            if( $node->name=='member_login_link' ){
                if( $CTX->get('k_member_logged_in', 'global') ){
                    $link = 'javascript:void(0)';
                }
                else{
                    $link = $KMEMBER->get_link( $KMEMBER->login_tpl ) . '?redirect=' . urlencode( $redirect );
                }
            }
            else{ //logout link
                if( $CTX->get('k_member_logged_in', 'global') ){
                    $nonce = $FUNCS->create_nonce( 'member_logout_'.$CTX->get('k_member_id', 'global') );
                    $link = $KMEMBER->get_link( $KMEMBER->login_tpl ) . '?act=logout&nonce='.$nonce. '&redirect='.urlencode( $redirect );
                }
                else{
                    $link = 'javascript:void(0)';
                }
            }
            return $link; 
        }
        
        
    } // end class KMember
    
    $KMEMBER = new KMember();
    
    // register custom tags
    $FUNCS->register_tag( 'member_define_fields', array('KMember', 'define_fields_handler') );
    $FUNCS->register_tag( 'member_check_login', array('KMember', 'check_login_handler') );
    $FUNCS->register_tag( 'member_process_login_form', array('KMember', 'process_login_handler') );
    $FUNCS->register_tag( 'member_process_logout', array('KMember', 'process_logout_handler') );
    $FUNCS->register_tag( 'member_process_forgot_password_form', array('KMember', 'process_forgot_password_handler') );
    $FUNCS->register_tag( 'member_process_reset_password', array('KMember', 'process_reset_password_handler') );
    $FUNCS->register_tag( 'member_process_registration_form', array('KMember', 'process_registration_handler'), 1 );
    $FUNCS->register_tag( 'member_process_activation', array('KMember', 'process_activation_handler') );
    $FUNCS->register_tag( 'member_login_link', array('KMember', 'login_link_handler') );
    $FUNCS->register_tag( 'member_logout_link', array('KMember', 'login_link_handler') );
    
    
    // UDF for password hashing
    class KPasswordHasher extends KUserDefinedField{
        
        function handle_params( $params ){
            global $FUNCS, $AUTH;
            if( $AUTH->user->access_level < K_ACCESS_LEVEL_SUPER_ADMIN ) return;
            
            $attr = $FUNCS->get_named_vars(
                array(  'hash_field'=>'',
                  ),
                $params
            );
            $attr['hash_field'] = trim( $attr['hash_field'] );
            
            return $attr;
        }
        
        function _render( $input_name, $input_id, $extra='' ){
            $this->k_type = 'password';
            return KField::_render( $input_name, $input_id, $extra ); // Calling grandparent statically! Not a bug: https://bugs.php.net/bug.php?id=42016
            return $html;
        }
        
        function validate(){
            global $KMEMBER;
            
            if( $this->page_id==-1 )$this->required = 1;
            
            if( parent::validate() ){
                if( strlen($this->hash_field) ){
                    // loop through sibling fields to find the associated password_hash field
                    $found = 0;
                    for( $x=0; $x<count($this->siblings); $x++ ){
                        $f = &$this->siblings[$x];
                        if( $f->name==$this->hash_field ){ 
                            $found = 1;
                            
                            if( !$this->is_empty() ){
                                // Set field's value to the hash of our content
                                $hash = $KMEMBER->hasher->HashPassword( trim($this->data) );
                                $f->store_posted_changes( $hash );
                            }
                            else{
                                $f->modified = 0;
                            }
                            unset( $f );
                            break;
                        }
                        unset( $f );
                    }
                    
                    if( !$found ){
                        $this->err_msg = 'password hash field not found';
                        return false;
                    }
                    else{
                        return true;
                    }
                }
                else{
                    return true;
                }
            }
            else{
                return false;
            }
        }
        
        function is_empty(){
            if( strlen(trim($this->data)) ){
                return false;
            }
            return true;
        }
        
        // Save to database
        function get_data_to_save(){ 
            return ''; // nothing
        }
        
    } // end class KPasswordHasher
    
    // register custom fields
    $FUNCS->register_udf( 'password_hasher', 'KPasswordHasher', 0/*repeatable*/, 0/*searchable*/ );