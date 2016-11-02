Light Centralized Digital Signage
=================================

[![Build Status](https://travis-ci.org/jf-guillou/lcds.svg?branch=master)](https://travis-ci.org/jf-guillou/lcds) [![GitHub license](https://img.shields.io/badge/license-New%20BSD-blue.svg)](https://raw.githubusercontent.com/jf-guillou/lcds/master/LICENSE.md)

REQUIREMENTS
------------

- PHP >= 5.6
- php5-ldap / php7-ldap
- MySQL >= 5.5 OR MariaDB >= 10.0
- [Composer](https://getcomposer.org/)

INSTALLATION
------------

```bash
composer self-update
composer global require "fxp/composer-asset-plugin:^1.2.0"
cd /path/to/install
git clone https://github.com/jf-guillou/lcds.git
cd lcds
composer install --no-dev
nano config/db.php # Setup database connection
nano config/params.php # Setup app configuration (mainly for language)
./yii migrate --interactive=0
```

UPGRADE
-------

```bash
composer self-update
composer global update
cd /path/to/install/lcds
git pull
composer install --no-dev
./yii migrate --interactive=0
```
