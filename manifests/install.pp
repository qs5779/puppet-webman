# Class: webman::install
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
class webman::install {

  $wmdirectory = "${webman::parentdirectory}/webman"

  if $webman::ensure == 'present' {
    if $webman::manageparent {
      file { $webman::parentdirectory :
        ensure => 'directory'
      }
    }

    file { $wmdirectory:
      ensure => 'directory',
      mode   => '0755',
    }

    file { "${wmdirectory}/class.manpagelookup.php":
      ensure => 'file',
      mode   => '0644',
      source => 'puppet:///modules/webman/class.manpagelookup.php',
    }

    file { "${wmdirectory}/index.php":
      ensure => 'file',
      mode   => '0644',
      source => 'puppet:///modules/webman/index.php',
    }

    file { "${wmdirectory}/index.template":
      ensure  => 'file',
      mode    => '0644',
      content => template('webman/index.template.erb'),
    }
  }
  else {
    file { $wmdirectory:
      ensure => 'absent',
      force  => true,
    }
  }

}
