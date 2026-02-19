# 完全な実装例

新規APIエンドポイント追加の完全な実装例を示します。

## 目次

1. [例1: Stage Start API](#例1-stage-start-api)
2. [例2: Gacha Draw API](#例2-gacha-draw-api)
3. [例3: User Buy Stamina API](#例3-user-buy-stamina-api)

---

## 例1: Stage Start API

ステージ開始APIの完全な実装例。

### ルーティング定義

**ファイル:** `api/routes/api.php`

```php
Route::middleware([
    'encrypt',
    'auth:api',
    'block_multiple_access',
    'user_status_check',
    'client_version_check',
    'asset_version_check',
    'master_version_check',
    'cross_day_check',
])->group(function () {
    Route::middleware(['content_maintenance_check'])->group(function () {
        Route::controller(Controllers\StageController::class)->group(function () {
            Route::post('/stage/start', 'start');
            Route::post('/stage/end', 'end');
            Route::post('/stage/continue_diamond', 'continueDiamond');
            Route::post('/stage/abort', 'abort');
            Route::post('/stage/cleanup', 'cleanup');
        });
    });
});
```

**ファイルパス:** `api/routes/api.php:121-128`

---

### Controller実装

**ファイル:** `api/app/Http/Controllers/StageController.php`

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Stage\UseCases\StageStartUseCase;
use App\Http\ResponseFactories\StageResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StageController extends Controller
{
    public function __construct(
        private Request $request,
        private StageResponseFactory $responseFactory,
    ) {
    }

    public function start(StageStartUseCase $useCase, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mstStageId' => 'required',
            'partyNo' => 'required',
            'isChallengeAd' => 'required|boolean',
        ]);

        $partyNo = $request->input('partyNo', 0);
        $isChallengeAd = $request->input('isChallengeAd', false);

        $resultData = $useCase->exec(
            $this->request->user(),
            $validated['mstStageId'],
            $partyNo,
            $isChallengeAd,
        );

        return $this->responseFactory->createStartResponse($resultData);
    }
}
```

**ファイルパス:** `api/app/Http/Controllers/StageController.php:18-45`

**実装ポイント:**
- コンストラクタで `Request` と `StageResponseFactory` を注入
- メソッド引数で `StageStartUseCase` を受け取る
- バリデーションで `mstStageId`, `partyNo`, `isChallengeAd` を検証
- デフォルト値を設定（`partyNo`, `isChallengeAd`）
- `$this->request->user()` で認証ユーザーを取得
- UseCaseを実行してResultDataを取得
- ResponseFactoryでレスポンス生成

---

### ResultData実装

**ファイル:** `api/app/Http/Responses/ResultData/StageStartResultData.php`

```php
<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Http\Responses\Data\UsrParameterData;
use App\Http\Responses\Data\UsrStageStatusData;

class StageStartResultData
{
    public function __construct(
        public UsrParameterData $usrUserParameter,
        public UsrStageStatusData $usrStageStatus
    ) {
    }
}
```

**ファイルパス:** `api/app/Http/Responses/ResultData/StageStartResultData.php`

**実装ポイント:**
- 2つのDataクラス（`UsrParameterData`, `UsrStageStatusData`）を保持
- publicプロパティで定義
- ロジックなし（データ受け渡し専用）

---

### ResponseFactory実装

**ファイル:** `api/app/Http/ResponseFactories/StageResponseFactory.php`

```php
<?php

declare(strict_types=1);

namespace App\Http\ResponseFactories;

use App\Http\Responses\ResultData\StageStartResultData;
use Illuminate\Http\JsonResponse;

class StageResponseFactory
{
    public function __construct(
        private ResponseDataFactory $responseDataFactory,
    ) {
    }

    public function createStartResponse(StageStartResultData $resultData): JsonResponse
    {
        $result = [];

        $result = $this->responseDataFactory->addUsrParameterData($result, $resultData->usrUserParameter);
        $result = $this->responseDataFactory->addUsrInGameStatusData($result, $resultData->usrStageStatus);

        return response()->json($result);
    }
}
```

**ファイルパス:** `api/app/Http/ResponseFactories/StageResponseFactory.php:26-35`

**実装ポイント:**
- `ResponseDataFactory` を注入
- ResultDataからレスポンス配列を構築
- `ResponseDataFactory` のメソッドを使用してレスポンスキーを統一
- `response()->json()` でJSONレスポンスを返す

---

### UseCase実装（参考）

**ファイル:** `api/app/Domain/Stage/UseCases/StageStartUseCase.php`

```php
<?php

declare(strict_types=1);

namespace App\Domain\Stage\UseCases;

use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Stage\Services\StageSessionService;
use App\Domain\User\Delegators\UserDelegator;
use App\Http\Responses\Data\UsrStageStatusData;
use App\Http\Responses\ResultData\StageStartResultData;

class StageStartUseCase
{
    public function __construct(
        private UserDelegator $userDelegator,
        private StageSessionService $stageSessionService,
    ) {
    }

    public function exec(CurrentUser $user, string $mstStageId, int $partyNo, bool $isChallengeAd): StageStartResultData
    {
        $usrUserId = $user->id;

        // ビジネスロジック
        // ...

        $usrParameter = $this->userDelegator->getUsrUserParameterByUsrUserId($usrUserId);
        $usrStageSession = $this->stageSessionService->getUsrStageSessionWithResetDaily($usrUserId, $now);

        return new StageStartResultData(
            $this->makeUsrParameterData($usrParameter),
            new UsrStageStatusData($usrStageSession),
        );
    }
}
```

**ファイルパス:** `api/app/Domain/Stage/UseCases/StageStartUseCase.php`

---

## 例2: Gacha Draw API

ガチャ実行APIの実装例（複数のコストタイプに対応）。

### ルーティング定義

**ファイル:** `api/routes/api.php`

```php
Route::middleware(['content_maintenance_check'])->group(function () {
    Route::controller(Controllers\GachaController::class)->group(function () {
        Route::get('/gacha/prize', 'prize');
        Route::get('/gacha/history', 'history');
        Route::post('/gacha/draw/ad', 'drawAd');
        Route::post('/gacha/draw/diamond', 'drawDiamond');
        Route::post('/gacha/draw/paid_diamond', 'drawPaidDiamond');
        Route::post('/gacha/draw/item', 'drawItem');
        Route::post('/gacha/draw/free', 'drawFree');
    });
});
```

**ファイルパス:** `api/routes/api.php:142-150`

---

### Controller実装

**ファイル:** `api/app/Http/Controllers/GachaController.php`

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Common\Constants\System;
use App\Domain\Gacha\Enums\CostType;
use App\Domain\Gacha\UseCases\GachaDrawUseCase;
use App\Http\ResponseFactories\GachaResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GachaController extends Controller
{
    public function __construct(
        private Request $request,
        private GachaResponseFactory $responseFactory
    ) {
    }

    public function drawDiamond(GachaDrawUseCase $useCase): JsonResponse
    {
        $validated = $this->request->validate([
            'oprGachaId' => 'required',
            'drewCount' => 'required',
            'playNum' => 'required',
            'costNum' => 'required',
        ]);

        $resultData = $useCase->exec(
            $this->request->user(),
            $validated['oprGachaId'],
            $validated['drewCount'],
            $validated['playNum'],
            null,
            $validated['costNum'],
            (int)$this->request->header(System::HEADER_PLATFORM),
            $this->request->getBillingPlatform(),
            CostType::DIAMOND
        );

        return $this->responseFactory->createDrawResponse($resultData);
    }

    public function drawAd(GachaDrawUseCase $useCase): JsonResponse
    {
        $validated = $this->request->validate([
            'oprGachaId' => 'required',
            'drewCount' => 'required',
        ]);

        $resultData = $useCase->exec(
            $this->request->user(),
            $validated['oprGachaId'],
            $validated['drewCount'],
            1,
            null,
            1,
            (int)$this->request->header(System::HEADER_PLATFORM),
            $this->request->getBillingPlatform(),
            CostType::AD
        );

        return $this->responseFactory->createDrawResponse($resultData);
    }
}
```

**ファイルパス:** `api/app/Http/Controllers/GachaController.php`

**実装ポイント:**
- 同じUseCaseを複数のメソッドで使用（`GachaDrawUseCase`）
- コストタイプ（`CostType::DIAMOND`, `CostType::AD`）で処理を分岐
- プラットフォーム情報を取得（`$request->header(System::HEADER_PLATFORM)`）
- 課金プラットフォームを取得（`$request->getBillingPlatform()`）

---

### ResultData実装

**ファイル:** `api/app/Http/Responses/ResultData/GachaDrawResultData.php`

```php
<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Domain\Gacha\Models\UsrGachaInterface;
use App\Http\Responses\Data\UsrParameterData;
use Illuminate\Support\Collection;

class GachaDrawResultData
{
    public function __construct(
        public Collection $gachaRewards,
        public Collection $usrUnits,
        public Collection $usrItems,
        public UsrParameterData $usrParameterData,
        public UsrGachaInterface $usrGacha,
        public Collection $usrGachaUppers,
    ) {
    }
}
```

**ファイルパス:** `api/app/Http/Responses/ResultData/GachaDrawResultData.php`

**実装ポイント:**
- 複数のCollectionを保持
- Interfaceを直接プロパティに設定（`UsrGachaInterface`）
- PHPDocで型を明示すべき（ここでは省略されているが、推奨は追加）

---

## 例3: User Buy Stamina API

スタミナ購入APIの実装例（シンプルなパターン）。

### ルーティング定義

**ファイル:** `api/routes/api.php`

```php
Route::middleware([
    'encrypt',
    'auth:api',
    'block_multiple_access',
    'user_status_check',
    'client_version_check',
    'asset_version_check',
    'master_version_check',
    'cross_day_check',
])->group(function () {
    Route::controller(Controllers\UserController::class)->group(function () {
        Route::post('/user/buy_stamina_ad', 'buyStaminaAd');
        Route::post('/user/buy_stamina_diamond', 'buyStaminaDiamond');
    });
});
```

**ファイルパス:** `api/routes/api.php:42-48`

**実装ポイント:**
- `content_maintenance_check` なし（User系APIのため）
- 標準ミドルウェアセットを使用

---

### Controller実装

**ファイル:** `api/app/Http/Controllers/UserController.php`

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\User\UseCases\UserBuyStaminaAdUseCase;
use App\Domain\User\UseCases\UserBuyStaminaDiamondUseCase;
use App\Http\ResponseFactories\UserResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        private Request $request,
        private UserResponseFactory $responseFactory,
    ) {
    }

    public function buyStaminaAd(UserBuyStaminaAdUseCase $useCase): JsonResponse
    {
        // バリデーション不要（パラメータなし）
        $resultData = $useCase->exec($this->request->user());

        return $this->responseFactory->createBuyStaminaAdResponse($resultData);
    }

    public function buyStaminaDiamond(UserBuyStaminaDiamondUseCase $useCase): JsonResponse
    {
        $validated = $this->request->validate([
            'count' => 'required|integer|min:1',
        ]);

        $resultData = $useCase->exec(
            $this->request->user(),
            $validated['count']
        );

        return $this->responseFactory->createBuyStaminaDiamondResponse($resultData);
    }
}
```

**実装ポイント:**
- パラメータがない場合はバリデーション不要（`buyStaminaAd`）
- 最小値チェックの例（`min:1`）

---

### ResultData実装

**ファイル:** `api/app/Http/Responses/ResultData/UserBuyStaminaAdResultData.php`

```php
<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Http\Responses\Data\UsrParameterData;

class UserBuyStaminaAdResultData
{
    public function __construct(
        public UsrParameterData $usrUserParameter,
    ) {
    }
}
```

**実装ポイント:**
- 最もシンプルなResultData（1つのDataクラスのみ）

---

## 実装パターンの比較

### パターンA: パラメータなし

```php
public function history(GachaHistoryUseCase $useCase): JsonResponse
{
    // バリデーション不要
    $resultData = $useCase->exec($this->request->user());
    return $this->responseFactory->createHistoryResponse($resultData);
}
```

**特徴:**
- リクエストパラメータがない
- バリデーション不要
- ユーザー情報のみを使用

---

### パターンB: 単純なパラメータ

```php
public function start(StageStartUseCase $useCase, Request $request): JsonResponse
{
    $validated = $request->validate([
        'mstStageId' => 'required',
        'partyNo' => 'required',
    ]);

    $resultData = $useCase->exec(
        $this->request->user(),
        $validated['mstStageId'],
        $validated['partyNo']
    );

    return $this->responseFactory->createStartResponse($resultData);
}
```

**特徴:**
- シンプルなバリデーション
- バリデーション済みの値を直接使用

---

### パターンC: ヘッダー情報を使用

```php
use App\Domain\Common\Constants\System;

public function end(StageEndUseCase $useCase, Request $request): JsonResponse
{
    $platform = (int) $request->header(System::HEADER_PLATFORM);

    $validated = $request->validate([
        'mstStageId' => 'required',
        'inGameBattleLog' => 'required',
    ]);

    $resultData = $useCase->exec(
        $this->request->user(),
        $platform,
        $validated['mstStageId'],
        $validated['inGameBattleLog']
    );

    return $this->responseFactory->createEndResponse($resultData);
}
```

**特徴:**
- ヘッダー情報を取得（プラットフォーム）
- `System::HEADER_PLATFORM` 定数を使用
- int型にキャスト

---

### パターンD: デフォルト値を設定

```php
public function start(StageStartUseCase $useCase, Request $request): JsonResponse
{
    $validated = $request->validate([
        'mstStageId' => 'required',
        'partyNo' => 'required',
        'isChallengeAd' => 'required|boolean',
    ]);

    // デフォルト値を設定
    $partyNo = $request->input('partyNo', 0);
    $isChallengeAd = $request->input('isChallengeAd', false);

    $resultData = $useCase->exec(
        $this->request->user(),
        $validated['mstStageId'],
        $partyNo,
        $isChallengeAd,
    );

    return $this->responseFactory->createStartResponse($resultData);
}
```

**特徴:**
- `$request->input()` でデフォルト値を設定
- バリデーション後に再度取得

---

### パターンE: Enumを使用

```php
use App\Domain\Gacha\Enums\CostType;

public function drawDiamond(GachaDrawUseCase $useCase): JsonResponse
{
    $validated = $this->request->validate([
        'oprGachaId' => 'required',
        'drewCount' => 'required',
    ]);

    $resultData = $useCase->exec(
        $this->request->user(),
        $validated['oprGachaId'],
        $validated['drewCount'],
        1,
        null,
        1,
        (int)$this->request->header(System::HEADER_PLATFORM),
        $this->request->getBillingPlatform(),
        CostType::DIAMOND  // Enum使用
    );

    return $this->responseFactory->createDrawResponse($resultData);
}
```

**特徴:**
- Enumでコストタイプを指定
- 同じUseCaseを異なるEnumで呼び分け

---

## 実装チェックリスト

完全な実装例を参考に、以下を確認：

**ルーティング:**
- [ ] 適切なミドルウェアグループに追加した
- [ ] コントローラーグループでまとめた
- [ ] URLパスとメソッド名が対応している

**Controller:**
- [ ] コンストラクタで `Request` と `ResponseFactory` を注入した
- [ ] メソッド引数で `UseCase` を受け取った
- [ ] バリデーションを実装した
- [ ] `$this->request->user()` で認証ユーザーを取得した
- [ ] 必要に応じてヘッダー情報を取得した
- [ ] デフォルト値を設定した（必要に応じて）
- [ ] UseCaseを実行してResultDataを取得した
- [ ] ResponseFactoryでレスポンスを生成した

**ResultData:**
- [ ] publicプロパティで定義した
- [ ] ロジックを持たせていない
- [ ] Collection型にPHPDocを記述した（必要に応じて）

**ResponseFactory:**
- [ ] `ResponseDataFactory` を注入した
- [ ] ResultDataからレスポンス配列を構築した
- [ ] `response()->json()` でレスポンスを返した

---

## 次のステップ

実装例を参考に実装が完了したら、以下のスキルでテストを実装してください：

**[api-test-implementation](../api-test-implementation/SKILL.md)** - テスト実装
