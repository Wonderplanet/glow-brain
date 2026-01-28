# RewardSendPolicy の使い方

RewardSendPolicyは、報酬送付時の動作を制御するポリシークラスです。特にリソース上限超過時の挙動を柔軟に設定できます。

## 目次

- [RewardSendPolicyとは](#rewardsendpolicyとは)
- [ポリシーの種類](#ポリシーの種類)
- [RewardSendMethodの種類](#rewardsendmethodの種類)
- [メールボックス送信の仕組み](#メールボックス送信の仕組み)
- [エラーハンドリング](#エラーハンドリング)
- [使い分けガイド](#使い分けガイド)

## RewardSendPolicyとは

**ファイルパス**: `api/app/Domain/Resource/Entities/RewardSendPolicy.php`

RewardSendPolicyは、報酬タイプごとに異なる送付方法（RewardSendMethod）を設定できるクラスです。

### クラス定義

```php
class RewardSendPolicy
{
    public function __construct(
        private RewardSendMethod $coinSendMethod = RewardSendMethod::NONE,
        private RewardSendMethod $freeDiamondSendMethod = RewardSendMethod::SEND_TO_MESSAGE,
        private RewardSendMethod $staminaSendMethod = RewardSendMethod::NONE,
        private RewardSendMethod $itemSendMethod = RewardSendMethod::NONE,
        private RewardSendMethod $expSendMethod = RewardSendMethod::NONE,
        private RewardSendMethod $emblemSendMethod = RewardSendMethod::NONE,
        private RewardSendMethod $unitSendMethod = RewardSendMethod::NONE,
        private ?GameException $resourceLimitReachedException = null,
    ) {}
}
```

### 主なメソッド

- `getSendMethodByRewardType(string $rewardType): RewardSendMethod`
- `throwResourceLimitReachedExceptionIfSet(): void`
- `getRewardTypesOfThrowErrorWhenResourceLimitReached(array $rewardTypes): array`

## ポリシーの種類

### 1. デフォルトポリシー

**使用場面**: ほとんどの通常の報酬送付

```php
$policy = RewardSendPolicy::createDefaultPolicy();

// または null を渡す（デフォルトポリシーが自動適用される）
$this->rewardDelegator->sendRewards($usrUserId, $platform, $now, policy: null);
```

**動作**:
- コイン: 上限超過時は何もしない（NONE）
- 無償プリズム: 上限超過時はメールボックスへ（SEND_TO_MESSAGE）
- スタミナ: 上限超過時は何もしない（NONE）
- アイテム: 上限超過時は何もしない（NONE）
- 経験値: 上限超過時は何もしない（NONE）
- エンブレム: 上限超過時は何もしない（NONE） ※重複時はコイン変換される
- ユニット: 上限超過時は何もしない（NONE） ※重複時はアイテム変換される

### 2. エラー投げるポリシー

**使用場面**: メールボックス受取など、上限超過を許容できない場合

```php
$policy = RewardSendPolicy::createThrowErrorWhenResourceLimitReachedPolicy(
    new GameException(
        ErrorCode::MESSAGE_REWARD_BY_OVER_MAX,
        'Cannot receive the message reward because it exceeds the resource limit.',
    ),
);

$this->rewardDelegator->sendRewards($usrUserId, $platform, $now, $policy);
```

**動作**:
- コイン: 上限超過時は例外を投げる（THROW_ERROR_WHEN_RESOURCE_LIMIT_REACHED）
- 無償プリズム: 上限超過時は例外を投げる（THROW_ERROR_WHEN_RESOURCE_LIMIT_REACHED）
- スタミナ: 上限超過時は例外を投げる（THROW_ERROR_WHEN_RESOURCE_LIMIT_REACHED）
- アイテム: 上限超過時は例外を投げる（THROW_ERROR_WHEN_RESOURCE_LIMIT_REACHED）
- 経験値: 上限超過時は例外を投げる（THROW_ERROR_WHEN_RESOURCE_LIMIT_REACHED）
- エンブレム: 上限超過時は何もしない（NONE） ※重複時はコイン変換される
- ユニット: 上限超過時は何もしない（NONE） ※重複時はアイテム変換される

**重要**: エンブレムとユニットは重複時に他リソースに変換されるため、上限超過エラーは投げない。

## RewardSendMethodの種類

**ファイルパス**: `api/app/Domain/Resource/Enums/RewardSendMethod.php`

```php
enum RewardSendMethod
{
    // 上限超過時は何もしない（配布しない）
    case NONE;

    // 上限超過時はメールボックスへ送信
    case SEND_TO_MESSAGE;

    // 上限超過時は例外を投げる
    case THROW_ERROR_WHEN_RESOURCE_LIMIT_REACHED;
}
```

## メールボックス送信の仕組み

### 自動メールボックス送信

RewardSendServiceは、`SEND_TO_MESSAGE`が設定されている報酬タイプで上限超過が発生した場合、自動的にメールボックスへ送信します。

**ファイルパス**: `api/app/Domain/Reward/Services/RewardSendService.php:240-271`

```php
private function sendToMessage(
    string $usrUserId,
    Collection $rewards,
): RewardSent {
    // メールボックス送信対象の報酬を抽出
    $messageSystemRewards = $rewards->filter(function (BaseReward $reward) {
        return $reward->getUnreceivedRewardReason() === UnreceivedRewardReason::SENT_TO_MESSAGE
            && $reward->isSent() === false;
    });

    if ($messageSystemRewards->isEmpty()) {
        return new RewardSent($rewards);
    }

    // メッセージ送信
    $messageDelegator = app(MessageDelegator::class);
    foreach ($messageSystemRewards as $reward) {
        $messageDelegator->addNewSystemMessage(
            usrUserId: $usrUserId,
            rewardGroupId: null,
            expiredAt: null, // 無期限
            reward: $reward,
            title: MessageConstant::REWARD_UNRECEIVED_TITLE,
            body: MessageConstant::REWARD_UNRECEIVED_BODY,
            prefixMessageSource: MessageSource::RESOURCE_LIMIT_REACHED->value,
        );

        $reward->markAsSent();
    }

    return new RewardSent($rewards);
}
```

### 個別SendServiceでのマーキング

個別SendService（例: FreeDiamondSendService）で上限超過を検知した場合、報酬に`SENT_TO_MESSAGE`マークを付けます。

**例: FreeDiamondSendService（想定実装）**

```php
class FreeDiamondSendService implements RewardSendServiceInterface
{
    use RewardSendServiceTrait;

    public function send(RewardSendContext $context): RewardSent
    {
        $usrUserId = $context->getUsrUserId();
        $rewards = $context->getRewards();
        $now = $context->getNow();

        // 報酬配布を試行し、上限超過ならメールボックス送信マークを付ける
        $this->trySendRewardsOrMarkAsSentToMessage(
            $rewards,
            fn() => $this->currencyService->addFreeDiamondByRewards(
                usrUserId: $usrUserId,
                rewards: $rewards,
                now: $now,
            )
        );

        return new RewardSent($rewards);
    }
}
```

**RewardSendServiceTraitの実装**: `api/app/Domain/Reward/Traits/RewardSendServiceTrait.php:48-64`

```php
public function trySendRewardsOrMarkAsSentToMessage(
    Collection $rewards,
    callable $sendCallback,
): void {
    try {
        $sendCallback();
        $this->markRewardsAsSent($rewards);
    } catch (\Throwable $e) {
        if ($this->isCurrencyOverflowException($e)) {
            // プリズム上限超過の場合、メールボックス送信マークを付ける
            $this->markRewardsAsSentToMessage($rewards);
            return;
        }

        // その他の例外はそのまま投げる
        throw $e;
    }
}
```

## エラーハンドリング

### リソース上限超過の検知

RewardSendServiceは2箇所でリソース上限超過をチェックします：

#### 1. 個別SendService実行時

```php
try {
    $rewardSendSummary = $this->execSendRewards(
        usrUserId: $usrUserId,
        platform: $platform,
        now: $now,
        policy: $policy,
    );
} catch (\Throwable $e) {
    if ($this->isCurrencyOverflowException($e)) {
        // プリズム上限超過時の例外
        $policy->throwResourceLimitReachedExceptionIfSet();
    }

    throw $e; // 指定された例外がない or その他例外はそのまま投げる
}
```

#### 2. 報酬送信完了後

```php
private function checkAndThrowErrorByRewarSendSummary(
    RewardSendSummary $rewardSendSummary,
    RewardSendPolicy $policy,
): void {
    $throwErrorRewardTypes = $policy->getRewardTypesOfThrowErrorWhenResourceLimitReached(
        array_keys($this->sendServiceClasses),
    );

    if ($rewardSendSummary->hasResourceOverflow($throwErrorRewardTypes)) {
        $policy->throwResourceLimitReachedExceptionIfSet();
    }
}
```

### 例外の種類

```php
// プリズム上限超過（有償）
WpCurrencyAddCurrencyOverByMaxException

// プリズム上限超過（無償）
WpCurrencyAddFreeCurrencyOverByMaxException
```

これらの例外は`isCurrencyOverflowException()`メソッドで検知されます。

## 使い分けガイド

### ✅ デフォルトポリシーを使うべき場合

- クエストクリア報酬
- ガシャ結果
- ショップ購入
- ログインボーナス
- ミッション報酬（一部）

**理由**: 上限超過時にエラーにせず、無償プリズムはメールボックスへ、その他は配布しない動作が適切。

### ✅ エラー投げるポリシーを使うべき場合

- メールボックス受取
- アイテム交換
- 特定のミッション報酬受取

**理由**: ユーザーが明示的に「受け取る」操作をする場合、上限超過時はエラーを返すべき。

### 実装例の比較

#### ❌ 間違った使い方

```php
// メールボックス受取でデフォルトポリシーを使う（間違い）
$this->rewardDelegator->sendRewards($usrUserId, $platform, $now);
// → 上限超過時に報酬が配布されず、ユーザーに通知もされない
```

#### ✅ 正しい使い方

```php
// メールボックス受取でエラー投げるポリシーを使う（正しい）
$policy = RewardSendPolicy::createThrowErrorWhenResourceLimitReachedPolicy(
    new GameException(
        ErrorCode::MESSAGE_REWARD_BY_OVER_MAX,
        'Cannot receive the message reward because it exceeds the resource limit.',
    ),
);
$this->rewardDelegator->sendRewards($usrUserId, $platform, $now, $policy);
// → 上限超過時にエラーが返され、ユーザーに適切に通知される
```

## カスタムポリシーの作成

デフォルトポリシーやエラー投げるポリシー以外の動作が必要な場合、カスタムポリシーを作成できます。

### パターン1: 特定のリソースのみカスタマイズ

```php
// 例: アイテムのみメールボックスへ送信し、他はデフォルト動作
$policy = new RewardSendPolicy(
    coinSendMethod: RewardSendMethod::NONE,
    freeDiamondSendMethod: RewardSendMethod::SEND_TO_MESSAGE, // デフォルト
    staminaSendMethod: RewardSendMethod::NONE,
    itemSendMethod: RewardSendMethod::SEND_TO_MESSAGE, // カスタマイズ
    expSendMethod: RewardSendMethod::NONE,
    emblemSendMethod: RewardSendMethod::NONE,
    unitSendMethod: RewardSendMethod::NONE,
);

$this->rewardDelegator->sendRewards($usrUserId, $platform, $now, $policy);
```

### パターン2: 一部のリソースのみエラーを投げる

```php
// 例: コインとアイテムのみエラーを投げ、他はデフォルト動作
$policy = new RewardSendPolicy(
    coinSendMethod: RewardSendMethod::THROW_ERROR_WHEN_RESOURCE_LIMIT_REACHED,
    freeDiamondSendMethod: RewardSendMethod::SEND_TO_MESSAGE, // デフォルト
    staminaSendMethod: RewardSendMethod::NONE,
    itemSendMethod: RewardSendMethod::THROW_ERROR_WHEN_RESOURCE_LIMIT_REACHED,
    expSendMethod: RewardSendMethod::NONE,
    emblemSendMethod: RewardSendMethod::NONE,
    unitSendMethod: RewardSendMethod::NONE,
    resourceLimitReachedException: new GameException(
        ErrorCode::CUSTOM_RESOURCE_LIMIT,
        'コインまたはアイテムの上限に達しました',
    ),
);

$this->rewardDelegator->sendRewards($usrUserId, $platform, $now, $policy);
```

### パターン3: ファクトリメソッドの追加

頻繁に使用するカスタムポリシーは、ファクトリメソッドとして追加できます。

**ファイルパス**: `api/app/Domain/Resource/Entities/RewardSendPolicy.php`

```php
/**
 * ショップ購入用のカスタムポリシー
 * コインとアイテムのみエラーを投げる
 */
public static function createShopPurchasePolicy(
    GameException $resourceLimitReachedException,
): self {
    return new self(
        coinSendMethod: RewardSendMethod::THROW_ERROR_WHEN_RESOURCE_LIMIT_REACHED,
        freeDiamondSendMethod: RewardSendMethod::SEND_TO_MESSAGE,
        staminaSendMethod: RewardSendMethod::NONE,
        itemSendMethod: RewardSendMethod::THROW_ERROR_WHEN_RESOURCE_LIMIT_REACHED,
        expSendMethod: RewardSendMethod::NONE,
        emblemSendMethod: RewardSendMethod::NONE,
        unitSendMethod: RewardSendMethod::NONE,
        resourceLimitReachedException: $resourceLimitReachedException,
    );
}
```

使用例：

```php
$policy = RewardSendPolicy::createShopPurchasePolicy(
    new GameException(
        ErrorCode::SHOP_PURCHASE_RESOURCE_LIMIT,
        'ショップアイテムを購入できません。所持枠を確認してください。',
    ),
);

$this->rewardDelegator->sendRewards($usrUserId, $platform, $now, $policy);
```

### カスタムポリシーのユースケース

| ユースケース | 設定内容 | 理由 |
|------------|---------|------|
| ショップ購入 | コイン・アイテム→エラー、他→デフォルト | 購入失敗を明確に伝える |
| イベント報酬 | 全て→メールボックス | 大量報酬を安全に配布 |
| 交換所 | 全て→エラー | 交換失敗を明確に伝える |
| ガシャ | デフォルト | 上限超過は許容 |

## RewardSendMethodの詳細

各RewardSendMethodの動作を詳しく説明します。

### NONE（デフォルト）

```php
RewardSendMethod::NONE
```

**動作**:
- 上限超過時: 配布を試行し、配布できなければそのまま（何もしない）
- isSent: 配布成功時のみtrue
- UnreceivedRewardReason: 配布失敗時は変更されない（NONE or 個別SendServiceで設定）

**使用場面**:
- コイン、スタミナ、アイテム、経験値など、上限超過を許容できるリソース
- エンブレム、ユニット（重複時は自動変換される）

### SEND_TO_MESSAGE

```php
RewardSendMethod::SEND_TO_MESSAGE
```

**動作**:
- 上限超過時: メールボックスへ自動送信
- isSent: メールボックス送信後はtrue
- UnreceivedRewardReason: SENT_TO_MESSAGE

**使用場面**:
- 無償プリズム（デフォルト設定）
- 大量配布が予想されるイベント報酬
- ユーザーに確実に渡したい報酬

### THROW_ERROR_WHEN_RESOURCE_LIMIT_REACHED

```php
RewardSendMethod::THROW_ERROR_WHEN_RESOURCE_LIMIT_REACHED
```

**動作**:
- 上限超過時: 指定された例外を投げる
- isSent: 例外が投げられるため設定されない
- UnreceivedRewardReason: 例外が投げられるため設定されない

**使用場面**:
- メールボックス受取
- ショップ購入、交換所など、明示的な受取操作
- 上限超過を許容できない場合

## まとめ

- **RewardSendPolicy**: 報酬タイプごとの送付方法を制御
- **デフォルトポリシー**: 通常の報酬送付で使用（無償プリズムのみメールボックスへ）
- **エラー投げるポリシー**: 明示的な受取操作で使用（上限超過時にエラー）
- **カスタムポリシー**: 特定のリソースのみ動作を変更できる
- **ファクトリメソッド**: 頻繁に使用するポリシーはファクトリメソッドとして追加
- **メールボックス自動送信**: RewardSendServiceが自動的に処理
- **エラーハンドリング**: 2箇所でチェック（実行時＋完了後）

詳細な実装例は以下を参照してください：
- **[examples/basic-usage.md](../examples/basic-usage.md)** - デフォルトポリシーの使用例
- **[examples/error-handling.md](../examples/error-handling.md)** - エラー投げるポリシーの使用例
