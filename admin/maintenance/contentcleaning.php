<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Maintenance:Batch content cleaning");
include_once("../inc/head.inc.php");
?>
<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
<p><?= $kaTranslate->translate('Maintenance:This will clean formatting for all posts and pages. This is an irreversible operation, please <a href="%s">backup your website</a> first.', 'backup.php'); ?></p>
<br />

<?php 
if(isset($_GET['clean']))
{
	$kaTexts = new kaTexts();
	
	/* PAGES */
	require_once('../pages/pages.lib.php');
	$kaPages = new kaPages();
	
	echo '<h2>'.$kaTranslate->translate('Menu:Pages').'</h2>';
	foreach($kaPages->getQuickList(array()) as $n)
	{
		echo $kaTranslate->translate('Maintenance:Processing %s', $n['titolo']).'... ';
		$u = array();
		$u['preview'] = $kaTexts->cleanFormatting($n['anteprima']);
		$u['text'] = $kaTexts->cleanFormatting($n['testo']);
		$kaPages->update($n['idpag'], $u);
		echo $kaTranslate->translate('Maintenance:cleaned out!').'<br>';
	}
	echo '<br>';

	/* NEWS */
	require_once('../news/news.lib.php');
	$kaNews = new kaNews();
	
	echo '<h2>'.$kaTranslate->translate('Menu:News').'</h2>';
	foreach($kaNews->getList() as $n)
	{
		echo $kaTranslate->translate('Maintenance:Processing %s', $n['titolo']).'... ';
		$u = array();
		$u['preview'] = $kaTexts->cleanFormatting($n['anteprima']);
		$u['text'] = $kaTexts->cleanFormatting($n['testo']);
		$u['idnews'] = $n['idnews'];
		$kaNews->update($u);
		echo $kaTranslate->translate('Maintenance:cleaned out!').'<br>';
	}
	echo '<br>';
	
	/* SHOP */
	require_once('../shop/shop.lib.php');
	$kaShop = new kaShop();
	
	echo '<h2>'.$kaTranslate->translate('Menu:Shop').'</h2>';
	foreach($kaShop->getQuickList(array()) as $n)
	{
		echo $kaTranslate->translate('Maintenance:Processing %s', $n['titolo']).'... ';
		$u = array();
		$u['preview'] = $kaTexts->cleanFormatting($n['anteprima']);
		$u['text'] = $kaTexts->cleanFormatting($n['testo']);
		$kaShop->update($n['idsitem'], $u);
		echo $kaTranslate->translate('Maintenance:cleaned out!').'<br>';
	}
	echo '<br>';

}
?>

<br>
<a href="?clean" class="smallbutton"><?= $kaTranslate->translate('Maintenance:Clean formatting'); ?></a><br />


<?php 
include_once("../inc/foot.inc.php");



