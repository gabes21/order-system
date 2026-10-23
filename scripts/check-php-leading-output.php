<?php

declare(strict_types=1);

$root = dirname(__DIR__);

$excludedDirectories = [
    '.git',
    '.idea',
    '.vscode',
    'node_modules',
    'storage',
    'vendor',
];

$excludedPaths = [
    'bootstrap/cache',
    'public/build',
    'public/hot',
];

$files = [];
$iterator = new RecursiveIteratorIterator(
    new RecursiveCallbackFilterIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
        function (SplFileInfo $file, string $path) use ($root, $excludedDirectories, $excludedPaths): bool {
            if (!$file->isDir()) {
                return true;
            }

            $relativePath = str_replace('\\', '/', substr($path, strlen($root) + 1));

            if (in_array($file->getFilename(), $excludedDirectories, true)) {
                return false;
            }

            foreach ($excludedPaths as $excludedPath) {
                if ($relativePath === $excludedPath || str_starts_with($relativePath, $excludedPath.'/')) {
                    return false;
                }
            }

            return true;
        }
    )
);

foreach ($iterator as $file) {
    if (!$file->isFile() || strtolower($file->getExtension()) !== 'php') {
        continue;
    }

    if (str_ends_with(strtolower($file->getFilename()), '.blade.php')) {
        continue;
    }

    $files[] = $file->getPathname();
}

sort($files);

$failures = [];

foreach ($files as $file) {
    $contents = file_get_contents($file);

    if ($contents === false) {
        $failures[] = [str_replace('\\', '/', substr($file, strlen($root) + 1)), 'Unable to read file.'];
        continue;
    }

    if (str_starts_with($contents, "\xEF\xBB\xBF")) {
        $failures[] = [str_replace('\\', '/', substr($file, strlen($root) + 1)), 'File starts with a UTF-8 byte order mark (BOM).'];
        continue;
    }

    $openingTagPosition = strpos($contents, '<?php');

    if ($openingTagPosition === false) {
        $failures[] = [str_replace('\\', '/', substr($file, strlen($root) + 1)), 'File does not contain a <?php opening tag.'];
        continue;
    }

    if ($openingTagPosition === 0) {
        continue;
    }

    $prefix = substr($contents, 0, $openingTagPosition);

    if (str_starts_with($prefix, '#!')) {
        $prefix = substr($prefix, strpos($prefix, "\n") === false ? strlen($prefix) : strpos($prefix, "\n") + 1);
    }

    if (trim($prefix) !== '') {
        $failures[] = [str_replace('\\', '/', substr($file, strlen($root) + 1)), 'Unexpected output before the <?php opening tag.'];
        continue;
    }

    if ($prefix !== '') {
        $failures[] = [str_replace('\\', '/', substr($file, strlen($root) + 1)), 'Whitespace before the <?php opening tag will be sent as output.'];
    }
}

if ($failures !== []) {
    fwrite(STDERR, "Found PHP files with leading output:".PHP_EOL);

    foreach ($failures as [$path, $reason]) {
        fwrite(STDERR, sprintf('  - %s: %s%s', $path, $reason, PHP_EOL));
    }

    exit(1);
}

fwrite(STDOUT, sprintf('Checked %d PHP files: no leading output found.%s', count($files), PHP_EOL));

exit(0);
