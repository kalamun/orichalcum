<?php 
define("PAGE_NAME","News:Delete a news");
include_once("../inc/head.inc.php");
include_once("./news.lib.php");
include_once("../inc/comments.lib.php");

$pageLayout=$kaImpostazioni->getVar('admin-news-layout',1,"*");
$pageMode=$kaImpostazioni->getVar('admin-news-layout',2,"*");

$kaNews=new kaNews();
$dataRef=preg_replace('/ DESC$/i','',$kaImpostazioni->getVar('news-order',1));

if(isset($_GET['m'])) $currMonth=$_GET['m'];
else $currMonth=date("n");
if(isset($_GET['y'])) $currYear=$_GET['y'];
else $currYear=date("Y");

/* AZIONI */
if(isset($_GET['delete'])) {
	$log="";
	$row=$kaNews->get($_GET['delete']);
	$id=$kaNews->delete($_GET['delete']);
	
	if($id==false) $log="Problemi durante l'eliminazione dal database";

	if($log!="") {
		echo '<div id="MsgAlert">'.$log.'</div>';
		$kaLog->add("ERR",'Errore nell\'eliminazione della news "'.$row['titolo'].'" (<em>ID: '.$row['idnews'].'</em>)');
		}
	else {
		echo '<div id="MsgSuccess">News eliminata con successo</div>';
		$kaLog->add("DEL",'Eliminata la news: '.$row['titolo'].' (<em>ID: '.$row['idnews'].'</em>)');
		}
	}
/* FINE AZIONI */


?>
<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
<br />
<?php 
if(!isset($_GET['idnews'])) {
	/* RIGHT COLUMN (same for both calendary and list views) */
	?>
	<div class="subset">
		<fieldset class="box"><legend><?= $kaTranslate->translate('UI:Search'); ?></legend>
		<input type="text" name="search" id="searchQ" style="width:180px;" value="<?php  if(isset($_GET['search'])) echo str_replace('"','&quot;',$_GET['search']); ?>" />
		<script type="text/javascript">
			function submitSearch() {
				var q=document.getElementById('searchQ').value;
				window.location="?search="+escape(q);
				}
			function searchKeyUp(e) {
			   var KeyID=(window.event)?event.keyCode:e.keyCode;
			   if(KeyID==13) submitSearch(); //invio
			   }
			document.getElementById('searchQ').onkeyup=searchKeyUp;
			
			function selectMenuRef(usePage) {
				document.getElementById('usePage').value=usePage;
				k_openIframeWindow(ADMINDIR+"inc/selectMenuRef.inc.php","450px","500px");
				}
			function selectElement(id,where) {
				var usePage=document.getElementById('usePage').value;
				var get="";
				if(String(window.location).indexOf("search=")>-1) {
					get=String(window.location);
					get=get.replace(/.*search=/,"");
					get="search="+get.replace(/^[[^\d]*].*/,"");
					}
				var url=String(window.location).replace(/\?.*/,"");
				window.location=url+'?usePage='+usePage+'&addtomenu='+id+','+where+'&'+get;
				}
			</script>
		</fieldset>
		<br />
		<h2><?= $kaTranslate->translate('News:Archive'); ?></h2>
		<?php 
		$tmpyyyy="";
		$q="SELECT ".$dataRef." FROM ".TABLE_NEWS." WHERE ll='".$_SESSION['ll']."' GROUP BY year(".$dataRef."),month(".$dataRef.") ORDER BY ".$dataRef." DESC";
		$rs=mysql_query($q);
		while($r=mysql_fetch_array($rs)) {
			$yyyy=substr($r[$dataRef],0,4);
			$mm=substr($r[$dataRef],5,2);
			if($tmpyyyy!=$yyyy) {
				if($tmpyyyy!="") echo '</ul>';
				echo '<ul class="newsArchive"><li>'.$yyyy.'</li>';
				$tmpyyyy=$yyyy;
				}
			echo '<li><a href="?m='.ltrim($mm,'0').'&y='.$yyyy.'">'.strftime("%B",mktime(1,0,0,$mm,1,$yyyy)).'</a></li>';
			}
		echo '</ul>';
		?>
		</div>

	<div class="topset">
		<?php 

		/* CALENDAR VIEW */
		if($pageMode=="calendario"&&(!isset($_GET['search'])||$_GET['search']=="")) { ?>
			<div class="box" style="text-align:center;">
				<h2><a href="?<?= 'm='.($currMonth-1<1?12:$currMonth-1).'&y='.($currMonth-1<1?$currYear-1:$currYear); ?>" class="smallbutton">&lt;</a>
				&nbsp;&nbsp;<?= strftime("%B %Y",mktime(1,0,0,$currMonth,1,$currYear)); ?>&nbsp;&nbsp;
				<a href="?<?= 'm='.($currMonth+1>12?1:$currMonth+1).'&y='.($currMonth+1>12?$currYear+1:$currYear); ?>" class="smallbutton">&gt;</a></h2>
				</div>
			<br />

			<table class="tabella calendario">
			<tr>
				<th><?= strftime("%A",mktime(1,0,0,1,3,0)); ?></th>
				<th><?= strftime("%A",mktime(1,0,0,1,4,0)); ?></th>
				<th><?= strftime("%A",mktime(1,0,0,1,5,0)); ?></th>
				<th><?= strftime("%A",mktime(1,0,0,1,6,0)); ?></th>
				<th><?= strftime("%A",mktime(1,0,0,1,7,0)); ?></th>
				<th><?= strftime("%A",mktime(1,0,0,1,1,0)); ?></th>
				<th><?= strftime("%A",mktime(1,0,0,1,2,0)); ?></th>
				</tr>
			<?php 
			$daysOffset=date("w",mktime(0,0,0,$currMonth,1,$currYear));
			if($daysOffset==0) $daysOffset=7;
			$daysInMonth=date("t",mktime(0,0,0,$currMonth,1,$currYear));

			$news=array();
			$events=array();
			if($dataRef=="starting_date"||$dataRef=="scadenza") $conditions="`starting_date` LIKE '".$currYear."-".($currMonth<10?'0':'').$currMonth."%' OR `scadenza` LIKE '".$currYear."-".($currMonth<10?'0':'').$currMonth."%'";
			else $conditions="`".$dataRef."` LIKE '".$currYear."-".($currMonth<10?'0':'').$currMonth."%'";
			foreach($kaNews->getList($conditions) as $row) {
				if(!isset($row['categorie'][0])) $row['categorie'][0]=array('dir'=>'tmp');
				if($row['calendario']=='n') $news[ltrim(substr($row[$dataRef],8,2),"0")][]=$row;
				else {
					if(($dataRef=="starting_date"||$dataRef=="scadenza")&&trim($row['starting_date'],"0-: ")!=""&&trim($row['scadenza'],"0-: ")!="") {
						$startingts=mktime(0,0,0,substr($row['starting_date'],5,2),substr($row['starting_date'],8,2),substr($row['starting_date'],0,4));
						$endingts=mktime(24,0,0,substr($row['scadenza'],5,2),substr($row['scadenza'],8,2),substr($row['scadenza'],0,4));
						if($startingts>$endingts) $endingts=$startingts;
						for($i=$startingts;$i<$endingts;$i+=86400) {
							if(date("Y-m",$i)==substr($row[$dataRef],0,7)) $events[date("j",$i)][]=$row;
							}
						}
					else $events[ltrim(substr($row[$dataRef],8,2),"0")][]=$row;
					}
				}

			?>
			<tr><?php 
			for($i=1;$i<$daysOffset;$i++) { ?>
				<td class="empty">&nbsp;</td>
				<?php  }
			for($i=1;$i<=$daysInMonth;$i++) { ?>
				<td>
				<div class="daynumber"><?= $i; ?></div>
				<?php 
				if(isset($events[$i])) {
					foreach($events[$i] as $n) { ?>
						<div class="smallalertbutton" onclick="kOpenBaloon('ajax/actionsBaloon.php?delete=<?= $n['idnews']; ?>',kGetPosition(this).y,(kGetPosition(this).x+this.offsetWidth/2));" onmouseout="kCloseBaloon();">
							<?= $n['titolo'] ?>
							</div>
						<?php  }
					}
				?>
				</td>
				<?php  if(($i+$daysOffset-1)%7==0) echo '</tr><tr>'; ?>
				<?php  }
			for($i=($i+$daysOffset-2);$i%7!=0;$i++) { ?>
				<td class="empty">&nbsp;</td>
				<?php  }
			?></tr><?php 
			?></table>
			<?php 
			if(count($news)>0) { ?>
				<br />
				<h2>Notizie fuori dal calendario</h2>
				<?php 
				foreach($news as $day) {
					foreach($day as $n) { ?>
						<div class="news" style="margin-left:10px;"><a href="?idnews=<?= $n['idnews']; ?>"><?= preg_replace("/(\d{4}).(\d{2}).(\d{2}).*/","$3-$2-$1",$n['data']); ?> - <?= $n['titolo'] ?></a></div>
						<?php  }
					}
				}
			}
		else { ?>
			<div class="box" style="text-align:center;">
			<?php 
			if(isset($_GET['search'])&&$_GET['search']!="") {
				echo $kaTranslate->translate('News:Displaying results for the search terms "%s"',$_GET['search']);
				?>
				<a href="?" class="smallbutton"><?= $kaTranslate->translate('News:Cancel'); ?></a>
				<?php 
				}
			else { ?>
				<h2><a href="?<?= 'm='.($currMonth-1<1?12:$currMonth-1).'&y='.($currMonth-1<1?$currYear-1:$currYear); ?>" class="smallbutton">&lt;</a>
				&nbsp;&nbsp;<?= strftime("%B %Y",mktime(1,0,0,$currMonth,1,$currYear)); ?>&nbsp;&nbsp;
				<a href="?<?= 'm='.($currMonth+1>12?1:$currMonth+1).'&y='.($currMonth+1>12?$currYear+1:$currYear); ?>" class="smallbutton">&gt;</a></h2>
				<?php  }
			?>
			</div>
			<br />
			<table class="tabella">
			<tr><th><?= $kaTranslate->translate('News:Title'); ?></th><th><?= $kaTranslate->translate('News:URL'); ?></th><?= ($kaImpostazioni->getVar('news-commenti',1)=='s'?'<th>'.$kaTranslate->translate('News:Comments').'</th>':''); ?>
			<?= (strpos($pageLayout,",date,")!==false?'<th style="text-align:center;">'.$kaTranslate->translate('News:Created').'</th>':''); ?>
			<?= (strpos($pageLayout,",public,")!==false?'<th style="text-align:center;">'.$kaTranslate->translate('News:Visible from').'</th>':''); ?>
			<?= (strpos($pageLayout,",expiration,")!==false?'<th style="text-align:center;">'.$kaTranslate->translate('News:Expiration').'</th>':''); ?>
			</tr>
			<?php 			$conditions="";
			if(isset($_GET['search'])) {
				$conditions.="(";
				$conditions.="titolo LIKE '%".b3_htmlize($_GET['search'],true,"")."%' OR ";
				$conditions.="sottotitolo LIKE '%".b3_htmlize($_GET['search'],true,"")."%' OR ";
				$conditions.="dir LIKE '%".b3_htmlize($_GET['search'],true,"")."%'";
				$conditions.=") AND ";
				}
			else $conditions.="`".$dataRef."` LIKE '".$currYear."-".($currMonth<10?'0':'').$currMonth."%' AND ";
			$conditions.=" `idnews`>0 ";
			foreach($kaNews->getList($conditions) as $row) {
				if(!isset($row['categorie'][0])) $row['categorie'][0]=array('dir'=>'tmp');
				echo '<tr>';
				echo '<td><h2><a href="?idnews='.$row['idnews'].'">'.$row['titolo'].'</a></h2>';
					echo '<small class="actions"><a href="?idnews='.$row['idnews'].'" class="warning">'.$kaTranslate->translate('UI:Delete').'</a> | <a href="'.SITE_URL.'/'.strtolower($_SESSION['ll'])."/".$kaImpostazioni->getVar('dir_news',1).'/'.$row['categorie'][0]['dir'].'/'.$row['dir'].'">'.$kaTranslate->translate('UI:View').'</a></small>';
					echo '</td>';
				echo '<td class="percorso"><a href="?idnews='.$row['idnews'].'">'.$row['dir'].'</a></td>';
				?>
				<?= ($kaImpostazioni->getVar('news-commenti',1)=='s'?'<td class="percorso"><strong>'.$row['commentiOnline'].'</strong> / '.$row['commentiTot'].'</td>':''); ?>
				<?= (strpos($pageLayout,",date,")!==false?'<td><div class="data"><div class="giorno">'.substr($row['data'],8,2).' '.strftime("%b",mktime(1,0,0,substr($row['data'],5,2),1,substr($row['data'],0,4))).'</div><div class="ora">'.substr($row['data'],11,5).'</div></div></td>':''); ?>
				<?= (strpos($pageLayout,",public,")!==false?'<td><div class="data"><div class="giorno">'.substr($row['pubblica'],8,2).' '.strftime("%b",mktime(1,0,0,substr($row['pubblica'],5,2),1,substr($row['pubblica'],0,4))).'</div><div class="ora">'.substr($row['pubblica'],11,5).'</div></div></td>':''); ?>
				<?= (strpos($pageLayout,",expiration,")!==false?'<td><div class="data"><div class="giorno">'.substr($row['scadenza'],8,2).' '.strftime("%b",mktime(1,0,0,substr($row['scadenza'],5,2),1,substr($row['scadenza'],0,4))).'</div><div class="ora">'.substr($row['scadenza'],11,5).'</div></div></td>':''); ?>
				<?php 
				echo '</tr>';
				}
			?></table>
		<?php  } ?>
		</div>
	<?php  }

else {
	$query="SELECT * FROM `".TABLE_NEWS."` WHERE `idnews`=".mysql_real_escape_string($_GET['idnews']);
	$results=mysql_query($query);
	$row=mysql_fetch_array($results);
	$yyyy=substr($row[$dataRef],0,4);
	$mm=substr($row[$dataRef],5,2);
	$dd=substr($row[$dataRef],8,2);
	?>
	<?= $kaTranslate->translate('News:Are you sure do you want to delete "%s"?',$row['titolo']); ?><br /><br />
	<div class="submit"><a href="?delete=<?= $_GET['idnews']; ?>" class="alertbutton"><?= $kaTranslate->translate('News:Yes, delete it'); ?></a>
	<a href="?m=<?= ltrim($mm,"0"); ?>&y=<?= $yyyy; ?>" class="button"><?= $kaTranslate->translate('News:No, don\'t delete it'); ?></a></div>
	<?php 
	}

include_once("../inc/foot.inc.php");
