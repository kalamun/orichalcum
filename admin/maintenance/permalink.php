<?php 
/* (c) Kalamun.org - GNU/GPL 3 */


define("PAGE_NAME","Maintenance:Permalinks verification");
include_once("../inc/head.inc.php");

/* ACTIONS */
if(isset($_GET['fixpermalink']))
{
	function createNewDir($olddir)
	{
			// create a new dir
			$dir = $olddir;
			
			// remove any previous date
			$dir = preg_replace("/(\d{4}).(\d+).(\d+)/", "", $dir);
			$dir = preg_replace("/(\d+).(\d+).(\d{4})/", "", $dir);
			$dir = preg_replace("/(\d{7,8})/", "", $dir);
			
			// strip multiple -
			$dir = preg_replace("/-+/", "-", $dir);
			
			// add a random 6 digits number
			$dir = rand(1,999999).'-'.$dir;
			
			// crop longest strings
			if(strlen($dir)>64) $dir = substr($dir, 0 ,64);
			
			return $dir;
	}

	$log="";
	
	foreach( array(TABLE_PAGINE => "idpag", TABLE_NEWS => "idnews", TABLE_SHOP_ITEMS => "idsitem") as $table=>$id )
	{
		$query="SELECT * FROM (SELECT `dir`,`ll`,count(*) AS `tot` FROM ".$table." GROUP BY `dir`,`ll`) AS `subtable` WHERE `tot`>1 ORDER BY `tot` DESC";
		$results=ksql_query($query);
		while($row=ksql_fetch_array($results))
		{
			$q="SELECT `".$id."` FROM ".$table." WHERE `dir`='".ksql_real_escape_string($row['dir'])."' AND `ll`='".ksql_real_escape_string($row['ll'])."'";
			$rs=ksql_query($q);
			while($r=ksql_fetch_array($rs))
			{
				$newdir = createNewDir($row['dir']);
				ksql_query("UPDATE ".$table." SET `dir`='".ksql_real_escape_string($newdir)."' WHERE `".$id."`=".$r[$id]);
				$log .= $kaTranslate->translate("Maintenance:Changed <em>%s</em> to <em>%s</em><br>", $row['dir'], $newdir);
			}
		}
	}
}

if(isset($success)) echo '<div id="MsgSuccess">'.$success.'</div>';
elseif(isset($alert)) echo '<div id="MsgAlert">'.$alert.'</div>';
/* END ACTIONS */

?>

<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
<br />

<?php 

if(!empty($log))
{
	echo $log.'<br><br>';
}

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
