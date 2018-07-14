<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

require_once( "../../inc/init.php" );
ok_bootstrap();

require_once( "functions.php" );

ok_init();

ok_admin_header();


/* check user */
$user = get_user_by( "user_id", $_GET['user_id'] );

/* redirect admin editing to config section */
if( $user->type == "ADMIN" )
{
	ok_redirect( "../config/admins/edit.php?user_id=".$_GET['user_id'] );
}
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
			<h1>Modifica un utente</h1>
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
	
		<?= ok_input( [ "type" => "hidden", "name" => "nonce", "value" => ok_create_nonce( "edit-user" ) ] ); ?>
		<?= ok_input( [ "type" => "hidden", "name" => "user_id", "value" => $user->user_id ] ); ?>
		
		<!-- fake fields are a workaround for chrome autofill getting the wrong fields -->
		<?= ok_input( [ "type" => "password", "name" => "fakepasswordremembered", "value" => "", "style" => "display:none;" ] ); ?>

		<div class="row">
			<div class="grid column w12">
				<?= ok_input( [ "type" => "text", "name" => "username", "id" => "username", "class" => "full-width", "value" => $user->username ], "Nome utente" ); ?>
			</div>
		</div>
		<div class="row">
			<div class="grid column w12">
				<?= ok_input( [ "type" => "password", "name" => "password", "id" => "password", "class" => "full-width", "autocomplete" => "off" ], "Nuova password" ); ?>
			</div>
		</div>
		
		<div class="row margin-top-60">
			<div class="grid column w6">
				<?= ok_input( [ "type" => "text", "name" => "first_name", "id" => "first_name", "class" => "full-width", "value" => $user->first_name ], "Nome" ); ?>
			</div>
			<div class="grid column w6">
				<?= ok_input( [ "type" => "text", "name" => "last_name", "id" => "last_name", "class" => "full-width", "value" => $user->last_name ], "Cognome" ); ?>
			</div>
		</div>

		<div class="row">
			<div class="grid column w12">
				<?= ok_input( [ "type" => "email", "name" => "email", "id" => "email", "class" => "full-width", "value" => $user->email ], "Indirizzo e-mail" ); ?>
			</div>
		</div>
		
		<div class="row margin-top-60">
			<div class="submit">
				<?= ok_input( [ "type" => "submit", "name" => "edit-user", "value" => "Salva le modifiche" ] ); ?><br>
				<br>
				<small><a href="index.php">annulla</a></small>
			</div>
		</div>
		
	</form>
	
</div>

<?php
ok_admin_footer();

