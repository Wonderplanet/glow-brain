<?php

namespace Tests\Unit\Exceptions;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Exceptions\Handler;
use App\Exceptions\HttpStatusCode;
use App\Exceptions\RetryableException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;
use WonderPlanet\Domain\Billing\Constants\ErrorCode as BillingErrorCode;
use WonderPlanet\Domain\Billing\Exceptions\WpBillingException;
use WonderPlanet\Domain\Currency\Constants\ErrorCode as CurrencyErrorCode;
use WonderPlanet\Domain\Currency\Exceptions\WpCurrencyException;

class HandlerTest extends TestCase
{
    private $handler;
    private $request;

    protected function setUp(): void
    {
        parent::setUp();
        // テスト時はdebugモードをオフにして、スタックトレースを表示しない
        Config::set('app.debug', false);
        $this->handler = new Handler(app()->make('Illuminate\Contracts\Container\Container'));
        $this->request = Request::create('/', 'GET');
    }

    public function test_HttpExceptionの場合_対応するStatusCodeと簡易的なメッセージを返却する()
    {
        $exception = new NotFoundHttpException();
        $response = $this->handler->render($this->request, $exception);

        $expectedJson = ['errorCode' => ErrorCode::HTTP_ERROR, 'message' => '404: Not Found'];
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode($expectedJson), $response->getContent());
    }

    public function test_GameException_debugモードoff_StatusCode299と指定されたErrorCodeを返却する()
    {
        $exception = new GameException(ErrorCode::REQUIRE_CLIENT_VERSION_UPDATE, "error_message");
        $response = $this->handler->render($this->request, $exception);

        $expectedJson = ['errorCode' => ErrorCode::REQUIRE_CLIENT_VERSION_UPDATE, 'message' => '500: Server Error'];
        $this->assertEquals(HttpStatusCode::ERROR, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode($expectedJson), $response->getContent());
    }

    public function test_GameException_debugモードon_StatusCode299と詳細情報を返却する()
    {
        Config::set('app.debug', true);
        $exception = new GameException(ErrorCode::REQUIRE_CLIENT_VERSION_UPDATE, "error_message");
        $response = $this->handler->render($this->request, $exception);

        $json = json_decode($response->getContent());

        $this->assertEquals(HttpStatusCode::ERROR, $response->getStatusCode());
        $this->assertEquals(ErrorCode::REQUIRE_CLIENT_VERSION_UPDATE, $json->errorCode);
        $this->assertStringContainsString('500: Server Error', $json->message);
        $this->assertStringContainsString('GameException: [' . ErrorCode::REQUIRE_CLIENT_VERSION_UPDATE . ']: error_message', $json->message);
    }

    public function test_RetryableException_debugモードoff_StatusCode503を返却する()
    {
        $exception = new RetryableException("error_message");
        $response = $this->handler->render($this->request, $exception);

        $expectedJson = ['errorCode' => ErrorCode::HTTP_ERROR, 'message' => '500: Server Error'];
        $this->assertEquals(HttpStatusCode::RETRYABLE_ERROR, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode($expectedJson), $response->getContent());
    }

    public function test_RetryableException_debugモードon_StatusCode503と詳細情報を返却する()
    {
        Config::set('app.debug', true);
        $exception = new RetryableException("error_message");
        $response = $this->handler->render($this->request, $exception);

        $json = json_decode($response->getContent());

        $this->assertEquals(HttpStatusCode::RETRYABLE_ERROR, $response->getStatusCode());
        $this->assertEquals(ErrorCode::HTTP_ERROR, $json->errorCode);
        $this->assertStringContainsString('500: Server Error', $json->message);
        $this->assertStringContainsString('RetryableException: [0]: error_message', $json->message);
    }

    public function test_ValidationException_debugモードoff_StatusCode299とVALIDATION_ERRORコードを返却する()
    {
        $exception = ValidationException::withMessages(["error_message"]);
        $response = $this->handler->render($this->request, $exception);

        $expectedJson = ['errorCode' => ErrorCode::VALIDATION_ERROR, 'message' => '500: Server Error'];
        $this->assertEquals(HttpStatusCode::ERROR, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode($expectedJson), $response->getContent());
    }

    public function test_ValidationException_debugモードon_StatusCode299と詳細情報を返却する()
    {
        Config::set('app.debug', true);
        $exception = ValidationException::withMessages(["error_message"]);
        $response = $this->handler->render($this->request, $exception);

        $json = json_decode($response->getContent());

        $this->assertEquals(HttpStatusCode::ERROR, $response->getStatusCode());
        $this->assertEquals(ErrorCode::VALIDATION_ERROR, $json->errorCode);
        $this->assertStringContainsString('500: Server Error', $json->message);
        $this->assertStringContainsString('ValidationException', $json->message);
    }

    public function test_予期しないException_StatusCode500とスタックトレース情報を返却する()
    {
        // このテストではdebugモードをオンにしてスタックトレースを表示する
        Config::set('app.debug', true);
        $exception = new \Exception("error_message");
        $response = $this->handler->render($this->request, $exception);

        $json = json_decode($response->getContent());

        $this->assertEquals(HttpStatusCode::UNKNOWN_ERROR, $response->getStatusCode());
        $this->assertEquals(ErrorCode::HTTP_ERROR, $json->errorCode);
        $this->assertStringContainsString('500: Server Error', $json->message);
        $this->assertStringContainsString('Exception: [0]: error_message', $json->message);
    }

    public function test_HttpException503_StatusCode500とスタックトレース情報を返却する()
    {
        // このテストではdebugモードをオンにしてスタックトレースを表示する
        Config::set('app.debug', true);
        $exception = new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, "error_message");
        $response = $this->handler->render($this->request, $exception);

        $json = json_decode($response->getContent());

        $this->assertEquals(HttpStatusCode::UNKNOWN_ERROR, $response->getStatusCode());
        $this->assertEquals(ErrorCode::HTTP_ERROR, $json->errorCode);
        $this->assertStringContainsString('500: Server Error', $json->message);
        $this->assertStringContainsString('Symfony\Component\HttpKernel\Exception\HttpException: [0]: error_message', $json->message);
    }

    public function test_debugフラグfalse_スタックトレース情報を返却しない()
    {
        Config::set('app.debug', false);
        $exception = new \Exception("error_message");
        $response = $this->handler->render($this->request, $exception);

        $expectedJson = ['errorCode' => ErrorCode::HTTP_ERROR, 'message' => '500: Server Error'];
        $this->assertEquals(HttpStatusCode::UNKNOWN_ERROR, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode($expectedJson), $response->getContent());
    }

    public static function WpBillingExceptionData(): array
    {
        return [
            [BillingErrorCode::SHOP_INFO_NOT_FOUND, ErrorCode::BILLING_SHOP_INFO_NOT_FOUND],
            [BillingErrorCode::INVALID_RECEIPT, ErrorCode::BILLING_VERIFY_RECEIPT_INVALID_RECEIPT],
            [BillingErrorCode::INVALID_ENVIRONMENT, ErrorCode::BILLING_INVALID_ENVIRONMENT],
            [BillingErrorCode::UNSUPPORTED_BILLING_PLATFORM, ErrorCode::BILLING_UNSUPPORTED_BILLING_PLATFORM],
            [BillingErrorCode::UNSUPPORTED_RECEIPT, ErrorCode::BILLING_UNKNOWN_ERROR],
            [BillingErrorCode::INVALID_ALLOWANCE, ErrorCode::BILLING_INVALID_ALLOWANCE],
            [BillingErrorCode::DUPLICATE_RECEIPT_UNIQUE_ID, ErrorCode::BILLING_VERIFY_RECEIPT_DUPLICATE_RECEIPT],
            [BillingErrorCode::USR_STORE_PRODUCT_HISTORY_NOT_FOUND, ErrorCode::BILLING_UNKNOWN_ERROR],
            [BillingErrorCode::UNMATCHED_USR_STORE_PRODUCT_HISTORY_USER_ID, ErrorCode::BILLING_UNKNOWN_ERROR],
            [BillingErrorCode::ALLOWANCE_AND_OPR_PRODUCT_NOT_MATCH, ErrorCode::BILLING_ALLOWANCE_AND_OPR_PRODUCT_NOT_MATCH],
            [BillingErrorCode::ALLOWANCE_AND_MST_STORE_PRODUCT_NOT_MATCH, ErrorCode::BILLING_ALLOWANCE_AND_MST_STORE_PRODUCT_NOT_MATCH],
            [BillingErrorCode::OPR_PRODUCT_NOT_FOUND, ErrorCode::MST_NOT_FOUND],
            [BillingErrorCode::MST_STORE_PRODUCT_NOT_FOUND, ErrorCode::MST_NOT_FOUND],
            [BillingErrorCode::APPSTORE_RESPONSE_STATUS_NOT_OK, ErrorCode::BILLING_APPSTORE_RESPONSE_STATUS_NOT_OK],
            [BillingErrorCode::APPSTORE_BUNDLE_ID_NOT_MATCH, ErrorCode::BILLING_APPSTORE_BUNDLE_ID_NOT_MATCH],
            [BillingErrorCode::APPSTORE_BUNDLE_ID_NOT_SET, ErrorCode::BILLING_APPSTORE_BUNDLE_ID_NOT_SET],
            [BillingErrorCode::GOOGLEPLAY_RECEIPT_STATUS_CANCELED, ErrorCode::BILLING_GOOGLEPLAY_RECEIPT_STATUS_CANCELED],
            [BillingErrorCode::GOOGLEPLAY_RECEIPT_STATUS_PENDING, ErrorCode::BILLING_GOOGLEPLAY_RECEIPT_STATUS_PENDING],
            [BillingErrorCode::GOOGLEPLAY_RECEIPT_STATUS_OTHER, ErrorCode::BILLING_GOOGLEPLAY_RECEIPT_STATUS_OTHER],
        ];
    }

    /**
     * @dataProvider WpBillingExceptionData
     */
    public function test_WpBillingException_debugモードoff_対応するエラーコードにマッピングされる($code, $expected)
    {
        $exception = new WpBillingException("error_message", $code);
        $response = $this->handler->render($this->request, $exception);

        $expectedJson = ['errorCode' => $expected, 'message' => '500: Server Error'];
        $this->assertEquals(HttpStatusCode::ERROR, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode($expectedJson), $response->getContent());
    }

    public function test_WpBillingException_debugモードon_詳細情報を返却する()
    {
        Config::set('app.debug', true);
        $exception = new WpBillingException("error_message", BillingErrorCode::SHOP_INFO_NOT_FOUND);
        $response = $this->handler->render($this->request, $exception);

        $json = json_decode($response->getContent());

        $this->assertEquals(HttpStatusCode::ERROR, $response->getStatusCode());
        $this->assertEquals(ErrorCode::BILLING_SHOP_INFO_NOT_FOUND, $json->errorCode);
        $this->assertStringContainsString('500: Server Error', $json->message);
        $this->assertStringContainsString('WpBillingException', $json->message);
        $this->assertStringContainsString('Billing-' . BillingErrorCode::SHOP_INFO_NOT_FOUND . ': error_message', $json->message);
    }

    public static function WpCurrencyExceptionData(): array
    {
        return [
            [CurrencyErrorCode::NOT_ENOUGH_PAID_CURRENCY, ErrorCode::CURRENCY_NOT_ENOUGH_PAID_CURRENCY],
            [CurrencyErrorCode::NOT_ENOUGH_CURRENCY, ErrorCode::CURRENCY_NOT_ENOUGH_CURRENCY],
            [CurrencyErrorCode::NOT_FOUND_FREE_CURRENCY, ErrorCode::CURRENCY_NOT_FOUND_FREE_CURRENCY],
            [CurrencyErrorCode::NOT_FOUND_CURRENCY_SUMMARY, ErrorCode::CURRENCY_NOT_FOUND_CURRENCY_SUMMARY],
            [CurrencyErrorCode::NOT_FOUND_PAID_CURRENCY, ErrorCode::CURRENCY_UNKNOWN_ERROR],
            [CurrencyErrorCode::FAILED_TO_ADD_PAID_CURRENCY_BY_ZERO, ErrorCode::CURRENCY_FAILED_TO_ADD_PAID_CURRENCY_BY_ZERO],
            [CurrencyErrorCode::ADD_CURRENCY_BY_OVER_MAX, ErrorCode::CURRENCY_ADD_CURRENCY_BY_OVER_MAX],
            [CurrencyErrorCode::USR_CURRENCY_PAID_NOT_FOUND, ErrorCode::CURRENCY_UNKNOWN_ERROR],
            [CurrencyErrorCode::FAILED_TO_REVERT_CURRENCY_BY_OVER_PURCHASE_AMOUNT, ErrorCode::CURRENCY_UNKNOWN_ERROR],
            [CurrencyErrorCode::FAILED_TO_REVERT_CURRENCY_BY_NOT_MATCH_SEQ_NO, ErrorCode::CURRENCY_UNKNOWN_ERROR],
            [CurrencyErrorCode::INVALID_DEBUG_ENVIRONMENT, ErrorCode::CURRENCY_INVALID_DEBUG_ENVIRONMENT],
            [CurrencyErrorCode::UNKNOWN_BILLING_PLATFORM, ErrorCode::CURRENCY_UNKNOWN_BILLING_PLATFORM],
            [CurrencyErrorCode::ADD_FREE_CURRENCY_BY_OVER_MAX, ErrorCode::CURRENCY_ADD_FREE_CURRENCY_BY_OVER_MAX],
            [CurrencyErrorCode::ADD_PAID_CURRENCY_BY_OVER_MAX, ErrorCode::CURRENCY_ADD_PAID_CURRENCY_BY_OVER_MAX],
        ];
    }

    /**
     * @dataProvider WpCurrencyExceptionData
     */
    public function test_WpCurrencyException_debugモードoff_対応するエラーコードにマッピングされる($code, $expected)
    {
        $exception = new WpCurrencyException("error_message", $code);
        $response = $this->handler->render($this->request, $exception);

        $expectedJson = ['errorCode' => $expected, 'message' => '500: Server Error'];
        $this->assertEquals(HttpStatusCode::ERROR, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode($expectedJson), $response->getContent());
    }

    public function test_WpCurrencyException_debugモードon_詳細情報を返却する()
    {
        Config::set('app.debug', true);
        $exception = new WpCurrencyException("error_message", CurrencyErrorCode::NOT_ENOUGH_PAID_CURRENCY);
        $response = $this->handler->render($this->request, $exception);

        $json = json_decode($response->getContent());

        $this->assertEquals(HttpStatusCode::ERROR, $response->getStatusCode());
        $this->assertEquals(ErrorCode::CURRENCY_NOT_ENOUGH_PAID_CURRENCY, $json->errorCode);
        $this->assertStringContainsString('500: Server Error', $json->message);
        $this->assertStringContainsString('WpCurrencyException', $json->message);
        $this->assertStringContainsString('Currency-' . CurrencyErrorCode::NOT_ENOUGH_PAID_CURRENCY . ': error_message', $json->message);
    }
}
