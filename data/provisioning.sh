#!/usr/bin/env bash

sudo usermod -aG adm vagrant
sudo apt-get update && sudo apt-get upgrade
sudo apt-get install -y php5-common php5-fpm php5-cli php5-curl php5-json php5-mysqlnd php5-xsl php5-memcached php5-intl \
php5-mcrypt php5-xdebug nginx transmission-daemon memcached mysql-server

cd /etc/nginx/sites-available
sudo cp /vagrant/data/nginx-vhost warez
cd ../sites-enabled
sudo ln -s ../sites-available/warez
sudo service nginx restart
