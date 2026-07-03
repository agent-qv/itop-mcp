<?php
declare(strict_types=1);

namespace App\Tools\core\get_related;

use Mcp\Capability\Attribute\McpTool;
use Mcp\Schema\ToolAnnotations;
use Twig\Environment;
use App\Service\iTopClientInterface;
use Psr\Log\LoggerInterface;
use App\Tools\iTopRestTools;
use App\Service\DatamodelService;

class iTopGetRelatedTools extends iTopRestTools
{
    private DatamodelService $datamodel; 
    
    public function __construct(Environment $twig, iTopClientInterface $iTopClient, LoggerInterface $mcpLogger, DatamodelService $datamodel)
    {
        $this->datamodel = $datamodel;
        parent::__construct($twig, $iTopClient, $mcpLogger);
    }
    
    /**
     * This tool provides information about the impact of a given object.
     * I.e. it returns a list of objects potentially impacted in case of a modification or a failure of the specified object
     * @param string $objectClass The class of the object for which to compute the impact
     * @param int $objectIi The identifier of the object for which to compute the impact
     */
    #[McpTool(name: TOOL_PREFIX.'get-impacted-objects', annotations: new ToolAnnotations(null, true, false, true, false))]
    public function getImpactedObjects(string $objectClass, int $objectId): string
    {
        $this->mcpLogger->info('[Tool called] get-impacted-objects');
        return $this->runToolFromTemplates('getRelatedObjects', 'relatedObjects',
            [
                'class' => $objectClass,
                'id' => $objectId,
                'direction' => 'down',
            ]
        );
    }
    
    /**
     * This tool provides information about objects from which the given object depends
     * I.e. it returns a list of objects whose modification or failure may have consequences on the specified object
     * @param string $objectClass The class of the object for which to compute the dependencies
     * @param int $objectIi The identifier of the object for which to compute the dependencies
     */
    #[McpTool(name: TOOL_PREFIX.'get-depends-on-objects', annotations: new ToolAnnotations(null, true, false, true, false))]
    public function getDependsOnObjects(string $objectClass, int $objectId): string
    {
        $this->mcpLogger->info('[Tool called] get-depends-on-objects');
        return $this->runToolFromTemplates('getRelatedObjects', 'relatedObjects',
            [
                'class' => $objectClass,
                'id' => $objectId,
                'direction' => 'up',
            ]
        );
    }
}