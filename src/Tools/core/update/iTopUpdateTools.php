<?php
declare(strict_types=1);

namespace App\Tools\core\update;

use Mcp\Capability\Attribute\McpTool;
use Mcp\Schema\ToolAnnotations;
use Twig\Environment;
use App\Service\iTopClientInterface;
use Psr\Log\LoggerInterface;
use App\Tools\iTopRestTools;
use App\Service\DatamodelService;

class iTopUpdateTools extends iTopRestTools
{
    private DatamodelService $datamodel; 
    
    public function __construct(Environment $twig, iTopClientInterface $iTopClient, LoggerInterface $mcpLogger, DatamodelService $datamodel)
    {
        $this->datamodel = $datamodel;
        parent::__construct($twig, $iTopClient, $mcpLogger);
    }

    
    /**
     * This tool updates a User Request in iTop by adding an entry to the log and optionally updating the title or the description
     * @param int $id The identifier of the UserRequest to update
     * @param string $additionalLogEntryHtml An new entry to add to the log of the UserRequest - NOTE the log entry MUST BE formatted in HTML 
     * @param string $title Specify the new title for the UserRequest (leave empty for no change) 
     * @param string $descriptionHtml Specify the new description for the UserRequest (leave empty for no change) - NOTE the descriptipon MUST BE formatted in HTML
     */
    #[McpTool(name: 'update-user-request', annotations: new ToolAnnotations(null, false, true, false, false))]
    public function updateUserRequest(int $id, string $additionalLogEntryHtml, string $title = '', string $descriptionHtml = ''): string
    {
        $this->mcpLogger->info('[Tool called] update-user-request');
        return $this->runToolFromTemplates('updateUserRequest', 'Anything',
            [
                'id' => $id,
                'title' => $title,
                'description' => $descriptionHtml,
                'log' => $additionalLogEntryHtml,
                'output_fields' => $this->datamodel->getListZlist('UserRequest'),
                'log_field' => 'public_log',
            ]
        );
    }
}