# ステージ系実装パターン

ステージクリア関連APIでの `applyUserTransactionChanges` 使用パターンです。

## 特徴

- 報酬配布（クリア報酬、初回報酬、ランダム報酬等）
- レベルアップ判定と追加処理
- ユーザーデータ更新（セッション、進捗等）
- 戻り値の受け取り

## 処理フロー

```
1. 前処理
   - マスタデータ取得
   - ステージセッション取得
   - レベルアップ前の状態保存

2. ビジネスロジック（callback 外）
   - クエストタイプごとの終了処理
   - ログ送信
   - 敵発見情報保存
   - パック開放処理
   - 報酬追加（addRewards は Service 内で実行）

3. トランザクション処理（callback 内）
   - 報酬配布実行
   - レベルアップ判定
   - 追加パック開放（レベルアップ時）
   - 戻り値を返す

4. レスポンス作成
   - 配布済み報酬を取得
   - レベルアップ情報作成
   - ResultData 作成
```

## 実装例

**ファイル**: `api/app/Domain/Stage/UseCases/StageEndUseCase.php`

```php
public function exec(
    CurrentUser $user,
    int $platform,
    string $mstStageId,
    array $inGameBattleLog = [],
): StageEndResultData {
    $usrUserId = $user->getUsrUserId();
    $now = $this->clock->now();

    // ===== 1. 前処理 =====
    $mstStage = $this->mstStageRepository->getStageGracePeriod($mstStageId, $now, true);
    $mstQuest = $this->mstQuestRepository->getQuestGracePeriod(
        $mstStage->getMstQuestId(),
        $now,
        isThrowError: true,
    );
    $questType = $mstQuest->getQuestType();

    // レベルアップ前の状態を保存
    $beforeUsrUserParameter = $this->userDelegator->getUsrUserParameterByUsrUserId($usrUserId);
    $beforeExp = $beforeUsrUserParameter->getExp();
    $beforeLevel = $beforeUsrUserParameter->getLevel();

    // ===== 2. ビジネスロジック =====
    $inGameBattleLogData = $this->stageLogService->makeStageInGameBattleLogData($inGameBattleLog);
    $usrStageSession = $this->usrStageSessionRepository->findByUsrUserId($usrUserId);
    $oprCampaigns = $this->oprCampaignRepository
        ->getByIds($usrStageSession->getOprCampaignIds())
        ->keyBy(fn($campaign) => $campaign->getCampaignType());

    // クエストタイプごとの終了処理（報酬追加もここで実行）
    $stageEndQuestService = $this->questServiceFactory->getStageEndQuestService($questType, $mstStageId, $now);
    $stageEndQuestService->end(
        $usrUserId,
        $mstStage,
        $usrStageSession,
        $inGameBattleLogData,
        $oprCampaigns,
        $this->clock->now(),
    );

    // ログ送信
    $this->stageLogService->sendEndLog(...);

    // 敵発見情報保存
    $newUsrEnemyDiscoveries = $this->inGameDelegator->addNewUsrEnemyDiscoveries(...);

    // ステージクリアパック開放
    $usrConditionPacks = $this->shopDelegator->releaseStageClearPack($usrUserId, $mstStageId, $now);

    // 探索報酬ステージID更新
    $this->idleIncentiveDelegator->updateRewardMstStageId($usrUserId, $mstStageId, $now);

    // ===== 3. トランザクション処理（戻り値あり） =====
    list(
        $usrConditionPacks,
        $afterUsrUserParameter,
    ) = $this->applyUserTransactionChanges(function () use (
        $usrUserId,
        $platform,
        $now,
        $beforeLevel,
        $usrConditionPacks,
    ) {
        // 報酬配布実行
        $this->rewardDelegator->sendRewards($usrUserId, $platform, $now);

        // レベルアップ判定
        $afterUsrUserParameter = $this->userDelegator->getUsrUserParameterByUsrUserId($usrUserId);
        if ($beforeLevel < $afterUsrUserParameter->getLevel()) {
            // レベルアップパック開放
            $usrConditionPacks = $usrConditionPacks->merge(
                $this->shopDelegator->releaseUserLevelPack(
                    $usrUserId,
                    $afterUsrUserParameter->getLevel(),
                    $now
                )
            );
        }

        return [
            $usrConditionPacks,
            $afterUsrUserParameter,
        ];
    });

    // ===== 4. レスポンス作成 =====
    $userLevelUpData = new UserLevelUpData(
        $beforeExp,
        $afterUsrUserParameter->getExp(),
        $this->rewardDelegator->getSentRewards(UserLevelUpReward::class),
    );

    return new StageEndResultData(
        $userLevelUpData,
        $this->rewardDelegator->getSentRewards(StageAlwaysClearReward::class),
        $this->rewardDelegator->getSentRewards(StageRandomClearReward::class),
        $this->rewardDelegator->getSentRewards(StageFirstClearReward::class),
        $this->rewardDelegator->getSentRewards(StageSpeedAttackClearReward::class),
        $usrConditionPacks,
        $this->usrModelDiffGetService->getChangedUsrArtworks(),
        $this->usrModelDiffGetService->getChangedUsrArtworkFragments(),
        $this->usrModelDiffGetService->getChangedUsrItems(),
        $this->usrModelDiffGetService->getChangedUsrUnits(),
        $newUsrEnemyDiscoveries,
        $usrStageSession->getOprCampaignIds()
    );
}
```

## ポイント

### 戻り値の受け取り

```php
// list() または array destructuring で受け取る
list($resultA, $resultB) = $this->applyUserTransactionChanges(function () use (...) {
    // 処理
    return [$resultA, $resultB];
});
```

### レベルアップ判定パターン

```php
// 1. 報酬配布前のレベルを保存
$beforeLevel = $beforeUsrUserParameter->getLevel();

// 2. callback 内で報酬配布後にレベルを再取得
$this->applyUserTransactionChanges(function () use ($beforeLevel, ...) {
    $this->rewardDelegator->sendRewards(...);

    // 報酬配布後のレベルを取得
    $afterUsrUserParameter = $this->userDelegator->getUsrUserParameterByUsrUserId($usrUserId);

    // レベルアップ判定
    if ($beforeLevel < $afterUsrUserParameter->getLevel()) {
        // レベルアップ時の追加処理
    }

    return [..., $afterUsrUserParameter];
});
```

### 複数報酬タイプの取得

```php
// 報酬タイプごとに取得
$this->rewardDelegator->getSentRewards(StageAlwaysClearReward::class);
$this->rewardDelegator->getSentRewards(StageRandomClearReward::class);
$this->rewardDelegator->getSentRewards(StageFirstClearReward::class);
$this->rewardDelegator->getSentRewards(StageSpeedAttackClearReward::class);
$this->rewardDelegator->getSentRewards(UserLevelUpReward::class);
```

### UsrModelDiffGetService の活用

```php
// トランザクション後に変更されたユーザーデータを取得
$this->usrModelDiffGetService->getChangedUsrUnits();
$this->usrModelDiffGetService->getChangedUsrItems();
$this->usrModelDiffGetService->getChangedUsrArtworks();
```

## 関連ファイル

- `api/app/Domain/Stage/UseCases/StageStartUseCase.php` - ステージ開始
- `api/app/Domain/Stage/UseCases/StageAbortUseCase.php` - ステージ中断
- `api/app/Domain/Stage/Services/StageEndQuestServiceInterface.php` - 終了処理インターフェース
