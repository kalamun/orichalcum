<?php 
session_start();
include('../../inc/connect.inc.php');
include('../../inc/kalamun.lib.php');
include('../../inc/main.lib.php');
include('../../inc/categorie.lib.php');

$kaCategorie=new kaCategorie();

$catSel=array();
if($_POST['idbanner']>0) {
	$query="SELECT categoria FROM ".TABLE_BANNER." WHERE idbanner='".$_POST['idbanner']."' LIMIT 1";
	$results=ksql_query($query);
	$row=ksql_fetch_array($results);
	$catSel[$row['categorie']]=true;
	}

?>
<table class="catList"><?php 
foreach($kaCategorie->getList(TABLE_BANNER) as $cat) {
	if($_POST['idbanner']==0&&count($catSel)==0) $catSel[$cat['idcat']]=true;
	?>
	<tr style="background-color:#eee">
	<td><?= b3_create_input("idcat","radio",$cat['categoria'],$cat['idcat'],"","",(isset($catSel[$cat['idcat']])?'checked':''),true); ?></td>
	</tr>
	<?php  }
?>
</table>

<?php  /* <div class="newCat">
<input type="text" name="nuovaCategoria" id="nuovaCategoria" onkeydown="k_keypressCat(event);" /><input type="button" value="Nuova" class="smallbutton" onclick="k_nuovaCat();" />
</div> */ 