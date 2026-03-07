<?php

namespace WonderPlanet\Util\Cryptography;

use Illuminate\Support\Facades\Config;

class AesRequestEncryptor
{
    private const CIPHER_ALGORITHM = 'AES-256-CBC';
    private const HASH_ALGORITHM = 'sha1';
    private const KEY_LENGTH = 32; // Byte (256 bits)
    private const IV_LENGTH = 16; // Byte (128 bits)
    private const PBKDF2_ITERATIONS = 2000;


    public function encrypt(string $data, string $password, string $salt): string|false
    {
        [$key, $iv] = self::generateKeyAndIV($password, $salt);
        $encrypted = openssl_encrypt($data, self::CIPHER_ALGORITHM, $key, OPENSSL_RAW_DATA, $iv);
        return $encrypted;
    }

    public function decrypt(string $data, string $password, string $salt): string|false
    {
        [$key, $iv] = self::generateKeyAndIV($password, $salt);
        $decrypted = openssl_decrypt($data, self::CIPHER_ALGORITHM, $key, OPENSSL_RAW_DATA, $iv);
        return $decrypted;
    }

    private function generateKeyAndIV(string $password, string $salt): array
    {
        $bytes = hash_pbkdf2(self::HASH_ALGORITHM, $password, $salt, self::PBKDF2_ITERATIONS, self::KEY_LENGTH + self::IV_LENGTH, true);
        return [substr($bytes, 0, self::KEY_LENGTH), substr($bytes, self::KEY_LENGTH, self::IV_LENGTH)];
    }
}
