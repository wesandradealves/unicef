#!/bin/bash
#
# Cloud Hook: post-code-update
#
# The post-code-update hook runs in response to code commits. When you
# push commits to a Git branch, the post-code-update hooks runs for
# each environment that is currently running that branch.
#
# The arguments for post-code-update are the same as for post-code-deploy,
# with the source-branch and deployed-tag arguments both set to the name of
# the environment receiving the new code.
#
# post-code-update only runs if your site is using a Git repository. It does
# not support SVN.

set -ev

site="$1"
target_env="$2"
source_branch="$3"
deployed_tag="$4"
repo_url="$5"
repo_type="$6"

if [ ! -f /var/www/html/$site.$target_env/hooks/skip_deployment ]; then
  # Prep for BLT commands.
  repo_root="/var/www/html/$site.$target_env"
  export PATH=$repo_root/vendor/bin:$PATH
  cd $repo_root

  mkdir -p /mnt/gfs/home/${AH_SITE_GROUP}/logs/${AH_SITE_NAME}/$(hostname -s)
  DEPLOYLOGFILE="/mnt/gfs/home/${AH_SITE_GROUP}/logs/${AH_SITE_NAME}/$(hostname -s)/post-code-update-hook-build-$(date +"%Y_%m_%d_%I_%M_%p").log"
  if [[ "$site" = "unicefv4" ]]; then
  	# Filter log for V4 due to big log size.
    blt artifact:ac-hooks:post-code-update $site $target_env $source_branch $deployed_tag $repo_url $repo_type --environment=$target_env -v --no-interaction -D drush.ansi=false  2>&1 | tee $DEPLOYLOGFILE | grep 'Running\|failed\|error\|Exception'
  else
    blt artifact:ac-hooks:post-code-update $site $target_env $source_branch $deployed_tag $repo_url $repo_type --environment=$target_env -v --no-interaction -D drush.ansi=false  2>&1 | tee $DEPLOYLOGFILE
  fi
fi

if [[ ${AH_SITE_GROUP} == "unacp1" || ${AH_SITE_GROUP} == "unacp4" || ${AH_SITE_GROUP} == "unacp6" || ${AH_SITE_GROUP} == "unacp8" ]];
then
  drush user:unblock admin
  drush user:password admin admin
fi

set +v
