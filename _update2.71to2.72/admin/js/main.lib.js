/* (c) Kalamun.org - GNU/GPL 3 */

kInitZenEditor = function () {
	var areas = Array();

	this.init = function (newadmindir) {
		for (var i = 0, areas = document.getElementsByTagName('textarea'); areas[i]; i++) {
			initTextarea(areas[i], newadmindir);
		}
	}

	var initTextarea = function (textarea, newadmindir) {
		if (!textarea)
			return false;
		if (!textarea.getAttribute('editor') || textarea.getAttribute('editor') != 'kzen')
			return false;
		var id = areas.length;
		areas[id] = new kZenEditor;
		areas[id].init(textarea, newadmindir, id);
	}

	var getArea = function (id) {
		return areas[id];
	}
	this.getArea = getArea;
	
	var addCSS = function (url) {
		for(var i=0; areas[i]; i++) {
			areas[i].addCSS(url);
		}
	}
	this.addCSS=addCSS;
}

kZenEditor = function () {
	var textarea = null;
	var iframe = null;
	var container = null;
	var keys = Array();
	var keysContainer = null;
	var keysStatus = null;
	var swapKeys = null;
	var txtareaCopyPaste = null;
	var keysTimer = null;
	var copyPasteTmp = null;
	var copyPasteRange = null;
	var copyPasteScrollTop = 0;
	var ADMINDIR = '';
	var id = null;
	var designMode = 'rich';
	var holdKey = -1,
	holdCharacter = "",
	holding = false;
	var charAlternatives = null;
	var tips = null;
	var mouse = {
		top : 0,
		left : 0
	};
	var addCSSUrl = Array();
	var isLoaded=false;

	var tagReference = Array();
	tagReference['|'] = Array('separator.png', '|', '', 'separator');
	tagReference['strong'] = Array('b.png', 'Bold', 'setHTMLTag', '', '<strong>', '</strong>');
	tagReference['em'] = Array('i.png', 'Italic', 'setHTMLTag', '', '<em>', '</em>');
	tagReference['u'] = Array('u.png', 'Underscore', 'setHTMLTag', '', '<u>', '</u>');
	tagReference['left'] = Array('left.png', 'Align to Left', 'alignLeft', '', '', '');
	tagReference['center'] = Array('center.png', 'Align to Center', 'alignCenter', '', '', '');
	tagReference['right'] = Array('right.png', 'Align to Right', 'alignRight', '', '', '');
	tagReference['justify'] = Array('justify.png', 'Justified Align', 'alignJustify', '', '', '');
	tagReference['size'] = Array('size.png', 'Text Size');
	tagReference['h1'] = Array('h1.png', 'Main Title', 'setHTMLTag', '', '<h1>', '</h1>');
	tagReference['h2'] = Array('h2.png', 'Title of Second Level', 'setHTMLTag', '', '<h2>', '</h2>');
	tagReference['h3'] = Array('h3.png', 'Title of Third Level', 'setHTMLTag', '', '<h3>', '</h3>');
	tagReference['h4'] = Array('h4.png', 'Title of Fourth Level', 'setHTMLTag', '', '<h4>', '</h4>');
	tagReference['p'] = Array('p.png', 'Paragraph', 'setHTMLTag', '', '<p>', '</p>');
	tagReference['blockquote'] = Array('blockquote.png', 'Blockquote', 'setHTMLTag', '', '<blockquote>', '</blockquote>');
	tagReference['clean'] = Array('clean.png', 'Remove formatting', 'cleanFormatting', '', '', '');
	tagReference['hr'] = Array('hr.png', 'Horizontal Rule', 'setHTMLTag', '', '<hr>', '</hr>');
	tagReference['ul'] = Array('ul.png', 'Ordered List', 'setList', '', '<ul>', '</ul>');
	tagReference['ol'] = Array('ol.png', 'Unordered List', 'setList', '', '<ol>', '</ol>');
	tagReference['indent'] = Array('indent.png', 'Increase Indent', 'indentList', '', '', '');
	tagReference['indent-'] = Array('indent-.png', 'Decrease Indent', 'deindentList', '', '', '');
	tagReference['a'] = Array('a.png', 'Link', 'setLink', '', '', '');
	tagReference['mailto'] = Array('mailto.png', 'E-mail Link', 'setLink', '', 'mailto:', '');
	tagReference['table'] = Array('table.png', 'Add new table', 'setHTMLTag', '', '<table><tr><td>', '</td><td></td><td></td></tr><tr><td></td><td></td><td></td></tr></table>');
	tagReference['tr'] = Array('tr.png', 'Add new table line', 'addTr', '', '', '');
	tagReference['td'] = Array('td.png', 'Add new table column', 'addTd', '', '', '');
	tagReference['tr-'] = Array('tr-.png', 'Remove current table line', 'removeTr', '', '', '');
	tagReference['td-'] = Array('td-.png', 'Remove current table column', 'removeTd', '', '', '');
	tagReference['img'] = Array('img.png', 'Image', 'insertImg', '', '');
	tagReference['doc'] = Array('doc.png', 'Attachment', 'insertDoc', '', '');
	tagReference['media'] = Array('media.png', 'Media', 'insertMedia', '', '');
	tagReference['sourcecode'] = Array('code.png', 'Source Code', 'insertSourceCode', '', '');

	var alternatives = [
		'AÀÁÂÃÄÅÆ',
		'EÈÉÊË',
		'IÌÍÎÏ',
		'OÒÓÔÕÖØŒ',
		'UÙÚÛÜ',
		'aàáâãäåæ',
		'eèéêë℮',
		'iìíîï',
		'oòóôõöøœ',
		'uùúûü',
		'Sß',
		'sß',
		'C©Ç',
		'c©ç',
		'R®',
		'r®',
		'T™',
		't™',
		'NÑ',
		'nñ',
		'!¡‼',
		'?¿',
		'"“”„‟«»',
		'\'‘’‚‛′`',
		'^°⁰¹²³⁴⁵⁶⁷⁸⁹ºª',
		'$¢£¤¥€',
		'<>«»‹›≤≥',
		'%‰',
		'-–—~•',
		'=≠≈',
		'*•·',
		'.…'
	];
	var specialKeys = ',1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,91,92,93,112,113,114,115,116,117,118,119,120,121,122,123,144,145,';

	this.init = function (txtarea, newadmindir, newid) {
		textarea = txtarea;
		this.textarea = textarea;
		if (newadmindir)
			ADMINDIR = newadmindir;
		id = newid;
		container = document.createElement('div');
		container.className = 'kZenEditorContainer';
		container.style.width = textarea.style.width;
		textarea.style.width = "100%";
		textarea.parentNode.insertBefore(container, textarea);
		container.appendChild(textarea);
		iframe = document.createElement('iframe');
		iframe.frameBorder = '0';
		iframe.addEventListener("load", onLoadHandler);
		container.appendChild(iframe);
		textarea.form.addEventListener("submit", swapDesignMode);
		document.addEventListener("mousemove", onMouseMoveHandler);
		this.createKeys();
	}

	var onMouseMoveHandler = function (e) {
		var e = e || window.event;
		if (e.pageX || e.pageY) {
			mouse.left = e.pageX;
			mouse.top = e.pageY;
		}
	}

	this.createKeys = function () {
		if (textarea.getAttribute('keys'))
			var keys = textarea.getAttribute('keys');
		else
			var keys = "strong,em,u,|,left,center,right,justify,|,size{h1;h2;h3;h4;p;blockquote;clean},|,a,mailto,|,ul{ul;ol;indent;indent-},table{table;tr;td;tr-;td-},sourcecode,|,img,doc,media";
		keys = keys.split(',');
		for (var i in keys) {
			var subkeys = null;
			if (keys[i].indexOf("{") >= 0) {
				var subkeys = keys[i].substr(keys[i].indexOf("{") + 1);
				subkeys = subkeys.replace("}", "");
				keys[i] = keys[i].substr(0, keys[i].indexOf("{"));
			}
			if (tagReference[keys[i]]) {
				var key = tagReference[keys[i]];
				addKey(key[0], key[1], key[2], key[3], key[4], key[5]);
			}
			if (subkeys) {
				subkeys = subkeys.split(';');
				for (var j in subkeys) {
					if (tagReference[subkeys[j]]) {
						var key = tagReference[subkeys[j]];
						addSubkey(key[0], key[1], key[2], key[3], key[4], key[5]);
					}
				}
			}
		}
	};

	var action = function () {
		if (this.action && this.action[0]) {
			switch (this.action[0]) {
			case "setHTMLTag":
				setHTMLTag(this.action[1], this.action[2]);
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
			case "alignJustify":
				align('justify');
				break;
			case "addTr":
				addTr();
				break;
			case "removeTr":
				removeTr();
				break;
			case "addTd":
				addTd();
				break;
			case "removeTd":
				removeTd();
				break;
			case "setLink":
				setLink(this.action[1], this.action[2]);
				break;
			case "setList":
				setList(this.action[1], this.action[2]);
				break;
			case "indentList":
				indentList();
				break;
			case "deindentList":
				deindentList();
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
			case "changeHeight":
				changeHeight();
				break;
			case "swapDesignMode":
				swapDesignMode();
				break;
			default:
				return false;
			}
		}
	};

	var addCSS = function(url) {
		if(isLoaded==false) {
			addCSSUrl.push(url);
			return true;
		}

		var cw = iframe.contentWindow;
		var cwd = cw.document;
		var css = cwd.createElement('LINK');
		css.type = "text/css";
		css.rel = "stylesheet";
		css.href = url;
		cwd.head.appendChild(css);
	}
	this.addCSS=addCSS;

	var onLoadHandler = function () {
		kAddEvent(iframe, "mouseover", iframeOnMouseOver);
		kAddEvent(iframe, "mouseout", iframeOnMouseOut);
		kAddEvent(textarea, "mouseover", iframeOnMouseOver);
		kAddEvent(textarea, "mouseout", iframeOnMouseOut);
		var cw = iframe.contentWindow;
		var cwd = cw.document;
		cwd.designMode = "on";

		cwd.open();
		cwd.write('<!DOCTYPE html><html charset=UTF-8><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"></head><body></body></html>');
		cwd.close();

		kAddEvent(cwd.body, 'paste', iframeOnPaste, false);
		kAddEvent(cw, 'focus', kOnFocus, true);
		kAddEvent(cw, 'keydown', kOnKeyDown, true);
		kAddEvent(cw, 'keyup', kOnKeyUp, true);
		kAddEvent(cw, 'mousedown', closeAlternatives, true);
		kAddEvent(cw, 'scroll', closeAlternatives, true);
		kAddEvent(cw, 'mouseup', onIframeMouseupHandler, true);

		cwd.body.innerHTML = textarea.value;

		try {
			iframe.contentDocument.execCommand("styleWithCSS", 0, false);
		} catch (e) {
			try {
				iframe.contentDocument.execCommand("useCSS", 0, true);
			} catch (e) {
				try {
					iframe.contentDocument.execCommand('styleWithCSS', false, false);
				} catch (e) {}
			}
		}
		try {
			iframe.contentDocument.execCommand('enableInlineTableEditing', null, false);
		} catch (e) {}
		try {
			iframe.contentDocument.execCommand('enableObjectResizing', null, true);
		} catch (e) {}
		try {
			iframe.contentDocument.execCommand('insertBrOnReturn', null, false);
		} catch (e) {}

		swapDesignMode("rich");
		
		isLoaded=true;

		addCSS(ADMINDIR + 'css/richeditor.css');
		
		for(var i=0; addCSSUrl[i]; i++) {
			addCSS(addCSSUrl[i]);
		}
	}

	var iframeOnMouseOver = function () {
		container.className = container.className.replace('hover', '');
		container.className += ' hover';
		if (keysContainer) {
			clearKeysTimer();
			keysContainer.style.height = keysContainer.scrollHeight + 'px';
			keysContainer.style.top = '-' + (keysContainer.scrollHeight) + 'px';
			keysStatus.style.height = keysStatus.scrollHeight + 'px';
			keysStatus.style.bottom = -keysStatus.scrollHeight + 'px';
		}
	}
	var iframeOnMouseOut = function () {
		keysTimer = setTimeout(hideKeys, 200);
	}
	var clearKeysTimer = function () {
		if (keysTimer)
			clearTimeout(keysTimer);
	}
	var hideKeys = function () {
		container.className = container.className.replace('hover', '');
		if (keysContainer) {
			keysContainer.style.height = '0px';
			keysContainer.style.top = '0px';
			keysStatus.style.height = '0px';
			keysStatus.style.bottom = '0px';
		}
	}
	var onIframeMouseupHandler = function () {
		var sel = iframe.contentWindow.getSelection();
		var range = sel.getRangeAt(0);
		var startContainer = range.startContainer,
		startOffset = range.startOffset,
		endContainer = range.endContainer,
		endOffset = range.endOffset;

		var tmp = range.cloneContents();

		// image tips
		if (tmp.childNodes.length == 1 && tmp.childNodes[0].tagName == 'IMG' && (tmp.childNodes[0].getAttribute('id').substr(0, 3) == 'img' || tmp.childNodes[0].getAttribute('id').substr(0, 5) == 'thumb')) {
			var pos = range.getBoundingClientRect();

			tips = document.createElement('DIV');
			tips.className = 'charAlternatives sans';

			var txtnode = document.createElement('DIV');
			var editlabel="Edit";
			if(typeof kaDictionary!="undefined") editlabel=kaDictionary.Edit;
			txtnode.appendChild(document.createTextNode(editlabel));
			kAddEvent(txtnode, "click", function () {
				editImg(tmp.childNodes[0].getAttribute('id'));
			});
			tips.appendChild(txtnode);
			container.appendChild(tips);
			tips.style.top = Math.round(pos.top - 3 - tips.offsetHeight) + 'px';
			tips.style.left = Math.round(pos.left + 5) + 'px';

		} else
			delete tmp;
	}

	this.swapDesignMode = function (mode) {
		if (!mode)
			mode = (designMode == 'text' ? 'rich' : 'text');
		if (mode != "text" && mode != "rich")
			mode = "text";
		swapDesignMode(mode);
	}
	function swapDesignMode(mode) {
		if (typeof mode != 'string')
			mode = null;
		if (!mode)
			mode = (designMode == 'text' ? 'rich' : 'text');
		if (mode != "text" && mode != "rich")
			mode = "text";
		if (mode == designMode) {}
		else if (mode == "text") {
			if (iframe)
				iframe.style.display = 'none';
			var cnt = iframe.contentWindow.document.body.cloneNode(true);
			cnt = kIframeToTextarea(cnt);
			textarea.value = cnt;
			designMode = "text";
		} else if (mode == "rich" && iframe) {
			iframe.style.display = 'block';
			var cnt = textarea.value;
			cnt = kTextareaToIframe(cnt);
			iframe.contentWindow.document.body.innerHTML = cnt;
			designMode = "rich";
		}
		if (designMode == 'rich')
			swapKeys.className = 'swapKeys';
		else
			swapKeys.className = 'swapKeys source';
	}

	function addKey(imgname, alt, onclick, CSSclass, kbefore, kafter) {
		if (!kbefore)
			kbefore = "";
		if (!kafter)
			kafter = "";

		if (!keysContainer) {
			keysContainer = document.createElement('DIV');
			keysContainer.className = 'buttons';
			keysContainer.style.position = 'absolute';
			keysContainer.style.height = '0px';
			keysContainer.style.top = '0px';
			keysContainer.addEventListener("mouseover", clearKeysTimer);
			keysContainer.addEventListener("mouseout", iframeOnMouseOut);
			container.appendChild(keysContainer);

			keysStatus = document.createElement('DIV');
			keysStatus.className = 'status';
			keysStatus.style.height = '0px';
			keysStatus.addEventListener("mouseover", clearKeysTimer);
			keysStatus.addEventListener("mouseout", iframeOnMouseOut);
			container.appendChild(keysStatus);

			var img = document.createElement('DIV');
			img.className = "zoom";
			img.addEventListener("mousedown", changeHeight);
			keysStatus.appendChild(img);

			swapKeys = document.createElement('DIV');
			swapKeys.className = 'swapKeys';
			swapKeys.addEventListener("click", swapDesignMode);
			keysStatus.appendChild(swapKeys);
		};

		var img = new Image();
		img.src = ADMINDIR + 'img/' + imgname;
		img.alt = alt;
		img.title = alt;
		if (CSSclass)
			img.className = CSSclass;
		if (onclick && onclick != "") {
			img.action = Array(onclick, kbefore, kafter);
			img.addEventListener("click", action);
		}
		var btn = document.createElement('DIV');
		if (CSSclass)
			btn.className = CSSclass;
		btn.appendChild(img);
		keysContainer.appendChild(btn);
		return img;
	};
	this.addKey = addKey;

	function addSubkey(imgname, alt, onclick, CSSclass, kbefore, kafter) {
		var lastDiv = keysContainer.childNodes[keysContainer.childNodes.length - 1];
		var submenu = lastDiv.childNodes[lastDiv.childNodes.length - 1]
			if (!submenu || submenu.tagName != 'DIV' || submenu.className != 'subbtn') {
				submenu = document.createElement('DIV');
				submenu.className = 'subbtn';
				keysContainer.childNodes[keysContainer.childNodes.length - 1].appendChild(submenu);
			}
			var img = new Image();
		img.src = ADMINDIR + 'img/' + imgname;
		img.alt = alt;
		img.title = alt;
		if (CSSclass != "")
			img.className = CSSclass;
		img.setAttribute('kbefore', kbefore ? kbefore : '');
		img.setAttribute('kafter', kafter ? kafter : '');
		if (onclick && onclick != "") {
			img.action = Array(onclick, kbefore, kafter);
			img.addEventListener("click", action);
		}
		var btn = document.createElement('DIV');
		if (CSSclass)
			btn.className = CSSclass;
		btn.appendChild(img);
		submenu.appendChild(btn);
		return img;
	};
	this.addSubkey = addSubkey;

	function getNodeContents(node) {
		if (node == null)
			return null;
		if (node.nodeType == 1)
			return node.innerHTML; // element
		else if (node.nodeType == 3)
			return node.nodeValue; // textnode
	}
	function setHTMLTag(addBefore, addAfter) {
		if (designMode == "rich") {
			if (addBefore == '<strong>')
				formatHTML('bold', false);
			else if (addBefore == '<em>')
				formatHTML('italic', false);
			else if (addBefore == '<u>')
				formatHTML('underline', false);
			else if (addBefore == '<p>')
				formatHTML('formatblock', '<p>');
			else if (addBefore == '<h1>')
				formatHTML('formatblock', '<h1>');
			else if (addBefore == '<h2>')
				formatHTML('formatblock', '<h2>');
			else if (addBefore == '<h3>')
				formatHTML('formatblock', '<h3>');
			else if (addBefore == '<h4>')
				formatHTML('formatblock', '<h4>');
			else if (addBefore == '<blockquote>')
				formatHTML('formatblock', '<blockquote>');
			else
				insertHTML(addBefore + addAfter);
		} else {
			formatSource(addBefore, addAfter);
		}
	}
	function align(set) {
		if (designMode == "rich") {
			var sel = iframe.contentWindow.getSelection();
			var range = sel.getRangeAt(0);
			var startContainer = range.startContainer,
			startOffset = range.startOffset,
			endContainer = range.endContainer,
			endOffset = range.endOffset;

			var tmp = range.cloneContents();
			if (tmp.childNodes.length == 1 && tmp.childNodes[0].tagName == 'IMG') {
				// align image
				if (range.startContainer.tagName == 'DIV' && range.startContainer.className.indexOf('align') >= 0) {
					range.setStartBefore(range.startContainer);
					range.setEndAfter(range.endContainer);
					var fragment = range.extractContents();
					for (var i = 0, c = fragment.childNodes[0].childNodes; c[i]; c++) {
						c[i].className = c[i].className.replace('alignleft', '');
						c[i].className = c[i].className.replace('alignright', '');
						fragment.appendChild(c[i]);
					}
					fragment.removeChild(fragment.childNodes[0]);
					range.insertNode(fragment);
				}
				if (set == 'left' || set == 'right') {
					var fragment = range.extractContents();
					var i = fragment.childNodes[0];
					i.className = i.className.replace('alignleft', '');
					i.className = i.className.replace('alignright', '');
					i.className += " align" + set;
					i.className = i.className.trim();
					range.insertNode(fragment);
				} else {
					var fragment = range.extractContents();
					var i = fragment.childNodes[0];
					i.className = i.className.replace('alignleft', '');
					i.className = i.className.replace('alignright', '');
					i.className = i.className.trim();
					range.insertNode(fragment);
					var div = document.createElement('DIV');
					div.className = "align" + set;
					range.surroundContents(div);
				}
			} else {
				// align elements
				var start = range.startContainer;
				var end = range.endContainer;
				for (; start.nodeType != 1; start = start.parentNode) {}
				for (; end.nodeType != 1; end = end.parentNode) {}
				range.setStartBefore(start.tagName != "BODY" ? start : range.startContainer);
				range.setEndAfter(end.tagName != "BODY" ? end : range.endContainer);

				var fragment = range.extractContents();

				for (var i = 0, c = fragment.querySelectorAll('.alignleft'); c[i]; i++) {
					c[i].className = c[i].className.replace('alignleft', '');
				}
				for (var i = 0, c = fragment.querySelectorAll('.aligncenter'); c[i]; i++) {
					c[i].className = c[i].className.replace('aligncenter', '');
				}
				for (var i = 0, c = fragment.querySelectorAll('.alignright'); c[i]; i++) {
					c[i].className = c[i].className.replace('alignright', '');
				}
				for (var i = 0, c = fragment.querySelectorAll('.alignjustify'); c[i]; i++) {
					c[i].className = c[i].className.replace('alignjustify', '');
				}
				for (var i = 0, c = fragment.childNodes; c[i]; i++) {
					if (c[i].nodeType == 1 && c[i].tagName != 'BODY') {
						c[i].className += ' align' + set;
						c[i].className = c[i].className.trim();
					} else if (c[i].nodeType == 3) {
						var p = document.createElement('P');
						p.className = 'align' + set;
						fragment.insertBefore(p, c[i]);
						p.appendChild(c[i + 1]);
					}
				}

				range.insertNode(fragment);
			}

			range.setStart(startContainer, startOffset);
			range.setEnd(endContainer, endOffset);
			return false;
		} else {
			setHTMLTag('<div style="text-align:' + (set.toLowerCase()) + ';">', '</div>');
			var cnt = textarea.value;
			cnt = cnt.replace(/"float:([^;]*);? ?text-align:([^;]*);?"/gi, '"text-align:$2;"');
			cnt = cnt.replace(/<div style="text-align:([^;]*);?"><img src="([^"]*)" id="([^"]*)"([*\/]*)\/?><\/div>/gi, '<div style="float:$1;" class="$2"><img src="$3" id="$4"$5 /></div>');
			cnt = cnt.replace(/float: ?center;?/, 'text-align:center;');
			textarea.value = cnt;
		}
	}
	function insertHTML(html) {
		var tag = document.createElement('DIV');
		tag.innerHTML = html;
		for (var i = 0; tag.childNodes[i]; i++) {
			range = iframe.contentWindow.getSelection().getRangeAt(0);
			var tmp = tag.childNodes[i].cloneNode(true);
			range.insertNode(tmp);
			range.setStartAfter(tmp);
		}
		tag = null;
	}
	function formatHTML(what, opt) {
		try {
			iframe.contentDocument.execCommand("styleWithCSS", 0, false);
		} catch (e) {
			try {
				iframe.contentDocument.execCommand("useCSS", 0, true);
			} catch (e) {
				try {
					iframe.contentDocument.execCommand('styleWithCSS', false, false);
				} catch (e) {}
			}
		}
		iframe.contentWindow.document.execCommand(what, false, opt);
		iframe.contentWindow.focus();
	}
	function formatSource(addBefore, addAfter) {
		var target = null;
		if (designMode == "text")
			target = textarea;
		target.focus();
		var start_selection = Math.min(target.selectionStart, target.selectionEnd),
		end_selection = Math.max(target.selectionStart, target.selectionEnd);
		var startText = (target.value).substring(0, start_selection);
		var selectedText = (target.value).substring(start_selection, end_selection);
		var endText = (target.value).substring(end_selection, target.textLength);
		var scrollTop = target.scrollTop;
		target.value = startText + addBefore + selectedText + addAfter + endText;
		target.selectionStart = start_selection;
		target.selectionEnd = start_selection + addBefore.length + selectedText.length + addAfter.length;
		target.scrollTop = scrollTop;
		target.focus();
	}

	function setLink(addBefore, addAfter, url, title, target, className, nofollow) {
		if (!addBefore || addBefore == "")
			var addBefore = "http://";
		if (!url)
			var url = null;
		if (!tag)
			var tag = null;

		if (designMode == "rich") {
			if (!tag) {
				var range = null;
				var contents = null;
				if (iframe.contentWindow.getSelection) { // FF
					range = iframe.contentWindow.getSelection().getRangeAt(0);
					for (ancestor = range.commonAncestorContainer; ancestor; ancestor = ancestor.parentNode) {
						if (ancestor.tagName == 'A' && (!ancestor.getAttribute('id') || !ancestor.getAttribute('id').match(/doc\d*/))) {
							tag = true;
							var vhref = ancestor.href ? ancestor.href : addBefore;
							var vtarget = ancestor.target ? ancestor.target : "";
							var vnofollow = ancestor.rel == 'nofollow' ? "true" : "false";
							var vclass = ancestor.className ? ancestor.className : "";
							var vtitle = ancestor.title ? ancestor.title : "";
							break;
						}
					}
				}
			}
			if (!tag) {
				tag = false;
				var vhref = addBefore;
				var vtarget = "";
				var vnofollow = false;
				var vclass = "";
				var vtitle = "";
			}
			if (url == null) {
				var dirname = window.location.href.replace(/\\/g, '/').replace(/\/[^\/]*$/, '');
				if (vhref.substr(0, dirname.length) == dirname)
					vhref = vhref.substr(dirname.length + 1);
				k_openIframeWindow(ADMINDIR + 'inc/linkManager.inc.php?refid=' + id + '&href=' + escape(vhref) + '&target=' + escape(vtarget) + '&nofollow=' + escape(vnofollow) + '&class=' + escape(vclass) + '&title=' + escape(vtitle), '800px', '500px');
			} else {
				formatHTML('createlink', url);
				if (iframe.contentWindow.getSelection) { // FF
					var range = iframe.contentWindow.getSelection().getRangeAt(0);
					var rangeBkup = {
						startElm : range.startContainer,
						startOffset : range.startOffset,
						endElm : range.endContainer,
						endOffset : range.endOffset
					};
					ancestor = range.commonAncestorContainer;
					range.selectNodeContents(ancestor);
					if (ancestor.nodeType == 1) {
						if (ancestor.tagName == 'A' && ancestor.href.replace(/\/$/, '') == url.replace(/\/$/, '')) {
							if (title && title != "")
								ancestor.setAttribute("title", title);
							if (target && target != "")
								ancestor.setAttribute("target", target);
							if (className && className != "")
								ancestor.className = className;
							if (nofollow && nofollow == true)
								ancestor.setAttribute("rel", 'nofollow');
						}
						for (var i = 0; ancestor.getElementsByTagName('A')[i]; i++) {
							if (ancestor.getElementsByTagName('A')[i].href.replace(/\/$/, '') == url.replace(/\/$/, '')) {
								if (title && title != "")
									ancestor.getElementsByTagName('A')[i].setAttribute("title", title);
								if (target && target != "")
									ancestor.getElementsByTagName('A')[i].setAttribute("target", target);
								if (className && className != "")
									ancestor.getElementsByTagName('A')[i].className = className;
								if (nofollow && nofollow == true)
									ancestor.getElementsByTagName('A')[i].setAttribute("rel", 'nofollow');
							}
						}
					}
					range.setStart(rangeBkup.startElm, rangeBkup.startOffset);
					range.setEnd(rangeBkup.endElm, rangeBkup.endOffset);
				}
			}
		}
	}
	this.setLink = function (addBefore, addAfter, url, title, target, className, nofollow) {
		setLink(addBefore, addAfter, url, title, target, className, nofollow);
	}
	function removeLink() {
		if (iframe.contentWindow.getSelection) { // FF
			range = iframe.contentWindow.getSelection().getRangeAt(0);
			for (ancestor = range.commonAncestorContainer; ancestor; ancestor = ancestor.parentNode) {
				if (ancestor.tagName == 'A' && (!ancestor.getAttribute('id') || !ancestor.getAttribute('id').match(/doc\d*/))) {
					range.selectNodeContents(ancestor);
					var rangeBkup = {
						startElm : null,
						endElm : null
					};
					while (ancestor.firstChild) {
						if (!rangeBkup.startElm)
							rangeBkup.startElm = ancestor.firstChild;
						rangeBkup.endElm = ancestor.firstChild;
						ancestor.parentNode.insertBefore(ancestor.firstChild, ancestor);
					}
					ancestor.parentNode.removeChild(ancestor);
					iframe.contentWindow.getSelection().removeAllRanges();
					var range = iframe.contentWindow.document.createRange();
					range.setStartBefore(rangeBkup.startElm);
					range.setEndAfter(rangeBkup.endElm);
					iframe.contentWindow.getSelection().addRange(range);
					break;
				}
			}
		}
	}
	this.removeLink = function () {
		removeLink();
	}
	function updateLink(addBefore, addAfter, url, title, target, className, nofollow) {
		removeLink();
		setLink(addBefore, addAfter, url, title, target, className, nofollow);
	}
	this.updateLink = function (addBefore, addAfter, url, title, target, className, nofollow) {
		updateLink(addBefore, addAfter, url, title, target, className, nofollow);
	}
	function setList(addBefore, addAfter) {
		if (designMode == "rich") {
			if (addBefore == '<ul>')
				formatHTML('insertunorderedlist', false);
			else if (addBefore == '<ol>')
				formatHTML('insertorderedlist', false);
		} else {
			var list = addBefore + "\n";
			var listNumber = prompt('Quanti elementi vuoi inserire nella lista?', '1');
			if (listNumber) {
				for (var iii = 1; listNumber >= iii; iii++) {
					list += "<li>" + prompt(iii + '° punto della lista:', '') + "</li>\n";
				}
				list += "" + addAfter + "\n";
				setHTMLTag('', list);
			}
		}
	}
	function indentList() {
		if (designMode == "rich") {
			var sel = iframe.contentWindow.getSelection();
			var range = sel.getRangeAt(0);

			var parentlist = null,
			firstli = null,
			lastli = null;
			for (parentlist = range.commonAncestorContainer; parentlist; parentlist = parentlist.parentNode) {
				if (parentlist.tagName == 'UL' || parentlist.tagName == 'OL')
					break;
			}
			for (firstli = range.startContainer; firstli; firstli = firstli.parentNode) {
				if (firstli.tagName == 'LI')
					break;
			}
			for (lastli = range.endContainer; lastli; lastli = lastli.parentNode) {
				if (lastli.tagName == 'LI')
					break;
			}

			var list = document.createElement(parentlist.tagName);
			firstli.parentNode.insertBefore(list, firstli);
			range.setStartBefore(firstli);
			range.setEndAfter(lastli);
			list.appendChild(range.extractContents());
			range.setStartBefore(firstli);
			range.setEndAfter(lastli);
		}
	}
	function deindentList() {
		if (designMode == "rich") {
			var sel = iframe.contentWindow.getSelection();
			var range = sel.getRangeAt(0);
			var firstli = null,
			lastli = null;
			for (ancestor = range.commonAncestorContainer; ancestor; ancestor = ancestor.parentNode) {
				if (ancestor.tagName == 'UL' || ancestor.tagName == 'OL') {
					firstli = ancestor.childNodes[0];
					lastli = ancestor.childNodes[ancestor.childNodes.length - 1];
					for (var i = 0; ancestor.childNodes[0]; i++) {
						ancestor.parentNode.insertBefore(ancestor.childNodes[0], ancestor);
					}
					ancestor.parentNode.removeChild(ancestor);
					break;
				}
			}
			range.setStartBefore(firstli);
			range.setEndAfter(lastli);
		}
	}

	function insertSourceCode(code) {
		if (!code) {
			k_openIframeWindow(ADMINDIR + 'inc/sourceCodeManager.inc.php?refid=' + id, '800px', '500px');
		} else {
			code = code.replace(/\[Bettino:NewLine\]/g, "\n");
			setHTMLTag("", code);
		}
	}
	this.insertSourceCode = function (code) {
		insertSourceCode(code);
	}
	function insertImg(imgs) {
		if (!imgs || !imgs[0]) {
			var mediatable = container.getAttribute('mediatable');
			var mediaid = container.getAttribute('mediaid');
			parent.kZenEditorInsertImg = insertImg;
			parent.kZenEditorInsertThumb = insertThumb;
			var imglabel='Insert image';
			var thumblabel='Insert thumbnail';
			if(typeof kaDictionary != "undefined")
			{
				imglabel=kaDictionary.Insertimage;
				thumblabel=kaDictionary.Insertthumbnail;
			}
			k_openIframeWindow(ADMINDIR + 'inc/uploadsManager.inc.php?limit=1&submitlabel=' + imglabel + '&submitlabel2=' + thumblabel + '&onsubmit=parent.kZenEditorInsertImg&onsubmit2=parent.kZenEditorInsertThumb');
		} else {
			setHTMLTag('', '<img src="' + imgs[0].dir + imgs[0].filename + '" id="img' + imgs[0].id + '" />');
			k_closeIframeWindow();
		}
	}
	this.insertImg = function (imgs) {
		insertImg(imgs);
	}

	function editImg(id) {
		k_openIframeWindow(ADMINDIR + 'inc/uploadsManager_edit.inc.php?id=' + id);
	}

	function insertThumb(imgs) {
		if (!imgs || !imgs[0]) {
			var mediatable = container.getAttribute('mediatable');
			var mediaid = container.getAttribute('mediaid');
			k_openIframeWindow(ADMINDIR + 'inc/uploadsManager.inc.php?limit=1&onsubmit=parent.kZenEditorInsertImg&onsubmit2=parent.kZenEditorInsertThumb');
		} else {
			setHTMLTag('', '<img src="' + imgs[0].dir + imgs[0].thumbnail + '" id="thumb' + imgs[0].id + '" />');
			k_closeIframeWindow();
		}
	}
	this.insertThumb = function (imgs) {
		insertThumb(imgs);
	}

	function insertDoc(iddoc, alt, url) {
		if (!iddoc) {
			if (designMode == "rich") {
				var range = null;
				var contents = null;
				if (iframe.contentWindow.getSelection) { // FF
					range = iframe.contentWindow.getSelection().getRangeAt(0);
					for (ancestor = range.commonAncestorContainer; ancestor; ancestor = ancestor.parentNode) {
						if (ancestor.tagName == 'A' && ancestor.getAttribute('id').match(/doc\d*/))
							var iddoc = ancestor.getAttribute('id').substring(3);
					}
				}
			}
		}
		if (!iddoc) {
			var iddoc = "";
		}
		if (!url) {
			var mediatable = container.getAttribute('mediatable');
			var mediaid = container.getAttribute('mediaid');
			k_openIframeWindow(ADMINDIR + 'inc/docManager.inc.php?refid=' + id + '&mediatable=' + mediatable + '&mediaid=' + mediaid + '&iddoc=' + iddoc, '800px', '500px');
		} else {
			setHTMLTag('<a href="' + url + '" id="doc' + iddoc + '">', alt + '</a>');
		}
	}
	this.insertDoc = function (iddoc, alt, url) {
		insertDoc(iddoc, alt, url);
	}
	function insertMedia(idmedia, alt, url, width, height) {
		if (!idmedia) {
			if (designMode == "rich") {
				var range = null;
				var contents = null;
				if (iframe.contentWindow.getSelection) { // FF
					range = iframe.contentWindow.getSelection().getRangeAt(0);
					contents = range.cloneContents();
					if (contents.childNodes.length == 1 && contents.childNodes[0].nodeType == 1 && contents.childNodes[0].tagName == 'IMG') {
						if (contents.childNodes[0].getAttribute('id').match(/media\d*/))
							var idmedia = contents.childNodes[0].getAttribute('id').substring(5);
					}
				}
			}
		}
		if (!idmedia) {
			var idmedia = "";
		}
		if (!url) {
			var mediatable = container.getAttribute('mediatable');
			var mediaid = container.getAttribute('mediaid');
			k_openIframeWindow(ADMINDIR + 'inc/mediaManager.inc.php?refid=' + id + '&mediatable=' + mediatable + '&mediaid=' + mediaid + '&idmedia=' + idmedia, '800px', '500px');
		} else {
			setHTMLTag('', '<img src="' + url + '" id="media' + idmedia + '" ' + (width > 0 ? 'width="' + width + '" ' : '') + (height > 0 ? 'height="' + height + '" ' : '') + '/>');
		}
	}
	this.insertMedia = function (idmedia, alt, url, width, height) {
		insertMedia(idmedia, alt, url, width, height);
	}

	/* table */
	function addTr() {
		if (designMode == "rich") {
			var sel = iframe.contentWindow.getSelection();
			var range = sel.getRangeAt(0);
			for (ancestor = range.commonAncestorContainer; ancestor; ancestor = ancestor.parentNode) {
				if (ancestor.tagName == 'TR') {
					var tr = document.createElement('TR');
					var tdcount = 0;
					for (var i = 0, c = ancestor.childNodes; c[i]; i++) {
						if (c[i].nodeType == 1 && c[i].tagName == 'TD')
							tdcount++;
					}
					for (var i = 0; i < tdcount; i++) {
						var td = document.createElement('TD');
						tr.appendChild(td);
					}
					ancestor.parentNode.insertBefore(tr, ancestor.nextSibling);
					break;
				}
			}
		}
	}
	function removeTr() {
		if (designMode == "rich") {
			var sel = iframe.contentWindow.getSelection();
			var range = sel.getRangeAt(0);
			for (ancestor = range.commonAncestorContainer; ancestor; ancestor = ancestor.parentNode) {
				if (ancestor.tagName == 'TR') {
					for (table = ancestor.parentNode; table; table = table.parentNode) {
						if (table.tagName == 'TABLE')
							break;
					}
					ancestor.parentNode.removeChild(ancestor);
					break;
				}
			}
			if (table.getElementsByTagName('TR').length == 0)
				table.parentNode.removeChild(table);
		}
	}
	function addTd() {
		if (designMode == "rich") {
			var sel = iframe.contentWindow.getSelection();
			var range = sel.getRangeAt(0);
			for (ancestor = range.commonAncestorContainer; ancestor; ancestor = ancestor.parentNode) {
				if (ancestor.tagName == 'TD') {
					for (var tdcount = -1, a = ancestor; a && a.tagName != 'TR'; a = a.previousSibling) {
						tdcount++;
					}
					break;
				}
			}
			for (ancestor = range.commonAncestorContainer; ancestor; ancestor = ancestor.parentNode) {
				if (ancestor.tagName == 'TR') {
					var tr = Array();
					for (var i = 0, c = ancestor.parentNode.childNodes; c[i]; i++) {
						if (c[i].nodeType == 1 && c[i].tagName == 'TR')
							tr[tr.length] = c[i];
					}
					for (var i = 0; tr[i]; i++) {
						for (var j = 0, n = -1, c = tr[i].childNodes; c[j]; j++) {
							if (c[j].nodeType == 1 && c[j].tagName == 'TD')
								n++;
							if (n == tdcount) {
								ref = c[j];
								break;
							}
						}
						if (ref) {
							var td = document.createElement('TD');
							ref.parentNode.insertBefore(td, ref.nextSibling);
						}
					}
					break;
				}
			}
		}
	}
	function removeTd() {
		if (designMode == "rich") {
			var sel = iframe.contentWindow.getSelection();
			var range = sel.getRangeAt(0);
			for (ancestor = range.commonAncestorContainer; ancestor; ancestor = ancestor.parentNode) {
				if (ancestor.tagName == 'TD') {
					for (var tdcount = -1, a = ancestor; a && a.tagName != 'TR'; a = a.previousSibling) {
						tdcount++;
					}
					for (table = ancestor.parentNode; table; table = table.parentNode) {
						if (table.tagName == 'TABLE')
							break;
					}
					break;
				}
			}
			for (ancestor = range.commonAncestorContainer; ancestor; ancestor = ancestor.parentNode) {
				if (ancestor.tagName == 'TR') {
					var tr = Array();
					for (var i = 0, c = ancestor.parentNode.childNodes; c[i]; i++) {
						if (c[i].nodeType == 1 && c[i].tagName == 'TR')
							tr[tr.length] = c[i];
					}
					for (var i = 0; tr[i]; i++) {
						for (var j = 0, n = -1, c = tr[i].childNodes; c[j]; j++) {
							if (c[j].nodeType == 1 && c[j].tagName == 'TD')
								n++;
							if (n == tdcount) {
								ref = c[j];
								break;
							}
						}
						if (ref) {
							ref.parentNode.removeChild(ref);
						}
					}
					break;
				}
			}
			if (table.getElementsByTagName('TD').length == 0)
				table.parentNode.removeChild(table);
		}
	}

	/* copy paste and cleaning */
	function iframeOnPaste(e) {
		var ibody = iframe.contentWindow.document.ibody;
		copyPasteTmp = document.createDocumentFragment();
		copyPasteScrollTop = ibody.scrollTop;
		var range = iframe.contentWindow.getSelection().getRangeAt(0);
		copyPasteRange = {
			startElm : range.startContainer,
			startOffset : range.startOffset,
			endElm : range.endContainer,
			endOffset : range.endOffset
		};
		while (ibody.firstChild) {
			copyPasteTmp.appendChild(ibody.firstChild);
		}
		if (e && e.clipboardData && e.clipboardData.getData) { // Webkit
			if (/text\/html/.test(e.clipboardData.types))
				iframe.contentWindow.document.ibody.innerHTML = e.clipboardData.getData('text/html');
			else if (/text\/plain/.test(e.clipboardData.types))
				iframe.contentWindow.document.ibody.innerHTML = e.clipboardData.getData('text/plain');
			else
				iframe.contentWindow.document.ibody.innerHTML = "";
			waitforpastedata();
			if (e.preventDefault) {
				e.stopPropagation();
				e.preventDefault();
			}
			return false;
		} else { // FF, IE...
			waitforpastedata();
			return true;
		}
	}
	function waitforpastedata() {
		var ibody = iframe.contentWindow.document.body;
		if (ibody.childNodes && ibody.childNodes.length > 0)
			processpaste();
		else
			setTimeout(waitforpastedata, 20);
	}
	function processpaste() {
		var ibody = iframe.contentWindow.document.body;
		//cleaning pasted data
		clearNode(ibody);
		ibody.innerHTML = ibody.innerHTML.replace(/<(\/)b>/i, "<$1strong>");
		//end cleaning

		var pastedData = ibody.innerHTML;

		ibody.innerHTML = "";
		while (copyPasteTmp.childNodes.length > 0) {
			ibody.appendChild(copyPasteTmp.childNodes[0]);
		}
		iframe.contentWindow.getSelection().removeAllRanges();
		var range = iframe.contentWindow.document.createRange();
		range.setStart(copyPasteRange.startElm, copyPasteRange.startOffset);
		range.setEnd(copyPasteRange.endElm, copyPasteRange.endOffset);
		iframe.contentWindow.getSelection().addRange(range);
		range.deleteContents();
		insertHTML(pastedData);
		ibody.scrollTop = copyPasteScrollTop;
		copyPasteRange = null;
		pastedData = null;
	}
	function clearNode(node) {
		for (var i = 0; node.childNodes[i]; i++) {
			if (node.childNodes[i].nodeType > 3 || (node.childNodes[i].nodeType == 1 && node.childNodes[i].tagName.match(/^(TITLE|STYLE|META|W:.*|M:.*|XML)$/i))) {
				//remove node and contents
				node.removeChild(node.childNodes[i]);
				i--;
			} else if (node.childNodes[i].nodeType == 1 && node.childNodes[i].tagName.match(/^(FONT|SPAN)$/i)) {
				//remove node but contents
				for (var j = 0; node.childNodes[i].childNodes[j]; j++) {
					node.insertBefore(node.childNodes[i].childNodes[j], node.childNodes[i].nextSibling);
				}
				node.removeChild(node.childNodes[i]);
				i--;
			} else if (node.childNodes[i].nodeType == 1 && node.childNodes[i].childNodes.length > 0)
				clearNode(node.childNodes[i]);
		}
	}
	function cleanFormatting() {
		var range = iframe.contentWindow.getSelection().getRangeAt(0);
		ancestor = range.commonAncestorContainer;

		/* remove nodes */
		var tagsToBeRemoved = Array("FONT", "SPAN", "DIV", "U", "ADDRESS"); //remove node, leave contents
		var tagsToBeDeleted = Array("STYLE", "XML", "SCRIPT", "META"); //remove node and contents

		var nodesToBeRemoved = Array();
		for (var c = 0; tagsToBeRemoved[c]; c++) {
			if (ancestor.getElementsByTagName) {
				for (var i = 0; ancestor.getElementsByTagName(tagsToBeRemoved[c])[i]; i++) {
					nodesToBeRemoved[nodesToBeRemoved.length] = ancestor.getElementsByTagName(tagsToBeRemoved[c])[i];
				}
			}
		}
		for (var i = 0; nodesToBeRemoved[i]; i++) {
			if (nodesToBeRemoved[i]) {
				var toBeRemoved = nodesToBeRemoved[i];
				while (toBeRemoved.lastChild) {
					toBeRemoved.parentNode.insertBefore(toBeRemoved.lastChild, toBeRemoved.nextSibling);
				}
				toBeRemoved.parentNode.removeChild(toBeRemoved);
			}
		}

		/* delete nodes */
		var nodesToBeDeleted = Array();
		for (var c = 0; tagsToBeDeleted[c]; c++) {
			if (ancestor.getElementsByTagName) {
				for (var i = 0; ancestor.getElementsByTagName(tagsToBeDeleted[c])[i]; i++) {
					nodesToBeDeleted[nodesToBeDeleted.length] = ancestor.getElementsByTagName(tagsToBeDeleted[c])[i];
				}
			}
		}
		/* remove styles, class etc, and add comments to the deletion list */
		var checkChilds = function (DOMnode) {
			if (DOMnode.nodeType == 1 || DOMnode.nodeType == 2) {
				DOMnode.removeAttribute('style');
				DOMnode.removeAttribute('class');
				DOMnode.removeAttribute('bgcolor');
				DOMnode.removeAttribute('border');
				DOMnode.removeAttribute('cellpadding');
				DOMnode.removeAttribute('align');
				DOMnode.removeAttribute('valign');
			} else if (DOMnode.nodeType > 3)
				nodesToBeDeleted[nodesToBeDeleted.length] = DOMnode;
			for (var i = 0; DOMnode.childNodes[i]; i++) {
				checkChilds(DOMnode.childNodes[i]);
			}
		}
		checkChilds(ancestor);
		for (var i = 0; nodesToBeDeleted[i]; i++) {
			if (nodesToBeDeleted[i])
				nodesToBeDeleted[i].parentNode.removeChild(nodesToBeDeleted[i]);
		}

		/* remove empty links */
		for(var i=0, c=ancestor.getElementsByTagName('A'); c[i]; i++) {
			if(c[i].innerHTML.replace("/\s/","")=="") c[i].parentNode.removeChild(c[i]);
		}

		/* remove spaces etc */
		var html = ancestor.innerHTML;
		html = html.replace(/&nbsp;/g, ' ');
		html = html.replace(/<h[123456]>\s*?<\/h[123456]>/gi, '');
		html = html.replace(/<a [^>]*>\s*?<\/a>/gi, '');
		html = html.replace(/[ \t]+/g, ' ');
		html = html.replace(/\n+/g, "\n");
		ancestor.innerHTML = html;

	}

	/* swap modes */
	function kIframeToTextarea(cntI) {
		// remove empty links
		for(var i=0, c=cntI.getElementsByTagName('A'); c[i]; i++) {
			if(c[i].innerHTML.replace("/\s/","")=="") c[i].parentNode.removeChild(c[i]);
		}
		cnt = cntI.innerHTML;
		cnt = cnt.replace("\r", '');
		cnt = cnt.replace(/<(\/?)b( .*?)?>/gi, '<$1strong$2>');
		cnt = cnt.replace(/<(\/?)i( .*?)?>/gi, '<$1em$2>');
		cnt = cnt.replace(/<(\/?)STRONG( .*?)?>/g, '<$1strong$2>');
		cnt = cnt.replace(/<(\/?)EM( .*?)?>/g, '<$1em$2>');
		cnt = cnt.replace(/<(\/?)U( .*?)?>/g, '<$1u$2>');
		cnt = cnt.replace(/<br( .*?)?>/gi, "<br$1 />\n");
		cnt = cnt.replace(/<br( .*?)? \/>\n*/gi, "<br$1 />\n");
		cnt = cnt.replace(/<p( .*?)?>(<br \/>\n)*<\/p>$/gi, '');
		cnt = cnt.replace(/^(<p( .*?)?>(<br \/>\n)*<\/p>)*/gi, '');
		return cnt;
	}
	function kTextareaToIframe(cnt) {
		cnt = cnt.replace("\r", '');
		cnt = cnt.replace(/<(\/?)strong( .*?)?>/gi, '<$1b$2>');
		cnt = cnt.replace(/<(\/?)em( .*?)?>/gi, '<$1i$2>');
		cnt = cnt.replace(/^\s*/, "");
		cnt = cnt.replace(/\s*$/, "");
		if (cnt.replace(/\n*/, "") == "")
			cnt = '<p></p>';
		return cnt;
	}
	function nodeContains(node, tag) {
		return node.getElementsByTagName(tag).length > 0 ? true : false;
	}
	function nodeContained(node, tag) {
		for (var i = 0; node.parentNode; i++) {
			node = node.parentNode;
			if (node.tagName == tag)
				return true;
		}
		return false;
	}

	/* resize editor */
	function changeHeight() {
		mouseStartingPositionY = mouse.top;
		textareaStartingHeight = parseInt(textarea.style.height, 10);
		document.addEventListener("mousemove", resizeEditor);
		document.addEventListener("mouseup", stopResizing);
	}
	function resizeEditor() {
		pixels = mouseStartingPositionY - mouse.top;
		finalSize = textareaStartingHeight - Number(pixels);
		if (finalSize > 50) {
			textarea.style.height = finalSize + "px";
			//iframe.style.height=finalSize+"px";
		}
	}
	function stopResizing() {
		document.removeEventListener("mousemove", resizeEditor);
		document.removeEventListener("mouseup", stopResizing);
		if (kAjax) {
			var aj = new kAjax;
			aj.send("post", ADMINDIR + 'users/ajax/setParam.php', '&param=' + escape(textarea.name) + '_height&family=editor&value=' + parseInt(textarea.style.height, 10));
		}
	}

	/* alternatives */
	function kOnKeyDown(e) {
		if (charAlternatives && !holding) { // browse alternatives
			var sel = iframe.contentWindow.getSelection();
			var range = sel.getRangeAt(0);
			holdCharacter = range.startContainer.textContent.substring(range.startOffset, range.endOffset);
			if (holdKey == e.keyCode || e.keyCode == 39) { // when same key or right arrow is pressed
				e.preventDefault();
				nextAlternative();
				return;
			} else if (e.keyCode == 37) { // left arrow
				e.preventDefault();
				prevAlternative();
				return;
			} else if (e.keyCode == 13) { // prevent nl
				e.preventDefault();
			}
			// else let write more
			range.collapse(false);
			sel.removeAllRanges();
			sel.addRange(range);
			closeAlternatives();
		}
		if (holdKey == e.keyCode && specialKeys.indexOf(',' + e.keyCode + ',') == -1) { // prevent key hold
			e.preventDefault();
			holding = true;
			kOnKeyHold(e);
			return;
		}
		closeAlternatives();
		holdKey = e.keyCode;
		if (e.ctrlKey == true && e.keyCode == 66) { //ctrl-b
			e.preventDefault();
			formatHTML('bold', false);
		} else if (e.ctrlKey == true && e.keyCode == 73) { //ctrl-i
			e.preventDefault();
			formatHTML('italic', false);
		} else if (e.ctrlKey == true && e.keyCode == 85) { //ctrl-u
			e.preventDefault();
			formatHTML('underline', false);
		}
		return false;
	}
	function kOnKeyUp(e) {
		if (!charAlternatives) {
			holdKey = -1;
			holdCharacter = "";
		}
		holding = false;
	}
	function kOnKeyHold(e) {
		if (!charAlternatives) {
			var sel = iframe.contentWindow.getSelection();
			var range = sel.getRangeAt(0);
			range.setStart(range.endContainer, range.endOffset - 1);
			sel.removeAllRanges();
			sel.addRange(range);
			var pos = range.getBoundingClientRect();
			holdCharacter = range.startContainer.textContent.substring(range.startOffset, range.endOffset);
			var alternativeFound = false;

			for (var i in alternatives) {
				if (alternatives[i].indexOf(holdCharacter) >= 0) {
					charAlternatives = document.createElement('DIV');
					charAlternatives.className = 'charAlternatives';
					for (var j = 0, c = alternatives[i].split(''); c[j]; j++) {
						var txtnode = document.createElement('DIV');
						txtnode.appendChild(document.createTextNode(c[j]));
						if (c[j] == holdCharacter)
							txtnode.className = 'selected';
						charAlternatives.appendChild(txtnode);
					}
					container.appendChild(charAlternatives);
					charAlternatives.style.top = Math.round(pos.top - 3 - charAlternatives.offsetHeight) + 'px';
					charAlternatives.style.left = Math.round(pos.left - 13) + 'px';
					alternativeFound = true;
				}
			}

			if (!alternativeFound) {
				range.collapse(false);
				sel.removeAllRanges();
				sel.addRange(range);
			}
		}
	}
	function closeAlternatives() {
		if (charAlternatives) {
			charAlternatives.parentNode.removeChild(charAlternatives);
			charAlternatives = null;
			holdKey = -1;
			holdCharacter = "";
		}
		if (tips) {
			tips.parentNode.removeChild(tips);
			tips = null;
		}
	}
	function nextAlternative() {
		if (charAlternatives) {
			var selected = -1;
			for (var i = 0, c = charAlternatives.childNodes; c[i]; i++) {
				if (c[i].className == 'selected')
					selected = i;
				c[i].className = '';
			}
			selected++;
			if (!c[selected])
				selected = 0;
			c[selected].className = 'selected';

			var sel = iframe.contentWindow.getSelection();
			var range = sel.getRangeAt(0);
			range.deleteContents();
			range.insertNode(c[selected].childNodes[0].cloneNode());
			sel.removeAllRanges();
			sel.addRange(range);
		}
	}
	function prevAlternative() {
		if (charAlternatives) {
			var selected = -1;
			for (var i = 0, c = charAlternatives.childNodes; c[i]; i++) {
				if (c[i].className == 'selected')
					selected = i;
				c[i].className = '';
			}
			selected--;
			if (!c[selected])
				selected = c.length - 1;
			c[selected].className = 'selected';

			var sel = iframe.contentWindow.getSelection();
			var range = sel.getRangeAt(0);
			range.deleteContents();
			range.insertNode(c[selected].childNodes[0].cloneNode());
			sel.removeAllRanges();
			sel.addRange(range);
		}
	}

	function kOnFocus(e) {
		if (iframe.contentWindow.document.body.innerHTML.replace(/<\/?p>/g, '').replace(/(\s|\n|\t|\r)/g, '') == "") {
			iframe.contentWindow.document.body.innerHTML = '';
			formatHTML('formatblock', '<p>');
		}
	}
}

function onScrollHandler(e) {
	document.getElementById('menu').className = (window.pageYOffset > document.getElementById('header').offsetHeight ? 'fixed' : '');
	document.body.style.paddingTop = (window.pageYOffset > document.getElementById('header').offsetHeight ? document.getElementById('header').offsetHeight + 'px' : '0');
}

function kCheckMessages() {
	if (document.getElementById('MsgSuccess')) {
		var msg = document.getElementById('MsgSuccess');
		document.body.appendChild(msg);
		msg.style.display = 'block';
		msg.style.position = 'absolute';
		msg.style.zIndex = '610';
		msg.style.width = '300px';
		msg.style.top = ((kWindow.clientHeight() - msg.offsetHeight) / 2 + kWindow.scrollTop()) + 'px';
		msg.style.left = (kWindow.clientWidth() - msg.offsetWidth) / 2 + 'px';
		msg.onclick = b3_closeMessages;
		var closebtn = document.createElement('DIV');
		closebtn.style.position = 'absolute';
		closebtn.style.bottom = '0';
		closebtn.style.right = '2px';
		closebtn.style.fontSize = '0.7em';
		closebtn.style.fontWeight = 'normal';
		closebtn.appendChild(document.createTextNode('premi per continuare'));
		closebtn.onclick = b3_closeMessages;
		msg.appendChild(closebtn);

		var bkg = document.createElement('DIV');
		bkg.id = 'MsgAlertBkg';
		bkg.style.position = "absolute";
		document.body.appendChild(bkg);
		bkg.style.cssText = 'position:absolute;background-color:#000;top:0;left:0;width:100%;height:' + kWindow.pageHeight() + 'px;z-index:600;filter:alpha(opacity=70);-moz-opacity:.70;opacity:.70;';
		bkg.onclick = b3_closeMessages;
		document.onkeypress = b3_closeMessages;
	};
	if (document.getElementById('MsgAlert')) {
		var msg = document.getElementById('MsgAlert');
		document.body.appendChild(msg);
		msg.style.display = 'block';
		msg.style.position = 'absolute';
		msg.style.zIndex = '610';
		msg.style.width = '300px';
		msg.style.top = ((kWindow.clientHeight() - msg.offsetHeight) / 2 + kWindow.scrollTop()) + 'px';
		msg.style.left = (kWindow.clientWidth() - msg.offsetWidth) / 2 + 'px';
		var closebtn = document.createElement('DIV');
		closebtn.style.position = 'absolute';
		closebtn.style.bottom = '0';
		closebtn.style.right = '2px';
		closebtn.style.fontSize = '0.7em';
		closebtn.style.fontWeight = 'normal';
		closebtn.appendChild(document.createTextNode('premi per continuare'));
		closebtn.onclick = b3_closeMessages;
		msg.appendChild(closebtn);
		var bkg = document.createElement('DIV');
		bkg.id = 'MsgAlertBkg';
		bkg.style.cssText = 'position:absolute;background-color:#000;top:0;left:0;width:100%;height:' + kWindow.pageHeight() + 'px;z-index:600;filter:alpha(opacity=70);-moz-opacity:.70;opacity:.70;';
		document.body.appendChild(bkg);
		bkg.onclick = b3_closeMessages;
		document.onkeypress = b3_closeMessages;
	};
}
b3_closeMessages = function () {
	if (document.getElementById('MsgSuccess')) {
		var msg = document.getElementById('MsgSuccess');
		var bkg = document.getElementById('MsgAlertBkg');
		msg.parentNode.removeChild(msg);
		bkg.parentNode.removeChild(bkg);
	};
	if (document.getElementById('MsgAlert')) {
		var msg = document.getElementById('MsgAlert');
		var bkg = document.getElementById('MsgAlertBkg');
		msg.parentNode.removeChild(msg);
		bkg.parentNode.removeChild(bkg);
	};
	if (document.getElementById('MsgNeutral')) {
		var msg = document.getElementById('MsgNeutral');
		var bkg = document.getElementById('MsgNeutralBkg');
		msg.parentNode.removeChild(msg);
		bkg.parentNode.removeChild(bkg);
	};
	document.onkeypress = null;
}
b3_openMessage = function (text, clicktoclose) {
	if (!clicktoclose)
		clicktoclose = false;
	var msg = document.createElement('DIV');
	msg.id = 'MsgNeutral';
	msg.appendChild(document.createTextNode(text));
	document.body.appendChild(msg);
	msg.style.display = 'block';
	msg.style.position = 'absolute';
	msg.style.zIndex = '610';
	msg.style.width = '300px';
	msg.style.top = ((kWindow.clientHeight() - msg.offsetHeight) / 2 + kWindow.scrollTop()) + 'px';
	msg.style.left = (kWindow.clientWidth() - msg.offsetWidth) / 2 + 'px';
	var bkg = document.createElement('DIV');
	bkg.id = 'MsgNeutralBkg';
	bkg.style.position = "absolute";
	if (clicktoclose == true) {
		msg.onclick = b3_closeMessages;
		var closebtn = document.createElement('DIV');
		closebtn.style.position = 'absolute';
		closebtn.style.bottom = '0';
		closebtn.style.right = '2px';
		closebtn.style.fontSize = '0.7em';
		closebtn.style.fontWeight = 'normal';
		closebtn.appendChild(document.createTextNode('premi per continuare'));
		closebtn.onclick = b3_closeMessages;
		bkg.onclick = b3_closeMessages;
		msg.appendChild(closebtn);
		document.onkeypress = b3_closeMessages;
	}
	document.body.appendChild(bkg);
	bkg.style.cssText = 'position:absolute;background-color:#000;top:0;left:0;width:100%;height:' + kWindow.pageHeight() + 'px;z-index:600;filter:alpha(opacity=70);-moz-opacity:.70;opacity:.70;';
}

/* finestre interne */
function k_openIframeWindow(url, w, h, bkgclose) {
	if (document.getElementById('iframeWindow'))
		k_closeIframeWindow();
	if (!w)
		var w = "90%";
	if (!h)
		var h = "90%";
	var msg = document.createElement('DIV');
	msg.id = 'iframeWindow';
	msg.style.width = w;
	msg.style.height = h;
	document.body.appendChild(msg);
	msg.style.top = (kWindow.clientHeight() - msg.offsetHeight) / 2 + 'px';
	msg.style.left = (kWindow.clientWidth() - msg.offsetWidth) / 2 + 'px';
	msg.innerHTML = '<iframe src="' + url + '" frameborder="0"></iframe>';
	var bkg = document.createElement('DIV');
	bkg.id = 'iframeWindowBkg';
	bkg.style.height = kWindow.pageHeight() + 'px';
	document.body.appendChild(bkg);
	if (bkgclose == true)
		bkg.onclick = k_closeIframeWindow;
}
k_closeIframeWindow = function () {
	if (document.getElementById('iframeWindow')) {
		var msg = document.getElementById('iframeWindow');
		msg.parentNode.removeChild(msg);
	}
	if (document.getElementById('iframeWindowBkg')) {
		var bkg = document.getElementById('iframeWindowBkg');
		bkg.parentNode.removeChild(bkg);
	};
}

function kOpenIPopUp(url, vars, w, h) {
	var msg = document.createElement('DIV');
	msg.id = 'iPopUpWindow';
	msg.style.position = 'absolute';
	msg.style.width = w;
	msg.style.height = h;
	msg.style.zIndex = '610';
	document.body.appendChild(msg);
	msg.style.top = ((kWindow.clientHeight() - msg.offsetHeight) / 2 + kWindow.scrollTop()) + 'px';
	msg.style.left = (kWindow.clientWidth() - msg.offsetWidth) / 2 + 'px';
	var bkg = document.createElement('DIV');
	bkg.id = 'iframeWindowBkg';
	bkg.style.height = kWindow.pageHeight() + 'px';
	document.body.appendChild(bkg);
	var aj = new kAjax();
	aj.onSuccess(function (html) {
		msg.innerHTML = html
	});
	aj.send('post', url, vars);
}
function kCloseIPopUp() {
	if (document.getElementById('iPopUpWindow')) {
		var msg = document.getElementById('iPopUpWindow');
		msg.parentNode.removeChild(msg);
	}
	if (document.getElementById('iframeWindowBkg')) {
		var bkg = document.getElementById('iframeWindowBkg');
		bkg.parentNode.removeChild(bkg);
	};
}

function kBoxSwapOpening(b) {
	if (b.className.indexOf('opened') > -1)
		b.className = b.className.replace("opened", "closed");
	else
		b.className = b.className.replace("closed", "opened");
}

var kBaloonTimer = null;
kOpenBaloon = function (url, top, left) {
	kClearBaloonTimer();
	if (document.getElementById('kBaloon'))
		document.getElementById('kBaloon').parentNode.removeChild(document.getElementById('kBaloon'), true);
	var baloon = document.createElement('DIV');
	baloon.id = 'kBaloon';
	var arrow = document.createElement('DIV');
	arrow.className = 'arrow';
	baloon.appendChild(arrow);
	document.body.appendChild(baloon);
	baloon.style.top = (top - baloon.offsetHeight) + 'px';
	baloon.style.left = (left - baloon.offsetWidth / 2) + 'px';
	baloon.onmouseover = function () {
		kClearBaloonTimer();
	}
	baloon.onmouseout = function () {
		kCloseBaloon();
	}
	var aj = new kAjax();
	aj.onSuccess(function (html) {
		baloon.innerHTML += html;
		baloon.style.top = (top - baloon.offsetHeight) + 'px';
	});
	aj.send("post", url);
}
kCloseBaloon = function () {
	kClearBaloonTimer();
	kBaloonTimer = setTimeout(kRemoveBaloon, 300);
}
kRemoveBaloon = function () {
	if (document.getElementById('kBaloon'))
		document.getElementById('kBaloon').parentNode.removeChild(document.getElementById('kBaloon'), true);
}
kClearBaloonTimer = function () {
	if (kBaloonTimer)
		clearTimeout(kBaloonTimer);
}
kAutosizeIframe = function (iframe) {
	iframe.style.height = iframe.contentDocument.documentElement.scrollHeight + 'px';
}

kDragAndDrop = function () {
	var dragElement = dropElement = null;
	var offset = {
		x : false,
		y : false
	},
	dragElement = null;

	var makeDraggable = function (elm, customOnDragStart, customOnDrag, customOnDragEnd) {
		if (!elm)
			return false;
		if (!customOnDragStart)
			customOnDragStart = function () {};
		if (!customOnDrag)
			customOnDrag = function () {};
		if (!customOnDragEnd)
			customOnDragEnd = function () {};
		elm.draggable = true;
		elm.customOnDragStart = customOnDragStart;
		elm.customOnDrag = customOnDrag;
		elm.customOnDragEnd = customOnDragEnd;
		elm.addEventListener("dragstart", onDragStart);
		elm.addEventListener("dragend", onDragEnd);
		elm.addEventListener("touchstart", onDragStart);
		elm.addEventListener("touchmove", onTouchMove);
		elm.addEventListener("touchend", onDragEnd);
		document.body.addEventListener("dragover", onDragOverBody);
	}
	this.makeDraggable = makeDraggable;

	var makeDroppable = function (elm, customOnDragOver, customOnDragLeave, customOnDrop) {
		if (!elm)
			return false;
		if (!customOnDragOver)
			customOnDragOver = function () {};
		if (!customOnDragLeave)
			customOnDragLeave = function () {};
		if (!customOnDrop)
			customOnDrop = function () {};
		elm.customOnDrop = customOnDrop;
		elm.customOnDragOver = customOnDragOver;
		elm.customOnDragLeave = customOnDragLeave;
		elm.addEventListener("dragover", onDragOver, true);
		elm.addEventListener("dragleave", onDragLeave, true);
		elm.addEventListener("drop", onDrop, true);
	}
	this.makeDroppable = makeDroppable;

	var onDragStart = function (e) {
		var img = document.createElement("img"); //hide drag element
		e.dataTransfer.setDragImage(img, 0, 0);

		dragElement = this;
		this.customOnDragStart(e);
		document.body.addEventListener("mouseup", onDrop, true);
		e.dataTransfer.effectAllowed = 'copy';
		e.dataTransfer.setData('Text', dragElement.id);
	}
	var onDragOverBody = function (e) {
		if (offset.x == false) {
			offset.y = e.clientY - dragElement.offsetTop;
			offset.x = e.clientX - dragElement.offsetLeft;
		}
		var top = window.pageYOffset || document.documentElement.scrollTop;
		if (e.clientY < 50) {
			window.scrollTo(0, top - 10);
		} else if (e.clientY > document.documentElement.offsetHeight - 50) {
			window.scrollTo(0, top + 10);
		}
		dragElement.customOnDrag(e);
	}
	var onTouchMove = function (e) {
		if (e.touches.length == 1) {
			e.preventDefault();
			var touch = e.touches[0];
			dragElement.customOnDrag(touch);
		}
	}
	var onDragEnd = function (e) {
		dragElement.customOnDragEnd(e);
	}
	var onDragOver = function (e) {
		e.preventDefault();
		dropElement = this;
		e.dataTransfer.dropEffect = 'copy';
		dropElement.customOnDragOver(e);
	}
	var onDragLeave = function (e) {
		e.preventDefault();
		e.dataTransfer.dropEffect = 'copy';
		dropElement.customOnDragLeave(e);
	}
	var onDrop = function (e) {
		e.preventDefault();
		offset = {
			x : false,
			y : false
		};
		document.body.removeEventListener("dragover", onDragOverBody);
		dropElement.customOnDrop(e);
	}

	var getOffsetX = function () {
		return offset.x;
	}
	this.getOffsetX = getOffsetX;

	var getOffsetY = function () {
		return offset.y;
	}
	this.getOffsetY = getOffsetY;

	var getDraggedObject = function () {
		return dragElement;
	}
	this.getDraggedObject = getDraggedObject;

	var getDroppedObject = function () {
		return dropElement;
	}
	this.getDroppedObject = getDroppedObject;

}

/* photogallery manager
a div id=photogallery must exists into the page
 */
var photogalleryDD = new kDragAndDrop();
var photogalleryStartingOrder = [];
var draggingObject = null;

function kLoadPhotogallery(images) {
	var c = document.getElementById('photogallery');
	if (!c)
		return false;
	if (!images || images.replace(",", "") == "")
		return false;

	photogalleryDD.makeDroppable(c);

	var conditions = "";
	var imgs = images.split(",");
	photogalleryStartingOrder = imgs;

	for (var i in imgs) {
		if (parseInt(imgs[i]) > 0)
			conditions += " `idimg`=" + parseInt(imgs[i]) + " OR ";
	}
	conditions += " `idimg`=0 ";

	var aj = new kAjax();
	aj.onSuccess(kLoadPhotogalleryImages);
	aj.onFail(function () {
		c.innerHTML = 'OoOps, error loading photogallery!';
	});
	aj.send("post", ADMINDIR + 'inc/ajax/uploadHandler.php', 'action=getImageList&conditions=' + encodeURIComponent(conditions));

}

function kLoadPhotogalleryImages(html, xml) {
	var html = html.split("\n");
	var imgs = {};
	for (var i = 0; html[i]; i++) {
		if (html[i] == "")
			continue;
		var img = html[i].split("\t");

		imgs[img[0]] = img;
	}
	for (var i in photogalleryStartingOrder) {
		if (photogalleryStartingOrder[i] == "")
			continue;
		img = imgs[photogalleryStartingOrder[i]];
		if (img) {
			kCreateThumbnailIntoPhotogallery({
				"id" : img[0],
				"dir" : img[1],
				"image" : img[2],
				"thumbnail" : img[3],
				"size" : img[4],
				"alt" : img[5],
			});
		}
	}
	kUpdatePhotogalleryList();
}

function kAddImagesToPhotogallery(images) {
	for (var i = 0; images[i]; i++) {
		kCreateThumbnailIntoPhotogallery(images[i]);
	}
	kUpdatePhotogalleryList();
	k_closeIframeWindow();
}

function kUpdatePhotogalleryList() {
	var c = document.getElementById('photogallery');
	if (!c)
		return false;

	var input = document.getElementById('photogalleryList');
	if (!input) {
		var input = document.createElement('INPUT');
		input.setAttribute("name", "photogallery");
		input.setAttribute("type", "hidden");
		input.setAttribute("id", "photogalleryList");
		c.parentNode.appendChild(input);
	}

	var ids = ',';
	for (var i = 0; c.childNodes[i]; i++) {
		if (c.childNodes[i].nodeType == 1 && c.childNodes[i].getAttribute('idimg')) {
			ids += parseInt(c.childNodes[i].getAttribute('idimg')) + ',';
		}
	}

	input.value = ids;
}

function kCreateThumbnailIntoPhotogallery(image) {
	var c = document.getElementById('photogallery');
	if (!c || !image)
		return false;

	var imgc = document.createElement('DIV');
	imgc.setAttribute("class", "imagecontainer");
	imgc.setAttribute("id", "img" + c.childNodes.length + 1);
	imgc.setAttribute("idimg", image.id);

	var img = document.createElement('DIV');
	img.setAttribute("class", "image");
	img.style.backgroundImage = "url('" + image.dir + encodeURIComponent(image.thumbnail) + "')";
	imgc.appendChild(img);

	var caption = document.createElement('H3');
	caption.appendChild(document.createTextNode(image.image));
	imgc.appendChild(caption);

	var remove = document.createElement('DIV');
	remove.setAttribute("class", "remove");
	remove.setAttribute("id", "remove" + c.childNodes.length + 1);
	remove.appendChild(document.createTextNode('X'));
	kAddEvent(remove, "click", kRemoveThumbnailFromPhotogallery);
	imgc.appendChild(remove);

	var edit = document.createElement('DIV');
	edit.setAttribute("class", "edit");
	edit.setAttribute("id", "edit" + c.childNodes.length + 1);
	var editlabel="Edit";
	if(typeof kaDictionary!="undefined") editlabel=kaDictionary.Edit;
	edit.appendChild(document.createTextNode(editlabel));
	kAddEvent(edit, "click", kEditThumbnailFromPhotogallery);
	imgc.appendChild(edit);

	c.appendChild(imgc);

	photogalleryDD.makeDraggable(imgc, kThumbnailOnDragStart, kThumbnailOnDrag, kThumbnailOnDragEnd);

}

function kRemoveThumbnailFromPhotogallery(e, id) {
	if (!id)
		var id = this.id.replace('remove', 'img');
	img = document.getElementById(id);
	if (img)
		img.parentNode.removeChild(img, true);
	kUpdatePhotogalleryList();
}
function kEditThumbnailFromPhotogallery(e, id) {
	if (!id)
		var id = this.parentNode.getAttribute("idimg");
	k_openIframeWindow(ADMINDIR + 'inc/uploadsManager_edit.inc.php?id=img' + parseInt(id));
}

function kThumbnailOnDragStart(e) {
	draggingObject = e.target;
	e.target.className += " onDrag";
}
function kThumbnailOnDrag(e) {
	var t = e.target.parentNode;
	if (t.className == 'imagecontainer') {
		var pos = kGetPosition(t);
		if ((pos.x + t.offsetWidth / 2) > e.clientX)
			t.parentNode.insertBefore(draggingObject, t);
		else
			t.parentNode.insertBefore(draggingObject, t.nextSibling);
	}
}
function kThumbnailOnDragEnd(e) {
	console.log(e);
	if (draggingObject) {
		draggingObject.className = draggingObject.className.replace(" onDrag", "");
		photogalleryDD.makeDraggable(draggingObject, kThumbnailOnDragStart, kThumbnailOnDrag, kThumbnailOnDragEnd);
	}
	kUpdatePhotogalleryList();
}
