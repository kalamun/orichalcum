<?
session_start();
if(!isset($_SESSION['iduser'])) die();
include('../../inc/connect.inc.php');
include('../../inc/kalamun.lib.php');
include('../../inc/main.lib.php');
include('../../inc/categorie.lib.php');

$kaCategorie=new kaCategorie();

$catSel=array();
if($_POST['idphg']>0) {
	$query="SELECT `categories` FROM `".TABLE_PHOTOGALLERY."` WHERE `idphg`='".intval($_POST['idphg'])."' LIMIT 1";
	$results=mysql_query($query);
	$row=mysql_fetch_array($results);
	foreach(explode(",",$row['categories']) as $idcat) {
		if($idcat!="") $catSel[$idcat]=true;
		}
	}

?>

<ul class="catList"><?

function printSubcat($cat) {
	global $categorie;
	global $kaTranslate;
	global $catSel;
	if($_POST['idphg']==0&&count($catSel)==0) $catSel[$cat['data']['idcat']]=true;
	?>
	<li>
		<?= b3_create_input("idcat[]","checkbox",$cat['data']['categoria'],$cat['data']['idcat'],"","",(isset($catSel[$cat['data']['idcat']])?'checked':''),true); ?></td>
		<?
		if(count($cat)>1) {
			?><ul><?
			foreach($cat as $ka=>$v) {
				if(is_numeric($ka)) {
					printSubcat($v);
					}
				}
			?></ul><?
			} ?>
		</li>
	<?
	}

$categorie=$kaCategorie->getStructuredList(TABLE_PHOTOGALLERY);
foreach($categorie as $cat) {
	printSubcat($cat);
	}
?>
</table>

