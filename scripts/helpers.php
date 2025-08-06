<?php

/**
 * Dump out values and exit.
 */
function dd(mixed ...$values) {
    foreach ($values as $value) {
        var_dump($value);
    }
    
    exit(1);
}

/**
 * Test to see if the given RegEx pattern is compatible with the current PHP version.
 * 
 * @throws Exception
 */
function test(string $pattern): void {
    set_error_handler(function ($errno, $errstr) {
        throw new RuntimeException($errstr, $errno);
    });

    if (str_contains($pattern, '/') && !str_contains($pattern, '\\/')) {
        $pattern = str_replace('/', '\\/', $pattern);
    }

    preg_match("/{$pattern}/u", '');

    restore_error_handler();
}

class GrammarPatternCollector
{
    /**
     * Collect RegEx patterns from the given grammar file.
     */
    public function collect(mixed $grammar): array
    {
        if (! $grammar || ! is_array($grammar)) {
            return [];
        }

        $patterns = [];
        
        if (array_is_list($grammar)) {
            foreach ($grammar as $rule) {
                $patterns = array_merge($patterns, $this->collect($rule));
            }

            return $patterns;
        }

        if (isset($grammar['match'])) {
            $patterns[] = $grammar['match'];
        }
        
        if (isset($grammar['begin'])) {
            $patterns[] = $grammar['begin'];
        }
        
        if (isset($grammar['end'])) {
            $patterns[] = $grammar['end'];
        }
        
        if (isset($grammar['while'])) {
            $patterns[] = $grammar['while'];
        }
        
        if (isset($grammar['patterns'])) {
            foreach ($grammar['patterns'] as $pattern) {
                $patterns = array_merge($patterns, $this->collect($pattern));
            }
        }
        
        if (isset($grammar['captures'])) {
            foreach ($grammar['captures'] as $capture) {
                $patterns = array_merge($patterns, $this->collect($capture));
            }
        }
        
        if (isset($grammar['beginCaptures'])) {
            foreach ($grammar['beginCaptures'] as $capture) {
                $patterns = array_merge($patterns, $this->collect($capture));
            }
        }
        
        if (isset($grammar['endCaptures'])) {
            foreach ($grammar['endCaptures'] as $capture) {
                $patterns = array_merge($patterns, $this->collect($capture));
            }
        }
        
        if (isset($grammar['injections'])) {
            foreach ($grammar['injections'] as $injection) {
                $patterns = array_merge($patterns, $this->collect($injection));
            }
        }

        foreach ($grammar['repository'] ?? [] as $repository) {
            $patterns = array_merge($patterns, $this->collect($repository));
        }

        return $patterns;
    }
}

class MarkdownTable
{
    /**
     * @param array<array<mixed>> $data
     * @param array<string, string> $headers
     */
    public function generate(array $data, array $headers = []): string
    {
        if ($data === []) {
            return '';
        }

        $table = '';

        if ($headers === []) {
            $headers = array_combine(array_keys($data[0]), array_keys($data[0]));
        }

        $headerLine = '| ' . implode(' | ', $headers) . ' |' . PHP_EOL;
        $separatorLine = '| ' . str_repeat('--- | ', count($headers)) . PHP_EOL;
        $table .= $headerLine . $separatorLine;

        foreach ($data as $row) {
            $rowData = [];

            foreach (array_keys($headers) as $key) {
                $rowData[] = $row[$key] ?? '';
            }

            $rowLine = '| ' . implode(' | ', $rowData) . ' |' . PHP_EOL;
            $table .= $rowLine;
        }

        $table = trim($table);

        return $table;
    }
}
