# コピー可能なテンプレート集

実装時にそのままコピーして使えるテンプレートを提供します。

## 目次

- [ResponseFactoryテンプレート](#responsefactoryテンプレート)
- [ResponseDataFactoryテンプレート](#responsedatafactoryテンプレート)
- [日時データ変換パターン](#日時データ変換パターン)
- [Collectionループパターン](#collectionループパターン)
- [nullチェックパターン](#nullチェックパターン)

---

## ResponseFactoryテンプレート

### テンプレート1: 最小構成

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

    public function createYourActionResponse(YourResultData $resultData): JsonResponse
    {
        $result = [];

        $result = $this->responseDataFactory->addUsrParameterData($result, $resultData->usrUserParameter);

        return response()->json($result);
    }
}
```

### テンプレート2: 複数のResponseDataFactoryメソッドを使用

```php
public function createComplexResponse(ComplexResultData $resultData): JsonResponse
{
    $result = [];

    // ユーザーパラメータ
    $result = $this->responseDataFactory->addUsrParameterData($result, $resultData->usrUserParameter);

    // アイテム(複数)
    $result = $this->responseDataFactory->addUsrItemData($result, $resultData->usrItems, true);

    // ユニット(複数)
    $result = $this->responseDataFactory->addUsrUnitData($result, $resultData->usrUnits, true);

    // ステージデータ
    $result = $this->responseDataFactory->addUsrStageData($result, $resultData->usrStages, true);

    return response()->json($result);
}
```

### テンプレート3: 直接配列に値を追加

```php
public function createVersionResponse(GameVersionResultData $resultData): JsonResponse
{
    $result = [];

    // ResultDataのプロパティを直接配列に詰める
    $result['mstHash'] = $resultData->mstHash;
    $result['oprHash'] = $resultData->oprHash;
    $result['assetHash'] = $resultData->assetHash;
    $result['tosVersion'] = $resultData->tosVersion;

    return response()->json($result);
}
```

### テンプレート4: プライベートメソッドで分割

```php
class YourResponseFactory
{
    public function __construct(
        private ResponseDataFactory $responseDataFactory,
    ) {
    }

    public function createResponse(YourResultData $resultData): JsonResponse
    {
        $result = [];

        $result = $this->addMainData($result, $resultData);
        $result = $this->addSubData($result, $resultData);

        return response()->json($result);
    }

    /**
     * @param array<mixed> $result
     * @param YourResultData $resultData
     * @return array<mixed>
     */
    private function addMainData(array $result, YourResultData $resultData): array
    {
        $mainData = [];
        $mainData = $this->responseDataFactory->addUsrParameterData($mainData, $resultData->usrUserParameter);
        $mainData = $this->responseDataFactory->addUsrItemData($mainData, $resultData->usrItems, true);

        $result['main'] = $mainData;

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param YourResultData $resultData
     * @return array<mixed>
     */
    private function addSubData(array $result, YourResultData $resultData): array
    {
        $subData = [];
        // ... サブデータを構築

        $result['sub'] = $subData;

        return $result;
    }
}
```

---

## ResponseDataFactoryテンプレート

### テンプレート1: シンプルなオブジェクトデータ

```php
/**
 * @param array<mixed> $result
 * @param YourDataInterface $yourData
 * @return array<mixed>
 */
public function addYourData(array $result, YourDataInterface $yourData): array
{
    $result['yourKey'] = [
        'id' => $yourData->getId(),
        'name' => $yourData->getName(),
        'count' => $yourData->getCount(),
    ];

    return $result;
}
```

### テンプレート2: 日時データを含む

```php
use App\Domain\Common\Utils\StringUtil;

/**
 * @param array<mixed> $result
 * @param YourDataInterface $yourData
 * @return array<mixed>
 */
public function addYourDataWithTimestamp(array $result, YourDataInterface $yourData): array
{
    $result['yourKey'] = [
        'id' => $yourData->getId(),
        'name' => $yourData->getName(),
        'createdAt' => StringUtil::convertToISO8601($yourData->getCreatedAt()),
        'updatedAt' => StringUtil::convertToISO8601($yourData->getUpdatedAt()),
    ];

    return $result;
}
```

### テンプレート3: nullチェック付き

```php
use App\Domain\Common\Utils\StringUtil;

/**
 * @param array<mixed> $result
 * @param YourDataInterface|null $yourData
 * @return array<mixed>
 */
public function addYourNullableData(array $result, ?YourDataInterface $yourData): array
{
    $key = 'yourKey';

    if ($yourData === null) {
        $result[$key] = null;
        return $result;
    }

    $result[$key] = [
        'id' => $yourData->getId(),
        'name' => $yourData->getName(),
        'timestamp' => StringUtil::convertToISO8601($yourData->getTimestamp()),
    ];

    return $result;
}
```

### テンプレート4: nullでデフォルト値を設定

```php
use App\Domain\Common\Utils\StringUtil;

/**
 * @param array<mixed> $result
 * @param YourDataInterface|null $yourData
 * @return array<mixed>
 */
public function addYourDataWithDefaults(array $result, ?YourDataInterface $yourData): array
{
    $response = [];

    if ($yourData === null) {
        $response = [
            'lastAccessAt' => null,
            'accessCount' => 0,
            'isActive' => false,
        ];
    } else {
        $response = [
            'lastAccessAt' => StringUtil::convertToISO8601($yourData->getLastAccessAt()),
            'accessCount' => $yourData->getAccessCount(),
            'isActive' => $yourData->getIsActive(),
        ];
    }

    $result['yourKey'] = $response;

    return $result;
}
```

### テンプレート5: Collection処理(foreachループ)

```php
/**
 * @param array<mixed> $result
 * @param Collection<YourItemInterface> $items
 * @param bool $isMulti
 * @return array<mixed>
 */
public function addYourItemsData(array $result, Collection $items, bool $isMulti): array
{
    $response = [];

    foreach ($items as $item) {
        /** @var YourItemInterface $item */
        $response[] = [
            'id' => $item->getId(),
            'name' => $item->getName(),
            'amount' => $item->getAmount(),
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

### テンプレート6: Collection処理(formatToResponse活用)

```php
/**
 * @param array<mixed> $result
 * @param Collection<YourDataClass> $dataList
 * @return array<mixed>
 */
public function addYourDataList(array $result, Collection $dataList): array
{
    $response = [];

    foreach ($dataList as $data) {
        /** @var YourDataClass $data */
        $response[] = $data->formatToResponse();
    }

    $result['dataList'] = $response;

    return $result;
}
```

### テンプレート7: Collection処理(map活用)

```php
/**
 * @param array<mixed> $result
 * @param Collection<YourReward> $rewards
 * @return array<mixed>
 */
public function addYourRewardsData(array $result, Collection $rewards): array
{
    $response = $rewards->map(function (YourReward $reward) {
        return $reward->formatToResponse();
    })->toArray();

    $result['rewards'] = $response;

    return $result;
}
```

### テンプレート8: Collection処理(groupByで集計)

```php
/**
 * @param array<mixed> $result
 * @param Collection<YourReward> $rewards
 * @return array<mixed>
 */
public function addGroupedRewardsData(array $result, Collection $rewards): array
{
    $grouped = $rewards->groupBy(function (YourReward $reward) {
        return $reward->getType() . $reward->getResourceId();
    })->map(function ($items) {
        /** @var YourReward $firstItem */
        $firstItem = $items->first();
        $response = $firstItem->formatToResponse();

        // 個数を合計
        $totalAmount = $items->sum(function (YourReward $reward) {
            return $reward->getAmount();
        });
        $response['totalAmount'] = $totalAmount;

        return $response;
    });

    $result['groupedRewards'] = $grouped->values()->toArray();

    return $result;
}
```

---

## 日時データ変換パターン

### パターン1: 単一の日時フィールド

```php
use App\Domain\Common\Utils\StringUtil;

$result['serverTime'] = StringUtil::convertToISO8601($resultData->serverTime->toDateTimeString());
```

### パターン2: 複数の日時フィールド

```php
use App\Domain\Common\Utils\StringUtil;

$result['yourData'] = [
    'id' => $data->getId(),
    'name' => $data->getName(),
    'createdAt' => StringUtil::convertToISO8601($data->getCreatedAt()),
    'updatedAt' => StringUtil::convertToISO8601($data->getUpdatedAt()),
    'expiresAt' => StringUtil::convertToISO8601($data->getExpiresAt()),
];
```

### パターン3: null許容の日時フィールド

```php
use App\Domain\Common\Utils\StringUtil;

$renotifyAt = $data->getRenotifyAt();
if ($renotifyAt !== null) {
    $renotifyAt = StringUtil::convertToISO8601($renotifyAt);
}

$result['yourData'] = [
    'id' => $data->getId(),
    'renotifyAt' => $renotifyAt,  // null または変換済み文字列
];
```

### パターン4: Collectionループ内での変換

```php
use App\Domain\Common\Utils\StringUtil;

$response = [];
foreach ($items as $item) {
    $response[] = [
        'id' => $item->getId(),
        'name' => $item->getName(),
        'timestamp' => StringUtil::convertToISO8601($item->getTimestamp()),
    ];
}
$result['items'] = $response;
```

---

## Collectionループパターン

### パターン1: 基本的なforeach

```php
$response = [];
foreach ($collection as $item) {
    /** @var YourItemInterface $item */
    $response[] = [
        'id' => $item->getId(),
        'name' => $item->getName(),
    ];
}
$result['items'] = $response;
```

### パターン2: formatToResponseを使う

```php
$response = [];
foreach ($collection as $item) {
    /** @var YourDataClass $item */
    $response[] = $item->formatToResponse();
}
$result['items'] = $response;
```

### パターン3: mapメソッド

```php
$response = $collection->map(function ($item) {
    return [
        'id' => $item->getId(),
        'name' => $item->getName(),
    ];
})->toArray();
$result['items'] = $response;
```

### パターン4: mapでformatToResponse

```php
$response = $collection->map(function (YourDataClass $item) {
    return $item->formatToResponse();
})->toArray();
$result['items'] = $response;
```

### パターン5: filterとmap

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
$result['items'] = $response;
```

### パターン6: 複数のCollectionをマージ

```php
use App\Domain\Common\Utils\StringUtil;

$firstRewards = $firstCollection->map(function ($reward) {
    return $reward->formatToResponse();
});

$secondRewards = $secondCollection->map(function ($reward) {
    return $reward->formatToResponse();
});

$result['allRewards'] = $firstRewards
    ->merge($secondRewards)
    ->values()
    ->toArray();
```

---

## nullチェックパターン

### パターン1: nullなら早期リターン

```php
public function addYourData(array $result, ?YourDataInterface $yourData): array
{
    if ($yourData === null) {
        $result['yourKey'] = null;
        return $result;
    }

    $result['yourKey'] = [
        'id' => $yourData->getId(),
        'name' => $yourData->getName(),
    ];

    return $result;
}
```

### パターン2: nullならデフォルト値

```php
public function addYourData(array $result, ?YourDataInterface $yourData): array
{
    $response = [];

    if ($yourData === null) {
        $response = [
            'count' => 0,
            'lastAccessAt' => null,
        ];
    } else {
        $response = [
            'count' => $yourData->getCount(),
            'lastAccessAt' => StringUtil::convertToISO8601($yourData->getLastAccessAt()),
        ];
    }

    $result['yourKey'] = $response;

    return $result;
}
```

### パターン3: フィールドごとにnullチェック

```php
use App\Domain\Common\Utils\StringUtil;

$expiresAt = $data->getExpiresAt();
if ($expiresAt !== null) {
    $expiresAt = StringUtil::convertToISO8601($expiresAt);
}

$result['yourData'] = [
    'id' => $data->getId(),
    'expiresAt' => $expiresAt,  // null または変換済み
];
```

### パターン4: Collectionがnullまたは空

```php
public function addYourItemsData(array $result, ?Collection $items, bool $isMulti): array
{
    $response = [];

    if ($items !== null) {
        foreach ($items as $item) {
            $response[] = [
                'id' => $item->getId(),
                'name' => $item->getName(),
            ];
        }
    }

    if ($isMulti) {
        $result['items'] = $response;  // 空配列またはデータ
    } else {
        $result['item'] = count($response) > 0 ? $response[0] : [];
    }

    return $result;
}
```

---

## 単数形・複数形の切り替え

### テンプレート: isMultiパラメータ

```php
/**
 * @param array<mixed> $result
 * @param Collection<YourItemInterface> $items
 * @param bool $isMulti
 * @return array<mixed>
 */
public function addYourItemData(array $result, Collection $items, bool $isMulti): array
{
    $response = [];

    foreach ($items as $item) {
        /** @var YourItemInterface $item */
        $response[] = [
            'id' => $item->getId(),
            'name' => $item->getName(),
        ];
    }

    if ($isMulti) {
        // 複数形のキー
        $result['items'] = $response;
    } else {
        // 単数形のキー (最初の1件、または空配列)
        $result['item'] = count($response) > 0 ? $response[0] : [];
    }

    return $result;
}
```

### 使用例

```php
// 複数のアイテムを返す
$result = $this->responseDataFactory->addYourItemData($result, $items, true);
// → { "items": [...] }

// 単一のアイテムを返す
$result = $this->responseDataFactory->addYourItemData($result, $items, false);
// → { "item": {...} } または { "item": [] }
```

---

## 完全な実装例

### 例1: シンプルなResponseFactoryメソッド

```php
use App\Http\Responses\ResultData\YourResultData;
use Illuminate\Http\JsonResponse;

public function createYourResponse(YourResultData $resultData): JsonResponse
{
    $result = [];

    $result = $this->responseDataFactory->addUsrParameterData($result, $resultData->usrUserParameter);
    $result = $this->responseDataFactory->addUsrItemData($result, $resultData->usrItems, true);

    return response()->json($result);
}
```

### 例2: シンプルなResponseDataFactoryメソッド

```php
use App\Domain\Common\Utils\StringUtil;

/**
 * @param array<mixed> $result
 * @param YourDataInterface $yourData
 * @return array<mixed>
 */
public function addYourData(array $result, YourDataInterface $yourData): array
{
    $result['yourKey'] = [
        'id' => $yourData->getId(),
        'name' => $yourData->getName(),
        'createdAt' => StringUtil::convertToISO8601($yourData->getCreatedAt()),
        'count' => $yourData->getCount(),
    ];

    return $result;
}
```

### 例3: Collectionを処理するResponseDataFactoryメソッド

```php
use App\Domain\Common\Utils\StringUtil;

/**
 * @param array<mixed> $result
 * @param Collection<YourItemInterface> $items
 * @param bool $isMulti
 * @return array<mixed>
 */
public function addYourItemsData(array $result, Collection $items, bool $isMulti): array
{
    $response = [];

    foreach ($items as $item) {
        /** @var YourItemInterface $item */
        $response[] = [
            'id' => $item->getId(),
            'name' => $item->getName(),
            'timestamp' => StringUtil::convertToISO8601($item->getTimestamp()),
            'amount' => $item->getAmount(),
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
