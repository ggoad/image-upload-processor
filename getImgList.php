<?php 
require_once("php_library/postVarSet.php");
require_once("php_library/removeRelDirs.php");



GetVarSet('id',$id);
	$id=intval($id);


$dir="article-images/$id";
$sd=RemoveRelDirs(scandir($dir));

$ret=[];

foreach($sd as $s)
{
	$chIms=RemoveRelDirs(scandir("$dir/$s"));
	foreach($chIms as $chim)
	{
		$ret[]="$s/$chim";
	}
}

die(json_encode([
	'success'=>true,
	'list'=>$ret
]));

?>