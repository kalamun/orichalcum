<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Maintenance:Batch content cleaning");
include_once("../inc/head.inc.php");

?>
<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
<p><?= $kaTranslate->translate('This will clean formatting for all posts and pages. This is an irreversible operation, please <a href="%s">backup your website</a> first.', 'backup.php'); ?></p>
<br />

<?php 
if(isset($_GET['clean']))
{
	$kaTexts = new kaTexts();
	
	/* NEWS */
	require_once('../news/news.lib.php');
	$kaNews = new kaNews();
	
	foreach($kaNews->getList() as $n)
	{
		//if(strpos($n['titolo'], "RACHAEL YAMAGATA")=== false) continue;

		echo 'Processing '.$n['titolo'].'... ';
		$u = array();
		//$u['preview'] = $kaTexts->cleanFormatting($n['anteprima']);
		$u['text'] = $kaTexts->cleanFormatting($n['testo']);
		$u['idnews'] = $n['idnews'];
		//echo nl2br(htmlentities($u['text']));
		$kaNews->update($u);
		echo ' processed<br>';
	}
}
?>

<br>
<a href="?clean" class="smallbutton"><?= $kaTranslate->translate('Maintenance:Clean formatting'); ?></a><br />


<?php 
include_once("../inc/foot.inc.php");



