<?
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME",'Statistics:Realtime');
include_once("../inc/head.inc.php");
include_once("stats.lib.php");
$kaStats=new kaStats;
?>

<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
<br />
<?
$kaStats->deleteOldStats();
$kaStats->load();
$realtime=$kaStats->getRealtime();
?>
Stanno visitando il sito in questo momento:
<div class="box" style="display:inline-block;"><h2><strong><?= $realtime['online']; ?></strong> <?= ($realtime['online']==1?$kaTranslate->translate('Statistics:user'):$kaTranslate->translate('Statistics:users')); ?></h2></div>
<br />
<br />

<?
$i=1;
foreach($realtime['users'] as $u) { ?>
	<div class="box user">
		<h4><?= $kaTranslate->translate('Statistics:user').' '.$i; ?></h4>
		<table>
			<tr><td><label><?= $kaTranslate->translate('Statistics:time'); ?></label></td><td><?= substr($u['date'],11,5); ?></td></tr>
			<tr><td><label><?= $kaTranslate->translate('Statistics:is watching'); ?></label></td><td><?= $u['lastpage']; ?></td></tr>
			<tr><td><label><?= $kaTranslate->translate('Statistics:system'); ?></label></td><td><?= $u['system']['browser'].' '.$u['system']['browserVersion']; ?><br />
				<?= $u['system']['os'].' '.$u['system']['osVersion']; ?></td></tr>
			<tr><td><label><?= $kaTranslate->translate('Statistics:country'); ?></label></td><td><?= $u['ll']==""?$kaTranslate->translate('Statistics:unknown'):$u['ll'].' <img src="'.BASEDIR.'img/lang/'.strtolower($u['ll']).'.gif" width="16" height="11" alt="" />'; ?></td></tr>
			</table>
		</div>
	<?
	$i++;
	}

include_once("../inc/foot.inc.php");
?>
