<?php
/*
TuneKSP 0.01 - https://github.com/Conti/TuneKSP

Copyright © 2013 Conti - ( http://sojugarden.com | https://github.com/Conti ). 
All rights reserved.
	
Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:
	
	•	Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
	•	Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
	•	Redistribution and use in source and binary forms, with or without modification, for direct or indirect commercial purposes is strictly forbidden.
	•	Neither the name of SojuGarden.com nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

$version = "0.01";

//GameData Path
$GDPath = "./GameData";

//Isolating individual mods into a temporary isolated GameData directory can be useful for debugging
//$GDPath = "./GameDataTest";

//Universe Replacer Path
$URPath = "./GameData/UniverseReplacer";
$URCfg = "./tuneksp-ur.cfg";

// Functions
function getPartName($fname, $key){
	$array = array();
	$fp = fopen($fname, "r");
	$level = 0;
	$partLevel = 0;
	while (!feof($fp)) {
		$line = fgets($fp);
		if(strstr($line, "PART")){
			$partLevel++;
		}
		if(strstr($line, "{")) {
			$level++;
		}
		if(strstr($line, "}")) {
			$level--;
		}
		if ($line && $level == 1 && $partLevel == 1 && strstr($line, $key)) {
			$chunk = explode("=", $line);
			$chunk = trim($chunk[1]);
			$array[$chunk] = $fname;
			$partLevel--;
		}
	}
	fclose($fp);
	return $array;

}

function getIvaName($fname, $key){
	$array = array();
	$fp = fopen($fname, "r");
	$level = 0;
	$partLevel = 0;
	while (!feof($fp)) {
		$line = fgets($fp);
		if(strstr($line, "INTERNAL")){
			$internalLevel++;
		}
		if ($line && $internalLevel == 1 && strstr($line, $key)) {
			$chunk = explode("=", $line);
			$chunk = trim($chunk[1]);
			$array[$chunk] = $fname;
			$internalLevel--;
		}
	}
	fclose($fp);
	return $array;

}

function getParts($fname){
	$array = array();
	$fp = fopen($fname, "r");

	while (!feof($fp)) {
		$line = fgets($fp);
		if ($line && strstr($line, "part =")) {
			$chunk = explode("=", $line);
			//strip part UID
			$chunk = trim(substr($chunk[1], 0, strrpos($chunk[1], '_')));
			$chunk = str_replace(".", "_", $chunk);
			$array[] = $chunk;
		}
	}
	fclose($fp);
	return $array;
}

function getKASContainerParts($fname){
	$array = array();
	$fp = fopen($fname, "r");
	$level = 0;
	$kasLevel = 0;
	while (!feof($fp)) {
		$line = fgets($fp);
		if(strstr($line, "CONTENT")){
			$kasLevel++;
		}
		if(strstr($line, "}") && $kasLevel > 0) {
			$kasLevel--;
		}
		if ($line && $kasLevel == 1 && strstr($line, "name =")) {
			$chunk = explode("=", $line);
			$chunk = trim(str_replace(".", "_", $chunk[1]));
			$array[] = $chunk;
		}
	}
	fclose($fp);
	return $array;
}

function getPartCfg($fname, $key){
	$array = array();
	$fp = fopen($fname, "r");
	$level = 0;
	$partLevel = 0;
	while (!feof($fp)) {
		$line = fgets($fp);
		if(strstr($line, "PART")){
			$partLevel++;
		}
		if(strstr($line, "{")) {
			$level++;
		}
		if(strstr($line, "}")) {
			$level--;
		}
		if ($line && $level == 1 && $partLevel == 1 && strstr($line, $key)) {
			$chunk = explode("=", $line);
			$chunk = trim($chunk[1]);
			$array[$fname] = $chunk;
		}
	}
	fclose($fp);
	return $array;

}

function getCfgTextures($fname, $key){
	$array = array();
	$fp = fopen($fname, "r");
	$level = 0;
	$partLevel = 0;
	while (!feof($fp)) {
		$line = fgets($fp);
		if (strstr($line, $key)) {
			$chunk = explode("=", $line);
			$chunk = trim($chunk[1]);
			if(isset($tList)){
				$tList = $tList.", ".$chunk;
			}
			else {
				$tList = $chunk;
			}
		}
	}
	fclose($fp);
	if(isset($tList)){
		$array[$fname] = $tList;
		unset($tList);
	}
	return $array;

}

function getModelCfg($fname, $key){
	$array = array();
	$fp = fopen($fname, "r");
	$level = 0;
	$modelLevel = 0;
	while (!feof($fp)) {
		$line = fgets($fp);
		if(strstr($line, "MODEL")){
			$modelLevel++;
		}
		if(strstr($line, "{")) {
			$level++;
		}
		if(strstr($line, "}")) {
			$level--;
		}
		if ($line && $level == 2 && $modelLevel == 1 && strstr($line, $key)) {
			$chunk = explode("=", $line);
			$chunk = trim($chunk[1]);
			$array[$fname] = $chunk.".mu";
		}
	}
	fclose($fp);
	return $array;

}

function getTexFromMu($fname, $cfg){
	if(file_exists($fname)){
		$array = array();
		$fp = fopen($fname, "rb");
		while (!feof($fp)) {
			$line = fgets($fp);
			if ($line && strstr($line, "mbm") || strstr($line, "png") || strstr($line, "tga")) {
				//break line into an array, split on null bytes
				$chunk = explode("\x00", $line);
				foreach($chunk as $key => $value){
					if(strstr($value, "mbm") || strstr($value, "png") || strstr($value, "tga")){
						//strip all non utf-8 characters
						$value = preg_replace('/[^(\x20-\x7F)]*/','', $value);
						//strip all characters except A-Z, a-z, 0-9, forward slashes, spaces, dots, hyphens, and underscores
						$value = preg_replace("/[^A-Za-z0-9\/ .-_]/", '', $value);
						if(isset($tList)){
							$tList = $tList.", ".$value;
						}
						else {
							$tList = $value;
						}
					}
				}					
			}
		}
		fclose($fp);
		if(isset($tList)){
			$array[$cfg] = $tList;
			unset($tList);
		}
		return $array;
	}
}

function getPartAssets($partNameArray, &$pMeshes, &$pModels, &$pTextures){
	foreach($partNameArray as $name => $paths){
		if(is_array($paths) == true){
			foreach($paths as $key => $path){
				if(file_exists($path)) {
					$pMeshes = array_merge_recursive($pMeshes, getPartCfg($path, "mesh ="));
					$pModels = array_merge_recursive($pModels, getModelCfg($path, "model ="));
					$pTextures = array_merge_recursive($pTextures, getCfgTextures($path, "texture ="));
				}
			}
		}
		else {
			$path = $paths;
			if(file_exists($path)) {
				$pMeshes = array_merge_recursive($pMeshes, getPartCfg($path, "mesh ="));
				$pModels = array_merge_recursive($pModels, getModelCfg($path, "model ="));
				$pTextures = array_merge_recursive($pTextures, getCfgTextures($path, "texture ="));
			}

		}
	}

	//get textures for all part mesh definitions
	foreach($pMeshes as $cfg => $mNames){
		if(is_array($mNames) == true){
			foreach($mNames as $key => $mName){
				//only process .mu files
				if(strstr($mName, ".mu")){
					//skip processing mu's for textures that have already been defined in part.cfg
					if(array_key_exists($cfg, $pTextures) == false){
						$fname = dirname($cfg)."/".$mName;
						if(file_exists($fname)) {
							$pTextures = array_merge_recursive($pTextures, getTexFromMu($fname, $cfg));
						}
					}
				}
			}
		}
		else {
			$mName = $mNames;
			//only process .mu files
			if(strstr($mName, ".mu")){
				//skip processing mu's for textures that have already been defined in part.cfg
				if(array_key_exists($cfg, $pTextures) == false){
					$fname = dirname($cfg)."/".$mName;
					if(file_exists($fname)) {
						$pTextures = array_merge_recursive($pTextures, getTexFromMu($fname, $cfg));
					}
				}
			}
		}

	}

	//get textures for all part model definitions
	foreach($pModels as $cfg => $mNames){
		if(is_array($mNames) == true){
			foreach($mNames as $key => $mName){
				//skip processing mu's for textures that have already been defined in part.cfg
				if(array_key_exists($cfg, $pTextures) == false){
					$fname = explode("GameData/", $cfg);
					$fname = $fname[0]."GameData/".$mName;
					if(file_exists($fname)) {
						$pTextures = array_merge_recursive($pTextures, getTexFromMu($fname, $cfg));
					}
				}
			}
		}
		else {
			$mName = $mNames;
			//skip processing mu's for textures that have already been defined in part.cfg
			if(array_key_exists($cfg, $pTextures) == false){
				$fname = explode("GameData/", $cfg);
				$fname = $fname[0]."GameData/".$mName;
				if(file_exists($fname)) {
					$pTextures = array_merge_recursive($pTextures, getTexFromMu($fname, $cfg));
				}
			}
		}
	}

	//build path_to_asset => cfgname array for all part assets
	$partAssets = array();
	foreach($pMeshes as $cfg => $names){
		if(is_array($names) == true){
			foreach($names as $key => $name){
				$path = dirname($cfg)."/";
				$name = trim($name);
				//strip path
				if(strstr($name, '/')){
					$name = trim(substr($name, strrpos($name, '/') + 1));
				}
				$objects = new RecursiveIteratorIterator( new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);
				foreach($objects as $key => $object){
					$pname = $object->getPathname();
					$fname = $object->getFilename();
					if(strstr($fname, $name)){
						$partAssets[$pname] = $cfg;
					}
				}
			}
		}
		else {
			$path = dirname($cfg)."/";
			$name = trim($names);
			//strip path
			if(strstr($name, '/')){
				$name = trim(substr($name, strrpos($name, '/') + 1));
			}
			$objects = new RecursiveIteratorIterator( new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);
			foreach($objects as $key => $object){
				$pname = $object->getPathname();
				$fname = $object->getFilename();
				if(strstr($fname, $name)){
					$partAssets[$pname] = $cfg;
				}
			}
		}
	}
	foreach($pModels as $cfg => $names){
		if(is_array($names) == true){
			foreach($names as $key => $name){
				$path = dirname($cfg)."/";
				$name = trim($name);
				if(strstr($name, '/')){
					$name = trim(substr($name, strrpos($name, '/') + 1));
				}
				$objects = new RecursiveIteratorIterator( new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);
				foreach($objects as $key => $object){
					$pname = $object->getPathname();
					$fname = $object->getFilename();
					if(strstr($fname, $name)){
						//if this pname is already in the array, append
						if(array_key_exists($pname, $partAssets)){
							$partAssets[$pname] = $partAssets[$pname].", ".$cfg;
						}
						else{
							$partAssets[$pname] = $cfg;
						}
					}
				}
			}
		}
		else {
			$path = dirname($cfg)."/";
			$name = trim($names);
			if(strstr($name, '/')){
				$name = trim(substr($name, strrpos($name, '/') + 1));
			}
			$objects = new RecursiveIteratorIterator( new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);
			foreach($objects as $key => $object){
				$pname = $object->getPathname();
				$fname = $object->getFilename();
				if(strstr($fname, $name)){
					//if this pname is already in the array, append
					if(array_key_exists($pname, $partAssets)){
						$partAssets[$pname] = $partAssets[$pname].", ".$cfg;
					}
					else{
						$partAssets[$pname] = $cfg;
					}
				}
			}
		}

	}
	foreach($pTextures as $cfg => $names){
		if(is_array($names) == true){
			foreach($names as $key => $name){
				$name = explode(",", $name);
				$path = dirname($cfg)."/";
				foreach($name as $key => $value){
					$value = trim($value);
					if(strstr($value, '/')){
						$value = trim(substr($value, strrpos($value, '/') + 1));
					}
					$objects = new RecursiveIteratorIterator( new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);
					foreach($objects as $key => $object){
						$pname = $object->getPathname();
						$fname = $object->getFilename();
						if(strstr($fname, $value)){
							//if this pname is already in the array, append
							if(array_key_exists($pname, $partAssets)){
								$partAssets[$pname] = $partAssets[$pname].", ".$cfg;
							}
							else{
								$partAssets[$pname] = $cfg;
							}
				
						}
					}
				}
			}
		}
		else {
			$name = explode(",", $names);
			$path = dirname($cfg)."/";
			foreach($name as $key => $value){
				$value = trim($value);
				if(strstr($value, '/')){
					$value = trim(substr($value, strrpos($value, '/') + 1));
				}
				$objects = new RecursiveIteratorIterator( new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);
				foreach($objects as $key => $object){
					$pname = $object->getPathname();
					$fname = $object->getFilename();
					if(strstr($fname, $value)){
						//if this pname is already in the array, append
						if(array_key_exists($pname, $partAssets)){
							$partAssets[$pname] = $partAssets[$pname].", ".$cfg;
						}
						else{
							$partAssets[$pname] = $cfg;
						}
				
					}
				}
			}
		}

	}
	return $partAssets;
}

//Check for presence of universe replacer mod
if(file_exists($URPath)){
	$urexists = true;
}
else {
	$urexists = false;
}



$enableall = false;
$disableparts = false;
$disableiva = false;
$tuneur = false;
$disableur = false;
$verbose = false;
$debug = false;
$help = false;
$validArg = false;
 
foreach($argv as $i => $opt){
	if($opt == "-b" || $opt == "--tune-building"){
		$disablealliva = true;
		$disableur = true;
		$validArg = true;
	}
	else if($opt == "-m" || $opt == "--tune-mission"){
		$disableparts = true;
		$disableiva = true;
		$tuneur = true;
		$validArg = true;
	}
	if($opt == "-e" || $opt == "--enable-all"){
		$enableall = true;
		$validArg = true;
	}
	if($opt == "-p" || $opt == "--disable-parts"){
		$disableparts = true;
		$validArg = true;
	}
	if($opt == "-i" || $opt == "--disable-iva"){
		$disableiva = true;
		$validArg = true;
	}
	if($opt == "-a" || $opt == "--disable-all-iva"){
		$disablealliva = true;
		$validArg = true;
	}
	if($opt == "-ut" || $opt == "--tune-universe"){
		$tuneur = true;
		$validArg = true;
	}
	if($opt == "-ud" || $opt == "--disable-universe"){
		$disableur = true;
		$validArg = true;
	}
	if($opt == "-v" || $opt == "--verbose"){
		$verbose = true;
		$validArg = true;
	}
	if($opt == "-d" || $opt == "--debug"){
		$debug = true;
		$validArg = true;
	}
	if($opt == "-h" || $opt == "--help"){
		$help = true;
		$validArg = true;
	}
}

if(!$argv[1] || $help == true || $validArg == false){
	echo "\nTuneKSP version $version\n\nUsage:\n";
	if($urexists == true){
		echo "-b  --tune-building    : Tune for VAB/SPH, Disable all IVA, Enable all Parts, Disable UR\n";
		echo "-m  --tune-mission     : Tune for mission, Disable unused IVA/Parts, Tune UR\n";
	}
	else{
		echo "-b  --tune-building    : Tune for VAB/SPH, Disable all IVA, Enable all Parts\n";
		echo "-m  --tune-mission     : Tune for mission, Disable unused IVA/Parts\n";
	}
	echo "-e  --enable-all       : Enable all parts and iva.\n";
	echo "-p  --disable-parts    : Disable unused parts.\n";
	echo "-i  --disable-iva      : Disable unused iva.\n";
	echo "-a  --disable-all-iva  : Disable all iva.\n";
	if($urexists == true){
		echo "-ut --tune-universe    : Tune active universe replacer textures.\n";
		echo "-ud --disable-universe : Disable all universe replacer textures.\n";
	}
	echo "-v  --verbose          : Verbose Output.\n";
	echo "-h  --help             : Display this message.\n";
	exit;
}

// Check for disabled assets
if($verbose == true){
	echo "Check for disabled assets...\n";
}
$disabled = array();
$isDisabled = false;
$path = realpath($GDPath);
$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);

foreach($objects as $name => $object){
	if($object->getExtension() == "disabled"){
		array_push($disabled, $object->getPathname());
		if($isDisabled == false){
			$isDisabled = true;
		}
	}
}

if($disableparts == true || $disableiva == true || $disablealliva == true && $isDisabled == true){
	$enableall = true;
	$isDisabled = false;
}

if($enableall == true){
	//restore disabled files
	if($verbose == true){
		echo "Restore disabled assets...\n";
	}
	foreach($disabled as $key => $oname){
		$nname = str_replace(".disabled", "", $oname);
		rename($oname, $nname);
	}
}

// Get file information from GameData
if($verbose == true){
	echo "Get file information from GameData...\n";
}
$tSize = 0;
$tIVAParts = 0;
$tPlugins = 0;
$tAudioSize = 0;
$mSize = 0;
$partNames = array();
$ivaNames = array();
$path = realpath($GDPath);
$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);

foreach($objects as $name => $object){
	//check all cfg's for PART{} and INTERNAL{} declarations
	if($object->getExtension() == "cfg"){
		$fname = $object->getPathname();
		$part = getPartName($fname, "name =");
		if($part != NULL){
			$partNames = array_merge_recursive($partNames, $part);
		}
		else{
			$iva = getIvaName($fname, "name =");
			if($iva != NULL){
				$ivaNames = array_merge_recursive($ivaNames, $iva);
			}
		}
	}
	//get size of all texture images
	if($object->getExtension() == "tga" || $object->getExtension() == "mbm" || $object->getExtension() == "png" || $object->getExtension() == "jpg" || $object->getExtension() == "jpeg") {
		$fsize = $object->getSize();
		$tSize = $tSize + $fsize;
	}
	//get size of all meshes
	if($object->getExtension() == "dae" || $object->getExtension() == "msh" || $object->getExtension() == "mu"){
		$fsize = $object->getSize();
		$mSize = $mSize + $fsize;
	}
	//count all plugin dlls
	if($object->getExtension() == "dll"){
		$tPlugins++;
	}
	//get size of all audio files
	if($object->getExtension() == "ogg" || $object->getExtension() == "wav"){
		$fsize = $object->getSize();
		$aSize = $aSize + $fsize;
	}
}

//restore commented out iva names in part cfgs
foreach($partNames as $name => $cfgs){
	if(is_array($cfgs) == true){
		foreach($cfgs as $key => $cfg){
			$lines = explode("\n", file_get_contents($cfg));
			$osize = count($lines);
			$internalLevel = 0;
			foreach($lines as $key => $line){
				if($line != NULL){
					if(strstr($line, "INTERNAL")){
						$internalLevel++;
					}
					if ($internalLevel == 1 && strstr($line, "name =")) {
						if(strstr($line, "//")){
							$iname = trim(substr($line, strrpos($line, '=') + 1));
							$lines[$key] = "\tname = $iname";
							$internalLevel--;
						}
					}
				}			
			}
			$lines = implode("\n", $lines);
			file_put_contents($cfg, $lines);
		}
	}
	else {
		$cfg = $cfgs;
		$lines = explode("\n", file_get_contents($cfg));
		$osize = count($lines);
		$internalLevel = 0;
		foreach($lines as $key => $line){
			if($line != NULL){
				if(strstr($line, "INTERNAL")){
					$internalLevel++;
				}
				if ($internalLevel == 1 && strstr($line, "name =")) {
					if(strstr($line, "//")){
						$iname = trim(substr($line, strrpos($line, '=') + 1));
						$lines[$key] = "\tname = $iname";
						$internalLevel--;
					}
				}
			}			
		}
		$lines = implode("\n", $lines);
		file_put_contents($cfg, $lines);
	}

}

//convert total texture size to MB and round two 2 decimal places
$tSize = number_format((float)$tSize/1048576, 2, '.', '');
$aSize = number_format((float)$aSize/1048576, 2, '.', '');
$mSize = number_format((float)$mSize/1048576, 2, '.', '');

$totalParts = count($partNames);
$totalIva = count($ivaNames);

//Compile a list of all parts currently used in craft files
if($verbose == true){
	echo "Compile a list of all parts currently used in craft files...\n";
}
$craftParts = array();
$path = realpath('./saves');
$tCraft = 0;
$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);
foreach($objects as $name => $object){
	if($object->getExtension() == "craft"){
		$fname = $object->getPathname();
		$craftParts = array_merge_recursive($craftParts, getParts($fname));
		// Get parts which are in KAS4+ part containers
		$craftParts = array_merge_recursive($craftParts, getKASContainerParts($fname));
		//for debugging as needed uncomment this //print_r(getKASContainerParts($fname));
		$tCraft++;
	}
}

$timesUsed = array_count_values($craftParts);

$craftParts = array_keys(array_count_values($craftParts));



//compare craftParts and partNames to determine if any parts are missing
if($verbose == true){
	echo "Compare craftParts and partNames to determine if any parts are missing...\n";
}
$missingParts = array();
foreach($craftParts as $key => $value){
	if(!$partNames[$value]){
		array_push($missingParts, $value);
	}
	
}
$mParts = count($missingParts);

if($debug == true){
	echo "missingParts:(".count($missingParts).")\n";
	print_r($missingParts);
}

//split used parts from partNames array and place into unusedParts/usedParts arrays
if($verbose == true){
	echo "Split used parts from partNames array and place into unusedParts/usedParts arrays...\n";
}
$usedParts = array();
$unusedParts = $partNames;
foreach($craftParts as $key => $value){
	if($unusedParts[$value]){
		$usedParts[$value] = $unusedParts[$value];
		unset($unusedParts[$value]);
	}
}

//unfortunately I don't have the time/patience to handle multi part .cfg files more elegantly yet (to disable only one of the parts within a multi part config) so for now I am simply going to remove any unused parts from the unused parts array which share a cfg file with a used part
if($verbose == true){
	echo "Checking for multi part cfg definitions which have some used and some unused assets...\n";
}
$found = false;
foreach($unusedParts as $name => $cfg){
	if(in_array($cfg, $usedParts) == true){
		$isUsed = array_search($cfg, $unusedParts);
		$usedName = array_search($cfg, $usedParts);
		if($verbose == true){
			if($found == false){
				echo "\n";
				$found = true;
			}
			echo "Unused part | $isUsed | shares cfg file with used part | $usedName | - $isUsed will not be disabled.\n";
		}
		unset($unusedParts[$isUsed]);
	}
}
if($verbose == true && $found == true){
	echo "\n";
}


//get unused part assets
$pMeshes = array();
$pModels = array();
$pTextures = array();
if($verbose == true){
	echo "Get unused part assets...\n";
}
$unusedAssets = getPartAssets($unusedParts, $pMeshes, $pModels, $pTextures);
if($debug == true){
	echo "unused pMeshes:(".count($pMeshes).")\n";
	print_r($pMeshes);

	echo "unused pModels:(".count($pModels).")\n";
	print_r($pModels);

	echo "unused pTextures:(".count($pTextures).")\n";
	print_r($pTextures);
}

//get used part assets
$pMeshes = array();
$pModels = array();
$pTextures = array();
if($verbose == true){
	echo "Get used part assets...\n";
}
$usedAssets = getPartAssets($usedParts, $pMeshes, $pModels, $pTextures);
if($debug == true){
	echo "used pMeshes:(".count($pMeshes).")\n";
	print_r($pMeshes);

	echo "used pModels:(".count($pModels).")\n";
	print_r($pModels);

	echo "used pTextures:(".count($pTextures).")\n";
	print_r($pTextures);
}

//remove any used assets from unused assets
if($verbose == true){
	echo "Check for cross linked unused assets that are still in use by another part...\n";
}
foreach($unusedAssets as $asset => $cfgs){
	if(array_key_exists($asset, $usedAssets) == true){
		unset($unusedAssets[$asset]);
	}
}

//split used ivas from ivaNames array and place into unusedIva/usedIva arrays
if($verbose == true){
	echo "Split used ivas from ivaNames array and place into unusedIva/usedIva arrays...\n";
}
$usedIva = array();
$unusedIva = $ivaNames;
foreach($usedParts as $name => $cfgs){
	if(is_array($cfgs) == true){
		foreach($cfgs as $key => $cfg){
			$partIva = getIvaName($cfg, "name =");
			if($partIva != NULL){
				$usedIva = array_merge($usedIva, $partIva);
			}
		}
	}
	else {
		$cfg = $cfgs;
		$partIva = getIvaName($cfg, "name =");
		if($partIva != NULL){
			$usedIva = array_merge($usedIva, $partIva);
		}
	}

}
//remove usedIva's from unusedIva
if($verbose == true){
	echo "Remove usedIva's from unusedIva...\n";
}
foreach($usedIva as $name => $cfg){
	if($unusedIva[$name]){
		unset($unusedIva[$name]);
	}
}

//I have yet to see an IVA that does not contain the mesh and all textures in the same directory as the .cfg so we can skip most of the discovery we did for parts
if($disableiva == true && $disablealliva == false){
	//disable all unused ivas
	if($verbose == true){
		echo "Disable all unused ivas...\n";
	}
	foreach($unusedIva as $name => $cfg){
		$path = dirname($cfg);
		$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);
		foreach($objects as $name => $object){
			if($object->isFile()){
				$oname = $object->getPathname();
				$nname = $oname.".disabled";
				rename($oname, $nname);
			}
		}
	}
}

if($disablealliva == true){
	//disable all ivas
	if($verbose == true){
		echo "Disable all ivas...\n";
	}
	echo "You have chosen to disable all IVAs. Would you like to use one IVA to temporarily replace the default part IVA for all parts which have an IVA view?\nNote: Without any IVA you will not see kerbals or be able to switch to internal camera.\n\n";
	$iChoices = array_keys($ivaNames);
	$validOpt = false;
	while($validOpt == false){
		foreach($iChoices as $opt => $iname){
			echo "[$opt] = $iname\n";
		}
		$i = count($iChoices);
		echo "[$i] = Do not use any IVA views.\n";	
		$iEnabled = readline("\nEnter the numeric for the IVA you wish to use: ");
		if($iChoices[$iEnabled]){
			$iEnabled = $iChoices[$iEnabled];
			$validOpt = true;
		}
		else if($iEnabled == $i){
			$validOpt = true;
			$iEnabled = NULL;
		}
		else{
			echo "Invalid option.\n\n";
		}
	}

	//comment out iva definitions in part cfg files
	foreach($partNames as $name => $cfg){
		if(is_array($cfgs) == true){
			foreach($cfgs as $key => $cfg){
				$lines = explode("\n", file_get_contents($cfg));
				$osize = count($lines);
				$internalLevel = 0;
				foreach($lines as $key => $line){
					if($line != NULL){
						if(strstr($line, "INTERNAL")){
							$internalLevel++;
						}
						if ($internalLevel == 1 && strstr($line, "name =")) {
							if($iEnabled != NULL){
								$lines[$key] = "name = $iEnabled //".$line;
							}
							else {
								$lines[$key] = "//".$line;
							}
							$internalLevel--;
						}
					}			
				}
				$lines = implode("\n", $lines);
				file_put_contents($cfg, $lines);
			}
		}
		else {
			$cfg = $cfgs;
			$lines = explode("\n", file_get_contents($cfg));
			$osize = count($lines);
			$internalLevel = 0;
			foreach($lines as $key => $line){
				if($line != NULL){
					if(strstr($line, "INTERNAL")){
						$internalLevel++;
					}
					if ($internalLevel == 1 && strstr($line, "name =")) {
						if($iEnabled != NULL){
							$lines[$key] = "name = $iEnabled //".$line;
						}
						else {
							$lines[$key] = "//".$line;
						}
						$internalLevel--;
					}
				}			
			}
			$lines = implode("\n", $lines);
			file_put_contents($cfg, $lines);
		}
		
	}
	
	foreach($ivaNames as $name => $cfg){
		if(!strstr($name, $iEnabled)){
			$path = dirname($cfg);
			$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);
			foreach($objects as $name => $object){
				if($object->isFile()){
					$oname = $object->getPathname();
					$nname = $oname.".disabled";
					rename($oname, $nname);
				}
			}
		}
	}
}

if($disableparts == true){
	//disable all unused parts
	if($verbose == true){
		echo "Disable all unused parts...\n";
	}
	foreach($unusedAssets as $asset => $cfgs){
		if(file_exists($asset)){
			$oname = $asset;
			$nname = $asset.".disabled";
			rename($oname, $nname);
		}
	}
	foreach($unusedParts as $name => $cfgs){
		if(is_array($cfgs) == true){
			foreach($cfgs as $key => $cfg){
				if(file_exists($cfg)){
					$oname = $cfg;
					$nname = $cfg.".disabled";
					rename($oname, $nname);
				}
			}
		}
		else {
			$cfg = $cfgs;
			if(file_exists($cfg)){
				$oname = $cfg;
				$nname = $cfg.".disabled";
				rename($oname, $nname);
			}
		}

	}
}


// This is for the UniverseReplacer mod http://forum.kerbalspaceprogram.com/threads/44135-0-21-x-Universe-Replacer-v4-0
if($urexists == true){
	if($disableur == true){
		$urTex = array();
		$path = realpath($URPath);
		$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);

		foreach($objects as $name => $object){
			//find all texture assets
			if($object->getExtension() == "tga" || $object->getExtension() == "mbm" || $object->getExtension() == "png" || $object->getExtension() == "jpg" || $object->getExtension() == "jpeg") {
				$oname = $object->getPathname();
				$nname = $oname.".disabled";
				rename($oname, $nname);
			}
		}
	}		
	else if($tuneur == true){
		if(file_exists($URCfg)){
			$cfg = file_get_contents($URCfg);
		}
		else{
			$cfg = NULL;
		}
		
		$urTex = array();
		$path = realpath($URPath);
		$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);

		foreach($objects as $name => $object){
			$array = array();
			//find all texture assets
			if($object->getExtension() == "tga" || $object->getExtension() == "mbm" || $object->getExtension() == "png" || $object->getExtension() == "jpg" || $object->getExtension() == "jpeg") {
				$fpath = $object->getPath();
				$fname = $object->getPathname();
				$array[$fpath] = $fname;
				$urTex = array_merge_recursive($urTex, $array);
			}
		}
		
		echo "Available universe replacement textures:\n";
		$iChoices = array_keys($urTex);
		$enableList = array();
		if($cfg != NULL){
			$cfgValues = explode("\n",$cfg);
			foreach($cfgValues as $key => $path){
				$path = trim($path);
				if($path != ''){
					$enableList[$path] = basename($path);
				}
			}
		}
		$doneOpt = false;
		while($doneOpt == false){
			echo "\nThe following replacement groups are enabled:\n";
			if(count($enableList) > 0){
				$first = true;
				foreach($enableList as $path => $name){
					if($first == true){
						echo "$name";
						$first = false;
					}
					else {
						echo ", $name";
					}
				
				}
			}
			else {
				echo "(none)";
			}
			echo "\n\n";
			foreach($iChoices as $opt => $iname){
				echo "[$opt] = ".basename($iname)."\n";
			}
			echo "\n[a] = Enable All\n";
			echo "[d] = Disable All\n";
			echo "[s] = Save Configuration\n";
			$iEnabled = readline("\nEnter the numeric for the texture group you wish to enable/disable or a/d/s: ");
			if($iChoices[$iEnabled]){
				if($enableList[$iChoices[$iEnabled]]){
					unset($enableList[$iChoices[$iEnabled]]);
					echo "Disabled ".basename($iChoices[$iEnabled])."...\n";
				}
				else{
					$enableList[$iChoices[$iEnabled]] = basename($iChoices[$iEnabled]);
					echo "Enabled ".basename($iChoices[$iEnabled])."...\n";
				}
			}
			else if($iEnabled == 'a'){
				foreach($iChoices as $key => $path){
					$enableList[$path] = basename($path);
				}
				echo "Enabled all...\n";
			}
			else if($iEnabled == 'd'){
				$enableList = array();
				echo "Disabled all...\n";
			}
			else if($iEnabled == 's'){
				$doneOpt = true;
				echo "Saving Universe Replacer Configuration...\n";
			}
			else{
				echo "Invalid option.\n\n";
			}
		}
		//write to cfg file
		$cfg = NULL;
		foreach($enableList as $path => $basename){
			$cfg .= $path."\n";
		}
		file_put_contents($URCfg, $cfg);
		
		//remove enabled files from urTex array
		foreach($enableList as $path => $basename){
			if($urTex[$path]){
				unset($urTex[$path]);
			}
		}
		//disable remaining urTex
		foreach($urTex as $path => $textures){
			if(is_array($textures)){
				foreach($textures as $key => $texture){
					$oname = $texture;
					$nname = $oname.".disabled";
					rename($oname, $nname);
				}
			}
			else {
				$oname = $textures;
				$nname = $oname.".disabled";
				rename($oname, $nname);
			}
		}
	}
}

// Get file information from GameData for remaing active files
if($verbose == true){
	echo "Get file information from GameData for remaing active files...\n";
}
$activePartNames = array();
$activeIvaNames = array();
$path = realpath($GDPath);
$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);

foreach($objects as $name => $object){
	//check all cfg's for PART{} and INTERNAL{} declarations
	if($object->getExtension() == "cfg"){
		$fname = $object->getPathname();
		$part = getPartName($fname, "name =");
		if($part != NULL){
			$activePartNames = array_merge($activePartNames, $part);
		}
		else{
			$iva = getIvaName($fname, "name =");
			if($iva != NULL){
				$activeIvaNames = array_merge($activeIvaNames, $iva);
			}
		}
	}
	//get size of all texture images
	if($object->getExtension() == "tga" || $object->getExtension() == "mbm" || $object->getExtension() == "png" || $object->getExtension() == "jpg" || $object->getExtension() == "jpeg") {
		$fsize = $object->getSize();
		$activeTSize = $activeTSize + $fsize;
	}
	//get size of all meshes
	if($object->getExtension() == "dae" || $object->getExtension() == "msh" || $object->getExtension() == "mu"){
		$fsize = $object->getSize();
		$activeMSize = $activeMSize + $fsize;
	}

	//get size of all audio files
	if($object->getExtension() == "ogg" || $object->getExtension() == "wav"){
		$fsize = $object->getSize();
		$activeASize = $activeASize + $fsize;
	}
}

//convert total texture size to MB and round two 2 decimal places
$activeTSize = number_format((float)$activeTSize/1048576, 2, '.', '');
$activeASize = number_format((float)$activeASize/1048576, 2, '.', '');
$activeMSize = number_format((float)$activeMSize/1048576, 2, '.', '');

$activeParts = count($activePartNames);
$activeIva = count($activeIvaNames);

if($debug == true){
	echo "partNames:(".count($partNames).")\n";
	print_r($partNames);

	echo "usedParts:(".count($usedParts).")\n";
	print_r($usedParts);

	echo "unusedParts:(".count($unusedParts).")\n";
	print_r($unusedParts);
	
	echo "usedAssets:(".count($usedAssets).")\n";
	print_r($usedAssets);
	
	echo "unusedAssets:(".count($unusedAssets).")\n";
	print_r($unusedAssets);


	echo "ivaNames:(".count($ivaNames).")\n";
	print_r($ivaNames);

	echo "usedIva:(".count($usedIva).")\n";
	print_r($usedIva);
	
	echo "unusedIva:(".count($unusedIva).")\n";
	print_r($unusedIva);
	
	
	echo "craftParts:(".count($craftParts).")\n";
	print_r($craftParts);

	echo "timesUsed:(".count($timesUsed).")\n";
	print_r($timesUsed);
}

$missing = count($missingParts);
$tIVAParts = count($ivaNames);

if($verbose == true){
	arsort($timesUsed);
	echo "Times Used	Part Name\n";
	echo "----------------------------------------\n";
	foreach($timesUsed as $name => $times){
		echo "$times		$name\n";
	}
}

echo "----------------------------------------\n";
echo "Total Parts : $totalParts\n";
echo "Active Parts: $activeParts\n";
if($missing > 0){
	echo "Missing Parts: $mParts\n";
}
echo "Total IVA : $totalIva\n";
echo "Active IVA : $activeIva\n";
echo "Total Plugins : $tPlugins\n";
echo "Total Craft Files : $tCraft\n";
echo "Total Texture Size : $tSize MB\n";
echo "Active Texture Size : $activeTSize MB\n";
echo "Total Mesh Size : $mSize MB\n";
echo "Active Mesh Size : $activeMSize MB\n";
echo "Total Audio Size : $aSize MB\n";
echo "----------------------------------------\n";


if($missing > 0){
	asort($missingParts);
	echo "WARNING: The following parts are missing\n";
	echo "----------------------------------------\n";
	foreach($missingParts as $key => $name){
		echo "$name\n";
	}
	echo "----------------------------------------\n";
}
?>