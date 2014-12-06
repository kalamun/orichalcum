<?php /* (c) Kalamun.org - GNU/GPL 3 */

/* E-MAIL GATEWAY */
/* note that this class is also used by the admin panel to send e-mails */

class kEmails {
	protected $inited;
	private $from,$to,$subject,$message,$footer,$template,$uid,$attachments,$log,$method,$smtp,$mandrill;

	public function __construct()
	{
		$this->inited=false;
	}
	
	public function init()
	{
		$this->inited=true;
		if(!isset($GLOBALS['__template']))
		{
			require_once($_SERVER['DOCUMENT_ROOT'].BASEDIR.'inc/template.lib.php');
			$GLOBALS['__template']=new kTemplate();
		}
		$this->template=$GLOBALS['__template']->getVar('email_template_default',1);
		if($this->template=="") $this->template="default.php";
		
		$this->footer=$GLOBALS['__template']->getVar('newsletter_footer',1);
		
		$this->method=$GLOBALS['__template']->getVar('email_method',1);
		$this->smtp=array();
		$this->mandrill=array();
		if($this->method=="smtp")
		{
			$this->smtp['host']=$GLOBALS['__template']->getVar('email_smtp_server',1);
			$this->smtp['port']=$GLOBALS['__template']->getVar('email_smtp_server',2);
			if($this->smtp['port']=="") $this->smtp['port']=25;
			$this->smtp['username']=$GLOBALS['__template']->getVar('email_smtp_account',1);
			$this->smtp['password']=$GLOBALS['__template']->getVar('email_smtp_account',2);
		} elseif($this->method=="mandrill") {
			$this->mandrill['api_key']=$GLOBALS['__template']->getVar('email_mandrill_api',1);
		}
	}
	
	public function getVar($var)
	{
		return $this->$var;
	}

	public function send($from,$to,$subject,$message,$template="",$idarch=0,$replacements=array())
	{
		if(!$this->inited) $this->init();
		
		/*
		REPLACEMENTS keys must corresponds to $to keys
		eg:
		$to = array( "info@google.com" , "Roberto Pasini <info@kalamun.org>" );
		$replacements = array(
				array(
					"DESCRIPTION" => "Google",
					"PASSWORD" => "foobar",
					"AFFILIATION" => "customers"
					),
				array(
					"DESCRIPTION" => "myself",
					"PASSWORD" => "spritz",
					"AFFILIATION" => "selfreferences"
					)
			);
		*/
		
		if(empty($to)) return false;
		if(!is_array($to)) $to=array($to);
		
		$this->from=$from;
		$this->to=$to;
		$this->subject=$subject;
		$this->message=$message;
		$output=true;
		
		/* mandrill api: send all mails in a single session */
		if($this->method=="mandrill")
		{
			try {
				require_once($_SERVER['DOCUMENT_ROOT'].BASEDIR.'inc/mandrill/Mandrill.php');
				$mandrill = new Mandrill($this->mandrill['api_key']);

				$this->uid='*|UID|*';
				$composition=$this->preview($from,'*|NAME|* <*|EMAIL|*>',$subject,$message,$template,$this->uid);

				// replace Orichalcum-style placeholders with mailchimp-style placeholders
				foreach($replacements as $replacement)
				{
					foreach($replacement as $n=>$v)
					{
						$composition['html']=str_replace('{'.$n.'}','*|'.$n.'|*',$composition['html']);
						$composition['plain']=str_replace('{'.$n.'}','*|'.$n.'|*',$composition['plain']);
						$this->subject=str_replace('{'.$n.'}','*|'.$n.'|*',$this->subject);
					}
				}
				$composition['html']=str_replace('{NAME}','*|NAME|*',$composition['html']);
				$composition['html']=str_replace('{EMAIL}','*|EMAIL|*',$composition['html']);
				$composition['plain']=str_replace('{NAME}','*|NAME|*',$composition['plain']);
				$composition['plain']=str_replace('{EMAIL}','*|EMAIL|*',$composition['plain']);
				$this->subject=str_replace('{NAME}','*|NAME|*',$this->subject);
				$this->subject=str_replace('{EMAIL}','*|EMAIL|*',$this->subject);
				
				// split "from" in "name" and "mail address"
				$fromname=ADMIN_NAME;
				$frommail=ADMIN_MAIL;
				$from=explode("<",$from);
				if(isset($from[1]) && trim($from[1]," <>")!="")
				{
					$fromname=trim($from[0]);
					$frommail=trim($from[1]," <>");
				} else {
					$frommail=trim($from[0]," <>");
				}
				
				// collect recipients and merge vars
				$recipients=array();
				$mergevars=array();
				
				foreach($to as $k=>$t)
				{
					$toname="";
					$tomail="";
					$t=explode("<",$t);
					if(isset($t[1]) && trim($t[1]," <>")!="")
					{
						$toname=trim($t[0]);
						$tomail=trim($t[1]," <>");
					} else {
						$toname=trim($t[0]," <>");
						$tomail=trim($t[0]," <>");
					}

					$recipients[]=array(
						'email' => $tomail,
						'name' => $toname,
						'type' => 'to'
						);

					$mergevars[]=array(
						'rcpt' => $tomail,
						'vars' => array(
							array(
								'name' => 'NAME',
								'content' => $toname
								),
							array(
								'name' => 'EMAIL',
								'content' => $tomail
								),
							array(
								'name' => 'UID',
								'content' => $this->generateUID()
								)
							)
						);
					
					// add mergevars passed by input array $replacements
					if(!empty($replacements[$k]) && is_array($replacements[$k]))
					{
						foreach($replacements[$k] as $n=>$v)
						{
							$mergevars[count($mergevars)-1]['vars'][]=array(
								'name' => $n,
								'content' => $v
								);
						}
					}
				}


				$message = array(
					'html' => $composition['html'],
					'text' => $composition['plain'],
					'subject' => $this->subject,
					'from_email' => $frommail,
					'from_name' => $fromname,
					'to' =>$recipients,
					'headers' => array(),
					'important' => false,
					'track_opens' => null,
					'track_clicks' => null,
					'auto_text' => null,
					'auto_html' => null,
					'inline_css' => null,
					'url_strip_qs' => null,
					'preserve_recipients' => false,
					'view_content_link' => null,
					'bcc_address' => '',
					'tracking_domain' => null,
					'signing_domain' => null,
					'return_path_domain' => null,
					'merge' => true,
					'merge_language' => 'mailchimp',
					'global_merge_vars' => array(),
					'merge_vars' => $mergevars,
					'tags' => array('idarch'.$idarch),
					'subaccount' => null,
					'google_analytics_domains' => array(),
					'google_analytics_campaign' => '',
					'metadata' => array(),
					'recipient_metadata' => array(),
					'attachments' => array(),
					'images' => array()
				);

				$async = false;
				$ip_pool = '';
				$send_at = '';
				$result = $mandrill->messages->send($message, $async, $ip_pool, $send_at);
				

			} catch(Mandrill_Error $e) {
				//echo 'A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage();
				$output=false;
			}

			// archiveMail
			if(!empty($result))
			{
				foreach($result as $r)
				{
					if($r['status']=="sent")
					{
						$uid="";
						$name="";
						$email="";
						foreach($mergevars as $v)
						{
							if($v['rcpt']==$r['email'])
							{
								$name=$v['vars'][0]['content'];
								$email=$v['vars'][1]['content'];
								$uid=$v['vars'][2]['content'];
							}
						}
						
						$msg=array();
						$composition['subject']=$this->subject;
						foreach( array('html','plain','subject') as $part)
						{
							$msg[$part]=$composition[$part];
							$msg[$part]=str_replace("*|NAME|*",$name,$msg[$part]);
							$msg[$part]=str_replace("*|EMAIL|*",$email,$msg[$part]);
							$msg[$part]=str_replace("*|UID|*",$uid,$msg[$part]);
							if(!empty($replacements[$k]) && is_array($replacements[$k]))
							{
								foreach($replacements[$k] as $n=>$v)
								{
									$msg[$part]=str_replace("*|".$n."|*",$v,$msg[$part]);
								}
							}
						}
						
						$this->archiveMail($uid,$this->from,$r['email'],$msg['subject'],$composition['headers'],$msg['html'],$msg['plain'],$idarch);
					}
				}
			}
			
		} else {
		
			/* sent each e-mail separately */
			foreach($this->to as $k=>$to)
			{
				$this->uid=$this->generateUID();
				
				// replace mergevars
				$msg=$message;
				$sbj=$subject;
				if(!empty($replacements[$k]) && is_array($replacements[$k]))
				{
					foreach($replacements[$k] as $n=>$v)
					{
						$msg=str_replace("{".$n."}",$v,$msg);
						$sbj=str_replace("{".$n."}",$v,$sbj);
					}
				}
				$toname="";
				$tomail="";
				$t=explode("<",$to);
				if(isset($t[1]) && trim($t[1]," <>")!="")
				{
					$toname=trim($t[0]);
					$tomail=trim($t[1]," <>");
				} else {
					$toname=trim($t[0]," <>");
					$tomail=trim($t[0]," <>");
				}
				$msg=str_replace("{NAME}",$toname,$msg);
				$msg=str_replace("{EMAIL}",$tomail,$msg);
				$sbj=str_replace("{NAME}",$toname,$sbj);
				$sbj=str_replace("{EMAIL}",$tomail,$sbj);


				$composition=$this->preview($from,$to,$sbj,$msg,$template,$this->uid);

				$sent=false;
				if($this->method=="")
				{
					/* send via mail() */
					if(mail($to,$composition['subject'],$composition['message'],$composition['headers'])) $sent=true;
				
				} elseif($this->method=="smtp") {
					/* send via smtp */
					if($smtpConn=fsockopen($this->smtp['host'],$this->smtp['port']))
					{
						$talk="";
						fputs ($smtpConn,"EHLO ".$_SERVER['HTTP_HOST']."\r\n");
						if($talk["hello"]=fgets($smtpConn))
						{
							$tmp=$talk["hello"];
							while($tmp{3}=='-') { $tmp=fgets($smtpConn); $talk["hello"].="\n".$tmp; }
						}
						fputs($smtpConn,"AUTH LOGIN\r\n");
						if($talk["res"]=fgets($smtpConn))
						{
							$tmp=$talk["res"];
							while($tmp{3}=='-') { $tmp=fgets($smtpConn); $talk["res"].="\n".$tmp; }
						}
						fputs($smtpConn,base64_encode($this->smtp['username'])."\r\n");
						if($talk["user"]=fgets($smtpConn))
						{
							$tmp=$talk["user"];
							while($tmp{3}=='-') { $tmp=fgets($smtpConn); $talk["user"].="\n".$tmp; }
						}
						fputs($smtpConn,base64_encode($this->smtp['password'])."\r\n");
						if($talk["pass"]=fgets($smtpConn))
						{
							$tmp=$talk["pass"];
							while($tmp{3}=='-') { $tmp=fgets($smtpConn); $talk["pass"].="\n".$tmp; }
						}
						fputs ($smtpConn,"MAIL FROM: <".preg_replace('/.*?<(.*)>/','$1',$this->from).">\r\n");
						if($talk["from"]=fgets($smtpConn))
						{
							$tmp=$talk["from"];
							while($tmp{3}=='-') { $tmp=fgets($smtpConn); $talk["from"].="\n".$tmp; }
						}
						fputs ($smtpConn,"RCPT TO: <".preg_replace('/.*?<(.*)>/','$1',$to).">\r\n");
						if($talk["to"]=fgets($smtpConn))
						{
							$tmp=$talk["to"];
							while($tmp{3}=='-') { $tmp=fgets($smtpConn); $talk["to"].="\n".$tmp; }
						}
						fputs($smtpConn,"DATA\r\n");
						if($talk["data"]=fgets($smtpConn))
						{
							$tmp=$talk["data"];
							while($tmp{3}=='-') { $tmp=fgets($smtpConn); $talk["data"].="\n".$tmp; }
						}
						fputs($smtpConn,"To: ".$to."\r\nSubject:".$composition['subject']."\r\n".$composition['headers']."\n\n".$composition['message']."\r\n.\r\n");
						if($talk["send"]=fgets($smtpConn))
						{
							$tmp=$talk["send"];
							while($tmp{3}=='-') { $tmp=fgets($smtpConn); $talk["send"].="\n".$tmp; }
						}
						fputs($smtpConn,"QUIT\r\n");
						if($talk["quit"]=fgets($smtpConn))
						{
							$tmp=$talk["quit"];
							while($tmp{3}=='-') { $tmp=fgets($smtpConn); $talk["quit"].="\n".$tmp; }
						}
						fclose($smtpConn);
						$sent=true;
					}
				}

				if($sent==true)
				{
					if($this->log=="true") $this->archiveMail($this->uid,$this->from,$to,$sbj,$composition['headers'],$composition['html'],$composition['plain'],$idarch);
				} else $output=false;
			}
		}

		return $output;
	}
	
	public function preview($from,$to,$subject,$message,$template="",$uid="")
	{
		if(!$this->inited) $this->init();
		if($template=="") $template=$this->template;

		$this->from=$this->fixEmailSyntax($from);
		$this->to=$this->fixEmailSyntax($to);
		$this->subject=$subject;
		$this->message=$message;
		$this->uid=$uid;

		$boundary_main="----=_NextPart_".rand(10000,99999).".".rand(100000000,999999999);
		$composition=array();
		$composition['headers']="From: ".$this->from."\n";
		$composition['headers'].="MIME-Version: 1.0\n";
		$composition['headers'].="Content-Type: multipart/alternative; \n\tboundary=\"".$boundary_main."\"\n";

		//$composition['subject']=mb_convert_encoding($this->subject,"UTF-8","HTML-ENTITIES");
		$composition['subject']=$this->subject;
		$composition['subject']=b3_unhtmlize($composition['subject']);
		$composition['subject']='=?UTF-8?B?'.base64_encode($composition['subject']).'?=';

		/* pure HTML -> import template */
		$composition['html']=$this->getMailTemplate($GLOBALS['__template']->getTemplateDir().'email/'.$template);

		/* pure PLAIN -> clean HTML */
		$composition['plain']=mb_convert_encoding($composition['html'],"UTF-8","HTML-ENTITIES");
		$composition['plain']=preg_replace("/<title>.*?<\/title>/si","\n",$composition['plain']);
		$composition['plain']=preg_replace("/<style[^>]*>.*?<\/style>/si","\n",$composition['plain']);
		$composition['plain']=str_replace("<br />","\n",$composition['plain']);
		$composition['plain']=str_replace("\t","",$composition['plain']);
		$composition['plain']=str_replace("\r","",$composition['plain']);
		$composition['plain']=preg_replace("/ +/s"," ",$composition['plain']);
		$composition['plain']=preg_replace("/\n+/s","\r\n",$composition['plain']);
		$composition['plain']=str_replace("\n","\r\n",$composition['plain']);
		$composition['plain']=strip_tags($composition['plain']);
		$composition['plain']=trim($composition['plain']);

		/* conform to quoted-printable */
		$composition['chunked-plain']=quoted_printable_encode($composition['plain']);
		$composition['chunked-html']=quoted_printable_encode($composition['html']);

		$composition['message']="\n";
		$composition['message'].="--".$boundary_main."\n";
		$composition['message'].="Content-Type: text/plain; charset=\"utf-8\"\n";
		$composition['message'].="Content-Transfer-Encoding: quoted-printable\n";
		$composition['message'].="\n".$composition['chunked-plain']."\n\n";
		$composition['message'].="--".$boundary_main."\n";
		$composition['message'].="Content-Type: text/html; charset=\"utf-8\"\n";
		$composition['message'].="Content-Transfer-Encoding: quoted-printable\n";
		$composition['message'].="\n".$composition['chunked-html'];
		$composition['message'].="\n\n--".$boundary_main."--\n\n";

		return $composition;
	}
	
	public function spamratio()
	{
		return false;
	}
	
	public function getMailTemplate($template='')
	{
		if(!$this->inited) $this->init();
		if($template=="") $template=$GLOBALS['__template']->getTemplateDir().'email/'.$this->template;
		if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/'.BASEDIR.$template)) $template="template/bettino/email/default.php";
		if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/'.BASEDIR.$template)) return false;
		if(is_file($_SERVER['DOCUMENT_ROOT'].'/'.BASEDIR.$template))
		{
			ob_start();
			include($_SERVER['DOCUMENT_ROOT'].'/'.BASEDIR.$template);
			$contents=ob_get_contents();
			ob_end_clean();
			return $contents;
		}
		return false;
	}

	public function getMailSubTemplate($f)
	{
		if(!$this->inited) $this->init();
		$template=$GLOBALS['__template']->getTemplateDir().'email/inc/'.$f.'.php';
		if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/'.BASEDIR.$template)) return false;
		if(is_file($_SERVER['DOCUMENT_ROOT'].'/'.BASEDIR.$template)) {
			ob_start();
			include($_SERVER['DOCUMENT_ROOT'].'/'.BASEDIR.$template);
			$contents=ob_get_contents();
			ob_end_clean();
			return $contents;
			}
		return false;
	}

	public function archiveMail($uid,$from,$to,$title,$header,$html,$plain,$idarch=0)
	{
		if(!$this->inited) $this->init();
		$from=ksql_real_escape_string($from);
		$to=ksql_real_escape_string($to);
		$title=ksql_real_escape_string($title);
		$header=ksql_real_escape_string($header);
		$html=ksql_real_escape_string($html);
		$plain=ksql_real_escape_string($plain);
		if($uid=="") $uid=$this->generateUID();
		$uid=ksql_real_escape_string($uid);
		$idarch=intval($idarch);
		
		$query="INSERT INTO ".TABLE_EMAIL_LOG." (`date`,`from`,`to`,`title`,`header`,`html`,`plain`,`uid`,`readed`,`idarch`) VALUES(NOW(),'".$from."','".$to."','".$title."','".$header."','".$html."','".$plain."','".$uid."','0000-00-00 00:00:00','".$idarch."')";
		ksql_query($query);
		return $uid;
	}
	
	public function setAsRead($uid)
	{
		$query="UPDATE `".TABLE_EMAIL_LOG."` SET `readed`=NOW() WHERE `uid`='".ksql_real_escape_string($uid)."' LIMIT 1";
		if(ksql_query($query)) return true;
		return false;
	}

	public function generateUID()
	{
		$chars="QWERTYUIOPASDFGHKLZXCVBNM987654321MNBVCXZLKHGFDSAPOIUTREQ123456789";
		$num=dechex(date("yzHis"));
		while(strlen($num)<16)
		{
			$start=rand(0,strlen($chars)-1);
			$num.=substr($chars,$start,1);
		}
		return $num;
	}

	private function fixEmailSyntax($email)
	{
		// verify the syntax of the e-mail and correct it
		$email=trim($email);
		if(strpos($email,"<")!==false)
		{
			//format: "name" <email@domain.tld>
			list($name,$address)=explode("<",$email);
			$name=trim($name,' "');
			$address=trim($address,' <>');
			$email='"'.$name.'" <'.$address.'>';
		} else {
			$email=trim($email);
		}
		return $email;
	}
}
