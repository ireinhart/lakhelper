LaK helper
==========

Please read the setup instruction.


habitat.php
===========
A small helper script to export your live account to a small excel file (csv).
You can use the exported file to import your habitats in Lakatoo.


alliance.php
===========
A small script to save some alliance data and send an email.


setup
=====

* clone this repro
* download Zend Framework 2 and copy the Zend-Folder to /library/Zend
* copy /public/config.php.dist to /public/config.php
* /public/config.php: edit password and login field
* install Virtual Box and vargant
* in terminal: vagrant up (only for Mac and Linux, NOT Windows)
* browse to http://localhost:8080/habitat.php


setup for the web
=================

Webserver with PHP required!

* clone this repro
* download Zend Framework 2 and copy the Zend-Folder to /library/Zend
* copy /public/config.php.dist to /public/config.php 
* /public/config.php: edit password and login field
* use ftp or sftp to copy the hole files to your webhoster
* browse to http://yourdomin/habitat.php




