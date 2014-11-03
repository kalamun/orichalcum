<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

error_reporting(0);
define("PAGE_NAME","Repository > Documenti");
define("PAGE_LEVEL",2);
include_once("../inc/head.inc.php");
require_once('../inc/documents.lib.php');
$kaDocuments=new kaDocuments();
$items4page=30;
if(!isset($_GET['p'])) $_GET['p']=1;
if(!isset($_GET['q'])) $_GET['q']="";
?>

<input type="button" onclick="window.parent.k_openIframeWindow(ADMINDIR+'inc/docManager.inc.php?&mode=justupload&forcerefresh=true&mediatable=&mediaid=','800px','500px');" class="button" style="float:right;" value="Carica documento" />
<h1><?php  echo PAGE_NAME; ?></h1>
<br />

<div class="box">
	<div style="float:right;font-size:.8em;"><form method="get" action=""><input type="hidden" name="p" value="<?= $_GET['p']; ?>" /><input type="text" name="q" style="width:100px;" value="<?= str_replace('"','&quot;',$_GET['q']); ?>" /> <input type="submit" value="Cerca" class="smallbutton" /></form></div>
	Pagine: 
	<?php 
	$tot=$kaDocuments->countList();
	for($i=1;$i<=ceil($tot/$items4page);$i++) { ?>
		<a href="?p=<?= $i; ?>"<?= ($i==$_GET['p'])?'style="background-color:#ffc;padding:0 5px;"':''; ?>><?= $i; ?></a>
		<?php  }
	?>
	<div style="clear:both;"></div>
	</div><br />

<table><?php 
$i=0;
foreach($kaDocuments->getList(false,false,'filename',"filename LIKE '%".$_GET['q']."%' OR alt LIKE '%".$_GET['q']."%'",(($_GET['p']-1)*$items4page),$items4page) as $doc) {
	?>
	<td style="padding-bottom:20px;">
		<strong><?= $doc['filename']; ?></strong><br />
		Peso: <?php  if(file_exists($_SERVER['DOCUMENT_ROOT'].BASEDIR.$doc['url'])) echo filesize($_SERVER['DOCUMENT_ROOT'].BASEDIR.$doc['url']); ?> Kb<br />
		<div class="small"><?= $doc['hotlink']==0?SITE_URL.BASEDIR.$doc['url']:$doc['url']; ?></div><br />
		<a href="javascript:window.parent.k_openIframeWindow(ADMINDIR+'inc/docManager.inc.php?iddoc=<?php echo $doc['iddoc']; ?>&forcerefresh=true&action=fullsize&mediatable=&mediaid=','800px','500px');" class="smallbutton">Guarda</a>
		<a href="javascript:window.parent.k_openIframeWindow(ADMINDIR+'inc/docManager.inc.php?iddoc=<?php echo $doc['iddoc']; ?>&forcerefresh=true&action=properties&mediatable=&mediaid=','800px','500px');" class="smallbutton">Modifica</a>
		<a href="javascript:window.parent.k_openIframeWindow(ADMINDIR+'inc/docManager.inc.php?iddoc=<?php echo $doc['iddoc']; ?>&forcerefresh=true&action=delete&mediatable=&mediaid=','800px','500px');" class="smallbutton">Elimina</a>
		</td>
	<?php 
	$i++;
	if($i%2==0) echo '</tr>';
	}
?></table>
<br />
<div class="box" style="text-align:center;">Pagine: 
	<?php 
	$tot=$kaDocuments->countList();
	for($i=1;$i<=ceil($tot/$items4page);$i++) { ?>
		<a href="?p=<?= $i; ?>"<?= ($i==$_GET['p'])?'style="background-color:#ffc;padding:0 5px;"':''; ?>><?= $i; ?></a>
		<?php  }
	?>
	</div><br />

<div class="submit"><input type="button" onclick="window.parent.k_openIframeWindow(ADMINDIR+'inc/docManager.inc.php?&mode=justupload&forcerefresh=true&mediatable=&mediaid=','800px','500px');" class="button" value="Carica documento" /></div>


<?php 
include_once("../inc/foot.inc.php");
