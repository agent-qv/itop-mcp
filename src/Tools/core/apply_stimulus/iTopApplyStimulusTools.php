<?php
declare(strict_types=1);

namespace App\Tools\core\apply_stimulus;

use Mcp\Capability\Attribute\McpTool;
use Mcp\Schema\ToolAnnotations;
use Twig\Environment;
use App\Service\iTopClientInterface;
use Psr\Log\LoggerInterface;
use App\Tools\iTopRestTools;
use App\Service\DatamodelService;

class iTopApplyStimulusTools extends iTopRestTools
{
    private DatamodelService $datamodel;
    
    public function __construct(Environment $twig, iTopClientInterface $iTopClient, LoggerInterface $mcpLogger, DatamodelService $datamodel)
    {
        $this->datamodel = $datamodel;
        parent::__construct($twig, $iTopClient, $mcpLogger);
    }
    
    /**
     * This tool can apply a stimulus t o change the state of an iTop object driven by a life-cycle (typically Tickets).
     *
     * IMPORTANT: before calling this tool use the tool 'list-all-classes' to determine the list of available classes
     * and 'get-class-schema(<className>)' to obtain information about the schema for a particular class (to get information about the fields, their type and possible values)
     * and to also get information about the possible state transitions (and their associated stimulus code)
     *
     * @param string $object_class The class of the object to update
     * @param int $id The key of the object to update
     * @param string $stimulus_code The code of the stimulus to apply to the object
     * @param string $fields A JSON formatted object {"field_code":"field_value"} for the fields to take into account during the transition
     */
    #[McpTool(name: TOOL_PREFIX.'apply-stimulus-to-any-object', annotations: new ToolAnnotations(null, false, true, false, false))]
    public function applyStimulusToAnyObject(string $object_class, int $id, string $stimulus_code, string $fields_json): string
    {
        $fields = json_decode($fields_json, true);
        if ($fields === false) {
            $error = 'Error: invalid value for the parameter "field_json". Expecting a valid JSON structure {"code":"value"} for each field to populate.';
            $this->mcpLogger->error('[Tool called] apply-stimulus-to-any-object', ['error' => $error, 'object_class' => $object_class, 'id' => $id, 'fields_json' => $fields_json]);
            return $error;
        }
        $this->mcpLogger->info('[Tool called] apply-stimulus-to-any-object');
        return $this->runToolFromTemplates('applyStimulusOnAnything', 'Anything',
            [
                'class' => $object_class,
                'id' => $id,
                'stimulus' => $stimulus_code,
                'fields' => $fields,
                'output_fields' => $this->datamodel->getListZlist($object_class),
            ]
            );
    }
}