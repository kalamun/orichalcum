<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","News:Edit a News");
include_once("../inc/head.inc.php");
include_once("./news.lib.php");
include_once("../inc/comments.lib.php");
include_once("../inc/metadata.lib.php");

$pageLayout=$kaImpostazioni->getVar('admin-news-layout',1,"*");
$pageMode=$kaImpostazioni->getVar('admin-news-layout',2,"*");

$kaNews=new kaNews();
$kaMetadata=new kaMetadata;
$dataRef=preg_replace('/ DESC$/i','',$kaImpostazioni->getVar('news-order',1));

if(isset($_GET['m'])) $currMonth=$_GET['m'];
else $currMonth=date("n");
if(isset($_GET['y'])) $currYear=$_GET['y'];
else $currYear=date("Y");


?>
<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
<br />

<script type="text/javascript" src="./js/edit.js"></script>

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
			</script>
		</fieldset>
		<br />
		<h2><?= $kaTranslate->translate('News:Archive'); ?></h2>
		<?php 
		$tmpyyyy="";
		$q="SELECT ".$dataRef." FROM ".TABLE_NEWS." WHERE ll='".$_SESSION['ll']."' GROUP BY year(".$dataRef."),month(".$dataRef.") ORDER BY ".$dataRef." DESC";
		$rs=ksql_query($q);
		while($r=ksql_fetch_array($rs)) {
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
		<?php 

	/****** CALENDARY LAYOUT *******/
	if($pageMode=="calendario"&&(!isset($_GET['search'])||$_GET['search']=="")) { ?>
		<div class="topset">
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
				<div class="daynumber"><?= $i; ?></div> <small><a href="new.php?visible_day=<?= $currYear.'-'.$currMonth.'-'.$i; ?>&visible_hour=<?= date("H:i"); ?>"><?= $kaTranslate->translate('News:Add a new post'); ?></a></small>
				<?php 
				if(isset($events[$i])) {
					foreach($events[$i] as $n) { ?>
						<div class="smallbutton" onclick="kOpenBaloon('ajax/actionsBaloon.php?idnews=<?= $n['idnews']; ?>',kGetPosition(this).y,(kGetPosition(this).x+this.offsetWidth/2));" onmouseout="kCloseBaloon();">
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
			?></div>
		<?php  }

	/****** LIST LAYOUT *******/
	else { ?>
		<div class="topset">
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
			<tr><th><?= $kaTranslate->translate('News:Title'); ?></th><th><?= $kaTranslate->translate('News:URL'); ?></th>
			<?= ($kaImpostazioni->getVar('news-commenti',1)=='s'?'<th>'.$kaTranslate->translate('News:Comments').'</th>':''); ?>
			<?= (strpos($pageLayout,",date,")!==false?'<th style="text-align:center;">'.$kaTranslate->translate('News:Created').'</th>':''); ?>
			<?= (strpos($pageLayout,",public,")!==false?'<th style="text-align:center;">'.$kaTranslate->translate('News:Visible from').'</th>':''); ?>
			<?= (strpos($pageLayout,",startingdate,")!==false?'<th style="text-align:center;">'.$kaTranslate->translate('News:Starting date').'</th>':''); ?>
			<?= (strpos($pageLayout,",expiration,")!==false?'<th style="text-align:center;">'.$kaTranslate->translate('News:Expiration').'</th>':''); ?>
			</tr>
			<?php 
			/* SEARCH CONDITIONS */
			$conditions="";
			if(isset($_GET['search'])) {
				$conditions.="(";
				$conditions.="`titolo` LIKE '%".b3_htmlize($_GET['search'],true,"")."%' OR ";
				$conditions.="`sottotitolo` LIKE '%".b3_htmlize($_GET['search'],true,"")."%' OR ";
				$conditions.="`dir` LIKE '%".b3_htmlize($_GET['search'],true,"")."%'";
				$conditions.=") AND ";
				}
			else $conditions.="`".$dataRef."` LIKE '".$currYear."-".($currMonth<10?'0':'').$currMonth."%' AND ";
			$conditions.=" `idnews`>0 ";

			foreach($kaNews->getList($conditions) as $row) {
				if(!isset($row['categorie'][0])) $row['categorie'][0]=array('dir'=>'tmp');
				?>
				<tr>
				<td><a href="?idnews=<?= $row['idnews']; ?>"><?= $row['titolo']; ?></a>
					<?php  if($row['online']=='n') echo '<small class="alert">'.$kaTranslate->translate('News:DRAFT').'</small>'; ?><br />
					<small class="actions"><a href="?idnews=<?= $row['idnews']; ?>"><?= $kaTranslate->translate('UI:Edit'); ?></a> | <a href="<?= SITE_URL.BASEDIR.strtolower($_SESSION['ll'])."/".$kaImpostazioni->getVar('dir_news',1).'/'.$row['categorie'][0]['dir'].'/'.$row['dir']; ?>"><?= $kaTranslate->translate('UI:View'); ?></a> | <a href="new.php?copyfrom=<?= $row['idnews']; ?>"><?= $kaTranslate->translate('UI:Copy'); ?></a></small>
					</td>
				<td class="percorso"><a href="?idnews=<?= $row['idnews']; ?>"><?= $row['dir']; ?></a></td>
				<?= ($kaImpostazioni->getVar('news-commenti',1)=='s'?'<td class="percorso"><strong>'.$row['commentiOnline'].'</strong> / '.$row['commentiTot'].'</td>':''); ?>
				<?= (strpos($pageLayout,",date,")!==false?'<td><div class="data"><div class="giorno">'.substr($row['data'],8,2).' '.strftime("%b",mktime(1,0,0,substr($row['data'],5,2),1,substr($row['data'],0,4))).'</div><div class="ora">'.substr($row['data'],11,5).'</div></div></td>':''); ?>
				<?= (strpos($pageLayout,",public,")!==false?'<td><div class="data"><div class="giorno">'.substr($row['pubblica'],8,2).' '.strftime("%b",mktime(1,0,0,substr($row['pubblica'],5,2),1,substr($row['pubblica'],0,4))).'</div><div class="ora">'.substr($row['pubblica'],11,5).'</div></div></td>':''); ?>
				<?= (strpos($pageLayout,",startingdate,")!==false?'<td><div class="data"><div class="giorno">'.substr($row['starting_date'],8,2).' '.strftime("%b",mktime(1,0,0,substr($row['starting_date'],5,2),1,substr($row['starting_date'],0,4))).'</div><div class="ora">'.substr($row['starting_date'],11,5).'</div></div></td>':''); ?>
				<?= (strpos($pageLayout,",expiration,")!==false?'<td><div class="data"><div class="giorno">'.substr($row['scadenza'],8,2).' '.strftime("%b",mktime(1,0,0,substr($row['scadenza'],5,2),1,substr($row['scadenza'],0,4))).'</div><div class="ora">'.substr($row['scadenza'],11,5).'</div></div></td>':''); ?>
				<?php 
				echo '</tr>';
				}
			?></table>
			</div>
		<?php  }
	}

/****** EDIT SINGLE NEWS *******/
else {


	/*
	if the current language is different from the page language, means that the user have clicked on the flag and are requesting the translation of this page.
	- if the page has a translation in the requested language, edit the translation
	- if the page hasn't a translated version, create a new translate page
	*/
	$row=$kaNews->get($_GET['idnews']);
	if($_SESSION['ll']!=$row['ll']) {
		if(isset($row['traduzioni'][$_SESSION['ll']])&&$row['traduzioni'][$_SESSION['ll']]!="") $url="?idnews=".$row['traduzioni'][$_SESSION['ll']];
		else $url="new.php?translate=".$_GET['idnews'];
		?>
		<div class="MsgNeutral">
			<h2><?= $kaTranslate->translate('News:Searching for translation'); ?></h2>
			<a href="<?= $url; ?>"><?= $kaTranslate->translate('News:if nothing happens, click here'); ?></a>
			<meta http-equiv="refresh" content="0;URL='<?= $url; ?>'">
			</div>
		<?php 
		die();
		}


	/* AZIONI */
	if(isset($_POST['update'])&&isset($_GET['idnews'])) {
		$log="";

		/* update translation table in all involved pages (past and current) */
		if(isset($_POST['translation_id'])) {
			// translation has this format: |LL=idpag|LL=idpag|...
			$translations="";
			$_POST['translation_id'][$_SESSION['ll']]=$_GET['idnews'];
			foreach($_POST['translation_id'] as $k=>$v) {
				if($v!="") {
					$translations.=$k.'='.$v.'|';
					$kaNews->removePageFromTranslations($v);
					}
				}
			// first of all, clear translations from previous+current pages
			foreach($row['traduzioni'] as $k=>$v) {
				if($v!="") $kaNews->removePageFromTranslations($v);
				}
			// then set the new translations in the current pages
			foreach($_POST['translation_id'] as $k=>$v) {
				if($v!="") {
					$kaNews->setTranslations($v,$translations);
					}
				}
			}

		$vars=array("idnews"=>$_GET['idnews']);
		
		if(isset($_POST['date_day'])&&isset($_POST['date_hour'])) $vars['date_date']=preg_replace('/(\d{1,2})[^\d](\d{1,2})[^\d](\d{4})/','$3-$2-$1',$_POST['date_day']).' '.preg_replace('/(\d{1,2})[^\d](\d{1,2})/','$1:$2:00',$_POST['date_hour']);
		if(isset($_POST['visible_day'])&&isset($_POST['visible_hour'])) $vars['visible_date']=preg_replace('/(\d{1,2})[^\d](\d{1,2})[^\d](\d{4})/','$3-$2-$1',$_POST['visible_day']).' '.preg_replace('/(\d{1,2})[^\d](\d{1,2})/','$1:$2:00',$_POST['visible_hour']);
		if(isset($_POST['expiration_day'])&&isset($_POST['expiration_hour'])) $vars['expiration_date']=preg_replace('/(\d{1,2})[^\d](\d{1,2})[^\d](\d{4})/','$3-$2-$1',$_POST['expiration_day']).' '.preg_replace('/(\d{1,2})[^\d](\d{1,2})/','$1:$2:00',$_POST['expiration_hour']);
		if(isset($_POST['starting_day'])&&isset($_POST['starting_hour'])) $vars['starting_date']=preg_replace('/(\d{1,2})[^\d](\d{1,2})[^\d](\d{4})/','$3-$2-$1',$_POST['starting_day']).' '.preg_replace('/(\d{1,2})[^\d](\d{1,2})/','$1:$2:00',$_POST['starting_hour']);
		elseif(isset($vars['expiration_date'])) $vars['starting_date']=$vars['expiration_date'];

		if(isset($_POST['idcat'])) $vars['categories']=",".implode(",",$_POST['idcat']).",";
		else $vars['categories']=",";

		if(strpos($pageLayout,",home,")!=false) { isset($_POST['home']) ? $vars['home']='s' : $vars['home']='n'; }
		if(strpos($pageLayout,",calendario,")!=false) { isset($_POST['calendario']) ? $vars['calendar']='s' : $vars['calendar']='n'; }
		if(isset($_POST['titolo'])) $vars['title']=b3_htmlize($_POST['titolo'],false,"");
		if(isset($_POST['sottotitolo'])) $vars['subtitle']=b3_htmlize($_POST['sottotitolo'],false,"");
		if(isset($_POST['anteprima'])) $vars['preview']=b3_htmlize($_POST['anteprima'],false);
		if(isset($_POST['testo'])) $vars['text']=b3_htmlize($_POST['testo'],false);
		if(isset($_POST['dir'])) $vars['dir']=$_POST['dir'];
		if(isset($_POST['template'])) $vars['template']=$_POST['template'];
		if(isset($_POST['layout'])) $vars['layout']=$_POST['layout'];
		if(isset($_POST['featuredimage'])) $vars["featuredimage"]=$_POST['featuredimage'];
		if(isset($_POST['photogallery'])) $vars["photogallery"]=$_POST['photogallery'];
		$vars["online"]= (isset($_POST['online']) ? 'n' : 'y');

		$id=$kaNews->update($vars);

		if($id==false) $log="Problemi durante la modifica del database<br />";
		else {
			if(strpos($pageLayout,",seo,")!==false) {
				if(isset($_POST['seo_robots'])) $_POST['seo_robots']=implode(",",$_POST['seo_robots']);
				else $_POST['seo_robots']="";
				foreach($_POST as $ka=>$v) {
					if(substr($ka,0,4)=="seo_") $kaMetadata->set(TABLE_NEWS,$id,$ka,$v);
					}
				}
			}

		if($log!="") {
			echo '<div id="MsgAlert">'.$log.'</div>';
			$kaLog->add("ERR",'Errore nella modifica della news <em>'.b3_htmlize($_POST['titolo'],true,"").'</em>');
			}
		else {
			echo '<div id="MsgSuccess">'.$kaTranslate->translate('News:News successfully updated').'</div>';
			$kaLog->add("UPD",'Modificata la news: '.$_POST['titolo'].'');
			}
		}
	/* FINE AZIONI */


	$row=$kaNews->get($_GET['idnews']);

	?><form action="?idnews=<?= $row['idnews']; ?>" method="post" onsubmit="return checkForm();">
		<script style="text/javascript" src="<?= ADMINRELDIR; ?>js/comments.js"></script>
		<div class="subset">

		<?php  if(strpos($pageLayout,",date,")!==false) { ?>
			<fieldset class="box"><legend><?= $kaTranslate->translate('News:Created'); ?></legend>
				<?= b3_create_input("date_day","text"," ",preg_replace('/(\d{4}).(\d{2}).(\d{2}).*/','$3-$2-$1',$row['data']),"70px",250); ?> <?= b3_create_input("date_hour","text","alle ore ",preg_replace('/.*(\d{2}):(\d{2}):(\d{2})/','$1:$2',$row['data']),"40px",250); ?>
				</fieldset>
			<?php  } ?>
		<?php  if(strpos($pageLayout,",public,")!==false) { ?>
			<fieldset class="box"><legend><?= $kaTranslate->translate('News:Visible from'); ?></legend>
				<?= b3_create_input("visible_day","text"," ",preg_replace('/(\d{4}).(\d{2}).(\d{2}).*/','$3-$2-$1',$row['pubblica']),"70px",250); ?> <?= b3_create_input("visible_hour","text","alle ore ",preg_replace('/.*(\d{2}):(\d{2}):(\d{2})/','$1:$2',$row['pubblica']),"40px",250); ?>
				</fieldset>
			<?php  } ?>
		<?php  if(strpos($pageLayout,",startingdate,")!==false) { ?>
			<fieldset class="box"><legend><?= $kaTranslate->translate('News:Starting date'); ?></legend>
				<?= b3_create_input("starting_day","text"," ",preg_replace('/(\d{4}).(\d{2}).(\d{2}).*/','$3-$2-$1',$row['starting_date']),"70px",250); ?> <?= b3_create_input("starting_hour","text","alle ore ",preg_replace('/.*(\d{2}):(\d{2}):(\d{2})/','$1:$2',$row['starting_date']),"40px",250); ?>
				</fieldset>
			<?php  } ?>
		<?php  if(strpos($pageLayout,",expiration,")!==false) { ?>
			<fieldset class="box"><legend><?= $kaTranslate->translate('News:Expiration'); ?></legend>
				<?= b3_create_input("expiration_day","text"," ",preg_replace('/(\d{4}).(\d{2}).(\d{2}).*/','$3-$2-$1',$row['scadenza']),"70px",250); ?> <?= b3_create_input("expiration_hour","text","alle ore ",preg_replace('/.*(\d{2}):(\d{2}):(\d{2})/','$1:$2',$row['scadenza']),"40px",250); ?>
				</fieldset>
			<?php  } ?>
		<br />
		<?php  if(strpos($pageLayout,",home,")!==false) { ?>
			<div class="box"><?php 				echo b3_create_input("home","checkbox",$kaTranslate->translate("News:Show in home page"),"s","","",($row['home']=='s'?'checked':''));
				?></div>
			<br />
			<?php  } ?>

		<?php  if(strpos($pageLayout,",calendario,")!==false) { ?>
			<div class="box"><?php 				echo b3_create_input("calendario","checkbox","Mostra nel calendario ","s","","",($row['calendario']=='s'?'checked':''));
				?></div>
			<br />
			<?php  } ?>

		<?php  if(strpos($pageLayout,",categories,")!==false) { ?>
			<div class="box closed"><h2 onclick="kBoxSwapOpening(this.parentNode);"><?= $kaTranslate->translate('News:Categories'); ?></h2>
				<div id="categorie">Loading...</div>
				<script type="text/javascript" src="./ajax/categorie.js"></script>
				<script type="text/javascript">k_reloadCat(<?php  echo $row['idnews']; ?>);</script>
				</div>
			<br />
			<?php  } ?>

		<?php 
		if($kaImpostazioni->getVar('news-commenti',1)=='s'||$row['commentiTot']>0) { ?>
			<fieldset class="box"><legend><?= $kaTranslate->translate('News:Comments'); ?></legend>
				Questa notizia ha <?= $row['commentiTot']; ?> commenti<?php  if($row['commentiTot']-$row['commentiOnline']>0) echo ', di cui '.($row['commentiTot']-$row['commentiOnline']).' ancora da moderare'; ?>.<br />
				<a href="javascript:k_openIframeWindow(ADMINDIR+'inc/commentsManager.php?t=<?= TABLE_NEWS; ?>&id=<?= $row['idnews']; ?>','600px','400px')" class="smallbutton">Gestione commenti</a>
				</fieldset><br />
			<?php  } ?>

		<?php 
		if($kaImpostazioni->getVar('facebook',1)=='s') { ?>
			<fieldset class="box"><legend>Facebook</legend>
				<div class="newCat"><a href="javascript:k_openIframeWindow('ajax/facebook.php?id=<?= $row['idnews']; ?>','600px','500px')" class="smallbutton">Crea un evento da questa notizia</a></div>
				</fieldset><br />
			<?php  } ?>

		<?php  if(strpos($pageLayout,",featuredimage,")!==false) { ?>
			<fieldset class="box"><legend><?= $kaTranslate->translate('News:Featured Image'); ?></legend>
				<div id="featuredImageContainer"><?php 					if($row['featuredimage']>0)
					{
						$img=$kaImages->getImage($row['featuredimage']);
						?>
						<img src="<?= BASEDIR.$img['thumb']['url']; ?>">
						<?php 
					}
					?></div>
				<input type="hidden" name="featuredimage" id="featuredimage" value="<?= $row['featuredimage']; ?>">
				<a href="javascript:k_openIframeWindow('../inc/uploadsManager.inc.php?limit=1&submitlabel=<?= urlencode($kaTranslate->translate('Pages:Set featured image')); ?>&onsubmit=setFeaturedImage','90%','90%');" class="smallbutton"><?= $kaTranslate->translate('News:Choose featured image'); ?></a>
				<small><a href="javascript:removeFeaturedImage();" id="removeFeaturedImage" class="warning" <?php  if($row['featuredimage']==0) echo 'style="display:none;"'; ?>><?= $kaTranslate->translate('UI:Delete'); ?></a></small>
				</fieldset><br />
			<?php  } ?>
		</div>
		
		<div class="topset">
		<?php  if(strpos($pageLayout,",title,")!==false) {
			echo '<div class="title">'.b3_create_input("titolo","text",$kaTranslate->translate('News:Title')."<br />",b3_lmthize($row['titolo'],"input"),"70%",250).'</div>';
			}
		
		if(!isset($row['categorie'][0])) $row['categorie'][0]=array("dir"=>"");
		?>
		<div class="URLBox"><?= b3_create_input("dir","text",$kaTranslate->translate('News:Page URL').": ".BASEDIR.strtolower($_SESSION['ll'])."/".$kaImpostazioni->getVar('dir_news',1).'/[categoria]/',b3_lmthize($row['dir'],"input"),"400px",64,'onkeyup="checkURL(this)"'); ?> <span id="dirYetExists" style="display:none;">Questo indirizzo esiste gi&agrave;!</span> <a href="<?= BASEDIR.strtolower($_SESSION['ll'])."/".$kaImpostazioni->getVar('dir_news',1).'/'.$row['categorie'][0]['dir'].'/'.$row['dir'].'?preview='.md5(ADMIN_MAIL); ?>" target="_blank"><?= $kaTranslate->translate('UI:View'); ?></a></div>
		<script type="text/javascript">
			var target=document.getElementById('dir');
			target.setAttribute("oldvalue",target.value);
			</script>
		<br />

		<?php  if(strpos($pageLayout,",subtitle,")!==false) {
			echo b3_create_input("sottotitolo","text",$kaTranslate->translate('News:Subtitle')."<br />",b3_lmthize($row['sottotitolo'],"input"),"90%",250);
			echo '<br /><br />';
			} ?>

		<?php  if(strpos($pageLayout,",preview,")!==false) {
			echo b3_create_textarea("anteprima",$kaTranslate->translate('News:Introduction')."<br />",b3_lmthize($row['anteprima'],"textarea"),"100%","100px",RICH_EDITOR,true,TABLE_NEWS,$row['idnews']);
			echo '<br />';
			} ?>
	
		<?php  if(strpos($pageLayout,",text,")!==false) {
			echo b3_create_textarea("testo",$kaTranslate->translate('News:Contents')."<br />",b3_lmthize($row['testo'],"textarea"),"100%","300px",RICH_EDITOR,true,TABLE_NEWS,$row['idnews']);
			echo '<br />';
			} ?>

		<?php  if(strpos($pageLayout,",photogallery,")!==false) { ?>
			<div class="box <?= trim($row['photogallery'],",")=="" ? "closed" : "opened"; ?>"><h2 onclick="kBoxSwapOpening(this.parentNode);"><?= $kaTranslate->translate('UI:Photogallery'); ?></h2>
				<a href="javascript:k_openIframeWindow('../inc/uploadsManager.inc.php?submitlabel=<?= urlencode($kaTranslate->translate('UI:Add selected images to the list')); ?>&onsubmit=kAddImagesToPhotogallery','90%','90%');" class="smallbutton"><?= $kaTranslate->translate('UI:Add images to gallery'); ?></a>
				<div id="photogallery"></div>
				<script type="text/javascript">
					kLoadPhotogallery('<?= $row['photogallery']; ?>');
				</script>
			</div>
			<?php  } ?>

		<?php  if(strpos($pageLayout,",documentgallery,")!==false) { ?>
			<div class="box <?= count($row['docgallery'])==0?'closed':'opened'; ?>"><h2 onclick="kBoxSwapOpening(this.parentNode);"><?= $kaTranslate->translate('News:Document gallery'); ?></h2>
			<iframe src="<?php echo ADMINDIR; ?>inc/docgallery.inc.php?refid=docgallery&mediatable=<?php echo TABLE_NEWS; ?>&mediaid=<?php echo $row['idnews']; ?>" class="docsframe" id="docgallery" onload="kAutosizeIframe(this);"></iframe>
			</div>
			<?php  } ?>

		<?php  if(strpos($pageLayout,",seo,")!==false) {
			?><div class="box closed"><h2 onclick="kBoxSwapOpening(this.parentNode);"><?= $kaTranslate->translate('News:SEO'); ?></h2>
			<table>
				<tr>
					<td><label for="seo_changefreq"><?= $kaTranslate->translate('News:Change frequency'); ?></label></td>
					<td><select name="seo_changefreq" id="seo_changefreq">
						<?php 
						foreach(array(""=>"","always"=>$kaTranslate->translate('News:Always'),"hourly"=>$kaTranslate->translate('News:Hourly'),"daily"=>$kaTranslate->translate('News:Daily'),"weekly"=>$kaTranslate->translate('News:Weekly'),"monthly"=>$kaTranslate->translate('News:Monthly'),"yearly"=>$kaTranslate->translate('News:Yearly'),"never"=>$kaTranslate->translate('News:Never')) as $ka=>$v) {
							$md=$kaMetadata->get(TABLE_PAGINE,$row['idnews'],'seo_changefreq');
							?><option value="<?= $ka; ?>" <?= ($md['value']==$ka?'selected':''); ?>><?= $v; ?></option><?php 
							} ?>
						</select>&nbsp;&nbsp;&nbsp;&nbsp;
						</td>
					<td><label for="seo_title"><?= $kaTranslate->translate('News:Title'); ?></label></td>
					<td><input type="text" name="seo_title" id="seo_title" style="width:300px;" value="<?php  $md=$kaMetadata->get(TABLE_NEWS,$row['idnews'],'seo_title'); echo b3_lmthize($md['value'],"input"); ?>" /></td>
					</tr>
				<tr>
					<td><label for="seo_priority"><?= $kaTranslate->translate('News:Priority'); ?></label></td>
					<td><input type="text" name="seo_priority" id="seo_priority" style="width:50px;" value="<?php  $md=$kaMetadata->get(TABLE_NEWS,$row['idnews'],'seo_priority'); echo b3_lmthize($md['value'],"input"); ?>" /></td>
					<td><label for="seo_description"><?= $kaTranslate->translate('News:Description'); ?></label></td>
					<td><input type="text" name="seo_description" id="seo_description" style="width:300px;" value="<?php  $md=$kaMetadata->get(TABLE_NEWS,$row['idnews'],'seo_description'); echo b3_lmthize($md['value'],"input"); ?>" /></td>
					</tr>
				<tr><td colspan="2"></td>
					<td><label for="seo_keywords"><?= $kaTranslate->translate('News:Keywords'); ?></label></td>
					<td><input type="text" name="seo_keywords" id="seo_keywords" style="width:300px;" value="<?php  $md=$kaMetadata->get(TABLE_NEWS,$row['idnews'],'seo_keywords'); echo b3_lmthize($md['value'],"input"); ?>" /></td>
					</tr>
				<tr>
					<td colspan="2">
						<input type="checkbox" name="seo_robots[]" id="seo_robots_noindex" value="noindex" <?php  $md=$kaMetadata->get(TABLE_NEWS,$row['idnews'],'seo_robots'); if(strpos($md['value'],"noindex")!==false) { echo 'checked'; }; ?> /> <label for="seo_robots_noindex"><?= $kaTranslate->translate('News:No index'); ?></label>,
						<input type="checkbox" name="seo_robots[]" id="seo_robots_nofollow" value="nofollow" <?php  $md=$kaMetadata->get(TABLE_NEWS,$row['idnews'],'seo_robots'); if(strpos($md['value'],"nofollow")!==false) { echo 'checked'; }; ?>/> <label for="seo_robots_nofollow"><?= $kaTranslate->translate('News:No follow'); ?></label>,
						<input type="checkbox" name="seo_robots[]" id="seo_robots_noarchive" value="noarchive" <?php  $md=$kaMetadata->get(TABLE_NEWS,$row['idnews'],'seo_robots'); if(strpos($md['value'],"noarchive")!==false) { echo 'checked'; }; ?>/> <label for="seo_robots_noarchive"><?= $kaTranslate->translate('News:No archive'); ?></label>
						</td>
					<td><label for="seo_canonical">Canonical URL</label></td>
					<td><input type="text" name="seo_canonical" id="seo_canonical" style="width:300px;" value="<?php  $md=$kaMetadata->get(TABLE_NEWS,$row['idnews'],'seo_canonical'); echo b3_lmthize($md['value'],"input"); ?>" /></td>
					</tr>
				</table>
			</div><?php 
			} ?>

		<?php  if(strpos($pageLayout,",metadata,")!==false) {
			?><div class="box closed"><h2 onclick="kBoxSwapOpening(this.parentNode);">Meta-dati</h2>
			<div id="divMetadata"></div>
			<script type="text/javascript">kaMetadataReload('<?= TABLE_NEWS; ?>',<?= $row['idnews']; ?>);</script>
			<a href="javascript:kOpenIPopUp(ADMINDIR+'inc/ajax/metadataNew.php','t=<?= TABLE_NEWS; ?>&id=<?= $row['idnews']; ?>','600px','400px')" class="smallbutton">Nuovo meta-dato</a>
			</div>
			<?php  } ?>

		<?php  if(strpos($pageLayout,",template,")!==false||strpos($pageLayout,",layout,")!==false) { ?>
			<div class="box closed"><h2 onclick="kBoxSwapOpening(this.parentNode);">Template</h2>
			<?php  if(strpos($pageLayout,",template,")!==false) { ?>
				<?php 
				$option=array("");
				$value=array("-default-");
				foreach($kaImpostazioni->getTemplateList() as $file) {
					$option[]=$file;
					$value[]=str_replace("_"," ",$file);
					}
				echo b3_create_select("template","Template ",$value,$option,$row['template']);
				} ?>

			<?php  if(strpos($pageLayout,",layout,")!==false) { ?>
				<?php 
				$option=array("");
				$value=array("-default-");
				foreach($kaImpostazioni->getLayoutList() as $file) {
					$option[]=$file;
					$file=str_replace("_"," ",$file);
					$file=str_replace(".php"," ",$file);
					$file=str_replace(".html"," ",$file);
					$value[]=$file;
					}
				echo b3_create_select("layout","Layout ",$value,$option,$row['layout']);
				} ?>
				</div>
			<?php  } ?>

		<?php  if(strpos($pageLayout,",translate,")!==false) { ?>
			<div class="box closed"><h2 onclick="kBoxSwapOpening(this.parentNode);">Traduzioni</h2>
				<table><?php 
					$translation=array();
					$translation_id=array();
					$query_l="SELECT * FROM ".TABLE_LINGUE." WHERE ll<>'".$row['ll']."' ORDER BY `lingua`";
					$results_l=ksql_query($query_l);
					while($page_l=ksql_fetch_array($results_l)) {
						if(!isset($row['traduzioni'][$page_l['ll']])||$row['traduzioni'][$page_l['ll']]=="") {
							$translation[$page_l['ll']]="";
							$translation_id[$page_l['ll']]="";
							}
						else {
							$tmp=$kaNews->getTitleById($row['traduzioni'][$page_l['ll']]);
							$translation[$page_l['ll']]=$tmp['titolo'];
							$translation_id[$page_l['ll']]=$tmp['idnews'];
							}
						?>
						<tr>
						<td><label for="translation['<?= $page_l['ll']; ?>']"><strong><?= $page_l['lingua']; ?></strong></label></td>
						<td><div class="suggestionsContainer">
							<?= b3_create_input("translation[".$page_l['ll']."]","text","",$translation[$page_l['ll']],"200px",250,'autocomplete="off"'); ?>
							<?= b3_create_input("translation_id[".$page_l['ll']."]","hidden","",$translation_id[$page_l['ll']]); ?>
							<img src="<?= ADMINDIR; ?>img/close.png" alt="clear" width="12" height="12" id="translation_clear<?= $page_l['ll']; ?>" class="suggestionsClear" />
							<script type="text/javascript">translation<?= $page_l['ll']; ?>Handler=new kAutocomplete();translation<?= $page_l['ll']; ?>Handler.init('<?= $page_l['ll']; ?>');</script>
							</div></td>
						</tr>
						<?php  } ?>
					</table>
				</div>
			<?php  } ?>
		<br /><br />
	
		<div style="clear:both;"></div>
		<div class="submit">
			<input type="submit" name="update" value="<?= $kaTranslate->translate('UI:Save'); ?>" class="button" />
			<div class="draft"><?= b3_create_input("online","checkbox",$kaTranslate->translate('News:DRAFT'),'n',false,false,($row['online']=='y'?'':'checked')); ?></div>
		</div>
	</div>
</form>
	<?php 
	}

include_once("../inc/foot.inc.php");
