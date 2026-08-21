<?php

declare(strict_types=1);

require_once __DIR__ . '/render-readme-changelog.php';

$readme_examples = [
    'wordpress' => "== Changelog ==\n\n= 1.0.1 =\n* Added private updates.\n\n= 1.0.0 =\n* Initial release.",
    'markdown'  => "## Changelog ##\n\n### 1.0.1 ###\n* Added private updates.\n\n### 1.0.0 ###\n* Initial release.",
];
$release_dates = [
    '1.0.1' => '2026-08-21',
];
$expected_changelog = '<h3>1.0.1 - 2026-08-21</h3><ul><li>Added private updates.</li></ul><h3>1.0.0</h3><ul><li>Initial release.</li></ul>';

foreach ($readme_examples as $format => $readme_contents) {
    $rendered_changelog = render_readme_changelog($readme_contents, $release_dates);

    if ($rendered_changelog !== $expected_changelog) {
        fwrite(STDERR, "Rendered {$format} changelog did not match the expected output.\n");
        exit(1);
    }
}

echo "Changelog renderer tests passed.\n";
