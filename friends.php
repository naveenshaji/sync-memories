<?php require_once( 'admin/cms.php' ); ?>
<cms:template title='Friends' clonable='1'>
	<cms:editable type='text' search_type='integer' name='author_id' label='Author ID' />
	<cms:editable type='text' search_type='integer' name='friend_id' label='Friend ID' required='1' validator='non_zero_decimal' />
</cms:template>
<cms:redirect url='notes.php' />
<?php COUCH::invoke(); ?>