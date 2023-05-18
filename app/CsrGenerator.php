<?php

namespace App;

use Illuminate\Support\Arr;
use InvalidArgumentException;

class CsrGenerator
{
    /**
     * Generate a new CSR.
     *
     * @return \App\Csr
     */
    public function rsa(array $distinguishedNames, int $keySize = 2048): Csr
    {
        $requiredKeys = [
            'commonName',
            'organizationName',
            'organizationalUnitName',
            'localityName',
            'stateOrProvinceName',
            'countryName',
        ];

        // Verify all keys are present
        foreach ($requiredKeys as $key) {
            if (! array_key_exists($key, $distinguishedNames)) {
                throw new InvalidArgumentException("Missing required DN component: {$key}");
            }
        }

        // Generates a new private key
        $keyResource = openssl_pkey_new([
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
            'private_key_bits' => $keySize,
        ]);

        // Generates a new CSR
        $csrResource = openssl_csr_new(Arr::only($distinguishedNames, $requiredKeys), $keyResource);

        openssl_csr_export($csrResource, $csrString);
        openssl_pkey_export($keyResource, $privateKeyString);

        $keyPair = new KeyPair(
            privateKey: $privateKeyString,
            publicKey: openssl_pkey_get_details($keyResource)['key'],
            type: KeyPairType::Rsa,
        );

        return new Csr($csrString, $keyPair);
    }
}
