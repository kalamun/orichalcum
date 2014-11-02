<?
/* (c) Kalamun.org - GPL v3 */

class kaNews {
	protected $ll='',$kaComments,$kaCategorie,$kaImgallery,$kaDocgallery,$kaMetadata;
	
	public function kaNews() {
		require_once($_SERVER['DOCUMENT_ROOT'].ADMINDIR.'inc/comments.lib.php');
		require_once($_SERVER['DOCUMENT_ROOT'].ADMINDIR.'inc/imgallery.lib.php');
		require_once($_SERVER['DOCUMENT_ROOT'].ADMINDIR.'inc/docgallery.lib.php');
		require_once($_SERVER['DOCUMENT_ROOT'].ADMINDIR."inc/categorie.lib.php");
		require_once($_SERVER['DOCUMENT_ROOT'].ADMINDIR."inc/metadata.lib.php");
		$this->kaComments=new kaComments();
		$this->kaCategorie=new kaCategorie();
		$this->kaImgallery=new kaImgallery();
		$this->kaDocgallery=new kaDocgallery();
		$this->kaMetadata=new kaMetadata();
		$this->ll=$_SESSION['ll'];
		}

	public function add($values,$sottotitolo="",$anteprima="",$testo="",$categorie="",$data="",$pubblica="",$scadenza="",$template="",$layout="",$traduzioni="",$dir="",$home="s",$calendario="s",$iduser=false,$ll=false) {
		if(!is_array($values)) {
			$title=$values;
			$values=array();
			$values['title']=$title;
			$values['subtitle']=$sottotitolo;
			$values['preview']=$anteprima;
			$values['text']=$testo;
			$values['categories']=$categorie;
			$values['creation_date']=$data;
			$values['public_date']=$pubblica;
			$values['expiration_date']=$scadenza;
			$values['template']=$template;
			$values['layout']=$layout;
			$values['translations']=$traduzioni;
			$values['dir']=$dir;
			$values['home']=$home;
			$values['calendar']=$calendario;
			$values['iduser']=$iduser;
			$values['ll']=$ll;
			}
		
		if(!isset($values['ll'])||$values['ll']==false) $values['ll']=$_SESSION['ll'];
		if(!isset($values['iduser'])||$values['iduser']==false) $values['iduser']=$_SESSION['iduser'];
		if(!isset($values['home'])||$values['home']!="s") $values['home']="n";
		if(!isset($values['calendar'])||$values['calendar']!="s") $values['calendar']="n";
		if(!isset($values['photogallery'])) $values['photogallery']=",";
		
		if(isset($values['title'])) $values['title']=mysql_real_escape_string($values['title']);
		if(isset($values['subtitle'])) $values['subtitle']=mysql_real_escape_string($values['subtitle']);
		if(isset($values['preview'])) $values['preview']=mysql_real_escape_string($values['preview']);
		if(isset($values['text'])) $values['text']=mysql_real_escape_string($values['text']);
		if(isset($values['template'])) $values['template']=mysql_real_escape_string($values['template']);
		if(isset($values['translations'])) $values['translations']=mysql_real_escape_string($values['translations']);
		if(isset($values['photogallery'])) $values['photogallery']=mysql_real_escape_string($values['photogallery']);

		if(!isset($values['dir'])&&isset($values['titolo'])) $values['dir']=preg_replace("/[^\w\/\.\-\x{C0}-\x{D7FF}\x{2C00}-\x{D7FF}]+/","-",strtolower($values['titolo']));
		if(!isset($values['dir'])||$values['dir']==""||$values['dir']=="-.html") $values['dir']=rand(10,999999);
		if(strlen($values['dir'])>64) $values['dir']=substr(str_replace(".html","",$values['dir']),0,64).".html";
		$values['dir']=mysql_real_escape_string($values['dir']);
		$query="SELECT `idnews` FROM `".TABLE_NEWS."` WHERE `dir`='".$values['dir']."' LIMIT 1";
		$results=mysql_query($query);
		if(mysql_fetch_array($results)||trim($values['dir'])=="") {
			$values['dir']=rand(100,999).'-'.$values['dir'];
			if(strlen($values['dir'])>64) $values['dir']=substr($values['dir'],0,64);
			$query="SELECT `idnews` FROM ".TABLE_NEWS." WHERE `dir`='".$values['dir']."' LIMIT 1";
			$results=mysql_query($query);
			if(mysql_fetch_array($results)) {
				$values['dir']=rand(100,999).$values['dir'];
				if(strlen($values['dir'])>64) $dir=substr($values['dir'],0,64);
				}
			}

		/* copy from another post, then update with the new values */
		if(isset($values['copyfrom'])&&is_numeric($values['copyfrom'])) {
			$query="SELECT * FROM `".TABLE_NEWS."` WHERE `idnews`='".intval($values['copyfrom'])."' LIMIT 1";
			$results=mysql_query($query);
			if($row=mysql_fetch_array($results)) {
				$query="INSERT INTO `".TABLE_NEWS."` (`titolo`,`sottotitolo`,`anteprima`,`testo`,`featuredimage`,`photogallery`,`data`,`pubblica`,`starting_date`,`scadenza`,`modified`,`template`,`layout`,`traduzioni`,`categorie`,`dir`,`home`,`calendario`,`iduser`,`ll`)
					SELECT `titolo`,`sottotitolo`,`anteprima`,`testo`,`featuredimage`,`photogallery`,`data`,`pubblica`,`starting_date`,`scadenza`,`modified`,`template`,`layout`,`traduzioni`,`categorie`,`dir`,`home`,`calendario`,`iduser`,`ll` FROM `".TABLE_NEWS."` WHERE `idnews`='".$values['copyfrom']."' LIMIT 1
					";

				if(mysql_query($query)) {
					$idnews=mysql_insert_id();
					
					//update with the new values
					$query="UPDATE `".TABLE_NEWS."` SET ";
					if(isset($values['title'])) $query.="`titolo`='".$values['title']."',";
					if(isset($values['subtitle'])) $query.="`sottotitolo`='".$values['subtitle']."',";
					if(isset($values['preview'])) $query.="`anteprima`='".$values['preview']."',";
					if(isset($values['text'])) $query.="`testo`='".$values['text']."',";
					if(isset($values['public_date'])) $query.="`pubblica`='".$values['public_date']."',";
					if(isset($values['starting_date'])) $query.="`starting_date`='".$values['starting_date']."',";
					if(isset($values['expiration_date'])) $query.="`scadenza`='".$values['expiration_date']."',";
					if(isset($values['template'])) $query.="`template`='".$values['template']."',";
					if(isset($values['layout'])) $query.="`layout`='".$values['layout']."',";
					if(isset($values['translations'])) $query.="`traduzioni`='".$values['translations']."',";
					if(isset($values['categories'])) $query.="`categorie`='".$values['categories']."',";
					if(isset($values['dir'])) $query.="`dir`='".$values['dir']."',";
					if(isset($values['home'])) $query.="`home`='".$values['home']."',";
					if(isset($values['calendar'])) $query.="`calendario`='".$values['calendar']."',";
					$query.="`data`='".$values['creation_date']."',`iduser`='".$values['iduser']."',`ll`='".$values['ll']."' WHERE `idnews`=".$idnews." LIMIT 1";
					if(!mysql_query($query)) return false; //error updating with news values
					
					//copy metadata
					foreach($this->kaMetadata->getList(TABLE_NEWS,$values['copyfrom']) as $ka=>$v) {
						$this->kaMetadata->set(TABLE_NEWS,$idnews,$ka,$v);
						}
					
					//copy document gallery
					$this->copyDocumentGallery($values['copyfrom'],$idnews);
					
					return $idnews;
					}
				else return false; //error inserting the new record
				}
			else return false; //the source record not exists
			}
		
		/* insert a new post */
		else {
			if(!isset($values['title'])) $values['title']="";
			if(!isset($values['subtitle'])) $values['subtitle']="";
			if(!isset($values['preview'])) $values['preview']="";
			if(!isset($values['text'])) $values['text']="";
			if(!isset($values['creation_date'])) $values['creation_date']=date("Y-m-d H:i:s");
			if(!isset($values['public_date'])) $values['public_date']=date("Y-m-d H:i:s");
			if(!isset($values['expiration_date'])) $values['expiration_date']=date("Y-m-d H:i:s");
			if(!isset($values['starting_date'])) $values['starting_date']=$values['expiration_date'];
			if(!isset($values['template'])) $values['template']="";
			if(!isset($values['layout'])) $values['layout']="";
			if(!isset($values['translations'])) $values['translations']="";
			if(!isset($values['categories'])) $values['categories']="";
			if(!isset($values['dir'])) $values['dir']="";
			if(!isset($values['home'])) $values['home']="";
			if(!isset($values['calendar'])) $values['calendar']="";
			$query="INSERT INTO `".TABLE_NEWS."` (`titolo`,`sottotitolo`,`anteprima`,`testo`,`featuredimage`,`photogallery`,`data`,`pubblica`,`starting_date`,`scadenza`,`modified`,`template`,`layout`,`traduzioni`,`categorie`,`dir`,`home`,`calendario`,`iduser`,`ll`)
					VALUES('".$values['title']."','".$values['subtitle']."','".$values['preview']."','".$values['text']."',0,'".$values['photogallery']."','".$values['creation_date']."','".$values['public_date']."','".$values['starting_date']."','".$values['expiration_date']."',NOW(),'".$values['template']."','".$values['layout']."','".$values['translations']."','".$values['categories']."','".$values['dir']."','".$values['home']."','".$values['calendar']."','".$values['iduser']."','".$values['ll']."')";
			if(mysql_query($query)) return mysql_insert_id();
			}

		return false;
		}
	

	public function copyDocumentGallery($from,$to) {
		foreach($this->kaDocgallery->getList(TABLE_NEWS,$from) as $doc) {
			$this->kaDocgallery->add(TABLE_NEWS,$to,$doc['iddoc']);
			}
		}
	
		
	public function getList($conditions="",$ordine=false,$lang=false) {
		if($ordine==false) {
			require_once($_SERVER['DOCUMENT_ROOT'].ADMINDIR."inc/config.lib.php");
			$kaImpostazioni=new kaImpostazioni();
			$ordine=$kaImpostazioni->getVar('news-order',1);
			}
		if($lang==false) $lang=$this->ll;
		$output=array();
		if($conditions!="") $conditions="(".$conditions.")";

		$query="SELECT * FROM ".TABLE_NEWS." WHERE ";
		if($conditions!="") $query.="(".$conditions.") AND ";
		$query.="ll='".$lang."' ORDER BY ".$ordine;
		$results=mysql_query($query);
		for($i=0;$row=mysql_fetch_array($results);$i++) {
			$output[$i]=$row;
			$output[$i]['categorie']=array();
			foreach($this->kaCategorie->getList(TABLE_NEWS) as $cat) {
				if(strpos($row['categorie'],','.$cat['idcat'].',')!==false) $output[$i]['categorie'][]=$cat;
				}

			$output[$i]['commentiOnline']=$this->kaComments->count(TABLE_NEWS,$row['idnews'],"public='s'");
			$output[$i]['commentiTot']=$this->kaComments->count(TABLE_NEWS,$row['idnews']);
			}
		return $output;
		}

	public function getQuickList($vars) {
		if(!isset($vars['start'])) $vars['start']=0;
		if(!isset($vars['limit'])) $vars['limit']=999;
		$output=array();
		$query="SELECT * FROM ".TABLE_NEWS." WHERE `idnews`>0 ";
		if(isset($vars['match'])) $query.=" AND (`titolo` LIKE '%".mysql_real_escape_string($vars['match'])."%' OR `dir` LIKE '%".mysql_real_escape_string($vars['match'])."%')";
		if(isset($vars['ll'])) $query.=" AND `ll`='".mysql_real_escape_string($vars['ll'])."' ";
		if(isset($vars['exclude_ll'])) $query.=" AND `ll`<>'".mysql_real_escape_string($vars['exclude_ll'])."' ";
		$query.=" ORDER BY `titolo` LIMIT ".$vars['start'].",".$vars['limit'];
		$results=mysql_query($query);
		while($row=mysql_fetch_array($results)) {
			$output[]=$row;
			}
		return $output;
		}

	public function get($idnews) {
		$output=array();
		$query="SELECT * FROM ".TABLE_NEWS." WHERE idnews='".$idnews."' LIMIT 1";
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		$output=$row;
		$output['categorie']=array();
		foreach($this->kaCategorie->getList(TABLE_NEWS) as $cat) {
			if(strpos($row['categorie'],','.$cat['idcat'].',')!==false) $output['categorie'][]=$cat;
			}

		$output['traduzioni']=array();
		foreach(explode("|",$row['traduzioni']) as $t) {
			$ll=substr($t,0,2);
			$id=intval(substr($t,3));
			if($ll!=""&&$id!=0) $output['traduzioni'][$ll]=$id;
			}

		$output['commentiOnline']=$this->kaComments->count(TABLE_NEWS,$row['idnews'],"public='s'");
		$output['commentiTot']=$this->kaComments->count(TABLE_NEWS,$row['idnews']);
		$output['imgallery']=$this->kaImgallery->getList(TABLE_NEWS,$row['idnews']);
		$output['docgallery']=$this->kaDocgallery->getList(TABLE_NEWS,$row['idnews']);
		return $output;
		}

	public function getTitleById($idnews) {
		$query="SELECT `titolo`,`dir`,`idnews` FROM ".TABLE_NEWS." WHERE `idnews`='".intval($idnews)."' LIMIT 1";
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		return $row;
		}

	public function update($vars) {
		if(empty($vars['idnews'])) return false;
		if(empty($vars['ll'])) $ll=$_SESSION['ll'];
		if(empty($vars['iduser'])) $vars['iduser']=$_SESSION['iduser'];

		if(isset($vars['title'])) $vars['title']=mysql_real_escape_string($vars['title']);
		if(isset($vars['subtitle'])) $vars['subtitle']=mysql_real_escape_string($vars['subtitle']);
		if(isset($vars['preview'])) $vars['preview']=mysql_real_escape_string($vars['preview']);
		if(isset($vars['text'])) $vars['text']=mysql_real_escape_string($vars['text']);
		if(isset($vars['categories'])) $vars['categories']=mysql_real_escape_string($vars['categories']);
		if(isset($vars['template'])) $vars['template']=mysql_real_escape_string($vars['template']);
		if(isset($vars['layout'])) $vars['layout']=mysql_real_escape_string($vars['layout']);
		if(isset($vars['dir'])) $vars['dir']=mysql_real_escape_string($vars['dir']);

		$query="UPDATE ".TABLE_NEWS." SET ";
		if(isset($vars['title'])) $query.="`titolo`='".$vars['title']."',";
		if(isset($vars['subtitle'])) $query.="`sottotitolo`='".$vars['subtitle']."',";
		if(isset($vars['preview'])) $query.="`anteprima`='".$vars['preview']."',";
		if(isset($vars['text'])) $query.="`testo`='".$vars['text']."',";
		if(isset($vars['categories'])) $query.="`categorie`='".$vars['categories']."',";
		if(isset($vars['date_date'])) $query.="`data`='".$vars['date_date']."',";
		if(isset($vars['visible_date'])) $query.="`pubblica`='".$vars['visible_date']."',";
		if(isset($vars['starting_date'])) $query.="`starting_date`='".$vars['starting_date']."',";
		if(isset($vars['expiration_date'])) $query.="`scadenza`='".$vars['expiration_date']."',";
		if(isset($vars['template'])) $query.="`template`='".$vars['template']."',";
		if(isset($vars['layout'])) $query.="`layout`='".$vars['layout']."',";
		if(isset($vars['dir'])) $query.="`dir`='".$vars['dir']."',";
		if(isset($vars['home'])) $query.="`home`='".$vars['home']."',";
		if(isset($vars['calendar'])) $query.="`calendar`='".$vars['calendar']."',";
		if(isset($vars['iduser'])) $query.="`iduser`='".$vars['iduser']."',";
		if(isset($vars['featuredimage'])) $query.="`featuredimage`='".$vars['featuredimage']."',";
		if(isset($vars['photogallery'])) $query.="`photogallery`='".$vars['photogallery']."',";

		$query.="`modified`=NOW() WHERE `idnews`='".intval($vars['idnews'])."'";
		if(mysql_query($query)) return $vars['idnews'];
		else return false;
		}
		
	public function count($from=0,$to=0,$lang=false) {
		if($to==0) $to=9999;
		if($lang==false) $lang=$this->ll;
		$query="SELECT count(*) AS tot FROM ".TABLE_NEWS." WHERE ll='".$lang."' LIMIT ".$from.",".$to;
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		return $row['tot'];
		}

	public function delete($idnews) {
		$query="DELETE FROM ".TABLE_NEWS." WHERE `idnews`='".intval($idnews)."' LIMIT 1";
		if(mysql_query($query)) return $idnews;
		else return false;
		}

	public function setTranslations($idnews,$translations) {
		$query="UPDATE ".TABLE_NEWS." SET `traduzioni`='".mysql_real_escape_string($translations)."' WHERE `idnews`='".mysql_real_escape_string($idnews)."' LIMIT 1";
		if(mysql_query($query)) return true;
		else return false;
		}
	public function removePageFromTranslations($idnews) {
		$query="UPDATE ".TABLE_NEWS." SET `traduzioni`=REPLACE(`traduzioni`,'=".mysql_real_escape_string($idnews)."|','=|') WHERE `traduzioni` LIKE '%=".mysql_real_escape_string($idnews)."%|'";
		if(mysql_query($query)) return true;
		else return false;
		}

	}
?>