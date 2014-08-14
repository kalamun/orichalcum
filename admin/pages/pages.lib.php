<?
/* (c) Kalamun.org - GPL v3 */

class kaPages {
	protected $kaComments,$kaImgallery,$kaDocgallery;

	public function kaPages() {
		require_once($_SERVER['DOCUMENT_ROOT'].'/'.ADMINDIR.'inc/comments.lib.php');
		require_once($_SERVER['DOCUMENT_ROOT'].'/'.ADMINDIR.'inc/imgallery.lib.php');
		require_once($_SERVER['DOCUMENT_ROOT'].'/'.ADMINDIR.'inc/docgallery.lib.php');
		$this->kaComments=new kaComments();
		$this->kaImgallery=new kaImgallery();
		$this->kaDocgallery=new kaDocgallery();
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

	public function update($idpag,$vars) {
		$query="UPDATE ".TABLE_PAGINE." SET ";
		if(isset($vars['riservata'])) $query.="`riservata`='".mysql_real_escape_string($vars['riservata'])."',";
		$query=rtrim($query,",");
		$query.=" WHERE `idpag`=".mysql_real_escape_string($idpag)." LIMIT 1";
		if(mysql_query($query)) return true;
		else return false;
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
	public function getConversions($vars) {
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

	public function updateConversions($vars) {
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
	}
?>