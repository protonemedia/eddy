<?php

namespace Tests\Unit;

use App\CsrGenerator;
use Tests\TestCase;

class CsrGeneratorTest extends TestCase
{
    /** @test */
    public function it_can_generate_a_csr()
    {
        $generator = new CsrGenerator;

        $pair = $generator->rsa([
            'commonName' => 'apple.com',
            'countryName' => 'US',
            'localityName' => 'Cupertino',
            'stateOrProvinceName' => 'California',
            'organizationName' => 'Apple Inc.',
            'organizationalUnitName' => 'IT',
        ]);

        $data = openssl_csr_get_subject($pair->csr);

        $this->assertArrayHasKey('CN', $data);
        $this->assertArrayHasKey('O', $data);
        $this->assertArrayHasKey('OU', $data);
        $this->assertArrayHasKey('L', $data);
        $this->assertArrayHasKey('ST', $data);
        $this->assertArrayHasKey('C', $data);

        $this->assertEquals('apple.com', $data['CN']);
        $this->assertEquals('Apple Inc.', $data['O']);
        $this->assertEquals('IT', $data['OU']);
        $this->assertEquals('Cupertino', $data['L']);
        $this->assertEquals('California', $data['ST']);
        $this->assertEquals('US', $data['C']);
    }

    /** @test */
    public function it_throws_an_exception_on_a_missing_dn()
    {
        $generator = new CsrGenerator;

        try {
            $generator->rsa([
                // 'commonName' => 'apple.com',
                'countryName' => 'US',
                'localityName' => 'Cupertino',
                'stateOrProvinceName' => 'California',
                'organizationName' => 'Apple Inc.',
                'organizationalUnitName' => 'IT',
            ]);
        } catch (\Exception $e) {
            return $this->assertEquals('Missing required DN component: commonName', $e->getMessage());
        }

        $this->fail('Exception was not thrown');
    }
}
