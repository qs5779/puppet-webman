# Class: webman::config.
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
class webman::config {

  if defined(Service[$webman::webmanservicename]) {
    $wmnotify = [ Service[$webman::webmanservicename] ]
  }
  else {
    $wmnotify = undef
  }

  $wmconfig = sprintf('%s/%02d-webman.conf', $webman::wmconfigdirectory, $webman::configorderindex)

  if $webman::wmconfiglinkdirectory != undef {
    $wmconfiglink = sprintf('%s/%02d-webman.conf', $webman::wmconfiglinkdirectory, $webman::configorderindex)
  }
  else {
    $wmconfiglink = undef
  }

  if $webman::ensure == 'present' {

    file { $wmconfig:
      ensure  => 'present',
      mode    => '0644',
      content => template($webman::wmconfigtmpl),
      notify  => $wmnotify,
    }

    if $wmconfiglink != undef {
      file { $wmconfiglink:
        ensure => 'link',
        target => $wmconfig,
        notify  => $wmnotify,
      }
    }
  }
  else {
    file { $wmconfig:
      ensure => 'absent',
      notify => $wmnotify,
    }

    if $wmconfiglink != undef {
      file { $wmconfiglink:
        ensure => 'absent',
        notify  => $wmnotify,
      }
    }
  }

}
