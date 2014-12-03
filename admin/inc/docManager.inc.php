<?php /* (c) Kalamun.org - GNU/GPL 3 */

error_reporting(0);
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

define("PAGE_NAME","Document Manager");
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
	@import "<?php echo ADMINDIR; ?>css/docmanager.css";
	</style>

<script type="text/javascript">var ADMINDIR='<?php echo str_replace("'","\'",ADMINDIR); ?>';</script>
<script type="text/javascript" src="<?php echo ADMINDIR; ?>js/kalamun.js"></script>
</head>

<body>

<?php 
if(!isset($_GET['mode'])) $_GET['mode']="";
if(!isset($_GET['forcerefresh'])) $_GET['forcerefresh']=false;
if(!isset($_GET['mediatable'])) $_GET['mediatable']="";
if(!isset($_GET['mediaid'])) $_GET['mediaid']="";
if(!isset($_GET['search'])) $_GET['search']="";
if(!isset($_GET['iddoc'])) $_GET['iddoc']=0;
if(!isset($_GET['refid'])) $_GET['refid']=0;
include('./documents.lib.php');
$kaDocuments=new kaDocuments();

if(intval($_GET['iddoc'])>0) {
	/* MODIFICA SINGOLO DOCUMENTO */
	 ?>
	<div id="docheader">
		<h1>Modifica Documento</h1>
		<a href="javascript:window.parent.k_closeIframeWindow();" class="closeWindow"><img src="<?= ADMINRELDIR; ?>img/closeWindow.gif" alt="Close" width="9" height="9" /></a>
		<div class="smenu sel">
			<ul>
			<?php 
			$menu=array();
			$menu['properties']="Propriet&agrave; del documento";
			$menu['files']="Files";
			$menu['istances']="Utilizzo";
			$menu['fullsize']="Guarda";
			$menu['delete']="Elimina";
			if(!isset($_GET['action'])) $_GET['action']='properties';
			foreach($menu as $ka=>$m) {
				echo '<li><a href="?iddoc='.$_GET['iddoc'].'&forcerefresh='.$_GET['forcerefresh'].'&refid='.$_GET['refid'].'&mediatable='.$_GET['mediatable'].'&mediaid='.$_GET['mediaid'].'&search='.$_GET['search'].'&action='.$ka.'&mode='.$_GET['mode'].'" class="'.($_GET['action']==$ka?'sel':'').'">'.$m.'</a></li>';
				}
			?>
			</ul>
			</div>
		</div>
.
	<div id="doccontents">
		<?php 
		if($_GET['action']=="properties") {
			if(isset($_POST['save'])) {
				$log="";
				$doc=$kaDocuments->getDocument($_GET['iddoc']);
				if(!isset($doc['iddoc'])) $log.="Errore: sembra che il documento non esista...";
				elseif($kaDocuments->updateAlt($_GET['iddoc'],$_POST['alt'])==false) $log.="Errore durante il salvataggio delle modifiche.<br />";
			
				if($log=="") {
					$kaLog->add("UPD","Modificate le propriet&agrave; del documento ".$doc['filename']." (<em>ID: ".$doc['iddoc']."</em>)");
					?>
					<div class="success">Documento modificata con successo.</div>
					<?php  }
				else {
					$kaLog->add("ERR","Errore durante la modifica delle propriet&agrave; del documento ".$doc['filename']." (<em>ID: ".$doc['iddoc']."</em>)");
					echo '<div class="alert">'.$log.'</div>';
					}
				}
			?>
			<form action="?iddoc=<?= $_GET['iddoc']; ?>&forcerefresh=<?= $_GET['forcerefresh']; ?>&refid=<?php echo $_GET['refid']; ?>&mediatable=<?php echo $_GET['mediatable']; ?>&mediaid=<?php echo $_GET['mediaid']; ?>&search=<?php echo $_GET['search']; ?>&action=<?= $_GET['action']; ?>" method="post" enctype="multipart/form-data">
				<?php  $doc=$kaDocuments->getDocument($_GET['iddoc']);
				if(!isset($doc['filename'])) $doc['filename']='Nessun documento... forse il caricamento non era andato a buon fine?';
				?>
				<table style="margin:10px auto;">
				<tr><td colspan="2" align="center"><h2><?= $doc['filename']; ?></h2></td></tr>
				<tr><td align="right"><label for="alt">Didascalia</label></td><td><textarea name="alt" id="alt" style="width:300px;height:50px;"><?= b3_lmthize($doc['alt'],"textarea"); ?></textarea></td></tr>
				</table><br />
				<div class="note">P.s: Attento! Modificando questo documento, esso verrà cambiato in tutti i posti in cui è stato utilizzato!</div>
				<div class="submit"><input type="submit" name="save" value="Salva le modifiche" class="button" /></div>
				</form>
			<?php  }

		elseif($_GET['action']=="files") {
			if(isset($_POST['savedoc'])) {
				/* SALVA MODIFICHE DOCUMENTO */
				$log="";
				$doc=$kaDocuments->getDocument($_GET['iddoc']);
				$iddoc=$kaDocuments->update($doc['iddoc'],$_FILES['doc']['tmp_name'],$_FILES['doc']['name']);
			
				if($log=="") {
					$kaLog->add("UPD","Sostituito il documento ".$doc['filename']." (<em>ID: ".$doc['iddoc']."</em>)");
					?>
					<div class="success">Documento modificato con successo.</div>
					<?php  }
				else {
					$kaLog->add("ERR","Errore durante la sostituzione del documento ".$doc['filename']." (<em>ID: ".$doc['iddoc']."</em>)");
					echo '<div class="alert">'.$log.'</div>';
					}
				}
			elseif(isset($_POST['save'])) {
				/* SALVA MODIFICHE HOTLINK */
				$log="";
				$doc=$kaDocuments->getDocument($_GET['iddoc']);
				$iddoc=$kaDocuments->updateHotlink($doc['iddoc'],$_GET['doc']);
			
				if($log=="") {
					$kaLog->add("UPD","Modificato l'hotlink ".$doc['filename']." (<em>ID: ".$doc['iddoc']."</em>)");
					?>
					<div class="success">Documento modificato con successo.</div>
					<?php  }
				else {
					$kaLog->add("ERR","Errore durante la modifica dell'hotlink del documento ".$doc['filename']." (<em>ID: ".$doc['iddoc']."</em>)");
					echo '<div class="alert">'.$log.'</div>';
					}
				}
			elseif(isset($_POST['hotlinktodoc'])) {
				/* IMPORTA HOTLINK */
				$log="";
				//controllo l'esistenza/raggiungibilità del doc
				$file_headers=@get_headers($_POST['image']);
				if(!$file_headers) $log="URL non valido"; 
				else if($file_headers[0]=='HTTP/1.1 404 Not Found') $log="Documento non trovato";
				else {
					$doc=$kaDocuments->getDocument($_GET['iddoc']);
					$iddoc=$kaDocuments->update($doc['iddoc'],$_POST['doc'],basename($_POST['doc']));
					if($iddoc==false) $log.="Errore durante l'importazione del file ".$doc['url'].".<br />";
					}

				if($log=="") {
					$kaLog->add("UPD","Importato il documento hotlink ".$_POST['img']." in locale (<em>ID: ".$doc['iddoc']."</em>)");
					?>
					<div class="success">Documento caricato con successo.</div><br />
					<?php  printInsertButtons($iddoc); ?>
					<?php  }
				else {
					$kaLog->add("ERR","Errore di importazione del documento hotlink ".$_POST['img']." in locale (<em>ID: ".$doc['iddoc']."</em>)");
					echo '<div class="alert">'.$log.'</div><br />';
					}
				}
			?>
			<form action="?iddoc=<?= $_GET['iddoc']; ?>&forcerefresh=<?= $_GET['forcerefresh']; ?>&refid=<?php echo $_GET['refid']; ?>&mediatable=<?php echo $_GET['mediatable']; ?>&mediaid=<?php echo $_GET['mediaid']; ?>&search=<?php echo $_GET['search']; ?>&action=<?= $_GET['action']; ?>" method="post" enctype="multipart/form-data">
				<?php  $doc=$kaDocuments->getDocument($_GET['iddoc']); ?>
				<table style="margin:10px auto;">
				<tr><td align="center"><h2>Documento</h2><a href="<?= ($doc['hotlink']==false?BASEDIR:'').$doc['url']; ?>"><?= $doc['filename']; ?></a></td>
					<td style="vertical-align:middle;"><label ref="doc">Cambia</label><br /><?php 
						if($doc['hotlink']==false) { ?><input name="doc" type="file" id="doc" /> <input name="savedoc" type="submit" value="Salva modifiche" class="smallbutton" /><?php  }
						else { ?><input type="text" name="doc" value="<?= str_replace('"','&quot;',$doc['url']); ?>" style="width:300px;"> <input name="save" type="submit" value="Salva modifiche" class="smallbutton" /> <input name="hotlinktodoc" type="submit" value="Importa nel sito" class="smallbutton" /><?php  } ?>
						</td></tr>
				</table><br />
				<div class="note">P.s: Attento! Modificando questo documento, esso verrà cambiato in tutti i posti in cui è stato utilizzato!</div>
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
						$q.=" `".$row['Field']."` LIKE '%id=\"doc".$_GET['iddoc']."\"%' ";
						$q.=" OR ";
						}
					}
				if($primary!="") {
					$q.=$primary.'=0 ';
					if($primary!="") {
						$rs=ksql_query($q);
						while($r=ksql_fetch_array($rs)) { ?>
							<tr><td class="small"><?= substr($ka,6); ?></td><td class="small"><?= $r[$primary]; ?></td><td><?= isset($r['titolo'])?$r['titolo']:'<em>Non disponibile</em>'; ?></td>
							<?php  }
						}
					}
				}
			?></tbody></table><?php 
			
			?>
			<?php  }

		elseif($_GET['action']=="fullsize") {
			$doc=$kaDocuments->getDocument($_GET['iddoc']);
			?>
			<h2><a href="<?= ($doc['hotlink']==false?BASEDIR:'').$doc['url']; ?>" target="_blank"><?= $doc['filename']; ?></a></h2>
			<div class="note">Clicca per aprire...</div>
			<?php  }

		elseif($_GET['action']=="delete") {
			if(isset($_POST['delete'])) {
				$log="";
				$doc=$kaDocuments->getDocument($_GET['iddoc']);
				if(!isset($doc['iddoc'])) $log='Errore: sembra che il documento non esista...';
				elseif(!$kaDocuments->delete($_GET['iddoc'])) $log.="Errore durante la rimozione del documento.<br />";
			
				if($log=="") {
					$kaLog->add("DEL","Eliminato definitivamente il documento ".$doc['filename']." (<em>ID: ".$doc['iddoc']."</em>)");
					?>
					<div class="success">Documento eliminato con successo.</div>
					<?php  if($_GET['forcerefresh']==true) { ?>
						<script type="text/javascript">
							window.parent.location.reload();
							</script>
						<?php  }
					else { ?>
						<script type="text/javascript">
							window.parent.b3_openMessage('Documento eliminato con successo.',true);
							window.parent.k_closeIframeWindow();
							</script>
						<?php  } ?>
					<?php  }
				else {
					$kaLog->add("ERR","Errore durante l'eliminazione del documento ".$doc['filename']." (<em>ID: ".$doc['iddoc']."</em>)");
					echo '<div class="alert">'.$log.'</div>';
					}
				}
			else {
				?>
				<form action="?iddoc=<?= $_GET['iddoc']; ?>&forcerefresh=<?= $_GET['forcerefresh']; ?>&refid=<?php echo $_GET['refid']; ?>&mediatable=<?php echo $_GET['mediatable']; ?>&mediaid=<?php echo $_GET['mediaid']; ?>&search=<?php echo $_GET['search']; ?>&action=<?= $_GET['action']; ?>" method="post" enctype="multipart/form-data">
					<?php  $doc=$kaDocuments->getDocument($_GET['iddoc']);
					if(!isset($doc['filename'])) $doc['filename']='Nessun documento... forse il caricamento non era andato a buon fine?';
					?>
					<table style="margin:10px auto;">
					<tr><td colspan="2" align="center"><?= $doc['filename']; ?><br /><br />
					Stai per eliminare <strong>definitivamente</strong> dal sito questo documento: sei sicuro di volerlo fare?</td></tr>
					</table><br />
					
					<div class="note">P.s: Attento! Perderai il documento da tutte le pagine in cui è utilizzato!</div>
					<div class="submit"><input type="submit" name="delete" value="ELIMINA il documento" class="button" /></div>
					</form>
				<?php  }
			}
		?>
		</div>
	<?php  }

elseif($_GET['mode']=='justupload') {
	/* UPLOAD SINGOLO DOCUMENTO */
	?>
	<div id="imgheader">
		<h1>Inserisci Documento</h1>
		<a href="javascript:window.parent.k_closeIframeWindow();" class="closeWindow"><img src="<?= ADMINRELDIR; ?>img/closeWindow.gif" alt="Close" width="9" height="9" /></a>
		<div class="smenu sel">
			<ul>
			<?php 
			$menu=array();
			$menu['upload']="Scegli dal computer";
			$menu['internet']="Scegli da internet";
			if(!isset($_GET['action'])) $_GET['action']='upload';
			foreach($menu as $ka=>$m) {
				echo '<li><a href="?iddoc='.$_GET['iddoc'].'&forcerefresh='.$_GET['forcerefresh'].'&refid='.$_GET['refid'].'&mediatable='.$_GET['mediatable'].'&mediaid='.$_GET['mediaid'].'&search='.$_GET['search'].'&action='.$ka.'&mode='.$_GET['mode'].'" class="'.($_GET['action']==$ka?'sel':'').'">'.$m.'</a></li>';
				}
			?>
			</ul>
			</div>
		</div>
.
	<div id="imgcontents">
		<?php 
		/* UPLOAD SINGOLO DOCUMENTO */
		if($_GET['action']=="upload") {
			if(isset($_POST['save'])) {
				$log="";
				$iddoc=$kaDocuments->upload($_FILES['document']['tmp_name'],$_FILES['document']['name'],$_GET['mediatable'],$_GET['mediaid'],$_POST['alt']);
				if($iddoc==false) $log.="Errore durante il caricamento del file ".$_FILES['document']['name'].".<br />";

				if($log=="") {
					?>
					<div class="success">Documento caricato con successo.</div><br />
					<?php  if($_GET['forcerefresh']==true) { ?>
						<script type="text/javascript">
							window.parent.location.reload();
							</script>
						<?php  }
					else { ?>
						<script type="text/javascript">
							window.parent.b3_openMessage('Documento caricato con successo.',true);
							window.parent.k_closeIframeWindow();
							</script>
						<?php  } ?>
					<?php  }
				else echo '<div class="alert">'.$log.'</div><br />';
				}
			else { ?>
				<form action="?forcerefresh=<?= $_GET['forcerefresh']; ?>&refid=<?= $_GET['refid']; ?>&mediatable=<?= $_GET['mediatable']; ?>&mediaid=<?= $_GET['mediaid']; ?>&search=<?= $_GET['search']; ?>&action=<?= $_GET['action']; ?>&mode=<?= $_GET['mode']; ?>" method="post" enctype="multipart/form-data">
					<table style="margin:10px auto;">
					<tr style="font-size:1.6em;"><td><label for="document">Scegli un documento</label></td><td><input type="file" id="document" name="document" /></td></tr>
					<tr><td><label for="alt">Didascalia</label></td><td><textarea name="alt" id="alt" style="width:100%;height:50px;"></textarea></td></tr>
					</table><br />
					<div class="submit"><input type="submit" name="save" value="Carica documento" class="button" /></div>
					</form>
				<?php  }
			}

		/* SELEZIONA DA INTERNET */
		if($_GET['action']=="internet") {
			if(isset($_POST['save'])) {
				$log="";
				//controllo l'esistenza/raggiungibilità dell'immagine
				$file_headers=@get_headers($_POST['document']);
				if(!$file_headers) $log="URL non valido"; 
				else if($file_headers[0]=='HTTP/1.1 404 Not Found') $log="Documento non trovato";
				else {
					if($_POST['howtoembed']=='hotlink') {
						$iddoc=$kaDocuments->setHotlink($_POST['document'],$_GET['mediatable'],$_GET['mediaid'],$_POST['alt']);
						if($iddoc==false) $log.="Errore durante la creazione dell'hotlink.<br />";
						}
					else {
						$iddoc=$kaDocuments->upload($_POST['document'],basename($_POST['document']),$_GET['mediatable'],$_GET['mediaid'],$_POST['alt']);
						if($iddoc==false) $log.="Errore durante la copia del file ".$_POST['document'].".<br />";
						}
					}

				if($log=="") {
					?>
					<div class="success">Documento caricato con successo.</div><br />
					<?php  if($_GET['forcerefresh']==true) { ?>
						<script type="text/javascript">
							window.parent.location.reload();
							</script>
						<?php  }
					else { ?>
						<script type="text/javascript">
							window.parent.b3_openMessage('Documento caricato con successo.',true);
							window.parent.k_closeIframeWindow();
							</script>
						<?php  } ?>
					<?php  }
				else echo '<div class="alert">'.$log.'</div><br />';
				}
			else { ?>
				<form action="?forcerefresh=<?= $_GET['forcerefresh']; ?>&refid=<?= $_GET['refid']; ?>&mediatable=<?= $_GET['mediatable']; ?>&mediaid=<?= $_GET['mediaid']; ?>&search=<?= $_GET['search']; ?>&action=<?= $_GET['action']; ?>&mode=<?= $_GET['mode']; ?>" method="post" enctype="multipart/form-data">
					<table style="margin:10px auto;">
					<tr style="font-size:1.6em;"><td><label for="document">URL del documento</label></td><td><input type="text" id="document" name="document" /></td></tr>
					<tr><td></td><td>
						<input type="radio" name="howtoembed" id="copy" value="copy" checked="checked" /> <label for="copy">Crea una copia nel tuo sito</label><br />
						<input type="radio" name="howtoembed" id="hotlink" value="hotlink" /> <label for="hotlink">Crea un hotlink</label><br />
						<br />
						</td></tr>
					<tr><td><label for="alt">Didascalia</label></td><td><textarea name="alt" id="alt" style="width:100%;height:50px;"></textarea></td></tr>
					</table><br />
					<div class="submit"><input type="submit" name="save" value="Carica documento" class="button" /></div>
					</form>
				<?php  }
		}
	}

else {	
	function printInsertButtons($iddoc) {
		global $kaDocuments;
		$doc=$kaDocuments->getDocument($iddoc);
		?>
		<script type="text/javascript">
			window.parent.txts.getArea('<?= $_GET['refid']; ?>').insertDoc('<?= $doc['iddoc']; ?>','<?= addslashes($doc['alt']); ?>','<?= ($doc['hotlink']==false?BASEDIR:'').$doc['url']; ?>');
			window.parent.k_closeIframeWindow();
			</script>
		<?php  }

	/* UPLOAD SINGOLO DOCUMENTO */
	?>
	<div id="imgheader">
		<h1>Inserisci Documento</h1>
		<a href="javascript:window.parent.k_closeIframeWindow();" class="closeWindow"><img src="<?= ADMINRELDIR; ?>img/closeWindow.gif" alt="Close" width="9" height="9" /></a>
		<div class="smenu sel">
			<ul>
			<?php 
			$menu=array();
			$menu['upload']="Scegli dal computer";
			$menu['repository']="Scegli dall'archivio";
			$menu['internet']="Scegli da internet";
			if(!isset($_GET['action'])) $_GET['action']='upload';
			foreach($menu as $ka=>$m) {
				echo '<li><a href="?iddoc='.$_GET['iddoc'].'&forcerefresh='.$_GET['forcerefresh'].'&refid='.$_GET['refid'].'&mediatable='.$_GET['mediatable'].'&mediaid='.$_GET['mediaid'].'&search='.$_GET['search'].'&action='.$ka.'&mode='.$_GET['mode'].'" class="'.($_GET['action']==$ka?'sel':'').'">'.$m.'</a></li>';
				}
			?>
			</ul>
			</div>
		</div>
.
	<div id="imgcontents">
		<?php 
		/* UPLOAD SINGOLO DOCUMENTO */
		if($_GET['action']=="upload") {
			if(isset($_POST['save'])) {
				$log="";
				$iddoc=$kaDocuments->upload($_FILES['document']['tmp_name'],$_FILES['document']['name'],$_GET['mediatable'],$_GET['mediaid'],$_POST['alt']);
				if($iddoc==false) $log.="Errore durante il caricamento del file ".$_FILES['image']['name'].".<br />";

				if($log=="") {
					?>
					<div class="success">Documento caricato con successo.</div><br />
					<?php  printInsertButtons($iddoc); ?>
					<?php  }
				else echo '<div class="alert">'.$log.'</div><br />';
				}
			else { ?>
				<form action="?refid=<?= $_GET['refid']; ?>&mediatable=<?= $_GET['mediatable']; ?>&mediaid=<?= $_GET['mediaid']; ?>&search=<?= $_GET['search']; ?>&action=<?= $_GET['action']; ?>" method="post" enctype="multipart/form-data">
					<table style="margin:10px auto;">
					<tr style="font-size:1.6em;"><td><label for="document">Scegli un documento</label></td><td><input type="file" id="document" name="document" /></td></tr>
					<tr><td><label for="alt">Didascalia</label></td><td><textarea name="alt" id="alt" style="width:100%;height:50px;"></textarea></td></tr>
					</table><br />
					<div class="submit"><input type="submit" name="save" value="Carica documento" class="button" /></div>
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
				$immagini=$kaDocuments->getList("","","ordine",$conditions);
				if(!is_array($immagini)||count($immagini)==0) $immagini=array();
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
				<div class="documentPreview"><ul><?php 				foreach($immagini as $doc) {
					$filename=BASERELDIR.DIR_DOCS.$doc['iddoc'].'/'.$doc['filename'];
					if(file_exists($filename)) {
						?><li>
						<div class="preview">
							<?= $doc['alt']; ?>
							<div class="small"><a href="<?= BASERELDIR.$doc['url']; ?>" target="_blank"><?= $doc['filename']; ?></a></div>
							<div class="options"><a href="?refid=<?= $_GET['refid']; ?>&mediatable=<?= $_GET['mediatable']; ?>&mediaid=<?= $_GET['mediaid']; ?>&search=<?= $_GET['search']; ?>&action=<?= $_GET['action']; ?>&insert=<?= $doc['iddoc']; ?>" class="smallbutton">Inserisci</a></div>
							</div>
						</li><?php 						$n++;
						}
					}
				?></ul></div>
				<?php 				if($n==0) echo '<div class="empty">Nessun documento caricato</div>';
			}
		}


		/* SELEZIONA DA INTERNET */
		if($_GET['action']=="internet") {
			if(isset($_POST['save'])) {
				$log="";
				//controllo l'esistenza/raggiungibilità dell'immagine
				$file_headers=@get_headers($_POST['document']);
				if(!$file_headers) $log="URL non valido"; 
				else if($file_headers[0]=='HTTP/1.1 404 Not Found') $log="Documento non trovata";
				else {
					if($_POST['howtoembed']=='hotlink') {
						$iddoc=$kaDocuments->setHotlink($_POST['document'],$_GET['mediatable'],$_GET['mediaid'],$_POST['alt']);
						if($iddoc==false) $log.="Errore durante la creazione dell'hotlink.<br />";
						}
					else {
						$iddoc=$kaDocuments->upload($_POST['document'],basename($_POST['document']),$_GET['mediatable'],$_GET['mediaid'],$_POST['alt']);
						if($iddoc==false) $log.="Errore durante la copia del file ".$_POST['document'].".<br />";
						}
					}

				if($log=="") {
					?>
					<div class="success">Documento caricato con successo.</div><br />
					<?php  printInsertButtons($iddoc); ?>
					<?php  }
				else echo '<div class="alert">'.$log.'</div><br />';
				}
			else { ?>
				<form action="?refid=<?= $_GET['refid']; ?>&mediatable=<?= $_GET['mediatable']; ?>&mediaid=<?= $_GET['mediaid']; ?>&search=<?= $_GET['search']; ?>&action=<?= $_GET['action']; ?>" method="post" enctype="multipart/form-data">
					<table style="margin:10px auto;">
					<tr style="font-size:1.6em;"><td><label for="document">URL del documento</label></td><td><input type="text" id="document" name="document" /></td></tr>
					<tr><td></td><td>
						<input type="radio" name="howtoembed" id="copy" value="copy" checked="checked" /> <label for="copy">Crea una copia nel tuo sito</label><br />
						<input type="radio" name="howtoembed" id="hotlink" value="hotlink" /> <label for="hotlink">Crea un hotlink</label><br />
						<br />
						</td></tr>
					<tr><td><label for="alt">Didascalia</label></td><td><textarea name="alt" id="alt" style="width:100%;height:50px;"></textarea></td></tr>
					</table><br />
					<div class="submit"><input type="submit" name="save" value="Carica documento" class="button" /></div>
					</form>
				<?php  }
		}

		?>
		</div>
	<?php  } ?>

	</div>
</body>
</html>
