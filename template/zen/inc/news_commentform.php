<div id="newsForm">
	<script type="text/javascript">
		function kCheckForm(f) {
			if(f.c_name.value=="") { alert('Fill the form, please.'); f.c_name.focus(); return false; }
			if(f.c_email.value=="") { alert('Fill the form, please.'); f.c_email.focus(); return false; }
			if(f.c_text.value=="") { alert('Fill the form, please.'); f.c_text.focus(); return false; }
			return true;
			}
		</script>

	<form method="post" action="" onsubmit="return kCheckForm(this);">
		<table>
		<thead><h2><?= kTranslate('Commenta'); ?></h2></thead>
		<tbody>
		<tr><th><label for="c_name"><?= kTranslate('Name'); ?></label></th><td><input type="text" name="commentName" id="commentName" maxlength="255"></td></tr>
		<tr><th><label for="c_email"><?= kTranslate('E-mail'); ?></label></th><td><input type="text" name="commentEmail" id="commentEmail" maxlength="255"></td></tr>
		<tr><th><label for="c_text"><?= kTranslate('Comment'); ?></label></th><td><textarea name="commentText" id="commentText"></textarea></td></tr>
		<tr><td colspan="2" class="submit"><input type="submit" name="commentSubmit" value="<?= kTranslate('Submit'); ?>"></td></tr>
		</tbody>
		</table>
		</form>
	</div>