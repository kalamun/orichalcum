var soTimer=0;
var scTimer=0;
function showReq(id) {
	var f=document.getElementById(id);
	f.style.display='block';
	f.style.overflow='hidden';
	var hh=parseInt(f.offsetHeight);
	f.style.height='10px';
	soTimer=setInterval(function() { scrollOpening(f,hh); },30);
	}
function scrollOpening(f,hh) {
	f.style.height=(parseInt(f.style.height)+10)+'px';
	if(parseInt(f.style.height)>=hh) clearInterval(soTimer);
	}

var timer=null;
var markURLfield=function(success) {
	if(success=="true") document.getElementById('dirYetExists').style.display="inline";
	else document.getElementById('dirYetExists').style.display="none";
	}
function checkURL(urlField) {
	urlField.value=urlField.value.replace(/[^\w\/\.\-À-퟿Ⰰ-퟿]+/g,"-").toLowerCase();
	if(typeof(ajaxTimer)!=='undefined') clearTimeout(ajaxTimer);
	ajaxTimer=setTimeout("b3_ajaxSend('post','ajax/checkUrl.php','url="+escape(urlField.value)+"',markURLfield);",1000);
	}
function title2url() {
	var titleField=document.getElementById('titolo');
	var urlField=document.getElementById('dir');
	if(!urlField.getAttribute("completed")&&titleField.value!="") urlField.value=titleField.value.replace(/[^\w\/\.\-À-퟿Ⰰ-퟿]+/g,"-").toLowerCase();
	checkURL(urlField);
	}
function titleBlur() {
	var titleField=document.getElementById('titolo');
	var urlField=document.getElementById('dir');
	if(urlField.value!="") urlField.setAttribute("completed","true");
	checkURL(urlField);
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
