dsconnect
=========

# Intro

Social Network for TribalWars

This is a social network for TribalWars. You can follow players, villages and tribes and get updates about enoblements and point changes. You can also write custom posts and discuss. A mapping tool and an attack-planner is included too. The network is currently run at [dsconnect.de](http://www.dsconnect.de) for all german and english game worlds. The software is internationalized for german and english. 

# Notes

## Configuration

You'll need to change:

PYTHON:

Database access data

PHP:

Database access data and

* ```$config['encryption_key'] ```
* ```$config['tw_private_key'] ```
* ```private static $salt = 'SOME_STATIC_SALT';```

## Code

The code is from 2012 an based on an old version of CodeIgniter.

## Cronjobs

To import world-data correctly, you'll need to run the included python-script via cronjob.


## Unit Graph
To get the graphics of the game-units, you'll need to add:

* 'static/image/ds/speed.png'
* 'static/image/ds/unit_archer.png'
* 'static/image/ds/unit_axe.png'
* 'static/image/ds/unit_catapult.png'
* 'static/image/ds/unit_heavy.png'
* 'static/image/ds/unit_knight.png'
* 'static/image/ds/unit_light.png'
* 'static/image/ds/unit_marcher.png'
* 'static/image/ds/unit_ram.png'
* 'static/image/ds/unit_snob.png'
* 'static/image/ds/unit_spear.png'
* 'static/image/ds/unit_spy.png'
* 'static/image/ds/unit_sword.png'
