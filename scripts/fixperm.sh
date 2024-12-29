#!/bin/bash

# Abort if anything fails
set -e

source .env

sudo chown -R "$WODBY_USER_ID:$WODBY_GROUP_ID" .
