<?php 

class kaStats {
	protected $kaConfig,$expiration,$contatti,$visite,$stats;
	
	public function __construct($process=array())
	{
		require_once(ADMINRELDIR.'inc/config.lib.php');
		$this->kConfig=new kaImpostazioni();
		$this->expiration=$this->kConfig->getVar('stats_expiration',1,'*');

		if(!empty($process['visits'])) $this->processVisits();
		if(!empty($process['newsletter'])) $this->createNewslettersSummary();
	}
	
	public function processVisits($deepRefresh=false)
	{
		/* move statistics to archive (a buffer zone before summary) */
		$q="INSERT INTO ".TABLE_STATS_ARCHIVE." (`ip`,`date`,`url`,`referer`,`system`,`contacts`) SELECT `ip`,`date`,`url`,`referer`,`system`,`contacts` FROM ".TABLE_STATISTICHE." WHERE `date`<'".date("Y-m-d H:i",time()-3600)."'";
		if(mysql_query($q))
		{
			$q="DELETE FROM ".TABLE_STATISTICHE." WHERE `date`<'".date("Y-m-d H:i",time()-3600)."'";
			mysql_query($q);
		}
		
		/* country detection where not assigned yet */
		$q="SELECT idlog,ip FROM ".TABLE_STATISTICHE." WHERE ll='' OR ll=NULL";
		if($deepRefresh==true) $q.=" OR ll='**'";
		$rs=mysql_query($q);
		while($r=mysql_fetch_array($rs))
		{
			$atonip=ip2long($r['ip']);
			if(substr($atonip,0,2)<=14) $postfix=10;
			elseif(substr($atonip,0,1)<2) $postfix=15;
			elseif(substr($atonip,0,1)<3) $postfix=2;
			else $postfix=3;
			$query="SELECT ll FROM ".TABLE_IP2COUNTRY.$postfix." WHERE ipfrom<='".$atonip."' AND ipto>='".$atonip. "' LIMIT 1";
			$results=mysql_query($query);
			$row=mysql_fetch_array($results);
			if($row['ll']=="") $row['ll']='**';
			$query="UPDATE ".TABLE_STATISTICHE." SET `ll`='".$row['ll']."' WHERE idlog='".$r['idlog']."' LIMIT 1";
			mysql_query($query);
		}

		$q="SELECT idlog,ip FROM ".TABLE_STATS_ARCHIVE." WHERE ll='' OR ll=NULL";
		if($deepRefresh==true) $q.=" OR ll='**'";
		$rs=mysql_query($q);
		while($r=mysql_fetch_array($rs))
		{
			$atonip=ip2long($r['ip']);
			if(substr($atonip,0,2)<=14) $postfix=10;
			elseif(substr($atonip,0,1)<2) $postfix=15;
			elseif(substr($atonip,0,1)<3) $postfix=2;
			else $postfix=3;
			$query="SELECT ll FROM ".TABLE_IP2COUNTRY.$postfix." WHERE ipfrom<='".$atonip."' AND ipto>='".$atonip. "' LIMIT 1";
			$results=mysql_query($query);
			$row=mysql_fetch_array($results);
			if($row['ll']=="") $row['ll']='**';
			$query="UPDATE ".TABLE_STATS_ARCHIVE." SET `ll`='".$row['ll']."' WHERE idlog='".$r['idlog']."'";
			mysql_query($query);
		}

		/* detect expiration if not assigned */
		if($this->expiration==0)
		{
			$q="SELECT min(`date`) AS `start`,max(`date`) AS `end` FROM (SELECT * FROM ".TABLE_STATS_ARCHIVE.") AS s";
			$rs=mysql_query($q);
			$row=mysql_fetch_array($rs);
			$start=mktime(substr($row['start'],11,2),substr($row['start'],14,2),substr($row['start'],17,2),substr($row['start'],5,2),substr($row['start'],8,2),substr($row['start'],0,4));
			$end=mktime(substr($row['end'],11,2),substr($row['end'],14,2),substr($row['end'],17,2),substr($row['end'],5,2),substr($row['end'],8,2),substr($row['end'],0,4));
			$this->expiration=ceil(($end-$start)/86400)+1;
		}
		
		mysql_query("OPTIMIZE TABLE `".TABLE_STATISTICHE."`");
		mysql_query("OPTIMIZE TABLE `".TABLE_STATS_ARCHIVE."`");

	}


	/* create summaries for newsletters */
	public function createNewslettersSummary()
	{
		require_once(ADMINRELDIR.'newsletter/newsletter.lib.php');
		$kaNewsletter = new kaNewsletter();
		
		/* collect all the newsletters older than 15 days */
		foreach($kaNewsletter->getArchiveList( array("stats"=>false, "conditions"=>"`data`<'".strftime("%Y-%m-%d %H:%M:%S",time() - 1296000)."'") ) as $list)
		{
			// check if this newsletter was already processed
			$query="SELECT * FROM `".TABLE_STATS_SUMMARY."` WHERE `family`='newsletter' AND `type`='recipients' AND `reference`='".intval($list['idarch'])."' LIMIT 1";
			$results=mysql_query($query);
			$row=mysql_fetch_array($results);
			if($row==false)
			{
				$sent=$kaNewsletter->getLogCount(array("idarch"=>$list['idarch'], "readed"=>false));
				$readed=$kaNewsletter->getLogCount(array("idarch"=>$list['idarch'], "readed"=>true));
				$readedfrom=implode("\n",$kaNewsletter->getReadedFrom(array("idarch"=>$list['idarch'], "readed"=>true)));
				$this->insertSummaryEntry("newsletter", "recipients", $list['idarch'], $sent, '');
				$this->insertSummaryEntry("newsletter", "readed", $list['idarch'], $readed, $readedfrom);
				
				if(!empty($list['idarch'])) $kaNewsletter->deleteLogs(array("idarch"=>$list['idarch']));
			}
		}
		
		mysql_query("OPTIMIZE TABLE `".TABLE_EMAIL_LOG."`");
		mysql_query("OPTIMIZE TABLE `".TABLE_EMAIL_QUEUE."`");
	}
	
	public function insertSummaryEntry($family, $type, $reference, $count=0, $data='')
	{
		$query="INSERT INTO ".TABLE_STATS_SUMMARY." (`family`, `type`, `reference`, `count`, `data`) VALUES('".mysql_real_escape_string($family)."', '".mysql_real_escape_string($type)."', '".mysql_real_escape_string($reference)."', '".intval($count)."', '".mysql_real_escape_string($data)."')";
		mysql_query($query);
	}
	
	public function getSummaryEntry($family, $type, $reference)
	{
		$query="SELECT * FROM `".TABLE_STATS_SUMMARY."` WHERE `family`='".mysql_real_escape_string($family)."' AND `type`='".mysql_real_escape_string($type)."' AND `reference`='".mysql_real_escape_string($reference)."' LIMIT 1";
		$results=mysql_query($query);
		return mysql_fetch_array($results);
	}
	
	public function deleteOldStats()
	{
		if($this->expiration>0)
		{
			$q="DELETE FROM ".TABLE_STATS_ARCHIVE." WHERE date<'".date("Y-m-d",time()-($this->expiration*24*60*60))." 00:00:00'";
			mysql_query($q);
		}
	}

	public function load()
	{
		$this->contatti=array();
		$this->visite=array();
		$this->stats=array();
		$this->stats['contatti']['max']=0; //la punta maggiore di contatti
		$this->stats['contatti']['min']=0; //la punta minore di contatti
		$this->stats['contatti']['tot']=0; //contatti totali
		$this->stats['contatti']['avg']=0; //contatti medi giornalieri
		for($i=0;$i<=6;$i++) { $this->stats['contatti']['d'.$i]=0; } //contatti per giorno della settimana
		$this->stats['contatti']['maxday']=0;
		for($i=0;$i<=23;$i++) { $this->stats['contatti']['h'.$i]=0; } //contatti per fascia oraria
		$this->stats['contatti']['maxhour']=0;
		$this->stats['visite']['max']=0; //la punta maggiore di visite
		$this->stats['visite']['min']=0; //la punta minore di visite
		$this->stats['visite']['tot']=0; //visite totali
		$this->stats['visite']['avg']=0; //visite medi giornalieri
		for($i=0;$i<=6;$i++) { $this->stats['visite']['d'.$i]=0; } //visite per giorno della settimana
		$this->stats['visite']['maxday']=0;
		for($i=0;$i<=23;$i++) { $this->stats['visite']['h'.$i]=0; } //visite per fascia oraria
		$this->stats['visite']['maxhour']=0;

		$q="SELECT * FROM ".TABLE_STATISTICHE." UNION SELECT * FROM ".TABLE_STATS_ARCHIVE;
		$p=mysql_query($q);
		while($r=mysql_fetch_array($p))
		{
			$timestamp=mktime(0,0,0,substr($r['date'],5,2),substr($r['date'],8,2),substr($r['date'],0,4));
			if(!isset($this->contatti[substr($r['date'],0,10)])) { $this->contatti[substr($r['date'],0,10)]=0; }
			if(!isset($this->visite[substr($r['date'],0,10)])) { $this->visite[substr($r['date'],0,10)]=0; }
			$this->contatti[substr($r['date'],0,10)]+=$r['contacts'];
			$this->visite[substr($r['date'],0,10)]++;
			if($this->contatti[substr($r['date'],0,10)]>$this->stats['contatti']['max']) { $this->stats['contatti']['max']=$this->contatti[substr($r['date'],0,10)]; }
			if($this->contatti[substr($r['date'],0,10)]<$this->stats['contatti']['min']) { $this->stats['contatti']['min']=$this->contatti[substr($r['date'],0,10)]; }
			if($this->visite[substr($r['date'],0,10)]>$this->stats['visite']['max']) { $this->stats['visite']['max']=$this->visite[substr($r['date'],0,10)]; }
			if($this->visite[substr($r['date'],0,10)]<$this->stats['visite']['min']) { $this->stats['visite']['min']=$this->visite[substr($r['date'],0,10)]; }
			$this->stats['visite']['d'.date("w",$timestamp)]++;
			$this->stats['visite']['h'.intval(substr($r['date'],11,2))]++;
			$this->stats['contatti']['d'.date("w",$timestamp)]+=$r['contacts'];
			$this->stats['contatti']['h'.intval(substr($r['date'],11,2))]+=$r['contacts'];
		}

		//elaboro le medie e totali
		foreach($this->contatti as $ka=>$v) { $this->stats['contatti']['tot']+=$v; }
		$this->stats['contatti']['avg']=round($this->stats['contatti']['tot']/$this->expiration,2);
		foreach($this->visite as $ka=>$v) { $this->stats['visite']['tot']+=$v; }
		$this->stats['visite']['avg']=round($this->stats['visite']['tot']/$this->expiration,2);
		$this->stats['visite']['maxday']=max($this->stats['visite']['d0'],$this->stats['visite']['d1'],$this->stats['visite']['d2'],$this->stats['visite']['d3'],$this->stats['visite']['d4'],$this->stats['visite']['d5'],$this->stats['visite']['d6']);
		$this->stats['visite']['maxhour']=max($this->stats['visite']['h0'],$this->stats['visite']['h1'],$this->stats['visite']['h2'],$this->stats['visite']['h3'],$this->stats['visite']['h4'],$this->stats['visite']['h5'],$this->stats['visite']['h6'],$this->stats['visite']['h7'],$this->stats['visite']['h8'],$this->stats['visite']['h9'],$this->stats['visite']['h10'],$this->stats['visite']['h11'],$this->stats['visite']['h12'],$this->stats['visite']['h13'],$this->stats['visite']['h14'],$this->stats['visite']['h15'],$this->stats['visite']['h16'],$this->stats['visite']['h17'],$this->stats['visite']['h18'],$this->stats['visite']['h19'],$this->stats['visite']['h20'],$this->stats['visite']['h21'],$this->stats['visite']['h22'],$this->stats['visite']['h23']);
		$this->stats['contatti']['maxday']=max($this->stats['contatti']['d0'],$this->stats['contatti']['d1'],$this->stats['contatti']['d2'],$this->stats['contatti']['d3'],$this->stats['contatti']['d4'],$this->stats['contatti']['d5'],$this->stats['contatti']['d6']);
		$this->stats['contatti']['maxhour']=max($this->stats['contatti']['h0'],$this->stats['contatti']['h1'],$this->stats['contatti']['h2'],$this->stats['contatti']['h3'],$this->stats['contatti']['h4'],$this->stats['contatti']['h5'],$this->stats['contatti']['h6'],$this->stats['contatti']['h7'],$this->stats['contatti']['h8'],$this->stats['contatti']['h9'],$this->stats['contatti']['h10'],$this->stats['contatti']['h11'],$this->stats['contatti']['h12'],$this->stats['contatti']['h13'],$this->stats['contatti']['h14'],$this->stats['contatti']['h15'],$this->stats['contatti']['h16'],$this->stats['contatti']['h17'],$this->stats['contatti']['h18'],$this->stats['contatti']['h19'],$this->stats['contatti']['h20'],$this->stats['contatti']['h21'],$this->stats['contatti']['h22'],$this->stats['contatti']['h23']);
	}

	public function getRecords() {
		$output=array();
		$q="SELECT * FROM ".TABLE_STATISTICHE." UNION SELECT * FROM ".TABLE_STATS_ARCHIVE;
		$p=mysql_query($q);
		while($row=mysql_fetch_array($p)) {
			$output[]=$this->prettifyRecord($row);
			}
		return $output;
		}

	public function getVisite() {
		return $this->visite;
		}

	public function getContatti() {
		return $this->contatti;
		}

	public function getStats() {
		return $this->stats;
		}

	public function getRealtime() {
		$output=array();
		
		$output['users']=array();
		$query="SELECT * FROM ".TABLE_STATISTICHE." WHERE `date`>NOW()-900";
		$results=mysql_query($query);
		while($row=mysql_fetch_array($results)) {
			$output['users'][]=$this->prettifyRecord($row);
			}
		$output['online']=count($output['users']);
		
		return $output;
		}

	private function prettifyRecord($r) {
		if($r['ll']=='**') $r['ll']='';
		$r['pages']=explode("\n",trim($r['url']));
		$r['firstpage']=$r['pages'][0];
		$r['lastpage']=$r['pages'][count($r['pages'])-1];
		$r['system']=$this->prettifySystem($r['system']);
		return $r;
		}
	
	private function prettifySystem($s) {
		$output=array("browser"=>"","browserVersion"=>"","os"=>"","osVersion"=>"","mobile"=>false);
		
		/* detect browser */
		//IE
		if(strpos($s,"MSIE")!==false) {
			$output['browser']='Microsoft Internet Explorer';
			preg_match_all("/MSIE ([0-9\.]*)/",$s,$tmp);
			$output['browserVersion']=isset($tmp[1][0])?$tmp[1][0]:'unknow';
			}
		//Firefox
		elseif(strpos($s,"Firefox")!==false) {
			$output['browser']='Mozilla Firefox';
			preg_match_all("/Firefox\/([0-9\.]*)/",$s,$tmp);
			$output['browserVersion']=isset($tmp[1][0])?$tmp[1][0]:'unknow';
			}
		//Chrome
		elseif(strpos($s,"Chrome")!==false) {
			$output['browser']='Google Chrome';
			preg_match_all("/Chrome\/([0-9\.]*)/",$s,$tmp);
			$output['browserVersion']=isset($tmp[1][0])?$tmp[1][0]:'unknow';
			}
		//Safari
		elseif(strpos($s,"Safari")!==false) {
			$output['browser']='Apple Safari';
			preg_match_all("/Safari\/([0-9\.]*)/",$s,$tmp);
			$output['browserVersion']=isset($tmp[1][0])?$tmp[1][0]:'unknow';
			}
		//Opera
		elseif(strpos($s,"Opera")!==false) {
			$output['browser']='Opera';
			preg_match_all("/Opera\/([0-9\.]*)/",$s,$tmp);
			$output['browserVersion']=isset($tmp[1][0])?$tmp[1][0]:'unknow';
			}
		
		/* detect os */
		//Windows
		if(strpos($s,"Windows")!==false) {
			$output['os']='Windows';
			preg_match_all("/Windows ([^;]*);/",$s,$tmp);
			if(isset($tmp[1][0])) {
				if($tmp[1][0]=="NT 6.2") $output['osVersion']="8";
				elseif($tmp[1][0]=="NT 6.1") $output['osVersion']="7";
				elseif($tmp[1][0]=="NT 6.0") $output['osVersion']="Vista";
				elseif($tmp[1][0]=="NT 5.1") $output['osVersion']="XP";
				elseif($tmp[1][0]=="NT 5.2") $output['osVersion']="NT";
				elseif(substr($tmp[1][0],0,5)=="Phone") { $output['osVersion']=$tmp[1][0]; $output['mobile']=true; }
				else $output['osVersion']=$tmp[1][0];
				}
			else $output['osVersion']="unknown";
			}
		//Mac
		elseif(strpos($s,"Mac OS X")!==false) {
			$output['os']='Mac OS X';
			preg_match_all("/Mac OS X ([^;]*);/",$s,$tmp);
			if(isset($tmp[1][0])) {
				$output['osVersion']=str_replace("_",".",$tmp[1][0]);
				}
			else $output['osVersion']="unknown";
			}
		//Linux
		elseif(strpos($s,"Linux")!==false) {
			$output['os']='Linux';
			if(strpos($s,"Ubuntu")!==false) $output['osVersion']="Ubuntu";
			elseif(strpos($s,"Kubuntu")!==false) $output['osVersion']="Kubuntu";
			elseif(strpos($s,"Debian")!==false) $output['osVersion']="Debian";
			elseif(strpos($s,"Mint")!==false) $output['osVersion']="Mint";
			elseif(strpos($s,"Slackware")!==false) $output['osVersion']="Slackware";
			elseif(strpos($s,"Redhat")!==false) $output['osVersion']="Redhat";
			elseif(strpos($s,"gentoo")!==false) $output['osVersion']="Gentoo";
			elseif(strpos($s,"Android")!==false) { $output['os']="Android"; $output['mobile']=true; }
			}
		//Apple mobile
		elseif(strpos($s,"iPhone")!==false) { $output['os']='iPhone'; $output['mobile']=true; }
		elseif(strpos($s,"iPod")!==false) { $output['os']='iPod'; $output['mobile']=true; }
		elseif(strpos($s,"iPad")!==false) { $output['os']='iPad'; $output['mobile']=true; }
		
		return $output;
		}
	}

