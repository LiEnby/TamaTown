# A script to update login hash to new format (more secure)

import sqlite3
import binascii
import hashlib

SQLLITE_DB_PATH = "DreamTown.db"

db = sqlite3.connect(SQLLITE_DB_PATH)	
c = db.cursor()
cur = c.execute("SELECT PassHash FROM users")
rows = cur.fetchall()

for row in rows:
	ogPassHash = row[0]
	
	passHashBin = binascii.unhexlify(ogPassHash)
	
	m = hashlib.sha512()
	m.update(passHashBin)
	PassHash = binascii.hexlify(m.digest()).decode("utf-8")
	
	print("P: "+str(ogPassHash) + " : " + str(PassHash))
	c.execute('UPDATE users SET PassHash=? WHERE PassHash=?',(PassHash,ogPassHash))

cur = c.execute("SELECT AnswerHash FROM securityQuestion")
rows = cur.fetchall()

for row in rows:
	ogAnswerHash = row[0]
	
	answerHashBin = binascii.unhexlify(ogAnswerHash)
	
	m = hashlib.sha512()
	m.update(answerHashBin)
	AnswerHash = binascii.hexlify(m.digest()).decode("utf-8")
	
	print("A: "+str(ogAnswerHash) + " : " + str(AnswerHash))
	c.execute('UPDATE securityQuestion SET AnswerHash=? WHERE AnswerHash=?',(AnswerHash,ogAnswerHash))


db.commit()
db.close()