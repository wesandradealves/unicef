#!/bin/bash
# sudo apt-get update --allow-releaseinfo-change && a2enmod proxy_fcgi setenvif && a2enconf php7.4-fpm
# sudo apt-get install -o Dpkg::Options::="--force-confold" -y php7.4-bz2 curl && apt-get autoremove

# echo "PHP Version";
# php -r "echo PHP_VERSION;"

# echo "########################## Install NodeJS, NPM and Gulp ############################"
# sudo apt-get update --allow-releaseinfo-change
# curl -sL https://deb.nodesource.com/setup_14.x | sudo -E bash -
# sudo apt-get install -y nodejs
# sudo apt-get install -y npm

# # Install Gulp
# npm install -g gulp

echo "############################## Run Composer Install ################################"
cd /opt/atlassian/pipelines/agent/build
composer install

echo "#################################### Build Theme ###################################"
cd docroot/themes/custom/umio_base && npm install && npm run gulp build
rm -R node_modules

cd /opt/atlassian/pipelines/agent/build
cd docroot/themes/custom/umio && npm install && npm run gulp build
rm -R node_modules

cd /opt/atlassian/pipelines/agent/build
cd docroot/themes/custom/admin && npm install && npm run gulp build
rm -R node_modules

export PIPELINES_ENV=PIPELINES_ENV
