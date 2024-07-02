<?php 
	Class DirUtil{

		static $skip = ['.','..'];

		static function ensure($dir){
			$dir = explode('/', $dir);
			$carry = [];
			$str = '';
			while($dir)
			{
				array_push($carry, array_shift($dir));
				$str = join('/', $carry);
				if(!is_dir($str)){
					@mkdir($str);
				}
			}
		}

		static function empty($dir){
			$sd = scandir($dir);
			
			foreach($sd as $s)
			{
				if(in_array($s, self::$skip)){
					continue;
				}
				if(is_file("$dir/$s")){
					unlink("$dir/$s");
				}else if(is_dir("$dir/$s")){
					self::delete("$dir/$s");
				}
			}
		}

		static function delete($dir){
			if(substr($dir, -1) == "/"){
				$dir = substr($dir, 0, strlen($dir)-1);
			}
			if(!file_exists($dir)){
				return true;
			}
			
			if(!is_dir($dir)){
				return unlink($dir);
			}

			foreach(scandir($dir) as $item)
			{
				if(in_array($item, self::$skip)){
					continue;
				}
				if(!self::delete("$dir/$item")){
					return false;
				}
			}
			return rmdir($dir);
		}

		static function copy($dir, $target){
			$dirs=scandir($dir);
			
			@mkdir($target);
			
			foreach($dirs as $d)
			{
				if(in_array($d, self::$skip)){
					continue;
				}
				if(is_dir("$dir/$d")){
					self::copy("$dir/$d", "$target/$d");
				}else if(is_file("$dir/$d")){
					copy("$dir/$d", "$target/$d");
				}
			}
		}

		/* 
			@param $zip string | ZipArchive
				if zip is a string, a new ZipArchive object is initialized
					with the destination being that of the string provided.
		*/
		static function zip($zip, $srcFolder, $dirStack = []){
			
			$needsClose = false;
			if(is_string($zip)){
				$zipDestination = $zip;
				$zip = new ZipArchive();
				$zipSuccess = $zip->open($zipDestination, ZipArchive::CREATE);
				if($zipSuccess !== true){
					throw new Exception("Invalid zip file destination: ZipArchiveError = $zipSuccess");
				}
				$needsClose = true;
			}else if(!($zip instanceof ZipArchive)){
				throw new Exception('zip must be a string or an instance of a ZipArchive');
			}

			$dirs=scandir($srcFolder);
			
			foreach($dirs as $d)
			{
				if(in_array($d, self::$skip)){
					continue;
				}
				if(is_dir("$srcFolder/$d")){
					$zip->addEmptyDir(join('/',$dirStack)."/$d");
					self::zip($zip, "$srcFolder/$d", array_merge($dirStack,[$d]));
				}else if(is_file("$srcFolder/$d")){
					$zip->addFile("$srcFolder/$d", join('/',$dirStack)."/$d");
				}
			}

			if($needsClose){
				$zip->close();
			}
		}
	}
?>