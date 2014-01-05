/* (c) Kalamun.org - GNU/GPL 3 */

function kNewFolder(dir) {
	var aj=new kAjax();
	aj.onSuccess(function() {
		});
	aj.send("post","ajax/mkdir.php","");
	}
	
function swapMembersListVisualization(inpt,idMembersList) {
	var membersList=document.getElementById(idMembersList);
	if(inpt.value=='restricted') membersList.style.display='block';
	else membersList.style.display='none';
	}

/* multiple upload */
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

		uploadConfig_xhr.open("POST",form.action);
		console.log(fd);
		uploadConfig_xhr.send(fd);
		if(document.getElementById('inputContainer')) document.getElementById('inputContainer').style.display='none';
		if(document.getElementById('uploadFileSave')) document.getElementById('uploadFileSave').style.display='none';
		if(document.getElementById('uploadFileUploading')) document.getElementById('uploadFileUploading').style.display='inline';

		return false;
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
	window.location.href=window.location.href.replace(/delete=.*/,"");
	}

function uploadFailed(evt) {
	window.location.href=window.location.href.replace(/delete=^\$+/,"");
	}

function uploadCanceled(evt) {
	}

function selectAll() {
	var c=document.getElementById('membersList');
	for(var i=0,cb=c.getElementsByTagName('INPUT');cb[i];i++) {
		cb[i].checked=true;
		}
	}
function unselectAll() {
	var c=document.getElementById('membersList');
	for(var i=0,cb=c.getElementsByTagName('INPUT');cb[i];i++) {
		cb[i].checked=false;
		}
	}
