import requests
import hmac
import json
import hashlib
import time
import urllib

data = {}

s = requests.Session()

headers = {"APICODE": "-",
            "Content-Type": "application/x-www-form-urlencoded"}

defaultApiReq = {"c":0,"u":0,"z":"","v":"","t":0,"k":0,"d":"","a":"","i":"","o":0,"b":0,"n":0,"w":"","m":"","q":"","p":0,"g":0,"x":0.0,"y":0.0,"e":0,"f":0,"s":0}



def sendEnApiRequest(jdata):
    jdata['t'] = int(time.time())
    jdata = json.dumps(jdata)
    
    headers["APICODE"] = hmac.new(bytes("695719951020924", "UTF-8"), msg = bytes(jdata , 'UTF-8'), digestmod = hashlib.sha256).hexdigest().upper()
    
    edata = "json="+urllib.parse.quote_plus(jdata)
    r = s.post("https://tmgcmeetsapp.com/api_en/meets.php", 
                headers=headers, 
                data=edata)
    print(jdata)
    print(r.content)
    time.sleep(1)
    return json.loads(r.content)
     


data['EN'] = {}
data['JP'] = {}


req = defaultApiReq
req['c'] = 11
req['v'] = '1.4.3'

enUser = sendEnApiRequest(req)

req['u'] = enUser['UserId']
req['z'] = enUser['LoginCode']

data['EN']['InfoHtml'] = enUser['InfoHtml']
data['EN']['EventInfoHtml'] = enUser['EventInfoHtml']
data['EN']['AppVer'] = enUser['AppVersion']

# Read Tutorials

req['c'] = 12
req['k'] = 0
cmd12 = sendEnApiRequest(req)
tutorialList = cmd12['TutorialList']
data['EN']['Tutorials'] = []
for tutorial in tutorialList.keys():
    data['EN']['Tutorials'].append(tutorialList[tutorial]['tutorial_id'])

# Read Game Settngs
req['c'] = 58
cmd58 = sendEnApiRequest(req)
data['EN']['EventCollabAsset'] = int(cmd58['EventCollabAsset'])
data['EN']['EventMonthlyId'] = int(cmd58['EventMonthlyId'])
data['EN']['EventGameId'] = []

for i in cmd58['EventGameId'].split(','):
    data['EN']['EventGameId'].append(int(i))

data['EN']['GameAsset'] = int(cmd58['GameAsset'])
data['EN']['ParkAsset'] = int(cmd58['ParkAsset'])
data['EN']['PartyAsset'] = int(cmd58['PartyAsset'])

data['EN']['MenteFlag'] = int(cmd58['MenteFlag'])

# Read Challanges
req['c'] = 55
cmd55 = sendEnApiRequest(req)

challangeList = cmd55['ChallengeList']
data['EN']['Challanges'] = []

for key in challangeList.keys():
    del challangeList[key]['user_count']
    del challangeList[key]['user_reward1_flag']
    del challangeList[key]['user_reward2_flag']
    del challangeList[key]['user_reward3_flag']
    del challangeList[key]['user_reward4_flag']
    del challangeList[key]['user_reward5_flag']
    del challangeList[key]['user_reward6_flag']
    del challangeList[key]['user_reward7_flag']
    del challangeList[key]['user_reward8_flag']
    del challangeList[key]['user_reward9_flag']
    del challangeList[key]['user_reward10_flag']
    data['EN']['Challanges'].append(challangeList[key])

open("data.json", "w").write(json.dumps(data, indent=4, sort_keys=True))