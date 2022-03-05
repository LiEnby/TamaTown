import requests
import urllib
import hmac
import hashlib
import time
import json

headers = {"APICODE": "-",
            "Content-Type": "application/x-www-form-urlencoded"}

s = requests.Session()

while True:
    jdata = input("Enter str: ");
    
    jj = json.loads(jdata)
    jj['t'] = int(time.time())
    jdata = json.dumps(jj)
    
    headers["APICODE"] = hmac.new(bytes("695719951020924", "UTF-8"), msg = bytes(jdata , 'UTF-8'), digestmod = hashlib.sha256).hexdigest().upper()
    
    edata = "json="+urllib.parse.quote_plus(jdata)
    
    print(s.post("https://tmgcmeetsapp.com/api_en/meets.php", 
        headers=headers, 
        data=edata).text)