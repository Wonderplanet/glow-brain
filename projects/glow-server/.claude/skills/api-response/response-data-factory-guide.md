# ResponseDataFactory実装ガイド

`ResponseDataFactory`への新しいメソッド追加方法を説明します。

## 目次

- [ResponseDataFactoryの役割](#responsedatafactoryの役割)
- [メソッドの命名規則](#メソッドの命名規則)
- [実装パターン](#実装パターン)
- [単数形・複数形の扱い](#単数形複数形の扱い)
- [Collectionの処理](#collectionの処理)
- [formatToResponseの活用](#formattoresponseの活用)

---

## ResponseDataFactoryの役割

### 責務

**ファイルパス:** `api/app/Http/ResponseFactories/ResponseDataFactory.php`

```php
/**
 * DomainごとのResponseFactoryで使う、レスポンスデータの生成をまとめたクラス
 *
 * APIレスポンスの内容は、glow-schemaリポジトリのyamlで定義している。
 * 対応するキーとレスポンス内容を全APIで統一するために、ResponseDataFactoryでレスポンスする配列を生成する。
 * 例:usrUserParameterのレスポンスは、usrParameterというキーで返すことになっている。
 *
 * DomainごとのResponseFactoryでやることは、ResultDataの情報を使って、どんなレスポンス配列を作成する必要があるかを把握し、
 * ResponseDataFactoryの関数を組み合わせ、最終的に必要な内容へ調整すること。
 */
```

### ポイント

1. **統一されたレスポンス形式**
   - 同じデータには常に同じキーとフォーマットを使用
   - 全APIで整合性を保証

2. **再利用性**
   - 複数のResponseFactoryから利用される
   - 共通データ構造を一箇所で管理

3. **glow-schemaとの対応**
   - yamlで定義されたレスポンス仕様に準拠
   - キー名とデータ構造を厳密に一致

---

## メソッドの命名規則

### パターン1: add{DataName}Data

基本的なデータ追加メソッド

```php
// ユーザーパラメータを追加
public function addUsrParameterData(array $result, UsrParameterData $usrUserParameter): array

// アイテムデータを追加
public function addUsrItemData(array $result, Collection $usrItems, bool $isMulti): array

// ミッションデータを追加
public function addUsrMissionDailyData(array $result, Collection $usrMissionStatusDataList): array
```

### パターン2: add{特定のキー}

特定のレスポンスキーに対応

```php
// 'myId'キーを追加
public function addMyIdData(array $result, UsrUserProfileInterface $usrUserProfile): array

// 'badges'キーを追加
public function addGameBadgeData(array $result, GameBadgeData $gameBadgeData): array
```

### 命名のポイント

- `add` で始める
- データの種類を明確に表現
- `Data` で終わる (ほとんどの場合)
- 第1引数は `array $result`
- 戻り値は `array`

---

## 実装パターン

### パターン1: シンプルなオブジェクトデータ

**ファイルパス:** `api/app/Http/ResponseFactories/ResponseDataFactory.php:160-174`

```php
public function addUsrParameterData(array $result, UsrParameterData $usrUserParameter): array
{
    $result['usrParameter'] = [
        'level' => $usrUserParameter->getLevel(),
        'exp' => $usrUserParameter->getExp(),
        'coin' => $usrUserParameter->getCoin(),
        'stamina' => $usrUserParameter->getStamina(),
        'staminaUpdatedAt' => StringUtil::convertToISO8601($usrUserParameter->getStaminaUpdatedAt()),
        'freeDiamond' => $usrUserParameter->getFreeDiamond(),
        'paidDiamondIos' => $usrUserParameter->getPaidDiamondIos(),
        'paidDiamondAndroid' => $usrUserParameter->getPaidDiamondAndroid(),
    ];

    return $result;
}
```

**ポイント:**
- オブジェクトのgetterを呼び出して値を取得
- レスポンスキー(`usrParameter`)を明確に定義
- 日時データは `StringUtil::convertToISO8601()` で変換

### パターン2: nullチェック付き

**ファイルパス:** `api/app/Http/ResponseFactories/ResponseDataFactory.php:446-463`

```php
public function addUsrIdleIncentiveData(array $result, ?UsrIdleIncentiveInterface $usrIdleIncentive): array
{
    $key = 'usrIdleIncentive';

    if ($usrIdleIncentive === null) {
        $result[$key] = null;
        return $result;
    }

    $result[$key] = [
        'diamondQuickReceiveCount' => $usrIdleIncentive->getDiamondQuickReceiveCount(),
        'adQuickReceiveCount' => $usrIdleIncentive->getAdQuickReceiveCount(),
        'idleStartedAt' => StringUtil::convertToISO8601($usrIdleIncentive->getIdleStartedAt()),
        'diamondQuickReceiveAt' => StringUtil::convertToISO8601($usrIdleIncentive->getDiamondQuickReceiveAt()),
        'adQuickReceiveAt' => StringUtil::convertToISO8601($usrIdleIncentive->getAdQuickReceiveAt()),
    ];
    return $result;
}
```

**ポイント:**
- 引数に `?` を付けてnull許容
- nullの場合は `null` を設定して早期リターン
- 日時データは複数フィールドで変換

### パターン3: 条件分岐のあるデータ

**ファイルパス:** `api/app/Http/ResponseFactories/ResponseDataFactory.php:180-201`

```php
public function addUsrLoginData(array $result, ?UsrUserLoginInterface $usrUserLogin): array
{
    $response = [];

    if (is_null($usrUserLogin)) {
        $response = [
            'lastLoginAt' => null,
            'loginDayCount' => 0,
            'loginContinueDayCount' => 0,
        ];
    } else {
        $response = [
            'lastLoginAt' => StringUtil::convertToISO8601($usrUserLogin->getLastLoginAt()),
            'loginDayCount' => $usrUserLogin->getLoginDayCount(),
            'loginContinueDayCount' => $usrUserLogin->getLoginContinueDayCount(),
        ];
    }

    $result['usrLogin'] = $response;

    return $result;
}
```

**ポイント:**
- nullの場合はデフォルト値を設定
- データがある場合は実際の値を設定
- 構造を保ちながらnullセーフに処理

### パターン4: Collection処理(単純ループ)

**ファイルパス:** `api/app/Http/ResponseFactories/ResponseDataFactory.php:129-140`

```php
public function addMngInGameNoticeData(array $result, Collection $mngInGameNoticeDataList): array
{
    $response = [];
    foreach ($mngInGameNoticeDataList as $mngInGameNoticeData) {
        /** @var MngInGameNoticeData $mngInGameNoticeData */
        $response[] = $mngInGameNoticeData->formatToResponse();
    }
    // クライアント側の変更対応を避けるために一旦oprのままにする
    $result['oprInGameNotices'] = $response;

    return $result;
}
```

**ポイント:**
- `foreach` でCollectionをループ
- 各要素の `formatToResponse()` を呼び出し
- 配列に追加

### パターン5: Collection処理(複雑な変換)

**ファイルパス:** `api/app/Http/ResponseFactories/ResponseDataFactory.php:779-808`

```php
public function addStageRewardData(
    array $result,
    Collection $stageFirstClearRewards,
    Collection $stageAlwaysClearRewards,
    Collection $stageRandomClearRewards,
    Collection $stageSpeedAttackClearRewards,
): array {
    // firstClearは獲得した内容そのままで、alwayClearはtypeとresourceIdごとに個数をまとめた情報をレスポンスする。
    $firstClearRewards = $stageFirstClearRewards->map(function (StageFirstClearReward $reward) {
        return $reward->formatToResponse();
    });
    $alwaysClearRewards = $stageAlwaysClearRewards->groupBy(function (StageAlwaysClearReward $reward) {
        return $reward->getType() . $reward->getResourceId();
    })->map(function ($rewards) {
        /** @var StageAlwaysClearReward $targetReward */
        $targetReward = $rewards->first();
        $response = $targetReward->formatToResponse();
        $resourceAmount = $rewards->sum(function (StageAlwaysClearReward $reward) {
            return $reward->getAmount();
        });
        $response['reward']['resourceAmount'] = $resourceAmount;
        return $response;
    });
    $randomClearRewards = $stageRandomClearRewards->map(function (StageRandomClearReward $reward) {
        return $reward->formatToResponse();
    });
    $stageSpeedAttackClearRewards = $stageSpeedAttackClearRewards->map(function (StageSpeedAttackClearReward $reward) {
        return $reward->formatToResponse();
    });
    $result['stageRewards'] = $firstClearRewards
        ->merge($alwaysClearRewards)
        ->merge($randomClearRewards)
        ->merge($stageSpeedAttackClearRewards)
        ->values()
        ->toArray();

    return $result;
}
```

**ポイント:**
- Collectionの `map()`, `groupBy()`, `sum()` を活用
- 複数のCollectionをマージ
- 複雑なデータ集計処理

---

## 単数形・複数形の扱い

### isMultiパラメータパターン

**ファイルパス:** `api/app/Http/ResponseFactories/ResponseDataFactory.php:475-493`

```php
public function addUsrItemData(array $result, Collection $usrItems, bool $isMulti): array
{
    $response = [];
    /** @var UsrItemInterface $usrItem */
    foreach ($usrItems as $usrItem) {
        $response[] = [
            'mstItemId' => $usrItem->getMstItemId(),
            'amount' => $usrItem->getAmount(),
        ];
    }

    if ($isMulti) {
        $result['usrItems'] = $response;  // 複数形
    } else {
        $result['usrItem'] = count($response) > 0 ? $response[0] : [];  // 単数形
    }

    return $result;
}
```

### 使用パターン

```php
// 複数のアイテムを返す場合
$result = $this->responseDataFactory->addUsrItemData($result, $usrItems, true);
// → { "usrItems": [...] }

// 単一のアイテムを返す場合
$result = $this->responseDataFactory->addUsrItemData($result, $usrItems, false);
// → { "usrItem": {...} } または { "usrItem": [] }
```

### 空データの扱い

```php
// データがない場合
if ($isMulti) {
    $result['usrItems'] = [];  // 空配列
} else {
    $result['usrItem'] = [];   // 空配列
}
```

---

## Collectionの処理

### 基本的なループ

```php
$response = [];
foreach ($collection as $item) {
    $response[] = [
        'id' => $item->getId(),
        'name' => $item->getName(),
    ];
}
$result['items'] = $response;
```

### mapを使った変換

```php
$response = $collection->map(function ($item) {
    return [
        'id' => $item->getId(),
        'name' => $item->getName(),
    ];
})->toArray();
$result['items'] = $response;
```

### groupByで集計

```php
$grouped = $collection->groupBy(function ($item) {
    return $item->getType();
})->map(function ($items) {
    return [
        'type' => $items->first()->getType(),
        'count' => $items->count(),
        'totalAmount' => $items->sum('amount'),
    ];
});
```

### filterとmap

```php
$response = $collection
    ->filter(function ($item) {
        return $item->isActive();
    })
    ->map(function ($item) {
        return $item->formatToResponse();
    })
    ->values()  // キーをリセット
    ->toArray();
```

---

## formatToResponseの活用

### Dataクラスのメソッドを利用

多くのDataクラスには `formatToResponse()` メソッドがあります。

```php
// UsrPvpStatusData.php
public function formatToResponse(): array
{
    return [
        'score' => $this->getScore(),
        'pvpRankClassType' => $this->getPvpRankClassType()->value,
        'pvpRankClassLevel' => $this->getPvpRankClassLevel(),
        'dailyRemainingChallengeCount' => $this->getDailyRemainingChallengeCount(),
        'dailyRemainingItemChallengeCount' => $this->getDailyRemainingItemChallengeCount(),
    ];
}
```

### ResponseDataFactoryでの利用

```php
public function addUsrPvpStatusData(array $result, UsrPvpStatusData $usrPvpStatusData): array
{
    $result['usrPvpStatus'] = $usrPvpStatusData->formatToResponse();
    return $result;
}
```

### Collectionでの利用

```php
public function addMngInGameNoticeData(array $result, Collection $mngInGameNoticeDataList): array
{
    $response = [];
    foreach ($mngInGameNoticeDataList as $mngInGameNoticeData) {
        $response[] = $mngInGameNoticeData->formatToResponse();
    }
    $result['oprInGameNotices'] = $response;
    return $result;
}
```

---

## 型定義の書き方

### 基本的な型定義

```php
/**
 * @param array<mixed> $result
 * @param UsrParameterData $usrUserParameter
 * @return array<mixed>
 */
public function addUsrParameterData(array $result, UsrParameterData $usrUserParameter): array
```

### Collection型の定義

```php
/**
 * @param array<mixed> $result
 * @param Collection<UsrItemInterface> $usrItems
 * @param bool $isMulti
 * @return array<mixed>
 */
public function addUsrItemData(array $result, Collection $usrItems, bool $isMulti): array
```

### 複数のCollection

```php
/**
 * @param array<mixed> $result
 * @param Collection<StageFirstClearReward> $stageFirstClearRewards
 * @param Collection<StageAlwaysClearReward> $stageAlwaysClearRewards
 * @param Collection<StageRandomClearReward> $stageRandomClearRewards
 * @return array<mixed>
 */
public function addStageRewardData(
    array $result,
    Collection $stageFirstClearRewards,
    Collection $stageAlwaysClearRewards,
    Collection $stageRandomClearRewards,
): array
```

---

## 実装時のチェックリスト

新しいResponseDataFactoryメソッドを追加する際:

### メソッド設計
- [ ] メソッド名が `add{DataName}Data` の形式
- [ ] 第1引数が `array $result`
- [ ] 戻り値が `array`
- [ ] PHPDocで型を定義

### データ処理
- [ ] 日時データは `StringUtil::convertToISO8601()` で変換
- [ ] null値を適切に処理
- [ ] 空配列を適切に返す
- [ ] glow-schemaのキー名と一致

### Collection処理
- [ ] `foreach` または `map()` で適切に処理
- [ ] `formatToResponse()` を活用
- [ ] 単数形・複数形を正しく使い分け
- [ ] `isMulti` パラメータが必要か検討

### コード品質
- [ ] 重複コードを避ける
- [ ] 複雑な処理はコメントで説明
- [ ] 型キャストを明示
- [ ] 既存のパターンに従う

---

## 実装例まとめ

### 最小構成

```php
public function addYourData(array $result, YourData $yourData): array
{
    $result['yourKey'] = [
        'field1' => $yourData->getField1(),
        'field2' => $yourData->getField2(),
    ];

    return $result;
}
```

### Collection処理

```php
public function addYourCollectionData(array $result, Collection $items, bool $isMulti): array
{
    $response = [];
    foreach ($items as $item) {
        $response[] = [
            'id' => $item->getId(),
            'name' => $item->getName(),
        ];
    }

    if ($isMulti) {
        $result['items'] = $response;
    } else {
        $result['item'] = count($response) > 0 ? $response[0] : [];
    }

    return $result;
}
```

### nullチェック付き

```php
public function addYourNullableData(array $result, ?YourData $yourData): array
{
    if ($yourData === null) {
        $result['yourKey'] = null;
        return $result;
    }

    $result['yourKey'] = [
        'field1' => $yourData->getField1(),
        'field2' => StringUtil::convertToISO8601($yourData->getField2()),
    ];

    return $result;
}
```
