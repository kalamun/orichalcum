<?
/* (c) Kalamun.org - GNU/GPL 3 */


define("PAGE_NAME","Maintenance:Check if the e-mail address of registered users exists");
include_once("../inc/head.inc.php");

/* AZIONI */
/* FINE AZIONI */

?>
<h1><? echo $kaTranslate->translate(PAGE_NAME); ?></h1>
<br />

<style>
input {
	width:250px;
}
</style>

<?
if(isset($_GET['check']))
{
	?>
	<table class="tabella">
	<?
	
	//array of free e-mail providers
	$freeProviders=array(
		"gmail.com",
		"hotmail.com",
		"libero.it",
		"yahoo.com",
		"yahoo.it",
		"yahoo.es",
		"yahoo.fr",
		"yahoo.co.uk",
		"yahoo.co.in",
		"yahoo.de",
		"virgilio.it",
		"alice.it",
		"mail.ru",
		"yandex.ru",
		"virgin.net",
		"gmx.net",
		"aol.com",
		"katamail.com",
		"tin.it",
		"iol.it",
		"verizon.net",
		"inwind.it",
		"sfr.fr",
		"web.de",
		"gmx.de",
		"tiscali.it",
		"windowslive.com",
		"orange.fr",
		"msn.com",
		"free.fr",
		"email.it"
		);

	//already checked providers: false= invalid domain, true= valid domain
	$checkedProviders=array();
	
	$counter=0;

	require_once('../members/members.lib.php');
	$kaMembers=new kaMembers();
	foreach($kaMembers->getUsersList(array("groupby"=>"email", "conditions"=>"`status`='act'")) as $m)
	{
		$error=""; // error description
		$errorcode=""; // error "gravity"

		//check for empty emails
		$m['email']=trim($m['email']);
		if($m['email']=="") $error="Empty e-mail";
		
		//check the format of email
		if($error=="")
		{
			if(!preg_match("/^[A-Z0-9\._%\+-]+@[A-Z0-9\.-]+\.[A-Z]{2,4}$/i",$m['email']))
			{
				$error="Invalid email format";
				$errorcode=0;
			}
		}
		
		//skip if it is a free provider
		$domain=substr($m['email'], strpos($m['email'],"@")+1);
		if($error=="")
		{
			if(array_search($domain,$freeProviders)!==false) continue;
		}
		
		//check if domain exists
		if($error=="")
		{
			if(isset($checkedProviders[$domain])) {
				if($checkedProviders[$domain]==false)
				{
					$error="Domain does not exist";
					$errorcode=5;
				} else continue;
			} else {
				if(!checkdnsrr($domain,'A'))
				{
					$error="Domain does not exist";
					$errorcode=5;
					$checkedProviders[$domain]=false;
				} else $checkedProviders[$domain]=true;
			}
		}
		
		//display the address only if it contains errors
		if($error=="") continue;
		?>
		<tr id="entry<?= $m['idmember']; ?>">
			<td><?= $m['name']; ?></td>
			<td><input type="text" id="email<?= $m['idmember']; ?>" value="<?= addslashes($m['email']); ?>"> <a href="javascript:updateEmail(<?= $m['idmember']; ?>)" class="smallbutton"><?= $kaTranslate->translate("UI:Save"); ?></a></td>
			<td id="error<?= $m['idmember']; ?>"><?= $error; ?></td>
			<td><a href="javascript:deleteMember(<?= $m['idmember']; ?>);" class="smallalertbutton" onclick="return confirm('<?= addslashes($kaTranslate->translate('UI:Are you sure?')); ?>');"><?= $kaTranslate->translate("UI:Delete"); ?></a></td>
		</tr>
		<?
		
		$counter++;
	}
	?>
	</table>
	<br>
	<h2><?= $counter.' '.$kaTranslate->translate('Maintenance:invalid email addresses'); ?></h2>
	
	
	<script type="text/javascript">
		function updateEmail(id)
		{
			var input=document.getElementById('email'+id);
			if(!input) return false;
			
			input.setAttribute("disabled","disabled");

			var newemail=input.value;
			
			var aj=new kAjax();
			aj.onSuccess(function(html,xml) {
				input.removeAttribute("disabled");
				if(input.value.match(/^[\w\d\._%\+-]+@[\w\d\.-]+\.\w{2,4}$/i)!=false) document.getElementById('error'+id).innerHTML='OK';
				});
			aj.send("post",'ajax/emailsHandler.inc.php','&updateEmail='+id+'&email='+encodeURIComponent(newemail));
		}
		
		function deleteMember(id)
		{
			var aj=new kAjax();
			aj.onSuccess(function(html,xml) {
				if(html=="true")
				{
					document.getElementById("entry"+id).parentNode.removeChild(document.getElementById("entry"+id),true);
				}
				});
			aj.send("post",'ajax/emailsHandler.inc.php','&deleteMember='+id);
		}
	</script>
	
<? } ?>

<br>
<a href="?check" class="smallbutton"><?= $kaTranslate->translate('Maintenance:Check'); ?></a><br />


<?
include_once("../inc/foot.inc.php");
?>
