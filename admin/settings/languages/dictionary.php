<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

require_once( "../../../inc/init.php" );
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

<section class="page-header">
	<div class="row">
		<div class="grid w12">
			<h1>Lingue / <strong>Dizionario</strong></h1>
		</div>
	</div>
</section>


<section class="page-container">
	<div class="row">
		<div class="grid w12">

			<table>
				<tr>
					<th>Lingua</th>
					<th>Codice</th>
				</tr>
				
				<?php
				$args = [];

				foreach( get_languages( $args ) as $language )
				{
					?>
					<tr>
						<td>
							<?= $language->language; ?>
							<div class="actions">
								<a class="alert" href="?remove=<?= esc_url( $language->code ); ?>&nonce=<?= esc_url( ok_create_nonce( 'remove-language' ) ); ?>">
									Rimuovi
								</a>
							</div>
						</td>
						<td><?= $language->code; ?></td>
					</tr>
					<?php
				}
				?>
			</table>

		</div>
	</div>
</section>

<?php
ok_admin_footer();

