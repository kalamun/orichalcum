<?
/* (c) 2011 Kalamun.org - GPL3 */
/* MAIL GATEWAY needed to send e-mail using "/inc/emails.lib.php" */


/*$_POST['to']='info@kalamun.org';
$_POST['from']='info@kalamun.org';
$_POST['subject']='Prova mailgateway';
$_POST['message']='sto provando il gateway';
*/
/*$log="Start!\n";
foreach($_POST as $ka=>$v) {
	$log.=$ka.'='.$v."\n";
	}
file_put_contents('log.txt',$log);
*/
if(!isset($_POST['sid'])) die();
session_id($_POST['sid']);
session_start();
if(!isset($_SESSION['iduser'])) die();

/*$log.="session inited\n";
file_put_contents('log.txt',$log);*/

if(!isset($_POST['to'])
	||!isset($_POST['subject'])
	||!isset($_POST['message'])
	) die();

require_once("../../inc/tplshortcuts.lib.php");
kInitBettino('../../');

/*$log.="bettino inited\n";
file_put_contents('log.txt',$log);*/


if(!isset($_POST['from'])||trim($_POST['from']," <>@"=="")) $_POST['from']=ADMIN_NAME.' <'.ADMIN_MAIL.'>';
if(!isset($_POST['template'])) $_POST['template']="";

kSendEmail($_POST['from'],$_POST['to'],$_POST['subject'],$_POST['message'],$_POST['template']);

/*$log.="email sended\n";
file_put_contents('log.txt',$log);*/

?>