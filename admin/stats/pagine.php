<?
define("PAGE_NAME","Statistics:Pages");
include_once("../inc/head.inc.php");
?>

<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
<br />
<?
if(!isset($_GET['mode'])) $_GET['mode']='visited';

$modes=array(
	"visited"=>$kaTranslate->translate('Statistics:Visited pages'),
	"entry"=>$kaTranslate->translate('Statistics:Landing pages'),
	"exit"=>$kaTranslate->translate('Statistics:Exit pages'),
	);
?>
<div class="tab"><dl>
	<?
	foreach($modes as $m=>$l) { ?>
		<dt><a href="?mode=<?= $m; ?>"<?= $m==$_GET['mode']?' class="sel"':''; ?>><?= $l; ?></a></dt>
		<? }
	?>
	</dl></div>
<?

/* RACCOLGO LE STATISTICHE */
$pagine=array();
$tot=0;

$q="SELECT url FROM ".TABLE_STATISTICHE." UNION SELECT url FROM ".TABLE_STATS_ARCHIVE;
$p=mysql_query($q);
while($r=mysql_fetch_array($p)) {
	$page=explode("\n",trim($r['url']));
	if($_GET['mode']=='visited') {
		foreach($page as $pag) {
		  $pag=trim($pag);
			if(!isset($pagine[$pag])) { $pagine[$pag]=0; }
			$pagine[$pag]++;
			$tot++;
			}
		}
	elseif($_GET['mode']=='entry') {
		if(!isset($pagine[$page[0]])) { $pagine[$page[0]]=0; }
		$pagine[$page[0]]++;
		$tot++;
		}
	elseif($_GET['mode']=='exit') {
		if(!isset($pagine[$page[count($page)-1]])) { $pagine[$page[count($page)-1]]=0; }
		$pagine[$page[count($page)-1]]++;
		$tot++;
		}
	}

/* MOSTRO LE VISITE */
?>
<table class="tabella" width="100%">
<tr>
	<th><?= $kaTranslate->translate('Statistics:Page'); ?></th>
	<th colspan="2"><?= $kaTranslate->translate('Statistics:Visits'); ?></th>
	</tr>
<?
arsort($pagine);
$i=0;
foreach($pagine as $ka=>$c) {
	$i%2==0?$class="odd":$class="even";
	?>
	<tr class="<?= $class; ?>">
	<td class="labelReferer"><a href="<?= SITE_URL.'/'.$ka; ?>"><?= $ka; ?></a></td>
	<td class="counter"><?= $c ?></td>
	<td><div class="graph" style="width:<?= round($c/$tot*100,2); ?>%;"></div></td>
	</tr>
	<?
	$i++;
	}
echo '</table>';
echo '<br /><br />';
?>

<?php
include_once("../inc/foot.inc.php");
?>
