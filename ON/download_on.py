import requests
import os
import hashlib

s = requests.Session()

def genUrl(version, platform, file):
    return "https://tmgcmeetsapp.com/asset/"+version+"/"+platform+"/"+file

def genFiles(version, platform, file):
    return version+"/"+platform+"/"+file


def makeDirsFor(filename):
    dirPath = os.path.dirname(filename)
    if not os.path.exists(dirPath):
        os.makedirs(dirPath)
    
def md5(fname):
    hash_md5 = hashlib.md5()
    hash_md5.update(open(fname, "rb").read())    
    return hash_md5.hexdigest()

streamingassets = [
    "common/common.csv",
    "audio/audio.csv",
    "accessory/accessory.csv",
    "avatar/avatar.csv",
    "chara/chara.csv",
    "infos/infos.csv",
    "npc/npc.csv",
    "pet/pet.csv",
    "scenes/scenes.csv",
 
 
#    "chara/settings",
#    "accessory/settings",
#    "audio/se",
#    "avatar/sprites",

    
    "infos_en/infos_en.csv",
    "scenes_en/scenes_en.csv",
    
]

versions = ["v6", "v7"]
platforms = ["Android", "iOS"]

for version in versions:
    for platform in platforms:

        for streamingasset in streamingassets:
            url = genUrl(version, platform, streamingasset)
            print(url)
            r = s.get(url)
            
            if not r.status_code == 200:
                continue
            
            f = genFiles(version, platform, streamingasset)
            makeDirsFor(f)
            open(f, "wb").write(r.content)
             
            # Download all assets folder
            if streamingasset.endswith(".csv"):
                csvdata = r.content.replace(b"\r", b"").split(b'\n')
                for csv in csvdata:
                    if csv == b"":
                        continue
                    commas = csv.split(b',')
                    
                    relPath = commas[0].decode("UTF-8")
                    
                    url = genUrl(version, platform, relPath)
                    print(url)
                    f = genFiles(version, platform, relPath)
                    replace = False
                    
                    if not os.path.exists(f):
                        r = s.get(url)
                        
                        if not r.status_code == 200:
                            continue
                        
                        makeDirsFor(f)
                        open(f, "wb").write(r.content)
             