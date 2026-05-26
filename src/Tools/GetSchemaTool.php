<?php
declare(strict_types=1);

namespace App\Tools;

use Mcp\Capability\Attribute\McpTool;

class GetSchemaTool
{
    /**
     * This tool lists the possible classes of objects in iTop.
     */
    #[McpTool(name: 'get-itop-classes')]
    public function getiTopClasses(): string
    {
        return 
<<<EOF
Person: a physical person, a contact.
UserRequest: a ticket submitted by a person requesting some assistance.
Team: a group of persons.
Organization: an entity inside a company, Organizations can be arranged in hierarchy. In iTop a person belongs to one and only one Organization.
EOF;
    }

    /**
     * This tool documents the schema of a specified class in iTop.
     */
    #[McpTool(name: 'get-class-schema')]
    public function getClassSchema(string $className): string
    {
        $result = "The class $className does not exist in iTop. Please try again.";
        
        switch($className)
        {
            case "Person":
                $result =
<<<EOF
Person: a physical person, a contact.
  Fields:
    - org_id (Organization), mandatory. Foreign key to an Organisation object
    - name (Name), mandatory. String
    - first_name (Given name), mandatory. String
    - email (Email address). String
    - phone (Telephone number). String
EOF;
                break;
            case "UserRequest":
                $result =
<<<EOF
UserRequest: a ticket submitted by a person requesting some assistance.
  Fields:
    - caller_id (Caller), mandatory. Foreign key to a Person object
    - org_id (Organization), mandatory. Foreign key to an Organisation object
    - title (Title), mandatory. String
    - description (Description), mandatory. String
    - impact (Impact), optional. Enum (1 = high, 2 = medium, 3 = low)
    - urgency (Urgency), optional. Enum (1 = critical, 2 = hight, 3 = medium, 4 = low)
    - service_id (Service), optional. Foreign key to a Service object
    - priority (Priority), read-only / computed. 1 = critical, 2, 3 or 4 = low
EOF;
                break;
            case "Organization":
                $result =
<<<EOF
Organization: an entity inside a company, Organizations can be arranged in hierarchy. In iTop a person belongs to one and only one Organization.
  Fields:
    - name (Name), mandatory. String
    - code (Code). String
    - parent_id (Parent Organization). A foreign key to an Organization object
EOF;
                break;
        }
        return $result;
    }
}
