#!/usr/bin/env bash

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"
MODULE_DIR="${PROJECT_ROOT}/modules/currencyrate"
OUTPUT_DIR="${PROJECT_ROOT}/dist"

if [[ ! -d "${MODULE_DIR}" ]]; then
  echo "Module directory not found: ${MODULE_DIR}" >&2
  exit 1
fi

MODULE_VERSION="$(php -r '
  $path = $argv[1];
  $content = @file_get_contents($path);
  if ($content === false) {
      fwrite(STDERR, "Cannot read module file: {$path}\n");
      exit(1);
  }
  if (!preg_match("/version\\s*=\\s*'\''([^'\'']+)'\''\\s*;/", $content, $matches)) {
      fwrite(STDERR, "Cannot detect module version from {$path}\n");
      exit(1);
  }
  echo $matches[1];
' "${MODULE_DIR}/currencyrate.php")"

mkdir -p "${OUTPUT_DIR}"
ARCHIVE_NAME="currencyrate-v${MODULE_VERSION}.zip"
ARCHIVE_PATH="${OUTPUT_DIR}/${ARCHIVE_NAME}"

rm -f "${ARCHIVE_PATH}"

(
  cd "${PROJECT_ROOT}"
  zip -qr "${ARCHIVE_PATH}" "modules/currencyrate" \
    -x "modules/currencyrate/.git/*" \
    -x "modules/currencyrate/.github/*" \
    -x "modules/currencyrate/tests/*" \
    -x "modules/currencyrate/phpunit.xml.dist"
)

echo "Built package: ${ARCHIVE_PATH}"
