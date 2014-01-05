/* (c) Kalamun 2009 - GPL 3 */

/* lightbuzz */
kLightbuzz=function() {
	var container=document.body;
	this.setContainer=function(c) {
		container=document.getElementById(c);
		}

	this.init=function() {
		for(var i=0;container.getElementsByTagName('A')[i];i++) {
			var elm=container.getElementsByTagName('A')[i];
			if(elm.rel&&elm.rel=="lightbuzz") elm.onclick=this.onClickHandler;
			}
		}
	this.onClickHandler=function() {
		openIPopUp(this.href);
		return false;
		}
	function openIPopUp(url,w,h) {
		var msg=document.createElement('DIV');
		msg.id='lightBuzzIPopUp';
		msg.style.position='absolute';
		msg.style.top='-3000px';
		msg.style.left='-3000px';
		msg.style.zIndex='100';
		document.body.appendChild(msg);
		var img=document.createElement('IMG');
		msg.onclick=closeIPopUp;
		img.src=url;
		img.onload=centerIPopUpOnScreen;
		msg.appendChild(img);
		var bkg=document.createElement('DIV');
		bkg.id='lightBuzzIPopUpBkg';
		bkg.style.cssText='height:'+kWindow.pageHeight()+'px;';
		document.body.appendChild(bkg);
		bkg.onclick=closeIPopUp;
		}
	function centerIPopUpOnScreen() {
		msg=document.getElementById('lightBuzzIPopUp');
		msg.style.top=((kWindow.clientHeight()-msg.offsetHeight)/2+kWindow.scrollTop())+'px';
		msg.style.left=(kWindow.clientWidth()-msg.offsetWidth)/2+'px';
		}
	function closeIPopUp() {
		if(document.getElementById('lightBuzzIPopUp')) {
			var msg=document.getElementById('lightBuzzIPopUp');
			msg.parentNode.removeChild(msg);
			}
		if(document.getElementById('lightBuzzIPopUpBkg')) {
			var bkg=document.getElementById('lightBuzzIPopUpBkg');
			bkg.parentNode.removeChild(bkg);
			};
		}
	}
function kLightbuzzInit() {
	var kLightbuzzTmp=new kLightbuzz;
	kLightbuzzTmp.init();
	}
kAddEvent(window,"onload",kLightbuzzInit);
