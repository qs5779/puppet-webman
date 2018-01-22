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
    $webmanservicename = $group
  }

  if $configdirectory == undef {
    if $servicename == undef {
      case $::osfamily {
        'Debian': {
          $webmanconfigdirectory = '/etc/apache2/conf-available'
        }
        default:  {
          $webmanconfigdirectory = '/etc/httpd/conf.d'
        }
      }
    }
    else {
      case $servicename {
        'apache2': {
          $webmanconfigdirectory = '/etc/apache2/conf-available'
        }
        'httpd': {
          $webmanconfigdirectory = '/etc/httpd/conf.d'
        }
        'nginx': {
          if $::osfamily == 'Debian' {
            $webmanconfigdirectory = '/etc/nginx/conf-available'
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
    $webmanconfigdirectory = $group
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
