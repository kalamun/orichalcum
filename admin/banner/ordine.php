<?php 
define("PAGE_NAME","Modifica l'ordine dei banner");
define("PAGE_LEVEL",2);
include_once("../inc/head.inc.php");

define("BANNER_REF","home");

/* AZIONI */
if(isset($_POST['save'])) {
	$log="";
	foreach($_POST['foto'] as $ka=>$v) {
		$query="UPDATE ".TABLE_BANNER." SET ordine=".($ka+1)." WHERE idbanner=".$v." LIMIT 1";
		if(!ksql_query($query)) $log="Errore durante il salvataggio nel DB";
		}
	if($log!="") echo '<div id="MsgAlert">'.$log.'</div>';
	else echo '<div id="MsgSuccess">Ordine dei banner salvato con successo</div>';
	}
/* FINE AZIONI */



?>
<h1><?php  echo PAGE_NAME; ?></h1>
<br />

	<script type="text/javascript" src="<?php  echo ADMINDIR; ?>/js/drag_and_drop.js"></script>
	<script type="text/javascript">
			kDragAndDrop=new kDrago();
			kDragAndDrop.dragClass="DragZone";
			kDragAndDrop.dropClass="DragZone";
			kDragAndDrop.containerTag('DIV');
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
				document.getElementById('saveOrder').style.display='block';
				});
		</script>

	<form action="" method="post">
	<div class="dragdrop">
		<div class="DragZone"><?php 
			$query="SELECT * FROM ".TABLE_BANNER." WHERE ref='".BANNER_REF."' ORDER BY ordine";
			$results=ksql_query($query);
			for($i=0;$row=ksql_fetch_array($results);$i++) {
				$query_i="SELECT * FROM ".TABLE_IMG." WHERE tabella='".TABLE_BANNER."' AND id='".$row['idbanner']."' LIMIT 1";
				$results_i=ksql_query($query_i);
				$row_i=ksql_fetch_array($results_i);
				$filename=DIR_IMG.$row_i['idimg'].'/'.$row_i['filename'];
				?><div><input type="hidden" name="foto[]" value="<?php  echo $row['idbanner']; ?>" /><?php 
				if(file_exists(BASERELDIR.$filename)) echo '<img src="'.BASERELDIR.$filename.'" alt="'.str_replace('"','&quot;',trim(strip_tags($row_i['alt']))).'" />';
				?></div><?php 
				}
			?></div>
		</div>
	<br />
	<br />
	
	<div class="submit" id="saveOrder" style="display:none;">
		<input type="submit" name="save" class="button" value="Salva le modifiche" />
		</div>
	</form>
<?php 
include_once("../inc/foot.inc.php");
