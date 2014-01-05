<?
/* (c) Kalamun.org - GNU/GPL 3 */

?>
<div class="tab"><dl>
<?
$submenu_label=array("Configurazione generale","Paesi e zone","Metodi di pagamento","Coupons","Spedizione","Notifiche");
$submenu_url=array("shop.php","shop-countries.php","shop-payments.php","shop-coupons.php","shop-ships.php","shop-notifications.php");
foreach($submenu_label as $i=>$label) {
	echo '<dt>';
	echo '<a href="'.$submenu_url[$i].'"';
	if($submenu_url[$i]==basename($_SERVER['PHP_SELF'])) { echo ' class="sel"'; }
	echo '>'.$label.'</a>';
	echo '</dt>';
	}
?>
</dl></div>

