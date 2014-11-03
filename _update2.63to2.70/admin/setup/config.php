<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Config.inc.php");
define("PAGE_LEVEL",1);
include_once("../inc/head.inc.php");
include_once("../inc/config.lib.php");
$kaConfigInc=new kaConfigInc;

//$filename='../inc/config.inc.php';
//flush();

if(isset($_POST['update'])) {
	$_POST['contents']=str_replace('$__db[\'user\']="'.preg_replace("/./","*",$__db['user']).'";','$__db[\'user\']="'.$__db['user'].'";',$_POST['contents']);
	$_POST['contents']=str_replace('$__db[\'password\']="'.preg_replace("/./","*",$__db['password']).'";','$__db[\'password\']="'.$__db['password'].'";',$_POST['contents']);
	$_POST['contents']=str_replace("\r","",$_POST['contents']);
	if($kaConfigInc->write($_POST['contents'])==false) echo '<div id="MsgAlert">'.$kaConfigInc->getError().'</div>';
	else echo '<div id="MsgSuccess">Modifiche salvate con successo</div>';
	}
elseif(isset($_POST['recover'])) {
	$kaConfigInc->recover();
	echo '<div id="MsgSuccess">Ripristinata la copia di bkup</div>';
	}
	
?>
<h1><?php  echo PAGE_NAME; ?></h1>
<br />
<div class="note">
	PER FAVORE, NON TOCCARE QUI SE NON SAI ESATTAMENTE QUELLO CHE STAI FACENDO!	<strong>POTRESTI METTERE FUORIUSO IL SITO!!!</strong><br />
	Username e Password sono stati offuscati: se vuoi cambiarli scrivi quelli nuovi al posto degli asterischi!
	</div>
<br />

<form action="?" method="post">
	<textarea style="width:100%;height:400px;" name="contents"><?php 
		$c=$kaConfigInc->read();
		$c=str_replace('$__db[\'user\']="'.$__db['user'].'";','$__db[\'user\']="'.preg_replace("/./","*",$__db['user']).'";',$c);
		$c=str_replace('$__db[\'password\']="'.$__db['password'].'";','$__db[\'password\']="'.preg_replace("/./","*",$__db['password']).'";',$c);
		echo $c;
		?></textarea><br /><br />
	<div class="submit"><input type="submit" name="update" value="Salva" class="button">
	<?php 
	if(file_exists($kaConfigInc->getFilename().'.bkup.php')) {
		?><input type="submit" name="recover" value="Ripristina la copia di back-up del <?= date("d-m-Y (H:i)",filemtime($kaConfigInc->getFilename().'.bkup.php')); ?>" class="button" onclick="return confirm('Sei sicuro di voler ripristinare la copia di back-up?');" /><?php 
		}
	?>
	</div>
	</form></div><br /><br />

<?php 
include_once("../inc/foot.inc.php");
