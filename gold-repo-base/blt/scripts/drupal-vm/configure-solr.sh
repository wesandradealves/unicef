#!/bin/bash
#
# Example shell script to run post-provisioning.
#
# This script configures the default Apache Solr search core to use one of the
# Drupal Solr module's configurations. This shell script presumes you have
# `solr` in the `installed_extras`, and is currently set up for the D8 versions
# of Search API Solr.
#
# It's also intended for Solr 4.5. For other versions of Solr, refer to the
# example scripts provided by DrupalVM.
set -e

SOLR_SETUP_COMPLETE_FILE="/etc/drupal_vm_solr_config_complete_collection1"

# Search API Solr module.
SOLR_MODULE_NAME="search_api_solr"
SOLR_VERSION="7.x"
SOLR_CORE_PATH="/data/solr/data/collection1"
UNICEF_SOLR_CONF_PATH="/var/www/unicefplatform/solr-unicef/conf"

# Check to see if we've already performed this setup.
if [ ! -e "$SOLR_SETUP_COMPLETE_FILE" ]; then
  # Copy the Solr configuration into place over the default `collection1` core.
  sudo service solr stop
  echo "search clonning..";

  # Check if custom solr unicef directory exists.
  if [ -d "/var/www/unicefplatform/ansible-playbook-search/roles/solr-unicef/conf" ]; then
    sudo cp -a /var/www/unicefplatform/ansible-playbook-search/roles/solr-unicef/conf/. $SOLR_CORE_PATH/conf/
    echo "copy solr conf from git complete";
  elif [ -d "$UNICEF_SOLR_CONF_PATH" ]; then
    sudo cp -a $UNICEF_SOLR_CONF_PATH/. $SOLR_CORE_PATH/conf/
  else
    sudo cp -a /var/www/unicefplatform/docroot/modules/contrib/$SOLR_MODULE_NAME/solr-conf-templates/$SOLR_VERSION/. $SOLR_CORE_PATH/conf/
  fi

  # Adjust the autoCommit time so index changes are committed in 1s.
  sudo sed -i 's/\(<maxTime>\)\([^<]*\)\(<[^>]*\)/\11000\3/g' $SOLR_CORE_PATH/conf/solrconfig.xml

  # Fix file permissions.
  sudo chown -R solr:solr $SOLR_CORE_PATH/conf

  # Restart Apache Solr.
  sudo service solr restart

  # Create a file to indicate this script has already run.
  sudo touch $SOLR_SETUP_COMPLETE_FILE
else
  exit 0
fi
