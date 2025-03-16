#!/bin/bash

echo "Setting up Git hooks..."

cp hooks/* .git/hooks/
chmod +x .git/hooks/*
