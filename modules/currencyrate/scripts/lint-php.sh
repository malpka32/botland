#!/usr/bin/env bash

set -euo pipefail

if command -v git >/dev/null 2>&1 && git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
  files="$(git ls-files -- '*.php' ':!:vendor/*')"
else
  shopt -s globstar nullglob
  php_files=(**/*.php)
  filtered_files=()
  for file in "${php_files[@]}"; do
    if [[ "${file}" == vendor/* ]]; then
      continue
    fi
    filtered_files+=("${file}")
  done
  files="$(printf '%s\n' "${filtered_files[@]:-}")"
fi

if [[ -z "${files}" ]]; then
  echo "No PHP files to lint."
  exit 0
fi

while IFS= read -r file; do
  [[ -z "${file}" ]] && continue
  php -d error_reporting=E_ALL\&~E_DEPRECATED -l "${file}"
done <<< "${files}"
