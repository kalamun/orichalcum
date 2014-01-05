<?
define("PAGE_NAME","Statistics:Systems and Browsers");
include_once("../inc/head.inc.php");
include_once("stats.lib.php");
$kaStats=new kaStats();
?>

<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
<br />

<script type="text/javascript">
	function kShowDetails(ref) {
		var trOpen=document.getElementById(ref);
		var t=document.getElementById('browsers');
		for(var i=0;t.getElementsByTagName('TR')[i];i++) {
			var tr=t.getElementsByTagName('TR')[i];
			if(tr.getElementsByTagName('table')[0]) {
				if(tr==trOpen) tr.getElementsByTagName('table')[0].style.display=(tr.getElementsByTagName('table')[0].style.display=='block'?'none':'block');
				else tr.getElementsByTagName('table')[0].style.display='none';
				}
			}
		var t=document.getElementById('oss');
		for(var i=0;t.getElementsByTagName('TR')[i];i++) {
			var tr=t.getElementsByTagName('TR')[i];
			if(tr.getElementsByTagName('table')[0]) {
				if(tr==trOpen) tr.getElementsByTagName('table')[0].style.display=(tr.getElementsByTagName('table')[0].style.display=='block'?'none':'block');
				else tr.getElementsByTagName('table')[0].style.display='none';
				}
			}
		}
	</script>

<?

/* RACCOLGO LE STATISTICHE */
$browser=array();
$browserTot=array();
$os=array();
$osTot=array();
$mobile=array();
$mobileTot=array();
$tot=0;

$blankPlaceHolder=$kaTranslate->translate('Statistics:unknown');
foreach($kaStats->getRecords() as $r) {
	if($r['system']['browser']=='') $r['system']['browser']=$blankPlaceHolder;
	if($r['system']['os']=='') $r['system']['os']=$blankPlaceHolder;
	//cut the version number
	if(strpos($r['system']['browserVersion'],".")!==false) {
		$i=0;
		$offset=0;
		while(substr_count($r['system']['browserVersion'],".")>=$i-1) {
			$offset=strpos($r['system']['browserVersion'],".",$offset)+1;
			$i++;
			}
		$cut=strpos($r['system']['browserVersion'],".",$offset);
		if($cut==0) $cut=strlen($r['system']['browserVersion']);
		$r['system']['browserVersion']=substr($r['system']['browserVersion'],0,$cut);
		}
	if(!isset($browserTot[$r['system']['browser']])) $browserTot[$r['system']['browser']]=0;
	if(!isset($browser[$r['system']['browser']][$r['system']['browserVersion']])) $browser[$r['system']['browser']][$r['system']['browserVersion']]=0;
	$browserTot[$r['system']['browser']]++;
	$browser[$r['system']['browser']][$r['system']['browserVersion']]++;

	if($r['system']['mobile']==false) {
		if(!isset($osTot[$r['system']['os']])) $osTot[$r['system']['os']]=0;
		if(!isset($os[$r['system']['os']][$r['system']['osVersion']])) $os[$r['system']['os']][$r['system']['osVersion']]=0;
		$osTot[$r['system']['os']]++;
		$os[$r['system']['os']][$r['system']['osVersion']]++;
		}
	else {
		if(!isset($mobileTot[$r['system']['os']])) $mobileTot[$r['system']['os']]=0;
		if(!isset($mobile[$r['system']['os']][$r['system']['osVersion']])) $mobile[$r['system']['os']][$r['system']['osVersion']]=0;
		$mobileTot[$r['system']['os']]++;
		$mobile[$r['system']['os']][$r['system']['osVersion']]++;
		}
	$tot++;
	}

arsort($browserTot);
arsort($osTot);
arsort($mobileTot);

/* BROWSERS */
?>
<strong><?= strtoupper($kaTranslate->translate('Statistics:Browsers')); ?></strong><br />
<table class="tabella" style="width:100%" id="browsers">
<?
$i=0;
foreach($browserTot as $b=>$totBrowser) {
	$i%2==0?$class="odd":$class="even";
	$details=$browser[$b];
	?>
	<tr class="<?= $class; ?>" id="br<?= $i; ?>">
		<td class="label"><a href="javascript:kShowDetails('br<?= $i; ?>');"><?= $b; ?></a>
		<table style="display:none;" class="substats"><?
		krsort($details);
		foreach($details as $v=>$c) { ?>
			<tr>
				<td style="padding-left:50px;"><?= $v; ?></td>
				<td style="text-align:right;"><?= $c; ?></td>
				<td><div class="graph" style="width:<?= $c; ?>px;"></div></td>
				</tr>
			<? } ?>
			</table></td>
		<td class="counter"><?= $totBrowser; ?></td>
		<td><div class="graph" style="width:<?= round($totBrowser/$tot*100,2); ?>%;"></div></td>
		</tr><?
	$i++;
	}
?>
</table>
<br /><br />

<?
/* SISTEMI OPERATIVI */
?>
<strong><?= strtoupper($kaTranslate->translate('Statistics:Operative Systems')); ?></strong><br />
<table class="tabella" style="width:100%" id="oss">
<?
$i=0;
foreach($osTot as $b=>$totOs) {
	$i%2==0?$class="odd":$class="even";
	$details=$os[$b];
	?>
	<tr class="<?= $class; ?>" id="os<?= $i; ?>">
		<td class="label"><a href="javascript:kShowDetails('os<?= $i; ?>');"><?= $b; ?></a>
		<table style="display:none;" class="substats"><?
		arsort($details);
		foreach($details as $v=>$c) { ?>
			<tr>
				<td style="padding-left:50px;"><?= $v; ?></td>
				<td style="text-align:right;"><?= $c; ?></td>
				<td><div class="graph" style="width:<?= $c; ?>px;"></div></td>
				</tr>
			<? } ?>
			</table></td>
		<td class="counter"><?= $totOs; ?></td>
		<td><div class="graph" style="width:<?= round($totOs/$tot*100,2); ?>%;"></div></td>
		</tr><?
	$i++;
	}
?>
</table>
<br /><br />

<?
/* MOBILE */
?>
<strong><?= strtoupper($kaTranslate->translate('Statistics:Mobile')); ?></strong><br />
<table class="tabella" style="width:100%" id="mobile">
<?
$i=0;
foreach($mobileTot as $b=>$totMobile) {
	$i%2==0?$class="odd":$class="even";
	$details=$mobile[$b];
	?>
	<tr class="<?= $class; ?>" id="os<?= $i; ?>">
		<td class="label"><a><?= $b; ?></a>
		<td class="counter"><?= $totMobile; ?></td>
		<td><div class="graph" style="width:<?= round($totMobile/$tot*100,2); ?>%;"></div></td>
		</tr><?
	$i++;
	}
?>
</table>

<?
include_once("../inc/foot.inc.php");
?>
