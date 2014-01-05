/* (c) Kalamun.org - GNU/GPL 3 */

kRichTextOn=function(id,rich,keys) {
	if(!id||id=="") return false;
	if(!keys) var keys="strong,em,u,|,left,center,right,|,a,mailto,|,ul,ol,table,|,size{h1;h2;h3;h4;p;clean},hr,sourcecode,|,cite,code,abbr,acronym,img,doc,media";
	
	var kRichTextarea=new kTextarea;
	kRichTextarea.id(id);
	kRichTextarea.createKeys(keys);
	if(rich) kRichTextarea.richEditOn();
	return kRichTextarea;
	}

kTextarea=function() {
	var designMode="text";
	var id="";
	var textarea=null;
	var iframe=null;
	var container=null;
	var keys=Array();
	var keysContainer=null;
	var keysStatus=null;
	var swapKeys=null;
	var txtareaCopyPaste=null;
	var keysTimer=null;
	var copyPasteTmp=null;
	var copyPasteRange=null;
	var copyPasteScrollTop=0;
	
	var tagReference=Array();
	tagReference['|']=Array('separator.png','|','','separator');
	tagReference['strong']=Array('b.png','Bold','setHTMLTag','','<strong>','</strong>');
	tagReference['em']=Array('i.png','Italic','setHTMLTag','','<em>','</em>');
	tagReference['u']=Array('u.png','Underscore','setHTMLTag','','<u>','</u>');
	tagReference['left']=Array('left.png','Align to Left','alignLeft','','','');
	tagReference['center']=Array('center.png','Align to Center','alignCenter','','','');
	tagReference['right']=Array('right.png','Align to Right','alignRight','','','');
	tagReference['size']=Array('size.png','Text Size');
	tagReference['h1']=Array('h1.png','Main Title','setHTMLTag','','<h1>','</h1>');
	tagReference['h2']=Array('h2.png','Title of Second Level','setHTMLTag','','<h2>','</h2>');
	tagReference['h3']=Array('h3.png','Title of Third Level','setHTMLTag','','<h3>','</h3>');
	tagReference['h4']=Array('h4.png','Title of Fourth Level','setHTMLTag','','<h4>','</h4>');
	tagReference['p']=Array('p.png','Paragraph','setHTMLTag','','<p>','</p>');
	tagReference['clean']=Array('clean.png','Remove formatting','cleanFormatting','','','');
	tagReference['hr']=Array('hr.png','Horizontal Rule','setHTMLTag','','<hr>','</hr>');
	tagReference['ul']=Array('ul.png','Ordered List','setList','','<ul>','</ul>');
	tagReference['ol']=Array('ol.png','Unordered List','setList','','<ol>','</ol>');
	tagReference['a']=Array('a.png','Link','setLink','','','');
	tagReference['mailto']=Array('mailto.png','E-mail Link','setLink','','mailto:','');
	tagReference['table']=Array('table.png','Table','setHTMLTag','','<table border="0" style="width:100%"><tr><td>&nbsp;','</td><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr></table>');
	tagReference['img']=Array('img.png','Image','insertImg','','');
	tagReference['doc']=Array('doc.png','Attachment','insertDoc','','');
	tagReference['media']=Array('media.png','Media','insertMedia','','');
	tagReference['sourcecode']=Array('code.png','Source Code','insertSourceCode','','');

	this.id=function(idval) {
		id=idval;
		textarea=document.getElementById(id);
		this.textarea=textarea;
		iframe=document.getElementById('iframe_'+id);
		this.iframe=iframe;
		container=textarea.parentNode;
		this.container=container;
		};

	this.createKeys=function(keysval) {
		keys=keysval.split(',');
		for(var i in keys) {
			var subkeys=null;
			if(keys[i].indexOf("{")>=0) {
				var subkeys=keys[i].substr(keys[i].indexOf("{")+1);
				subkeys=subkeys.replace("}","");
				keys[i]=keys[i].substr(0,keys[i].indexOf("{"));
				}
			if(tagReference[keys[i]]) {
				var key=tagReference[keys[i]];
				addKey(key[0],key[1],key[2],key[3],key[4],key[5]);
				}
			if(subkeys) {
				subkeys=subkeys.split(';');
				for(var j in subkeys) {
					if(tagReference[subkeys[j]]) {
						var key=tagReference[subkeys[j]];
						addSubkey(key[0],key[1],key[2],key[3],key[4],key[5]);
						}
					}
				}
			}
		};

	var action=function() {
		switch(this.getAttribute('kaction')) {
			case "setHTMLTag":
				setHTMLTag(this.getAttribute('kbefore'),this.getAttribute('kafter'));
				break;
			case "alignLeft":
				align('left');
				break;
			case "alignCenter":
				align('center');
				break;
			case "alignRight":
				align('right');
				break;
			case "setLink":
				setLink(this.getAttribute('kbefore'),this.getAttribute('kafter'));
				break;
			case "setList":
				setList(this.getAttribute('kbefore'),this.getAttribute('kafter'));
				break;
			case "changeHeight":
				changeHeight();
				break;
			case "insertImg":
				insertImg();
				break;
			case "insertDoc":
				insertDoc();
				break;
			case "insertMedia":
				insertMedia();
				break;
			case "insertSourceCode":
				insertSourceCode();
				break;
			case "cleanFormatting":
				cleanFormatting();
				break;
			case "swapDesignMode-rich":
				swapDesignMode("rich");
				break;
			case "swapDesignMode-text":
				swapDesignMode("text");
				break;
			default:
				return false;
			}
		};

	this.richEditOn=function() {
		kAddEvent(textarea.form,"onsubmit",swapDesignMode,"before");
		iframe=document.createElement('IFRAME');
		iframe.id='iframe_'+id;
		iframe.style.width=textarea.style.width;
		iframe.style.height=textarea.style.height;
		iframe.frameBorder='0';

		var onload=function() {
			iframe.onfocus=null;
			iframe.onload=null;
			iframe.onmouseover=iframeOnMouseOver;
			iframe.onmouseout=iframeOnMouseOut;
			textarea.onmouseover=iframeOnMouseOver;
			textarea.onmouseout=iframeOnMouseOut;
			iframe.contentWindow.document.designMode="on";
			iframe.contentWindow.document.open();
			iframe.contentWindow.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"><html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /></head><body></body></html>');
			iframe.contentWindow.document.close();
			var csslink=iframe.contentWindow.document.createElement('link');
			csslink.setAttribute('rel','stylesheet');
			csslink.setAttribute('type','text/css');
			csslink.setAttribute('href',ADMINDIR+'css/richeditor.css');
			iframe.contentWindow.document.getElementsByTagName('head')[0].appendChild(csslink);
			try { iframe.contentDocument.execCommand("styleWithCSS",0,false); }
			catch(e) {
				try { iframe.contentDocument.execCommand("useCSS",0,true); }
				catch (e) {
					try { iframe.contentDocument.execCommand('styleWithCSS', false, false); }
					catch (e) { }
					}
				}
			try{ iframe.contentDocument.execCommand('enableInlineTableEditing',null,true); }
			catch(e){ }
			try{ iframe.contentDocument.execCommand('enableObjectResizing',null,true); }
			catch(e){ }
			if(iframe.contentWindow.addEventListener) {
				iframe.contentWindow.document.body.addEventListener('paste',iframeOnPaste,false);
				iframe.contentWindow.addEventListener('focus',kOnFocus,true);
				iframe.contentWindow.addEventListener('keydown',kOnKeyDown,true);
				iframe.contentWindow.addEventListener('keyup',kOnKeyUp,true);
				}
			swapDesignMode("rich");
			}
		if(kBrowser.IE) iframe.onfocus=onload;
		else iframe.onload=onload;
		document.getElementById(id).parentNode.insertBefore(iframe,document.getElementById(id));
		iframe.focus();
		}
	var iframeOnMouseOver=function() {
		clearKeysTimer();
		var h=keysContainer.getElementsByTagName('DIV')[0].offsetHeight+4;
		keysContainer.style.height=h+'px';
		keysContainer.style.top='-'+(h+1)+'px';
		var h=keysStatus.getElementsByTagName('DIV')[0].offsetHeight;
		keysStatus.style.height=h+'px';
		}
	var iframeOnMouseOut=function() {
		keysTimer=setTimeout(hideKeys,200);
		}
	var clearKeysTimer=function() {
		if(keysTimer) clearTimeout(keysTimer);
		}
	var hideKeys=function() {
		keysContainer.style.height='0px';
		keysContainer.style.top='0px';
		keysStatus.style.height='0px';
		}


	this.swapDesignMode=function(mode) {
		if(!mode) mode=(designMode=='text'?'rich':'text');
		if(mode!="text"&&mode!="rich") mode="text";
		swapDesignMode(mode);
		}
	function swapDesignMode(mode) {
		if(!mode) mode=(designMode=='text'?'rich':'text');
		if(mode!="text"&&mode!="rich") mode="text";
		if(mode==designMode) {
			}
		else if(mode=="text") {
			textarea.style.display='inline';
			if(iframe) iframe.style.display='none';
			var cnt=iframe.contentWindow.document.body.cloneNode(true); // copio
			cnt=kIframeToTextarea(cnt); // pulisco
			textarea.value=cnt; // incollo
			designMode="text";
			}
		else if(mode=="rich"&&iframe) {
			textarea.style.display='none';
			iframe.style.display='inline';
			var cnt=textarea.value; // copio
			cnt=kTextareaToIframe(cnt); // pulisco
			iframe.contentWindow.document.body.innerHTML=cnt; // incollo
			designMode="rich";
			}
		swapDesignModeKeys(mode);
		}
	function swapDesignModeKeys(mode) {
		for(var i in swapKeys.getElementsByTagName('img')) {
			swapKeys.getElementsByTagName('img')[i].className='';
			if(swapKeys.getElementsByTagName('img')[i].id==id+"_swapKey-"+mode) swapKeys.getElementsByTagName('img')[i].className='sel';
			}
		}
	
	function addKey(imgname,alt,onclick,CSSclass,kbefore,kafter) {
		if(!keysContainer) {
			keysContainer=document.createElement('DIV');
			keysContainer.className='mainbkg';
			keysContainer.style.position='absolute';
			keysContainer.style.height='0px';
			keysContainer.style.top='0px';
			keysContainer.onmouseover=clearKeysTimer;
			keysContainer.onmouseout=iframeOnMouseOut;
			document.getElementById(id).parentNode.insertBefore(keysContainer,document.getElementById(id));
			keysStatus=document.createElement('DIV');
			keysStatus.className='mainstatus';
			keysStatus.style.position='absolute';
			keysStatus.style.height='0px';
			keysStatus.onmouseover=clearKeysTimer;
			keysStatus.onmouseout=iframeOnMouseOut;
			document.getElementById(id).parentNode.appendChild(keysStatus);

			var img=document.createElement('DIV');
			img.className="zoom";
			img.setAttribute('kaction',"changeHeight");
			img.onmousedown=action;
			keysStatus.appendChild(img);
			
			swapKeys=document.createElement('DIV');
			swapKeys.className='swapKeys';
			keysStatus.appendChild(swapKeys);
			var img=new Image();
			img.src=ADMINDIR+'/img/mode-rich.png';
			img.title="WYSIWYG Editor";
			img.className="swapKey";
			img.id=id+"_swapKey-rich";
			img.setAttribute('kaction',"swapDesignMode-rich");
			img.onclick=action;
			swapKeys.appendChild(img);
			var img=new Image();
			img.src=ADMINDIR+'/img/mode-html.png';
			img.title="HTML Editor";
			img.className="swapKey";
			img.id=id+"_swapKey-text";
			img.setAttribute('kaction',"swapDesignMode-text");
			img.onclick=action;
			swapKeys.appendChild(img);
			swapDesignModeKeys(designMode);
			};

		var img=new Image();
		img.src=ADMINDIR+'img/'+imgname;
		img.alt=alt;
		img.title=alt;
		if(CSSclass) img.className=CSSclass;
		img.setAttribute('kbefore',kbefore?kbefore:'');
		img.setAttribute('kafter',kafter?kafter:'');
		if(onclick&&onclick!="") {
			img.setAttribute('kaction',onclick);
			img.onclick=action;
			}
		var btn=document.createElement('DIV');
		btn.className='btn';
		if(CSSclass) btn.className+=' '+CSSclass;
		btn.appendChild(img);

		keysContainer.appendChild(btn);
		return img;
		};
	this.addKey=addKey;

	function addSubkey(imgname,alt,onclick,CSSclass,kbefore,kafter) {
		var lastDiv=keysContainer.childNodes[keysContainer.childNodes.length-1];
		var submenu=lastDiv.childNodes[lastDiv.childNodes.length-1]
		if(!submenu||submenu.tagName!='DIV'||submenu.className!='subbtn') {
			submenu=document.createElement('DIV');
			submenu.className='subbtn';
			keysContainer.childNodes[keysContainer.childNodes.length-1].appendChild(submenu);
			}
		var img=new Image();
		img.src=ADMINDIR+'img/'+imgname;
		img.alt=alt;
		img.title=alt;
		if(CSSclass!="") img.className=CSSclass;
		img.setAttribute('kbefore',kbefore?kbefore:'');
		img.setAttribute('kafter',kafter?kafter:'');
		if(onclick&&onclick!="") {
			img.setAttribute('kaction',onclick);
			img.onclick=action;
			}
		var btn=document.createElement('DIV');
		btn.className='btn';
		if(CSSclass) btn.className+=' '+CSSclass;
		btn.appendChild(img);
		submenu.appendChild(btn);
		submenu.style.width=(submenu.childNodes.length*(submenu.childNodes[0].offsetWidth+2))+'px';
		return img;
		};
	this.addSubkey=addSubkey;

	function getNodeContents(node) {
		if(node==null) return null;
        if(node.nodeType==1) return node.innerHTML; // element
		else if(node.nodeType==3) return node.nodeValue; // textnode
	    }			
	function setHTMLTag(addBefore,addAfter) {
		if(designMode=="rich") {
			if(addBefore=='<strong>') formatHTML('bold',false);
			else if(addBefore=='<em>') formatHTML('italic',false);
			else if(addBefore=='<u>') formatHTML('underline',false);
			else if(addBefore=='<p>') formatHTML('formatblock','<p>');
			else if(addBefore=='<h1>') formatHTML('formatblock','<h1>');
			else if(addBefore=='<h2>') formatHTML('formatblock','<h2>');
			else if(addBefore=='<h3>') formatHTML('formatblock','<h3>');
			else if(addBefore=='<h4>') formatHTML('formatblock','<h4>');
			else insertHTML(addBefore+addAfter);
			}
		else {
			formatSource(addBefore,addAfter);
			}
		}
	function align(set) {
		if(designMode=="rich") {
			var range=null;
			var contents=null;
			var ancestor=null;
			if(iframe.contentWindow.getSelection) { // FF
				range=iframe.contentWindow.getSelection().getRangeAt(0);
				contents=range.cloneContents();
				ancestor=range.commonAncestorContainer;
				var al="";
				var fl="";
				if(contents.childNodes.length==1&&contents.childNodes[0].nodeType==1&&contents.childNodes[0].tagName=='IMG') {
					if(set=="center") { fl=""; al=set; }
					else { fl=set; al=""; }
					}
				else {
					range.selectNodeContents(ancestor);
					fl="";
					al=set;
					}
				if(ancestor.tagName=="DIV") {
					ancestor.style.textAlign=al;
					ancestor.style.cssFloat=fl;
					if(fl!="") ancestor.className=ancestor.className.replace(/align(left|right|center)/,"align"+set.toLowerCase());
					else ancestor.className=ancestor.className.replace(/align(left|right|center)/,"");
					}
				else {
					var tag=iframe.contentWindow.document.createElement('DIV');
					tag.style.textAlign=al;
					tag.style.cssFloat=fl;
					if(fl!="") tag.className="align"+set.toLowerCase();
					range.surroundContents(tag);
					range.selectNodeContents(range.startContainer.childNodes[0]);
					}
				}    
			else if(iframe.contentWindow.document.selection) {
				range=iframe.contentWindow.document.selection.createRange();
				range.expand("sentence");
				range.pasteHTML('<div style="width:100%; text-align:'+set.toLowerCase()+'">'+range.htmlText+'</div>')
				}
			iframe.contentWindow.focus();
			}
		else {
			setHTMLTag('<div style="text-align:'+(set.toLowerCase())+';">','</div>');
			var cnt=textarea.value;
			cnt=cnt.replace(/"float:([^;]*);? ?text-align:([^;]*);?"/gi,'"text-align:$2;"');
			cnt=cnt.replace(/<div style="text-align:([^;]*);?"><img src="([^"]*)" id="([^"]*)"([*\/]*)\/?><\/div>/gi,'<div style="float:$1;" class="$2"><img src="$3" id="$4"$5 /></div>');
			cnt=cnt.replace(/float: ?center;?/,'text-align:center;');
			textarea.value=cnt;
			}
		}
	function changeHeight() {
		mouseStartingPositionY=kWindow.mousePos.y;
		textareaStartingHeight=parseInt(textarea.style.height,10);
		document.addEventListener("mousemove",resizeEditor);
		document.addEventListener("mouseup",stopResizing);
		}
	function resizeEditor() {
		pixels=mouseStartingPositionY-kWindow.mousePos.y;
		finalSize=textareaStartingHeight-Number(pixels);
		if(finalSize>50) {
			textarea.style.height=finalSize+"px";
			iframe.style.height=finalSize+"px";
			}
		}
	function stopResizing() {
		document.removeEventListener("mousemove",resizeEditor);
		document.removeEventListener("mouseup",stopResizing);
		var aj=new kAjax;
		aj.send("post",ADMINDIR+'users/ajax/setParam.php','&param='+escape(textarea.name)+'_height&family=editor&value='+parseInt(textarea.style.height,10));
		}
	function insertHTML(html) {
		var tag=document.createElement('DIV');
		tag.innerHTML=html;
		for(var i=0;tag.childNodes[i];i++) {
			if(document.all) {
				range=iframe.contentDocument.selection.createRange();
				range.pasteHTML(tag.innerHTML);
				range.setEndPoint("StartToEnd",range);
				}
			else {
				range=iframe.contentWindow.getSelection().getRangeAt(0);
				var tmp=tag.childNodes[i].cloneNode(true);
				range.insertNode(tmp);
				range.setStartAfter(tmp);
				}
			}
		tag=null;
		}
	function formatHTML(what,opt) {
		try { iframe.contentDocument.execCommand("styleWithCSS",0,false); }
		catch(e) {
			try { iframe.contentDocument.execCommand("useCSS",0,true); }
			catch (e) {
				try { iframe.contentDocument.execCommand('styleWithCSS', false, false); }
				catch (e) { }
				}
			}
		iframe.contentWindow.document.execCommand(what,false,opt);
		iframe.contentWindow.focus();
		}
	function formatSource(addBefore,addAfter) {
		var target=null;
		if(designMode=="text") target=textarea;
		if(!kBrowser.IE) {
			target.focus();
			var start_selection=Math.min(target.selectionStart,target.selectionEnd),
				end_selection=Math.max(target.selectionStart,target.selectionEnd);
			var startText=(target.value).substring(0,start_selection);
			var selectedText=(target.value).substring(start_selection,end_selection);
			var endText=(target.value).substring(end_selection,target.textLength);
			var scrollTop=target.scrollTop;
			target.value=startText+addBefore+selectedText+addAfter+endText;
			target.selectionStart=start_selection;
			target.selectionEnd=start_selection+addBefore.length+selectedText.length+addAfter.length;
			target.scrollTop=scrollTop;
			}
		else {
			var selectedText=document.selection.createRange().text;
			target.focus(target.caretPos);
			target.caretPos=document.selection.createRange();
			target.caretPos.text=addBefore+selectedText+addAfter;
			target.caretPos.moveStart("character",(addBefore.length+selectedText.length+addAfter.length)*-1);
			target.caretPos.select();
			}
		target.focus();
		}

	function setLink(addBefore,addAfter,url,title,target,className,nofollow) {
		if(!addBefore||addBefore=="") var addBefore="http://";
		if(!url) var url=null;
		if(!tag) var tag=null;

		if(designMode=="rich") {
			if(!tag) {
				var range=null;
				var contents=null;
				if(iframe.contentWindow.getSelection) { // FF
					range=iframe.contentWindow.getSelection().getRangeAt(0);
					for(ancestor=range.commonAncestorContainer;ancestor;ancestor=ancestor.parentNode) {
						if(ancestor.tagName=='A'&&(!ancestor.getAttribute('id')||!ancestor.getAttribute('id').match(/doc\d*/))) {
							tag=true;
							var vhref=ancestor.href?ancestor.href:addBefore;
							var vtarget=ancestor.target?ancestor.target:"";
							var vnofollow=ancestor.rel=='nofollow'?"true":"false";
							var vclass=ancestor.className?ancestor.className:"";
							var vtitle=ancestor.title?ancestor.title:"";
							break;
							}
						}
					}
				}
			if(!tag) {
				tag=false;
				var vhref=addBefore;
				var vtarget="";
				var vnofollow=false;
				var vclass="";
				var vtitle="";
				}
			if(url==null) {
				var dirname=window.location.href.replace(/\\/g,'/').replace(/\/[^\/]*$/, '');
				if(vhref.substr(0,dirname.length)==dirname) vhref=vhref.substr(dirname.length+1);
				console.log(ADMINDIR+'inc/linkManager.inc.php?refid='+id+'&addBefore='+escape(addBefore)+'&href='+escape(vhref)+'&target='+escape(vtarget)+'&nofollow='+escape(vnofollow)+'&class='+escape(vclass)+'&title='+escape(vtitle));
				k_openIframeWindow(ADMINDIR+'inc/linkManager.inc.php?refid='+id+'&href='+escape(vhref)+'&target='+escape(vtarget)+'&nofollow='+escape(vnofollow)+'&class='+escape(vclass)+'&title='+escape(vtitle),'800px','500px');
				}
			else {
				formatHTML('createlink',url);
				if(iframe.contentWindow.getSelection) { // FF
					var range=iframe.contentWindow.getSelection().getRangeAt(0);
					var rangeBkup={
						startElm:range.startContainer,
						startOffset:range.startOffset,
						endElm:range.endContainer,
						endOffset:range.endOffset
						};
					ancestor=range.commonAncestorContainer;
					range.selectNodeContents(ancestor);
					if(ancestor.nodeType==1) {
						if(ancestor.tagName=='A'&&ancestor.href.replace(/\/$/,'')==url.replace(/\/$/,'')) {
							if(title&&title!="") ancestor.setAttribute("title",title);
							if(target&&target!="") ancestor.setAttribute("target",target);
							if(className&&className!="") ancestor.className=className;
							if(nofollow&&nofollow==true) ancestor.setAttribute("rel",'nofollow');
							}
						for(var i=0;ancestor.getElementsByTagName('A')[i];i++) {
							if(ancestor.getElementsByTagName('A')[i].href.replace(/\/$/,'')==url.replace(/\/$/,'')) {
								if(title&&title!="") ancestor.getElementsByTagName('A')[i].setAttribute("title",title);
								if(target&&target!="") ancestor.getElementsByTagName('A')[i].setAttribute("target",target);
								if(className&&className!="") ancestor.getElementsByTagName('A')[i].className=className;
								if(nofollow&&nofollow==true) ancestor.getElementsByTagName('A')[i].setAttribute("rel",'nofollow');
								}
							}
						}
					range.setStart(rangeBkup.startElm,rangeBkup.startOffset);
					range.setEnd(rangeBkup.endElm,rangeBkup.endOffset);
					}
				}
			}
		}
	this.setLink=function(addBefore,addAfter,url,title,target,className,nofollow) {
		setLink(addBefore,addAfter,url,title,target,className,nofollow);
		}
	function removeLink() {
		if(iframe.contentWindow.getSelection) { // FF
			range=iframe.contentWindow.getSelection().getRangeAt(0);
			for(ancestor=range.commonAncestorContainer;ancestor;ancestor=ancestor.parentNode) {
				if(ancestor.tagName=='A'&&(!ancestor.getAttribute('id')||!ancestor.getAttribute('id').match(/doc\d*/))) {
					range.selectNodeContents(ancestor);
					var rangeBkup={
						startElm:null,
						endElm:null
						};

					while(ancestor.firstChild) {
						if(!rangeBkup.startElm) rangeBkup.startElm=ancestor.firstChild;
						rangeBkup.endElm=ancestor.firstChild;
						ancestor.parentNode.insertBefore(ancestor.firstChild,ancestor);
						}
					ancestor.parentNode.removeChild(ancestor);
					iframe.contentWindow.getSelection().removeAllRanges();
					var range=iframe.contentWindow.document.createRange();
					range.setStartBefore(rangeBkup.startElm);
					range.setEndAfter(rangeBkup.endElm);
					iframe.contentWindow.getSelection().addRange(range);
					break;
					}
				}
			}
		}
	this.removeLink=function() { removeLink(); }
	function updateLink(addBefore,addAfter,url,title,target,className,nofollow) {
		removeLink();
		setLink(addBefore,addAfter,url,title,target,className,nofollow);
		}
	this.updateLink=function(addBefore,addAfter,url,title,target,className,nofollow) { updateLink(addBefore,addAfter,url,title,target,className,nofollow); }
	function setList(addBefore,addAfter) {
		if(designMode=="rich") {
			if(addBefore=='<ul>') formatHTML('insertunorderedlist',false);
			else if(addBefore=='<ol>') formatHTML('insertorderedlist',false);
			}
		else {
			var list=addBefore+"\n";
			var listNumber=prompt('Quanti elementi vuoi inserire nella lista?','1');
			if(listNumber) {
				for(var iii=1;listNumber>=iii;iii++) { list+="<li>"+prompt(iii+'° punto della lista:','')+"</li>\n"; }
				list+=""+addAfter+"\n";
				setHTMLTag('',list);
				}
			}
		}
	function insertSourceCode(code) {
		if(!code) {
			k_openIframeWindow(ADMINDIR+'inc/sourceCodeManager.inc.php?refid='+id,'800px','500px');
			}
		else {
			code=code.replace(/\[Bettino:NewLine\]/g,"\n");
			setHTMLTag("",code);
			}
		}
	this.insertSourceCode=function(code) {
		insertSourceCode(code);
		}
	function insertImg(idimg,size,url) {
		if(!idimg) {
			if(designMode=="rich") {
				var range=null;
				var contents=null;
				if(iframe.contentWindow.getSelection) { // FF
					range=iframe.contentWindow.getSelection().getRangeAt(0);
					contents=range.cloneContents();
					if(contents.childNodes.length==1&&contents.childNodes[0].nodeType==1&&contents.childNodes[0].tagName=='IMG') {
						if(contents.childNodes[0].getAttribute('id').match(/img\d*/)) var idimg=contents.childNodes[0].getAttribute('id').substring(3);
						else if(contents.childNodes[0].getAttribute('id').match(/thumb\d*/)) var idimg=contents.childNodes[0].getAttribute('id').substring(5);
						}
					}
				}
			}
		if(!idimg) { var idimg=""; }
		if(!url) {
			var mediatable=container.getAttribute('mediatable');
			var mediaid=container.getAttribute('mediaid');
			k_openIframeWindow(ADMINDIR+'inc/imgManager.inc.php?refid='+id+'&mediatable='+mediatable+'&mediaid='+mediaid+'&idimg='+idimg,'800px','500px');
			}
		else {
			setHTMLTag('','<img src="'+url+'" id="'+size+idimg+'" />');
			}
		}
	this.insertImg=function(idimg,size,url) {
		insertImg(idimg,size,url);
		}
	function insertDoc(iddoc,alt,url) {
		if(!iddoc) {
			if(designMode=="rich") {
				var range=null;
				var contents=null;
				if(iframe.contentWindow.getSelection) { // FF
					range=iframe.contentWindow.getSelection().getRangeAt(0);
					for(ancestor=range.commonAncestorContainer;ancestor;ancestor=ancestor.parentNode) {
						if(ancestor.tagName=='A'&&ancestor.getAttribute('id').match(/doc\d*/)) var iddoc=ancestor.getAttribute('id').substring(3);
						}
					}
				}
			}
		if(!iddoc) { var iddoc=""; }
		if(!url) {
			var mediatable=container.getAttribute('mediatable');
			var mediaid=container.getAttribute('mediaid');
			k_openIframeWindow(ADMINDIR+'inc/docManager.inc.php?refid='+id+'&mediatable='+mediatable+'&mediaid='+mediaid+'&iddoc='+iddoc,'800px','500px');
			}
		else {
			setHTMLTag('<a href="'+url+'" id="doc'+iddoc+'">',alt+'</a>');
			}
		}
	this.insertDoc=function(iddoc,alt,url) {
		insertDoc(iddoc,alt,url);
		}
	function insertMedia(idmedia,alt,url,width,height) {
		if(!idmedia) {
			if(designMode=="rich") {
				var range=null;
				var contents=null;
				if(iframe.contentWindow.getSelection) { // FF
					range=iframe.contentWindow.getSelection().getRangeAt(0);
					contents=range.cloneContents();
					if(contents.childNodes.length==1&&contents.childNodes[0].nodeType==1&&contents.childNodes[0].tagName=='IMG') {
						if(contents.childNodes[0].getAttribute('id').match(/media\d*/)) var idmedia=contents.childNodes[0].getAttribute('id').substring(5);
						}
					}
				}
			}
		if(!idmedia) { var idmedia=""; }
		if(!url) {
			var mediatable=container.getAttribute('mediatable');
			var mediaid=container.getAttribute('mediaid');
			k_openIframeWindow(ADMINDIR+'inc/mediaManager.inc.php?refid='+id+'&mediatable='+mediatable+'&mediaid='+mediaid+'&idmedia='+idmedia,'800px','500px');
			}
		else {
			setHTMLTag('','<img src="'+url+'" id="media'+idmedia+'" '+(width>0?'width="'+width+'" ':'')+(height>0?'height="'+height+'" ':'')+'/>');
			}
		}
	this.insertMedia=function(idmedia,alt,url,width,height) {
		insertMedia(idmedia,alt,url,width,height);
		}

	function kRemoveTag(node,tag) {
		for(var i=0;node.childNodes[i];i++) {
			if(node.childNodes[i].tagName==tag) {
				var nextNode=node.childNodes[i].nextSibling;
				for(var j=0;node.childNodes[i].childNodes[j];j++) {
					node.insertBefore(node.childNodes[i].childNodes[j].cloneNode(true),nextNode);
					}
				node.insertBefore(document.createElement('BR'),node.childNodes[i].nextNode);
				node.insertBefore(document.createElement('BR'),node.childNodes[i].nextNode);
				node.removeChild(node.childNodes[i]);
				}
			if(node.childNodes[i].hasChildNodes()) kRemoveTag(node.childNodes[i],tag);
			}
		}

	function iframeOnPaste(e) {
		var ibody=iframe.contentWindow.document.ibody;
		copyPasteTmp=document.createDocumentFragment();
		copyPasteScrollTop=ibody.scrollTop;
		var range=iframe.contentWindow.getSelection().getRangeAt(0);
		copyPasteRange={
			startElm:range.startContainer,
			startOffset:range.startOffset,
			endElm:range.endContainer,
			endOffset:range.endOffset
			};
		while(ibody.firstChild) {
			copyPasteTmp.appendChild(ibody.firstChild);
			}
		if(e&&e.clipboardData&&e.clipboardData.getData) { // Webkit
			if(/text\/html/.test(e.clipboardData.types)) iframe.contentWindow.document.ibody.innerHTML=e.clipboardData.getData('text/html');
			else if(/text\/plain/.test(e.clipboardData.types)) iframe.contentWindow.document.ibody.innerHTML=e.clipboardData.getData('text/plain');
			else iframe.contentWindow.document.ibody.innerHTML="";
			waitforpastedata();
			if(e.preventDefault) {
				e.stopPropagation();
				e.preventDefault();
				}
			return false;
			}
		else { // FF, IE...
			waitforpastedata();
			return true;
			}
		}
	function waitforpastedata() {
		var ibody=iframe.contentWindow.document.body;
		if(ibody.childNodes&&ibody.childNodes.length>0) processpaste();
		else setTimeout(waitforpastedata,20);
		}
	function processpaste() {
		var ibody=iframe.contentWindow.document.body;
		//cleaning pasted data
		clearNode(ibody);
		ibody.innerHTML=ibody.innerHTML.replace(/<(\/)b>/i,"<$1strong>");
		//end cleaning

		var pastedData=ibody.innerHTML;

		ibody.innerHTML="";
		while(copyPasteTmp.childNodes.length>0) {
			ibody.appendChild(copyPasteTmp.childNodes[0]);
			}
		iframe.contentWindow.getSelection().removeAllRanges();
		var range=iframe.contentWindow.document.createRange();
		range.setStart(copyPasteRange.startElm,copyPasteRange.startOffset);
		range.setEnd(copyPasteRange.endElm,copyPasteRange.endOffset);
		iframe.contentWindow.getSelection().addRange(range);
		range.deleteContents();
		insertHTML(pastedData);
		ibody.scrollTop=copyPasteScrollTop;
		copyPasteRange=null;
		pastedData=null;
		}
	function clearNode(node) {
		for(var i=0;node.childNodes[i];i++) {
			if(node.childNodes[i].nodeType>3||(node.childNodes[i].nodeType==1&&node.childNodes[i].tagName.match(/^(TITLE|STYLE|META|W:.*|M:.*|XML)$/i))) {
				//remove node and contents
				node.removeChild(node.childNodes[i]);
				i--;
				}
			else if(node.childNodes[i].nodeType==1&&node.childNodes[i].tagName.match(/^(FONT|SPAN)$/i)) {
				//remove node but contents
				for(var j=0;node.childNodes[i].childNodes[j];j++) {
					node.insertBefore(node.childNodes[i].childNodes[j],node.childNodes[i].nextSibling);
					}
				node.removeChild(node.childNodes[i]);
				i--;
				}
			else if(node.childNodes[i].nodeType==1&&node.childNodes[i].childNodes.length>0) clearNode(node.childNodes[i]);
			}
		}
	function cleanFormatting() {
		var range=iframe.contentWindow.getSelection().getRangeAt(0);
		ancestor=range.commonAncestorContainer;
		
		/* remove nodes */
		var tagsToBeRemoved=Array("FONT","SPAN","DIV","U","ADDRESS"); //remove node, leave contents
		var tagsToBeDeleted=Array("STYLE","XML","SCRIPT","META"); //remove node and contents
		
		var nodesToBeRemoved=Array(); 
		for(var c=0;tagsToBeRemoved[c];c++) {
			if(ancestor.getElementsByTagName) {
				for(var i=0;ancestor.getElementsByTagName(tagsToBeRemoved[c])[i];i++) {
					nodesToBeRemoved[nodesToBeRemoved.length]=ancestor.getElementsByTagName(tagsToBeRemoved[c])[i];
					}
				}
			}
		for(var i=0;nodesToBeRemoved[i];i++) {
			if(nodesToBeRemoved[i]) {
				var toBeRemoved=nodesToBeRemoved[i];
				while(toBeRemoved.lastChild) {
					toBeRemoved.parentNode.insertBefore(toBeRemoved.lastChild,toBeRemoved.nextSibling);
					}
				toBeRemoved.parentNode.removeChild(toBeRemoved);
				}
			}

		/* delete nodes */
		var nodesToBeDeleted=Array(); 
		for(var c=0;tagsToBeDeleted[c];c++) {
			if(ancestor.getElementsByTagName) {
				for(var i=0;ancestor.getElementsByTagName(tagsToBeDeleted[c])[i];i++) {
					nodesToBeDeleted[nodesToBeDeleted.length]=ancestor.getElementsByTagName(tagsToBeDeleted[c])[i];
					}
				}
			}
		/* remove styles, class etc, and add comments to the deletion list */
		var checkChilds=function(DOMnode) {
			if(DOMnode.nodeType==1||DOMnode.nodeType==2) {
				DOMnode.removeAttribute('style');
				DOMnode.removeAttribute('class');
				DOMnode.removeAttribute('bgcolor');
				DOMnode.removeAttribute('border');
				DOMnode.removeAttribute('cellpadding');
				DOMnode.removeAttribute('align');
				DOMnode.removeAttribute('valign');
				}
			else if(DOMnode.nodeType>3) nodesToBeDeleted[nodesToBeDeleted.length]=DOMnode;
			for(var i=0;DOMnode.childNodes[i];i++) {
				checkChilds(DOMnode.childNodes[i]);
				}
			}
		checkChilds(ancestor);
		for(var i=0;nodesToBeDeleted[i];i++) {
			if(nodesToBeDeleted[i]) nodesToBeDeleted[i].parentNode.removeChild(nodesToBeDeleted[i]);
			}

		/* remove spaces etc */
		var html=ancestor.innerHTML;
		html=html.replace(/&nbsp;/g,' ');
		html=html.replace(/<h[123456]>\s*?<\/h[123456]>/gi,'');
		html=html.replace(/<p>\s*?<\/p>/gi,'');
		html=html.replace(/[ \t]+/g,' ');
		html=html.replace(/\n+/g,"\n");
		ancestor.innerHTML=html;

		}

	function kIframeToTextarea(cntI) {
		cnt=cntI.innerHTML;
		cnt=cnt.replace("\r",'');
		cnt=cnt.replace(/<div id="?kCopyPasteStart"?><\/div>/gi,'');
		cnt=cnt.replace(/<div id="?kCopyPasteStop"?><\/div>/gi,'');
		cnt=cnt.replace(/<(\/?)b( .*?)?>/gi,'<$1strong$2>');
		cnt=cnt.replace(/<(\/?)i( .*?)?>/gi,'<$1em$2>');
		cnt=cnt.replace(/<(\/?)STRONG( .*?)?>/g,'<$1strong$2>');
		cnt=cnt.replace(/<(\/?)EM( .*?)?>/g,'<$1em$2>');
		cnt=cnt.replace(/<(\/?)U( .*?)?>/g,'<$1u$2>');
		cnt=cnt.replace(/<br( .*?)?>/gi,"<br$1 />\n");
		cnt=cnt.replace(/<br( .*?)? \/>\n*/gi,"<br$1 />\n");
		cnt=cnt.replace(/<p( .*?)?>(<br \/>\n)*<\/p>$/gi,'');
		cnt=cnt.replace(/^(<p( .*?)?>(<br \/>\n)*<\/p>)*/gi,'');
		return cnt;
		}
	function kTextareaToIframe(cnt) {
		cnt=cnt.replace("\r",'');
		cnt=cnt.replace(/<div id="?kCopyPasteStart"?><\/div>/gi,'');
		cnt=cnt.replace(/<div id="?kCopyPasteStop"?><\/div>/gi,'');
		cnt=cnt.replace(/<(\/?)strong( .*?)?>/gi,'<$1b$2>');
		cnt=cnt.replace(/<(\/?)em( .*?)?>/gi,'<$1i$2>');
		cnt=cnt.replace(/^\s*/,"");
		cnt=cnt.replace(/\s*$/,"");
		if(cnt.replace(/\n*/,"")=="") cnt='<p></p>';
		return cnt;
		}
	function nodeContains(node,tag) {
		return node.getElementsByTagName(tag).length>0?true:false;
		}
	function nodeContained(node,tag) {
		for(var i=0;node.parentNode;i++) {
			node=node.parentNode;
			if(node.tagName==tag) return true;
			}
		return false;
		}
	function kOnKeyDown(e) {
		if(e.ctrlKey==true&&e.keyCode==66) { //ctrl-b
			e.preventDefault();
			formatHTML('bold',false);
			}
		else if(e.ctrlKey==true&&e.keyCode==73) { //ctrl-i
			e.preventDefault();
			formatHTML('italic',false);
			}
		else if(e.ctrlKey==true&&e.keyCode==85) { //ctrl-u
			e.preventDefault();
			formatHTML('underline',false);
			}
		return false;
		}
	function kOnKeyUp(e) {
		//do nothing
		}
	function kOnFocus(e) {
		if(iframe.contentWindow.document.body.innerHTML.replace(/<\/?p>/g,'').replace(/(\s|\n|\t|\r)/g,'')=="") {
			iframe.contentWindow.document.body.innerHTML='';
			formatHTML('formatblock','<p>');
			}
		}
	}

function onScrollHandler(e) {
	document.getElementById('menu').className=(window.pageYOffset>document.getElementById('header').offsetHeight?'fixed':'');
	}

function kCheckMessages() {
	if(document.getElementById('MsgSuccess')) {
		var msg=document.getElementById('MsgSuccess');
		document.body.appendChild(msg);
		msg.style.display='block';
		msg.style.position='absolute';
		msg.style.zIndex='610';
		msg.style.width='300px';
		msg.style.top=((kWindow.clientHeight()-msg.offsetHeight)/2+kWindow.scrollTop())+'px';
		msg.style.left=(kWindow.clientWidth()-msg.offsetWidth)/2+'px';
		msg.onclick=b3_closeMessages;
		var closebtn=document.createElement('DIV');
		closebtn.style.position='absolute';
		closebtn.style.bottom='0';
		closebtn.style.right='2px';
		closebtn.style.fontSize='0.7em';
		closebtn.style.fontWeight='normal';
		closebtn.appendChild(document.createTextNode('premi per continuare'));
		closebtn.onclick=b3_closeMessages;
		msg.appendChild(closebtn);

		var bkg=document.createElement('DIV');
		bkg.id='MsgAlertBkg';
		bkg.style.position="absolute";
		document.body.appendChild(bkg);
		bkg.style.cssText='position:absolute;background-color:#000;top:0;left:0;width:100%;height:'+kWindow.pageHeight()+'px;z-index:600;filter:alpha(opacity=70);-moz-opacity:.70;opacity:.70;';
		bkg.onclick=b3_closeMessages;
		document.onkeypress=b3_closeMessages;
		};
	if(document.getElementById('MsgAlert')) {
		var msg=document.getElementById('MsgAlert');
		document.body.appendChild(msg);
		msg.style.display='block';
		msg.style.position='absolute';
		msg.style.zIndex='610';
		msg.style.width='300px';
		msg.style.top=((kWindow.clientHeight()-msg.offsetHeight)/2+kWindow.scrollTop())+'px';
		msg.style.left=(kWindow.clientWidth()-msg.offsetWidth)/2+'px';
		var closebtn=document.createElement('DIV');
		closebtn.style.position='absolute';
		closebtn.style.bottom='0';
		closebtn.style.right='2px';
		closebtn.style.fontSize='0.7em';
		closebtn.style.fontWeight='normal';
		closebtn.appendChild(document.createTextNode('premi per continuare'));
		closebtn.onclick=b3_closeMessages;
		msg.appendChild(closebtn);
		var bkg=document.createElement('DIV');
		bkg.id='MsgAlertBkg';
		bkg.style.cssText='position:absolute;background-color:#000;top:0;left:0;width:100%;height:'+kWindow.pageHeight()+'px;z-index:600;filter:alpha(opacity=70);-moz-opacity:.70;opacity:.70;';
		document.body.appendChild(bkg);
		bkg.onclick=b3_closeMessages;
		document.onkeypress=b3_closeMessages;
		};
	}
b3_closeMessages=function() {
	if(document.getElementById('MsgSuccess')) {
		var msg=document.getElementById('MsgSuccess');
		var bkg=document.getElementById('MsgAlertBkg');
		msg.parentNode.removeChild(msg);
		bkg.parentNode.removeChild(bkg);
		};
	if(document.getElementById('MsgAlert')) {
		var msg=document.getElementById('MsgAlert');
		var bkg=document.getElementById('MsgAlertBkg');
		msg.parentNode.removeChild(msg);
		bkg.parentNode.removeChild(bkg);
		};
	if(document.getElementById('MsgNeutral')) {
		var msg=document.getElementById('MsgNeutral');
		var bkg=document.getElementById('MsgNeutralBkg');
		msg.parentNode.removeChild(msg);
		bkg.parentNode.removeChild(bkg);
		};
	document.onkeypress=null;
	}
b3_openMessage=function(text,clicktoclose) {
	if(!clicktoclose) clicktoclose=false;
	var msg=document.createElement('DIV');
	msg.id='MsgNeutral';
	msg.appendChild(document.createTextNode(text));
	document.body.appendChild(msg);
	msg.style.display='block';
	msg.style.position='absolute';
	msg.style.zIndex='610';
	msg.style.width='300px';
	msg.style.top=((kWindow.clientHeight()-msg.offsetHeight)/2+kWindow.scrollTop())+'px';
	msg.style.left=(kWindow.clientWidth()-msg.offsetWidth)/2+'px';
	var bkg=document.createElement('DIV');
	bkg.id='MsgNeutralBkg';
	bkg.style.position="absolute";
	if(clicktoclose==true) {
		msg.onclick=b3_closeMessages;
		var closebtn=document.createElement('DIV');
		closebtn.style.position='absolute';
		closebtn.style.bottom='0';
		closebtn.style.right='2px';
		closebtn.style.fontSize='0.7em';
		closebtn.style.fontWeight='normal';
		closebtn.appendChild(document.createTextNode('premi per continuare'));
		closebtn.onclick=b3_closeMessages;
		bkg.onclick=b3_closeMessages;
		msg.appendChild(closebtn);
		document.onkeypress=b3_closeMessages;
		}
	document.body.appendChild(bkg);
	bkg.style.cssText='position:absolute;background-color:#000;top:0;left:0;width:100%;height:'+kWindow.pageHeight()+'px;z-index:600;filter:alpha(opacity=70);-moz-opacity:.70;opacity:.70;';
	}

/* finestre interne */
function k_openIframeWindow(url,w,h,bkgclose) {
	if(document.getElementById('iframeWindow')) k_closeIframeWindow();
	if(!w) var w="500px";
	if(!h) var h="400px";
	var msg=document.createElement('DIV');
	msg.id='iframeWindow';
	msg.style.position='absolute';
	msg.style.zIndex='610';
	msg.style.width=w;
	msg.style.height=h;
	document.body.appendChild(msg);
	msg.style.top=((kWindow.clientHeight()-msg.offsetHeight)/2+kWindow.scrollTop())+'px';
	msg.style.left=(kWindow.clientWidth()-msg.offsetWidth)/2+'px';
	msg.innerHTML='<iframe src="'+url+'" frameborder="0" style="width:'+w+';height:'+h+';"></iframe>';
	var bkg=document.createElement('DIV');
	bkg.id='iframeWindowBkg';
	bkg.style.height=kWindow.pageHeight()+'px';
	document.body.appendChild(bkg);
	if(bkgclose==true) bkg.onclick=k_closeIframeWindow;
	}
k_closeIframeWindow=function() {
	if(document.getElementById('iframeWindow')) {
		var msg=document.getElementById('iframeWindow');
		msg.parentNode.removeChild(msg);
		}
	if(document.getElementById('iframeWindowBkg')) {
		var bkg=document.getElementById('iframeWindowBkg');
		bkg.parentNode.removeChild(bkg);
		};
	}

function kOpenIPopUp(url,vars,w,h) {
	var msg=document.createElement('DIV');
	msg.id='iPopUpWindow';
	msg.style.position='absolute';
	msg.style.width=w;
	msg.style.height=h;
	msg.style.zIndex='610';
	document.body.appendChild(msg);
	msg.style.top=((kWindow.clientHeight()-msg.offsetHeight)/2+kWindow.scrollTop())+'px';
	msg.style.left=(kWindow.clientWidth()-msg.offsetWidth)/2+'px';
	var bkg=document.createElement('DIV');
	bkg.id='iframeWindowBkg';
	bkg.style.height=kWindow.pageHeight()+'px';
	document.body.appendChild(bkg);
	var aj=new kAjax();
	aj.onSuccess(function(html) {msg.innerHTML=html});
	aj.send('post',url,vars);
	}
function kCloseIPopUp() {
	if(document.getElementById('iPopUpWindow')) {
		var msg=document.getElementById('iPopUpWindow');
		msg.parentNode.removeChild(msg);
		}
	if(document.getElementById('iframeWindowBkg')) {
		var bkg=document.getElementById('iframeWindowBkg');
		bkg.parentNode.removeChild(bkg);
		};
	}

function kBoxSwapOpening(b) {
	if(b.className.indexOf('opened')>-1) b.className=b.className.replace("opened","closed");
	else b.className=b.className.replace("closed","opened");
	}

var kBaloonTimer=null;
kOpenBaloon=function(url,top,left) {
	kClearBaloonTimer();
	if(document.getElementById('kBaloon')) document.getElementById('kBaloon').parentNode.removeChild(document.getElementById('kBaloon'),true);
	var baloon=document.createElement('DIV');
	baloon.id='kBaloon';
	var arrow=document.createElement('DIV');
	arrow.className='arrow';
	baloon.appendChild(arrow);
	document.body.appendChild(baloon);
	baloon.style.top=(top-baloon.offsetHeight)+'px';
	baloon.style.left=(left-baloon.offsetWidth/2)+'px';
	baloon.onmouseover=function() { kClearBaloonTimer(); }
	baloon.onmouseout=function() { kCloseBaloon(); }
	var aj=new kAjax();
	aj.onSuccess(function(html) {
		baloon.innerHTML+=html;
		baloon.style.top=(top-baloon.offsetHeight)+'px';
		});
	aj.send("post",url);
	}
kCloseBaloon=function() {
	kClearBaloonTimer();
	kBaloonTimer=setTimeout(kRemoveBaloon,300);
	}
kRemoveBaloon=function() {
	if(document.getElementById('kBaloon')) document.getElementById('kBaloon').parentNode.removeChild(document.getElementById('kBaloon'),true);
	}
kClearBaloonTimer=function() {
	if(kBaloonTimer) clearTimeout(kBaloonTimer);
	}
kAutosizeIframe=function(iframe) {
	iframe.style.height=iframe.contentDocument.documentElement.scrollHeight+'px';
	}

kDragAndDrop=function() {
	var dragElement=dropElement=null;
	var offset={x:false,y:false},dragElement=null;

	var makeDraggable=function(elm,customOnDragStart,customOnDrag,customOnDragStop) {
		if(!elm) return false;
		if(!customOnDragStart) customOnDragStart=function() {};
		if(!customOnDrag) customOnDrag=function() {};
		if(!customOnDragStop) customOnDragStop=function() {};
		elm.draggable=true;
		elm.customOnDragStart=customOnDragStart;
		elm.customOnDrag=customOnDrag;
		elm.customOnDragStop=customOnDragStop;
		elm.addEventListener("dragstart",onDragStart);
		elm.addEventListener("drag",onDrag);
		elm.addEventListener("touchstart",onDragStart);
		elm.addEventListener("touchmove",onTouchMove);
		elm.addEventListener("touchend",onDragStop);
		document.body.addEventListener("dragover",onDragOverBody);
		}
	this.makeDraggable=makeDraggable;
	
	var makeDroppable=function(elm,customOnDragOver,customOnDragLeave,customOnDrop) {
		if(!elm) return false;
		if(!customOnDragOver) customOnDragOver=function() {};
		if(!customOnDragLeave) customOnDragLeave=function() {};
		if(!customOnDrop) customOnDrop=function() {};
		elm.customOnDrop=customOnDrop;
		elm.customOnDragOver=customOnDragOver;
		elm.customOnDragLeave=customOnDragLeave;
		elm.addEventListener("dragover",onDragOver);
		elm.addEventListener("dragleave",onDragLeave);
		elm.addEventListener("drop",onDrop);
		}
	this.makeDroppable=makeDroppable;
	
	var onDragStart=function(e) {
		dragElement=this;
		this.customOnDragStart(e);
		e.dataTransfer.effectAllowed='copy';
		e.dataTransfer.setData('Text',dragElement.id);
		}
	var onDrag=function(e) {
		}
	var onDragOverBody=function(e) {
		if(offset.x==false) {
			offset.y=e.clientY-dragElement.offsetTop;
			offset.x=e.clientX-dragElement.offsetLeft;
			}
		if(e.clientY<50) {
			window.scrollTo(0,document.documentElement.scrollTop-10);
			}
		else if(e.clientY>document.documentElement.offsetHeight-50) {
			window.scrollTo(0,document.documentElement.scrollTop+10);
			}
		dragElement.customOnDrag(e);
		}
	var onTouchMove=function(e) {
		if(e.touches.length==1) {
			e.preventDefault();
			var touch=e.touches[0];
			dragElement.customOnDrag(touch);
			}
		}
	var onDragStop=function(e) {
		dragElement.customOnDragStop(e);
		}
	var onDragOver=function(e) {
		e.preventDefault();
		dropElement=this;
		e.dataTransfer.dropEffect='copy';
		dropElement.customOnDragOver(e);
		}
	var onDragLeave=function(e) {
		e.preventDefault();
		e.dataTransfer.dropEffect='copy';
		dropElement.customOnDragLeave(e);
		}
	var onDrop=function(e) {
		e.preventDefault();
		offset={x:false,y:false};
		document.body.removeEventListener("dragover",onDragOverBody);
		dropElement.customOnDrop(e);
		}
	
	var getOffsetX=function() {
		return offset.x;
		}
	this.getOffsetX=getOffsetX;

	var getOffsetY=function() {
		return offset.y;
		}
	this.getOffsetY=getOffsetY;

	var getDraggedObject=function() {
		return dragElement;
		}
	this.getDraggedObject=getDraggedObject;

	var getDroppedObject=function() {
		return dropElement;
		}
	this.getDroppedObject=getDroppedObject;

	}