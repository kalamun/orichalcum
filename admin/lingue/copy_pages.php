<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

session_start();
define("PAGE_NAME","Languages:Copy content");
define("PAGE_LEVEL",1);
include_once("../inc/head.inc.php");

if(!isset($_GET['sourceLang'])&&isset($_POST['sourceLang'])) $_GET['sourceLang']=$_POST['sourceLang'];
if(!isset($_GET['sourceLang'])) $_GET['sourceLang']=DEFAULT_LANG;


/* AZIONI */
if(isset($_POST['copyPages'])&&isset($_POST['sourceLang'])&&isset($_POST['destinationLang'])) {
	$log="";
	if($_POST['sourceLang']==$_POST['destinationLang']) $log="Source and destination languages can't be the same";
	
	if($log=="") {
		//categorie
		$catMap=array();
		$q="SELECT * FROM ".TABLE_CATEGORIE." WHERE tabella='".TABLE_PAGINE."' AND ll='".$_POST['sourceLang']."'";
		$rs=ksql_query($q);
		while($r=ksql_fetch_array($results)) {
			$q2="SELECT * FROM ".TABLE_CATEGORIE." WHERE tabella='".TABLE_PAGINE."' AND ll='".$_POST['destinationLang']."' AND dir='".$r['dir']."' LIMIT 1";
			$rs2=ksql_query($q2);
			$r2=ksql_fetch_array($rs2);
			if($r2==false) {
				$q="INSERT INTO ".TABLE_CATEGORIE." (tabella,categoria,dir,ordine,ll) VALUES('".TABLE_PAGINE."','".ksql_real_escape_string($r['categoria'])."','".ksql_real_escape_string($r['dir'])."','".$r['ordine']."','".$_POST['destinationLang']."')";
				if(ksql_query($q)) $catMap[$r['idcat']]=ksql_insert_id();
				}
			else $catMap[$r['idcat']]=$r2['idcat'];
			}
		
		$query="SELECT * FROM ".TABLE_PAGINE." WHERE (idpag=0 ";
		foreach($_POST['copyPage'] as $idpag) {
			$query.=" OR idpag=".intval($idpag)." ";
			}
		$query.=") AND ll='".$_POST['sourceLang']."'";
		$results=ksql_query($query);
		while($row=ksql_fetch_array($results)) {
			$row['dir']==str_replace('/'.strtolower($_POST['sourceLang']).'/','/'.strtolower($_POST['destinationLang']).'/',$row['dir']);
			$q="SELECT * FROM ".TABLE_PAGINE." WHERE ll='".$_POST['destinationLang']."' AND dir='".$row['dir']."' LIMIT 1";
			$rs=ksql_query($q);
			$r=ksql_fetch_array($rs);
			if($r!=false&&isset($_POST['overwrite'])) {
				$q="DELETE FROM ".TABLE_PAGINE." WHERE idpag='".$r['idpag']."'";
				ksql_query($q);
				}
			if($r==false||isset($_POST['overwrite'])) {
				//pagina
				$idpag=$row['idpag'];
				if(isset($row['idpag'])) unset($row['idpag']);
				$row['ll']=$_POST['destinationLang'];
				foreach($catMap as $ka=>$v) {
					$row['categorie']=str_replace(",".$ka.",",",".$v.",",$row['categorie']);
					}
				$q="INSERT INTO ".TABLE_PAGINE." (";
				foreach($row as $ka=>$v) {
					if(!is_numeric($ka)) $q.=$ka.',';
					}
				$q=rtrim($q,",");
				$q.=') VALUES(';
				foreach($row as $ka=>$v) {
					if(!is_numeric($ka)) $q.="'".ksql_real_escape_string($v)."',";
					}
				$q=rtrim($q,",");
				$q.=')';
				ksql_query($q);
				$row['idpag']=ksql_insert_id();
				//immagini
				$q="SELECT * FROM ".TABLE_IMGALLERY." WHERE tabella='".TABLE_PAGINE."' AND id='".$idpag."'";
				$rs=ksql_query($q);
				while($r=ksql_fetch_array($rs)) {
					$q="INSERT INTO ".TABLE_IMGALLERY." (tabella,id,ordine,idimg) VALUES('".TABLE_PAGINE."','".$row['idpag']."','".$r['ordine']."','".$r['idimg']."')";
					ksql_query($q);
					}
				//documenti
				$q="SELECT * FROM ".TABLE_DOCGALLERY." WHERE tabella='".TABLE_PAGINE."' AND id='".$idpag."'";
				$rs=ksql_query($q);
				while($r=ksql_fetch_array($rs)) {
					$q="INSERT INTO ".TABLE_DOCGALLERY." (tabella,id,ordine,iddoc) VALUES('".TABLE_PAGINE."','".$row['idpag']."','".$r['ordine']."','".$r['iddoc']."')";
					ksql_query($q);
					}
				//metadata
				$q="SELECT * FROM ".TABLE_METADATA." WHERE tabella='".TABLE_PAGINE."' AND id='".$idpag."'";
				$rs=ksql_query($q);
				while($r=ksql_fetch_array($rs)) {
					$q="INSERT INTO ".TABLE_METADATA." (tabella,id,param,value) VALUES('".TABLE_PAGINE."','".$row['idpag']."','".ksql_real_escape_string($r['param'])."','".ksql_real_escape_string($r['value'])."')";
					ksql_query($q);
					}
				}
			}

		}
	}
/***/


if(isset($log)) {
	if($log=="") echo '<div id="MsgSuccess">'.$kaTranslate->translate('Languages:Language successfully copied').'</div>';
	else echo '<div id="MsgAlert">'.$kaTranslate->translate('Languages:'.$log).'</div>';
	}

?><h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
	<br />
	<?php  include('copy_menu.inc.php'); ?>
	<br />

	<form action="?" method="post">
	<table>
	<tr>
	<td><label for="sourceLang"><?= $kaTranslate->translate("Languages:Source language"); ?></label><br />
		<select name="sourceLang" id="sourceLang" onchange="window.location.href='<?= $_SERVER['PHP_SELF']; ?>?sourceLang='+this.value"><?php 
		$query="SELECT * FROM ".TABLE_LINGUE." ORDER BY ordine";
		$results=ksql_query($query);
		$i=0;
		while($row=ksql_fetch_array($results)) {
			?><option value="<?= $row['ll']; ?>"<?= $_GET['sourceLang']==$row['ll']?' selected':''; ?>><?= $row['lingua']; ?></option>
			<?php  } ?>
		</select>
		</td>
	<td><br />&rarr;</td>
	<td><label for="destinationLang"><?= $kaTranslate->translate("Languages:Destination language"); ?></label><br />
		<select name="destinationLang" id="destinationLang"><?php 
		$query="SELECT * FROM ".TABLE_LINGUE." ORDER BY ordine";
		$results=ksql_query($query);
		$i=0;
		while($row=ksql_fetch_array($results)) {
			?><option value="<?= $row['ll']; ?>"><?= $row['lingua']; ?></option>
			<?php  } ?>
		</select>
		</td>
	</tr></table>

	<br />
	<h2><?= $kaTranslate->translate("Languages:What pages do you want to copy?"); ?></h2>
	<table>
	<?php 
	$query="SELECT * FROM ".TABLE_PAGINE." WHERE ll='".$_GET['sourceLang']."' ORDER BY titolo";
	$results=ksql_query($query);
	while($row=ksql_fetch_array($results)) { ?>
		<tr><td><input type="checkbox" name="copyPage[]" value="<?= $row['idpag']; ?>" id="copyPage<?= $row['idpag']; ?>" /></td><td><label for="copyPage<?= $row['idpag']; ?>"><?= $row['titolo']; ?> <small>(<?= $row['dir']; ?>)</small></label></td></tr>
		<?php  } ?>
	</table>
	<br />
	<br />
	<div class="submit">
		<input type="submit" name="copyPages" value="<?= $kaTranslate->translate("Languages:Copy pages"); ?>" class="button" />
		<input type="checkbox" name="overwrite" id="overwrite" /> <label for="overwrite"><?= $kaTranslate->translate("Languages:Overwrite existing ones"); ?></label><br />
		</div>
	</form>
<?php 	
include_once("../inc/foot.inc.php");
