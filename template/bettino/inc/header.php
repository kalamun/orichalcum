<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?= kGetLanguage(); ?>" lang="<?= kGetLanguage(); ?>">

<head>
<title><?= kGetSiteName().' &gt; '.kGetTitle(); ?></title>
<? $metadata=kGetSeoMetadata(); ?>
<meta name="description" content="<?= $metadata['description']; ?>" />
<meta name="keywords" content="<?= $metadata['keywords']; ?>" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF8" />
<meta name="author" content="kalamun.org" />
<meta name="revisit-after" content="<?= $metadata['revisit_after']; ?>" />
<meta name="copyright" content="(c) <?= kGetSiteName(); ?>" />
<link rel="shortcut icon" href="<?= kGetTemplateDir(); ?>img/favicon.png" />
<link rel="alternate" type="application/rss+xml" title="RSS News" href="<?= kGetBaseDir().strtolower(kGetLanguage()); ?>/feed/" />
<style type="text/css" media="screen">
	@import "<?= kGetTemplateDir(); ?>css/screen.css";
	</style>
<script type="text/javascript" src="<?= kGetTemplateDir(); ?>js/kalamun.js"></script>
<script type="text/javascript" src="<?= kGetTemplateDir(); ?>js/layout.js"></script>
<script type="text/javascript" src="<?= kGetTemplateDir(); ?>js/lightbuzz.js"></script>
<script type="text/javascript">var TEMPLATEDIR='<?= kGetTemplateDir(); ?>';</script>
</head>

<body>

<div id="container">
	<div id="header">
		<h2><a href="<?= kGetBaseDir(); ?>"><?= kGetSiteName(); ?></a></h2>
		<? if(kGetSitePayoff()!="") { ?><h3><?= kGetSitePayoff(); ?></h3><? } ?>
		<div id="lang"><? kPrintLanguages(); ?></div>
		<div id="menu"><? kPrintMenu(0,true); ?></div>
		<script type="text/javascript">kMenuInit();</script>
		</div>
	<div id="corpo">
