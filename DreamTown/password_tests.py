LOGIN_CHARSET = " ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"
LOGOUT_CHARSET = " ABCDEFGHIJKLMNOPQRSTUVWXYZ"
def decode_letter(letter):
	ln = len(LOGOUT_CHARSET)
	return LOGIN_CHARSET.index(letter) % ln

def encode_letter(numb):
	ln = len(LOGOUT_CHARSET)
	return LOGOUT_CHARSET[numb % ln]
	

def shift(text,type):
	ln = len(text)
	amount = 0
	if(type == 0 or type ==1):
		amount = 0
	if(type == 2 or type ==3):
		amount = 1
	if(type == 4 or type ==5):
		amount = 2
	if(type == 6 or type ==7):
		amount = 3
	if(type == 8 or type ==9):
		amount = 4
	if(type == 10 or type ==11):
		amount = 5
	if(type == 12 or type ==13):
		amount = 6
	if(type == 14 or type ==15):
		amount = 7
	ctext = ""
	for i in range(0,ln):
		ctext += text[(i+amount)%ln]
	return ctext

def rearrange(text,type):
	ctext = ""
	if(type == 1 or type == 0): #0241376
		ctext += text[0]
		ctext += text[2]
		ctext += text[4]
		ctext += text[1]
		ctext += text[3]
		ctext += text[7]
		ctext += text[5]
		ctext += text[6]
		return ctext
	if(type == 2 or type == 3): #60241375
		ctext += text[6]
		ctext += text[0]
		ctext += text[2]
		ctext += text[4]
		ctext += text[1]
		ctext += text[3]
		ctext += text[7]
		ctext += text[5]
		return ctext
	if(type == 4 or type == 5): #56024137
		ctext += text[5]
		ctext += text[6]
		ctext += text[0]
		ctext += text[2]
		ctext += text[4]
		ctext += text[1]
		ctext += text[3]
		ctext += text[7]
		return ctext
	if(type == 6 or type == 7): #75602413
		ctext += text[7]
		ctext += text[5]
		ctext += text[6]
		ctext += text[0]
		ctext += text[2]
		ctext += text[4]
		ctext += text[1]
		ctext += text[3]
		return ctext
	if(type == 8 or type == 9): #37560241
		ctext += text[3]
		ctext += text[7]
		ctext += text[5]
		ctext += text[6]
		ctext += text[0]
		ctext += text[2]
		ctext += text[4]
		ctext += text[1]
		return ctext
	if(type == 10 or type == 11): #13756024
		ctext += text[1]
		ctext += text[3]
		ctext += text[7]
		ctext += text[5]
		ctext += text[6]
		ctext += text[0]
		ctext += text[2]
		ctext += text[4]
		return ctext
	if(type == 12 or type == 13): #41375602
		ctext += text[4]
		ctext += text[1]
		ctext += text[3]
		ctext += text[7]
		ctext += text[5]
		ctext += text[6]
		ctext += text[0]
		ctext += chr(ord(text[2])+6)
		return ctext
	if(type == 14 or type == 15): #24137560
		ctext += text[2]
		ctext += text[4]
		ctext += text[1]
		ctext += text[3]
		ctext += text[7]
		ctext += text[5]
		ctext += text[6]
		ctext += text[0]
		return ctext
def decrypt(ciphertext):
	ln = len(ciphertext)
	plaintext = ""
	for i in range(0,ln):
		letter = ciphertext[i]
		
		lnum = decode_letter(letter)
		lnum -= (i+1)
		
		letter = encode_letter(lnum)
		plaintext += letter
		
	return plaintext
	
def decode_name(code):
	ctype = int(code[1],16)
	#print(ctype)
	code = code[6:]
	code = rearrange(code,ctype)
	#print(code)
	code = decrypt(code)
	code = shift(code,ctype)
	return code

#print(decode_name("562DF1DJFLHHJF"))
codelist = open("Codes.txt","r").read().split('\n')
for code in codelist:
	if code[0] == "#":
		continue
	print(decode_name(code))