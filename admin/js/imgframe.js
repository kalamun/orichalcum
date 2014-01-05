/* (c) Kalamun.org - GNU/GPL 3 */

function uploadImage(tabella,id) {
	window.parent.k_openIframeWindow(ADMINDIR+'inc/uploadImage.inc.php?tabella='+tabella+'&id='+id,"600px","400px");
	}
function updateImage(tabella,id,idimg) {
	window.parent.k_openIframeWindow(ADMINDIR+'inc/updateImage.inc.php?tabella='+tabella+'&id='+id+'&idimg='+idimg,"600px","400px");
	}
function uploadVideo(tabella,id) {
	window.parent.k_openIframeWindow(ADMINDIR+'inc/uploadVideo.inc.php?tabella='+tabella+'&id='+id,"600px","400px");
	}
function updateVideo(tabella,id,idvideo) {
	window.parent.k_openIframeWindow(ADMINDIR+'inc/updateVideo.inc.php?tabella='+tabella+'&id='+id+'&idvideo='+idvideo,"600px","400px");
	}
function uploadDocument(tabella,id) {
	window.parent.k_openIframeWindow(ADMINDIR+'inc/uploadDocument.inc.php?tabella='+tabella+'&id='+id,"600px","400px");
	}
function updateDocument(tabella,id,iddoc) {
	window.parent.k_openIframeWindow(ADMINDIR+'inc/updateDocument.inc.php?tabella='+tabella+'&id='+id+'&iddoc='+iddoc,"600px","400px");
	}

function showSelectedFiles(input) {
	var output="";
	for(var i=0;input.files[i];i++) {
		var file=input.files[i];
		var fileSize=0;
		if(file.size>1024*1024) fileSize=(Math.round(file.size*100/(1024*1024))/100).toString()+'Mb';
		else fileSize=(Math.round(file.size*100/1024)/100).toString()+'Kb';
		output+='<div>'+file.name+' ('+fileSize+')</div>';
		}
	if(document.getElementById('fileList')) document.getElementById('fileList').innerHTML=output;
	}

/*insert images into page*/
function insertImg(refid,idimg,type,url) {
	window.parent.kTxtArea[refid].insertImg(idimg,type,url);
	window.parent.k_closeIframeWindow();
	}


/* UPLOAD */
var uploadConfig_refid=false;
var uploadConfig_forcerefresh=false;
var uploadConfig_onSuccessMsg=false;
var uploadConfig_xhr=false;

function uploadFile(form) {
	if(form.action) {
		uploadConfig_xhr=new XMLHttpRequest();
		var fd=(form.getFormData?form.getFormData():new FormData(form));

		if(document.getElementById('progressNumber')) uploadConfig_xhr.upload.addEventListener("progress",uploadProgress,false);
		uploadConfig_xhr.addEventListener("load",uploadComplete,false);
		uploadConfig_xhr.addEventListener("error",uploadFailed,false);
		uploadConfig_xhr.addEventListener("abort",uploadCanceled,false);

		if(form.action.indexOf('forcerefresh=true')>=0) uploadConfig_forcerefresh=true;
		uploadConfig_xhr.open("POST",form.action);
		uploadConfig_xhr.send(fd);
		document.getElementById('image').style.display='none';
		}
	}

function uploadProgress(evt) {
	if(evt.lengthComputable) {
		var percentComplete=Math.round(evt.loaded*100/evt.total);
		document.getElementById('progressNumber').value='Please wait... '+percentComplete.toString()+'%';
		}
	else document.getElementById('progressNumber').value='Please wait...';
	}

function uploadComplete(evt) {
	if(uploadConfig_forcerefresh==true&&window.parent&&uploadConfig_refid==false) window.parent.location.reload();
	else {
		if(uploadConfig_forcerefresh==true&&uploadConfig_refid) window.parent.document.getElementById(uploadConfig_refid).src=window.parent.document.getElementById(uploadConfig_refid).src; //iframe
		if(uploadConfig_onSuccessMsg) {
			window.parent.b3_openMessage(uploadConfig_onSuccessMsg,true);
			window.parent.k_closeIframeWindow();
			}
		else {
			document.body.innerHTML=uploadConfig_xhr.responseText;
			}
		}
	}

function uploadFailed(evt) {
	document.getElementById('image').style.display='block';
	}

function uploadCanceled(evt) {
	}
