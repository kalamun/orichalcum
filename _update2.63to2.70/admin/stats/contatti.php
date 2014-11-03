<?php define("PAGE_NAME","Statistiche &gt; Contatti");
include_once("../inc/head.inc.php");
include_once("stats.lib.php");
$kaStats=new kaStats;
$mese=array("","Gennaio","Febbraio","Marzo","Aprile","Maggio","Giugno","Luglio","Agosto","Settembre","Ottobre","Novembre","Dicembre");
$giorno=array("Domenica","Luned&igrave;","Marted&igrave;","Mercoled&igrave;","Gioved&igrave;","Venerd&igrave;","Sabato");
?>
<h1><?php echo PAGE_NAME; ?></h1>
<br />
<?php 
$kaStats->deleteOldStats();
$kaStats->load();
$contatti=$kaStats->getContatti();
$visite=$kaStats->getVisite();
$stats=$kaStats->getStats();


/* MOSTRO LE VISITE */
$maxheight=300;
$avgheight=$maxheight/$stats['contatti']['max'];
?>
<div style="overflow:auto;width:100%;height:<?= $maxheight+65; ?>px;border: solid 1px #CCC;">
	<div style="position:relative;height:<?= $maxheight+10; ?>px;">
		<div class="statAvg" style="bottom:<?= round($stats['contatti']['avg']*$avgheight); ?>px;">MEDIA <?= $stats['contatti']['avg']; ?></div>
		<?php 
		for($i=0;$i<=count($contatti);$i++) {
			$timestamp=time()-(count($contatti)*24*60*60-86400*$i);
			$day=date("Y-m-d",$timestamp);
			$dayoftheweek=date("w",$timestamp);
			if(isset($contatti[$day])) {
				if($contatti[$day]==$stats['contatti']['max']) $class="max";
				elseif($contatti[$day]==$stats['contatti']['min']) $class="min";
				else $class="";
				echo '<div style="height:'.round($contatti[$day]*$avgheight).'px;left:'.($i*23).'px;" class="statCols '.$class.'" title="'.$contatti[$day].' visitatori unici">'.$contatti[$day].'</div>';
				}
			if(substr($day,8)=="01"||$i==0) { echo '<div style="position:absolute; top:'.($maxheight+30).'px; left:'.($i*23-1).'px;" class="statsMonth">'.$mese[ltrim(substr($day,5,2),"0")].' '.substr($day,0,4).'</div>'; }
			echo '<div style="top:'.($maxheight+12).'px; left:'.($i*23-1).'px;" class="statDay'.($dayoftheweek==0?' domenica':'').'">'.substr($day,8).'</div>';
			}
		?>
		</div>
	</div>

<div class="stats">
TOTALE: <strong><?= $stats['contatti']['tot']; ?></strong><br />
MEDIA GIORNALIERA: <strong><?= $stats['contatti']['avg']; ?></strong><br />
Ogni visitatore ha guardato <strong><?= round($stats['contatti']['tot']/$stats['visite']['tot'],2) ?> pagine</strong> di media ad ogni visita<br />
</div>

<br />
<hr />
<br />
<h2>VISITATORI PER GIORNO DELLA SETTIMANA</h2>
<table>
<?php 
$maxwidth=600;
$avgwidth=round($maxwidth/$stats['contatti']['maxday']);
foreach($giorno as $ka=>$v) {
	?><tr><th class="d<?= $ka; ?>"><?= $v; ?></th><td><div class="statWeekDay" style="width:<?= $stats['contatti']['d'.$ka]*$avgwidth; ?>px;"><?= $stats['contatti']['d'.$ka]; ?></div></td></tr><?php 
	}
	?>
</table>

<br />
<hr />
<br />
<h2>VISITATORI PER FASCIA ORARIA</h2>
<table>
<?php 
$maxwidth=600;
$avgwidth=round($maxwidth/$stats['contatti']['maxhour']);
for($i=0;$i<=23;$i++) {
	?><tr><th><?= $i; ?>:00</th><td><div class="statWeekDay" style="width:<?= $stats['contatti']['h'.$i]*$avgwidth; ?>px;"><?= $stats['contatti']['h'.$i]; ?></div></td></tr><?php 
	}
	?>
</table>

<?php include_once("../inc/foot.inc.php");
