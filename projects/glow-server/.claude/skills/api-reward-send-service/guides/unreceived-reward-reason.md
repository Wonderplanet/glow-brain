# UnreceivedRewardReasonの調整方法

報酬が受け取れなかった理由（UnreceivedRewardReason）の種類と、各SendServiceでの設定方法を説明します。

## 目次

- [UnreceivedRewardReasonとは](#unreceivedrewardreasonとは)
- [理由の種類](#理由の種類)
- [BaseRewardでの使い方](#baserewardでの使い方)
- [各SendServiceでの設定パターン](#各sendserviceでの設定パターン)
- [新しい理由の追加方法](#新しい理由の追加方法)
- [実装例](#実装例)

## UnreceivedRewardReasonとは

**ファイルパス**: `api/app/Domain/Resource/Enums/UnreceivedRewardReason.php`

UnreceivedRewardReasonは、報酬が受け取れなかった理由を表すenumです。報酬オブジェクト（BaseReward）に設定され、クライアントへのレスポンスで使用されます。

### 用途

1. **クライアントへの情報提供**: 報酬が受け取れなかった理由をユーザーに表示
2. **メールボックス送信の判定**: `SENT_TO_MESSAGE`の場合はメールボックスへ送信
3. **レスポンスのフィルタリング**: 受け取れなかった報酬を除外する際の判定

## 理由の種類

### 定義

```php
enum UnreceivedRewardReason: string
{
    // 受け取れた状態。受け取れなかった理由はなし
    case NONE = 'None';

    // 報酬獲得時にリソース上限超過したため、受け取りできなかった
    case RESOURCE_LIMIT_REACHED = 'ResourceLimitReached';

    // 報酬獲得時にリソース上限超過したため、上限までは受け取ったが、上限を超えた分は破棄された
    case RESOURCE_OVERFLOW_DISCARDED = 'ResourceOverflowDiscarded';

    // 不正なデータで、受け取りできなかった
    case INVALID_DATA = 'InvalidData';

    // 報酬獲得時にリソース上限超過したため、即時受け取りは行わず、メールボックスに送った
    case SENT_TO_MESSAGE = 'SentToMessage';
}
```

### 各理由の詳細

| 理由 | 値 | 意味 | 使用場面 |
|-----|---|------|---------|
| NONE | None | 正常に受け取れた | デフォルト状態 |
| RESOURCE_LIMIT_REACHED | ResourceLimitReached | リソース上限超過で受け取れなかった | 上限超過時（配布なし） |
| RESOURCE_OVERFLOW_DISCARDED | ResourceOverflowDiscarded | 上限まで受け取り、超過分は破棄 | 上限超過時（一部配布） |
| INVALID_DATA | InvalidData | 不正なデータで受け取れなかった | データ検証エラー時 |
| SENT_TO_MESSAGE | SentToMessage | メールボックスへ送信した | メールボックス送信時 |

## BaseRewardでの使い方

**ファイルパス**: `api/app/Domain/Resource/Entities/Rewards/BaseReward.php`

BaseRewardクラスには、UnreceivedRewardReasonを設定・取得するメソッドがあります。

### 主なメソッド

```php
abstract class BaseReward
{
    private UnreceivedRewardReason $unreceivedRewardReason = UnreceivedRewardReason::NONE;

    /**
     * 受け取れなかった理由を設定
     */
    public function setUnreceivedRewardReason(UnreceivedRewardReason $reason): void
    {
        $this->unreceivedRewardReason = $reason;
    }

    /**
     * 受け取れなかった理由を取得
     */
    public function getUnreceivedRewardReason(): UnreceivedRewardReason
    {
        return $this->unreceivedRewardReason;
    }

    /**
     * 送信済みとしてマーク
     */
    public function markAsSent(): void
    {
        $this->isSent = true;
    }

    /**
     * 送信済みかどうか
     */
    public function isSent(): bool
    {
        return $this->isSent;
    }
}
```

## 各SendServiceでの設定パターン

### パターン1: 正常配布（NONE）

最もシンプルなパターン。報酬が正常に配布された場合。

```php
class CoinSendService implements RewardSendServiceInterface
{
    public function send(RewardSendContext $context): RewardSent
    {
        $usrUserId = $context->getUsrUserId();
        $rewards = $context->getRewards();
        $now = $context->getNow();

        // 配布実行
        $this->userDelegator->addCoinByRewards(
            usrUserId: $usrUserId,
            rewards: $rewards,
            now: $now,
        );

        // 報酬オブジェクトに自動的にNONEが設定される（デフォルト値）
        return new RewardSent($rewards);
    }
}
```

**重要**: `UnreceivedRewardReason::NONE`はデフォルト値なので、明示的に設定する必要はありません。

### パターン2: メールボックス送信（SENT_TO_MESSAGE）

上限超過時にメールボックスへ送信する場合。

**ファイルパス**: `api/app/Domain/Reward/Services/FreeDiamondSendService.php`

```php
class FreeDiamondSendService implements RewardSendServiceInterface
{
    use RewardSendServiceTrait;

    public function send(RewardSendContext $context): RewardSent
    {
        $usrUserId = $context->getUsrUserId();
        $platform = $context->getPlatform();
        $rewards = $context->getRewards();
        $sendMethod = $context->getSendMethod();

        // 送信処理
        $sendCallback = function () use ($usrUserId, $platform, $totalAmount, $rewards) {
            $this->appCurrencyDelegator->addIngameFreeDiamond(
                $usrUserId,
                $platform,
                $totalAmount,
                new FreeDiamondSendTrigger($rewards),
            );
        };

        switch ($sendMethod) {
            case RewardSendMethod::SEND_TO_MESSAGE:
                // 上限超過時はメールボックスへ
                // trySendRewardsOrMarkAsSentToMessage内でSENT_TO_MESSAGEが設定される
                $this->trySendRewardsOrMarkAsSentToMessage(
                    $rewards,
                    $sendCallback,
                );
                break;
            default:
                $sendCallback();
                break;
        }

        return new RewardSent($rewards);
    }
}
```

**RewardSendServiceTraitの実装**:

```php
trait RewardSendServiceTrait
{
    /**
     * 報酬配布を試行し、上限超過ならメールボックス送信マークを付ける
     */
    protected function trySendRewardsOrMarkAsSentToMessage(
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

            throw $e;
        }
    }

    /**
     * 報酬にメールボックス送信マークを付ける
     */
    protected function markRewardsAsSentToMessage(Collection $rewards): void
    {
        foreach ($rewards as $reward) {
            $reward->setUnreceivedRewardReason(UnreceivedRewardReason::SENT_TO_MESSAGE);
        }
    }
}
```

### パターン3: リソース上限超過（RESOURCE_LIMIT_REACHED）

上限超過で全く配布できなかった場合。

```php
class YourSendService implements RewardSendServiceInterface
{
    public function send(RewardSendContext $context): RewardSent
    {
        $usrUserId = $context->getUsrUserId();
        $rewards = $context->getRewards();
        $now = $context->getNow();

        try {
            // 配布実行
            $this->yourDelegator->addYourResource(
                usrUserId: $usrUserId,
                rewards: $rewards,
                now: $now,
            );

            // 正常配布できた場合
            foreach ($rewards as $reward) {
                $reward->markAsSent();
                // UnreceivedRewardReason::NONEはデフォルト値
            }
        } catch (ResourceLimitReachedException $e) {
            // 上限超過で全く配布できなかった場合
            foreach ($rewards as $reward) {
                $reward->setUnreceivedRewardReason(
                    UnreceivedRewardReason::RESOURCE_LIMIT_REACHED
                );
                // isSent()はfalseのまま
            }
        }

        return new RewardSent($rewards);
    }
}
```

### パターン4: 一部破棄（RESOURCE_OVERFLOW_DISCARDED）

上限まで配布し、超過分を破棄した場合。

```php
class YourSendService implements RewardSendServiceInterface
{
    public function send(RewardSendContext $context): RewardSent
    {
        $usrUserId = $context->getUsrUserId();
        $rewards = $context->getRewards();
        $now = $context->getNow();

        foreach ($rewards as $reward) {
            try {
                // 配布実行
                $actualAmount = $this->yourDelegator->addYourResourceWithLimit(
                    usrUserId: $usrUserId,
                    amount: $reward->getAmount(),
                    now: $now,
                );

                if ($actualAmount < $reward->getAmount()) {
                    // 一部のみ配布できた（超過分は破棄）
                    $reward->setUnreceivedRewardReason(
                        UnreceivedRewardReason::RESOURCE_OVERFLOW_DISCARDED
                    );
                }

                $reward->markAsSent();
            } catch (\Exception $e) {
                // エラー処理
            }
        }

        return new RewardSent($rewards);
    }
}
```

### パターン5: 不正なデータ（INVALID_DATA）

データ検証でエラーが発生した場合。

```php
class YourSendService implements RewardSendServiceInterface
{
    public function send(RewardSendContext $context): RewardSent
    {
        $usrUserId = $context->getUsrUserId();
        $rewards = $context->getRewards();
        $now = $context->getNow();

        foreach ($rewards as $reward) {
            // データ検証
            if ($this->validateReward($reward) === false) {
                // 不正なデータ
                $reward->setUnreceivedRewardReason(
                    UnreceivedRewardReason::INVALID_DATA
                );
                continue; // 次の報酬へ
            }

            // 配布実行
            $this->yourDelegator->addYourResource(
                usrUserId: $usrUserId,
                reward: $reward,
                now: $now,
            );

            $reward->markAsSent();
        }

        return new RewardSent($rewards);
    }

    private function validateReward(BaseReward $reward): bool
    {
        // データ検証ロジック
        return $reward->getAmount() > 0 && $reward->getId() !== null;
    }
}
```

## 新しい理由の追加方法

新しい理由が必要な場合は、以下の手順で追加します。

### 手順1: UnreceivedRewardReasonに追加

**ファイルパス**: `api/app/Domain/Resource/Enums/UnreceivedRewardReason.php`

```php
enum UnreceivedRewardReason: string
{
    case NONE = 'None';
    case RESOURCE_LIMIT_REACHED = 'ResourceLimitReached';
    case RESOURCE_OVERFLOW_DISCARDED = 'ResourceOverflowDiscarded';
    case INVALID_DATA = 'InvalidData';
    case SENT_TO_MESSAGE = 'SentToMessage';
    case YOUR_NEW_REASON = 'YourNewReason'; // 追加
}
```

### 手順2: SendServiceで使用

```php
class YourSendService implements RewardSendServiceInterface
{
    public function send(RewardSendContext $context): RewardSent
    {
        $rewards = $context->getRewards();

        foreach ($rewards as $reward) {
            if ($this->shouldApplyNewReason($reward)) {
                $reward->setUnreceivedRewardReason(
                    UnreceivedRewardReason::YOUR_NEW_REASON
                );
            }
        }

        return new RewardSent($rewards);
    }
}
```

### 手順3: クライアント対応

クライアント側で新しい理由に対応する必要があります：
- エラーメッセージの表示
- UIの調整
- ログ記録

## 実装例

### 例1: メールボックス送信の完全な流れ

```php
// 1. SendServiceで SENT_TO_MESSAGE を設定
$reward->setUnreceivedRewardReason(UnreceivedRewardReason::SENT_TO_MESSAGE);

// 2. RewardSendService::sendToMessage でフィルタリング
$messageSystemRewards = $rewards->filter(function (BaseReward $reward) {
    return $reward->getUnreceivedRewardReason() === UnreceivedRewardReason::SENT_TO_MESSAGE
        && $reward->isSent() === false;
});

// 3. メールボックスへ送信
$messageDelegator = app(MessageDelegator::class);
foreach ($messageSystemRewards as $reward) {
    $messageDelegator->addNewSystemMessage(
        usrUserId: $usrUserId,
        rewardGroupId: null,
        expiredAt: null,
        reward: $reward,
        title: MessageConstant::REWARD_UNRECEIVED_TITLE,
        body: MessageConstant::REWARD_UNRECEIVED_BODY,
        prefixMessageSource: MessageSource::RESOURCE_LIMIT_REACHED->value,
    );

    $reward->markAsSent();
}
```

### 例2: 複数の理由を持つ報酬の処理

```php
public function send(RewardSendContext $context): RewardSent
{
    $rewards = $context->getRewards();

    foreach ($rewards as $reward) {
        // データ検証
        if (!$this->validateReward($reward)) {
            $reward->setUnreceivedRewardReason(UnreceivedRewardReason::INVALID_DATA);
            continue;
        }

        try {
            // 配布実行
            $actualAmount = $this->addResourceWithLimit($reward);

            if ($actualAmount === 0) {
                // 全く配布できなかった
                $reward->setUnreceivedRewardReason(
                    UnreceivedRewardReason::RESOURCE_LIMIT_REACHED
                );
            } elseif ($actualAmount < $reward->getAmount()) {
                // 一部のみ配布できた
                $reward->setUnreceivedRewardReason(
                    UnreceivedRewardReason::RESOURCE_OVERFLOW_DISCARDED
                );
            }

            $reward->markAsSent();
        } catch (\Exception $e) {
            // エラー処理
            throw $e;
        }
    }

    return new RewardSent($rewards);
}
```

## RewardSendServiceでの自動処理

RewardSendServiceは、`SENT_TO_MESSAGE`が設定された報酬を自動的にメールボックスへ送信します。

**ファイルパス**: `api/app/Domain/Reward/Services/RewardSendService.php`

```php
private function sendToMessage(
    string $usrUserId,
    Collection $rewards,
): RewardSent {
    // SENT_TO_MESSAGE かつ未送信の報酬を抽出
    $messageSystemRewards = $rewards->filter(function (BaseReward $reward) {
        return $reward->getUnreceivedRewardReason() === UnreceivedRewardReason::SENT_TO_MESSAGE
            && $reward->isSent() === false;
    });

    if ($messageSystemRewards->isEmpty()) {
        return new RewardSent($rewards);
    }

    // メールボックスへ送信
    $messageDelegator = app(MessageDelegator::class);
    foreach ($messageSystemRewards as $reward) {
        $messageDelegator->addNewSystemMessage(
            usrUserId: $usrUserId,
            rewardGroupId: null,
            expiredAt: null,
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

## まとめ

- **NONE**: 正常配布（デフォルト値）
- **RESOURCE_LIMIT_REACHED**: 上限超過で配布なし
- **RESOURCE_OVERFLOW_DISCARDED**: 上限まで配布、超過分破棄
- **INVALID_DATA**: 不正なデータで配布不可
- **SENT_TO_MESSAGE**: メールボックスへ送信（RewardSendServiceが自動処理）

UnreceivedRewardReasonは報酬オブジェクトに設定され、クライアントへのレスポンスやメールボックス送信の判定に使用されます。各SendServiceで適切な理由を設定することで、ユーザーに正確な情報を提供できます。

詳細な実装例は **[examples/error-handling.md](../examples/error-handling.md)** を参照してください。
