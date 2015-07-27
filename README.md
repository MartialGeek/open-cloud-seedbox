# Open Cloud Seedbox

## Why

This application was designed for simplifying the use of a torrent client installed on a seedbox.
Currently, there is only one tracker supported: T411. And if you want to use the upload feature of the application,
only the Freebox v6 adapter is implemented.
A basic file browser is also provided.

## Installation (dev)

If you want to contribute to the project, a Vagrant box is configured to make the installation easier.

### Requirements

- [Vagrant](https://www.vagrantup.com)

### Get the code

Fork the project and clone it.

```sh
git clone git@github.com:MyUserName/open-cloud-seedbox.git
```

### Generate a GitHub token

The provided Vagrant box embeds Composer, and it needs a GitHub token to install and update the project
dependencies. Open [this page of your profile](https://github.com/settings/tokens/new) and generate a new
token with the default parameters. Copy the generated token, copy the
file data/provisioning/files/composer-auth.json.dist without ".dist" and replace the empty string of the "github.com"
json attribute by your token.

### Run the box

Go into your application path and run the Vagrant box. A Puppet provisioning will install the packages needed by
the application.

```sh
cd path/to/app
vagrant up
```

### Edit your "hosts" file

Edit your "hosts" file (/etc/hosts on Linux or OSX or c:\Windows\System32\drivers\etc\hosts on Windows) to add the
seedbox.dev IP (default to 192.168.0.42):

```
127.0.0.1      localhost
172.0.0.42     seedbox.dev
```

### Configure your tracker account

Now run your browser and open the URL [http://seedbox.dev](http://seedbox.dev). Sign in with the dev user:

- email: nice-user@seedbox.io
- password: myseedbox

Open your profile page and enter your tracker credentials in the form.  
