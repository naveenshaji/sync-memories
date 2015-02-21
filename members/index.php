<?php require_once( '../admin/cms.php' ); ?>

<cms:template clonable='1' title='Members'>

    <cms:member_define_fields />
    
    <!-- 
        Fields for 'email', 'password' and 'active' already come pre-defined.
        If more fields are required, they can be defined here below in the usual manner.
    -->        

</cms:template>

<?php COUCH::invoke(); ?>