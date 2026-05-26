<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/')]
    public function index(Request $request, string $iTopUrl, string $projectDir): Response
    {
        $httpCode = 0;
        if ($iTopUrl !== '') {
            $httpCode = $this->getHttpCode($iTopUrl);
        }
        return $this->render('home.html.twig', [
            'iTopUrl' => $iTopUrl,
            'projectDir' => $projectDir,
            'mcpUri' => $request->getUriForPath('/_mcp'),
            'http_code' => $httpCode]
        );
    }
    
    protected function getHttpCode(string $url): int
    {
        $options = [
            CURLOPT_RETURNTRANSFER => true,     // return the content of the request
            CURLOPT_HEADER         => false,    // don't return the headers in the output
            CURLOPT_FOLLOWLOCATION => true,     // follow redirects
            CURLOPT_ENCODING       => "",       // handle all encodings
            CURLOPT_USERAGENT      => "MCP-server", // who am i
            CURLOPT_AUTOREFERER    => true,     // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
            CURLOPT_TIMEOUT        => 120,      // timeout on response
            CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
            CURLOPT_SSL_VERIFYPEER => false,    // Disabled SSL Cert checks
        ];
        
        $ch = curl_init($url);
        curl_setopt_array($ch, $options);
        @curl_exec($ch);
        $info = curl_getinfo($ch);
        return $info['http_code'];
    }
}