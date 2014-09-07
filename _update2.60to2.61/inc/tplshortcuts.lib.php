<?php
/* (c) Kalamun.org - GNU/GPL 3 */

/* generic */
function kInitBettino($dir=false) {
	if($dir==false) $dir="";
	if(session_id()=="") session_start();
	if(!isset($_GET['url'])) $_GET['url']="";
	$GLOBALS['url']=explode("/",trim($_GET['url'],'/'));
	for($i=0;$i<=2;$i++) { if(!isset($GLOBALS['url'][$i])) $GLOBALS['url'][$i]=""; }
	$GLOBALS['__dir__']=$GLOBALS['url'][0];
	$GLOBALS['__subdir__']=$GLOBALS['url'][1];
	$GLOBALS['__subsubdir__']=$GLOBALS['url'][2];
	for($i=3;isset($GLOBALS['url'][$i]);$i++) {
		$GLOBALS['__subsubdir__'].='/'.$GLOBALS['url'][$i];
		}

	require_once($dir."admin/inc/config.inc.php");
	if(defined("DEBUG")&&DEBUG==true) error_reporting(E_ALL);
	else error_reporting(0);
	require_once($_SERVER['DOCUMENT_ROOT'].BASEDIR."admin/inc/connect.inc.php");
	require_once($_SERVER['DOCUMENT_ROOT'].BASEDIR."admin/inc/main.lib.php");
	require_once($_SERVER['DOCUMENT_ROOT'].BASEDIR."inc/kalamun.lib.php");
	require_once($_SERVER['DOCUMENT_ROOT'].BASEDIR."inc/setlang.inc.php");

	$timezone=kGetVar('timezone',1);
	if($timezone!="") {
		date_default_timezone_set($timezone);
		$query="SET time_zone='".date("P")."'";
		$results=mysql_query($query);
		}

	require_once($_SERVER['DOCUMENT_ROOT'].BASEDIR."inc/config.lib.php");
	require_once($_SERVER['DOCUMENT_ROOT'].BASEDIR."inc/images.lib.php");
	require_once($_SERVER['DOCUMENT_ROOT'].BASEDIR."inc/documents.lib.php");
	require_once($_SERVER['DOCUMENT_ROOT'].BASEDIR."inc/media.lib.php");
	require_once($_SERVER['DOCUMENT_ROOT'].BASEDIR."inc/template.lib.php");
	require_once($_SERVER['DOCUMENT_ROOT'].BASEDIR."inc/emails.lib.php");
	require_once($_SERVER['DOCUMENT_ROOT'].BASEDIR."inc/pagine.lib.php");
	require_once($_SERVER['DOCUMENT_ROOT'].BASEDIR.'inc/news.lib.php');
	require_once($_SERVER['DOCUMENT_ROOT'].BASEDIR.'inc/shop.lib.php');
	require_once($_SERVER['DOCUMENT_ROOT'].BASEDIR.'inc/photogallery.lib.php');
	require_once($_SERVER['DOCUMENT_ROOT'].BASEDIR.'inc/utenti.lib.php');
	require_once($_SERVER['DOCUMENT_ROOT'].BASEDIR.'inc/banner.lib.php');
	require_once($_SERVER['DOCUMENT_ROOT'].BASEDIR.'inc/private.lib.php');

	$GLOBALS['__config']=new kImpostazioni();
	$GLOBALS['__images']=new kImages();
	$GLOBALS['__images_gallery']=new kImgallery();
	$GLOBALS['__documents']=new kDocuments();
	$GLOBALS['__documents_gallery']=new kDocgallery();
	$GLOBALS['__media']=new kMedia();
	$GLOBALS['__template']=new kTemplate();
	$GLOBALS['__emails']=new kEmails();
	$GLOBALS['__pages']=new kPages();
	$GLOBALS['__news']=new kNews();
	$GLOBALS['__shop']=new kShop();
	$GLOBALS['__banner']=new kBanners();
	$GLOBALS['__photogallery']=new kPhotogallery();
	$GLOBALS['__users']=new kUsers();
	$GLOBALS['__members']=new kMembers();
	$GLOBALS['__private']=new kPrivate();
	}
function kGetVar($var,$value=1,$ll=false) {
	if($ll==false) $ll=LANG;
	if(isset($GLOBALS['__template'])) {
		return $GLOBALS['__template']->getVar($var,$value,$ll);
		}
	else {
		$query="SELECT value".$value." FROM ".TABLE_CONFIG." WHERE param='".$var."' AND ll='".$ll."' LIMIT 1";
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		return $row['value'.$value];
		}
	}
function kGetAdminName() {
	return ADMIN_NAME;
	}
function kGetAdminEmail() {
	return ADMIN_MAIL;
	}
function kGetBaseDir() {
	return BASEDIR;
	}
function kGetBasePath() {
	return $_SESSION['DOCUMENT_ROOT'].BASEDIR;
	}
function kGetCurrentLanguageDir() {
	return BASEDIR.strtolower(LANG).'/';
	}
function kGetCurrentLanguagePath() {
	return $_SESSION['DOCUMENT_ROOT'].BASEDIR.strtolower(LANG).'/';
	}
function kGetTemplateDir($basedir=false) {
	if($basedir==false) $basedir=BASEDIR;
	return $basedir.$GLOBALS['__template']->getTemplateDir();
	}
function kGetTemplatePath($basedir=false) {
	if($basedir==false) $basedir=BASEDIR;
	return $_SERVER['DOCUMENT_ROOT'].$basedir.$GLOBALS['__template']->getTemplateDir();
	}
function kGetFeedDir($basedir=false) {
	if($basedir==false) $basedir=BASEDIR;
	return $basedir.strtolower(LANG).'/'.$GLOBALS['__template']->getVar('dir_feed',1).'/';
	}
function kGetSearchDir($basedir=false) {
	if($basedir==false) $basedir=BASEDIR;
	return $basedir.strtolower(LANG).'/'.$GLOBALS['__template']->getVar('dir_search',1).'/';
	}
function kGetLanguage() {
	return LANG;
	}
function kTranslate($param,$ll=false) {
	$args=func_get_args();
	array_shift($args);
	array_shift($args);
	return $GLOBALS['__template']->translate($param,$ll,$args);
	}
function kIsMobile() {
	return $GLOBALS['__template']->isMobile();
	}
function kWhatMobile() {
	return $GLOBALS['__template']->isMobile(true);
	}
function kIsHome() {
	return $GLOBALS['__template']->isHome();
	}
function kIsNews() {
	return $GLOBALS['__template']->isNews();
	}
function kIsShop() {
	return $GLOBALS['__template']->isShop();
	}
function kIsShopCart() {
	return $GLOBALS['__template']->isShopCart();
	}
function kIsPhotogallery() {
	return $GLOBALS['__template']->isPhotogallery();
	}
function kIsFeed() {
	return $GLOBALS['__template']->isFeed();
	}
function kIsSearch() {
	return $GLOBALS['__template']->isSearch();
	}
function kIsPrivate() {
	return $GLOBALS['__template']->isPrivate();
	}
function kGetHomeDir($ll=false) {
	return kGetVar('home_page',1,$ll);
	}
function kGetNewsDir($ll=false) {
	return kGetVar('dir_news',1,$ll);
	}
function kGetPrivateDir($ll=false) {
	return kGetVar('dir_private',1,$ll);
	}
function kGetShopDir($ll=false) {
	return kGetVar('dir_shop',1,$ll);
	}
function kGetShopCartDir($ll=false) {
	return kGetVar('dir_shop_cart',1,$ll);
	}
function kGetShopManufacturersDir($ll=false) {
	return kGetVar('dir_shop_manufacturers',1,$ll);
	}
function kGetPhotogalleriesDir($ll=false) {
	return kGetVar('dir_photogallery',1,$ll);
	}
function kGetUsersDir($ll=false) {
	return kGetVar('dir_users',1,$ll);
	}
function kPrintHeader($file=false) {
	if($file==false) $file="header.php";
	include($_SERVER['DOCUMENT_ROOT'].kGetTemplateDir().'inc/'.$file);
	}
function kPrintFooter($file=false) {
	if($file==false) $file="footer.php";
	include($_SERVER['DOCUMENT_ROOT'].kGetTemplateDir().'inc/'.$file);
	}
function kGetCountryNameByCode($ll) {
	foreach(file($_SERVER['DOCUMENT_ROOT'].BASEDIR.'admin/shop/countries.txt') as $line)
	{
		$tmp=explode("\t",$line);
		$tmp[1]=trim($tmp[1]);
		if($tmp[1]==$ll) return trim($tmp[0]);
	}
	return false;
}
	
/* site layout */
function kGetSiteURL() {
	return SITE_URL;
	}
function kGetSiteName() {
	return kGetVar('sitename',1);
	}
function kGetSitePayoff() {
	return kGetVar('sitename',2);
	}
function kGetTitle() {
	if($GLOBALS['__template']->getTitle()!=false) return $GLOBALS['__template']->getTitle();
	else {
		if(kHavePage()) $metadata=$GLOBALS['__pages']->getMetadata();
		elseif(kHaveNews()) $metadata=$GLOBALS['__news']->getMetadata();
		elseif(kHavePhotogallery()) $metadata=$GLOBALS['__photogallery']->getMetadata();
		elseif(kHaveManufacturer()) $metadata=$GLOBALS['__shop']->getManufacturerMetadata();
		elseif(kHaveShop()) $metadata=$GLOBALS['__shop']->getMetadata();
		else return strip_tags($GLOBALS['__template']->getMenuCrumbs());

		if(trim($metadata['seo_title'])) return $metadata['seo_title'];
		else return $metadata['titolo'];
		}
	}

function kGetSeoMetadata($dir=null,$ll=null) {
	if($ll==null) $ll=LANG;
	if($dir==null) $dir=trim($GLOBALS['__dir__'].'/'.$GLOBALS['__subdir__'].'/'.$GLOBALS['__subsubdir__'],"/");
	$seometadata=array();
	$revisit_after=array("always"=>"1 day","hourly"=>"1 day","daily"=>"1 day","weekly"=>"7 days","monthly"=>"31 days","yearly"=>"365 days","never"=>"365 days");
	$seometadata['description']=kGetVar('seo_description',1,$ll);
	$seometadata['keywords']=kGetVar('seo_keywords',1,$ll);
	$seometadata['changefreq']=kGetVar('seo_changefreq',1,$ll);
	$seometadata['revisit_after']=$revisit_after;
	$seometadata['priority']=kGetVar('seo_priority',1,$ll);
	$seometadata['robots']=kGetVar('seo_robots',1,$ll);
	$seometadata['seo_canonical']="";
	$seometadata['featuredimage']=array();
	if($GLOBALS['__pages']->pageExists($dir,$ll)) { $metadata=$GLOBALS['__pages']->getMetadata($dir); }
	elseif($GLOBALS['__news']->newsExists($dir,$ll)) { $metadata=$GLOBALS['__news']->getMetadata($dir); }
	elseif($GLOBALS['__photogallery']->photogalleryExists($dir)) { $metadata=$GLOBALS['__photogallery']->getMetadata($dir,$ll); }
	elseif($GLOBALS['__shop']->shopItemExists($dir)) { $metadata=$GLOBALS['__shop']->getMetadata($dir,$ll); }
	elseif($GLOBALS['__shop']->shopManufacturerExists($dir)) { $metadata=$GLOBALS['__shop']->getManufacturerMetadata($dir,$ll); }
	if(isset($metadata['seo_title'])&&$metadata['seo_title']!="") $seometadata['title']=$metadata['seo_title'];
	if(isset($metadata['seo_description'])&&$metadata['seo_description']!="") $seometadata['description']=$metadata['seo_description'];
	if(isset($metadata['seo_keywords'])&&$metadata['seo_keywords']!="") $seometadata['keywords']=$metadata['seo_keywords'];
	if(isset($metadata['seo_changefreq'])&&$metadata['seo_changefreq']!="") $seometadata['changefreq']=$metadata['seo_changefreq'];
	if(isset($metadata['seo_priority'])&&$metadata['seo_priority']!="") $seometadata['priority']=$metadata['seo_priority'];
	if(isset($metadata['seo_robots'])&&$metadata['seo_robots']!="") $seometadata['robots']=$metadata['seo_robots'];
	if(isset($metadata['seo_canonical'])&&$metadata['seo_canonical']!="") $seometadata['canonical']=$metadata['seo_canonical'];
	if(isset($metadata['seo_changefreq'])&&$metadata['seo_changefreq']!="") $seometadata['revisit_after']=$revisit_after[$seometadata['changefreq']];
	if(isset($metadata['featuredimage'])&&$metadata['featuredimage']!="") $seometadata['featuredimage']=$metadata['featuredimage'];
	return $seometadata;
	}

function kGetCrumbs() {
	return $GLOBALS['__template']->getMenuCrumbs("array");
	}
function kPrintCrumbs() {
	echo $GLOBALS['__template']->getMenuCrumbs();
	}
function kGetLanguages($translations=null) {
	if($translations==null) {
		if(kHavePage()) {
			$p=kGetPage();
			$translations=$p['traduzioni'];
			}
		elseif(kHaveNews()) {
			$n=kGetNews();
			$translations=$n['traduzioni'];
			}
		elseif(kHavePhotogallery()) {
			$n=kGetPhotogallery();
			$translations=$n['traduzioni'];
			}
		elseif(kHaveShopItem()) {
			$n=kGetShopItem();
			$translations=$n['traduzioni'];
			}
		elseif(kIsNews()) {
			$vars['translations']=array();
			foreach($GLOBALS['__template']->getLanguages(array()) as $ll=>$lang) {
				$vars['translations'][$ll]=SITE_URL.BASEDIR.strtolower($ll).'/'.$GLOBALS['__template']->getVar('dir_news',1,$ll);
				}
			}
		elseif(kIsPhotogallery()) {
			$vars['translations']=array();
			foreach($GLOBALS['__template']->getLanguages(array()) as $ll=>$lang) {
				$vars['translations'][$ll]=SITE_URL.BASEDIR.strtolower($ll).'/'.$GLOBALS['__template']->getVar('dir_photogallery',1,$ll);
				}
			}
		elseif(kIsShop()) {
			$vars['translations']=array();
			foreach($GLOBALS['__template']->getLanguages(array()) as $ll=>$lang) {
				$vars['translations'][$ll]=SITE_URL.BASEDIR.strtolower($ll).'/'.$GLOBALS['__template']->getVar('dir_shop',1,$ll);
				}
			}
		elseif(kIsSearch()) {
			$vars['translations']=array();
			foreach($GLOBALS['__template']->getLanguages(array()) as $ll=>$lang) {
				$vars['translations'][$ll]=SITE_URL.BASEDIR.strtolower($ll).'/'.$GLOBALS['__template']->getVar('dir_search',1,$ll);
				}
			}
		elseif(kIsPrivate()) {
			$vars['translations']=array();
			foreach($GLOBALS['__template']->getLanguages(array()) as $ll=>$lang) {
				$vars['translations'][$ll]=SITE_URL.BASEDIR.strtolower($ll).'/'.$GLOBALS['__template']->getVar('dir_private',1,$ll);
				}
			}
		elseif(kIsFeed()) {
			$vars['translations']=array();
			foreach($GLOBALS['__template']->getLanguages(array()) as $ll=>$lang) {
				$vars['translations'][$ll]=SITE_URL.BASEDIR.strtolower($ll).'/'.$GLOBALS['__template']->getVar('dir_feed',1,$ll);
				}
			}
		}
	return $GLOBALS['__template']->getLanguages($translations);
	}
function kPrintLanguages($vars=null,$mode="flags") {
	if(!is_array($vars)) {
		$vars=array("translations"=>$vars);
		if($mode=="flags") $vars['flags']=true;
		else $vars['labels']=true;
		}
	if(!isset($vars['translations'])||$vars['translations']==null) {
		if(kHavePage()) {
			$p=kGetPage();
			$vars['translations']=$p['traduzioni'];
			}
		elseif(kHaveNews()) {
			$n=kGetNews();
			$vars['translations']=$n['traduzioni'];
			}
		elseif(kHavePhotogallery()) {
			$n=kGetPhotogallery();
			$vars['translations']=$n['traduzioni'];
			}
		elseif(kHaveShopItem()) {
			$n=kGetShopItem();
			$vars['translations']=$n['traduzioni'];
			}
		elseif(kIsNews()) {
			$vars['translations']=array();
			foreach($GLOBALS['__template']->getLanguages(array()) as $ll=>$lang) {
				$vars['translations'][$ll]=SITE_URL.BASEDIR.strtolower($ll).'/'.$GLOBALS['__template']->getVar('dir_news',1,$ll);
				}
			}
		elseif(kIsPhotogallery()) {
			$vars['translations']=array();
			foreach($GLOBALS['__template']->getLanguages(array()) as $ll=>$lang) {
				$vars['translations'][$ll]=SITE_URL.BASEDIR.strtolower($ll).'/'.$GLOBALS['__template']->getVar('dir_photogallery',1,$ll);
				}
			}
		elseif(kIsShop()) {
			$vars['translations']=array();
			foreach($GLOBALS['__template']->getLanguages(array()) as $ll=>$lang) {
				$vars['translations'][$ll]=SITE_URL.BASEDIR.strtolower($ll).'/'.$GLOBALS['__template']->getVar('dir_shop',1,$ll);
				}
			}
		elseif(kIsSearch()) {
			$vars['translations']=array();
			foreach($GLOBALS['__template']->getLanguages(array()) as $ll=>$lang) {
				$vars['translations'][$ll]=SITE_URL.BASEDIR.strtolower($ll).'/'.$GLOBALS['__template']->getVar('dir_search',1,$ll);
				}
			}
		elseif(kIsPrivate()) {
			$vars['translations']=array();
			foreach($GLOBALS['__template']->getLanguages(array()) as $ll=>$lang) {
				$vars['translations'][$ll]=SITE_URL.BASEDIR.strtolower($ll).'/'.$GLOBALS['__template']->getVar('dir_private',1,$ll);
				}
			}
		elseif(kIsFeed()) {
			$vars['translations']=array();
			foreach($GLOBALS['__template']->getLanguages(array()) as $ll=>$lang) {
				$vars['translations'][$ll]=SITE_URL.BASEDIR.strtolower($ll).'/'.$GLOBALS['__template']->getVar('dir_feed',1,$ll);
				}
			}
		}
	echo $GLOBALS['__template']->printLanguages($vars);
	}
function kPrintMenu($vars=false,$recursive=true,$img=false,$labels=true,$collection='') {
	if(!is_array($vars)) {
		$sub=$vars;
		$vars=array();
		$vars['sub']=$sub;
		$vars['recursive']=$recursive;
		$vars['img']=$img;
		$vars['labels']=$labels;
		$vars['collection']=$collection;
		$vars['ll']=false;
		}
	if(!isset($vars['sub'])) $vars['sub']=false;
	if(!isset($vars['recursive'])) $vars['recursive']=false;
	if(!isset($vars['img'])) $vars['img']=false;
	if(!isset($vars['labels'])) $vars['labels']=true;
	if(!isset($vars['collection'])) $vars['collection']='';
	if(!isset($vars['ll'])) $vars['ll']=false;
	if(!isset($vars['template'])) $vars['template']='';

	$GLOBALS['__template']->setMenuCollection($vars['collection']);
	echo $GLOBALS['__template']->printMenu($vars);
	}
function kGetMenu($vars=false,$recursive=true,$img=false,$labels=true,$collection='') {
	if(!is_array($vars)) {
		$sub=$vars;
		$vars=array();
		$vars['sub']=$sub;
		$vars['recursive']=$recursive;
		$vars['img']=$img;
		$vars['labels']=$labels;
		$vars['collection']=$collection;
		$vars['ll']=false;
		}
	if(!isset($vars['sub'])) $vars['sub']=false;
	if(!isset($vars['recursive'])) $vars['recursive']=false;
	if(!isset($vars['img'])) $vars['img']=false;
	if(!isset($vars['labels'])) $vars['labels']=true;
	if(!isset($vars['collection'])) $vars['collection']='';
	if(!isset($vars['ll'])) $vars['ll']=false;
	if(!isset($vars['template'])) $vars['template']='';

	$GLOBALS['__template']->setMenuCollection($vars['collection']);
	return $GLOBALS['__template']->printMenu($vars);
	}
function kGetMenuStructure() {
	return $GLOBALS['__template']->menuStructure;
	}
function kGetMenuElementById($idmenu) {
	return $GLOBALS['__template']->menuContents[$idmenu];
	}
function kSetMenuSelectedByURL($url) {
	$GLOBALS['__template']->setMenuSelectedByURL($url);
	}
function kGetMenuId() {
	$m=$GLOBALS['__template']->getMenuSelected();
	return $m['idmenu'];
	}
function kGetFooter() {
	return kGetVar('footer',1);
	}
function kGetCopyright() {
	return kGetVar('footer',2);
	}
function kGetExternalStatistics() {
	return kGetVar('google_analytics',1);
	}
function kGetContents() {
	return $GLOBALS['__template']->contents;
	}

/* images */
function kGetImagesDir() {
	return DIR_IMG;
	}
function kSetImage($imgArray) {
	return $GLOBALS['__template']->imgDB=$imgArray;
	}
function kGetImageId() {
	return $GLOBALS['__template']->imgDB['idimg'];
	}
function kGetImageFilename() {
	return $GLOBALS['__template']->imgDB['filename'];
	}
function kGetImageFilesize($um="Kb",$dec=0) {
	if($um=="Kb") $divide=1024;
	elseif($um=="Mb") $divide=1024*1024;
	elseif($um=="Gb") $divide=1024*1024*1024;
	else $divide=1;
	return number_format($GLOBALS['__template']->imgDB['filesize']/$divide,$dec);
	}
function kGetImageWidth() {
	return $GLOBALS['__template']->imgDB['width'];
	}
function kGetImageHeight() {
	return $GLOBALS['__template']->imgDB['height'];
	}
function kGetImageURL() {
	return $GLOBALS['__template']->imgDB['url'];
	}
function kGetImageClass() {
	return $GLOBALS['__template']->imgDB['class'];
	}
function kGetImageAlt() {
	return $GLOBALS['__template']->imgDB['alt'];
	}
function kGetImageCaption() {
	return $GLOBALS['__template']->imgDB['caption'];
	}
function kGetThumbFilename() {
	return $GLOBALS['__template']->imgDB['thumb']['filename'];
	}
function kGetThumbWidth() {
	return $GLOBALS['__template']->imgDB['thumb']['width'];
	}
function kGetThumbHeight() {
	return $GLOBALS['__template']->imgDB['thumb']['height'];
	}
function kGetThumbURL() {
	return $GLOBALS['__template']->imgDB['thumb']['url'];
	}
function kGetThumbAlt() {
	return $GLOBALS['__template']->imgDB['alt'];
	}
function kGetThumbCaption() {
	return $GLOBALS['__template']->imgDB['caption'];
	}
function kGetThumbClass() {
	return $GLOBALS['__template']->imgDB['class'];
	}

/* documents */
function kGetDocumentsDir() {
	return DIR_DOCS;
	}
function kGetDocumentsByGallery($table=false,$id=false,$orderby=false,$conditions=false) {
	$GLOBALS['__documents']=new kDocgallery();
	if($orderby==false) $orderby='ordine';
	return $GLOBALS['__documents']->getList($table,$id,$orderby,$conditions);
	}
function kSetDocument($docArray) {
	return $GLOBALS['__template']->docDB=$docArray;
	}
function kGetDocumentId() {
	return $GLOBALS['__template']->docDB['iddoc'];
	}
function kGetDocumentFilename() {
	return $GLOBALS['__template']->docDB['filename'];
	}
function kGetDocumentFilesize($um="Kb",$dec=0) {
	if($um=="Kb") $divide=1024;
	elseif($um=="Mb") $divide=1024*1024;
	elseif($um=="Gb") $divide=1024*1024*1024;
	else $divide=1;
	return number_format($GLOBALS['__template']->docDB['filesize']/$divide,$dec);
	}
function kGetDocumentURL() {
	return $GLOBALS['__template']->docDB['url'];
	}
function kGetDocumentAlt() {
	return str_replace("\n","",$GLOBALS['__template']->docDB['alt']);
	}
function kGetDocumentCaption() {
	if($GLOBALS['__template']->docDB['caption']!="") return $GLOBALS['__template']->docDB['caption'];
	else return $GLOBALS['__template']->docDB['alt'];
	}
function kGetDocumentWidth() {
	return $GLOBALS['__template']->docDB['width'];
	}
function kGetDocumentHeight() {
	return $GLOBALS['__template']->docDB['height'];
	}

/* media */
function kGetMediasDir() {
	return DIR_MEDIA;
	}
function kSetMedia($mediaArray) {
	return $GLOBALS['__template']->mediaDB=$mediaArray;
	}
function kMediaIsHotlink() {
	return $GLOBALS['__template']->mediaDB['hotlink']!=false?true:false;
	}
function kMediaIsHtmlCode() {
	return $GLOBALS['__template']->mediaDB['htmlcode']!=false?true:false;
	}
function kGetMediaId() {
	return $GLOBALS['__template']->mediaDB['idmedia'];
	}
function kGetMediaFilename() {
	return $GLOBALS['__template']->mediaDB['filename'];
	}
function kGetMediaFilesize($um="Kb",$dec=0) {
	if($um=="Kb") $divide=1024;
	elseif($um=="Mb") $divide=1024*1024;
	elseif($um=="Gb") $divide=1024*1024*1024;
	else $divide=1;
	return number_format($GLOBALS['__template']->mediaDB['filesize']/$divide,$dec);
	}
function kGetMediaWidth() {
	return $GLOBALS['__template']->mediaDB['width'];
	}
function kGetMediaHeight() {
	return $GLOBALS['__template']->mediaDB['height'];
	}
function kGetMediaURL() {
	return $GLOBALS['__template']->mediaDB['url'];
	}
function kGetMediaAlt() {
	return $GLOBALS['__template']->mediaDB['alt'];
	}
function kGetMediaTitle() {
	return $GLOBALS['__template']->mediaDB['title'];
	}
function kGetMediaDuration() {
	return $GLOBALS['__template']->mediaDB['duration'];
	}
function kGetMediaMimeType() {
	return $GLOBALS['__template']->mediaDB['mimetype'];
	}
function kGetMediaHtmlCode() {
	return $GLOBALS['__template']->mediaDB['htmlcode'];
	}
function kGetMediaCaption() {
	return $GLOBALS['__template']->mediaDB['caption'];
	}
function kGetMediaThumbFilename() {
	return $GLOBALS['__template']->mediaDB['thumb']['filename'];
	}
function kGetMediaThumbWidth() {
	return $GLOBALS['__template']->mediaDB['thumb']['width'];
	}
function kGetMediaThumbHeight() {
	return $GLOBALS['__template']->mediaDB['thumb']['height'];
	}
function kGetMediaThumbURL() {
	return $GLOBALS['__template']->mediaDB['thumb']['url'];
	}
function kGetMediaThumbAlt() {
	return $GLOBALS['__template']->mediaDB['alt'];
	}
function kGetMediaThumbCaption() {
	return $GLOBALS['__template']->mediaDB['caption'];
	}

/* comments */
function kSetComment($c) {
	$GLOBALS['__template']->setComment($c);
	}
function kAddComment($vars) {
	$GLOBALS['__template']->addComment($vars);
	}
function kGetCommentID() {
	return $GLOBALS['__template']->commentDB['idcomm'];
	}
function kGetCommentDate($dateformat=false) {
	if($dateformat==false) $dateformat=kGetVar('timezone',2);
	$H=substr($GLOBALS['__template']->commentDB['data'],11,2);
	$i=substr($GLOBALS['__template']->commentDB['data'],14,2);
	$s=substr($GLOBALS['__template']->commentDB['data'],17,2);
	$d=substr($GLOBALS['__template']->commentDB['data'],8,2);
	$m=substr($GLOBALS['__template']->commentDB['data'],5,2);
	$Y=substr($GLOBALS['__template']->commentDB['data'],0,4);
	return strftime($dateformat,mktime($H,$i,$s,$m,$d,$Y));
	}
function kGetCommentAuthor() {
	return $GLOBALS['__template']->commentDB['autore'];
	}
function kGetCommentEmail() {
	return $GLOBALS['__template']->commentDB['email'];
	}
function kGetCommentIP() {
	return $GLOBALS['__template']->commentDB['ip'];
	}
function kGetCommentText() {
	return $GLOBALS['__template']->commentDB['testo'];
	}

/* email gateway */
function kGetEmailSubject() {
	return $GLOBALS['__emails']->getVar('subject');
	}
function kGetEmailMessage() {
	return $GLOBALS['__emails']->getVar('message');
	}
function kGetEmailFooter() {
	$footer=$GLOBALS['__emails']->getVar('footer');
	if($footer=="") $footer=kGetVar('footer',1);
	return $footer;
	}
function kGetEmailUID() {
	return $GLOBALS['__emails']->getVar('uid');
	}
function kGetEmailLoggerURL() {
	return SITE_URL.BASEDIR.'inc/email_logger.php?uid='.$GLOBALS['__emails']->getVar('uid');;
	}
function kSendEmail($from,$to,$subject,$message,$template=false) {
	return $GLOBALS['__emails']->send($from,$to,$subject,$message,$template);
	}

/* search engine */
function kSearch($keywords) {
	$output=array_merge(
		$GLOBALS['__pages']->search($keywords),
		$GLOBALS['__news']->search($keywords)
		);
	return $output;
	}

/* pages */
function kHavePage() {
	$dir=trim($GLOBALS['__dir__'].'/'.$GLOBALS['__subdir__'].'/'.$GLOBALS['__subsubdir__'],"/");
	if($dir!="") {
		if($GLOBALS['__pages']->pageExists($dir)) return true;
		}
	return false;
	}
function kSetPageTemplate($dir=false) {
	if($dir==false) $dir=trim($GLOBALS['__dir__'].'/'.$GLOBALS['__subdir__'].'/'.$GLOBALS['__subsubdir__'],"/");
	$p=$GLOBALS['__pages']->getPage($dir);
	if(!isset($p['layout'])) $p['layout']="";
	$GLOBALS['__template']->setTemplate($p['template']);
	$GLOBALS['__template']->setLayout($p['layout']);
	}
function kGetPageList($ll=null) {
	return $GLOBALS['__pages']->getPageList($ll);
	}
function kGetPageQuickList($cat=false,$page=false,$limit=false,$start=false,$conditions="",$options="",$orderby="",$ll=null) {
	if($page==false) isset($_GET['page'])?$page=$_GET['page']:$page=1;
	if($limit==false) $limit=1024;
	if($start==false) $start=0;
	$start+=($page-1)*$limit;
	return $GLOBALS['__pages']->getQuickList($cat,$start,$limit,$conditions,$options,$orderby,$ll);
	}
function kGetPage($dir=false) {
	if($dir==false) $dir=trim($GLOBALS['__dir__'].'/'.$GLOBALS['__subdir__'].'/'.$GLOBALS['__subsubdir__'],"/");
	if($GLOBALS['__pages']->pageExists($dir)) return $GLOBALS['__pages']->getPage($dir);
	}
function kSetPageByDir($dir=false,$ll=false) {
	if($dir==false) $dir=trim($GLOBALS['__dir__'].'/'.$GLOBALS['__subdir__'].'/'.$GLOBALS['__subsubdir__'],"/");
	if($ll==false) $ll=LANG;
	return $GLOBALS['__pages']->setPageByDir($dir,$ll);
	}
function kGetPageId() {
	return $GLOBALS['__pages']->getPageVar('idpag');
	}
function kGetPagePermalink() {
	return $GLOBALS['__pages']->getPageVar('permalink');
	}
function kPrintPage($dir=false) {
	if($dir==false) $dir=trim($GLOBALS['__dir__'].'/'.$GLOBALS['__subdir__'].'/'.$GLOBALS['__subsubdir__'],"/");
	kSetPageByDir($dir);
	echo $GLOBALS['__template']->getSubtemplate('page');
	}
function kGetPageDir() {
	return $GLOBALS['__pages']->getPageVar('dir');
	}
function kGetPageTitle() {
	return $GLOBALS['__pages']->getPageVar('titolo');
	}
function kGetPageSubtitle() {
	return $GLOBALS['__pages']->getPageVar('sottotitolo');
	}
function kGetPagePreview() {
	return $GLOBALS['__pages']->getPageVar('anteprima');
	}
function kGetPageText() {
	return $GLOBALS['__pages']->getPageVar('testo');
	}
function kGetPageFeaturedImage() {
	return $GLOBALS['__pages']->getPageVar('featuredimage');
	}
function kGetPagePhotogallery() {
	return $GLOBALS['__pages']->getPageVar('imgs');
	}
function kGetPageDocuments() {
	return $GLOBALS['__pages']->getPageVar('docs');
	}
function kGetPageDateCreated() {
	return $GLOBALS['__pages']->getPageVar('created');
	}
function kGetPageDateModified() {
	return $GLOBALS['__pages']->getPageVar('modified');
	}
function kGetPageEmbeddedImages() {
	return $GLOBALS['__pages']->getPageVar('embeddedimgs');
	}
function kGetPageEmbeddedDocuments() {
	return $GLOBALS['__pages']->getPageVar('embeddeddocs');
	}
function kGetPageEmbeddedMedias() {
	return $GLOBALS['__pages']->getPageVar('embeddedmedias');
	}
function kGetParagraphTitle() {
	return $GLOBALS['__template']->pageDB['titolo'];
	}
function kGetParagraphSubtitle() {
	return $GLOBALS['__template']->pageDB['sottotitolo'];
	}
function kGetParagraphId() {
	return $GLOBALS['__template']->pageDB['idlpt'];
	}
function kGetParagraphText() {
	return $GLOBALS['__template']->pageDB['testo'];
	}
function kGetForm() {
	return $GLOBALS['__template']->pageDB['form'];
	}
function kGetFormId() {
	return $GLOBALS['__template']->pageDB['idlpt'];
	}
function kGetPageComments() {
	return $GLOBALS['__pages']->getPageVar('commenti');
	}
function kPrintPageComments() {
	foreach(kGetPageComments() as $n) {
		kSetComment($n);
		echo $GLOBALS['__template']->getSubtemplate('page_comment');
		}
	}
function kGetPageCommentsCount() {
	return count($GLOBALS['__pages']->getPageVar('commenti'));
	}
function kPrintPageCommentsForm() {
	if(kGetVar('pages-commenti',1)=='s'&&$GLOBALS['__pages']->getPageVar('allowcomments')=='s') {
		echo $GLOBALS['__template']->getSubtemplate('page_commentform');
		}
	}
function kAddPageComment($name,$email,$text,$idpag,$public="n") {
	if(kGetVar('pages-commenti',1)=='s'&&$GLOBALS['__pages']->getPageVar('allowcomments')=='s') {
		return $pages->addComment($name,$email,$text,$idpag,$public="n");
		}
	}
function kGetPageMetadata($param=false,$dir=false,$ll=false) {
	if($dir==false) $dir=trim($GLOBALS['__dir__'].'/'.$GLOBALS['__subdir__'].'/'.$GLOBALS['__subsubdir__']," /");
	if($ll==false) $ll=LANG;
	$metadata=$GLOBALS['__pages']->getMetadata($dir,$ll);
	if($param!=false) {
		if(isset($metadata[$param])) return $metadata[$param];
		else return "";
		}
	return $metadata;
	}
function kGetPageCategories() {
	$output=$GLOBALS['__pages']->getPageVar('categorie');
	return $output;
	}
function kGetPagesCategoryByName($name,$ll=false) {
	return $GLOBALS['__pages']->getCatByName($name,$ll);
	}
function kGetPagesCategoryById($idcat) {
	return $GLOBALS['__pages']->getCatById($idcat);
	}
function kGetPagesCategories($ll=false) {
	return $GLOBALS['__pages']->getCategories($ll);
	}
function kGetPageConversions($boolean=false) {
	return $GLOBALS['__pages']->getConversionsResults($boolean);
	}



/* news */
function kHaveNews() {
	return $GLOBALS['__news']->newsExists();
	}
function kSetNewsTemplate($dir=false) {
	$n=$GLOBALS['__news']->getNewsTemplate($dir);
	$GLOBALS['__template']->setTemplate($n['template']);
	$GLOBALS['__template']->setLayout($n['layout']);
	}
function kGetNews($dir=false) {
	return $GLOBALS['__news']->getNews($dir);
	}
function kSetNewsByDir($dir=false,$ll=false) {
	if($ll==false) $ll=LANG;
	$GLOBALS['__news']->setNewsByDir($dir,$ll);
	}
function kNewsIsInHome() {
	return $GLOBALS['__news']->getNewsVar('home')=='s'?true:false;
	}
function kNewsIsInCalendar() {
	return $GLOBALS['__news']->getNewsVar('calendar')=='s'?true:false;
	}
function kGetNewsId() {
	return $GLOBALS['__news']->getNewsVar('idnews');
	}
function kGetNewsSubdir() {
	return $GLOBALS['__news']->getNewsVar('dir');
	}
function kGetNewsTitle() {
	return $GLOBALS['__news']->getNewsVar('titolo');
	}
function kGetNewsSubtitle() {
	return $GLOBALS['__news']->getNewsVar('sottotitolo');
	}
function kGetNewsPreview() {
	return $GLOBALS['__news']->getNewsVar('anteprima');
	}
function kGetNewsText() {
	return $GLOBALS['__news']->getNewsVar('testo');
	}
function kGetNewsFeaturedImage() {
	return $GLOBALS['__news']->getNewsVar('featuredimage');
	}
function kGetNewsPhotogallery() {
	return $GLOBALS['__news']->getNewsVar('imgs');
	}
function kGetNewsDocuments() {
	return $GLOBALS['__news']->getNewsVar('docs');
	}
function kGetNewsPermalink() {
	return $GLOBALS['__news']->getNewsVar('permalink');
	}
function kGetNewsDateCreated() {
	return $GLOBALS['__news']->getNewsVar('pubblica');
	}
function kGetNewsDateModified() {
	return $GLOBALS['__news']->getNewsVar('modified');
	}
function kGetNewsStartingDate() {
	return $GLOBALS['__news']->getNewsVar('starting_date');
	}
function kGetNewsExpirationDate() {
	return $GLOBALS['__news']->getNewsVar('scadenza');
	}
function kGetNewsArchivePermalink() {
	$type=$GLOBALS['__template']->pageDB;
	$permalink=$GLOBALS['__news']->getNewsVar('archpermalink');
	return $permalink[$type];
	}
function kGetNewsAuthor() {
	$author=$GLOBALS['__news']->getNewsVar('autore');
	return $author['name'];
	}
function kGetNewsAuthorPermalink() {
	$author=$GLOBALS['__news']->getNewsVar('autore');
	return $author['permalink'];
	}
function kGetNewsDate($format=false,$d=false) {
	$orderby="";
	if($format==false) $format=kGetVar('timezone',2);
	if($d=='creation') $date=$GLOBALS['__news']->getNewsVar('data');
	elseif($d=='expiration') $date=$GLOBALS['__news']->getNewsVar('scadenza');
	elseif($d=='starting') $date=$GLOBALS['__news']->getNewsVar('starting_date');
	elseif($d=='publish') $date=$GLOBALS['__news']->getNewsVar('pubblica');
	else {
		if($orderby=="") $orderby=kGetVar('news-order',1);
		if($orderby=="") $orderby="pubblica DESC";
		if(strpos($orderby,"data ")!==false||strpos($orderby,"pubblica ")!==false||strpos($orderby,"starting_date ")!==false||strpos($orderby,"scadenza ")!==false) $dataRef=trim(substr($orderby,0,-4)); else $dataRef='pubblica';
		$date=$GLOBALS['__news']->getNewsVar($dataRef);
		}
	return utf8_encode(strftime($format,mktime(substr($date,11,2),substr($date,14,2),substr($date,17,2),substr($date,5,2),substr($date,8,2),substr($date,0,4))));
	}
function kGetNewsAssignedCategories() {
	return $GLOBALS['__news']->getNewsVar('categorie');
	}
function kGetNewsEmbeddedImages() {
	return $GLOBALS['__news']->getNewsVar('embeddedimgs');
	}
function kGetNewsEmbeddedDocuments() {
	return $GLOBALS['__news']->getNewsVar('embeddeddocs');
	}
function kGetNewsEmbeddedMedias() {
	return $GLOBALS['__news']->getNewsVar('embeddedmedias');
	}
function kGetNewsMetadata($param=false,$dir=false,$ll=false) {
	if($dir==false) $dir=trim($GLOBALS['__dir__'].'/'.$GLOBALS['__subdir__'].'/'.$GLOBALS['__subsubdir__']," /");
	if($ll==false) $ll=LANG;
	$metadata=$GLOBALS['__news']->getMetadata($dir,$ll);
	if($param!=false) {
		if(isset($metadata[$param])) return $metadata[$param];
		else return "";
		}
	return $metadata;
	}
function kGetNewsComments() {
	return $GLOBALS['__news']->getNewsVar('commenti');
	}
function kPrintNewsComments() {
	foreach(kGetNewsComments() as $n) {
		kSetComment($n);
		echo $GLOBALS['__template']->getSubtemplate('news_comment');
		}
	}
function kGetNewsCommentsCount() {
	return count($GLOBALS['__news']->getNewsVar('commenti'));
	}
function kGetNewsCommentID() {
	return $GLOBALS['__template']->commentDB['idcomm'];
	}
function kGetNewsCommentDate($dateformat=false) {
	if($dateformat==false) $dateformat=kGetVar('timezone',2);
	$H=substr($GLOBALS['__template']->commentDB['data'],11,2);
	$i=substr($GLOBALS['__template']->commentDB['data'],14,2);
	$s=substr($GLOBALS['__template']->commentDB['data'],17,2);
	$d=substr($GLOBALS['__template']->commentDB['data'],8,2);
	$m=substr($GLOBALS['__template']->commentDB['data'],5,2);
	$Y=substr($GLOBALS['__template']->commentDB['data'],0,4);
	return strftime($dateformat,mktime($H,$i,$s,$m,$d,$Y));
	}
function kGetNewsCommentAuthor() {
	return $GLOBALS['__template']->commentDB['autore'];
	}
function kGetNewsCommentEmail() {
	return $GLOBALS['__template']->commentDB['email'];
	}
function kGetNewsCommentIP() {
	return $GLOBALS['__template']->commentDB['ip'];
	}
function kGetNewsCommentText() {
	return $GLOBALS['__template']->commentDB['testo'];
	}
function kPrintNewsCommentsForm() {
	if(kGetVar('news-commenti',1)=='s') {
		echo $GLOBALS['__template']->getSubtemplate('news_commentform');
		}
	}
function kPrintNews($dir=false) {
	$cat=$GLOBALS['__subdir__'];
	$GLOBALS['__news']->setCatByDir($cat);
	kSetNewsByDir($dir);
	echo $GLOBALS['__template']->getSubtemplate('news_page');
	}
function kSetNewsCategories($cat) {
	$GLOBALS['__news']->setCatByDir($cat);
	}
function kGetNewsPrevious($orderby=false,$dir=false,$limit=1,$cat="*") {
	if($dir==false) $dir=$GLOBALS['__subsubdir__'];
	if($cat==false) $cat=$GLOBALS['__subdir__'];
	if($orderby==false) $orderby=kGetVar('news-order',1);
	if($orderby=="") $orderby="data";
	$GLOBALS['__news']->setCatByDir($cat);
	return $GLOBALS['__news']->getPrevious($orderby,$dir,$limit,$cat);
	}
function kGetNewsNext($orderby=false,$dir=false,$limit=1,$cat="*") {
	if($dir==false) $dir=$GLOBALS['__subsubdir__'];
	if($cat==false) $cat=$GLOBALS['__subdir__'];
	if($orderby==false) $orderby=kGetVar('news-order',1);
	if($orderby=="") $orderby="data";
	$GLOBALS['__news']->setCatByDir($cat);
	return $GLOBALS['__news']->getNext($orderby,$dir,$limit,$cat);
	}
function kGetNewsList($vars=false,$page=false,$limit=false,$start=false,$conditions="",$options="",$orderby="",$ll=null) {
	if(!is_array($vars)) {
		$vars=array('category'=>$vars);
		$vars['page']=$page;
		$vars['limit']=$limit;
		$vars['offset']=$start;
		$vars['conditions']=$conditions;
		$vars['options']=$options;
		$vars['orderby']=$orderby;
		$vars['ll']=$ll;
		}
	if(!isset($vars['category'])||$vars['category']==false) $vars['category']=$GLOBALS['__subdir__'];
	$GLOBALS['__news']->setCatByDir($vars['category']);
	if(!isset($vars['page'])||$vars['page']==false) $vars['page']=1;
	if(!isset($vars['limit'])||$vars['limit']==false) $vars['limit']=kGetVar('news',1);
	if(!isset($vars['offset'])||$vars['offset']==false) $vars['offset']=0;
	$vars['offset']+=($vars['page']-1)*$vars['limit'];
	return $GLOBALS['__news']->getList($vars);
	}
function kGetNewsQuickList($vars=false,$page=false,$limit=false,$start=false,$conditions="",$options="",$orderby="",$ll=null) {
	if(!is_array($vars)) {
		$vars=array('category'=>$vars);
		$vars['page']=$page;
		$vars['limit']=$limit;
		$vars['offset']=$start;
		$vars['conditions']=$conditions;
		$vars['options']=$options;
		$vars['orderby']=$orderby;
		$vars['ll']=$ll;
		}
	if(!isset($vars['category'])||$vars['category']==false) $vars['category']=$GLOBALS['__subdir__'];
	$GLOBALS['__news']->setCatByDir($vars['category']);
	if(!isset($vars['page'])||$vars['page']==false) $vars['page']=1;
	if(!isset($vars['limit'])||$vars['limit']==false) $vars['limit']=kGetVar('news',1);
	if(!isset($vars['offset'])||$vars['offset']==false) $vars['offset']=0;
	$vars['offset']+=($vars['page']-1)*$vars['limit'];
	return $GLOBALS['__news']->getQuickList($vars);
	}
function kPrintNewsList($vars=false,$page=false,$limit=false,$start=false,$conditions="",$options="",$home=true) {
	if(!is_array($vars)) {
		$vars=array('category'=>$vars);
		$vars['page']=$page;
		$vars['limit']=$limit;
		$vars['offset']=$start;
		$vars['conditions']=$conditions;
		$vars['options']=$options;
		$vars['orderby']=$orderby;
		$vars['home']=$home;
		}
	
	foreach(kGetNewsList($vars) as $n) {
		kSetNewsByDir($n['dir']);
		echo $GLOBALS['__template']->getSubtemplate('news_preview');
		}
	}
function kPrintNewsCalendar($cat=false,$yyyy=false,$mm=false,$mode="") {
	if($mode!="") $mode="_".$mode;
	if($yyyy==false) $yyyy=date("Y");
	if($mm==false) $mm=date("m");
	if(!preg_match("/\d{4}.\d{2}(.\d{2})?/",$cat)) $GLOBALS['__news']->setCatByDir($cat);
	$output=array();
	$output['yyyy']=$yyyy;
	$output['mm']=$mm;
	$output['daysOffset']=date("w",mktime(0,0,0,$mm,1,$yyyy));
	if($output['daysOffset']==0) $output['daysOffset']=7;
	$output['daysInMonth']=date("t",mktime(0,0,0,$mm,1,$yyyy));
	$GLOBALS['__template']->newsDB=$output;
	echo $GLOBALS['__template']->getSubtemplate('news_calendar'.$mode);
	}
function kGetNewsArchiveTitle() {
	$type=$GLOBALS['__template']->pageDB;
	if($GLOBALS['__dir__']==kGetVar('dir_news')) {
		return $GLOBALS['__news']->getCatByDir($GLOBALS['__subdir__']);
		}
	return false;
	}
function kGetNewsArchiveMetaTitle() {
	$type=$GLOBALS['__template']->pageDB;
	if($type=="day") return kGetNewsDate("%d");
	if($type=="month") return kGetNewsDate("%B");
	if($type=="year") return kGetNewsDate("%Y");
	}
function kGetNewsArchive($type="month",$cat=false,$count=false) {
	$output=array();
	if($cat==false) $cat=$GLOBALS['__subdir__'];
	if($cat=="*") $GLOBALS['__news']->setCatByDir();
	else $GLOBALS['__news']->setCatByDir($cat);
	if($type=="categories") {
		$output=$GLOBALS['__news']->getCategories($count);
		}
	else {
		$orderby=kGetVar('news-order',1);
		if($orderby=="") $orderby="pubblica DESC";
		$dataRef=preg_replace('/ desc$/i','',$orderby);
		
		$vars=array();
		$vars['options']="GROUP BY year(".$dataRef.")".($type=="month"||$type=="day"?",month(".$dataRef.")":"").($type=="day"?",day(".$dataRef.")":"");
		foreach($GLOBALS['__news']->getList($vars) as $n) {
			$output[]=$n;
			}
		}
	return $output;
	}
function kPrintNewsArchive($type="month",$cat=false) {
	$GLOBALS['__template']->pageDB=$type;
	foreach(kGetNewsArchive($type,$cat) as $n) {
		kSetNewsByDir($n['dir']);
		echo $GLOBALS['__template']->getSubtemplate('news_archive');
		}
	}
function kGetLatestNews($vars=false,$limit=1,$home=true) {
	if(!is_array($vars)) {
		$cat=$vars;
		$vars=array();
		$vars['category']=$cat;
		$vars['limit']=$limit;
		$vars['home']=$home;
		}
	$conditions="";
	if(isset($vars['home'])&&(($vars['home']==true&&kIsHome())||(string)$vars['home']=="force")) $vars['conditions']="`home`='s'";
	if(isset($vars['category'])&&$vars['category']==false&&$GLOBALS['__subdir__']!="") $vars['category']=$GLOBALS['__subdir__'];
	if(!isset($vars['limit'])||$vars['limit']==false) $limit=kGetVar('news',1);
	if(!isset($vars['category'])) $vars['category']="";
	$GLOBALS['__news']->setCatByDir($vars['category']);
	
	if(!isset($vars['from'])) $vars['from']=0;
	if(!isset($vars['limit'])) $vars['limit']=1;
	return $GLOBALS['__news']->getList($vars);
	}
function kGetUpcomingNews($vars=false) {
	$dateRef=kGetVar('news-order',1);
	$dateRef=str_replace(" DESC","",$dateRef);
	$dateRef=str_replace(" ASC","",$dateRef);
	if(!isset($vars['category'])) $vars['category']="*";
	$vars['conditions']="`".$dateRef."`>NOW()";
	if(isset($vars['home'])&&(($vars['home']==true&&kIsHome())||(string)$vars['home']=="force")) $vars['conditions'].=" AND `home`='s'";
	if((!isset($vars['category'])||$vars['category']==false)&&$GLOBALS['__subdir__']!="") $vars['category']=$GLOBALS['__subdir__'];
	if(!isset($vars['category'])) $vars['category']="*";
	if(!isset($vars['limit'])||$vars['limit']==false) $limit=kGetVar('news',1);
	$GLOBALS['__news']->setCatByDir($vars['category']);
	$vars['orderby']=$dateRef.' ASC';

	if(!isset($vars['from'])) $vars['from']=0;
	if(!isset($vars['limit'])) $vars['limit']=1;
	return $GLOBALS['__news']->getList($vars);
	}
function kGetNewsCount($conditions="") {
	return $GLOBALS['__news']->countNews($conditions);
	}
function kGetNewsCategoryById($idcat) {
	return $GLOBALS['__news']->getCatById($idcat);
	}
function kGetNewsCategoryByDir($dir=null) {
	if($dir==null&&kIsNews()) $dir=$GLOBALS['__subdir__'];
	return $GLOBALS['__news']->getCatByDir($dir);
	}
function kGetNewsCategories() {
	return $GLOBALS['__news']->getCategories();
	}

/* photogalleries */
function kHavePhotogallery() {
	if($GLOBALS['__dir__']==trim(kGetVar('dir_photogallery',1),"/")&&$GLOBALS['__subdir__']!="") {
		return $GLOBALS['__photogallery']->photogalleryExists($GLOBALS['__subsubdir__']);
		}
	else return false;
	}
function kGetPhotogalleryList($limit=false,$start=false,$conditions="",$options="") {
	if($limit==false) $limit=9999;
	if($start==false) $start=0;
	return $GLOBALS['__photogallery']->getList($start,$limit,$conditions,$options);
	}
function kPrintPhotogalleryList($limit=false,$start=false,$conditions="",$options="") {
	if($limit==false) $limit=9999;
	if($start==false) $start=0;
	foreach($GLOBALS['__photogallery']->getList($start,$limit,$conditions,$options) as $n) {
		kSetPhotogalleryByDir($n['dir']);
		if(isset($n['imgs'][0])) $GLOBALS['__template']->imgDB=$n['imgs'][0];
		else $GLOBALS['__template']->imgDB=array();
		echo $GLOBALS['__template']->getSubtemplate('photogallery_preview');
		}
	}
function kSetPhotogalleryByDir($dir=false) {
	$GLOBALS['__photogallery']->setGalleryByDir($dir);
	}
function kGetPhotogallery($dir=false) {
	return $GLOBALS['__photogallery']->getGalleryByDir($dir);
	}
function kGetPhotogalleryId() {
	return $GLOBALS['__photogallery']->getGalleryVar('idphg');
	}
function kGetPhotogalleryDir() {
	return $GLOBALS['__photogallery']->getGalleryVar('dir');
	}
function kGetPhotogalleryTitle() {
	return $GLOBALS['__photogallery']->getGalleryVar('titolo');
	}
function kGetPhotogalleryText() {
	return $GLOBALS['__photogallery']->getGalleryVar('testo');
	}
function kGetPhotogalleryPermalink($type="month") {
	return $GLOBALS['__photogallery']->getGalleryVar('permalink');
	}
function kGetPhotogalleryDate($format=false) {
	if($format==false) $format=kGetVar('timezone',2);
	$date=$GLOBALS['__photogallery']->getGalleryVar('data');
	return strftime($format,mktime(substr($date,11,2),substr($date,14,2),substr($date,17,2),substr($date,5,2),substr($date,8,2),substr($date,0,4)));
	}
function kGetPhotogalleryImages() {
	return $GLOBALS['__photogallery']->getGalleryVar('imgs');
	}
function kPrintPhotogallery($dir=false) {
	kSetPhotogalleryByDir($dir);
	echo $GLOBALS['__template']->getSubtemplate('photogallery_page');
	}
function kGetPhotogalleryMetadata($param=false,$dir=false,$ll=false) {
	if($dir==false) $dir=trim($GLOBALS['__dir__'].'/'.$GLOBALS['__subdir__'].'/'.$GLOBALS['__subsubdir__']," /");
	if($ll==false) $ll=LANG;
	$metadata=$GLOBALS['__photogallery']->getMetadata($dir,$ll);
	if($param!=false) {
		if(isset($metadata[$param])) return $metadata[$param];
		else return "";
		}
	return $metadata;
	}
function kGetPhotogalleryComments($id=0) {
	if($id==0) {
		$id=kGetPhotogalleryId();
		}
	return $GLOBALS['__template']->getComments(array("table"=>TABLE_PHOTOGALLERY,"id"=>$id));
	}
function kPrintPhotogalleryComments() {
	foreach(kGetPhotogalleryComments() as $n) {
		kSetComment($n);
		echo $GLOBALS['__template']->getSubtemplate('photogallery_comment');
		}
	}
function kGetPhotogalleryCommentsCount() {
	return count($GLOBALS['__photogallery']->getGalleryVar('commenti'));
	}
function kPrintPhotogalleryCommentsForm() {
	if(kGetVar('photogallery-commenti',1)=='s') {
		echo $GLOBALS['__template']->getSubtemplate('photogallery_commentform');
		}
	}
	
/* banners */
function kGetBannerList($vars=null,$from=-1,$limit=null,$orderby=false) {
	if(!is_array($vars)) {
		$vars=array("category"=>$vars);
		$vars['from']=$from;
		$vars['limit']=$limit;
		$vars['orderby']=$orderby;
		}
	return $GLOBALS['__banner']->getBanners($vars);
	}
function kSetBanner($bannerDB) {
	return $GLOBALS['__banner']->setBanner($bannerDB);
	}
function kGetBannerHref() {
	return $GLOBALS['__banner']->getVar('url');
	}
function kGetBannerTitle() {
	return $GLOBALS['__banner']->getVar('title');
	}
function kGetBannerDescription() {
	return $GLOBALS['__banner']->getVar('description');
	}
function kGetBannerViews() {
	return $GLOBALS['__banner']->getVar('views');
	}
function kGetBannerURL() {
	return $GLOBALS['__banner']->getVar('permalink');
	}
function kGetBannerWidth() {
	return $GLOBALS['__banner']->getVar('width');
	}
function kGetBannerHeight() {
	return $GLOBALS['__banner']->getVar('height');
	}
function kGetBannerAlt() {
	return $GLOBALS['__banner']->getVar('title');
	}
function kGetBannerId() {
	return $GLOBALS['__banner']->getVar('idbanner');
	}

/* users */
function kHaveUser() {
	$username=trim($GLOBALS['__subdir__']);
	if($username!="") {
		if($GLOBALS['__users']->userExists($username)) return true;
		}
	return false;
	}
function kGetUsersList() {
	return $GLOBALS['__users']->getUsers();
	}
function kPrintUser($username=false) {
	kSetUserByUsername($username);
	echo $GLOBALS['__template']->getSubtemplate('user_sheet');
	}
function kGetUserByUsername($username=false) {
	$user=$GLOBALS['__users']->kGetUserByUsername($username);
	return $user;
	}
function kSetUserByUsername($username=false) {
	$GLOBALS['__users']->kSetUserByUsername($username);
	$user=kGetUserByUsername($username);
	$GLOBALS['__template']->imgDB=$user['imgs'][0];
	}
function kGetUserName() {
	return $GLOBALS['__users']->userDB['name'];
	}
function kGetUserEmail() {
	return $GLOBALS['__users']->userDB['email'];
	}
function kGetUserPreview() {
	return $GLOBALS['__users']->userDB['summary'];
	}
function kGetUserInfo() {
	return $GLOBALS['__users']->userDB['description'];
	}
function kGetUserPermalink() {
	return $GLOBALS['__users']->userDB['permalink'];
	}


/* members */
function kGetMemberList($affiliation=false) {
	if($affiliation!=false) $conditions="affiliation='".b3_htmlize($affiliation,true,"")."'";
	else $conditions=false;
	return $GLOBALS['__members']->getList(false,$conditions);
	}
function kGetMemberByUsername($username,$affiliation=false) {
	if($affiliation!=false) $conditions="affiliation='".b3_htmlize($affiliation,true,"")."'";
	else $conditions=false;
	return $GLOBALS['__members']->getByUsername($username,false,$conditions);
	}
function kGetMemberById($idmember,$affiliation=false) {
	if($affiliation!=false) $conditions="affiliation='".b3_htmlize($affiliation,true,"")."'";
	else $conditions=false;
	return $GLOBALS['__members']->getById($idmember,false,$conditions);
	}
function kMemberIsLogged() {
	return $GLOBALS['__members']->isLogged();
	}
function kMemberLogIn($username,$password) {
	return $GLOBALS['__members']->logIn($username,$password);
	}
function kMemberLogOut() {
	return $GLOBALS['__members']->logOut();
	}
function kPrintLogInForm() {
	echo $GLOBALS['__template']->getSubtemplate('loginform');
	}
function kPrintMemberRegistrationForm() {
	echo $GLOBALS['__template']->getSubtemplate('registrationform');
	}
function kGetMemberId() {
	return $GLOBALS['__members']->getVar('idmember');
	}
function kGetMemberUsername() {
	return $GLOBALS['__members']->getVar('username');
	}
function kGetMemberPassword() {
	return $GLOBALS['__members']->getVar('password');
	}
function kGetMemberName() {
	return $GLOBALS['__members']->getVar('name');
	}
function kGetMemberEmail() {
	return $GLOBALS['__members']->getVar('email');
	}
function kGetMemberPhotogallery() {
	return $GLOBALS['__members']->getVar('imgs');
	}
function kGetMemberDocuments() {
	return $GLOBALS['__members']->getVar('docs');
	}
function kGetMemberMetadata($param) {
	return $GLOBALS['__members']->getMetadata($param);
	}
function kMemberRegister($username,$password=false,$name,$email,$affiliation="",$expire=false) {
	if($expire==false) $expire="0000-00-00 00:00:00";
	return $GLOBALS['__members']->register($username,$password,$name,$email,$affiliation,$expire);
	}
function kMemberReplaceMetadata($username,$param,$value,$affiliation=false) {
	return $GLOBALS['__members']->replaceMetadata($username,$param,$value,$affiliation);
	}
function kMemberExists($username,$affiliation="") {
	$GLOBALS['__members']->memberExists($username,$affiliation);	
	}
function kMemberPasswordReset($username) {
	return $GLOBALS['__members']->passwordReset($username);
	}


/* shop */
function kHaveShop() {
	return $GLOBALS['__shop']->shopExists();
	}
function kHaveShopItem() {
	return $GLOBALS['__shop']->shopItemExists();
	}
function kHaveManufacturer() {
	return $GLOBALS['__shop']->shopManufacturerExists();
	}
function kSetShopTemplate($dir=false) {
	$n=$GLOBALS['__shop']->getShopTemplate($dir);
	$GLOBALS['__template']->setTemplate($n['template']);
	$GLOBALS['__template']->setLayout($n['layout']);
	}
function kGetShopCurrentCategoryName() {
	if(!$GLOBALS['__shop']->shopExists()) return false;
	$cat=$GLOBALS['__shop']->getCategoryByDir($GLOBALS['__subdir__']);
	if(isset($cat['categoria'])) return $cat['categoria'];
	return false;
	}
function kGetShopCategoryByDir($dir=null) {
	if($dir==null&&kIsShop()) $dir=$GLOBALS['__subdir__'];
	return $GLOBALS['__template']->getCategory(array("table"=>TABLE_SHOP_ITEMS,"dir"=>$dir));
	}
function kGetShopParentCategories($dir=null) {
	if($dir==null) $dir=$GLOBALS['__subdir__'];
	return $GLOBALS['__template']->getParentCategories(array("table"=>TABLE_SHOP_ITEMS,"dir"=>$dir));
	}
function kGetShopCountries($zone=false) {
	return $GLOBALS['__shop']->getCountries($zone);
	}
function kGetShopPayPalBusinessId() {
	return $GLOBALS['__shop']->getPayPalBusinessId();
	}
function kPrintShopPayPalForm() {
	echo $GLOBALS['__template']->getSubtemplate('paypalform');
	}
function kGetShopVirtualPayBusinessId() {
	$v=kGetVar('shop-virtualpay',1);
	return substr($v,0,strpos($v,"|"));
	}
function kGetShopVirtualPayABI() {
	$v=kGetVar('shop-virtualpay',1);
	return substr($v,strpos($v,"|")+1);
	}
function kGetShopVirtualPayKEY() {
	$v=kGetVar('shop-virtualpay',2);
	return $v;
	}
function kPrintShopVirtualPayForm() {
	echo $GLOBALS['__template']->getSubtemplate('virtualpayform');
	}
function kGetShopPagOnlineBusinessId() {
	$v=kGetVar('shop-pagonline',1);
	return substr($v,0,strpos($v,"|"));
	}
function kGetShopPagOnlinePassword() {
	$v=kGetVar('shop-pagonline',1);
	return substr($v,strpos($v,"|")+1);
	}
function kGetShopPagOnlineKEY() {
	$v=kGetVar('shop-pagonline',2);
	return $v;
	}
function kPrintShopPagOnlineForm() {
	echo $GLOBALS['__template']->getSubtemplate('pagonlineform');
	}
function kGetShopPayPalReturnPage($success=true) {
	if($success==true) return kGetVar('shop-paypal-return',1);
	else return kGetVar('shop-paypal-return',2);
	}
function kGetShopPaymentById($idspay) {
	return $GLOBALS['__shop']->getPaymentById($idspay);
	}
function kGetShopPaymentsByZone($zone) {
	return $GLOBALS['__shop']->getPaymentsByZone($zone);
	}
function kGetShopPaymentsByCountryCode($ll) {
	return $GLOBALS['__shop']->getPaymentsByCountryCode($ll);
	}
function kSetShopPaymentById($idspay) {
	return $GLOBALS['__shop']->setPaymentById($idspay);
	}
function kGetShopZoneByCountryCode($ll) {
	return $GLOBALS['__shop']->getZoneByCountry($ll);
	}
function kGetShopDelivererById($iddel) {
	return $GLOBALS['__shop']->getDelivererById($iddel);
	}
function kGetShopDeliverersByCountryCode($ll) {
	return $GLOBALS['__shop']->getDeliverersByCountryCode($ll);
	}
function kGetShopDeliverersByZone($zone) {
	return $GLOBALS['__shop']->getDeliverersByZone($zone);
	}
function kSetShopDelivererById($iddel) {
	return $GLOBALS['__shop']->setDelivererById($iddel);
	}
function kGetShopDelivererPriceByKg($kg,$zone=1) {
	return $GLOBALS['__shop']->getDelivererPriceByKg($kg,$zone);
	}
function kGetShopItemsCount($vars) {
	return $GLOBALS['__shop']->countItems($vars);
	}
function kGetShopItemList($vars=0,$limit=10,$conditions="",$options="",$orderby="",$ll=null) {
	if(!is_array($vars)) {
		$vars=array("from"=>$vars);
		$vars['limit']=$limit;
		$vars['conditions']=$conditions;
		$vars['options']=$options;
		$vars['orderby']=$orderby;
		$vars['ll']=$ll;
		}
	return $GLOBALS['__shop']->getItemList($vars);
	}
function kGetShopItemQuickList($vars=0,$limit=10,$conditions="",$options="",$orderby="",$ll=null) {
	if(!is_array($vars)) {
		$vars=array("from"=>$vars);
		$vars['limit']=$limit;
		$vars['conditions']=$conditions;
		$vars['options']=$options;
		$vars['orderby']=$orderby;
		$vars['ll']=$ll;
		}
	return $GLOBALS['__shop']->getItemQuickList($vars);
	}
function kSetShopCartVar($param,$value) {
	return $GLOBALS['__shop']->setCartVar($param,$value);
	}
function kGetShopCartVar($param) {
	return $GLOBALS['__shop']->getCartVar($param);
	}
function kSetShopItemById($idsitem) {
	$GLOBALS['__shop']->setItemById($idsitem);
	}
function kSetShopItemByDir($dir=false) {
	if($dir==false) $dir=$GLOBALS['__subsubdir__'];
	$GLOBALS['__shop']->setItemByDir($dir);
	}
function kGetShopItem($dir=false) {
	if($dir==false) $dir=$GLOBALS['__subsubdir__'];
	return $GLOBALS['__shop']->getItemByDir($dir);
	}
function kGetShopItemById($idsitem) {
	return $GLOBALS['__shop']->getItemById($idsitem);
	}
function kGetShopItemId() {
	return $GLOBALS['__shop']->getItemVar('idsitem');
	}
function kGetShopItemDir() {
	return $GLOBALS['__shop']->getItemVar('dir');
	}
function kGetShopItemCode() {
	return $GLOBALS['__shop']->getItemVar('productcode');
	}
function kGetShopItemTitle() {
	return $GLOBALS['__shop']->getItemVar('titolo');
	}
function kGetShopItemSubtitle() {
	return $GLOBALS['__shop']->getItemVar('sottotitolo');
	}
function kGetShopItemPreview() {
	return $GLOBALS['__shop']->getItemVar('anteprima');
	}
function kGetShopItemText() {
	return $GLOBALS['__shop']->getItemVar('testo');
	}
function kGetShopItemFeaturedImage() {
	return $GLOBALS['__shop']->getItemVar('featuredimage');
	}
function kGetShopItemFullPrice() {
	return $GLOBALS['__shop']->getItemVar('prezzo');
	}
function kGetShopItemPrice($vars=array()) {
	/*
	allowed vars
	- idsitem = id of the item
	- variations = array with the id of the active variations
	*/
	return $GLOBALS['__shop']->getItemPrice($vars);
	}
function kGetShopCurrency($mode="") {
	if($mode=="symbol") return kGetVar('shop-currency',2);
	else return kGetVar('shop-currency',1);
	}
function kGetShopItemPriceDiscounted() {
	return $GLOBALS['__shop']->getItemVar('scontato');
	}
function kGetShopItemQuantity() {
	return $GLOBALS['__shop']->getItemVar('qta');
	}
function kGetShopItemWeight() {
	return $GLOBALS['__shop']->getItemVar('weight');
	}
function kGetShopItemPhotogallery() {
	return $GLOBALS['__shop']->getItemVar('imgs');
	}
function kGetShopItemDocuments() {
	return $GLOBALS['__shop']->getItemVar('docs');
	}
function kGetShopItemPermalink() {
	return $GLOBALS['__shop']->getItemVar('permalink');
	}
function kGetShopItemDateCreated() {
	return $GLOBALS['__shop']->getItemVar('public');
	}
function kGetShopItemDateModified() {
	return $GLOBALS['__shop']->getItemVar('modified');
	}
function kGetShopItemDate($format=false,$d=false) {
	if($format==false) $format=kGetVar('timezone',2);
	if($d=='creation') $date=$GLOBALS['__shop']->getItemVar('created');
	elseif($d=='expiration') $date=$GLOBALS['__shop']->getItemVar('expired');
	elseif($d=='publish') $date=$GLOBALS['__shop']->getItemVar('public');
	else $date=$GLOBALS['__shop']->getItemVar('public');
	return strftime($format,mktime(substr($date,11,2),substr($date,14,2),substr($date,17,2),substr($date,5,2),substr($date,8,2),substr($date,0,4)));
	}
function kGetShopItemCategories() {
	return $GLOBALS['__shop']->getItemVar('categories');
	}
function kGetShopItemEmbeddedImages() {
	return $GLOBALS['__shop']->getItemVar('embeddedimgs');
	}
function kGetShopItemEmbeddedDocuments() {
	return $GLOBALS['__shop']->getItemVar('embeddeddocs');
	}
function kGetShopItemEmbeddedMedias() {
	return $GLOBALS['__shop']->getItemVar('embeddedmedias');
	}
function kGetShopItemMetadata($param=false,$dir=false,$ll=false) {
	if($dir==false) $dir=trim($GLOBALS['__dir__'].'/'.$GLOBALS['__subdir__'].'/'.$GLOBALS['__subsubdir__']," /");
	if($ll==false) $ll=LANG;
	$metadata=$GLOBALS['__shop']->getMetadata($dir,$ll);
	if($param!=false) {
		if(isset($metadata[$param])) return $metadata[$param];
		else return false;
		}
	return $metadata;
	}
function kGetShopItemCustomFields($vars=array()) {
	/*
	allowed values:
	- name : filter by name
	- id : filter by idsfield
	if no params was passed, return a list of all custom fields of the selected item
	*/
	return $GLOBALS['__shop']->getItemCustomFields($vars);
	}
function kGetShopItemVariations($vars=array()) {
	/*
	allowed values:
	- collection : filter by collection
	- id : filter by idsvar
	if no params was passed, return a list of all variations of the selected item
	*/
	return $GLOBALS['__shop']->getItemVariations($vars);
	}
function kPrintShopItemComments() {
	foreach(kGetShopItemComments() as $n) {
		$GLOBALS['__template']->commentDB=$n;
		echo $GLOBALS['__template']->getSubtemplate('shop_comment');
		}
	}
function kGetShopItemComments() {
	return $GLOBALS['__shop']->getItemVar('commenti');
	}
function kPrintShopItemCommentsForm() {
	if(kGetVar('shop-commenti',1)=='s') {
		echo $GLOBALS['__template']->getSubtemplate('shop_commentform');
		}
	}
function kPrintShopItem($dir=false) {
	$cat=$GLOBALS['__subdir__'];
	$GLOBALS['__shop']->setCatByDir($cat);
	kSetShopItemByDir($dir);
	echo $GLOBALS['__template']->getSubtemplate('shop_item_page');
	}
function kGetShopCart() {
	$output=array("items"=>array(),"totalprice"=>0,"totalweight"=>0,"itemsnumber"=>0);
	$output['items']=$GLOBALS['__shop']->getCart();
	foreach($output['items'] as $item) {
		$output['totalprice']+=floatval($item['realprice'])*$item['qty'];
		$output['totalweight']+=floatval($item['weight'])*$item['qty'];
		$output['itemsnumber']+=$item['qty'];
		}
	return $output;
	}
function kGetShopCartItemsCount($vars=array()) {
	/* input vars
	[idsitem] -> the id of the item (optional)
	[variations] -> an array of variation ids (optional)
	*/
	return $GLOBALS['__shop']->getCartItemsCount($vars);
	}
function kGetShopCartItemsAmount() {
	return $GLOBALS['__shop']->getCartItemsPrice();
	}
function kGetShopCartTotalAmount($vars,$iddel=null,$country=null) {
	/* input vars:
	idspay -> id of payment system
	iddel -> id of deliverers (courrier)
	country -> 2 digits country code
	coupons -> array with codes
	*/
	if(!is_array($vars)) {
		$vars=array("idspay"=>$vars);
		$vars['iddel']=$iddel;
		$vars['country']=$country;
		}
	return $GLOBALS['__shop']->getCartTotalPrice($vars);
	}
function kShopCouponsMarkAsUsed($coupons) {
	return $GLOBALS['__shop']->couponsMarkAsUsed($coupons);
	}
function kGetShopCartShippingPrice($iddel=false,$country=false) {
	return $GLOBALS['__shop']->getCartShippingPrice($iddel,$country);
	}
function kGetShopCartPaymentPrice($price=false,$idspay=false,$iddel=false,$country=false) {
	return $GLOBALS['__shop']->getCartPaymentPrice($price,$idspay,$iddel,$country);
	}
function kShopAddToCart($idsitem,$qty=1,$variations=array(),$customvariations=array()) {
	if(intval($idsitem)<=0) return false;
	$GLOBALS['__shop']->addItemToCart(intval($idsitem),$qty,$variations,$customvariations);
	}
function kShopRemoveFromCart($idsitem,$qty=1,$variations=array(),$customvariations=array()) {
	$GLOBALS['__shop']->removeItemFromCart($idsitem,$qty,$variations,$customvariations);
	}
function kShopIncreaseCartItem($uid,$qty=1) {
	$GLOBALS['__shop']->addItemToCartByUniqueID($uid,$qty);
	}
function kShopDecreaseCartItem($uid,$qty=1) {
	$GLOBALS['__shop']->removeItemFromCartByUniqueID($uid,$qty);
	}
function kShopEmptyCart() {
	$GLOBALS['__shop']->emptyCart();
	}
function kGetShopCartCoupon($vars=array()) {
	/* input vars:
	code -> coupon code
	*/
	return $GLOBALS['__shop']->getCouponByCode($vars['code']);
	}
function kShopCheckOrderValidity($vars) {
	return $GLOBALS['__shop']->checkOrderValidity($vars);
	}
function kShopSaveOrder($vars,$emptycart=true) {
	return $GLOBALS['__shop']->saveOrder($vars,$emptycart);
	}
function kGetShopOrderByNumber($uid) {
	return $GLOBALS['__shop']->getOrderByNumber($uid);	
	}
function kSetShopOrderByNumber($uid) {
	return $GLOBALS['__shop']->setOrderByNumber($uid);	
	}
function kGetShopOrderVar($param) {
	return $GLOBALS['__shop']->getOrderVar($param);
	}
function kGetShopOrderId() {
	return $GLOBALS['__shop']->getOrderVar('idord');	
	}
function kGetShopOrderNumber() {
	return $GLOBALS['__shop']->getOrderVar('uid');	
	}
function kGetShopOrderTotalAmount() {
	return $GLOBALS['__shop']->getOrderVar('totalprice');	
	}
function kGetShopCategories($vars=array()) {
	$vars['table']=TABLE_SHOP_ITEMS;
	return $GLOBALS['__template']->getCategories($vars);
	}
function kGetShopOrders($vars=array()) {
	/*
	- idmember : filter orders by the id of the member 
	- orderby : how to order outputs
	*/
	return $GLOBALS['__shop']->getOrders($vars);
	}

/*****************/
/* manufacturers */
/*****************/

function kGetManufacturersList($vars=array())
{
	if(!isset($vars['ll'])) $vars['ll']=LANG;
	return $GLOBALS['__shop']->getManufacturersList($vars);
}

function kSetManufacturer($dir="",$ll=false)
{
	if($ll==false) $ll=LANG;
	if($dir==""&&kHaveManufacturer()) $dir=$GLOBALS['__subsubdir__'];
	$GLOBALS['__shop']->setManufacturer($dir,$ll);
}

function kGetManufacturerId()
{
	return $GLOBALS['__shop']->getManufacturerVar('idsman');
}
function kGetManufacturerPermalink()
{
	return $GLOBALS['__shop']->getManufacturerVar('permalink');
}
function kGetManufacturerDir()
{
	return $GLOBALS['__shop']->getManufacturerVar('dir');
}
function kGetManufacturerName()
{
	return $GLOBALS['__shop']->getManufacturerVar('name');
}
function kGetManufacturerSubtitle()
{
	return $GLOBALS['__shop']->getManufacturerVar('subtitle');
}
function kGetManufacturerPreview()
{
	return $GLOBALS['__shop']->getManufacturerVar('preview');
}
function kGetManufacturerDescription()
{
	return $GLOBALS['__shop']->getManufacturerVar('description');
}
function kGetManufacturerPhotogallery()
{
	return $GLOBALS['__shop']->getManufacturerVar('imgs');
}
function kGetManufacturerDocuments()
{
	return $GLOBALS['__shop']->getManufacturerVar('docs');
}
function kGetManufacturerFeaturedImage()
{
	return $GLOBALS['__shop']->getManufacturerVar('featuredimage');
}
function kGetManufacturerMetadata($param=false,$dir=false,$ll=false)
{
	if($dir==false) $dir=trim($GLOBALS['__dir__'].'/'.$GLOBALS['__subdir__'].'/'.$GLOBALS['__subsubdir__']," /");
	if($ll==false) $ll=LANG;
	$metadata=$GLOBALS['__shop']->getManufacturerMetadata($dir,$ll);
	if($param!=false)
	{
		if(isset($metadata[$param])) return $metadata[$param];
		else return false;
	}
	return $metadata;
}


	
/* private */
function kPrivateDirIsWritable($dir=-1) {
	if($dir==-1) $dir=$GLOBALS['__subdir__'].'/'.$GLOBALS['__subsubdir__'];
	return $GLOBALS['__private']->dirIsWritable($dir);
	}
function kPrivateDirExists($dir=-1) {
	if($dir==-1) $dir=$GLOBALS['__subdir__'].'/'.$GLOBALS['__subsubdir__'];
	return $GLOBALS['__private']->dirExists($dir);
	}
function kGetPrivateFileList($dir) {
	return $GLOBALS['__private']->getDirContent($dir);
	}
function kPrivateIsFile($dir="") {
	if($dir=="") $dir=trim($GLOBALS['__subdir__'].'/'.$GLOBALS['__subsubdir__']," ./");
	return $GLOBALS['__private']->isFile($dir);
	}
function kPrivateForceDownload($dir="") {
	if($dir=="") $dir=trim($GLOBALS['__subdir__'].'/'.$GLOBALS['__subsubdir__']," ./");
	return $GLOBALS['__private']->forceDownload($dir);
	}
function kPrivateFileIsDownloadable($filename="") {
	if($filename=="") $filename=trim($GLOBALS['__subdir__'].'/'.$GLOBALS['__subsubdir__']," ./");
	return $GLOBALS['__private']->canIDownload($filename);
	}
function kGetUploadHandlerURL() {
	return BASEDIR.'inc/uploadHandler.php';
	}
?>
