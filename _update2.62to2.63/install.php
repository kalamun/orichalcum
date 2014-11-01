<?
/* INITIAL CHECKS */
$targz='upgrade.tar.gz';
if(!file_exists('tmp/'.$targz)) die('Some files are missing, or you don\'t have write permissions on this directory');
echo 'Upgrading to Orichalcum 2.63...<br />';

error_reporting(E_ALL);

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
		@chmod($__dest,$options['filePermission']);
	   
	} elseif(is_dir($source)) {
		if ($dest[strlen($dest)-1]=='/') {
			if ($source[strlen($source)-1]=='/') {
				//Copy only contents
			} else {
				//Change parent itself and its contents
				$dest=$dest.basename($source);
				if(!file_exists($dest)) mkdir($dest);
				@chmod($dest,$options['filePermission']);
			}
		} else {
			if ($source[strlen($source)-1]=='/') {
				//Copy parent directory with new name and all its content
				if(!file_exists($dest)) mkdir($dest,$options['folderPermission']);
				@chmod($dest,$options['filePermission']);
			} else {
				//Copy parent directory with new name and all its content
				if(!file_exists($dest)) mkdir($dest,$options['folderPermission']);
				@chmod($dest,$options['filePermission']);
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



/* COPY FILES */
kiTgzExtract('tmp/'.$targz,'tmp/');
smartCopy('tmp/admin',BASERELDIR.'admin');
smartCopy('tmp/inc',BASERELDIR.'inc');
unlink('tmp/'.$targz);
echo 'All files was successfully updated.<br />';


/* UPDATE DATABASE */
mysql_query("ALTER TABLE `".TABLE_NEWS."` ADD UNIQUE ( `dir` ) ");
mysql_query("ALTER TABLE `".TABLE_SHOP_ITEMS."` ADD UNIQUE ( `dir` ) ");
mysql_query("ALTER TABLE `".TABLE_MEMBERS."` ADD UNIQUE ( `username` ) ");
mysql_query("ALTER TABLE `".TABLE_METADATA."` ADD INDEX ( `tabella` , `id` ) ");
mysql_query("ALTER TABLE `".TABLE_COMMENTI."` ADD INDEX ( `tabella` , `ip` ) ");
mysql_query("ALTER TABLE `".TABLE_PAGINE."` ADD `photogallery` TEXT NOT NULL AFTER `testo` ");
mysql_query("ALTER TABLE `".TABLE_CATEGORIE."` ADD `photogallery` TEXT NOT NULL AFTER `categoria` ");
mysql_query("ALTER TABLE `".TABLE_NEWS."` ADD `photogallery` TEXT NOT NULL AFTER `featuredimage` ");
mysql_query("ALTER TABLE `".TABLE_PHOTOGALLERY."` ADD `featuredimage` INT( 6 ) NOT NULL AFTER `dir` ");
mysql_query("ALTER TABLE `".TABLE_PHOTOGALLERY."` ADD `photogallery` TEXT NOT NULL AFTER `featuredimage` ");
mysql_query("ALTER TABLE `".TABLE_PHOTOGALLERY."` ADD `categories` VARCHAR( 255 ) NOT NULL AFTER `photogallery` ");
mysql_query("ALTER TABLE `".TABLE_PHOTOGALLERY."` ADD `modified` DATETIME NOT NULL AFTER `data` ");
mysql_query("ALTER TABLE `".TABLE_SHOP_ITEMS."` ADD `photogallery` TEXT NOT NULL AFTER `featuredimage` ");
mysql_query("ALTER TABLE `".TABLE_SHOP_MANUFACTURERS."` ADD `photogallery` TEXT NOT NULL AFTER `featuredimage` ");
mysql_query("ALTER TABLE `".TABLE_USERS."` ADD `featuredimage` INT( 6 ) NOT NULL AFTER `email` ");


/* MIGRATE CONTENTS FROM OLD PHOTOGALLERIES TO NEW ONE */
// pages
$query="SELECT `idpag`,`titolo` FROM ".TABLE_PAGINE;
$results=mysql_query($query);
while($row=mysql_fetch_array($results))
{
	$photogallery=",";
	$c=0;
	$q="SELECT `idimg` FROM ".TABLE_IMGALLERY." WHERE `tabella`='".TABLE_PAGINE."' AND `id`='".$row['idpag']."' ORDER BY `ordine`";
	$rs=mysql_query($q);
	while($r=mysql_fetch_array($rs))
	{
		$photogallery.=$r['idimg'].",";
		$c++;
	}
	mysql_query("UPDATE ".TABLE_PAGINE." SET `photogallery`='".$photogallery."' WHERE `idpag`='".$row['idpag']."' LIMIT 1");
}

// news
$query="SELECT `idnews`,`titolo` FROM ".TABLE_NEWS;
$results=mysql_query($query);
while($row=mysql_fetch_array($results))
{
	$photogallery=",";
	$c=0;
	$q="SELECT `idimg` FROM ".TABLE_IMGALLERY." WHERE `tabella`='".TABLE_NEWS."' AND `id`='".$row['idnews']."' ORDER BY `ordine`";
	$rs=mysql_query($q);
	while($r=mysql_fetch_array($rs))
	{
		$photogallery.=$r['idimg'].",";
		$c++;
	}
	mysql_query("UPDATE ".TABLE_PAGINE." SET `photogallery`='".$photogallery."' WHERE `idnews`='".$row['idnews']."' LIMIT 1");
}

// shop
$query="SELECT `idsitem`,`titolo` FROM ".TABLE_SHOP_ITEMS;
$results=mysql_query($query);
while($row=mysql_fetch_array($results))
{
	$photogallery=",";
	$c=0;
	$q="SELECT `idimg` FROM ".TABLE_IMGALLERY." WHERE `tabella`='".TABLE_SHOP_ITEMS."' AND `id`='".$row['idsitem']."' ORDER BY `ordine`";
	$rs=mysql_query($q);
	while($r=mysql_fetch_array($rs))
	{
		$photogallery.=$r['idimg'].",";
		$c++;
	}
	mysql_query("UPDATE ".TABLE_SHOP_ITEMS." SET `photogallery`='".$photogallery."' WHERE `idsitem`='".$row['idsitem']."' LIMIT 1");
}

// manufacturers
$query="SELECT `idsman`,`name` FROM ".TABLE_SHOP_MANUFACTURERS;
$results=mysql_query($query);
while($row=mysql_fetch_array($results))
{
	$photogallery=",";
	$c=0;
	$q="SELECT `idimg` FROM ".TABLE_IMGALLERY." WHERE `tabella`='".TABLE_SHOP_MANUFACTURERS."' AND `id`='".$row['idsman']."' ORDER BY `ordine`";
	$rs=mysql_query($q);
	while($r=mysql_fetch_array($rs))
	{
		$photogallery.=$r['idimg'].",";
		$c++;
	}
	mysql_query("UPDATE ".TABLE_SHOP_MANUFACTURERS." SET `photogallery`='".$photogallery."' WHERE `idsman`='".$row['idsman']."' LIMIT 1");
}

// photogalleries
$query="SELECT `idphg`,`titolo` FROM ".TABLE_PHOTOGALLERY;
$results=mysql_query($query);
while($row=mysql_fetch_array($results))
{
	$photogallery=",";
	$c=0;
	$q="SELECT `idimg` FROM ".TABLE_IMGALLERY." WHERE `tabella`='".TABLE_PHOTOGALLERY."' AND `id`='".$row['idphg']."' ORDER BY `ordine`";
	$rs=mysql_query($q);
	while($r=mysql_fetch_array($rs))
	{
		$photogallery.=$r['idimg'].",";
		$c++;
	}
	mysql_query("UPDATE ".TABLE_PHOTOGALLERY." SET `photogallery`='".$photogallery."' WHERE `idphg`='".$row['idphg']."' LIMIT 1");
}

// menu
$query="SELECT `idmenu`,`label` FROM ".TABLE_MENU;
$results=mysql_query($query);
while($row=mysql_fetch_array($results))
{
	$photogallery=",";
	$c=0;
	$q="SELECT `idimg` FROM ".TABLE_IMGALLERY." WHERE `tabella`='".TABLE_MENU."' AND `id`='".$row['idmenu']."' ORDER BY `ordine`";
	$rs=mysql_query($q);
	while($r=mysql_fetch_array($rs))
	{
		$photogallery.=$r['idimg'].",";
		$c++;
	}
	mysql_query("UPDATE ".TABLE_MENU." SET `photogallery`='".$photogallery."' WHERE `idmenu`='".$row['idmenu']."' LIMIT 1");
}

// categories
$query="SELECT `idcat`,`categoria` FROM ".TABLE_CATEGORIE;
$results=mysql_query($query);
while($row=mysql_fetch_array($results))
{
	$photogallery=",";
	$c=0;
	$q="SELECT `idimg` FROM ".TABLE_IMGALLERY." WHERE `tabella`='".TABLE_CATEGORIE."' AND `id`='".$row['idcat']."' ORDER BY `ordine`";
	$rs=mysql_query($q);
	while($r=mysql_fetch_array($rs))
	{
		$photogallery.=$r['idimg'].",";
		$c++;
	}
	mysql_query("UPDATE ".TABLE_CATEGORIE." SET `photogallery`='".$photogallery."' WHERE `idcat`='".$row['idcat']."' LIMIT 1");
}

mysql_query("DELETE FROM ".TABLE_IMGALLERY." WHERE `tabella`='".TABLE_PAGINE."'");
mysql_query("DELETE FROM ".TABLE_IMGALLERY." WHERE `tabella`='".TABLE_NEWS."'");
mysql_query("DELETE FROM ".TABLE_IMGALLERY." WHERE `tabella`='".TABLE_SHOP_ITEMS."'");
mysql_query("DELETE FROM ".TABLE_IMGALLERY." WHERE `tabella`='".TABLE_SHOP_MANUFACTURERS."'");
mysql_query("DELETE FROM ".TABLE_IMGALLERY." WHERE `tabella`='".TABLE_PHOTOGALLERY."'");
mysql_query("DELETE FROM ".TABLE_IMGALLERY." WHERE `tabella`='".TABLE_MENU."'");
mysql_query("DELETE FROM ".TABLE_IMGALLERY." WHERE `tabella`='".TABLE_CATEGORIE."'");




/* CHANGE CONFIG */
$cnfg=file_get_contents('../inc/config.inc.php');

//update version number
$cnfg=str_replace('"SW_VERSION","2.62"','"SW_VERSION","2.63"',$cnfg);

//save changes
if(!file_put_contents('../inc/config.inc.php',$cnfg)) echo "<strong>Errors occurred during the update of config.inc.php!</strong><br />";
echo 'Config file was successfully updated.<br />';

echo '<br /><h3>DONE! Orichalcum 2.63 is ready to serve.</h3>';

unlink("tmp/install.php");
?>