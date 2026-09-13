<?php
function check(string $code, $bitPos)
{
    $checksum = 0;
    for ($i = 0; $i < strlen($code); $i++) {
        if ($i == $bitPos) {
            continue;
        }
        $checksum += intval($code[$i]);
    }
    return $checksum % 10;
}

function verify_code(string $code)
{
    $type = intval($code[5]);
    
    $checkBitPos = 0;
    
    switch($type) {
        case 0:
        case 1:
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
    $calcBit = check($code, $checkBitPos);
    
    return ($checkBit == $calcBit);
}

function fail()
{
    print("mch=00");
    exit();
}

header("Content-Type: www-form-urlencoded");
session_start(); 

if (!isset($_POST["tapa"])) {
    fail();
}

$loginCode = $_POST["tapa"];

if (strlen($loginCode) != 14) {
    fail();
}

$_SESSION["loginCode"] = $loginCode;

if (verify_code($loginCode)) {
    $pattern = intval($loginCode[5]);
    if ($pattern === 0 || $pattern === 1) {
        $dest1 = $loginCode[8];
        $dest2 = $loginCode[0];
    } elseif ($pattern === 2 || $pattern === 3) {
        $dest1 = $loginCode[1];
        $dest2 = $loginCode[2];
    } elseif ($pattern === 4 || $pattern === 5) {
        $dest1 = $loginCode[10];
        $dest2 = $loginCode[11];
    } elseif ($pattern === 6 || $pattern === 7) {
        $dest1 = $loginCode[13];
        $dest2 = $loginCode[12];
    } elseif ($pattern === 8 || $pattern === 9) {
        $dest1 = $loginCode[10];
        $dest2 = $loginCode[8];
    } else {
        $dest1 = "0";
        $dest2 = "0";
    }
    $dest = $dest1 . $dest2;
    if (in_array($dest2, array("2", "3", "5"))) {
        $mde = "2";
        $acd = $dest1;
    } else {
        $mde = "1";
        $acd = "0";
    }
    $query = "mch=01&wrk=21&par=50&acd=" . $acd . "&npa=72&mde=" . $mde . "&xda=81&dte=68721579380456";
    print($query);
} else {
    fail();
}
?>
