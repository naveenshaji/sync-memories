<?php require_once( '../admin/cms.php' ); ?>
    <cms:template title='Member Profile' hidden='1' />
    
    <!-- every template dealing with members has to begin with the following tag -->
    <cms:member_check_login />
    
    <!-- this is secured page. login first to access it -->
    <cms:if k_member_logged_out >
        <cms:redirect "<cms:member_login_link />" />
    </cms:if>
    
    <!-- someone who reaches here has to be a logged-in member -->
    <h2>Edit profile</h2>
    
    <!-- are there any success messages to show from previous save? -->
    <cms:set success_msg="<cms:get_flash 'success_msg' />" />
    <cms:if success_msg >
        <h4>Profile updated.</h4>
    </cms:if>

    <!-- this ia regular databound-form -->
    <cms:form 
        masterpage=k_member_template 
        mode='edit'
        page_id=k_member_id
        enctype="multipart/form-data"
        method='post'
        anchor='0'
        >
        
        <cms:if k_success >
            <cms:db_persist_form />

            <cms:set_flash name='success_msg' value='1' />
            <cms:redirect k_page_link /> 
        </cms:if>  
        
        <cms:if k_error >
            <font color='red'><cms:each k_error ><cms:show item /><br /></cms:each></font>
        </cms:if>
        
        
        DisplayName:<br />
        <cms:input type="bound" name="k_page_title" style="width:200px;" /><br />


        E-mail:<br />
        <cms:input type="bound" name="member_email" style="width:200px;" /><br />


        New Password: (If you would like to change the password type a new one. Otherwise leave this blank.)<br />
        <cms:input type="bound" name="member_password" style="width:200px;" /><br />


        Repeat Password:<br />
        <cms:input type="bound" name="member_password_repeat" style="width:200px;" /><br />


        <input type="submit" name="submit" value="Save"/>   
        
    </cms:form>   

    <!-- give an option to logout -->
    <a href="<cms:member_logout_link />">logout</a>  

<?php COUCH::invoke(); ?>