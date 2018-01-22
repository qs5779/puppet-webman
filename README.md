# puppet-webman

## Overview

The module installs a directory with three files that provides a web based
man page viewer.

Parameters to the main `::webman` class :

* `$ensure`           : Ensure parameter for the module. Default 'present' otherwise purges
* `$owner`            : Specifies owner of web files. Computed if not specified
* `$group`            : Specifies group of web files. Computed if not specified
* `$servicename`      : For future use, to specify non apache web server
* `$configdirectory`  : optional non-stadard config directory rarely or never needed
* `$parentdirectory`  : parent directory of web service default is /var/www
* `$manageparent`     : boolean true if module should manage the parent directory default false
* `$configorderindex` : number to precede the config file with when order is needed
* `$cachedirectory`   : when specified pages are cached and their life managed via cron


## Compatibility

This initial version supports an apache web server on Debian or RedHat linux families.'
