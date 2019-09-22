#!/usr/bin/python3

import cgi
import cgitb;
import urllib
import urllib.parse

print("Content-Type: text/plain")
print("")
output = {"ResultCode":"OK"}
loginNo = ""
codeType = 0
gotchiPoints = 0
itemId = 0
playing = ""
requestType = 0


def CheckBit(code,verify=True,bit=9):
	checkBit = []
	checkBit += code
	codeArr = []
	for i in range(0,len(code)):
		if i == bit:
			continue
		codeArr.append(int(checkBit[i]))
	checksum = sum(codeArr) % 10
	if verify == False:
		return checksum
	if code[bit] == str(checksum):
		return 10
	else:
		return checksum


		
def FindType(code):
	if (code[3] == "8" or code[3] == "9") and CheckBit(code,True,4) == 10:
		return 4
	elif (code[3] == "2" or code[3] == "3") and CheckBit(code,True,5) == 10:
		return 2
	elif (code[3] == "4" or code[3] == "5") and CheckBit(code,True,7) == 10:
		return 3
	elif (code[3] == "7" or code[3] == "6") and CheckBit(code,True,8) == 10:
		return 1
	elif (code[3] == "0" or code[3] == "1") and CheckBit(code,True,9) == 10:
		return 0
	else:
		return 5

def FindCheckLoc(code):
	i = 0
	while i < len(code):
		print("CHECK "+str(i)+","+str(CheckBit(code,True,i)))
		i = i +1

def GetTamaIndex(code, type):
	tamaIndex = []
	if type == 0: 
		tamaIndex.append(code[5])
		tamaIndex.append(code[8])
	elif type == 1: 
		tamaIndex.append(code[5])
		tamaIndex.append(code[7])
	elif type == 2: 
		tamaIndex.append(code[2])
		tamaIndex.append(code[4])
	elif type == 3: 
		tamaIndex.append(code[8])
		tamaIndex.append(code[4])
	elif type == 4:  
		tamaIndex.append(code[9])
		tamaIndex.append(code[7])
	return tamaIndex

def GetTamaRegion(code, type):
	if type == 0: 
		return code[1]
	elif type == 1: 
		return code[0]
	elif type == 2: 
		return code[1]
	elif type == 3: 
		return code[2]
	elif type == 4: 
		return code[5]


def CgiGetCode():
	try:
		argv = {}
		arguments = cgi.FieldStorage()
		for i in arguments.keys():
			argv[i]=arguments[i].value
		
		#Read GET Paramaters
		requestType = int(argv['c'])
		if requestType == 1:
			loginNo = argv['u']+argv['d']
			if len(loginNo) != 10:
				output['ResultCode']="ERROR"
				return
			codeType = int(argv['m'])
			gotchiPoints = int(argv['g'])
			itemId = int(argv['i'])
			
			#playing = argv['p']
	except:
		output['ResultCode']="ERROR"
		return
	
	#Input Validation
	if requestType == 1:
		if gotchiPoints < 0 or gotchiPoints > 5:
			output['ResultCode']="ERROR"
			return
		if itemId < 0 or itemId > 99:
			output['ResultCode']="ERROR"
			return
		if codeType < 0 or codeType > 4:
			output['ResultCode']="ERROR"
			return
	# Actural Processing
	if requestType == 1:
		if codeType == 0: ## login
			type = FindType(loginNo)
			if type == 5:
				output['ResultCode']="ERROR"
				return
			else: 
				output['ResultCode']="OK"
				CharCode = GetTamaIndex(loginNo,type)
				output['CharacterCode']=str(CharCode[0])+str(CharCode[1])
				output['VER']=str(GetTamaRegion(loginNo,type))
				return
		elif codeType != 0:
			type = FindType(loginNo)
			region = GetTamaRegion(loginNo,type)
			tamaIndex = GetTamaIndex(loginNo,type)
			
			iid = str(itemId)
			while len(iid) != 2:
				iid = "0"+iid
			ggp = str(gotchiPoints)
			while len(ggp) != 2:
				ggp = "0"+ggp
			
			logoutNo = str(codeType)+str(region)+iid[0]+"0"+iid[1]+str(tamaIndex[0])+ggp+str(tamaIndex[1])
			logoutNo += str(CheckBit(logoutNo,False,9))
			output['PasswordUp'] = logoutNo[:5]
			output['PasswordDown'] = logoutNo[5:]
			return
CgiGetCode()
print(urllib.parse.urlencode(output),end="")