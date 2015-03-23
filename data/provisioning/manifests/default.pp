Exec['apt_update'] -> Package <| |>

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
    'curl',
    'git'
]

$env_path_line = 'PATH=$HOME/.composer/vendor/bin:/opt/nodejs/bin:$PATH'
$profile_bash_script = '/home/vagrant/.profile'

package { $php_packages:
    ensure  => installed,
    notify  => Service['nginx']
}

exec { 'download_nodejs':
    command     => '/usr/bin/curl http://nodejs.org/dist/v0.12.0/node-v0.12.0-linux-x64.tar.gz -o /tmp/nodejs.tar.gz',
    require     => Package['curl'],
    notify      => Exec['untar_nodejs'],
    unless      => '/usr/bin/test -f /opt/nodejs/bin/node'
}

file { '/opt/nodejs':
    ensure => directory
}

exec { 'untar_nodejs':
    command     => '/bin/tar xvf /tmp/nodejs.tar.gz --strip-components 1',
    cwd         => '/opt/nodejs',
    require     => File['/opt/nodejs'],
    refreshonly => true
}

file { '/tmp/nodejs.tar.gz':
    ensure  => absent,
    require => Exec['untar_nodejs']
}

exec { 'install_sass':
    command => '/usr/bin/gem install sass',
    unless  => '/usr/bin/which sass',
}

exec { 'install_grunt':
    command => '/opt/nodejs/bin/npm install -g grunt-cli',
    require => Exec['download_nodejs'],
    unless => '/opt/nodejs/bin/npm list -g | /bin/grep grunt-cli'
}

file { '/var/log/php5-fpm.log':
    ensure  => present,
    group   => 'adm',
    mode    => 0644,
    require => Package['php5-fpm']
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
    www_root             => $root,
    use_default_location => false,
    index_files          => ['index.php']
}

nginx::resource::location { 'warez_root':
    vhost               => 'warez.dev',
    location            => '/',
    location_custom_cfg => {
        try_files   => '$uri $uri/ /index.php$query_string'
    }
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

class { 'mysql::server':
    root_password => 'warezisfun'
}

mysql::db { 'warez':
    user     => 'warez',
    password => 'warez',
    host     => 'localhost',
    grant    => ['ALL'],
}

package { $misc_packages:
    ensure => installed
}

user { 'vagrant':
    ensure => present,
    groups => ['vagrant', 'adm', 'www-data'],
    require => Package['nginx']
}
