<?php
/* (c) Kalamun.org - GNU/GPL 3 */
define("PAGE_NAME","Shop:Delete a manufacturer");
include_once("../inc/head.inc.php");
include_once("./shop.lib.php");
include_once("../inc/metadata.lib.php");

$kaShop=new kaShop;
$kaMetadata=new kaMetadata;
$pageLayout=$kaImpostazioni->getVar('admin-manufacturers-layout',1,"*");

if(!isset($_GET['search'])) $_GET['search']="";


?><script type="text/javascript" src="./js/edit.js" charset="UTF-8"></script><?


/**************************************/
/* if no page is specified, show list */
/**************************************/

/* ACTIONS */	

if(isset($_GET['delete']))
{
	$log="";
	$id=$kaShop->deleteManufacturer($_GET['delete']);
	if($id==false) $log="An error occurred while deleting manufacture";

	if($log!="")
	{
		echo '<div id="MsgAlert">'.$log.'</div>';
		$kaLog->add("ERR",'Shop: Error while deleting manufacturer ID: <em>'.$_GET['delete'].'</em>');
	} else {
		echo '<div id="MsgSuccess">'.$kaTranslate->translate('Shop:Item successfully deleted').'</div>';
		$kaLog->add("DEL",'Shop: Manufacturer removed ID: <em>'.$_GET['delete'].'</em>');
	}
}

/* END ACTIONS */

	
?>
<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
<br />

<div class="subset">
	<fieldset class="box"><legend><?= $kaTranslate->translate('UI:Search'); ?></legend>
	<input type="text" name="search" id="searchQ" style="width:180px;" value="<?= str_replace('"','&quot;',$_GET['search']); ?>" />
	<script type="text/javascript">
		function selectMenuRef(usePage) {
			document.getElementById('usePage').value=usePage;
			k_openIframeWindow(ADMINDIR+"inc/selectMenuRef.inc.php","450px","500px");
			}
		function selectElement(id,where) {
			var usePage=document.getElementById('usePage').value;
			var get="";
			if(String(window.location).indexOf("search=")>-1) {
				get=String(window.location);
				get=get.replace(/.*search=/,"");
				get="search="+get.replace(/^[[^\d]*].*/,"");
				}
			var url=String(window.location).replace(/\?.*/,"");
			window.location=url+'?usePage='+usePage+'&addtomenu='+id+','+where+'&'+get;
			}
		document.getElementById('searchQ').onkeyup=searchKeyUp;
		</script>
	</div>
	
<div class="topset">
	<input type="hidden" id="usePage" />
	<table class="tabella">
	
		<tr>
			<? if(strpos($pageLayout,",featuredimage,")!==false) { ?>
				<th>&nbsp;</th>
			<? } ?>

			<th><?= $kaTranslate->translate('Shop:Name'); ?></th>
		</tr>

		<tbody>
		<?
		foreach($kaShop->getManufacturersList( array("search"=>$_GET['search']) ) as $page) { ?>
			<tr>
			<?php if(strpos($pageLayout,",featuredimage,")!==false) { ?>
				<td class="featuredimage">
					<div class="container"><?php
						if($page['featuredimage']>0)
						{
							$img=$kaImages->getImage($page['featuredimage']);
							?>
							<img src="<?= BASEDIR.$img['thumb']['url']; ?>">
							<?
						}
					?></div>
				</td>
			<?php } ?>
				<td>
					<a href="?idsman=<?= $page['idsman']; ?>" class="title"><?= $page['name']; ?></a><br>
					<a href="?idsman=<?= $page['idsman']; ?>" class="url"><?= $page['dir']; ?></a><br>
					<small class="actions">
						<a href="?delete=<?= $page['idsman']; ?>" class="delete" onclick="return confirm('<?= addslashes($kaTranslate->translate('Shop:Are you sure do you want to remove this manufacturer? This operation IS NOT REVERSIBLE.')); ?>');"><?= $kaTranslate->translate('Shop:Delete'); ?></a> |
						<a href="<?= SITE_URL.BASEDIR.strtolower($_SESSION['ll'])."/".$page['dir']; ?>" target="_blank"><?= $kaTranslate->translate('Shop:Visit'); ?></a>
					</small>
				</td>
			</tr>
			<? } ?>

		</tbody>
	</table>
	</div>

<?php
include_once("../inc/foot.inc.php");
?>
