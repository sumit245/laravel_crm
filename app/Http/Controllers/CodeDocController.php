<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;

/**
 * CodeDocController
 *
 * Provides a browsable code-level documentation interface at /docs/code.
 * Scans the app/ directory recursively, uses PHP reflection to extract
 * class metadata, method signatures, parameters, return types, and docblocks.
 *
 * This controller does NOT require authentication so documentation is always accessible.
 *
 * @package App\Http\Controllers
 */
class CodeDocController extends Controller
{
    /**
     * Base path for scanning source files.
     *
     * @var string
     */
    protected string $basePath;

    /**
     * Ordered list of directory categories to document.
     *
     * @var array<string, string>
     */
    protected array $categories = [
        'Http/Controllers' => 'Web Controllers',
        'Http/Controllers/API' => 'API Controllers',
        'Http/Controllers/Auth' => 'Auth Controllers',
        'Http/Middleware' => 'Middleware',
        'Http/Requests' => 'Form Requests',
        'Models' => 'Models',
        'Services' => 'Services',
        'Repositories' => 'Repositories',
        'Contracts' => 'Contracts / Interfaces',
        'Imports' => 'Excel Imports',
        'Exports' => 'Excel Exports',
        'Helpers' => 'Helpers',
        'Enums' => 'Enums',
        'Jobs' => 'Queue Jobs',
        'Mail' => 'Mailables',
        'Policies' => 'Policies',
        'Traits' => 'Traits',
        'Providers' => 'Service Providers',
        'Console' => 'Console Commands',
        'Exceptions' => 'Exceptions',
    ];

    /**
     * Create a new CodeDocController instance.
     * Sets the base path to the app/ directory.
     */
    /**
     * Business context map loaded from config/docs_business_context.php
     * @var array
     */
    protected array $businessContext = [];

    public function __construct()
    {
        $this->basePath = app_path();
        $this->businessContext = config('docs_business_context', []);
    }

    /**
     * Resolve business context for a given class name.
     * Tries exact match, short name, and API-prefixed names.
     */
    protected function getBusinessContext(?string $className): array
    {
        if (!$className)
            return [];

        // Try exact match first
        if (isset($this->businessContext[$className])) {
            return $this->businessContext[$className];
        }

        // Try short class name (without namespace)
        $short = class_basename($className);
        if (isset($this->businessContext[$short])) {
            return $this->businessContext[$short];
        }

        // Try API-prefixed key (e.g., API\LoginController)
        if (str_contains($className, 'API\\')) {
            $apiKey = 'API\\' . $short;
            if (isset($this->businessContext[$apiKey])) {
                return $this->businessContext[$apiKey];
            }
        }

        return [];
    }

    /**
     * Display the documentation dashboard / landing page.
     *
     * Scans all categories, counts files and classes, and renders
     * a summary overview with navigation links.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $tree = $this->buildNavigationTree();
        $stats = $this->buildStats($tree);
        $routeSummary = $this->getRouteSummary();

        return view('docs.index', compact('tree', 'stats', 'routeSummary'));
    }

    /**
     * Display documentation for a specific category or a specific file.
     *
     * When only $category is given, lists all files in that category with
     * quick summaries. When $file is also given, renders detailed documentation
     * for that single file including method tables and source code.
     *
     * @param  string      $category  The category slug (e.g., 'Models', 'Http-Controllers')
     * @param  string|null $file      Optional file name without extension (e.g., 'StoreController')
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show(string $category, ?string $file = null)
    {
        $tree = $this->buildNavigationTree();
        $categoryPath = str_replace('-', '/', $category);

        // Resolve matching category key
        $matchedKey = null;
        foreach ($this->categories as $dirPath => $label) {
            if ($this->slugify($dirPath) === $category) {
                $matchedKey = $dirPath;
                break;
            }
        }

        if (!$matchedKey) {
            abort(404, "Category not found: {$category}");
        }

        $categoryLabel = $this->categories[$matchedKey];
        $files = $this->getFilesForCategory($matchedKey);

        if ($file) {
            // Show single file documentation
            $fileData = $this->getFileDocumentation($matchedKey, $file);
            if (!$fileData) {
                abort(404, "File not found: {$file}");
            }
            return view('docs.show', compact('tree', 'categoryLabel', 'matchedKey', 'fileData', 'file', 'category'));
        }

        // Show category listing
        $fileSummaries = [];
        foreach ($files as $f) {
            $fileSummaries[] = $this->getFileSummary($matchedKey, $f);
        }

        return view('docs.category', compact('tree', 'categoryLabel', 'matchedKey', 'fileSummaries', 'category'));
    }

    // ─────────────────────────────────────────────────────────────
    //  Navigation Tree
    // ─────────────────────────────────────────────────────────────

    /**
     * Build the full navigation tree for the sidebar.
     *
     * Returns an array keyed by category slug, each containing the
     * label and list of PHP files found in that category directory.
     *
     * @return array<string, array{label: string, files: string[]}>
     */
    protected function buildNavigationTree(): array
    {
        $tree = [];
        foreach ($this->categories as $dirPath => $label) {
            $files = $this->getFilesForCategory($dirPath);
            if (!empty($files)) {
                $slug = $this->slugify($dirPath);
                $tree[$slug] = [
                    'label' => $label,
                    'dirPath' => $dirPath,
                    'files' => $files,
                ];
            }
        }
        return $tree;
    }

    /**
     * Get all PHP file basenames (without extension) for a category directory.
     *
     * Scans the directory non-recursively for the top-level categories,
     * and recursively for nested categories like Services/ and Repositories/.
     *
     * @param  string $dirPath  Relative path under app/ (e.g., 'Models')
     * @return string[]         Sorted list of file basenames
     */
    protected function getFilesForCategory(string $dirPath): array
    {
        $fullPath = $this->basePath . '/' . $dirPath;
        if (!is_dir($fullPath)) {
            return [];
        }

        $files = [];
        // For categories that contain subdirectories with single files (Services, Repositories, etc.)
        $nestedCategories = ['Services', 'Repositories', 'Contracts', 'Http/Requests', 'Console'];
        $isNested = false;
        foreach ($nestedCategories as $nc) {
            if (str_starts_with($dirPath, $nc)) {
                $isNested = true;
                break;
            }
        }

        // For Controllers directories that have subdirectories (API, Auth), only get direct children
        if ($dirPath === 'Http/Controllers') {
            // Get only direct PHP files, not subdirectory files
            foreach (glob($fullPath . '/*.php') as $filePath) {
                $files[] = pathinfo($filePath, PATHINFO_FILENAME);
            }
        } else if ($isNested) {
            // Recursively get all PHP files
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($fullPath, \RecursiveDirectoryIterator::SKIP_DOTS)
            );
            foreach ($iterator as $fileInfo) {
                if ($fileInfo->isFile() && $fileInfo->getExtension() === 'php') {
                    // Build relative name with subdirectory prefix
                    $relativePath = str_replace($fullPath . '/', '', $fileInfo->getPathname());
                    $name = str_replace('.php', '', $relativePath);
                    $name = str_replace('/', '.', $name);
                    $files[] = $name;
                }
            }
        } else {
            foreach (glob($fullPath . '/*.php') as $filePath) {
                $files[] = pathinfo($filePath, PATHINFO_FILENAME);
            }
        }

        sort($files);
        return $files;
    }

    // ─────────────────────────────────────────────────────────────
    //  File Documentation
    // ─────────────────────────────────────────────────────────────

    /**
     * Build detailed documentation data for a single PHP file.
     *
     * Uses ReflectionClass to extract: namespace, class docblock,
     * constants, properties, and all methods with their full signatures.
     *
     * @param  string $categoryDir  Category directory path (e.g., 'Models')
     * @param  string $fileName     File basename without extension (e.g., 'User')
     * @return array|null           Documentation data array, or null if file not found
     */
    protected function getFileDocumentation(string $categoryDir, string $fileName): ?array
    {
        $filePath = $this->resolveFilePath($categoryDir, $fileName);
        if (!$filePath || !file_exists($filePath)) {
            return null;
        }

        $data = [
            'fileName' => $fileName,
            'filePath' => str_replace($this->basePath . '/', 'app/', $filePath),
            'fullPath' => $filePath,
            'fileSize' => filesize($filePath),
            'lastModified' => date('Y-m-d H:i:s', filemtime($filePath)),
            'sourceCode' => file_get_contents($filePath),
            'namespace' => null,
            'className' => null,
            'classDocblock' => null,
            'parentClass' => null,
            'interfaces' => [],
            'traits' => [],
            'constants' => [],
            'properties' => [],
            'methods' => [],
            'isClass' => false,
            'businessContext' => [],
        ];

        // Try to get the fully qualified class name
        $fqcn = $this->resolveFQCN($filePath);

        if ($fqcn && class_exists($fqcn)) {
            try {
                $ref = new ReflectionClass($fqcn);
                $data['isClass'] = true;
                $data['namespace'] = $ref->getNamespaceName();
                $data['className'] = $ref->getShortName();
                $data['classDocblock'] = $this->parseDocblock($ref->getDocComment() ?: '');
                $data['parentClass'] = $ref->getParentClass() ? $ref->getParentClass()->getName() : null;
                $data['interfaces'] = array_values(array_map(fn($i) => $i->getName(), $ref->getInterfaces()));
                $data['traits'] = array_values(array_map(fn($t) => $t->getName(), $ref->getTraits()));

                // Constants
                foreach ($ref->getReflectionConstants() as $const) {
                    if ($const->getDeclaringClass()->getName() === $fqcn) {
                        $data['constants'][] = [
                            'name' => $const->getName(),
                            'value' => $this->formatValue($const->getValue()),
                            'visibility' => $this->getConstVisibility($const),
                        ];
                    }
                }

                // Properties
                foreach ($ref->getProperties() as $prop) {
                    if ($prop->getDeclaringClass()->getName() === $fqcn) {
                        $data['properties'][] = [
                            'name' => '$' . $prop->getName(),
                            'visibility' => $this->getVisibility($prop),
                            'type' => $prop->hasType() ? $prop->getType()->__toString() : 'mixed',
                            'docblock' => $this->parseDocblock($prop->getDocComment() ?: ''),
                            'isStatic' => $prop->isStatic(),
                        ];
                    }
                }

                // Methods
                foreach ($ref->getMethods() as $method) {
                    if ($method->getDeclaringClass()->getName() === $fqcn) {
                        $data['methods'][] = $this->extractMethodData($method);
                    }
                }
            } catch (\Throwable $e) {
                // If reflection fails, still show the source
                $data['reflectionError'] = $e->getMessage();
            }
        } elseif ($fqcn && (interface_exists($fqcn) || trait_exists($fqcn) || enum_exists($fqcn))) {
            try {
                $ref = new ReflectionClass($fqcn);
                $data['isClass'] = true;
                $data['namespace'] = $ref->getNamespaceName();
                $data['className'] = $ref->getShortName();
                $data['classDocblock'] = $this->parseDocblock($ref->getDocComment() ?: '');

                if ($ref->isInterface()) {
                    $data['classType'] = 'interface';
                } elseif ($ref->isTrait()) {
                    $data['classType'] = 'trait';
                } elseif ($ref->isEnum()) {
                    $data['classType'] = 'enum';
                }

                foreach ($ref->getMethods() as $method) {
                    if ($method->getDeclaringClass()->getName() === $fqcn) {
                        $data['methods'][] = $this->extractMethodData($method);
                    }
                }
            } catch (\Throwable $e) {
                $data['reflectionError'] = $e->getMessage();
            }
        }

        // Merge business context
        $data['businessContext'] = $this->getBusinessContext($data['className'] ?? $fileName);

        return $data;
    }

    /**
     * Extract detailed data from a single ReflectionMethod.
     *
     * Parses the method's docblock, parameters, return type, and visibility.
     *
     * @param  ReflectionMethod $method  The method to extract data from
     * @return array  Associative array with method documentation data
     */
    protected function extractMethodData(ReflectionMethod $method): array
    {
        $docblock = $this->parseDocblock($method->getDocComment() ?: '');

        $params = [];
        foreach ($method->getParameters() as $param) {
            $paramType = $param->hasType() ? $param->getType()->__toString() : 'mixed';
            $paramDoc = $docblock['params'][$param->getName()] ?? '';
            $params[] = [
                'name' => '$' . $param->getName(),
                'type' => $paramType,
                'hasDefault' => $param->isDefaultValueAvailable(),
                'default' => $param->isDefaultValueAvailable() ? $this->formatValue($param->getDefaultValue()) : null,
                'isNullable' => $param->allowsNull(),
                'description' => $paramDoc,
            ];
        }

        $returnType = 'void';
        if ($method->hasReturnType()) {
            $returnType = $method->getReturnType()->__toString();
        } elseif (!empty($docblock['return'])) {
            $returnType = $docblock['return'];
        }

        return [
            'name' => $method->getName(),
            'visibility' => $method->isPublic() ? 'public' : ($method->isProtected() ? 'protected' : 'private'),
            'isStatic' => $method->isStatic(),
            'isAbstract' => $method->isAbstract(),
            'parameters' => $params,
            'returnType' => $returnType,
            'summary' => $docblock['summary'] ?? '',
            'description' => $docblock['description'] ?? '',
            'startLine' => $method->getStartLine(),
            'endLine' => $method->getEndLine(),
        ];
    }

    /**
     * Get a brief summary for a file (used in category listing).
     *
     * Returns the file name, class docblock summary, method count, and file size.
     *
     * @param  string $categoryDir  Category directory path
     * @param  string $fileName     File basename without extension
     * @return array  Summary data for the file
     */
    protected function getFileSummary(string $categoryDir, string $fileName): array
    {
        $filePath = $this->resolveFilePath($categoryDir, $fileName);
        $summary = [
            'name' => $fileName,
            'path' => $filePath ? str_replace($this->basePath . '/', 'app/', $filePath) : 'unknown',
            'size' => $filePath ? $this->formatFileSize(filesize($filePath)) : '0 B',
            'methodCount' => 0,
            'classSummary' => '',
            'classType' => 'class',
            'businessContext' => [],
        ];

        if (!$filePath)
            return $summary;

        $fqcn = $this->resolveFQCN($filePath);
        $resolvedClassName = null;
        if ($fqcn && (class_exists($fqcn) || interface_exists($fqcn) || trait_exists($fqcn) || enum_exists($fqcn))) {
            try {
                $ref = new ReflectionClass($fqcn);
                $resolvedClassName = $ref->getShortName();
                $docblock = $this->parseDocblock($ref->getDocComment() ?: '');
                $summary['classSummary'] = $docblock['summary'] ?? '';
                $summary['methodCount'] = count(array_filter(
                    $ref->getMethods(),
                    fn($m) => $m->getDeclaringClass()->getName() === $fqcn
                ));
                if ($ref->isInterface())
                    $summary['classType'] = 'interface';
                elseif ($ref->isTrait())
                    $summary['classType'] = 'trait';
                elseif ($ref->isEnum())
                    $summary['classType'] = 'enum';
            } catch (\Throwable $e) {
                // Ignore reflection errors for summary
            }
        }

        // Merge business context — use it as the primary summary if available
        $bctx = $this->getBusinessContext($resolvedClassName ?? $fileName);
        $summary['businessContext'] = $bctx;
        if (!empty($bctx['business_summary'])) {
            $summary['classSummary'] = $bctx['business_summary'];
        }

        return $summary;
    }

    // ─────────────────────────────────────────────────────────────
    //  Docblock Parsing
    // ─────────────────────────────────────────────────────────────

    /**
     * Parse a PHPDoc docblock string into structured data.
     *
     * Extracts the summary line, longer description, @param tags,
     * @return tag, and @throws tags from the raw docblock.
     *
     * @param  string $docComment  Raw docblock string including comment markers
     * @return array  Parsed docblock with keys: summary, description, params, return, throws
     */
    protected function parseDocblock(string $docComment): array
    {
        $result = [
            'summary' => '',
            'description' => '',
            'params' => [],
            'return' => '',
            'throws' => [],
        ];

        if (empty($docComment))
            return $result;

        // Remove comment markers
        $lines = preg_split('/\r?\n/', $docComment);
        $cleaned = [];
        foreach ($lines as $line) {
            $line = preg_replace('#^\s*/?\*+/?\s?#', '', $line);
            $cleaned[] = $line;
        }

        $text = implode("\n", $cleaned);

        // Split at first @tag
        $parts = preg_split('/\n\s*(?=@)/', $text, 2);
        $narrative = trim($parts[0] ?? '');
        $tagBlock = $parts[1] ?? '';

        // Summary is first non-empty line
        $narrativeLines = array_filter(explode("\n", $narrative), fn($l) => trim($l) !== '');
        if (!empty($narrativeLines)) {
            $result['summary'] = trim(array_shift($narrativeLines));
            $result['description'] = trim(implode("\n", $narrativeLines));
        }

        // Parse tags
        if (!empty($tagBlock)) {
            preg_match_all('/@param\s+(\S+)\s+\$(\S+)\s*(.*)/m', $tagBlock, $paramMatches, PREG_SET_ORDER);
            foreach ($paramMatches as $m) {
                $result['params'][$m[2]] = trim($m[3]);
            }

            if (preg_match('/@return\s+(\S+)\s*(.*)/m', $tagBlock, $returnMatch)) {
                $result['return'] = trim($returnMatch[1] . ' ' . ($returnMatch[2] ?? ''));
            }

            preg_match_all('/@throws\s+(\S+)\s*(.*)/m', $tagBlock, $throwsMatches, PREG_SET_ORDER);
            foreach ($throwsMatches as $m) {
                $result['throws'][] = trim($m[1] . ' ' . ($m[2] ?? ''));
            }
        }

        return $result;
    }

    // ─────────────────────────────────────────────────────────────
    //  Stats & Route Summary
    // ─────────────────────────────────────────────────────────────

    /**
     * Build aggregate statistics from the navigation tree.
     *
     * Counts total files, total methods, and per-category breakdowns.
     *
     * @param  array $tree  Navigation tree from buildNavigationTree()
     * @return array  Statistics including totalFiles, totalMethods, categories
     */
    protected function buildStats(array $tree): array
    {
        $totalFiles = 0;
        $totalMethods = 0;
        $categoryStats = [];

        foreach ($tree as $slug => $cat) {
            $fileCount = count($cat['files']);
            $totalFiles += $fileCount;
            $methodCount = 0;

            foreach ($cat['files'] as $f) {
                $filePath = $this->resolveFilePath($cat['dirPath'], $f);
                if ($filePath) {
                    $fqcn = $this->resolveFQCN($filePath);
                    if ($fqcn && (class_exists($fqcn) || interface_exists($fqcn) || trait_exists($fqcn) || enum_exists($fqcn))) {
                        try {
                            $ref = new ReflectionClass($fqcn);
                            $methodCount += count(array_filter(
                                $ref->getMethods(),
                                fn($m) => $m->getDeclaringClass()->getName() === $fqcn
                            ));
                        } catch (\Throwable $e) {
                        }
                    }
                }
            }

            $totalMethods += $methodCount;
            $categoryStats[] = [
                'slug' => $slug,
                'label' => $cat['label'],
                'fileCount' => $fileCount,
                'methodCount' => $methodCount,
            ];
        }

        return [
            'totalFiles' => $totalFiles,
            'totalMethods' => $totalMethods,
            'categories' => $categoryStats,
        ];
    }

    /**
     * Get a summary of all registered web and API routes.
     *
     * Collects route method, URI, name, and controller action
     * for display on the documentation dashboard.
     *
     * @return array  List of route summary arrays
     */
    protected function getRouteSummary(): array
    {
        $routes = [];
        foreach (\Illuminate\Support\Facades\Route::getRoutes() as $route) {
            $action = $route->getActionName();
            if (str_contains($action, 'CodeDocController'))
                continue; // skip doc routes

            $routes[] = [
                'methods' => implode('|', $route->methods()),
                'uri' => $route->uri(),
                'name' => $route->getName() ?? '-',
                'action' => $action === 'Closure' ? 'Closure' : class_basename(str_replace('@', '::', $action)),
            ];
        }

        return $routes;
    }

    // ─────────────────────────────────────────────────────────────
    //  Utility Methods
    // ─────────────────────────────────────────────────────────────

    /**
     * Resolve the absolute file path for a given category and file name.
     *
     * Handles dot-notation for nested files (e.g., "Task.TaskManagementService"
     * resolves to Services/Task/TaskManagementService.php).
     *
     * @param  string $categoryDir  Category directory under app/
     * @param  string $fileName     File basename (dot notation for nested)
     * @return string|null          Absolute file path, or null if not found
     */
    protected function resolveFilePath(string $categoryDir, string $fileName): ?string
    {
        // Replace dots with directory separators for nested files
        $relativePath = str_replace('.', '/', $fileName) . '.php';
        $fullPath = $this->basePath . '/' . $categoryDir . '/' . $relativePath;
        return file_exists($fullPath) ? $fullPath : null;
    }

    /**
     * Resolve the fully qualified class name (FQCN) from a PHP file path.
     *
     * Parses the file content to extract namespace and class declarations.
     *
     * @param  string $filePath  Absolute path to the PHP file
     * @return string|null       FQCN like "App\Models\User", or null
     */
    protected function resolveFQCN(string $filePath): ?string
    {
        $content = file_get_contents($filePath);
        $namespace = null;
        $className = null;

        if (preg_match('/namespace\s+([^;]+);/', $content, $nsMatch)) {
            $namespace = trim($nsMatch[1]);
        }

        // Match class, interface, trait, or enum declaration
        if (preg_match('/(?:class|interface|trait|enum)\s+(\w+)/', $content, $classMatch)) {
            $className = $classMatch[1];
        }

        if ($namespace && $className) {
            return $namespace . '\\' . $className;
        }

        return null;
    }

    /**
     * Convert a directory path to a URL-safe slug.
     *
     * Replaces '/' with '-' for use in route parameters.
     *
     * @param  string $dirPath  Directory path like 'Http/Controllers'
     * @return string           Slug like 'Http-Controllers'
     */
    protected function slugify(string $dirPath): string
    {
        return str_replace('/', '-', $dirPath);
    }

    /**
     * Get the visibility keyword for a ReflectionProperty.
     *
     * @param  \ReflectionProperty $prop
     * @return string  'public', 'protected', or 'private'
     */
    protected function getVisibility(\ReflectionProperty $prop): string
    {
        if ($prop->isPublic())
            return 'public';
        if ($prop->isProtected())
            return 'protected';
        return 'private';
    }

    /**
     * Get the visibility keyword for a ReflectionClassConstant.
     *
     * @param  \ReflectionClassConstant $const
     * @return string  'public', 'protected', or 'private'
     */
    protected function getConstVisibility(\ReflectionClassConstant $const): string
    {
        if ($const->isPublic())
            return 'public';
        if ($const->isProtected())
            return 'protected';
        return 'private';
    }

    /**
     * Format a PHP value for display (used for defaults and constants).
     *
     * @param  mixed $value  The value to format
     * @return string        Human-readable string representation
     */
    protected function formatValue($value): string
    {
        if (is_null($value))
            return 'null';
        if (is_bool($value))
            return $value ? 'true' : 'false';
        if (is_string($value))
            return "'" . addslashes($value) . "'";
        if (is_array($value))
            return json_encode($value);
        return (string) $value;
    }

    /**
     * Format file size in human-readable units.
     *
     * @param  int $bytes  File size in bytes
     * @return string      Formatted size string (e.g., "12.5 KB")
     */
    protected function formatFileSize(int $bytes): string
    {
        if ($bytes >= 1048576)
            return round($bytes / 1048576, 1) . ' MB';
        if ($bytes >= 1024)
            return round($bytes / 1024, 1) . ' KB';
        return $bytes . ' B';
    }
}
