# Warez companion

## Why

This application was designed for simplifying the use of a torrent client installed on a seedbox.
Currently, there is only one tracker supported: T411. And if you want to use the upload feature of the application,
only the Freebox v6 adapter is implemented.

## Installation (dev)

If you want to contribute to the project, a Vagrant box is configured to make the installation easier.

### Requirements

- [Vagrant](https://www.vagrantup.com)

### Get the code

Fork the project and clone it.

```sh
git clone git@github.com:MyUserName/warez.git
```

### Run the box

Go into your application path and run the Vagrant box. A Puppet provisioning will install the packages needed by
the application.

```sh
cd path/to/app
vagrant up
```

### Edit your "hosts" file

Open a SSH connection to the virtual machine and copy the public IP (eth2 adapter).

```sh
vagrant ssh
ifconfig
```

Now edit your "hosts" file (/etc/hosts on Linux or OSX or c:\Windows\System32\drivers\etc\hosts on Windows) and paste
the IP. The domain name must be "warez.dev".

```
127.0.0.1      localhost
172.xx.xx.xx   warez.dev
```

### Configure your application

The torrent client installed on the VM and supported by the application is debian-transmission. In order to allow the
app to communicate with the RPC interface of transmission, you must edit the transmission settings to customize the
credentials.

```sh
vagrant ssh
sudo service transmission-daemon stop
sudo vim /etc/transmission-daemon/settings.json
```

Find the parameter "rpc-password" and replace the actual password by a new one. You can also customize the
"rpc-username" if you want. Then, restart the transmission client.

```sh
sudo service transmission-daemon start
```

Now go the application path, copy the file config/parameters.php.dist to config/parameters.php and configure the
parameters. Your file should look like this:

```php
<?php

return [
    'app_env' => 'dev',
    'doctrine_driver' => 'pdo_mysql',  // The PDO driver of your choice
    'doctrine_dbname' => 'warez',  // This DB was created by the provisioning
    'doctrine_host' => 'localhost',
    'doctrine_user' => 'warez',  // This SQL user was created by the provisioning
    'doctrine_password' => 'warez', // The password was configured by the provisioning
    'security_encoder_password' => 'aSuperP@ssW0rD', // Used to encrypt your tracker password
    'security_encoder_salt' => '',  // Keep this value empty
    'torrent_files_path' => '/tmp/warez', // This path was created by the provisioning (must be writable by the group debian-transmission)
    'transmission_login' => 'warez', // The transmission RPC username
    'transmission_password' => 'warez', // The transmission RPC password
    'transmission_host' => 'localhost',
    'transmission_port' => '9091',
    'transmission_rpc_uri' => '/transmission/rpc', // The default RPC uri
];
```

### Install the vendors

Install the PHP, nodejs and bower libraries:

```sh
cd /var/www/warez
composer install
npm install
bower install
```

### Compile and symlink the assets

The CSS framework used for the project is [Foundation](http://foundation.zurb.com/). Compile the assets by running the grunt build command:

```sh
grunt build
```

Then, when you work on the application stylesheets, you should ask to grunt for watching your assets changes:

```sh
grunt
```

Then install the assets in the web path:

```sh
./bin/warez assets:install
```

### Update the database schema

Open a SSH connection and run the Doctrine CLI tool.

```sh
vagrant ssh
cd /var/www/warez
./bin/doctrine orm:schema-tool:update --force
```

Now create a user.

```sh
./bin/warez user:create username email password
```

### Configure your tracker account

Now run your browser and open the URL [http://warez.dev](http://warez.dev). Sign in with the credentials of the user you
have just created. Open your profile page and enter your tracker credentials in the form.  
