node lak inherits default {

  class { 'localmail': }

  class { 'php':
    mysql_password => 'root',
    mysql_sqlfile  => '/vagrant/public/db.sql',
    document_root  => '/vagrant/public/',
    server_name    => 'lak.dev',
  }
}

node 'lak.dev' inherits lak  {}
