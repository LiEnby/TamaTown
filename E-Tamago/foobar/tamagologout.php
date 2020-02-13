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

function fail()
{
	echo("tapa=00000000000000");
	exit();
}

header("Content-Type: www-form-urlencoded");
session_start(); 

if(!isset($_SESSION["loginCode"]))
{
	fail();
}

$code = $_SESSION["loginCode"];


if(isset($_POST["mde"]) and isset($_POST["npa"]))
{
	$mode = strval($_POST["mde"]);
	$id = strval($_POST["npa"]);
	
	//input validaition:
	
	if(strlen($id) > 3)
	{
		fail();
	}
	else if(strlen($mode) > 1)
	{
		fail();
	}
	
	// processing 
	
	while(strlen($id) < 3)
	{
		$id = "0".$id;
	}
	$type = intval($code[5]);
	
	switch($type)
	{
		case 0:
		case 1:
			$code[8] = $mode;
			$code[0] = $id[0];
			$code[13] = $id[1];
			$code[11] = $id[3];
			$code[7] = check($code,7);
			break;
		case 2:
		case 3:
			$code[1] = $mode;
			$code[2] = $id[0];
			$code[8] = $id[1];
			$code[13] = $id[2];
			$code[7] = check($code,7);
			break;
		case 4:
		case 5:
			$code[10] = $mode;
			$code[11] = $id[0];
			$code[7] = $id[1];
			$code[3] = $id[2];
			$code[4] = check($code,4);
			break;
		case 6:
		case 7:
			$code[13] = $mode;
			$code[12] = $id[0];
			$code[2] = $id[1];
			$code[1] = $id[2];
			$code[11] = check($code,11);
			break;
		case 8:
		case 9:
			$code[10] = $mode;
			$code[8] = $id[0];
			$code[4] = $id[1];
			$code[2] = $id[2];
			$code[1] = check($code,1);
			break;
	}
	echo("tapa=".$code);
}
?>