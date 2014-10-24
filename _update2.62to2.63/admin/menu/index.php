<?
/* (c) Kalamun.org - GNU/GPL 3 */

if(!isset($_GET['collection'])) $_GET['collection']="";

define("PAGE_NAME","Menu:Navigation menu management");
include_once("../inc/head.inc.php");
include_once("../inc/images.lib.php");
include_once("./menu.lib.php");

$kaMenu=new kaMenu();
$kaImages=new kaImages();

$kaMenu->setCollection($_GET['collection']);

/* AZIONI */
if(isset($_POST['save'])&&isset($_POST['menu'])) {
	$log="";
	foreach($_POST['menu'] as $ka=>$v) {
		if(!isset($ordine[$_POST['ref'][$ka]])) $ordine[$_POST['ref'][$ka]]=0;
		$ordine[$_POST['ref'][$ka]]++;
		$query="UPDATE ".TABLE_MENU." SET `ordine`=".($ordine[$_POST['ref'][$ka]]).",`ref`='".$_POST['ref'][$ka]."' WHERE `idmenu`='".$v."' AND `ll`='".$_SESSION['ll']."' LIMIT 1";
		if(!mysql_query($query)) $log="Errore durante il salvataggio nel DB";
		}
	if($log=="") echo '<div id="MsgSuccess">Men&ugrave; salvato con successo</div>';
	else echo '<div id="MsgAlert">'.$log.'</div>';
	}
elseif(isset($_GET['delete'])) {
	$log="";
	if($kaMenu->deleteElement(array('idmenu'=>$_GET['delete']))) {
		echo '<div id="MsgSuccess">Voce del men&ugrave; eliminata con successo</div>';
		}
	else {
		echo '<div id="MsgAlert">'.$log.'</div>';
		}
	}
elseif(isset($_POST['insert'])) {
	$log="";
	$query="SELECT ordine FROM ".TABLE_MENU." WHERE `ll`='".$_SESSION['ll']."' AND `ref`='0' ORDER BY ordine DESC LIMIT 1";
	$results=mysql_query($query);
	$row=mysql_fetch_array($results);
	$ordine=$row['ordine']+1;

	$query="INSERT INTO ".TABLE_MENU." (label,url,ref,ll,ordine) VALUES('".b3_htmlize($_POST['label'],true,"")."','".b3_htmlize($_POST['url'],true,"")."','0','".$_SESSION['ll']."','".$ordine."')";
	if(!mysql_query($query)) $log="Errore durante il salvataggio nel Database";
	else $id=mysql_insert_id();

	if($_FILES['img']['name']!="") $kaImages->upload($_FILES['img']['tmp_name'],$_FILES['img']['name'],TABLE_MENU,$id,$_POST['label'],false);

	if($log=="") echo '<div id="MsgSuccess">Men&ugrave; salvato con successo</div>';
	else echo '<div id="MsgAlert">'.$log.'</div>';
	}
elseif(isset($_GET['newCollection'])&&$_GET['collection']!="") {
	$log="";
	$query="INSERT INTO ".TABLE_MENU." (collection,label,url,ref,ll,ordine) VALUES('".mysql_real_escape_string($_GET['collection'])."','###placeholder###','#','0','##','1')";
	if(!mysql_query($query)) $log="Errore durante il salvataggio nel Database";
	else $id=mysql_insert_id();

	if($log=="") echo '<div id="MsgSuccess">Men&ugrave; creato con successo</div>';
	else echo '<div id="MsgAlert">'.$log.'</div>';
	}
elseif(isset($_GET['deleteCollection'])&&$_GET['collection']!="") {
	$log="";
	$query="DELETE FROM ".TABLE_MENU." WHERE collection='".mysql_real_escape_string($_GET['collection'])."'";
	if(!mysql_query($query)) $log="Errore durante la rimozione dal Database";
	else $id=mysql_insert_id();

	if($log=="") {
		echo '<div id="MsgSuccess">Men&ugrave; rimosso con successo</div>';
		$kaMenu->setCollection('');		
		$_GET['collection']="";
		}
	else echo '<div id="MsgAlert">'.$log.'</div>';
	}

/***/

?><h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
	<br />
	<?php /* COLLECTIONS */ ?>
	<div class="tab"><dl>
		<?php
		foreach($kaMenu->getCollections() as $c) { ?>
			<dt class="<?= ($c!=""&&$_GET['collection']==$c?'haveActions':''); ?>">
				<a href="?collection=<?= urlencode($c); ?>" class="<?= ($_GET['collection']==$c?'sel':''); ?>"><?= $c==""?$kaTranslate->translate('Menu:Main menu'):$c; ?></a>
				<?php if($c!=""&&$_GET['collection']==$c) { ?><a href="?collection=<?= urlencode($c); ?>&deleteCollection" class="actions" onclick="return confirm('Sicuro di voler cancellare questo menu?');"><img src="<?= ADMINDIR; ?>img/del.png" /></a><?php } ?>
				</dt>
			<?php } ?>
		<dt><a href="javascript:kOpenIPopUp('ajax/newCollection.php','','400px','200px')"><?= $kaTranslate->translate('Menu:Add new menu'); ?></a></dt>
		</dl></div>
	<br />

	<script type="text/javascript" src="<? echo ADMINDIR; ?>/js/drag_and_drop.js"></script>
	<script type="text/javascript">
		kDragAndDrop=new kDrago();
		kDragAndDrop.dragClass("DragZone");
		kDragAndDrop.dropClass("DragZone");
		kDragAndDrop.containerTag('LI');
		kDragAndDrop.addDropTag('LI');
		kDragAndDrop.addDropTag('UL');
		kDragAndDrop.onDrag(function (drag,target) {
			document.getElementById('orderby').className='ondrag';
			var container=drag.parentNode.childNodes;
			if(target.className!='DragZone'&&target!=drag) {
				if(target.tagName=='LI') {
					if((parseInt(target.getAttribute("ddTop"))+target.childNodes[0].offsetHeight/2)>kWindow.mousePos.y) target.parentNode.insertBefore(drag,target);
					else target.parentNode.insertBefore(drag,target.nextSibling);
					}
				else if(target.tagName=='UL') target.appendChild(drag);
				}
			kDragAndDrop.savePosition();
			});
		kDragAndDrop.onDrop(function (drag,target) {
			document.getElementById('orderby').className='';
			var ref=0;
			for(var i=0;drag.parentNode.parentNode.childNodes[0].childNodes[i];i++) {
				if(drag.parentNode.parentNode.childNodes[0].childNodes[i].name=="menu[]") {
					ref=drag.parentNode.parentNode.childNodes[0].childNodes[i].value;
					break;
					}
				}
			for(var i=0;drag.getElementsByTagName('INPUT')[i];i++) {
				if(drag.getElementsByTagName('INPUT')[i].name=="ref[]") {
					drag.getElementsByTagName('INPUT')[i].value=ref;
					break;
					}
				}
			});
		
		function saving() {
			b3_openMessage('saving...',false);
			//document.getElementById('orderby').submit();
			}
		</script>

	
	
	<form action="" method="post" id="orderby" onsubmit="saving();">
		<div class="dragdrop">
			<ul class="DragZone"><?
				$menu=$kaMenu->getMenuContents();
				foreach($kaMenu->getMenuStructure(0) as $m) {
					printSubmenu($m);
					}

				function printSubmenu($m) {
					global $menu;
					global $kaTranslate;
					?><li><div class="elm">
					<strong><?= $menu[$m['data']]['label']; ?></strong><br />
					<small><?= $menu[$m['data']]['url']; ?>&nbsp;</small>
					<input type="hidden" name="menu[]" value="<?= $menu[$m['data']]['idmenu']; ?>" />
					<input type="hidden" name="ref[]" value="<?= $menu[$m['data']]['ref']; ?>" />
					<span style="text-align:right;" class="actions">
						<a href="edit.php?collection=<?= urlencode($_GET['collection']); ?>&idmenu=<?= $menu[$m['data']]['idmenu']; ?>" class="smallbutton"><?= $kaTranslate->translate('UI:Edit'); ?></a>
						<a href="?collection=<?= urlencode($_GET['collection']); ?>&delete=<?= $menu[$m['data']]['idmenu']; ?>" onclick="return confirm('Sei sicuro di voler eliminare questa voce?');" class="smallalertbutton"><?= $kaTranslate->translate('UI:Delete'); ?></a>
					</span>
					</div>
					<ul><?
					if(count($m)>1) {
						foreach($m as $ka=>$v) {
							if($ka!='data') printSubmenu($m[$ka]);
							}
						} ?>
					</ul></li><?
					}

				?></ul>
			</div>
		<br />
		<a href="new.php?collection=<?= urlencode($_GET['collection']); ?>" class="smallbutton"><?= $kaTranslate->translate('Menu:Add a new element'); ?></a>
		<br />
		<br />
		
		<div class="submit" id="submit">
			<input type="submit" name="save" class="button" value="<?= $kaTranslate->translate('Menu:Save order'); ?>" />
			</div>
	</form>
<?	
include_once("../inc/foot.inc.php");
?>
