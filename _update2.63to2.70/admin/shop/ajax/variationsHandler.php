<?php /* (c) Kalamun.org - GNU/GPL 3 */

require_once('../../inc/connect.inc.php');
require_once('../../inc/kalamun.lib.php');
require_once('../../inc/sessionmanager.inc.php');
require_once('../../inc/main.lib.php');
require_once('../../inc/config.lib.php');
if(!isset($_SESSION['iduser'])) die('Operation denied');

require_once('../shop.lib.php');
$kaShop=new kaShop();
$kaTranslate=new kaAdminTranslate();
$kaTranslate->import('shop');


/* PRINT THE LIST OF ALL VARIATIONS FOR DEFINED ITEM */
if(isset($_POST['getList'])&&isset($_POST['idsitem'])) {
	
	$variations=$kaShop->getVariations(array("idsitem"=>$_POST['idsitem']));
	
	if(count($variations)==0) echo $kaTranslate->translate('Shop:There are no variations for this item');

	else {
		$bkupcollection="";
		foreach($variations as $row) {
			if($bkupcollection!=$row['collection']) {
				if($bkupcollection!="") { ?></tbody></table><div style="clear:both;"></div><br /><?php  }
				?><div style="float:left;margin:10px 20px 10px 0;"><h3><?= $row['collection']; ?></h3></div><table class="tabella" style="float:left;margin:10px 20px 10px 0;"><tbody class="DragZone"><?php 
				} ?>
			<tr>
			<td><a href="javascript:k_openIframeWindow('ajax/variationsEdit.php?idsvar=<?= $row['idsvar']; ?>','800px','500px');"><?= $row['name']; ?></a></td>
			<td><?= $row['price']; ?></td>
			<td>
				<a href="javascript:k_openIframeWindow('ajax/variationsEdit.php?idsvar=<?= $row['idsvar']; ?>','800px','500px');" class="smallbutton"><?= $kaTranslate->translate('UI:Edit'); ?></a>
				<a href="javascript:k_deleteVariation('<?= $row['idsvar']; ?>');" class="smallbutton" style="background-color:#ca0000;"><?= $kaTranslate->translate('UI:Delete'); ?></a>
				</td>
			<td class="sposta"><input type="hidden" name="idsvarOrder[<?= $row['collection']; ?>][]" value="<?= $row['idsvar']; ?>" /><img src="<?= ADMINDIR; ?>img/drag_v.gif" width="18" height="18" alt="Sposta" /> <?= $kaTranslate->translate('UI:Move'); ?></td>
			</tr>
			<?php 
			$bkupcollection=$row['collection'];
			}
		?></tbody></table><div style="clear:both;"></div><?php 

		}

	}

/* DELETE A VARIATION BY ID */
elseif($_POST['delete']) {
	$kaShop->deleteVariation($_POST['delete']);
	}
