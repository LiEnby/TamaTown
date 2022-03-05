import requests
import os


def incDate(date):
    yr = int(date[0:2])
    mo = int(date[2:4])
    dy = int(date[4:6])
    
    dy += 1
    if dy > 32:
        dy = 0
        mo += 1
    
    if mo > 12:
        dy = 0
        mo = 0
        yr += 1
    
    if yr > 22:
        os._exit(0)
    
    ndate = ""
    ndate += str(yr).zfill(2)
    ndate += str(mo).zfill(2)
    ndate += str(dy).zfill(2)
    
    return ndate
    


s = requests.Session()
date = "180000"
log = open("urls.txt", "w")

while True:
    url = "https://tmgcmeetsapp.com/page/en_eventinfo_"+date+".html"
    r = s.head(url)
    if r.status_code == 200:
        print("FOUND: "+url)
        log.write(url + "\n")
        log.flush()
    else:
        print("Nothing on "+date)
    date = incDate(date)