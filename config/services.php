<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

// Registers extra tool directories (see APP_EXTRA_TOOLS_MANIFEST) as Symfony services, the
// same way `App\: resource: '../src/'` does for src/Tools in services.yaml - autowiring plus
// the mcp.tool autoconfiguration (registered by the mcp bundle for the #[McpTool] attribute)
// is what makes a discovered class actually callable with its dependencies, instead of
// falling back to `new $class()` with no arguments.
//
// This runs at container-compile time, so it reads the manifest itself rather than going
// through App\Capability\iTopBuilder::getExtraToolDirs() (a runtime service). Both must be
// kept in sync with the manifest format - see iTopBuilder for the discovery side of the
// same file.
return function (ContainerConfigurator $configurator): void {
    $manifestFile = $_SERVER['APP_EXTRA_TOOLS_MANIFEST'] ?? $_ENV['APP_EXTRA_TOOLS_MANIFEST'] ?? '';
    if ($manifestFile === '') {
        return;
    }

    $projectDir = \dirname(__DIR__);
    $manifestPath = $projectDir.'/'.$manifestFile;
    if (!is_file($manifestPath) || !is_readable($manifestPath)) {
        return;
    }

    $entries = json_decode((string)file_get_contents($manifestPath), true);
    if (!\is_array($entries)) {
        return;
    }

    foreach ($entries as $entry) {
        if (!isset($entry['namespace'], $entry['directory'])) {
            continue;
        }
        $dir = $projectDir.'/'.ltrim($entry['directory'], '/');
        if (!is_dir($dir)) {
            continue;
        }
        // Autoconfiguration reads the #[McpTool] attribute via reflection to add the
        // mcp.tool tag, which needs the class loaded - Composer has no autoload rule for
        // this directory, so load it explicitly before handing the directory to load().
        // Recursive to match load()'s own directory scan.
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                require_once $file->getPathname();
            }
        }
        $configurator->services()
            ->load($entry['namespace'], $dir)
            ->autowire()
            ->autoconfigure();
    }
};
