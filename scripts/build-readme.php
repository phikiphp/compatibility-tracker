<?php

require_once __DIR__ . '/helpers.php';

$compatibilities = [];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(__DIR__ . '/../compatibility', RecursiveDirectoryIterator::SKIP_DOTS),
);

/** @var SplFileInfo $file */
foreach ($iterator as $file) {
    if ($file->getExtension() !== 'json') {
        continue;
    }

    $data = json_decode(file_get_contents($file->getRealPath()), true);
    $data['incompatible'] = count($data['incompatible']);

    $compatibilities[] = $data;
}

$markdownTable = new MarkdownTable();
$markdown = $markdownTable->generate($compatibilities, [
    'name' => 'Name',
    'total' => 'No. patterns',
    'compatible' => 'No. compatible patterns',
    'incompatible' => 'No. incompatible patterns',
]);

$readme = file_get_contents(__DIR__ . '/../meta/README-HEAD.md');
$readme .= PHP_EOL . $markdown . PHP_EOL;
$readme .= file_get_contents(__DIR__ . '/../meta/README-TAIL.md');

file_put_contents(__DIR__ . '/../README.md', $readme);

echo "README generated.\n";
