#!/usr/bin/python3
import os
import binascii
import json

print("Content-Type: application/json")
print("")

uuid = binascii.hexlify(os.urandom(64)).decode('utf8')
js = {"token":uuid}

print(json.dumps(js))