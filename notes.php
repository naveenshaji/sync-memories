<?php require_once( 'admin/cms.php' ); ?>
<cms:template title='Notes' clonable='1'>
    <cms:editable type='textarea' name='note' height='200' label='Note' />
    <cms:editable type='text' search_type='integer' name='author_id' label='Author ID' />
    <cms:editable name="note_cat" label="Category" type='text' />
</cms:template>
<cms:member_check_login />
<cms:redirect url='index.php' />
<?php COUCH::invoke(); ?>