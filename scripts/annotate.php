<?php

/**
 * Auto-Annotate Script v2 (Token-based)
 *
 * Scans all PHP files in app/ and adds PHPDoc blocks to methods
 * that don't already have one. Uses PHP tokenizer instead of
 * reflection — no Laravel bootstrap needed.
 *
 * Usage: php scripts/annotate.php
 */

$basePath = realpath(__DIR__ . '/../');
$appDir = $basePath . '/app';
$processed = 0;
$annotated = 0;
$filesModified = 0;
$skippedFiles = [];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($appDir, RecursiveDirectoryIterator::SKIP_DOTS)
);

foreach ($iterator as $fileInfo) {
    if (!$fileInfo->isFile() || $fileInfo->getExtension() !== 'php')
        continue;
    if (str_contains($fileInfo->getPathname(), '/DTOs/'))
        continue;

    $filePath = $fileInfo->getPathname();
    $processed++;

    try {
        $result = processFile($filePath);
        if ($result['modified']) {
            $filesModified++;
            $annotated += $result['methodsAnnotated'];
            $rel = str_replace($basePath . '/', '', $filePath);
            echo "[OK] {$rel} ({$result['methodsAnnotated']} methods, classDoc=" . ($result['classDocAdded'] ? 'yes' : 'no') . ")\n";
        }
    } catch (Throwable $e) {
        $rel = str_replace($basePath . '/', '', $filePath);
        $skippedFiles[] = "$rel: " . $e->getMessage();
    }
}

echo "\n=== Summary ===\n";
echo "Files scanned: $processed\n";
echo "Files modified: $filesModified\n";
echo "Methods annotated: $annotated\n";
if (!empty($skippedFiles)) {
    echo "Skipped/errors: " . count($skippedFiles) . "\n";
    foreach ($skippedFiles as $s)
        echo "  SKIP: $s\n";
}

// ──────────────────────────────────────────────────────────────
// Core Processing
// ──────────────────────────────────────────────────────────────

function processFile(string $filePath): array
{
    $content = file_get_contents($filePath);
    $lines = explode("\n", $content);
    $totalLines = count($lines);
    $modified = false;
    $methodsAnnotated = 0;
    $classDocAdded = false;

    // Detect the class/interface/trait/enum name and namespace
    $namespace = '';
    $className = '';
    if (preg_match('/namespace\s+([^;]+);/', $content, $m)) {
        $namespace = trim($m[1]);
    }
    if (preg_match('/(?:class|interface|trait|enum)\s+(\w+)/', $content, $m)) {
        $className = $m[1];
    }

    if (!$className)
        return ['modified' => false, 'methodsAnnotated' => 0, 'classDocAdded' => false];

    // ── Step 1: Add class-level docblock if missing ──
    // Find the class line
    $classLineIdx = null;
    for ($i = 0; $i < $totalLines; $i++) {
        if (preg_match('/^\s*(abstract\s+|final\s+)*(class|interface|trait|enum)\s+' . preg_quote($className) . '\b/', $lines[$i])) {
            $classLineIdx = $i;
            break;
        }
    }

    if ($classLineIdx !== null) {
        // Check if there's already a docblock above
        $hasClassDoc = false;
        $checkIdx = $classLineIdx - 1;
        // Skip blank lines
        while ($checkIdx >= 0 && trim($lines[$checkIdx]) === '')
            $checkIdx--;
        if ($checkIdx >= 0 && str_contains($lines[$checkIdx], '*/')) {
            $hasClassDoc = true;
        }

        if (!$hasClassDoc) {
            // Detect type
            $type = 'Class';
            if (preg_match('/interface\s/', $lines[$classLineIdx]))
                $type = 'Interface';
            elseif (preg_match('/trait\s/', $lines[$classLineIdx]))
                $type = 'Trait';
            elseif (preg_match('/enum\s/', $lines[$classLineIdx]))
                $type = 'Enum';

            // Detect parent class
            $parentInfo = '';
            if (preg_match('/extends\s+(\w+)/', $lines[$classLineIdx], $em)) {
                $parentInfo = "\n * Extends {$em[1]}.";
            }

            // Get indentation
            preg_match('/^(\s*)/', $lines[$classLineIdx], $indM);
            $indent = $indM[1] ?? '';

            $docblock = "{$indent}/**\n{$indent} * {$type} {$className}\n{$indent} *{$parentInfo}\n{$indent} * @package {$namespace}\n{$indent} */\n";

            array_splice($lines, $classLineIdx, 0, explode("\n", rtrim($docblock)));
            $totalLines = count($lines);
            $modified = true;
            $classDocAdded = true;
        }
    }

    // ── Step 2: Find all methods and add docblocks ──
    // Re-scan lines since we may have inserted class doc
    $i = 0;
    while ($i < count($lines)) {
        $line = $lines[$i];

        // Match method declarations: public/protected/private [static] function name(...)
        if (preg_match('/^(\s*)(public|protected|private)(\s+static)?\s+function\s+(\w+)\s*\(([^)]*)\)/', $line, $funcMatch)) {
            $indent = $funcMatch[1];
            $visibility = $funcMatch[2];
            $isStatic = !empty(trim($funcMatch[3] ?? ''));
            $methodName = $funcMatch[4];
            $paramsStr = trim($funcMatch[5]);

            // Check if the line continues on the next line (multi-line params)
            $fullLine = $line;
            $endLine = $i;
            if (!str_contains($fullLine, ')')) {
                // Multi-line parameter declaration
                while ($endLine + 1 < count($lines) && !str_contains($lines[$endLine], ')')) {
                    $endLine++;
                    $fullLine .= ' ' . trim($lines[$endLine]);
                }
                if (preg_match('/function\s+\w+\s*\(([^)]*)\)/', $fullLine, $fpMatch)) {
                    $paramsStr = trim($fpMatch[1]);
                }
            }

            // Detect return type
            $returnType = 'void';
            if (preg_match('/\)\s*:\s*([^{]+)/', $fullLine, $rtMatch)) {
                $returnType = trim($rtMatch[1]);
            }

            // Check if there's already a docblock above
            $hasDocBlock = false;
            $prevContentIdx = $i - 1;
            while ($prevContentIdx >= 0 && trim($lines[$prevContentIdx]) === '')
                $prevContentIdx--;
            if ($prevContentIdx >= 0 && str_contains($lines[$prevContentIdx], '*/')) {
                // Check if the docblock has @param or @return
                $dbStart = $prevContentIdx;
                while ($dbStart > 0 && !str_contains($lines[$dbStart], '/**'))
                    $dbStart--;
                $existingDoc = implode("\n", array_slice($lines, $dbStart, $prevContentIdx - $dbStart + 1));
                if (str_contains($existingDoc, '@param') || str_contains($existingDoc, '@return')) {
                    $hasDocBlock = true;
                }
            }

            if (!$hasDocBlock) {
                // Collect any single-line // comments above the method
                $commentLines = [];
                $commentStart = $i - 1;
                while ($commentStart >= 0 && preg_match('/^\s*\/\//', $lines[$commentStart])) {
                    array_unshift($commentLines, trim(preg_replace('/^\s*\/\/\s?/', '', $lines[$commentStart])));
                    $commentStart--;
                }

                // Check if the line above is a closing */ of an existing incomplete docblock
                $aboveIsDoc = false;
                if ($prevContentIdx >= 0 && str_contains($lines[$prevContentIdx], '*/') && empty($commentLines)) {
                    $aboveIsDoc = true;
                }

                if (!$aboveIsDoc) {
                    // Remove old // comments
                    $removeCount = count($commentLines);
                    if ($removeCount > 0) {
                        $removeStart = $i - $removeCount;
                        array_splice($lines, $removeStart, $removeCount);
                        $i -= $removeCount;
                    }

                    // Build docblock
                    $summary = generateSummary($methodName, $commentLines, $className, $namespace);
                    $docLines = ["{$indent}/**", "{$indent} * {$summary}", "{$indent} *"];

                    // Data flow annotation
                    $dataFlow = getDataFlow($methodName, $namespace, $paramsStr);
                    if ($dataFlow) {
                        $docLines[] = "{$indent} * {$dataFlow}";
                        $docLines[] = "{$indent} *";
                    }

                    // Parse parameters
                    if (!empty($paramsStr)) {
                        $params = parseParameters($paramsStr);
                        foreach ($params as $p) {
                            $desc = getParamDesc($p['name']);
                            $docLines[] = "{$indent} * @param  {$p['type']}  \${$p['name']}  {$desc}";
                        }
                    }

                    // Return type
                    if ($methodName !== '__construct') {
                        $retDesc = getReturnDesc($returnType);
                        $docLines[] = "{$indent} * @return {$returnType}  {$retDesc}";
                    }

                    $docLines[] = "{$indent} */";

                    // Insert docblock
                    $docblockLines = $docLines;
                    array_splice($lines, $i, 0, $docblockLines);
                    $i += count($docblockLines); // skip past what we inserted
                    $modified = true;
                    $methodsAnnotated++;
                }
            }
        }

        $i++;
    }

    if ($modified) {
        file_put_contents($filePath, implode("\n", $lines));
    }

    return ['modified' => $modified, 'methodsAnnotated' => $methodsAnnotated, 'classDocAdded' => $classDocAdded];
}

function parseParameters(string $paramsStr): array
{
    $params = [];
    // Split by comma but respect nested parentheses/brackets
    $parts = preg_split('/,(?![^(]*\))/', $paramsStr);
    foreach ($parts as $part) {
        $part = trim($part);
        if (empty($part))
            continue;

        $type = 'mixed';
        $name = '';
        $tokens = preg_split('/\s+/', $part);

        foreach ($tokens as $t) {
            if (str_starts_with($t, '$')) {
                $name = ltrim($t, '$');
                // Clean up things like $foo = 'default'
                $name = explode('=', $name)[0];
                $name = trim($name);
                break;
            } else {
                $type = $t;
            }
        }

        if ($name) {
            $params[] = ['type' => $type, 'name' => $name];
        }
    }
    return $params;
}

function generateSummary(string $name, array $comments, string $className, string $namespace): string
{
    if (!empty($comments)) {
        // Filter out empty @-tags
        $filtered = array_filter($comments, fn($c) => !str_starts_with(trim($c), '@'));
        if (!empty($filtered)) {
            return implode(' ', $filtered);
        }
    }

    $patterns = [
        '__construct' => "Create a new {$className} instance.",
        'index' => "Display a listing of the resource.",
        'create' => "Show the form for creating a new resource.",
        'store' => "Store a newly created resource in storage.",
        'show' => "Display the specified resource.",
        'edit' => "Show the form for editing the specified resource.",
        'update' => "Update the specified resource in storage.",
        'destroy' => "Remove the specified resource from storage.",
        'boot' => "Bootstrap the application services.",
        'register' => "Register the application services.",
        'handle' => "Handle the incoming request or job.",
        'render' => "Render the mailable content.",
        'build' => "Build the mailable message.",
        'rules' => "Get the validation rules that apply to the request.",
        'authorize' => "Determine if the user is authorized to make this request.",
        'model' => "Define the model for each imported row.",
        'map' => "Map and transform the imported row data.",
        'headings' => "Define the column headings for export.",
        'collection' => "Get the collection of data for export.",
        'query' => "Build the query for export data.",
    ];

    if (isset($patterns[$name]))
        return $patterns[$name];

    if (str_starts_with($name, 'get')) {
        $s = preg_replace('/([A-Z])/', ' $1', substr($name, 3));
        return "Get the" . strtolower($s) . ".";
    }
    if (str_starts_with($name, 'set')) {
        $s = preg_replace('/([A-Z])/', ' $1', substr($name, 3));
        return "Set the" . strtolower($s) . ".";
    }
    if (str_starts_with($name, 'is') || str_starts_with($name, 'has') || str_starts_with($name, 'can')) {
        $s = preg_replace('/([A-Z])/', ' $1', $name);
        return "Check" . strtolower($s) . ".";
    }
    if (str_starts_with($name, 'import')) {
        $s = preg_replace('/([A-Z])/', ' $1', substr($name, 6));
        return "Import" . strtolower($s) . " data from file.";
    }
    if (str_starts_with($name, 'export')) {
        $s = preg_replace('/([A-Z])/', ' $1', substr($name, 6));
        return "Export" . strtolower($s) . " data to file.";
    }
    if (str_starts_with($name, 'bulk')) {
        $s = preg_replace('/([A-Z])/', ' $1', substr($name, 4));
        return "Perform bulk" . strtolower($s) . " operation.";
    }
    if (str_starts_with($name, 'send')) {
        $s = preg_replace('/([A-Z])/', ' $1', substr($name, 4));
        return "Send" . strtolower($s) . ".";
    }
    if (str_starts_with($name, 'store')) {
        $s = preg_replace('/([A-Z])/', ' $1', substr($name, 5));
        return "Store" . strtolower($s) . " in the database.";
    }
    if (str_starts_with($name, 'delete') || str_starts_with($name, 'remove')) {
        $s = preg_replace('/([A-Z])/', ' $1', $name);
        return ucfirst(strtolower(trim($s))) . ".";
    }
    if (str_starts_with($name, 'update')) {
        $s = preg_replace('/([A-Z])/', ' $1', substr($name, 6));
        return "Update" . strtolower($s) . ".";
    }
    if (str_starts_with($name, 'view')) {
        $s = preg_replace('/([A-Z])/', ' $1', substr($name, 4));
        return "View" . strtolower($s) . ".";
    }
    if (str_starts_with($name, 'download')) {
        $s = preg_replace('/([A-Z])/', ' $1', substr($name, 8));
        return "Download" . strtolower($s) . ".";
    }
    if (str_starts_with($name, 'prepare')) {
        $s = preg_replace('/([A-Z])/', ' $1', substr($name, 7));
        return "Prepare" . strtolower($s) . " data.";
    }
    if (str_starts_with($name, 'calculate')) {
        $s = preg_replace('/([A-Z])/', ' $1', substr($name, 9));
        return "Calculate" . strtolower($s) . ".";
    }
    if (str_starts_with($name, 'format')) {
        $s = preg_replace('/([A-Z])/', ' $1', substr($name, 6));
        return "Format" . strtolower($s) . " for display.";
    }
    if (str_starts_with($name, 'assign')) {
        $s = preg_replace('/([A-Z])/', ' $1', substr($name, 6));
        return "Assign" . strtolower($s) . ".";
    }
    if (str_starts_with($name, 'log')) {
        $s = preg_replace('/([A-Z])/', ' $1', substr($name, 3));
        return "Log" . strtolower($s) . " information.";
    }
    if (str_starts_with($name, 'check')) {
        $s = preg_replace('/([A-Z])/', ' $1', substr($name, 5));
        return "Check" . strtolower($s) . ".";
    }
    if (str_starts_with($name, 'confirm')) {
        $s = preg_replace('/([A-Z])/', ' $1', substr($name, 7));
        return "Confirm" . strtolower($s) . ".";
    }
    if (str_starts_with($name, 'dispatch')) {
        $s = preg_replace('/([A-Z])/', ' $1', substr($name, 8));
        return "Dispatch" . strtolower($s) . ".";
    }
    if (str_starts_with($name, 'filter')) {
        $s = preg_replace('/([A-Z])/', ' $1', substr($name, 6));
        return "Filter" . strtolower($s) . ".";
    }
    if (str_starts_with($name, 'return')) {
        $s = preg_replace('/([A-Z])/', ' $1', substr($name, 6));
        return "Return" . strtolower($s) . ".";
    }
    if (str_starts_with($name, 'replace')) {
        $s = preg_replace('/([A-Z])/', ' $1', substr($name, 7));
        return "Replace" . strtolower($s) . ".";
    }
    if (str_starts_with($name, 'search')) {
        return "Search for matching records.";
    }

    $words = preg_replace('/([A-Z])/', ' $1', $name);
    return ucfirst(strtolower(trim($words))) . ".";
}

function getDataFlow(string $name, string $namespace, string $params): ?string
{
    $hasRequest = str_contains($params, 'Request');

    if (str_contains($namespace, 'Controller') && $hasRequest) {
        if (str_contains($name, 'Data') || str_contains($name, 'data')) {
            return "Data flow: HTTP Request → Controller → JSON Response";
        }
        if (in_array($name, ['store', 'update', 'import', 'destroy'])) {
            return "Data flow: HTTP Request → Validation → Database → Redirect with status";
        }
        if (in_array($name, ['index', 'show', 'create', 'edit'])) {
            return "Data flow: HTTP Request → Database Query → Blade View";
        }
        return "Data flow: HTTP Request → Processing → Response";
    }

    if (str_contains($namespace, 'Service') || str_contains($namespace, 'Repository')) {
        return "Data flow: Called by Controller → Database interaction → Returns result";
    }

    if (str_contains($namespace, 'Import') && in_array($name, ['model', 'map'])) {
        return "Data flow: Excel Row → Validation → Eloquent Model";
    }

    if (str_contains($namespace, 'Export') && in_array($name, ['collection', 'query'])) {
        return "Data flow: Database Query → Collection → Excel Output";
    }

    return null;
}

function getParamDesc(string $name): string
{
    $map = [
        'request' => 'The incoming HTTP request',
        'id' => 'The resource identifier',
        'storeId' => 'The store identifier',
        'projectId' => 'The project identifier',
        'vendorId' => 'The vendor identifier',
        'vendor_id' => 'The vendor identifier',
        'userId' => 'The user identifier',
        'user_id' => 'The user identifier',
        'siteId' => 'The site identifier',
        'meetId' => 'The meeting identifier',
        'poleId' => 'The pole identifier',
        'taskId' => 'The task identifier',
        'user' => 'The user model instance',
        'project' => 'The project model instance',
        'item' => 'The item model instance',
        'file' => 'The uploaded file',
        'data' => 'The input data array',
        'status' => 'The status value',
        'type' => 'The type identifier',
        'filter' => 'The filter criteria',
        'query' => 'The search query string',
        'page' => 'The page number for pagination',
        'limit' => 'The number of records to return',
        'perPage' => 'Number of results per page',
        'district' => 'The district identifier or name',
        'block' => 'The block identifier or name',
        'panchayat' => 'The panchayat identifier or name',
        'ward' => 'The ward identifier or number',
        'filename' => 'The name of the file',
        'engineer_id' => 'The engineer identifier',
        'manager_id' => 'The manager identifier',
        'managerId' => 'The manager identifier',
        'staffId' => 'The staff identifier',
        'jobId' => 'The background job identifier',
        'meet' => 'The meeting model instance',
        'point' => 'The discussion point model instance',
        'followUp' => 'The follow-up model instance',
        'key' => 'The lookup key',
        'value' => 'The value to set',
        'row' => 'The imported data row',
        'activityLog' => 'The activity log record',
        'any' => 'Catch-all route parameter',
    ];

    return $map[$name] ?? '';
}

function getReturnDesc(string $type): string
{
    if (str_contains($type, 'JsonResponse'))
        return 'JSON response with result data';
    if (str_contains($type, 'RedirectResponse'))
        return 'Redirect to previous or specified page';
    if (str_contains($type, 'View') || str_contains($type, 'view'))
        return 'The rendered view';
    if (str_contains($type, 'Collection'))
        return 'Collection of results';
    if (str_contains($type, 'StreamedResponse') || str_contains($type, 'BinaryFileResponse'))
        return 'File download response';
    if ($type === 'void')
        return '';
    if ($type === 'bool')
        return 'Success status';
    if ($type === 'array')
        return 'Result data array';
    if ($type === 'int')
        return 'The resulting integer value';
    if ($type === 'string')
        return 'The resulting string value';
    if ($type === 'self' || $type === 'static')
        return 'The instance for method chaining';
    return '';
}
