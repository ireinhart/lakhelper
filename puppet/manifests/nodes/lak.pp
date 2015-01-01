node lak inherits default {

  class { 'localmail': }

  class { 'php':
    mysql_password => 'root',
    mysql_sqlfile => '/vagrant/public/db.sql',
  }
}

node 'lak.dev' inherits lak  {}
