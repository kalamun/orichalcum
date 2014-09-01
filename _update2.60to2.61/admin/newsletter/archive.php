<?
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Newsletter:Archive");
include_once("../inc/head.inc.php");

require_once("./newsletter.lib.php");
$kaNewsletter=new kaNewsletter();
$queueCount=$kaNewsletter->getQueueCount();

?><script type="text/javascript" src="js/newsletter.js"></script><?


/* ACTIONS */
if(isset($_GET['delete'])) {
	$log="";
	if(!$kaNewsletter->deleteFromArchive($_GET['delete'])) $log="Newsletter:Error while deleting from database";

	if($log!="") {
		echo '<div id="MsgAlert">'.$kaTranslate->translate($log).'</div>';
		$kaLog->add("ERR",'Newsletter: Error while deleting archived mail <em>ID: '.$_GET['delete'].'</em>');
		}
	else {
		echo '<div id="MsgSuccess">'.$kaTranslate->translate('Newsletter:Entry successfully deleted').'</div>';
		$kaLog->add("DEL",'Newsletter: Deleted archived mail <em>ID: '.$_GET['delete'].'</em>');
		$queueCount=$kaNewsletter->getQueueCount();
		}
	}
/* END ACTIONS */

?>

<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
<br />

<? if($queueCount>0) { ?>
	<div class="box alert">
		<div style="display:inline-block;margin-right:20px;"><h3><?= $kaTranslate->translate('Newsletter:E-mail queue'); ?></h3>
			<?= $kaTranslate->translate('Newsletter:There are %d e-mails in queue',$queueCount); ?></div>
		<div style="display:inline-block;vertical-align:top;margin-right:20px;"><input type="button" onclick="processQueue();" value="<?= $kaTranslate->translate('Newsletter:Process queue'); ?>" class="button" /></div>
		<div style="clear:both;"></div>
		</div>
	<? } ?>

<br />

<table class="tabella">
<tr>
	<th><?= $kaTranslate->translate('Newsletter:Submission date'); ?></th>
	<th><?= $kaTranslate->translate('Newsletter:Subject'); ?></th>
	<th><?= $kaTranslate->translate('Newsletter:Recipients number'); ?></th>
	<th><?= $kaTranslate->translate('Newsletter:Open rate'); ?></th>
	<th></th>
	</tr>
	<?
	if(!isset($_GET['page'])) $_GET['page']=0;
	$q_show=10; //records per page
	$q_start=$_GET['page']*$q_show;

	$records=$kaNewsletter->getArchiveList(array("from"=>$q_start,"limit"=>$q_show));
	foreach($records as $i=>$row) { ?>
		<tr class="<?= ($i%2==0?"odd":"even"); ?>">
		<td class="date">
			<?= preg_replace("/(\d{4})-(\d{2})-(\d{2}) (\d{2}:\d{2}):\d{2}/","$3-$2-$1",$row['data']) ?><br />
			<img src="<?= ADMINRELDIR; ?>img/clock10.png" width="10" height="10" alt="" /> <?= preg_replace("/(\d{4})-(\d{2})-(\d{2}) (\d{2}:\d{2}):\d{2}/","$4",$row['data']) ?>
			</td>
		<td class="subject"><?= $row['titolo']; ?></td>
		<td class="recipients"><?= $row['destinatari']; ?><br />
			<small><?
			if($row['inqueue']==0) echo $kaTranslate->translate('Newsletter:Already sent');
			elseif($row['inqueue']==intval($row['destinatari'])) echo $kaTranslate->translate('Newsletter:On queue');
			else echo $kaTranslate->translate('Newsletter:Processing');
			?></small></td>
		<td class="readed"><?= $row['readed']; ?><br />
			<small><?= ($row['destinatari'] ? round(100/$row['destinatari']*$row['readed'],2) : '--'); ?> %</small>
			</td>
		<td nowrap>
			<a href="write.php?import=<?= $row['idarch']; ?>" class="smallbutton"><?= $kaTranslate->translate('Newsletter:Edit as new'); ?></a>
			<a href="?delete=<?= $row['idarch']; ?>" class="smallalertbutton" onclick="return confirm('<?= addslashes($kaTranslate->translate('Newsletter:Do you really want to delete this entry and all his e-mails still in queue?')); ?>')"><?= $kaTranslate->translate('UI:Delete'); ?></a>
			</td>
		</tr>
		<? } ?>
	</table>

	<br />
	<div class="box" style="text-align:center;">
		<?
		$count=$kaNewsletter->countArchiveRecords();
		if($_GET['page']>0) echo '<a href="?page='.($_GET['page']-1).'">&laquo; '.$kaTranslate->translate('Newsletter:Previous page').'</a>&nbsp;&nbsp;&nbsp;';
		for($i=0;$i*$q_show<$count;$i++) { ?>
			<a href="?page=<?= $i; ?>" <?= $i==$_GET['page']?'class="smallbutton"':''; ?>>&nbsp;<?= ($i+1); ?>&nbsp;</a>
			<? }
		if($_GET['page']<$i-1) echo '&nbsp;&nbsp;&nbsp;<a href="?page='.($_GET['page']+1).'">'.$kaTranslate->translate('Newsletter:Next page').' &raquo;</a>';
		?>
		</div>

<? include_once("../inc/foot.inc.php"); ?>
