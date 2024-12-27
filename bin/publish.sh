#!/usr/bin/env bash
dir_path=$(pwd)
dir_name=$(basename "$dir_path")
zip_name=$dir_name.zip

if [ -f "../$zip_name" ]; then
    rm "../$zip_name"
fi

if [ "$1" = "production" ]; then
    zip -r "../$zip_name" . -x "frontend/*.yaml" -x "strauss.phar" -x "frontend/*.lock" -x "tests/*" -x "bin/*" -x ".git/*" -x ".github/*" -x "node_modules/*" -x "composer.json" -x "composer.lock"
else
    zip -r "../$zip_name" . -x "frontend/*.yaml" -x "strauss.phar" -x "frontend/*.lock" -x "tests/*" -x "bin/*" -x ".git/*" -x ".github/*" -x "node_modules/*"
fi
