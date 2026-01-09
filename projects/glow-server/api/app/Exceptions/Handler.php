<?php

namespace App\Exceptions;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Common\Notifications\ExceptionSlackNotification;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use WonderPlanet\Domain\Billing\Constants\ErrorCode as BillingErrorCode;
use WonderPlanet\Domain\Billing\Exceptions\WpBillingException;
use WonderPlanet\Domain\Currency\Constants\ErrorCode as CurrencyErrorCode;
use WonderPlanet\Domain\Currency\Exceptions\WpCurrencyException;

class Handler extends ExceptionHandler
{
    /**
     * slack通知を行わないエラーコード一覧
     * 必要に応じて追加する
     */
    private const ERROR_CODES_WITHOUT_SLACK_NOTIFICATION = [
        ErrorCode::NOT_FOUND_APPLY_ASSET_RELEASE,
        ErrorCode::ADMIN_DEBUG_FAILED,
        ErrorCode::REQUIRE_CLIENT_VERSION_UPDATE,
        ErrorCode::REQUIRE_RESOURCE_UPDATE,
        ErrorCode::AVAILABLE_ASSET_VERSION_NOT_FOUND,
        ErrorCode::CROSS_DAY,
    ];

    /**
     * エラーログへの書き込みを行わないエラーコード一覧
     * 必要に応じて追加する
     */
    private const ERROR_CODES_WITHOUT_WRITE_LOG = [
        ErrorCode::REQUIRE_CLIENT_VERSION_UPDATE,
        ErrorCode::REQUIRE_RESOURCE_UPDATE,
        ErrorCode::AVAILABLE_ASSET_VERSION_NOT_FOUND,
        ErrorCode::CROSS_DAY,
    ];

    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        // renderableに登録した順に判定されるため、
        // Exceptionを継承したクラスを対象とする場合は処理順に注意すること

        $this->renderable(function (GameException $e) {
            // $previousが格納されている場合は、それをロギングする
            //  課金基盤内での例外などが格納されている。
            if (!is_null($e->getPrevious())) {
                Log::error(
                    $e->getPrevious(),
                    ['user_id' => $this->getCurrentUserId()],
                );
            }

            return response()->json([
                'errorCode' => $e->getCode(),
                'message' => $this->createErrorMessage($e),
            ], HttpStatusCode::ERROR);
        });

        // 課金ライブラリの例外
        $this->renderable(function (WpBillingException $e) {
            $code = $this->convertBillingErrorCodeToErrorCode($e->getCode());

            return response()->json([
                'errorCode' => $code,
                'message' => $this->createErrorMessage($e),
            ], HttpStatusCode::ERROR);
        });
        $this->renderable(function (WpCurrencyException $e) {
            $code = $this->convertCurrencyErrorCodeToErrorCode($e->getCode());

            return response()->json([
                'errorCode' => $code,
                'message' => $this->createErrorMessage($e),
            ], HttpStatusCode::ERROR);
        });

        $this->renderable(function (ValidationException $e) {
            return response()->json([
                'errorCode' => ErrorCode::VALIDATION_ERROR,
                'message' => $this->createErrorMessage($e),
            ], HttpStatusCode::ERROR);
        });

        $this->renderable(function (RetryableException $e) {
            return response()->json([
                'errorCode' => ErrorCode::HTTP_ERROR,
                'message' => $this->createErrorMessage($e),
            ],HttpStatusCode::RETRYABLE_ERROR); // alias 503
        });

        $this->renderable(function (HttpException $e) {
            return response()->json([
                'errorCode' => ErrorCode::HTTP_ERROR,
                'message' => $this->createHTTPErrorMessage($e),
            ], $e->getStatusCode());
        });

        // ThrowableはすべてのExceptionの基底クラスとなるため、最後に記述する
        // そうでないとここで全てひっかかってしまう
        $this->renderable(function (\Throwable $e) {
            return response()->json([
                'errorCode' => ErrorCode::HTTP_ERROR,
                'message' => $this->createErrorMessage($e),
            ], HttpStatusCode::UNKNOWN_ERROR);
        });
    }

    public function report(\Throwable $e)
    {
        // Slack通知
        if ($e instanceof \Throwable) {
            $this->sendSlackNotification($e);
        }
        if ($this->errorLogSkipForSpecificCodesEnabled($e->getCode())) {
            // 特定のエラーコードの場合はエラーログへの書き込みをスキップ
            return;
        }

        // ユーザーIDをログコンテキストに追加
        Log::withContext(['user_id' => $this->getCurrentUserId()]);

        parent::report($e);
    }

    /**
     * @return string
     */
    private function createHTTPErrorMessage(HttpException $e)
    {
        $statusCode = $e->getStatusCode();
        switch ($statusCode) {
            case 401:
                return "{$statusCode}: Unauthorized";
            case 403:
                return "{$statusCode}: Forbidden";
            case 404:
                return "{$statusCode}: Not Found";
            case 419:
                return "{$statusCode}: Page Expired";
            case 429:
                return "{$statusCode}: Too Many Requests";
            case 500:
                return $this->createErrorMessage($e);
            case 503:
                return "{$statusCode}: Service Unavailable";
            default:
                return "{$statusCode}: Error";
        }
    }

    /**
     * @return string
     */
    private function createErrorMessage(\Throwable $e)
    {
        $message = "500: Server Error";
        if (config('app.debug')) {
            $details = $this->createErrorDetail($e);
            $message .= "\n\n" . $details;
        }
        return $message;
    }

    private function createErrorDetail(\Throwable $e): string
    {
        return get_class($e) . ": [{$e->getCode()}]: {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}\n{$e->getTraceAsString()}";
    }

    private function sendSlackNotification(\Throwable $e): void
    {
        if (!$this->slackNotificationEnabled()) {
            // Slack通知を無効にしている場合は何もしない
            return;
        }

        if ($e instanceof GameException) {
            $errorCode = $e->getCode();
            if (in_array($errorCode, self::ERROR_CODES_WITHOUT_SLACK_NOTIFICATION)) {
                // 除外対象のエラーコードはslack通知しない
                return;
            }
            $message = $this->createErrorDetail($e);
            $httpStatus = HttpStatusCode::ERROR;
        } elseif ($e instanceof WpBillingException) {
            $errorCode = $this->convertBillingErrorCodeToErrorCode($e->getCode());
            $message = $this->createErrorDetail($e);
            $httpStatus = HttpStatusCode::ERROR;
        } elseif ($e instanceof WpCurrencyException) {
            $errorCode = $this->convertCurrencyErrorCodeToErrorCode($e->getCode());
            $message = $this->createErrorDetail($e);
            $httpStatus = HttpStatusCode::ERROR;
        } elseif ($e instanceof ValidationException) {
            $errorCode = ErrorCode::VALIDATION_ERROR;
            $message = $this->createErrorDetail($e);
            $httpStatus = HttpStatusCode::ERROR;
        } elseif ($e instanceof RetryableException) {
            $errorCode = ErrorCode::HTTP_ERROR;
            $message = $this->createErrorDetail($e);
            $httpStatus = HttpStatusCode::RETRYABLE_ERROR;
        } elseif ($e instanceof HttpException) {
            $errorCode = ErrorCode::HTTP_ERROR;
            $message = $this->createHTTPErrorMessage($e);
            $httpStatus = HttpStatusCode::ERROR;
        } else {
            $errorCode = ErrorCode::HTTP_ERROR;
            $message = $this->createErrorMessage($e);
            $httpStatus = HttpStatusCode::UNKNOWN_ERROR;
        }

        $message = "*env:* " . config('app.env') . "\n" .
            "*error_code:* {$errorCode}\n" .
            "*http_status:* {$httpStatus}\n" .
            "*message:* \n``` $message ```";
        try {
            // エラーの無限ループを避けるためにtry-catchで囲む
            Notification::route('slack', config('services.slack.webhook_url'))
                ->notify(new ExceptionSlackNotification($message));
        } catch (\Throwable $e) {
            // Slack通知に失敗した場合は、ログに出力する
            Log::error('', [$e]);
        }
    }

    private function convertBillingErrorCodeToErrorCode(int $code): int
    {
        // 特定のエラーはプロダクト側のエラーに変換する
        // 次のエラーはAPI側では扱わないため、UNKNOWN_ERRORに変換する
        //   - UNSUPPORTED_RECEIPT
        //   - USR_STORE_PRODUCT_HISTORY_NOT_FOUND
        //   - UNMATCHED_USR_STORE_PRODUCT_HISTORY_USER_ID
        return match ($code) {
            BillingErrorCode::SHOP_INFO_NOT_FOUND => ErrorCode::BILLING_SHOP_INFO_NOT_FOUND,
            BillingErrorCode::INVALID_RECEIPT => ErrorCode::BILLING_VERIFY_RECEIPT_INVALID_RECEIPT,
            BillingErrorCode::INVALID_ENVIRONMENT => ErrorCode::BILLING_INVALID_ENVIRONMENT,
            BillingErrorCode::UNSUPPORTED_BILLING_PLATFORM => ErrorCode::BILLING_UNSUPPORTED_BILLING_PLATFORM,
            BillingErrorCode::INVALID_ALLOWANCE => ErrorCode::BILLING_INVALID_ALLOWANCE,
            BillingErrorCode::DUPLICATE_RECEIPT_UNIQUE_ID => ErrorCode::BILLING_VERIFY_RECEIPT_DUPLICATE_RECEIPT,
            BillingErrorCode::BILLING_TRANSACTION_END => ErrorCode::BILLING_TRANSACTION_END,
            BillingErrorCode::ALLOWANCE_AND_OPR_PRODUCT_NOT_MATCH => ErrorCode::BILLING_ALLOWANCE_AND_OPR_PRODUCT_NOT_MATCH,
            BillingErrorCode::ALLOWANCE_AND_MST_STORE_PRODUCT_NOT_MATCH => ErrorCode::BILLING_ALLOWANCE_AND_MST_STORE_PRODUCT_NOT_MATCH,
            BillingErrorCode::OPR_PRODUCT_NOT_FOUND => ErrorCode::MST_NOT_FOUND,
            BillingErrorCode::MST_STORE_PRODUCT_NOT_FOUND => ErrorCode::MST_NOT_FOUND,
            BillingErrorCode::APPSTORE_RESPONSE_STATUS_NOT_OK => ErrorCode::BILLING_APPSTORE_RESPONSE_STATUS_NOT_OK,
            BillingErrorCode::APPSTORE_BUNDLE_ID_NOT_MATCH => ErrorCode::BILLING_APPSTORE_BUNDLE_ID_NOT_MATCH,
            BillingErrorCode::APPSTORE_BUNDLE_ID_NOT_SET => ErrorCode::BILLING_APPSTORE_BUNDLE_ID_NOT_SET,
            BillingErrorCode::GOOGLEPLAY_RECEIPT_STATUS_CANCELED => ErrorCode::BILLING_GOOGLEPLAY_RECEIPT_STATUS_CANCELED,
            BillingErrorCode::GOOGLEPLAY_RECEIPT_STATUS_PENDING => ErrorCode::BILLING_GOOGLEPLAY_RECEIPT_STATUS_PENDING,
            BillingErrorCode::GOOGLEPLAY_RECEIPT_STATUS_OTHER => ErrorCode::BILLING_GOOGLEPLAY_RECEIPT_STATUS_OTHER,
            default => ErrorCode::BILLING_UNKNOWN_ERROR,
        };
    }

    private function convertCurrencyErrorCodeToErrorCode(int $code): int
    {
        // 特定のエラーはプロダクト側のエラーに変換する
        // 次のエラーはAPI側では扱わないため、UNKNOWN_ERRORに変換する
        //   - NOT_FOUND_PAID_CURRENCY
        //   - USR_CURRENCY_PAID_NOT_FOUND
        //   - FAILED_TO_REVERT_CURRENCY_BY_OVER_PURCHASE_AMOUNT
        //   - FAILED_TO_REVERT_CURRENCY_BY_NOT_MATCH_SEQ_NO
        return match ($code) {
            CurrencyErrorCode::NOT_ENOUGH_PAID_CURRENCY => ErrorCode::CURRENCY_NOT_ENOUGH_PAID_CURRENCY,
            CurrencyErrorCode::NOT_ENOUGH_CURRENCY => ErrorCode::CURRENCY_NOT_ENOUGH_CURRENCY,
            CurrencyErrorCode::NOT_FOUND_FREE_CURRENCY => ErrorCode::CURRENCY_NOT_FOUND_FREE_CURRENCY,
            CurrencyErrorCode::NOT_FOUND_CURRENCY_SUMMARY => ErrorCode::CURRENCY_NOT_FOUND_CURRENCY_SUMMARY,
            CurrencyErrorCode::FAILED_TO_ADD_PAID_CURRENCY_BY_ZERO => ErrorCode::CURRENCY_FAILED_TO_ADD_PAID_CURRENCY_BY_ZERO,
            CurrencyErrorCode::ADD_CURRENCY_BY_OVER_MAX => ErrorCode::CURRENCY_ADD_CURRENCY_BY_OVER_MAX,
            CurrencyErrorCode::ADD_FREE_CURRENCY_BY_OVER_MAX => ErrorCode::CURRENCY_ADD_FREE_CURRENCY_BY_OVER_MAX,
            CurrencyErrorCode::ADD_PAID_CURRENCY_BY_OVER_MAX => ErrorCode::CURRENCY_ADD_PAID_CURRENCY_BY_OVER_MAX,
            CurrencyErrorCode::INVALID_DEBUG_ENVIRONMENT => ErrorCode::CURRENCY_INVALID_DEBUG_ENVIRONMENT,
            CurrencyErrorCode::UNKNOWN_BILLING_PLATFORM => ErrorCode::CURRENCY_UNKNOWN_BILLING_PLATFORM,
            default => ErrorCode::CURRENCY_UNKNOWN_ERROR,
        };
    }

    /**
     * 特定のエラーコードの場合はエラーログへの書き込みをスキップするかどうか
     * @param int|string $errorCode
     * @return bool
     */
    private function errorLogSkipForSpecificCodesEnabled(int|string $errorCode): bool
    {
        return env('ENABLE_ERROR_LOG_SKIP_FOR_SPECIFIC_CODES', false)
            && in_array($errorCode, self::ERROR_CODES_WITHOUT_WRITE_LOG);
    }

    /**
     * Slack通知を有効にするかどうか
     * 環境変数 ENABLE_SLACK_ERROR_NOTIFICATION が true の場合に有効
     * @return bool
     */
    private function slackNotificationEnabled(): bool
    {
        return env('ENABLE_SLACK_ERROR_NOTIFICATION', false);
    }

    /**
     * 現在のユーザーIDを取得する
     * @return string
     */
    private function getCurrentUserId(): string
    {
        $user = auth()->user();
        if ($user instanceof CurrentUser) {
            return $user->getUsrUserId();
        }

        return '';
    }
}
