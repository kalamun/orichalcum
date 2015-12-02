<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

//error_reporting(0);
define("PAGE_NAME","Repository > Oggetti Multimediali");
define("PAGE_LEVEL",2);
include_once("../inc/head.inc.php");
require_once('../inc/media.lib.php');
$kaMedia=new kaMedia();
$items4page=10;
if(!isset($_GET['p'])) $_GET['p']=1;
if(!isset($_GET['q'])) $_GET['q']="";
?>

<div style="float:right;">
	<input type="button" onclick="window.parent.k_openIframeWindow(ADMINDIR+'inc/uploadsManager.inc.php?fileType=media');" class="button" value="<?= $kaTranslate->translate('Repository:Upload a multimedia file'); ?>" />
</div>

<h1><?php  echo PAGE_NAME; ?></h1>
<br />

<div class="box">
	<div style="float:right;font-size:.8em;"><form method="get" action=""><input type="hidden" name="p" value="<?= $_GET['p']; ?>" /><input type="text" name="q" style="width:100px;" value="<?= str_replace('"','&quot;',$_GET['q']); ?>" /> <input type="submit" value="Cerca" class="smallbutton" /></form></div>
	Pagine: 
	<?php 
	$tot=$kaMedia->countList();
	for($i=1;$i<=ceil($tot/$items4page);$i++) { ?>
		<a href="?p=<?= $i; ?>"<?= ($i==$_GET['p'])?'style="background-color:#ffc;padding:0 5px;"':''; ?>><?= $i; ?></a>
		<?php  }
	?>
	<div style="clear:both;"></div>
	</div><br />

<table><?php 
$i = 0;
$vars = array();
$vars['filetype'] = 2; // media
$vars['orderby'] = 'filename';
$vars['conditions'] = "`filename` LIKE '%".$_GET['q']."%' OR `alt` LIKE '%".$_GET['q']."%'";
$vars['offset'] = (($_GET['p']-1)*$items4page);
$vars['limit'] = $items4page;

foreach($kaImages->getList($vars) as $media) {
	if($media['thumb']['filename']!=""){
		
		$defaultsize = $kaImpostazioni->getParam('thumb_size','*');
		if(empty($media['thumb']['width'])) $media['thumb']['width'] = $defaultsize['value1'];
		if(empty($media['thumb']['height'])) $media['thumb']['height'] = $defaultsize['value2'];

		if($media['thumb']['width']>$media['thumb']['height']) {
			$w=100;
			$h=100/$media['thumb']['width']*$media['thumb']['height'];
			}
		else {
			$h=100;
			$w=100/$media['thumb']['height']*$media['thumb']['width'];
			}
		}
	if($i%2==0) echo '<tr>';
	?>
	<td><div class="thumb"><?php  if($media['thumb']['filename']!="") { ?><img src="<?= BASEDIR.$media['thumb']['url']; ?>" width="<?= $w; ?>" height="<?= $h; ?>" alt="<?= BASEDIR.$media['alt']; ?>" /><?php  } else { ?>Nessuna anteprima<?php  } ?></div></td>
	<td>
		<strong><?= $media['alt']!=""?$media['alt']:$media['filename']; ?></strong><br />
		Dimensioni: <?= $media['width']; ?> x <?= $media['height']; ?> px - Durata: <?= $media['metadata']['duration']; ?> sec.<br />
		<div class="small"><?php  if(trim($media['metadata']['embeddingcode'])=="") { ?>Permalink: <?= $media['hotlink']==0?SITE_URL.BASEDIR.$media['url']:$media['url']; } else { ?>Codice incorporato<?php  } ?></div><br />
		<a href="javascript:window.parent.k_openIframeWindow(ADMINDIR+'inc/uploadsManager_edit.inc.php?id=img<?php echo $media['idimg']; ?>');" class="smallbutton"><?= $kaTranslate->translate('Repository:View'); ?></a>
		<a href="javascript:window.parent.k_openIframeWindow(ADMINDIR+'inc/uploadsManager_edit.inc.php?id=img<?php echo $media['idimg']; ?>');" class="smallbutton"><?= $kaTranslate->translate('UI:Edit'); ?></a>
		<a href="javascript:window.parent.k_openIframeWindow(ADMINDIR+'inc/uploadsManager_edit.inc.php?id=img<?php echo $media['idimg']; ?>&forcerefresh=true');" class="smallalertbutton"><?= $kaTranslate->translate('UI:Delete'); ?></a>
		</td>
	<?php 
	$i++;
	if($i%2==0) echo '</tr>';
	}
?></table>
<br />
<div class="box" style="text-align:center;">Pagine: 
	<?php 
	$tot=$kaMedia->countList();
	for($i=1;$i<=ceil($tot/$items4page);$i++) { ?>
		<a href="?p=<?= $i; ?>"<?= ($i==$_GET['p'])?'style="background-color:#ffc;padding:0 5px;"':''; ?>><?= $i; ?></a>
		<?php  }
	?>
	</div><br />

<div class="submit"><input type="button" onclick="window.parent.k_openIframeWindow(ADMINDIR+'inc/mediaManager.inc.php?&mode=justupload&forcerefresh=true&mediatable=&mediaid=','800px','500px');" class="button" value="Carica oggetto multimediale" /></div>


<?php 
include_once("../inc/foot.inc.php");
