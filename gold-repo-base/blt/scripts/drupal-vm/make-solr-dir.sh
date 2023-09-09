#!/bin/bash

if [ ! -d /optsolr ]; then
  sudo mkdir /optsolr;
else
  exit 0
fi
