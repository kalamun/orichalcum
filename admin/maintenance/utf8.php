<?php 
/* (c) Kalamun.org - GNU/GPL 3 */


define("PAGE_NAME","Verifica e correzione della codifica caratteri del database");
include_once("../inc/head.inc.php");

/* AZIONI */
if(isset($_GET['checkdburf8'])) {
	// check if all tables and columns of the db are UTF8 encoded
	$const=get_defined_constants(true);
	$count=array(0,0);
	foreach($const['user'] as $k=>$v) {
		if(substr($k,0,6)=="TABLE_") {
			$rs=ksql_query("SHOW TABLE STATUS LIKE '".constant($k)."'");
			if($row=ksql_fetch_array($rs)) {
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
		}
	$success="Verifica codifica database: ".$count[0]." tabelle aggiustate, ".$count[1]." colonne aggiustate";
	}

if(isset($success)) echo '<div id="MsgSuccess">'.$success.'</div>';
elseif(isset($alert)) echo '<div id="MsgAlert">'.$alert.'</div>';
/* FINE AZIONI */

?>
<h1><?php  echo PAGE_NAME; ?></h1>
<br />

<a href="?checkdburf8" class="smallbutton">Clicca qui per verificare e correggere</a><br />


<?php 
include_once("../inc/foot.inc.php");
