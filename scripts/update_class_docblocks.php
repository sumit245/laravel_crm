<?php
/**
 * Script: Update class-level PHPDoc blocks with real business context.
 *
 * Reads config/docs_business_context.php and rewrites the class-level
 * docblock in each matching PHP file to include:
 *   - Business summary as the main description
 *   - @business-domain tag
 *   - @data-flow tag
 *   - @depends-on tag with listed classes
 *
 * Usage:  php scripts/update_class_docblocks.php
 *         php scripts/update_class_docblocks.php --dry-run
 */

$dryRun = in_array('--dry-run', $argv);
$basePath = dirname(__DIR__);
$configPath = $basePath . '/config/docs_business_context.php';

if (!file_exists($configPath)) {
    echo "ERROR: config/docs_business_context.php not found.\n";
    exit(1);
}

$businessContext = require $configPath;
echo "Loaded " . count($businessContext) . " business context entries.\n";

// Discover all PHP files under app/
$appDir = $basePath . '/app';
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($appDir, RecursiveDirectoryIterator::SKIP_DOTS)
);

$stats = ['scanned' => 0, 'matched' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => 0];

foreach ($iterator as $fileInfo) {
    if (!$fileInfo->isFile() || $fileInfo->getExtension() !== 'php') {
        continue;
    }

    $filePath = $fileInfo->getPathname();
    $stats['scanned']++;

    $content = file_get_contents($filePath);

    // Extract class/interface/trait/enum name from the file
    if (!preg_match('/(?:class|interface|trait|enum)\s+(\w+)/', $content, $classMatch)) {
        continue; // Not a class file
    }
    $className = $classMatch[1];

    // Try to find business context for this class
    $bctx = null;
    if (isset($businessContext[$className])) {
        $bctx = $businessContext[$className];
    } else {
        // Check API-prefixed key
        if (strpos($filePath, '/API/') !== false) {
            $apiKey = 'API\\' . $className;
            if (isset($businessContext[$apiKey])) {
                $bctx = $businessContext[$apiKey];
            }
        }
    }

    if (!$bctx) {
        continue; // No business context for this class
    }

    $stats['matched']++;

    // Extract namespace
    $namespace = '';
    if (preg_match('/namespace\s+([^;]+);/', $content, $nsMatch)) {
        $namespace = trim($nsMatch[1]);
    }

    // Determine class type keyword
    if (preg_match('/(class|interface|trait|enum)\s+' . preg_quote($className) . '/', $content, $typeMatch)) {
        $classType = $typeMatch[1];
    } else {
        $classType = 'class';
    }

    // Determine what it extends/implements
    $extends = '';
    if (preg_match('/class\s+' . preg_quote($className) . '\s+extends\s+(\w+)/', $content, $extMatch)) {
        $extends = $extMatch[1];
    }
    $implements = [];
    if (preg_match('/class\s+' . preg_quote($className) . '[^{]*implements\s+([^{]+)/', $content, $implMatch)) {
        $implements = array_map('trim', explode(',', $implMatch[1]));
    }

    // Build the new docblock
    $lines = [];
    $lines[] = '/**';

    // Business summary as the main class description
    $summary = $bctx['business_summary'] ?? '';
    if ($summary) {
        // Word-wrap at ~100 chars per line
        $wrapped = wordwrap($summary, 95, "\n");
        foreach (explode("\n", $wrapped) as $line) {
            $lines[] = ' * ' . trim($line);
        }
    }

    $lines[] = ' *';

    // Data flow
    if (!empty($bctx['data_flow'])) {
        $lines[] = ' * Data Flow:';
        $flowWrapped = wordwrap($bctx['data_flow'], 90, "\n");
        foreach (explode("\n", $flowWrapped) as $line) {
            $lines[] = ' *   ' . trim($line);
        }
        $lines[] = ' *';
    }

    // Dependencies
    if (!empty($bctx['depends_on'])) {
        $deps = implode(', ', $bctx['depends_on']);
        $lines[] = ' * @depends-on ' . $deps;
    }

    // Business domain
    if (!empty($bctx['business_domain'])) {
        $lines[] = ' * @business-domain ' . $bctx['business_domain'];
    }

    // Standard tags
    if ($namespace) {
        $lines[] = ' * @package ' . $namespace;
    }

    $lines[] = ' */';
    $newDocblock = implode("\n", $lines);

    // Now replace the existing class docblock
    // Strategy: Find the docblock immediately before the class/interface/trait/enum declaration
    $pattern = '/\/\*\*[\s\S]*?\*\/\s*\n(\s*(?:abstract\s+|final\s+)?(?:class|interface|trait|enum)\s+' . preg_quote($className) . ')/';

    if (preg_match($pattern, $content, $docMatch, PREG_OFFSET_CAPTURE)) {
        // Found existing docblock before class declaration
        $fullMatch = $docMatch[0][0];
        $offset = $docMatch[0][1];
        $classDecl = $docMatch[1][0]; // The class declaration line

        $newContent = substr($content, 0, $offset) . $newDocblock . "\n" . $classDecl . substr($content, $offset + strlen($fullMatch));
    } else {
        // No docblock found — insert one before the class declaration
        $classPattern = '/(\s*(?:abstract\s+|final\s+)?(?:class|interface|trait|enum)\s+' . preg_quote($className) . ')/';
        if (preg_match($classPattern, $content, $classLineMatch, PREG_OFFSET_CAPTURE)) {
            $offset = $classLineMatch[0][1];
            $classDecl = $classLineMatch[0][0];
            $newContent = substr($content, 0, $offset) . "\n" . $newDocblock . "\n" . ltrim($classDecl) . substr($content, $offset + strlen($classDecl));
        } else {
            echo "  SKIP: Could not locate class declaration in {$filePath}\n";
            $stats['skipped']++;
            continue;
        }
    }

    if ($dryRun) {
        echo "  [DRY-RUN] Would update: " . str_replace($basePath . '/', '', $filePath) . " ({$className})\n";
        $stats['updated']++;
    } else {
        $result = file_put_contents($filePath, $newContent);
        if ($result === false) {
            echo "  ERROR: Failed to write: {$filePath}\n";
            $stats['errors']++;
        } else {
            $relPath = str_replace($basePath . '/', '', $filePath);
            echo "  ✓ Updated: {$relPath} ({$className})\n";
            $stats['updated']++;
        }
    }
}

echo "\n=== Summary ===\n";
echo "Scanned: {$stats['scanned']} files\n";
echo "Matched: {$stats['matched']} files with business context\n";
echo "Updated: {$stats['updated']} files\n";
echo "Skipped: {$stats['skipped']} files\n";
echo "Errors:  {$stats['errors']} files\n";

if ($dryRun) {
    echo "\n(Dry run — no files were modified. Run without --dry-run to apply changes.)\n";
}
