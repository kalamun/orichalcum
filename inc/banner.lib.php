<?
/* (c) Kalamun.org - GNU/GPL 3 */

/* PAGINE */
class kBanners {
	protected $inited;
	protected $bannerDB,$kDocuments;

	public function __construct() {
		$this->inited=false;
		}

	public function init() {
		$this->inited=true;
		require_once($_SERVER['DOCUMENT_ROOT'].BASEDIR."admin/inc/connect.inc.php");
		require_once($_SERVER['DOCUMENT_ROOT'].BASEDIR."admin/inc/main.lib.php");
		require_once($_SERVER['DOCUMENT_ROOT'].BASEDIR."inc/documents.lib.php");
		require_once($_SERVER['DOCUMENT_ROOT'].BASEDIR."inc/kalamun.lib.php");
		$this->kDocuments=new kDocuments();
		$this->bannerDB=array();
		}

	function getCategories() {
		if(!$this->inited) $this->init();
		$cat=array();
		$query="SELECT * FROM `".TABLE_CATEGORIE."` WHERE `tabella`='".TABLE_BANNER."' AND `ll`='".mysql_real_escape_string(LANG)."' ORDER BY `ordine`";
		$results=mysql_query($query);
		while($row=mysql_fetch_array($results)) {
			$cat[]=$row;
			}
		return $cat;
		}

	function getBanners($vars=array()) {
		if(!$this->inited) $this->init();
		$banners=array();
		if(!isset($vars['orderby'])||$vars['orderby']==false) $vars['orderby']="`ordine`";
		if(!isset($vars['lang'])||$vars['lang']==false) $vars['lang']=$_GET['lang'];
	
		$cat=$this->getCategories();
		if(!isset($vars['category'])||$vars['category']==false) $vars['category']=$cat[0]['category'];
		else $vars['category']=b3_htmlize($vars['category'],false,"");
		foreach($cat as $c) {
			if($c['categoria']==$vars['category']) $idcat=$c['idcat'];
			}

		$query="SELECT * FROM `".TABLE_BANNER."` WHERE `categoria`='".mysql_real_escape_string($idcat)."' AND `ll`='".mysql_real_escape_string(strtoupper($vars['lang']))."' AND `online`='s' ORDER BY ".mysql_real_escape_string($vars['orderby'])."";
		if((isset($vars['from'])&&$vars['from']>=0)||(isset($vars['limit'])&&$vars['limit']>0)) {
			if(!isset($vars['from'])||$vars['from']<0) $vars['from']=0;
			$query.=" LIMIT ".$vars['from'];
			if(isset($vars['limit'])&&$vars['limit']>0) $query.=",".$vars['limit'];
			}

		$results=mysql_query($query);

		for($i=0;$row=mysql_fetch_array($results);$i++) {
			$banners[$i]=$row;
			$b=$this->kDocuments->getList(TABLE_BANNER,$row['idbanner']);
			if(!isset($b[0])) $b[0]=array();
			$banners[$i]['banner']=$b[0];
			$banners[$i]['permalink']=SITE_URL.BASEDIR.DIR_DOCS.$banners[$i]['banner']['iddoc'].'/'.$banners[$i]['banner']['filename'];
			$banners[$i]['width']=0;
			$banners[$i]['height']=0;
			$size=getimagesize($_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_DOCS.$banners[$i]['banner']['iddoc'].'/'.$banners[$i]['banner']['filename']);
			if($size!=false) {
				$banners[$i]['width']=$size[0];
				$banners[$i]['height']=$size[1];
				}
			//aumento il counter delle impressioni
			$query="UPDATE `".TABLE_BANNER."` SET `views`=`views`+1 WHERE `idbanner`='".$row['idbanner']."' LIMIT 1";
			mysql_query($query);
			}

		return $banners;
		}

	function setBanner($bannerDB) {
		if(!$this->inited) $this->init();
		$this->bannerDB=$bannerDB;
		}
	
	function getVar($param) {
		if(!$this->inited) $this->init();
		if(isset($this->bannerDB[$param])) return $this->bannerDB[$param];
		else return false;
		}
	}
?>