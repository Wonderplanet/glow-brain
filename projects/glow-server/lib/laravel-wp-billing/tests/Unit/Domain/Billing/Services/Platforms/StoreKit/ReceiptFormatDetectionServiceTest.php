<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Billing\Services\Platforms\StoreKit;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Billing\Services\Platforms\StoreKit\ReceiptFormatDetectionService;

/**
 * ReceiptFormatDetectionService のテスト
 */
class ReceiptFormatDetectionServiceTest extends TestCase
{
    private ReceiptFormatDetectionService $receiptFormatDetectionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->receiptFormatDetectionService = new ReceiptFormatDetectionService();
    }

    #[Test]
    public function detectReceiptType_StoreKit2のJWSトークン形式を検出()
    {
        // Setup - 実際のStoreKit2 JWS構造をシミュレート
        $headerData = [
            'alg' => 'ES256',
            'typ' => 'JWT',
            'x5c' => ['MIICertificate...']
        ];
        $payloadData = [
            'transactionId' => '123456789',
            'productId' => 'test.product'
        ];

        // 正しいBase64URLエンコーディング
        $header = rtrim(strtr(base64_encode(json_encode($headerData)), '+/', '-_'), '=');
        $payload = rtrim(strtr(base64_encode(json_encode($payloadData)), '+/', '-_'), '=');
        $signature = rtrim(strtr(base64_encode('mock_signature_data'), '+/', '-_'), '=');

        $jwsToken = "{$header}.{$payload}.{$signature}";

        // Execute
        $result = $this->receiptFormatDetectionService->detectReceiptType($jwsToken);

        // Verify
        $this->assertEquals(ReceiptFormatDetectionService::RECEIPT_TYPE_STOREKIT2, $result);
    }

    #[Test]
    public function detectReceiptType_StoreKit1のJSON形式を検出()
    {
        // Setup - StoreKit1のJSON形式
        $jsonReceipt = json_encode([
            'Store' => 'AppleAppStore',
            'TransactionID' => '1000000123456789',
            'Payload' => 'base64encodeddata...'
        ]);

        // Execute
        $result = $this->receiptFormatDetectionService->detectReceiptType($jsonReceipt);

        // Verify
        $this->assertEquals(ReceiptFormatDetectionService::RECEIPT_TYPE_STOREKIT1, $result);
    }

    #[Test]
    public function detectReceiptType_不明な形式の場合()
    {
        // Setup
        $unknownReceipt = 'invalid receipt format';

        // Execute
        $result = $this->receiptFormatDetectionService->detectReceiptType($unknownReceipt);

        // Verify
        $this->assertEquals(ReceiptFormatDetectionService::RECEIPT_TYPE_UNKNOWN, $result);
    }

    #[Test]
    public function detectReceiptType_空文字列の場合()
    {
        // Execute
        $result = $this->receiptFormatDetectionService->detectReceiptType('');

        // Verify
        $this->assertEquals(ReceiptFormatDetectionService::RECEIPT_TYPE_UNKNOWN, $result);
    }

    #[Test]
    public function detectReceiptType_不正なJWS形式_パート数が不足()
    {
        // Setup - JWSの必要な3パートが不足
        $invalidJws = 'header.payload'; // signatureがない

        // Execute
        $result = $this->receiptFormatDetectionService->detectReceiptType($invalidJws);

        // Verify
        $this->assertEquals(ReceiptFormatDetectionService::RECEIPT_TYPE_UNKNOWN, $result);
    }

    #[Test]
    public function detectReceiptType_不正なJWS形式_無効なBase64URL()
    {
        // Setup - 無効な文字を含むBase64URL
        $invalidJws = 'invalid+char.invalid/char.invalid=char';

        // Execute
        $result = $this->receiptFormatDetectionService->detectReceiptType($invalidJws);

        // Verify
        $this->assertEquals(ReceiptFormatDetectionService::RECEIPT_TYPE_UNKNOWN, $result);
    }

    #[Test]
    public function detectReceiptType_不正なJWS形式_ヘッダーがJSON以外()
    {
        // Setup - ヘッダー部分が有効なJSONでない
        $invalidHeader = base64_encode('invalid json');
        $validPayload = base64_encode(json_encode(['test' => 'data']));
        $signature = 'signature';
        $invalidJws = "{$invalidHeader}.{$validPayload}.{$signature}";

        // Execute
        $result = $this->receiptFormatDetectionService->detectReceiptType($invalidJws);

        // Verify
        $this->assertEquals(ReceiptFormatDetectionService::RECEIPT_TYPE_UNKNOWN, $result);
    }

    #[Test]
    public function detectReceiptType_JWSにalgフィールドが不足()
    {
        // Setup - algフィールドがないヘッダー
        $headerWithoutAlg = base64_encode(json_encode([
            'typ' => 'JWT',
            'x5c' => ['cert']
        ]));
        $payload = base64_encode(json_encode(['test' => 'data']));
        $signature = 'signature';
        $jwsWithoutAlg = "{$headerWithoutAlg}.{$payload}.{$signature}";

        // Execute
        $result = $this->receiptFormatDetectionService->detectReceiptType($jwsWithoutAlg);

        // Verify
        $this->assertEquals(ReceiptFormatDetectionService::RECEIPT_TYPE_UNKNOWN, $result);
    }

    #[Test]
    public function detectReceiptType_JWSにx5cフィールドが不足()
    {
        // Setup - x5cフィールドがないヘッダー
        $headerWithoutX5c = base64_encode(json_encode([
            'alg' => 'ES256',
            'typ' => 'JWT'
        ]));
        $payload = base64_encode(json_encode(['test' => 'data']));
        $signature = 'signature';
        $jwsWithoutX5c = "{$headerWithoutX5c}.{$payload}.{$signature}";

        // Execute
        $result = $this->receiptFormatDetectionService->detectReceiptType($jwsWithoutX5c);

        // Verify
        $this->assertEquals(ReceiptFormatDetectionService::RECEIPT_TYPE_UNKNOWN, $result);
    }

    #[Test]
    public function detectReceiptType_JWSでアルゴリズムがES256以外()
    {
        // Setup - ES256以外のアルゴリズム
        $headerWithRS256 = base64_encode(json_encode([
            'alg' => 'RS256',
            'typ' => 'JWT',
            'x5c' => ['cert']
        ]));
        $payload = base64_encode(json_encode(['test' => 'data']));
        $signature = 'signature';
        $jwsWithRS256 = "{$headerWithRS256}.{$payload}.{$signature}";

        // Execute
        $result = $this->receiptFormatDetectionService->detectReceiptType($jwsWithRS256);

        // Verify
        $this->assertEquals(ReceiptFormatDetectionService::RECEIPT_TYPE_UNKNOWN, $result);
    }

    #[Test]
    public function detectReceiptType_不正なJSON形式()
    {
        // Setup - 無効なJSON
        $invalidJson = '{"invalid": json}';

        // Execute
        $result = $this->receiptFormatDetectionService->detectReceiptType($invalidJson);

        // Verify
        $this->assertEquals(ReceiptFormatDetectionService::RECEIPT_TYPE_UNKNOWN, $result);
    }

    #[Test]
    public function detectReceiptType_JSONだがStoreKit1の必須フィールドが不足()
    {
        // Setup - 有効なJSONだがStoreKit1の構造ではない
        $jsonWithoutRequiredFields = json_encode([
            'some_field' => 'some_value',
            'other_field' => 'other_value'
        ]);

        // Execute
        $result = $this->receiptFormatDetectionService->detectReceiptType($jsonWithoutRequiredFields);

        // Verify
        $this->assertEquals(ReceiptFormatDetectionService::RECEIPT_TYPE_UNKNOWN, $result);
    }

    #[Test]
    public function detectReceiptType_StoreKit1のStoreフィールドが不正()
    {
        // Setup - Storeフィールドが'AppleAppStore'以外
        $jsonWithWrongStore = json_encode([
            'Store' => 'GooglePlay',
            'TransactionID' => '123456789',
            'Payload' => 'payload'
        ]);

        // Execute
        $result = $this->receiptFormatDetectionService->detectReceiptType($jsonWithWrongStore);

        // Verify
        $this->assertEquals(ReceiptFormatDetectionService::RECEIPT_TYPE_UNKNOWN, $result);
    }
}
