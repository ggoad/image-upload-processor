<?php 
	
	require_once("php_library/postVarSet.php");
	require_once("php_library/DirUtil.php");
	require_once("php_library/mime2ext.php");
	
	
	PostVarSet("uploadToken",$uploadToken);
		$uploadToken=preg_replace('/[^A-Za-z0-9]/','',$uploadToken);
	PostVarSet('final', $final, true);
	
	$tempName=$_FILES['dd']['tmp_name'];
	
	if(!is_uploaded_file($tempName)){
		die("Bad File");
	}
	
	file_put_contents("temporaryUpload/$uploadToken/file", file_get_contents($tempName), FILE_APPEND);

	if($final){
		
		PostVarSet('mediaName',$mediaName);
			$mediaName=str_replace('..','', $mediaName);
			$mediaSlug=preg_replace('/[^A-Za-z0-9]/','-', $mediaName);
			if(!$mediaName || !$mediaSlug){
				die("Bad Name...");
			}
		
		$mime=mime_content_type("temporaryUpload/$uploadToken/file");
		$ext=mime2ext($mime);
		
		
		PostVarSet('mediaSub',$mediaSub);
			$mediaSub=str_replace('..','', $mediaSub);
		PostVarSet('artPk',$artPk);
			$artPk=intval($artPk);
			
		PostVarSet('originalMime',$originalMime, true);
			$originalExt='';
			if($originalMime){
				$originalExt=mime2ext($originalMime);
			}
			
		$ext=($originalExt ? $originalExt.'.' : '').$ext;
		
		
		DirUtil::ensure("article-images/$artPk/$mediaSlug");
		
		
		$dest="article-images/$artPk/$mediaSlug/$mediaSub.$ext";
		
		
		
			
		copy("temporaryUpload/$uploadToken/file", $dest);
		DirUtil::delete("temporaryUpload/$uploadToken");
	}
	
	die("SUCCESS");
?>