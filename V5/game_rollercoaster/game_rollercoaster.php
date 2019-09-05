<?php
$width=0;
$height=0;
$data="";

if(isset($_POST["img_height"]))
{
	if(is_numeric($_POST["img_height"]))
	{
		$height=(int)$_POST["img_height"];
	}
	else
	{
		echo("img_height is NaN");
		exit();
	}
}
else
{
	echo("img_height is undefined");
	exit();
}

if(isset($_POST["img_width"]))
{
	if(is_numeric($_POST["img_width"]))
	{
		$width=(int)$_POST["img_width"];
	}
	else
	{
		echo("img_width is NaN");
		exit();
	}
}
else
{
	echo("img_width is undefined");
	exit();
}
if(isset($_POST["img_data"]))
{
	$data=$_POST["img_data"];
}
else
{
	echo("img_data is undefined");
	exit();
}

$img = imagecreatetruecolor($width,$height); 

$x = 0;
$y = 0;
$i = 0;

while($x < $width)
{
  $y = 0;
  while($y < $height)
  {
	$red = hexdec(substr($data,$i,2));
	$i += 2;
	
	$green = hexdec(substr($data,$i,2));
	$i += 2;
	
	$blue = hexdec(substr($data,$i,2));
	$i += 2;
	
	$color = imagecolorallocate($img,$red,$green,$blue);
	imagesetpixel($img,$x,$y,$color);
	
	 $y++;
  }
  $x++;
}

header('Content-Type: image/png');

//chrome doesnt like saving dynamically generated images so..
header('Content-Disposition: attachment; filename="'.md5(rand(0,256)).'.png"');

imagepng($img);
imagedestroy($img);
?>