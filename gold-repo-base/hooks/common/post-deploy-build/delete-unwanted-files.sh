#!/bin/bash
#
# Cloud Hook: post-code-deploy
#
# The post-code-deploy hook is run whenever you use the Workflow page to
# deploy new code to an environment, either via drag-drop or by selecting
# an existing branch or tag from the Code drop-down list. See
# ../README.md for details.
#
# Usage: post-code-deploy site target-env source-branch deployed-tag repo-url
#                         repo-type

site="$1"
target_env="$2"
source_branch="$3"
deployed_tag="$4"
repo_url="$5"
repo_type="$6"

# In BLT 12 the artifact is build in /tmp/blt-deploy.
BASE_DIR="/tmp/blt-deploy"

# Drop files that are not needed on production, information leakage ~ security.
rm -r "$BASE_DIR"/docroot/modules/contrib/ctools/tests
rm -r "$BASE_DIR"/docroot/modules/contrib/metatag/tests
find "$BASE_DIR"/docroot/sites -name "default.local.settings.php" | xargs rm
rm -r "$BASE_DIR"/docroot/libraries/cropper/demo
rm -r "$BASE_DIR"/docroot/libraries/cropper/docs
rm -r "$BASE_DIR"/docroot/libraries/cropper/examples
rm -r "$BASE_DIR"/docroot/libraries/cropper/test
rm "$BASE_DIR"/docroot/libraries/cropper/ISSUE_TEMPLATE.md
rm "$BASE_DIR"/docroot/libraries/cropper/assets/css/bootstrap.min.css
rm "$BASE_DIR"/docroot/libraries/cropper/assets/css/font-awesome.min.css
rm "$BASE_DIR"/docroot/libraries/cropper/assets/css/qunit.css
rm "$BASE_DIR"/docroot/libraries/cropper/assets/js/bootstrap.min.js
rm "$BASE_DIR"/docroot/libraries/cropper/assets/js/jquery.min.js
rm "$BASE_DIR"/docroot/libraries/cropper/assets/js/qunit.js
rm "$BASE_DIR"/docroot/libraries/cropper/gulpfile.js
rm "$BASE_DIR"/docroot/core/install.php
rm "$BASE_DIR"/docroot/core/authorize.php
rm "$BASE_DIR"/docroot/core/LICENSE.txt
rm "$BASE_DIR"/docroot/core/package.json
rm -r "$BASE_DIR"/docroot/modules/contrib/eva/tests
rm -r "$BASE_DIR"/docroot/modules/custom/unicef_portable_fixtures
rm -r "$BASE_DIR"/vendor/simplesamlphp/simplesamlphp/docs
rm -r "$BASE_DIR"/vendor/simplesamlphp/simplesamlphp/bin
rm -r "$BASE_DIR"/vendor/simplesamlphp/simplesamlphp/modules/exampleattributeserver
rm -r "$BASE_DIR"/vendor/simplesamlphp/simplesamlphp/modules/exampleauth
rm "$BASE_DIR"/docroot/core/phpcs.xml.dist
rm "$BASE_DIR"/docroot/core/phpunit.xml.dist
rm "$BASE_DIR"/docroot/modules/contrib/search_api_solr/*.txt
rm "$BASE_DIR"/docroot/modules/contrib/search_api/*.txt
rm -r "$BASE_DIR"/docroot/modules/contrib/search_api_solr/tests
rm -r "$BASE_DIR"/docroot/modules/contrib/search_api_autocomplete/tests
rm -r "$BASE_DIR"/docroot/docs
rm -r "$BASE_DIR"/docroot/tests
#remove from individual sites.
ALLSITEFILES="$BASE_DIR/docroot/scripts/delete-unwanted-files.txt"
if [ -f "$ALLSITEFILES" ]
then
  while IFS= read -r line1
  do
    fileordir=$line1
    echo "deleting- $BASE_DIR/docroot/$fileordir"
    rm -r "$BASE_DIR/docroot/$fileordir"
  done < $ALLSITEFILES
else
  echo "$ALLSITEFILES not found"
fi
echo "deleted sites unwanted file $ALLSITEFILES"
rm -r "$BASE_DIR"/docroot/scripts
rm "$BASE_DIR"/docroot/blt.yml
rm "$BASE_DIR"/docroot/README.md
rm "$BASE_DIR"/docroot/.travis.yml
rm -r "$BASE_DIR"/docroot/modules/contrib/migrate_plus/tests
rm -r "$BASE_DIR"/docroot/modules/contrib/libraries/tests
rm -r "$BASE_DIR"/docroot/core/modules/update/tests
rm -r "$BASE_DIR"/docroot/libraries/jquery/src
rm -r "$BASE_DIR"/docroot/core/tests
rm "$BASE_DIR"/docroot/update.php
