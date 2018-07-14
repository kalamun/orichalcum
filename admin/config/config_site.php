<?php 
/* (c) Kalamun.org - GNU/GPL 3 */
require_once( "../../inc/init.php" );
ok_bootstrap();

require_once( __DIR__ . "/functions.php" );

ok_init();

do_action( "save_options" );

ok_admin_header();

global $ok_db;
?>

<div class="page-header">
	<div class="row">
		<div class="grid w12">
			<h1>Config</h1>
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
	
		<?= ok_input( [ "type" => "hidden", "name" => "nonce", "value" => ok_create_nonce( "save-options" ) ] ); ?>

		<div class="row">
			<div class="grid column w12">
				<?= ok_input( [ "type" => "text", "name" => "site_name", "id" => "site_name", "class" => "full-width", "value" => get_option( "site_name", "" ) ], "Nome del sito" ); ?>
			</div>
		</div>
		
		<div class="row">
			<div class="grid column w12">
				<?= ok_input( [ "type" => "text", "name" => "payoff", "id" => "payoff", "class" => "full-width", "value" => get_option( "payoff", "" ) ], "Motto" ); ?>
			</div>
		</div>
		
		<div class="row">
			<div class="grid column w12">
				<?= ok_input( [ "type" => "text", "name" => "site_url", "id" => "site_url", "class" => "full-width", "value" => get_option( "site_url", "", "*" ) ], "Indirizzo del sito" ); ?>
			</div>
		</div>
		
		<hr>

		<div class="row margin-top-60">
			<div class="submit">
				<?= ok_input( [ "type" => "submit", "name" => "save-options", "value" => "Salva le modifiche" ] ); ?>
			</div>
		</div>

	</form>

</div>

<?php
ok_admin_footer();

