<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Setup:Template");
include_once("../inc/head.inc.php");
include_once("../inc/template.lib.php");

$default_template=$kaImpostazioni->getVar('template_default',1,$_SESSION['ll']);
$default_mobiletemplate=$kaImpostazioni->getVar('template_default',2,$_SESSION['ll']);

?>
<script type="text/javascript" src="js/template.js"></script>
<script type="text/javascript">var editor=new kEditor();</script>
<?php 

/* ACTIONS */

// set a new default template
if(isset($_GET['default'])) {
	if(is_dir(BASERELDIR.DIR_TEMPLATE.$_GET['default'])&&trim($_GET['default'],".")!="") {
		$log="";

		if(!$kaImpostazioni->setParam("template_default",$_GET['default'],$default_mobiletemplate,$_SESSION['ll'])) $log="Setup:Error setting the new template";
		
		if($log!="") {
			echo '<div id="MsgAlert">'.$kaTranslate->translate($log).'</div>';
			$kaLog->add("ERR",'Setup: Error while changing the default template');
			}
		else {
			echo '<div id="MsgSuccess">'.$kaTranslate->translate('Setup:Successfully saved').'</div>';
			$kaLog->add("UPD",'Setup: New default template: <em>'.$_GET['default'].'</em>');
			$default_template=$_GET['default'];
			}
		}
	}

// set a new default template for mobile
if(isset($_GET['defaultmobile'])) {
	if(is_dir(BASERELDIR.DIR_TEMPLATE.$_GET['defaultmobile'])&&trim($_GET['defaultmobile'],".")!="") {
		$log="";

		if(!$kaImpostazioni->setParam("template_default",$default_template,$_GET['defaultmobile'],$_SESSION['ll'])) $log="Setup:Error setting the new template";
		
		if($log!="") {
			echo '<div id="MsgAlert">'.$kaTranslate->translate($log).'</div>';
			$kaLog->add("ERR",'Setup: Error while changing the default template');
			}
		else {
			echo '<div id="MsgSuccess">'.$kaTranslate->translate('Setup:Successfully saved').'</div>';
			$kaLog->add("UPD",'Setup: New default mobile template: <em>'.$_GET['defaultmobile'].'</em>');
			$default_mobiletemplate=$_GET['defaultmobile'];
			}
		}
	}

// delete template
elseif(isset($_GET['delete'])) {
	if($_GET['delete']!=$default_template) {
		$kaTemplate=new kaTemplate($_GET['delete']);
		$results=$kaTemplate->delete();
		if($results==false) {
			echo '<div id="MsgAlert">'.$kaTemplate->getError().'</div>';
			$kaLog->add("ERR",'Errore durante la rimozione del template <em>'.$_GET['delete'].'</em>');
			}
		else {
			echo '<div id="MsgSuccess">Template rimosso con successo</div>';
			$kaLog->add("DEL",'Rimosso con successo il template <em>'.$_GET['delete'].'</em>');
			}
		}
	else {
		echo '<div id="MsgAlert">Non puoi eliminare il template predefinito!</div>';
		$kaLog->add("ERR",'Errore durante la rimozione del template <em>'.$_GET['delete'].'</em>');
		}
	}

// copy template
elseif(isset($_POST['copy'])&&isset($_POST['destination'])) {
	$kaTemplate=new kaTemplate();
	$results=$kaTemplate->copy($_POST['copy'],$_POST['destination']);
	if($results==false) {
		echo '<div id="MsgAlert">'.$kaTemplate->getError().'</div>';
		$kaLog->add("ERR",'Errore durante la copia del template <em>'.$_POST['copy'].'</em>');
		}
	else {
		echo '<div id="MsgSuccess">Template copiato con successo</div>';
		$kaLog->add("DEL",'Copiato con successo il template <em>'.$_POST['copy'].'</em> in <em>'.$_POST['destination'].'</em>');
		}
	}
/**/


/**********************************/
/* TEMPLATE EDITOR                */
/**********************************/

if(isset($_GET['tpl'])) {
	$allowedExtensions=array("php"=>true,"txt"=>true,"html"=>true,"sql"=>true,"htm"=>true,"js"=>true,"css"=>true,"svg"=>true);

	$_GET['tpl']=trim($_GET['tpl'],"./");
	if(isset($_GET['f'])) $_GET['f']=trim($_GET['f'],"./");
	if(!isset($_GET['f'])||$_GET['f']==""||!file_exists(BASERELDIR.DIR_TEMPLATE.$_GET['tpl'].'/'.$_GET['f'])) $_GET['f']="index.php";
	$kaTemplate=new kaTemplateFile($_GET['tpl'].'/'.$_GET['f']);

	/* azioni */
	if(isset($_POST['update'])) {
		if(get_magic_quotes_gpc()==true) $_POST['contents']=stripslashes($_POST['contents']);
		$results=$kaTemplate->write($_POST['contents']);
		if($results==false) {
			echo '<div id="MsgAlert">'.$kaTemplate->getError().'</div>';
			$kaLog->add("ERR",'Errore durante il salvataggio del file di template <em>'.$_GET['tpl'].'/'.$_GET['f'].'</em>');
			}
		else {
			echo '<div id="MsgSuccess">File salvato con successo</div>';
			$kaLog->add("INS",'Salvato con successo il file di template <em>'.$_GET['tpl'].'/'.$_GET['f'].'</em>');
			}
		}
	elseif(isset($_GET['delf'])) {
		$results=$kaTemplate->delete($_GET['tpl'].'/'.$_GET['delf']);
		if($results==false) {
			echo '<div id="MsgAlert">'.$kaTemplate->getError().'</div>';
			$kaLog->add("ERR",'Errore durante la rimozione del file di template <em>'.$_GET['tpl'].'/'.$_GET['delf'].'</em>');
			}
		else {
			echo '<div id="MsgSuccess">File rimosso con successo</div>';
			$kaLog->add("DEL",'Rimosso con successo il file di template <em>'.$_GET['tpl'].'/'.$_GET['delf'].'</em>');
			}
		if(!file_exists(BASERELDIR.DIR_TEMPLATE.$_GET['tpl'].'/'.$_GET['f'])) {
			$_GET['f']="index.php";
			$kaTemplate=new kaTemplateFile($_GET['tpl'].'/'.$_GET['f']);
			}
		}
	/**/
	?>

	<h1><?= $kaTranslate->translate(PAGE_NAME).': '.$_GET['tpl']; ?></h1>
	<br />
	<?php 
	
	if(is_dir(BASERELDIR.DIR_TEMPLATE.$_GET['tpl'])&&trim($_GET['tpl'],".")!="") {
		?>
		<div class="subset"><h2><?= $kaTranslate->translate('Setup:Files'); ?></h2><?php 
			function printDirContent($dir) {
				echo '<ul class="fileList">';
				foreach(scandir($dir) as $file) {
					if(is_dir($dir.'/'.$file)&&trim($file,"./")!="") { ?>
						<li class="folder">
						<a onclick="editor.explorerSwapChild(this.parentNode);"><?= $file; ?></a>
						<?php  printDirContent($dir.'/'.$file); ?>
						</li>
						<?php  }
					}
				foreach(scandir($dir) as $file) {
					if(!is_dir($dir.'/'.$file)) {
						$extension=substr($file,strrpos($file,'.')+1);
						?>
						<li class="file">
							<?php 
							if(isset($GLOBALS['allowedExtensions'][$extension])) echo '<a href="?tpl='.$_GET['tpl'].'&f='.trim(substr($dir,strlen(BASERELDIR.DIR_TEMPLATE.$_GET['tpl'])+1).'/'.$file,"./").'">'.$file.'</a>';
							else echo $file;
							?>
						<small class="actions"><a href="?tpl=<?= $_GET['tpl'].'&f='.$_GET['f'].'&delf='.trim(substr($dir,strlen(BASERELDIR.DIR_TEMPLATE.$_GET['tpl'])+1).'/'.$file,"./"); ?>" onclick="return confirm(\'Sei sicuro di voler cancellare questo file?\');"><img src="<?= ADMINRELDIR; ?>img/close.png" width="12" height="12" alt="Elimina file" /></a></small>
						</li>
						<?php  }
					}
				echo '</ul>';
				}
			printDirContent(BASERELDIR.DIR_TEMPLATE.$_GET['tpl']);
			?></div>
		
		<div class="topset">
			<form action="?tpl=<?= $_GET['tpl']; ?>&f=<?= $_GET['f']; ?>" method="post">
			<div class="intestazione"><div style="float:right;">Ultima modifica <?= date("d-m-Y (H:i)",filemtime(BASERELDIR.DIR_TEMPLATE.$kaTemplate->getFilename())); ?></div><h2><?= $_GET['tpl'].'/'.$_GET['f']; ?></h2></div>
			<textarea id="HTMLeditor" wrap="off" name="contents"><?php 
				echo htmlentities($kaTemplate->read(),ENT_NOQUOTES,"UTF-8");
				?></textarea><br />
			<script type="text/javascript">editor.init('HTMLeditor');</script>
			<div class="submit"><input type="submit" name="update" value="Salva" class="button"><input type="button" value="Indietro" class="button" onclick="window.location='?';"></div>
			</form>
			</div>
		<?php  }
	else {
		echo 'Il template che hai selezionato non &egrave; installato!';
		}
	}

elseif(isset($_GET['copy'])) {
	?>
	<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
	<br />
	<div class="topset">
		<script type="text/javascript">
			function checkURL(field) {
				field.value=field.value.replace(/[^\w^\/]+/g,"-");
				}
			</script>
		<form action="?" method="post">
			<input type="hidden" name="copy" value="<?= $_GET['copy']; ?>" />
			<div class="title"><?= b3_create_input("destination","text","Nome della copia del template<br />",$_GET['copy']."_copy","400px",64,'onkeyup="checkURL(this)"'); ?></div><br />
			<div class="submit"><input type="submit" value="Salva" class="button" /> <input type="button" value="Annulla" class="button" onclick="window.location('?');" /></div>
			</form>
	</div>
	<?php 
	}


/**************************************/
/* TEMPLATE LIST                      */
/**************************************/

else {
	?>
	<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
	<br />
	<?php  include('templatemenu.php'); ?>
	
	<div class="topset">
		<input type="hidden" id="usePage" />
		<table class="tabella">
		<tr><th>Template</th><th></th></tr><?php 		if($handle=opendir(BASERELDIR.DIR_TEMPLATE)) {
			while(false!==($file=readdir($handle))) {
				if(is_dir(BASERELDIR.DIR_TEMPLATE.$file)&&trim($file,".")!="") { ?>
					<tr>
					<td><h2><a href="?tpl=<?= $file; ?>"><?= ($file==$default_template?'<strong>'.$file.'</strong>':$file); ?></a></h2>
						<small class="actions">
							<a href="?tpl=<?= $file; ?>">Modifica</a> |
							<a href="?copy=<?= $file; ?>">Copia</a> |
							<a href="?delete=<?= $file; ?>" onclick="return confirm(\'Sei sicuro di voler eliminare questo template?\');" class="warning">Elimina</a> |
							<a href="?default=<?= $file; ?>">PREDEFINITO</a> |
							<a href="?defaultmobile=<?= $file; ?>">Mobile</a>
							</small>
						</td>
					<td class="percorso">
						<?= $file==$default_template?'PREDEFINITO':'&nbsp;'; ?><br />
						<?= $file==$default_mobiletemplate?'Mobile':'&nbsp;'; ?><br />
						</td>
					</tr>
					<?php  }
				}
			closedir($handle);
			}
		?></table>
		</div>
	<?php 
	}

include_once("../inc/foot.inc.php");
