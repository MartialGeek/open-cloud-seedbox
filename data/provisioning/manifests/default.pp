Exec['apt_update'] -> Package <| |>

include nginx

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

$project_path  = "/var/www/open-cloud-seedbox"
$document_root = "${$project_path}/web"
$env_path_line = 'PATH=$HOME/.composer/vendor/bin:/opt/nodejs/bin:$PATH'
$profile_bash_script = '/home/vagrant/.profile'

Exec {
    path => '/opt/nodejs/bin:/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin'
}

package { $php_packages:
    ensure  => installed,
    notify  => Service['nginx']
}

exec { 'download_nodejs':
    command     => 'curl http://nodejs.org/dist/v0.12.0/node-v0.12.0-linux-x64.tar.gz -o /tmp/nodejs.tar.gz',
    require     => Package['curl'],
    notify      => Exec['untar_nodejs'],
    unless      => 'test -f /opt/nodejs/bin/node'
}

file { '/opt/nodejs':
    ensure => directory
}

exec { 'untar_nodejs':
    command     => 'tar xvf /tmp/nodejs.tar.gz --strip-components 1',
    cwd         => '/opt/nodejs',
    require     => File['/opt/nodejs'],
    refreshonly => true
}

file { '/tmp/nodejs.tar.gz':
    ensure  => absent,
    require => Exec['untar_nodejs']
}

exec { 'install_sass':
    command => 'gem install sass',
    unless  => 'which sass',
}

exec { 'install_grunt':
    command => 'npm install -g grunt-cli',
    require => Exec['untar_nodejs'],
    unless => 'npm list -g | grep grunt-cli'
}

exec { 'install_bower':
    command => 'npm install -g bower',
    require => Exec['untar_nodejs'],
    unless => 'npm list -g | grep bower'
}

file { '/var/log/php5-fpm.log':
    ensure  => present,
    group   => 'adm',
    mode    => 0644,
    require => Package['php5-fpm']
}

exec { 'install_composer':
    command => 'curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer',
    require => Package['php5-cli'],
    unless => 'ls /usr/local/bin/composer'
}

file { '/home/vagrant/.profile':
    ensure => present
} ->
exec { 'add_composer_path':
    command => "echo '${env_path_line}' >> ${$profile_bash_script}",
    unless => "grep '${$env_path_line}' ${$profile_bash_script}"
}

nginx::resource::vhost { 'seedbox.dev':
    www_root             => $document_root,
    use_default_location => false,
    index_files          => ['index.php']
}

nginx::resource::location { 'seedbox_root':
    vhost               => 'seedbox.dev',
    location            => '/',
    location_custom_cfg => {
        try_files   => '$uri $uri/ /index.php?$query_string'
    }
}

nginx::resource::location { 'seedbox_php':
    vhost              => 'seedbox.dev',
    fastcgi            => 'unix:/var/run/php5-fpm.sock',
    location           => '~ index.php(/|$)',
    fastcgi_param      => {
        'SCRIPT_FILENAME' => '$realpath_root$fastcgi_script_name',
        'DOCUMENT_ROOT'   => '$realpath_root'
    }
}

class { 'mysql::server':
    root_password => 'seedbox'
}

mysql::db { 'seedbox':
    user     => 'seedbox',
    password => 'seedbox',
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

user { 'debian-transmission':
    ensure => present,
    groups => ['debian-transmission', 'www-data'],
    require => Package['transmission-daemon']
}

file { '/var/www/open-cloud-seedbox/config/parameters.php':
    ensure  => present,
    content => file('/var/www/open-cloud-seedbox/data/provisioning/files/parameters.php'),

}

service { 'transmission-daemon':
    ensure => stopped,
    require => Package['transmission-daemon']
}

file { '/etc/transmission-daemon/settings.json':
    ensure  => present,
    content => file('/var/www/open-cloud-seedbox/data/provisioning/files/transmission-settings.json'),
    owner   => 'debian-transmission',
    group   => 'debian-transmission',
    mode    => 0600,
    notify  => Service['transmission-daemon'],
    require => File['/home/vagrant/.composer/auth.json']
}

file { '/home/vagrant/.composer':
    ensure => directory,
    owner  => 'vagrant',
    group  => 'vagrant',
    mode   => 0775
}

file { '/home/vagrant/.composer/auth.json':
    ensure  => present,
    content => file('/var/www/open-cloud-seedbox/data/provisioning/files/composer-auth.json'),
    owner   => 'vagrant',
    group   => 'vagrant',
    mode    => 0600,
    require => File['/home/vagrant/.composer']
}

exec { 'install_composer_dependencies':
    command     => 'composer install',
    require     => [
        Exec['install_composer'],
        File['/home/vagrant/.composer/auth.json']
    ],
    cwd         => $project_path,
    environment => 'HOME=/home/vagrant',
    user        => 'vagrant',
    timeout => 1200
}

exec { 'install_npm_dependencies':
    command => 'npm install',
    require => Exec['untar_nodejs'],
    cwd     => $project_path,
    user    => 'vagrant',
    timeout => 1200
}

exec { 'install_bower_dependencies':
    command     => 'bower install --config.interactive=false',
    require     => Exec['install_bower'],
    cwd         => $project_path,
    environment => 'HOME=/home/vagrant',
    user        => 'vagrant'
}

exec { 'build_assets':
    command => 'grunt build',
    require => [
        Exec['install_grunt'],
        Exec['install_npm_dependencies']
    ],
    cwd     => $project_path,
    user    => 'vagrant'
}

exec { 'symlink_assets':
    command => "${project_path}/bin/seedbox assets:install",
    require => [
        Exec['build_assets'],
        Class['rabbitmq']
    ],
    cwd     => $project_path,
    user    => 'vagrant'
}

exec { 'update_sql_schema':
    command => "${project_path}/bin/doctrine orm:schema-tool:update --force",
    require => [
        File['/var/www/open-cloud-seedbox/config/parameters.php'],
        Exec['install_composer_dependencies'],
        Mysql::Db['seedbox'],
    ],
    cwd     => $project_path,
    user    => 'vagrant'
}

exec { 'create_seedbox_user':
    command => "${project_path}/bin/seedbox user:create NiceUser nice-user@seedbox.io myseedbox",
    require => Exec['update_sql_schema'],
    cwd     => $project_path,
    user    => 'vagrant',
    notify  => Exec['touch /home/vagrant/.seedbox_user_created'],
    unless  => 'ls /home/vagrant/.seedbox_user_created'
}

exec { 'touch /home/vagrant/.seedbox_user_created':
    user        => 'vagrant',
    refreshonly => true
}

class { 'rabbitmq':
    admin_enable => true,
    delete_guest_user => false,
    config_variables => { loopback_users => [] }
}

rabbitmq_user { 'seedbox':
    password => 'seedbox'
}

rabbitmq_vhost { 'seedbox':
    ensure => present
}

rabbitmq_user_permissions { 'seedbox@seedbox':
    configure_permission => '.*',
    read_permission      => '.*',
    write_permission     => '.*'
}
