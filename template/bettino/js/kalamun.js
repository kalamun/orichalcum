/* (c) Kalamun.org - GNU/GPL 3 */

var kBrowser={
	IE:!!(window.attachEvent&&!window.opera),
	IE4:navigator.userAgent.indexOf('MSIE 4')>-1,
	IE5:navigator.userAgent.indexOf('MSIE 5')>-1,
	IE6:navigator.userAgent.indexOf('MSIE 6')>-1,
	IE7:navigator.userAgent.indexOf('MSIE 7')>-1,
	OP:!!window.opera,
	SF:navigator.userAgent.indexOf('Safari')>-1,
	FF:navigator.userAgent.indexOf('Gecko')>-1&&navigator.userAgent.indexOf('Safari')==-1
	};

kAddEvent=function(obj,event,func,where) {
	if(!where) var where="after";
	if(typeof obj[event]!='function') obj[event]=func;
	else {
		var oldfunc=obj[event];
		if(where=="before") obj[event]=function() { func(); if(oldfunc) oldfunc(); };
		else obj[event]=function() { if(oldfunc) oldfunc(); func(); };
		}
	}
kGetPosition=function(obj) {
	var pos=Array();
	pos['left']=0;
	pos['top']=0;
	if(obj) {
		while(obj.offsetParent) {
			pos['left']+=obj.offsetLeft;
			pos['top']+=obj.offsetTop;
			obj=obj.offsetParent;
			}
		pos['left']+=obj.offsetLeft;
		pos['top']+=obj.offsetTop;
		}
	return {x:pos['left'],y:pos['top']};
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
		else if(document.body.scrollHeight>document.body.offsetHeight) ww=document.body.scrollWidth; //all but IE Mac
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
			ajaxObj.setRequestHeader("connection","close");
			ajaxObj.onreadystatechange=onStateChange;
			ajaxObj.send(vars);
			}
		delete ajaxObj;
		}	
	}


function easeIn(value,totalSteps,actualStep,pwr) { 
	totalSteps=Math.max(totalSteps,actualStep,1);
	var step=Math.pow(((1/totalSteps)*actualStep),pwr)*(value);
	return Math.ceil(step);
	}
function easeOut(value,totalSteps,actualStep,pwr) { 
	totalSteps=Math.max(totalSteps,actualStep,1);
	var step=value-(Math.pow(((1/totalSteps)*(totalSteps-actualStep)),pwr)*(value));
	return Math.ceil(step);
	}
function easeInOut(value,totalSteps,actualStep,pwr) { 
	totalSteps=Math.max(totalSteps,actualStep,1);
	var p1=Math.ceil(totalSteps/2);
	var p2=totalSteps-p1;
	var p1a=Math.min(actualStep,p1);
	var p2a=actualStep-p1a;
	var step=Math.pow(((1/p1)*p1a),pwr)*(value/2);
	if(p2a>0) step+=value/2-(Math.pow(((1/p2)*(p2-p2a)),pwr)*(value/2));
	return Math.ceil(step);
	}
