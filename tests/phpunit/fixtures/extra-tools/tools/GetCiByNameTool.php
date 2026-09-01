<?php

namespace App\Tests\Fixtures\ExtraTools;

use App\Service\iTopClientInterface;
use Mcp\Capability\Attribute\McpTool;
use Mcp\Schema\ToolAnnotations;
use Psr\Log\LoggerInterface;

/**
 * A real tool living outside src/Tools, used as a fixture for
 * tests/phpunit/ExtraToolDirsTest.php: a genuine lookup against iTop's CMDB, with real
 * dependency injection (iTopClientInterface, from src/) - what a deployment or an iTop
 * extension can add via APP_EXTRA_TOOLS_MANIFEST, not a toy demo.
 */
class GetCiByNameTool
{
    public function __construct(
        private iTopClientInterface $iTopClient,
        private LoggerInterface $mcpLogger,
    ) {
    }

    /**
     * Looks up a Configuration Item (CI) in iTop's CMDB by its exact name.
     * @param string $name The CI's name
     */
    #[McpTool(name: TOOL_PREFIX.'get-ci-by-name', annotations: new ToolAnnotations(null, true, false, true, false))]
    public function getCiByName(string $name): string
    {
        $this->mcpLogger->info('[Tool called] get-ci-by-name');

        $safeName = str_replace(["\\", "'", '"'], '', $name);
        $requestJson = json_encode([
            'operation' => 'core/get',
            'class' => 'FunctionalCI',
            'key' => "SELECT FunctionalCI WHERE name = '$safeName'",
            'output_fields' => 'friendlyname,finalclass,org_id_friendlyname',
        ]);

        $response = $this->iTopClient->postJsonToItop($requestJson);
        $decoded = json_decode($response, true);

        if (!is_array($decoded) || ($decoded['code'] ?? null) !== 0) {
            return 'iTop error: '.($decoded['message'] ?? $response);
        }
        if (empty($decoded['objects'])) {
            return "No CI found with name '$name'.";
        }

        $lines = [];
        foreach ($decoded['objects'] as $object) {
            $fields = $object['fields'] ?? [];
            $lines[] = sprintf(
                '%s (%s), id %s, org: %s',
                $fields['friendlyname'] ?? $name,
                $fields['finalclass'] ?? ($object['class'] ?? '?'),
                $object['key'] ?? '?',
                $fields['org_id_friendlyname'] ?? '?'
            );
        }

        return implode("\n", $lines);
    }
}
