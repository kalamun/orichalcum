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
	var form=null,fileList=null,formData=null,queue=Array(),currentFile=0,uploadFileProgress=null,search=null,orderby=null,onsubmit=null,onsubmit2=null,limit=1000,lastSelectedItem=null,editDialog=null;
	
	var init=function(formObject,dropObject,browseObject,editorObject,fileListContainer,searchContainer,orderbyContainer,onsubmitContainer,onsubmitContainer2,onsubmitAction,onsubmitAction2,limitNumber) {
		if(!formObject) return false;
		if(!dropObject) return false;
		if(!browseObject) return false;
		if(!editorObject) return false;
		form=formObject;
		fileList=fileListContainer;

		editDialog=editorObject;
		kAddEvent(editDialog.querySelector('.closeWindow'),"click",closeEditDialog);
		editDialog.getElementsByTagName('iframe')[0].src=ADMINDIR+'inc/uploadsManager_loading.inc.php';

		if(limitNumber) limit=parseInt(limitNumber);
		if(onsubmitAction) onsubmit=onsubmitAction;
		if(onsubmitAction2) onsubmit2=onsubmitAction2;

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
			aj.send('post','../inc/ajax/uploadHandler.php','&action=getImageList&start='+parseInt(start)+'&limit='+parseInt(itemsperpage)+'&orderby='+orderKey+'&search='+encodeURIComponent(searchKey)+'&conditions='+encodeURIComponent(conditions));
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
		aj.send('post','../inc/ajax/uploadHandler.php','&action=getImageList&start='+parseInt(start)+'&limit='+parseInt(itemsperpage)+'&orderby='+orderKey+'&search=&conditions='+encodeURIComponent(conditions));
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
		
		li.appendChild(div);
		li.appendChild(h3);
		li.appendChild(edit);
		li.appendChild(inpt);
		kAddEvent(li,"click",selectImage);

		return li;
	}

	var selectImage=function(e,img)
	{
		if(!img) img=this;
		
		if(img.className.indexOf('selected')>=0)
		{
			img.className=img.className.replace('selected','');
		} else {
			// if the maximum number of selected items was already scored, deselect last selected item
			if(fileList.querySelectorAll('.selected').length>=limit && lastSelectedItem) {
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


	var onOver=function(e) {
		e.stopPropagation();
		e.preventDefault();
		this.className='hover';
		}
	var onOut=function(e) {
		e.stopPropagation();
		e.preventDefault();
		this.className='';
		}
	var onDrop=function(e) {
		e.stopPropagation();
		e.preventDefault();
		this.className='';

		var files=e.target.files||e.dataTransfer.files;
		/* Multiple Uploads */
		for(i=0;i<files.length;++i) {
			if (!files[i].type.match(/.*/)) {
				// display some message
				continue;
				}
			if (files[i].size>maxFileSize) {
				// display some message
				continue;
				}
			addFileInQueue(files[i]);
			}
		if(isLoading==false) startUploading();
		}

	var onSearchKeyPress=function(e) {
		if(e.keyCode==13) {
			e.preventDefault();
			searchKey=search.value;
			cleanFileList();
			loadImages();
			}
		}

	var onCaptionKeypress=function(e)
	{
		if(e.keyCode==13) {
			e.preventDefault();
			document.body.focus();
			document.body.focus();
			var caption=this.value;
			var id=parseInt(this.id.replace('caption',''));
			if(id>0)
			{
				var saving=document.createElement('DIV');
				saving.className='status';
				saving.id='saving'+id;
				saving.appendChild(document.createTextNode(kaDictionary.Saving));
				this.parentNode.appendChild(saving);
				var aj=new kAjax();
				aj.onSuccess(captionSaved);
				aj.onFail( function() { alert('error while saving caption :-('); } );
				aj.send('post','../inc/ajax/uploadHandler.php','&action=saveCaption&id='+parseInt(id)+'&caption='+encodeURIComponent(caption));
			}
		}
	}
	
	var captionSaved=function(html,xml)
	{
		id=parseInt(html);
		console.log(id);
		if(objectArchive[id]) objectArchive[id].caption=document.getElementById('caption'+id).value;
		var li=document.getElementById('saving'+id);
		if(li) li.parentNode.removeChild(li,true);
	}

	var onOrderByClick=function(e) {
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

	var addFileInQueue=function(file) {
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

	var startUploading=function() {
		if(isLoading==false&&queue[currentFile]) uploadFile(queue[currentFile]);
		}

	var uploadFile=function(file) {
		var xhr=new XMLHttpRequest();
		if(xhr.upload&&file.size<=maxFileSize) {
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
			xhr.onreadystatechange=function(e) {
				if(xhr.readyState===4) {
					if(xhr.status==200) {
						response=xhr.responseText;
						response=response.split("|"); // response is "filetype|id|filename"
						var id=parseInt(response[1]);
						if(id)
						{
							var li=document.getElementById('upload'+currentFile);
							li.id='img'+id;
							reloadImage(id,true);
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
