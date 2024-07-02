<?php
	require_once("php_library/DirUtil.php");
	require_once("php_library/randomString.php");
	



	$str=generateRandomString();
	while(is_dir("temporaryUpload/$str"))
	{
		$str=generateRandomString();
	}
	DirUtil::ensure("temporaryUpload/$str");
	
	die(json_encode([
		'success'=>true,
		'uploadToken'=>$str
	]));
?>