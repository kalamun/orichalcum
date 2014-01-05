<?php
/* (c) Kalamun.org - GNU/GPL 3 */

require_once('../../inc/connect.inc.php');
require_once('../../inc/kalamun.lib.php');
require_once('../../inc/sessionmanager.inc.php');
require_once('../../inc/main.lib.php');

if(!isset($_SESSION['iduser'])) die('Non hai il permesso di utilizzare questa funzione');
if(!isset($_GET['ideml'])) die('Mancano indicazioni sulla prenotazione: impossibile continuare');

require_once('../../inc/log.lib.php');
$kaEmailLog=new kaEmailLog();

define("PAGE_NAME","E-mail Reader");

echo $kaEmailLog->getEmailContent($_GET['ideml']);
?>
