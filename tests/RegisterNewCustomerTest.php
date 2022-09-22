<?php

namespace App\Tests;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;

class RegisterNewCustomerTest extends ApiTestCase
{
    private Client $apiTestCase;

    public function setUp(): void
    {
        parent::setUp();
        $this->apiTestCase = static::createClient();
    }

    public function test_that_customer_vat_is_valid(): void
    {
        $this->apiTestCase->request(
            method: "POST", url: '/api/v2/shop/customers', options: [
            'json' => [
                'firstName' => 'John',
                'lastName' => 'Doe',
                'email' => 'john@doe.fr',
                'password' => 'foobar123',
                'vatNumber' => 'FR62893587717'
            ],
        ]);

        self::assertResponseIsSuccessful();
    }

    public function test_that_customer_vat_is_invalid(): void
    {
        $this->apiTestCase->request(
            method: "POST", url: '/api/v2/shop/customers', options: [
            'json' => [
                'firstName' => 'John',
                'lastName' => 'Doe',
                'email' => 'john@foo.fr',
                'password' => 'foobar123',
                'vatNumber' => 'FR693587717'
            ],
        ]);

        self::assertResponseStatusCodeSame(500);
        self::assertJsonContains(['message' => 'VAT number format "FR693587717" is invalid.']);
    }
}
