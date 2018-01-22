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
  String $owner = $webman::params::owner,
  String $group = $webman::params::group,
  String $service_name = $webman::params::service_name,
  String $config_directory = $webman::params::config_directory,
  String $parent_directory = '/var/www',
  Boolean $manage_parent = false,
  Integer $config_order_index = 66,
  Optional String $cache_directory = undef,
) inherits webman::params {

  File {
    owner => $owner,
    group => $group,
  }

  include 'webman::install'
  include 'webman::config'
}
