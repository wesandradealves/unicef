#!/bin/sh

DEFAULT=$(tput setaf 255)$(tput setab 0)$(tput bold)
SUCCESS=$(tput setaf 2)$(tput setab 0)$(tput bold)
ERROR=$(tput setaf 255)$(tput setab 1)$(tput bold)

echo "$DEFAULT RUNNING PHPCS$(tput sgr 0)"
if lando phpcs ; then
	echo "$SUCCESS OK$(tput sgr 0)"
else
	echo "$ERROR ERROR IN PHPCS, PLEASE FIX THE ERRORS BEFORE COMMIT$(tput sgr 0)"
	exit 1
fi

# echo "$DEFAULT RUNNING PHPUNIT$(tput sgr 0)"
# if lando phpunit ; then
# 	echo "$SUCCESS OK$(tput sgr 0)"
# else
# 	echo "$ERROR ERROR IN PHPUNIT, PLEASE FIX THE ERRORS BEFORE COMMIT$(tput sgr 0)"
# 	exit 1
# fi

echo "$DEFAULT RUNNING PHPSTAN$(tput sgr 0)"
if lando phpstan ; then
	echo "$SUCCESS OK$(tput sgr 0)"
else
	echo "$ERROR ERROR IN PHPSTAN, PLEASE FIX THE ERRORS BEFORE COMMIT$(tput sgr 0)"
	exit 1
fi
