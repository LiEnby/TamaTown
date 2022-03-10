<?php
// V5 Api Emulator, by SilicaAndPina
// Allows the game to connect w a Tamagotchi Version 5!

// Based on : https://github.com/KuromeSan/TamaTown/blob/master/V5/pc/cgi/Famitama.cgi

header("Content-Type: x-www-form-urlencoded");

$output = array( "ResultCode" => "OK" );

function CheckBit(string $code, bool $verify=true, int $bit=9)
{
	$codeArr = [];
	for($i = 0; $i < strlen($code); $i++){
		if($i == $bit){
			continue;
		}
		array_push($codeArr, intval($code[$i]));
	}
	
	$checksum = array_sum($codeArr) % 10;
	
	if($verify == false){
		return $checksum;
	}

	if($code[$bit] == strval($checksum)) {
		return 10;
	}
	else{
		return $checksum;
	}
}

function FindType($code){
	if(($code[3] == "8" || $code[3] == "9") && CheckBit($code,true,4) == 10){
		return 4;
	}
	else if(($code[3] == "2" || $code[3] == "3") && CheckBit($code,true,5) == 10){
		return 2;
	}
	else if(($code[3] == "4" || $code[3] == "5") && CheckBit($code,true,7) == 10){
		return 3;
	}
	else if(($code[3] == "7" || $code[3] == "6") && CheckBit($code,true,8) == 10){
		return 1;
	}
	else if(($code[3] == "0" || $code[3] == "1") && CheckBit($code,true,9) == 10){
		return 0;
	}
	else{
		return 5;
	}
}

function GetTamaIndex(string $code, int $type){
	$tamaIndex = [];
	if($type == 0){
		array_push($tamaIndex, $code[5]);
		array_push($tamaIndex, $code[8]);
	}
	else if($type == 1){
		array_push($tamaIndex, $code[5]);
		array_push($tamaIndex, $code[7]);
	}
	else if($type == 2){
		array_push($tamaIndex, $code[2]);
		array_push($tamaIndex, $code[4]);
	}
	else if($type == 3){
		array_push($tamaIndex, $code[8]);
		array_push($tamaIndex, $code[4]);
	}
	else if($type == 4){  
		array_push($tamaIndex, $code[9]);
		array_push($tamaIndex, $code[7]);
	}
	return $tamaIndex;	
}

function GetTamaRegion($code, $type){
	if($type == 0){
		return $code[1];
	}
	else if($type == 1){
		return $code[0];
	}
	else if($type == 2){
		return $code[1];
	}
	else if($type == 3){
		return $code[2];
	}
	else if($type == 4){
		return $code[5];
	}
}

function CgiGetCode(){
	global $output;

	// Get Params
	$requestType = $_GET['c'];
	if($requestType == 1){
		
		if(!isset($_GET['u'], $_GET['d'], $_GET['m'], $_GET['g'], $_GET['i'])){
			return false;
		}

		$loginNo = $_GET['u'].$_GET['d'];
		if(strlen($loginNo) != 10){
			return false;
		}
		
		$codeType = $_GET['m'];
		$gotchiPoints = $_GET['g'];
		$itemId = $_GET['i'];
	}
	// input validation
	if($requestType == 1) {
		if($gotchiPoints < 0 || $gotchiPoints > 5){
			return false;
		}
		if($itemId < 0 || $itemId > 999){
			return false;
		}
		if($codeType < 0 || $codeType > 4){
			return false;
		}
	}
	
	// Process input.
	
	if($requestType == 1){
		if($codeType == 0){ // login
			$type = FindType($loginNo);

			if($type == 5){
				return false;
			}
			else{
				$output['ResultCode']="OK";
				$CharCode = GetTamaIndex($loginNo, $type);
				$output['CharacterCode']=strval($CharCode[0]).strval($CharCode[1]);
				$output['VER']=strval(GetTamaRegion($loginNo, $type));
				return true;
			}
		}
		else if($codeType != 0){ // logout
			// Login parameters
			$type = FindType($loginNo);
			$region = GetTamaRegion($loginNo, $type);
			$tamaIndex = GetTamaIndex($loginNo, $type);
			
			// Input validation
			if($type == 5){
				return false;
			}
			
			// Logout parameters
			$unknownValue = 0;
			$logoutType = rand(0,3);
			
			if($codeType == 2){ #GP
				$itemId = rand(0 ,999);
			}
			$iid = strval($itemId);
			
			while(strlen($iid) != 3){
				$iid = "0".$iid;
			}

			
			if($logoutType == 0 || $logoutType == 1){
				$logoutNo = strval($codeType);
				$logoutNo .= strval($region);
				$logoutNo .= $iid[1];
				$logoutNo .= strval($logoutType);
				$logoutNo .= $iid[2];
				$logoutNo .= strval($tamaIndex[0]);
				$logoutNo .= strval($unknownValue);
				if($codeType == 2){ # GP
					$logoutNo .= strval($gotchiPoints);
				}
				else{
					$logoutNo .= $iid[0];
				}
				$logoutNo .= strval($tamaIndex[1]);
				$logoutNo .= "C";
			}
			else if($logoutType == 2 or $logoutType == 3){
				$logoutNo = $iid[1];
				$logoutNo .= strval($region);
				$logoutNo .= strval($tamaIndex[0]);
				$logoutNo .= strval($logoutType);
				$logoutNo .= strval($tamaIndex[1]);
				$logoutNo .= "C";
				if($codeType == 2){ # GP
					$logoutNo += strval($gotchiPoints);
				}
				else{
					$logoutNo += $iid[0];
				}
				$logoutNo += $iid[2];
				$logoutNo += strval($unknownValue);
				$logoutNo += strval($codeType);
			}
				
			// Calculate checksum
			$indx = strpos($logoutNo, "C");
			$cbit = strval(CheckBit($logoutNo,false,$indx));
			$logoutNo = str_replace("C", $cbit, $logoutNo);
			
			$output['PasswordUp'] = substr($logoutNo, 0, 5);
			$output['PasswordDown'] = substr($logoutNo, 5, 10);
			return true;
		}
		
	}
}

if(CgiGetCode()){
		$output['ResultCode'] = "OK";
}
else{
		$output['ResultCode'] = "ERROR";
}
$msg = http_build_query($output);
echo($msg);
?>