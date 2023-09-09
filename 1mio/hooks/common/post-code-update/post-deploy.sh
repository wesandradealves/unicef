#!/bin/sh
#
# Cloud Hook: post-deploy
#
# Run drush cache-clear all in the target environment and import configs


# Map the script inputs to convenient names.
site=$1
target_env=$2
drush_alias=$site'.'$target_env

drush10 @$drush_alias cr
drush10 @$drush_alias locale:check
drush10 @$drush_alias locale:update
drush10 @$drush_alias cim -y

# Execute a standard drush command.
drush10 @$drush_alias cr
