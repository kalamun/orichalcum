<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

require_once( "../../../inc/init.php" );
ok_init();

ok_admin_header();

?>

<section class="page-header">
	<div class="row">
		<div class="grid w12">
			<h1><strong>Site structure</strong> <small>also known as custom post type</small></h1>
		</div>
	</div>
</section>

<?php print_clean_errors(); ?>

<section class="page-container">
	<div class="row">
		<div class="grid w12">

		<pre>
		<?php
		print_r( get_post_types() );
		?>
		</pre>

		<button>Add a new post type</button>
		
		<form id="add-post-type" action="<?= get_ajax_url(); ?>">
			<input type="hidden" name="nonce" value="<?= esc_url( ok_create_nonce( 'new-post-type' ) ); ?>">

			<label>Slug</label>
			<input name="new_post_type_slug" value=""><br>
			
			<label>Label</label>
			<input name="new_post_type_name" value=""><br>
			
			<label>Singular name</label>
			<input name="new_post_type_singular" value=""><br>
			
			<?=
			ok_input( [
				"type" => "radio",
				"name" => "new_post_type_status",
				"value" => "publish",
				"checked" => true,
				], "Public" );
			?>
			<?=
			ok_input( [
				"type" => "radio",
				"name" => "new_post_type_status",
				"value" => "private",
				], "Private" );
			?>
			<?=
			ok_input( [
				"type" => "radio",
				"name" => "new_post_type_status",
				"value" => "hidden",
				], "Hidden" );
			?>
			
			<br>
			<div class="submit">
				<input type="submit" value="Add">
			</div>
		</form>
		
		</div>
	</div>
</section>

<?php
ok_admin_footer();

