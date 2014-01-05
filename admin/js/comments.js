/* (c) 2010 Kalamun.org GPL3 */

function deleteComment(idcomm,ADMINDIR) {
	var aj=new kAjax();
	aj.onSuccess(function(html) { if(html=="") removeComment(idcomm); else alert(html); });
	aj.send('post',ADMINDIR+'inc/ajax/commentsDelete.php','idcomm='+escape(idcomm)+'');
	}
function removeComment(idcomm) {
	if(document.getElementById('comment'+idcomm)) {
		var div=document.getElementById('comment'+idcomm);
		div.parentNode.removeChild(div);
		}
	}
function approveComment(idcomm,ADMINDIR) {
	var aj=new kAjax();
	aj.onSuccess(function(html) { if(html=="s"||html=="n") updateCommentApprove(idcomm,html); else alert(html); });
	aj.send('post',ADMINDIR+'inc/ajax/commentsApprove.php','idcomm='+escape(idcomm)+'');
	}
function updateCommentApprove(idcomm,status) {
	if(document.getElementById('commentApprove'+idcomm)) {
		document.getElementById('commentApprove'+idcomm).style.display=(status=='n'?'inline':'none');
		document.getElementById('commentHide'+idcomm).style.display=(status=='s'?'inline':'none');
		}	
	if(document.getElementById('comment'+idcomm)) {
		var div=document.getElementById('comment'+idcomm);
		div.className=(status=='n'?'disapproved':'approved');
		}	
	}