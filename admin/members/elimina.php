<?
/* 2010 (c) Roberto Kalamun Pasini - GPLv3 */

define("PAGE_NAME","Elimina dall'Anagrafica");
require_once("../inc/head.inc.php");
require_once("./members.lib.php");
$kaMembers=new kaMembers();

/* AZIONI */
if(isset($_GET['idmember'])) {
	$log="";
	$m=$kaMembers->getUserById($_GET['idmember']);
	if($m['idmember']>0) {
		if(!$kaMembers->del($m['idmember'])) $log="Problemi durante l'eliminazione dell'utente";
		}
	else $log="L'utente non esiste";
	
	if($log=="") {
		echo '<div id="MsgSuccess">Utente eliminato con successo</div>';
		$kaLog->add("DEL",'Eliminato l\'utente <em>'.$m['name'].'</em> - '.$m['username'].' (<em>ID: '.$m['idmember'].'</em>)');
		}
	else {
		$kaLog->add("ERR",'Errore nell\'eliminazione dell\'utente <em>'.$m['name'].'</em> - '.$m['username'].' (<em>ID: '.$m['idmember'].'</em>)');
		echo '<div id="MsgAlert">'.$log.'</div>';
		}
	}
elseif(isset($_GET['enable'])) {
	$log="";
	$m=$kaMembers->getUserById($_GET['enable']);
	if($m['idmember']>0) {
		if(!$kaMembers->update($m['idmember'],$m['name'],$m['email'],$m['username'],$m['password'],$m['affiliation'])) $log="Problemi durante l'eliminazione dell'utente";
		}
	else $log="L'utente non esiste";
	
	if($log=="") {
		echo '<div id="MsgSuccess">Utente risorto con successo</div>';
		$kaLog->add("UPD",'Risorto l\'utente <em>'.$m['name'].'</em> - '.$m['username'].' (<em>ID: '.$m['idmember'].'</em>)');
		}
	else {
		$kaLog->add("ERR",'Errore nella risurrezione dell\'utente <em>'.$m['name'].'</em> - '.$m['username'].' (<em>ID: '.$m['idmember'].'</em>)');
		echo '<div id="MsgAlert">'.$log.'</div>';
		}
	}
/* FINE AZIONI */



/*************************************************************/
/* LIST OF MEMBERS                                           */
/*************************************************************/
/* if there are more than 50 members, split into different pages */
$numberOfUsers=$kaMembers->countUsers();

?>

<h1><? echo PAGE_NAME; ?></h1>
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

	<? if($numberOfUsers>50) { ?>
		<div class="box pager" style="text-align:center;">
			<?
			$letters="#ABCDEFGHIJKLMNOPQRSTUWYXZ";
			if(!isset($_GET['p'])) $_GET['p']="A";
			for($i=0;isset($letters[$i]);$i++) { ?>
				<a href="?p=<?= urlencode($letters[$i]); ?>" class="<?= $_GET['p']==$letters[$i]?'selected':''; ?>"><?= $letters[$i]; ?></a>
				<? }
			?>
			</div>
			<br />
		<? } ?>
	
	<div class="topset">
		<input type="hidden" id="useUser" />
		<table class="tabella">
		<tr><th>&nbsp;</th><th>Utente</th><th>Username</th><th>Creato</th><th>Ultimo Accesso</th><th>Scadenza</th></tr><?php
		$vars=array();
		if(isset($_GET['p'])) {
			if($_GET['p']!="#") $vars['conditions']="`name` LIKE '".mysql_real_escape_string($_GET['p'])."%'";
			else {
				$vars['conditions']="";
				$letters="ABCDEFGHIJKLMNOPQRSTUWYXZ";
				for($i=0;isset($letters[$i]);$i++) {
					$vars['conditions'].="`name` NOT LIKE '".$letters[$i]."%' AND ";
					}
				$vars['conditions']=substr($vars['conditions'],0,-4);
				}
			}
		foreach($kaMembers->getUsersList($vars) as $ka=>$v) {
			if($v['status']!="del") {
				echo '<tr>';
				echo '<td><img src="img/'.$v['status'].'.png" width="16" height="16" /></td>';
				echo '<td onmouseover="showActions(this)" onmouseout="hideActions(this)"><h2><a href="?idmember='.$v['idmember'].(isset($_GET['p'])?'&p='.$_GET['p']:'').'">'.$v['name'].' '.($v['affiliation']!=""?'<span style="color:#888;font-size:.8em;">('.$v['affiliation'].')</span>':'').'</a></h2>';
					echo '<div class="small" style="visibility:hidden;"><a href="?idmember='.$v['idmember'].(isset($_GET['p'])?'&p='.$_GET['p']:'').'">Elimina</a></div>';
					echo '</td>';
				echo '<td class="percorso"><a href="?idmember='.$v['idmember'].(isset($_GET['p'])?'&p='.$_GET['p']:'').'">'.$v['username'].'</a></td>';
				echo '<td class="percorso">'.str_replace("h","<br />h",$v['created_friendly']).'</td>';
				echo '<td class="percorso">'.str_replace("h","<br />h",$v['lastlogin_friendly']).'</td>';
				echo '<td class="percorso">'.str_replace("h","<br />h",$v['expiration_friendly']).'</td>';
				echo '</tr>';
				}
			}
		?></table>

		<br /><br />
		<h2>Utenti gi&agrave; eliminati</h2>
		<table class="tabella">
		<tr><th>&nbsp;</th><th>Utente</th><th>Username</th><th>Creato</th><th>Ultimo Accesso</th><th>Scadenza</th></tr><?php
		foreach($kaMembers->getUsersList() as $ka=>$v) {
			if($v['status']=="del") {
				echo '<tr>';
				echo '<td><img src="img/'.$v['status'].'.png" width="16" height="16" /></td>';
				echo '<td onmouseover="showActions(this)" onmouseout="hideActions(this)"><h2><a href="?idmember='.$v['idmember'].'">'.$v['name'].' '.($v['affiliation']!=""?'<span style="color:#888;font-size:.8em;">('.$v['affiliation'].')</span>':'').'</a></h2>';
					echo '<div class="small" style="visibility:hidden;"><a href="?enable='.$v['idmember'].'">Resuscita</a></div>';
					echo '</td>';
				echo '<td class="percorso"><a href="?idmember='.$v['idmember'].'">'.$v['username'].'</a></td>';
				echo '<td class="percorso">'.str_replace("h","<br />h",$v['created_friendly']).'</td>';
				echo '<td class="percorso">'.str_replace("h","<br />h",$v['lastlogin_friendly']).'</td>';
				echo '<td class="percorso">'.str_replace("h","<br />h",$v['expiration_friendly']).'</td>';
				echo '</tr>';
				}
			}
		?></table>
		</div>
		<?

	
include_once("../inc/foot.inc.php");
?>
