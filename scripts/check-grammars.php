<?php

require_once __DIR__ . '/helpers.php';

echo "Locating grammar files...\n";

/** @var array{ path: string, name: string, total: int, compatible: int, incompatible: array{ pattern: string, error: string } } */
$grammars = [];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(__DIR__ . '/../textmate-grammars-themes/packages/tm-grammars/grammars', RecursiveDirectoryIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if ($file->getExtension() !== 'json') {
        continue;
    }

    $grammars[] = [
        'path' => $file->getRealpath(),
        'name' => $file->getBasename('.json'),
        'total' => 0,
        'compatible' => 0,
        'incompatible' => [],
    ];
}

printf("Found %d grammar files.\n", count($grammars));

echo "Checking grammar files...\n";

$grammarPatternCollector = new GrammarPatternCollector();

foreach ($grammars as ['path' => $path, 'name' => $name, 'total' => &$total, 'compatible' => &$compatible, 'incompatible' => &$incompatible]) {
    echo "Checking {$name}\n";

    $json = json_decode(file_get_contents($path), associative: true);
    $patterns = $grammarPatternCollector->collect($json);
    
    printf("-> Found %d patterns in %s.\n", count($patterns), $name);

    $total = count($patterns);

    foreach ($patterns as $pattern) {
        try {
            test($pattern);

            $compatible++;
        } catch (RuntimeException $e) {
            // TextMate patterns can contain references to matching groups that do not technically exist in the pattern.
            if (str_contains($e->getMessage(), 'Compilation failed: reference to non-existent subpattern')) {
                $compatible++;
                continue;
            }

            $incompatible[] = [
                'pattern' => $pattern,
                'error' => str_replace('preg_match(): ', '', $e->getMessage()),
            ];
        }
    }

    printf("-> %d compatible patterns, %d incompatible patterns.\n", $compatible, count($incompatible));

    $output = __DIR__ . '/../compatibility/' . $name . '.json';

    printf("-> Writing results to %s...\n", $output);

    file_put_contents($output, json_encode([
        'name' => $name,
        'total' => $total,
        'compatible' => $compatible,
        'incompatible' => $incompatible,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
}

echo "Done!\n";
