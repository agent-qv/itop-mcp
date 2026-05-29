<?php
declare(strict_types=1);

namespace App\Tools\schema;

use Mcp\Capability\Attribute\McpTool;
use App\Service\DatamodelService;

class iTopSchemaTools
{
    public function __construct(protected DatamodelService $datamodel)
    {
        
    }

    /**
     * This tool lists all the possible classes of objects managed in iTop.
     */
    #[McpTool(name: 'list-all-classes')]
    public function listAllClasses(): string
    {
        $output = '';
        $classes = $this->datamodel->getClasses();
        ksort($classes);
        foreach($classes as $className => $classInfo) {
            if ((strstr($classInfo['category'], 'bizmodel') !== false) && ($classInfo['is_link'] !== 1)) {
                // Only bizmodel classes which are not n:n links
                $description = $classInfo['description'] === '' ? '' : ': '.$classInfo['description'];
                $inheritance = !in_array($classInfo['parent'], ['DBObject', 'CMDBOject', 'cmdbAbstractObject']) ? ' derived from class '.$classInfo['parent'] : '';
                $output .= "Class {$className} (label: {$classInfo['label']}){$inheritance}{$description}\n";
            }
        }
        return $output;
    }

    /**
     * This tool documents the schema (i.e. the list of all the fields) of a specified class in iTop.
     */
    #[McpTool(name: 'get-class-schema')]
    public function getClassSchema(string $className): string
    {
        $fields = $this->datamodel->getClassSchema($className);
        if ($fields === []) {
            return "The class $className does not exist in iTop. Please provide a valid iTop class name (not a label).";
        }
        
        $output = "Class $className:\n  Fields:\n";
        foreach($fields as $code => $fieldInfo) {
            $type = substr($fieldInfo['type'], 9);
            $description = $fieldInfo['description'] === '' ? '' : ', '.$fieldInfo['description'];
            $mandatory = $fieldInfo['is_null_allowed'] === 'false' ? ' mandatory ' : '';
            $output .= "    - {$code} ({$fieldInfo['label']}), type: {$type}{$mandatory}{$description}\n";
        }
        return $output;
    }
}
