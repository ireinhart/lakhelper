node lak inherits default {

  class { 'localmail': }

  class { 'php':
    mysql_password => 'root',
  }
}

node 'lak.dev' inherits lak  {}
