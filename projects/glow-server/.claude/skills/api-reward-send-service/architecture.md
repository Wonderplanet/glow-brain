# アーキテクチャと設計思想

RewardDelegator、RewardManager、RewardSendServiceの関係性と、なぜこのような設計になっているかを解説します。

## 目次

- [コンポーネント概要](#コンポーネント概要)
- [RewardDelegatorの役割](#rewarddelegatorの役割)
- [RewardManagerの役割](#rewardmanagerの役割)
- [RewardSendServiceの役割](#rewardsendserviceの役割)
- [個別SendServiceの役割](#個別sendserviceの役割)
- [処理フロー](#処理フロー)
- [設計思想](#設計思想)

## コンポーネント概要

報酬送付システムは以下のコンポーネントで構成されています：

```
UseCase/Service
    ↓ (報酬を追加)
RewardDelegator
    ↓ (報酬を管理)
RewardManager ← (報酬一時保管)
    ↓ (報酬を送付)
RewardSendService
    ↓ (報酬タイプごとに処理)
CoinSendService, ItemSendService, ... (個別SendService)
    ↓ (実際の配布処理)
UserDelegator, ItemDelegator, ... (各ドメインのDelegator)
```

## RewardDelegatorの役割

**ファイルパス**: `api/app/Domain/Reward/Delegators/RewardDelegator.php`

RewardDelegatorは、RewardManagerとRewardSendServiceの**ファサード**として機能します。

### 主なメソッド

```php
class RewardDelegator
{
    public function __construct(
        private RewardManager $rewardManager,
        private RewardSendService $rewardSendService,
    ) {}

    // 報酬を追加
    public function addReward(BaseReward $reward): void
    public function addRewards(Collection $rewards): void

    // 報酬を送付
    public function sendRewards(
        string $usrUserId,
        int $platform,
        CarbonImmutable $now,
        ?RewardSendPolicy $policy = null,
    ): void

    // 送付済み報酬を取得
    public function getSentRewards(string $rewardClass): Collection
}
```

### なぜDelegatorが必要か

1. **単一窓口**: UseCaseは`RewardDelegator`のみに依存すればよい
2. **責務の分離**: 管理（RewardManager）と送付（RewardSendService）を分離
3. **シンプルなインターフェース**: 複雑な内部実装を隠蔽

## RewardManagerの役割

**ファイルパス**: `api/app/Domain/Reward/Managers/RewardManager.php`

RewardManagerは報酬オブジェクトの**一時保管と状態管理**を行います。

### 主な責務

```php
class RewardManager implements RewardManagerInterface
{
    // 送付前の報酬リスト
    private array $needToSendRewards = [];

    // 送付済みの報酬リスト（報酬クラスごとに分類）
    private array $sentRewards = [];

    // 報酬を送付前リストに追加
    public function addReward(BaseReward $reward): void
    public function addRewards(Collection $rewards): void

    // 送付前リストを取得
    public function getNeedToSendRewards(): Collection

    // 送付後処理（送付前→送付済みリストへ移動）
    public function afterSend(RewardSent $sentData): void

    // 送付済み報酬を取得
    public function getSentRewards(string $rewardClass): Collection
}
```

### なぜManagerが必要か

1. **バッチ処理**: 複数の報酬をまとめて送付できる
2. **状態管理**: 送付前と送付済みを明確に区別
3. **報酬クラス別の取得**: レスポンス作成時に報酬クラスごとに取得可能

### 実装例

```php
// ミッション報酬を追加
$missionReward = new MissionReward(
    RewardType::COIN->value,
    null,
    1000,
    $mstMissionId
);
$this->rewardManager->addReward($missionReward);

// レベルアップ報酬を追加
$levelUpReward = new UserLevelUpReward(
    RewardType::ITEM->value,
    'item_001',
    5,
);
$this->rewardManager->addReward($levelUpReward);

// 一括送付
$this->rewardSendService->sendRewards($usrUserId, $platform, $now);

// 報酬クラスごとに取得
$missionRewards = $this->rewardManager->getSentRewards(MissionReward::class);
$levelUpRewards = $this->rewardManager->getSentRewards(UserLevelUpReward::class);
```

## RewardSendServiceの役割

**ファイルパス**: `api/app/Domain/Reward/Services/RewardSendService.php`

RewardSendServiceは**報酬送付処理全体の制御**を行います。

### 主な責務

1. **報酬タイプごとの振り分け**: 報酬を報酬タイプ別にグループ化
2. **個別SendServiceの実行**: 報酬タイプに応じたSendServiceを取得・実行
3. **変換処理**: 重複エンブレムのコイン変換、重複ユニットのアイテム変換
4. **メールボックス送信**: 上限超過時のメールボックス自動送信
5. **ログ記録**: 報酬送付ログの記録
6. **エラーハンドリング**: RewardSendPolicyに基づく例外処理

### 処理フロー

```php
public function sendRewards(
    string $usrUserId,
    int $platform,
    CarbonImmutable $now,
    ?RewardSendPolicy $policy = null,
): RewardSendSummary {
    // 1. 前処理: 報酬変換（重複エンブレム→コイン等）
    $this->beforeSend($usrUserId, $now);

    // 2. 報酬タイプごとにグループ化
    $rewardTypeGroups = $this->rewardManager->getNeedToSendRewards()
        ->groupBy(fn(BaseReward $reward) => $reward->getType());

    // 3. 各報酬タイプを処理
    foreach ($rewardTypeGroups as $type => $typeRewards) {
        // 3-1. SendServiceを取得
        $sendService = $this->getSendService($type);

        // 3-2. 報酬配布を実行
        $rewardSent = $sendService->send($context);

        // 3-3. メールボックスへ送信（必要な場合）
        $this->sendToMessage($usrUserId, $rewardSent->getRewards());

        // 3-4. 後処理（送付済みリストへ移動）
        $this->rewardManager->afterSend($rewardSent);

        // 3-5. ログ送信
        $this->logging($usrUserId, $type, $rewardSent);
    }

    return $rewardSendSummary;
}
```

### SendServiceマッピング

```php
private array $sendServiceClasses = [
    RewardType::COIN->value => CoinSendService::class,
    RewardType::FREE_DIAMOND->value => FreeDiamondSendService::class,
    RewardType::STAMINA->value => StaminaSendService::class,
    RewardType::ITEM->value => ItemSendService::class,
    RewardType::EXP->value => ExpSendService::class,
    RewardType::EMBLEM->value => EmblemSendService::class,
    RewardType::UNIT->value => UnitSendService::class,
];
```

## 個別SendServiceの役割

**インターフェース**: `api/app/Domain/Reward/Services/RewardSendServiceInterface.php`

各報酬タイプ専用のSendServiceは**実際の配布処理**を行います。

### インターフェース定義

```php
interface RewardSendServiceInterface
{
    public function send(RewardSendContext $context): RewardSent;
}
```

### 実装例: CoinSendService

**ファイルパス**: `api/app/Domain/Reward/Services/CoinSendService.php`

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

        // UserDelegatorに委譲して実際の配布を実行
        $this->userDelegator->addCoinByRewards(
            usrUserId: $usrUserId,
            rewards: $rewards,
            now: $now,
        );

        return new RewardSent($rewards);
    }
}
```

### 実装例: ItemSendService

**ファイルパス**: `api/app/Domain/Reward/Services/ItemSendService.php`

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

        // ItemDelegatorに委譲して実際の配布を実行
        $this->itemDelegator->addItemByRewards(
            usrUserId: $usrUserId,
            rewards: $rewards,
            now: $now,
        );

        return new RewardSent(rewards: $rewards);
    }
}
```

### 個別SendServiceの責務

1. **各ドメインのDelegatorに委譲**: 実際の配布処理は各ドメインが担当
2. **RewardSentの返却**: 配布結果をRewardSentとして返却
3. **シンプルに保つ**: ビジネスロジックは各ドメインに任せる

## 処理フロー

全体の処理フローを図示します：

```
[UseCase/Service]
    ↓ addReward/addRewards
[RewardDelegator]
    ↓
[RewardManager] ← 報酬を一時保管（needToSendRewards配列）
    ↓
[UseCase/Service]
    ↓ sendRewards
[RewardDelegator]
    ↓
[RewardSendService]
    ↓ 1. beforeSend（報酬変換）
[RewardManager] ← 変換後の報酬を追加
    ↓ 2. getNeedToSendRewards
[RewardSendService]
    ↓ 3. 報酬タイプごとにループ
[CoinSendService, ItemSendService, ...]
    ↓ 4. 各ドメインに委譲
[UserDelegator, ItemDelegator, ...]
    ↓ 5. 実際の配布処理
[DB更新]
    ↓ 6. RewardSent返却
[RewardSendService]
    ↓ 7. afterSend
[RewardManager] ← 送付済みリストへ移動（sentRewards配列）
    ↓
[UseCase/Service]
    ↓ getSentRewards(MissionReward::class)
[RewardDelegator]
    ↓
[RewardManager] → 送付済み報酬を返却
```

## 設計思想

### なぜこのような設計なのか

#### 1. 関心の分離

- **RewardManager**: 状態管理のみ
- **RewardSendService**: 送付処理の制御
- **個別SendService**: 報酬タイプ固有の処理
- **各ドメインDelegator**: 実際のビジネスロジック

#### 2. 拡張性

新しい報酬タイプを追加する場合：
1. 新しいSendServiceを実装
2. RewardSendServiceの`$sendServiceClasses`に追加

既存コードへの影響は最小限。

#### 3. テスタビリティ

各コンポーネントが独立しているため、個別にテスト可能：
- RewardManagerのテスト: 状態管理のみ
- RewardSendServiceのテスト: 送付制御のみ
- 個別SendServiceのテスト: 報酬タイプ固有処理のみ

#### 4. 循環参照の回避

```php
// MessageDelegatorを使う場合、コンストラクタインジェクションしない
// （循環参照を避けるため）
$messageDelegator = app(MessageDelegator::class);
```

RewardSendServiceは多くのドメインから使われるため、循環参照を避ける設計が重要。

#### 5. 柔軟なエラーハンドリング

RewardSendPolicyにより、同じRewardSendServiceで異なるエラーハンドリング戦略を実現：
- デフォルト: 上限超過時はメールボックスへ
- エラー時: 上限超過時は例外を投げる

詳細は **[guides/reward-send-policy.md](guides/reward-send-policy.md)** を参照。

## まとめ

- **RewardDelegator**: 単一窓口（ファサード）
- **RewardManager**: 状態管理（送付前/送付済み）
- **RewardSendService**: 送付処理の制御
- **個別SendService**: 報酬タイプ固有の処理
- **各ドメインDelegator**: 実際のビジネスロジック

この設計により、関心の分離、拡張性、テスタビリティ、循環参照の回避を実現しています。
