# DreamTown uses PythonCGI ...
#
# Add <server_path>/friends/cgi-bin to $PYTHONPATH in your server config
# (its done using SetVar for your VirtualHost in apache2 and fastcgi_param in nginx)
#
# also define DT_DATABASE_USER, DT_DATABASE_PASSWORD, DT_DATABASE_HOST, DT_DATABASE_PORT and DT_DATABASE_NAME
# ... see example config for more info 

import mariadb
import json
import binascii
import hashlib
import os
import sys


SUCCESS = 1
USER_DOES_NOT_EXIST = 2
INVALID_PASSWORD = 3    
NAME_ALREADY_USED = 4   
ANSWER_INCORRECT = 5

# custom errors
INVALID_METHOD = 1020

def DbConnect():
    return mariadb.connect(
            user=os.environ.get("DT_DATABASE_USER"), 
            password=os.environ.get("DT_DATABASE_PASSWORD"), 
            host=os.environ.get("DT_DATABASE_HOST"), 
            port=int(os.environ.get("DT_DATABASE_PORT")), 
            database=os.environ.get("DT_DATABASE_NAME") 
    )
db = DbConnect()
   
def PrintHeaders():
    print("Content-Type: application/json")
    print("Access-Control-Allow-Headers: *")
    print("Access-Control-Allow-Origin: *")
    print("")
    

def EnsurePost():
    method = os.environ["REQUEST_METHOD"]
    
    if method != "POST":
        result = {"status":INVALID_METHOD, "message": "Unexpected method: "+method+ " expecting: POST"}
        print(json.dumps(result))
        sys.quit()

def xor(data, key):
    l = len(key)
    return bytearray((
        (data[i] ^ key[i % l]) for i in range(0,len(data))
    ))
	

def pass_salt_algo(passwd, Salt):
	m = hashlib.sha512()
	m.update(passwd.encode('utf-8'))
	passHash = m.digest()
	
	salt = bytearray(binascii.unhexlify(Salt))
	saltedHash = xor(passHash,salt);
	
	m = hashlib.sha512()
	m.update(saltedHash)
	outHash = m.digest();
	
	return binascii.hexlify(outHash).decode("utf-8")

c = db.cursor()

try:
	c.execute("""
	CREATE TABLE users(
	Name TEXT(12),
	PassHash TEXT(128),
	Salt TEXT(128),
	LastSession TEXT(128),
	CreationDate bigint
	);
	""")
except:
	pass
try:
	c.execute("""
	CREATE TABLE securityQuestion(
	Name TEXT(12),
	QuestionType int,
	AnswerHash TEXT(128)
	);
	""")
except:
	pass
try:
	c.execute("""
	CREATE TABLE characterList(
	Name TEXT(12),
	CharacterId int,
	ActualCharacterId int
	);
	""")
except:
	pass
try:
	c.execute("""
	CREATE TABLE relationsList(
	Name TEXT(12),
	CharacterId int,
	Level int,
	Progress int
	);
	""")
except:
	pass
try:
	c.execute("""
	CREATE TABLE npcList(
	Name TEXT(12),
	CharacterId int,
	NextTimestamp bigint,
	Pool TEXT(8024),
	RequestLevel int
	);
	""")
except:
	pass
try:
	c.execute("""
	CREATE TABLE areaList(
	Name TEXT(12),
	LastVisit int,
	AreaId int,
	NextRubishSpawnTime bigint,
	ActualAreaId int
	);
	""")
except:
	pass
try:
	c.execute("""
	CREATE TABLE itemList(
	Name TEXT(12),
	ItemId int,
	Quantity int
	);
	""")
except:
	pass
try:
	c.execute("""
	CREATE TABLE currencyList(
	Name TEXT(12),
	CurrencyId int,
	Quantity int
	);
	""")
except:
	pass
try:
	c.execute("""
	CREATE TABLE containerList(
	Name TEXT(12),
	HarvestableTemplateId int,
	LastHarvest bigint,
	ContainerName TEXT(128),
	AreaId int
	);
	""")
except:
	pass
try:
	c.execute("""
	CREATE TABLE harvestablesList(
	Name TEXT(12),
	ItemTemplateId int,
	UpdateTime bigint,
	SlotIndex int,
	HarvestableName TEXT(128),
	AreaId int,
	ParentContainerName TEXT(128)
	);
	""")
except:
	pass
try:
	c.execute("""
	CREATE TABLE rubishList(
	Name TEXT(12),
	Id Text(64),
	X int,
	Y int,
	AreaId int,
	ItemTemplateId int
	);
	""")
except:
	pass
try:
	c.execute("""
	CREATE TABLE tutorial(
	Name TEXT(12),
	TutorialTemplateId int
	);
	""")
except:
	pass
	
try:
	c.execute("""
	CREATE TABLE scenario(
	Name TEXT(12),
	ScenarioId int,
	CustomData TEXT(128),
	StepId int,
	Completed int
	);
	""")
except:
	pass
db.commit()
db.close()

	

	
