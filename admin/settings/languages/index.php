<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

require_once( "../../../inc/init.php" );
ok_init();


languages_process_actions();

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
			<h1>Lingue / <strong>Lingue attive</strong></h1>
		</div>
	</div>
</section>


<section class="page-container">
	<div class="row">
		<div class="grid w12">

			<form action="<?= get_ajax_url(); ?>">
				<input type="hidden" name="nonce" value="<?= esc_url( ok_create_nonce( 'remove-language' ) ); ?>">
				<input type="hidden" name="fn" value="save-languages">
				
				<table>
					<tr>
						<th>Language</th>
						<th>ISO 639 code</th>
						<th>Country code</th>
						<th>On-line</th>
						<th>Default</th>
					</tr>
					
					<?php
					$args = [];
					foreach( get_languages( $args ) as $id => $language )
					{
						if( empty( $language->shortcode ) ) $language->shortcode = "";
						?>
						<tr data-code="<?= esc_attr( $language->code ); ?>" data-status="<?= esc_attr( $language->status ); ?>" data-status="<?= esc_attr( $language->status ); ?>">
							<td>
								<input name="language_name[<?= $id; ?>]" value="<?= esc_attr( $language->language ); ?>">
								<div class="actions">
									<a class="alert remove-language">
										Rimuovi
									</a>
								</div>
							</td>
							<td>
								<input name="language_code[<?= $id; ?>]" value="<?= esc_attr( $language->code ); ?>">
							</td>
							<td>
								<input name="language_shortcode[<?= $id; ?>]" value="<?= esc_attr( $language->shortcode ); ?>">
							</td>
							<td class="aligncenter">
								<?=
								ok_input( [
									"type" => "checkbox",
									"name" => "language_status[" . $id . "]",
									"value" => "active",
									"checked" => $language->status == 'publish' ? true : false,
									] );
								?>
							</td>
							<td class="aligncenter">
								<?=
								ok_input( [
									"type" => "radio",
									"name" => "language_default",
									"value" => $id,
									"checked" => empty( $language->default ) ? false : true,
									] );
								?>
							</td>
						</tr>
						<?php
					}
					?>
				</table>
				
				<section class="submit onchange">
					<input type="submit" value="Save">
				</section>
			</form>
			
			<fieldset>
				<legend>Aggiungi una lingua</legend>
				
				<select name="add_language">
					<?php
					foreach( file( 'languages-list.txt' ) as $line )
					{
						list( $language, $shortcode, $code ) = explode( "\t", $line );
						?>
						<option value="<?= $shortcode . '|' . $code; ?>"><?= $language ?></option>
						<?php
					}
					?>
				</select>
				<a href="?add" class="small button">Aggiungi</a>
			</fieldset>

		</div>
	</div>
</section>

<?php
ok_admin_footer();

