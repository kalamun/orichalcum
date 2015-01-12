<?php 
/* (c) Kalamun - GPL 3 */

class kPhotogallery {
	protected $inited;
	protected $__template,$images,$imgs,$loadedGallery,$commentsDB=false;

	public function __construct() {
		$this->inited=false;
		}
		
	public function init() {
		$this->inited=true;
		require_once($_SERVER['DOCUMENT_ROOT'].'/'.BASEDIR.'inc/template.lib.php');
		require_once($_SERVER['DOCUMENT_ROOT'].'/'.BASEDIR.'inc/images.lib.php');
		$this->imgs=new kImages();
		$this->loadedNews=false;
		}

	public function photogalleryExists($dir=-1) {
		if($dir==-1) $dir=$GLOBALS['__subsubdir__'];
		$query="SELECT * FROM `".TABLE_PHOTOGALLERY."` WHERE `ll`='".LANG."' AND `data`<=NOW() AND (`dir`='".b3_htmlize($dir,true,"")."' OR `dir`='".ksql_real_escape_string($dir)."') LIMIT 1";
		$results=ksql_query($query);
		if($row=ksql_fetch_array($results)) return true;
		else return false;
		}

	public function getMetadata($dir="false",$ll=false)
	{
		if(!$this->inited) $this->init();
		if($ll==false) $ll=LANG;
		if($dir!="false") $dir=explode("/",$dir);
		else $dir=array($GLOBALS['__dir__'],$GLOBALS['__subdir__'],$GLOBALS['__subsubdir__']);
		$metadata=array();
		$metadata['titolo']=$dir[0];
		$metadata['traduzioni']="";
		foreach(kGetLanguages() as $ll=>$lang) { $metadata['traduzioni'].=$ll."|".kGetVar('dir_photogallery',1,$ll)."\n"; }
		$metadata['template']=kGetVar('photogallery-template',1);
		$metadata['layout']="";
		$metadata['featuredimage']=array();
		
		if(isset($dir[1])&&$dir[1]!="")
		{
			$query="SELECT idphg,titolo,traduzioni,template,featuredimage FROM ".TABLE_PHOTOGALLERY." WHERE (`dir`='".b3_htmlize($GLOBALS['__subsubdir__'],true,"")."' OR `dir`='".ksql_real_escape_string($GLOBALS['__subsubdir__'])."') AND ll='".$ll."' LIMIT 1";
				$results=ksql_query($query);
					$row=ksql_fetch_array($results);
			$metadata['titolo'].=" &gt; ".$row['titolo'];
			$metadata['traduzioni']=$row['traduzioni'];
			if($metadata['template']!="") $metadata['template']=$row['template'];
			$metadata['featuredimage']=($row['featuredimage']>0 ? $this->imgs->getImage($row['featuredimage']) : array());
			$idphg=$row['idphg'];
		}
		
		if(isset($idphg))
		{
			$query="SELECT * FROM ".TABLE_METADATA." WHERE tabella='".TABLE_PHOTOGALLERY."' AND id='".$idphg."'";
			$results=ksql_query($query);
			while($row=ksql_fetch_array($results)) {
				$metadata[$row['param']]=$row['value'];
			}
		}
		return $metadata;
	}

	public function getGalleryByDir($dir=false) {
		if(!$this->inited) $this->init();
		if($dir==false) $dir=$GLOBALS['__subsubdir__'];
		
		$vars['photogallery']=true; // it needs to be implemented

		$output=array();
		$query="SELECT * FROM ".TABLE_PHOTOGALLERY." WHERE ll='".LANG."' AND data<=NOW() AND (`dir`='".b3_htmlize($dir,true,"")."' OR `dir`='".ksql_real_escape_string($dir)."') LIMIT 1";
		$results=ksql_query($query);
		$row=ksql_fetch_array($results);
		$output=$this->row2output($row);
		
		return $output;
		}

	public function getList($vars=array())
	{
		if(!isset($vars['photogallery'])) $vars['photogallery']=true;
		if(!isset($vars['comments'])) $vars['comments']=true;
		if(!isset($vars['translations'])) $vars['translations']=true;

		if(!$this->inited) $this->init();
		$output=array();
		
		if(empty($vars['ll'])) $vars['ll']=LANG;
		
		// if no categories was set, try to set one based on URL
		if(!isset($vars['category']))
		{
			if($this->photogalleryExists()==true && $GLOBALS['__subsubdir__']!="") $vars['category']=$GLOBALS['__subsubdir__'];
			else $vars['category']='*';
		}
		if($vars['category']!='*' && !is_array($vars['category'])) $vars['category']=array($vars['category']);
		if(!isset($vars['category_operator'])) $vars['category_operator']='OR';
		$vars['category_operator']=strtoupper($vars['category_operator']);

		$query="SELECT * FROM ".TABLE_PHOTOGALLERY." WHERE ll='".$vars['ll']."' ";
		if(!empty($vars['conditions'])) $query.=" AND (".$vars['conditions'].") ";
		if(!empty($vars['options'])) $query.=" ".$options." ";

		// insert categories into query
		if($vars['ll']==LANG && $vars['category']!="*")
		{
			if($vars['category_operator']=='OR') $query.="AND (`categories`=',' ";
			else $query.="AND (`categories`!='' ";
			foreach($vars['category'] as $category)
			{
				foreach($GLOBALS['__template']->getCategoresList(array("table"=>TABLE_PHOTOGALLERY)) as $cat)
				{
					if($category==$cat['idcat'] || $category==$cat['dir'] || $category==b3_htmlize($cat['dir'],true,"")) $query.=$vars['category_operator']." `categories` LIKE '%,".$cat['idcat'].",%' ";
				}
			}
			$query.=") ";
		}

		$query.=" AND `data`<=NOW() ORDER BY `".kGetVar('photogallery-order',1)."`";
		if(isset($vars['offset'])||isset($vars['limit']))
		{
			if(empty($vars['offset'])) $vars['offset']=0;
			$query.=" LIMIT ".intval($vars['offset']);
			if(!empty($vars['limit'])) $query.=",".intval($vars['limit']);
		}

		$results=ksql_query($query);
		while($row=ksql_fetch_array($results))
		{
			$output[]=$this->row2output($row,$vars);
		}
		
		return $output;
	}

	public function setGalleryByDir($dir)
	{
		if(!$this->inited) $this->init();
		$this->loadedGallery=$this->getGalleryByDir($dir);
	}
	public function getGalleryVar($var)
	{
		if(!$this->inited) $this->init();
		if($this->loadedGallery==false) $this->setGalleryByDir($GLOBALS['__subsubdir__']);
		return $this->loadedGallery[$var];
	}
		
	private function row2output($row,$vars=array())
	{
		if(!isset($vars['photogallery'])) $vars['photogallery']=true;
		if(!isset($vars['comments'])) $vars['comments']=true;
		if(!isset($vars['translations'])) $vars['translations']=true;

		$output=$row;

		$output['categories']=array();
		if(strpos(kGetVar('admin-page-layout',1),",categories,")!==false)
		{
			$row['categories']=trim($row['categories'],",");
			foreach(explode(",",$row['categories']) as $cat)
			{
				$output['categories'][]=$this->getCatById($cat);
			}
		}
		
		$subdir="default";
		if(trim($GLOBALS['__template']->getVar('dir_photogallery',1),"/")==$GLOBALS['__dir__'] && $GLOBALS['__subdir__']!="") $subdir=$GLOBALS['__subdir__'];
		elseif(!empty($output['categories'])) $subdir=$output['categories'][0]['dir'];
		$output['permalink']=BASEDIR.$GLOBALS['__template']->getLanguageURI(LANG).$GLOBALS['__template']->getVar('dir_photogallery',1).'/'.$subdir.'/'.$row['dir'];

		// get photogallery in the correct order
		$output['imgs']=array();
		if(!empty($vars['photogallery']) && trim($row['photogallery'],",")!="" )
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

		$output['commenti']=array();
		if($vars['comments']==true) $output['commenti']=$this->getComments($row['idphg']);
		
		$output['traduzioni']=array();
		if($vars['translations']==true)
		{
			foreach(explode("|",trim($row['traduzioni'],"|")) as $trad)
			{
				if(substr($trad,0,2)!="") $output['traduzioni'][substr($trad,0,2)]=$this->getPermalinkById(substr($trad,3));
			}
		}

		if($row['featuredimage']==0) $output['featuredimage']=false;
		else $output['featuredimage']=$this->imgs->getImage($row['featuredimage']);
		
		return $output;
	}

	public function getPermalinkById($idphg)
	{
		if(!$this->inited) $this->init();
		$query="SELECT `ll`,`dir`,`categorie` FROM `".TABLE_PHOTOGALLERY."` WHERE `idphg`='".intval($idphg)."' LIMIT 1";
		$results=ksql_query($query);
		$row=ksql_fetch_array($results);

		$subdir="";

		$catquery="SELECT * FROM `".TABLE_CATEGORIE."` WHERE `tabella`='".TABLE_SHOP_ITEMS."' AND `ll`='".$row['ll']."' ORDER BY `ordine`";
		$catresults=ksql_query($catquery);
		while($catrow=ksql_fetch_array($catresults))
		{
			if(strpos($row['categorie'],','.$catrow['idcat'].',')!==false)
			{
				$subdir=$catrow['dir'];
				break;
			}
		}

		return BASEDIR.$GLOBALS['__template']->getLanguageURI($row['ll']).$GLOBALS['__template']->getVar('dir_shop',1).'/'.$subdir.'/'.$row['dir'];
	}


	/* retrieve categories */
	// by dir
	public function getCategoryByDir($name,$ll=false)
	{
		if(!$this->inited) $this->init();
		if($ll==false) $ll=LANG;

		$query="SELECT * FROM ".TABLE_CATEGORIE." WHERE `tabella`='".TABLE_PHOTOGALLERY."' AND (`dir`='".b3_htmlize($name,true,"")."' OR `dir`='".ksql_real_escape_string($name)."') AND `ll`='".strtoupper($ll)."' LIMIT 1";
		$results=ksql_query($query);

		if($row=ksql_fetch_array($results)) return $row;
		else return false;
	}

	// by name
	public function getCatByName($name,$ll=false)
	{
		if(!$this->inited) $this->init();
		if($ll==false) $ll=LANG;

		$query="SELECT * FROM ".TABLE_CATEGORIE." WHERE `tabella`='".TABLE_PHOTOGALLERY."' AND (`categoria`='".b3_htmlize($name,true,"")."' OR `categoria`='".ksql_real_escape_string($name)."') AND `ll`='".strtoupper($ll)."' LIMIT 1";
		$results=ksql_query($query);

		if($row=ksql_fetch_array($results)) return $row;
		else return false;
	}

	// by id
	public function getCatById($idcat) {
		if(!$this->inited) $this->init();

		$query="SELECT * FROM ".TABLE_CATEGORIE." WHERE tabella='".TABLE_PHOTOGALLERY."' AND idcat='".intval($idcat)."' LIMIT 1";
		$results=ksql_query($query);

		if($row=ksql_fetch_array($results)) return $row;
		else return false;
	}

	
	/* comments */
	public function addComment($name,$email,$text,$idphg,$public="n") {
		if($public!="s") $public="n";
		$query="INSERT INTO ".TABLE_COMMENTI." (ip,data,tabella,id,autore,email,testo,public) VALUES('".$_SERVER['REMOTE_ADDR']."',NOW(),'".TABLE_PHOTOGALLERY."','".$idphg."','".b3_htmlize($name,true,"")."','".b3_htmlize($email,true,"")."','".b3_htmlize($text,true,"")."','".$public."')";
		ksql_query($query);
		$idcomm=ksql_insert_id();
		return $idcomm;
		}
	public function getComments($idnews) {
		$output=array();
		$query="SELECT * FROM ".TABLE_COMMENTI." WHERE tabella='".TABLE_PHOTOGALLERY."' AND id='".intval($idnews)."' AND public='s' ORDER BY data";
		$results=ksql_query($query);
		for($i=0;$row=ksql_fetch_array($results);$i++) {
			$output[$i]=$row;
			$output[$i]['dataleggibile']=preg_replace("/(\d{4}).(\d{2}).(\d{2}) (\d{2}).(\d{2}).*/","$3-$2-$1 $4:$5",$row['data']);
			}
		return $output;
		}

	}
