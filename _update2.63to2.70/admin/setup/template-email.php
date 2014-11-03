<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Setup:E-mail template");
include_once("../inc/head.inc.php");
include_once("../inc/template.lib.php");

$query="SELECT * FROM ".TABLE_CONFIG." WHERE param='email_template_default' AND ll='".$_SESSION['ll']."' LIMIT 1";
$results=mysql_query($query);
$row=mysql_fetch_array($results);
$default=$row['value1'];

$query="SELECT * FROM ".TABLE_CONFIG." WHERE param='template_default' AND ll='".$_SESSION['ll']."' LIMIT 1";
$results=mysql_query($query);
$row=mysql_fetch_array($results);
$defaulttemplate=$row['value1'];


/* AZIONI */
if(isset($_GET['default'])) {
	if(is_file(BASERELDIR.DIR_TEMPLATE.$defaulttemplate.'/email/'.$_GET['default'])&&trim($_GET['default'],".")!="") {
		$log="";
		$query="SELECT * FROM ".TABLE_CONFIG." WHERE param='email_template_default' AND ll='".$_SESSION['ll']."' LIMIT 1";
		$results=mysql_query($query);
		if(!mysql_fetch_array($results)) $query="INSERT INTO ".TABLE_CONFIG." (value1,value2,param,ll) VALUES('".$_GET['default']."','','email_template_default','".$_SESSION['ll']."')";
		else $query="UPDATE ".TABLE_CONFIG." SET value1='".$_GET['default']."' WHERE param='email_template_default' AND ll='".$_SESSION['ll']."' LIMIT 1";
		if(!mysql_query($query)) $log="Errore di salvataggio nel database";
		if($log!="") {
			echo '<div id="MsgAlert">'.$log.'</div>';
			$kaLog->add("ERR",'Errore nell\'impostazione di <em>'.$_GET['default'].'</em> come template di default per le e-mail');
			}
		else {
			echo '<div id="MsgSuccess">Modifiche salvate con successo</div>';
			$kaLog->add("UPD",'Impostato <em>'.$_GET['default'].'</em> come nuovo template di default per le e-mail');
			$default=$_GET['default'];
			}
		}
	}
elseif(isset($_POST['update'])) {
	$log="";
	if(!isset($_POST['email_log1'])||$_POST['email_log1']!="true") $_POST['email_log1']="false";
	if(!$kaImpostazioni->replaceParam("email_log",$_POST['email_log1'],$_POST['email_log2'])) $log.="Problemi durante il salvataggio del parametro ".$ka."<br />";
	if(!$kaImpostazioni->replaceParam("email-queue-mailperhour",$_POST['email-queue-mailperhour'],"","*")) $log.="Problemi durante il salvataggio del parametro email-queue-mailperhour<br />";
	if($log!="") {
		echo '<div id="MsgAlert">'.$log.'</div>';
		$kaLog->add("ERR",'SetupBase: Errore nella modifica delle directory');
		}
	else {
		$kaLog->add("UPD",'SetupBase: Modificate le impostazioni di log e-mail');
		echo '<div id="MsgSuccess">'.$kaTranslate->translate('Setup:Successfully saved').'</div>';
		}
	}
/**/

?>

<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
<br />
<?php  include('templatemenu.php'); ?>
<div class="topset">
	<input type="hidden" id="usePage" />
	<table class="tabella">
	<tr><th><?= $kaTranslate->translate('Setup:Template'); ?></th><th></th></tr><?php 
	if($handle=opendir(BASERELDIR.DIR_TEMPLATE.$defaulttemplate.'/email')) {
		while(false!==($file=readdir($handle))) {
			if(!is_dir(BASERELDIR.DIR_TEMPLATE.$file)&&trim($file,".")!="") { ?>
				<tr>
				<td><h2><a href="?default=<?= $file; ?>"><?= ($file==$default?'<strong>'.str_replace(".php","",$file).'</strong>':str_replace(".php","",$file)); ?></a></h2>
					<small class="actions"><a href="?default=<?= $file; ?>">Imposta come predefinito</a></small>
					</td>
				<td class="percorso"><?= ($file==$default?$kaTranslate->translate('Setup:DEFAULT'):''); ?></td>
				</tr>
				<?php  }
			}
		closedir($handle);
		}
	?></table>
	</div>
<br />

<?php 
include_once("../inc/foot.inc.php");
