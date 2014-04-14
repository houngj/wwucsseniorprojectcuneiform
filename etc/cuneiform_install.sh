#!/bin/bash
set -e # Exit on any non-zero returns

REPO_DIR=`mktemp -d`

PACKAGES=(git git-core apache2 php5 phpmyadmin mysql-server-5.6 mysql-client-5.6 memcached php5-memcached openssh-server)

# Verify we're running Ubuntu 14.04
if [ $(lsb_release -r | sed 's/Release:\s*//') != "14.04" ]; then
    echo "mysql 5.6 is unavaliable on your version.";
    echo "Please run 'sudo do-release-upgrade -d' to upgrade";
    exit 1
fi

echo "Updating package repository"
sudo apt-get -y -qq update

echo "Installing package updates"
sudo apt-get -y -qq dist-upgrade

echo "Installing required packages (${PACKAGES[@]})"
# DEBIAN_FRONEND=noninteractive prevents prompting, we'll set the mysql root password later
sudo DEBIAN_FRONTEND=noninteractive apt-get -y -qq install "${PACKAGES[@]}"

# Generate a random password for MySQL's root user
PASSWORD=`tr -cd '[:alnum:]' < /dev/urandom | fold -w32 | head -n1`
echo "Setting mysql root password to $PASSWORD"
mysqladmin -u root password "$PASSWORD"

echo "Writing MySQL root password to '${HOME}/mysql_root_password'"
echo "$PASSWORD" > ${HOME}/mysql_root_password

echo "Cloning repository into '${REPO_DIR}'"
git clone https://github.com/brandontoner/wwucsseniorprojectcuneiform.git -b master "${REPO_DIR}"

# innodb_ft_min_token_size = 1 allows indexing of all words.
if ! grep 'innodb_ft_min_token_size = 1'  /etc/mysql/my.cnf >/dev/null; then
    echo "Adding 'innodb_ft_min_token_size = 1' to /etc/mysql/my.cnf"
    sudo sed -i "94iinnodb_ft_min_token_size = 1" /etc/mysql/my.cnf
fi

# Enable compression in apache2
echo "Setting 'zlib.output_compression = On' in /etc/php5/apache2/php.ini"
sudo sed -i "s/zlib.output_compression.*/zlib.output_compression = On/" /etc/php5/apache2/php.ini

echo "Restarting mysql"
sudo service mysql restart

echo "Restarting apache2"
sudo service apache2 restart

echo "Retreiving database dump"
wget "http://brandontoner.com/cuneiform_dump.sql.tar.bz2"
tar -xvf "cuneiform_dump.sql.tar.bz2"

echo "Importing schema"
mysql -u root -p$PASSWORD <${REPO_DIR}/schema/cuneiform_schema.sql

echo "Importing database dump"
mysql -u dingo -phungry! -D cuneiform <dump.sql

echo "Copying site"
if [ -d "/var/www/html" ]; then
    sudo rm -rf /var/www/html/*
    sudo cp -r ${REPO_DIR}/site/* /var/www/html
else
    sudo rm -f /var/www/*
    sudo cp -r ${REPO_DIR}/site/* /var/www
fi

echo "Cleaning up"
rm -r cuneiform_dump.sql.tar.bz2 dump.sql ${REPO_DIR}


