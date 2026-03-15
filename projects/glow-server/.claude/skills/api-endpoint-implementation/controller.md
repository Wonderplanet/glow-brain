# Controller実装パターン

APIエンドポイントのController実装方法を説明します。

## 目次

1. [基本構造](#基本構造)
2. [コンストラクタ](#コンストラクタ)
3. [メソッド実装](#メソッド実装)
4. [バリデーション](#バリデーション)
5. [ヘッダー取得](#ヘッダー取得)
6. [エラーハンドリング](#エラーハンドリング)

---

## 基本構造

### Controllerクラスの基本形

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\{Domain}\UseCases\{Action}UseCase;
use App\Http\ResponseFactories\{Domain}ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class {Domain}Controller extends Controller
{
    public function __construct(
        private Request $request,
        private {Domain}ResponseFactory $responseFactory,
    ) {
    }

    public function {action}({Action}UseCase $useCase): JsonResponse
    {
        // バリデーション
        $validated = $this->request->validate([...]);

        // UseCase実行
        $resultData = $useCase->exec(...);

        // レスポンス生成
        return $this->responseFactory->create{Action}Response($resultData);
    }
}
```

### 実例: StageController

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

        $resultData = $useCase->exec(
            $this->request->user(),
            $validated['mstStageId'],
            $validated['partyNo'],
            $request->input('isChallengeAd', false),
        );

        return $this->responseFactory->createStartResponse($resultData);
    }
}
```

**ファイルパス:** `api/app/Http/Controllers/StageController.php:26-45`

---

## コンストラクタ

### 必須の依存注入

全てのControllerで以下を注入します：

```php
public function __construct(
    private Request $request,
    private {Domain}ResponseFactory $responseFactory,
) {
}
```

**注入する依存:**
1. **Request** - リクエストオブジェクト（`$this->request->user()` でユーザー取得）
2. **{Domain}ResponseFactory** - レスポンス生成用ファクトリー

### なぜUseCaseはコンストラクタで注入しないのか？

UseCaseはメソッド引数で受け取ります。これにより：
- メソッドごとに異なるUseCaseを使用できる
- LaravelのDIコンテナが自動解決する
- テスト時にモックしやすい

---

## メソッド実装

### メソッドシグネチャ

```php
public function {action}({Action}UseCase $useCase, Request $request): JsonResponse
{
    // ...
}
```

**引数:**
1. `{Action}UseCase $useCase` - 実行するUseCase（DIコンテナが自動注入）
2. `Request $request` - リクエストオブジェクト（オプション、バリデーション時に使用）

**戻り値:**
- `JsonResponse` - JSON形式のレスポンス

### メソッド内の処理フロー

```php
public function start(StageStartUseCase $useCase, Request $request): JsonResponse
{
    // 1. バリデーション
    $validated = $request->validate([...]);

    // 2. 入力値の取得（デフォルト値設定）
    $partyNo = $request->input('partyNo', 0);
    $isChallengeAd = $request->input('isChallengeAd', false);

    // 3. UseCase実行
    $resultData = $useCase->exec(
        $this->request->user(),  // 認証ユーザー
        $validated['mstStageId'],
        $partyNo,
        $isChallengeAd,
    );

    // 4. レスポンス生成
    return $this->responseFactory->createStartResponse($resultData);
}
```

---

## バリデーション

### バリデーション実装

**使用スキル:** **[api-request-validation](../api-request-validation/SKILL.md)**

基本パターン：

```php
$validated = $request->validate([
    'mstStageId' => 'required',
    'partyNo' => 'required',
    'isChallengeAd' => 'required|boolean',
]);
```

### バリデーション後の値取得

**パターン1: バリデーション済みの値を直接使用**

```php
$validated = $request->validate([
    'mstStageId' => 'required',
]);

// バリデーション済みの値を使用
$resultData = $useCase->exec($validated['mstStageId']);
```

**パターン2: デフォルト値を設定**

```php
$validated = $request->validate([
    'partyNo' => 'nullable',
]);

// デフォルト値を設定
$partyNo = $request->input('partyNo', 0);
$resultData = $useCase->exec($partyNo);
```

---

## ヘッダー取得

### プラットフォーム情報の取得

```php
use App\Domain\Common\Constants\System;

public function end(StageEndUseCase $useCase, Request $request): JsonResponse
{
    // プラットフォーム取得（iOS/Android）
    $platform = (int) $request->header(System::HEADER_PLATFORM);

    // 課金プラットフォーム取得
    $billingPlatform = $request->getBillingPlatform();

    $validated = $request->validate([...]);

    $resultData = $useCase->exec(
        $this->request->user(),
        $platform,
        $validated['mstStageId'],
        $billingPlatform,
    );

    return $this->responseFactory->createEndResponse($resultData);
}
```

**ファイルパス:** `api/app/Http/Controllers/StageController.php:47-61`

### 利用可能なヘッダー

- `System::HEADER_PLATFORM` - プラットフォーム（iOS/Android）
- `System::HEADER_LANGUAGE` - 言語設定
- `System::HEADER_CLIENT_VERSION` - クライアントバージョン
- `System::HEADER_ASSET_VERSION` - アセットバージョン

### カスタムメソッド

- `$request->getBillingPlatform()` - 課金プラットフォーム取得

---

## エラーハンドリング

### GameExceptionのスロー

ビジネスロジックエラーは `GameException` をスローします：

```php
use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;

// UseCase内でのエラー
if (!$condition) {
    throw new GameException(ErrorCode::STAGE_NOT_START);
}
```

**重要:** Controllerでは例外をキャッチせず、LaravelのExceptionHandlerに任せます。

### エラーコード定義

エラーコードは `App\Domain\Common\Constants\ErrorCode` で定義：

```php
class ErrorCode
{
    public const STAGE_NOT_START = 1001;
    public const STAGE_ALREADY_CLEARED = 1002;
    public const INSUFFICIENT_STAMINA = 2001;
    // ...
}
```

**ファイルパス:** `api/app/Domain/Common/Constants/ErrorCode.php`

---

## 実装チェックリスト

Controller実装時に以下を確認：

- [ ] `declare(strict_types=1);` を記述した
- [ ] 名前空間が正しい（`App\Http\Controllers`）
- [ ] `Controller` クラスを継承した
- [ ] コンストラクタで `Request` と `ResponseFactory` を注入した
- [ ] メソッド引数で `UseCase` を受け取った
- [ ] 戻り値の型を `JsonResponse` と明示した
- [ ] バリデーションを実装した（**api-request-validation** スキル参照）
- [ ] `$this->request->user()` で認証ユーザーを取得した
- [ ] UseCaseを実行してResultDataを取得した
- [ ] ResponseFactoryでレスポンスを生成した
- [ ] 例外処理をControllerでキャッチしていない（ExceptionHandlerに任せる）

---

## 次のステップ

Controller実装が完了したら、以下を実装してください：

1. **[result-data.md](result-data.md)** - ResultDataの実装
2. **[api-response](../api-response/SKILL.md)** - ResponseFactoryの実装
3. **[api-test-implementation](../api-test-implementation/SKILL.md)** - テストの実装
