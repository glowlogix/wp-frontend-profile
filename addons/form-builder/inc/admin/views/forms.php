<?php
/**
 * WP Forms List Table admin page view
 */
?>
<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?> <a href="<?php echo admin_url('admin.php?page=fbwfp_form_builder');?>" class="page-title-action">Add New</a></h1>

	<!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
	<form id="wpfep-all-forms" method="get">
		<!-- For plugins, we also need to ensure that the form posts back to our current page -->
		<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
		<!-- Now we can render the completed list table -->
		<?php $forms_list_table->search_box( 'Search', 'wpfep-search' );?>
		<?php $forms_list_table->display() ?>
	</form>

</div>