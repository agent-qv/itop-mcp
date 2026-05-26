<?php
namespace App\Service;

use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;
use DOMDocument;
use DOMNode;
use DOMXPath;

class DatamodelService
{

    public function __construct(private string $datamodelFile, private CacheInterface $datamodelCache, private string $language)
    {
        
    }
    
    public function getClasses(): array
    {
        $classes = $this->datamodelCache->get('Classes-'.$this->language, function(ItemInterface $item) {
            $item->expiresAfter(3600);
            if (file_exists($this->datamodelFile)) {
                return $this->getClassesFromXml($this->datamodelFile);
            }
            return [];
        }, 1.0);
        return $classes;
    }
    
    public function getClassSchema(string $className): array
    {
        $classSchema = $this->datamodelCache->get('ClassSchema-'.$className.'-'.$this->language, function(ItemInterface $item) use ($className) {
            $item->expiresAfter(3600);
            if (file_exists($this->datamodelFile)) {
                return $this->getClassSchemaFromXml($className, $this->datamodelFile);
            }
            return [];
        }, 1.0);
        return $classSchema;
    }
    
    public function getListZlist(string $className): string
    {
        $classes = $this->getClasses();
        return $classes[$className]['zlist_list'] ?? 'id,friendlyname';
    }
    
    public function getClassesFromXml(string $xmlFile): array
    {
        $classes = [];
        $document = new DOMDocument();
        $document->load($xmlFile);
        $xp = new \DOMXPath($document);
        $query = "//classes/class/properties/category/../..";
        $nodes = $xp->query($query);
        foreach($nodes as $node) {
            $info = [];
            $info['name'] = $node->getAttribute('id');
            $info['is_link'] = $this->getChildContents('properties/is_link', $node);
            $info['category'] = $this->getChildContents('properties/category', $node);
            $info['abstract'] = $this->getChildContents('properties/abstract', $node) == 'true' ? true : false;
            $info['parent'] = $this->getChildContents('parent', $node);
            $info['label'] = $this->getClassLabel($xp, $node->getAttribute('id'), $this->language);
            $info['description'] = $this->getClassDescription($xp, $node->getAttribute('id'), $this->language);
            $zlist = $this->getFlatZlist($xp, $node->getAttribute('id'), 'list');
            $info['zlist_list'] = $info['is_link'] ? $zlist : 'friendlyname,'.$zlist;
            $classes[$node->getAttribute('id')] = $info;
        }
        return $classes;
    }
    
    public function getClassSchemaFromXml(string $className,string $xmlFile)
    {
        $fields = [];
        $document = new DOMDocument();
        $document->load($xmlFile);
        $xp = new \DOMXPath($document);
        $query = "/itop_design/classes/class[@id='$className']";
        $nodes = $xp->query($query);
        if ($nodes->count() !== 1) {
            throw new \Exception("Class $className not found or ambiguous in the datamodel XML. ".$nodes->count()." elements found.");
        }
        $classNode = $nodes->item(0);
        $parent = $this->getChildContents('parent', $classNode);
        if (!in_array($parent, ['DBObject', 'CMDBOject', 'cmdbAbstractObject'])) {
            // Inherit the fields from the parent class(es)
            $fields = $this->getClassSchema($parent);
        }
        $fieldNodes = $xp->query("/itop_design/classes/class[@id='$className']/fields/field");
        foreach($fieldNodes as $fieldNode) {
            $fieldInfo = [];
            $fieldInfo['code'] = $fieldNode->getAttribute('id'); 
            $fieldInfo['type'] = $fieldNode->getAttribute('xsi:type');
            $fieldInfo['label'] = $this->getDictEntry("Class:$className/Attribute:{$fieldInfo['code']}", $xp);
            $fieldInfo['description'] = $this->getDictEntry("Class:$className/Attribute:{$fieldInfo['code']}+", $xp);
            $fields[$fieldInfo['code']] = $fieldInfo;
        }
        return $fields;
    }
    
    protected function getChildContents(string $path, DOMNode $node): string
    {
        $xp = new \DOMXPath($node->ownerDocument);
        $nodes = $xp->query($path, $node);
        return ($nodes->item(0) != null) ? $nodes->item(0)->textContent : '';
    }
    
    protected function getClassLabel(\DOMXPath $xp, string $className, string $language) {
        static $classLabels = null; // memoization
        
        if ($classLabels === null) {
            $query = "//dictionaries/dictionary[@id='".$language."']/entries/entry[starts-with(@id,'Class') and not(contains(@id, '/')) and not(contains(@id, '+'))]";
            $nodes = $xp->query($query);
            foreach($nodes as $node) {
                $name = substr($node->getAttribute('id'), 6);
                $classLabels[$name] = $node->textContent;
            }
        }
        return $classLabels[$className] ?? $className;
    }
    
    protected function getClassDescription(\DOMXPath $xp, string $className, string $language) {
        static $classDescriptions = null; // memoization
        
        if ($classDescriptions === null) {
            $query = "//dictionaries/dictionary[@id='".$language."']/entries/entry[starts-with(@id,'Class') and not(contains(@id, '/')) and contains(@id, '+')]"; // ends-with is a XPath 2.0 function
            $nodes = $xp->query($query);
            foreach($nodes as $node) {
                $name = substr(substr($node->getAttribute('id'), 6), 0, -1);
                $classDescriptions[$name] = $node->textContent;
            }
        }
        return $classDescriptions[$className] ?? '';
    }
    
    protected function getFlatZlist(DOMXPath $xp, string $className, string $listName): string
    {
        $items = $xp->query("//classes/class[@id='".$className."']/presentation/$listName/items/item");
        $zlist = [];
        foreach($items as $item) {
            $zlist[$item->getAttribute('id')] = $this->getChildContents('rank', $item);
        }

        asort($zlist, SORT_NUMERIC);
        return implode(',', array_keys($zlist));
    }
    
    protected function getDictEntry($id, DOMXPath $xp): string
    {
        $items = $xp->query("//dictonaries/dictionary[@id='{$this->language}']/items/item[@id='$id']");
        if ($items->count() === 0) {
            return '';
        }
        return $items->item(0)->textContent;
    }
}

