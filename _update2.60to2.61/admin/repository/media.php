<?
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

<input type="button" onclick="window.parent.k_openIframeWindow(ADMINDIR+'inc/mediaManager.inc.php?&mode=justupload&forcerefresh=true&mediatable=&mediaid=','800px','500px');" class="button" style="float:right;" value="Carica oggetto multimediale" />
<h1><? echo PAGE_NAME; ?></h1>
<br />

<div class="box">
	<div style="float:right;font-size:.8em;"><form method="get" action=""><input type="hidden" name="p" value="<?= $_GET['p']; ?>" /><input type="text" name="q" style="width:100px;" value="<?= str_replace('"','&quot;',$_GET['q']); ?>" /> <input type="submit" value="Cerca" class="smallbutton" /></form></div>
	Pagine: 
	<?
	$tot=$kaMedia->countList();
	for($i=1;$i<=ceil($tot/$items4page);$i++) { ?>
		<a href="?p=<?= $i; ?>"<?= ($i==$_GET['p'])?'style="background-color:#ffc;padding:0 5px;"':''; ?>><?= $i; ?></a>
		<? }
	?>
	<div style="clear:both;"></div>
	</div><br />

<table><?
$i=0;
foreach($kaMedia->getList(false,false,'filename',"`filename` LIKE '%".$_GET['q']."%' OR `alt` LIKE '%".$_GET['q']."%'",(($_GET['p']-1)*$items4page),$items4page) as $media) {
	if($media['thumb']['filename']!=""){
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
	<td><div class="thumb"><? if($media['thumb']['filename']!="") { ?><img src="<?= BASEDIR.$media['thumb']['url']; ?>" width="<?= $w; ?>" height="<?= $h; ?>" alt="<?= BASEDIR.$media['alt']; ?>" /><? } else { ?>Nessuna anteprima<? } ?></div></td>
	<td>
		<strong><?= $media['title']!=""?$media['title']:$media['filename']; ?></strong><br />
		Dimensioni: <?= $media['width']; ?> x <?= $media['height']; ?> px - Durata: <?= $media['duration']; ?> sec.<br />
		<div class="small"><? if(trim($media['htmlcode'])=="") { ?>Permalink: <?= $media['hotlink']==0?SITE_URL.BASEDIR.$media['url']:$media['url']; } else { ?>Codice incorporato<? } ?></div><br />
		<a href="javascript:window.parent.k_openIframeWindow(ADMINDIR+'inc/mediaManager.inc.php?idmedia=<?php echo $media['idmedia']; ?>&forcerefresh=true&action=fullsize&mediatable=&mediaid=','800px','500px');" class="smallbutton">Guarda</a>
		<a href="javascript:window.parent.k_openIframeWindow(ADMINDIR+'inc/mediaManager.inc.php?idmedia=<?php echo $media['idmedia']; ?>&forcerefresh=true&action=properties&mediatable=&mediaid=','800px','500px');" class="smallbutton">Modifica</a>
		<a href="javascript:window.parent.k_openIframeWindow(ADMINDIR+'inc/mediaManager.inc.php?idmedia=<?php echo $media['idmedia']; ?>&forcerefresh=true&action=delete&mediatable=&mediaid=','800px','500px');" class="smallbutton">Elimina</a>
		</td>
	<?
	$i++;
	if($i%2==0) echo '</tr>';
	}
?></table>
<br />
<div class="box" style="text-align:center;">Pagine: 
	<?
	$tot=$kaMedia->countList();
	for($i=1;$i<=ceil($tot/$items4page);$i++) { ?>
		<a href="?p=<?= $i; ?>"<?= ($i==$_GET['p'])?'style="background-color:#ffc;padding:0 5px;"':''; ?>><?= $i; ?></a>
		<? }
	?>
	</div><br />

<div class="submit"><input type="button" onclick="window.parent.k_openIframeWindow(ADMINDIR+'inc/mediaManager.inc.php?&mode=justupload&forcerefresh=true&mediatable=&mediaid=','800px','500px');" class="button" value="Carica oggetto multimediale" /></div>


<?
include_once("../inc/foot.inc.php");
?>
