<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

require_once( "../../inc/init.php" );
ok_init();

?>

<div class="padding-30">

	<h2>Uploads Gallery</h2>

	<form>
		<input type="file" class="upload-to-gallery" name="upload_file"><br>
	</form>

</div>

<script>
	ok_upload( document.getElementsByName('upload_file')[0] );
</script>

<?php
ok_admin_footer();

