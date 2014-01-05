<?php
/* (c) Kalamun.org - GNU/GPL 3 */

/* E-MAIL GATEWAY */
/* be careful: this class is used also by the admin panel to send e-mails */

class kEmails {
	protected $inited;
	private $from,$to,$subject,$message,$footer,$template,$attachments,$log,$smtp;

	public function __construct() {
		$this->inited=false;
		}
	
	public function init() {
		$this->inited=true;
		global $__template;
		if(!isset($__template)) {
			require_once($_SERVER['DOCUMENT_ROOT'].BASEDIR.'inc/template.lib.php');
			$__template=new kTemplate();
			}
		$this->template=$__template->getVar('email_template_default',1);
		if($this->template=="") $this->template="default.php";
		$this->log=$__template->getVar('email_log',1);
		$this->smtp=array('on'=>false);
		$this->smtp['on']=$__template->getVar('email_smtp_on',1);
		if($this->smtp['on']=="true") {
			$this->smtp['host']=$__template->getVar('email_smtp_server',1);
			$this->smtp['port']=$__template->getVar('email_smtp_server',2);
			if($this->smtp['port']=="") $this->smtp['port']=25;
			$this->smtp['username']=$__template->getVar('email_smtp_account',1);
			$this->smtp['password']=$__template->getVar('email_smtp_account',2);
			}
		}
	
	public function getVar($var) {
		return $this->$var;
		}

	public function send($from,$to,$subject,$message,$template="") {
		if(!$this->inited) $this->init();
		$this->from=$from;
		$this->to=$to;
		$this->subject=$subject;
		$this->message=$message;
	
		$composition=$this->preview($from,$to,$subject,$message,$template="");

		$sent=false;
		if($this->smtp['on']!="true") {
			/* send via mail() */
			if(mail($this->to,$composition['subject'],$composition['message'],$composition['headers'])) $sent=true;
			}
		else {
			/* send via smtp */
			if($smtpConn=fsockopen($this->smtp['host'],$this->smtp['port'])) {
				$talk="";
				fputs ($smtpConn,"EHLO ".$_SERVER['HTTP_HOST']."\r\n");
				if($talk["hello"]=fgets($smtpConn)) {
					$tmp=$talk["hello"];
					while($tmp{3}=='-') { $tmp=fgets($smtpConn); $talk["hello"].="\n".$tmp; }
					}
				fputs($smtpConn,"AUTH LOGIN\r\n");
				if($talk["res"]=fgets($smtpConn)) {
					$tmp=$talk["res"];
					while($tmp{3}=='-') { $tmp=fgets($smtpConn); $talk["res"].="\n".$tmp; }
					}
				fputs($smtpConn,base64_encode($this->smtp['username'])."\r\n");
				if($talk["user"]=fgets($smtpConn)) {
					$tmp=$talk["user"];
					while($tmp{3}=='-') { $tmp=fgets($smtpConn); $talk["user"].="\n".$tmp; }
					}
				fputs($smtpConn,base64_encode($this->smtp['password'])."\r\n");
				if($talk["pass"]=fgets($smtpConn)) {
					$tmp=$talk["pass"];
					while($tmp{3}=='-') { $tmp=fgets($smtpConn); $talk["pass"].="\n".$tmp; }
					}
				fputs ($smtpConn,"MAIL FROM: <".preg_replace('/.*?<(.*)>/','$1',$this->from).">\r\n");
				if($talk["from"]=fgets($smtpConn)) {
					$tmp=$talk["from"];
					while($tmp{3}=='-') { $tmp=fgets($smtpConn); $talk["from"].="\n".$tmp; }
					}
				fputs ($smtpConn,"RCPT TO: <".preg_replace('/.*?<(.*)>/','$1',$this->to).">\r\n");
				if($talk["to"]=fgets($smtpConn)) {
					$tmp=$talk["to"];
					while($tmp{3}=='-') { $tmp=fgets($smtpConn); $talk["to"].="\n".$tmp; }
					}
				fputs($smtpConn,"DATA\r\n");
				if($talk["data"]=fgets($smtpConn)) {
					$tmp=$talk["data"];
					while($tmp{3}=='-') { $tmp=fgets($smtpConn); $talk["data"].="\n".$tmp; }
					}
				fputs($smtpConn,"To: ".$this->to."\r\nSubject:".$composition['subject']."\r\n".$composition['headers']."\r\n\r\n".$composition['message']."\r\n.\r\n");
				if($talk["send"]=fgets($smtpConn)) {
					$tmp=$talk["send"];
					while($tmp{3}=='-') { $tmp=fgets($smtpConn); $talk["send"].="\n".$tmp; }
					}
				fputs($smtpConn,"QUIT\r\n");
				if($talk["quit"]=fgets($smtpConn)) {
					$tmp=$talk["quit"];
					while($tmp{3}=='-') { $tmp=fgets($smtpConn); $talk["quit"].="\n".$tmp; }
					}
				fclose($smtpConn);
				$sent=true;
				}
			}

		if($sent==true) {
			if($this->log=="true") $this->archiveMail($this->from,$this->to,$subject,$composition['headers'],$composition['html'],$composition['plain']);
			return true;
			}
		else return false;
		}
	
	public function preview($from,$to,$subject,$message,$template="") {
		if(!$this->inited) $this->init();
		global $__template;
		if($template=="") $template=$this->template;

		$this->from=$this->fixEmailSyntax($from);
		$this->to=$this->fixEmailSyntax($to);
		$this->subject=$subject;
		$this->message=$message;

		$boundary_main="----=_NextPart_".rand(10000,99999).".".rand(100000000,999999999);
		$composition=array();
		$composition['headers']="From: ".$this->from."\r\n";
		$composition['headers'].="MIME-Version: 1.0\r\n";
		$composition['headers'].="Content-Type: multipart/alternative; \r\n\tboundary=\"".$boundary_main."\"\r\n";

		$composition['subject']=mb_convert_encoding($this->subject,"UTF-8","HTML-ENTITIES");
		$composition['subject']='=?UTF-8?B?'.base64_encode($composition['subject']).'?=';
		
		/* pure HTML -> import template */
		$composition['html']=$this->getMailTemplate($__template->getTemplateDir().'email/'.$template);

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

		/* CHUNK versions, conforming to quoted-printable standard */
		$composition['chunked-plain']=chunk_split(mb_convert_encoding($composition['plain'],"UTF-8","HTML-ENTITIES"),74,"=\r\n");
		$composition['chunked-plain']=str_replace("==\r\n3D","=\r\n=3D",$composition['plain']);
		$composition['chunked-plain']=str_replace("=3=\r\nD","=\r\n=3D",$composition['plain']);
		$composition['chunked-html']=str_replace("=","=3D",$composition['html']);
		$composition['chunked-html']=chunk_split($composition['chunked-html'],74,"=\r\n");
		$composition['chunked-html']=str_replace("==\r\n3D","=\r\n=3D",$composition['chunked-html']);
		$composition['chunked-html']=str_replace("=3=\r\nD","=\r\n=3D",$composition['chunked-html']);

		$composition['message']="\r\n";
		$composition['message'].="--".$boundary_main."\r\n";
		$composition['message'].="Content-Type: text/plain; charset=\"UTF-8\"\r\n";
		$composition['message'].="Content-Transfer-Encoding: quoted-printable\r\n";
		$composition['message'].="\r\n".$composition['chunked-plain']."\r\n\r\n";
		$composition['message'].="--".$boundary_main."\r\n";
		$composition['message'].="Content-Type: text/html; charset=\"UTF-8\"\r\n";
		$composition['message'].="Content-Transfer-Encoding: quoted-printable\r\n";
		$composition['message'].="\r\n".$composition['chunked-html'];
		$composition['message'].="\r\n\r\n--".$boundary_main."--\r\n\r\n";

		return $composition;
		}
	
	public function spamratio() {
		return false;
		}
	
	public function getMailTemplate($template='') {
		if(!$this->inited) $this->init();
		if($template=="") $template=$__template->getTemplateDir().'email/'.$this->template;
		if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/'.BASEDIR.$template)) $template="template/bettino/email/default.php";
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

	public function archiveMail($from,$to,$title,$header,$html,$plain) {
		if(!$this->inited) $this->init();
		$from=mysql_real_escape_string($from);
		$to=mysql_real_escape_string($to);
		$title=mysql_real_escape_string($title);
		$header=mysql_real_escape_string($header);
		$html=mysql_real_escape_string($html);
		$plain=mysql_real_escape_string($plain);
		$uid=$this->generateUID();
		$query="INSERT INTO ".TABLE_EMAIL_LOG." (`date`,`from`,`to`,`title`,`header`,`html`,`plain`,`uid`,`readed`) VALUES(NOW(),'".$from."','".$to."','".$title."','".$header."','".$html."','".$plain."','".$uid."','0000-00-00 00:00:00')";
		mysql_query($query);
		}

	public function generateUID() {
		$chars="QERTYUIOPASDFGHKLZXCVBNM987654321MNBVCXZLKHGFDSAPOIUTREQ123456789";
		$num="";
		for($i=1;$i<=16;$i++) {
			$start=rand(0,strlen($chars)-1);
			$num.=substr($chars,$start,1);
			}
		return $num;
		}

	private function fixEmailSyntax($email) {
		// verify the syntax of the e-mail and correct it
		$email=trim($email);
		if(strpos($email,"<")!==false) {
			//format: "name" <email@domain.tld>
			list($name,$address)=explode("<",$email);
			$name=trim($name,' "');
			$address=trim($address,' <>');
			$email='"'.$name.'" <'.$address.'>';
			}
		else {
			$email=trim($email);
			}
		return $email;
		}
	}
