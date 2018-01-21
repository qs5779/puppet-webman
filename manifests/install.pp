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

  $webman_directory = "${webman::parent_directory}/webman"

  if $webman::ensure == 'present' {
    if $webman::manage_parent {
      file { $webman::parent_directory :
        ensure => 'directory'
      }
    }

    file { $webman_directory:
      ensure             => 'directory',
      source_permissions => 'use_when_creating',
      source             => 'puppet:///modules/webman/webman',
      recurse            => true,
    }
  }
  else {
    file { $webman_directory:
      ensure => 'absent',
      force  => true,
    }
  }

}
