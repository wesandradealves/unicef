#!/bin/sh

phpcs --config-set installed_paths ../../drupal/coder/coder_sniffer
cp /app/lando/pre-commit.sh /app/.git/hooks/pre-commit
chmod +x /app/.git/hooks/pre-commit
