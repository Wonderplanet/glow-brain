<?php

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Util\Cryptography\AesRequestEncryptor;

class AesRequestEncryptorTest extends TestCase
{
    #[Test]
    public function test_encrypt_decrypt_暗号化した文字列は同じkeyとivで復号できる()
    {
        // Setup
        $enc = new AesRequestEncryptor();
        $testData = 'testテストの文字列test';
        $password = 'password';
        $salt = 'salt';

        // Exercise
        $result = $enc->encrypt($testData, $password, $salt);
        $result = $enc->decrypt($result, $password, $salt);

        // Verify
        $this->assertEquals($testData, $result);
    }
}
