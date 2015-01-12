LaK helper
==========

Please read the setup instruction.

[ ![Codeship Status for ireinhart/lakhelper](https://codeship.com/projects/9052c2e0-7c70-0132-521c-0e6828aecb88/status?branch=master)](https://codeship.com/projects/56390)


habitat.php
===========
A small helper script to export your live account to a small excel file (csv).
You can use the exported file to import your habitats in Lakatoo.


alliance.php
============
A small script to save some alliance data into the database (to build a history)
and provide the actual alliance data (all habitats) as a cvs file for download.


complete_habitat.php
====================
Grow up your habitat. At the moment only buildings.


setup
=====

* clone this repro (git clone https://github.com/ireinhart/lakhelper.git)
* download Zend Framework 2 and copy the Zend-Folder to /library/Zend
* copy /public/config.php.dist to /public/config.php
* /public/config.php: edit password and login field
* install Virtual Box and vargant
* in terminal: vagrant up (only for Mac and Linux, NOT Windows)
* browse to http://localhost:8080/habitat.php


setup for the web
=================

Webserver with PHP required!

* clone this repro (git clone https://github.com/ireinhart/lakhelper.git)
* download Zend Framework 2 and copy the Zend-Folder to /library/Zend
* copy /public/config.php.dist to /public/config.php 
* /public/config.php: edit password and login field
* use ftp or sftp to copy the hole files to your webhoster
* browse to http://yourdomin/habitat.php




