<?php

namespace Tests\Feature\Domain\Auth;

use App\Domain\Auth\Services\IdTokenService;
use Firebase\JWT\JWT;
use Tests\TestCase;

class IdTokenServiceTest extends TestCase
{
    private const DUMMY_UUID = '00000000-0000-0000-0000-000000000000';

    private IdTokenService $idTokenService;

    public function setUp(): void
    {
        parent::setUp();
        $this->idTokenService = $this->app->make(IdTokenService::class);
    }

    /**
     * @test
     */
    public function create_正常なJWTのフォーマットでIDトークンが生成される()
    {
        // Exercise
        $result = $this->idTokenService->create(self::DUMMY_UUID);

        // Verify
        [$header, $payload, $signature] = explode('.', $result);

        // 必要な要素が含まれてるかの検証
        $this->assertNotEmpty($header);
        $this->assertNotEmpty($payload);
        $this->assertNotEmpty($signature);

        // ヘッダー部分の検証
        $decodedHeader = JWT::urlsafeB64Decode($header);
        $this->assertEquals('{"typ":"JWT","alg":"RS256"}', $decodedHeader);

        // ペイロード部分の検証
        $decodedPayload = JWT::urlsafeB64Decode($payload);
        $this->assertEquals('{"uuid":"00000000-0000-0000-0000-000000000000"}', $decodedPayload);

        // 署名部分の検証
        $rawSignature = JWT::sign(
            msg: "${header}.${payload}",
            key: config('encryption.jwt_private_key'),
            alg: 'RS256',
        );
        $expectedSignature = JWT::urlsafeB64Encode($rawSignature);
        $this->assertEquals($expectedSignature, $signature);
    }

    /**
     * @test
     */
    public function getUuid_IDトークンを正常に復号できる()
    {
        // Setup
        $idToken = $this->idTokenService->create(self::DUMMY_UUID);

        // Exercise
        $result = $this->idTokenService->getUuid($idToken);

        // Verify
        $this->assertEquals(self::DUMMY_UUID, $result);
    }
}
