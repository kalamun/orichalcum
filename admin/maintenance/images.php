<?php 
/* (c) Kalamun.org - GNU/GPL 3 */



define("PAGE_NAME","Verifica delle immagini orfane");
include_once("../inc/head.inc.php");

/* AZIONI */
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


if(isset($success)) echo '<div id="MsgSuccess">'.$success.'</div>';
elseif(isset($alert)) echo '<div id="MsgAlert">'.$alert.'</div>';
/* FINE AZIONI */

?>
<h1><?php  echo PAGE_NAME; ?></h1>
<br />

<p>Le immagini orfane sono le immagini che sono in archivio ma non risultano utilizzate in nessuna pagina</p>
<a href="?checkorphanimages" class="smallbutton">Clicca qui per verificare</a><br />


<?php 
include_once("../inc/foot.inc.php");
