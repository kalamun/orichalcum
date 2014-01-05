/* (c) Kalamun.org - GNU/GPL 3 */
/* DRAG AND DROP */

kDrago=function() {
	var dragClass="DragZone";
	var dropClass="DrogZone";
	var containerTag='';
	var dropTags=Array();
	var onDrag=function() { return false; }
	var onDrop=function() { return false; }
	var dropArea=null;
	var dragObject=null;
	var mouseOffset=null;
	var bottomItem=null;
	var drago=null;
	var flyingOver=null;
	var dragging=false;

	/* metodi pubblici */
	this.dragClass=function(cl) { dragClass=cl; };
	this.dropClass=function(cl) { dropClass=cl; };
	this.containerTag=function(tag) { containerTag=tag; };
	this.addDropTag=function(tag) { dropTags[dropTags.length]=tag; };
	this.onDrag=function(func) { onDrag=func }
	this.onDrop=function(func) { onDrop=func; }
	this.getDragObject=function() { return dragObject; }
	this.getDropArea=function() { return dropArea; }
	this.getFlyingOver=function() { return flyingOver; }
	this.savePosition=function() { savePosition(); }

	/* metodi privati */
	function kInitDrago() {
		drago=document.createElement('DIV');
		drago.id='drago';
		drago.style.cssText='position:absolute;display:none;';
		drago.style.opacity=0.6;
		drago.ondragstart=function() { return false; }
		document.body.appendChild(drago);
		};
	
	function mouseUp() {
		if(dragObject) {
			if(dragging==true) onDrop(dragObject,flyingOver);
			drago.style.display='none';
			dragObject.style.visibility='visible';
			dragObject=null;
			mouseOffset=null;
			dragging=false;
			}
		return false;
		};

	function theBottomItem() {
		//scelgo il contenitore desiderato dentro cui si trova l'oggetto cliccato
		var droppa=false;
		var tmp=kWindow.elementOver;
		var tmp2='none';
		while(tmp) {
			if(tmp.tagName==containerTag) {
				tmp2=tmp.parentNode;
				while(tmp2) {
					if(tmp2.className==dragClass||tmp2.className==dropClass) {
						var droppa=tmp;
						break;
						}
					tmp2=tmp2.parentNode;
					}
				if(droppa) break;
				}
			tmp=tmp.parentNode;
			}
		return droppa;
		};

	function mouseDown() {
		dragging=false;
		dragObject=theBottomItem();
		if(dragObject) {
			dragObject.ondragstart=function() { return false; };
			var childs=kGetAllChilds(dragObject);
			for(var i=0;childs[i];i++) {
				if(childs[i].nodeType==3) childs[i].ondragstart=function() { return false; };
				}
			if(!dropArea) {
				dropArea=Array();
				populateDropArea(document.body);
				}
			savePosition();
			populateDrago();
			return false;
			}
		else dragObject=null;
		};

	function populateDrago() {
		var docPos=kGetPosition(dragObject);
		var mousePos=kWindow.mousePos;
		mouseOffset={x:mousePos.x-docPos.x,y:mousePos.y-docPos.y};
		for(var i=0;i<drago.childNodes.length;i++) drago.removeChild(drago.childNodes[i]);
		drago.appendChild(dragObject.cloneNode(true));
		drago.setAttribute('mouseOffsetX',mouseOffset.x);
		drago.setAttribute('mouseOffsetY',mouseOffset.y);
		drago.style.top=mousePos.y-mouseOffset.y+'px';
		drago.style.left=mousePos.x-mouseOffset.x+'px';
		drago.style.width=dragObject.offsetWidth+'px';
		drago.style.height=dragObject.offsetHeight+'px';
		kAddEvent(document.body,"onmousemove",mouseMove);
		};

	function populateDropArea(obj) {
		for(var i=0;obj.childNodes[i];i++) {
			if(obj.childNodes[i].className==dropClass||obj.childNodes[i].className==dragClass) {
				dropArea[dropArea.length]=obj.childNodes[i];
				}
			else populateDropArea(obj.childNodes[i]);
			}
		};

	function savePosition() {
		for(var i=0;dropArea[i];i++) {
			dropArea[i].setAttribute('ddTop',kGetPosition(dropArea[i]).y);
			dropArea[i].setAttribute('ddLeft',kGetPosition(dropArea[i]).x);
			dropArea[i].setAttribute('ddBottom',parseInt(dropArea[i].getAttribute('ddTop'))+parseInt(dropArea[i].offsetHeight));
			dropArea[i].setAttribute('ddRight',parseInt(dropArea[i].getAttribute('ddLeft'))+parseInt(dropArea[i].offsetWidth));
			for(var j=0;dropArea[i].getElementsByTagName(containerTag)[j];j++) {
				setPositionCoords(dropArea[i].getElementsByTagName(containerTag)[j]);
				}
			for(var c=0;dropTags[c];c++) {
				for(var j=0;dropArea[i].getElementsByTagName(dropTags[c])[j];j++) {
					setPositionCoords(dropArea[i].getElementsByTagName(dropTags[c])[j]);
					}
				}
			}
		};
	function setPositionCoords(obj) {
		obj.setAttribute('ddTop',kGetPosition(obj).y);
		obj.setAttribute('ddLeft',kGetPosition(obj).x);
		obj.setAttribute('ddBottom',parseInt(obj.getAttribute('ddTop'))+parseInt(obj.offsetHeight));
		obj.setAttribute('ddRight',parseInt(obj.getAttribute('ddLeft'))+parseInt(obj.offsetWidth));
		}
		
	function mouseMove() {
		dragging=true;
		if(dragObject) {
			dragObject.style.visibility='hidden';
			drago.style.display='block';
			drago.style.top=kWindow.mousePos.y-drago.getAttribute('mouseOffsetY')+'px';
			drago.style.left=kWindow.mousePos.x-drago.getAttribute('mouseOffsetX')+'px';
			flyingOver=getFlyingOver();
			if(flyingOver) onDrag(dragObject,flyingOver);
			}
		}
	
	function getFlyingOver() {
		for(var i=0;dropArea[i];i++) {
			var candidates=Array();
			if(dropTags.length==0) {
				candidates=dropArea[i].getElementsByTagName(containerTag);
				}
			else {
				for(var j=0;dropArea[i].getElementsByTagName("*")[j];j++) {
					var obj=dropArea[i].getElementsByTagName("*")[j];
					for(var c=0;dropTags[c];c++) {
						if(obj.tagName==dropTags[c]) candidates[candidates.length]=obj;
						}
					}
				}
			for(var j=candidates.length-1;candidates[j];j--) {
				var obj=candidates[j];
				var mousePos=kWindow.mousePos;
				mousePos.x=parseInt(mousePos.x);
				mousePos.y=parseInt(mousePos.y);
				if(obj.style.visibility!='hidden'&&mousePos.y>=parseInt(obj.getAttribute('ddTop'))&&mousePos.y<=parseInt(obj.getAttribute('ddBottom'))&&mousePos.x>=parseInt(obj.getAttribute('ddLeft'))&&mousePos.x<=parseInt(obj.getAttribute('ddRight'))) {
					return obj;
					}
				}
			var obj=dropArea[i];
			if(obj.style.visibility!='hidden'&&mousePos.y>=parseInt(obj.getAttribute('ddTop'))&&mousePos.y<=parseInt(obj.getAttribute('ddBottom'))&&mousePos.x>=parseInt(obj.getAttribute('ddLeft'))&&mousePos.x<=parseInt(obj.getAttribute('ddRight'))) {
				return obj;
				}
			}
		return null;
		}

	kAddEvent(document,"onmousedown",mouseDown);
	kAddEvent(document,"onmouseup",mouseUp);
	kAddEvent(window,"onload",kInitDrago);
	};

	
