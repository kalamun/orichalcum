<?
/* (c) Kalamun.org - GNU/GPL 3 */
session_start();
if(!isset($_SESSION['iduser'])) die("You don't have permission to access here");


if(isset($_POST['message'])) {
	require_once("../../../inc/tplshortcuts.lib.php");
	kInitBettino("../../../");

	$preview=$GLOBALS['__emails']->preview("","",$_POST['subject'],$_POST['message'],$_POST['template']);
	echo $preview['html'];
	}

else { ?>
	<html>
	<body>
	<div style="text-align:center;">Loading...</div>
	<form id="formpreview" action="" method="post">
	<input type="text" name="template" id="template" value="" style="display:none;" />
	<input type="text" name="subject" id="subject" value="" style="display:none;" />
	<textarea name="message" id="message" style="display:none;"></textarea>
	<script type="text/javascript">
		document.getElementById('template').value=window.parent.document.getElementById('template').value;
		document.getElementById('subject').value=window.parent.document.getElementById('subject').value;
		document.getElementById('message').value=window.parent.document.getElementById('message').value;
		document.getElementById('formpreview').submit();
		</script>
	</form>
	</body>
	</html>
	<? } ?>
