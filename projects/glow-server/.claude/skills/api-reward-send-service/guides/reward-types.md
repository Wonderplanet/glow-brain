# 報酬タイプと個別SendService

各報酬タイプに対応するSendServiceの実装パターンを解説します。

## 目次

- [報酬タイプ一覧](#報酬タイプ一覧)
- [SendServiceの基本パターン](#sendserviceの基本パターン)
- [各SendServiceの実装](#各sendserviceの実装)
- [新しいSendServiceの追加方法](#新しいsendserviceの追加方法)

## 報酬タイプ一覧

**定義ファイル**: `api/app/Domain/Resource/Enums/RewardType.php`

| RewardType | SendService | 委譲先Delegator | 特徴 |
|------------|-------------|----------------|------|
| COIN | CoinSendService | UserDelegator | シンプルな配布 |
| FREE_DIAMOND | FreeDiamondSendService | AppCurrencyDelegator | メールボックス対応 |
| STAMINA | StaminaSendService | UserDelegator | シンプルな配布 |
| ITEM | ItemSendService | ItemDelegator | シンプルな配布 |
| EXP | ExpSendService | UserDelegator | レベルアップ報酬追加 |
| EMBLEM | EmblemSendService | EmblemDelegator | 重複時コイン変換 |
| UNIT | UnitSendService | UnitDelegator | 重複時アイテム変換 |

## SendServiceの基本パターン

すべてのSendServiceは`RewardSendServiceInterface`を実装します。

**インターフェース**: `api/app/Domain/Reward/Services/RewardSendServiceInterface.php`

```php
interface RewardSendServiceInterface
{
    public function send(RewardSendContext $context): RewardSent;
}
```

### RewardSendContext

**ファイルパス**: `api/app/Domain/Reward/Entities/RewardSendContext.php`

```php
class RewardSendContext
{
    public function __construct(
        private string $usrUserId,
        private int $platform,
        private Collection $rewards,  // Collection<BaseReward>
        private CarbonImmutable $now,
        private RewardSendMethod $sendMethod,
    ) {}

    public function getUsrUserId(): string
    public function getPlatform(): int
    public function getRewards(): Collection
    public function getNow(): CarbonImmutable
    public function getSendMethod(): RewardSendMethod
}
```

### RewardSent

**ファイルパス**: `api/app/Domain/Reward/Entities/RewardSent.php`

```php
class RewardSent
{
    public function __construct(
        private Collection $rewards,  // Collection<BaseReward>
    ) {}

    public function getRewards(): Collection
}
```

## 各SendServiceの実装

### 1. CoinSendService（シンプルパターン）

**ファイルパス**: `api/app/Domain/Reward/Services/CoinSendService.php`

最もシンプルな実装パターン。各ドメインのDelegatorに委譲するだけ。

```php
class CoinSendService implements RewardSendServiceInterface
{
    public function __construct(
        private UserDelegator $userDelegator,
    ) {}

    public function send(RewardSendContext $context): RewardSent
    {
        $usrUserId = $context->getUsrUserId();
        $rewards = $context->getRewards();
        $now = $context->getNow();

        // UserDelegatorに委譲
        $this->userDelegator->addCoinByRewards(
            usrUserId: $usrUserId,
            rewards: $rewards,
            now: $now,
        );

        return new RewardSent($rewards);
    }
}
```

**実装のポイント**:
- コンストラクタで対応するDelegatorを注入
- `send()`メソッドでDelegatorに委譲
- `RewardSent`を返却

### 2. ItemSendService（シンプルパターン）

**ファイルパス**: `api/app/Domain/Reward/Services/ItemSendService.php`

CoinSendServiceと同様のシンプルパターン。

```php
class ItemSendService implements RewardSendServiceInterface
{
    public function __construct(
        private ItemDelegator $itemDelegator,
    ) {}

    public function send(RewardSendContext $context): RewardSent
    {
        $usrUserId = $context->getUsrUserId();
        $rewards = $context->getRewards();
        $now = $context->getNow();

        // ItemDelegatorに委譲
        $this->itemDelegator->addItemByRewards(
            usrUserId: $usrUserId,
            rewards: $rewards,
            now: $now,
        );

        return new RewardSent(rewards: $rewards);
    }
}
```

### 3. StaminaSendService（シンプルパターン）

**ファイルパス**: `api/app/Domain/Reward/Services/StaminaSendService.php`

```php
class StaminaSendService implements RewardSendServiceInterface
{
    public function __construct(
        private UserDelegator $userDelegator,
    ) {}

    public function send(RewardSendContext $context): RewardSent
    {
        $usrUserId = $context->getUsrUserId();
        $rewards = $context->getRewards();
        $now = $context->getNow();

        // UserDelegatorに委譲
        $this->userDelegator->addStaminaByRewards(
            usrUserId: $usrUserId,
            rewards: $rewards,
            now: $now,
        );

        return new RewardSent($rewards);
    }
}
```

### 4. FreeDiamondSendService（メールボックス対応パターン）

**ファイルパス**: `api/app/Domain/Reward/Services/FreeDiamondSendService.php`

`RewardSendServiceTrait`を使用してメールボックス送信に対応。

```php
class FreeDiamondSendService implements RewardSendServiceInterface
{
    use RewardSendServiceTrait;

    public function __construct(
        private AppCurrencyDelegator $appCurrencyDelegator,
    ) {}

    public function send(RewardSendContext $context): RewardSent
    {
        $usrUserId = $context->getUsrUserId();
        $platform = $context->getPlatform();
        $rewards = $context->getRewards();
        $sendMethod = $context->getSendMethod();

        // 集計
        $totalAmount = 0;
        foreach ($rewards as $reward) {
            $totalAmount += $reward->getAmount();
        }

        if ($totalAmount <= 0) {
            // 配布量は0だが、送信済みとしてマークしておく
            $this->markRewardsAsSent($rewards);
            return new RewardSent($rewards);
        }

        // 送信処理
        $sendCallback = function () use ($usrUserId, $platform, $totalAmount, $rewards) {
            $this->appCurrencyDelegator->addIngameFreeDiamond(
                $usrUserId,
                $platform,
                $totalAmount,
                new FreeDiamondSendTrigger($rewards),
            );
        };

        // RewardSendMethodに応じて処理を分岐
        switch ($sendMethod) {
            case RewardSendMethod::SEND_TO_MESSAGE:
                // 上限超過時はメールボックスへ
                $this->trySendRewardsOrMarkAsSentToMessage(
                    $rewards,
                    $sendCallback,
                );
                break;
            default:
                // 通常送信
                $sendCallback();
                break;
        }

        return new RewardSent($rewards);
    }
}
```

**実装のポイント**:
- `RewardSendServiceTrait`を使用
- `trySendRewardsOrMarkAsSentToMessage()`で上限超過対応
- RewardSendMethodで処理を分岐

### 5. ExpSendService（追加報酬パターン）

**ファイルパス**: `api/app/Domain/Reward/Services/ExpSendService.php`

経験値配布後にレベルアップ報酬を追加するパターン。

```php
class ExpSendService implements RewardSendServiceInterface
{
    public function __construct(
        private RewardManagerInterface $rewardManager,
        private UserDelegator $userDelegator,
    ) {}

    public function send(RewardSendContext $context): RewardSent
    {
        $usrUserId = $context->getUsrUserId();
        $rewards = $context->getRewards();
        $now = $context->getNow();

        $usrUserParameter = $this->userDelegator->getUsrUserParameterByUsrUserId($usrUserId);
        $beforeAmount = $usrUserParameter->getExp();

        // 集計＆報酬オブジェクトに前後の値を設定
        $totalAmount = 0;
        foreach ($rewards as $reward) {
            $totalAmount += $reward->getAmount();

            $reward->setBeforeAmount($beforeAmount);
            $afterAmount = $beforeAmount + $reward->getAmount();
            $reward->setAfterAmount($afterAmount);
            $reward->markAsSent();

            $beforeAmount = $afterAmount;
        }

        // 送信
        if ($totalAmount <= 0) {
            return new RewardSent($rewards);
        }

        $userLevelUpData = $this->userDelegator->addExp($usrUserId, $totalAmount, $now);

        // レベルアップ報酬を追加（重要！）
        $this->rewardManager->addRewards($userLevelUpData->levelUpRewards);

        return new RewardSent($rewards);
    }
}
```

**実装のポイント**:
- `RewardManager`をコンストラクタ注入
- 経験値配布後、レベルアップ報酬を`rewardManager->addRewards()`で追加
- RewardSendServiceは2回ループ実行するため、レベルアップ報酬も配布される

### 6. EmblemSendService（重複変換パターン）

**概要**: 重複エンブレムは自動的にコインに変換されます（変換処理は`RewardSendService::convertRewards()`で実行）。

### 7. UnitSendService（重複変換パターン）

**概要**: 重複ユニットは自動的にアイテムに変換されます（変換処理は`RewardSendService::convertRewards()`で実行）。

## 新しいSendServiceの追加方法

新しい報酬タイプを追加する手順を説明します。

### ステップ1: SendServiceクラスを作成

```php
<?php

declare(strict_types=1);

namespace App\Domain\Reward\Services;

use App\Domain\Reward\Entities\RewardSendContext;
use App\Domain\Reward\Entities\RewardSent;
use App\Domain\YourDomain\Delegators\YourDelegator;

class YourRewardSendService implements RewardSendServiceInterface
{
    public function __construct(
        private YourDelegator $yourDelegator,
    ) {}

    public function send(RewardSendContext $context): RewardSent
    {
        $usrUserId = $context->getUsrUserId();
        $rewards = $context->getRewards();
        $now = $context->getNow();

        // YourDelegatorに委譲
        $this->yourDelegator->addYourResourceByRewards(
            usrUserId: $usrUserId,
            rewards: $rewards,
            now: $now,
        );

        return new RewardSent($rewards);
    }
}
```

### ステップ2: RewardSendServiceに登録

**ファイルパス**: `api/app/Domain/Reward/Services/RewardSendService.php`

```php
private array $sendServiceClasses = [
    RewardType::COIN->value => CoinSendService::class,
    RewardType::FREE_DIAMOND->value => FreeDiamondSendService::class,
    // ... 既存の報酬タイプ
    RewardType::YOUR_NEW_TYPE->value => YourRewardSendService::class, // 追加
];
```

### ステップ3: ログリポジトリを登録（オプション）

報酬送付ログを記録する場合：

```php
private array $logRepositoryClasses = [
    RewardType::COIN->value => LogCoinRepository::class,
    // ... 既存の報酬タイプ
    RewardType::YOUR_NEW_TYPE->value => LogYourResourceRepository::class, // 追加
];
```

### ステップ4: RewardSendPolicyに追加（必要に応じて）

**ファイルパス**: `api/app/Domain/Resource/Entities/RewardSendPolicy.php`

```php
public function __construct(
    private RewardSendMethod $coinSendMethod = RewardSendMethod::NONE,
    // ... 既存の報酬タイプ
    private RewardSendMethod $yourNewTypeSendMethod = RewardSendMethod::NONE, // 追加
    private ?GameException $resourceLimitReachedException = null,
) {}

public function getSendMethodByRewardType(string $rewardType): RewardSendMethod
{
    return match ($rewardType) {
        RewardType::COIN->value => $this->coinSendMethod,
        // ... 既存の報酬タイプ
        RewardType::YOUR_NEW_TYPE->value => $this->yourNewTypeSendMethod, // 追加
        default => RewardSendMethod::NONE,
    };
}
```

## パターン選択のガイドライン

### シンプルパターンを選ぶ場合

✅ 以下の条件を満たす場合：
- 単純な加算のみ
- 上限超過を気にしない
- 追加報酬が発生しない

**例**: COIN, STAMINA, ITEM

### メールボックス対応パターンを選ぶ場合

✅ 以下の条件を満たす場合：
- 上限超過時にメールボックスへ送信したい
- RewardSendPolicyの`SEND_TO_MESSAGE`を使用

**例**: FREE_DIAMOND

### 追加報酬パターンを選ぶ場合

✅ 以下の条件を満たす場合：
- 配布により別の報酬が発生する
- RewardManagerに報酬を追加する必要がある

**例**: EXP（レベルアップ報酬）

## まとめ

- すべてのSendServiceは`RewardSendServiceInterface`を実装
- 基本は各ドメインDelegatorへの委譲
- 必要に応じて`RewardSendServiceTrait`を使用
- 追加報酬が発生する場合は`RewardManager`に報酬を追加
- 新規追加時は`RewardSendService`と`RewardSendPolicy`に登録

詳細な実装例は以下を参照してください：
- **[examples/basic-usage.md](../examples/basic-usage.md)** - 基本的な使い方
- **[examples/testing.md](../examples/testing.md)** - SendServiceのテスト実装
