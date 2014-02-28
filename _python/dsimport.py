#!/usr/bin/python

# =====================================
# TribalWars Import-Script
#
# Coded by: agrafix (www.agrafix.net)
# Tested with python2.6 on ubuntu
#
# =====================================

import commands
import threading
import re
import sys
from optparse import OptionParser

#
# C O N F I G
#

# PHP-Configuration URL
my_cfg_url="/var/www/dsconnect/application/config/tribalwars.php"

# Database
my_user="dsconnect"
my_pass=""
my_data="dsconnect"

# Urls to Homepages
master_hosts = {
	'de': 'http://www.die-staemme.de/',
    'ch': 'http://www.staemme.ch/',
    'us': 'http://www.tribalwars.us/',
    'uk': 'http://www.tribalwars.co.uk/',
    'en': 'http://www.tribalwars.net/'}
	
# Type of world-data to import. Possible: ally, player, tribe, conquer
import_types = ["village", "player", "ally"]
	
# Worlds to import (DEPRECATED!)
import_worlds = []

#
# F U N C T I O N S
#
def TWImport_All(world, host):
	global import_types
	
	importThreads = []

	if (host == False):
		print "Skipping..."
		print ""
		return False

	print "Host: " + host
	print ""

	# no need 2 import conquer data
	for type in import_types:
		init = TWImport_Type(world, host, type)
		init.start()
		importThreads.append(init)

	for th in importThreads:
		th.join()

class TWImport_Type(threading.Thread):
	def __init__(self, world, host, type):
		threading.Thread.__init__(self)
		self.world = world
		self.host = host
		self.type = type

	def run(self):
		tw_download(self.world, self.host, self.type)
		tw_create_table(self.world, self.type)
		tw_import(self.world, self.type)
		tw_clear_cache(self.world, self.type)

class TWurl:
	stored = {}

	def getHost(self, worldId):
		lang = self._getLangId(worldId)

		if (self.stored.has_key(lang) == False):
			self.getServers(lang)

		if (self.stored[lang].has_key(worldId) == False):
			return False

		return self.stored[lang][worldId]

	def getServers(self, lang):
		global master_hosts

		host = master_hosts[lang]
		filename = '/tmp/' + lang + '_servers.txt';

		commands.getoutput('wget -qO- ' + host + 'backend/get_servers.php > ' + filename)

		f = open(filename, 'r')
		phpSerialized = f.read()

		self.stored[lang] = {}
		tmpDict = {}

		li = re.findall(r's:[0-9]*:"([a-z0-9]*)";s:[0-9]*:"http://([^"]*)";', phpSerialized)
		for match in li:
			tmpDict[match[0]] = "http://" + match[1]

		self.stored[lang] = tmpDict

		f.close()
		commands.getoutput('unlink ' + filename)

	def _getLangId(self, worldId):
		return worldId[0:2]

def tw_download(world, host, type):
	filename = tw_tmp_filename(world, type)
	print 'Downloading ' + host + '/map/' + type + '.txt.gz to ' + filename

	commands.getoutput('wget -qO- ' + host + '/map/' + type + '.txt.gz | gunzip > ' + filename)

def tw_create_table(world, type):
	global my_user
	global my_pass
	global my_data

	if (type == "village"):
		sql = "CREATE TABLE IF NOT EXISTS "+world+"_village (\
				id INT( 11 ) UNSIGNED NOT NULL ,\
				name VARCHAR( 255 ) NOT NULL ,\
				x INT( 5 ) UNSIGNED NOT NULL ,\
				y INT( 5 ) UNSIGNED NOT NULL ,\
				tribe INT( 11 ) UNSIGNED NOT NULL ,\
				points INT( 11 ) UNSIGNED NOT NULL ,\
				type INT( 5 ) UNSIGNED NOT NULL ,\
				PRIMARY KEY ( id )\
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;"
	elif (type == "ally"):
		sql = "CREATE TABLE IF NOT EXISTS "+world+"_ally (\
			  id int(11) unsigned NOT NULL,\
			  name varchar(255) collate utf8_unicode_ci NOT NULL,\
			  tag varchar(255) collate utf8_unicode_ci NOT NULL,\
			  members int(11) unsigned NOT NULL,\
			  villages int(11) unsigned NOT NULL,\
			  points int(11) unsigned NOT NULL,\
			  all_points int(11) unsigned NOT NULL,\
			  rank int(11) unsigned NOT NULL,\
			  PRIMARY KEY  (id)\
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;"
	elif (type == "conquer"):
		sql = "CREATE TABLE IF NOT EXISTS "+world+"_conquer (\
			  village_id int(11) unsigned NOT NULL,\
			  unix_timestamp int(11) unsigned NOT NULL,\
			  new_owner int(11) unsigned NOT NULL,\
			  old_owner int(11) unsigned NOT NULL\
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;"
	elif (type == "player"):
		sql = "CREATE TABLE IF NOT EXISTS "+world+"_player (\
			  id int(11) unsigned NOT NULL,\
			  name varchar(255) collate utf8_unicode_ci NOT NULL,\
			  ally int(11) unsigned NOT NULL,\
			  villages int(11) unsigned NOT NULL,\
			  points int(11) unsigned NOT NULL,\
			  rank int(11) unsigned NOT NULL,\
			  PRIMARY KEY  (id)\
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;"
	else:
		sql = ""
		print "INVALID TYPE!"

	if (sql != ""):
		commands.getoutput('mysql -u'+my_user+' -p\''+my_pass+'\' '+my_data+' -e "'+sql+';"')
	else:
		print "Could not create table " + type

def tw_import(world, type):
	global my_user
	global my_pass
	global my_data

	filename = tw_tmp_filename(world, type)
	table = world + '_' + type

	print 'Truncate ' + table
	commands.getoutput('mysql -u'+my_user+' -p\''+my_pass+'\' '+my_data+' -e "TRUNCATE TABLE '+table+';"')

	print 'Importing ' + filename + ' to ' + table
	commands.getoutput('mysql -u'+my_user+' -p\''+my_pass+'\' '+my_data+' -e "LOAD DATA LOCAL INFILE \''+filename+'\' INTO TABLE '+table+' FIELDS TERMINATED BY \',\' ENCLOSED BY \'\' LINES TERMINATED BY \'\n\';"')

def tw_clear_cache(world, type):
	filename = tw_tmp_filename(world, type)
	print 'Removing ' + filename
	commands.getoutput('unlink ' + filename)

def tw_tmp_filename(world, type):
	return '/tmp/' + type + '_' + world + '.txt'

#
# M A I N
#
HostHelper = TWurl()

for langID in master_hosts.keys():
	HostHelper.getServers(langID);
	
	for worlds in HostHelper.stored[langID].keys():
		import_worlds.append(worlds)
		
print "================="
print "DSImport"
print "================="

# commandline args
parser = OptionParser()
parser.add_option("-w", "--world", dest="only_world", help="Only import given world, usage: -w de34", default="off", type="string")
parser.add_option("-l", "--list", action="store_true", dest="only_list", help="Won't start the import, will only list worlds", default=False)

(options, args) = parser.parse_args()

# handle commandline args
if (options.only_world != "off"):
	import_worlds = []
	import_worlds.append(options.only_world)
	
# write to php config file
if (my_cfg_url != "" and options.only_world == "off"):
	fi = open(my_cfg_url, 'w')
	fi.write("<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');\n")
	
	fi.write("$config['tw_worlds'] = array();\n")
	
	for worldID in import_worlds:
		fi.write("$config['tw_worlds'][] = '" + worldID + "';\n")
		
	fi.write("natcasesort($config['tw_worlds']);\n")
	fi.write("?>")
	
	fi.close()
	
# list
if (options.only_list):
	while (len(import_worlds) > 0):
		worldID = import_worlds.pop()
		print worldID
		
	sys.exit()
		
	

while (len(import_worlds) > 0):
	worldID = import_worlds.pop()

	print ""
	print "== Importing data from " + worldID
	print ""

	TWImport_All(worldID, HostHelper.getHost(worldID))
