<?php

/**
 * Verifies the private updater integration contract without loading WordPress.
 */

$project_path = dirname(__DIR__, 2);
$plugin_file = $project_path . '/wenprise-product-collection-block.php';
$composer_file = $project_path . '/composer.json';
$readme_file = $project_path . '/readme.txt';
$package_file = $project_path . '/package.json';
$block_file = $project_path . '/src/block.json';
$build_block_file = $project_path . '/build/block.json';
$build_script_file = $project_path . '/bin/build-release.sh';
$workflow_file = $project_path . '/.github/workflows/publish-release.yml';
$puc_file = $project_path . '/vendor/yahnis-elsts/plugin-update-checker/plugin-update-checker.php';

/**
 * Fails the updater contract test when a condition is false.
 *
 * @param bool   $condition Test result.
 * @param string $message   Failure message.
 *
 * @return void
 */
function assert_updater_contract($condition, $message)
{
	if (!$condition) {
		fwrite(STDERR, "FAIL: {$message}\n");
		exit(1);
	}
}

$plugin_contents = file_get_contents($plugin_file);
$composer_contents = is_readable($composer_file) ? file_get_contents($composer_file) : false;
$readme_contents = file_get_contents($readme_file);
$package_contents = file_get_contents($package_file);
$block_contents = file_get_contents($block_file);
$build_block_contents = file_get_contents($build_block_file);
$build_script_contents = is_readable($build_script_file) ? file_get_contents($build_script_file) : false;
$workflow_contents = is_readable($workflow_file) ? file_get_contents($workflow_file) : false;
$composer_data = json_decode((string) $composer_contents, true);
$package_data = json_decode((string) $package_contents, true);
$block_data = json_decode((string) $block_contents, true);
$build_block_data = json_decode((string) $build_block_contents, true);

assert_updater_contract(false !== $plugin_contents, 'The main plugin file must be readable.');
assert_updater_contract(false !== $readme_contents, 'readme.txt must be readable.');
assert_updater_contract(is_array($composer_data), 'composer.json must contain valid JSON.');
assert_updater_contract(is_array($package_data), 'package.json must contain valid JSON.');
assert_updater_contract(is_array($block_data), 'src/block.json must contain valid JSON.');
assert_updater_contract(is_array($build_block_data), 'build/block.json must contain valid JSON.');
assert_updater_contract(false !== $build_script_contents, 'The release build script must be readable.');
assert_updater_contract(false !== $workflow_contents, 'The release workflow must be readable.');
assert_updater_contract(is_readable($puc_file), 'The production PUC package must be installed.');
assert_updater_contract(
	isset($composer_data['require']['yahnis-elsts/plugin-update-checker']),
	'Plugin Update Checker must be a production Composer dependency.'
);
assert_updater_contract(
	false !== strpos($plugin_contents, 'WENPRISE_PRODUCT_COLLECTION_BLOCK_FILE'),
	'The main plugin file constant must be defined.'
);
assert_updater_contract(
	false !== strpos($plugin_contents, 'https://api.wpcio.com/api/plugin/info/wenprise-product-collection-block'),
	'The updater must use the WPCIO plugin information endpoint.'
);
assert_updater_contract(
	false !== strpos($plugin_contents, 'wprs_product_collection_set_update_checker'),
	'The plugin runtime must register the private update checker.'
);

preg_match('/^[[:space:]]*\*[[:space:]]*Version:[[:space:]]*([^[:space:]]+)/m', (string) $plugin_contents, $header_matches);
preg_match("/define\('WENPRISE_PRODUCT_COLLECTION_BLOCK_VERSION',[[:space:]]*'([^']+)'\);/", (string) $plugin_contents, $constant_matches);
preg_match('/^[[:space:]]*Stable tag:[[:space:]]*([^[:space:]]+)/m', (string) $readme_contents, $stable_tag_matches);

$header_version = $header_matches[1] ?? '';
$constant_version = $constant_matches[1] ?? '';
$stable_tag = $stable_tag_matches[1] ?? '';

assert_updater_contract('1.0.1' === $header_version, 'The updater release must use version 1.0.1.');
assert_updater_contract($header_version === $constant_version, 'Header and version constant must match.');
assert_updater_contract($header_version === $stable_tag, 'Header and Stable tag must match.');
assert_updater_contract($header_version === ($package_data['version'] ?? ''), 'Header and package version must match.');
assert_updater_contract($header_version === ($block_data['version'] ?? ''), 'Header and source block version must match.');
assert_updater_contract($header_version === ($build_block_data['version'] ?? ''), 'Header and built block version must match.');
assert_updater_contract(
	false !== strpos((string) $readme_contents, '= 1.0.1 ='),
	'readme.txt must contain the 1.0.1 changelog heading.'
);
assert_updater_contract(
	false !== strpos((string) $build_script_contents, 'plugin_slug="wenprise-product-collection-block"'),
	'The release builder must use the plugin slug.'
);
assert_updater_contract(
	false !== strpos((string) $build_script_contents, "--exclude='vendor/bin/'"),
	'The release builder must exclude vendor/bin.'
);
assert_updater_contract(
	false !== strpos((string) $workflow_contents, 'https://api.wpcio.com/api/plugin/upload/'),
	'The workflow must upload to the WPCIO plugin route.'
);
assert_updater_contract(
	false === strpos((string) $workflow_contents, '/api/theme/'),
	'The plugin workflow must never use theme routes.'
);
assert_updater_contract(
	false === stripos((string) $workflow_contents, 'wpzhiku'),
	'The workflow must not upload releases to WPZhiku when its preflight fails.'
);
assert_updater_contract(
	!is_file($project_path . '/bin/publish.sh') && !is_file($project_path . '/bin/release.sh'),
	'Legacy release scripts must not coexist with the deterministic workflow.'
);
assert_updater_contract(
	!is_file($project_path . '/package-lock.json'),
	'The pnpm project must not retain a stale npm lockfile.'
);
preg_match_all('/secrets\.([A-Z0-9_]*RELEASE_UPLOAD_TOKEN)/', (string) $workflow_contents, $secret_matches);
assert_updater_contract(
	!empty($secret_matches[1]) && 1 === count(array_unique($secret_matches[1])) && 'WENPRISE_RELEASE_UPLOAD_TOKEN' === $secret_matches[1][0],
	'The workflow must use only secrets.WENPRISE_RELEASE_UPLOAD_TOKEN.'
);
assert_updater_contract(
	!preg_match('/__(?:PLUGIN|THEME|PROJECT|VERSION|API|PHP|DETAILS|STOREFRONT|UPDATE)[A-Z0-9_]*__/', (string) $build_script_contents . (string) $workflow_contents),
	'Release files must not contain unreplaced template tokens.'
);

fwrite(STDOUT, "Updater contract tests passed.\n");
