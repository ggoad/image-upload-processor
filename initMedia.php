<?php
	require_once("php_library/DirUtil.php");
	require_once("php_library/randomString.php");
	


	// generates a random string, and ensures against colissions.
	
	$str=generateRandomString();
	while(is_dir("temporaryUpload/$str"))
	{
		$str=generateRandomString();
	}
	
	
	// create dir for upload
	
	DirUtil::ensure("temporaryUpload/$str");
	
	die(json_encode([
		'success'=>true,
		'uploadToken'=>$str
	]));
?>