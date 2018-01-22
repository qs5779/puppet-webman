# Class: webman
# ===========================
#
# PHP web bases man page viewer

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
class webman (
  String $ensure = 'present',
  String $parentdirectory = '/var/www',
  Boolean $manageparent = false,
  Integer $configorderindex = 66,
  Optional String $owner,
  Optional String $group,
  Optional String $servicename,
  Optional String $configdirectory,
  Optional String $cachedirectory,
) inherits webman::params {

  case $::osfamily {
    'Debian': {
      $cowner = 'www-data'
      $cgroup = 'www-data'
      $cconfigdirectory = '/etc/apache2/conf-available'
      $cservicename = 'apache'
    }
    default:  {
      $cowner = 'apache'
      $cgroup = 'apache'
      $cconfigdirectory = '/etc/httpd/conf.d'
      $cservicename = 'httpd'
    }
  }

  if $owner == undef { $owner = $cowner }
  if $group == undef { $group = $cgroup }
  if $configdirectory == undef { $configdirectory = $cconfigdirectory }
  if $servicename == undef { $servicename = $cservicename }

  File {
    owner => $owner,
    group => $group,
  }

  include 'webman::install'
  include 'webman::config'
}
