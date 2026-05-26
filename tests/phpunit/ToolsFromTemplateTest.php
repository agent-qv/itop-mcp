<?php
namespace App\Tests\Phpunit;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Service\iTopClient;
use App\Tools\core\get\iTopGetTools;
use Twig\Environment;
use Psr\Log\LoggerInterface;
use App\Service\DatamodelService;

class ToolsFromTemplateTest extends KernelTestCase
{
    protected Environment $twigEnvironment;
    protected LoggerInterface $logger;
    protected DatamodelService $datamodel;

    public function setUp():void
    {
        // (1) boot the Symfony kernel
        self::bootKernel();
        
        // (2) use static::getContainer() to access the service container
        $container = static::getContainer();
        $this->twigEnvironment = $container->get(Environment::class);
        $this->logger = $container->get(LoggerInterface::class);
        $this->datamodel = $container->get(DatamodelService::class);
    }

    public function testGetPersonFromEmail(): void
    {
        // Mock the iTopClient service, to check that the templating works
        $mockiTopClient = $this->MockiTopClientThatWillReturn(
<<<JSON
{
  "operation": "core/get",
  "class": "Person",
  "key": "SELECT Person WHERE email = 'test\u0040demo.com'",
  "output_fields": "id,friendlyname,name,org_id,status,location_id,email,phone"
}
JSON
            ,
            
            ['objects' => ['Person::1' => [
                'key' => 1,
                'class' => 'Person',
                'fields' => [
                    'friendlyname' => 'Test Person',
                    'email' => 'test@demo.com',
                    'org_id' => 1,
            ]]]]
        );
        
        $tools = new iTopGetTools($this->twigEnvironment, $mockiTopClient, $this->logger, $this->datamodel);

        $this->assertEquals(<<<EXPECTED
class Person:
  id, friendlyname, email, org_id

Person {
  1, "Test Person", "test@demo.com", "1"
}

EXPECTED
            , $tools->getPersonFromEmail('test@demo.com'));
    }
    
    public function notYetGetPersonFromTelephone(): void
    {
        // Mock the iTopClient service, to check that the tempalting works
        $mockiTopClient = $this->MockiTopClientThatWillReturn(
<<<JSON
{
    "operation": "core/get",
    "class": "Person",
    "key": "SELECT Person WHERE email = 'test\u0040demo.com'",
    "output_fields": "id,friendlyname,name,org_id,status,location_id,email,phone\"
}
JSON
            ,
            
            ['objects' => ['Person::1' => [
                'key' => 1,
                'fields' => [
                    'friendlyname' => 'Test Person',
                    'email' => 'test@demo.com',
                    'org_id' => 1,
                ]]]]
            );
        
        $tools = new iTopGetTools($this->twigEnvironment, $mockiTopClient, $this->logger, $this->datamodel);
        
        $this->assertEquals(<<<EXPECTED
class Person:
  id,name,email,org_id
            
Person {
  1, "Test Person", test@demo.com, 1
}
            
EXPECTED
            , $tools->getPersonFromTelephone('123456789'));
    }

    protected function MockiTopClientThatWillReturn(string $inputData, array $outputData)
    {
        $mockiTopClient = $this->createMock(iTopClient::class);
        $mockiTopClient->expects(self::once())
        ->method('postJsonToItop')
        ->with($inputData)
        ->willReturn(json_encode($outputData));
        return $mockiTopClient;
    }
}