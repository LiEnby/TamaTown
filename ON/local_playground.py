import requests
import urllib
import hmac
import hashlib

headers = {"APICODE": "-",
            "Content-Type": "application/x-www-form-urlencoded"}

s = requests.Session()

while True:
    jdata = input("Enter str: ");
    headers["APICODE"] = hmac.new(bytes("695719951020924", "UTF-8"), msg = bytes(jdata , 'UTF-8'), digestmod = hashlib.sha256).hexdigest().upper()
    print(headers["APICODE"])
    
    edata = "json="+urllib.parse.quote_plus(jdata)
    
    print(s.post("http://127.0.0.1/api_en/meets.php", 
        headers=headers, 
        data=edata).text)