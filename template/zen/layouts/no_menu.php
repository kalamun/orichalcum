<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?= kGetLanguage(); ?>" lang="<?= kGetLanguage(); ?>">

<head>
<title><?= kGetSiteName().' &gt; '.kGetPageTitle(); ?></title>
<meta name="description" content="<?= kGetSiteName().' &gt; '.$template->getTitle(); ?>" />
<meta name="keywords" content="<?= kGetSiteName().' &gt; '.$template->getTitle(); ?>" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF8" />
<meta name="author" content="kalamun.org" />
<meta name="revisit-after" content="7 days" />
<meta name="copyright" content="(c) <?= kGetSiteName(); ?>" />
<link rel="shortcut icon" href="<?= kGetTemplateDir(); ?>img/favicon.png" />
<style type="text/css" media="screen">
	@import "<?= kGetTemplateDir(); ?>css/screen.css";
	</style>
<script type="text/javascript" src="<?= kGetTemplateDir(); ?>js/layout.js"></script>

</head>

<body>

<div id="container">
	<div id="header">
		<h1><a href="<?= kGetBaseDir(); ?>"><?= kGetSiteName(); ?></a></h1>
		<? if(kGetSitePayoff()!="") echo '<h2>'.kGetSitePayoff().'</h2>'; ?>
		<div id="lingue"><? kPrintLanguages(); ?></div>
		</div>
		
	<div id="corpo">
		<div id="colonna">
			</div>

		<div id="contenuto">
			<?
			if(kHavePage()) {
				kPrintPage();
				}
			elseif(kHaveNews()) {
				kPrintNews();
				}
			else {
				kPrintPage(kGetHomeDir());
				}
			?>
			<div style="clear:both;"></div>
			</div>
		<div style="clear:both;"></div>
		</div>

	<div id="footer">
		<?= kGetFooter(); ?>
		<div class="credits"><?= kGetCopyright(); ?></div>
		</div>
	</div>
<?= kGetExternalStatistics(); ?>

</body>
</html>
