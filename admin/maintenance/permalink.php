<?php 
/* (c) Kalamun.org - GNU/GPL 3 */


define("PAGE_NAME","Verifica dei permalink");
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

if(isset($success)) echo '<div id="MsgSuccess">'.$success.'</div>';
elseif(isset($alert)) echo '<div id="MsgAlert">'.$alert.'</div>';
/* FINE AZIONI */

?>
<h1><?php  echo PAGE_NAME; ?></h1>
<br />

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



<?php 
include_once("../inc/foot.inc.php");
