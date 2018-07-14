<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

require_once( "../../inc/init.php" );
ok_bootstrap();

require_once( "functions.php" );

ok_init();

ok_admin_header();

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
			<h1>Crea un nuovo utente</h1>
		</div>
	</div>
</div>

<?php
print_clean_errors();
print_clean_successes();
print_clean_notifications();
?>

<div class="page-container">

	<form action="" method="post">
	
		<?= ok_input( [ "type" => "hidden", "name" => "nonce", "value" => ok_create_nonce( "edit-user" ) ] ); ?>

		<!-- fake fields are a workaround for chrome autofill getting the wrong fields -->
		<?= ok_input( [ "type" => "text", "name" => "fakeusernameremembered", "value" => "", "style" => "display:none;" ] ); ?>
		<?= ok_input( [ "type" => "password", "name" => "fakepasswordremembered", "value" => "", "style" => "display:none;" ] ); ?>

		<div class="row">
			<div class="grid column w12">
				<?= ok_input( [ "type" => "text", "name" => "username", "id" => "username", "class" => "full-width" ], "Nome utente" ); ?>
			</div>
		</div>
		<div class="row">
			<div class="grid column w12">
				<?= ok_input( [ "type" => "password", "name" => "password", "id" => "password", "class" => "full-width" ], "Password" ); ?>
			</div>
		</div>
		
		<div class="row margin-top-60">
			<div class="grid column w6">
				<?= ok_input( [ "type" => "text", "name" => "first_name", "id" => "first_name", "class" => "full-width" ], "Nome" ); ?>
			</div>
			<div class="grid column w6">
				<?= ok_input( [ "type" => "text", "name" => "last_name", "id" => "last_name", "class" => "full-width" ], "Cognome" ); ?>
			</div>
		</div>

		<div class="row">
			<div class="grid column w12">
				<?= ok_input( [ "type" => "email", "name" => "email", "id" => "email", "class" => "full-width" ], "Indirizzo e-mail" ); ?>
			</div>
		</div>
		
		<div class="row margin-top-60">
			<div class="submit">
				<?= ok_input( [ "type" => "submit", "name" => "edit-user", "value" => "Crea l’utente" ] ); ?><br>
				<br>
				<small><a href="index.php">annulla</a></small>
			</div>
		</div>
		
	</form>
	
</div>

<?php
ok_admin_footer();

