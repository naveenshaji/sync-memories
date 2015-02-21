<?php require_once( '../admin/cms.php' ); ?>
    <cms:template title='Registration' hidden='1' />


    <!-- every template dealing with members has to begin with the following tag -->
    <cms:member_check_login />

    <cms:if k_member_logged_in >
        <!-- what is an already logged-in member doing on this page? Send back to homepage. -->
        <cms:redirect k_site_link />
    </cms:if>
    
    <!-- are there any success messages to show from previous actions? -->
    <cms:set success_msg="<cms:get_flash 'success_msg' />" />
    <cms:if success_msg >
        <div class="notice">
            <cms:if success_msg='1' >
                Your account has been created successfully and we have sent you an email.<br />
                Please click the verification link within that mail to activate your account.
            <cms:else />
                Activation was successful! You can now log in!<br />
                <a href="<cms:member_login_link />">Login</a>
            </cms:if>
        </div>
    <cms:else />
        
        <!-- now the real work -->
        <cms:set action="<cms:gpc 'act' method='get'/>" />
        
        <!-- is the visitor here by clicking the account-activation link we emailed? -->
        <cms:if action='activate' >
            <h1>Activate account</h1>
        
            <cms:member_process_activation />
            
            <cms:if k_success >
                 <cms:set_flash name='success_msg' value='2' />
                 <cms:redirect k_page_link />          
            <cms:else />
                <cms:show k_error />
            </cms:if>
        
        <cms:else />
        
            <!-- show the registration form -->
            <h1>Create an account</h1>

            <cms:form enctype="multipart/form-data" method='post' anchor='0'>
                <cms:if k_success >
                    <!-- 
                        The 'member_process_registration_form' tag below expects fields named 
                        'member_displayname', 'member_name' (optional), 'member_email', 
                        'member_password' and 'member_password_repeat'
                    -->
                    <cms:member_process_registration_form />
                    
                    <cms:if k_success >
                        <cms:set_flash name='success_msg' value='1' />
                        <cms:redirect k_page_link />
                    </cms:if>
                </cms:if>

                <cms:if k_error >
                    <font color='red'><cms:each k_error ><cms:show item /><br /></cms:each></font>
                </cms:if>
                
                
                Screen Name:<br />
                <cms:input name='member_displayname' type='text' /><br />
                
                Email Address:<br />
                <cms:input name='member_email' type='text' /><br />
                    
                Password:<br />
                <cms:input name='member_password' type='password' /><br />
                
                Repeat Password:<br />
                <cms:input name='member_password_repeat' type='password' /><br />
                    
                
                <input type="submit" name="submit" value="Create account"/>
                
            </cms:form>
            
        </cms:if>
    </cms:if>    
    
    
<?php COUCH::invoke(); ?>