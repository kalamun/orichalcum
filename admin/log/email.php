<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Log:E-mail archive");
include_once("../inc/head.inc.php");

$kaEmailLog=new kaEmailLog();

if(!isset($_GET['to'])) $_GET['to']="";
if(!isset($_GET['date'])) $_GET['date']="";
if(!isset($_GET['searchkey'])) $_GET['searchkey']="";
if(!isset($_GET['start'])) $_GET['start']=0;
if(!isset($_GET['stop'])) $_GET['stop']=30;

$conditions="";
$_GET['date']=trim($_GET['date']);
if($_GET['date']!="")
{
	// re-format date
	if(preg_match("/^(\d{2}).(\d{2}).(\d{4})$/",$_GET['date'])) $date=preg_replace("/(\d{2}).(\d{2}).(\d{4})/","$3-$2-$1",$_GET['date']);
	elseif(preg_match("/^(\d{2}).(\d{4})$/",$_GET['date'])) $date=preg_replace("/(\d{2}).(\d{4})/","$2-$1",$_GET['date']);
	elseif(preg_match("/^(\d{2}).(\d{2})$/",$_GET['date'])) $date=preg_replace("/(\d{2}).(\d{2})/","$2-$1",$_GET['date']);
	else $date=$_GET['date'];
	$conditions.=" `date` LIKE '%".ksql_real_escape_string($date)."%' AND ";
}
if($_GET['to']!="") $conditions.=" (`to` LIKE '%".ksql_real_escape_string($_GET['to'])."%' OR `to` LIKE '%".b3_htmlize($_GET['to'],true,"")."%') AND ";
if($_GET['searchkey']!="") $conditions.=" (`plain` LIKE '%".ksql_real_escape_string($_GET['searchkey'])."%' OR `title` LIKE '%".ksql_real_escape_string($_GET['searchkey'])."%' OR `plain` LIKE '%".b3_htmlize($_GET['searchkey'],true)."%' OR `title` LIKE '%".b3_htmlize($_GET['searchkey'],true)."%') AND ";
$conditions.="`ideml`>0";
?>
<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
<br />
<div class="box">
	<form action="" method="get">
		<strong><?= $kaTranslate->translate('Log:Filters'); ?>:</strong>
		<?= $kaTranslate->translate('Log:Date'); ?> <input type="text" name="date" style="width:100px;" maxlength="10" value="<?= $_GET['date']; ?>" />
		<?= $kaTranslate->translate('Log:To'); ?> <input type="text" name="to" style="width:100px;" value="<?= $_GET['to']; ?>" />
		<?= $kaTranslate->translate('Log:Keywords'); ?> <input type="text" name="searchkey" style="width:200px;" maxlength="12" value="<?= $_GET['searchkey']; ?>" />
		<input type="submit" class="smallbutton" value="<?= $kaTranslate->translate('Log:Apply filters'); ?>" />
		</form>
	</div>

<div class="box" style="text-align:center;">
	<?php 
	$tot=$kaEmailLog->count($conditions);
	for($i=0;$i<ceil($tot/$_GET['stop']);$i++) { ?>
		<a href="?to=<?= urlencode($_GET['to']); ?>&date=<?= urlencode($_GET['date']); ?>&searchkey=<?= urlencode($_GET['date']); ?>&start=<?= $i*$_GET['stop']; ?>"<?= ($i==$_GET['start']/$_GET['stop'])?'style="background-color:#ffc;padding:0 5px;"':''; ?>><?= $i+1; ?></a>
		<?php  }
	?>
	<div style="clear:both;"></div>
	</div><br />

<table class="tabella">
	<tr>
		<th><?= $kaTranslate->translate('Log:Subject'); ?> / <?= $kaTranslate->translate('Log:To'); ?></th>
		<th><?= $kaTranslate->translate('Log:Sent on'); ?></th>
		<th><?= $kaTranslate->translate('Log:Read on'); ?></th>
		<th></th>
	</tr>
	<?php 
	foreach($kaEmailLog->get($_GET['start'],$_GET['stop'],$conditions) as $p) { ?>
		<tr>
		<td><?= $p['title']; ?><br />
			<small><?= $kaTranslate->translate('Log:To'); ?>: <?= $p['to']; ?></small></td>
		<td class="data"><strong><?= $p['dataleggibile']; ?><br /><?= $p['oraleggibile']; ?></strong></td>
		<td class="data read"><?php
			if(trim($p['read_dataleggibile'],"0- ")!="") echo $p['read_dataleggibile'].'<br />'.$p['read_oraleggibile'];
			else echo $kaTranslate->translate('Log:Not read yet');
			?></td>
		<td><div class="actions"><a href="javascript:k_openIframeWindow('ajax/email.php?ideml=<?= $p['ideml']; ?>','800px','500px',true);" class="smallbutton"><?= $kaTranslate->translate('Log:View e-mail'); ?></a></div></td>
		</tr>
		<?php  }
	?>
	</table>

<br />
<br />

<div class="box" style="text-align:center;">
	<?php 
	$tot=$kaEmailLog->count($conditions);
	for($i=0;$i<ceil($tot/$_GET['stop']);$i++) { ?>
		<a href="?to=<?= urlencode($_GET['to']); ?>&date=<?= urlencode($_GET['date']); ?>&searchkey=<?= urlencode($_GET['date']); ?>&start=<?= $i*$_GET['stop']; ?>"<?= ($i==$_GET['start']/$_GET['stop'])?'style="background-color:#ffc;padding:0 5px;"':''; ?>><?= $i+1; ?></a>
		<?php  }
	?>
	<div style="clear:both;"></div>
	</div><br />

<?php 
include_once("../inc/foot.inc.php");
