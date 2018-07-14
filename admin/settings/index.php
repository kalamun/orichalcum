<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

require_once( "../../inc/init.php" );
ok_bootstrap();

require_once( "functions.php" );

ok_init();




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

			<table>
				<tr>
					<th>Nome</th>
					<th>Cognome</th>
					<th>Username</th>
					<th>Creato il</th>
					<th>Ultimo log-in</th>
				</tr>
				
				<?php
				$args = [
					"where" => [
						[
							"key" => "type",
							"compare" => "=",
							"value" => "ADMIN",
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

