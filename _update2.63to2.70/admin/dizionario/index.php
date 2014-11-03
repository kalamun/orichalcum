<?php 
define("PAGE_NAME","Menu:Dictionary");
include_once("../inc/head.inc.php");

/* ACTIONS */

/* clean terms */
function cleanTerm($term)
{
	$term=trim($term);
	// convert to html
	$term=b3_htmlize($term,true);
	$term=trim($term);
	// if term is <p>something</p>, strip p
	if(substr_count($term,"<p>")==1) $term=preg_replace("/<\/?p>/is","",$term);
	$term=trim($term);
	// remove ending <br>
	$term=preg_replace("/(<br ?\/?>)+$/is","",$term);
	$term=trim($term);
	
	return $term;
}

if(isset($_POST['insert']))
{
	$log="";
	if(get_magic_quotes_gpc()==true) $_POST['param']=stripslashes($_POST['param']);
	$term=cleanTerm($_POST['testo']);
	
	$query="INSERT INTO `".TABLE_DIZIONARIO."` (`param`,`testo`,`ll`) VALUES('".mysql_real_escape_string($_POST['param'])."','".$term."','".$_SESSION['ll']."')";
	if(!mysql_query($query)) $log="Problemi durante il salvataggio nel database";

	if($log!="") echo '<div id="MsgAlert">'.$log.'</div>';
	else echo '<div id="MsgSuccess">Termine salvato con successo</div>';

} elseif(isset($_POST['update'])) {
	$log="";
	if(get_magic_quotes_gpc()==true) $_POST['param']=stripslashes($_POST['param']);
	$term=cleanTerm($_POST['testo']);
	
	$query="UPDATE `".TABLE_DIZIONARIO."` SET `param`='".mysql_real_escape_string($_POST['param'])."',`testo`='".$term."' WHERE `iddiz`=".$_POST['iddiz'];
	if(!mysql_query($query)) $log="Problemi durante il salvataggio nel database";

	if($log!="") echo '<div id="MsgAlert">'.$log.'</div>';
	else echo '<div id="MsgSuccess">Termine salvato con successo</div>';

} elseif(isset($_GET['delete'])) {
	$log="";
	if(get_magic_quotes_gpc()==true) $_GET['delete']=stripslashes($_GET['delete']);
	$query="DELETE FROM `".TABLE_DIZIONARIO."` WHERE `param`='".mysql_real_escape_string($_GET['delete'])."'";
	if(!mysql_query($query)) $log="Problemi durante la rimozione dal database";

	if($log!="") echo '<div id="MsgAlert">'.$log.'</div>';
	else echo '<div id="MsgSuccess">Termine eliminato con successo</div>';

} elseif(isset($_GET['import'])) {
	$log="";

	// parse every file in the default template and default mobile template
	// and find declared terms
	function getEntries($dir) {
		$entries=array();
		foreach(scandir($dir) as $file) {
			if(is_dir($dir.'/'.$file)&&trim($file,"./")!="") {
				$entries=array_merge($entries,getEntries($dir.'/'.$file));
				}
			elseif(trim($file,"./")!="") {
				$c=file_get_contents($dir.'/'.$file);
				preg_match_all("/kTranslate\(['\"](.*?)['\"]\)/",$c,$match);
				foreach($match[1] as $key) {
					if(trim($key)!="") $entries[]=strip_tags($key);
					}
				}
			}
		return $entries;
		}

	$default_template=$kaImpostazioni->getVar('template_default',1,$_SESSION['ll']);
	$default_mobiletemplate=$kaImpostazioni->getVar('template_default',2,$_SESSION['ll']);

	$entries=getEntries(BASERELDIR.DIR_TEMPLATE.$default_template);
	if($default_template!=$default_mobiletemplate) $entries=array_merge($entries,getEntries(BASERELDIR.DIR_TEMPLATE.$default_mobiletemplate));

	// insert terms if them doesn't exists yet
	foreach($entries as $term) {
		$query="SELECT * FROM ".TABLE_DIZIONARIO." WHERE `param`='".mysql_real_escape_string($term)."' AND `ll`='".$_SESSION['ll']."' LIMIT 1";
		$results=mysql_query($query);
		if(!mysql_fetch_array($results)) {
			$query="INSERT INTO ".TABLE_DIZIONARIO." (`param`,`testo`,`ll`) VALUES('".mysql_real_escape_string($term)."','".b3_htmlize($term,true,"")."','".$_SESSION['ll']."')";
			if(!mysql_query($query)) $log="An error occurred while inserting `".$term."`, language `".$_SESSION['ll']."`<br />";
			}
		}

	if($log!="") echo '<div id="MsgAlert">'.$log.'</div>';
	else echo '<div id="MsgSuccess">Termine salvato con successo</div>';

}
/* END ACTIONS */


?>
<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
<br />


<?php 
/* New term GUI */
if(isset($_GET['addnew'])) { ?>
	<form action="?" method="post">
		<?= b3_create_input("param","text",$kaTranslate->translate('Dictionary:Term')." ",b3_lmthize($_GET['addnew'],"input"),"200px",255); ?><br /><br />
		<?= b3_create_textarea("testo",$kaTranslate->translate('Dictionary:Translation')." ","","99%","100px"); ?><br /><br />
		<div class="submit"><input type="submit" name="insert" value="<?= $kaTranslate->translate('UI:Save'); ?>" class="button" /> <input type="submit" name="" value="<?= $kaTranslate->translate('UI:Cancel'); ?>" class="button" /></div>
		</form><br />
	<?php  }

/* Edit term GUI */
elseif(isset($_GET['iddiz'])) {
	$query="SELECT * FROM `".TABLE_DIZIONARIO."` WHERE `iddiz`=".intval($_GET['iddiz'])." LIMIT 1";
	$results=mysql_query($query);
	$row=mysql_fetch_array($results);
	?>
	<form action="?" method="post">
		<input type="hidden" name="iddiz" value="<?= $row['iddiz']; ?>" />
		<?= b3_create_input("param","text",$kaTranslate->translate('Dictionary:Term')." ",b3_lmthize($row['param'],"input"),"200px",255); ?><br /><br />
		<?= b3_create_textarea("testo",$kaTranslate->translate('Dictionary:Translation')." ",b3_lmthize($row['testo'],"textarea"),"99%","100px",false); ?><br /><br />
		<div class="submit"><input type="submit" name="update" value="<?= $kaTranslate->translate('UI:Save'); ?>" class="button" /> <input type="submit" name="" value="<?= $kaTranslate->translate('UI:Cancel'); ?>" class="button" /></div>
		</form><br />
	<?php  }

/* List of terms GUI */
else {
	?>
	<table class="tabella" style="width:100%;">
	<tr>
		<th><?= $kaTranslate->translate('Dictionary:Term'); ?></th>
		<th><?= $kaTranslate->translate('Dictionary:%s translation',$_SESSION['ll']); ?></th>
		<th>&nbsp;</th>
		</tr>
	<?php 
	$dizionario=array();
	$query="SELECT * FROM `".TABLE_DIZIONARIO."` WHERE `ll`='".$_SESSION['ll']."' ORDER BY `param`";
	$results=mysql_query($query);
	for($i=0;$row=mysql_fetch_array($results);$i++) {
		$dizionario[]=$row;
		}

	function findTrad($param) {
		global $dizionario;
		foreach($dizionario as $d) {
			if($d['param']==$param) return $d;
			}
		$d=array("param"=>$param,"testo"=>"","iddiz"=>"","ll"=>$_SESSION['ll']);
		return $d;
		}

	$query="SELECT * FROM `".TABLE_DIZIONARIO."` GROUP BY `param` ORDER BY `param`";
	$results=mysql_query($query);
	for($i=0;$row=mysql_fetch_array($results);$i++) {
		$d=findTrad($row['param']);
		echo '<tr class="'.($i%2==0?'odd':'even').'">';
		echo '<td class="param">'.$row['param'].'</td>';
		echo '<td class="text">'.$d['testo'].'</td>';
		echo '<td><small class="actions">';
			if($d['iddiz']=="") echo '<a href="?addnew='.$d['param'].'" class="smallbutton">Modifica</a>';
			else echo '<a href="?iddiz='.$d['iddiz'].'" class="smallbutton">Modifica</a>';
			echo '<a href="?delete='.urlencode($d['param']).'" class="smallalertbutton" onclick="return confirm(\'Sicuro di voler eliminare questa voce?\')">Elimina</a>';
			echo '</small></td>';
		echo '</tr>';
		}
	if($i==0) { ?>
		<tr>
		<td colspan="3"><?= $kaTranslate->translate('Dictionary:No terms defined yet'); ?></td>
		</tr>
		<?php  }

	?>
	</table><br />

	<div class="submit">
		<form action="?addnew" method="post">
			<input type="submit" class="button" value="<?= $kaTranslate->translate('Dictionary:Add a new term'); ?>" />
			</form>
		<form action="?import" method="post">
			<input type="submit" class="smallbutton" value="<?= $kaTranslate->translate('Dictionary:Import terms from default template'); ?>" />
			</form>
		</div>
	</form>
	<br /><br />
	<?php  }

include_once("../inc/foot.inc.php");
