# 基本的な使い方

RewardDelegatorとRewardSendServiceを使った基本的な報酬送付フローの実装例を紹介します。

## 目次

- [基本フロー](#基本フロー)
- [実装例: ミッション報酬受取](#実装例-ミッション報酬受取)
- [実装例: ステージクリア報酬](#実装例-ステージクリア報酬)
- [ポイント](#ポイント)

## 基本フロー

報酬送付の基本的な流れは以下の3ステップです：

```php
// 1. 報酬を追加
$this->rewardDelegator->addReward($reward);
// または
$this->rewardDelegator->addRewards($rewards);

// 2. トランザクション内で報酬を送付
$this->rewardDelegator->sendRewards($usrUserId, $platform, $now);

// 3. 送付済み報酬を取得（レスポンス作成用）
$sentRewards = $this->rewardDelegator->getSentRewards(MissionReward::class);
```

## 実装例: ミッション報酬受取

**ファイルパス**: `api/app/Domain/Mission/UseCases/MissionBulkReceiveRewardUseCase.php`

ミッション報酬の一括受取処理の実装例です。

### コンストラクタ

```php
class MissionBulkReceiveRewardUseCase
{
    use UseCaseTrait;

    public function __construct(
        private MissionUpdateHandleService $missionUpdateHandleService,
        private MissionStatusService $missionStatusService,
        private UsrModelDiffGetService $usrModelDiffGetService,
        private MissionReceiveRewardService $missionReceiveRewardService,
        private MissionFetchService $missionFetchService,
        private LogMissionRewardRepository $logMissionRewardRepository,
        // Delegator
        private RewardDelegator $rewardDelegator, // RewardDelegatorを注入
        private UserDelegator $userDelegator,
        private ShopDelegator $shopDelegator,
        // Other
        private Clock $clock,
    ) {}
}
```

### メイン処理

```php
public function exec(
    CurrentUser $user,
    int $platform,
    string $missionType,
    array $mstMissionIds,
): MissionBulkReceiveRewardResultData {
    $usrUserId = $user->id;
    $now = $this->clock->now();

    $missionType = MissionType::getFromString($missionType);

    $beforeUsrUserParameter = $this->userDelegator->getUsrUserParameterByUsrUserId($user->id);
    $beforeExp = $beforeUsrUserParameter->getExp();
    $beforeLevel = $beforeUsrUserParameter->getLevel();

    // ミッション報酬を取得して RewardDelegator に追加
    // （この処理は MissionReceiveRewardService 内で実行される）
    $receiveRewardStatuses = $this->missionReceiveRewardService->bulkReceiveReward(
        $usrUserId,
        $now,
        $platform,
        $missionType,
        collect($mstMissionIds),
    );

    // トランザクション処理
    list(
        $usrConditionPacks,
        $afterUsrUserParameter,
    ) = $this->applyUserTransactionChanges(function () use (
        $usrUserId,
        $platform,
        $now,
        $missionType,
        $beforeLevel,
    ) {
        // ★ 報酬配布実行（デフォルトポリシー使用）
        $this->rewardDelegator->sendRewards($usrUserId, $platform, $now);

        // 報酬配布後のミッション進捗判定
        $this->missionUpdateHandleService->handleAllUpdateTriggeredMissions($usrUserId, $now);

        // 初心者ミッションの完了ステータス更新
        if ($missionType->isBeginner()) {
            $this->missionStatusService->completeBeginnerMission($usrUserId);
        }

        // レベルアップパックの開放
        $usrConditionPacks = collect();
        $afterUsrUserParameter = $this->userDelegator->getUsrUserParameterByUsrUserId($usrUserId);
        if ($beforeLevel < $afterUsrUserParameter->getLevel()) {
            $usrConditionPacks = $this->shopDelegator->releaseUserLevelPack(
                $usrUserId,
                $afterUsrUserParameter->getLevel(),
                $now
            );
        }

        // ★ ミッション報酬ログ（送付済み報酬を取得）
        $missionRewards = $this->rewardDelegator->getSentRewards(MissionReward::class);
        $this->logMissionRewardRepository->create(
            $usrUserId,
            $missionType,
            $missionRewards,
        );

        return [
            $usrConditionPacks,
            $afterUsrUserParameter,
        ];
    });

    // ミッション進捗データ取得
    // ... (省略)

    // ★ レスポンスデータを作成（送付済み報酬を取得）
    $userLevelUpData = new UserLevelUpData(
        $beforeExp,
        $afterUsrUserParameter->getExp(),
        $this->rewardDelegator->getSentRewards(UserLevelUpReward::class),
    );

    return new MissionBulkReceiveRewardResultData(
        $receiveRewardStatuses,
        $this->rewardDelegator->getSentRewards(MissionReward::class), // ミッション報酬
        // ... (その他のデータ)
        $userLevelUpData,
        $usrConditionPacks,
    );
}
```

### ポイント解説

#### 1. 報酬の追加

報酬の追加は`MissionReceiveRewardService::bulkReceiveReward()`内で実行されます。

**参考実装（MissionReceiveRewardService内）**:

```php
// ミッションごとの報酬を作成
foreach ($mstMissions as $mstMission) {
    $reward = new MissionReward(
        $mstMission->getResourceType(),
        $mstMission->getResourceId(),
        $mstMission->getResourceAmount(),
        $mstMission->getId(),
    );

    // RewardDelegatorに追加
    $this->rewardDelegator->addReward($reward);
}
```

#### 2. 報酬の送付

```php
// トランザクション内で実行
$this->applyUserTransactionChanges(function () use ($usrUserId, $platform, $now) {
    // デフォルトポリシーで送付（policy引数省略）
    $this->rewardDelegator->sendRewards($usrUserId, $platform, $now);
});
```

**デフォルトポリシー**:
- コイン、スタミナ、アイテム、経験値: 上限超過時は何もしない
- 無償プリズム: 上限超過時はメールボックスへ

#### 3. 送付済み報酬の取得

```php
// ミッション報酬を取得
$missionRewards = $this->rewardDelegator->getSentRewards(MissionReward::class);

// レベルアップ報酬を取得
$levelUpRewards = $this->rewardDelegator->getSentRewards(UserLevelUpReward::class);
```

**重要**: 送付済み報酬は報酬クラスごとに分類されているため、クラスを指定して取得します。

## 実装例: ステージクリア報酬

**ファイルパス**: `api/app/Domain/Stage/Services/StageEndQuestService.php`

ステージクリア時の報酬追加パターンです。

### 報酬の追加

```php
private function addStageReward(
    UsrStageInterface $usrStage,
    MstStageInterface $mstStage,
    Collection $oprCampaigns,
): void {
    $sendRewards = collect();

    // デフォルト報酬（経験値、コイン）
    $sendRewards = $sendRewards->merge($this->calcBaseRewards($mstStage, $oprCampaigns));

    // 初回クリア報酬
    if ($usrStage->isFirstClear()) {
        $sendRewards = $sendRewards->merge($this->calcFirstClearRewards(
            $this->stageService->getFirstClearRewardsByMstStageId(
                $mstStage->getId(),
            ),
        ));
    }

    // 定常クリア報酬
    $sendRewards = $sendRewards->merge($this->calcAlwaysClearRewards(
        $this->stageService->getAlwaysRewardsByMstStageId(
            $mstStage->getId(),
        ),
        $oprCampaigns,
    ));

    // ランダムクリア報酬
    $sendRewards = $sendRewards->merge($this->calcRandomClearRewards(
        $this->stageService->lotteryPercentageStageReward(
            $this->stageService->getRandomRewardsByMstStageId(
                $mstStage->getId(),
            ),
        ),
    ));

    // ★ 報酬をまとめて追加
    $this->rewardDelegator->addRewards($sendRewards);
}
```

### 報酬の作成例

```php
private function calcBaseRewards(
    MstStageInterface $mstStage,
    Collection $oprCampaigns,
) {
    $sendRewards = collect();

    // 経験値
    $exp = $this->stageService->applyCampaignByRewardType(
        $oprCampaigns,
        RewardType::EXP->value,
        $mstStage->getExp()
    );

    // コイン
    $coin = $this->stageService->applyCampaignByRewardType(
        $oprCampaigns,
        RewardType::COIN->value,
        $mstStage->getCoin()
    );

    // コイン報酬を作成
    $sendRewards->push(
        new StageAlwaysClearReward(
            RewardType::COIN->value,
            null,
            $coin,
            $mstStage->getId(),
        )
    );

    // 経験値報酬を作成
    $sendRewards->push(
        new StageAlwaysClearReward(
            RewardType::EXP->value,
            null,
            $exp,
            $mstStage->getId(),
        )
    );

    return $sendRewards;
}
```

### ポイント解説

#### 1. 複数の報酬をまとめて追加

```php
// Collection<BaseReward> を渡す
$this->rewardDelegator->addRewards($sendRewards);
```

#### 2. 報酬クラスの使い分け

- `StageAlwaysClearReward`: 定常クリア報酬
- `StageFirstClearReward`: 初回クリア報酬
- `MissionReward`: ミッション報酬
- `UserLevelUpReward`: レベルアップ報酬

報酬クラスごとに異なるレスポンスデータを作成できます。

## ポイント

### ✅ 必ずトランザクション内で実行

```php
$this->applyUserTransactionChanges(function () {
    $this->rewardDelegator->sendRewards($usrUserId, $platform, $now);
});
```

**理由**:
FreeDiamondの配布処理は課金基盤で実装されており、UsrModelManagerを使ったユーザーキャッシュ機構を使っていない。
そのため、遅延DB一括更新を実行することができないため、sendRewards処理は、トランザクション内で必ず実行する必要がある。

### ✅ デフォルトポリシーは引数省略可能

```php
// 以下は同じ意味
$this->rewardDelegator->sendRewards($usrUserId, $platform, $now);
$this->rewardDelegator->sendRewards($usrUserId, $platform, $now, policy: null);
$this->rewardDelegator->sendRewards($usrUserId, $platform, $now, RewardSendPolicy::createDefaultPolicy());
```

### ✅ 送付済み報酬は報酬クラスで取得

```php
// ミッション報酬のみ取得
$missionRewards = $this->rewardDelegator->getSentRewards(MissionReward::class);

// レベルアップ報酬のみ取得
$levelUpRewards = $this->rewardDelegator->getSentRewards(UserLevelUpReward::class);

// ステージ報酬のみ取得
$stageRewards = $this->rewardDelegator->getSentRewards(StageAlwaysClearReward::class);
```

### ✅ 報酬追加は送付前に

```php
// 正しい順序
$this->rewardDelegator->addRewards($rewards);  // 1. 追加
$this->rewardDelegator->sendRewards(...);       // 2. 送付

// 間違った順序
$this->rewardDelegator->sendRewards(...);       // 先に送付
$this->rewardDelegator->addRewards($rewards);  // 後で追加 → 送付されない！
```

### ❌ 間違った使い方

```php
// ❌ トランザクション外で実行
$this->rewardDelegator->sendRewards($usrUserId, $platform, $now);

// ❌ 送付後に報酬を追加
$this->rewardDelegator->sendRewards($usrUserId, $platform, $now);
$this->rewardDelegator->addRewards($rewards); // 送付されない
```

## まとめ

- **基本フロー**: addRewards → sendRewards → getSentRewards
- **トランザクション内で実行**: 必須
- **デフォルトポリシー**: 引数省略可能（通常の報酬送付）
- **報酬クラスで取得**: レスポンス作成時に報酬クラスごとに取得
- **追加は送付前に**: 送付後に追加した報酬は送付されない

エラーハンドリングが必要な場合は **[examples/error-handling.md](error-handling.md)** を参照してください。
