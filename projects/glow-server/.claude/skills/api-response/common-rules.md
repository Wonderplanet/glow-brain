# APIレスポンス作成の共通ルール

## 目次

- [日時データの変換](#日時データの変換)
- [レスポンスキーの命名規則](#レスポンスキーの命名規則)
- [nullとempty配列の扱い](#nullとempty配列の扱い)
- [型定義とPHPDoc](#型定義とphpdoc)
- [glow-schemaとの整合性](#glow-schemaとの整合性)

---

## 日時データの変換

### ✅ 必須ルール

**すべての日時データは `StringUtil::convertToISO8601()` で変換してからレスポンスする**

```php
use App\Domain\Common\Utils\StringUtil;

// ✅ 正しい実装
'staminaUpdatedAt' => StringUtil::convertToISO8601($usrUserParameter->getStaminaUpdatedAt()),
'lastLoginAt' => StringUtil::convertToISO8601($usrUserLogin->getLastLoginAt()),
'expiresAt' => StringUtil::convertToISO8601($expiresAt),
```

### ❌ 禁止パターン

```php
// ❌ 変換せずに直接返す
'staminaUpdatedAt' => $usrUserParameter->getStaminaUpdatedAt(),

// ❌ 他の方法で変換
'staminaUpdatedAt' => $usrUserParameter->getStaminaUpdatedAt()->format('c'),
```

### StringUtil::convertToISO8601の仕様

**ファイルパス:** `api/app/Domain/Common/Utils/StringUtil.php`

```php
public static function convertToISO8601(?string $dateString): ?string
{
    if (is_null($dateString) || $dateString === '') {
        return $dateString;
    }

    $dateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $dateString);
    return $dateTime->format(\DateTime::ATOM);
}
```

**動作:**
- `null` または空文字列の場合はそのまま返す
- `Y-m-d H:i:s` 形式の文字列をISO8601形式(ATOM形式)に変換
- 例: `"2024-01-15 10:30:00"` → `"2024-01-15T10:30:00+00:00"`

### 実装例

```php
// api/app/Http/ResponseFactories/ResponseDataFactory.php:167
public function addUsrParameterData(array $result, UsrParameterData $usrUserParameter): array
{
    $result['usrParameter'] = [
        'level' => $usrUserParameter->getLevel(),
        'exp' => $usrUserParameter->getExp(),
        'coin' => $usrUserParameter->getCoin(),
        'stamina' => $usrUserParameter->getStamina(),
        'staminaUpdatedAt' => StringUtil::convertToISO8601($usrUserParameter->getStaminaUpdatedAt()),
        // ↑ 日時データは必ず変換
        'freeDiamond' => $usrUserParameter->getFreeDiamond(),
        'paidDiamondIos' => $usrUserParameter->getPaidDiamondIos(),
        'paidDiamondAndroid' => $usrUserParameter->getPaidDiamondAndroid(),
    ];

    return $result;
}
```

---

## レスポンスキーの命名規則

### 基本ルール

1. **camelCase を使用**
   ```php
   // ✅ 正しい
   'usrParameter' => [...]
   'lastLoginAt' => $value
   'mstShopPassId' => $id

   // ❌ 間違い
   'usr_parameter' => [...]
   'last_login_at' => $value
   ```

2. **glow-schemaのYAML定義と一致させる**
   - APIレスポンスの内容はglow-schemaリポジトリのyamlで定義
   - 定義されたキーと完全に一致させる必要がある

3. **単数形・複数形の使い分け**
   ```php
   // 単数データ
   'usrParameter' => [...]
   'usrItem' => [...]

   // 複数データ(配列)
   'usrParameters' => [...]
   'usrItems' => [...]
   ```

### 実装例

```php
// api/app/Http/ResponseFactories/ResponseDataFactory.php:475-493
public function addUsrItemData(array $result, Collection $usrItems, bool $isMulti): array
{
    $response = [];
    foreach ($usrItems as $usrItem) {
        $response[] = [
            'mstItemId' => $usrItem->getMstItemId(),
            'amount' => $usrItem->getAmount(),
        ];
    }

    // isMultiフラグで単数形・複数形を切り替え
    if ($isMulti) {
        $result['usrItems'] = $response;  // 複数形
    } else {
        $result['usrItem'] = count($response) > 0 ? $response[0] : [];  // 単数形
    }

    return $result;
}
```

---

## nullとempty配列の扱い

### nullの扱い

```php
// ✅ データがnullの場合はnullを返す
public function addUsrIdleIncentiveData(array $result, ?UsrIdleIncentiveInterface $usrIdleIncentive): array
{
    $key = 'usrIdleIncentive';

    if ($usrIdleIncentive === null) {
        $result[$key] = null;  // nullはnullとして返す
        return $result;
    }

    $result[$key] = [
        'diamondQuickReceiveCount' => $usrIdleIncentive->getDiamondQuickReceiveCount(),
        // ...
    ];
    return $result;
}
```

### 空配列の扱い

```php
// ✅ データがない場合は空配列を返す
public function addUsrItemData(array $result, Collection $usrItems, bool $isMulti): array
{
    $response = [];
    foreach ($usrItems as $usrItem) {
        $response[] = [
            'mstItemId' => $usrItem->getMstItemId(),
            'amount' => $usrItem->getAmount(),
        ];
    }

    if ($isMulti) {
        $result['usrItems'] = $response;  // 空でも配列として返す
    } else {
        $result['usrItem'] = count($response) > 0 ? $response[0] : [];  // データがなければ空配列
    }

    return $result;
}
```

### nullチェックのパターン

```php
// パターン1: nullの場合は特別な値を設定
if (is_null($usrUserLogin)) {
    $response = [
        'lastLoginAt' => null,
        'loginDayCount' => 0,
        'loginContinueDayCount' => 0,
    ];
}

// パターン2: nullチェック後に変換
$renotifyAt = $usrStoreInfo->getRenotifyAt();
if ($renotifyAt !== null) {
    $renotifyAt = StringUtil::convertToISO8601($renotifyAt);
}
```

---

## 型定義とPHPDoc

### メソッドの型定義

```php
/**
 * @param array<mixed> $result
 * @param UsrParameterData $usrUserParameter
 * @return array<mixed>
 */
public function addUsrParameterData(array $result, UsrParameterData $usrUserParameter): array
{
    // ...
}
```

### Collectionの型定義

```php
/**
 * @param array<mixed> $result
 * @param Collection<UsrItemInterface> $usrItems
 * @param bool $isMulti
 * @return array<mixed>
 */
public function addUsrItemData(array $result, Collection $usrItems, bool $isMulti): array
{
    // ...
}
```

### 複雑な型の場合

```php
/**
 * @param array<mixed> $result
 * @param Collection<StageFirstClearReward> $stageFirstClearRewards
 * @param Collection<StageAlwaysClearReward> $stageAlwaysClearRewards
 * @param Collection<StageRandomClearReward> $stageRandomClearRewards
 * @param Collection<StageSpeedAttackClearReward> $stageSpeedAttackClearRewards
 * @return array<mixed>
 */
public function addStageRewardData(
    array $result,
    Collection $stageFirstClearRewards,
    Collection $stageAlwaysClearRewards,
    Collection $stageRandomClearRewards,
    Collection $stageSpeedAttackClearRewards,
): array {
    // ...
}
```

---

## glow-schemaとの整合性

### 基本原則

**ファイルパス:** `api/app/Http/ResponseFactories/ResponseDataFactory.php:90-99`

```php
/**
 * DomainごとのResponseFactoryで使う、レスポンスデータの生成をまとめたクラス
 *
 * APIレスポンスの内容は、glow-schemaリポジトリのyamlで定義している。
 * 対応するキーとレスポンス内容を全APIで統一するために、ResponseDataFactoryでレスポンスする配列を生成する。
 * 例：usrUserParameterのレスポンスは、usrParameterというキーで返すことになっている。
 *
 * DomainごとのResponseFactoryでやることは、ResultDataの情報を使って、どんなレスポンス配列を作成する必要があるかを把握し、
 * ResponseDataFactoryの関数を組み合わせ、最終的に必要な内容へ調整すること。
 */
```

### チェックリスト

- [ ] glow-schemaのYAML定義を確認したか
- [ ] レスポンスキーが定義と一致しているか
- [ ] データ型が定義と一致しているか
- [ ] 必須フィールドが含まれているか
- [ ] オプショナルフィールドのnull処理が適切か

### 実装時の注意

1. **レスポンスキーは統一する**
   - 同じデータには常に同じキーを使用
   - 例: ユーザーパラメータは常に `usrParameter`

2. **クライアント互換性を考慮**
   ```php
   // クライアント側の変更対応を避けるために一旦oprのままにする
   $result['oprInGameNotices'] = $response;
   ```
   ※ 本来は `mngInGameNotices` だが互換性のため古いキーを使用

3. **データの整合性を保証**
   - ResponseDataFactoryのメソッドを使うことで全APIで統一
   - 直接配列を作らず、既存メソッドを活用

---

## formatToResponseメソッドの活用

### DataクラスのformatToResponse

多くのDataクラスには `formatToResponse()` メソッドが実装されています。

```php
// api/app/Http/Responses/Data/UsrPvpStatusData.php:50-59
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

### 活用パターン

```php
// ResponseDataFactoryから利用
public function addUsrPvpStatusData(array $result, UsrPvpStatusData $usrPvpStatusData): array
{
    $result['usrPvpStatus'] = $usrPvpStatusData->formatToResponse();
    return $result;
}

// Collectionをループで処理
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

### Rewardクラスの getRewardResponseData()

報酬データには専用のメソッドがあります。

```php
// api/app/Domain/Resource/Entities/Rewards/BaseReward.php:292-297
public function getRewardResponseData(): array
{
    return [
        'reward' => $this->formatToResponse(),
    ];
}
```

使用例:
```php
public function addIdleIncentiveRewardData(array $result, Collection $rewards): array
{
    $response = [];
    foreach ($rewards as $reward) {
        $response[] = $reward->getRewardResponseData();
    }
    $result['rewards'] = $response;
    return $result;
}
```

---

## チェックリスト

新しいAPIレスポンスを実装する際は、以下を確認してください:

### 日時データ
- [ ] すべての日時フィールドで `StringUtil::convertToISO8601()` を使用している
- [ ] nullチェックは `StringUtil::convertToISO8601()` 内部で行われるため不要と理解している

### レスポンスキー
- [ ] camelCaseで命名している
- [ ] glow-schemaのYAML定義と一致している
- [ ] 単数形・複数形を正しく使い分けている

### null/空配列
- [ ] null値は適切に処理している
- [ ] 空配列は適切に返している
- [ ] オプショナルなデータのnullチェックを実装している

### 型定義
- [ ] PHPDocで適切に型を定義している
- [ ] Collectionの型パラメータを記述している
- [ ] 引数と戻り値の型ヒントを付けている

### コード品質
- [ ] 既存の `ResponseDataFactory` メソッドを再利用している
- [ ] `formatToResponse()` や `getRewardResponseData()` を活用している
- [ ] 重複コードを避けている
