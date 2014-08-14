<?
/* INITIAL CHECKS */
$targz='orichalcum260u.tar.gz';
if(!file_exists('tmp/'.$targz)) die('Some files are missing, or you don\'t have write permissions on this directory');
echo 'Upgrading to Orichalcum 2.60...<br />';

function kRmDir($dir) {
	if(is_dir($dir)&&!is_link($dir)) {
		foreach(glob($dir.'/*') as $sf) {
			if(!kRmDir($sf)) return false;
			}
		return rmdir($dir);
		}
	else {
		return unlink($dir);
		}
	}

function kiTarExtract($file,$dest) {
	$dest=trim($dest,'/').'/';
	if(!isset($dest)) return false;

	$tar=array();
	$tar['size']=filesize($file);
	$tar['data']=file_get_contents($file);

	$offset=0;
	for($i=0;$offset<$tar['size'];$i++) {
		$file=array();
		$file['name']=trim(substr($tar['data'],$offset,100));
		if(substr($file['name'],-1)=="/") { //dir
			$file['size']=0;
			if(!file_exists($dest.$file['name'])) mkdir($dest.$file['name']);
			}
		else { //file
			$file['size']=OctDec(trim(substr($tar['data'],($offset+124),12)));
			$file['data']=substr($tar['data'],($offset+512),$file['size']);
			//UTF8+Unix conversion dei file di testo
			$ext=substr($file['name'],-3);
			if($ext=="txt"|$ext=="php"|$ext=="html"|$ext=="xml") {
				$file['data']=str_replace("\r","",$file['data']);
				$file['data']=utf8_decode(utf8_encode($file['data']));
				}
			file_put_contents($dest.$file['name'],$file['data']);
			}
		$offset+=512+$file['size'];
		while(substr($tar['data'],$offset,1)==chr(0)) {
			$offset++;
			}
		}
	}
function kiTgzExtract($file,$dest) {
	$tmpname='tmp'.date("YmdHis").'.tar';
	copy($file,$dest.'/'.$tmpname.'.gz');
	file_put_contents($dest.'/'.$tmpname,kGzDecode(file_get_contents($dest.'/'.$tmpname.'.gz')));
	unlink($dest.'/'.$tmpname.'.gz');
	kiTarExtract($dest.'/'.$tmpname,$dest);
	unlink($dest.'/'.$tmpname);
	}


/**
 * Copy file or folder from source to destination, it can do
 * recursive copy as well and is very smart
 * It recursively creates the dest file or directory path if there weren't exists
 * Situtaions :
 * - Src:/home/test/file.txt ,Dst:/home/test/b ,Result:/home/test/b -> If source was file copy file.txt name with b as name to destination
 * - Src:/home/test/file.txt ,Dst:/home/test/b/ ,Result:/home/test/b/file.txt -> If source was file Creates b directory if does not exsits and copy file.txt into it
 * - Src:/home/test ,Dst:/home/ ,Result:/home/test/** -> If source was directory copy test directory and all of its content into dest     
 * - Src:/home/test/ ,Dst:/home/ ,Result:/home/**-> if source was direcotry copy its content to dest
 * - Src:/home/test ,Dst:/home/test2 ,Result:/home/test2/** -> if source was directoy copy it and its content to dest with test2 as name
 * - Src:/home/test/ ,Dst:/home/test2 ,Result:->/home/test2/** if source was directoy copy it and its content to dest with test2 as name
 * @todo
 *     - Should have rollback technique so it can undo the copy when it wasn't successful
 *  - Auto destination technique should be possible to turn off
 *  - Supporting callback function
 *  - May prevent some issues on shared enviroments : http://us3.php.net/umask
 * @param $source //file or folder
 * @param $dest ///file or folder
 * @param $options //folderPermission,filePermission
 * @return boolean
 */
function smartCopy($source, $dest, $options=array('folderPermission'=>0755,'filePermission'=>0755))
{
	$result=false;
   
	if (is_file($source)) {
		if ($dest[strlen($dest)-1]=='/') {
			if (!file_exists($dest)) {
				cmfcDirectory::makeAll($dest,$options['folderPermission'],true);
			}
			$__dest=$dest."/".basename($source);
		} else {
			$__dest=$dest;
		}
		$result=copy($source, $__dest);
		chmod($__dest,$options['filePermission']);
	   
	} elseif(is_dir($source)) {
		if ($dest[strlen($dest)-1]=='/') {
			if ($source[strlen($source)-1]=='/') {
				//Copy only contents
			} else {
				//Change parent itself and its contents
				$dest=$dest.basename($source);
				if(!file_exists($dest)) mkdir($dest);
				chmod($dest,$options['filePermission']);
			}
		} else {
			if ($source[strlen($source)-1]=='/') {
				//Copy parent directory with new name and all its content
				if(!file_exists($dest)) mkdir($dest,$options['folderPermission']);
				chmod($dest,$options['filePermission']);
			} else {
				//Copy parent directory with new name and all its content
				if(!file_exists($dest)) mkdir($dest,$options['folderPermission']);
				chmod($dest,$options['filePermission']);
			}
		}

		$dirHandle=opendir($source);
		while($file=readdir($dirHandle))
		{
			if($file!="." && $file!="..")
			{
				 if(!is_dir($source."/".$file)) {
					$__dest=$dest."/".$file;
				} else {
					$__dest=$dest."/".$file;
				}
				//echo "$source/$file ||| $__dest<br />";
				$result=smartCopy($source."/".$file, $__dest, $options);
			}
		}
		closedir($dirHandle);
	   
	} else {
		$result=false;
	}
	return $result;
} 



/* SET THE NEW ORICHALCUM */
kiTgzExtract('tmp/'.$targz,'tmp/');
smartCopy('tmp/admin',BASERELDIR.'admin');
smartCopy('tmp/inc',BASERELDIR.'inc');
unlink(BASERELDIR.'index.php');
unlink(BASERELDIR.'shortURL.php');
copy('tmp/index.php',BASERELDIR.'index.php');
copy('tmp/shortURL.php',BASERELDIR.'shortURL.php');
unlink('tmp/'.$targz);
echo 'All files was successfully updated.<br />';


/* DELETE OLD FILES */
if(file_exists('../banner/ordine.php')) unlink('../banner/ordine.php');
if(file_exists('../inc/landingpage.inc.php')) unlink('../inc/landingpage.inc.php');


/* UPDATE DB */
$query=array();
$query[]="ALTER TABLE `k_news` ADD `starting_date` DATETIME NOT NULL AFTER `pubblica`;";
$query[]="UPDATE `k_news` SET `starting_date`=`scadenza`;";
$query[]="ALTER TABLE `k_pagine` ADD `featuredimage` INT( 6 ) NOT NULL AFTER `allowconversions` ;";
$query[]="ALTER TABLE `k_news` ADD `featuredimage` INT( 6 ) NOT NULL AFTER `allowcomments` ;";
$query[]="ALTER TABLE `k_shop_items` ADD `featuredimage` INT( 6 ) NOT NULL AFTER `testo` ;";
$query[]="ALTER TABLE `k_shop_items` ADD `manufacturer` INT( 6 ) NOT NULL AFTER `customfields` ;";
$query[]="ALTER TABLE `k_immagini` DROP `tabella` , DROP `id`, DROP `ordine` ;";
$query[]="ALTER TABLE `k_immagini` ADD `creation_date` DATETIME NOT NULL ;";
$query[]="CREATE TABLE IF NOT EXISTS `k_shop_manufacturers` (`idsman` int(4) NOT NULL AUTO_INCREMENT, `dir` varchar(255) NOT NULL, `name` varchar(255) NOT NULL, `subtitle` varchar(255) NOT NULL, `preview` text NOT NULL, `description` text NOT NULL, `featuredimage` int(6) NOT NULL, `created` datetime NOT NULL, `modified` datetime NOT NULL, `translations` text NOT NULL, `ll` varchar(2) NOT NULL, PRIMARY KEY (`idsman`)) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
$query[]="ALTER TABLE `k_menu` ADD `photogallery` VARCHAR(255) NOT NULL DEFAULT ',' AFTER `ref`;";
$query[]="ALTER TABLE `k_email_log` ADD `idarch` INT( 6 ) NOT NULL DEFAULT '0' AFTER `date` ;";
foreach($query as $q) {
	if(!mysql_query($q)) echo 'An error occurred while upgrading database: '.$q.'<br>';
}

	
/* CHANGE CONFIG */
$cnfg=file_get_contents('../inc/config.inc.php');

//add tables
$cnfg=str_replace('define("TABLE_SHOP_COUPONS_CODES","k_shop_coupons_codes");','define("TABLE_SHOP_COUPONS_CODES","k_shop_coupons_codes");
define("TABLE_SHOP_MANUFACTURERS","k_shop_manufacturers");',$cnfg);

//update version number
$cnfg=str_replace('"SW_VERSION","2.51"','"SW_VERSION","2.60"',$cnfg);

//save changes
if(!file_put_contents('../inc/config.inc.php',$cnfg)) echo "<strong>Errors occurred during the update of config.inc.php!</strong><br />";
echo 'Config file was successfully updated.<br />';

echo '<br /><strong>DONE! Welcome to Orichalcum 2.60!</strong><br />';

unlink("tmp/install.php");
?>