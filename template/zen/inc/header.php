<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?= LANG; ?>" lang="<?= LANG; ?>">

<head>
<title><?= kGetSiteName().' &gt; '.kGetPageTitle(); ?></title>
<? $metadata=kGetSeoMetadata(); ?>
<meta name="description" content="<?= $metadata['description']; ?>" />
<meta name="keywords" content="<?= $metadata['keywords']; ?>" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF8" />
<meta name="author" content="kalamun.org" />
<meta name="revisit-after" content="<?= $metadata['revisit_after']; ?>" />
<meta name="copyright" content="(c) <?= kGetSiteName(); ?>" />
<link rel="shortcut icon" href="<?= kGetTemplateDir(); ?>img/favicon.png" />
<link rel="alternate" type="application/rss+xml" title="RSS News" href="<?= kGetBaseDir(); ?>feed/" />
<style type="text/css" media="screen">
	@import "<?= kGetTemplateDir(); ?>css/screen.css";
	</style>
<script type="text/javascript" src="<?= kGetTemplateDir(); ?>js/kalamun.js"></script>
<script type="text/javascript" src="<?= kGetTemplateDir(); ?>js/layout.js"></script>
<script type="text/javascript" src="<?= kGetTemplateDir(); ?>js/lightbuzz.js"></script>

</head>

<body>

<div id="container">
	<div id="header">
		<div class="titolo">
			<h1><a href="<?= kGetBaseDir(); ?>"><?= kGetSiteName(); ?></a></h1>
			<div id="payoff"><? if(kGetSitePayoff()) echo kGetSitePayoff(); ?></div>
			</div>
		</div>
		
	<div id="corpo">
	<div id="menu">
		<div id="rss">
			<img src="<?= kGetTemplateDir(); ?>img/rss.gif" width="" height="" alt="" />
			<div class="link"><?= kTranslate('Iscriviti agli'); ?> <a href="<?= kGetBaseDir().strtolower(kGetLanguage()).'/'; ?>feed/">RSS</a></div>
			</div>
		<div id="mainmenu"><?= kPrintMenu(); ?></div>
		<div style="clear:both;"></div>
		<div id="banner"><ul><?
			$bnrs=kGetBannerList('InEvidenza');
			if(!is_array($bnrs)) $bnrs=array();
			$i=0;
			foreach($bnrs as $b) {
				echo '<li><a href="'.$b['link'].'"><img src="'.$b['permalink'].'" alt="" width="'.$b['width'].'" height="'.$b['height'].'" /><br />'.$b['alt'].'</a></li>';
				$i++;
				}
			echo "</ul>";
			?>
			</div>
		</div>
	<div id="contenuto">
