<?php 
session_start();
if(!isset($_SESSION['iduser'])) die();

require_once('../main.lib.php');
$kaOrichalcum=new kaOrichalcum();
$kaOrichalcum->init( array("check-permissions"=>false, "x-frame-options"=>"") );

?>
<table class="metadataList">
<tr><th>Parametro</th><th>Valore</th></tr><?php 
$i=0;
foreach($kaMetadata->getParams($_POST['tabella'],$_POST['id']) as $param) {
	if(strlen($param['param'])<4||(strlen($param['param'])>=4&&substr($param['param'],0,4)!="seo_")) {
		?><tr>
		<th><?= htmlspecialchars($param['param']); ?></th>
		<td><?= nl2br(htmlspecialchars($param['value'])); ?>
			<small class="actions"><a href="javascript:kOpenIPopUp(ADMINDIR+'inc/ajax/metadataUpdate.php','t=<?= $_POST['tabella']; ?>&id=<?= $_POST['id']; ?>&p=<?= urlencode(addslashes($param['param'])); ?>','600px','400px')">Modifica</a></small>
			</td>
		<?php 
		$i++;
		}
	}
if($i==0) echo $kaTranslate->translate('Metadata:No available fields');
?>
</table>
