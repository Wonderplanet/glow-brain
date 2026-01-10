# エラーハンドリング実装例

リソース上限超過時のエラーハンドリングを含む報酬送付の実装例を紹介します。

## 目次

- [エラー投げるポリシーの使い方](#エラー投げるポリシーの使い方)
- [実装例: メールボックス受取](#実装例-メールボックス受取)
- [エラーハンドリングのパターン](#エラーハンドリングのパターン)
- [ポイント](#ポイント)

## エラー投げるポリシーの使い方

### 基本的な使い方

```php
use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Entities\RewardSendPolicy;

// エラー投げるポリシーを作成
$policy = RewardSendPolicy::createThrowErrorWhenResourceLimitReachedPolicy(
    new GameException(
        ErrorCode::MESSAGE_REWARD_BY_OVER_MAX,
        'Cannot receive the message reward because it exceeds the resource limit.',
    ),
);

// 報酬送付（上限超過時は例外が投げられる）
$this->rewardDelegator->sendRewards($usrUserId, $platform, $now, $policy);
```

### ポリシーの動作

**エラー投げるポリシー**:
- コイン: 上限超過時は例外を投げる
- 無償プリズム: 上限超過時は例外を投げる
- スタミナ: 上限超過時は例外を投げる
- アイテム: 上限超過時は例外を投げる
- 経験値: 上限超過時は例外を投げる
- エンブレム: 何もしない（重複時はコイン変換される）
- ユニット: 何もしない（重複時はアイテム変換される）

## 実装例: メールボックス受取

**ファイルパス**: `api/app/Domain/Message/UseCases/ReceiveUseCase.php`

メールボックスの報酬受取処理の実装例です。

### コンストラクタ

```php
class ReceiveUseCase
{
    use UseCaseTrait;

    public function __construct(
        private UsrMessageRepository $usrMessageRepository,
        private LogReceiveMessageRewardRepository $logReceiveMessageRewardRepository,
        private UsrMessageService $usrMessageService,
        private UsrModelDiffGetService $usrModelDiffGetService,
        private UserDelegator $userDelegator,
        private RewardDelegator $rewardDelegator, // RewardDelegatorを注入
        private ShopDelegator $shopDelegator,
        private Clock $clock,
    ) {}
}
```

### メイン処理

```php
public function exec(
    CurrentUser $user,
    int $platform,
    array $usrMessageIds,
    string $language,
): MessageReceiveResultData {
    $now = $this->clock->now();
    $usrUserParameter = $this->userDelegator->getUsrUserParameterByUsrUserId($user->id);
    $beforeExp = $usrUserParameter->getExp();
    $beforeLevel = $usrUserParameter->getLevel();

    // メッセージデータを取得
    $usrMessages = $this->usrMessageService->getByIdPlusRewardGroupIdData(
        $user->id,
        collect($usrMessageIds)
    );

    // システム送信メッセージと通常のメッセージに分ける
    [$systemUsrMessages, $normalUsrMessages] = $usrMessages->partition(function (UsrMessageInterface $row) {
        return is_null($row->getMngMessageId());
    });

    // 通常のメッセージの報酬を取得
    $mngMessageRewards = $this->usrMessageService->getMngMessageRewards(
        $user->id,
        $normalUsrMessages,
        $now,
        $language,
    );

    // ログ出力用に受け取り期限を収集
    $mngMessageExpiredAt = $normalUsrMessages->mapWithKeys(function (UsrMessageInterface $usrMessage) {
        return [$usrMessage->getMngMessageId() => $usrMessage->getExpiredAt()];
    });

    // ★ 報酬配布リストに追加
    $addRewards = collect();
    try {
        // 通常のメッセージ報酬
        foreach ($mngMessageRewards as $entity) {
            $reward = new MessageReward(
                $entity->getResourceType(),
                $entity->getResourceId(),
                $entity->getResourceAmount(),
                MessageSource::MNG_MESSAGE->value,
                $entity->getMngMessageId(),
                $mngMessageExpiredAt->get($entity->getMngMessageId(), null)
            );
            $this->rewardDelegator->addReward($reward);
            $addRewards->push($reward);
        }

        // システムメッセージ報酬
        foreach ($systemUsrMessages as $usrMessage) {
            $reward = new MessageReward(
                $usrMessage->getResourceType(),
                $usrMessage->getResourceId(),
                $usrMessage->getResourceAmount(),
                $usrMessage->getMessageSource(),
                $usrMessage->getMngMessageId(),
                $usrMessage->getExpiredAt()
            );
            $this->rewardDelegator->addReward($reward);
            $addRewards->push($reward);
        }
    } catch (\Exception $e) {
        throw new GameException(ErrorCode::ERROR_RECEIVED_MESSAGE_RESOURCE, $e->getMessage());
    }

    // 受け取り日時を更新
    try {
        $updateUsrMessages = collect();
        foreach ($usrMessages as $usrMessage) {
            if (is_null($usrMessage->getOpenedAt())) {
                $usrMessage->setOpenedAt($now);
            }
            $usrMessage->receive($now);
            $updateUsrMessages->push($usrMessage);
        }
        $this->usrMessageRepository->syncModels($updateUsrMessages);
    } catch (\Exception $e) {
        throw new GameException(ErrorCode::FAILURE_UPDATE_BY_USER_MESSAGES, $e->getMessage());
    }

    // トランザクション処理
    list(
        $usrConditionPacks,
        $afterUsrUserParameter,
        $sentMessageRewards,
    ) = $this->applyUserTransactionChanges(function () use ($user, $now, $platform, $beforeLevel) {
        // ★ 報酬配布実行（エラー投げるポリシー使用）
        $this->rewardDelegator->sendRewards(
            $user->id,
            $platform,
            $now,
            RewardSendPolicy::createThrowErrorWhenResourceLimitReachedPolicy(
                new GameException(
                    ErrorCode::MESSAGE_REWARD_BY_OVER_MAX,
                    'Cannot receive the message reward because it exceeds the resource limit.',
                ),
            ),
        );

        // ★ 送付済み報酬を取得
        $sentMessageRewards = $this->rewardDelegator->getSentRewards(MessageReward::class);

        // ログ記録
        $this->logReceiveMessageRewardRepository->createByRewards($user->id, $sentMessageRewards);

        // レベルアップパックの開放
        $usrConditionPacks = collect();
        $afterUsrUserParameter = $this->userDelegator->getUsrUserParameterByUsrUserId($user->id);
        if ($beforeLevel < $afterUsrUserParameter->getLevel()) {
            $usrConditionPacks = $this->shopDelegator->releaseUserLevelPack(
                $user->id,
                $afterUsrUserParameter->getLevel(),
                $now
            );
        }

        return [
            $usrConditionPacks,
            $afterUsrUserParameter,
            $sentMessageRewards,
        ];
    });

    // レスポンスデータを作成
    $userLevelUpData = new UserLevelUpData(
        $beforeExp,
        $afterUsrUserParameter->getExp(),
        $this->rewardDelegator->getSentRewards(UserLevelUpReward::class),
    );

    return new MessageReceiveResultData(
        $sentMessageRewards,
        $this->makeUsrParameterData($afterUsrUserParameter),
        $this->usrModelDiffGetService->getChangedUsrUnits(),
        $this->usrModelDiffGetService->getChangedUsrItems(),
        $this->usrModelDiffGetService->getChangedUsrEmblems(),
        $userLevelUpData,
        $usrConditionPacks
    );
}
```

### ポイント解説

#### 1. エラー投げるポリシーの作成

```php
$policy = RewardSendPolicy::createThrowErrorWhenResourceLimitReachedPolicy(
    new GameException(
        ErrorCode::MESSAGE_REWARD_BY_OVER_MAX,
        'Cannot receive the message reward because it exceeds the resource limit.',
    ),
);
```

**重要**:
- `ErrorCode`: エラーコードを指定
- エラーメッセージ: ユーザーに表示されるメッセージ

#### 2. ポリシーを指定して報酬送付

```php
$this->rewardDelegator->sendRewards(
    $user->id,
    $platform,
    $now,
    $policy, // エラー投げるポリシーを渡す
);
```

#### 3. 例外の捕捉（オプション）

例外をキャッチして独自のエラーハンドリングを行う場合：

```php
try {
    $this->rewardDelegator->sendRewards(
        $user->id,
        $platform,
        $now,
        $policy,
    );
} catch (GameException $e) {
    if ($e->getCode() === ErrorCode::MESSAGE_REWARD_BY_OVER_MAX) {
        // リソース上限超過時の処理
        Log::warning('Resource limit exceeded', [
            'usrUserId' => $user->id,
            'message' => $e->getMessage(),
        ]);
    }
    throw $e; // 再度投げる
}
```

## エラーハンドリングのパターン

### パターン1: 例外をそのまま投げる（推奨）

```php
// 例外をキャッチせず、フレームワークに任せる
$this->rewardDelegator->sendRewards(
    $usrUserId,
    $platform,
    $now,
    RewardSendPolicy::createThrowErrorWhenResourceLimitReachedPolicy(
        new GameException(ErrorCode::MESSAGE_REWARD_BY_OVER_MAX),
    ),
);
// → 例外が発生したら、フレームワークがエラーレスポンスを返す
```

### パターン2: 例外をキャッチしてログ記録

```php
try {
    $this->rewardDelegator->sendRewards(
        $usrUserId,
        $platform,
        $now,
        $policy,
    );
} catch (GameException $e) {
    // ログに記録
    Log::error('Failed to send rewards', [
        'usrUserId' => $usrUserId,
        'errorCode' => $e->getCode(),
        'message' => $e->getMessage(),
    ]);

    // 例外を再度投げる
    throw $e;
}
```

### パターン3: 例外を別の例外に変換

```php
try {
    $this->rewardDelegator->sendRewards(
        $usrUserId,
        $platform,
        $now,
        $policy,
    );
} catch (GameException $e) {
    if ($e->getCode() === ErrorCode::MESSAGE_REWARD_BY_OVER_MAX) {
        // より具体的なエラーメッセージに変換
        throw new GameException(
            ErrorCode::CUSTOM_ERROR_CODE,
            'メールボックスの報酬を受け取れませんでした。所持枠を確認してください。',
            $e,
        );
    }
    throw $e;
}
```

## ポイント

### ✅ メールボックス受取では必ずエラー投げるポリシーを使う

```php
// ✅ 正しい
$policy = RewardSendPolicy::createThrowErrorWhenResourceLimitReachedPolicy(
    new GameException(ErrorCode::MESSAGE_REWARD_BY_OVER_MAX),
);
$this->rewardDelegator->sendRewards($usrUserId, $platform, $now, $policy);

// ❌ 間違い（デフォルトポリシー）
$this->rewardDelegator->sendRewards($usrUserId, $platform, $now);
// → 上限超過時に報酬が配布されず、ユーザーに通知もされない
```

### ✅ エラーコードとメッセージを適切に設定

```php
new GameException(
    ErrorCode::MESSAGE_REWARD_BY_OVER_MAX,  // エラーコード
    'Cannot receive the message reward because it exceeds the resource limit.', // メッセージ
)
```

### ✅ 例外は基本的に再度投げる

```php
try {
    $this->rewardDelegator->sendRewards(...);
} catch (GameException $e) {
    Log::error('Error occurred', ['exception' => $e]);
    throw $e; // 再度投げる（重要）
}
```

### ❌ 間違った使い方

```php
// ❌ 例外を握りつぶす
try {
    $this->rewardDelegator->sendRewards(...);
} catch (GameException $e) {
    // 何もしない（例外が隠蔽される）
}

// ❌ デフォルトポリシーでメールボックス受取
$this->rewardDelegator->sendRewards($usrUserId, $platform, $now);
// → 上限超過時にエラーにならず、報酬が配布されない
```

## エラー種別とエラーコード

### よく使われるエラーコード

| エラーコード | 使用場面 | 意味 |
|------------|---------|------|
| `MESSAGE_REWARD_BY_OVER_MAX` | メールボックス受取 | リソース上限超過 |
| `LACK_OF_RESOURCES` | 汎用 | リソース不足 |
| `ERROR_RECEIVED_MESSAGE_RESOURCE` | メールボックス受取 | 報酬受取エラー |
| `FAILURE_UPDATE_BY_USER_MESSAGES` | メールボックス受取 | メッセージ更新エラー |

### 例外の種類

| 例外クラス | 発生場面 |
|----------|---------|
| `GameException` | ゲームロジックのエラー |
| `WpCurrencyAddCurrencyOverByMaxException` | 有償プリズム上限超過 |
| `WpCurrencyAddFreeCurrencyOverByMaxException` | 無償プリズム上限超過 |

## まとめ

- **エラー投げるポリシー**: メールボックス受取など明示的な受取操作で使用
- **エラーコードとメッセージ**: 適切に設定してユーザーに通知
- **例外は再度投げる**: 基本的に例外を握りつぶさない
- **デフォルトポリシーとの使い分け**: メールボックス受取では必ずエラー投げるポリシーを使う

基本的な使い方は **[examples/basic-usage.md](basic-usage.md)** を参照してください。
