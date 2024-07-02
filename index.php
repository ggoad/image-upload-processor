<!DOCTYPE html>
<html>
<head>
	<script>
		/* 
			configure this for the size of chunk
				to split the files into to be sent to the server
				
			If you are not uploading large enough images to observe
				the chunking, 
				then reduce this configured size
				
			If the uploads have too many chunks increase this. 
			
			I like 1024*1024
		*/
		var imageChunkSize=64*64;
	</script>
	<script src="js/ob2.js"></script>
	<script src="js/el.js"></script>
	<script src="js/elFetch.js"></script>
	
	<script src="js/basicModal.js"></script>
		<link rel="stylesheet" href="js/basicModal.css"/>
		
	<script src="js/softNotification.js"></script>
		<link rel="stylesheet" href="js/softNotification.css"/>
	
	<style>
		html{
			overflow-y:scroll;
		}
		input, label,button{
			margin:10px;
		}
		
		nav button{
			margin:4px;
			padding:3px;
			
		}
		#selectedButton{
			border:3px solid orange;
			color:white;
			background-color:black;
		}
		#articleCatcher:empty::before{
			content:"(Articles will appear here when you've made one.)";
			display:inline-block;
			margin:10px;
		}
		
		main>div{
			display:none;
		}
		.formActive #formContainer, .viewerActive #imgListContainer{
			display:block;
		}
		

		#submit{
			background-color:lightGreen;
			margin:40px;
			font-size:125%;
			border-radius:25%;
			padding:10px;
			border:6px outset orange;
			cursor:pointer;
			position:relative;
			top:0;
			transition:top 500ms, box-shadow 500ms, background-color 100ms;
			box-shadow:0 0 0 black;
		}
		#submit:hover{
			top:-3px;
			box-shadow: 0px 5px 5px #888888;
		}
		#submit:active{
			border-style:inset;
			background-color:#50CC50
		}
		
		input:invalid~#submit{
			display:none;
		}
		
		details, img{
			margin:15px;
		}
		.spinner{
			
			animation: spin 4s alternate infinite;
			display:inline-block;
		}
		@keyframes spin{
			0% {
			  transform: rotateX(0deg) rotateZ(0deg);
			}
			50% {
			  transform: rotateX(180deg) rotateZ(180deg);
			}
			100% {
			  transform: rotateX(360deg) rotateZ(360deg);
			}
		}
	</style>
</head>
<body>
	<h1>Image Upload Demonstation</h1>
	<nav onclick="SelectButton(event.target);">
		<h2>Select an Article</h2>
		<div id="articleCatcher"><?php 
		
			require_once("php_library/removeRelDirs.php");
			
			$sd=RemoveRelDirs(@scandir("article-images") ?: []);
			
			sort($sd, SORT_NUMERIC);
			foreach($sd as $s)
			{
				echo "<button>".htmlspecialchars($s)."</button>";
			}
				
			
		?></div>
		
		<button id="selectedButton">New Article</button>
	</nav>
	<br>
	<main class="formActive">
		<div id="formContainer">
			<label for="imageName">
			Readable Name: 
			</label>
			<input id="imageName" name="imageName" required />
			
			<br>
			<label for="image">
				Select and image to upload: <br>
			</label>
				<input type="file" accept=".png,.jpg,.jpeg,.webp" id="image" name="image" required />
			<br>
			<button id="submit" onclick="MakeArticle()">Submit</button>
		</div>
		<div id="imgListContainer"></div>
	</main>
	
	<script>
		/* There are helper functions at the bottom of this script  */
		var fileInput=document.getElementById("image"),
			nameInput=document.getElementById("imageName"),
			imgListContainer=document.getElementById("imgListContainer"),
			submitter=document.getElementById('submit');
		
		
		var mod;
		var progressLabel2, labelHeading, progressLabel;
		
		function MakeArticle(){
			submitter.setAttribute('disabled', '');
			
		    mod=BasicModal();
				/* Curry to re-enable submit button upon modal close */
				mod.OLDCLOSE=mod.CLOSE;
				mod.CLOSE=function(){
					submitter.removeAttribute('disabled');
					this.OLDCLOSE();
				}
			_el.REMOVE(mod.closer);
			_el.APPEND(mod.client, [
				labelHeading=_el.CREATE('h3','','',{},["Generating Article Id"]),
				progressLabel=_el.CREATE('div'),
				progressLabel2=_el.CREATE('div')
			]);
			
			ElFetch(progressLabel, ProgressMessage("Please Wait"), "getId.php",{},"json",{
				success:function(jsn){
					
					UploadImage(jsn.id);
				},
				fail:function(){
					_el.EMPTY(progressLabel);
					_el.APPEND(progressLabel, [
						"There was an error, please try again later. ",
						_el.CREATE('button','','',{
							onclick:function(){
								mod.CLOSE();
							}
						},["Click Here to Close"])
					]);
				}
			});
		}
		
		
		function UploadImage(artPk){
			var t=this;
			var dynImCount, dynChCount;
			
			var mainImage={
				file:fileInput.files[0],
				mime:fileInput.files[0].type,
				name:nameInput.value,
				sub:'source'
			};
			var newImgSizes=[];
			var imIndex=0;
			
			_el.EMPTY(labelHeading);
				_el.APPEND(labelHeading, ProgressMessage("Processing Image: "));
			
			var reader= new FileReader();
			reader.onload=function(e){
				var srcImg=_el.CREATE('img','','',{src:e.target.result, onload:function(){
					
					var canv=_el.CREATE('canvas');
					var ctx=canv.getContext('2d');
					var w=srcImg.naturalWidth;
					var h=srcImg.naturalHeight;
					canv.width=w; canv.height=h;
					
					
					var conv=1;
					
					/* Converts the next size of image (the cutoff is 50 px) */
					function ConvertIt(){
						if(w/conv < 50){
							newImgSizes.unshift(mainImage);
							
							/* Change label and start uploading */
							
							_el.EMPTY(progressLabel);
								_el.APPEND(progressLabel, [
									"Images: ",
									dynImCount=_el.CREATE('span','','',{},["0"]),
									" / "+newImgSizes.length
								]);
							_el.EMPTY(labelHeading);
								_el.APPEND(labelHeading, ProgressMessage("Uploading Images: "));
							return NextUpload();
						}
						conv*=2;
						var th=Math.floor(h/conv);
						var tw=Math.floor(w/conv);
						
						/* proportional rezizing */
						canv.width=tw; canv.height=th;
						
						ctx.clearRect(0,0,w,h);
						ctx.drawImage(srcImg, 0,0,tw,th);
						
						/*read the canvas after the resize */
						canv.toBlob(function(e){
							/* push new image onto stack */
							newImgSizes.push({
								file:e,
								mime:mainImage.mime,
								name:mainImage.name,
								sub:tw+"x"+th
							});
							
							/* if it's not a webp, convert to webp */
							if(mainImage.mime !== "image/webp"){
								canv.toBlob(function(e){
									newImgSizes.push({
										file:e,
										originalMime:mainImage.mime,
										mime:"image/webp",
										name:mainImage.name,
										sub:tw+"x"+th
									});
									ConvertIt();
								}, 'image/webp');
							}else{
								ConvertIt();
							}
						}, mainImage.mime);
					}
					
					/* Initial Conversion */
					if(['image/jpg', 'image/jpeg', 'image/png'].indexOf(mainImage.mime) > -1){
						ctx.clearRect(0,0,w,h);
						ctx.drawImage(srcImg,0,0,w,h);
						canv.toBlob(function(e){
							newImgSizes.push({
								file:e,
								originalMime:mainImage.mime,
								name:mainImage.name,
								sub:'source',
								mime:'image/webp'
							});
							ConvertIt();
						},'image/webp');
					}else{
						ConvertIt();
					}
				}});
			}
			reader.readAsDataURL(mainImage.file);
			
			/* Shifts next image off of stack */
			function NextUpload(){
				/* Update image count */ 
				_el.EMPTY(dynImCount);
				_el.APPEND(dynImCount, ""+imIndex);
				imIndex++;
				
				/* Initializes the next image */
				function GoForIt(med){
					_el.EMPTY(progressLabel2);
					
					ElFetch(_el.CREATE('div'),`Initializing ${med.type} Upload (${med.name}/${med.sub})`, 
						"initMedia.php", 
						{method:'GET'}, "json",{
							success:function(j){
								ContinueUpload(med, j.uploadToken);
							},
							fail:function(jsn){
								SoftNotification.Render("Failed: Upload Aborted "+jsn.msg);
								mod.CLOSE();
							}
						}
					);
				}
				
				if(newImgSizes.length){
					GoForIt(newImgSizes.shift());
				}else{
					Seal();
				}
				
			}
			
			/* Sends the next chunk of the current image */
			function ContinueUpload(med, uploadToken){
				if(!med.chunkTracker){
					/* initializes the chunk tracker if this is the first chunk */
					med.chunkTracker={
						fileSize:med.file.size,
						chunkSize:imageChunkSize,
						numChunks:Math.ceil(med.file.size/(imageChunkSize)),
						chunkIndex:0
					};
					_el.APPEND(progressLabel2, [
						"Chunks: ",
						dynChCount=_el.CREATE('span','','',{},["0"]),
						" / "+med.chunkTracker.numChunks
					]);
				}
				
				/* Update chunk message */
				_el.EMPTY(dynChCount);
				_el.APPEND(dynChCount, ""+med.chunkTracker.chunkIndex);
				
				/* Getting the actual chunk */
				var offset=med.chunkTracker.chunkIndex*med.chunkTracker.chunkSize;
				var chunk=med.file.slice(offset,offset+med.chunkTracker.chunkSize);
				med.chunkTracker.chunkIndex++;
				
				/* Construct form data */
				var fd=new FormData();
				fd.append('artPk',artPk);
				fd.append('mediaMime', med.mime);
				fd.append('originalMime', med.originalMime || '');
				fd.append('mediaBroadType', "Image");
				fd.append('mediaName', med.name);
				fd.append('mediaSub', med.sub);
				fd.append('dd', chunk);
				fd.append('uploadToken', uploadToken);
				fd.append('final', (med.chunkTracker.chunkIndex > med.chunkTracker.numChunks)?'1':'0');
				
				ElFetch(_el.CREATE('div') ,"Uploading "+med.type+": "+med.name+'/'+med.sub, 
					"continueMedia.php", 
					{method:'POST', body:fd}, "text",{
						success:function(j){
							
							/* If done, check for next image */
							
							if(med.chunkTracker.chunkIndex > med.chunkTracker.numChunks){
								NextUpload();
							}else{
								ContinueUpload(med, uploadToken);
							}
						},
						fail:function(txt){
							SoftNotification.Render("Failed: Upload Aborted: "+txt);
							mod.CLOSE();
						}
					}
				);
			}
						
			/* Rounds everything off */
			function Seal(){
				
				mod.CLOSE();
				_el.APPEND(articleCatcher, _el.CREATE('button','','',{},[''+artPk]));
				fileInput.value="";
				nameInput.value="";
				SoftNotification.Render("Upload Success!");
				
			}

			
		}
		
		////////////////// 
		//	HELPER FUNCTIONS
		//////////////////
		
		function SelectButton(t){
			if(t.tagName.toLowerCase() !== "button"){return;}
			var main=document.querySelector("main");
			_el.MoveId('selectedButton', t);
			_el.EMPTY(imgListContainer);
			if(t.innerHTML === "New Article"){
				main.className="formActive";
			}else{
				main.className="viewerActive";
				GetViewer(t.innerHTML);
			}
		}
		function ProgressMessage(m){
			return _el.CREATE("div",'','',{},[
				m+'... ',
				_el.CREATE('span','','spinner',{},["X"])
			]);
		}
		
		function GetViewer(id){
			id=parseInt(id);
				
			ElFetch(imgListContainer, ProgressMessage("Getting Image List"), "getImgList.php?id="+id, {}, 'json', {
				success:function(jsn){
					jsn.list.forEach(function(l){
						_el.APPEND(imgListContainer, 
							_el.CREATE('details','','',{},[
								_el.CREATE('summary','','',{},[l]),
								_el.CREATE('img','','',{src:"article-images/"+id+"/"+l})
							])
						);
					});
					if(!jsn.list.length){
						_el.APPEND(imgListContainer, "No images found for article id: "+id);
					}
				}
			});
			
		}
	</script>
	
</body>
</html>