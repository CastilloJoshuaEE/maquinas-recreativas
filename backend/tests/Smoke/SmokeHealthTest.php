<?php
// tests/Smoke/SmokeHealthTest.php

require_once __DIR__ . '/SmokeTestCase.php';

class SmokeHealthTest extends SmokeTestCase
{
    /**
     * @test
     */
    public function elEndpointHealthRespondeCorrectamente()
    {
        $response = $this->makeRequest('GET', '/health');
        
        $this->assertEquals(200, $this->getLastHttpCode());
        $this->assertTrue($this->isSuccessResponse($response));
        $this->assertArrayHasKey('status', $response);
        $this->assertEquals('ok', $response['status']);
    }

    /**
     * @test
     */
    public function elEndpointTestDbRespondeYConectaCorrectamente()
    {
        $response = $this->makeRequest('GET', '/test-db');
        
        $this->assertEquals(200, $this->getLastHttpCode());
        $this->assertTrue($this->isSuccessResponse($response));
        $this->assertArrayHasKey('message', $response);
        $this->assertStringContainsString('exitosa', $response['message']);
    }

    /**
     * @test
     */
    public function robotsTxtEstaAccesible()
    {
        $url = $this->baseUrl . '/robots.txt';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $this->assertEquals(200, $httpCode, 'robots.txt debe ser accesible');
    }

    /**
     * @test
     */
    public function sitemapXmlEstaAccesible()
    {
        $url = $this->baseUrl . '/sitemap.xml';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $this->assertEquals(200, $httpCode, 'sitemap.xml debe ser accesible');
    }

}