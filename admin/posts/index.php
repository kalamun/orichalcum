<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

require_once( "../../inc/init.php" );
ok_init();

if( !isset( $_GET['type'] ) )
{
	trigger_error( 'No post type defined' );
	die();
}

$post_type = get_post_type( $_GET['type'] );

if( empty( $_GET['type'] ) )
{
	trigger_error( 'Invalid post type' );
	die();
}

ok_admin_header();

?>

<div class="page-header">
	<div class="row">
		<div class="grid w8 column">
			<h1><?= $post_type->name; ?></h1>
		</div>
		<div class="grid w4 column">
			<a href="edit.php?type=<?= $_GET['type']; ?>" class="button">Add a new <?= $post_type->singular; ?></a>
		</div>
	</div>
</div>

<div class="page-container">
	<div class="row">
		<div class="grid w12">

			<table>
				<tr>
					<th><?= __( 'Title' ); ?></th>
					<th><?= __( 'Date' ); ?></th>
				</tr>
				
				<?php
				$args = [
					"where" => [
						[
							"key" => "post_type",
							"compare" => "=",
							"value" => $_GET['type'],
						],
					],
				];

				foreach( get_posts( $args ) as $post )
				{
					?>
					<tr>
						<td>
							<?= $post->post_title; ?>
							<div class="post-actions">
								<a href="edit.php?id=<?= $post->id; ?>"><?= __( 'Edit' ); ?></a>
								<a href="trash.php?id=<?= $post->id; ?>" class="alert"><?= __( 'Trash' ); ?></a>
							</div>
						</td>
						<td><?= $post->post_date; ?></td>
					</tr>
					<?php
				}
				?>
			</table>

		</div>
	</div>
</div>

<?php
ok_admin_footer();

