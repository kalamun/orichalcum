<?
require('../../../inc/tplshortcuts.lib.php');
kInitBettino();

$now=array();
$now['year']=substr($_POST['aaaamm'],0,4);
$now['month']=substr($_POST['aaaamm'],4,2);
$now['daysOffset']=date("w",mktime(0,0,0,$now['month'],1,$now['year']));
if($now['daysOffset']==0) $now['daysOffset']=7;
$now['daysInMonth']=date("t",mktime(0,0,0,$now['month'],1,$now['year']));
$next=array();
$next['year']=$now['year'];
if($now['month']<12) $next['month']=$now['month']+1;
else { $next['month']=1; $next['year']++; }
if($next['month']<10) $next['month']="0".$next['month'];
$prev=array();
$prev['year']=$now['year'];
if($now['month']-1>1) $prev['month']=$now['month']-1;
else { $prev['month']=12; $prev['year']--; }
if($prev['month']<10) $prev['month']="0".$prev['month'];

?>

<div class="titolo">
	<a href="javascript:loadCalendar('<?= $prev['year'].$prev['month']; ?>');" style="float:left;"><img src="<?= kGetTemplateDir(); ?>img/smallarrowL.png" class="goprev" /></a>
	<a href="javascript:loadCalendar('<?= $next['year'].$next['month']; ?>');" style="float:right;"><img src="<?= kGetTemplateDir(); ?>img/smallarrowR.png" class="gonext" /></a>
	<?= kTranslate('Appuntamenti'); ?><br /><?= strftime('%B',mktime(1,1,1,$now['month'],1,$now['year'])).' '.$now['year']; ?>
	</div>
<table class="daystable">
<tr>
	<th><?= kTranslate('L'); ?></th>
	<th><?= kTranslate('M'); ?></th>
	<th><?= kTranslate('M'); ?></th>
	<th><?= kTranslate('G'); ?></th>
	<th><?= kTranslate('V'); ?></th>
	<th><?= kTranslate('S'); ?></th>
	<th><?= kTranslate('D'); ?></th>
	</tr>
<?
$cNews=array();
$cEvents=array();
$conditions="`".kGetVar('news-order',1)."` LIKE '".$now['year']."-".$now['month']."%'";
foreach(kGetNewsList("*",false,999,0,$conditions) as $row) {
	if(!isset($row['categorie'][0])) $row['categorie'][0]=array('dir'=>'tmp');
	$cEvents[ltrim(substr($row[kGetVar('news-order',1)],8,2),"0")][]=$row;
	}
?>
<tr><?
for($i=1;$i<$now['daysOffset'];$i++) { ?>
	<td class="empty">&nbsp;</td>
	<? }
for($i=1;$i<=$now['daysInMonth'];$i++) { ?>
	<td<?= (($i+$now['daysOffset']-1)%7==0||($i+$now['daysOffset'])%7==0)?' class="we"':''; ?>>
	<div class="daynumber"><?
		if(isset($cEvents[$i])) { ?><a href="<?= $cEvents[$i][0]['archpermalink']['day']; ?>"><? }
		echo $i;
		if(isset($cEvents[$i])) { ?></a><? }
		?></div>
	</td>
	<? if(($i+$now['daysOffset']-1)%7==0) echo '</tr><tr>'; ?>
	<? }
for($i=($i+$now['daysOffset']-2);$i%7!=0;$i++) { ?>
	<td class="empty">&nbsp;</td>
	<? }
?></tr><?
?></table>
