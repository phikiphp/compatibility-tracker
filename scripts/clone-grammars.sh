#!/usr/bin/env bash

echo "Cloning grammars repository..."

if [[ -n $GH_TOKEN ]]; then
  GIT_REPO_URL="https://x-access-token:${GH_TOKEN}@github.com/shikijs/textmate-grammars-themes.git"
else
  GIT_REPO_URL="git@github.com:shikijs/textmate-grammars-themes.git"
fi

git clone --depth=1 --single-branch --branch=main "$GIT_REPO_URL" textmate-grammars-themes
rm -rf textmate-grammars-themes/.git

echo "Cloned grammars into textmate-grammars-themes directory."
