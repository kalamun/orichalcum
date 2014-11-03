<?php 
session_start();
include('../../inc/connect.inc.php');
include('../../inc/kalamun.lib.php');
include('../../inc/main.lib.php');
include('../../inc/categorie.lib.php');

$kaCategorie=new kaCategorie();

$catSel=array();
if($_POST['idnews']>0) {
	$query="SELECT `categorie` FROM `".TABLE_NEWS."` WHERE `idnews`='".mysql_real_escape_string($_POST['idnews'])."' LIMIT 1";
	$results=mysql_query($query);
	$row=mysql_fetch_array($results);
	foreach(explode(",",$row['categorie']) as $idcat) {
		if($idcat!="") $catSel[$idcat]=true;
		}
	}

?>
<table class="catList"><?php 
foreach($kaCategorie->getList(TABLE_NEWS) as $cat) { ?>
	<tr>
	<td><?= b3_create_input("idcat[]","checkbox",$cat['categoria'],$cat['idcat'],"","",(isset($catSel[$cat['idcat']])?'checked':''),true); ?></td>
	<td><a href="javascript:k_deleteCat(<?= $cat['idcat']; ?>);" onclick="return confirm('Sei sicuro di voler eliminare questa categoria?\nVerrà eliminata da TUTTE le news!');"><img src="<?= ADMINDIR; ?>img/close.png" width="12" height="12" alt="Cancella categoria" /></a></td>
	</tr>
	<?php  }
?>
</table>

<div class="newCat">
<input type="text" name="nuovaCategoria" id="nuovaCategoria" onkeydown="k_keypressCat(event);" /><input type="button" value="Nuova" class="smallbutton" onclick="k_nuovaCat();" />
</div>