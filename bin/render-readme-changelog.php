<?php

declare(strict_types=1);

/**
 * Convert a WordPress readme.txt Changelog section to safe basic HTML.
 *
 * @param string                $readme_contents Complete readme.txt contents.
 * @param array<string, string> $release_dates   Release dates keyed by version.
 *
 * @return string
 */
function render_readme_changelog(
    string $readme_contents,
    array $release_dates = []
): string {
    if (!preg_match('/^(?:==\s*Changelog\s*==|##\s*Changelog\s*##)\s*$\R(.*?)(?=^(?:==|##)\s|\z)/imsu', $readme_contents, $matches)) {
        return '';
    }

    $html = '';
    $list_open = false;
    $lines = preg_split('/\R/u', trim($matches[1]));

    foreach ($lines ?: [] as $line) {
        $line = trim($line);

        if (preg_match('/^(?:=\s*(.+?)\s*=|###\s*(.+?)\s*###)$/u', $line, $heading_matches)) {
            if ($list_open) {
                $html .= '</ul>';
                $list_open = false;
            }

            $heading = trim((string) ($heading_matches[1] !== '' ? $heading_matches[1] : $heading_matches[2]));

            if (isset($release_dates[$heading]) && $release_dates[$heading] !== '') {
                $heading .= ' - ' . $release_dates[$heading];
            }

            $html .= '<h3>' . htmlspecialchars($heading, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</h3>';
            continue;
        }

        if (preg_match('/^[*-]\s+(.+)$/u', $line, $item_matches)) {
            if (!$list_open) {
                $html .= '<ul>';
                $list_open = true;
            }

            $html .= '<li>' . htmlspecialchars(trim($item_matches[1]), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</li>';
            continue;
        }

        if ($line === '') {
            if ($list_open) {
                $html .= '</ul>';
                $list_open = false;
            }

            continue;
        }

        if ($list_open) {
            $html .= '</ul>';
            $list_open = false;
        }

        $html .= '<p>' . htmlspecialchars($line, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
    }

    if ($list_open) {
        $html .= '</ul>';
    }

    return $html;
}

/**
 * Load release dates from a tab-separated version map.
 *
 * @param string $version_dates_path Path to the generated version date map.
 *
 * @return array<string, string>
 */
function load_release_dates(string $version_dates_path): array
{
    if ($version_dates_path === '') {
        return [];
    }

    if (!is_readable($version_dates_path)) {
        throw new RuntimeException('Unable to read the release version date map.');
    }

    $lines = file($version_dates_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if ($lines === false) {
        throw new RuntimeException('Failed to read the release version date map.');
    }

    $release_dates = [];

    foreach ($lines as $line) {
        $parts = explode("\t", $line, 2);
        $version = trim((string) ($parts[0] ?? ''));
        $release_date = trim((string) ($parts[1] ?? ''));

        if ($version === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $release_date)) {
            throw new RuntimeException('Invalid release version date map entry.');
        }

        $release_dates[$version] = $release_date;
    }

    return $release_dates;
}

if (realpath((string) ($_SERVER['SCRIPT_FILENAME'] ?? '')) !== __FILE__) {
    return;
}

$readme_path = (string) ($argv[1] ?? '');
$version_dates_path = (string) ($argv[2] ?? '');

if ($readme_path === '' || !is_readable($readme_path)) {
    fwrite(STDERR, "Unable to read readme.txt.\n");
    exit(1);
}

$readme_contents = file_get_contents($readme_path);

if ($readme_contents === false) {
    fwrite(STDERR, "Failed to read readme.txt.\n");
    exit(1);
}

try {
    $release_dates = load_release_dates($version_dates_path);
} catch (RuntimeException $exception) {
    fwrite(STDERR, $exception->getMessage() . "\n");
    exit(1);
}

echo render_readme_changelog($readme_contents, $release_dates);
