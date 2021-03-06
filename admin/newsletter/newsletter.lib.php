<?php  /* (c)2013 Kalamun.org GPLv3 */

class kaNewsletter {
	protected $kaMembers, $kaStats;

	public function __construct()
	{
		require_once($_SERVER['DOCUMENT_ROOT'].'/'.ADMINDIR.'members/members.lib.php');
		$this->kaMembers=new kaMembers();
	}

	/* returns the list of newsletters available */
	public function getNewslettersList($vars=array())
	{
		$output=array();
		$query="SELECT * FROM `".TABLE_NEWSLETTER_LISTE."` WHERE ";
		if(isset($vars['idlista'])) $query.=" `idlista`='".ksql_real_escape_string($vars['idlista'])."' AND ";
		if(isset($vars['ll'])) $query.=" `ll`='".ksql_real_escape_string($vars['ll'])."' AND ";
		$query.=" `idlista`>0 ORDER BY `lista`";
		$results=ksql_query($query);
		while($row=ksql_fetch_array($results))
		{
			$row['subscribers_number'] = $this->countNewsletterSubsbribers($row['idlista']);
			$output[]=$row;
		}
		return $output;
	}
	
	/* returns the number of subscribers for the given list */
	public function countNewsletterSubsbribers($idlista)
	{
		$idlista = intval($idlista);
		$count = $this->kaMembers->countUsers(array("lists"=>array($idlista), "conditions"=>"`status`='act'"));
		return $count;
	}

	/* returns the list of available email templates for the current default template */
	public function getTemplatesList()
	{
		$output=array();
		$template=$GLOBALS['kaImpostazioni']->getVar('template_default',1);
		$template_email=$GLOBALS['kaImpostazioni']->getVar('email_template_default',1);

		$dir = $_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_TEMPLATE.$template.'/email';
		if($template!=""&&file_exists($dir))
		{
			foreach(scandir($dir) as $filename)
			{
				if(trim($filename,".")!="" && !is_dir($dir.'/'.$filename) && substr($filename,-4)=='.php')
				{
					$tmp = array();
					$tmp['filename'] = $filename;
					$tmp['label'] = str_replace(".php","",$filename);
					$tmp['label'] = str_replace("_"," ",$tmp['label']);
					$tmp['default'] = ($template_email==$filename ? true : false);
					$output[] = $tmp;
				}
			}
		}

		return $output;
	}
	
	// returns an array of blocks names for the requested template
	public function getTextBlocksFromTemplate($template_email)
	{
		$template_email = trim($template_email, " ./");
		
		$output = array();
		$template = $GLOBALS['kaImpostazioni']->getVar('template_default',1);
		$filename = $_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_TEMPLATE.$template.'/email/'.$template_email;
		if(!file_exists($filename)) return $output;

		$filecontent = file_get_contents($filename);
		preg_match_all("/kGetEmailMessage\((.*?)\)/", $filecontent, $match);
		foreach($match[1] as $block)
		{
			$block = trim($block, " '\"");
			$block = $this->stringToId($block);
			$output[$block] = true;
		}
		
		return array_keys($output);
	}
	
	// convert string to id compatible chars
	public function stringToId($string)
	{
		$string = str_replace(" ","_",$string);
		$string = str_replace("'","_",$string);
		$string = str_replace('"',"_",$string);
		$string = str_replace('?',"_",$string);
		return $string;
	}

	/* returns the configuration values of the list */
	public function getConfig()
	{
		$config=array();

		$query="SELECT `value1` FROM `".TABLE_CONFIG."` WHERE `param`='newsletter_pretitolo' AND ll='".ksql_real_escape_string($_SESSION['ll'])."' LIMIT 1";
		$results=ksql_query($query);
		$row=ksql_fetch_array($results);
		$config['prefix']=$row['value1'];

		$query="SELECT `value1` FROM ".TABLE_CONFIG." WHERE `param`='email_footer' AND ll='".ksql_real_escape_string($_SESSION['ll'])."' LIMIT 1";
		$results=ksql_query($query);
		$row=ksql_fetch_array($results);
		$config['footer']=$row['value1'];
		
		$query="SELECT * FROM ".TABLE_CONFIG." WHERE `param`='newsletter_mittente' AND ll='".ksql_real_escape_string($_SESSION['ll'])."' LIMIT 1";
		$results=ksql_query($query);
		$row=ksql_fetch_array($results);
		if(!isset($row['value1'])||trim($row['value1'])=="") $row['value1']=ADMIN_NAME;
		if(!isset($row['value2'])||trim($row['value2'])=="") $row['value2']=ADMIN_MAIL;
		$config['from']=$row['value1'].' <'.$row['value2'].'>';

		return $config;
	}

	/* returns an array of members (from members table) that match filters */
	public function getRecipients($vars)
	{
		$output=array();
		return $this->kaMembers->getUsersList($vars);
	}

	/* subscribe an existent user to some lists */
	public function subscribe($idmember,$lists)
	{
		if(!is_array($lists)) $lists=array($lists);

		$newsletter_lists=",";
		foreach($lists as $idlista)
		{
			$newsletter_lists.=$idlista.",";
		}

		return $this->kaMembers->updateNewsletter($idmember,$newsletter_lists);
	}

	public function sendWelcomeMessage($idmember)
	{
		if(!isset($GLOBALS['kaImpostazioni']))
		{
			require_once($_SERVER['DOCUMENT_ROOT'].ADMINDIR.'inc/config.lib.php');
			$GLOBALS['kaImpostazioni']=new kaImpostazioni();
		}

		//init Bettino front-end for email processing
		require_once(BASERELDIR."inc/tplshortcuts.lib.php");
		kInitBettino(BASERELDIR);

		$member=$this->kaMembers->getUserById($idmember);
		
		$to=$member['name'].' <'.$member['email'].'>';

		$subject=$GLOBALS['kaImpostazioni']->getVar('newsletter_benvenuto',1);

		$message=$GLOBALS['kaImpostazioni']->getVar('newsletter_benvenuto',2);
		$message=str_replace("{NAME}",$member['name'],$message);
		$message=str_replace("{EMAIL}",$member['email'],$message);
		$message=str_replace("{USERNAME}",$member['username'],$message);
		$message=str_replace("{PASSWORD}",$member['password'],$message);
		$message=str_replace("{AFFILIATION}",$member['affiliation'],$message);
		$message=str_replace("{EXPIRATION}",$member['expiration'],$message);

		$sender=$GLOBALS['kaImpostazioni']->getParam('newsletter_mittente');
		if(!isset($sender['value1'])||$sender['value1']=="") $sender['value1']=ADMIN_NAME;
		if(!isset($sender['value2'])||$sender['value2']=="") $sender['value2']=ADMIN_MAIL;
		$from=$sender['value1'].' <'.$sender['value2'].'>';

		if($GLOBALS['__emails']->send($from,$to,$subject,$message)) return true;
		return false;
	}

	public function addList($vars=array())
	{
		if(!isset($vars['listname'])) return false;
		if(!isset($vars['description'])) return false;
		$query="INSERT INTO `".TABLE_NEWSLETTER_LISTE."` (`data`,`lista`,`descr`,`ll`) VALUES(NOW(),'".b3_htmlize($vars['listname'],true,"")."','".b3_htmlize($vars['description'],true)."','".$_SESSION['ll']."')";

		if(ksql_query($query)) return true;
		else return false;
	}
	
	public function updateList($vars=array())
	{
		if(!isset($vars['idlista'])) return false;
		if(!isset($vars['listname'])) return false;
		if(!isset($vars['description'])) return false;
		$query="UPDATE `".TABLE_NEWSLETTER_LISTE."` SET `lista`='".b3_htmlize($vars['listname'],true,"")."',`descr`='".b3_htmlize($vars['description'],true)."' WHERE `idlista`=".ksql_real_escape_string($vars['idlista']);

		if(ksql_query($query)) return true;
		else return false;
	}

	public function emptyList($vars=array())
	{
		if(empty($vars['idlist']))
		{
			trigger_error('Is not possible to empty list: no list specified');
			return false;
		}
		
		// get list members
		foreach($this->kaMembers->getUsersList(array('lists'=>array($vars['idlist']))) as $member)
		{
			// update each member
			$newsletter_lists = str_replace(','.$vars['idlist'].',', '', $member['newsletter_lists']);
			$this->kaMembers->updateNewsletter($member['idmember'], $newsletter_lists);
		}
		
		return true;
	}

	public function deleteList($vars=array())
	{
		if(!isset($vars['idlista'])) return false;

		if(isset($vars['move_to']))
		{
			//if moving users to another list, set the new list if they aren't yet subscribed
			$query="UPDATE `".TABLE_MEMBERS."` SET `newsletter_lists`=CONCAT(`newsletter_lists`,'".ksql_real_escape_string($vars['move_to']).",') WHERE `newsletter_lists` LIKE '%,".$vars['idlista'].",%' AND `newsletter_lists` NOT LIKE '%,".$vars['move_to'].",%'";
			if(!ksql_query($query)) return false;
		}

		//then remove the old list from all users
		$query="UPDATE `".TABLE_MEMBERS."` SET `newsletter_lists`=REPLACE(`newsletter_lists`,',".ksql_real_escape_string($vars['idlista']).",',',')";
		if(!ksql_query($query)) return false;

		//then delete the old list
		$query="DELETE FROM `".TABLE_NEWSLETTER_LISTE."` WHERE `idlista`=".ksql_real_escape_string($vars['idlista'])." LIMIT 1";
		if(!ksql_query($query)) return false;

		return true;
	}



	/**********************/
	/* Archive management */
	/**********************/

	public function addToArchive($vars)
	{
		if(!isset($vars['subject'])) return false;
		if(!isset($vars['message'])) return false;
		if(!isset($vars['template'])) $vars['template']="";
		if(!isset($vars['recipients_number'])) $vars['recipients_number']=0;

		$query="INSERT INTO `".TABLE_NEWSLETTER_ARCH."` (`data`,`titolo`,`testo`,`template`,`destinatari`) VALUES(NOW(),'".ksql_real_escape_string($vars['subject'])."','".ksql_real_escape_string($vars['message'])."','".ksql_real_escape_string($vars['template'])."','".ksql_real_escape_string($vars['recipients_number'])."')";
		if(!ksql_query($query)) return false;
		else return ksql_insert_id();
	}

	public function getArchiveList($vars)
	{
		if(!isset($vars['from'])) $vars['from']=0;
		if(!isset($vars['limit'])) $vars['limit']=20;
		if(!isset($vars['stats'])) $vars['stats']=true;

		if(!empty($vars['stats']))
		{
			require_once("../stats/stats.lib.php");
			$this->kaStats=new kaStats(array("visits"=>false, "newsletter"=>true));
		}

		$output=array();
		$query="SELECT * FROM ".TABLE_NEWSLETTER_ARCH." WHERE `data`>0 ";
		if(isset($vars['conditions']))
		{
			$query.=" AND (".$vars['conditions'].") ";
		}
		$query.=" ORDER BY `data` DESC LIMIT ".intval($vars['from']).",".intval($vars['limit']);
		$results=ksql_query($query);
		while($row=ksql_fetch_array($results))
		{
			if(!empty($vars['stats']))
			{
				$row['inqueue'] = $this->getQueueCount(array("idarch"=>$row['idarch']));
				$summarycount=$this->kaStats->getSummaryEntry("newsletter", "read", $row['idarch']);
				if(!isset($summarycount['count'])) $summarycount['count']=0;
				$row['read'] = intval($this->getLogCount(array("idarch"=>$row['idarch'], "read"=>true))) + intval($summarycount['count']);
			}
			$output[]=$row;
		}

		return $output;
	}

	public function getFromArchive($vars)
	{
		if(!isset($vars['idarch'])) return false;

		$query="SELECT * FROM `".TABLE_NEWSLETTER_ARCH."` WHERE `idarch`='".ksql_real_escape_string($vars['idarch'])."' LIMIT 1";
		$results=ksql_query($query);
		$row=ksql_fetch_array($results);

		$row['testo'] = unserialize($row['testo']);
		if(!is_array($row['testo'])) $row['testo'] = array("-default-" => $row['testo']);

		return $row;
	}

	public function countArchiveRecords()
	{
		$query="SELECT count(*) AS `tot` FROM `".TABLE_NEWSLETTER_ARCH."`";
		$results=ksql_query($query);
		$row=ksql_fetch_array($results);
		return $row['tot'];
	}

	public function deleteFromArchive($idarch)
	{
		$query="DELETE FROM `".TABLE_NEWSLETTER_ARCH."` WHERE `idarch`='".intval($idarch)."' LIMIT 1";
		if(ksql_query($query)) {
			return $this->removeFromQueueByIdarch($idarch);
			}
		return false;
	}

	/********************/
	/* Queue management */
	/********************/

	//add a mail to queue list
	public function addToQueue($vars)
	{
		if(!isset($vars['from'])) return false;
		if(!isset($vars['to'])) return false;
		if(!isset($vars['subject'])) return false;
		if(!isset($vars['message'])) return false;
		if(!isset($vars['template'])) $vars['template']="";
		if(empty($vars['idarch'])) $vars['idarch']="0";
		if(empty($vars['mergevars']) || !is_array($vars['mergevars'])) $vars['mergevars']=array();
		
		$vars['message'].="\n<!--MERGEVARS\n";
		foreach($vars['mergevars'] as $k=>$v)
		{
			$vars['message'].=str_replace("=","",$k)."=".str_replace("\n"," ",$v)."\n";
		}
		$vars['message'].="-->";

		$query="INSERT INTO `".TABLE_EMAIL_QUEUE."` (`idarch`,`date`,`from`,`to`,`subject`,`message`,`template`) VALUES('".ksql_real_escape_string($vars['idarch'])."',NOW(),'".ksql_real_escape_string($vars['from'])."','".ksql_real_escape_string($vars['to'])."','".ksql_real_escape_string($vars['subject'])."','".ksql_real_escape_string($vars['message'])."','".ksql_real_escape_string($vars['template'])."')";
		if(!ksql_query($query)) return false;
		else return ksql_insert_id();
	}

	//count how many emails are in queue
	public function getQueueCount($vars=array())
	{
		$query="SELECT count(*) AS `tot` FROM `".TABLE_EMAIL_QUEUE."`";
		if(isset($vars['idarch'])) $query.=" WHERE `idarch`='".intval($vars['idarch'])."' ";
		$results=ksql_query($query);
		$row=ksql_fetch_array($results);
		return $row['tot'];
	}
	
	//get an array of latest emails in queue
	public function getQueueList($vars)
	{
		$output=array();

		$query="SELECT * FROM `".TABLE_EMAIL_QUEUE."` ORDER BY `date`,`idemlq` ";
		if(isset($vars['limit'])) $query.=" LIMIT 0,".intval($vars['limit']);
		$results=ksql_query($query);

		$i=0;
		while($row=ksql_fetch_array($results))
		{
			$output[$i]=$row;

			/* extract merge vars from email body */
			$output[$i]['mergevars']=array();
			preg_match("/\<\!--MERGEVARS(.*)--\>$/s",$row['message'],$vars);
			if(isset($vars[1]))
			{
				$vars=explode("\n",trim($vars[1]));
				foreach($vars as $v)
				{
					$param=substr($v,0,strpos($v,"="));
					$value=substr($v,strpos($v,"=")+1);
					$output[$i]['mergevars'][$param]=$value;
				}
			}
			
			$output[$i]['message'] = trim(preg_replace("/\<\!--.*?--\>/s", "", $output[$i]['message']));
			$output[$i]['message'] = unserialize($output[$i]['message']);
			
			$i++;
		}

		return $output;
	}

	//remove a mail from queue
	public function removeFromQueueById($idemlq)
	{
		$query="DELETE FROM `".TABLE_EMAIL_QUEUE."` WHERE `idemlq`='".ksql_real_escape_string($idemlq)."' LIMIT 1";
		if(ksql_query($query)) return true;
		else return false;
	}
	
	public function removeFromQueueByIdarch($idarch)
	{
		$query="DELETE FROM `".TABLE_EMAIL_QUEUE."` WHERE `idarch`='".ksql_real_escape_string($idarch)."'";
		if(ksql_query($query)) return true;
		else return false;
	}

	//count how many emails are in log
	public function getLogCount($vars=array())
	{
		$query="SELECT count(*) AS `tot` FROM `".TABLE_EMAIL_LOG."` WHERE `ideml`>0";
		if(isset($vars['idarch'])) $query.=" AND `idarch`='".intval($vars['idarch'])."' ";
		if(isset($vars['read']) && $vars['read']==true) $query.=" AND `readed`>'0000-00-00 00:00:00' ";

		$results=ksql_query($query);
		$row=ksql_fetch_array($results);
		return $row['tot'];
	}

	// get an array of email readers
	public function getReadFrom($vars=array())
	{
		$output=array();
		$query="SELECT * FROM `".TABLE_EMAIL_LOG."` WHERE `readed`>'0000-00-00 00:00:00' ";
		if(isset($vars['idarch'])) $query.=" AND `idarch`='".intval($vars['idarch'])."' ";
		$results=ksql_query($query);
		while($row=ksql_fetch_array($results))
		{
			$output[]=preg_replace("/.* <(.*?)>/","$1",$row['to']);
		}
		return $output;
	}
	
	// get an array of email readers
	public function deleteLogs($vars=array())
	{
		$output=array();
		$query="DELETE FROM `".TABLE_EMAIL_LOG."` WHERE `ideml`>0 ";
		if(isset($vars['idarch'])) $query.=" AND `idarch`='".intval($vars['idarch'])."' ";
		$results=ksql_query($query);
		
		return $results;
	}
	
}
