# Add <server_path>/friends/cgi-bin to $PYTHONPATH in /etc/enviroment and as a SetVar for your VirtualHost in apache2 

import mariadb
import binascii
import hashlib 


#MAKE SURE THE DB IS *OUTSIDE* THE PUBLIC_HTML!!!
#SQLLITE_DB_PATH = "/home/web/DreamTown.db"

SUCCESS = 1
USER_DOES_NOT_EXIST = 2
INVALID_PASSWORD = 3    
NAME_ALREADY_USED = 4   
ANSWER_INCORRECT = 5
	

def DbConnect():
    return mariadb.connect(
            user="root",
            password="DB_CREDENTIALS",
            host="127.0.0.1",
            port=3306,
            database="dreamtown"
    )
    
db = DbConnect()

def raise e:
    print("".join(traceback.TracebackException.from_exception(e).format())

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
	LastVisit bigint,
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

	

	
