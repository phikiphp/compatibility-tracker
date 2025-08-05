#!/usr/bin/env bash

echo "Cloning grammars repository..."

git clone --depth=1 --single-branch --branch=main git@github.com:shikijs/textmate-grammars-themes.git
rm -rf textmate-grammars-themes/.git

echo "Cloned grammars into textmate-grammars-themes directory."
