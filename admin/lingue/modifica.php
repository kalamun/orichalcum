<?php 
session_start();
if(!isset($_SESSION['iduser'])) die();

include('../inc/connect.inc.php');
include('../inc/kalamun.lib.php');
include('../inc/main.lib.php');
?>

<div id="iPopUpHeader">
	<h1>Modifica lingua</h1>
	<a href="javascript:kCloseIPopUp();" class="closeWindow"><img src="<?= ADMINDIR; ?>img/closeWindow.gif" alt="Close" width="9" height="9" /></a>
	</div>

<div style="padding:20px;">
	<div class="box">Scegli una lingua <select onchange="kSelectNewLang();" id="langTemplates"><option value="">Personalizzata...</option><?php 
		$ll=file('languages.txt');
		foreach($ll as $l) {
			$l=explode("\t",trim($l));
			?><option value="<?= trim($l[1]).trim($l[2]).trim($l[0]); ?>"><?= $l[0]; ?></option><?php 
			}
		?></select>
		</div>
	<br /><br />
	<form action="" method="post">
	<?php 
	$query="SELECT * FROM ".TABLE_LINGUE." WHERE idli=".$_POST['idli'];
	$results=mysql_query($query);
	$row=mysql_fetch_array($results);
	echo b3_create_input("lingua","text","Lingua: ",b3_lmthize($row['lingua'],"input"),"300px",64).'<br /><br />';
	echo b3_create_input("ll","text","Short tag: ",b3_lmthize($row['ll'],"input"),"50px",2,"onfocus=\"alert('Attento!\\nSe cambi questo parametro, perderai il contenuto\\ngi&agrave; inserito relativo a questa lingua.');this.onfocus=null;\"").' ';
	echo b3_create_input("code","text","Codice ISO: ",b3_lmthize($row['code'],"input"),"70px",5).'<br /><br />';
	echo b3_create_input("online","checkbox","On-line","","","",($row['online']=='s'?'checked':'')).'<br /><br />';
	echo b3_create_input("rtl","checkbox","Direzione del testo da destra a sinistra (RTL)","","","",($row['rtl']=='s'?'checked':'')).'<br /><br />';
	?><input type="hidden" name="idli" value="<?= $row['idli']; ?>" />
	
	<div class="submit" id="submit">
		<input type="submit" name="update" class="button" value="Salva le modifiche" />
		</div>
	</form>
	</div>
