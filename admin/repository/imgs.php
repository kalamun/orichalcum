<?
/* (c) Kalamun.org - GNU/GPL 3 */

error_reporting(0);
define("PAGE_NAME","Repository > Immagini");
include_once("../inc/head.inc.php");
require_once('../inc/images.lib.php');
$kaImages=new kaImages();
$items4page=30;
if(!isset($_GET['p'])) $_GET['p']=1;
if(!isset($_GET['q'])) $_GET['q']="";

$conditions="";
if($_GET['q']!="") $conditions.="filename LIKE '%".mysql_real_escape_string($_GET['q'])."%' OR alt LIKE '%".mysql_real_escape_string($_GET['q'])."%'";
?>

<input type="button" onclick="window.parent.k_openIframeWindow(ADMINDIR+'inc/imgManager.inc.php?&mode=justupload&forcerefresh=true&multiple&mediatable=&mediaid=','800px','500px');" class="button" style="float:right;" value="Carica immagine" />
<h1><? echo PAGE_NAME; ?></h1>
<br />

<div class="box">
	<div style="float:right;font-size:.8em;"><form method="get" action=""><input type="text" name="q" style="width:100px;" value="<?= str_replace('"','&quot;',$_GET['q']); ?>" /> <input type="submit" value="Cerca" class="smallbutton" /></form></div>
	Pagine: 
	<?
	$tot=$kaImages->countList(false,false,$conditions);
	for($i=1;$i<=ceil($tot/$items4page);$i++) { ?>
		<a href="?p=<?= $i; ?>&q=<?= urlencode($_GET['q']); ?>"<?= ($i==$_GET['p'])?'style="background-color:#ffc;padding:0 5px;"':''; ?>><?= $i; ?></a>
		<? }
	?>
	<div style="clear:both;"></div>
	</div><br />

<table><?
$i=0;
foreach($kaImages->getList(false,false,'filename',$conditions,(($_GET['p']-1)*$items4page),$items4page) as $img) {
	if($img['thumb']['width']>0) {
		if($img['thumb']['width']>$img['thumb']['height']) {
			$w=100;
			$h=100/$img['thumb']['width']*$img['thumb']['height'];
			}
		else {
			$h=100;
			$w=100/$img['thumb']['height']*$img['thumb']['width'];
			}
		if($i%2==0) echo '<tr>';
		if(!isset($img['thumb']['alt'])) $img['thumb']['alt']="";
		?>
		<td><div class="thumb"><img src="<?= BASEDIR.$img['thumb']['url']; ?>" width="<?= $w; ?>" height="<?= $h; ?>" alt="<?= BASEDIR.$img['thumb']['alt']; ?>" /></div></td>
		<td>
			<strong><?= $img['filename']; ?></strong><br />
			Dimensioni: <?= $img['width']; ?> x <?= $img['height']; ?> px<br />
			<div class="small">Permalink: <?= $img['hotlink']==0?SITE_URL.BASEDIR.$img['url']:$img['url']; ?></div><br />
			<a href="javascript:window.parent.k_openIframeWindow(ADMINDIR+'inc/imgManager.inc.php?idimg=<?php echo $img['idimg']; ?>&forcerefresh=true&action=fullsize&mediatable=&mediaid=','800px','500px');" class="smallbutton">Guarda</a>
			<a href="javascript:window.parent.k_openIframeWindow(ADMINDIR+'inc/imgManager.inc.php?idimg=<?php echo $img['idimg']; ?>&forcerefresh=true&action=properties&mediatable=&mediaid=','800px','500px');" class="smallbutton">Modifica</a>
			<a href="javascript:window.parent.k_openIframeWindow(ADMINDIR+'inc/imgManager.inc.php?idimg=<?php echo $img['idimg']; ?>&forcerefresh=true&action=delete&mediatable=&mediaid=','800px','500px');" class="smallbutton">Elimina</a>
			</td>
		<?
		$i++;
		if($i%2==0) echo '</tr>';
		}
	else {
		?>
		<td>ERROR</td>
		<td>
			<strong><?= $img['filename']; ?></strong><br />
			Dimensioni: <?= $img['width']; ?> x <?= $img['height']; ?> px<br />
			<div class="small">Permalink: <?= $img['hotlink']==0?SITE_URL.BASEDIR.$img['url']:$img['url']; ?></div><br />
			<a href="javascript:window.parent.k_openIframeWindow(ADMINDIR+'inc/imgManager.inc.php?idimg=<?php echo $img['idimg']; ?>&forcerefresh=true&action=fullsize&mediatable=&mediaid=','800px','500px');" class="smallbutton">Guarda</a>
			<a href="javascript:window.parent.k_openIframeWindow(ADMINDIR+'inc/imgManager.inc.php?idimg=<?php echo $img['idimg']; ?>&forcerefresh=true&action=properties&mediatable=&mediaid=','800px','500px');" class="smallbutton">Modifica</a>
			<a href="javascript:window.parent.k_openIframeWindow(ADMINDIR+'inc/imgManager.inc.php?idimg=<?php echo $img['idimg']; ?>&forcerefresh=true&action=delete&mediatable=&mediaid=','800px','500px');" class="smallbutton">Elimina</a>
			</td>
		<?
		}
	}
?></table>
<br />
<div class="box" style="text-align:center;">Pagine: 
	<?
	for($i=1;$i<=ceil($tot/$items4page);$i++) { ?>
		<a href="?p=<?= $i; ?>"<?= ($i==$_GET['p'])?'style="background-color:#ffc;padding:0 5px;"':''; ?>><?= $i; ?></a>
		<? }
	?>
	</div><br />

<div class="submit"><input type="button" onclick="window.parent.k_openIframeWindow(ADMINDIR+'inc/imgManager.inc.php?&mode=justupload&forcerefresh=true&mediatable=&mediaid=','800px','500px');" class="button" value="Carica immagine" /></div>


<?
include_once("../inc/foot.inc.php");
?>
