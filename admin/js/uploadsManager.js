/*
This class is used inside admin/inc/uploadsManager.inc.php to manage drag&drop uploads,
image browsing, sorting etc etc.
It is usually opened inside an iframe.
When submit button is pressed, the submit action (passed by init() function) is called, passing an array with the json details for each selected image
*/

function kUploads()
{
	var counter=0,isLoading=false,objectArchive=Array();
	var maxFileSize=25000000,isLoading=false,searchKey="",orderKey="";
	var form=null,fileList=null,formData=null,queue=Array(),currentFile=0,uploadFileProgress=null,search=null,orderby=null,onsubmit=null,onsubmit2=null,limit=1000,lastSelectedItem=null,internetUploadDialog=null,editDialog=null,shiftPressed=false,fileType="",timer=null;
	
	var init=function(formObject,dropObject,browseObject,editorObject,internetUploadObject,fileListContainer,searchContainer,orderbyContainer,onsubmitContainer,onsubmitContainer2,onsubmitAction,onsubmitAction2,limitNumber) {
		if(!formObject) return false;
		if(!dropObject) return false;
		if(!browseObject) return false;
		if(!editorObject) return false;
		form=formObject;
		fileList=fileListContainer;
		fileType=document.getElementById('fileType').value;

		editDialog=editorObject;
		kAddEvent(editDialog.querySelector('.closeWindow'),"click",closeEditDialog);
		editDialog.getElementsByTagName('iframe')[0].src=ADMINDIR+'inc/uploadsManager_loading.inc.php';

		if(internetUploadObject)
		{
			internetUploadDialog=internetUploadObject;
			kAddEvent(internetUploadDialog.querySelector('.closeWindow'),"click",closeInternetUploadDialog);
			internetUploadDialog.getElementsByTagName('iframe')[0].src=ADMINDIR+'inc/uploadsManager_loading.inc.php';
		}

		if(limitNumber) limit=parseInt(limitNumber);
		if(onsubmitAction) onsubmit=onsubmitAction;
		if(onsubmitAction2) onsubmit2=onsubmitAction2;

		shiftPressed=false;
		kAddEvent(document.body,"keydown",onDocumentKeyPress);
		kAddEvent(document.body,"keyup",onDocumentKeyUp);
		
		kAddEvent(dropObject,"dragenter",onOver);
		kAddEvent(dropObject,"dragleave",onOut);
		kAddEvent(dropObject,"dragover",onOver);
		kAddEvent(dropObject,"drop",onDrop);
		kAddEvent(browseObject,"change",onDrop);
		if(document.getElementById('MAX_FILE_SIZE')) maxFileSize=parseInt(document.getElementById('MAX_FILE_SIZE').value);
		
		if(searchContainer) {
			search=searchContainer;
			kAddEvent(search,"keypress",onSearchKeyPress);
		}
		
		if(orderbyContainer) {
			orderby=orderbyContainer;
			for(var i=0,c=orderby.getElementsByTagName('A');c[i];i++) {
				kAddEvent(c[i],"click",onOrderByClick);
			}
		}
		
		if(onsubmitContainer) kAddEvent(onsubmitContainer,"click",onSubmit);
		if(onsubmitContainer2) kAddEvent(onsubmitContainer2,"click",onSubmit2);
		
		}
	this.init=init;

	// register if special keys are pressed
	var onDocumentKeyPress=function(e)
	{
		if(e.keyCode=="16") shiftPressed=true;
	}
	var onDocumentKeyUp=function(e)
	{
		if(e.keyCode=="16") shiftPressed=false;
	}

	// empty the <ul> that contains file list
	var cleanFileList=function()
	{
		if(fileList) 
		{
			fileList.innerHTML='';
			counter=0;
		}
		
	}

	var loadImages=function(start,itemsperpage,conditions)
	{
		if(isLoading==false) // prevend double calls
		{
			isLoading=true;
			if(!start) start=counter;
			if(!itemsperpage) itemsperpage=30;
			if(!conditions) conditions="";
			var aj=new kAjax();
			aj.onSuccess(printImages);
			aj.onFail( function() { alert('loading error :-('); } );
			aj.send('post','../inc/ajax/uploadHandler.php','&action=getImageList&fileType='+encodeURIComponent(fileType)+'&start='+parseInt(start)+'&limit='+parseInt(itemsperpage)+'&orderby='+orderKey+'&search='+encodeURIComponent(searchKey)+'&conditions='+encodeURIComponent(conditions));
			counter+=itemsperpage;
		}
	}
	this.loadImages=loadImages;

	// reload a previously loaded item, then print it instead the old one
	var reloadImage=function(id,select)
	{
		start=0;
		var itemsperpage=1;
		conditions="`idimg`='"+parseInt(id)+"'";
		var aj=new kAjax();
		aj.onSuccess( function(html,xml) { printImages(html,xml,select); } );
		aj.onFail( function() { alert('loading error :-('); } );
		aj.send('post','../inc/ajax/uploadHandler.php','&action=getImageList&fileType='+encodeURIComponent(fileType)+'&start='+parseInt(start)+'&limit='+parseInt(itemsperpage)+'&orderby='+orderKey+'&search=&conditions='+encodeURIComponent(conditions));
	}
	this.reloadImage=reloadImage;
	
	// add ajax-loaded images to the list
	var printImages=function(html,xml,select)
	{
		if(!select) select=false;
		for(var i=0, imgs=html.split("\n"); imgs[i]; i++)
		{
			img=imgs[i].split("\t");
			var li=createImage(img);
			if(fileList)
			{
				if(document.getElementById('img'+img[0]))
				{
					// if the li already exists, replace its content with the new one
					oldli=document.getElementById('img'+img[0]);
					oldli.parentNode.insertBefore(li,oldli);
					oldli.parentNode.removeChild(oldli);
				} else {
					// append
					fileList.appendChild(li);
				}
				if(select) selectImage(null,li);
			}
		}
		isLoading=false;
	}
	
	// create and return image element
	var createImage=function(img)
	{
		var id=img[0],
			dir=img[1],
			filename=img[2],
			thumbnail=img[3],
			size=img[4],
			caption=img[5];

		// add to local archive
		objectArchive[id]={
			'id': id,
			'dir': dir,
			'filename': filename,
			'thumbnail': thumbnail,
			'size': size,
			'caption': caption
			}

		var li=document.createElement('LI');
		li.id='img'+id;
		
		var div=document.createElement('DIV');
		div.className='bkg';
		div.style.backgroundImage="url('"+escape(dir+thumbnail)+"')";
		
		var h3=document.createElement('H3');
		h3.appendChild(document.createTextNode(filename));
		
		edit=document.createElement('DIV');
		kAddEvent(edit,"click",openEditDialog);
		edit.appendChild(document.createTextNode(kaDictionary.Edit));
		edit.className='edit';
		
		var inpt=document.createElement('INPUT');
		inpt.id='caption'+id;
		inpt.placeholder=kaDictionary.WriteCaptionHere;
		inpt.value=caption;
		kAddEvent(inpt,"keypress",onCaptionKeypress);
		kAddEvent(inpt,"blur",onCaptionBlur);
		kAddEvent(inpt,"click",onCaptionClick,true);
		
		li.appendChild(div);
		li.appendChild(h3);
		li.appendChild(edit);
		li.appendChild(inpt);
		kAddEvent(li,"click",selectImage);

		return li;
	}

	var selectImage=function(e,img)
	{
		e.preventDefault();
		window.getSelection().removeAllRanges();

		if(!img) img=this;
		
		if(lastSelectedItem && shiftPressed) // multiple selection with Shift
		{
			// get the status of last selected item, and apply it to all the other items
			var status = (lastSelectedItem.className.indexOf('selected')>=0 ? 'selected' : '');
			var start = -1;
			var end = -1;
			for(var i=0; fileList.childNodes[i]; i++)
			{
				if(fileList.childNodes[i]==img) start=i;
				else if(fileList.childNodes[i]==lastSelectedItem) end=i;
			}

			if(end<start) end = [start, start = end][0]; // swap values
			if(status=='selected' && end-start+fileList.querySelectorAll('.selected').length>=limit) end=start+limit-fileList.querySelectorAll('.selected').length;
			if(start==-1 || end==-1 || end==start) return false;
			
			for(var i=start; i<=end; i++)
			{
				fileList.childNodes[i].className=fileList.childNodes[i].className.replace('selected','');
				if(status=='selected') fileList.childNodes[i].className=fileList.childNodes[i].className+' selected';
			}

			lastSelectedItem=img;
			
		}

		else if(img.className.indexOf('selected')>=0)
		{
			img.className=img.className.replace('selected','');

		} else {
			// if the maximum number of selected items was already scored, deselect last selected item
			if(fileList.querySelectorAll('.selected').length>=limit && lastSelectedItem)
			{
				lastSelectedItem.className=lastSelectedItem.className.replace('selected','');
			}
			img.className=img.className+' selected';
		}
		
		lastSelectedItem=img;
	}
	
	var onScrollHandler=function()
	{
		var scrollTop = fileList.scrollTop;
		var clientHeight = fileList.offsetHeight;
		var pageHeight = fileList.scrollHeight;
		if(pageHeight-clientHeight-scrollTop<100) loadImages();
	}
	kAddEvent(window,"scroll",onScrollHandler);

	var onOver=function(e)
	{
		e.stopPropagation();
		e.preventDefault();
		this.className='hover';
	}
	var onOut=function(e)
	{
		e.stopPropagation();
		e.preventDefault();
		this.className='';
	}
	var onDrop=function(e)
	{
		e.stopPropagation();
		e.preventDefault();
		this.className='';

		var files=e.target.files||e.dataTransfer.files;
		/* Multiple Uploads */
		for(i=0;i<files.length;++i)
		{
			if (!files[i].type.match(/.*/)) continue;
			if (files[i].size>maxFileSize) continue;
			addFileToQueue(files[i]);
		}
		if(isLoading==false) startUploading();
	}

	var onSearchKeyPress=function(e)
	{
		if(e.keyCode==13)
		{
			e.preventDefault();
			searchKey=search.value;
			cleanFileList();
			loadImages();
		}
	}

	var onCaptionClick=function(e)
	{
		e.preventDefault();
		if(this.parentNode.className.indexOf('selected')<0) selectImage(null, this.parentNode);
	}

	var onCaptionKeypress=function(e)
	{
		if(e.keyCode==13)
		{
			e.preventDefault();
			document.body.focus();
			saveCaption(this);
		}
	}
	
	var onCaptionBlur=function(e)
	{
		saveCaption(this);
	}
	
	var saveCaption=function(inpt)
	{
		var caption=inpt.value;
		var id=parseInt(inpt.id.replace('caption',''));
		if(id>0)
		{
			var saving=document.createElement('DIV');
			saving.className='status';
			saving.id='saving'+id;
			saving.appendChild(document.createTextNode(kaDictionary.Saving));
			inpt.parentNode.appendChild(saving);
			var aj=new kAjax();
			aj.onSuccess(captionSaved);
			aj.onFail( function() { alert('error while saving caption :-('); } );
			aj.send('post','../inc/ajax/uploadHandler.php','&action=saveCaption&id='+parseInt(id)+'&caption='+encodeURIComponent(caption));
		}
	}
	
	var captionSaved=function(html,xml)
	{
		id=parseInt(html);
		if(objectArchive[id]) objectArchive[id].caption=document.getElementById('caption'+id).value;
		var li=document.getElementById('saving'+id);
		if(li) li.parentNode.removeChild(li,true);
	}

	var onOrderByClick=function(e)
	{
		e.preventDefault();
		for(var i=0, c=orderby.getElementsByTagName('A'); c[i]; i++) {
			c[i].className='';
			}
		this.className="selected";
		orderKey="`"+this.getAttribute("ref")+"`";
		if(orderKey=="`creation_date`") orderKey+=" DESC";
		cleanFileList();
		loadImages();
	}

	var addFileToQueue=function(file)
	{
		var idqueue=queue.length;
		queue[idqueue]=file;
		var li=document.createElement('LI');
		li.id='upload'+idqueue;
		var newFile=document.createElement('DIV');
		newFile.className='bkg';
		var fileName=document.createElement('H3');
		fileName.appendChild(document.createTextNode(file.name));
		var status=document.createElement('DIV');
		status.appendChild(document.createTextNode('...'));
		status.className='status';
		
		var reader = new FileReader();
        reader.onload = function (e) {
            newFile.style.backgroundImage="url('"+e.target.result+"')";
			}
        reader.readAsDataURL(file);
		li.appendChild(newFile);
		li.appendChild(fileName);
		li.appendChild(status);
		if(fileList.childNodes[0]) fileList.insertBefore(li,fileList.childNodes[0]);
		else fileList.appendChild(li);
	}
	
	/* add a file to queue, to be uploaded via internet - copy is a boolean value that indicate if the file must be copied to the server or hotlinked */
	var addRemoteFileToQueue = function (url, copy)
	{
		file = {
			url: url,
			copy: copy
			}
		var idqueue=queue.length;
		queue[idqueue]=file;
		var li=document.createElement('LI');
		li.id='upload'+idqueue;
		var newFile=document.createElement('DIV');
		newFile.className='bkg';
		var fileName=document.createElement('H3');
		fileName.appendChild(document.createTextNode(url));
		var status=document.createElement('DIV');
		status.appendChild(document.createTextNode('...'));
		status.className='status';
		
		li.appendChild(newFile);
		li.appendChild(fileName);
		li.appendChild(status);
		if(fileList.childNodes[0]) fileList.insertBefore(li,fileList.childNodes[0]);
		else fileList.appendChild(li);
		
		if(isLoading==false) startUploading();
	}
	this.addRemoteFileToQueue = addRemoteFileToQueue;

	var startUploading=function()
	{
		if(isLoading==false&&queue[currentFile]) uploadFile(queue[currentFile]);
	}

	var uploadFile=function(file)
	{
		// normal upload
		if(file.name)
		{
			var xhr=new XMLHttpRequest();
			if(xhr.upload && file.size<=maxFileSize)
			{
				isLoading=true;

				// progress bar
				uploadFileProgress=document.createElement('DIV');
				uploadFileProgress.className='progressBar';
				var li=document.getElementById('upload'+currentFile);
				li.removeChild(li.querySelector('.status'),true);
				li.appendChild(uploadFileProgress);
				kAddEvent(xhr.upload,"progress", function(e) {
					var pc=parseInt(e.loaded/e.total*100);
					uploadFileProgress.style.width=pc+"%";
					},false);

				// file received/failed
				xhr.onreadystatechange=function(e)
				{
					if(xhr.readyState===4)
					{
						if(xhr.status==200)
						{
							response=xhr.responseText;
							response=response.split("|"); // response is "filetype|id|filename"
							var id=parseInt(response[1]);
							if(id)
							{
								var li=document.getElementById('upload'+currentFile);
								if(fileType.indexOf(response[0])<0) // filetype is not displayed
								{
									li.parentNode.removeChild(li);
								} else {
									li.id='img'+id;
									reloadImage(id,true);
								}
							}
							currentFile++;
						}
						else alert('Unknown error while uploading');
						isLoading=false;
						startUploading(); // upload next file
					}
				};

				// start upload
				xhr.open("POST",form.action,true);
				xhr.setRequestHeader("X-Filename",file.name);
				xhr.send(file);
			}
			
		// internet upload
		} else if(file.url) {
			isLoading=true;

			// progress bar
			uploadFileProgress=document.createElement('DIV');
			uploadFileProgress.className='progressBar';
			var li=document.getElementById('upload'+currentFile);
			li.removeChild(li.querySelector('.status'),true);
			li.appendChild(uploadFileProgress);

			console.log('start');

			var aj = new kAjax();
			aj.onSuccess(remoteUploadComplete);
			aj.onFail( function() { alert('loading error :-('); remoteUploadComplete(""); } );
			aj.send('post','../inc/ajax/uploadHandler.php','&action=startInternetUpload&url='+encodeURIComponent(file.url)+'&copy='+encodeURIComponent(file.copy));

			setProgressBarStatus("0");
			timer = setInterval(remoteUploadProgress, 2000);
		}
	}
	
	var remoteUploadProgress = function()
	{
		var aj = new kAjax();
		aj.onSuccess(setProgressBarStatus);
		//aj.send('post','../inc/ajax/uploadHandlerProgress.php','&action=getInternetUploadProgress&url='+encodeURIComponent(file.url));
		aj.send('post',ADMINDIR+'../arch/tmp/progress_'+file.url.substring(file.url.lastIndexOf('/') + 1)+'.txt');
	}
	
	var setProgressBarStatus = function(response)
	{
		if(response=="end")
		{
			remoteUploadComplete("");
			response=100;
		}
		uploadFileProgress.style.width = parseInt(response)+"%";
	}

	var remoteUploadComplete = function(response)
	{
		if(timer) clearInterval(timer);
		timer = null;

		response=response.split("|"); // response is "filetype|id|filename"
		var id=parseInt(response[1]);
		if(id)
		{
			var li=document.getElementById('upload'+currentFile);
			if(fileType.indexOf(response[0])<0) // filetype is not displayed
			{
				li.parentNode.removeChild(li);
			} else {
				li.id='img'+id;
				reloadImage(id,true);
			}
		}
		currentFile++;
		isLoading=false;
		startUploading(); // upload next file
	}

	var onSubmit=function() {
		var selected=Array();
		for(var i=0, c=fileList.querySelectorAll('.selected'); c[i]; i++)
		{
			selected[selected.length]=objectArchive[c[i].id.substr(3)];
		}
		if(onsubmit && selected.length>0) onsubmit(selected);
		}
	var onSubmit2=function() {
		var selected=Array();
		for(var i=0, c=fileList.querySelectorAll('.selected'); c[i]; i++)
		{
			selected[selected.length]=objectArchive[c[i].id.substr(3)];
		}
		if(onsubmit2 && selected.length>0) onsubmit2(selected);
		}
	
	/* INTERNET UPLOAD */
	var openInternetUploadDialog=function()
	{
		internetUploadDialog.getElementsByTagName('iframe')[0].src=ADMINDIR+'inc/uploadsManager_internetupload.inc.php?id='+this.parentNode.id;
		internetUploadDialog.className='open';
	}
	this.openInternetUploadDialog=openInternetUploadDialog;

	var closeInternetUploadDialog=function()
	{
		internetUploadDialog.className='';
		internetUploadDialog.getElementsByTagName('iframe')[0].src=ADMINDIR+'inc/uploadsManager_loading.inc.php';
	}
	this.closeInternetUploadDialog=closeInternetUploadDialog;

	/* EDIT */
	var openEditDialog=function()
	{
		editDialog.getElementsByTagName('iframe')[0].src=ADMINDIR+'inc/uploadsManager_edit.inc.php?id='+this.parentNode.id;
		editDialog.className='open';
	}
	this.openEditDialog=openEditDialog;

	var closeEditDialog=function()
	{
		editDialog.className='';
		editDialog.getElementsByTagName('iframe')[0].src=ADMINDIR+'inc/uploadsManager_loading.inc.php';
	}
	this.closeEditDialog=closeEditDialog;

}
