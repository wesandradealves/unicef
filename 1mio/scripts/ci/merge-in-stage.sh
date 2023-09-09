#!/usr/bin/env bash

# Exit immediately if a any command exits with a non-zero status
# e.g. pull-request merge fails because of conflict
set -e

# Set destination branch
DEST_BRANCH=$1

# Create new pull request and get its ID
echo "Creating automatic PR: $BITBUCKET_BRANCH -> $DEST_BRANCH"
# echo "BITBUCKET_REPO_OWNER => $BITBUCKET_REPO_OWNER";
# echo "BITBUCKET_REPO_SLUG => $BITBUCKET_REPO_SLUG";
# echo "DEST_BRANCH => $DEST_BRANCH";
# echo "BB_USER => $BB_USER";
# echo "BB_PASSWORD => $BB_PASSWORD";
PR_ID=`curl -X POST https://api.bitbucket.org/2.0/repositories/$BITBUCKET_REPO_OWNER/$BITBUCKET_REPO_SLUG/pullrequests \
  --fail --show-error \
  --user $BB_USER:$BB_PASSWORD \
  -H 'content-type: application/json' \
  -d '{
    "title": "Scheduled PR: '$BITBUCKET_BRANCH' -> '$DEST_BRANCH'",
    "description": "Automatic PR from pipelines",
    "state": "OPEN",
    "destination": {
      "branch": {
              "name": "'$DEST_BRANCH'"
          }
    },
    "source": {
      "branch": {
              "name": "'$BITBUCKET_BRANCH'"
          }
    }
  }' \
  | sed -E "s/.*\"id\": ([0-9]+).*/\1/g"`

# Merge PR
echo "Merging PR: $PR_ID"
curl -X POST https://api.bitbucket.org/2.0/repositories/$BITBUCKET_REPO_OWNER/$BITBUCKET_REPO_SLUG/pullrequests/$PR_ID/merge \
  --fail --show-error --silent \
  --user $BB_USER:$BB_PASSWORD \
  -H 'content-type: application/json' \
  -d '{
    "close_source_branch": false,
    "merge_strategy": "merge_commit"
  }'
