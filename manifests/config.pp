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

  if defined(Service["$webman::service_name"]) {
    $webman_notify = [ Service["$webman::service_name"] ]
  }
  else {
    $webman_notify = undef
  }

  $webman_config = sprintf('%s/%02d-webman.conf', $webman::config_directory, $webman::config_order_index)

  if $webman::ensure == 'present' {

    file { $webman_config:
      ensure  => 'present',
      mode    => '0644',
      content => template('webman/webman.conf.erb'),
      notify  => $webman_notify,
    }
  }
  else {
    file { $webman_config:
      ensure => 'absent',
      notify => $webman_notify,
    }
  }

}
