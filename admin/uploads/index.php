<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

require_once( "../../inc/init.php" );
ok_init();

ok_admin_header();

?>

<div class="page-header">
	<div class="row">
		<div class="grid w8 column">
			<h1><?= __( 'Uploads' ); ?></h1>
		</div>
		<div class="grid w4 column">
			<a href="edit.php" class="button">Upload a file</a>
		</div>
	</div>
</div>

<div class="page-container">
	<div class="row">
		<div class="grid w12">

			<form>
				<p id="dbi-upload-progress">Please select a file and click "Upload" to continue.</p>

				<input id="dbi-file-upload" type="file" name="dbi_import_file" /><br><br>

				<input id="dbi-file-upload-submit" class="button button-primary" type="submit" value="Upload" />
			</form>

		</div>
	</div>
</div>

<?php
ok_admin_footer();

