<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Maintenance:Backup");
include_once("../inc/head.inc.php");

if(!file_exists('bkups')) mkdir('bkups');

/* AZIONI */
if(isset($_GET['delete']))
{
	$file=$_GET['delete'];
	$file=str_replace(array("/"," "),"",$file);
	if(file_exists('bkups/'.$file))
	{
		unlink('bkups/'.$file);
		$success="File successfully deleted";
	} else $alert="The filename is not valid: impossible to delete ".$file;
}

elseif(isset($_GET['backupnow']))
{
	$format="gz";
	if(class_exists("ZipArchive")) $format="zip";
	
	// create zip file
	if($format=="zip")
	{

		// collect all files and zip
		class kZipper extends ZipArchive {
			public function addDir($path) {
				$this->addEmptyDir($path);
				$nodes=glob($path.'/*');
				if(!empty($nodes))
				{
					foreach($nodes as $node) {
						if(is_dir($node)) {
							if(basename(rtrim($node,"/"))!="bkups") $this->addDir($node);
						} elseif(is_file($node)) {
							$this->addFile($node,preg_replace("/^".preg_quote(rtrim($_SERVER['DOCUMENT_ROOT'],"/").BASEDIR,"/")."/","",$node));
						}
					}
				}
			}
		}
		$zip=new kZipper();
		$filename="./bkups/".date("YmdHi").".zip";
		$basedir=rtrim($_SERVER['DOCUMENT_ROOT'],"/").BASEDIR;
		if($zip->open($filename,ZipArchive::CREATE)!==TRUE) exit("cannot open <$filename>\n");

		// backup file archive
		if(isset($_GET['archive']))
		{
			$zip->addDir($basedir.'arch');
		}

		// backup orichalcum
		if(isset($_GET['cms']))
		{
			$zip->addDir($basedir.'inc');
			$zip->addDir($basedir.'admin');
			$zip->addDir($basedir.'img');
			$zip->addDir($basedir.'template');
			$zip->addFile($basedir.'index.php','index.php');
			$zip->addFile($basedir.'shortURL.php','shortURL.php');
			$zip->addFile($basedir.'robots.txt','robots.txt');
			$zip->addFile($basedir.'.htaccess','.htaccess');
		}

		// backup database
		if(isset($_GET['mysql']))
		{
			$mysqldump=$basedir.'admin/maintenance/bkups/orichalcum.sql';

			// export database
			exec('mysqldump --user='.$__db['user'].' --password='.$__db['password'].' --host='.$__db['host'].' --extended-insert=false '.$__db['name'],$return);
			file_put_contents($mysqldump,"-- ORICHALCUM ".SW_VERSION." (".date("d-m-Y H:i").")\n\n".implode("\n",$return));

			$zip->addFile($mysqldump,basename($mysqldump));
		}

		$zip->close();

		if(!empty($mysqldump)) unlink($mysqldump);

		$success="Your backup file is ready: please download it and keep it in a safe place";
	
	// create gz file
	} elseif($format=="gz")	{

		$gz=new kTarGz();
		$filename="./bkups/".date("YmdHi").".tar.gz";
		if($gz->tarOpen($filename)==false) exit("cannot open <$filename>\n");
		$basedir=rtrim($_SERVER['DOCUMENT_ROOT'],"/").BASEDIR;
		$gz->tarSetBaseDir($basedir);
		$gz->tarExclude($basedir.'admin/maintenance/bkups');

		// backup file archive
		if(isset($_GET['archive']))
		{
			$gz->tarRecursiveAdd($basedir.'arch');
		}

		// backup orichalcum
		if(isset($_GET['cms']))
		{
			$gz->tarRecursiveAdd($basedir.'inc');
			$gz->tarRecursiveAdd($basedir.'admin');
			$gz->tarRecursiveAdd($basedir.'img');
			$gz->tarRecursiveAdd($basedir.'template');
			$gz->tarRecursiveAdd($basedir.'index.php');
			$gz->tarRecursiveAdd($basedir.'shortURL.php');
			$gz->tarRecursiveAdd($basedir.'robots.txt');
			$gz->tarRecursiveAdd($basedir.'.htaccess');
		}

		// backup database
		if(isset($_GET['mysql']))
		{
			$mysqldump=$basedir.'admin/maintenance/bkups/orichalcum.sql';

			// export database
			exec('mysqldump --user='.$__db['user'].' --password='.$__db['password'].' --host='.$__db['host'].' --extended-insert=false '.$__db['name'],$return);
			file_put_contents($mysqldump,"-- ORICHALCUM ".SW_VERSION." (".date("d-m-Y H:i").")\n\n".implode("\n",$return));

			rename($mysqldump,$basedir.'orichalcum.sql');
			$gz->tarRecursiveAdd($basedir.'orichalcum.sql');
		}

		$gz->tarClose();

		if(file_exists($basedir.'orichalcum.sql')) unlink($basedir.'orichalcum.sql');

		$success="Your backup file is ready: please download it and keep it in a safe place";
	
	}

}

if(isset($success)) echo '<div id="MsgSuccess">'.$success.'</div>';
elseif(isset($alert)) echo '<div id="MsgAlert">'.$alert.'</div>';
/* FINE AZIONI */

?>
<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
<br />

<form action="" method="get">
	<table>
	<tr><td>
			<?= b3_create_input("archive","checkbox",$kaTranslate->translate('Maintenance:Backup media archive'),"1","","","checked"); ?><br>
			<?= b3_create_input("mysql","checkbox",$kaTranslate->translate('Maintenance:Backup database'),"1","","","checked"); ?><br>
			<?= b3_create_input("cms","checkbox",$kaTranslate->translate('Maintenance:Backup Orichalcum'),"1","","","checked"); ?><br>
		</td><td></td></tr>
	<tr>
		<td>
			<input type="submit" name="backupnow" class="button" value="<?= addslashes($kaTranslate->translate('Maintenance:Backup your website now!')); ?>">
		</td>
		<td>
		<small><?= $kaTranslate->translate('Maintenance:This operation could require some time!'); ?><br />
		<strong><?= $kaTranslate->translate('Maintenance:When done, please download the file, store it in a safe place and delete it from server!'); ?></strong></small>
		</td>
		</tr>
	</table>
</form>

<br />

<table class="tabella">
	<tr>
		<th><?= $kaTranslate->translate('Maintenance:File name'); ?></th>
		<th><?= $kaTranslate->translate('Maintenance:Date'); ?></th>
		<th><?= $kaTranslate->translate('Maintenance:Size'); ?></th>
		<th>&nbsp;</th>
		</tr>
	<?php 
	if(glob("bkups/*")) {
		foreach(glob("bkups/*") as $file) {
			if(trim($file,".")!="") { ?>
				<tr>
					<td><?= basename($file); ?></td>
					<td class="percorso"><strong><?= date("d-m-Y H:i",filectime($file)); ?></strong></td>
					<td><?= number_format(filesize($file)/1024/1024,2); ?>Mb</td>
					<td><a href="<?= $file; ?>" class="smallbutton"><?= $kaTranslate->translate('Maintenance:Download'); ?></a>
						<a href="?delete=<?= urlencode(basename($file)); ?>" class="smallalertbutton" onclick="return confirm('<?= $kaTranslate->translate('Maintenance:Do you really want to delete this backup file?'); ?>');"><?= $kaTranslate->translate('Maintenance:Delete'); ?></a></td>
					</tr>
			<?php  }
		}
	}
	?>
	</table>


<?php 
include_once("../inc/foot.inc.php");
