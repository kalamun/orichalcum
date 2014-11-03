<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Utenti pubblici");
include_once("../inc/head.inc.php");
include_once("./users.lib.php");
$kaUsers=new kaUsers();

define("PARAM","utenti_pubblici");

/* ACTIONS */
if(isset($_POST['update'])) {
	$usersList=",";
	if(isset($_POST['users'])) {
		foreach($_POST['users'] as $u) {
			$usersList.=$u.',';
			}
		}
	$kaImpostazioni->setParam(PARAM,$usersList,"");
	}
/**/

?>
<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
<br />
Scegli dai seguenti utenti quelli che sono pubblici e quindi possono avere la scheda visibile nel sito:<br />
<br />
<?php 
$cfg=$kaImpostazioni->getParam(PARAM);
$checkedusers=explode(",",trim($cfg['value1'],","));
?>
<form action="?" method="post">

	<script type="text/javascript" src="<?= ADMINDIR; ?>/js/drag_and_drop.js"></script>
	<script type="text/javascript">
		kDragAndDrop=new kDrago();
		kDragAndDrop.dragClass("DragZone");
		kDragAndDrop.dropClass("DragZone");
		kDragAndDrop.containerTag('TR');
		kDragAndDrop.onDrag(function (drag,target) {
			var container=drag.parentNode.childNodes;
			if(target.className!='DragZone'&&target!=drag) {
				if((parseInt(target.getAttribute("ddTop"))+target.offsetHeight/2)>kWindow.mousePos.y) target.parentNode.insertBefore(drag,target);
				else target.parentNode.insertBefore(drag,target.nextSibling);
				}
			kDragAndDrop.savePosition();
			});
		kDragAndDrop.onDrop(function (drag,target) {
			});
		</script>
	
	<div>
		<table class="tabella">
		<tbody  class="DragZone">
			<?php 
			//show before checked user, in the right order
			$users=$kaUsers->getUsersList();
			foreach($checkedusers as $iduser) {
				foreach($users as $user) {
					if($user['iduser']==$iduser) kaPrintUser($user,true);
					}
				}
			//then show the other users, in alphabetical order
			foreach($users as $user) {
				$display=true;
				foreach($checkedusers as $iduser) {
					if($user['iduser']==$iduser) $display=false;
					}
				if($display) kaPrintUser($user);
				}

			function kaPrintUser($user,$checked=false) {
				?>
				<tr>
				<td><?= b3_create_input("users[]","checkbox",$user['name'],$user['iduser'],"","",($checked==true?'checked':''),true); ?></td>
				<td class="percorso"><?= $user['username']; ?></td>
				<td class="sposta"><img src="<?= ADMINRELDIR; ?>img/drag_v.gif" width="18" height="18" alt="Sposta" /> Sposta</td>
				</tr>
				<?php  }
				?>
			</tbody></table>
	<br />
	<br />
	<div class="submit"><input type="submit" name="update" value="<?= $kaTranslate->translate('UI:Save'); ?>" class="button"></div>
</form>
<br /><br />

<?php 
include_once("../inc/foot.inc.php");
