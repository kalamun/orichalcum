<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

require_once( "../../inc/init.php" );
ok_bootstrap();

require_once( "functions.php" );

ok_init();

ok_admin_header();


/* check user */
$user = get_user_by( "user_id", $_GET['user_id'] );


?>

<section class="page-submenu">
	<div class="row">
		<div class="grid w12">
			<?php include( 'submenu.php' ); ?>
		</div>
	</div>
</section>

<div class="page-header">
	<div class="row">
		<div class="grid w12">
			<h1>Elimina un utente</h1>
		</div>
	</div>
</div>

<?php
print_clean_errors();
print_clean_successes();
print_clean_notifications();
?>

<div class="page-container">

	<form action="?user_id=<?= intval($_GET['user_id']); ?>" method="post">
	
		<?= ok_input( [ "type" => "hidden", "name" => "nonce", "value" => ok_create_nonce( "delete-user" ) ] ); ?>
		<?= ok_input( [ "type" => "hidden", "name" => "user_id", "value" => $user->user_id ] ); ?>
		
		<div class="row">
			<div class="grid column w12 aligncenter">
				Stai per eliminare l’utente <strong><?= $user->username; ?></strong>.<br>
				Sei sicuro di voler procedere?
			</div>
		</div>
		<div class="row margin-top-60">
			<div class="submit">
				<?= ok_input( [ "type" => "submit", "name" => "delete-user", "value" => "Elimina l’utente", "class" => "alert" ] ); ?><br>
				<br>
				<small><a href="index.php">annulla</a></small>
			</div>
		</div>
		
	</form>
	
</div>

<?php
ok_admin_footer();

