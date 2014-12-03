<?php /* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Pages:Delete a page");
include_once("../inc/head.inc.php");


/* AZIONI */
if(isset($_GET['delete'])) {
	$query="SELECT * FROM ".TABLE_PAGINE." WHERE idpag='".intval($_GET['delete'])."' LIMIT 1";
	$results=ksql_query($query);
	$row=ksql_fetch_array($results);
	?>
	<h1><?= $kaTranslate->translate('Pages:You are going to delete the page "%s": are you sure?',$row['titolo']); ?></h1><br />
	<br />
	<form action="?" method="post">
	<input type="hidden" name="idpag" value="<?= intval($_GET['delete']); ?>" />
	<div class="submit">
		<input type="submit" name="delete" value="<?= $kaTranslate->translate('UI:Delete'); ?>" class="button" />
		<input type="submit" name="" value="<?= $kaTranslate->translate('UI:Cancel'); ?>" class="button" />
		</div>
	</form>
	<?php 
	include_once("../inc/foot.inc.php");
	die();
	}

elseif(isset($_POST['delete'])) {
	$log="";
	$query="SELECT * FROM ".TABLE_PAGINE." WHERE idpag='".intval($_POST['idpag'])."' LIMIT 1";
	$results=ksql_query($query);
	$old=ksql_fetch_array($results);
	
	if(isset($old['idpag'])&&$old['idpag']!="") {
		$query="DELETE FROM ".TABLE_PAGINE." WHERE idpag='".intval($old['idpag'])."' LIMIT 1";
		if(!ksql_query($query)) $log=$kaTranslate->translate('Pages:Errors occurred while deleting page from database');
		else {
			$id=$_POST['idpag'];
			//remove conversions
			$query="DELETE FROM ".TABLE_CONVERSIONS." WHERE idpag='".intval($old['idpag'])."'";
			ksql_query($query);
			//remove images and documents galleries (do not delete any file)
			$query="DELETE FROM ".TABLE_IMGALLERY." WHERE `tabella`='".TABLE_PAGINE."' AND `id`='".intval($old['idpag'])."'";
			ksql_query($query);
			$query="DELETE FROM ".TABLE_DOCGALLERY." WHERE `tabella`='".TABLE_PAGINE."' AND `id`='".intval($old['idpag'])."'";
			ksql_query($query);
			//delete comments
			$query="DELETE FROM ".TABLE_COMMENTI." WHERE `tabella`='".TABLE_PAGINE."' AND `id`='".intval($old['idpag'])."'";
			ksql_query($query);
			
			//remove from menu
			require_once('../menu/menu.lib.php');
			$kaMenu=new kaMenu();
			foreach($kaMenu->getMenuElementsByUrl(array("url"=>$old['dir'])) as $m) {
				$kaMenu->deleteElement(array('idmenu'=>$m['idmenu']));
				}

			}
		}

	if($log!="") {
		echo '<div id="MsgAlert">'.$log.'</div>';
		$kaLog->add("ERR",'Errore nell\'eliminazione della pagina <em>'.b3_htmlize($old['dir'],true,"").'</em>');
		}
	else {
		echo '<div id="MsgSuccess">'.$kaTranslate->translate('Pages:Page was successfully deleted').'</div>';
		$kaLog->add("DEL",'Eliminata la pagina: <a href="'.BASEDIR.strtolower($old['ll']).'/'.$old['dir'].'">'.$old['dir'].'</a>');
		}
	}
/***/

?><h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
	<br />
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
		</div>
		
	<div class="topset">
		<input type="hidden" id="usePage" />
		<table class="tabella">
		<tr><th><?= $kaTranslate->translate('Pages:Title'); ?></th><th><?= $kaTranslate->translate('Pages:Page URL'); ?></th></tr><?php 		$conditions="";
		if(isset($_GET['search'])) {
			$conditions.="(";
			$conditions.="titolo LIKE '%".b3_htmlize($_GET['search'],true,"")."%' OR ";
			$conditions.="sottotitolo LIKE '%".b3_htmlize($_GET['search'],true,"")."%' OR ";
			$conditions.="dir LIKE '%".b3_htmlize($_GET['search'],true,"")."%'";
			$conditions.=") AND ";
			}
		$conditions.="ll='".$_SESSION['ll']."'";
		$query="SELECT idpag,titolo,dir FROM ".TABLE_PAGINE." WHERE ".$conditions." ORDER BY titolo";
		$results=ksql_query($query);
		while($row=ksql_fetch_array($results)) { ?>
			<tr>
			<td><h2><a href="?delete=<?= $row['idpag']; ?>"><?= $row['titolo']; ?></a></h2>
				<small class="actions"><a href="?delete=<?= $row['idpag']; ?>" class="warning"><?= $kaTranslate->translate('Pages:Delete'); ?></a></small>
				</td>
			<td class="percorso"><a href="?idpag=<?= $row['idpag']; ?>"><?= $row['dir']; ?></a></td>
			</tr>
			<?php  } ?>
		</table>
		</div>
	</ul>
	
<?php 
include_once("../inc/foot.inc.php");
