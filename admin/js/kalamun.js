/* (c) Kalamun.org - GNU/GPL 3 */

var kBrowser={
	IE:!!(window.attachEvent&&!window.opera),
	IE4:navigator.userAgent.indexOf('MSIE 4')>-1,
	IE5:navigator.userAgent.indexOf('MSIE 5')>-1,
	IE6:navigator.userAgent.indexOf('MSIE 6')>-1,
	IE7:navigator.userAgent.indexOf('MSIE 7')>-1,
	IE8:navigator.userAgent.indexOf('MSIE 8')>-1,
	OP:!!window.opera,
	SF:navigator.userAgent.indexOf('Safari')>-1,
	CR:navigator.userAgent.indexOf('Chrome')>-1,
	FF:navigator.userAgent.indexOf('Gecko')>-1&&navigator.userAgent.indexOf('Safari')==-1
	};

kAddEvent=function(obj,event,func,model) {
	if(!model) model=true;
	if(obj.addEventListener) return obj.addEventListener(event,func,model);
	if(obj.attachEvent) return obj.attachEvent('on'+event,func);
	return false;
	}

kGetPosition=function(obj) {
	var pos=Array();
	pos['left']=0;
	pos['top']=0;
	if(obj) {
		while(obj.offsetParent) {
			pos['left']+=obj.offsetLeft-obj.scrollLeft;
			pos['top']+=obj.offsetTop-obj.scrollTop;
			var tmp=obj.parentNode;
			while(tmp!=obj.offsetParent) {
				pos['left']-=tmp.scrollLeft;
				pos['top']-=tmp.scrollTop;
				tmp=tmp.parentNode;
				}
			obj=obj.offsetParent;
			}
		pos['left']+=obj.offsetLeft;
		pos['top']+=obj.offsetTop;
		}
	return {x:pos['left'],y:pos['top']};
	}

kGetAllChilds=function(obj) {
	var childs=Array();
	for(var i=0;obj.childNodes[i];i++) {
		childs.pop(obj.childNodes[i]);
		if(obj.childNodes[i].childNodes.length>0) childs.concat(kGetAllChilds(obj.childNodes[i]));
		}
	return childs;
	}

kWindow=new function() {
	this.filterResults=function(win,docel,body) {
		var result=win?win:0;
		if(docel&&(!result||(result>docel))) result=docel;
		return body&&(!result||(result>body))?body:result;
		}

	// size
	this.clientWidth=function() {
		return this.filterResults(window.innerWidth?window.innerWidth:0,document.documentElement?document.documentElement.clientWidth:0,document.body?document.body.clientWidth:0);
		}
	this.clientHeight=function() {
		return this.filterResults(window.innerHeight?window.innerHeight:0,document.documentElement?document.documentElement.clientHeight:0,document.body?document.body.clientHeight:0);
		}
	this.pageWidth=function() {
		if(window.innerHeight&&window.scrollMaxY) ww=window.innerWidth+window.scrollMaxX; //FF
		else if(document.body.scrollWidth>document.body.offsetWidth) ww=document.body.scrollWidth; //all but IE Mac
		else ww=document.body.offsetWidth; //IE 6 Strict, Mozilla (not FF), Safari
		return ww;
		}
	this.pageHeight=function() {
		if(window.innerHeight&&window.scrollMaxY) yy=window.innerHeight+window.scrollMaxY; //FF
		else if(document.body.scrollHeight>document.body.offsetHeight) yy=document.body.scrollHeight; //all but IE Mac
		else yy=document.body.offsetHeight; //IE 6 Strict, Mozilla (not FF), Safari
		return yy;
		}

	// scroll
	this.scrollLeft=function() {
		return this.filterResults(window.pageXOffset?window.pageXOffset:0,document.documentElement?document.documentElement.scrollLeft:0,document.body?document.body.scrollLeft:0);
		}
	this.scrollTop=function() {
		return this.filterResults(window.pageYOffset?window.pageYOffset:0,document.documentElement?document.documentElement.scrollTop:0,document.body ? document.body.scrollTop:0);
		}

	// mouse
	this.mousePos={x:0,y:0};
	this.elementOver=null;
	}

kMouseMove=function(e) {
	var e=e||window.event;
	if(e.pageX||e.pageY) var mPos={x:e.pageX,y:e.pageY};
	else var mPos={x:e.clientX+document.body.scrollLeft-document.body.clientLeft,y:e.clientY+document.body.scrollTop-document.body.clientTop};
	kWindow.mousePos=mPos;
	kWindow.elementOver=(e.target)?e.target:e.srcElement;
	return false;
	}
kAddEvent(document,"mousemove",kMouseMove);

/* AJAX */
kAjax=function() {
	var onSuccess=function(txt) {};;
	var onFail=function(txt) {};;
	var ajaxObj=null;
	var method="get";
	var uri="";
	var vars="";

	this.send=function(vmethod,vuri,vvars) {
		method=vmethod.toLowerCase();
		uri=vuri;
		vars=vvars;
		ajaxSend();
		}
	this.onSuccess=function(func) { onSuccess=func }
	this.onFail=function(func) { onFail=func; }

	function createXMLHttpRequest() {
		var XHR=null;
		if(typeof(XMLHttpRequest)==="function"||typeof(XMLHttpRequest)==="object") XHR=new XMLHttpRequest(); //browser standard
		else if(window.ActiveXObject&&!kBrowser.IE4) { //ie4, BLOCCATO
			if(kBrowser.IE5) XHR=new ActiveXObject("Microsoft.XMLHTTP"); //ie5.x: metodo diverso
			else XHR=new ActiveXObject("Msxml2.XMLHTTP"); //ie6: metodo diverso
			}
		return XHR;
		}
	function onStateChange() {
		if(ajaxObj.readyState===4) {
			if(ajaxObj.status==200) onSuccess(ajaxObj.responseText,ajaxObj.responseXML);
			else onFail(ajaxObj.status);
			}
		}
	function ajaxSend() {
		ajaxObj=createXMLHttpRequest();
		if(method=="get") {
			uri+="?"+vars;
			ajaxObj.open(method,uri,true);
			ajaxObj.onreadystatechange=onStateChange;
			ajaxObj.send(null);
			}
		else if(method=="post") {
			ajaxObj.open(method,uri,true);
			ajaxObj.setRequestHeader("content-type","application/x-www-form-urlencoded");
			//ajaxObj.setRequestHeader("connection","close");
			ajaxObj.onreadystatechange=onStateChange;
			ajaxObj.send(vars);
			}
		delete ajaxObj;
		}	
	}


/* Metadatas */
function kaMetadataReload(t,id) {
	var c=document.getElementById('divMetadata');
	c.innerHTML='Loading...';
	var aj=new kAjax();
	aj.onSuccess(kaMetadataShow);
	aj.send('post',ADMINDIR+'inc/ajax/metadataShow.php','tabella='+t+'&id='+id);
	}
function kaMetadataSave(t,id,p,v) {
	var c=document.getElementById('divMetadata');
	c.innerHTML='Loading...';
	var aj=new kAjax();
	aj.onSuccess(function() {kaMetadataReload(t,id);kCloseIPopUp();});
	v=encodeURIComponent(v);
	p=encodeURIComponent(p);
	aj.send('post',ADMINDIR+'inc/ajax/metadataSave.php','t='+t+'&id='+id+'&p='+p+'&v='+v);
	}
var kaMetadataShow=function(html) {
	var c=document.getElementById('divMetadata');
	c.innerHTML=html;
	}

/* Comments */
function deleteComment(idcomm,ADMINDIR) {
	var aj=new kAjax;
	aj.send('post',ADMINDIR+'inc/ajax/commentsDelete.php','idcomm='+encodeURIComponent(idcomm)+'',function(html) { if(html=="") removeComment(idcomm); else alert(html); });
	}
function removeComment(idcomm) {
	if(document.getElementById('comment'+idcomm)) {
		var div=document.getElementById('comment'+idcomm);
		div.parentNode.removeChild(div);
		}
	}
function approveComment(idcomm,ADMINDIR) {
	var aj=new kAjax;
	aj.send('post',ADMINDIR+'inc/ajax/commentsApprove.php','idcomm='+encodeURIComponent(idcomm)+'',function(html) { if(html=="s"||html=="n") updateCommentApprove(idcomm,html); else alert(html); });
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