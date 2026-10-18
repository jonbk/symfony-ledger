<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    private function request(string $method, string $uri, ?string $content = null, ?string $apiKey = null): int
    {
        $apiKey ??= static::getContainer()->getParameter('ledger.apiKey');

        $this->client->request($method, $uri, server: [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $apiKey,
            'CONTENT_TYPE' => 'application/json',
        ], content: $content);

        return $this->client->getResponse()->getStatusCode();
    }

    private function validPayload(array $override = []): string
    {
        return json_encode(array_merge([
            'action' => 'TEST',
            'identifier' => 'T-000001',
            'author' => 'test@example.com',
            'date' => '2023-01-03 08:39:29',
            'metadata' => ['foo' => 'bar'],
        ], $override));
    }

    public function testHealthIsPublic(): void
    {
        $this->client->request('GET', '/health');

        $this->assertResponseIsSuccessful();
    }

    public function testInvalidApiKeyIsRejected(): void
    {
        $this->assertSame(401, $this->request('GET', '/blocks', apiKey: 'wrong-key'));
    }

    public function testValidBlockIsCreated(): void
    {
        $this->assertSame(200, $this->request('POST', '/blocks', $this->validPayload()));
    }

    /**
     * @dataProvider invalidPayloadProvider
     */
    public function testInvalidPayloadIsRejected(string $payload): void
    {
        $this->assertSame(400, $this->request('POST', '/blocks', $payload));
    }

    public function invalidPayloadProvider(): iterable
    {
        yield 'json invalide' => ['{'];
        yield 'champ manquant' => [json_encode(['action' => 'TEST'])];
        yield 'champ vide' => [$this->validPayload(['author' => ' '])];
        yield 'champ non chaîne' => [$this->validPayload(['identifier' => 42])];
        yield 'champ trop long' => [$this->validPayload(['action' => str_repeat('a', 256)])];
        yield 'date invalide' => [$this->validPayload(['date' => 'pas une date'])];
        yield 'metadata absente' => [$this->validPayload(['metadata' => null])];
    }

    public function testInvalidPageIsRejected(): void
    {
        $this->assertSame(400, $this->request('GET', '/blocks?page=0'));
        $this->assertSame(400, $this->request('GET', '/blocks?page=-3'));
    }
}
