<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

require_once( "../../inc/init.php" );
ok_init();

ok_admin_header();

if( !empty( $_GET['id'] ) && is_numeric( $_GET['id'] ) )
{
	$post = get_post( $_GET['id'] );
	if( empty( $post ) )
	{
		trigger_error( __( 'The post you asked for is no longer available' ) );
	}
	$_GET['post_type'] = $post->post_type;
}

if( empty( $post ) )
	$post = (object) [
		"post_type" => "",
		"post_title" => "",
		"post_subtitle" => "",
		"post_content" => "",
		"post_date" => "",
		"post_excerpt" => "",
		"post_status" => "draft",
	];

?>

<form action="" method="post" id="update-post">
	<?= ok_input( [ "type" => "hidden", "name" => "nonce", "value" => ok_create_nonce( "publish-page" ) ] ); ?>
	<?= ok_input( [ "type" => "hidden", "name" => "action", "value" => "do_ajax" ] ); ?>
	<?= ok_input( [ "type" => "hidden", "name" => "fn", "value" => "update-post" ] ); ?>
	<?php
	if( !empty( $post->id ) )
		echo ok_input( [ "type" => "hidden", "name" => "id", "value" => $post->id ] );
	?>
	<div class="page-header">
		<div class="row">
			<div class="grid w12">
				<div class="title">
					<?= ok_textarea( [ "name" => "post_title", "placeholder" => "Write something beautiful", "value" => esc_attr( $post->post_title ) ], "", "title_editor" ); ?>
				</div>
				<div class="subtitle">
					<?= ok_textarea( [ "name" => "post_subtitle", "placeholder" => "Subtitle here", "value" => esc_attr( $post->post_subtitle ) ], "", "title_editor" ); ?>
				</div>
			</div>
		</div>
	</div>

	<?php
	print_clean_errors();
	?>

	<div class="page-container">

		<div class="row">
			<div class="grid w12">
				<?= ok_textarea( [ "name" => "post_content", "placeholder" => "Make the web a better place", "value" => esc_attr( $post->post_content ) ], "", "content_editor" ); ?>
			</div>
		</div>
		
		
		<div class="row margin-top-60">
			<section class="submit">
				<?= ok_input( [ "type" => "submit", "name" => "publish-page", "value" => !empty( $post->id ) ? __( "Save" ) : __( "Publish" ) ] ); ?>
			</section>
		</div>
		
	</div>
</form>

<?php
ok_admin_footer();

