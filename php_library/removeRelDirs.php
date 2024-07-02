<?php

function RemoveRelDirs($arr){

	return array_filter($arr, function($a){return (array_search($a, ['.','..']) === false);});
}
?>