#!/usr/bin/env bash
#
# db-copy Cloud hook: db-scrub
#
# Scrub important information from a Drupal database.
#
# Usage: db-scrub.sh site target-env db-name source-env
set -ev
site="$1"
target_env="$2"
db_name="$3"
source_env="$4"
# Prep for BLT commands.
repo_root="/var/www/html/$site.$target_env"
export PATH=$repo_root/vendor/bin:$PATH
cd $repo_root

echo "$site.$target_env: Scrubbing database $db_name"

# Only perform on V4 relatd sites
if [ "$site" == 'unicefv4' ] && [ "$target_env" != 'prod' ]; then
(cat <<EOF
UPDATE users_field_data SET mail=CONCAT('untidv1+', uid, '@gmail.com'), init=CONCAT('untidv1+', uid, '@gmail.com') WHERE uid != 0;
EOF
) | drush @$site.$target_env ah-sql-cli --db=$db_name
fi

if [ "$site" == 'unacp1' ] || [ "$site" == 'unacp4' ] || [ "$site" == 'unacp6' ] || [ "$site" == 'unacp8' ]; then
(cat <<EOF
UPDATE users_field_data SET mail=CONCAT('untidv1+', uid, '@gmail.com'), init=CONCAT('untidv1+', uid, '@gmail.com') WHERE uid != 0;
EOF
) | drush @$site.$target_env ah-sql-cli --db=$db_name
fi
set +v
