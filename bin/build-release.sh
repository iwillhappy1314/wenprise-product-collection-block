#!/usr/bin/env bash

set -euo pipefail

plugin_slug="wenprise-product-collection-block"
plugin_name="Wenprise Product Collection Block"
plugin_description="Add a product collection taxonomy for WooCommerce and display products by collection."
plugin_file="wenprise-product-collection-block.php"
version_constant="WENPRISE_PRODUCT_COLLECTION_BLOCK_VERSION"
tag_name="${1:-}"
repository="${2:-${GITHUB_REPOSITORY:-}}"
project_path="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
release_path="${project_path}/.release"

if [[ -z "${tag_name}" || "${tag_name}" != v* ]]; then
    echo "Error: provide a v-prefixed release tag, for example v1.2.3." >&2
    exit 1
fi

if [[ -z "${repository}" ]]; then
    echo "Error: provide the GitHub repository as owner/repository." >&2
    exit 1
fi

version="${tag_name#v}"
plugin_version="$(sed -nE 's/^[[:space:]]*\*[[:space:]]*Version:[[:space:]]*([^[:space:]]+).*/\1/p' "${project_path}/${plugin_file}" | head -n 1)"
constant_version="$(sed -nE "s/^[[:space:]]*define\([[:space:]]*'${version_constant}',[[:space:]]*'([^']+)'[[:space:]]*\);/\1/p" "${project_path}/${plugin_file}" | head -n 1)"
stable_tag="$(sed -nE 's/^[[:space:]]*Stable tag:[[:space:]]*([^[:space:]]+).*/\1/p' "${project_path}/readme.txt" | head -n 1)"
requires_wordpress="$(sed -nE 's/^[[:space:]]*Requires at least:[[:space:]]*(.+)[[:space:]]*$/\1/p' "${project_path}/readme.txt" | head -n 1)"
requires_php="$(sed -nE 's/^[[:space:]]*Requires PHP:[[:space:]]*(.+)[[:space:]]*$/\1/p' "${project_path}/readme.txt" | head -n 1)"
tested_wordpress="$(sed -nE 's/^[[:space:]]*Tested up to:[[:space:]]*(.+)[[:space:]]*$/\1/p' "${project_path}/readme.txt" | head -n 1)"

if [[ "${version}" != "${plugin_version}" || "${version}" != "${constant_version}" || "${version}" != "${stable_tag}" ]]; then
    echo "Error: tag, plugin header, version constant, and Stable tag must match." >&2
    exit 1
fi

work_path="$(mktemp -d)"
trap 'rm -rf "${work_path}"' EXIT
version_dates_path="${work_path}/release-version-dates.tsv"
release_date=""
last_updated=""

while IFS= read -r release_tag; do
    tag_release_date="$(git -C "${project_path}" for-each-ref "refs/tags/${release_tag}" --format='%(taggerdate:format:%Y-%m-%d)')"
    tag_last_updated="$(git -C "${project_path}" for-each-ref "refs/tags/${release_tag}" --format='%(taggerdate:iso-strict)')"

    if [[ -z "${tag_release_date}" || -z "${tag_last_updated}" ]]; then
        tag_release_date="$(git -C "${project_path}" log -1 --format='%cs' "${release_tag}^{commit}")"
        tag_last_updated="$(git -C "${project_path}" log -1 --format='%cI' "${release_tag}^{commit}")"
    fi

    printf '%s\t%s\n' "${release_tag#v}" "${tag_release_date}" >> "${version_dates_path}"

    if [[ "${release_tag}" == "${tag_name}" ]]; then
        release_date="${tag_release_date}"
        last_updated="${tag_last_updated}"
    fi
done < <(git -C "${project_path}" tag --list --sort=version:refname 'v*')

if [[ -z "${release_date}" || -z "${last_updated}" ]]; then
    echo "Error: release tag ${tag_name} does not exist." >&2
    exit 1
fi

mkdir -p "${release_path}" "${work_path}/${plugin_slug}"
find "${release_path}" -mindepth 1 -maxdepth 1 -type f -delete

rsync -a "${project_path}/" "${work_path}/${plugin_slug}/" \
    --exclude='.git/' \
    --exclude='.github/' \
    --exclude='.release/' \
    --exclude='.DS_Store' \
    --exclude='.editorconfig' \
    --exclude='.gitignore' \
    --exclude='IMPLEMENTATION_PLAN.md' \
    --exclude='bin/' \
    --exclude='composer.json' \
    --exclude='composer.lock' \
    --exclude='node_modules/' \
    --exclude='package.json' \
    --exclude='package-lock.json' \
    --exclude='pnpm-lock.yaml' \
    --exclude='pnpm-workspace.yaml' \
    --exclude='src/' \
    --exclude='tests/' \
    --exclude='vendor/bin/'

zip_name="${plugin_slug}-${version}.zip"
zip_path="${release_path}/${zip_name}"

(
    cd "${work_path}"
    zip -q -r "${zip_path}" "${plugin_slug}"
)

checksum="$(php -r 'echo hash_file("sha256", $argv[1]);' "${zip_path}")"
printf '%s  %s\n' "${checksum}" "${zip_name}" > "${release_path}/${zip_name}.sha256"

download_url="https://github.com/${repository}/releases/download/${tag_name}/${zip_name}"
details_url="https://github.com/${repository}/releases/tag/${tag_name}"
metadata_path="${release_path}/${plugin_slug}.json"
changelog="$(php "${project_path}/bin/render-readme-changelog.php" "${project_path}/readme.txt" "${version_dates_path}")"

if [[ -z "${changelog}" ]]; then
    echo "Error: readme.txt does not contain a publishable Changelog section." >&2
    exit 1
fi

expected_release_heading="<h3>${version} - ${release_date}</h3>"

if [[ "${changelog}" != *"${expected_release_heading}"* ]]; then
    echo "Error: readme.txt Changelog does not contain the current release version ${version}." >&2
    exit 1
fi

php -r '
$metadata = [
    "name" => $argv[1], "slug" => $argv[2], "version" => $argv[3],
    "download_url" => $argv[4], "details_url" => $argv[5],
    "requires" => $argv[6], "requires_php" => $argv[7], "tested" => $argv[8],
    "last_updated" => $argv[9], "sha256" => $argv[10],
    "sections" => ["description" => $argv[11], "changelog" => $argv[12]],
];
file_put_contents($argv[13], json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL);
' "${plugin_name}" "${plugin_slug}" "${version}" "${download_url}" "${details_url}" "${requires_wordpress}" "${requires_php}" "${tested_wordpress}" "${last_updated}" "${checksum}" "${plugin_description}" "${changelog}" "${metadata_path}"

echo "Built ${zip_path}, ${metadata_path}, and ${zip_path}.sha256"
