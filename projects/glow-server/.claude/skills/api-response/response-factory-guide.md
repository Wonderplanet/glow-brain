# ResponseFactory実装ガイド

ドメインごとのResponseFactoryクラスの実装方法を説明します。

## 目次

- [ResponseFactoryの役割](#responsefactoryの役割)
- [クラス構造](#クラス構造)
- [メソッドの命名規則](#メソッドの命名規則)
- [実装パターン](#実装パターン)
- [Traitの活用](#traitの活用)

---

## ResponseFactoryの役割

### 責務

1. **ResultDataからJsonResponseを作成**
   - UseCaseやServiceから返される `ResultData` を受け取る
   - `ResponseDataFactory` のメソッドを組み合わせて配列を構築
   - `JsonResponse` を返す

2. **エンドポイントごとのレスポンス組み立て**
   - 各APIエンドポイントに対応するレスポンスメソッドを提供
   - どのデータをどの順序でレスポンスに含めるかを決定

### 配置場所

```
api/app/Http/ResponseFactories/
├── ResponseDataFactory.php         # 共通Factory
├── GameResponseFactory.php         # Game関連
├── ShopResponseFactory.php         # Shop関連
├── StageResponseFactory.php        # Stage関連
├── UserResponseFactory.php         # User関連
└── ...
```

---

## クラス構造

### 基本構造

```php
<?php

declare(strict_types=1);

namespace App\Http\ResponseFactories;

use App\Http\Responses\ResultData\YourResultData;
use Illuminate\Http\JsonResponse;

class YourResponseFactory
{
    public function __construct(
        private ResponseDataFactory $responseDataFactory,
    ) {
    }

    public function createYourResponse(YourResultData $resultData): JsonResponse
    {
        $result = [];

        // ResponseDataFactoryのメソッドを使ってデータを追加
        $result = $this->responseDataFactory->addUsrParameterData($result, $resultData->usrUserParameter);
        $result = $this->responseDataFactory->addUsrItemData($result, $resultData->usrItems, true);

        return response()->json($result);
    }
}
```

### 実装例

**ファイルパス:** `api/app/Http/ResponseFactories/GameResponseFactory.php`

```php
<?php

declare(strict_types=1);

namespace App\Http\ResponseFactories;

use App\Domain\Common\Utils\StringUtil;
use App\Http\Responses\Data\GameFetchData;
use App\Http\Responses\ResultData\GameBadgeResultData;
use App\Http\Responses\ResultData\GameFetchResultData;
use App\Http\Responses\ResultData\GameServerTimeResultData;
use App\Http\Responses\ResultData\GameUpdateAndFetchResultData;
use App\Http\Responses\ResultData\GameVersionResultData;
use Illuminate\Http\JsonResponse;

class GameResponseFactory
{
    public function __construct(
        private ResponseDataFactory $responseDataFactory,
    ) {
    }

    public function createVersionResponse(GameVersionResultData $resultData): JsonResponse
    {
        $result = [];

        $result['mstHash'] = $resultData->mstHash;
        $result['oprHash'] = $resultData->oprHash;
        $result['mstI18nHash'] = $resultData->mstI18nHash;
        $result['oprI18nHash'] = $resultData->oprI18nHash;
        $result['mstPath'] = $resultData->mstPath;
        $result['oprPath'] = $resultData->oprPath;
        // ...

        return response()->json($result);
    }

    public function createServerTimeResponse(GameServerTimeResultData $resultData): JsonResponse
    {
        $result = [];

        $result['serverTime'] = StringUtil::convertToISO8601($resultData->serverTime->toDateTimeString());

        return response()->json($result);
    }

    public function createBadgeResponse(GameBadgeResultData $resultData): JsonResponse
    {
        $result = [];

        $result = $this->responseDataFactory->addGameBadgeData($result, $resultData->gameBadgeData);
        $result = $this->responseDataFactory->addMngContentCloseData($result, $resultData->mngContentCloses);

        return response()->json($result);
    }
}
```

---

## メソッドの命名規則

### パターン1: create{Action}Response

エンドポイントのアクションに対応するレスポンスを作成

```php
// /api/game/version
public function createVersionResponse(GameVersionResultData $resultData): JsonResponse

// /api/game/server_time
public function createServerTimeResponse(GameServerTimeResultData $resultData): JsonResponse

// /api/stage/start
public function createStartResponse(StageStartResultData $resultData): JsonResponse

// /api/stage/end
public function createEndResponse(StageEndResultData $resultData): JsonResponse
```

### パターン2: 具体的な操作名

```php
// /api/shop/purchase
public function createPurchaseResponse(ShopPurchaseResultData $resultData): JsonResponse

// /api/shop/trade_pack
public function createTradePackResponse(ShopTradePackResultData $resultData): JsonResponse

// /api/user/change_name
public function createUserChangeNameResponse(UserChangeNameResultData $resultData): JsonResponse
```

### 命名のポイント

- `create` で始める
- エンドポイントのアクションを明確に表現
- `Response` で終わる
- 引数は対応する `ResultData`
- 戻り値は `JsonResponse`

---

## 実装パターン

### パターン1: シンプルなレスポンス

ResultDataのプロパティを直接配列に詰める

```php
public function createVersionResponse(GameVersionResultData $resultData): JsonResponse
{
    $result = [];

    $result['mstHash'] = $resultData->mstHash;
    $result['oprHash'] = $resultData->oprHash;
    $result['assetHash'] = $resultData->assetHash;
    $result['tosVersion'] = $resultData->tosVersion;

    return response()->json($result);
}
```

### パターン2: ResponseDataFactoryを活用

共通メソッドを使って統一されたレスポンス形式を保証

```php
public function createStartResponse(StageStartResultData $resultData): JsonResponse
{
    $result = [];

    $result = $this->responseDataFactory->addUsrParameterData($result, $resultData->usrUserParameter);
    $result = $this->responseDataFactory->addUsrInGameStatusData($result, $resultData->usrStageStatus);

    return response()->json($result);
}
```

### パターン3: 複雑なネスト構造

複数のResponseDataFactoryメソッドを組み合わせる

**ファイルパス:** `api/app/Http/ResponseFactories/StageResponseFactory.php:37-76`

```php
public function createEndResponse(StageEndResultData $resultData): JsonResponse
{
    $result = [];

    $result = $this->responseDataFactory->addStageRewardData(
        $result,
        $resultData->stageFirstClearRewards,
        $resultData->stageAlwaysClearRewards,
        $resultData->stageRandomClearRewards,
        $resultData->stageSpeedAttackClearRewards,
    );

    $result = $this->responseDataFactory->addUserLevelData($result, $resultData->userLevelUpData);

    $result = $this->responseDataFactory->addUsrItemData(
        $result,
        $resultData->usrItems,
        true,
    );

    $result = $this->responseDataFactory->addUsrUnitData(
        $result,
        $resultData->usrUnits,
        true,
    );

    $result = $this->responseDataFactory->addUsrConditionPackData($result, $resultData->usrConditionPacks);

    $rewards = $resultData->stageFirstClearRewards->concat($resultData->stageAlwaysClearRewards);
    $result = $this->responseDataFactory->addDuplicatedRewardData($result, $rewards);

    $result = $this->responseDataFactory->addUsrArtworkData($result, $resultData->usrArtworks);
    $result = $this->responseDataFactory->addUsrArtworkFragmentData($result, $resultData->usrArtworkFragments);

    $result = $this->responseDataFactory->addUsrEnemyDiscoveryData($result, $resultData->newUsrEnemyDiscoveries);

    $result['oprCampaignIds'] = $resultData->oprCampaignIds->values()->toArray();

    return response()->json($result);
}
```

### パターン4: プライベートメソッドで構造化

複雑なレスポンスは分割して可読性を向上

**ファイルパス:** `api/app/Http/ResponseFactories/GameResponseFactory.php:53-81`

```php
class GameResponseFactory
{
    /**
     * @param array<mixed> $result
     * @param GameFetchData $gameFetchData
     * @return array<mixed>
     */
    private function addFetchResponse(array $result, GameFetchData $gameFetchData): array
    {
        $response = [];
        $response = $this->responseDataFactory->addUsrParameterData($response, $gameFetchData->usrUserParameter);
        $response = $this->responseDataFactory->addUsrBuyCountData($response, $gameFetchData->usrUserBuyCount);
        $response = $this->responseDataFactory->addUsrStageData($response, $gameFetchData->usrStages, true);
        $response = $this->responseDataFactory->addUsrStageEventData($response, $gameFetchData->usrStageEvents, true);
        $response = $this->responseDataFactory->addGameBadgeData($response, $gameFetchData->gameBadgeData);
        $response = $this->responseDataFactory->addMissionStatusData($response, $gameFetchData->missionStatusData);

        $result['fetch'] = $response;

        return $result;
    }

    public function createFetchResponse(GameFetchResultData $resultData): JsonResponse
    {
        $result = $this->addFetchResponse([], $resultData->gameFetchData);

        return response()->json($result);
    }
}
```

---

## Traitの活用

共通機能はTraitで再利用します。

### CurrencySummaryResponderTrait

通貨情報のレスポンスを追加

**使用例:**

```php
class ShopResponseFactory
{
    use CurrencySummaryResponderTrait;

    public function __construct(
        private ResponseDataFactory $responseDataFactory,
    ) {
    }

    // ...
}
```

### StoreInfoResponderTrait

ストア情報のレスポンスを追加

**使用例:**

```php
class ShopResponseFactory
{
    use CurrencySummaryResponderTrait;
    use StoreInfoResponderTrait;

    // ...
}
```

### Traitの活用ポイント

1. 複数のResponseFactoryで使う共通機能はTraitに抽出
2. ドメイン横断で使う機能に適用
3. ResponseDataFactoryと併用

---

## 実装時のチェックリスト

新しいResponseFactoryメソッドを作成する際:

### 構造
- [ ] メソッド名が `create{Action}Response` の形式
- [ ] 引数が対応する `ResultData`
- [ ] 戻り値が `JsonResponse`
- [ ] `ResponseDataFactory` をコンストラクタでDI

### データ処理
- [ ] `ResponseDataFactory` のメソッドを活用
- [ ] 日時データは `StringUtil::convertToISO8601()` で変換
- [ ] null値を適切に処理
- [ ] 空配列を適切に返す

### コード品質
- [ ] 複雑な処理はプライベートメソッドに分割
- [ ] 重複コードは避ける
- [ ] Traitで共通化できる処理を確認
- [ ] glow-schemaのYAML定義と一致

### 型定義
- [ ] PHPDocで型を定義
- [ ] 引数と戻り値に型ヒント
- [ ] Collectionの型パラメータを記述

---

## 実装例まとめ

### 最小構成

```php
class MinimalResponseFactory
{
    public function __construct(
        private ResponseDataFactory $responseDataFactory,
    ) {
    }

    public function createResponse(YourResultData $resultData): JsonResponse
    {
        $result = [];
        $result = $this->responseDataFactory->addUsrParameterData($result, $resultData->usrUserParameter);
        return response()->json($result);
    }
}
```

### 完全な例

```php
class CompleteResponseFactory
{
    use CurrencySummaryResponderTrait;

    public function __construct(
        private ResponseDataFactory $responseDataFactory,
    ) {
    }

    public function createComplexResponse(ComplexResultData $resultData): JsonResponse
    {
        $result = [];

        // 基本データ
        $result = $this->responseDataFactory->addUsrParameterData($result, $resultData->usrUserParameter);

        // コレクションデータ
        $result = $this->responseDataFactory->addUsrItemData($result, $resultData->usrItems, true);
        $result = $this->responseDataFactory->addUsrUnitData($result, $resultData->usrUnits, true);

        // ネストしたデータ構造
        $result = $this->addNestedData($result, $resultData);

        // 直接配列に追加する場合
        $result['customField'] = $resultData->customValue;

        return response()->json($result);
    }

    /**
     * @param array<mixed> $result
     * @param ComplexResultData $resultData
     * @return array<mixed>
     */
    private function addNestedData(array $result, ComplexResultData $resultData): array
    {
        $nested = [];
        $nested = $this->responseDataFactory->addSomeData($nested, $resultData->someData);
        $result['nestedData'] = $nested;
        return $result;
    }
}
```
