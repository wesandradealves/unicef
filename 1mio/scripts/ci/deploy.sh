#!/bin/bash
set -x
set -e

if [ -n "${BITBUCKET_REPO_SLUG}" ] ; then
    rm -R drush
    rm docroot/sites/default/settings.local.php

    git config user.email "bitbucket@ciandt.com"
    git config user.name "Bitbucket Pipelines"

    git remote add deploy $DEPLOY_URL;

    git add -f docroot/themes/custom/umio_base/css/main.css
    git add -f docroot/themes/custom/umio/css/main.css
    git add -f docroot/themes/custom/admin/css/main.css

    # If the module is -dev, a .git file comes down.
    find docroot -name .git -print0 | xargs -0 rm -rf
    find vendor -name .git -print0 | xargs -0 rm -rf
    find vendor -name .gitignore -print0 | xargs -0 rm -rf

    SHA=$(git rev-parse HEAD)
    GIT_MESSAGE="Deploying ${SHA}: $(git log -1 --pretty=%B)"

    git add --force --all

    # Exclusions:
    git status
    git commit -qm "${GIT_MESSAGE}" --no-verify

    if [ $BITBUCKET_TAG ];
      then
        git tag --force -m "Deploying tag: ${BITBUCKET_TAG}" ${BITBUCKET_TAG}
        git push deploy refs/tags/${BITBUCKET_TAG}
    fi;

    if [ $BITBUCKET_BRANCH ];
      then
        git push deploy -v --force refs/heads/$BITBUCKET_BRANCH;
    fi;

    git reset --mixed $SHA;
fi;
