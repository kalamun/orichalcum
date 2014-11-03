<?php 
define("PAGE_NAME","Banner:Delete a banner");
include_once("../inc/head.inc.php");
include_once("./banner.lib.php");
include('../inc/categorie.lib.php');
$kaBanner=new kaBanner();
$kaCategorie=new kaCategorie();


/* AZIONI */
if(isset($_GET['delete'])) {
	$log="";
	if(!$kaBanner->delete($_GET['delete'])) $log=$kaTranslate->translate("Banner:Problems occurred while deleting banner");

	if($log!="") {
		echo '<div id="MsgAlert">'.$log.'</div>';
		$kaLog->add("ERR",'Errore nell\'eliminazione del banner <em>ID: '.$_GET['delete'].'</em>');
		}
	else {
		echo '<div id="MsgSuccess">'.$kaTranslate->translate('Banner:Successfully Deleted').'</div>';
		$kaLog->add("DEL",'Eliminato il banner: <em>ID: '.$_GET['delete'].'</em>');
		}
	}
/* FINE AZIONI */
?>

<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
<br />

<div class="tab"><dl>
	<?php 	foreach($kaCategorie->getList(TABLE_BANNER) as $c) {
		if(!isset($_GET['idcat'])) $_GET['idcat']=$c['idcat'];
		?>
		<dt>
			<a href="?idcat=<?= urlencode($c['idcat']); ?>" class="<?= ($c['idcat']==$_GET['idcat']?'sel':''); ?>"><?= $c['categoria']; ?></a>
			</dt>
		<?php } ?>
	</dl></div>
<br />

<div>
	<form action="" method="post" id="orderby">
		<table class="tabella">
		<thead><tr><th><?= $kaTranslate->translate('Banner:Title'); ?></th><th><?= $kaTranslate->translate('Banner:Target URL'); ?></th><th><?= $kaTranslate->translate('Banner:Views'); ?></th></thead>
		<tbody  class="DragZone">
		<?php 
			foreach($kaBanner->getList($_GET['idcat']) as $banner) {
				?><tr>
					<td><a href="?idcat=<?= $_GET['idcat']; ?>&delete=<?= $banner['idbanner']; ?>" onclick="return confirm('Sei sicuro di voler cancellare il banner?');"><?php  echo $banner['title']; ?></a>
						<?php  if($banner['online']=='n') echo '<small class="alert">'.$kaTranslate->translate('Banner:DRAFT').'</small>'; ?><br />
						<small class="actions"><a href="?idcat=<?= $_GET['idcat']; ?>&delete=<?= $banner['idbanner']; ?>" onclick="return confirm('Sei sicuro di voler cancellare il banner?');"><?= $kaTranslate->translate('UI:Delete'); ?></a></small>
					<td class="percorso"><?= $banner['url']; ?></td>
					<td class="views"><?= $banner['views']; ?></td>
					</tr>
					<?php 
				}
			?></tbody></table>
		</form>
	</div>

<?php 
include_once("../inc/foot.inc.php");
