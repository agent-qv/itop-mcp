<?php
declare(strict_types=1);

namespace App\Tools\core\natural_language_search;

use Mcp\Capability\Attribute\McpTool;
use Mcp\Schema\ToolAnnotations;
use Twig\Environment;
use App\Service\iTopClientInterface;
use Psr\Log\LoggerInterface;
use App\Tools\iTopRestTools;


class iTopNaturalLanguageSearchTool extends iTopRestTools
{
    public function __construct(Environment $twig, iTopClientInterface $iTopClient, LoggerInterface $mcpLogger)
    {
        parent::__construct($twig, $iTopClient, $mcpLogger);
    }
    
    /**
     * This tool performs a search (multi-criterion search) in iTop from a sentence in natural language
     * Examples:
     * - What are the persons who do not belong to any team?
     * - Give me the list of all tickets opened by Claude Monet last week
     *
     * @param string $query A question (in natural language) from the user
     * @param int $limit The number of object per page
     * @param int $page The (one based) number of the page
     * 
     */
    #[McpTool(name: 'search-in-natural-language', annotations: new ToolAnnotations(null, true, false, false, false))]
    public function searchInNaturalLanguage(string $query, int $limit = 20, int $page = 1): string
    {
        $this->mcpLogger->info('[Tool called] search-in-natural-language');
        return $this->runToolFromTemplates('searchInNaturalLanguage', 'Anything', ['query' => $query, 'limit' => $limit, 'page' => $page]);
    }
}