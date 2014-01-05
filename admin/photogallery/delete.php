<?php
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Elimina una Galleria Fotografica");
define("PAGE_LEVEL",1);
include_once("../inc/head.inc.php");
require_once('../inc/images.lib.php');
$kaImages=new kaImages();
include_once("./photogallery.lib.php");
$kaPhotogallery=new kaPhotogallery();

?>

<h1><?php echo PAGE_NAME; ?></h1>
<br />

<?

if(isset($_GET['delete'])) {
	$row=$kaPhotogallery->getById($_GET['delete']);
	?>
	<h1>Sei sicuro di voler eliminare la galleria "<em><?php echo $row['titolo']; ?></em>"?</h1>
	
	<form action="?" method="post"><?php
	echo b3_create_input("idphg","hidden","",$_GET['delete']);
	echo b3_create_input("delimg","checkbox","Rimuovi tutte le immagini legate a questa pagina","");

	$usage=array();
	$alert=false;
	$immagini=$kaImages->getList(TABLE_PHOTOGALLERY,$_GET['delete']);
	foreach($immagini as $img) {
		$u=$kaImages->usage($img['idimg']);
		if(count($u)>1) $alert=true;
		}
	if($alert==true) { ?>
		<div class="note">
		<strong>Attenzione!</strong> Le immagini di questa pagina sono utilizzate anche in altre pagine! Eliminandole, verranno rimosse da tutte le pagine del sito!<br />
		Considera che puoi eliminarle singolarmente in qualsiasi momento, anche se non le rimuovi subito...
		</div>
		<?php }

	echo '<br />';
	?>

	<br /><br />
	<div class="submit">
		<input type="submit" name="delete" value="Elimina" class="button" />
		<input type="submit" name="" value="Annulla" class="button" />
		</div>
	</form>
	<?php 
	include_once("../inc/foot.inc.php");
	die();
	}

else {
	if(isset($_POST['delete'])) {
		$log="";
		$old=$kaPhotogallery->getById($_POST['idphg']);
		
		if(isset($_POST['delimg'])) $_POST['delimg']=true;
		else $_POST['delimg']=false;

		if(!$kaPhotogallery->delById($_POST['idphg'],$_POST['delimg'])) $log="Errore durante l'eliminazione della galleria";

		if($log!="") {
			echo '<div id="MsgAlert">'.$log.'</div>';
			$kaLog->add("ERR",'Errore nell\'eliminazione della galleria fotografica <em>'.b3_htmlize($old['titolo'],true,"").'</em> (<em>ID: '.$_POST['delete'].'</em>)');
			}
		else {
			echo '<div id="MsgSuccess">Galleria eliminata con successo</div>';
			$kaLog->add("DEL",'Eliminata la galleria fotografica <a href="'.BASEDIR.strtolower($old['ll']).'/'.$old['dir'].'">'.$old['titolo'].'</a>  (<em>ID: '.$_POST['delete'].'</em>)');
			}
		}

	?>
	<div class="subset">
		<fieldset class="box"><legend>Cerca</legend>
		<input type="text" name="search" id="searchQ" style="width:180px;" value="<? if(isset($_GET['search'])) echo str_replace('"','&quot;',$_GET['search']); ?>" />
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
			function showActions(td) {
				for(var i=0;td.getElementsByTagName('DIV')[i];i++) {
					td.getElementsByTagName('DIV')[i].style.visibility='visible';
					}
				}
			function hideActions(td) {
				for(var i=0;td.getElementsByTagName('DIV')[i];i++) {
					td.getElementsByTagName('DIV')[i].style.visibility='hidden';
					}
				}
			</script>
		</div>
		
	<div class="topset">
		<input type="hidden" id="usePage" />
		<table class="tabella">
		<tr><th>Galleria</th><th>Indirizzo</th></tr><?php
		$conditions="";
		if(isset($_GET['search'])) {
			$conditions.="titolo LIKE '%".b3_htmlize($_GET['search'],true,"")."%' OR ";
			$conditions.="testo LIKE '%".b3_htmlize($_GET['search'],true,"")."%' OR ";
			$conditions.="dir LIKE '%".b3_htmlize($_GET['search'],true,"")."%'";
			}
		
		$list=$kaPhotogallery->getList($conditions);
		foreach($list as $ka=>$g) {
			echo '<tr>';
			echo '<td onmouseover="showActions(this)" onmouseout="hideActions(this)"><h2><a href="?idphg='.$g['idphg'].'">'.$g['titolo'].'</a></h2>';
				echo '<div class="small" style="visibility:hidden;"><a href="?delete='.$g['idphg'].'">Elimina</a> | <a href="'.SITE_URL.'/'.strtolower($_SESSION['ll'])."/".$kaImpostazioni->getVar('dir_photogallery',1)."/".$g['dir'].'">Visita</a></div>';
				echo '</td>';
			echo '<td class="percorso"><a href="?idphg='.$g['idphg'].'">'.$g['dir'].'</a></td>';
			echo '</tr>';
			}
		?></table>
		</div>
	<?
	} ?>
	
<?php	
include_once("../inc/foot.inc.php");
?>
