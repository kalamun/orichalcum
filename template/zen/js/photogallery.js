/* (c) Kalamun 2009 - GPL 3 */

/* fadeshow */
k_Fadeshow=function() {
	var currentImg=0;
	var container=false;
	var fader=false;
	var Timer=false;
	var show="true";
	var imgs=Array();
	var height=0;

	this.setContainer=function(c) {
		container=document.getElementById(c);
		}

	this.init=function() {
		imgs=container.getElementsByTagName('DIV');
		for(var i=0;imgs[i];i++) {
			imgs[i].style.position="absolute";
			imgs[i].style.top=0;
			imgs[i].style.left=0;
			imgs[i].style.display='block';
			imgs[i].style.zIndex=30;
			o=1;
			if(i>0) o=0;
			imgs[i].style.opacity=o;
			imgs[i].style.MozOpacity=o;
			imgs[i].style.KhtmlOpacity=o;
			imgs[i].style.filter="alpha(opacity="+o*100+")";
			if(imgs[i].offsetHeight>height) height=imgs[i].offsetHeight;
			}
		container.style.height=height+"px";
		if(imgs.length>0) {
			Timer=setInterval(this.showImg,50);
			}
		}

	this.showImg=function() {
		var i=currentImg;
		var j=0;
		if(imgs[i+1]) j=i+1;
		var o=0;
		imgs[i].style.zIndex=31;
		imgs[j].style.zIndex=32;
		if(show=="true") {
			var o=parseFloat(imgs[i].style.opacity)-0.05;
			}
		if(o>1) o=1;
		else if(o<0) o=0;
		imgs[i].style.opacity=o;
		imgs[i].style.MozOpacity=o;
		imgs[i].style.KhtmlOpacity=o;
		imgs[i].style.filter="alpha(opacity="+o*100+")"; 
		imgs[j].style.opacity=1-o;
		imgs[j].style.MozOpacity=1-o;
		imgs[j].style.KhtmlOpacity=1-o;
		imgs[j].style.filter="alpha(opacity="+(1-o)*100+")"; 
		if(show=="true"&&o<=0) {
			show="pause";
			setTimeout(function() { show="false"; },3000);
			}
		if(show=="false") {
			show="true";
			currentImg++;
			if(currentImg>imgs.length-1) currentImg=0;
			imgs[i].style.zIndex=30;
			}
		}
	var showImg=this.showImg;
	}



/* photogallery */
function centerImgOnParent(img) {
	img.style.position='absolute';
	img.style.top=(img.parentNode.parentNode.offsetHeight-img.height)/2+'px';
	//alert(img.style.top);
	img.style.left=(img.parentNode.parentNode.offsetWidth-img.width)/2+'px';
	img.onmouseover=function() { img.parentNode.parentNode.className="phgthumbhover"; centerImgOnParent(img); }
	img.onmouseout=function() { img.parentNode.parentNode.className="phgthumb"; centerImgOnParent(img); }
	}
function selectPhgThumb(phgThumb) {
	if(phgThumb) {
		var container=document.getElementById('phgThumb'+phgThumb).parentNode;
		for(var i=0;container.childNodes[i];i++) {
			if(container.childNodes[i].nodeType!=3) {
				container.childNodes[i].className=container.childNodes[i].className.replace(' sel','');
				}
			}
		document.getElementById('phgThumb'+phgThumb).className+=' sel';
		}
	}
function showPhoto(id) {
	var container=document.getElementById('photoThumb'+id);
	var viewer=document.getElementById('phgViewer');
	viewer.innerHTML="";
	if(container&&viewer) {
		//selectPhgThumb(id);
		var url=container.getElementsByTagName('A')[0].href;
		viewer.style.display='block';
		img=document.createElement('IMG');
		img.setAttribute("src",url);
		img.style.position="relative";
		viewer.style.height=viewer.offsetHeight+"px";
		//img.style.top=viewer.offsetHeight+"px";
		img.style.top="-3000px";
		viewer.appendChild(img);
		arrowL=document.createElement('IMG');
		arrowL.setAttribute("src",TEMPLATEDIR+'img/phg_arrowL.png');
		arrowL.onclick=function() { prevPhoto(id); };
		viewer.appendChild(arrowL);
		arrowL.className="arrowL";
		arrowR=document.createElement('IMG');
		arrowR.setAttribute("src",TEMPLATEDIR+'img/phg_arrowR.png');
		arrowR.onclick=function() { nextPhoto(id); };
		viewer.appendChild(arrowR);
		arrowR.className="arrowR";
		img.onload=function() { fitImage(img); }
		img.onclick=hidePhoto;
		return false;
		}
	else return true;
	}
function nextPhoto(id) {
	var container=document.getElementById('photoThumb'+id).parentNode;
	var nextImg=null;
	var nextIsMine=null;
	for(var i=0;container.childNodes[i];i++) {
		if(container.childNodes[i].nodeType!=3) {
			if(nextImg==null||nextIsMine==true) {
				nextImg=container.childNodes[i];
				nextIsMine=false;
				}
			if(container.childNodes[i]==document.getElementById('photoThumb'+id)) nextIsMine=true;
			}
		}
	showPhoto(nextImg.id.substring(10));
	}
function prevPhoto(id) {
	var container=document.getElementById('photoThumb'+id).parentNode;
	var nextImg=null;
	var nextIsMine=null;
	for(var i=container.childNodes.length-1;i>=0;i--) {
		if(container.childNodes[i].nodeType!=3) {
			if(nextImg==null||nextIsMine==true) {
				nextImg=container.childNodes[i];
				nextIsMine=false;
				}
			if(container.childNodes[i]==document.getElementById('photoThumb'+id)) nextIsMine=true;
			}
		}
	showPhoto(nextImg.id.substring(10));
	}
function hidePhoto() {
	var viewer=document.getElementById('phgViewer');
	for(var i=0;viewer.childNodes[i];i++) {
		viewer.removeChild(viewer.childNodes[i],true);
		}
	viewer.style.display="none";
	}
function fitImage(img) {
	img.style.display="inline";
	img.style.top="0px";
	if(img.width>img.parentNode.offsetWidth) var maxwidth=img.parentNode.offsetWidth;
	else var maxwidth=img.width;
	img.height*=maxwidth/img.width;
	img.width=maxwidth;
	changeViewerHeight(img.height);
	}
function changeViewerHeight(h) {
	if(changeViewerHeightTimer!=false) clearTimeout(changeViewerHeightTimer);
	var viewer=document.getElementById('phgViewer');
	viewer.style.height=viewer.offsetHeight+((h-viewer.offsetHeight)/10)+'px';
	if(Math.ceil(viewer.offsetHeight/20)==Math.ceil(h/20)) viewer.style.height=h+'px';
	else if(viewer.offsetHeight!=h) changeViewerHeightTimer=setTimeout(function() { changeViewerHeight(h); },20);
	}
changeViewerHeightTimer=false;
/* fine photogallery */
