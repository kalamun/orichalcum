<?php 
session_start();
include('../../inc/connect.inc.php');
include('../../inc/kalamun.lib.php');
include('../../inc/main.lib.php');
include('../../inc/categorie.lib.php');

$kaCategorie=new kaCategorie();

$catSel=array();
if($_POST['idpag']>0) {
	$query="SELECT `categorie` FROM `".TABLE_PAGINE."` WHERE `idpag`='".ksql_real_escape_string($_POST['idpag'])."' LIMIT 1";
	$results=ksql_query($query);
	$row=ksql_fetch_array($results);
	foreach(explode(",",$row['categorie']) as $idcat) {
		if($idcat!="") $catSel[$idcat]=true;
		}
	}

?>
<table class="catList"><?php 
foreach($kaCategorie->getList(TABLE_PAGINE) as $cat) {
	?>
	<tr>
	<td><?= b3_create_input("idcat[]","checkbox",$cat['categoria'],$cat['idcat'],"","",(isset($catSel[$cat['idcat']])?'checked':''),true); ?></td>
	<td><a href="javascript:k_deleteCat(<?= $cat['idcat']; ?>);" onclick="return confirm('Sei sicuro di voler eliminare questa categoria?\nVerrà eliminata da TUTTE le news!');"><img src="<?= ADMINDIR; ?>img/close.png" width="12" height="12" alt="X" /></a></td>
	</tr>
	<?php  }
?>
</table>

<div class="newCat">
<input type="text" name="nuovaCategoria" id="nuovaCategoria" onkeydown="k_keypressCat(event);" /><input type="button" value="Nuova" class="smallbutton" onclick="k_nuovaCat();" />
</div>