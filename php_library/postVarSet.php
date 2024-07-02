<?php 

if(!defined('POSTVARSET_OBREPLY')){
	define('POSTVARSET_OBREPLY',false);
}
function PostVarSet($name, &$cat, $opt=false){
	return ObVarSet($_POST, $name, $cat, $opt);
}
function ObVarSet($ob, $name, &$cat, $opt=false){
	if(isset($ob[$name])){
		if($ob[$name] !== ''){
			$cat=$ob[$name];
			return true;
		}
	}
	if(!$opt){
		if(ini_get('display_errors') != '1'){die();}
		$msg="$name was not provided. $name is required.";
		if(POSTVARSET_OBREPLY){
			die(json_encode(['success'=>false, 'msg'=>$msg]));
		}
		die($msg);
	}
	return false;
}
function GetVarSet($name, &$cat, $opt=false){return ObVarSet($_GET, $name, $cat, $opt);}
?>