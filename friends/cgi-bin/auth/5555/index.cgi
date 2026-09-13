#!/usr/bin/python3
from dreamtown_config import *
import os
import binascii
import json

PrintHeaders()

uuid = binascii.hexlify(os.urandom(64)).decode('utf8')
js = {"token":uuid}

print(json.dumps(js))