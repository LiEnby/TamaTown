<?php
$ResultCode="OK";

$type = 0;
if(isset($_GET["c"]))
{
	if(is_numeric($_GET["c"]))
	{
		$type = (int)$_GET["c"];
	}
}


function startsWith ($string, $startString) 
{ 
    $len = strlen($startString); 
    if(substr($string, 0, $len) === $startString)
	{
		return true;
	}
	else
	{
		return false;
	}
} 

function read_ini_value(string $ini, string $category, string $key)
{
	$stats = fopen($ini, "r");

		
	do{
		$line = fgets($stats);
		if(feof($stats))
		{
			fclose($stats);
			return false;
		}
	}
	while($line != "[".$category."]\n");
		
	do{
		$line = fgets($stats);
		
		if(startsWith($line,"["))
		{
			fclose($stats);
			return "";
		}
		if(feof($stats))
		{
			fclose($stats);
			return "";
		}
	}
	while(!startsWith($line,$key));
	$values = explode("=",$line);
	fclose($stats);
	
	return str_replace("\n","",$values[1]);
}

function write_ini_value(string $ini, string $category, string $key, string $value)
{
	$sz = filesize($ini);
	$stats = fopen($ini, "r+");

		
	do{
		$line = fgets($stats);
		if(feof($stats))
		{
			fclose($stats);
			return false;
		}
	}
	while($line != "[".$category."]\n");
		
	do{
		$line = fgets($stats);
		
		if(startsWith($line,"["))
		{
			fclose($stats);
			return false;
		}
		if(feof($stats))
		{
			fclose($stats);
			return false;
		}
	}
	while(!startsWith($line,$key));
	
	$curPos = ftell($stats);
	
	$remaining = $sz - $curPos;
	$after = fread($stats,$remaining);
	
	$nextPos = $curPos-strlen($line);
	
	fseek($stats,$nextPos,SEEK_SET);
	fwrite($stats,$key."=".$value."\n");
	fwrite($stats,$after);
	
	fclose($stats);
	return true;
}

function get_poll_result(string $country)
{
	//Get Total Vote Count
	$TotalVotes = 0;
	for($i = 1; $i<=6; $i++)
	{
		$TotalVotes += (int)read_ini_value("stats.ini",$country,$i);
	}
	
	$WWAnswer = "";
	for($i = 1; $i<=6; $i++)
	{
		$WWA = (int)read_ini_value("stats.ini",$country,$i);
		if($WWA != 0 || $TotalVotes != 0)
		{
			$WWA = (int)floor(($WWA / $TotalVotes)*100);
		}
		$WWAnswer .= (string)$i."_".(string)$WWA;
		if($i != 6)
		{
			$WWAnswer .= ",";
		}
	}
	return $WWAnswer;
}
switch($type)
{
	case 3: //login
	$ResultCode="OK";
	$DateTime = date('Ymd', time());
	break;
	case 5: //survey entry
	$country = "";
	$key = 0;
	$character = 0;
	
	//Read "f"
	if(isset($_GET["f"]))
	{
		if(is_string($_GET["f"]))
		{
			$country = $_GET["f"];
		}
		else
		{
			$ResultCode="ERROR";
			break;
		}
	}
	else
	{
		$ResultCode="ERROR";
		break;
	}
	
	//Read "e"
	if(isset($_GET["e"]))
	{
		if(is_numeric($_GET["e"]))
		{
			$key = (int)$_GET["e"];
		}
		else
		{
			$ResultCode="ERROR";
			break;
		}
	}
	else
	{
		$ResultCode="ERROR";
		break;
	}
	
	//Read "a"
	if(isset($_GET["a"]))
	{
		if(is_numeric($_GET["a"]))
		{
			$character = $_GET["a"];
		}
		else
		{
			$ResultCode="ERROR";
			break;
		}
	}
	else
	{
		$ResultCode="ERROR";
		break;
	}
	
	// Sanitize user input (no exploit for u)
	if($character < 1 || $character > 6)
	{
		$ResultCode="ERROR";
		break;
	}
	if($key != 2)
	{
		$ResultCode="ERROR";
		break;
	}
	if(!($country == "NA" || $country == "EU" || $country == "JP" || $country == "AO"))
	{
		$ResultCode="ERROR";
		break;
	}
	
	// Write new statistics (finally)
	if(!file_exists("stats.ini")) //initalize
	{
		$stats = fopen("stats.ini", "w+");
		fwrite($stats,"[JP]\n");
		for($i = 1;$i<=6;$i++)
		{
			fwrite($stats,(string)$i."=0\n");
		}
		
		fwrite($stats,"[NA]\n");
		for($i = 1;$i<=6;$i++)
		{
			fwrite($stats,(string)$i."=0\n");
		}
		
		fwrite($stats,"[EU]\n");
		for($i = 1;$i<=6;$i++)
		{
			fwrite($stats,(string)$i."=0\n");
		}
		
		fwrite($stats,"[AO]\n");
		for($i = 1;$i<=6;$i++)
		{
			fwrite($stats,(string)$i."=0\n");
		}
		fclose($stats);
	}
	
	$value = read_ini_value("stats.ini",$country,(string)$character);
	if($value == "")
	{
		error_log("Read_Ini_Failed!".$country.$character);
		$ResultCode="ERROR";
		break;
	}
	$newVal = (int)$value+1;
	$ret = write_ini_value("stats.ini",$country,(string)$character,(string)$newVal);
	if($ret == false)
	{
		error_log("Write_Ini_Failed!");
		$ResultCode="ERROR";
		break;
	}
	
	//Get Poll Results
	$JPAnswer = get_poll_result("JP");
	$NAAnswer = get_poll_result("NA");
	$EUAnswer = get_poll_result("EU");
	$AOAnswer = get_poll_result("AO");
	
	break;
	
	default: //unimplemented - just say success
	$ResultCode="OK";
	break;
}
	


$output= Array(
	"ResultCode" => $ResultCode
);
if(isset($JPAnswer))
{
	$output["JPAnswer"] = $JPAnswer;
}
if(isset($NAAnswer))
{
	$output["NAAnswer"] = $NAAnswer;
}
if(isset($EUAnswer))
{
	$output["EUAnswer"] = $EUAnswer;
}
if(isset($AOAnswer))
{
	$output["AOAnswer"] = $AOAnswer;
}
if(isset($DateTime))
{
	$output["DateTime"] = $DateTime;
}

echo(urldecode(http_build_query($output)));


?>