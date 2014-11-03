<?php //error_reporting(0);
session_start();
if(!isset($_SESSION['iduser'])) die("You don't have permissions to access this informations");
if(!isset($_GET['idord'])) die('Error selecting order');

require_once('../../inc/connect.inc.php');
require_once('../../inc/kalamun.lib.php');
require_once('../../inc/sessionmanager.inc.php');
require_once('../../users/users.lib.php');
require_once('../../inc/main.lib.php');
$kaUsers=new kaUsers();
$kaTranslate=new kaAdminTranslate();
$kaTranslate->import('shop');

if(!$kaUsers->canIUse('shop')) die("You don't have permissions to access this informations");

include('../shop.lib.php');
$kaShop=new kaShop();

$o=$kaShop->getOrderById($_GET['idord']);
?>
<h2><?= $kaTranslate->translate('Shop:Shipment details'); ?></h2>
<table class="transactions">
<tr><td>
<?php  if($o['shipped']=='n') {
	echo $kaTranslate->translate('Shop:Not yet shipped');
	}
else { 
	echo $kaTranslate->translate('Shop:Shipped on').' ';
	echo preg_replace("/(\d{4}).(\d{2}).(\d{2}) (\d{2}).(\d{2}).(\d{2})/","$3.$2.$1 <img src=\"../img/clock10.png\" width=\"10\" height=\"10\" /> $4:$5",$o['shippedon']).'<br />';
	echo $kaTranslate->translate('Shop:Deliverer').' '.$o['deliverer']['name'].'<br />';
	echo $kaTranslate->translate('Shop:Tracking number').' '.$o['tracking_number'].'<br />';
	echo $kaTranslate->translate('Shop:Tracking URL').' <a href="'.$o['tracking_url'].'">'.$o['tracking_url'].'</a><br />';
	} ?>
</td></tr>
</table>

<?php 
if($o['shipped']=='n') { ?>
	<input type="button" class="smallbutton" value="<?= $kaTranslate->translate('Shop:Mark as shipped'); ?>" onclick="k_openIframeWindow('ajax/deliveryManager.php?idord=<?= $o['idord']; ?>','600px','400px');" />
	<?php  } 