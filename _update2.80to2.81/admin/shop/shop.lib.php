<?php 
/* (c) Kalamun.org - GPL v3 */

class kaShop {
	protected $ll='',$kaComments,$kaCategorie,$kaImgallery,$kaDocgallery,$kaImpostazioni,
			$categoriesList;
	
	public function kaShop() {
		require_once($_SERVER['DOCUMENT_ROOT'].'/'.ADMINDIR.'inc/comments.lib.php');
		require_once($_SERVER['DOCUMENT_ROOT'].'/'.ADMINDIR.'inc/imgallery.lib.php');
		require_once($_SERVER['DOCUMENT_ROOT'].'/'.ADMINDIR.'inc/docgallery.lib.php');
		require_once($_SERVER['DOCUMENT_ROOT'].'/'.ADMINDIR."inc/categorie.lib.php");
		require_once($_SERVER['DOCUMENT_ROOT'].'/'.ADMINDIR."inc/config.lib.php");
		$this->kaComments=new kaComments();
		$this->kaCategorie=new kaCategorie();
		$this->kaImgallery=new kaImgallery();
		$this->kaDocgallery=new kaDocgallery();
		$this->kaImpostazioni=new kaImpostazioni();
		$this->ll=$_SESSION['ll'];
		$this->categoriesList=$this->kaCategorie->getList(TABLE_SHOP_ITEMS);
		}

	public function addItem($dir,$categorie,$titolo,$sottotitolo,$anteprima,$testo,$prezzo,$scontato,$created,$public,$expired,$qta=false,$weight=false,$layout=false,$ll=false) {
		if($ll==false) $ll=$_SESSION['ll'];
		
		if(!isset($dir)&&isset($titolo)) $dir=preg_replace("/[^\w\/\.\-\x{C0}-\x{D7FF}\x{2C00}-\x{D7FF}]+/","-",strtolower($titolo));
		if(!isset($dir)||$dir==""||$dir=="-.html") $dir=rand(10,999999);
		if(strlen($dir)>64) $dir=substr(str_replace(".html","",$dir),0,64).".html";
		$dir=ksql_real_escape_string($dir);
		
		$titolo=ksql_real_escape_string($titolo);
		$sottotitolo=ksql_real_escape_string($sottotitolo);
		$anteprima=ksql_real_escape_string($anteprima);
		$testo=ksql_real_escape_string($testo);
		if($qta==false) $qta=0;
		if($weight==false) $weight=0;
		if($layout==false) $layout="";
		$prezzo=number_format($prezzo,2,'.','');
		$scontato=number_format($scontato,2,'.','');
		
		$query="SELECT count(*) as tot FROM ".TABLE_SHOP_ITEMS." WHERE ll='".$ll."' ORDER BY ordine DESC LIMIT 1";
		$results=ksql_query($query);
		$row=ksql_fetch_array($results);
		$ordine=$row['tot']+1;

		$query="INSERT INTO ".TABLE_SHOP_ITEMS." (`online`,`dir`,`categorie`,`productcode`,`titolo`,`sottotitolo`,`anteprima`,`testo`,`featuredimage`,`photogallery`,`prezzo`,`scontato`,`created`,`public`,`expired`,`modified`,`qta`,`weight`,`layout`,`privatearea`,`rating`,`votes`,`customfields`,`manufacturer`,`ordine`,`traduzioni`,`ll`) VALUES('n','".$dir."','".$categorie."','','".$titolo."','".$sottotitolo."','".$anteprima."','".$testo."',0,',','".$prezzo."','".$scontato."','".$created."','".$public."','".$expired."',NOW(),'".$qta."','".$weight."','".$layout."','','0','0','',0,'".$ordine."','','".$ll."')";
		if(ksql_query($query)) return ksql_insert_id();
		else return false;
		}
		
	public function countItems($conditions="",$lang=false)
	{
		if($lang==false) $lang=$this->ll;
		
		$query="SELECT count(`idsitem`) as `tot` FROM `".TABLE_SHOP_ITEMS."` WHERE ";
		if($conditions!="") $query.="(".$conditions.") AND ";
		$query.="`ll`='".$lang."'";
		$results=ksql_query($query);
		if($row=ksql_fetch_array($results)) return $row['tot'];
		return 0;
	}

	public function getItemsList($conditions="",$ordine=false,$lang=false) {
		if($ordine==false) {
			$ordine=$this->kaImpostazioni->getVar('shop-order',1);
			}
		if($lang==false) $lang=$this->ll;
		$output=array();

		$query="SELECT * FROM ".TABLE_SHOP_ITEMS." WHERE ";
		if($conditions!="") $query.="(".$conditions.") AND ";
		$query.="`ll`='".$lang."' ORDER BY ".$ordine.",`titolo`,`productcode`,`idsitem`";
		$results=ksql_query($query);
		for($i=0;$row=ksql_fetch_array($results);$i++) {
			$output[$i]=$row;
			$output[$i]['categorie']=array();
			foreach($this->categoriesList as $cat) {
				if(strpos($row['categorie'],','.$cat['idcat'].',')!==false) $output[$i]['categorie'][]=$cat;
				}

			$output[$i]['commentiOnline']=$this->kaComments->count(TABLE_SHOP_ITEMS,$row['idsitem'],"public='s'");
			$output[$i]['commentiTot']=$this->kaComments->count(TABLE_SHOP_ITEMS,$row['idsitem']);
			}
		return $output;
		}

	public function getQuickList($vars) {
		if(!isset($vars['start'])) $vars['start']=0;
		if(!isset($vars['limit'])) $vars['limit']=999;
		if(!isset($vars['ll'])) $vars['ll']=$this->ll;
		if(!isset($vars['orderby'])) $vars['orderby']='`titolo`';
		$output=array();
		$query="SELECT * FROM `".TABLE_SHOP_ITEMS."` WHERE `idsitem`>0 ";
		if($vars['conditions']!="") $query.=" AND (".$vars['conditions'].") ";
		if(isset($vars['match'])) $query.=" AND (`titolo` LIKE '%".ksql_real_escape_string($vars['match'])."%' OR `dir` LIKE '%".ksql_real_escape_string($vars['match'])."%')";
		if(isset($vars['ll'])) $query.=" AND `ll`='".ksql_real_escape_string($vars['ll'])."' ";
		if(isset($vars['exclude_ll'])) $query.=" AND `ll`<>'".ksql_real_escape_string($vars['exclude_ll'])."' ";
		$query.=" ORDER BY ".$vars['orderby'].",`titolo`,`productcode`,`idsitem` LIMIT ".$vars['start'].",".$vars['limit'];
		$results=ksql_query($query);
		for($i=0;$row=ksql_fetch_array($results);$i++) {
			$output[$i]=$row;
			$output[$i]['categorie']=array();
			foreach($this->categoriesList as $cat) {
				if(strpos($row['categorie'],','.$cat['idcat'].',')!==false) $output[$i]['categorie'][]=$cat;
				}
			}
		return $output;
		}

	public function getItem($idsitem) {
		require_once($_SERVER['DOCUMENT_ROOT'].'/'.ADMINDIR."inc/comments.lib.php");
		$this->kaComments=new kaComments();

		$output=array();
		$query="SELECT * FROM ".TABLE_SHOP_ITEMS." WHERE `idsitem`='".intval($idsitem)."' LIMIT 1";
		$results=ksql_query($query);
		if($row=ksql_fetch_array($results)) {
			$output=$row;

			$output['categorie']=array();
			foreach($this->kaCategorie->getList(TABLE_SHOP_ITEMS) as $cat) {
				if(strpos($row['categorie'],','.$cat['idcat'].',')!==false) $output['categorie'][]=$cat;
				}

			$output['privatearea']=explode("\n",trim($output['privatearea']));
			$output['traduzioni']=array();
			foreach(explode("|",$row['traduzioni']) as $t) {
				$ll=substr($t,0,2);
				$id=intval(substr($t,3));
				if($ll!=""&&$id!=0) $output['traduzioni'][$ll]=$id;
				}

			$output['customfields']=array();
			foreach($this->getCustomFields(explode(",",trim($row['categorie'],","))) as $field) {
				$output['customfields'][$field['idsfield']]="";
				}
			foreach(explode("</field>",trim($row['customfields'])) as $f) {
				$f=trim($f);
				if(!empty($f)) {
					preg_match('/^<field id="(\d+)">(.*)/s',$f,$match);
					if(isset($match[1])) $output['customfields'][$match[1]]=$match[2];
					}
				}

			$output['commentiOnline']=$this->kaComments->count(TABLE_SHOP_ITEMS,$row['idsitem'],"public='s'");
			$output['commentiTot']=$this->kaComments->count(TABLE_SHOP_ITEMS,$row['idsitem']);
			$output['imgallery']=$this->kaImgallery->getList(TABLE_SHOP_ITEMS,$row['idsitem']);
			$output['docgallery']=$this->kaDocgallery->getList(TABLE_SHOP_ITEMS,$row['idsitem']);
			return $output;
			}
		else return false;
		}

	public function getItemCategories() {
		/* returns an array of all categories assigned to shop items */
		return $this->kaCategorie->getList(TABLE_SHOP_ITEMS);
		}

	public function getTitleById($idsitem) {
		$query="SELECT `titolo`,`dir`,`idsitem` FROM ".TABLE_SHOP_ITEMS." WHERE `idsitem`='".intval($idsitem)."' LIMIT 1";
		$results=ksql_query($query);
		$row=ksql_fetch_array($results);
		return $row;
		}

	public function updateItem($idsitem,$vars)
	{
	
		if(empty($vars['ll'])) $vars['ll']=$_SESSION['ll'];
		
		foreach(array("productcode","titolo","sottotitolo","anteprima","testo","template","layout","dir","privatearea") as $field)
		{
			if(isset($vars[$field])) $vars[$field]=ksql_real_escape_string($vars[$field]);
		}
		
		if(isset($vars['online']) && $vars['online']!="y") $vars['online']='n';

		$cf="";
		//customfields: for each field of the category, get the value or, if missing, assign an empty value
		if(isset($vars['categories']))
		{
			$cfvars = array();
			$cfvars["categories"] = explode(",",trim($vars['categories'],","));
			$cfvars["categories"][] = "*";
			foreach($this->getCustomFields($cfvars) as $f)
			{
				if(!isset($vars['customfields'][$f['idsfield']])) $vars['customfields'][$f['idsfield']]="";
				//if the field is a textarea, process
				if($f['type']=='textarea') $vars['customfields'][$f['idsfield']]=b3_htmlize($vars['customfields'][$f['idsfield']],false);
				if($f['type']=='multichoice') $vars['customfields'][$f['idsfield']]=implode("\n",$vars['customfields'][$f['idsfield']]);
				$cf.='<field id="'.$f['idsfield'].'">'.ksql_real_escape_string($vars['customfields'][$f['idsfield']])."</field>\n";
			}
		}

		$query="UPDATE ".TABLE_SHOP_ITEMS." SET ";
		if(isset($vars['online'])) $query.="`online`='".$vars['online']."',";
		if(isset($vars['productcode'])) $query.="`productcode`='".$vars['productcode']."',";
		if(isset($vars['titolo'])) $query.="`titolo`='".$vars['titolo']."',";
		if(isset($vars['sottotitolo'])) $query.="`sottotitolo`='".$vars['sottotitolo']."',";
		if(isset($vars['anteprima'])) $query.="`anteprima`='".$vars['anteprima']."',";
		if(isset($vars['testo'])) $query.="`testo`='".$vars['testo']."',";
		if(isset($vars['categories'])) $query.="`categorie`='".$vars['categories']."',";
		if(isset($vars['prezzo'])) $query.="`prezzo`='".$vars['prezzo']."',";
		if(isset($vars['scontato'])) $query.="`scontato`='".$vars['scontato']."',";
		if(isset($vars['qta'])) $query.="`qta`='".$vars['qta']."',";
		if(isset($vars['weight'])) $query.="`weight`='".$vars['weight']."',";
		if(isset($vars['created'])) $query.="`created`='".$vars['created']."',";
		if(isset($vars['public'])) $query.="`public`='".$vars['public']."',";
		if(isset($vars['expiration'])) $query.="`expired`='".$vars['expiration']."',";
		if(isset($vars['layout'])) $query.="`layout`='".$vars['layout']."',";
		if(isset($vars['dir'])) $query.="`dir`='".$vars['dir']."',";
		if(isset($vars['privatearea'])) $query.="`privatearea`='".$vars['privatearea']."',";
		if(isset($vars['featuredimage'])) $query.="`featuredimage`='".$vars['featuredimage']."',";
		if(isset($vars['manufacturer'])) $query.="`manufacturer`='".$vars['manufacturer']."',";
		if(isset($vars['photogallery'])) $query.="`photogallery`='".$vars['photogallery']."',";

		$query.="`customfields`='".$cf."',`modified`=NOW() WHERE `idsitem`='".$idsitem."'";
		if(ksql_query($query)) return $idsitem;
		else return false;
	}

	public function count($from=0,$to=0,$lang=false) {
		if($to==0) $to=9999;
		if($lang==false) $lang=$this->ll;
		$query="SELECT count(*) AS tot FROM ".TABLE_SHOP_ITEMS." WHERE ll='".$lang."' LIMIT ".$from.",".$to;
		$results=ksql_query($query);
		$row=ksql_fetch_array($results);
		return $row['tot'];
		}

	public function offlineItem($idsitem) {
		$query="UPDATE ".TABLE_SHOP_ITEMS." SET online='n' WHERE idsitem='".$idsitem."'";
		if(ksql_query($query)) return $idsitem;
		else return false;
		}
	public function deleteItem($idsitem) {
		$query="DELETE FROM ".TABLE_SHOP_ITEMS." WHERE idsitem='".$idsitem."'";
		if(ksql_query($query)) return $idsitem;
		else return false;
		}
	function sort($idsitem) {
		for($i=0;isset($idsitem[$i]);$i++) {
			if(is_numeric($idsitem[$i])) {
				$query="UPDATE ".TABLE_SHOP_ITEMS." SET ordine='".($i+1)."' WHERE idsitem=".$idsitem[$i]." AND ll='".$_SESSION['ll']."' LIMIT 1";
				if(!ksql_query($query)) return false;
				}
			}
		return true;
		}

	public function setTranslations($idsitem,$translations) {
		$query="UPDATE ".TABLE_SHOP_ITEMS." SET `traduzioni`='".ksql_real_escape_string($translations)."' WHERE `idsitem`='".ksql_real_escape_string($idsitem)."' LIMIT 1";
		if(ksql_query($query)) return true;
		else return false;
		}
	public function removePageFromTranslations($idsitem) {
		$query="UPDATE ".TABLE_SHOP_ITEMS." SET `traduzioni`=REPLACE(`traduzioni`,'=".ksql_real_escape_string($idsitem)."|','=|') WHERE `traduzioni` LIKE '%=".ksql_real_escape_string($idsitem)."%|'";
		if(ksql_query($query)) return true;
		else return false;
		}


		
	public function getOrderArchiveMonths($conditions="",$status='OPN')
	{
		$output=array();
		$query="SELECT `date` FROM `".TABLE_SHOP_ORDERS."` WHERE `status`='".ksql_real_escape_string($status)."' ";
		if($conditions!="") $query.=" AND (".$conditions.") ";
		$query.=" GROUP BY year(`date`),month(`date`) ORDER BY `date` DESC";
		
		$results=ksql_query($query);
		while($row=ksql_fetch_array($results)) {
			$output[]=substr($row['date'],0,7);
			}

		return $output;
	}
		
	public function getOrderList($conditions="",$status='OPN') {
		require_once($_SERVER['DOCUMENT_ROOT'].BASEDIR.'admin/members/members.lib.php');
		$kaMembers=new kaMembers();
		$output=array();
		$query="SELECT * FROM `".TABLE_SHOP_ORDERS."` WHERE `status`='".ksql_real_escape_string($status)."' ";
		if($conditions!="") $query.=" AND (".$conditions.") ";
		$query.=" ORDER BY `date` DESC";
		$results=ksql_query($query);
		while($row=ksql_fetch_array($results)) {
			$id=count($output);
			$output[$id]=$row;
			$output[$id]['friendlydate']=preg_replace("/(\d{4}).(\d{2}).(\d{2}) (\d{2}).(\d{2}).(\d{2})/","$3-$2-$1 $4:$5",$row['date']);
			if($row['idmember']>0) $output[$id]['member']=$kaMembers->getUserById($row['idmember']);
			else $output[$id]['member']=array("idmember"=>0,"name"=>"","username"=>"","password"=>"","email"=>"");
			$tmp=$this->getPaymentMethodById($row['idspay']);
			if(isset($tmp['idspay'])) $output[$id]['payment_method']=$tmp;
			else $output[$id]['payment_method']=array('name'=>$output[$id]['payment_method']);
			$tmp=$this->getDelivererById($row['iddel']);
			if(isset($tmp['iddel'])) $output[$id]['deliverer']=$tmp;
			else $output[$id]['deliverer']=array('name'=>$output[$id]['deliverer']);
			}
		return $output;
		}

	public function getOrderById($idord) {
		require_once($_SERVER['DOCUMENT_ROOT'].BASEDIR.'admin/members/members.lib.php');
		$kaMembers=new kaMembers();
		$output=array();
		$query="SELECT * FROM `".TABLE_SHOP_ORDERS."` WHERE idord='".intval($idord)."' LIMIT 1";
		$results=ksql_query($query);
		if($row=ksql_fetch_array($results)) {
			$output=$row;
			$output['friendlydate']=preg_replace("/(\d{4}).(\d{2}).(\d{2}) (\d{2}).(\d{2}).(\d{2})/","$3-$2-$1 $4:$5",$row['date']);
			if($row['idmember']>0) $output['member']=$kaMembers->getUserById($row['idmember']);
			else $output['member']=array("idmember"=>"","name"=>"","email"=>"","username"=>"","password"=>"","metadata"=>array());
			$tmp=$this->getPaymentMethodById($row['idspay']);
			if(isset($tmp['idspay'])) $output['payment_method']=$tmp;
			else $output['payment_method']=array('name'=>$output['payment_method']);
			$tmp=$this->getDelivererById($row['iddel']);
			if(isset($tmp['iddel'])) $output['deliverer']=$tmp;
			else $output['deliverer']=array('name'=>$output['deliverer']);
			$output['transactions']=$this->getTransactionsByOrderId($row['idord']);
			
			// the items list is json encoded
			$output['items']=json_decode($row['items'],true);
			}
		return $output;
		}

	public function addPayment($idord,$value,$idspay,$details,$currency=false) {
		if($value>0) {
			$o=$this->getOrderById($idord);
			$p=$this->getPaymentMethodById($idspay);
			if($o['idord']=="") return false;
			if($currency==false) {
				$currency=$this->kaImpostazioni->getVar('shop-currency',2);
				}
			$query="INSERT INTO `".TABLE_SHOP_TRANSACTIONS."` (`idord`,`date`,`value`,`currency`,`idspay`,`method`,`details`) VALUES('".intval($idord)."',NOW(),'".number_format($value,2,'.','')."','".b3_htmlize($currency,true,"")."','".$p['idspay']."','".b3_htmlize($p['name'],true,"")."','".b3_htmlize($details,true,"")."')";
			if(!ksql_query($query)) return false;
			
			$o=$this->getOrderById($idord);
			$totalamount=0;
			if(count($o['transactions'])>0) {
				foreach($o['transactions'] as $t) {
					$totalamount+=$t['value'];
					}
				}
			if($totalamount>=$o['totalprice']) {
				$query="UPDATE `".TABLE_SHOP_ORDERS."` SET payed='s' WHERE `idord`='".intval($o['idord'])."' LIMIT 1";
				if(!ksql_query($query)) return false;
				if($o['member']['email']!="") $this->sendEmail('payed',$o['idord']);
				
				//set permissions for private area folders
				if(!$this->applyPrivatePermissions($idord)) return false;
				}
			return true;
			}
		}
	public function reprocessPayment($idord) {
		$o=$this->getOrderById($idord);
		$totalamount=0;
		if(count($o['transactions'])>0) {
			foreach($o['transactions'] as $t) {
				$totalamount+=$t['value'];
				}
			}
		if($totalamount>=$o['totalprice']) {
			$query="UPDATE `".TABLE_SHOP_ORDERS."` SET payed='s' WHERE `idord`='".intval($o['idord'])."' LIMIT 1";
			if(!ksql_query($query)) return false;
			if($o['member']['email']!="") $this->sendEmail('payed',$o['idord']);
			
			//set permissions for private area folders
			if(!$this->applyPrivatePermissions($idord)) return false;
			}
		return true;
		}
	public function reportShipment($idord,$iddel,$tracking_number,$tracking_url) {
		$d=$this->getDelivererById($iddel);
		if($d['iddel']=="") return false;
		$o=$this->getOrderById($idord);
		if($o['idord']=="") return false;
		$query="UPDATE `".TABLE_SHOP_ORDERS."` SET `shipped`='s',`iddel`='".intval($d['iddel'])."',`deliverer`='".b3_htmlize($d['name'],true,"")."',`tracking_number`='".b3_htmlize($tracking_number,true,"")."',`tracking_url`='".b3_htmlize($tracking_url,true,"")."',`shippedon`=NOW(),`status`='CLS' WHERE idord='".$o['idord']."' LIMIT 1";
		if(!ksql_query($query)) return false;
		if(!$this->applyPrivatePermissions($idord)) return false;
		$this->sendEmail('sended',$o['idord']);
		return true;
		}
	public function cancelOrder($idord) {
		$o=$this->getOrderById($idord);
		if($o['idord']=="") return false;
		$query="UPDATE `".TABLE_SHOP_ORDERS."` SET `status`='CNC' WHERE idord='".intval($o['idord'])."' LIMIT 1";
		if(!ksql_query($query)) return false;
		return true;
		}
	public function closeOrder($idord) {
		$o=$this->getOrderById($idord);
		if($o['idord']=="") return false;
		$query="UPDATE `".TABLE_SHOP_ORDERS."` SET `status`='CLS' WHERE idord='".intval($o['idord'])."' LIMIT 1";
		if(!ksql_query($query)) return false;
		if(!$this->applyPrivatePermissions($idord)) return false;
		return true;
		}
	public function deleteOrder($idord) {
		$o=$this->getOrderById($idord);
		if($o['idord']=="") return false;
		$query="DELETE FROM `".TABLE_SHOP_ORDERS."` WHERE `idord`='".intval($o['idord'])."' LIMIT 1";
		if(!ksql_query($query)) return false;
		return true;
		}
	public function orderUpdateNotes($idord,$notes)
	{
		$query="UPDATE `".TABLE_SHOP_ORDERS."` SET `notes`='".ksql_real_escape_string($notes)."' WHERE `idord`='".intval($idord)."' LIMIT 1";
		if(!ksql_query($query)) return false;
		return true;
	}

	public function applyPrivatePermissions($idord) {
		require_once($_SERVER['DOCUMENT_ROOT'].ADMINDIR.'private/private.lib.php');
		$kaPrivate=new kaPrivate();

		$o=$this->getOrderById($idord);

		$dirlist=array();
		foreach($o['items'] as $items)
		{
			if(isset($items['privatearea']) && is_array($items['privatearea']))
			{
				foreach($items['privatearea'] as $dir)
				{
					if($items['privatearea']!="") $dirlist[$dir]=true;
				}
			}
		}
		
		/* grant access to private area */
		foreach($dirlist as $dir=>$true)
		{
			$p=$kaPrivate->getPermissions($dir);
			if($p['permissions']=='restricted')
			{
				$armembers=array($o['idmember']=>true);
				foreach($p['members'] as $m)
				{
					$armembers[$m['idmember']]=true;
				}
				$kaPrivate->setPermissions($dir,'restricted',$armembers,'private',array());
			}
		}

		return true;
		}
	
	public function sendEmail($type,$idord) {
		$o=$this->getOrderById($idord);
		if(trim(strip_tags($this->kaImpostazioni->getVar('shop-mail_'.$type,1),"<img>"))!="") {
			$orderDateTimestamp=mktime(substr($o['date'],11,2),substr($o['date'],14,2),substr($o['date'],17,2),substr($o['date'],5,2),substr($o['date'],8,2),substr($o['date'],0,4));
			
			$mail=array();
			$mail['from']=$this->kaImpostazioni->getVar('shop-mail_from',1).' <'.$this->kaImpostazioni->getVar('shop-mail_from',2).'>';
			if(trim($mail['from']," <>")=="") $mail['from']="";
			$mail['to']=$o['member']['name'].' <'.$o['member']['email'].'>';

			$mail['subject']=$this->kaImpostazioni->getVar('shop-mail_'.$type,2);
			$mail['subject']=str_replace("{NAME}",$o['member']['name'],$mail['subject']);
			$mail['subject']=str_replace("{ORDER_NUMBER}",$o['uid'],$mail['subject']);

			$mail['message']=$this->kaImpostazioni->getVar('shop-mail_'.$type,1);
			$mail['message']=str_replace("{NAME}",$o['member']['name'],$mail['message']);
			$mail['message']=str_replace("{EMAIL}",$o['member']['email'],$mail['message']);
			$mail['message']=str_replace("{USERNAME}",$o['member']['username'],$mail['message']);
			$mail['message']=str_replace("{PASSWORD}",$o['member']['password'],$mail['message']);
			$address="";
			if(isset($o['member']['Address'])) $address.=$o['member']['Address'].'<br />';
			if(isset($o['member']['ZipCode'])) $address.=$o['member']['ZipCode'].' ';
			if(isset($o['member']['City'])) $address.=$o['member']['City'];

			$mail['message']=str_replace("{ORDER_DATE}",strftime($this->kaImpostazioni->getVar('timezone',2),$orderDateTimestamp),$mail['message']);
			$mail['message']=str_replace("{CURRENT_DATE}",strftime($this->kaImpostazioni->getVar('timezone',2)),$mail['message']);
			$mail['message']=str_replace("{ADDRESS}",$address,$mail['message']);
			$mail['message']=str_replace("{CARRIER}",$o['deliverer']['name'],$mail['message']);
			$mail['message']=str_replace("{DELIVERER}",$o['deliverer']['name'],$mail['message']);
			$mail['message']=str_replace("{TRACKING_URL}",$o['tracking_url'],$mail['message']);
			$mail['message']=str_replace("{TRACKING_NUMBER}",$o['tracking_number'],$mail['message']);
			$mail['message']=str_replace("{SHIPPING_ADDRESS}",nl2br(strip_tags($o['shipping_address'])),$mail['message']);
			$mail['message']=str_replace("{PAYMENT_METHOD}",$o['payment_method']['name'],$mail['message']);
			$mail['message']=str_replace("{ORDER_ITEMS}","",$mail['message']);
			$mail['message']=str_replace("{ORDER_NUMBER}",$o['uid'],$mail['message']);
			$mail['message']=str_replace("{BILLING_DATA}",nl2br(strip_tags($o['invoice_data'])),$mail['message']);
			$mail['message']=str_replace("{ORDER_PRICE}",$o['totalprice'],$mail['message']);
			

			require_once("../../../inc/tplshortcuts.lib.php");
			kInitBettino("../../../");
			$results=$GLOBALS['__emails']->send($mail['from'],$mail['to'],$mail['subject'],$mail['message'],"");

			return $results;
			}
		}
		
	public function getPaymentMethodsByZone($idzone) {
		$output=array();
		$query="SELECT * FROM `".TABLE_SHOP_PAYMENTS."` WHERE zones LIKE '%,".intval($idzone).",%' ORDER BY `ordine`";
		$results=ksql_query($query);
		while($row=ksql_fetch_array($results)) {
			$output[]=$row;
			}
		return $output;
		}
	public function getPaymentMethodById($idspay) {
		$output=array();
		$query="SELECT * FROM `".TABLE_SHOP_PAYMENTS."` WHERE idspay='".intval($idspay)."' LIMIT 1";
		$results=ksql_query($query);
		while($row=ksql_fetch_array($results)) {
			$output=$row;
			}
		return $output;
		}
	public function getDeliverersByZone($idzone) {
		$output=array();
		$query="SELECT * FROM `".TABLE_SHOP_DELIVERERS."` WHERE zones LIKE '%,".intval($idzone).",%' ORDER BY `ordine`";
		$results=ksql_query($query);
		while($row=ksql_fetch_array($results)) {
			$output[]=$row;
			}
		return $output;
		}
	public function getDelivererById($iddel) {
		$output=array();
		$query="SELECT * FROM `".TABLE_SHOP_DELIVERERS."` WHERE iddel='".intval($iddel)."' LIMIT 1";
		$results=ksql_query($query);
		while($row=ksql_fetch_array($results)) {
			$output=$row;
			}
		return $output;
		}
	public function getTransactionsByOrderId($idord) {
		$output=array();
		$query="SELECT * FROM `".TABLE_SHOP_TRANSACTIONS."` WHERE idord='".intval($idord)."' ORDER BY `date`";
		$results=ksql_query($query);
		while($row=ksql_fetch_array($results)) {
			$id=count($output);
			$output[$id]=$row;
			$output[$id]['friendlydate']=preg_replace("/(\d{4}).(\d{2}).(\d{2}) (\d{2}).(\d{2}).(\d{2})/","$3-$2-$1 $4:$5",$output[$id]['date']);
			}
		return $output;
		}

	/*** CUSTOM FIELDS ***/
	public function getCustomFields($vars=array())
	{
		/* returns an array with custom fields */
		$output=array();
		$categories=array();
		if(!isset($vars['order'])) $vars['order']='order';

		$query="SELECT * FROM ".TABLE_SHOP_CUSTOMFIELDS." WHERE ";
			// get only selected categories
			$query.=" (`categories`='' ";

			// "*" = all the categories, even those that do not yet exist
			if(!isset($vars['categories'])) $vars['categories'] = array("*");
			foreach($vars['categories'] as $cat)
			{
				if($cat == "*") $query.=" OR `categories` LIKE '%,*,%'";
			}

			foreach($this->kaCategorie->getList(TABLE_SHOP_ITEMS) as $cat)
			{
				if(isset($vars['categories'][0]))
				{
					//filter categories
					$isValid=false;
					foreach($vars['categories'] as $c)
					{
						if($c == $cat['idcat']) $isValid=true;
					}
					
					if($isValid==true) $query .= " OR `categories` LIKE '%,".$cat['idcat'].",%'";
					
				} else {
					//filters by category was not setted
					$query.=" OR `categories` LIKE '%,".$cat['idcat'].",%'";
				}
				$categories[$cat['idcat']]=$cat['categoria'];
			}
			$query.=") ";
		//filters by id
		if(isset($vars['id'])) $query.=" AND `idsfield`='".intval($vars['id'])."' ";
		
		$query.=" ORDER BY `".$vars['order']."`";

		$results=ksql_query($query);
		while($row=ksql_fetch_array($results))
		{
			$row['categoriesList']="";
			foreach(explode(",",trim($row['categories'],",")) as $idcat)
			{
				if($idcat == "*")
				{
					$row['categoriesList']="All";
					break;
				}
				if(isset($categories[$idcat])) $row['categoriesList'].=$categories[$idcat]."\n";
			}
			$row['categoriesList']=trim($row['categoriesList']);

			$output[]=$row;
		}
		
		return $output;
	}

	public function addCustomField($vars=array())
	{
		/* add a custom field to the shop */
		if(!isset($vars['name'])) return false;
		if(!isset($vars['type'])) return false;
		if(!isset($vars['values'])) $vars['values']="";
		if(!isset($vars['categories'])) $vars['categories']=",";
		$query="SELECT `order` FROM `".TABLE_SHOP_CUSTOMFIELDS."` ORDER BY `order` DESC LIMIT 1";
		$results=ksql_query($query);
		if($row=ksql_fetch_array($results)) $vars['order']=$row['order']+1;
		else $vars['order']=1;

		$query="INSERT INTO ".TABLE_SHOP_CUSTOMFIELDS." (`name`,`type`,`values`,`categories`,`order`) VALUES('".ksql_real_escape_string($vars['name'])."','".ksql_real_escape_string($vars['type'])."','".ksql_real_escape_string($vars['values'])."','".b3_htmlize($vars['categories'],true,"")."','".intval($vars['order'])."')";
		if(ksql_query($query)) return true;
		else return false;
	}

	public function updateCustomField($vars=array())
	{
		/* update a custom field */
		if(!isset($vars['idsfield'])) return false;
		if(!isset($vars['name'])) return false;
		if(!isset($vars['type'])) return false;
		if(!isset($vars['values'])) $vars['values']="";
		if(!isset($vars['categories'])) $vars['categories']=",";

		$query="UPDATE ".TABLE_SHOP_CUSTOMFIELDS." SET `name`='".ksql_real_escape_string($vars['name'])."',`type`='".ksql_real_escape_string($vars['type'])."',`values`='".ksql_real_escape_string($vars['values'])."',`categories`='".ksql_real_escape_string($vars['categories'])."' WHERE `idsfield`='".intval($vars['idsfield'])."' LIMIT 1";
		if(ksql_query($query)) return true;
		else return false;
	}

	public function removeCustomField($idsfield)
	{
		/* permanently delete a custom field */
		$query="DELETE FROM ".TABLE_SHOP_CUSTOMFIELDS." WHERE `idsfield`=".intval($idsfield)." LIMIT 1";
		if(ksql_query($query)) return true;
		else return false;
	}

	public function sortCustomFields($order)
	{
		$i=1;
		foreach($order as $idsfield)
		{
			$query="UPDATE ".TABLE_SHOP_CUSTOMFIELDS." SET `order`=".$i." WHERE `idsfield`='".ksql_real_escape_string($idsfield)."' LIMIT 1";
			ksql_query($query);
			$i++;
		}
	}

		
	/* VARIATIONS */
	
	public function getVariationCollections($vars)
	{
		if(!isset($vars['orderby'])) $vars['orderby']='`collection` ASC';
		$output=array();
		
		$query="SELECT `collection` FROM `".TABLE_SHOP_VARIATIONS."` WHERE ";
		if(isset($vars['idsitem'])) $query.=" `idsitem`='".intval($vars['idsitem'])."' AND ";
		$query.=" `idsvar`<>0 GROUP BY `collection` ORDER BY ".$vars['orderby'];

		$results=ksql_query($query);
		while($row=ksql_fetch_array($results)) {
			if(trim($row['collection'])!="") $output[]=$row['collection'];
			}

		return $output;
	}
	
	public function getVariations($vars)
	{
		if(!isset($vars['orderby'])) $vars['orderby']='`collection` ASC,`order` ASC';
		$output=array();
		
		$query="SELECT * FROM `".TABLE_SHOP_VARIATIONS."` WHERE ";
		if(isset($vars['idsitem'])) $query.=" `idsitem`='".intval($vars['idsitem'])."' AND ";
		if(isset($vars['idsvar'])) $query.=" `idsvar`='".intval($vars['idsvar'])."' AND ";
		$query.=" `idsvar`<>0 ORDER BY ".$vars['orderby'];

		$results=ksql_query($query);
		while($row=ksql_fetch_array($results))
		{
			$output[]=$row;
		}

		return $output;
	}
	
	public function addVariation($idsitem,$name,$collection,$copy)
	{
		$idsitem=intval($idsitem);
		$name=ksql_real_escape_string($name);
		$collection=ksql_real_escape_string($collection);
		$copy=ksql_real_escape_string($copy);

		$query="SELECT `order` FROM ".TABLE_SHOP_VARIATIONS." WHERE `idsitem`='".$idsitem."' AND `collection`='".$collection."' ORDER BY `order` DESC LIMIT 1";
		$results=ksql_query($query);
		$row=ksql_fetch_array($results);
		$order=$row['order']+1;
		
		if($copy=="")
		{
			$query="INSERT INTO ".TABLE_SHOP_VARIATIONS." (`idsitem`,`name`,`descr`,`price`,`collection`,`order`) VALUES('".$idsitem."','".$name."','','','".$collection."','".$order."')";
			if(ksql_query($query)) return ksql_insert_id();
		} else {
			$query="INSERT INTO ".TABLE_SHOP_VARIATIONS." (`idsitem`,`name`,`descr`,`price`,`collection`,`order`) SELECT `idsitem`,`name`,`descr`,`price`,`collection`,`order` FROM ".TABLE_SHOP_VARIATIONS." WHERE `idsvar`='".$copy."' LIMIT 1";
			if(ksql_query($query))
			{
				$id=ksql_insert_id();
				$query="UPDATE ".TABLE_SHOP_VARIATIONS." SET `name`='".$name."',`collection`='".$collection."',`order`='".$order."' WHERE idsvar='".$id."' LIMIT 1";
				if(ksql_query($query)) return $id;
				return false;
			}
			return false;
		}
		return false;
	}
	
	//import variations from another item
	public function importVariations($from,$to)
	{
		$output=true;
		$query="SELECT * FROM ".TABLE_SHOP_VARIATIONS." WHERE `idsitem`='".intval($from)."' ORDER BY `collection` ASC,`order` ASC";
		$results=ksql_query($query);
		while($row=ksql_fetch_array($results))
		{
			$q="SELECT * FROM ".TABLE_SHOP_VARIATIONS." WHERE `idsitem`='".intval($to)."' AND `collection`='".$row['collection']."' ORDER BY `order` DESC LIMIT 1";
			$rs=ksql_query($q);
			$r=ksql_fetch_array($rs);
			$order=$r['order']+1;
			
			$q="INSERT INTO ".TABLE_SHOP_VARIATIONS." (`idsitem`,`collection`,`name`,`descr`,`price`,`discounted`,`default`,`order`)
				VALUES('".intval($to)."','".ksql_real_escape_string($row['collection'])."','".ksql_real_escape_string($row['name'])."','".ksql_real_escape_string($row['descr'])."','".ksql_real_escape_string($row['price'])."','".ksql_real_escape_string($row['discounted'])."','".ksql_real_escape_string($row['default'])."','".ksql_real_escape_string($order)."')";
			if(!ksql_query($q)) $output=false;
		}
		return $output;
	}
	
	public function updateVariation($vars)
	{
		if(!isset($vars['idsvar'])) return false;
		
		$query="UPDATE ".TABLE_SHOP_VARIATIONS." SET ";
		foreach($vars as $k=>$v)
		{
			$vars[$k]=ksql_real_escape_string(trim($v));
			if($k=='price') $vars[$k]=str_replace(",",".",$vars[$k]);
			if($k!='idsvar') $query.=" `".$k."`='".$vars[$k]."',";
		}
		$query=rtrim($query,",");
		$query.=" WHERE `idsvar`='".$vars['idsvar']."' LIMIT 1";
		
		if(ksql_query($query)) return true;
		return false;
	}
	
	public function deleteVariation($idsvar)
	{
		$query="DELETE FROM `".TABLE_SHOP_VARIATIONS."` WHERE `idsvar`='".intval($idsvar)."' LIMIT 1";
		if(ksql_query($query)) return true;
		return false;
	}


	/* COUPONS */
	
	//insert a gived number of coupons, with the code generated in the gived format declared as %d=digit and %s=word
	public function insertCoupons($vars) {
		if(!isset($vars['idscoup'])) return false;
		if(!isset($vars['quantity'])) return false;
		$vars['quantity']=intval($vars['quantity']);
		if($vars['quantity']==0) return true;
		if(!isset($vars['format'])) $vars['format']="%d%d%d%s%s%d%d%d";
		if(!isset($vars['allowedchars'])) $vars['allowedchars']="QWERTYUIOPASDFGHJKLZXCVBNM";

		$i=0;
		$errors=0; //errors count prevent the passing of a format that can't create sufficient random variations
		while($i<$vars['quantity']&&$errors<10) {
			$code=$vars['format'];
			while(strpos($code,"%d")!==false) {
				$code=preg_replace('/%d/',rand(0,9),$code,1);
				}
			while(strpos($code,"%s")!==false) {
				$code=preg_replace('/%s/',substr($vars['allowedchars'],rand(0,strlen($vars['allowedchars'])-1),1),$code,1);
				}
			
			//check if code exists yet
			$query="SELECT * FROM ".TABLE_SHOP_COUPONS_CODES." WHERE `code`='".ksql_real_escape_string($code)."' LIMIT 1";
			$results=ksql_query($query);
			if(!ksql_fetch_array($results)) {
				//if not exists, insert
				$query="INSERT INTO ".TABLE_SHOP_COUPONS_CODES." (`idscoup`,`code`,`valid`) VALUES('".ksql_real_escape_string($vars['idscoup'])."','".ksql_real_escape_string($code)."',1)";
				ksql_query($query);
				$i++;
				}
			else $errors++;
			}
		
		}

	public function markCouponAsUsed($code) {
		$query="UPDATE ".TABLE_SHOP_COUPONS_CODES." SET `valid`=0 WHERE `code`='".ksql_real_escape_string($code)."'";
		return ksql_query($query);
		}

	public function markCouponAsValid($code) {
		$query="UPDATE ".TABLE_SHOP_COUPONS_CODES." SET `valid`=1 WHERE `code`='".ksql_real_escape_string($code)."'";
		return ksql_query($query);
		}

	public function deleteCoupon($code) {
		$query="DELETE FROM ".TABLE_SHOP_COUPONS_CODES." WHERE `code`='".ksql_real_escape_string($code)."'";
		return ksql_query($query);
		}
	
	//get an array with the list of coupons
	public function getCouponCodesList($vars) {
		if(isset($vars['valid'])) {
			if($vars['valid']!=1) $vars['valid']=0;
			}
			
		$output=array();
		
		$query="SELECT * FROM ".TABLE_SHOP_COUPONS_CODES." WHERE `idscoup`>0 ";
		if(isset($vars['valid'])) $query.=" AND `valid`='".$vars['valid']."' ";
		if(isset($vars['idscoup'])) $query.=" AND `idscoup`='".intval($vars['idscoup'])."' ";
		$query.=" ORDER BY `code`";
		$results=ksql_query($query);
		while($row=ksql_fetch_array($results)) {
			$output[]=$row;
			}

		return $output;
		}

	
	/**************************************************
	* MANUFACTURERS
	**************************************************/
	
	// create a new manufacturer
	public function createManufacturer($vars)
	{
		if(!isset($vars['name'])) $vars['name']="";
		if(!isset($vars['dir'])) $vars['dir']=preg_replace("/[^\w\/\.\-\x{C0}-\x{D7FF}\x{2C00}-\x{D7FF}]+/","-",strtolower($vars['name']));
		$vars['dir']=preg_replace("/[\?|#|'|\"]+/","-",$vars['dir']);

		$query="INSERT INTO `".TABLE_SHOP_MANUFACTURERS."` (
			`name`,
			`dir`,
			`subtitle`,
			`preview`,
			`description`,
			`featuredimage`,
			`photogallery`,
			`created`,
			`modified`,
			`translations`,
			`ll`
			) VALUES(
			'".b3_htmlize($vars['name'],true,"")."',
			'".ksql_real_escape_string($vars['dir'])."',
			'',
			'',
			'',
			0,
			',',
			NOW(),
			NOW(),
			'',
			'".$_SESSION['ll']."'
			)";
		
		if(ksql_query($query)) return ksql_insert_id();
		else return false;
	}

	public function countManufacturers($vars=array())
	{
		if(!isset($vars['ll'])) $vars['ll']=$_SESSION['ll'];
		if(!isset($vars['orderby'])) $vars['orderby']='`name`';
		$conditions='';
		if(isset($vars['conditions'])) $conditions.="(".$vars['conditions'].") AND ";
		if(isset($vars['search']))
		{
			$conditions.="(";
			$conditions.="`name` LIKE '%".b3_htmlize($_GET['search'],true,"")."%' OR ";
			$conditions.="`subtitle` LIKE '%".b3_htmlize($_GET['search'],true,"")."%' OR ";
			$conditions.="`dir` LIKE '%".ksql_real_escape_string($_GET['search'])."%'";
			$conditions.=") AND ";
		}

		$conditions.="ll='".$vars['ll']."'";
		$query="SELECT count(`idsman`) AS `tot` FROM ".TABLE_SHOP_MANUFACTURERS." WHERE ".$conditions." ORDER BY ".$vars['orderby'];
		$results=ksql_query($query);
		$row=ksql_fetch_array($results);
		
		return $row['tot'];
	}

	// it returns an array with the list of the manufacturers' entries on database
	public function getManufacturersList($vars=array())
	{
		$output=array();
		
		if(!isset($vars['ll'])) $vars['ll']=$_SESSION['ll'];
		if(!isset($vars['orderby'])) $vars['orderby']='`name`';
		$conditions='';
		if(isset($vars['conditions'])&&$vars['conditions']!="") $conditions.="(".$vars['conditions'].") AND ";
		if(isset($vars['search']))
		{
			$conditions.="(";
			$conditions.="`name` LIKE '%".b3_htmlize($_GET['search'],true,"")."%' OR ";
			$conditions.="`subtitle` LIKE '%".b3_htmlize($_GET['search'],true,"")."%' OR ";
			$conditions.="`dir` LIKE '%".ksql_real_escape_string($_GET['search'])."%'";
			$conditions.=") AND ";
		}

		$conditions.="ll='".$vars['ll']."'";
		$query="SELECT `idsman`,`name`,`dir`,`featuredimage` FROM ".TABLE_SHOP_MANUFACTURERS." WHERE ".$conditions." ORDER BY ".$vars['orderby'];
		$results=ksql_query($query);

		while($page=ksql_fetch_array($results))
		{
			$output[]=$page;
		}
		
		return $output;
	}

	// it returns the entries of a single manufacturer, selected by id
	public function getManufacturer($idsman)
	{

		if(!isset($idsman)) return false;
		$query="SELECT * FROM ".TABLE_SHOP_MANUFACTURERS." WHERE `idsman`='".intval($idsman)."' LIMIT 1";
		$results=ksql_query($query);

		if($page=ksql_fetch_array($results))
		{
			$page['imgallery']=$this->kaImgallery->getList(TABLE_SHOP_MANUFACTURERS,$page['idsman']);
			$page['docgallery']=$this->kaDocgallery->getList(TABLE_SHOP_MANUFACTURERS,$page['idsman']);
			return $page;
		}
		
		return false;
	}
	
	// update a manufacturer by idsman (mandatory field)
	public function updateManufacturer($vars)
	{
		if(!isset($vars['idsman'])) return false;

		$page=$this->getManufacturer($vars['idsman']);
		if($page==false) return false;
		
		/* update translation table in all involved pages (past and current) */
		if(isset($vars['translation_id']))
		{
			// translation has this format: |LL=idsman|LL=idsman|...
			$translations="";
			$vars['translation_id'][$_SESSION['ll']]=$_GET['idsman'];
			foreach($vars['translation_id'] as $k=>$v) {
				if($v!="") {
					$translations.=$k.'='.$v.'|';
					$kaShop->removeManufacturerFromTranslations($v);
					}
				}
			// first of all, clear translations from previous+current pages
			foreach($page['traduzioni'] as $k=>$v) {
				if($v!="") $kaShop->removeManufacturerFromTranslations($v);
			}
			// then set the new translations in the current pages
			foreach($vars['translation_id'] as $k=>$v) {
				if($v!="") {
					$kaShop->setManufacturerTranslations($v,$translations);
				}
			}
		}

		/* clean permalink */
		if(isset($vars['dir'])) $vars['dir']=preg_replace("/[\?|#|'|\"]+/","-",$vars['dir']);

		/* categories */
		if(isset($vars['idcat']))
		{
			$categorie=",";
			foreach($vars['idcat'] as $idcat) { $categorie.=intval($idcat).','; }
		} else $categorie=",,";

		//modifico o inserisco il record
		$query="UPDATE ".TABLE_SHOP_MANUFACTURERS." SET ";
		if(isset($vars['name'])) $query.="`name`='".b3_htmlize($vars['name'],true,"")."',";
		if(isset($vars['subtitle'])) $query.="`subtitle`='".b3_htmlize($vars['subtitle'],true,"")."',";
		if(isset($vars['preview'])) $query.="`preview`='".b3_htmlize($vars['preview'],true)."',";
		if(isset($vars['description'])) $query.="`description`='".b3_htmlize($vars['description'],true)."',";
		if(isset($vars['dir'])) $query.="`dir`='".ksql_real_escape_string($vars['dir'])."',";
		if(isset($vars['featuredimage'])) $query.="`featuredimage`='".intval($vars['featuredimage'])."',";
		if(isset($vars['photogallery'])) $query.="`photogallery`='".ksql_real_escape_string($vars['photogallery'])."',";
		$query.="`modified`=NOW() WHERE `idsman`=".ksql_real_escape_string($vars['idsman'])." LIMIT 1";

		if(!ksql_query($query)) return false;

		foreach($vars as $ka=>$v)
		{
			if(substr($ka,0,4)=="seo_") $GLOBALS['kaMetadata']->set(TABLE_SHOP_MANUFACTURERS,$vars['idsman'],$ka,$v);
		}
		
		return $vars['idsman'];
	}

	// delete a manufacturer
	public function deleteManufacturer($idsman)
	{
		require_once($_SERVER['DOCUMENT_ROOT'].ADMINDIR.'inc/docgallery.lib.php');
		require_once($_SERVER['DOCUMENT_ROOT'].ADMINDIR.'inc/metadata.lib.php');
		$kaImgallery=new kaImgallery();
		$kaDocgallery=new kaDocgallery();
		$kaMetadata=new kaMetadata();
		
		$query="DELETE FROM `".TABLE_SHOP_MANUFACTURERS."` WHERE `idsman`='".intval($idsman)."' LIMIT 1";
		
		if(ksql_query($query))
		{
			// remove from document gallery
			foreach($kaDocgallery->getList(TABLE_SHOP_MANUFACTURERS,intval($idsman)) as $doc)
			{
				$kaDocgallery->del($doc['iddocg']);
			}

			// remove from metadata
			foreach($kaMetadata->getList(array("table"=>TABLE_SHOP_MANUFACTURERS,"id"=>intval($idsman))) as $md)
			{
				$kaMetadata->set($md['tabella'],$md['id'],$md['param'],"");
			}

			return true;
		} else return false;
	}

}
