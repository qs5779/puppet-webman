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
      mode => '0755',
    }

    file { "${webman_directory}/class.manpagelookup.php":
      ensure => 'directory',
      mode   => '0644',
      source => 'puppet:///modules/webman/class.manpagelookup.php',
    }

    file { "${webman_directory}/index.php":
      ensure => 'directory',
      mode   => '0644',
      source => 'puppet:///modules/webman/index.php',
    }

    file { "${webman_directory}/index.template":
      ensure  => 'directory',
      mode    => '0644',
      content => template('webman/index.template.erb'),
    }
  }
  else {
    file { $webman_directory:
      ensure => 'absent',
      force  => true,
    }
  }

}
