<?php 

// this is a dummy file to simulate the generation of an id
// 		such as a mysql primary key

require_once("php_library/DirUtil.php");

$id=0;

while(is_dir("article-images/$id"))
{
	$id++;
}

DirUtil::ensure("article-images/$id");

die(json_encode([
	'success'=>true,
	'id'=>$id
]));

?>