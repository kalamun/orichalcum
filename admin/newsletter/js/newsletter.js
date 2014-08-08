function processQueue() {
	var w=400;
	var h=250;
	var l=(screen.width-w)/2;
	var t=(screen.height-h)/2;
	var popup=window.open("","processQueue","status=0,height="+h+",width="+w+",top="+t+",left="+l+",resizable=0");
	if(popup.location.href="about:blank") {
		popup.location.href=ADMINDIR+"newsletter/ajax/processQueue.php";
		}
	popup.focus();
	return popup;
	}

function openPreviewPopup() {
	txts.getArea(0).swapDesignMode();
	txts.getArea(0).swapDesignMode();
	var template=document.getElementById('template').value;
	var subject=document.getElementById('subject').value;
	var message=document.getElementById('message').value;
	kOpenIPopUp('ajax/previewManager.php','&subject='+encodeURIComponent(subject),'700px','80%');
	}
