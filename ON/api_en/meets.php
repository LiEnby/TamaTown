<?php
// == COMMANDS ==
define("COMMAND_AUTH", 11);
define("COMMAND_GET_HOME", 12);
define("COMMAND_PARENTAL_CONTROL", 30);
define("COMMAND_SAVE_CHAR_DATA", 44);
define("COMMAND_GET_PROFILE", 46);
define("COMMAND_GET_NOTEBOOK", 51);
define("COMMAND_GET_SETTINGS", 58);

// == STATUS ==
define("STATUS_INVALID_JSON", 9);
define("STATUS_INVALID_AUTH", 7);
define("STATUS_INVALID_LOGINCODE", 8);
define("STATUS_OK", 0);

// == USER TYPES ==

define("USER_GUEST", 0);
define("USER_BNID", 1);
define("USER_LINE", 2);
define("USER_MIX", 3);
define("USER_MIX2", 4);


// == API CONSTANTS == 
define("ENGLISH_API", true);
define("API_KEY", "695719951020924");

define("FLAG_ON", "ON");
define("FLAG_OFF", "OFF");

define("DEFAULT_BNID_NAME", "");
define("DEFAULT_BNID_FLAG", FLAG_OFF);

define("MOVING_FLAG", 0);
define("TERMS_COUNT", "2");


// == DATABASE CONFIG ==

$dbname = 'MeetsEn';
$dbuser = 'root';
$dbpass = 'kingofitall413';
$dbhost = '127.0.0.1';

// == FUNCTIONS ==

$connect = NULL;
$gameData = json_decode(file_get_contents('data.json'));
$result = array( "ResultCode" => STATUS_INVALID_JSON );


if(ENGLISH_API)
	$gameData = $gameData->EN;
else
	$gameData = $gameData->JP;

function ReadGameSettings(){
	global $gameData;
	return $gameData;
}

function DbConnect(){
	global $connect;
	global $dbhost;
	global $dbuser;
	global $dbpass;
	global $dbname;
	if($connect === NULL)
		$connect = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
	return $connect;
}

function populate_db()
{
	$connect = DbConnect();
	mysqli_query($connect, "CREATE TABLE IF NOT EXISTS Users(
															UserId INT,
															LoginSecret TEXT, 
															CustomerId BIGINT, 
															LastLoginCode TEXT, 
															BnidName TEXT, 
															BnidFlag TEXT, 
															UserKind INT, 
															UserLang INT,
															LastLogin INT)");
															
	mysqli_query($connect, "CREATE TABLE IF NOT EXISTS ParentalControls(
															UserId INT,
															DisableSearchTarget INT, 
															DisableViewSearchFriend INT,
															DisableStampArea INT)");
												
	mysqli_query($connect, "CREATE TABLE IF NOT EXISTS Profiles(
															UserId INT,
															CharaData TEXT,
															CharaToyKey TEXT,															
															LikeCount INT, 
															LikeTotal INT,
															AddressCode INT,
															Sex INT,
															Body INT,
															Face INT, 
															Hair INT,
															Top INT,
															Bottom INT,
															UserCode TEXT)");

	mysqli_query($connect, "CREATE TABLE IF NOT EXISTS HomeInfo(
															UserId INT,
															Point INT,
															EventPoint INT,
															SearchFlag INT)");
															
	mysqli_query($connect, "CREATE TABLE IF NOT EXISTS TutorialsCompleted(
															UserId INT,
															TutorialId INT,
															TutorialData TEXT,
															TutorialFlag INT,
															TutorialGuestFlag INT,
															TutorialMeetsFlag INT)");
	
	mysqli_query($connect, "CREATE TABLE IF NOT EXISTS ChallangesCompleted(
															UserId INT,
															ChallangeId INT,
															UserCount INT,
															UserReward1Flag INT,
															UserReward2Flag INT,
															UserReward3Flag INT,
															UserReward4Flag INT,
															UserReward5Flag INT,
															UserReward6Flag INT,
															UserReward7Flag INT,
															UserReward8Flag INT,
															UserReward9Flag INT,
															UserReward10Flag INT)");

	mysqli_query($connect, "CREATE TABLE IF NOT EXISTS Notebook(
															UserId INT,
															NoteId INT,
															RefUserId INT)");
}

function generate_usercode(){
	$valid_chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$total_valid = strlen($valid_chars);
	
	$ascii_part = "";
	for($i = 0; $i < 3; $i++){
		$ascii_part .= $valid_chars[random_int(0, $total_valid-1)];
	}

	$id_part = random_int(100000, 999999);
	
	return $ascii_part . strval($id_part);
}

function add_user($userId, $loginSecret, $customerId, $lastLoginCode, $bnidName, $bnidFlag, $userKind, $userLang)
{
	$connect = DbConnect();
	mysqli_begin_transaction($connect);
	// Create users table entry
	$loginDate = time();
	$stmt = $connect->prepare("INSERT INTO Users VALUES(?,?,?,?,?,?,?,?,?)");
	$stmt->bind_param("isssssiii", $userId,
								$loginSecret,
								$customerId, 
								$lastLoginCode,
								$bnidName, 
								$bnidFlag, 
								$userKind, 
								$userLang,
								$loginDate);
	$stmt->execute();
	
	// Create parental controls table entry
	$stmt = $connect->prepare("INSERT INTO ParentalControls VALUES(?,0,0,0)");
	$stmt->bind_param("i", $userId);
	$stmt->execute();

	// Create profile entry
	$userCode = generate_usercode();
	$stmt = $connect->prepare("INSERT INTO Profiles VALUES(?, '', 0, 0, 0, 100, 1, 1101, 1203, 1301, 1401, 1508, ?)");
	$stmt->bind_param("is", $userId, $userCode);
	$stmt->execute();

	// Create home entry
	$userCode = generate_usercode();
	$stmt = $connect->prepare("INSERT INTO HomeInfo VALUES(?, 0, 0, 0)");
	$stmt->bind_param("i", $userId);
	$stmt->execute();

	// Initalize Tutorials
	foreach(ReadGameSettings()->Tutorials as $tutorial){
		$stmt = $connect->prepare("INSERT INTO TutorialsCompleted VALUES(?, ?, '', 0, 0, 0)");
		$stmt->bind_param("ii", $userId, $tutorial);
		$stmt->execute();	
	}
	
	// Initalize Challanges
	foreach(ReadGameSettings()->Challanges as $challange){
		$chid = intval($challange->id);
		
		$stmt = $connect->prepare("INSERT INTO ChallangesCompleted VALUES(?, ?, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0)");
		$stmt->bind_param("ii", $userId, $chid);
		$stmt->execute();
	}
	
	mysqli_commit($connect);
}

function set_last_login_date($userId){
	$connect = DbConnect();
	$lognDate = time();
	$stmt = $connect->prepare("UPDATE Users SET LastLogin=? WHERE UserId=?");
	$stmt->bind_param("ii", $loginDate,
							$userId);
	$stmt->execute();
}

function set_last_login_code($userId, $lastLoginCode){
	$connect = DbConnect();
	$stmt = $connect->prepare("UPDATE Users SET LastLoginCode=? WHERE UserId=?");
	$stmt->bind_param("si", $lastLoginCode,
							$userId);
	$stmt->execute();
	
}

function validate_login_code($userId, $gotLoginCode){
	$connect = DbConnect();
	
	$stmt = $connect->prepare("SELECT LastLoginCode	FROM Users WHERE UserId=?");
	$stmt->bind_param("i", $userId);
	$stmt->execute();
	$result = $stmt->get_result();
	
	return ($result->fetch_row()[0] === $gotLoginCode);
}

function get_user_parental_control($userId)
{
	$connect = DbConnect();
	$stmt = $connect->prepare("SELECT * FROM ParentalControls WHERE UserId=?");
	$stmt->bind_param("i", $userId);
	$stmt->execute();
	$result = $stmt->get_result();
	return $result->fetch_row();
}

function get_profile_data($userId)
{
	$connect = DbConnect();
	$stmt = $connect->prepare("SELECT * FROM Profiles WHERE UserId=?");
	$stmt->bind_param("i", $userId);
	$stmt->execute();
	$result = $stmt->get_result();
	return $result->fetch_row();
}

function get_notebook_data($userId)
{
	$connect = DbConnect();
	$stmt = $connect->prepare("SELECT * FROM Notebook WHERE UserId=?");
	$stmt->bind_param("i", $userId);
	$stmt->execute();
	$result = $stmt->get_result();
	return $result->fetch_all();
}


function get_user_tutorial_info($userId, $tutorialId){
	$connect = DbConnect();
	$stmt = $connect->prepare("SELECT * FROM TutorialsCompleted WHERE UserId=? AND TutorialId=?");
	$stmt->bind_param("ii", $userId, $tutorialId);
	$stmt->execute();
	$result = $stmt->get_result();
	return $result->fetch_row();
}


function get_user_home_info($userId){
	$connect = DbConnect();
	$stmt = $connect->prepare("SELECT * FROM HomeInfo WHERE UserId=?");
	$stmt->bind_param("i", $userId);
	$stmt->execute();
	$result = $stmt->get_result();
	return $result->fetch_row();
}

function get_user_challange_info($userId, $challangeId){
	$connect = DbConnect();
	$stmt = $connect->prepare("SELECT * FROM ChallangesCompleted WHERE UserId=? AND ChallangeId=?");
	$stmt->bind_param("i", $userId, $challangeId);
	$stmt->execute();
	$result = $stmt->get_result();
	return $result->fetch_row();
}


function get_user_info($userId)
{
	$connect = DbConnect();
	$stmt = $connect->prepare("SELECT * FROM Users WHERE UserId=?");
	$stmt->bind_param("i", $userId);
	$stmt->execute();
	$result = $stmt->get_result();
	return $result->fetch_row();
}

function get_latest_userid(){
	$connect = DbConnect();
	$result = mysqli_query($connect, "SELECT MAX(UserId)+1 FROM Users");
	$userId = $result->fetch_row()[0];
	if($userId == NULL)
		$userId = 1;
	
	return $userId;
}

function set_chara_data($userId, $charaData){
	$connect = DbConnect();

	$stmt = $connect->prepare("UPDATE Profiles SET CharaData=? WHERE UserId=?");
	$stmt->bind_param("si", $charaData,
							$userId);
	$stmt->execute();

}


function set_user_kind($userId, $userKind){
	$connect = DbConnect();

	$stmt = $connect->prepare("UPDATE Users SET UserKind=? WHERE UserId=?");
	$stmt->bind_param("ii", $charaData,
							$userId);
	$stmt->execute();

}

function set_results_defaults($c){
	global $result;
	$result['ServerDate'] = date("Ymdihs");
	$result['c'] = intval($c);
}

function set_user_challange_count($userId, $challangeId, $userCount){
	$connect = DbConnect();

	$stmt = $connect->prepare("UPDATE ChallangesCompleted SET UserCount=? WHERE UserId=? AND ChallangeId=?");
	$stmt->bind_param("iii",$userCount,
							$userId,
							$challangeId);
	$stmt->execute();

}



function set_user_reward_1($userId, $challangeId){
	$connect = DbConnect();
	$stmt = $connect->prepare("UPDATE ChallangesCompleted SET UserReward1Flag=1 WHERE UserId=? AND ChallangeId=?");
	$stmt->bind_param("ii", $userId,
							$challangeId);
	$stmt->execute();

}
function set_user_reward_2($userId, $challangeId){
	$connect = DbConnect();
	$stmt = $connect->prepare("UPDATE ChallangesCompleted SET UserReward2Flag=1 WHERE UserId=? AND ChallangeId=?");
	$stmt->bind_param("ii", $userId,
							$challangeId);
	$stmt->execute();

}
function set_user_reward_3($userId, $challangeId){
	$connect = DbConnect();
	$stmt = $connect->prepare("UPDATE ChallangesCompleted SET UserReward3Flag=1 WHERE UserId=? AND ChallangeId=?");
	$stmt->bind_param("ii", $userId,
							$challangeId);
	$stmt->execute();

}
function set_user_reward_4($userId, $challangeId){
	$connect = DbConnect();
	$stmt = $connect->prepare("UPDATE ChallangesCompleted SET UserReward4Flag=1 WHERE UserId=? AND ChallangeId=?");
	$stmt->bind_param("ii", $userId,
							$challangeId);
	$stmt->execute();

}
function set_user_reward_5($userId, $challangeId){
	$connect = DbConnect();
	$stmt = $connect->prepare("UPDATE ChallangesCompleted SET UserReward5Flag=1 WHERE UserId=? AND ChallangeId=?");
	$stmt->bind_param("ii", $userId,
							$challangeId);
	$stmt->execute();

}
function set_user_reward_6($userId, $challangeId){
	$connect = DbConnect();
	$stmt = $connect->prepare("UPDATE ChallangesCompleted SET UserReward6Flag=1 WHERE UserId=? AND ChallangeId=?");
	$stmt->bind_param("ii", $userId,
							$challangeId);
	$stmt->execute();

}
function set_user_reward_7($userId, $challangeId){
	$connect = DbConnect();
	$stmt = $connect->prepare("UPDATE ChallangesCompleted SET UserReward7Flag=1 WHERE UserId=? AND ChallangeId=?");
	$stmt->bind_param("ii", $userId,
							$challangeId);
	$stmt->execute();

}
function set_user_reward_8($userId, $challangeId){
	$connect = DbConnect();
	$stmt = $connect->prepare("UPDATE ChallangesCompleted SET UserReward8Flag=1 WHERE UserId=? AND ChallangeId=?");
	$stmt->bind_param("ii", $userId,
							$challangeId);
	$stmt->execute();

}
function set_user_reward_9($userId, $challangeId){
	$connect = DbConnect();
	$stmt = $connect->prepare("UPDATE ChallangesCompleted SET UserReward9Flag=1 WHERE UserId=? AND ChallangeId=?");
	$stmt->bind_param("ii", $userId,
							$challangeId);
	$stmt->execute();

}
function set_user_reward_10($userId, $challangeId){
	$connect = DbConnect();
	$stmt = $connect->prepare("UPDATE ChallangesCompleted SET UserReward10Flag=1 WHERE UserId=? AND ChallangeId=?");
	$stmt->bind_param("ii", $userId,
							$challangeId);
	$stmt->execute();

}



function earn_challange($userId, $challangeId){
	foreach(ReadGameSettings()->Challanges as $challange){
		if($challange->id == $challangeId){
			$cinfo = get_user_challange_info($userId, $challangeId);
			$cres = array( 
							'id' => strval($challange->id),
							'title' => $challange->title,
							'kind' => strval($challange->kind),
							'last_flag' => 0
                		 );
			
			set_user_challange_count($userId, $challangeId, $cinfo[2] + 1);
			
			if($cinfo[3] == 0){
				set_user_reward_1($userId, $challangeId);
				$cres['reward_no'] = 1;
				$cres['achiv'] = $challange->reward1;
			}
			else if($cinfo[4] == 0){
				set_user_reward_2($userId, $challangeId);
				$cres['reward_no'] = 2;
				$cres['achiv'] = $challange->reward2;
			}
			else if($cinfo[5] == 0){
				set_user_reward_3($userId, $challangeId);
				$cres['reward_no'] = 3;
				$cres['achiv'] = $challange->reward3;
			}
			else if($cinfo[6] == 0){
				set_user_reward_4($userId, $challangeId);
				$cres['reward_no'] = 4;
				$cres['achiv'] = $challange->reward4;
			}
			else if($cinfo[7] == 0){
				set_user_reward_5($userId, $challangeId);
				$cres['reward_no'] = 5;
				$cres['achiv'] = $challange->reward5;
			}
			else if($cinfo[8] == 0){
				set_user_reward_6($userId, $challangeId);
				$cres['reward_no'] = 6;
				$cres['achiv'] = $challange->reward6;
			}
			else if($cinfo[9] == 0){
				set_user_reward_7($userId, $challangeId);
				$cres['reward_no'] = 7;
				$cres['achiv'] = $challange->reward7;
			}
			else if($cinfo[10] == 0){
				set_user_reward_8($userId, $challangeId);
				$cres['reward_no'] = 8;
				$cres['achiv'] = $challange->reward8;
			}
			else if($cinfo[11] == 0){
				set_user_reward_9($userId, $challangeId);
				$cres['reward_no'] = 9;
				$cres['achiv'] = $challange->reward9;
			}
			else if($cinfo[12] == 0){
				set_user_reward_10($userId, $challangeId);
				$cres['reward_no'] = 10;
				$cres['achiv'] = $challange->reward10;
				$cres['last_flag'] = 1;
			}
			
			$cres['reward'] = explode(",", $cres['achiv'])[1];
			
			return array( strval($challange->id) => $cres );
		}
	}
}

function check_acheivements($userData){
	global $result;
	$curDate = time();
	$lastLognDate = $userData[8];
	$diff = $curDate - $lastLognDate;
	$result['AchievementRewardList'] = array();
	// Add login bonus acheivements
	if( ($diff >= 86400) && ($diff < 172800) ){
		foreach(ReadGameSettings()->Challanges as $challange){
			if($challange->kind == 3){
				$cinfo = earn_challange($userData[0], $challange->id);
				array_push($result['AchievementRewardList'], $cinfo);
			}
		}
	}
		
	set_last_login_date($userData[0]);
}

populate_db();

// == API Logic ==

if(isset($_SERVER["HTTP_APICODE"])) {
	$apicode = $_SERVER["HTTP_APICODE"];
	if(isset($_POST["json"])) {
		$json = $_POST["json"];
		$signature = strtoupper(hash_hmac('sha256', $json, API_KEY));
		if(hash_equals($apicode, $signature)){
			// Signature validation successful

			$jdata = json_decode($json);

			switch(intval($jdata->c)){
				case COMMAND_GET_SETTINGS:
					set_results_defaults($jdata->c);
					$result['ResultCode'] = STATUS_INVALID_LOGINCODE;
					$result['MenteFlag'] = ReadGameSettings()->MenteFlag;
					
					if(validate_login_code($jdata->u, $jdata->z)){					
						$result['EventCollabAsset'] = strval(ReadGameSettings()->EventCollabAsset);
						$result['EventGameId'] = implode(",",ReadGameSettings()->EventGameId);
						$result['EventMonthlyId'] = strval(ReadGameSettings()->EventMonthlyId);
						
						$result['GameAsset'] = strval(ReadGameSettings()->GameAsset);
						$result['ParkAsset'] = strval(ReadGameSettings()->ParkAsset);
						$result['PartyAsset'] = strval(ReadGameSettings()->PartyAsset);

						$result['ResultCode'] = STATUS_OK;
					}					
				case COMMAND_GET_NOTEBOOK:
					set_results_defaults($jdata->c);
					$result['ResultCode'] = STATUS_INVALID_LOGINCODE;
					if(validate_login_code($jdata->u, $jdata->z)){					
						$result['NotebookList'] = array();
						
						$ndata = get_notebook_data($jdata->u);
						foreach($ndata as $note){
							$nres = array(
								"notebook_id" => strval($ndata[1]),
								"profile_uid" => strval($ndata[2])
							);
									
							$pdata = get_profile_data($ndata[2]);
							$udata = get_user_info($ndata[2]);
							
							// Read profile data
							$nres['chara_data'] = $pdata[1];
							$nres['chara_toy_key'] = $pdata[2];
							$nres['address_code'] = strval($pdata[5]);
							$nres['avatar_data'] = $pdata[6].",".$pdata[7].",".$pdata[8].",".$pdata[9].",".$pdata[10].",".$pdata[11];
							$nres['profile_user_code'] = $pdata[12];

							// Read user data
							$nres['profile_user_kind'] = strval($udata[6]);
							$nres['profile_user_lang'] = strval($udata[7]);
							
							array_push($result['NotebookList'], array( strval($ndata[1]) => $nres ));
						}
						
						$result['ResultCode'] = STATUS_OK;
						$result['ResultDetail'] = STATUS_OK;

					}
					break;
				case COMMAND_GET_HOME:
					set_results_defaults($jdata->c);
					$result['ResultCode'] = STATUS_INVALID_LOGINCODE;
					if(validate_login_code($jdata->u, $jdata->z)){
						// Check achievements
						$userData = get_user_info($jdata->u);
						
						$userId = $userData[0];
						check_acheivements($userData);
						
						// Read home info
						$userHome = get_user_home_info($userId);
						$result['Point'] = strval($userHome[1]);
						$result['EventPoint'] = strval($userHome[2]);
						$result['SearchFlag'] = strval($userHome[3]);
						
						// Read Tutorials
						$result['TutorialList'] = array();
						foreach(ReadGameSettings()->Tutorials as $tutorialId){
							$userTutorals = get_user_tutorial_info($userId, $tutorialId);
							$tinfo = array(strval($tutorialId) => array(
								"tutorial_data" => $userTutorals[2],
								"tutorial_flag" => $userTutorals[3],
								"tutorial_guset_flag" => $userTutorals[4],
								"tutorial_id" => $tutorialId,
								"tutorial_meets_flag" => $userTutorals[5]
							));
							array_push($result['TutorialList'], $tinfo);
						}
						
						$result['ResultCode'] = STATUS_OK;

					}
					break;
				case COMMAND_GET_PROFILE:
					set_results_defaults($jdata->c);
					
					$result['ResultCode'] = STATUS_INVALID_LOGINCODE;
					
					if(validate_login_code($jdata->u, $jdata->z)){
						
						$pdata = get_profile_data($jdata->u);
						
						// Return profile data
						$result['CharaData'] = $pdata[1];
						$result['CharaToyKey'] = $pdata[2];
						
						$result['LikeCount'] = $pdata[3];
						$result['LikeTotal'] = $pdata[4];
						
						$result['ProfileAddressCode'] = $pdata[5];
						$result['ProfileAvatarData'] = $pdata[6].",".$pdata[7].",".$pdata[8].",".$pdata[9].",".$pdata[10].",".$pdata[11];
						$result['UserCode'] = $pdata[12];
						
						// Give ResultDetail too.. for some reason
						$result['ResultDetail'] = STATUS_OK;
						$result['ResultCode'] = STATUS_OK;
					}				
				break;
				case COMMAND_SAVE_CHAR_DATA: 
					set_results_defaults($jdata->c);
					
					$result['ResultCode'] = STATUS_INVALID_LOGINCODE;
					
					if(validate_login_code($jdata->u, $jdata->z)){
						// TODO: add validation
						set_user_kind($jdata->u, $jdata->k);
						set_chara_data($jdata->u, $jdata->d);
						
						// Return profile data
						$pdata = get_profile_data($jdata->u);
						
						$result['ProfileAddressCode'] = $pdata[5];
						$result['ProfileAvatarData'] = $pdata[6].",".$pdata[7].",".$pdata[8].",".$pdata[9].",".$pdata[10].",".$pdata[11];
						$result['UserCode'] = $pdata[12];
						
						// Give ResultDetail too..
						$result['ResultDetail'] = STATUS_OK;
						$result['ResultCode'] = STATUS_OK;
					}
				break;
				case COMMAND_PARENTAL_CONTROL: 
					set_results_defaults($jdata->c);
					
					$result['ResultCode'] = STATUS_INVALID_LOGINCODE;
					
					if(validate_login_code($jdata->u, $jdata->z)){
						$pinfo = get_user_parental_control($jdata->u);
						
						// Why are we dealing with overzealus parents?
						
						$result['ParentExcluded'] = $pinfo[0];
						$result['ParentSearch'] = $pinfo[1];
						$result['ParentStamp'] = $pinfo[2];
						
						$result['ResultCode'] = STATUS_OK;
					}
				break;
				
				
				
				// Special Case:
				// Login to the app	
				case COMMAND_AUTH: 
					set_results_defaults($jdata->c);
					
					$result['ResultCode'] = STATUS_INVALID_AUTH;

					$result['AppVersion'] = ReadGameSettings()->AppVer;
					$result['MovingFlag'] = MOVING_FLAG;
					$result['MenteFlag'] = ReadGameSettings()->MenteFlag;
					$result['TermsCount'] = TERMS_COUNT;
					$result['AssetVersion'] = ReadGameSettings()->GameAsset;

					
					if($jdata->u == 0) { // Register
						
						$result['ResultCode'] = STATUS_OK;
						
						// Set default language and userkind
						$result['UserLang'] = intval(ENGLISH_API);
						$result['UserKind'] = strval(USER_GUEST);
						
						// Return InfoHtml and EventInfoHtml.
						$result['InfoHtml'] = ReadGameSettings()->InfoHtml;			
						$result['EventInfoHtml'] = ReadGameSettings()->EventInfoHtml;

						// No Bandai Namco ID used by default.
						$result['BnidName'] = DEFAULT_BNID_NAME;
						$result['BnidFlag'] = DEFAULT_BNID_FLAG;
						
						
						// Generate LoginCode "Token"
						$result['LoginCode'] = bin2hex(random_bytes(0x14));
						
						// Generate Customer ID
						$loginSecret = bin2hex(random_bytes(0x18));						
						$customerId = random_int(1000000000000000,9999999999999999);
						$result['AppCode'] = $loginSecret.'.'.$customerId;
						
						// Generate UserID
						$result['UserId'] = strval(get_latest_userid());
						
						// Add to database.
						add_user($result['UserId'], 
								$loginSecret, 
								strval($customerId), 
								$result['LoginCode'],
								$result['BnidName'], 
								$result['BnidFlag'], 
								$result['UserKind'], 
								$result['UserLang']);
					}
					else{
						
						// Read user info from database, 
						$userInfo = get_user_info($jdata->u);
						
						// Read user secrets
						$loginSecret = $userInfo[1];
						$customerId = $userInfo[2];

						// Generate App Codes
						$expectedAppCode = $loginSecret.'.'.$customerId;
						
						// Check if correct secrets were given:
						if($expectedAppCode === $jdata->q) {
							$result['ResultCode'] = STATUS_OK;
							
							// Read User Id
							$result['UserId'] = $userInfo[0];

							// Set default language and userkind
							$result['UserLang'] = $userInfo[7];
							$result['UserKind'] = strval($userInfo[6]);
							
							// Return InfoHtml and EventInfoHtml.
							$result['InfoHtml'] = ReadGameSettings()->InfoHtml;			
							$result['EventInfoHtml'] = ReadGameSettings()->EventInfoHtml;

							// Get Bandai Namco Id Information
							$result['BnidName'] = $userInfo[4];
							$result['BnidFlag'] = $userInfo[5];
							
							// Generate LoginCode "Token"
							$result['LoginCode'] = bin2hex(random_bytes(0x14));

							// Return Expected AppCode
							$result['AppCode'] = $expectedAppCode;
						
							set_last_login_code($result['UserId'], $result['LoginCode']);
						}
					}
					break;
				default:
					break;
			}

		}
	}
}



header("Content-Type: application/json");
echo(json_encode($result));

?>