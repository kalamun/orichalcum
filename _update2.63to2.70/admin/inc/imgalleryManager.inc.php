<?php /* (c) Kalamun.org - GNU/GPL 3 */

error_reporting(0);
require_once('./connect.inc.php');
require_once('kalamun.lib.php');
require_once('./sessionmanager.inc.php');
require_once('./main.lib.php');
$kaTranslate=new kaAdminTranslate();
if(!isset($_SESSION['iduser'])) die($kaTranslate->translate('You don\'t have permission to use this function'));

/* set default timezone in PHP and MySQL */
$timezone=kaGetVar('timezone',1);
if($timezone!="") {
	date_default_timezone_set($timezone);
	$query="SET time_zone='".date("P")."'";
	mysql_query($query);
	}

require_once('./log.lib.php');
$kaLog=new kaLog();

define("PAGE_NAME","Photogallery Manager");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
<head>
<title><?php echo ADMIN_NAME." - ".PAGE_NAME; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="author" content="Roberto Pasini - www.kalamun.org" />
<meta name="copyright" content="no(c)" />
<style type="text/css">
	@import "<?php echo ADMINDIR; ?>css/screen.css";
	@import "<?php echo ADMINDIR; ?>css/main.lib.css";
	@import "<?php echo ADMINDIR; ?>css/imgmanager.css";
	</style>

<script type="text/javascript">var ADMINDIR='<?php echo str_replace("'","\'",ADMINDIR); ?>';</script>
<script type="text/javascript" src="<?php echo ADMINDIR; ?>js/kalamun.js"></script>
<script type="text/javascript" src="<?php echo ADMINDIR; ?>js/imgframe.js"></script>
</head>

<body>

<?php 
if(!isset($_GET['mode'])) $_GET['mode']="";
if(!isset($_GET['forcerefresh'])) $_GET['forcerefresh']=false;
if(!isset($_GET['start'])) $_GET['start']=1;
if(!isset($_GET['max'])) $_GET['max']=999;
if(!isset($_GET['mediatable'])) $_GET['mediatable']="";
if(!isset($_GET['mediaid'])) $_GET['mediaid']="";
if(!isset($_GET['search'])) $_GET['search']="";
if(!isset($_GET['idimg'])) $_GET['idimg']=0;
if(!isset($_GET['idimga'])) $_GET['idimga']=0;
include('./images.lib.php');
$kaImages=new kaImages();
include('./imgallery.lib.php');
$kaImgallery=new kaImgallery();

if(intval($_GET['idimga'])>0) {
	/* MODIFICA SINGOLA IMMAGINE */
	$imga=$kaImgallery->getImage($_GET['idimga']);
	if(intval($imga['idimg'])<=0) die('Errore nell\'ID dell\'immagine');
	$_GET['idimg']=$imga['idimg'];
	?>
	<div id="imgheader">
		<h1>Modifica Immagine</h1>
		<a href="javascript:window.parent.k_closeIframeWindow();" class="closeWindow"><img src="<?= ADMINRELDIR; ?>img/closeWindow.gif" alt="Close" width="9" height="9" /></a>
		<div class="smenu sel">
			<ul>
			<?php 
			$menu=array();
			$menu['properties']="Propriet&agrave; dell'immagine";
			$menu['files']="Files";
			$menu['istances']="Utilizzo";
			$menu['fullsize']="Guarda a dimensioni reali";
			$menu['delete']="Elimina";
			if(!isset($_GET['action'])) $_GET['action']='properties';
			foreach($menu as $ka=>$m) {
				echo '<li><a href="?idimga='.$_GET['idimga'].'&forcerefresh='.$_GET['forcerefresh'].'&refid='.$_GET['refid'].'&mediatable='.$_GET['mediatable'].'&mediaid='.$_GET['mediaid'].'&search='.$_GET['search'].'&action='.$ka.'&start='.$_GET['start'].'&max='.$_GET['max'].'" class="'.($_GET['action']==$ka?'sel':'').'">'.$m.'</a></li>';
				}
			?>
			</ul>
			</div>
		</div>
.
	<div id="imgcontents">
		<?php 
		if($_GET['action']=="properties") {
			if(isset($_POST['save'])) {
				$log="";
				$img=$kaImages->getImage($_GET['idimg']);
				if($kaImages->updateAlt($_GET['idimg'],$_POST['alt'])==false) $log.="Errore durante il salvataggio delle modifiche.<br />";
			
				if($log=="") {
					$kaLog->add("UPD","Modificate le propriet&agrave; dell'immagine ".$img['filename']." (<em>ID: ".$img['idimg']."</em>)");
					?>
					<div class="success">Immagine modificata con successo.</div>
					<?php  }
				else {
					$kaLog->add("ERR","Errore durante la modifica delle propriet&agrave; dell'immagine ".$img['filename']." (<em>ID: ".$img['idimg']."</em>)");
					echo '<div class="alert">'.$log.'</div>';
					}
				}
			?>
			<form action="?idimga=<?= $_GET['idimga']; ?>&forcerefresh=<?= $_GET['forcerefresh']; ?>&refid=<?php echo $_GET['refid']; ?>&mediatable=<?php echo $_GET['mediatable']; ?>&mediaid=<?php echo $_GET['mediaid']; ?>&search=<?php echo $_GET['search']; ?>&action=<?= $_GET['action']; ?>&start=<?= $_GET['start']; ?>&max=<?= $_GET['max']; ?>" method="post" enctype="multipart/form-data">
				<?php  $img=$kaImages->getImage($_GET['idimg']); ?>
				<table style="margin:10px auto;">
				<tr><td colspan="2" align="center"><img src="<?= BASEDIR.$img['thumb']['url']; ?>" height="100" alt="" /></td></tr>
				<tr><td align="right"><label for="alt"><?= $kaTranslate->translate('Img:Caption'); ?></label></td><td><textarea name="alt" id="alt" style="width:300px;height:50px;"><?= b3_lmthize($img['alt'],"textarea"); ?></textarea></td></tr>
				</table><br />
				<div class="note">P.s: Attento! Modificando questa immagine, essa verrà cambiata in tutti i posti in cui è stata utilizzata!</div>
				<div class="submit"><input type="submit" name="save" value="Salva le modifiche" class="button" /></div>
				</form>
			<?php  }

		elseif($_GET['action']=="files") {
			if(isset($_POST['saveimg'])) {
				/* SALVA MODIFICHE IMMAGINE */
				$log="";
				$img=$kaImages->getImage($_GET['idimg']);
				isset($_POST['autoresize'])?$_POST['autoresize']=true:$_POST['autoresize']=false;
				if(!isset($_POST['imgwidth'])) $_POST['imgwidth']=0;
				if(!isset($_POST['imgheight'])) $_POST['imgheight']=0;
				$idimg=$kaImages->updateImage($img['idimg'],$_FILES['img']['tmp_name'],$_FILES['img']['name'],$_POST['autoresize'],$_POST['imgwidth'],$_POST['imgheight']);
			
				if($log=="") {
					$kaLog->add("UPD","Sostituita l'immagine ".$img['filename']." (<em>ID: ".$img['idimg']."</em>)");
					?>
					<div class="success">Immagine modificata con successo.</div>
					<?php  }
				else {
					$kaLog->add("ERR","Errore durante la sostituzione dell'immagine ".$img['filename']." (<em>ID: ".$img['idimg']."</em>)");
					echo '<div class="alert">'.$log.'</div>';
					}
				}
			if(isset($_POST['savethumb'])) {
				/* SALVA MODIFICHE THUMB */
				$log="";
				isset($_POST['autoresizethumb'])?$_POST['autoresizethumb']=true:$_POST['autoresizethumb']=false;
				$img=$kaImages->getImage($_GET['idimg']);
				$idimg=$kaImages->setThumb($img['idimg'],$_FILES['thumbnail']['tmp_name'],$_FILES['thumbnail']['name'],$_POST['autoresizethumb']);
				if($log=="") {
					$kaLog->add("UPD","Sostituita la thumbnail ".$img['thumb']['filename']." (<em>ID: ".$img['idimg']."</em>)");
					?>
					<div class="success"><?= $kaTranslate->translate('Img:Thumbnail successfully updated'); ?></div>
					<?php  }
				else {
					$kaLog->add("ERR","Errore durante la sostituzione della thumbnail ".$img['thumb']['filename']." (<em>ID: ".$img['idimg']."</em>)");
					echo '<div class="alert">'.$log.'</div>';
					}
				}
			elseif(isset($_POST['hotlinktoimg'])) {
				/* IMPORTA HOTLINK */
				$log="";
				//controllo l'esistenza/raggiungibilità dell'immagine
				$file_headers=@get_headers($_POST['image']);
				if(!$file_headers) $log="URL non valido"; 
				else if($file_headers[0]=='HTTP/1.1 404 Not Found') $log="Immagine non trovata";
				else {
					$img=$kaImages->getImage($_GET['idimg']);
					isset($_POST['autoresize'])?$_POST['autoresize']=true:$_POST['autoresize']=false;
					if(!isset($_POST['imgwidth'])) $_POST['imgwidth']=0;
					if(!isset($_POST['imgheight'])) $_POST['imgheight']=0;
					$idimg=$kaImages->updateImage($img['idimg'],$_POST['img'],basename($_POST['img']),$_POST['autoresize'],$_POST['imgwidth'],$_POST['imgheight']);
					if($idimg==false) $log.="Errore durante l'importazione del file ".$img['url'].".<br />";
					}

				if($log=="") {
					$kaLog->add("UPD","Importata l'immagine hotlink ".$_POST['img']." in locale (<em>ID: ".$img['idimg']."</em>)");
					?>
					<div class="success"><?= $kaTranslate->translate('Img:Image successfully uploaded'); ?></div><br />
					<?php  printInsertButtons($idimg); ?>
					<?php  }
				else {
					$kaLog->add("ERR","Errore di importazione dell'immagine hotlink ".$_POST['img']." in locale (<em>ID: ".$img['idimg']."</em>)");
					echo '<div class="alert">'.$log.'</div><br />';
					}
				}
			?>
			<form action="?idimga=<?= $_GET['idimga']; ?>&forcerefresh=<?= $_GET['forcerefresh']; ?>&refid=<?php echo $_GET['refid']; ?>&mediatable=<?php echo $_GET['mediatable']; ?>&mediaid=<?php echo $_GET['mediaid']; ?>&search=<?php echo $_GET['search']; ?>&action=<?= $_GET['action']; ?>&start=<?= $_GET['start']; ?>&max=<?= $_GET['max']; ?>" method="post" enctype="multipart/form-data">
				<?php  $img=$kaImages->getImage($_GET['idimg']); ?>
				<table style="margin:10px auto;">
				<tr><td align="center"><h2>Immagine</h2><img src="<?= ($img['hotlink']==false?BASEDIR:'').$img['url']; ?>" height="100" alt="" /></td>
					<td style="vertical-align:middle;"><label ref="img">Cambia</label><br /><?php 
						if($img['hotlink']==false) { ?><input name="img" type="file" id="img" /> <input name="saveimg" type="submit" value="Salva modifiche" class="smallbutton" /><?php  }
						else { ?><input type="text" name="img" value="<?= str_replace('"','&quot;',$img['url']); ?>" style="width:300px;"> <input name="save" type="submit" value="Salva modifiche" class="smallbutton" /> <input name="hotlinktoimg" type="submit" value="Importa nel sito" class="smallbutton" /><?php  }
						?><br />
						<input type="checkbox" name="autoresize" id="autoresize" value="1" checked="checked" onchange="this.checked?document.getElementById('manualresize').style.display='none':document.getElementById('manualresize').style.display='block';" /> <label for="autoresize"><?= $kaTranslate->translate('Img:Automatic resize'); ?></label>
						<div id="manualresize" style="display:none;"><label for="imgwidth"><?= $kaTranslate->translate('Img:Width'); ?></label> <input type="text" name="imgwidth" id="imgwidth" value="" style="width:50px;" />px <label for="imgheight"><?= $kaTranslate->translate('Img:Height'); ?></label> <input type="text" name="imgheight" id="imgheight" value="" style="width:50px;" />px</div><br />
						</td></tr>
				<tr><td align="center"><h2><?= $kaTranslate->translate('Img:Thumbnail'); ?></h2><img src="<?= BASEDIR.$img['thumb']['url']; ?>" height="100" alt="" /></td>
					<td style="vertical-align:middle;">
						<label ref="thumbnail">Cambia</label><br /><input name="thumbnail" type="file" id="thumbnail" /> <input name="savethumb" type="submit" value="Salva modifiche" class="smallbutton" /><br />
						<input type="checkbox" name="autoresizethumb" id="autoresizethumb" value="1" checked="checked" /> <label for="autoresizethumb"><?= $kaTranslate->translate('Img:Automatic resize'); ?></label>
						</td>
					</tr>
				</table><br />
				<div class="note">P.s: Attento! Modificando questa immagine, essa verrà cambiata in tutti i posti in cui è stata utilizzata!</div>
				</form>
			<?php  }

		elseif($_GET['action']=="istances") {
			?><h2>Utilizzo</h2><br /><?php 
			$tables=array();
			foreach(get_defined_constants() as $ka=>$v) {
				if(substr($ka,0,6)=="TABLE_") $tables[$ka]=$v;
				}
			asort($tables);
			?><table class="tabella" style="margin:0 auto;"><thead><tr><th>CONTESTO</th><th>ID</th><th>TITOLO</th></thead><tbody><?php 
			foreach($tables as $ka=>$t) {
				$q="SELECT * FROM ".$t." WHERE ";
				$query="SHOW COLUMNS FROM ".$t."";
				$results=mysql_query($query);
				$primary="";
				while($row=mysql_fetch_array($results)) {
					if($row['Key']=='PRI') $primary=$row['Field'];
					if(substr($row['Type'],0,7)=='varchar'||substr($row['Type'],0,4)=='text') {
						$q.=" `".$row['Field']."` LIKE '%id=\"img".$_GET['idimg']."\"%' ";
						$q.=" OR `".$row['Field']."` LIKE '%id=\"thumb".$_GET['idimg']."\"%' ";
						$q.=" OR ";
						}
					}
				if($primary!="") {
					$q.=$primary.'=0 ';
					$rs=mysql_query($q);
					while($r=mysql_fetch_array($rs)) { ?>
						<tr><td class="small"><?= substr($ka,6); ?></td><td class="small"><?= $r[$primary]; ?></td><td><?= isset($r['titolo'])?$r['titolo']:'<em>Non disponibile</em>'; ?></td>
						<?php  }
					}
				}
			?></tbody></table><?php 
			
			?>
			<?php  }

		elseif($_GET['action']=="fullsize") {
			$img=$kaImages->getImage($_GET['idimg']);
			?>
			<img src="<?= BASEDIR.$img['url']; ?>" width="<?= $img['width']; ?>" height="<?= $img['height']; ?>" alt="" />
			<?php  }

		elseif($_GET['action']=="delete") {
			if(isset($_POST['delete'])) {
				$log="";
				$img=$kaImages->getImage($_GET['idimg']);
				if(!$kaImgallery->del($_GET['idimga'],$_GET['start'],$_GET['max'])) $log.="Errore durante la rimozione dell'immagine.<br />";
			
				if($log=="") {
					$kaLog->add("DEL","Rimossa l'immagine ".$img['filename']." dalla galleria (<em>ID: ".$img['idimg']."</em>)");
					?>
					<div class="success">Immagine rimossa dalla galleria con successo (l'immagine &egrave; ancora disponibile nel repository).</div>
					<script type="text/javascript">
						ifr=window.parent.document.getElementById('<?php echo $_GET['refid']; ?>');
						ifr.src=ifr.src;
						window.parent.b3_openMessage('Immagine rimossa dalla galleria con successo.',true);
						window.parent.k_closeIframeWindow();
						</script>
					<?php  }
				else {
					$kaLog->add("ERR","Errore durante l'eliminazione dell'immagine ".$img['filename']." (<em>ID: ".$img['idimg']."</em>)");
					echo '<div class="alert">'.$log.'</div>';
					}
				}
			else {
				?>
				<form action="?idimga=<?= $_GET['idimga']; ?>&forcerefresh=<?= $_GET['forcerefresh']; ?>&refid=<?php echo $_GET['refid']; ?>&mediatable=<?php echo $_GET['mediatable']; ?>&mediaid=<?php echo $_GET['mediaid']; ?>&search=<?php echo $_GET['search']; ?>&action=<?= $_GET['action']; ?>&start=<?= $_GET['start']; ?>&max=<?= $_GET['max']; ?>" method="post" enctype="multipart/form-data">
					<?php  $img=$kaImages->getImage($_GET['idimg']); ?>
					<table style="margin:10px auto;">
					<tr><td colspan="2" align="center"><img src="<?= BASEDIR.$img['thumb']['url']; ?>" height="100" alt="" /><br /><br />
					Stai per eliminare l'immagine dalla galleria, rester&agrave; comunque disponibile nel repository: sei sicuro di volerlo fare?</td></tr>
					</table><br />
					
					<div class="submit"><input type="submit" name="delete" value="ELIMINA l'immagine" class="button" /></div>
					</form>
				<?php  }
			}
		?>
		</div>
	<?php  }


else {	
	function printInsertButtons($idimg) {
		?>
		<br />
		<div class="success">Immagine inserita con successo...</div>
		<script type="text/javascript">
			ifr=window.parent.document.getElementById('<?php echo $_GET['refid']; ?>');
			ifr.src=ifr.src;
			window.parent.b3_openMessage('Immagine inserita con successo.',true);
			window.parent.k_closeIframeWindow();
			</script>
		<?php  }

	/* UPLOAD SINGOLA IMMAGINE */
	?>
	<div id="imgheader">
		<h1><?= $kaTranslate->translate('Img:Insert a picture'); ?></h1>
		<a href="javascript:window.parent.k_closeIframeWindow();" class="closeWindow"><img src="<?= ADMINRELDIR; ?>img/closeWindow.gif" alt="Close" width="9" height="9" /></a>
		<div class="smenu sel">
			<ul>
			<?php 
			$menu=array();
			$menu['upload']=$kaTranslate->translate('Img:Upload from computer');
			$menu['repository']=$kaTranslate->translate('Img:Choose from repository');
			$menu['internet']=$kaTranslate->translate('Img:Choose from internet');
			if(!isset($_GET['action'])) $_GET['action']='upload';
			foreach($menu as $ka=>$m) {
				echo '<li><a href="?idimg='.$_GET['idimg'].'&start='.$_GET['start'].'&max='.$_GET['max'].'&forcerefresh='.$_GET['forcerefresh'].'&refid='.$_GET['refid'].'&mediatable='.$_GET['mediatable'].'&mediaid='.$_GET['mediaid'].'&search='.$_GET['search'].'&action='.$ka.'&start='.$_GET['start'].'&max='.$_GET['max'].'" class="'.($_GET['action']==$ka?'sel':'').'">'.$m.'</a></li>';
				}
			?>
			</ul>
			</div>
		</div>
.
	<div id="imgcontents">
		<?php 
		/* UPLOAD SINGOLA IMMAGINE */
		if($_GET['action']=="upload") {
			if(isset($_POST['save'])) {
				$log="";
				isset($_POST['autoresize'])?$_POST['autoresize']=true:$_POST['autoresize']=false;
				if(!isset($_POST['imgwidth'])) $_POST['imgwidth']=0;
				if(!isset($_POST['imgheight'])) $_POST['imgheight']=0;
				foreach($_FILES['image']['tmp_name'] as $ka=>$f) {
					$idimg=$kaImages->upload($f,$_FILES['image']['name'][$ka],$_GET['mediatable'],$_GET['mediaid'],$_POST['alt'],$_POST['autoresize'],$_POST['imgwidth'],$_POST['imgheight']);
					if($idimg==false) $log.="Errore durante il caricamento del file ".$_FILES['image']['name'].".<br />";
					else {
						if(isset($_POST['autothumbnail'])) $kaImages->setThumb($idimg);
						else { $kaImages->setThumb($idimg,$_FILES['thumbnail']['tmp_name'],$_FILES['thumbnail']['name'],false); }
						$kaImgallery->add($_GET['mediatable'],$_GET['mediaid'],$idimg,$_GET['start'],$_GET['max']);
						}
					}

				if($log=="") {
					?>
					<div class="success"><?= $kaTranslate->translate('Img:Image successfully uploaded'); ?></div><br />
					<?php  if($_GET['forcerefresh']==true) { ?>
						<script type="text/javascript">
							window.parent.location.reload();
							</script>
						<?php  } ?>
					<?php  }
				else echo '<div class="alert">'.$log.'</div><br />';
				}
			else { ?>
				<script type="text/javascript">
					var uploadConfig_onSuccessMsg='<?= addslashes($kaTranslate->translate('Img:Image successfully uploaded')); ?>';
					var uploadConfig_refid='<?= addslashes($_GET['refid']); ?>';
					</script>
				<form action="?refid=<?= $_GET['refid']; ?>&start=<?= $_GET['start']; ?>&max=<?= $_GET['max']; ?>&mediatable=<?= $_GET['mediatable']; ?>&mediaid=<?= $_GET['mediaid']; ?>&search=<?= $_GET['search']; ?>&action=<?= $_GET['action']; ?>&start=<?= $_GET['start']; ?>&max=<?= $_GET['max']; ?>&forcerefresh=true" method="post" enctype="multipart/form-data">
					<input type="hidden" name="save" value="y" />
					<table style="margin:10px auto;">
					<tr style="font-size:1.6em;">
						<td><label for="image"><?= $kaTranslate->translate('Img:Choose a picture'); ?></label></td>
						<td>
							<div class="advFileInput">
								<input type="file" id="image" name="image[]" accept="image/*" multiple onchange="showSelectedFiles(this)" />
								<input type="button" id="progressNumber" value="<?= $kaTranslate->translate('Img:Browse'); ?>" class="button" />
								<div id="fileList"></div>
								</div>
							</td>
						</tr>
					<tr><td></td><td>
						<input type="checkbox" name="autoresize" id="autoresize" value="1" checked="checked" onchange="this.checked?document.getElementById('manualresize').style.display='none':document.getElementById('manualresize').style.display='block';" /> <label for="autoresize"><?= $kaTranslate->translate('Img:Automatic resize'); ?></label>
						<div id="manualresize" style="display:none;"><label for="imgwidth"><?= $kaTranslate->translate('Img:Width'); ?></label> <input type="text" name="imgwidth" id="imgwidth" value="" style="width:50px;" />px <label for="imgheight"><?= $kaTranslate->translate('Img:Height'); ?></label> <input type="text" name="imgheight" id="imgheight" value="" style="width:50px;" />px</div><br />
						<input type="checkbox" name="autothumbnail" id="autothumbnail" value="1" checked="checked" onchange="this.checked?document.getElementById('manualthumb').style.display='none':document.getElementById('manualthumb').style.display='block';" /> <label for="autothumbnail"><?= $kaTranslate->translate('Img:Automatic thumbnail'); ?></label>
						<div id="manualthumb" style="display:none;"><label for="thumbnail"><?= $kaTranslate->translate('Img:Choose a custom thumbnail'); ?></label> <input type="file" id="thumbnail" name="thumbnail" /></div><br />
						<br />
						</td></tr>
					<tr><td><label for="alt"><?= $kaTranslate->translate('Img:Caption'); ?></label></td><td><textarea name="alt" id="alt" style="width:100%;height:50px;"></textarea></td></tr>
					</table><br />
					<div class="submit"><input type="button" name="save" value="<?= $kaTranslate->translate('Img:Upload picture'); ?>" class="button" onclick="uploadFile(this.form)" /></div>
					</form>
				<?php  }
			}

		/* SELEZIONA DAL REPOSITORY */
		if($_GET['action']=="repository") {
			if(isset($_GET['insert'])&&intval($_GET['insert'])>0) {
				$kaImgallery->add($_GET['mediatable'],$_GET['mediaid'],$_GET['insert'],$_GET['start'],$_GET['max']);
				printInsertButtons($_GET['insert']);
				}
			else {
				$conditions="";
				if(!isset($_GET['search'])) $_GET['search']="";
				else $conditions="filename LIKE '%".$_GET['search']."%' OR alt LIKE '%".$_GET['search']."%'";
				$immagini=$kaImages->getList("","","idimg DESC",$conditions,false,40);
				if(!is_array($immagini)||count($immagini)==0) $immagini=array();
				$n=0;
				?>
				<div class="box">
					<form method="get" action="">
					<input type="hidden" name="action" value="<?= $_GET['action']; ?>" />
					<input type="hidden" name="refid" value="<?= $_GET['refid']; ?>" />
					<input type="hidden" name="mode" value="<?= $_GET['mode']; ?>" />
					<input type="hidden" name="forcerefresh" value="<?= $_GET['forcerefresh']; ?>" />
					<input type="hidden" name="start" value="<?= $_GET['start']; ?>" />
					<input type="hidden" name="max" value="<?= $_GET['max']; ?>" />
					<input type="hidden" name="mediatable" value="<?= $_GET['mediatable']; ?>" />
					<input type="hidden" name="mediaid" value="<?= $_GET['mediaid']; ?>" />
					<?= $kaTranslate->translate('UI:Search'); ?>: <input type="text" name="search" style="width:300px;" value="<?= $_GET['search']; ?>" />
					<input type="submit" class="smallbutton" value="<?= $kaTranslate->translate('UI:refresh'); ?>" />
					</form>
					</div>
				<div class="imagePreview"><ul><?php 				foreach($immagini as $img) {
					$filename=BASERELDIR.DIR_IMG.$img['idimg'].'/'.$img['filename'];
					$thumbname=BASERELDIR.DIR_IMG.$img['idimg'].'/'.$img['thumb']['filename'];
					if($img['thumb']['filename']!=""&&file_exists($thumbname)&&!is_dir($thumbname)) {
						$size=getimagesize($thumbname);
						?><li>
						<div class="preview">
							<?php 							$size[1]=150/$size[0]*$size[1];
							$size[0]=150;
							if($size[1]>100) {
								$size[0]=100/$size[1]*$size[0];
								$size[1]=100;
								}
							?>
							<a href="?refid=<?= $_GET['refid']; ?>&start=<?= $_GET['start']; ?>&max=<?= $_GET['max']; ?>&mediatable=<?= $_GET['mediatable']; ?>&mediaid=<?= $_GET['mediaid']; ?>&search=<?= $_GET['search']; ?>&action=<?= $_GET['action']; ?>&start=<?= $_GET['start']; ?>&max=<?= $_GET['max']; ?>&insert=<?= $img['idimg']; ?>">
							<img src="<?= $thumbname.'?'.rand(0,666); ?>" width="<?= $size[0]; ?>" height="<?= $size[1]; ?>" style="padding:<?= (100-$size[1])/2; ?>px 0;" alt="<?= str_replace('"','&quot;',trim(strip_tags($img['alt']))); ?>" />
							</a>
							</div>
						<div class="options">
							<?= $img['filename']; ?><br />
							<?= $img['width'].'x'.$img['height'].' px'; ?><br />
							<a href="?refid=<?= $_GET['refid']; ?>&start=<?= $_GET['start']; ?>&max=<?= $_GET['max']; ?>&mediatable=<?= $_GET['mediatable']; ?>&mediaid=<?= $_GET['mediaid']; ?>&search=<?= $_GET['search']; ?>&action=<?= $_GET['action']; ?>&start=<?= $_GET['start']; ?>&max=<?= $_GET['max']; ?>&insert=<?= $img['idimg']; ?>" class="smallbutton">Inserisci</a>
							</div>
						</li><?php 						$n++;
						}
					}
				?></ul></div>
				<?php 				if($n==0) echo '<div class="empty">Nessuna immagine caricata</div>';
			}
		}


		/* SELEZIONA DA INTERNET */
		if($_GET['action']=="internet") {
			if(isset($_POST['save'])) {
				$log="";
				//controllo l'esistenza/raggiungibilità dell'immagine
				$file_headers=@get_headers($_POST['image']);
				if(!$file_headers) $log="URL non valido"; 
				else if($file_headers[0]=='HTTP/1.1 404 Not Found') $log="Immagine non trovata";
				else {
					isset($_POST['autoresize'])?$_POST['autoresize']=true:$_POST['autoresize']=false;
					$idimg=$kaImages->upload($_POST['image'],basename($_POST['image']),$_GET['mediatable'],$_GET['mediaid'],$_POST['alt'],$_POST['autoresize']);
					if($idimg==false) $log.="Errore durante la copia del file ".$_POST['image'].".<br />";
					else {
						if(isset($_POST['autothumbnail'])) $kaImages->setThumb($idimg);
						else { $kaImages->setThumb($idimg,$_FILES['thumbnail']['tmp_name'],$_FILES['thumbnail']['name'],false); }
						}
					if($_POST['howtoembed']=='hotlink') {
						$img=$kaImages->getImage($idimg);
						unlink(BASERELDIR.$img['url']);
						$kaImages->setHotlink($idimg,$_POST['image']);
						}
					}

				if($log=="") {
					?>
					<div class="success"><?= $kaTranslate->translate('Img:Image successfully uploaded'); ?></div><br />
					<?php 
					$kaImgallery->add($_GET['mediatable'],$_GET['mediaid'],$idimg,$_GET['start'],$_GET['max']);
					printInsertButtons($idimg); ?>
					<?php  }
				else echo '<div class="alert">'.$log.'</div><br />';
				}
			else { ?>
				<form action="?refid=<?= $_GET['refid']; ?>&start=<?= $_GET['start']; ?>&max=<?= $_GET['max']; ?>&mediatable=<?= $_GET['mediatable']; ?>&mediaid=<?= $_GET['mediaid']; ?>&search=<?= $_GET['search']; ?>&action=<?= $_GET['action']; ?>&start=<?= $_GET['start']; ?>&max=<?= $_GET['max']; ?>" method="post" enctype="multipart/form-data">
					<table style="margin:10px auto;">
					<tr style="font-size:1.6em;"><td><label for="image"><?= $kaTranslate->translate('Img:Image URL'); ?></label></td><td><input type="text" id="image" name="image" /></td></tr>
					<tr><td></td><td>
						<input type="radio" name="howtoembed" id="copy" value="copy" checked="checked" /> <label for="copy"><?= $kaTranslate->translate('Img:Copy to your website'); ?></label><br />
						<input type="radio" name="howtoembed" id="hotlink" value="hotlink" /> <label for="hotlink"><?= $kaTranslate->translate('Img:Load as Hotlink'); ?></label>
						<br />
						<input type="checkbox" name="autoresize" id="autoresize" value="1" checked="checked" onchange="this.checked?document.getElementById('manualresize').style.display='none':document.getElementById('manualresize').style.display='block';" /> <label for="autoresize"><?= $kaTranslate->translate('Img:Automatic resize'); ?></label>
						<div id="manualresize" style="display:none;"><label for="width"><?= $kaTranslate->translate('Img:Width'); ?></label> <input type="text" value="" style="width:50px;" />px <label for="height"><?= $kaTranslate->translate('Img:Height'); ?></label> <input type="text" value="" style="width:50px;" />px</div><br />
						<input type="checkbox" name="autothumbnail" id="autothumbnail" value="1" checked="checked" onchange="this.checked?document.getElementById('manualthumb').style.display='none':document.getElementById('manualthumb').style.display='block';" /> <label for="autothumbnail"><?= $kaTranslate->translate('Img:Automatic thumbnail'); ?></label>
						<div id="manualthumb" style="display:none;"><label for="thumbnail"><?= $kaTranslate->translate('Img:Choose a custom thumbnail'); ?></label> <input type="file" id="thumbnail" name="thumbnail" /></div><br />
						<br />
						</td></tr>
					<tr><td><label for="alt"><?= $kaTranslate->translate('Img:Caption'); ?></label></td><td><textarea name="alt" id="alt" style="width:100%;height:50px;"></textarea></td></tr>
					</table><br />
					<div class="submit"><input type="submit" name="save" value="<?= $kaTranslate->translate('Img:Upload picture'); ?>" class="button" /></div>
					</form>
				<?php  }
		}

		?>
		</div>
	<?php  } ?>

	</div>
</body>
</html>
