<?php 
/* 2013 (c) Roberto Kalamun Pasini - GPLv3 */

define("PAGE_NAME","Members:Edit member");
require_once("../inc/head.inc.php");
require_once("./members.lib.php");
$kaMembers=new kaMembers();

require_once('../newsletter/newsletter.lib.php');
$kaNewsletter=new kaNewsletter();

require_once('../private/private.lib.php');
$kaPrivate=new kaPrivate();


/* AZIONI */
if(isset($_POST['update'])&&isset($_POST['m_username'])&&$_POST['m_username']!=""&&isset($_POST['m_name'])&&isset($_GET['idmember'])) {
	$log="";
	if(!$kaMembers->update($_GET['idmember'],$_POST['m_name'],$_POST['m_email'],$_POST['m_username'],$_POST['m_password'],$_POST['m_affiliation'],$_POST['m_expiration'],$_POST['m_status'])) $log="Problemi durante la modifica dell'utente";
	elseif($kaUsers->canIUse('newsletter')) {
		$lists=array();
		if(isset($_POST['idlista'])) {
			foreach($_POST['idlista'] as $idlista=>$true) { $lists[]=$idlista; }
			}
		if(!$kaNewsletter->subscribe($_GET['idmember'],$lists)) $log="Members:Error while updating newsletter subscriptions";
		}
	
	if($log=="") {
		echo '<div id="MsgSuccess">Utente modificato con successo</div>';
		$kaLog->add("UPD",'Modificato l\'utente <em>'.$_POST['m_name'].'</em> - '.$_POST['m_username'].' (<em>ID: '.$_GET['idmember'].'</em>)');
		}
	else {
		$kaLog->add("ERR",'Errore nella modifica dell\'utente <em>'.$_POST['m_name'].'</em> - '.$_POST['m_username'].' (<em>ID: '.$_GET['idmember'].'</em>)');
		echo '<div id="MsgAlert">'.$log.'</div>';
		}
	}
elseif(isset($_GET['disable'])) {
	$log="";
	$m=$kaMembers->getUserById($_GET['disable']);
	if($m['idmember']>0) {
		if(!$kaMembers->update($m['idmember'],$m['name'],$m['email'],$m['username'],$m['password'],$m['affiliation'],$m['expiration'],"sus")) $log="Problemi durante l'eliminazione dell'utente";
		}
	else $log="L'utente non esiste";
	
	if($log=="") {
		echo '<div id="MsgSuccess">Utente disabilitato con successo</div>';
		$kaLog->add("UPD",'Disabilitato l\'utente <em>'.$m['name'].'</em> - '.$m['username'].' (<em>ID: '.$m['idmember'].'</em>)');
		}
	else {
		$kaLog->add("ERR",'Errore nella disabilitazione dell\'utente <em>'.$m['name'].'</em> - '.$m['username'].' (<em>ID: '.$m['idmember'].'</em>)');
		echo '<div id="MsgAlert">'.$log.'</div>';
		}
	}
elseif(isset($_GET['enable'])) {
	$log="";
	$m=$kaMembers->getUserById($_GET['enable']);
	if($m['idmember']>0) {
		if(!$kaMembers->update($m['idmember'],$m['name'],$m['email'],$m['username'],$m['password'],$m['affiliation'],$m['expiration'],"act")) $log="Problemi durante l'eliminazione dell'utente";
		}
	else $log="L'utente non esiste";
	
	if($log=="") {
		echo '<div id="MsgSuccess">Utente abilitato con successo</div>';
		$kaLog->add("UPD",'Disabilitato l\'utente <em>'.$m['name'].'</em> - '.$m['username'].' (<em>ID: '.$m['idmember'].'</em>)');
		}
	else {
		$kaLog->add("ERR",'Errore nell\'abilitazione dell\'utente <em>'.$m['name'].'</em> - '.$m['username'].' (<em>ID: '.$m['idmember'].'</em>)');
		echo '<div id="MsgAlert">'.$log.'</div>';
		}
	}
/* FINE AZIONI */


/* CONTROLLO FORM */
?>

<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
<br />
<?php 

if(isset($_GET['idmember'])) {
	$u=$kaMembers->getUserById($_GET['idmember']);
	?>
	<div class="subset">
		</div>

	<script type="text/javascript">
		function checkForm() {
			f=document.getElementById('theForm');
			if(f.m_name.value.length==0) { alert("Devi specificare un nome"); return false; }
			else if(f.m_username.value.length==0) { alert("Devi specificare uno username"); return false; }
			for(var i=0;f.getElementsByTagName('INPUT')[i];i++) f.getElementsByTagName('INPUT')[i].disabled=false;
			return true;
			}

		var timer=null;
		var markUsernamefield=function(success) {
			if(success=="true") document.getElementById('usernameExists').style.display="inline";
			else document.getElementById('usernameExists').style.display="none";
			}
		function checkUsername(field) {
			var target=document.getElementById('m_username')
			//controllo maiuscole
			if(target.value==target.value.replace(/[A-Z]+/g,"")) document.getElementById('maiuscole').style.display='none';
			else document.getElementById('maiuscole').style.display='block';
			if(typeof(ajaxTimer)!=='undefined') clearTimeout(ajaxTimer);
			t=setTimeout("b3_ajaxSend('post','ajax/checkUsername.php','username="+escape(field.value)+"',markUsernamefield);",500);
			}
		function title2user() {
			var titleField=document.getElementById('m_name');
			var urlField=document.getElementById('m_username');
			if(!urlField.getAttribute("completed")&&titleField.value!="") urlField.value=titleField.value.replace(/[^\w]+/g,"-").toLowerCase();
			}
		function titleBlur() {
			var titleField=document.getElementById('m_name');
			var urlField=document.getElementById('m_username');
			if(urlField.value!="") urlField.setAttribute("completed","true");
			checkUsername(urlField);
			}
		function showActions(td) {
			for(var i=0;td.getElementsByTagName('DIV')[i];i++) {
				td.getElementsByTagName('DIV')[i].style.visibility='visible';
				}
			}
		function hideActions(td) {
			for(var i=0;td.getElementsByTagName('DIV')[i];i++) {
				td.getElementsByTagName('DIV')[i].style.visibility='hidden';
				}
			}
		</script>

	<form id="theForm" action="?idmember=<?= $u['idmember']; ?>" method="post" onsubmit="return checkForm();">
	
	<div class="subset">
		<fieldset class="box"><legend><?= $kaTranslate->translate('Members:Last login'); ?></legend><?= preg_replace("/(\d{4}).(\d{2}).(\d{2}) (\d{2}).(\d{2}).(\d{2})/","$3-$2-$1 $4:$5",$u['lastlogin']); ?></fieldset>
		</div>
	
	<div class="topset">
	<table>
	<tr><td><label for="m_name"><?= $kaTranslate->translate('Members:Name'); ?></label></td><td><?= b3_create_input("m_name","text","",$u['name'],"300px",150,'onkeyup="title2user()" onblur="titleBlur()"'); ?></td></tr>
	<tr><td><label for="m_email"><?= $kaTranslate->translate('Members:E-mail'); ?></label></td><td><?= b3_create_input("m_email","text","",$u['email'],"300px",250); ?></td></tr>
	<tr><td><label for="m_username"><?= $kaTranslate->translate('Members:Username'); ?></label></td><td><div class="title"><?= b3_create_input("m_username","text","",$u['username'],"400px",64,'onkeyup="checkUsername(this)"'); ?> <span id="usernameExists" style="display:none;">Questo username esiste gi&agrave;!</span></div></td></tr>
	<tr><td><label for="m_password"><?= $kaTranslate->translate('Members:Password'); ?></label></td><td><div class="title"><?= b3_create_input("m_password","text","",$u['password'],"400px",64); ?></div></td></tr>
	<tr><td><label for="m_affiliation"><?= $kaTranslate->translate('Members:Affiliation'); ?></label></td><td><?= b3_create_input("m_affiliation","text","",$u['affiliation'],"100px",30); ?></td></tr>
	<tr><td><label for="m_expiration"><?= $kaTranslate->translate('Members:Expiration'); ?></label></td><td><?= b3_create_input("m_expiration","text","",(trim($u['expiration'],"0-")==""?"":$u['expiration']),"100px",30); ?>  <span class="small"><?= $kaTranslate->translate('Members:Leave empty for no expiration'); ?></span></td></tr>
	<tr><td><label for="m_status"><?= $kaTranslate->translate('Members:Status'); ?></label></td><td><?php 
		$options=array($kaTranslate->translate('Members:Active'),$kaTranslate->translate('Members:Suspended'));
		$values=array("act","sus");
		echo b3_create_select("m_status","",$options,$values,$u['status']);
		?></td></tr>
	</table>
	<br />

	<div class="box closed"><h2 onclick="kBoxSwapOpening(this.parentNode);"><?= $kaTranslate->translate('Members:Meta-data'); ?></h2>
	<div id="divMetadata"></div>
	<script type="text/javascript">kaMetadataReload('<?= TABLE_MEMBERS; ?>',<?= $u['idmember']; ?>);</script>
	<a href="javascript:kOpenIPopUp(ADMINDIR+'inc/ajax/metadataNew.php','t=<?= TABLE_MEMBERS; ?>&id=<?= $u['idmember']; ?>','600px','400px')" class="smallbutton"><?= $kaTranslate->translate('Members:Add Meta-data'); ?></a>
	</div>

	<div class="box <?= count($u['imgallery'])==0?'closed':'opened'; ?>"><h2 onclick="kBoxSwapOpening(this.parentNode);"><?= $kaTranslate->translate('Members:Photo gallery'); ?></h2>
	<iframe src="<?php echo ADMINDIR; ?>inc/imgallery.inc.php?refid=imgallery&mediatable=<?= TABLE_MEMBERS; ?>&mediaid=<?= $u['idmember']; ?>" class="imgframe" id="imgallery"></iframe>
	</div>

	<div class="box <?= count($u['docgallery'])==0?'closed':'opened'; ?>"><h2 onclick="kBoxSwapOpening(this.parentNode);"><?= $kaTranslate->translate('Members:Document gallery'); ?></h2>
	<iframe src="<?php echo ADMINDIR; ?>inc/docgallery.inc.php?refid=docgallery&mediatable=<?= TABLE_MEMBERS; ?>&mediaid=<?= $u['idmember']; ?>" class="docsframe" id="docgallery"></iframe>
	</div>

	<?php  if($kaUsers->canIUse('newsletter')) { ?>
		<div class="box <?= count($u['docgallery'])==0?'closed':'opened'; ?>"><h2 onclick="kBoxSwapOpening(this.parentNode);"><?= $kaTranslate->translate('Members:Subscribed newsletter lists'); ?></h2>
		<table><tr>
			<?php 
			$languages=$kaAdminMenu->getLanguages();
			foreach($languages as $lang) { ?>
				<td style="padding-right:50px;">
				<h3><?= $lang['lingua']; ?></h3>
				<?php 
				$lists=$kaNewsletter->getNewslettersList(array("ll"=>$lang['ll']));
				foreach($lists as $list) {
					echo b3_create_input("idlista[".$list['idlista']."]","checkbox",$list['lista'],"1","","",(strpos($u['newsletter_lists'],",".$list['idlista'].",")!==false?'checked':'')).' <small>('.$list['subscribers_number'].')</small><br />';
					}
				?>
				</td>
				<?php  } ?>
			</tr></table>
		</div>
		<?php  } ?>

	<?php  if($kaUsers->canIUse('private')) { ?>
		<div class="box <?= count($u['docgallery'])==0?'closed':'opened'; ?>"><h2 onclick="kBoxSwapOpening(this.parentNode);"><?= $kaTranslate->translate('Members:Private Area'); ?></h2>
		<?= $kaTranslate->translate('Members:This user has access to the following directories'); ?>:<br />

		<?php 
		function printDir($path) {
			global $kaPrivate,$u;
			$dirs=$kaPrivate->getDirContent($path);
			if($dirs) {
				foreach($dirs as $k=>$dir) {
					if(is_numeric($k)&&isset($dir['dirname'])&&isset($dir['permissions']['permissions'])&&(
						$dir['permissions']['permissions']=='public'||
						$dir['permissions']['permissions']=='members'||
						($dir['permissions']['permissions']=='restricted'&&isset($dir['permissions']['members'][$u['idmember']]))
						)) {
						echo '<ul>';
						echo $dir['dirname'];
						printDir($dir['dirname']);
						echo '</ul>';
						}
					}
				}
			}
		printDir("");
		?>
		</div>
		<?php  } ?>

	<br />

	</div>
	<div class="submit"><input type="submit" name="update" value="<?= $kaTranslate->translate('UI:Save'); ?>" class="button"> <input type="button" value="<?= $kaTranslate->translate('UI:Cancel'); ?>" onclick="window.location='?'" class="button" /></div>
	</form>
	<?php 
	}

else {
	/*************************************************************/
	/* LIST OF MEMBERS                                           */
	/*************************************************************/
	
	/* filters */
	if(!isset($_GET['skName'])) $_GET['skName']=""; //filter by name
	if(!isset($_GET['skEmail'])) $_GET['skEmail']=""; //filter by email
	if(!isset($_GET['skNewsletter'])) $_GET['skNewsletter']=""; //subscribed to newsletter
	if(!isset($_GET['skShop'])) $_GET['skShop']=""; //is a customer
	if(!isset($_GET['skAffiliation'])) $_GET['skAffiliation']=""; //filter by affiliation
	if(!isset($_GET['skExpired'])) $_GET['skExpired']=""; //if true show only expired, false only not expired
	if(!isset($_GET['skStatus'])) $_GET['skStatus']=""; //filter by status
	if(!isset($_GET['skMetadata'])) $_GET['skMetadata']=array(""); //filter by custom fields
	if(!isset($_GET['skMetadataOperators'])) $_GET['skMetadataOperators']=array("="); //operators for custom fields
	if(!isset($_GET['skMetadataValues'])) $_GET['skMetadataValues']=array(""); //values of custom fields
	$conditions="";
	
	if($_GET['skName']!="") $conditions.=" `name` LIKE '%".ksql_real_escape_string($_GET['skName'])."%' AND ";
	if($_GET['skEmail']!="") $conditions.=" `email` LIKE '%".ksql_real_escape_string($_GET['skEmail'])."%' AND ";
	if($_GET['skAffiliation']!="") $conditions.=" `affiliation` LIKE '%".ksql_real_escape_string($_GET['skAffiliation'])."%' AND ";

	if($kaUsers->canIUse('newsletter')&&$_GET['skNewsletter']!="") {
		//if true show only subscribers, else only not subscribed to any list
		$lists=$kaNewsletter->getNewslettersList();
		$subconditions=" (";
		foreach($lists as $list) {
			if($_GET['skNewsletter']=="true") $subconditions.=" `newsletter_lists` LIKE '%,".$list['idlista'].",%' OR ";
			else $subconditions.=" `newsletter_lists` NOT LIKE '%,".$list['idlista'].",%' AND ";
			}
		if($subconditions!=" (") $subconditions=substr($subconditions,0,-4).') AND ';
		else $subconditions=$_GET['skNewsletter']=="true"?" `idmember`=0 AND ":""; //if no lists exists yet, show only if false
		$conditions.=$subconditions;
		}

	//if metadata filter is set, retrive ids of users that match the requested metadata
	if($_GET['skMetadata'][0]!="") {
		$subconditions=" (";
		
		//if requested equal to empty, search for members without that metadata
		if($_GET['skMetadataOperators'][0]=="="&&$_GET['skMetadataValues'][0]=="") {
			foreach($kaMetadata->getList(array("table"=>TABLE_MEMBERS,"param"=>$_GET['skMetadata'][0],"return_records"=>true)) as $md) {
				$subconditions.="`idmember`<>'".$md['id']."' AND ";
				}
			if($subconditions!=" (") $subconditions.="`idmember`<>0) AND ";
			else $subconditions="";
			}
		else {
			$value=$_GET['skMetadataValues'][0];
			if($_GET['skMetadataOperators'][0]=='LIKE') $value='%'.$value.'%';
			foreach($kaMetadata->getList(array("table"=>TABLE_MEMBERS,"param"=>$_GET['skMetadata'][0],"value_operator"=>$_GET['skMetadataOperators'][0],"value"=>$value,"return_records"=>true)) as $md) {
				$subconditions.="`idmember`='".$md['id']."' OR ";
				}
			if($subconditions!=" (") $subconditions.="`idmember`=0) AND ";
			else $subconditions="";
			}
		$conditions.=$subconditions;
		}
	
	$conditions=substr($conditions,0,-4);

	$numberOfUsers=$kaMembers->countUsers(array("conditions"=>$conditions));

	?>
	<p><?= $numberOfUsers.' '.$kaTranslate->translate('Members:members in current selection'); ?></p>
	<div class="box closed">
		<h2 onclick="kBoxSwapOpening(this.parentNode);"><?= $kaTranslate->translate('Members:Filters'); ?></h2>
		<form action="" method="get">
			<?= b3_create_input("skName","text",$kaTranslate->translate('Members:Name').' ',b3_lmthize($_GET['skName'],"input")); ?><br />
			<?= b3_create_input("skEmail","text",$kaTranslate->translate('Members:E-mail').' ',b3_lmthize($_GET['skEmail'],"input")); ?><br />
			<?= b3_create_input("skAffiliation","text",$kaTranslate->translate('Members:Affiliation').' ',b3_lmthize($_GET['skAffiliation'],"input")); ?><br />

			<?php 
			if($kaUsers->canIUse('newsletter')) {
				$values=array("","true","false");
				$labels=array("all","subscribers of one or more lists","not subscribers");
				foreach($labels as $k=>$v) {
					$labels[$k]=$kaTranslate->translate('Members:'.$v);
					}
				echo b3_create_select("skNewsletter",$kaTranslate->translate('Members:Newsletters').' ',$labels,$values,$_GET['skNewsletter']); ?><br />
				<?php  } ?>
				
	
			<?php 
			$values=array("");
			foreach($kaMetadata->getParams(TABLE_MEMBERS) as $param) {
				$values[]=$param['param'];
				}
			//show metadata selection only if there are at least one metadata defined
			if(count($values)>1) {
				$labels=$values;
				echo b3_create_select("skMetadata[]",$kaTranslate->translate('Members:Meta-data'),$labels,$values,$_GET['skMetadata'][0]);

				$values=array("=","<>","LIKE",">","<");
				$labels=array("equal to","different from","contains","greater than","lower than");
				foreach($labels as $k=>$v) {
					$labels[$k]=$kaTranslate->translate('Members:'.$v);
					}
				echo b3_create_select("skMetadataOperators[]","",$labels,$values,$_GET['skMetadataOperators'][0]);
				
				echo b3_create_input("skMetadataValues[]","text","",$_GET['skMetadataValues'][0]);
				?><br />
				<?php  } ?>
			
			<input type="submit" value="<?= $kaTranslate->translate('Members:apply filters'); ?>" class="smallbutton" />
			</form>
		</div>
		<br />
	
	
	<script type="text/javascript">
		function selectMenuRef(useUser) {
			document.getElementById('useUser').value=useUser;
			k_openIframeWindow(ADMINDIR+"inc/selectMenuRef.inc.php","450px","500px");
			}
		function selectElement(id,where) {
			var useUser=document.getElementById('useUser').value;
			var get="";
			if(String(window.location).indexOf("search=")>-1) {
				get=String(window.location);
				get=get.replace(/.*search=/,"");
				get="search="+get.replace(/^[[^\d]*].*/,"");
				}
			var url=String(window.location).replace(/\?.*/,"");
			window.location=url+'?useUser='+useUser+'&addtomenu='+id+','+where+'&'+get;
			}
		</script>

	<?php 
	/* if there are more than 50 members, split into different pages */
	if($numberOfUsers>50) { ?>
		<div class="box pager" style="text-align:center;">
			<?php 
			$append_var=$_SERVER['QUERY_STRING'];
			foreach($_GET as $kaey => $value) {
				if($kaey=="chg_lang"||$kaey=="delete"||$kaey=="confirm"||$kaey=="p") {
					$append_var=preg_replace("/".$kaey."=?[^&]*&?/","",$append_var);
					}
				}

			$letters="#ABCDEFGHIJKLMNOPQRSTUVWYXZ";
			if(!isset($_GET['p'])) $_GET['p']="A";
			for($i=0;isset($letters[$i]);$i++) { ?>
				<a href="?p=<?= urlencode($letters[$i]); ?>&<?= $append_var; ?>" class="<?= $_GET['p']==$letters[$i]?'selected':''; ?>"><?= $letters[$i]; ?></a>
				<?php  }
			?>
			</div>
			<br />
		<?php  } ?>
		
	<div class="topset">
		<input type="hidden" id="useUser" />
		<table class="tabella">
		<tr><th>&nbsp;</th><th>Utente</th><th>Username</th><th>Creato</th><th>Ultimo Accesso</th><th>Scadenza</th></tr><?php 		$vars=array();
		$vars['conditions']=$conditions;
		if(isset($_GET['p'])) {
			if($vars['conditions']!="") $vars['conditions'].=' AND ';
			if($_GET['p']!="#") $vars['conditions'].=" `name` LIKE '".ksql_real_escape_string($_GET['p'])."%'";
			else {
				$letters="ABCDEFGHIJKLMNOPQRSTUVWYXZ";
				for($i=0;isset($letters[$i]);$i++) {
					$vars['conditions'].="`name` NOT LIKE '".$letters[$i]."%' AND ";
					}
				$vars['conditions']=substr($vars['conditions'],0,-4);
				}
			}

		$userList=$kaMembers->getUsersList($vars);
		if($userList) {
			foreach($userList as $ka=>$v) {
				if($v['status']!="del") {
					echo '<tr>';
					echo '<td><img src="img/'.$v['status'].'.png" width="16" height="16" /></td>';
					echo '<td onmouseover="showActions(this)" onmouseout="hideActions(this)"><h2><a href="?idmember='.$v['idmember'].'">'.$v['name'].' '.($v['affiliation']!=""?'<span style="color:#888;font-size:.8em;">('.$v['affiliation'].')</span>':'').'</a></h2>';
						echo '<small class="actions"><a href="?idmember='.$v['idmember'].'">'.$kaTranslate->translate('Members:Edit').'</a> | ';
						if($v['status']=="act") echo '<a href="?disable='.$v['idmember'].'">Disabilita</a></div>';
						else echo '<a href="?enable='.$v['idmember'].'">Abilita</a></small>';
						echo '</td>';
					echo '<td class="percorso"><a href="?idmember='.$v['idmember'].'">'.$v['username'].'</a></td>';
					echo '<td class="percorso">'.str_replace("h","<br />h",$v['created_friendly']).'</td>';
					echo '<td class="percorso">'.str_replace("h","<br />h",$v['lastlogin_friendly']).'</td>';
					echo '<td class="percorso">'.str_replace("h","<br />h",$v['expiration_friendly']).'</td>';
					echo '</tr>';
					}
				}
			}
		?></table>
		</div>
		<?php 
	}

	
include_once("../inc/foot.inc.php");
