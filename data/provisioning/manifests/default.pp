include '::mysql::server'
include php
include nginx

$root = '/var/www/warez/web'

class { [
    'php::fpm',
    'php::cli',
    'php::extension::curl',
    'php::extension::intl',
    'php::extension::mcrypt',
    'php::extension::memcached',
    'php::extension::mysql',
    'php::extension::opcache',
    'php::extension::xdebug',
    'php::composer',
    'php::composer::auto_update'
]: }

nginx::resource::vhost { 'warez.dev':
    www_root    => $root,
    index_files => ['index.php'],
    try_files   => ['$uri', '$uri/', '/index.php$query_string']
}

nginx::resource::location { 'warez_php':
    vhost              => 'warez.dev',
    fastcgi            => 'unix:/var/run/php5-fpm.sock',
    location           => '~ index.php(/|$)',
    fastcgi_param      => {
        'SCRIPT_FILENAME' => '$realpath_root$fastcgi_script_name',
        'DOCUMENT_ROOT'   => '$realpath_root'
    }
}

package { 'transmission-daemon':
    ensure => installed
}

package { 'memcached':
    ensure => installed
}

