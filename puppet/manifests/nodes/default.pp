node default {

    if $::osfamily == 'debian' {
        Package { require => Class["apt::update"] }

        class { 'apt':
                always_apt_update => true,
        }

        $ensure_installed_packages = hiera_array('default_packages')

        package { $ensure_installed_packages:
           ensure => 'latest',
        }

    }

}
