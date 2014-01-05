<?
define("PAGE_NAME","Statistics:Referer");
include_once("../inc/head.inc.php");
include_once("stats.lib.php");
$kaStats=new kaStats();
?>

<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
<br />
<?
if(!isset($_GET['mode'])) $_GET['mode']='countries';

$modes=array(
	"countries"=>$kaTranslate->translate('Statistics:Countries'),
	"referer"=>$kaTranslate->translate('Statistics:Referer'),
	"facebook"=>'Facebook',
	"google"=>'Google',
	"bing"=>'Bing',
	"yahoo"=>'Yahoo',
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

/* COUNTRIES */
if($_GET['mode']=='countries') {
	$countries=array();
	$tot=0;
	foreach($kaStats->getRecords() as $r) {
		if($r['ll']=="") $r['ll']=$kaTranslate->translate('Statistics:unknown');
		if(!isset($countries[$r['ll']])) $countries[$r['ll']]=0;
		$countries[$r['ll']]++;
		$tot++;
		}
	arsort($countries);
	
	?><table class="tabella" style="width:100%;"><?
	$i=0;
	foreach($countries as $langcode=>$c) {
		$i%2==0?$class="odd":$class="even";
		?>
		<tr class="<?= $class; ?>">
		<td class="label" style="text-align:right;"><?= $langcode; ?>
			<? if(strlen($langcode)==2) { ?><img src="<?= BASEDIR; ?>img/lang/<?= strtolower($langcode); ?>.gif" width="16" height="11" alt="" /><? } ?>
			</td>
		<td class="counter" style="text-align:right;"><?= $c; ?></td>
		<td><div class="graph" style="width:<?= round($c/$tot*100,2); ?>%;"></div></td>
		</tr>
		<?
		$i++;
		}
	?></table><?
	}

/* REFERER */
elseif($_GET['mode']=='referer') {
	$referer=array();
	$tot=0;
	foreach($kaStats->getRecords() as $r) {
		if(trim($r['referer'])!=""&&substr($r['referer'],0,strlen(SITE_URL))!=SITE_URL) {
			if(preg_match('/^https?:\/\/.*?\.google\..*/',$r['referer'])) $r['referer']='Google';
			elseif(preg_match('/^https?:\/\/.*?\.bing\.com.*/',$r['referer'])) $r['referer']='Bing';
			elseif(preg_match('/^https?:\/\/.*?\.yahoo\..*/',$r['referer'])) $r['referer']='Yahoo';
			elseif(preg_match('/^https?:\/\/.*?\.facebook\.com.*/',$r['referer'])) $r['referer']='Facebook';
			if(!isset($referer[$r['referer']])) $referer[$r['referer']]=0;
			$referer[$r['referer']]++;
			$tot++;
			}
		}
	arsort($referer);
	
	?><table class="tabella" style="width:100%;"><?
	$i=0;
	foreach($referer as $r=>$c) {
		$i%2==0?$class="odd":$class="even";
		?>
		<tr class="<?= $class; ?>">
		<td class="labelReferer"><a href="<?
			if($r=='Facebook') echo '?mode=facebook';
			elseif($r=='Google') echo '?mode=google';
			elseif($r=='Bing') echo '?mode=bing';
			elseif($r=='Yahoo') echo '?mode=yahoo';
			else echo $r;
			?>"><?
			if(strlen($r)>80) echo substr($r,0,50).' ... '.substr($r,-10);
			else echo $r;
			?></a></td>
		<td class="counter" style="text-align:right;"><?= $c; ?></td>
		<td><div class="graph" style="width:<?= round($c/$tot*100,2); ?>%;"></div></td>
		</tr>
		<?
		$i++;
		}
	?></table><?
	}

/* FACEBOOK */
elseif($_GET['mode']=='facebook') {
	$referer=array();
	$mobile=0;
	$tot=0;
	foreach($kaStats->getRecords() as $r) {
		if(trim($r['referer'])!=""&&preg_match('/^https?:\/\/.*?\.facebook\.com.*/',$r['referer'])) {
			preg_match("/[&|\?]u=([^&]+)/",$r['referer'],$match);
			$searchkey=urldecode($match[1]);
			if(trim($searchkey)!="") {
				$referer[$searchkey]++;
				$tot++;
				}
			preg_match('/^https?:\/\/(.*?)\.facebook\.com.*/',$r['referer'],$match);
			$subdomain=urldecode($match[1]);
			if($subdomain=='m') $mobile++;
			}
		}
	arsort($referer);
	
	?>
	<?= $kaTranslate->translate('Statistics:Visitors'); ?>: <strong><?= $tot; ?></strong> (<em><?= $mobile; ?> <?= $kaTranslate->translate('Statistics:via mobile'); ?></em>)<br />
	<br />
	<table class="tabella" style="width:100%;"><?
	$i=0;
	foreach($referer as $r=>$c) {
		$i%2==0?$class="odd":$class="even";
		?>
		<tr class="<?= $class; ?>">
		<td class="labelReferer"><a href="<?= $r; ?>"><?
			if(strlen($r)>80) echo substr($r,0,50).' ... '.substr($r,-10);
			else echo $r;
			?></a></td>
		<td class="counter" style="text-align:right;"><?= $c; ?></td>
		<td><div class="graph" style="width:<?= round($c/$tot*100,2); ?>%;"></div></td>
		</tr>
		<?
		$i++;
		}
	?></table><?
	}

/* GOOGLE */
elseif($_GET['mode']=='google') {
	$referer=array();
	$tot=0;
	$mobile=0;
	$maps=0;
	$plus=0;
	foreach($kaStats->getRecords() as $r) {
		if(trim($r['referer'])!=""&&preg_match('/^https?:\/\/.*?\.google\..*/',$r['referer'])) {
			preg_match("/[&|\?]q=([^&]+)/",$r['referer'],$match);
			$searchkey=isset($match[1])?urldecode($match[1]):'';
			$searchkey=trim($searchkey);
			if($searchkey!=""&&strtolower($searchkey)!="google") {
				if(!isset($referer[$searchkey])) $referer[$searchkey]=0;
				$referer[$searchkey]++;
				$tot++;
				}
			preg_match('/^https?:\/\/(.*?)\.google\..*/',$r['referer'],$match);
			$subdomain=urldecode($match[1]);
			if($subdomain=='maps') $maps++;
			elseif($subdomain=='plus') $plus++;
			if($r['system']['mobile']==true) $mobile++;
			}
		}
	arsort($referer);
	
	?>
	<?= $kaTranslate->translate('Statistics:Visitors'); ?>: <strong><?= $tot; ?></strong> (<em><?= $mobile; ?> <?= $kaTranslate->translate('Statistics:via mobile'); ?> - Google Maps: <?= $maps; ?> - Google Plus: <?= $plus; ?></em>)<br />
	<br />
	<table class="tabella" style="width:100%;"><?
	$i=0;
	foreach($referer as $r=>$c) {
		$i%2==0?$class="odd":$class="even";
		?>
		<tr class="<?= $class; ?>">
		<td class="labelReferer"><a href="<?= $r; ?>"><?
			if(strlen($r)>80) echo substr($r,0,50).' ... '.substr($r,-10);
			else echo $r;
			?></a></td>
		<td class="counter" style="text-align:right;"><?= $c; ?></td>
		<td><div class="graph" style="width:<?= round($c/$tot*100,2); ?>%;"></div></td>
		</tr>
		<?
		$i++;
		}
	?></table><?
	}

elseif($_GET['mode']=='yahoo') {
	$referer=array();
	$tot=0;
	$mobile=0;
	foreach($kaStats->getRecords() as $r) {
		if(trim($r['referer'])!=""&&preg_match('/^https?:\/\/.*?\.yahoo\.com.*/',$r['referer'])) {
			preg_match("/[&|\?]p=([^&]+)/",$r['referer'],$match);
			$searchkey=urldecode($match[1]);
			if(trim($searchkey)!=""&&strtolower($searchkey)!="yahoo") {
				$referer[$searchkey]++;
				$tot++;
				}
			if($r['system']['mobile']==true) $mobile++;
			}
		}
	arsort($referer);
	
	?>
	<?= $kaTranslate->translate('Statistics:Visitors'); ?>: <strong><?= $tot; ?></strong> (<em><?= $mobile; ?></em> <?= $kaTranslate->translate('Statistics:via mobile'); ?></em>)<br />
	<br />
	<table class="tabella" style="width:100%;"><?
	$i=0;
	foreach($referer as $r=>$c) {
		$i%2==0?$class="odd":$class="even";
		?>
		<tr class="<?= $class; ?>">
		<td class="labelReferer"><a href="<?= $r; ?>"><?
			if(strlen($r)>80) echo substr($r,0,50).' ... '.substr($r,-10);
			else echo $r;
			?></a></td>
		<td class="counter" style="text-align:right;"><?= $c; ?></td>
		<td><div class="graph" style="width:<?= round($c/$tot*100,2); ?>%;"></div></td>
		</tr>
		<?
		$i++;
		}
	?></table><?
	}

/* BING */
elseif($_GET['mode']=='bing') {
	$referer=array();
	$tot=0;
	$mobile=0;
	foreach($kaStats->getRecords() as $r) {
		if(trim($r['referer'])!=""&&preg_match('/^https?:\/\/.*?\.bing\.com.*/',$r['referer'])) {
			preg_match("/[&|\?]q=([^&]+)/",$r['referer'],$match);
			$searchkey=urldecode($match[1]);
			if(trim($searchkey)!=""&&strtolower($searchkey)!="bing") {
				$referer[$searchkey]++;
				$tot++;
				}
			if($r['system']['mobile']==true) $mobile++;
			}
		}
	arsort($referer);
	
	?>
	<?= $kaTranslate->translate('Statistics:Visitors'); ?>: <strong><?= $tot; ?></strong> (<em><?= $mobile; ?></em> <?= $kaTranslate->translate('Statistics:via mobile'); ?></em>)<br />
	<br />
	<table class="tabella" style="width:100%;"><?
	$i=0;
	foreach($referer as $r=>$c) {
		$i%2==0?$class="odd":$class="even";
		?>
		<tr class="<?= $class; ?>">
		<td class="labelReferer"><a href="<?= $r; ?>"><?
			if(strlen($r)>80) echo substr($r,0,50).' ... '.substr($r,-10);
			else echo $r;
			?></a></td>
		<td class="counter" style="text-align:right;"><?= $c; ?></td>
		<td><div class="graph" style="width:<?= round($c/$tot*100,2); ?>%;"></div></td>
		</tr>
		<?
		$i++;
		}
	?></table><?
	}
	

include_once("../inc/foot.inc.php");
?>
