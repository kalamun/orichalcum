<?
/* (c) Kalamun.org - GPL v3 */

class kaPages {
	protected $kaComments,$kaImgallery,$kaDocgallery;

	public function kaPages() {
		require_once($_SERVER['DOCUMENT_ROOT'].'/'.ADMINDIR.'inc/comments.lib.php');
		require_once($_SERVER['DOCUMENT_ROOT'].'/'.ADMINDIR.'inc/imgallery.lib.php');
		require_once($_SERVER['DOCUMENT_ROOT'].'/'.ADMINDIR.'inc/docgallery.lib.php');
		require_once($_SERVER['DOCUMENT_ROOT'].'/'.ADMINDIR.'inc/metadata.lib.php');
		require_once($_SERVER['DOCUMENT_ROOT'].'/'.ADMINDIR.'menu/menu.lib.php');
		$this->kaComments=new kaComments();
		$this->kaImgallery=new kaImgallery();
		$this->kaDocgallery=new kaDocgallery();
		$this->kaMetadata=new kaMetadata();
		$this->kaMenu=new kaMenu();
		}

	public function get($vars) {
		if(!is_array($vars)) $vars=array("idpag"=>$vars);

		$query="SELECT * FROM ".TABLE_PAGINE." WHERE ";
		if(isset($vars['idpag'])) $query.=" `idpag`='".mysql_real_escape_string($vars['idpag'])."' AND ";
		if(isset($vars['dir'])) $query.=" `dir`='".mysql_real_escape_string($vars['dir'])."' AND ";
		if(isset($vars['ll'])) $query.=" `ll`='".mysql_real_escape_string($vars['ll'])."' AND ";
		$query.=" `idpag`>0 LIMIT 1";
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		$output=$row;

		$output['traduzioni']=array();
		foreach(explode("|",$row['traduzioni']) as $t) {
			$ll=substr($t,0,2);
			$id=intval(substr($t,3));
			if($ll!=""&&$id!=0) $output['traduzioni'][$ll]=$id;
			}

		$output['commentiOnline']=$this->kaComments->count(TABLE_PAGINE,$row['idpag'],"public='s'");
		$output['commentiTot']=$this->kaComments->count(TABLE_PAGINE,$row['idpag']);
		$output['imgallery']=$this->kaImgallery->getList(TABLE_PAGINE,$row['idpag']);
		$output['docgallery']=$this->kaDocgallery->getList(TABLE_PAGINE,$row['idpag']);
		$output['conversions']=$this->getConversions(array("idpag"=>$row['idpag']));
		return $output;
		}

	public function getTitleById($idpag) {
		$query="SELECT `titolo`,`dir`,`idpag`,`riservata` FROM ".TABLE_PAGINE." WHERE `idpag`='".intval($idpag)."' LIMIT 1";
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		return $row;
		}

	public function getQuickList($vars) {
		if(!isset($vars['start'])) $vars['start']=0;
		if(!isset($vars['limit'])) $vars['limit']=999;
		$output=array();
		$query="SELECT * FROM ".TABLE_PAGINE." WHERE `idpag`>0 ";
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
	
	public function add($vars)
	{
		$log="";
		
		if(!isset($vars['dir'])) $vars['dir']="";
		if(!isset($vars['title'])) $vars['title']="";
		if(!isset($vars['categories'])) $vars['categories']=",";
		
		if($vars['dir']=="") $vars['dir']=preg_replace("/[\s\?]*/","-",strtolower($vars['title']));
		if($vars['dir']=="") $vars['dir']=strtolower(strftime("%d-%B-%Y-%H-%M-%S")).'.html';
		
		// check id dir already exists
		$query="SELECT (`idpag`) as `tot` FROM `".TABLE_PAGINE."` WHERE `dir`='".mysql_real_escape_string($vars['dir'])."' AND `ll`='".mysql_real_escape_string($_SESSION['ll'])."' LIMIT 1";
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		if($row['tot']>0) $vars['dir']=rand(100000,999999).$vars['dir'];
		
		// insert
		$query="INSERT INTO ".TABLE_PAGINE." (
				`created`,
				`modified`,
				`titolo`,
				`sottotitolo`,
				`anteprima`,
				`testo`,
				`photogallery`,
				`categorie`,
				`ll`,
				`dir`,
				`template`,
				`layout`,
				`traduzioni`,
				`riservata`,
				`allowcomments`,
				`allowconversions`,
				`featuredimage`)
			VALUES(
				NOW(),
				NOW(),
				'".mysql_real_escape_string($vars['title'])."',
				'',
				'<p></p>',
				'<p></p>',
				',',
				'".mysql_real_escape_string($vars['categories'])."',
				'".mysql_real_escape_string($_SESSION['ll'])."',
				'".mysql_real_escape_string($vars['dir'])."',
				'',
				'',
				'',
				's',
				'n',
				false,
				0
			)";
		if(!mysql_query($query)) $log='Pages:Errors occurred while saving';
		else $id=mysql_insert_id();

		if($log!="") return $log;

		//if the page is a translated version of another page
		if(isset($vars['translation_id'])&&$vars['translation_id']!="") {
			$page=$this->get($vars['translation_id']);
			// first of all, clear translations from previous+current pages
			foreach($page['traduzioni'] as $k=>$v) {
				if($v!="") $this->removePageFromTranslations($v);
				}
			// translation has this format: |LL=idpag|LL=idpag|...
			$page['traduzioni'][$_SESSION['ll']]=$id;
			$translations="|";
			foreach($page['traduzioni'] as $k=>$v) {
				$translations.=$k."=".$v."|";
				}
			// then set the new translations in the current pages
			foreach($page['traduzioni'] as $k=>$v) {
				if($v!="") {
					$this->setTranslations($v,$translations);
					}
				}
			}

		//copy contents from another page
		if(isset($vars['copyfrom']))
		{
			$query="SELECT * FROM `".TABLE_PAGINE."` WHERE `idpag`=".mysql_real_escape_string($vars['copyfrom'])." LIMIT 1";
			$results=mysql_query($query);
			if($row=mysql_fetch_array($results))
			{
				$query="UPDATE ".TABLE_PAGINE." SET
					`sottotitolo`='".mysql_real_escape_string($row['sottotitolo'])."',
					`anteprima`='".mysql_real_escape_string($row['anteprima'])."',
					`testo`='".mysql_real_escape_string($row['testo'])."',
					`categorie`='".mysql_real_escape_string($row['categorie'])."',
					`allowcomments`='".mysql_real_escape_string($row['allowcomments'])."',
					`allowconversions`='".mysql_real_escape_string($row['allowconversions'])."',
					`featuredimage`='".mysql_real_escape_string($row['featuredimage'])."',
					`template`='".mysql_real_escape_string($row['template'])."',
					`layout`='".mysql_real_escape_string($row['layout'])."',
					`photogallery`='".mysql_real_escape_string($row['photogallery'])."'
					WHERE `idpag`=".mysql_real_escape_string($id)." LIMIT 1";
				if(!mysql_query($query)) $log='Pages:Errors occurred while copying contents';
				
				// copy conversions
				$this->copyConversions($row['idpag'],$id);

				// copy metadata
				foreach($this->kaMetadata->getList(TABLE_PAGINE,$row['idpag']) as $ka=>$v)
				{
					$this->kaMetadata->set(TABLE_PAGINE,$id,$ka,$v);
				}
			}
		}

		if($log!="") return $log;

		//add to menu
		if(!empty($vars['addtomenu']))
		{
			$query="SELECT `idpag`,`titolo`,`dir` FROM `".TABLE_PAGINE."` WHERE `idpag`='".$id."' AND `ll`='".mysql_real_escape_string($_SESSION['ll'])."' LIMIT 1";
			$results=mysql_query($query);
			if($page=mysql_fetch_array($results))
			{
				$mvars=array();
				$mvars['title']=$page['titolo'];
				$mvars['dir']=$page['dir'];
				$mvars['idpag']=$page['idpag'];
				$addtomenu=explode(",",$vars['addtomenu']);
				$mvars['idmenu']=$addtomenu[0];
				$mvars['where']=$addtomenu[1];
				$log=$this->kaMenu->addElement($mvars);

				if($log==false) return 'Pages:An error occurred while inserting page into menu';
			}
		}
		
		return $id;
	}

	public function update($idpag,$vars)
	{
		$offline='n';

		if(empty($vars['categories'])) $vars['categories']=",";
		
		//modifico o inserisco il record
		$query="UPDATE ".TABLE_PAGINE." SET ";
			if(isset($vars['title'])) $query.="`titolo`='".mysql_real_escape_string($vars['title'])."',";
			if(isset($vars['subtitle'])) $query.="`sottotitolo`='".mysql_real_escape_string($vars['subtitle'])."',";
			if(isset($vars['preview'])) $query.="`anteprima`='".b3_htmlize($vars['preview'],true)."',";
			if(isset($vars['text'])) $query.="`testo`='".b3_htmlize($vars['text'],true)."',";
			if(isset($vars['photogallery'])) $query.="`photogallery`='".b3_htmlize($vars['photogallery'],true)."',";
			if(isset($vars['dir'])) $query.="`dir`='".mysql_real_escape_string($vars['dir'])."',";
			if(isset($vars['template'])) $query.="`template`='".mysql_real_escape_string($vars['template'])."',";
			if(isset($vars['layout'])) $query.="`layout`='".mysql_real_escape_string($vars['layout'])."',";
			if(isset($vars['featuredimage'])) $query.="`featuredimage`='".intval($vars['featuredimage'])."',";
			if(isset($vars['allowcomments'])) $query.="`allowcomments`='".intval($vars['allowcomments'])."',";
			if(isset($vars['allowconversions'])) $query.="`allowconversions`='".intval($vars['allowconversions'])."',";
			if(isset($vars['offline'])&&($vars['offline']=='s'||$vars['offline']=='n')) $query.="`riservata`='".mysql_real_escape_string($vars['offline'])."',";
			$query.="`categorie`='".mysql_real_escape_string($vars['categories'])."', `modified`=NOW() WHERE `idpag`='".intval($_GET['idpag'])."' LIMIT 1";
		if(!mysql_query($query)) return "Pages:An error occurred while saving into the database";
		else $id=$_GET['idpag'];

		foreach($vars as $ka=>$v)
		{
			if(substr($ka,0,4)=="seo_") $this->kaMetadata->set(TABLE_PAGINE,$idpag,$ka,$v);
		}
	
		return true;
	}

	public function setTranslations($idpag,$translations) {
		$query="UPDATE ".TABLE_PAGINE." SET `traduzioni`='".mysql_real_escape_string($translations)."' WHERE `idpag`='".mysql_real_escape_string($idpag)."' LIMIT 1";
		if(mysql_query($query)) return true;
		else return false;
		}
	public function removePageFromTranslations($idpag) {
		$query="UPDATE ".TABLE_PAGINE." SET `traduzioni`=REPLACE(`traduzioni`,'=".mysql_real_escape_string($idpag)."|','=|') WHERE `traduzioni` LIKE '%=".mysql_real_escape_string($idpag)."%|'";
		if(mysql_query($query)) return true;
		else return false;
		}

	public function switchDraft($idpag) {
		$page=$this->getTitleById($idpag);
		$draft=$page['riservata']=='n'?'s':'n';
		$this->update($idpag,array("riservata"=>$draft));
		return $draft;
		}
		


	/* CONVERSIONS */
	public function getConversions($vars)
	{
		if(!is_array($vars)) $vars=array("idpag"=>$vars);

		$query="SELECT * FROM ".TABLE_CONVERSIONS." WHERE ";
		if(isset($vars['idpag'])) $query.=" `idpag`='".mysql_real_escape_string($vars['idpag'])."' AND ";
		$query.=" `idpag`>0 LIMIT 1";
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		if($row==false) {
			//get an empty array;
			$row=array();
			$query="DESCRIBE ".TABLE_CONVERSIONS;
			$results=mysql_query($query);
			while($r=mysql_fetch_array($results)) {
				$row[$r['Field']]="";
				}
			}
		$output=$row;

		$output['variables']=array();
		foreach(explode("\n",trim($row['variables'])) as $line) {
			if(trim($line)!="") {
				$line=explode("\t",trim($line));
				if(!isset($line[0])) continue;
				if(!isset($line[1])) $line[1]="";
				if(!isset($line[2])) $line[2]="";
				$output['variables'][]=array("variable_name"=>$line[0],"correspondence"=>$line[1],"mandatory"=>$line[2]);
				}
			}

		$output['create_member_username']="";
		$output['create_member_password']="";
		$output['create_member_expiration']="";
		$output['create_member_affiliation']="";
		foreach(explode("\n",$output['create_member_config']) as $l) {
			if(substr($l,0,2)=="u:") $output['create_member_username']=substr($l,2);
			if(substr($l,0,2)=="p:") $output['create_member_password']=substr($l,2);
			if(substr($l,0,2)=="e:") $output['create_member_expiration']=substr($l,2);
			if(substr($l,0,2)=="a:") $output['create_member_affiliation']=substr($l,2);
			}

		return $output;
	}

	public function updateConversions($vars)
	{
		if(!is_array($vars)) return false;
		if(!isset($vars['idpag'])) return false;

		//check if conversions record exists for the gived page. if not, create.
		$query="SELECT `idconv` FROM `".TABLE_CONVERSIONS."` WHERE `idpag`=".mysql_real_escape_string($vars['idpag'])." LIMIT 1";
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		if($row['idconv']==false) {
			$query="INSERT INTO `".TABLE_CONVERSIONS."` (`idpag`,`moderate`,`create_member`,`create_member_config`,`newsletters_add`,`newsletters_remove`,`private_dir`,`notification_emails`,`notification_from`,`notification_subject`,`notification_text`,`followup_from`,`followup_subject`,`followup_text`,`conversion_code`,`fail_code`,`variables`) VALUES ('".mysql_real_escape_string($vars['idpag'])."',false,false,'','','',false,'','','','','','','','','','')";
			mysql_query($query);
			$row['idconv']=mysql_insert_id();
			}
		if($row['idconv']==false) return false;

		//now write the update query
		$query="UPDATE `".TABLE_CONVERSIONS."` SET ";
		if(isset($vars['moderate'])) $query.="`moderate`=".($vars['moderate']>0?$vars['moderate']:"0").",";
		if(isset($vars['create_member'])) $query.="`create_member`=".($vars['create_member']==true?'true':'false').",";
		if(isset($vars['create_member_config'])) $query.="`create_member_config`='".mysql_real_escape_string($vars['create_member_config'])."',";
		if(isset($vars['private_dir'])) $query.="`private_dir`=".($vars['private_dir']==true?'true':'false').",";
		if(isset($vars['newsletters_add'])) $query.="`newsletters_add`='".mysql_real_escape_string($vars['newsletters_add'])."',";
		if(isset($vars['newsletters_remove'])) $query.="`newsletters_remove`='".mysql_real_escape_string($vars['newsletters_remove'])."',";
		if(isset($vars['variables'])) $query.="`variables`='".mysql_real_escape_string($vars['variables'])."',";
		if(isset($vars['notification_from'])) $query.="`notification_from`='".mysql_real_escape_string($vars['notification_from'])."',";
		if(isset($vars['notification_emails'])) $query.="`notification_emails`='".mysql_real_escape_string($vars['notification_emails'])."',";
		if(isset($vars['notification_subject'])) $query.="`notification_subject`='".mysql_real_escape_string($vars['notification_subject'])."',";
		if(isset($vars['notification_text'])) $query.="`notification_text`='".b3_htmlize($vars['notification_text'],true)."',";
		if(isset($vars['followup_from'])) $query.="`followup_from`='".mysql_real_escape_string($vars['followup_from'])."',";
		if(isset($vars['followup_subject'])) $query.="`followup_subject`='".mysql_real_escape_string($vars['followup_subject'])."',";
		if(isset($vars['followup_text'])) $query.="`followup_text`='".b3_htmlize($vars['followup_text'],true)."',";
		if(isset($vars['conversion_code'])) $query.="`conversion_code`='".mysql_real_escape_string($vars['conversion_code'])."',";
		if(isset($vars['fail_code'])) $query.="`fail_code`='".mysql_real_escape_string($vars['fail_code'])."',";
		$query.="`idpag`=".mysql_real_escape_string($vars['idpag'])." WHERE `idconv`=".mysql_real_escape_string($row['idconv'])." LIMIT 1";
		if(mysql_query($query)) return true;
		else return false;
	}
	
	// copy conversions from idpag to a new idpag
	public function copyConversions($from,$to)
	{
		// check if conversions are defined
		$query="SELECT count(`idconv`) as `tot` FROM `".TABLE_CONVERSIONS."` WHERE `idpag`='".intval($from)."' LIMIT 1";
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		if($row['tot']==0) return true; // no conversions defined
		
		// duplicate
		$query="INSERT INTO `".TABLE_CONVERSIONS."` (`idpag`,`moderate`,`create_member`,`create_member_config`,`newsletters_add`,`newsletters_remove`,`private_dir`,`notification_emails`,`notification_from`,`notification_subject`,`notification_text`,`followup_from`,`followup_subject`,`followup_text`,`conversion_code`,`fail_code`,`variables`)
			(SELECT `idpag`,`moderate`,`create_member`,`create_member_config`,`newsletters_add`,`newsletters_remove`,`private_dir`,`notification_emails`,`notification_from`,`notification_subject`,`notification_text`,`followup_from`,`followup_subject`,`followup_text`,`conversion_code`,`fail_code`,`variables` FROM `".TABLE_CONVERSIONS."` WHERE `idpag`='".intval($from)."' LIMIT 1)";
		if(!mysql_query($query)) return false;
		$id=mysql_insert_id();
		
		// change the idpag reference to the new one
		$query="UPDATE `".TABLE_CONVERSIONS."` SET `idpag`='".intval($to)."' WHERE `idconv`='".$id."' LIMIT 1";
		if(!mysql_query($query)) return false;
		return true;
	}
}
?>