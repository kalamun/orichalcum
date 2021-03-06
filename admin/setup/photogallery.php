<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Setup:Photogallery settings");
include_once("../inc/head.inc.php");

$kaTranslate->import('photogallery');

/* AZIONI */
if(isset($_POST['update'])) {
	if(!isset($_POST['photogallery-commenti'][1])) $_POST['photogallery-commenti'][1]='n';
	if(!isset($_POST['photogallery-commenti'][2])) $_POST['photogallery-commenti'][2]='n';
	$kaImpostazioni->setParam('photogallery-commenti',$_POST['photogallery-commenti'][1],$_POST['photogallery-commenti'][2]);

	$kaImpostazioni->setParam('photogallery-template',$_POST['photogallery-template'][1],"");
	$kaImpostazioni->setParam('photogallery-order',$_POST['photogallery-order'][1],"");

	$layout=",";
	if(isset($_POST['layout'])) {
		foreach($_POST['layout'] as $ka=>$v) {
			$layout.=$ka.",";
			}
		}
	$kaImpostazioni->setParam('admin-photogallery-layout',$layout,"","*");

	}
/* FINE AZIONI */

$template=$kaImpostazioni->getParam('photogallery-template');
$order=$kaImpostazioni->getParam('photogallery-order');
$layout=$kaImpostazioni->getParam('admin-photogallery-layout',"*");
$comments=$kaImpostazioni->getParam('photogallery-commenti');

?>
<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
<br />

<script type="text/javascript">
	function ableAllFields(f) {
		inputs=f.getElementsByTagName('INPUT');
		for(var i=0;inputs[i];i++) {
			inputs[i].disabled=false;
			}
		return true;
		}
	</script>

<form action="?" method="post" onsubmit="return ableAllFields(this)">
	<table>
	<tr>
		<td><label for="photogallery-template1">Template di default per le photogallery</label></td>
		<td><?php 
			$option=array("");
			$value=array("-default-");
			foreach($kaImpostazioni->getTemplateList() as $file) {
				$option[]=$file;
				$value[]=str_replace("_"," ",$file);
				}
			echo b3_create_select("photogallery-template[1]","",$value,$option,$template['value1']);
			?></td>
		</tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	
	<tr>
		<td><label for="photogallery-order1">Ordina le photogallery per</label></td>
		<td><?php 
			$option=array("data ASC","data DESC","ordine","titolo");
			$value=array("Cronologico ascendente","Cronologico discendente","Impostato dall'utente","Titolo (alfabetico)");
			echo b3_create_select("photogallery-order[1]","",$value,$option,$order['value1']);
			?></td>
		</tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	
	</table>
	<br /><br />
<h3>Commenti</h3>
	<?= b3_create_input("photogallery-commenti[1]","checkbox","Abilita i commenti","s","","",($comments['value1']=="s"?'checked':''),true); ?><br />
	<?= b3_create_input("photogallery-commenti[2]","checkbox","Modera i commenti","s","","",($comments['value2']=="s"?'checked':''),true); ?><br />

	<br /><br />

<h3><?= $kaTranslate->translate('Setup:Fields to display in administration panel'); ?></h3>
	<?php
	$elm=array("title"=>"Photogalleries:Title","text"=>"Photogalleries:Description","photogallery"=>"Photogalleries:Photo gallery","featuredimage"=>"Photogalleries:Featured image","categories"=>"Photogalleries:Categories","template"=>"Photogalleries:Template","layout"=>"Photogalleries:Layout","metadata"=>"Photogalleries:Metadata","traduzioni"=>"Photogalleries:Translations","seo"=>"Photogalleries:SEO (Search Engine Optimization)");
	$elmobl=array("title"=>true,"photogallery"=>true);
	foreach($elm as $ka=>$v)
	{
		echo b3_create_input("layout[".$ka."]", "checkbox", $kaTranslate->translate($v), "s", "", "", (strpos($layout['value1'],",".$ka.",")!==false||isset($elmobl[$ka])?'checked':'').' '.(isset($elmobl[$ka])?'disabled':''),true).'<br />';
	}
	?>

	<br /><br />
	<div class="submit"><input type="submit" name="update" value="<?= $kaTranslate->translate('UI:Save'); ?>" class="button"></div>
</form></div><br /><br />

<?php 
include_once("../inc/foot.inc.php");
