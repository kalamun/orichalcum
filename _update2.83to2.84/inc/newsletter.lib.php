<?php 
class kNewsletter {
	protected $inited;
	protected $allowedDate,$categoriesList,$allowedCategories,$kText,$__usersList,$loadedNews,$dir_news,$orderby,$if_expired,$newsTemplate,$newsLayout,$imgs,$docgallery;
	
	public function __construct()
	{
		$this->inited=false;
	}

	/* reset to defaults value */
	public function init()
	{
		$this->inited=true;
		require_once($_SERVER['DOCUMENT_ROOT'].BASEDIR."admin/inc/main.lib.php");
		require_once($_SERVER['DOCUMENT_ROOT'].BASEDIR."inc/kalamun.lib.php");
		require_once($_SERVER['DOCUMENT_ROOT'].BASEDIR."inc/utenti.lib.php");
		$this->kText=new kText();
		$this->imgs=new kImages();
		$this->docgallery=new kDocgallery();
	}
	

	/* get a list by name or id */
	public function getList($params)
	{
		if(!$this->inited) $this->init();
		
		if(empty($params['name']) && empty($params['id'])) return false;
		
		$query = "SELECT * FROM `".TABLE_NEWSLETTER_LISTE."` WHERE ";
		if(!empty($params['id'])) $query .= "`idlista` = '".intval($params['id'])."' ";
		if(!empty($params['name'])) $query .= "`lista` = '".ksql_real_escape_string(b3_htmlize($params['name'], true, ""))."' ";
		$query .= " LIMIT 1";
		
		$results = ksql_query($query);
		$row = ksql_fetch_array($results);
		
		return $row;
	}

	
	/* get the list of the lists */
	public function getLists()
	{
		if(!$this->inited) $this->init();
		
		$output = array();
		$query = "SELECT * FROM `".TABLE_NEWSLETTER_LISTE."` ORDER BY `lista`";
		$results = ksql_query($query);
		while($row = ksql_fetch_array($results))
		{
			$output[] = $row;
		}
		
		return $output;
	}

	
	/* subscribe to a list
	 params:
	 - name
	 - email
	 - listname
	 - listid
	 - affiliation
	*/
	public function subscribe($params)
	{
		if(!$this->inited) $this->init();

		// verify the input params
		if(empty($params['email'])) return false;
		if(empty($params['name'])) $params['name']='';
		if(empty($params['affiliation'])) $params['affiliation']='';
		
		// get the id of the list by list name
		if(empty($params['listid']) && !empty($params['listname']))
		{
			$list = $this->getList( array("name" => $params['listname']) );
			if(!empty($list['idlista'])) $params['listid'] = $list['idlista'];
		}
		
		// if no list is defined, get the first one
		if(empty($params['listid']) && empty($params['listname']))
		{
			$lists = $this->getLists();
			if(empty($lists))
			{
				trigger_error('Newsletter: you have not created any list yet, please create almost one list under admin/newsletter');
				return false;
			}
			$params['listid'] = $lists[0]['idlista'];
		}
		
		$params['listid'] = intval($params['listid']);
		
		// get the user with the same e-mail
		$member = kGetMemberByEmail($params['email']);
		
		// if the user doesn't exists, create a new one
		if(empty($member))
		{
			$idmember = kMemberRegister($params['email'], false, $params['name'], $params['email'], $params['affiliation'], false);
			
			if(empty($idmember))
			{
				trigger_error('Newsletter: an error occurred while registering member');
				return false;
			}
			
			$member = kGetMemberById($idmember);
		}
		
		// subscribe to the newsletter
		if(strpos($member['newsletter_lists'], ",".$params['listid'].",") !== false) return true; // skip if already registered
		
		$member['newsletter_lists'] .= $params['listid'].',';
		
		if(!kMemberReplaceData($member['username'], 'newsletter_lists', $member['newsletter_lists'], $params['affiliation'])) return false;
		
		// send notification
		$this->sendWelcomeMessage($member['idmember']);
		
		return true;
	}

	
	// send welcome message
	public function sendWelcomeMessage($idmember)
	{
		$member = kGetMemberById($idmember);
		if(empty($member)) return false;
		
		$to = $member['name'].' <'.$member['email'].'>';

		$subject = kGetVar('newsletter_benvenuto', 1);
		if(empty(trim($subject))) return true;

		$message = kGetVar('newsletter_benvenuto', 2);
		if(empty(trim(strip_tags($message)))) return true;

		$message = str_replace("{NAME}", $member['name'], $message);
		$message = str_replace("{EMAIL}", $member['email'], $message);
		$message = str_replace("{USERNAME}", $member['username'], $message);
		$message = str_replace("{PASSWORD}", $member['password'], $message);
		$message = str_replace("{AFFILIATION}", $member['affiliation'], $message);
		$message = str_replace("{EXPIRATION}", $member['expiration'], $message);
		
		$sender_name = kGetVar('newsletter_mittente', 1);
		$sender_email = kGetVar('newsletter_mittente', 2);
		if(empty($sender_name)) $sender_name = ADMIN_NAME;
		if(empty($sender_email)) $sender_email = ADMIN_MAIL;
		$from = $sender_name.' <'.$sender_email.'>';

		if($GLOBALS['__emails']->send($from, $to, $subject, $message)) return true;
		return false;
	}

}

