Exec['apt_update'] -> Package <| |>

include '::mysql::server'
include nginx

$root = '/var/www/warez/web'

$php_packages = [
  'php5-common',
  'php5-cli',
  'php5-fpm',
  'php5-curl',
  'php5-intl',
  'php5-json',
  'php5-mcrypt',
  'php5-memcached',
  'php5-mysqlnd',
  'php5-xdebug',
  'php5-xsl',
]

$misc_packages = [
    'transmission-daemon',
    'memcached',
    'curl'
]

$env_path_line = 'PATH=$HOME/.composer/vendor/bin:$PATH'
$profile_bash_script = '/home/vagrant/.profile'

package { $php_packages:
    ensure  => installed,
    notify  => Service['nginx']
}

exec { 'install_composer':
    command => '/usr/bin/curl -sS https://getcomposer.org/installer | sudo /usr/bin/php -- --install-dir=/usr/local/bin --filename=composer',
    require => Package['php5-cli'],
    unless => '/bin/ls /usr/local/bin/composer'
}

file { '/home/vagrant/.profile':
    ensure => present
} ->
exec { 'add_composer_path':
    command => "/bin/echo '${env_path_line}' >> ${$profile_bash_script}",
    unless => "/bin/grep '${$env_path_line}' ${$profile_bash_script}"
}

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

package { $misc_packages:
    ensure => installed
}
