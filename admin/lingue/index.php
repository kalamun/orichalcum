<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

session_start();
if(isset($_SESSION['iduser'])) {
	/* AZIONI */
	if(isset($_GET['delete'])) {
		include('../inc/connect.inc.php');
		include('../inc/main.lib.php');
		$log="";
		$query="DELETE FROM ".TABLE_LINGUE." WHERE idli=".$_GET['delete'];
		if(!ksql_query($query)) $log="Problemi durante l'eliminazione della voce";
		}

	elseif(isset($_POST['save'])&&isset($_POST['lingua'])) {
		include('../inc/connect.inc.php');
		include('../inc/main.lib.php');
		foreach($_POST['lingua'] as $ka=>$v) {
			$query="UPDATE ".TABLE_LINGUE." SET ordine=".($ka+1)." WHERE idli=".$v." LIMIT 1";
			if(!ksql_query($query)) $log="Errore durante il salvataggio nel DB";
			}
		}

	elseif(isset($_POST['insert'])&&isset($_POST['lingua'])) {
		include('../inc/connect.inc.php');
		include('../inc/main.lib.php');
		$log="";
		$query="SELECT * FROM ".TABLE_LINGUE." ORDER BY ordine DESC LIMIT 1";
		$results=ksql_query($query);
		$row=ksql_fetch_array($results);
		$ordine=$row['ordine']+1;

		isset($_POST['online'])?$online='s':$online='n';
		isset($_POST['rtl'])?$rtl='s':$rtl='n';
		$query="INSERT INTO ".TABLE_LINGUE." (ll,lingua,code,online,rtl,ordine) VALUES('".b3_htmlize($_POST['ll'],true,"")."','".b3_htmlize($_POST['lingua'],true,"")."','".b3_htmlize($_POST['code'],true,"")."','".$online."','".$rtl."','".$ordine."')";
		if(!ksql_query($query)) $log="Errore durante l'inserimento nel database";
		
		// copia del config di default
		$query="SELECT * FROM ".TABLE_CONFIG." WHERE ll='".DEFAULT_LANG."'";
		$results=ksql_query($query);
		while($row=ksql_fetch_array($results)) {
			$q="SELECT * FROM ".TABLE_CONFIG." WHERE ll='".$_POST['ll']."' AND param='".$row['param']."' LIMIT 1";
			$rs=ksql_query($q);
			if(ksql_fetch_array($rs)==false) {
				$q="INSERT INTO ".TABLE_CONFIG." (param,value1,value2,ll) VALUES('".$row['param']."','".addslashes($row['value1'])."','".addslashes($row['value2'])."','".$_POST['ll']."')";
				ksql_query($q);
				}
			}
		}

	elseif(isset($_POST['update'])&&isset($_POST['lingua'])) {
		include('../inc/connect.inc.php');
		include('../inc/main.lib.php');
		$log="";

		isset($_POST['online'])?$online='s':$online='n';
		isset($_POST['rtl'])?$rtl='s':$rtl='n';
		$query="UPDATE ".TABLE_LINGUE." SET ll='".b3_htmlize($_POST['ll'],true,"")."',code='".b3_htmlize($_POST['code'],true,"")."',lingua='".b3_htmlize($_POST['lingua'],true,"")."',online='".$online."',rtl='".$rtl."' WHERE idli=".$_POST['idli'];
		if(!ksql_query($query)) $log="Errore durante il salvataggio nel Database";
		}

	/***/
	}

define("PAGE_NAME","Gestione delle lingue del sito");
define("PAGE_LEVEL",1);
include_once("../inc/head.inc.php");

if(isset($log)) {
	if($log=="") echo '<div id="MsgSuccess">Modifiche effettuate con successo</div>';
	else echo '<div id="MsgAlert">'.$log.'</div>';
	}

?><h1><?php  echo PAGE_NAME; ?></h1>
	<br />

	<script type="text/javascript" src="<?php  echo ADMINDIR; ?>/js/drag_and_drop.js"></script>
	<script type="text/javascript">
			kDragAndDrop=new kDrago();
			kDragAndDrop.dragClass="DragZone";
			kDragAndDrop.dropClass="DragZone";
			kDragAndDrop.containerTag('LI');
			kDragAndDrop.onDrag(function () {
				var drag=kDragAndDrop.getDragObject();
				var target=kDragAndDrop.getFlyingOver();
				var container=drag.parentNode.childNodes;
				for(var i=0;container[i];i++) {
					if(container[i]==drag) {
						target.parentNode.insertBefore(drag,target.nextSibling);
						break;
						}
					else if(container[i]==target) {
						target.parentNode.insertBefore(drag,target);
						break;
						}
					}
				kDragAndDrop.savePosition();
				});
			kDragAndDrop.onDrop(function (drag,target) {
				b3_openMessage('Salvataggio delle modifiche in corso');
				document.getElementById('saveOrder').submit();
				});

			function kSelectNewLang() {
				var s=document.getElementById('langTemplates').value;
				document.getElementById('lingua').value=s.substring(7);
				document.getElementById('ll').value=s.substring(0,2);
				document.getElementById('code').value=s.substring(2,7);
				}
		</script>
	
	<form action="?" method="post" id="saveOrder">
	<div class="DragZone">
	<ul class="dragdrop lingue"><?php 
		$query="SELECT * FROM ".TABLE_LINGUE." ORDER BY ordine";
		$results=ksql_query($query);
		$i=0;
		while($row=ksql_fetch_array($results)) {
			?><li><?php  echo '<strong>'.$row['lingua'].'</strong><div class="small">'.$row['ll'].' - '.($row['online']=='s'?'ONLINE':'OFFLINE').'</div>'; ?><input type="hidden" name="lingua[]" value="<?php  echo $row['idli']; ?>" /><span><a href="javascript:kOpenIPopUp('modifica.php','idli=<?php  echo $row['idli']; ?>','600px','400px')"><img src="<?php  echo ADMINRELDIR; ?>/img/12edit.gif" width="12" height="12" alt="modifica" /></a><a href="?delete=<?php  echo $row['idli']; ?>" onclick="return confirm('Sei sicuro di voler eliminare questa voce?');"><img src="<?php  echo ADMINRELDIR; ?>/img/12close.gif" width="12" height="12" alt="elimina" /></a></span></li><?php  $i++; }
		?></ul>
		</div>
	<br />
	<input type="hidden" name="save" value="Salva le modifiche" />
	</form>
	<br />
	<div class="submit"><input type="button" class="button" value="Aggiungi una lingua" onclick="kOpenIPopUp('nuovo.php','','600px','400px')" /></div>
<?php 	
include_once("../inc/foot.inc.php");
