#!/bin/sh
set -eu

CRON_FILE="/etc/cron.daily/currencyrate"
PHP_BIN="${PHP_BIN:-php}"

CRON_URL="$("${PHP_BIN}" -r '
chdir("/var/www/html");
require "/var/www/html/config/config.inc.php";

$secureKey = md5(_COOKIE_KEY_ . (string) Configuration::get("PS_SHOP_NAME"));
$adminDirectoryName = trim((string) getenv("PS_FOLDER_ADMIN"));
if ($adminDirectoryName === "") {
    $adminCandidates = glob("/var/www/html/admin*/cron_currency_rates.php");
    if (is_array($adminCandidates) && $adminCandidates !== []) {
        $adminDirectoryName = basename((string) dirname($adminCandidates[0]));
    }
}
if ($adminDirectoryName === "") {
    throw new RuntimeException("Could not resolve PrestaShop admin directory.");
}
$shopBaseUri = trim((string) __PS_BASE_URI__, "/");
$shopBasePath = $shopBaseUri === "" ? "" : "/" . $shopBaseUri;

$domain = getenv("PS_DOMAIN");
if (!is_string($domain) || trim($domain) === "") {
    $domain = (string) Configuration::get("PS_SHOP_DOMAIN");
}
$domain = preg_replace("#^https?://#i", "", trim($domain, "/"));

$scheme = getenv("PS_SCHEME");
if (!is_string($scheme) || ($scheme !== "http" && $scheme !== "https")) {
    $scheme = "http";
}

echo sprintf(
    "%s://%s%s/%s/cron_currency_rates.php?secure_key=%s",
    $scheme,
    $domain,
    $shopBasePath,
    $adminDirectoryName,
    $secureKey
);
')"

if [ -z "${CRON_URL}" ]; then
  echo "[currencyrate-init] Could not resolve cron URL." >&2
  exit 1
fi

echo
echo "=============================================================="
echo "== [CURRENCYRATE CRON URL]"
echo "== ${CRON_URL}"
echo "=============================================================="
echo
