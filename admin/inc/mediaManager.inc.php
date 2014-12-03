<?php /* (c) Kalamun.org - GNU/GPL 3 */

require_once('./connect.inc.php');
require_once('kalamun.lib.php');
require_once('./sessionmanager.inc.php');
require_once('./main.lib.php');
if(!isset($_SESSION['iduser'])) die('Non hai il permesso di utilizzare questa funzione');

/* set default timezone in PHP and MySQL */
$timezone=kaGetVar('timezone',1);
if($timezone!="") {
	date_default_timezone_set($timezone);
	$query="SET time_zone='".date("P")."'";
	ksql_query($query);
	}

require_once('./log.lib.php');
$kaLog=new kaLog();

define("PAGE_NAME","Media Manager");
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
if(!isset($_GET['mediatable'])) $_GET['mediatable']="";
if(!isset($_GET['mediaid'])) $_GET['mediaid']="";
if(!isset($_GET['search'])) $_GET['search']="";
if(!isset($_GET['idmedia'])) $_GET['idmedia']=0;
if(!isset($_GET['refid'])) $_GET['refid']=0;
include('./media.lib.php');
$kaMedia=new kaMedia();

if(intval($_GET['idmedia'])>0) {
	/* MODIFICA SINGOLA CONTENUTO MULMEDIALE */
	 ?>
	<div id="mediaheader">
		<h1>Modifica Contenuto Multimediale</h1>
		<a href="javascript:window.parent.k_closeIframeWindow();" class="closeWindow"><img src="<?= ADMINRELDIR; ?>img/closeWindow.gif" alt="Close" width="9" height="9" /></a>
		<div class="smenu sel">
			<ul>
			<?php 
			$menu=array();
			$menu['properties']="Propriet&agrave; del contenuto multimediale";
			$menu['files']="Files";
			$menu['istances']="Utilizzo";
			$menu['fullsize']="Guarda a dimensioni reali";
			$menu['delete']="Elimina";
			if(!isset($_GET['action'])) $_GET['action']='properties';
			foreach($menu as $ka=>$m) {
				echo '<li><a href="?idmedia='.$_GET['idmedia'].'&forcerefresh='.$_GET['forcerefresh'].'&refid='.$_GET['refid'].'&mediatable='.$_GET['mediatable'].'&mediaid='.$_GET['mediaid'].'&search='.$_GET['search'].'&action='.$ka.'&mode='.$_GET['mode'].'" class="'.($_GET['action']==$ka?'sel':'').'">'.$m.'</a></li>';
				}
			?>
			</ul>
			</div>
		</div>
.
	<div id="mediacontents">
		<?php 
		if($_GET['action']=="properties") {
			if(isset($_POST['save'])) {
				$log="";
				$media=$kaMedia->getMedia($_GET['idmedia']);
				if($kaMedia->updateProperties($_GET['idmedia'],$_POST['mediatitle'],$_POST['alt'],$_POST['mediawidth'],$_POST['mediaheight'],$_POST['mediaduration'])==false) $log.="Errore durante il salvataggio delle modifiche.<br />";
			
				if($log=="") {
					$kaLog->add("UPD","Modificate le propriet&agrave; del contenuto multimediale ".$media['filename']." (<em>ID: ".$media['idmedia']."</em>)");
					?>
					<div class="success">Contenuto Multimediale modificato con successo.</div>
					<?php  }
				else {
					$kaLog->add("ERR","Errore durante la modifica delle propriet&agrave; del contenuto multimediale ".$media['filename']." (<em>ID: ".$media['idmedia']."</em>)");
					echo '<div class="alert">'.$log.'</div>';
					}
				}
			?>
			<form action="?idmedia=<?= $_GET['idmedia']; ?>&forcerefresh=<?= $_GET['forcerefresh']; ?>&refid=<?php echo $_GET['refid']; ?>&mediatable=<?php echo $_GET['mediatable']; ?>&mediaid=<?php echo $_GET['mediaid']; ?>&search=<?php echo $_GET['search']; ?>&action=<?= $_GET['action']; ?>" method="post" enctype="multipart/form-data">
				<?php  $media=$kaMedia->getMedia($_GET['idmedia']); ?>
				<table style="margin:10px auto;">
				<tr><td colspan="2" align="center"><img src="<?= BASEDIR.$media['thumb']['url']; ?>" height="100" alt="" /></td></tr>
				<tr><td><label for="mediatitle">Titolo</label></td><td><input type="text" name="mediatitle" id="mediatitle" value="<?= b3_lmthize($media['title'],"input"); ?>" style="width:300px;" /></td></tr>
				<tr><td><label for="mediawidth">Larghezza</label></td><td><input type="text" name="mediawidth" id="mediawidth" value="<?= $media['width']; ?>" style="width:50px;" /> px</td></tr>
				<tr><td><label for="mediaheight">Altezza</label></td><td><input type="text" name="mediaheight" id="mediaheight" value="<?= $media['height']; ?>" style="width:50px;" /> px</td></tr>
				<tr><td><label for="mediaduration">Durata</label></td><td><input type="text" name="mediaduration" id="mediaduration" value="<?= $media['duration']; ?>" style="width:50px;" /> secondi</td></tr>
				<tr><td><label for="alt">Didascalia</label></td><td><textarea name="alt" id="alt" style="width:300px;height:50px;"><?= b3_lmthize($media['alt'],"textarea"); ?></textarea></td></tr>
				</table><br />
				<div class="note">P.s: Attento! Modificando questo contenuto multimediale, esso verrà cambiato in tutti i posti in cui è stato utilizzato!</div>
				<div class="submit"><input type="submit" name="save" value="Salva le modifiche" class="button" /></div>
				</form>
			<?php  }

		elseif($_GET['action']=="files") {
			if(isset($_POST['saveimg'])) {
				/* SALVA MODIFICHE CONTENUTO MULMEDIALE */
				$log="";
				$media=$kaMedia->getMedia($_GET['idmedia']);
				isset($_POST['autoresize'])?$_POST['autoresize']=true:$_POST['autoresize']=false;
				if(!isset($_POST['mediawidth'])) $_POST['mediawidth']=0;
				if(!isset($_POST['mediaheight'])) $_POST['mediaheight']=0;
				$idmedia=$kaMedia->updateMedia($media['idmedia'],$_FILES['media']['tmp_name'],$_FILES['media']['name'],$_POST['autoresize'],$_POST['mediawidth'],$_POST['mediaheight']);
			
				if($log=="") {
					$kaLog->add("UPD","Sostituita l'contenuto multimediale ".$media['filename']." (<em>ID: ".$media['idmedia']."</em>)");
					?>
					<div class="success">Contenuto Multimediale modificato con successo.</div>
					<?php  }
				else {
					$kaLog->add("ERR","Errore durante la sostituzione dell'contenuto multimediale ".$media['filename']." (<em>ID: ".$media['idmedia']."</em>)");
					echo '<div class="alert">'.$log.'</div>';
					}
				}
			if(isset($_POST['savethumb'])) {
				/* SALVA MODIFICHE THUMB */
				$log="";
				isset($_POST['autoresizethumb'])?$_POST['autoresizethumb']=true:$_POST['autoresizethumb']=false;
				$media=$kaMedia->getMedia($_GET['idmedia']);
				$idmedia=$kaMedia->setThumb($media['idmedia'],$_FILES['thumbnail']['tmp_name'],$_FILES['thumbnail']['name'],$_POST['autoresizethumb']);
				if($log=="") {
					$kaLog->add("UPD","Sostituita la thumbnail ".$media['thumb']['filename']." (<em>ID: ".$media['idmedia']."</em>)");
					?>
					<div class="success">Miniatura modificata con successo.</div>
					<?php  }
				else {
					$kaLog->add("ERR","Errore durante la sostituzione della thumbnail ".$media['thumb']['filename']." (<em>ID: ".$media['idmedia']."</em>)");
					echo '<div class="alert">'.$log.'</div>';
					}
				}
			elseif(isset($_POST['hotlinktoimg'])) {
				/* IMPORTA HOTLINK */
				$log="";
				//controllo l'esistenza/raggiungibilità dell'contenuto multimediale
				$file_headers=@get_headers($_POST['media']);
				if(!$file_headers) $log="URL non valido"; 
				else if($file_headers[0]=='HTTP/1.1 404 Not Found') $log="Contenuto Multimediale non trovata";
				else {
					$media=$kaMedia->getMedia($_GET['idmedia']);
					isset($_POST['autoresize'])?$_POST['autoresize']=true:$_POST['autoresize']=false;
					if(!isset($_POST['mediawidth'])) $_POST['mediawidth']=0;
					if(!isset($_POST['mediaheight'])) $_POST['mediaheight']=0;
					$idmedia=$kaMedia->updateMedia($media['idmedia'],$_POST['media'],basename($_POST['media']),$_POST['autoresize'],$_POST['mediawidth'],$_POST['mediaheight']);
					if($idmedia==false) $log.="Errore durante l'importazione del file ".$media['url'].".<br />";
					}

				if($log=="") {
					$kaLog->add("UPD","Importata l'contenuto multimediale hotlink ".$_POST['media']." in locale (<em>ID: ".$media['idmedia']."</em>)");
					?>
					<div class="success">Contenuto Multimediale caricata con successo.</div><br />
					<?php  printInsertButtons($idmedia); ?>
					<?php  }
				else {
					$kaLog->add("ERR","Errore di importazione dell'contenuto multimediale hotlink ".$_POST['media']." in locale (<em>ID: ".$media['idmedia']."</em>)");
					echo '<div class="alert">'.$log.'</div><br />';
					}
				}
			elseif(isset($_POST['save'])) {
				$log="";
				$media=$kaMedia->getMedia($_GET['idmedia']);
				if(!$kaMedia->setHotlink($_GET['idmedia'],$_POST['media'])) $log="Problema durante il salvataggio dell'hotlink";
				if($log=="") {
					$kaLog->add("UPD","Modificato l'hotlink ".$media['hotlink']." (<em>ID: ".$media['idmedia']."</em>)");
					?>
					<div class="success">Hotlink modificato con successo.</div>
					<?php  }
				else {
					$kaLog->add("ERR","Errore durante la sostituzione dell'hotlink ".$media['hotlink']." (<em>ID: ".$media['idmedia']."</em>)");
					echo '<div class="alert">'.$log.'</div>';
					}
				}
			elseif(isset($_POST['htmlcodesave'])) {
				$log="";
				$media=$kaMedia->getMedia($_GET['idmedia']);
				if($_POST['htmlcode']=="") $_POST['htmlcode']=" ";
				if(!$kaMedia->updateProperties($_GET['idmedia'],null,null,null,null,null,$_POST['htmlcode'])) $log="Problema durante il salvataggio del codice incorporato";
				if($log=="") {
					$kaLog->add("UPD","Modificato il codice incorporato (<em>ID: ".$media['idmedia']."</em>)");
					?>
					<div class="success">Codice modificato con successo.</div>
					<?php  }
				else {
					$kaLog->add("ERR","Errore durante la sostituzione del codice incorporato (<em>ID: ".$media['idmedia']."</em>)");
					echo '<div class="alert">'.$log.'</div>';
					}
				}
			?>
			<form action="?idmedia=<?= $_GET['idmedia']; ?>&forcerefresh=<?= $_GET['forcerefresh']; ?>&refid=<?php echo $_GET['refid']; ?>&mediatable=<?php echo $_GET['mediatable']; ?>&mediaid=<?php echo $_GET['mediaid']; ?>&search=<?php echo $_GET['search']; ?>&action=<?= $_GET['action']; ?>" method="post" enctype="multipart/form-data">
				<?php  $media=$kaMedia->getMedia($_GET['idmedia']); ?>
				<table style="margin:10px auto;">
				<?php  if(trim($media['htmlcode']=="")) { ?>
				<tr><td align="center"><h2>Contenuto Multimediale</h2><a href="<?= ($media['hotlink']==false?BASEDIR:'').$media['url']; ?>" alt="">Apri</a></td>
					<td style="vertical-align:middle;"><label ref="media">Cambia</label><br /><?php 
						if($media['hotlink']==false) { ?><input name="media" type="file" id="media" /> <input name="saveimg" type="submit" value="Salva modifiche" class="smallbutton" /><?php  }
						else { ?><input type="text" name="media" value="<?= str_replace('"','&quot;',$media['url']); ?>" style="width:300px;"> <input name="save" type="submit" value="Salva modifiche" class="smallbutton" /> <input name="hotlinktoimg" type="submit" value="Importa nel sito" class="smallbutton" /><?php  }
						?><br /><br />
						</td></tr>
					<?php  }
				else { ?>
				<tr><td align="center"><h2>Codice incorporato</h2></td>
					<td style="vertical-align:middle;"><label ref="media">Cambia</label><br />
						<textarea name="htmlcode" style="width:400px;height:100px;"><?= b3_lmthize($media['htmlcode'],"textarea"); ?></textarea>
						<input name="htmlcodesave" type="submit" value="Salva codice" class="smallbutton" />
						<br /><br />
						</td></tr>
					<?php  } ?>
				<tr><td align="center"><h2>Miniatura</h2><img src="<?= BASEDIR.$media['thumb']['url']; ?>" height="100" alt="" /></td>
					<td style="vertical-align:middle;">
						<label ref="thumbnail">Cambia</label><br /><input name="thumbnail" type="file" id="thumbnail" /> <input name="savethumb" type="submit" value="Salva modifiche" class="smallbutton" /><br />
						<input type="checkbox" name="autoresizethumb" id="autoresizethumb" value="1" checked="checked" /> <label for="autoresizethumb">Ridimensionamento automatico</label>
						</td>
					</tr>
				</table><br />
				<div class="note">P.s: Attento! Modificando questo contenuto multimediale, esso verrà cambiato in tutti i posti in cui è stato utilizzato!</div>
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
				$results=ksql_query($query);
				$primary="";
				while($row=ksql_fetch_array($results)) {
					if($row['Key']=='PRI') $primary=$row['Field'];
					if(substr($row['Type'],0,7)=='varchar'||substr($row['Type'],0,4)=='text') {
						$q.=" `".$row['Field']."` LIKE '%id=\"media".$_GET['idmedia']."\"%' ";
						$q.=" OR ";
						}
					}
				if($primary!="") {
					$q.=$primary.'=0 ';
					$rs=ksql_query($q);
					while($r=ksql_fetch_array($rs)) { ?>
						<tr><td class="small"><?= substr($ka,6); ?></td><td class="small"><?= $r[$primary]; ?></td><td><?= isset($r['titolo'])?$r['titolo']:'<em>Non disponibile</em>'; ?></td>
						<?php  }
					}
				}
			?></tbody></table><?php 
			
			?>
			<?php  }

		elseif($_GET['action']=="fullsize") {
			$media=$kaMedia->getMedia($_GET['idmedia']);
			?>
			<div style="text-align:center;">
			<h2><a href="<?= ($media['hotlink']==false?BASEDIR:'').$media['url']; ?>" target="_blank">Clicca qui per aprire il file</a></h2>
			<?= ($media['hotlink']==false?BASEDIR:'').$media['url']; ?>
			</div>
			<?php  }

		elseif($_GET['action']=="delete") {
			if(isset($_POST['delete'])) {
				$log="";
				$media=$kaMedia->getMedia($_GET['idmedia']);
				if(!$kaMedia->delete($_GET['idmedia'])) $log.="Errore durante la rimozione del contenuto multimediale.<br />";
			
				if($log=="") {
					$kaLog->add("DEL","Eliminata definitivamente il contenuto multimediale ".$media['filename']." (<em>ID: ".$media['idmedia']."</em>)");
					?>
					<div class="success">Contenuto Multimediale eliminato con successo.</div>
					<?php  if($_GET['forcerefresh']==true) { ?>
						<script type="text/javascript">
							window.parent.location.reload();
							</script>
						<?php  }
					else { ?>
						<script type="text/javascript">
							window.parent.b3_openMessage('Contenuto Multimediale eliminato con successo.',true);
							window.parent.k_closeIframeWindow();
							</script>
						<?php  } ?>
					<?php  }
				else {
					$kaLog->add("ERR","Errore durante l'eliminazione del contenuto multimediale ".$media['filename']." (<em>ID: ".$media['idmedia']."</em>)");
					echo '<div class="alert">'.$log.'</div>';
					}
				}
			else {
				?>
				<form action="?idmedia=<?= $_GET['idmedia']; ?>&forcerefresh=<?= $_GET['forcerefresh']; ?>&refid=<?php echo $_GET['refid']; ?>&mediatable=<?php echo $_GET['mediatable']; ?>&mediaid=<?php echo $_GET['mediaid']; ?>&search=<?php echo $_GET['search']; ?>&action=<?= $_GET['action']; ?>" method="post" enctype="multipart/form-data">
					<?php  $media=$kaMedia->getMedia($_GET['idmedia']); ?>
					<table style="margin:10px auto;">
					<tr><td colspan="2" align="center"><img src="<?= BASEDIR.$media['thumb']['url']; ?>" height="100" alt="" /><br /><br />
					Stai per eliminare <strong>definitivamente</strong> dal sito questo contenuto multimediale: sei sicuro di volerlo fare?</td></tr>
					</table><br />
					
					<div class="note">P.s: Attento! Perderai il contenuto multimediale da tutte le pagine in cui è utilizzato!</div>
					<div class="submit"><input type="submit" name="delete" value="ELIMINA il contenuto multimediale" class="button" /></div>
					</form>
				<?php  }
			}
		?>
		</div>
	<?php  }

elseif($_GET['mode']=='justupload') {
	/* FAI SOLO L'UPLOAD */
	?>
	<div id="mediaheader">
		<h1>Carica Contenuto Multimediale</h1>
		<a href="javascript:window.parent.k_closeIframeWindow();" class="closeWindow"><img src="<?= ADMINRELDIR; ?>img/closeWindow.gif" alt="Close" width="9" height="9" /></a>
		<div class="smenu sel">
			<ul>
			<?php 
			$menu=array();
			$menu['upload']="Scegli dal computer";
			$menu['internet']="Scegli da internet";
			$menu['embed']="Incorpora codice";
			if(!isset($_GET['action'])) $_GET['action']='upload';
			foreach($menu as $ka=>$m) {
				echo '<li><a href="?forcerefresh='.$_GET['forcerefresh'].'&refid='.$_GET['refid'].'&mediatable='.$_GET['mediatable'].'&mediaid='.$_GET['mediaid'].'&search='.$_GET['search'].'&action='.$ka.'&mode='.$_GET['mode'].'" class="'.($_GET['action']==$ka?'sel':'').'">'.$m.'</a></li>';
				}
			?>
			</ul>
			</div>
		</div>
.
	<div id="mediacontents">
		<?php 
		/* UPLOAD SINGOLA CONTENUTO MULMEDIALE */
		if($_GET['action']=="upload") {
			if(isset($_POST['save'])) {
				$log="";
				if(!isset($_POST['resize'])) $_POST['resize']=false;
				if(!isset($_POST['mediawidth'])) $_POST['mediawidth']=0;
				if(!isset($_POST['mediaheight'])) $_POST['mediaheight']=0;
				if(!isset($_POST['mediaduration'])) $_POST['mediaduration']=0;
				$idmedia=$kaMedia->upload($_FILES['media']['tmp_name'],$_FILES['media']['name'],$_GET['mediatable'],$_GET['mediaid'],$_POST['mediatitle'],$_POST['alt'],$_POST['resize'],$_POST['mediawidth'],$_POST['mediaheight'],$_POST['mediaduration']);
				if($idmedia==false) $log.="Errore durante il caricamento del file ".$_FILES['media']['name'].".<br />";
				else {
					$kaMedia->setThumb($idmedia,$_FILES['thumbnail']['tmp_name'],$_FILES['thumbnail']['name'],false);
					}

				if($log=="") {
					?>
					<div class="success">Contenuto Multimediale caricato con successo.</div><br />
					<?php  if($_GET['forcerefresh']==true) { ?>
						<script type="text/javascript">
							window.parent.location.reload();
							</script>
						<?php  }
					else { ?>
						<script type="text/javascript">
							window.parent.b3_openMessage('Contenuto Multimediale caricato con successo.',true);
							window.parent.k_closeIframeWindow();
							</script>
						<?php  } ?>
					<?php  }
				else echo '<div class="alert">'.$log.'</div><br />';
				}
			else { ?>
				<form action="?mode=<?= $_GET['mode']; ?>&forcerefresh=<?= $_GET['forcerefresh']; ?>&refid=<?= $_GET['refid']; ?>&mediatable=<?= $_GET['mediatable']; ?>&mediaid=<?= $_GET['mediaid']; ?>&search=<?= $_GET['search']; ?>&action=<?= $_GET['action']; ?>" method="post" enctype="multipart/form-data">
					<table style="margin:10px auto;">
					<tr style="font-size:1.6em;"><td><label for="media">Scegli un contenuto multimediale</label></td><td><input type="file" id="media" name="media" /></td></tr>
					<tr><td></td><td>
						<div id="manualresize">
						<table>
						<tr><td><label for="mediatitle">Titolo</label></td><td><input type="text" name="mediatitle" id="mediatitle" value="" style="width:300px;" /></td></tr>
						<tr><td><label for="mediawidth">Larghezza</label></td><td><input type="text" name="mediawidth" id="mediawidth" value="" style="width:50px;" /> px</td></tr>
						<tr><td><label for="mediaheight">Altezza</label></td><td><input type="text" name="mediaheight" id="mediaheight" value="" style="width:50px;" /> px</td></tr>
						<tr><td><label for="mediaduration">Durata</label></td><td><input type="text" name="mediaduration" id="mediaduration" value="" style="width:50px;" /> secondi</td></tr>
						</table>
						</div><br />
						<div id="manualthumb"><label for="thumbnail">Scegli una miniatura</label> <input type="file" id="thumbnail" name="thumbnail" /></div><br />
						<br />
						</td></tr>
					<tr><td style="text-align:right;"><label for="alt">Didascalia</label></td><td><textarea name="alt" id="alt" style="width:100%;height:50px;"></textarea></td></tr>
					</table><br />
					<div class="submit"><input type="submit" name="save" value="Carica contenuto multimediale" class="button" /></div>
					</form>
				<?php  }
			}

		/* SELEZIONA DA INTERNET */
		if($_GET['action']=="internet") {
			if(isset($_POST['save'])) {
				$log="";
				//controllo l'esistenza/raggiungibilità del contenuto multimediale
				$file_headers=@get_headers($_POST['media']);
				if(!$file_headers) $log="URL non valido"; 
				else if($file_headers[0]=='HTTP/1.1 404 Not Found') $log="Contenuto Multimediale non trovato";
				else {
					isset($_POST['autoresize'])?$_POST['autoresize']=true:$_POST['autoresize']=false;
					if($_POST['howtoembed']=='hotlink') {
						$idmedia=$kaMedia->upload("","",$_GET['mediatable'],$_GET['mediaid'],$_POST['mediatitle'],$_POST['alt'],$_POST['autoresize'],$_POST['mediawidth'],$_POST['mediaheight'],$_POST['mediaduration']);
						$kaMedia->setHotlink($idmedia,$_POST['media']);
						}
					else {
						$idmedia=$kaMedia->upload($_POST['media'],basename($_POST['media']),$_GET['mediatable'],$_GET['mediaid'],$_POST['mediatitle'],$_POST['alt'],$_POST['autoresize'],$_POST['mediawidth'],$_POST['mediaheight'],$_POST['mediaduration']);
						if($idmedia==false) $log.="Errore durante la copia del file ".$_POST['media'].".<br />";
						}
					if(isset($_FILES['thumbnail']['name'])&&$_FILES['thumbnail']['name']!="") $kaMedia->setThumb($idmedia,$_FILES['thumbnail']['tmp_name'],$_FILES['thumbnail']['name'],false);
					}

				if($log=="") {
					?>
					<div class="success">Contenuto Multimediale caricato con successo.</div><br />
					<?php  if($_GET['forcerefresh']==true) { ?>
						<script type="text/javascript">
							window.parent.location.reload();
							</script>
						<?php  }
					else { ?>
						<script type="text/javascript">
							window.parent.b3_openMessage('Contenuto Multimediale caricato con successo.',true);
							window.parent.k_closeIframeWindow();
							</script>
						<?php  } ?>
					<?php  }
				else echo '<div class="alert">'.$log.'</div><br />';
				}
			else { ?>
				<form action="?mode=<?= $_GET['mode']; ?>&forcerefresh=<?= $_GET['forcerefresh']; ?>&refid=<?= $_GET['refid']; ?>&mediatable=<?= $_GET['mediatable']; ?>&mediaid=<?= $_GET['mediaid']; ?>&search=<?= $_GET['search']; ?>&action=<?= $_GET['action']; ?>" method="post" enctype="multipart/form-data">
					<table style="margin:10px auto;">
					<tr style="font-size:1.6em;"><td><label for="media">URL del contenuto multimediale</label></td><td><input type="text" id="media" name="media" /></td></tr>
					<tr><td></td><td>
						<input type="radio" name="howtoembed" id="copy" value="copy" checked="checked" /> <label for="copy">Crea una copia nel tuo sito</label><br />
						<input type="radio" name="howtoembed" id="hotlink" value="hotlink" /> <label for="hotlink">Crea un hotlink</label>
						<br />
						<div id="manualresize">
						<table>
						<tr><td><label for="mediatitle">Titolo</label></td><td><input type="text" name="mediatitle" id="mediatitle" value="" style="width:300px;" /></td></tr>
						<tr><td><label for="mediawidth">Larghezza</label></td><td><input type="text" name="mediawidth" id="mediawidth" value="" style="width:50px;" /> px</td></tr>
						<tr><td><label for="mediaheight">Altezza</label></td><td><input type="text" name="mediaheight" id="mediaheight" value="" style="width:50px;" /> px</td></tr>
						<tr><td><label for="mediaduration">Durata</label></td><td><input type="text" name="mediaduration" id="mediaduration" value="" style="width:50px;" /> secondi</td></tr>
						</table>
						</div><br />
						<div id="manualthumb"><label for="thumbnail">Scegli una miniatura</label> <input type="file" id="thumbnail" name="thumbnail" /></div><br />
						<br />
						</td></tr>
					<tr><td><label for="alt">Didascalia</label></td><td><textarea name="alt" id="alt" style="width:100%;height:50px;"></textarea></td></tr>
					</table><br />
					<div class="submit"><input type="submit" name="save" value="Carica contenuto multimediale" class="button" /></div>
					</form>
				<?php  }
			}

		/* CODICE DA INCORPORARE */
		if($_GET['action']=="embed") {
			if(isset($_POST['save'])) {
				$log="";
				$idmedia=$kaMedia->embed($_GET['mediatable'],$_GET['mediaid'],$_POST['htmlcode'],$_POST['mediatitle'],$_POST['mediaduration'],$_POST['alt']);
				if($idmedia==false) $log="Problemi durante il salvataggio";
				elseif(isset($_FILES['thumbnail']['name'])&&$_FILES['thumbnail']['name']!="") $kaMedia->setThumb($idmedia,$_FILES['thumbnail']['tmp_name'],$_FILES['thumbnail']['name'],false);
		
				if($log=="") {
					?>
					<div class="success">Contenuto Multimediale caricato con successo.</div><br />
					<?php  if($_GET['forcerefresh']==true) { ?>
						<script type="text/javascript">
							window.parent.location.reload();
							</script>
						<?php  }
					else { ?>
						<script type="text/javascript">
							window.parent.b3_openMessage('Contenuto Multimediale caricato con successo.',true);
							window.parent.k_closeIframeWindow();
							</script>
						<?php  } ?>
					<?php  }
				else echo '<div class="alert">'.$log.'</div><br />';
				}
			else { ?>
				<form action="?mode=<?= $_GET['mode']; ?>&forcerefresh=<?= $_GET['forcerefresh']; ?>&refid=<?= $_GET['refid']; ?>&mediatable=<?= $_GET['mediatable']; ?>&mediaid=<?= $_GET['mediaid']; ?>&search=<?= $_GET['search']; ?>&action=<?= $_GET['action']; ?>" method="post" enctype="multipart/form-data">
					<table style="margin:10px auto;">
					<tr><td><label for="mediatitle">Titolo</label> <input type="text" name="mediatitle" id="mediatitle" value="" style="width:300px;" /></td>
						<td><label for="mediaduration">Durata</label> <input type="text" name="mediaduration" id="mediaduration" value="" style="width:50px;" /> sec.</td></tr>
					<tr><td colspan="2"><label for="alt">Didascalia</label> <textarea name="alt" id="alt" style="width:100%;height:50px;"></textarea></td></tr>
					<tr style="font-size:1.6em;"><td colspan="2"><br /><label for="htmlcode">Codice da incorporare</label></td></tr>
					<tr><td colspan="2"><textarea name="htmlcode" id="htmlcode" style="width:500px;height:100px;"></textarea></td></tr>
					<tr><td colspan="2"><label for="thumbnail">Scegli una miniatura</label> <input type="file" id="thumbnail" name="thumbnail" /></td></tr>
					</table><br />
					<div class="submit"><input type="submit" name="save" value="Carica contenuto multimediale" class="button" /></div>
					</form>
				<?php  }
			}
		}


else {	
	function printInsertButtons($idmedia) {
		global $kaMedia;
		$media=$kaMedia->getMedia($idmedia);
		?>
		<div style="text-align:center;"><img src="<?= BASEDIR.$media['thumb']['url']; ?>" height="100" alt="" /></div>
		<br />
		<div class="submit" style="padding:15px;">
			Aspetta...
			</div>
		<script type="text/javascript">
			window.parent.txts.getArea('<?= $_GET['refid']; ?>').insertMedia('<?= $media['idmedia']; ?>','','<?= BASEDIR.$media['thumb']['url']; ?>','<?= $media['width']; ?>','<?= $media['height']; ?>');
			window.parent.k_closeIframeWindow();
			</script>
		<?php  }

	/* UPLOAD SINGOLA CONTENUTO MULMEDIALE */
	?>
	<div id="mediaheader">
		<h1>Inserisci Contenuto Multimediale</h1>
		<a href="javascript:window.parent.k_closeIframeWindow();" class="closeWindow"><img src="<?= ADMINRELDIR; ?>img/closeWindow.gif" alt="Close" width="9" height="9" /></a>
		<div class="smenu sel">
			<ul>
			<?php 
			$menu=array();
			$menu['upload']="Scegli dal computer";
			$menu['repository']="Scegli dall'archivio";
			$menu['internet']="Scegli da internet";
			$menu['embed']="Incorpora codice";
			if(!isset($_GET['action'])) $_GET['action']='upload';
			foreach($menu as $ka=>$m) {
				echo '<li><a href="?idmedia='.$_GET['idmedia'].'&forcerefresh='.$_GET['forcerefresh'].'&refid='.$_GET['refid'].'&mediatable='.$_GET['mediatable'].'&mediaid='.$_GET['mediaid'].'&search='.$_GET['search'].'&action='.$ka.'&mode='.$_GET['mode'].'" class="'.($_GET['action']==$ka?'sel':'').'">'.$m.'</a></li>';
				}
			?>
			</ul>
			</div>
		</div>
.
	<div id="mediacontents">
		<?php 
		/* UPLOAD SINGOLA CONTENUTO MULMEDIALE */
		if($_GET['action']=="upload") {
			if(isset($_POST['save'])) {
				$log="";
				if(!isset($_POST['resize'])) $_POST['resize']=false;
				if(!isset($_POST['mediawidth'])) $_POST['mediawidth']=0;
				if(!isset($_POST['mediaheight'])) $_POST['mediaheight']=0;
				if(!isset($_POST['mediaduration'])) $_POST['mediaduration']=0;
				$idmedia=$kaMedia->upload($_FILES['media']['tmp_name'],$_FILES['media']['name'],$_GET['mediatable'],$_GET['mediaid'],$_POST['mediatitle'],$_POST['alt'],$_POST['resize'],$_POST['mediawidth'],$_POST['mediaheight'],$_POST['mediaduration']);
				if($idmedia==false) $log.="Errore durante il caricamento del file ".$_FILES['media']['name'].".<br />";
				else {
					$kaMedia->setThumb($idmedia,$_FILES['thumbnail']['tmp_name'],$_FILES['thumbnail']['name'],false);
					}

				if($log=="") {
					?>
					<div class="success">Contenuto Multimediale caricato con successo.</div><br />
					<?php  printInsertButtons($idmedia); ?>
					<?php  }
				else echo '<div class="alert">'.$log.'</div><br />';
				}
			else { ?>
				<form action="?refid=<?= $_GET['refid']; ?>&mediatable=<?= $_GET['mediatable']; ?>&mediaid=<?= $_GET['mediaid']; ?>&search=<?= $_GET['search']; ?>&action=<?= $_GET['action']; ?>" method="post" enctype="multipart/form-data">
					<table style="margin:10px auto;">
					<tr style="font-size:1.6em;"><td><label for="media">Scegli un contenuto multimediale</label></td><td><input type="file" id="media" name="media" /></td></tr>
					<tr><td></td><td>
						<div id="manualresize">
						<table>
						<tr><td><label for="mediatitle">Titolo</label></td><td><input type="text" name="mediatitle" id="mediatitle" value="" style="width:300px;" /></td></tr>
						<tr><td><label for="mediawidth">Larghezza</label></td><td><input type="text" name="mediawidth" id="mediawidth" value="" style="width:50px;" /> px</td></tr>
						<tr><td><label for="mediaheight">Altezza</label></td><td><input type="text" name="mediaheight" id="mediaheight" value="" style="width:50px;" /> px</td></tr>
						<tr><td><label for="mediaduration">Durata</label></td><td><input type="text" name="mediaduration" id="mediaduration" value="" style="width:50px;" /> secondi</td></tr>
						</table>
						</div><br />
						<div id="manualthumb"><label for="thumbnail">Scegli una miniatura</label> <input type="file" id="thumbnail" name="thumbnail" /></div><br />
						<br />
						</td></tr>
					<tr><td style="text-align:right;"><label for="alt">Didascalia</label></td><td><textarea name="alt" id="alt" style="width:100%;height:50px;"></textarea></td></tr>
					</table><br />
					<div class="submit"><input type="submit" name="save" value="Carica contenuto multimediale" class="button" /></div>
					</form>
				<?php  }
			}

		/* SELEZIONA DAL REPOSITORY */
		if($_GET['action']=="repository") {
			if(isset($_GET['insert'])&&intval($_GET['insert'])>0) {
				printInsertButtons($_GET['insert']);
				}
			else {
				$conditions="";
				if(!isset($_GET['search'])) $_GET['search']="";
				else $conditions="filename LIKE '%".$_GET['search']."%' OR alt LIKE '%".$_GET['search']."%'";
				$medias=$kaMedia->getList("","","ordine",$conditions);
				if(!is_array($medias)||count($medias)==0) $medias=array();
				$n=0;
				?>
				<div class="box">
					<form method="get" action="">
					<input type="hidden" name="action" value="<?= $_GET['action']; ?>" />
					<input type="hidden" name="refid" value="<?= $_GET['refid']; ?>" />
					<input type="hidden" name="mode" value="<?= $_GET['mode']; ?>" />
					<input type="hidden" name="forcerefresh" value="<?= $_GET['forcerefresh']; ?>" />
					<input type="hidden" name="mediatable" value="<?= $_GET['mediatable']; ?>" />
					<input type="hidden" name="mediaid" value="<?= $_GET['mediaid']; ?>" />
					Cerca: <input type="text" name="search" style="width:300px;" value="<?= $_GET['search']; ?>" />
					<input type="submit" class="smallbutton" value="vai" />
					</form>
					</div>
				<div class="imagePreview"><ul><?php 				foreach($medias as $media) {
					$thumbname=BASERELDIR.DIR_MEDIA.$media['idmedia'].'/'.$media['thumb']['filename'];
					?><li>
					<div class="preview">
						<a href="?refid=<?= $_GET['refid']; ?>&mediatable=<?= $_GET['mediatable']; ?>&mediaid=<?= $_GET['mediaid']; ?>&search=<?= $_GET['search']; ?>&action=<?= $_GET['action']; ?>&insert=<?= $media['idmedia']; ?>">
						<?php 						if(file_exists($thumbname)) {
							$size=getimagesize($thumbname);
							$size[1]=150/$size[0]*$size[1];
							$size[0]=150;
							if($size[1]>100) {
								$size[0]=100/$size[1]*$size[0];
								$size[1]=100;
								}
							?><img src="<?= $thumbname.'?'.rand(0,666); ?>" width="<?= $size[0]; ?>" height="<?= $size[1]; ?>" style="padding:<?= (100-$size[1])/2; ?>px 0;" alt="<?= str_replace('"','&quot;',trim(strip_tags($media['alt']))); ?>" /><?php 
							}
						else { ?>
							<div style="background-color:#eee;width:100px;height:100px;display:block;"></div>
							<?php  } ?>
						</a>
						</div>
					<div class="options">
						<?= $media['title']!=""?$media['title']:$media['filename']; ?><br />
						<?= $media['width'].'x'.$media['height'].' px - '.$media['duration'].' sec.'; ?><br />
						<a href="?refid=<?= $_GET['refid']; ?>&mediatable=<?= $_GET['mediatable']; ?>&mediaid=<?= $_GET['mediaid']; ?>&search=<?= $_GET['search']; ?>&action=<?= $_GET['action']; ?>&insert=<?= $media['idmedia']; ?>" class="smallbutton">Inserisci</a>
						</div>
					</li><?php 					$n++;
					}
				?></ul></div>
				<?php 				if($n==0) echo '<div class="empty">Nessuna contenuto multimediale caricata</div>';
			}
		}


		/* SELEZIONA DA INTERNET */
		if($_GET['action']=="internet") {
			if(isset($_POST['save'])) {
				$log="";
				//controllo l'esistenza/raggiungibilità del contenuto multimediale
				$file_headers=@get_headers($_POST['media']);
				if(!$file_headers) $log="URL non valido"; 
				else if($file_headers[0]=='HTTP/1.1 404 Not Found') $log="Contenuto Multimediale non trovato";
				else {
					isset($_POST['autoresize'])?$_POST['autoresize']=true:$_POST['autoresize']=false;
					if($_POST['howtoembed']=='hotlink') {
						$idmedia=$kaMedia->upload("","",$_GET['mediatable'],$_GET['mediaid'],$_POST['mediatitle'],$_POST['alt'],$_POST['autoresize'],$_POST['mediawidth'],$_POST['mediaheight'],$_POST['mediaduration']);
						$kaMedia->setHotlink($idmedia,$_POST['media']);
						}
					else {
						$idmedia=$kaMedia->upload($_POST['media'],basename($_POST['media']),$_GET['mediatable'],$_GET['mediaid'],$_POST['mediatitle'],$_POST['alt'],$_POST['autoresize'],$_POST['mediawidth'],$_POST['mediaheight'],$_POST['mediaduration']);
						if($idmedia==false) $log.="Errore durante la copia del file ".$_POST['media'].".<br />";
						}
					if(isset($_FILES['thumbnail']['name'])&&$_FILES['thumbnail']['name']!="") $kaMedia->setThumb($idmedia,$_FILES['thumbnail']['tmp_name'],$_FILES['thumbnail']['name'],false);
					}

				if($log=="") {
					?>
					<div class="success">Contenuto Multimediale caricato con successo.</div><br />
					<?php  printInsertButtons($idmedia); ?>
					<?php  }
				else echo '<div class="alert">'.$log.'</div><br />';
				}
			else { ?>
				<form action="?refid=<?= $_GET['refid']; ?>&mediatable=<?= $_GET['mediatable']; ?>&mediaid=<?= $_GET['mediaid']; ?>&search=<?= $_GET['search']; ?>&action=<?= $_GET['action']; ?>" method="post" enctype="multipart/form-data">
					<table style="margin:10px auto;">
					<tr style="font-size:1.6em;"><td><label for="media">URL del contenuto multimediale</label></td><td><input type="text" id="media" name="media" /></td></tr>
					<tr><td></td><td>
						<input type="radio" name="howtoembed" id="copy" value="copy" checked="checked" /> <label for="copy">Crea una copia nel tuo sito</label><br />
						<input type="radio" name="howtoembed" id="hotlink" value="hotlink" /> <label for="hotlink">Crea un hotlink</label>
						<br />
						<div id="manualresize">
						<table>
						<tr><td><label for="mediatitle">Titolo</label></td><td><input type="text" name="mediatitle" id="mediatitle" value="" style="width:300px;" /></td></tr>
						<tr><td><label for="mediawidth">Larghezza</label></td><td><input type="text" name="mediawidth" id="mediawidth" value="" style="width:50px;" /> px</td></tr>
						<tr><td><label for="mediaheight">Altezza</label></td><td><input type="text" name="mediaheight" id="mediaheight" value="" style="width:50px;" /> px</td></tr>
						<tr><td><label for="mediaduration">Durata</label></td><td><input type="text" name="mediaduration" id="mediaduration" value="" style="width:50px;" /> secondi</td></tr>
						</table>
						</div><br />
						<div id="manualthumb"><label for="thumbnail">Scegli una miniatura</label> <input type="file" id="thumbnail" name="thumbnail" /></div><br />
						<br />
						</td></tr>
					<tr><td><label for="alt">Didascalia</label></td><td><textarea name="alt" id="alt" style="width:100%;height:50px;"></textarea></td></tr>
					</table><br />
					<div class="submit"><input type="submit" name="save" value="Carica contenuto multimediale" class="button" /></div>
					</form>
				<?php  }
			}

		/* CODICE DA INCORPORARE */
		if($_GET['action']=="embed") {
			if(isset($_POST['save'])) {
				$log="";
				$idmedia=$kaMedia->embed($_GET['mediatable'],$_GET['mediaid'],$_POST['htmlcode'],$_POST['mediatitle'],$_POST['mediaduration'],$_POST['alt']);
				if($idmedia==false) $log="Problemi durante il salvataggio";
				elseif(isset($_FILES['thumbnail']['name'])&&$_FILES['thumbnail']['name']!="") $kaMedia->setThumb($idmedia,$_FILES['thumbnail']['tmp_name'],$_FILES['thumbnail']['name'],false);
		
				if($log=="") {
					?>
					<div class="success">Contenuto Multimediale caricato con successo.</div><br />
					<?php  printInsertButtons($idmedia); ?>
					<?php  }
				else echo '<div class="alert">'.$log.'</div><br />';
				}
			else { ?>
				<form action="?mode=<?= $_GET['mode']; ?>&forcerefresh=<?= $_GET['forcerefresh']; ?>&refid=<?= $_GET['refid']; ?>&mediatable=<?= $_GET['mediatable']; ?>&mediaid=<?= $_GET['mediaid']; ?>&search=<?= $_GET['search']; ?>&action=<?= $_GET['action']; ?>" method="post" enctype="multipart/form-data">
					<table style="margin:10px auto;">
					<tr><td><label for="mediatitle">Titolo</label> <input type="text" name="mediatitle" id="mediatitle" value="" style="width:300px;" /></td>
						<td><label for="mediaduration">Durata</label> <input type="text" name="mediaduration" id="mediaduration" value="" style="width:50px;" /> sec.</td></tr>
					<tr><td colspan="2"><label for="alt">Didascalia</label> <textarea name="alt" id="alt" style="width:100%;height:50px;"></textarea></td></tr>
					<tr style="font-size:1.6em;"><td colspan="2"><br /><label for="htmlcode">Codice da incorporare</label></td></tr>
					<tr><td colspan="2"><textarea name="htmlcode" id="htmlcode" style="width:500px;height:100px;"></textarea></td></tr>
					<tr><td colspan="2"><label for="thumbnail">Scegli una miniatura</label> <input type="file" id="thumbnail" name="thumbnail" /></td></tr>
					</table><br />
					<div class="submit"><input type="submit" name="save" value="Carica contenuto multimediale" class="button" /></div>
					</form>
				<?php  }
			}

		?>
		</div>
	<?php  } ?>

	</div>
</body>
</html>
