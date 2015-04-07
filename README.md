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

### Configure your tracker account

Now run your browser and open the URL [http://warez.dev](http://warez.dev). Sign in with the dev user:

- email: nice-user@warez.io
- password: warezisfun

Open your profile page and enter your tracker credentials in the form.  
