<?
/* 2010 (c) Roberto Kalamun Pasini - GPLv3 */

define("PAGE_NAME","Inserisci un nuovo membro in anagrafica");
include_once("../inc/head.inc.php");
include_once("./members.lib.php");
$kaMembers=new kaMembers();

/* AZIONI */
if(isset($_POST['insert'])&&$_POST['m_prefix']!=""&&$_POST['m_qty']!="") {
	$log="";
	if(preg_match("/\d{2}.\d{2}.\d{4}/",$_POST['m_expiration'])) $_POST['m_expiration']=preg_replace("/(\d{2}).(\d{2}).(\d{4})/","$3-$2-$1",$_POST['m_expiration']);
	else $_POST['m_expiration']="";
	$id=$kaMembers->addMass($_POST['m_qty'],$_POST['m_prefix'],$_POST['m_affiliation'],$_POST['m_expiration']);

	if($id==false) $log="Problemi durante la creazione del nuovo utente";
	if($log=="") {
		$id=mysql_insert_id();
		$kaLog->add("INS",'Members: Mass creation successfully done for '.$_POST['m_qty'].' users');
		echo '<div id="MsgSuccess">Membri inseriti con successo</div>';
		}
	else {
		$kaLog->add("ERR",'Members: Errors occurred while creating users');
		echo '<div id="MsgAlert">'.$log.'</div>';
		}
	}
/* FINE AZIONI */

?>

	<script type="text/javascript">
		function checkForm(f) {
			if(f.m_qty.value.length==0) { alert("Devi specificare una quantità"); return false; }
			return true;
			}
		</script>

	<h1><?= $kaTranslate->translate('Members:New member'); ?></h1>
	<br />
	<? include('nuovomenu.php'); ?>
	<br />
	<form action="" method="post" onSubmit="return checkForm(this);">
	<div class="topset">
	<table>
	<tr><td><label for="m_qty"><?= $kaTranslate->translate('Members:How many users?'); ?></label></td><td><?= b3_create_input("m_qty","text","","10","100px",5); ?></td></tr>
	<tr><td><label for="m_prefix"><?= $kaTranslate->translate('Members:Username prefix'); ?></label></td><td><div class="title"><?= b3_create_input("m_prefix","text","","","400px",64); ?></div></td></tr>
	<tr><td><label for="m_affiliation"><?= $kaTranslate->translate('Members:Affiliation'); ?></label></td><td><?= b3_create_input("m_affiliation","text","","","100px",16); ?></td></tr>
	<tr><td><label for="m_scadenza"><?= $kaTranslate->translate('Members:Expiration'); ?></label></td><td><?= b3_create_input("m_expiration","text","",date("d-m-Y",mktime(0,0,0,date("m")+1,date("d"),date("Y"))),"100px",16); ?>  <span class="small"><?= $kaTranslate->translate('Members:Leave empty for no expiration'); ?></span></td></tr>
	</table>
	</div>
	<div class="submit"><input type="submit" name="insert" value="Crea Utente" class="button"></div>
	</form>

<?
	
include_once("../inc/foot.inc.php");
?>
