<?php 
class kNews {
	/*
	$categoriesList = all the cats
	$allowedCategories = id of available cats as defined in control panel (should be overwrited by setCatByDir())
	$allowedDate = date (year or month or day) to show. if ="" allow any date
	*/
	protected $inited;
	protected $allowedDate,$categoriesList,$allowedCategories,$kText,$__usersList,$loadedNews,$dir_news,$orderby,$if_expired,$newsTemplate,$newsLayout,$imgs,$imgallery,$docgallery;
	
	public function __construct() {
		$this->inited=false;
		}

	/* reset to defaults value */
	public function init() {
		$this->inited=true;
		require_once($_SERVER['DOCUMENT_ROOT'].'/'.BASEDIR."admin/inc/main.lib.php");
		require_once($_SERVER['DOCUMENT_ROOT'].'/'.BASEDIR."inc/images.lib.php");
		require_once($_SERVER['DOCUMENT_ROOT'].'/'.BASEDIR."inc/documents.lib.php");
		require_once($_SERVER['DOCUMENT_ROOT'].'/'.BASEDIR."inc/kalamun.lib.php");
		require_once($_SERVER['DOCUMENT_ROOT'].'/'.BASEDIR."inc/tplshortcuts.lib.php");
		$this->kText=new kText();
		$this->imgs=new kImages();
		$this->imgallery=new kImgallery();
		$this->docgallery=new kDocgallery();

		$this->loadedNews=false;
		$this->allowedDate="";
		$this->allowedCategories=array();
		$this->categoriesList=array();
		$this->dir_news=$GLOBALS['__template']->getVar('dir_news',1);
		$this->orderby=$GLOBALS['__template']->getVar('news-order',1);
		if($this->orderby=="") $this->orderby="pubblica DESC";
		$this->if_expired=$GLOBALS['__template']->getVar('news-order',2);
		$allowedCategories=trim($GLOBALS['__template']->getVar('news',2),",");
		$this->newsTemplate=$GLOBALS['__template']->getVar('news-template',1);
		$this->newsLayout=$GLOBALS['__template']->getVar('news-template',2);

		// load categories
		$meta=array();
		$query="SELECT * FROM ".TABLE_METADATA." WHERE `tabella`='".TABLE_CATEGORIE."'";
			$results=ksql_query($query);
				while($row=ksql_fetch_array($results)) {
			$meta[$row['id']][$row['param']]=$row['value'];
			}
		$query="SELECT * FROM ".TABLE_CATEGORIE." WHERE tabella='".TABLE_NEWS."' AND ll='".LANG."' ORDER BY ordine";
		$results=ksql_query($query);
		while($row=ksql_fetch_array($results)) {
			$this->categoriesList[$row['ordine']]=$row;
			$this->categoriesList[$row['ordine']]['permalink']=BASEDIR.$GLOBALS['__template']->getLanguageURI(LANG).$this->dir_news.'/'.$row['dir'];
			
			// get photogallery in the correct order
			$this->categoriesList[$row['ordine']]['imgs']=array();
			if(trim($row['photogallery'],",")!="" )
			{
				$conditions="";
				foreach(explode(",",trim($row['photogallery'],",")) as $idimg)
				{
					$conditions.="`idimg`='".intval($idimg)."' OR ";
				}
				$conditions.="`idimg`='0'";
				
				$imgs=$GLOBALS['__images']->getList(false,false,false,$conditions);
				
				foreach(explode(",",trim($row['photogallery'],",")) as $idimg)
				{
					foreach($imgs as $img)
					{
						if($img['idimg']==$idimg) $this->categoriesList[$row['ordine']]['imgs'][]=$img;
					}
				}
			}
			

			$this->categoriesList[$row['ordine']]['metadata']=isset($meta[$row['idcat']])?$meta[$row['idcat']]:array();
			if($allowedCategories=="*") $this->allowedCategories[$row['idcat']]=true;
			elseif(strpos($allowedCategories,','.$row['idcat'].',')!==false) $this->allowedCategories[$row['idcat']]=true;
			}
		unset($allowedCategories);
		}
	
	public function search($keywords) {
		if(!$this->inited) $this->init();
		/* search for one or more terms, passed as array */
		$output=array();
		if(!is_array($keywords)) $keywords=array($keywords);
		
		$query="SELECT `titolo`,`dir`,`anteprima` FROM ".TABLE_NEWS." WHERE ";
		foreach($keywords as $k) {
			$k=trim($k);
			if($k!=""&&strlen($k)>3) {
				$k=b3_htmlize($k,true,"");
				$query.="(`titolo` LIKE '%".$k."%' OR `sottotitolo` LIKE '%".$k."%' OR `anteprima` LIKE '%".$k."%' OR `testo` LIKE '%".$k."%') AND ";
				}
			}
		if(substr($query,-6)=="WHERE ") return $output; //if no valid keywords return the empty array (prevent to return all pages)

		$query.="`ll`='".ksql_real_escape_string(LANG)."' ";
		if(!isset($_GET['preview'])||$_GET['preview']!=md5(ADMIN_MAIL)) $query.=" AND `pubblica`<=NOW() AND `online`='y' ";
		if($this->if_expired=="nascondi") $query.=" AND `scaduta`<=NOW() ";
		$query.="ORDER BY ".$this->orderby."";

		$results=ksql_query($query);
		while($row=ksql_fetch_array($results)) {

			$subdir="";
			foreach($this->categoriesList as $cat) {
				if($subdir=="") $subdir=$cat['dir'];
				if(strpos($row['categorie'],','.$cat['idcat'].',')!==false) {
					if($GLOBALS['url'][0]==$this->dir_news&&$GLOBALS['url'][1]==$cat['dir']) $subdir=$GLOBALS['url'][1];
					}
				}

			$output[]=array(
				"title"=>$row['titolo'],
				"permalink"=>BASEDIR.$GLOBALS['__template']->getLanguageURI(LANG).$this->dir_news.'/'.$subdir.'/'.$row['dir'],
				"excerpt"=>$row['anteprima']
				);
			}
		return $output;
		}


	/******************************************/
	/* CATEGORIES                             */
	/******************************************/
	/* set allowed categories */
	public function setCatByDir($dir="",$append=false) {
		if(!$this->inited) $this->init();
		if($append==false) {
			$this->allowedCategories=array();
			$this->allowedDate="";
			}
		if($dir=="") {
			// if $dir=="" reload allowed categories as defined in control panel
			$dir=array();
			$categories=trim($GLOBALS['__template']->getVar('news',2),",");
			if($categories=="*") $dir="*";
			else {
				foreach(explode(",",$categories) as $idcat) {
					foreach($this->categoriesList as $cat) {
						if($cat['idcat']==$idcat) $dir[]=$cat['dir'];
						}
					}
				}
			}

		if(!is_array($dir)) $dir=array($dir);
		foreach($this->categoriesList as $cat) {
			foreach($dir as $d) {
				if($cat['dir']==$d||$d=="*") { // if $dir=="*" set all dir as allowed
					$this->allowedCategories[$cat['idcat']]=true;
					}
				elseif(preg_match("/\d{4}-\d{2}(-\d{4})?/",$d)) {
					$this->allowedDate=$d;
					}
				}
			}
		return true;
		}

	/* get category */
	public function getCatByDir($dir) {
		if(!$this->inited) $this->init();
		if(preg_match("/\d{4}-\d{2}(-\d{2})?/",$dir)) {
			$dateformat=$GLOBALS['__template']->getVar('timezone',2);
			if(substr($dir,8,2)) $dd=substr($dir,8,2);
			else {
				$dd="01";
				$dateformat=trim(str_replace("%d","",$dateformat),"/-. ");
				}
			return array(
				"idcat"=>false,
				"categoria"=>strftime($dateformat,mktime(0,0,0,substr($dir,5,2),$dd,substr($dir,0,4))),
				"dir"=>$dir,
				"permalink"=>BASEDIR.$GLOBALS['__template']->getLanguageURI(LANG).$this->dir_news.'/'.$dir,
				"imgs"=>array()
				);
			}
		else {
			foreach($this->categoriesList as $cat) {
				if($cat['dir']==$dir) {
					return $cat;
					}
				}
			}
		return false;
		}
	public function getCatById($idcat) {
		if(!$this->inited) $this->init();
		foreach($this->categoriesList as $cat) {
			if($cat['idcat']==$idcat) return $cat;
			}
		return false;
		}
	public function getCategories($count=false) {
		if(!$this->inited) $this->init();
		$output=array();
		$i=0;
		foreach($this->categoriesList as $cat) {
			if(isset($this->allowedCategories[$cat['idcat']])) {
				$output[$i]=$cat;
				if($count==true) {
					$query="SELECT count(*) AS tot FROM ".TABLE_NEWS." WHERE `categorie` LIKE '%,".$cat['idcat'].",%'";
					$results=ksql_query($query);
					$row=ksql_fetch_array($results);
					$output[$i]['count']=$row['tot'];
					}
				}
			$i++;
			}
		return $output;
		}
	

	/******************************************/
	/* SINGLE NEWS                            */
	/******************************************/
	/* check if gived dir is owned by a news */
	public function newsExists($dir=null,$ll=null) {
		if(!$this->inited) $this->init();
		if($ll==null) $ll=LANG;
		$ll=strtoupper($ll);
		$dir=$dir==null?$GLOBALS['url']:explode("/",$dir);
		if($dir[0]!=$GLOBALS['__template']->getVar('dir_news')) return false;
		if(!isset($dir[2])) $dir[2]="";

		if($this->orderby=="") $this->orderby="data";
		$query="SELECT * FROM ".TABLE_NEWS." WHERE (`dir`='".b3_htmlize($dir[2],true,"")."' OR `dir`='".ksql_real_escape_string($dir[2])."') AND `ll`='".ksql_real_escape_string($ll)."' ";
		if(!isset($_GET['preview'])||$_GET['preview']!=md5(ADMIN_MAIL)) $query.=" AND `pubblica`<=NOW() AND `online`='y' ";
		if($this->if_expired=="nascondi") $query.=" AND `scaduta`<=NOW() ";
		$query.=" LIMIT 1";
			$results=ksql_query($query);
				if($row=ksql_fetch_array($results)) return true;
		else return false;
		}

	public function setNewsByDir($dir=false,$ll=false) {
		if(!$this->inited) $this->init();
		$this->loadedNews=$this->getNews($dir,$ll);
		}
	public function getNewsVar($var) {
		if(!$this->inited) $this->init();
		if(!isset($this->loadedNews['idnews'])) $this->setNewsByDir($GLOBALS['url'][2]);
		if(!isset($this->loadedNews['idnews'])) return false;
		return $this->loadedNews[$var];
		}

	/* retrive template and layout of requested news */
	public function getNewsTemplate($dir=false) {
		if(!$this->inited) $this->init();
		if($dir==false||$dir=="") $dir=$GLOBALS['url'][2];

		// if this news is the same loaded get template from loadedNews
		if($this->loadedNews!=false&&$this->loadedNews['dir']==$dir&&$this->loadedNews['ll']==LANG) {
			return array("template"=>$this->loadedNews['template'],"layout"=>$this->loadedNews['layout']);
			}

		// else get from database
		$query="SELECT `template`,`layout` FROM ".TABLE_NEWS." WHERE (`dir`='".b3_htmlize($dir,true,"")."' OR `dir`='".ksql_real_escape_string($dir)."') AND `ll`='".ksql_real_escape_string(LANG)."' ";
		if(!isset($_GET['preview'])||$_GET['preview']!=md5(ADMIN_MAIL)) $query.=" AND `pubblica`<=NOW() AND `online`='y' ";
		if($this->if_expired=="nascondi") $query.=" AND `scaduta`<=NOW() ";
		if(count($this->allowedCategories)>0) {
			$query.="AND (`categorie`=',' ";
			foreach($this->allowedCategories as $cat=>$true) {
				$query.="OR `categorie` LIKE '%,".$cat.",%' ";
				}
			$query.=") ";
			}
		$query.=" LIMIT 1";
			$results=ksql_query($query);
				if($row=ksql_fetch_array($results)) return $row;
		return false;
		}

	/* retrieve metadata for requested news */
	public function getMetadata($dir=null,$ll=false) {
		if(!$this->inited) $this->init();
		if($ll==false) $ll=LANG;
		if($dir==null) $dir=$GLOBALS['url'];
		else $dir=explode("/",$dir);
		if(!isset($dir[1])) $dir[1]="";
		if(!isset($dir[2])) $dir[2]="";
		for($i=3;isset($dir[$i]);$i++) { $dir[2].="/".$dir[$i]; }

		// if this news is the same loaded get template from loadedNews
		$news=array();
		if(isset($this->loadedNews['dir'])&&$this->loadedNews['dir']==$dir[2]&&$this->loadedNews['ll']==LANG) {
			$news=$this->loadedNews;
			}
		elseif($dir[2]!="") {
			$news=$this->getNews($dir[2],$ll);
			}
	
		$metadata=array();
		$metadata['titolo']="";
		$metadata['traduzioni']="";
		foreach(kGetLanguages() as $code=>$lang) { $metadata['traduzioni'].=$code."|".$GLOBALS['__template']->getVar('dir_news',1,$code)."\n"; }
		$metadata['template']=$this->newsTemplate;
		$metadata['layout']="";
		if($dir[2]!="") {
			$metadata['titolo'].=$news['titolo'];
			$metadata['traduzioni']=$news['traduzioni'];
			if($metadata['template']!="") $metadata['template']=$news['template'];
			if($metadata['layout']!="") $metadata['layout']=$news['layout'];
			$metadata['featuredimage']=($news['featuredimage']>0 ? $this->imgs->getImage($news['featuredimage']) : array());
			}
		if(isset($news['idnews'])) {
			if(isset($this->loadedNews['idnews'])&&$news['idnews']==$this->loadedNews['idnews']&&isset($news['metadata'])) {
				foreach($news['metadata'] as $param=>$value) {
					$metadata[$param]=$value;
					}
				}
			else {
				$query="SELECT * FROM ".TABLE_METADATA." WHERE tabella='".TABLE_NEWS."' AND id='".intval($news['idnews'])."'";
				$results=ksql_query($query);
				while($row=ksql_fetch_array($results)) {
					$metadata[$row['param']]=$row['value'];
					}
				$this->loadedNews['metadata']=$metadata;
				}
			}
		return $metadata;
		}

	/* convert raw array from database into improved array */
	private function row2output($row,$orderby="`data` DESC") {
		$vars['photogallery']=true; // for now always true... need to be implemented
		if(!$this->inited) $this->init();
		$output=$row;
		if($orderby=="") $orderby=$this->orderby;
		if(strpos($orderby,"scadenza")!==false) $dataRef="scadenza";
			elseif(strpos($orderby,"starting_date")!==false) $dataRef="starting_date";
			elseif(strpos($orderby,"data")!==false) $dataRef="data";
			else $dataRef="pubblica";

		// get categories and give priority to actual category for permalink
		$output['categorie']=array();
		$subdir="";
		foreach($this->categoriesList as $cat) {
			if(strpos($row['categorie'],','.$cat['idcat'].',')!==false) {
				$output['categorie'][]=$cat;
				if($GLOBALS['url'][0]==$this->dir_news&&$GLOBALS['url'][1]==$cat['dir']) $subdir=$GLOBALS['url'][1];
				}
			}
		if($subdir=="") $subdir=$output['categorie'][0]['dir'];

		$output['permalink']=BASEDIR.$GLOBALS['__template']->getLanguageURI(LANG).$this->dir_news.'/'.$subdir.'/'.$row['dir'];
		$output['catpermalink']=BASEDIR.$GLOBALS['__template']->getLanguageURI(LANG).$this->dir_news.'/'.$subdir;
		$output['archpermalink']['year']=BASEDIR.$GLOBALS['__template']->getLanguageURI(LANG).$this->dir_news.'/'.substr($row[$dataRef],0,4);
		$output['archpermalink']['month']=$output['archpermalink']['year'].substr($row[$dataRef],4,3);
		$output['archpermalink']['day']=$output['archpermalink']['month'].substr($row[$dataRef],7,3);
		$kText=new kText();
		$output['embeddedimgs']=array();
		$output['embeddeddocs']=array();
		$output['embeddedmedias']=array();
		$output['anteprima']=$this->kText->formatText($output['anteprima']);
		$tmp=$this->kText->embedImg($output['anteprima']);
			$output['anteprima']=$tmp[0];
			if(is_array($tmp[1])) $output['embeddedimgs']=array_merge($output['embeddedimgs'],$tmp[1]);
		$tmp=$this->kText->embedDocs($output['anteprima']);
			$output['anteprima']=$tmp[0];
			if(is_array($tmp[1])) $output['embeddeddocs']=array_merge($output['embeddeddocs'],$tmp[1]);
		$tmp=$this->kText->embedMedia($output['anteprima']);
			$output['anteprima']=$tmp[0];
			if(is_array($tmp[1])) $output['embeddedmedias']=array_merge($output['embeddedmedias'],$tmp[1]);
		$output['testo']=$this->kText->formatText($output['testo']);
		$tmp=$this->kText->embedImg($output['testo']);
			$output['testo']=$tmp[0];
			if(is_array($tmp[1])) $output['embeddedimgs']=array_merge($output['embeddedimgs'],$tmp[1]);
		$tmp=$this->kText->embedDocs($output['testo']);
			$output['testo']=$tmp[0];
			if(is_array($tmp[1])) $output['embeddeddocs']=array_merge($output['embeddeddocs'],$tmp[1]);
		$tmp=$this->kText->embedMedia($output['testo']);
			$output['testo']=$tmp[0];
			if(is_array($tmp[1])) $output['embeddedmedias']=array_merge($output['embeddedmedias'],$tmp[1]);

		if($row['featuredimage']==0) $output['featuredimage']=false;
		else $output['featuredimage']=$this->imgs->getImage($row['featuredimage']);

		// get photogallery in the correct order
		$output['imgs']=array();
		if($vars['photogallery']==true && trim($row['photogallery'],",")!="" )
		{
			$conditions="";
			foreach(explode(",",trim($row['photogallery'],",")) as $idimg)
			{
				$conditions.="`idimg`='".intval($idimg)."' OR ";
			}
			$conditions.="`idimg`='0'";
			
			$imgs=$this->imgs->getList(false,false,false,$conditions);
			
			foreach(explode(",",trim($row['photogallery'],",")) as $idimg)
			{
				foreach($imgs as $img)
				{
					if($img['idimg']==$idimg) $output['imgs'][]=$img;
				}
			}
		}

		$output['docs']=$GLOBALS['__documents_gallery']->getList(TABLE_NEWS,$row['idnews']);

		$output['autore']=$GLOBALS['__users']->getUserById($row['iduser']);
		$output['commenti']=$this->getComments($row['idnews']);

		$output['traduzioni']=array();
		foreach(explode("|",trim($row['traduzioni'],"|")) as $trad) {
			if(substr($trad,0,2)!="") $output['traduzioni'][substr($trad,0,2)]=$this->getPermalinkById(substr($trad,3));
			}

		return $output;
		}
		
	/* get news by dir or, if not gived, by url */
	public function getNews($dir=false,$ll=false) {
		if(!$this->inited) $this->init();
		if($ll==false) $ll=LANG;
		if($dir==false) $dir=$GLOBALS['url'][2];
		if($this->orderby=="") $orderby="data DESC";

		$query="SELECT * FROM ".TABLE_NEWS." WHERE (`dir`='".b3_htmlize($dir,true,"")."' OR `dir`='".ksql_real_escape_string($dir)."') AND `ll`='".ksql_real_escape_string($ll)."' ";
		if(!isset($_GET['preview'])||$_GET['preview']!=md5(ADMIN_MAIL)) $query.=" AND `pubblica`<=NOW() AND `online`='y' ";
		if($this->if_expired=="nascondi") $query.=" AND scaduta<=NOW() ";
		$query.="ORDER BY ".$this->orderby." LIMIT 1";
		$results=ksql_query($query);
		if($row=ksql_fetch_array($results)) $row=$this->row2output($row);
		else $row=array();
		return $row;
		}
	
	public function getPrevious($orderby="",$dir,$limit=1,$cat="*") {
		if(!$this->inited) $this->init();
		if($orderby=="") $orderby=$this->orderby;
		if(strpos($orderby,"scadenza")!==false) $dataRef="scadenza";
			elseif(strpos($orderby,"starting_date")!==false) $dataRef="starting_date";
			elseif(strpos($orderby,"data")!==false) $dataRef="data";
			else $dataRef="pubblica";
		if($cat=="*") $this->setCatByDir();
		$limit=intval($limit);

		$query="SELECT `".$dataRef."` FROM `".TABLE_NEWS."` WHERE `dir`='".ksql_real_escape_string($dir)."' LIMIT 1";
			$results=ksql_query($query);
				if($row=ksql_fetch_array($results)) {
			$output=array();
			$query="SELECT * FROM ".TABLE_NEWS." WHERE `".$dataRef."`<'".$row[$dataRef]."' AND ll='".LANG."' AND pubblica<=NOW() AND `online`='y' ";
			if($this->if_expired=="nascondi") $query.="AND scaduta<=NOW() ";
			$query.="AND (categorie='' ";
			foreach($this->allowedCategories as $cat=>$on) {
				$query.="OR categorie LIKE '%,".$cat.",%' ";
				}
			$query.=") ";
			$query.="ORDER BY ".$orderby." LIMIT ".$limit;
			$results=ksql_query($query);
			$results=ksql_query($query);
			while($row=ksql_fetch_array($results)) {
				$output[]=$this->row2output($row,$orderby);
				}
			return $output;
			}
		else return false;
		}
	public function getNext($orderby="",$dir,$limit=1,$cat="*") {
		if(!$this->inited) $this->init();
		$this->if_expired=$GLOBALS['__template']->getVar('news-order',2);
		if($orderby=="") $orderby=$GLOBALS['__template']->getVar('news-order',1);
		if($orderby=="") $orderby="pubblica DESC";
		if(strpos($orderby,"scadenza")!==false) $dataRef="scadenza";
			elseif(strpos($orderby,"starting_date")!==false) $dataRef="starting_date";
			elseif(strpos($orderby,"data")!==false) $dataRef="data";
			else $dataRef="pubblica";
		$orderby=preg_replace('/ desc$/i',' ASC',$orderby);
		$query="SELECT `".$dataRef."` FROM ".TABLE_NEWS." WHERE `dir`='".ksql_real_escape_string($dir)."' LIMIT 1";
		$results=ksql_query($query);
		if($row=ksql_fetch_array($results)) {
			$output=array();
			$query="SELECT * FROM ".TABLE_NEWS." WHERE `".$dataRef."`>'".$row[$dataRef]."' AND ll='".LANG."' AND pubblica<=NOW() AND `online`='y' ";
			if($this->if_expired=="nascondi") $query.="AND scaduta<=NOW() ";
			$query.="AND (categorie='' ";
			foreach($this->allowedCategories as $cat=>$on) {
				$query.="OR categorie LIKE '%,".$cat.",%' ";
				}
			$query.=") ";
			$query.="ORDER BY ".$orderby." LIMIT ".$limit;
				$results=ksql_query($query);
					while($row=ksql_fetch_array($results)) {
				$output[]=$this->row2output($row,$orderby);
				}
			return $output;
			}
		else return false;
		}

	public function getPermalinkById($idnews) {
		if(!$this->inited) $this->init();
		$query="SELECT `ll`,`dir`,`categorie` FROM `".TABLE_NEWS."` WHERE `idnews`='".intval($idnews)."' LIMIT 1";
		$results=ksql_query($query);
		$row=ksql_fetch_array($results);

		$subdir="";

		$allowedCategories=$GLOBALS['__template']->getVar('news',2,$row['ll']);
		$catquery="SELECT * FROM `".TABLE_CATEGORIE."` WHERE `tabella`='".TABLE_NEWS."' AND `ll`='".$row['ll']."' ORDER BY `ordine`";
		$catresults=ksql_query($catquery);
		while($catrow=ksql_fetch_array($catresults)) {
			if($allowedCategories==",*,"&&strpos($row['categorie'],','.$catrow['idcat'].',')!==false) {
				$subdir=$catrow['dir'];
				break;
				}
			elseif(strpos($allowedCategories,','.$catrow['idcat'].',')!==false&&strpos($row['categorie'],','.$catrow['idcat'].',')!==false) {
				$subdir=$catrow['dir'];
				break;
				}
			}
		unset($allowedCategories);
		return BASEDIR.$GLOBALS['__template']->getLanguageURI($row['ll']).$GLOBALS['__template']->getVar('dir_news',1,$row['ll']).'/'.$subdir.'/'.$row['dir'];
		}


	/******************************************/
	/* NEWS LISTS                             */
	/******************************************/
	public function getList($vars=array()) {
		if(!$this->inited) $this->init();
		if(!isset($vars['ll'])||$vars['ll']=="") $vars['ll']=LANG;
		if(!isset($vars['orderby'])||$vars['orderby']=="") $vars['orderby']=$this->orderby;
		if(strpos($vars['orderby'],"`")===false) $vars['orderby']=$vars['orderby'];
		if(strpos($vars['orderby'],"scadenza")!==false) $dataRef="scadenza";
			elseif(strpos($vars['orderby'],"starting_date")!==false) $dataRef="starting_date";
			elseif(strpos($vars['orderby'],"data")!==false) $dataRef="data";
			else $dataRef="pubblica";
		if(!isset($vars['ll'])||$vars['ll']=="") $vars['ll']=LANG;

		$output=array();
		$query="SELECT * FROM `".TABLE_NEWS."` WHERE `ll`='".ksql_real_escape_string($vars['ll'])."' AND `pubblica`<=NOW() AND `online`='y' ";
		if($this->if_expired=="archivia"&&isset($vars['archive'])) {
			if($vars['archive']==true) $query.="AND `scadenza`<NOW() ";
			else $query.="AND `scadenza`>=NOW() ";
			}
		elseif($this->if_expired=="nascondi") $query.="AND `scadenza`<NOW() ";
		if(isset($vars['conditions'])&&$vars['conditions']!="") $query.="AND (".$vars['conditions'].") ";
		
		if(count($this->allowedCategories)>0) {
			$query.="AND (`categorie`=',' ";
			foreach($this->allowedCategories as $idcat=>$true) {
				$query.="OR `categorie` LIKE '%,".intval($idcat).",%' ";
				}
			$query.=") ";
			}
		if($this->allowedDate!="") $query.=" AND `".ksql_real_escape_string($dataRef)."` LIKE '".ksql_real_escape_string($this->allowedDate)."%' ";
		if(isset($vars['options'])&&$vars['options']!="") $query.=" ".$vars['options']." ";
		
		if(isset($vars['home'])&&$vars['home']==true) $query.=" AND `home`='s' ";
		if(isset($vars['calendar'])) {
			if($vars['calendar']==true) $query.=" AND `calendario`='s' ";
			else $query.=" AND `calendario`='n' ";
			}
		
		$query.="ORDER BY ".ksql_real_escape_string($vars['orderby']).",`idnews` DESC ";
		if(isset($vars['offset'])||isset($vars['limit'])) {
			if(!isset($vars['offset'])) $vars['offset']=0;
			$query.=" LIMIT ".intval($vars['offset']);
			if(isset($vars['limit'])) $query.=",".intval($vars['limit']);
			}
		$results=ksql_query($query);
		for($i=0;$row=ksql_fetch_array($results);$i++) {
			$output[$i]=$this->row2output($row,$vars['orderby']);
			}
		return $output;
		}

	public function getQuickList($vars) {
		if(!$this->inited) $this->init();
		if(!isset($vars['ll'])||$vars['ll']=="") $vars['ll']=LANG;
		if(!isset($vars['offset'])||$vars['offset']=="") $vars['offset']=0;
		if(!isset($vars['limit'])||$vars['limit']=="") $vars['limit']=10;
		if(!isset($vars['orderby'])||$vars['orderby']=="") $vars['orderby']=$this->orderby;
		if($vars['orderby']=="") $vars['orderby']="pubblica";
		if(strpos($vars['orderby'],"scadenza")!==false) $dataRef="scadenza";
			elseif(strpos($vars['orderby'],"starting_date")!==false) $dataRef="starting_date";
			elseif(strpos($vars['orderby'],"data")!==false) $dataRef="data";
			else $dataRef="pubblica";
		if(!isset($vars['ll'])||$vars['ll']=="") $vars['ll']=LANG;

		$output=array();
		$query="SELECT * FROM `".TABLE_NEWS."` WHERE `ll`='".$vars['ll']."' AND `pubblica`<=NOW() AND `online`='y' ";
		if($this->if_expired=="archivia"&&isset($vars['archive'])) {
			if($vars['archive']==true) $query.="AND `scadenza`<NOW() ";
			else $query.="AND `scadenza`>=NOW() ";
			}
		elseif($this->if_expired=="nascondi") $query.="AND `scadenza`<NOW() ";
		if(isset($vars['conditions'])&&$vars['conditions']!="") $query.="AND (".$vars['conditions'].") ";
		
		if(count($this->allowedCategories)>0) {
			$query.="AND (`categorie`=',' ";
			foreach($this->allowedCategories as $idcat=>$true) {
				$query.="OR `categorie` LIKE '%,".intval($idcat).",%' ";
				}
			$query.=") ";
			}
		if($this->allowedDate!="") $query.=" AND `".ksql_real_escape_string($dataRef)."` LIKE '".ksql_real_escape_string($this->allowedDate)."%' ";
		if(isset($vars['options'])&&$vars['options']!="") $query.=" ".$vars['options']." ";
		
		if(isset($vars['home'])&&$vars['home']==true) $query.=" AND `home`='s' ";
		if(isset($vars['calendar'])) {
			if($vars['calendar']==true) $query.=" AND `calendario`='s' ";
			else $query.=" AND `calendario`='n' ";
			}
		
		$query.="ORDER BY ".$vars['orderby'].",idnews DESC LIMIT ".$vars['limit']." OFFSET ".$vars['offset']."";
		$results=ksql_query($query);
		for($i=0;$row=ksql_fetch_array($results);$i++) {
			$output[$i]=$row;
			$output[$i]['categorie']=array();
			$subdir="";
			foreach($this->categoriesList as $cat) {
				if(strpos($row['categorie'],','.$cat['idcat'].',')!==false) {
					$output[$i]['categorie'][]=$cat;
					if(isset($GLOBALS['__dir__'])&&$GLOBALS['__dir__']==$GLOBALS['__template']->getVar('dir_news',1)&&$GLOBALS['__subdir__']==$cat['dir']) $subdir=$GLOBALS['__subdir__'];
					}
				}
			if($subdir=="") $subdir=$output[$i]['categorie'][0]['dir'];
			
			$output[$i]['featuredimage'] = $output[$i]['featuredimage']>0 ? $this->imgs->getImage($output[$i]['featuredimage']) : array();

			$output[$i]['permalink']=BASEDIR.$GLOBALS['__template']->getLanguageURI(LANG).$GLOBALS['__template']->getVar('dir_news',1).'/'.$subdir.'/'.$row['dir'];
			$output[$i]['catpermalink']=BASEDIR.$GLOBALS['__template']->getLanguageURI(LANG).$GLOBALS['__template']->getVar('dir_news',1).'/'.$subdir;
			$output[$i]['archpermalink']['year']=BASEDIR.$GLOBALS['__template']->getLanguageURI(LANG).$GLOBALS['__template']->getVar('dir_news',1).'/'.substr($row[$dataRef],0,4);
			$output[$i]['archpermalink']['month']=BASEDIR.$GLOBALS['__template']->getLanguageURI(LANG).$GLOBALS['__template']->getVar('dir_news',1).'/'.substr($row[$dataRef],0,7);
			$output[$i]['archpermalink']['day']=BASEDIR.$GLOBALS['__template']->getLanguageURI(LANG).$GLOBALS['__template']->getVar('dir_news',1).'/'.substr($row[$dataRef],0,10);
			}
		return $output;
		}

	public function countNews($conditions="") {
		if(!$this->inited) $this->init();
		$query="SELECT count(*) AS tot FROM ".TABLE_NEWS." WHERE ll='".LANG."' AND `pubblica`<=NOW() AND `online`='y' AND `categorie`<>',,' ";
		if($this->geo>0) $query.="AND categorie LIKE '%,".$this->geo.",%' ";
		if($this->cat>0) $query.="AND categorie LIKE '%,".$this->cat.",%' ";
		if($conditions!="") $query.="AND (".$conditions.") ";
		$results=ksql_query($query);
		$row=ksql_fetch_array($results);
		return $row['tot'];
		}
		
	public function briciole() {
		if(!$this->inited) $this->init();
		$geo=$this->geo;
		$cat=$this->cat;
		$briciole='<a href="'.BASEDIR.'/index.php">Home page</a>';
		if($geo>0) $briciole.=' &gt; <a href="'.BASEDIR.'/notizie/'.title2dir($this->geoList[$geo]).'">'.$this->geoList[$geo].'</a>';
		if($cat>0) $briciole.=' &gt; <a href="'.BASEDIR.'/notizie/'.title2dir($this->geoList[$geo]).'/'.title2dir($this->categoriesList[$cat]).'">'.$this->categoriesList[$cat].'</a>';
		return $briciole;
		}

	private function findFirstCat($cat) {
		if(!$this->inited) $this->init();
		foreach($this->categoriesList as $row['idcat']=>$row['categoria']) {
			if(strpos($cat,','.$row['idcat'].',')!==false) return $row['categoria'];
			}
		return false;
		}

	public function addComment($name,$email,$text,$idnews,$public="n") {
		if(!$this->inited) $this->init();
		if($public!="s") $public="n";
		$query="INSERT INTO ".TABLE_COMMENTI." (ip,data,tabella,id,autore,email,testo,public) VALUES('".$_SERVER['REMOTE_ADDR']."',NOW(),'".TABLE_NEWS."','".$idnews."','".b3_htmlize($name,true,"")."','".b3_htmlize($email,true,"")."','".b3_htmlize($text,true,"")."','".$public."')";
		$results=ksql_query($query);
		$idcomm=ksql_insert_id();
		
		//notifica
		$mail=array("headers"=>"","to"=>"","subject"=>"","message"=>"");
		$query="SELECT idnews,titolo,iduser FROM ".TABLE_NEWS." WHERE idnews=".$idnews." LIMIT 1";
		$results=ksql_query($query);
		$row=ksql_fetch_array($results);
		$iduser=$row['iduser'];
		$titolo=$row['titolo'];
		$idnews=$row['idnews'];
		$query="SELECT * FROM ".TABLE_USERS." WHERE iduser='".$iduser."' LIMIT 1";
		$results=ksql_query($query);
		if($row=ksql_fetch_array($results)) {
			$siteTitle=$GLOBALS['__template']->getVar('sitename',1);
			$mail['subject']="[".$siteTitle."] Nuovo commento";
			$mail['message']="Ciao ".$row['name'].",\n"
							."Hai ricevuto un commento alla notizia ".$titolo." del tuo sito ".SITE_URL."\n"
							."Ecco cosa hanno scritto:\n"
							."---\n"
							.$text."\n"
							."---\n"
							."autore: ".$name." (".$email." - ip:".$_SERVER['REMOTE_ADDR'].")\n"
							."---\n";
			if($public=="n") {
				$mail['subject'].=" da moderare";
				$mail['message'].="\n"
								."Questo commento attende la tua moderazione, quindi non sarà visibile fino a quando non l'avrai approvato.\n"
								."Per approvarlo vai qui: ".SITE_URL."/admin/news/commenti.php?idnews=".$idnews."\n";
				}
			$mail['headers']='From: '.$name.' <'.$email.'>';
			$mail['to']=ADMIN_MAIL;
			mail($mail['to'],$mail['subject'],$mail['message'],$mail['headers']);
			}
		}
	
	public function getComments($idnews) {
		if(!$this->inited) $this->init();
		$output=array();
		$query="SELECT * FROM ".TABLE_COMMENTI." WHERE tabella='".TABLE_NEWS."' AND id='".intval($idnews)."' AND public='s' ORDER BY data";
		$results=ksql_query($query);
		for($i=0;$row=ksql_fetch_array($results);$i++) {
			$output[$i]=$row;
			$output[$i]['dataleggibile']=preg_replace("/(\d{4}).(\d{2}).(\d{2}) (\d{2}).(\d{2}).*/","$3-$2-$1 $4:$5",$row['data']);
			}
		return $output;
		}
	}

