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
  String $ensure                    = 'present',
  String $parentdirectory           = '/var/www',
  Boolean $manageparent             = false,
  Integer $configorderindex         = 66,
  Optional[String] $owner           = undef,
  Optional[String] $group           = undef,
  Optional[String] $servicename     = undef,
  Optional[String] $configdirectory = undef,
  Optional[String] $cachedirectory  = undef,
) {

  if $owner == undef {
    case $::osfamily {
      'Debian': {
        $webmanowner = 'www-data'
      }
      default:  {
        $webmanowner = 'apache'
      }
    }
  }
  else {
    $webmanowner = $owner
  }

  if $group == undef {
    case $::osfamily {
      'Debian': {
        $webmangroup = 'www-data'
      }
      default:  {
        $webmangroup = 'apache'
      }
    }
  }
  else {
    $webmangroup = $group
  }

  if $servicename == undef {
    case $::osfamily {
      'Debian': {
        $webmanservicename = 'apache2'
      }
      default:  {
        $webmanservicename = 'httpd'
      }
    }
  }
  else {
    $webmanservicename = $servicename
  }

  if $configdirectory == undef {
    if $servicename == undef {
      case $::osfamily {
        'Debian': {
          $wmconfigdirectory = '/etc/apache2/conf-available'
          $wmconfiglinkdirectory = '/etc/apache2/conf-enabled'
        }
        default:  {
          $wmconfigdirectory = '/etc/httpd/conf.d'
          $wmconfiglinkdirectory = undef
        }
      }
    }
    else {
      case $servicename {
        'apache2': {
          $wmconfigdirectory = '/etc/apache2/conf-available'
          $wmconfiglinkdirectory = '/etc/apache2/conf-enabled'
        }
        'httpd': {
          $wmconfigdirectory = '/etc/httpd/conf.d'
          $wmconfiglinkdirectory = undef
        }
        'nginx': {
          if $::osfamily == 'Debian' {
            $wmconfigdirectory = '/etc/nginx/conf-available'
            $wmconfiglinkdirectory = '/etc/nginx/conf-enabled'
          }
          else {
            fail("Cannot determine configdirectory for servicename ${servicename} on family ${::osfamily}")
          }
        }
        default:  {
          fail("Cannot determine configdirectory for servicename ${servicename}")
        }
      }
    }
  }
  else {
    $wmconfigdirectory = $configdirectory
    $wmconfiglinkdirectory = undef
  }

  case $webmanservicename {
    /^apache2|httpd$/: {
      $wmconfigtmpl = 'webman/webman.conf.erb'
    }
    default:  {
      fail("No config template available for servicename ${webmanservicename} yet, sorry.")
    }
  }

  File {
    owner => $webmanowner,
    group => $webmangroup,
  }

  include 'webman::install'
  include 'webman::config'
}
