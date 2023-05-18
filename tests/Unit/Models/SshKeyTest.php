<?php

namespace Tests\Unit\Models;

use App\Models\FingerprintAlgorithm;
use App\Models\SshKey;
use Tests\TestCase;

class SshKeyTest extends TestCase
{
    /** @test */
    public function it_can_generate_valid_fingerprints()
    {
        $testCases = [
            [
                'key' => 'ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABAQDHvEgFEN9hKtRK8RWklwRo6+ZmEceTEn1zN9rgXyGXrRtsmELf8ZEw5MpfI6S6l2OFJaruhEKSfe1Qm+zPdQZHcghmCN7rVpvy18OCJq+rTCUt7M39TKJ6QRAWHiRSMqL3ht8dGRbOLpTZLCBX3Z1/hdPz1ye/EMwTY7x4Tzofp2tPlcnBO9Zvh5iksLpL7CcZd0Z7BGlgE2eFwPZ4vRR0iSBQMHmHEPLX6boWhgFz/uORxu0gJEixz+FFwavYAZGpGGZrJQ90Pcejk9e1iCohRq+106jf0I5u1j0G7FUeMpsmSGnuFRXbcnywiHLyHLA0gTKXaWJHID6FxfqEoTAb lukecousins@Lukes-iMac.local',
                'fingerprint' => '47:ae:96:ec:e0:b7:bc:59:69:db:24:37:56:21:8f:46',
                'sha256Fingerprint' => '+Sg6ZIRbJ4y0tqyP/N4zLhn6pwgzbH1oMKjdJZ+IWvc=',
            ],
            [
                'key' => 'ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABAQDHbry1PSM+P0eQZX32b8Gy/cAkxktpzB0hVPnvLDAqUGA2k7e4MJz7V4jYpNqeWUMqElKSAgn/GYwra7U2YpA/eZWSPpWIL2pwy4oQ2+bRaSzlt6wyB4aTLG1dHRFl+kYK3dN3XDdjY2Fx+xQX809eeWtmqBWy5khbQ2Fx0ovK6f0mSbtJRMrLioBYQ/2YQXeR5HQH8CjLZHJ8XeY4BjJ0D+W0yRim+uD7d3CqCUlR2BVDi0MevVMnvwefKQD9cJMOJSsHdGP8Vm/PMpZ5tS4JSTULVkBHfzpC/2D7AI4jbAxUO9j7kChaRY+te+6mYLJGkUtgMDOrTNTaOEAKjeO/ lukecousins@Lukes-iMac.local',
                'fingerprint' => '74:e7:80:bf:89:11:31:55:f7:4d:78:86:0d:fd:13:f5',
                'sha256Fingerprint' => '6EbRDmqVEmgAD6FOXQuIiG8uUSteIDKZzj2bgZ+88V0=',
            ],
            [
                'key' => 'ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABAQDtxokPPjj4DNFrgNwIeyNB17m9+Afc7zUyjJr7E6RcrSyxnvwVrYyQDC0YJ6p6Ze4VUvx2Rv8RaU01J4wbVSY4TQN0NAR0A+uxnOuvu1MMu6/6cm2cU+QDHQWpPSGV4AQG01GZuUZMrM9uHJ3ZxW3ZP7gc5JSmAkvUi2TsMVRwQIFhTNG9jbJACV76z2GrF7Jze5SEH93hfSfE+qm4AM2Tiq7B7Ion/o/XIHbtIMte1y8HYC568aOHK+BbDLhn50UYl834YQzLsu3DBfGMONKFTcJmvzFrOdK5CSqKSlEygRGhZ3Cw8L1ReKYvWRtnCfsKHmnz1ZPV6d/MwTjKmJvx',
                'fingerprint' => 'b1:17:8b:f3:34:8a:17:c7:70:ac:dd:c9:da:b2:b6:bd',
                'sha256Fingerprint' => 'Dvxyo3sSYtSvazExp6SihBKt6BYSjbUG1NG7L3sbAq0=',
            ],
        ];

        foreach ($testCases as $case) {
            $generatedFingerprint = SshKey::generateFingerprint($case['key'], FingerprintAlgorithm::Md5);
            $this->assertEquals($case['fingerprint'], $generatedFingerprint);

            $generatedSha256Fingerprint = SshKey::generateFingerprint($case['key'], FingerprintAlgorithm::Sha256);
            $this->assertEquals($case['sha256Fingerprint'], $generatedSha256Fingerprint);
        }
    }

    /** @test */
    public function it_returns_null_on_an_invalid_key()
    {
        $this->assertNull(
            SshKey::generateFingerprint('rsa AAAAB3NzaC1yc2EAAAADAQABAAABAQDtxokPPjj4DNFrgNwIeyNB17m9+Afc7zUyjJr7E6RcrSyxnvwVrYyQDC0YJ6p6Ze4VUvx2Rv8RaU01J4wbVSY4TQN0NAR0A+uxnOuvu1MMu6/6cm2cU+QDHQWpPSGV4AQG01GZuUZMrM9uHJ3ZxW3ZP7gc5JSmAkvUi2TsMVRwQIFhTNG9jbJACV76z2GrF7Jze5SEH93hfSfE+qm4AM2Tiq7B7Ion/o/XIHbtIMte1y8HYC568aOHK+BbDLhn50UYl834YQzLsu3DBfGMONKFTcJmvzFrOdK5CSqKSlEygRGhZ3Cw8L1ReKYvWRtnCfsKHmnz1ZPV6d/MwTjKmJvx lukecousins@Lukes-iMac.local')
        );
    }
}
