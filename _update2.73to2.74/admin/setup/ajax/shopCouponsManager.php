<?php /* (c) Kalamun.org - GNU/GPL 3 */

require_once('../../inc/main.lib.php');
$kaOrichalcum=new kaOrichalcum();
$kaOrichalcum->init( array("check-permissions"=>false, "x-frame-options"=>"") );

$kaTranslate->import('setup');

define("PAGE_NAME","Coupons Manager");
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
<head>
<title><?php echo ADMIN_NAME." - ".PAGE_NAME; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="author" content="Roberto Pasini - www.kalamun.org" />
<meta name="copyright" content="no(c)" />
<style type="text/css">
	@import "<?php echo ADMINDIR; ?>css/screen.css";
	@import "<?php echo ADMINDIR; ?>css/main.lib.css";
	@import "<?php echo ADMINDIR; ?>css/docmanager.css";
	</style>

<script type="text/javascript">var ADMINDIR='<?php echo str_replace("'","\'",ADMINDIR); ?>';</script>
<script type="text/javascript" src="<?php echo ADMINDIR; ?>js/kalamun.js"></script>
</head>

<body>

<?php 
include('../../shop/shop.lib.php');
$kaShop=new kaShop();

if(isset($_GET['markasvalid'])) {
	$kaShop->markCouponAsValid($_GET['markasvalid']);
	}
elseif(isset($_GET['markasused'])) {
	$kaShop->markCouponAsUsed($_GET['markasused']);
	}
elseif(isset($_GET['del'])) {
	$kaShop->deleteCoupon($_GET['del']);
	}
?>

<div id="imgheader">
	<h1>Coupons</h1>
	<a href="javascript:window.parent.k_closeIframeWindow();" class="closeWindow"><img src="<?= ADMINRELDIR; ?>img/closeWindow.gif" alt="Close" width="9" height="9" /></a>
	</div>

<div style="padding:30px;">
	<table class="tabella hidebuttons">
		<tr>
			<th>Codice</th>
			<th>&nbsp</th>
		</tr>
		<?php 
		foreach($kaShop->getCouponCodesList(array('idscoup'=>$_GET['idscoup'],'valid'=>$_GET['valid'])) as $m) { ?>
			<tr>
			<td><?= $m['code']; ?></td>
			<td>
				<?php  if($_GET['valid']==1) { ?><a href="?valid=<?= $_GET['valid']; ?>&idscoup=<?= $_GET['idscoup']; ?>&markasused=<?= $m['code']; ?>" class="smallbutton">Segna come utilizzato</a><?php  }
					elseif($_GET['valid']==0) { ?><a href="?valid=<?= $_GET['valid']; ?>&idscoup=<?= $_GET['idscoup']; ?>&markasvalid=<?= $m['code']; ?>" class="smallbutton">Segna come valido</a><?php  } ?>
				<a href="?valid=<?= $_GET['valid']; ?>&idscoup=<?= $_GET['idscoup']; ?>&del=<?= $m['code']; ?>" class="smallalertbutton" onclick="return confirm('Vuoi davvero cancellare questo coupon?');">Elimina</a></td>
			</tr>
			<?php  }
		?>
		</table>
	</div>

</body>
</html>
