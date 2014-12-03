<?php 
/* (c) Kalamun.org - GNU/GPL 3 */


/* todo */
/*
- check of unused metadata
- check of unused images
- check of unused documents
- check of unused videos
- check of contents from removed languages
*/


define("PAGE_NAME","Manutenzione");
include_once("../inc/head.inc.php");

/* AZIONI */
if(isset($_GET['fixpermalink'])) {
	$query="SELECT * FROM (SELECT dir,ll,count(*) AS tot FROM ".TABLE_PAGINE." GROUP BY `dir`,`ll`) AS subtable WHERE tot>1 ORDER BY tot DESC";
	$results=ksql_query($query);
	while($row=ksql_fetch_array($results)) {
		$q="SELECT idpag FROM ".TABLE_PAGINE." WHERE `dir`='".ksql_real_escape_string($row['dir'])."' AND `ll`='".ksql_real_escape_string($row['ll'])."'";
		$rs=ksql_query($q);
		while($r=ksql_fetch_array($rs)) {
			ksql_query("UPDATE ".TABLE_PAGINE." SET `dir`=CONCAT('".rand(1000,9999)."-',`dir`) WHERE `idpag`=".$r['idpag']);
			}
		}

	$query="SELECT * FROM (SELECT dir,ll,count(*) AS tot FROM ".TABLE_NEWS." GROUP BY `dir`,`ll`) AS subtable WHERE tot>1 ORDER BY tot DESC";
	$results=ksql_query($query);
	while($row=ksql_fetch_array($results)) {
		$q="SELECT idnews FROM ".TABLE_NEWS." WHERE `dir`='".ksql_real_escape_string($row['dir'])."' AND `ll`='".ksql_real_escape_string($row['ll'])."'";
		$rs=ksql_query($q);
		while($r=ksql_fetch_array($rs)) {
			ksql_query("UPDATE ".TABLE_NEWS." SET `dir`=CONCAT('".rand(1000,9999)."-',`dir`) WHERE `idnews`=".$r['idnews']);
			}
		}

	$query="SELECT * FROM (SELECT dir,ll,count(*) AS tot FROM ".TABLE_SHOP_ITEMS." GROUP BY `dir`,`ll`) AS subtable WHERE tot>1 ORDER BY tot DESC";
	$results=ksql_query($query);
	while($row=ksql_fetch_array($results)) {
		$q="SELECT idsitem FROM ".TABLE_SHOP_ITEMS." WHERE `dir`='".ksql_real_escape_string($row['dir'])."' AND `ll`='".ksql_real_escape_string($row['ll'])."'";
		$rs=ksql_query($q);
		while($r=ksql_fetch_array($rs)) {
			ksql_query("UPDATE ".TABLE_SHOP_ITEMS." SET `dir`=CONCAT('".rand(1000,9999)."-',`dir`) WHERE `idsitem`=".$r['idsitem']);
			}
		}

	}
if(isset($_GET['sendtestemail'])) {
	$subject='123 Test';
	$message="Bonjour!\nIt's just a test... your mailserver works!";
	$headers='From: '.ADMIN_MAIL;
	if(mail($_GET['sendtestemail'],$subject,$message,$headers)) $success="E-mail inviata";
	else $alert="Errore di invio dell'e-mail";
	}

if(isset($_GET['removeimages'])&&isset($_POST['idimg'])) {
	require_once(ADMINRELDIR."inc/images.lib.php");
	$kaImages=new kaImages();
	$log="";
	foreach($_POST['idimg'] as $idimg) {
		if(!$kaImages->delete($idimg)) $log.="Errore durante la rimozione dell'immagine con ID ".$idimg."<br />";
		}
	if($log!="") $alert=$log;
	else $success="Immagini eliminate con successo";
	}

if(isset($_GET['checkorphanimages'])) {
	$unused=array();
	
	$tables=array();
	foreach(get_defined_constants() as $ka=>$v) {
		if(substr($ka,0,6)=="TABLE_") $tables[$ka]=$v;
		}
	asort($tables);
	
	$query="SELECT * FROM ".TABLE_IMG;
	$results_imgs=ksql_query($query);
	while($img=ksql_fetch_array($results_imgs)) {
		$used=false;

		// controlla che non sia usata in qualche galleria
		// o in qualche menù
		$query="SELECT * FROM ".TABLE_IMGALLERY." WHERE `idimg`='".$img['idimg']."' LIMIT 1";
		$results=ksql_query($query);
		if(ksql_fetch_array($results)!=false) {
			$used=true;
			}
		
		// controlla che non sia stata inserita in qualche pagina
		if($used==false) {
			foreach($tables as $ka=>$t) {
				$q="SELECT * FROM `".$t."` WHERE ";
				$query="SHOW COLUMNS FROM `".$t."`";
				$results=ksql_query($query);
				if($results) {
					$primary="";
					while($row=ksql_fetch_array($results)) {
						if($row['Key']=='PRI') $primary=$row['Field'];
						if(substr($row['Type'],0,7)=='varchar'||substr($row['Type'],0,4)=='text') {
							$q.=" `".$row['Field']."` LIKE '%id=\"img".$img['idimg']."\"%' ";
							$q.=" OR `".$row['Field']."` LIKE '%id=\"thumb".$img['idimg']."\"%' ";
							$q.=" OR `".$row['Field']."` LIKE '%".$img['filename']."\"%' ";
							$q.=" OR ";
							}
						}
					$q=rtrim($q," OR");
					if($primary!="") {
						$rs=ksql_query($q);
						if($rs!=false) {
							while($r=ksql_fetch_array($rs)) {
								$used=true;
								}
							}
						}
					}
				}
			}
		
		// not used: insert into array of unused images
		if($used==false) {
			$unused[]=$img;
			}
		}
	
	if(count($unused)==0) {
		$success='Tutto a posto, nessuna immagine inutilizzata!';
		}
	else { ?>
		<h1><?php  echo PAGE_NAME; ?></h1>
		<br />
		<h2>Ci sono <?= count($unused); ?> immagini orfane</h2>
		<br />
		
		<script type="text/javascript">
			function kSelectAll() {
				var onoff=document.getElementById('checkall');
				var table=document.getElementById('filelist');
				var checks=table.getElementsByTagName('INPUT');
				for(var i=0;checks[i];i++) {
					checks[i].checked=onoff.checked;
					}
				}
			</script>

		<form action="?checkorphanimages&removeimages" method="POST" onsubmit="return confirm('Sei sicuro di voler cancellare completamente le immagini selezionate?');">
			<table class="tabella" id="filelist">
			<tr><th><input type="checkbox" id="checkall" onchange="kSelectAll()"></th>
				<th style="text-align:left;"><label for="checkall" style="color:#fff;">Seleziona/Deseleziona tutte</label></th>
				<th><input type="submit" value="Cancella selezionati" class="alertbutton" /></th></tr>
			<?php 
			$i=1;
			foreach($unused as $img) {
				$filename=ltrim(DIR_IMG,"./").$img['idimg'].'/'.$img['thumbnail'];
				$url=BASEDIR.$filename;
				?>
				<tr class="<?= $i%2==0?'even':'odd'; ?>"><td><input type="checkbox" name="idimg[]" value="<?= $img['idimg']; ?>" id="file<?= $img['idimg']; ?>" /></td>
					<td><label for="file<?= $img['idimg']; ?>"><img src="<?= $url; ?>" height="100"></label></td>
					<td><label for="file<?= $img['idimg']; ?>"><?= $img['filename']; ?><br />ID: <?= $img['idimg']; ?></label></td></tr>
					</tr>
				<?php 
				$i++;
				}
			?>
			<tr><th><input type="checkbox" id="checkall" onchange="kSelectAll()"></th>
				<th>Seleziona/Deseleziona tutte</th>
				<th><input type="submit" value="Cancella selezionati" class="alertbutton" /></th></tr>
			</table>
			</form>
		<?php 
		include_once("../inc/foot.inc.php");
		die();
		}
	}

elseif(isset($_GET['checkdburf8'])) {
	// check if all tables and columns of the db are UTF8 encoded
	$const=get_defined_constants(true);
	$count=array(0,0);
	foreach($const['user'] as $k=>$v) {
		if(substr($k,0,6)=="TABLE_") {
			$rs=ksql_query("SHOW TABLE STATUS LIKE '".constant($k)."'");
			$row=ksql_fetch_array($rs);
			if($row['Collation']!=""&&$row['Collation']!="utf8_general_ci") {
				ksql_query("ALTER TABLE `".constant($k)."` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci");
				$count[0]++;
				}
			
			$rs=ksql_query("SHOW FULL COLUMNS FROM ".constant($k));
			while($row=ksql_fetch_array($rs)) {
				if($row['Collation']!=""&&$row['Collation']!="utf8_general_ci") {
					ksql_query("ALTER TABLE `".constant($k)."` CHANGE `".$row['Field']."` `".$row['Field']."` ".$row['Type']." CHARACTER SET utf8 COLLATE utf8_general_ci ".($row['Null']!="NO"?"NOT":"")." NULL ".($row['Default']!=""?"DEFAULT '".$row['Default']."'":""));
					$count[1]++;
					}
				}
			}
		}
	$success="Verifica codifica database: ".$count[0]." tabelle aggiustate, ".$count[1]." colonne aggiustate";
	}

if(isset($success)) echo '<div id="MsgSuccess">'.$success.'</div>';
elseif(isset($alert)) echo '<div id="MsgAlert">'.$alert.'</div>';
/* FINE AZIONI */

?>
<h1><?php  echo PAGE_NAME; ?></h1>
<br />

<h2>Test funzionamento mailserver</h2>
<form method="get" action="">
	<label for="sendtestemail">Destinatario</label> <input type="text" value="<?= (isset($_GET['sendtestemail'])?$_GET['sendtestemail']:ADMIN_MAIL); ?>" name="sendtestemail" id="sendtestemail" />
	<input type="submit" value="Invia e-mail di test" class="smallbutton">
	</form>

<br />
	
<h2>Verifica dei permalink</h2>
<?php 
$output="";
$query="SELECT * FROM (SELECT dir,count(*) AS tot FROM ".TABLE_PAGINE." GROUP BY `dir`,`ll`) AS subtable WHERE tot>1 ORDER BY tot DESC";
$results=ksql_query($query);
while($row=ksql_fetch_array($results)) {
	$output.='<li>Pagine: <strong>'.$row['tot'].'</strong> '.$row['dir'].'</li>';
	}
$query="SELECT * FROM (SELECT dir,count(*) AS tot FROM ".TABLE_NEWS." GROUP BY `dir`,`ll`) AS subtable WHERE tot>1 ORDER BY tot DESC";
$results=ksql_query($query);
while($row=ksql_fetch_array($results)) {
	$output.='<li>News: <strong>'.$row['tot'].'</strong> '.$row['dir'].'</li>';
	}
$query="SELECT * FROM (SELECT dir,count(*) AS tot FROM ".TABLE_SHOP_ITEMS." GROUP BY `dir`,`ll`) AS subtable WHERE tot>1 ORDER BY tot DESC";
$results=ksql_query($query);
while($row=ksql_fetch_array($results)) {
	$output.='<li>Shop: <strong>'.$row['tot'].'</strong> '.$row['dir'].'</li>';
	}
if($output!="") { ?>
	Ci sono i seguenti permalink doppi:<br />
	<ul><?= $output; ?></ul>
	<a href="?fixpermalink" class="smallbutton">Clicca qui per risolvere</a><br />
	<?php  }
else { ?>
	Tutto a posto!<br />
	<?php  } ?>

	
<br />
	
<h2>Verifica delle immagini orfane</h2>
<p>Le immagini orfane sono le immagini che sono in archivio ma non risultano utilizzate in nessuna pagina</p>
<a href="?checkorphanimages" class="smallbutton">Clicca qui per verificare</a><br />

<br />

<h2>Verifica e correzione della codifica caratteri del database</h2>
<a href="?checkdburf8" class="smallbutton">Clicca qui per verificare e correggere</a><br />


<?php 
include_once("../inc/foot.inc.php");
