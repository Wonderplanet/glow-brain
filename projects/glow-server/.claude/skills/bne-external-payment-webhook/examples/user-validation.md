# ユーザ情報取得ウェブフック実装例

## 概要

ユーザーがWebStoreにログインした時に呼び出されるウェブフックの実装例です。

## リクエスト例

```json
{
  "notification_type": "web_store_user_validation",
  "user": {
    "user_id": "V2HSRCGHNntfJZHXVUJtnctA",
    "mbid": "100000001",
    "person_id": "pi00.7BwKu22uUY6jWspri5OsVA--",
    "language": "ja"
  },
  "custom_parameters": {
    "user_ip": "192.168.1.1",
    "store_code": "web-store-jp"
  }
}
```

## レスポンス例

```json
{
  "user": {
    "id": "user_display_id_12345",
    "internal_id": "1001",
    "name": "PlayerName",
    "level": 50,
    "birthday": "19900515",
    "country": "JP",
    "currency": "JPY"
  }
}
```

## 実装例

### 1. Controller

**ファイルパス**: `api/app/Http/Controllers/WebStore/WebhookController.php`

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\WebStore;

use App\Domain\WebStore\UseCases\HandleUserValidationUseCase;
use App\Http\Controllers\Controller;
use App\Http\ResponseFactories\WebStoreWebhookResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class WebhookController extends Controller
{
    public function __construct(
        private WebStoreWebhookResponseFactory $responseFactory,
    ) {
    }

    public function handleUserValidation(
        HandleUserValidationUseCase $useCase,
        Request $request
    ): JsonResponse {
        try {
            // バリデーション
            $validated = $request->validate([
                'notification_type' => 'required|in:web_store_user_validation',
                'user' => 'required|array',
                'user.user_id' => 'required|string',
                'user.mbid' => 'required|string',
                'user.person_id' => 'required|string',
                'user.language' => 'required|string',
                'custom_parameters' => 'required|array',
                'custom_parameters.user_ip' => 'required|ip',
                'custom_parameters.store_code' => 'required|string',
            ]);

            // UseCaseを実行
            $result = $useCase(
                $validated['user']['user_id'],
                $validated['user']['mbid'],
                $validated['user']['person_id'],
                $validated['user']['language'],
                $validated['custom_parameters']['user_ip'],
                $validated['custom_parameters']['store_code']
            );

            return $this->responseFactory->createUserValidationResponse($result);

        } catch (ValidationException $e) {
            return $this->responseFactory->createInvalidParameterError($e->getMessage());
        } catch (\Exception $e) {
            \Log::error('WebStore UserValidation webhook error', [
                'user_id' => $request->input('user.user_id'),
                'error' => $e->getMessage(),
            ]);
            return $this->responseFactory->createServerError();
        }
    }
}
```

### 2. UseCase

**ファイルパス**: `api/app/Domain/WebStore/UseCases/HandleUserValidationUseCase.php`

```php
<?php

declare(strict_types=1);

namespace App\Domain\WebStore\UseCases;

use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\WebStore\Exceptions\UserNotFoundException;
use App\Domain\WebStore\ValueObjects\UserValidationResult;

class HandleUserValidationUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function __invoke(
        string $bnidUserId,
        string $mbid,
        string $personId,
        string $language,
        string $userIp,
        string $storeCode
    ): UserValidationResult {
        // ユーザー取得（BNIDで検索）
        $user = $this->userRepository->findByBnid($bnidUserId);

        if (!$user) {
            throw new UserNotFoundException("User not found: {$bnidUserId}");
        }

        // ユーザー情報をValue Objectに変換
        return new UserValidationResult(
            id: $user->usr_search_id ?? '',  // 表示用ID
            internalId: (string)$user->usr_user_id,  // 内部ID
            name: $user->usr_user_name ?? '',
            level: $user->usr_level ?? 1,
            birthday: $user->usr_birthday ?? '',  // YYYYMMDD形式
            country: $user->usr_country_code ?? '',  // ISO 3166-1 alpha-2
            currency: $user->usr_currency_code ?? '',  // ISO 4217
        );
    }
}
```

### 3. Value Object

**ファイルパス**: `api/app/Domain/WebStore/ValueObjects/UserValidationResult.php`

```php
<?php

declare(strict_types=1);

namespace App\Domain\WebStore\ValueObjects;

readonly class UserValidationResult
{
    public function __construct(
        public string $id,
        public string $internalId,
        public string $name,
        public int $level,
        public string $birthday,
        public string $country,
        public string $currency,
    ) {
    }
}
```

### 4. ResponseFactory

**ファイルパス**: `api/app/Http/ResponseFactories/WebStoreWebhookResponseFactory.php`

```php
<?php

declare(strict_types=1);

namespace App\Http\ResponseFactories;

use App\Domain\WebStore\ValueObjects\UserValidationResult;
use Illuminate\Http\JsonResponse;

class WebStoreWebhookResponseFactory
{
    public function createUserValidationResponse(UserValidationResult $result): JsonResponse
    {
        return response()->json([
            'user' => [
                'id' => $result->id,
                'internal_id' => $result->internalId,
                'name' => $result->name,
                'level' => $result->level,
                'birthday' => $result->birthday,
                'country' => $result->country,
                'currency' => $result->currency,
            ]
        ], 200);
    }

    // エラーレスポンスメソッドは guides/error-handling.md 参照
}
```

### 5. ルーティング

**ファイルパス**: `api/routes/api.php`

```php
Route::prefix('webstore/webhooks')->group(function () {
    Route::post('/user-validation', [WebhookController::class, 'handleUserValidation'])
        ->middleware('xsolla.signature');
});
```

## webstore-platform-integrationスキルとの連携

国コード・通貨コード取得はwebstore-platform-integrationスキルの責務です。

`HandleUserValidationUseCase`内で、Apple/Googleプラットフォームから国コード・通貨コードを取得する処理を実装する必要があります。

```php
// webstore-platform-integrationスキルで実装
$platformInfo = $this->platformService->getPlatformInfo($user);
$countryCode = $platformInfo->countryCode ?? '';
$currencyCode = $platformInfo->currencyCode ?? '';
```

詳細は **webstore-platform-integration** スキルを参照してください。

## チェックリスト

- [ ] Controllerを実装した
- [ ] UseCaseを実装した
- [ ] Value Objectを実装した
- [ ] ResponseFactoryを実装した
- [ ] ルーティングを設定した
- [ ] 署名検証ミドルウェアを適用した
- [ ] webstore-platform-integrationと連携した
- [ ] テストを実装した
