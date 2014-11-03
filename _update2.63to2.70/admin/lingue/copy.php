<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

session_start();
define("PAGE_NAME","Languages:Copy content");
define("PAGE_LEVEL",1);
include_once("../inc/head.inc.php");




/* AZIONI */
if(isset($_POST['copy'])&&isset($_POST['sourceLang'])&&isset($_POST['destinationLang'])) {
	$log="";
	if($_POST['sourceLang']==$_POST['destinationLang']) $log="Source and destination languages can't be the same";
	
	if($log=="") {
		/* IMPOSTAZIONI */
		if(isset($_POST['copyConfig'])) {
			$query="SELECT * FROM ".TABLE_CONFIG." WHERE ll='".$_POST['sourceLang']."'";
			$results=mysql_query($query);
			while($row=mysql_fetch_array($results)) {
				$q="SELECT * FROM ".TABLE_CONFIG." WHERE ll='".$_POST['destinationLang']."' AND param='".$row['param']."' LIMIT 1";
				$rs=mysql_query($q);
				$r=mysql_fetch_array($rs);
				if($r==false) {
					$q="INSERT INTO ".TABLE_CONFIG." (param,value1,value2,ll) VALUES('".$row['param']."','".addslashes($row['value1'])."','".addslashes($row['value2'])."','".$_POST['destinationLang']."')";
					mysql_query($q);
					}
				elseif(isset($_POST['overwrite'])) {
					$q="UPDATE ".TABLE_CONFIG." SET value1='".addslashes($row['value1'])."',value2='".addslashes($row['value2'])."' WHERE idconf='".$r['idconf']."'";
					mysql_query($q);
					}
				}
			}

		/* MENU */
		if(isset($_POST['copyMenu'])) {
			if(isset($_POST['overwrite'])) {
				$q="DELETE FROM ".TABLE_MENU." WHERE ll='".$_POST['destinationLang']."'";
				mysql_query($q);

				$query="SELECT idmenu FROM ".TABLE_MENU." ORDER BY idmenu DESC LIMIT 1";
				$results=mysql_query($query);
				$row=mysql_fetch_array($results);
				$offset=$row['idmenu']+1;

				$query="SELECT * FROM ".TABLE_MENU." WHERE ll='".$_POST['sourceLang']."' ORDER BY idmenu";
				$results=mysql_query($query);
				for($i=0;$row=mysql_fetch_array($results);$i++) {
					if($i=0) $offset-=$row['idmenu'];
					$row['ref']==0?$ref=0:$ref=$row['ref']+$offset;
					$q="INSERT INTO ".TABLE_MENU." (`idmenu`,`collection`,`label`,`url`,`ref`,`ll`,`ordine`) VALUES('".($row['idmenu']+$offset)."','".addslashes($row['collection'])."','".addslashes($row['label'])."','".addslashes(str_replace('/'.strtolower($_POST['sourceLang']).'/','/'.strtolower($_POST['destinationLang']).'/',$row['url']))."','".$ref."','".$_POST['destinationLang']."','".$row['ordine']."')";
					mysql_query($q);
					}
				}
			}

		/* PAGINE */
		if(isset($_POST['copyPages'])) {
			//categorie
			$catMap=array();
			$q="SELECT * FROM ".TABLE_CATEGORIE." WHERE tabella='".TABLE_PAGINE."' AND ll='".$_POST['sourceLang']."'";
			$rs=mysql_query($q);
			while($r=mysql_fetch_array($rs)) {
				$q2="SELECT * FROM ".TABLE_CATEGORIE." WHERE tabella='".TABLE_PAGINE."' AND ll='".$_POST['destinationLang']."' AND dir='".$r['dir']."' LIMIT 1";
				$rs2=mysql_query($q2);
				$r2=mysql_fetch_array($rs2);
				if($r2==false) {
					$q="INSERT INTO ".TABLE_CATEGORIE." (tabella,categoria,dir,ordine,ll) VALUES('".TABLE_PAGINE."','".mysql_real_escape_string($r['categoria'])."','".mysql_real_escape_string($r['dir'])."','".$r['ordine']."','".$_POST['destinationLang']."')";
					if(mysql_query($q)) $catMap[$r['idcat']]=mysql_insert_id();
					}
				else $catMap[$r['idcat']]=$r2['idcat'];
				}
			
			$query="SELECT * FROM ".TABLE_PAGINE." WHERE ll='".$_POST['sourceLang']."'";
			$results=mysql_query($query);
			while($row=mysql_fetch_array($results)) {
				$row['dir']==str_replace('/'.strtolower($_POST['sourceLang']).'/','/'.strtolower($_POST['destinationLang']).'/',$row['dir']);
				$q="SELECT * FROM ".TABLE_PAGINE." WHERE ll='".$_POST['destinationLang']."' AND dir='".$row['dir']."' LIMIT 1";
				$rs=mysql_query($q);
				$r=mysql_fetch_array($rs);
				if($r!=false&&isset($_POST['overwrite'])) {
					$q="DELETE FROM ".TABLE_PAGINE." WHERE idpag='".$r['idpag']."'";
					mysql_query($q);
					}
				if($r==false||isset($_POST['overwrite'])) {
					//pagina
					$idpag=$row['idpag'];
					if(isset($row['idpag'])) unset($row['idpag']);
					$row['ll']=$_POST['destinationLang'];
					foreach($catMap as $ka=>$v) {
						$row['categorie']=str_replace(",".$ka.",",",".$v.",",$row['categorie']);
						}
					$q="INSERT INTO ".TABLE_PAGINE." (";
					foreach($row as $ka=>$v) {
						if(!is_numeric($ka)) $q.=$ka.',';
						}
					$q=rtrim($q,",");
					$q.=') VALUES(';
					foreach($row as $ka=>$v) {
						if(!is_numeric($ka)) $q.="'".mysql_real_escape_string($v)."',";
						}
					$q=rtrim($q,",");
					$q.=')';
					mysql_query($q);
					$row['idpag']=mysql_insert_id();
					//immagini
					$q="SELECT * FROM ".TABLE_IMGALLERY." WHERE tabella='".TABLE_PAGINE."' AND id='".$idpag."'";
					$rs=mysql_query($q);
					while($r=mysql_fetch_array($rs)) {
						$q="INSERT INTO ".TABLE_IMGALLERY." (tabella,id,ordine,idimg) VALUES('".TABLE_PAGINE."','".$row['idpag']."','".$r['ordine']."','".$r['idimg']."')";
						mysql_query($q);
						}
					//documenti
					$q="SELECT * FROM ".TABLE_DOCGALLERY." WHERE tabella='".TABLE_PAGINE."' AND id='".$idpag."'";
					$rs=mysql_query($q);
					while($r=mysql_fetch_array($rs)) {
						$q="INSERT INTO ".TABLE_DOCGALLERY." (tabella,id,ordine,iddoc) VALUES('".TABLE_PAGINE."','".$row['idpag']."','".$r['ordine']."','".$r['iddoc']."')";
						mysql_query($q);
						}
					//metadata
					$q="SELECT * FROM ".TABLE_METADATA." WHERE tabella='".TABLE_PAGINE."' AND id='".$idpag."'";
					$rs=mysql_query($q);
					while($r=mysql_fetch_array($rs)) {
						$q="INSERT INTO ".TABLE_METADATA." (tabella,id,param,value) VALUES('".TABLE_PAGINE."','".$row['idpag']."','".mysql_real_escape_string($r['param'])."','".mysql_real_escape_string($r['value'])."')";
						mysql_query($q);
						}
					}
				}
			}

		/* NEWS */
		if(isset($_POST['copyNews'])) {
			//categorie
			$catMap=array();
			$q="SELECT * FROM ".TABLE_CATEGORIE." WHERE tabella='".TABLE_NEWS."' AND ll='".$_POST['sourceLang']."'";
			$rs=mysql_query($q);
			while($r=mysql_fetch_array($rs)) {
				$q2="SELECT * FROM ".TABLE_CATEGORIE." WHERE tabella='".TABLE_NEWS."' AND ll='".$_POST['destinationLang']."' AND dir='".$r['dir']."' LIMIT 1";
				$rs2=mysql_query($q2);
				$r2=mysql_fetch_array($rs2);
				if($r2==false) {
					$q="INSERT INTO ".TABLE_CATEGORIE." (tabella,categoria,dir,ordine,ll) VALUES('".TABLE_NEWS."','".mysql_real_escape_string($r['categoria'])."','".mysql_real_escape_string($r['dir'])."','".$r['ordine']."','".$_POST['destinationLang']."')";
					if(mysql_query($q)) $catMap[$r['idcat']]=mysql_insert_id();
					}
				else $catMap[$r['idcat']]=$r2['idcat'];
				}
			
			$query="SELECT * FROM ".TABLE_NEWS." WHERE ll='".$_POST['sourceLang']."'";
			$results=mysql_query($query);
			while($row=mysql_fetch_array($results)) {
				$row['dir']==str_replace('/'.strtolower($_POST['sourceLang']).'/','/'.strtolower($_POST['destinationLang']).'/',$row['dir']);
				$q="SELECT * FROM ".TABLE_NEWS." WHERE ll='".$_POST['destinationLang']."' AND dir='".$row['dir']."' LIMIT 1";
				$rs=mysql_query($q);
				$r=mysql_fetch_array($rs);
				if($r!=false&&isset($_POST['overwrite'])) {
					$q="DELETE FROM ".TABLE_NEWS." WHERE idnews='".$r['idnews']."'";
					mysql_query($q);
					}
				if($r==false||isset($_POST['overwrite'])) {
					//news
					$idnews=$row['idnews'];
					if(isset($row['idnews'])) unset($row['idnews']);
					$row['ll']=$_POST['destinationLang'];
					foreach($catMap as $ka=>$v) {
						$row['categorie']=str_replace(",".$ka.",",",".$v.",",$row['categorie']);
						}
					$q="INSERT INTO ".TABLE_NEWS." (";
					foreach($row as $ka=>$v) {
						if(!is_numeric($ka)) $q.=$ka.',';
						}
					$q=rtrim($q,",");
					$q.=') VALUES(';
					foreach($row as $ka=>$v) {
						if(!is_numeric($ka)) $q.="'".mysql_real_escape_string($v)."',";
						}
					$q=rtrim($q,",");
					$q.=')';
					mysql_query($q);
					$row['idnews']=mysql_insert_id();
					//immagini
					$q="SELECT * FROM ".TABLE_IMGALLERY." WHERE tabella='".TABLE_NEWS."' AND id='".$idnews."'";
					$rs=mysql_query($q);
					while($r=mysql_fetch_array($rs)) {
						$q="INSERT INTO ".TABLE_IMGALLERY." (tabella,id,ordine,idimg) VALUES('".TABLE_NEWS."','".$row['idnews']."','".$r['ordine']."','".$r['idimg']."')";
						mysql_query($q);
						}
					//documenti
					$q="SELECT * FROM ".TABLE_DOCGALLERY." WHERE tabella='".TABLE_NEWS."' AND id='".$idnews."'";
					$rs=mysql_query($q);
					while($r=mysql_fetch_array($rs)) {
						$q="INSERT INTO ".TABLE_DOCGALLERY." (tabella,id,ordine,iddoc) VALUES('".TABLE_NEWS."','".$row['idnews']."','".$r['ordine']."','".$r['iddoc']."')";
						mysql_query($q);
						}
					//metadata
					$q="SELECT * FROM ".TABLE_METADATA." WHERE tabella='".TABLE_NEWS."' AND id='".$idnews."'";
					$rs=mysql_query($q);
					while($r=mysql_fetch_array($rs)) {
						$q="INSERT INTO ".TABLE_METADATA." (tabella,id,param,value) VALUES('".TABLE_NEWS."','".$row['idnews']."','".mysql_real_escape_string($r['param'])."','".mysql_real_escape_string($r['value'])."')";
						mysql_query($q);
						}
					}
				}
			}

		/* SHOP */
		if(isset($_POST['copyShop'])) {
			//categorie
			$catMap=array();
			$q="SELECT * FROM ".TABLE_CATEGORIE." WHERE tabella='".TABLE_SHOP_ITEMS."' AND ll='".$_POST['sourceLang']."'";
			$rs=mysql_query($q);
			while($r=mysql_fetch_array($rs)) {
				$q2="SELECT * FROM ".TABLE_CATEGORIE." WHERE tabella='".TABLE_SHOP_ITEMS."' AND ll='".$_POST['destinationLang']."' AND dir='".$r['dir']."' LIMIT 1";
				$rs2=mysql_query($q2);
				$r2=mysql_fetch_array($rs2);
				if($r2==false) {
					$q="INSERT INTO ".TABLE_CATEGORIE." (tabella,categoria,dir,ordine,ll) VALUES('".TABLE_SHOP_ITEMS."','".mysql_real_escape_string($r['categoria'])."','".mysql_real_escape_string($r['dir'])."','".$r['ordine']."','".$_POST['destinationLang']."')";
					if(mysql_query($q)) $catMap[$r['idcat']]=mysql_insert_id();
					}
				else $catMap[$r['idcat']]=$r2['idcat'];
				}
			
			$query="SELECT * FROM ".TABLE_SHOP_ITEMS." WHERE ll='".$_POST['sourceLang']."'";
			$results=mysql_query($query);
			while($row=mysql_fetch_array($results)) {
				$row['dir']==str_replace('/'.strtolower($_POST['sourceLang']).'/','/'.strtolower($_POST['destinationLang']).'/',$row['dir']);
				$q="SELECT * FROM ".TABLE_SHOP_ITEMS." WHERE ll='".$_POST['destinationLang']."' AND dir='".$row['dir']."' LIMIT 1";
				$rs=mysql_query($q);
				$r=mysql_fetch_array($rs);
				if($r!=false&&isset($_POST['overwrite'])) {
					$q="DELETE FROM ".TABLE_SHOP_ITEMS." WHERE idsitem='".$r['idsitem']."'";
					mysql_query($q);
					}
				if($r==false||isset($_POST['overwrite'])) {
					//item
					$idsitem=$row['idsitem'];
					if(isset($row['idsitem'])) unset($row['idsitem']);
					$row['ll']=$_POST['destinationLang'];
					foreach($catMap as $ka=>$v) {
						$row['categorie']=str_replace(",".$ka.",",",".$v.",",$row['categorie']);
						}
					$q="INSERT INTO ".TABLE_SHOP_ITEMS." (";
					foreach($row as $ka=>$v) {
						if(!is_numeric($ka)) $q.=$ka.',';
						}
					$q=rtrim($q,",");
					$q.=') VALUES(';
					foreach($row as $ka=>$v) {
						if(!is_numeric($ka)) $q.="'".mysql_real_escape_string($v)."',";
						}
					$q=rtrim($q,",");
					$q.=')';
					mysql_query($q);
					$row['idsitem']=mysql_insert_id();
	
					//immagini
					$q="SELECT * FROM ".TABLE_IMGALLERY." WHERE tabella='".TABLE_SHOP_ITEMS."' AND id='".$idsitem."'";
					$rs=mysql_query($q);
					while($r=mysql_fetch_array($rs)) {
						$q="INSERT INTO ".TABLE_IMGALLERY." (tabella,id,ordine,idimg) VALUES('".TABLE_SHOP_ITEMS."','".$row['idsitem']."','".$r['ordine']."','".$r['idimg']."')";
						mysql_query($q);
						}
					//documenti
					$q="SELECT * FROM ".TABLE_DOCGALLERY." WHERE tabella='".TABLE_SHOP_ITEMS."' AND id='".$idsitem."'";
					$rs=mysql_query($q);
					while($r=mysql_fetch_array($rs)) {
						$q="INSERT INTO ".TABLE_DOCGALLERY." (tabella,id,ordine,iddoc) VALUES('".TABLE_SHOP_ITEMS."','".$row['idsitem']."','".$r['ordine']."','".$r['iddoc']."')";
						mysql_query($q);
						}
					//metadata
					$q="SELECT * FROM ".TABLE_METADATA." WHERE tabella='".TABLE_SHOP_ITEMS."' AND id='".$idsitem."'";
					$rs=mysql_query($q);
					while($r=mysql_fetch_array($rs)) {
						$q="INSERT INTO ".TABLE_METADATA." (tabella,id,param,value) VALUES('".TABLE_SHOP_ITEMS."','".$row['idsitem']."','".mysql_real_escape_string($r['param'])."','".mysql_real_escape_string($r['value'])."')";
						mysql_query($q);
						}
					}

				//custom fields
				$q="SELECT * FROM ".TABLE_SHOP_CUSTOMFIELDS."";
				$rs=mysql_query($q);
				while($r=mysql_fetch_array($rs)) {
					foreach($catMap as $fromcat=>$tocat) {
						if(strpos($r['categories'],','.$fromcat.',')!==false&&strpos($r['categories'],','.$tocat.',')===false) {
							$q="UPDATE ".TABLE_SHOP_CUSTOMFIELDS." SET `categories`=CONCAT(`categories`,'".$tocat.",') WHERE `idsfield`='".$r['idsfield']."' LIMIT 1";
							mysql_query($q);
							}
						}
					}

				}
			}

		/* PHOTOGALLERY */
		if(isset($_POST['copyPhotogallery'])) {
			$query="SELECT * FROM ".TABLE_PHOTOGALLERY." WHERE ll='".$_POST['sourceLang']."'";
			$results=mysql_query($query);
			while($row=mysql_fetch_array($results)) {
				$row['dir']==str_replace('/'.strtolower($_POST['sourceLang']).'/','/'.strtolower($_POST['destinationLang']).'/',$row['dir']);
				$q="SELECT * FROM ".TABLE_PHOTOGALLERY." WHERE ll='".$_POST['destinationLang']."' AND dir='".$row['dir']."' LIMIT 1";
				$rs=mysql_query($q);
				$r=mysql_fetch_array($rs);
				if($r!=false&&isset($_POST['overwrite'])) {
					$q="DELETE FROM ".TABLE_PHOTOGALLERY." WHERE idphg='".$r['idphg']."'";
					mysql_query($q);
					}
				if($r==false||isset($_POST['overwrite'])) {
					//gallery
					$idphg=$row['idphg'];
					if(isset($row['idphg'])) unset($row['idphg']);
					$row['ll']=$_POST['destinationLang'];
					$q="INSERT INTO ".TABLE_PHOTOGALLERY." (";
					foreach($row as $ka=>$v) {
						if(!is_numeric($ka)) $q.=$ka.',';
						}
					$q=rtrim($q,",");
					$q.=') VALUES(';
					foreach($row as $ka=>$v) {
						if(!is_numeric($ka)) $q.="'".mysql_real_escape_string($v)."',";
						}
					$q=rtrim($q,",");
					$q.=')';
					mysql_query($q);
					$row['idphg']=mysql_insert_id();
					//immagini
					$q="SELECT * FROM ".TABLE_IMGALLERY." WHERE tabella='".TABLE_PHOTOGALLERY."' AND id='".$idphg."'";
					$rs=mysql_query($q);
					while($r=mysql_fetch_array($rs)) {
						$q="INSERT INTO ".TABLE_IMGALLERY." (tabella,id,ordine,idimg) VALUES('".TABLE_PHOTOGALLERY."','".$row['idphg']."','".$r['ordine']."','".$r['idimg']."')";
						mysql_query($q);
						}
					//metadata
					$q="SELECT * FROM ".TABLE_METADATA." WHERE tabella='".TABLE_PHOTOGALLERY."' AND id='".$idphg."'";
					$rs=mysql_query($q);
					while($r=mysql_fetch_array($rs)) {
						$q="INSERT INTO ".TABLE_METADATA." (tabella,id,param,value) VALUES('".TABLE_PHOTOGALLERY."','".$row['idphg']."','".mysql_real_escape_string($r['param'])."','".mysql_real_escape_string($r['value'])."')";
						mysql_query($q);
						}
					}
				}
			}

		/* BANNER */
		if(isset($_POST['copyBanners'])) {
			//categorie
			$catMap=array();
			$q="SELECT * FROM ".TABLE_CATEGORIE." WHERE tabella='".TABLE_BANNER."' AND ll='".$_POST['sourceLang']."'";
			$rs=mysql_query($q);
			while($r=mysql_fetch_array($rs)) {
				$q2="SELECT * FROM ".TABLE_CATEGORIE." WHERE tabella='".TABLE_BANNER."' AND ll='".$_POST['destinationLang']."' AND dir='".$r['dir']."' LIMIT 1";
				$rs2=mysql_query($q2);
				$r2=mysql_fetch_array($rs2);
				if($r2==false) {
					$q="INSERT INTO ".TABLE_CATEGORIE." (tabella,categoria,dir,ordine,ll) VALUES('".TABLE_BANNER."','".mysql_real_escape_string($r['categoria'])."','".mysql_real_escape_string($r['dir'])."','".$r['ordine']."','".$_POST['destinationLang']."')";
					if(mysql_query($q)) $catMap[$r['idcat']]=mysql_insert_id();
					}
				else $catMap[$r['idcat']]=$r2['idcat'];
				}
			
			if(isset($_POST['overwrite'])) {
				$q="DELETE FROM ".TABLE_BANNER." WHERE ll='".$_POST['sourceLang']."'";
				mysql_query($q);
				}
			$query="SELECT * FROM ".TABLE_BANNER." WHERE ll='".$_POST['sourceLang']."'";
			$results=mysql_query($query);
			while($row=mysql_fetch_array($results)) {
				//banner
				$idbanner=$row['idbanner'];
				if(isset($row['idbanner'])) unset($row['idbanner']);
				$row['ll']=$_POST['destinationLang'];
				foreach($catMap as $ka=>$v) {
					$row['categoria']=str_replace($ka,$v,$row['categoria']);
					}
				$q="INSERT INTO ".TABLE_BANNER." (";
				foreach($row as $ka=>$v) {
					if(!is_numeric($ka)) $q.=$ka.',';
					}
				$q=rtrim($q,",");
				$q.=') VALUES(';
				foreach($row as $ka=>$v) {
					if(!is_numeric($ka)) $q.="'".mysql_real_escape_string($v)."',";
					}
				$q=rtrim($q,",");
				$q.=')';
				mysql_query($q);
				$row['idbanner']=mysql_insert_id();
				//documenti van copiati
				$q="SELECT * FROM ".TABLE_DOCS." WHERE tabella='".TABLE_BANNER."' AND id='".$idbanner."'";
				$rs=mysql_query($q);
				while($r=mysql_fetch_array($rs)) {
					$q="INSERT INTO ".TABLE_DOCS." (filename,hotlink,tabella,id,alt,ordine) VALUES('".mysql_real_escape_string($r['filename'])."','".mysql_real_escape_string($r['hotlink'])."','".TABLE_BANNER."','".$row['idbanner']."','".mysql_real_escape_string($r['alt'])."','".$r['ordine']."')";
					mysql_query($q);
					$iddoc=mysql_insert_id();
					mkdir($_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_DOCS.$iddoc);
					if($r['filename']!="") {
						copy($_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_DOCS.$r['iddoc'].'/'.$r['filename'],$_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_DOCS.$iddoc.'/'.$r['filename']);
						}
					}
				//metadata
				$q="SELECT * FROM ".TABLE_METADATA." WHERE tabella='".TABLE_BANNER."' AND id='".$idbanner."'";
				$rs=mysql_query($q);
				while($r=mysql_fetch_array($rs)) {
					$q="INSERT INTO ".TABLE_METADATA." (tabella,id,param,value) VALUES('".TABLE_BANNER."','".$row['idbanner']."','".mysql_real_escape_string($r['param'])."','".mysql_real_escape_string($r['value'])."')";
					mysql_query($q);
					}
				}
			}

		}
	}
/***/


if(isset($log)) {
	if($log=="") echo '<div id="MsgSuccess">'.$kaTranslate->translate('Languages:Language successfully copied').'</div>';
	else echo '<div id="MsgAlert">'.$kaTranslate->translate('Languages:'.$log).'</div>';
	}

?><h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
	<br />
	<?php  include('copy_menu.inc.php'); ?>
	<br />

	<form action="?" method="post">
	<table>
	<tr>
	<td><label for="sourceLang"><?= $kaTranslate->translate("Languages:Source language"); ?></label><br />
		<select name="sourceLang" id="sourceLang"><?php 
		$query="SELECT * FROM ".TABLE_LINGUE." ORDER BY ordine";
		$results=mysql_query($query);
		$i=0;
		while($row=mysql_fetch_array($results)) {
			?><option value="<?= $row['ll']; ?>"><?= $row['lingua']; ?></option>
			<?php  } ?>
		</select>
		</td>
	<td><br />&rarr;</td>
	<td><label for="destinationLang"><?= $kaTranslate->translate("Languages:Destination language"); ?></label><br />
		<select name="destinationLang" id="destinationLang"><?php 
		$query="SELECT * FROM ".TABLE_LINGUE." ORDER BY ordine";
		$results=mysql_query($query);
		$i=0;
		while($row=mysql_fetch_array($results)) {
			?><option value="<?= $row['ll']; ?>"><?= $row['lingua']; ?></option>
			<?php  } ?>
		</select>
		</td>
	</tr></table>

	<br />
	<h2><?= $kaTranslate->translate("Languages:What do you want to copy?"); ?></h2>
	<table>
	<tr><td><input type="checkbox" name="copyConfig" id="copyConfig" checked /></td><td><label for="copyConfig"><?= $kaTranslate->translate("Menu:Setup"); ?></label></td></tr>
	<tr><td><input type="checkbox" name="copyMenu" id="copyMenu" checked /></td><td><label for="copyMenu"><?= $kaTranslate->translate("Menu:Navigation Menu"); ?></label></td></tr>
	<tr><td><input type="checkbox" name="copyPages" id="copyPages" checked /></td><td><label for="copyPages"><?= $kaTranslate->translate("Menu:Pages"); ?></label></td></tr>
	<tr><td><input type="checkbox" name="copyNews" id="copyNews" checked /></td><td><label for="copyNews"><?= $kaTranslate->translate("Menu:News"); ?></label></td></tr>
	<tr><td><input type="checkbox" name="copyShop" id="copyShop" checked /></td><td><label for="copyShop"><?= $kaTranslate->translate("Menu:Shop"); ?></label></td></tr>
	<tr><td><input type="checkbox" name="copyPhotogallery" id="copyPhotogallery" checked /></td><td><label for="copyPhotogallery"><?= $kaTranslate->translate("Menu:Photogallery"); ?></label></td></tr>
	<tr><td><input type="checkbox" name="copyBanners" id="copyBanners" checked /></td><td><label for="copyBanners"><?= $kaTranslate->translate("Menu:Banners"); ?></label></td></tr>
	<!--<tr><td><input type="checkbox" name="copyShop" id="copyShop" checked /></td><td><label for="copyShop"><?= $kaTranslate->translate("Menu:Shop"); ?></label></td></tr>-->
	</table>
	<br />
	<br />
	<div class="submit">
		<input type="submit" name="copy" value="<?= $kaTranslate->translate("Languages:Copy content"); ?>" class="button" />
		<input type="checkbox" name="overwrite" id="overwrite" /> <label for="overwrite"><?= $kaTranslate->translate("Languages:Overwrite existing ones"); ?></label><br />
		</div>
	</form>
<?php 	
include_once("../inc/foot.inc.php");
