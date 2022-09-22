<?php

namespace App\Tests;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
use App\Entity\Taxonomy\Taxon;

class CreatedProductContainsOneVariantTest extends ApiTestCase
{
    private Client $apiTestCase;

    public function setUp(): void
    {
        parent::setUp();
        $this->apiTestCase = static::createClient();
    }

    public function test_that_customer_can_create_product(): void
    {
        $this->givenUserExists(email: 'john@doe.fr');
        $this->givenTaxonExists(code: 'foo');

        $this->apiTestCase->request(
            method: "POST", url: '/api/v2/shop/products', options: [
                'auth_bearer' => $this->getToken(email: 'john@doe.fr'),
                'json' => [
                    'taxon' => $this->findIriBy(Taxon::class, ['code' => 'foo']),
                    'price' => '7000',
                    'name' => '404 miss imagination',
                    'description' => 'ilorem ipsum lalaland',
                ],
        ]);

        self::assertResponseIsSuccessful();
    }

    private function givenUserExists(string $email)
    {
        $this->apiTestCase->request(
            method: "POST", url: '/api/v2/shop/customers', options: [
            'json' => [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => $email,
                'password' => 'foobar123',
                'vatNumber' => 'FR62893587717'
            ],
        ]);

        self::assertResponseIsSuccessful();
    }

    private function getToken(string $email): string
    {
        $response = $this->apiTestCase->request(
            method: "POST", url: '/api/v2/shop/authentication-token', options: [
            'json' => [
                'email' => $email,
                'password' => 'foobar123',
            ],
        ])->getContent();

        return \json_decode($response, true, 512, JSON_THROW_ON_ERROR)['token'];
    }
}
