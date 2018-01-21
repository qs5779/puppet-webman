# Class: webman::params
# ===========================
#
# Authors
# -------
#
# Author Name <que at wtfo-guru dot com>
#
# Copyright
# ---------
#
# Copyright 2018 Quien Sabe.
#
# Revision History:
# 20180121 - que - initial version
#
class webman::params {
  case $facts['os']['family'] {
    'Debian': {
      $owner = 'www-data'
      $group = 'www-data'
      $service_name = 'apache',
      $config_directory = '/etc/apache2/conf-enabled',
    }
    default:  {
      $owner = 'apache'
      $group = 'apache'
      $service_name = 'httpd'
      $config_directory = '/etc/httpd/conf.d',
    }
  }
}
