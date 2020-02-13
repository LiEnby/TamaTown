<?php

function check(string $code, $bitPos)
{
	$checksum = 0;
	intval($code[5]);

	for($i=0; $i<strlen($code); $i++)
	{
		if($i == $bitPos)
		{
			continue;
		}
		$checksum += intval($code[$i]);
	}
	return $checksum % 10;
}

function verify_code(string $code)
{
	$type = intval($code[5]);
	
	$checkBit = 0;
	$checkBitPos = 0;
	
	switch($type)
	{
		case 0:
		case 1:
			$checkBitPos = 7;
			break;
		case 2:
		case 3:
			$checkBitPos = 7;
			break;
		case 4:
		case 5:
			$checkBitPos = 4;
			break;
		case 6:
		case 7:
			$checkBitPos = 11;
			break;
		case 8:
		case 9:
			$checkBitPos = 1;
			break;
	}
	
	$checkBit = intval($code[$checkBitPos]);
	$calcBit = check($code,$checkBitPos);
	
	if($checkBit == $calcBit)
	{
		return true;
	}
	else
	{
		return false;
	}
}

function fail()
{
	print("mch=00");
	exit();	
}

//main process

header("Content-Type: www-form-urlencoded");
session_start(); 

if(!isset($_POST["tapa"]))
{
	fail();
}

$loginCode = $_POST["tapa"];

if(strlen($loginCode) != 14)
{
	fail();
}

$_SESSION["loginCode"] = $loginCode;
$isValid = verify_code($loginCode);

if($isValid)
{
	print("mch=01&wrk=21&par=50&acd=6&npa=72&mde=1&xda=81&dte=68721579380456");
}
else
{
	fail();
}
?>
