<?php require_once( 'admin/cms.php' ); ?>
<cms:template title='Categories' clonable='1'>
    <cms:editable type='text' search_type='integer' name='author_id' label='Author ID' />
</cms:template>
<cms:redirect url='notes.php' />
<?php COUCH::invoke(); ?>