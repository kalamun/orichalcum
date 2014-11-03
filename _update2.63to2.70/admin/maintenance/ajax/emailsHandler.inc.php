<?php /* (c) Kalamun.org - GNU/GPL 3 */

require_once('../../inc/connect.inc.php');
require_once('../../inc/kalamun.lib.php');
require_once('../../inc/sessionmanager.inc.php');
require_once('../../inc/main.lib.php');
require_once('../../inc/config.lib.php');
if(!isset($_SESSION['iduser'])) die('Operation denied');

require_once('../../members/members.lib.php');
$kaMembers=new kaMembers();

if(isset($_POST['updateEmail']) && isset($_POST['email']))
{
	$u=$kaMembers->getUserById($_POST['updateEmail']);
	if(!isset($u['idmember'])) return false;
	
	$kaMembers->updateEmail($u['idmember'],$_POST['email']);
	die('true');
}


if(isset($_POST['deleteMember']))
{
	if($kaMembers->del($_POST['deleteMember']) != false) die('true');
	die('false');
}


