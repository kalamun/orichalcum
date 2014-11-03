<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Repository:Images Archive");
include_once("../inc/head.inc.php");
require_once('../inc/images.lib.php');

$kaImages=new kaImages();
$items4page=15;
if(!isset($_GET['p'])) $_GET['p']=1;
if(!isset($_GET['q'])) $_GET['q']="";

$conditions="";
if($_GET['q']!="") $conditions.="`filename` LIKE '%".mysql_real_escape_string($_GET['q'])."%' OR `thumbnail` LIKE '%".mysql_real_escape_string($_GET['q'])."%' OR `alt` LIKE '%".mysql_real_escape_string($_GET['q'])."%'";
?>

<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>

<div class="subset">
	<form method="get" action="" class="box">
		<input type="text" name="q" value="<?= str_replace('"','&quot;',$_GET['q']); ?>" placeholder="<?= $kaTranslate->translate('UI:Search'); ?>" />
	</form>
	<br>
	<input type="button" onclick="window.parent.k_openIframeWindow(ADMINDIR+'inc/uploadsManager.inc.php');" class="button" value="<?= $kaTranslate->translate('Repository:Upload an image'); ?>" />
</div>

<div class="topset">
	<div class="box">
		<?= $kaTranslate->translate('Repository:Pages'); ?>
		<?php 
		if($_GET['p']>1) { ?><a href="?p=<?= $_GET['p']-1; ?>&q=<?= urlencode($_GET['q']); ?>">&lt; <?= $kaTranslate->translate('Repository:previous'); ?></a><?php  }

		$tot=$kaImages->countList(false,false,$conditions);
		for($i=1;$i<=ceil($tot/$items4page);$i++)
		{
			?><a href="?p=<?= $i; ?>&q=<?= urlencode($_GET['q']); ?>" class="<?= ($i==$_GET['p'])?'selected':''; ?>"><?= $i; ?></a><?php 
		}

		if($_GET['p']<ceil($tot/$items4page)) { ?><a href="?p=<?= $_GET['p']+1; ?>&q=<?= urlencode($_GET['q']); ?>"><?= $kaTranslate->translate('Repository:next'); ?> &gt;</a><?php  }
		?>
	</div>


	<div class="imageList">
		<?php 
		foreach($kaImages->getList('`filename`',$conditions,(($_GET['p']-1)*$items4page),$items4page) as $img) {
			?>
			<div class="image">
				<div class="thumb" style="background-image:url('<?= addslashes(BASEDIR.$img['thumb']['url']); ?>');" /></div>
				<?= ($img['alt']!="" ? $img['alt'] : $kaTranslate->translate('Repository:No caption defined')); ?><br />
				<small>
					<?= $img['filename']; ?><br />
					<?= $kaTranslate->translate('Repository:Uploaded on'); ?> <?= preg_replace("/(\d{4}).(\d{2}).(\d{2}) (\d{2}).(\d{2}).(\d{2})/","$3/$2/$1 - $4:$5",$img['creation_date']); ?><br />
					<?= $kaTranslate->translate('Repository:Size'); ?>: <?= $img['width']; ?> x <?= $img['height']; ?> px<br />
				</small>
				<a href="javascript:window.parent.k_openIframeWindow(ADMINDIR+'inc/uploadsManager_edit.inc.php?id=img<?php echo $img['idimg']; ?>');" class="smallbutton"><?= $kaTranslate->translate('Repository:View'); ?></a>
				<a href="javascript:window.parent.k_openIframeWindow(ADMINDIR+'inc/uploadsManager_edit.inc.php?id=img<?php echo $img['idimg']; ?>');" class="smallbutton"><?= $kaTranslate->translate('UI:Edit'); ?></a>
				<a href="javascript:window.parent.k_openIframeWindow(ADMINDIR+'inc/uploadsManager_edit.inc.php?id=img<?php echo $img['idimg']; ?>&forcerefresh=true');" class="smallalertbutton"><?= $kaTranslate->translate('UI:Delete'); ?></a>
			</div>
			<?php 
		}
		?>
	</div>
	<br />

	<div class="box">
		<?= $kaTranslate->translate('Repository:Pages'); ?>
		<?php 
		if($_GET['p']>1) { ?><a href="?p=<?= $_GET['p']-1; ?>&q=<?= urlencode($_GET['q']); ?>">&lt; <?= $kaTranslate->translate('Repository:previous'); ?></a><?php  }

		$tot=$kaImages->countList(false,false,$conditions);
		for($i=1;$i<=ceil($tot/$items4page);$i++)
		{
			?><a href="?p=<?= $i; ?>&q=<?= urlencode($_GET['q']); ?>" class="<?= ($i==$_GET['p'])?'selected':''; ?>"><?= $i; ?></a><?php 
		}

		if($_GET['p']<ceil($tot/$items4page)) { ?><a href="?p=<?= $_GET['p']+1; ?>&q=<?= urlencode($_GET['q']); ?>"><?= $kaTranslate->translate('Repository:next'); ?> &gt;</a><?php  }
		?>
	</div>
</div>


<?php 
include_once("../inc/foot.inc.php");
