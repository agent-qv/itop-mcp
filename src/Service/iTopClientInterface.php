<?php
namespace App\Service;

interface iTopClientInterface
{
    public function postJsonToItop(string $json): string;
    
    public function canConnect(): bool;
    
    public function getRestEndpointUrl(): string;
}