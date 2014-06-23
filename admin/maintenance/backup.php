<?
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Maintenance:Backup");
include_once("../inc/head.inc.php");

if(!file_exists('bkups')) mkdir('bkups');

/* AZIONI */
if(isset($_GET['delete'])) {
	$file=$_GET['delete'];
	$file=str_replace(array("/"," "),"",$file);
	if(file_exists('bkups/'.$file)) {
		unlink('bkups/'.$file);
		$success="File successfully deleted";
		}
	else $alert="The filename is not valid: impossible to delete ".$file;
	}

elseif(isset($_GET['backupnow'])) {
	if(class_exists("ZipArchive")) {

		$mysqldump=rtrim($_SERVER['DOCUMENT_ROOT'],"/").BASEDIR.'admin/maintenance/bkups/orichalcum.sql';

		// export database
		exec('mysqldump --user='.$__db['user'].' --password='.$__db['password'].' --host='.$__db['host'].' --extended-insert=false '.$__db['name'],$return);
		file_put_contents($mysqldump,"-- ORICHALCUM ".SW_VERSION." (".date("d-m-Y H:i").")\n\n".implode("\n",$return));

		// collect all files and zip
		class kZipper extends ZipArchive {
			public function addDir($path) {
				$this->addEmptyDir($path);
				$nodes=glob($path.'/*');
				foreach($nodes as $node) {
					if(is_dir($node)) {
						if(basename(rtrim($node,"/"))!="bkups") $this->addDir($node);
						}
					elseif(is_file($node)) {
						$this->addFile($node,preg_replace("/^".preg_quote(rtrim($_SERVER['DOCUMENT_ROOT'],"/").BASEDIR,"/")."/","",$node));
						}
					}
				}
			}
		$zip=new kZipper();
		$filename="./bkups/".date("YmdHi").".zip";
		if($zip->open($filename,ZipArchive::CREATE)!==TRUE) {
			exit("cannot open <$filename>\n");
			}
		$zip->addDir($_SERVER['DOCUMENT_ROOT'].BASEDIR.'arch');
		$zip->addDir($_SERVER['DOCUMENT_ROOT'].BASEDIR.'inc');
		$zip->addDir($_SERVER['DOCUMENT_ROOT'].BASEDIR.'admin');
		$zip->addDir($_SERVER['DOCUMENT_ROOT'].BASEDIR.'images');
		$zip->addDir($_SERVER['DOCUMENT_ROOT'].BASEDIR.'template');
		$zip->addFile($_SERVER['DOCUMENT_ROOT'].BASEDIR.'index.php','index.php');
		$zip->addFile($_SERVER['DOCUMENT_ROOT'].BASEDIR.'shortURL.php','shortURL.php');
		$zip->addFile($_SERVER['DOCUMENT_ROOT'].BASEDIR.'robots.txt','robots.txt');
		$zip->addFile($_SERVER['DOCUMENT_ROOT'].BASEDIR.'.htaccess','.htaccess');
		$zip->addFile($mysqldump,basename($mysqldump));
		$zip->close();

		unlink($mysqldump);

		$success="Your backup file is ready: please download it and keep it in a safe place";
		}
	else {
		$alert="ZipArchive class is not available on this server";
		}
	}

if(isset($success)) echo '<div id="MsgSuccess">'.$success.'</div>';
elseif(isset($alert)) echo '<div id="MsgAlert">'.$alert.'</div>';
/* FINE AZIONI */

?>
<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
<br />

<table>
<tr><td>
	<a href="?backupnow" class="button"><?= $kaTranslate->translate('Maintenance:Backup your website now!'); ?></a><br />
	</td>
	<td>
	<small><?= $kaTranslate->translate('Maintenance:This operation could require some time!'); ?><br />
	<strong><?= $kaTranslate->translate('Maintenance:When done, please download the file, store it in a safe place and delete it from server!'); ?></strong></small>
	</td>
	</tr>
</table>

<br />

<table class="tabella">
	<tr>
		<th><?= $kaTranslate->translate('Maintenance:File name'); ?></th>
		<th><?= $kaTranslate->translate('Maintenance:Date'); ?></th>
		<th><?= $kaTranslate->translate('Maintenance:Size'); ?></th>
		<th>&nbsp;</th>
		</tr>
	<?
	foreach(glob("bkups/*") as $file) {
		if(trim($file,".")!="") { ?>
			<tr>
				<td><?= basename($file); ?></td>
				<td class="percorso"><strong><?= date("d-m-Y H:i",filectime($file)); ?></strong></td>
				<td><?= number_format(filesize($file)/1024/1024,2); ?>Mb</td>
				<td><a href="<?= $file; ?>" class="smallbutton"><?= $kaTranslate->translate('Maintenance:Download'); ?></a>
					<a href="?delete=<?= urlencode(basename($file)); ?>" class="smallalertbutton" onclick="return confirm('<?= $kaTranslate->translate('Maintenance:Do you really want to delete this backup file?'); ?>');"><?= $kaTranslate->translate('Maintenance:Delete'); ?></a></td>
				</tr>
			<? }
		}
	?>
	</table>


<?
include_once("../inc/foot.inc.php");
?>
