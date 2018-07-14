<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

require_once( "../../../inc/init.php" );
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
			<h1>Gestione amministratori</h1>
		</div>
	</div>
</div>

<?php
print_clean_errors();
print_clean_successes();
print_clean_notifications();
?>

<div class="page-container">
	<div class="row">
		<div class="grid w12">

			<table>
				<tr>
					<th>Nome e Cognome</th>
					<th>Username</th>
					<th>Creato il</th>
					<th>Ultimo log-in</th>
				</tr>
				
				<?php
				$args = [
					"where" => [
						"relation" => "AND",
						[
							"key" => "type",
							"compare" => "=",
							"value" => "ADMIN",
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
						<td>
							<a href="edit.php?user_id=<?= $user->user_id; ?>"><?= $user->first_name; ?> <?= $user->last_name; ?></a><br>
							<small class="actions">
								<a href="edit.php?user_id=<?= $user->user_id; ?>">Modifica</a>
								• <a href="delete.php?user_id=<?= $user->user_id; ?>" class="alert">Elimina</a>
							</small>
						</td>
						<td><?= $user->username; ?></td>
						<td class="date"><?= decode_datetime( $user->date_created ); ?></td>
						<td class="date"><?= !empty( $user->date_last_login ) ? decode_datetime( $user->date_last_login ) : ''; ?></td>
					</tr>
					<?php
				}
				?>
			</table>

			<br>
			<a href="add.php" class="small button">Crea un nuovo amministratore</a><br>

		</div>
	</div>
</div>

<?php
ok_admin_footer();

