<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

require_once( "../../inc/init.php" );
ok_bootstrap();

require_once( "functions.php" );

ok_init();

do_action( "delete_user" );



ok_admin_header();

?>

<div class="page-header">
	<div class="row">
		<div class="grid w12">
			<h1>Gestione utenti</h1>
		</div>
	</div>
</div>

<div class="page-container">
	<div class="row">
		<div class="grid w12">

			<form action="" method="post">
	
				<?= ok_input( [ "type" => "hidden", "name" => "nonce", "value" => ok_create_nonce( "delete-user" ) ] ); ?>

				<table>
					<tr>
						<th>Nome</th>
						<th>Cognome</th>
						<th>Username</th>
						<th>Creato il</th>
						<th>Ultimo log-in</th>
						<th>&nbsp;</th>
					</tr>
					
					<?php
					$args = [
						"where" => [
							"relation" => "AND",
							[
								"key" => "type",
								"compare" => "=",
								"value" => "GUEST",
							],
							[
								"key" => "status",
								"compare" => "=",
								"value" => "ACT",
							],
						],
					];

					foreach( get_users( $args ) as $user )
					{
						?>
						<tr>
							<td><?= $user->first_name; ?></td>
							<td><?= $user->last_name; ?></td>
							<td><?= $user->username; ?></td>
							<td><?= $user->date_created; ?></td>
							<td><?= $user->date_last_login; ?></td>
							<td><?= ok_input( [ "type"=>"submit", "name"=>"delete-user[".$user->user_id."]", "value"=>"Rimuovi", "class"=>"small button" ] ); ?></td>
						</tr>
						<?php
					}
					?>
				</table>
			
			</form>

		</div>
	</div>
</div>

<?php
ok_admin_footer();

