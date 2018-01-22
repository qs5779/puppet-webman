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

  $wmconfig = sprintf('%s/%02d-webman.conf', $webman::webmanconfigdirectory, $webman::configorderindex)

  if $webman::ensure == 'present' {

    file { $wmconfig:
      ensure  => 'present',
      mode    => '0644',
      content => template($webman::wmconfigtmpl),
      notify  => $wmnotify,
    }
  }
  else {
    file { $wmconfig:
      ensure => 'absent',
      notify => $wmnotify,
    }
  }

}
