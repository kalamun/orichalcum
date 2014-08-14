<?php
/* (c) Kalamun.org - GNU/GPL 3 */

require_once("main.lib.php");
$orichalcum = new kaOrichalcum();
$orichalcum->init( array("x-frame-options"=>"", "check-permissions"=>false) );

define("PAGE_NAME",$kaTranslate->translate('Uploads:Loading'));
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?= $_SESSION['ll']; ?>" lang="<?= $_SESSION['ll']; ?>">
<head>
<title><?= ADMIN_NAME." - ".PAGE_NAME; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="author" content="Roberto Pasini - www.kalamun.org" />
<meta name="copyright" content="no(c)" />
<link rel="stylesheet" href="<?= ADMINDIR; ?>css/screen.css?<?= SW_VERSION; ?>" type="text/css" />
<link rel="stylesheet" href="<?= ADMINDIR; ?>css/main.lib.css?<?= SW_VERSION; ?>" type="text/css" />
<link rel="stylesheet" href="<?= ADMINDIR; ?>css/uploadsmanager.css?<?= SW_VERSION; ?>" type="text/css" />

<script type="text/javascript">
	var ADMINDIR='<?= str_replace("'","\'",ADMINDIR); ?>';
	</script>
<script type="text/javascript" src="<?= ADMINDIR; ?>js/dictionary.js.php?<?= SW_VERSION; ?>" charset="utf-8"></script>
<script type="text/javascript" src="<?= ADMINDIR; ?>js/kalamun.js?<?= SW_VERSION; ?>" charset="utf-8"></script>
<script type="text/javascript" src="<?= ADMINDIR; ?>js/main.lib.js?<?= SW_VERSION; ?>" charset="utf-8"></script>
<script type="text/javascript" src="<?= ADMINDIR; ?>js/uploadsManager.js?<?= SW_VERSION; ?>" charset="utf-8"></script>
</head>

<body>
<br>
<div style="text-align:center;">
	<h2><?= $kaTranslate->translate('UI:Loading...'); ?></h2>
</div>

</body>
</html>
