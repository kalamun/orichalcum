<?php /* (c) Kalamun.org - GNU/GPL 3 */ ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?= $_SESSION['ll']; ?>" lang="<?= $_SESSION['ll']; ?>">
<head>
<title><?= $GLOBALS['kaImpostazioni']->getVar("sitename",1)." &gt; "; ?>Login</title>
<meta name="description" content="<?= $GLOBALS['kaImpostazioni']->getVar("sitename",1)." Pannello di Controllo"; ?>" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="author" content="Roberto Pasini - www.kalamun.org" />
<meta name="copyright" content="no(c)" />
<link rel="shortcut icon" href="<?= ADMINDIR; ?>img/favicon.png" />
<link rel="stylesheet" href="<?= ADMINDIR; ?>css/init.css?<?= SW_VERSION; ?>" type="text/css" />
<link rel="stylesheet" href="<?= ADMINDIR; ?>css/screen.css?<?= SW_VERSION; ?>" type="text/css" />
<link rel="stylesheet" href="<?= ADMINDIR; ?>css/main.lib.css?<?= SW_VERSION; ?>" type="text/css" />
<?php 
/* if module contains any substyle, include it */
$filename='css/substyle.css';
if(file_exists($filename)) echo '<link rel="stylesheet" href="'.ADMINDIR.PAGE_ID.'/'.$filename.'?'.SW_VERSION.'" type="text/css" />';
?>

<script type="text/javascript">
	var ADMINDIR='<?= addslashes(ADMINDIR); ?>';
	var BASEDIR='<?= addslashes(BASEDIR); ?>';
	</script>
<script type="text/javascript" src="<?= ADMINDIR; ?>js/kalamun.js?<?= SW_VERSION; ?>"></script>
<script type="text/javascript" src="<?= ADMINDIR; ?>js/main.lib.js?<?= SW_VERSION; ?>"></script>
</head>

<body>

<?php global $kaTranslate,$kaImpostazioni;
?>
<div class="pageCenter">
	<div id="login">
		<div class="orichalcum"><a href="http://orichalcum.it">Orichalcum</a></div>
		<?php 		//colleziono i get
		$get="";
		foreach($_GET as $ka=>$v) {
			if($ka!="logout") $get.=$ka.'='.$v.'&';
			}
		?>
		<form name="login" action="?<?php echo $get; ?>" method="post">
		<?= b3_create_input("orichalcum_admin_username","text",$GLOBALS['kaTranslate']->translate('UI:Username')."<br />","","",250,'placeholder="'.$GLOBALS['kaTranslate']->translate('UI:Username').'"'); ?><br />
		<br />
		<?= b3_create_input("orichalcum_admin_password","password",$GLOBALS['kaTranslate']->translate('UI:Password')."<br />","","",250,'placeholder="'.$GLOBALS['kaTranslate']->translate('UI:Password').'"'); ?>
		<div class="remember"><br />
		<?= b3_create_input("orichalcum_admin_remember","checkbox",$GLOBALS['kaTranslate']->translate('UI:Remember'),"",""); ?><br /><br /></div>

		<div class="submit"><input type="submit" name="login" value="<?= $GLOBALS['kaTranslate']->translate('UI:Enter'); ?>" class="button"></div>
		</form>
		</div>
	</div>

</body>
</html>