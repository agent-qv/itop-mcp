<?php
namespace App\Tests\Phpunit;

use App\Capability\iTopBuilder;
use App\Service\iTopClientInterface;
use App\Tests\Fixtures\ExtraTools\GetCiByNameTool;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use ReflectionMethod;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

// The fixture is deliberately not covered by any PSR-4 mapping - that is exactly the
// situation the extra-tool-dirs mechanism exists for, so this test loads it the same way
// iTopBuilder::requirePhpFiles() does at runtime, rather than relying on autoloading.
require_once __DIR__.'/fixtures/extra-tools/tools/GetCiByNameTool.php';

class ExtraToolDirsTest extends TestCase
{
    private const MANIFEST = 'tests/phpunit/fixtures/extra-tools/manifest.json';

    /**
     * The extra-tool-dirs mechanism itself: a directory declared in APP_EXTRA_TOOLS_MANIFEST
     * gets merged into discovery, same as the built-in src/Tools/{verb} directories.
     */
    public function testExtraDirIsMergedIntoDiscovery(): void
    {
        $projectDir = \dirname(__DIR__, 2);
        $client = $this->createStub(iTopClientInterface::class);
        $client->method('canConnect')->willReturn(false);

        $builder = new iTopBuilder($client, new ArrayAdapter(), $projectDir, new NullLogger(), self::MANIFEST);

        $method = new ReflectionMethod($builder, 'getDiscoveryDirs');
        $method->setAccessible(true);
        $dirs = $method->invoke($builder);

        $this->assertContains('/tests/phpunit/fixtures/extra-tools/tools', $dirs);
    }

    public function testExtraDirIsAbsentWithoutManifest(): void
    {
        $projectDir = \dirname(__DIR__, 2);
        $client = $this->createStub(iTopClientInterface::class);
        $client->method('canConnect')->willReturn(false);

        $builder = new iTopBuilder($client, new ArrayAdapter(), $projectDir, new NullLogger());

        $method = new ReflectionMethod($builder, 'getDiscoveryDirs');
        $method->setAccessible(true);
        $dirs = $method->invoke($builder);

        $this->assertNotContains('/tests/phpunit/fixtures/extra-tools/tools', $dirs);
    }

    /**
     * The fixture tool itself: a real tool, with a genuine constructor dependency
     * (iTopClientInterface), that actually calls out to iTop and parses the response - so a
     * reviewer sees what an extra tool actually looks like, not a toy example.
     */
    public function testGetCiByNameParsesAMatchingCi(): void
    {
        $client = $this->createStub(iTopClientInterface::class);
        $client->method('postJsonToItop')->willReturn(json_encode([
            'code' => 0,
            'message' => '',
            'objects' => [
                'PC::12' => [
                    'code' => 0,
                    'class' => 'PC',
                    'key' => '12',
                    'fields' => [
                        'friendlyname' => 'PC-EXAMPLE',
                        'finalclass' => 'PC',
                        'org_id_friendlyname' => 'Demo',
                    ],
                ],
            ],
        ]));

        $tool = new GetCiByNameTool($client, new NullLogger());

        $this->assertSame('PC-EXAMPLE (PC), id 12, org: Demo', $tool->getCiByName('PC-EXAMPLE'));
    }

    public function testGetCiByNameReportsNoMatch(): void
    {
        $client = $this->createStub(iTopClientInterface::class);
        $client->method('postJsonToItop')->willReturn(json_encode([
            'code' => 0,
            'message' => '',
            'objects' => null,
        ]));

        $tool = new GetCiByNameTool($client, new NullLogger());

        $this->assertSame("No CI found with name 'NOPE'.", $tool->getCiByName('NOPE'));
    }
}
