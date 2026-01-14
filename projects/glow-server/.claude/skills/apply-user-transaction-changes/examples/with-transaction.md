# applyUserTransactionChanges 使用例

## 基本パターン

```php
public function exec(CurrentUser $user, ...): ResultData
{
    $usrUserId = $user->getUsrUserId();
    $now = $this->clock->now();

    // 1. 前処理（バリデーション、データ取得）
    $mstData = $this->repository->get($mstId);
    $usrData = $this->repository->getByUsrUserId($usrUserId);

    // 2. ビジネスロジック（UsrModelManager 経由の値変更）
    $usrData->incrementCount();
    $usrData->setUpdatedAt($now->toDateTimeString());

    // 3. 報酬追加（まだ配布しない）
    $rewards = $this->service->makeRewards($mstData);
    $this->rewardDelegator->addRewards($rewards);

    // 4. トランザクション処理
    $this->applyUserTransactionChanges(function () use (
        $usrUserId,
        $platform,
        $now,
    ) {
        // 報酬配布実行
        $this->rewardDelegator->sendRewards($usrUserId, $platform, $now);
    });

    // 5. レスポンス作成
    return new ResultData(
        $this->rewardDelegator->getSentRewards(SomeReward::class),
        ...
    );
}
```

## 戻り値を受け取るパターン

```php
list($resultA, $resultB) = $this->applyUserTransactionChanges(function () use (
    $usrUserId,
    $platform,
    $now,
    $beforeLevel,
) {
    // 報酬配布実行
    $this->rewardDelegator->sendRewards($usrUserId, $platform, $now);

    // 報酬受け取り後の状態を取得
    $afterUsrUserParameter = $this->userDelegator->getUsrUserParameterByUsrUserId($usrUserId);

    // レベルアップ判定
    if ($beforeLevel < $afterUsrUserParameter->getLevel()) {
        $usrConditionPacks = $this->shopDelegator->releaseUserLevelPack(
            $usrUserId,
            $afterUsrUserParameter->getLevel(),
            $now
        );
    }

    return [
        $usrConditionPacks,
        $afterUsrUserParameter,
    ];
});
```

## 課金基盤連携パターン

```php
$this->applyUserTransactionChanges(function () use (
    $usrUserId,
    $platform,
    $now,
    $logGachaAction,
) {
    // 1. リソース消費（課金基盤連携）
    $this->gachaService->execConsumeResource($logGachaAction);

    // 2. 報酬配布
    $this->rewardDelegator->sendRewards($usrUserId, $platform, $now);

    // 3. キャッシュ保存（ガチャ履歴など）
    $gachaRewards = $this->rewardDelegator->getSentRewards(GachaReward::class);
    $this->gachaService->addGachaHistory(
        $usrUserId,
        $oprGachaId,
        $costType->value,
        $costId,
        $costNum,
        $playNum,
        $now,
        $gachaRewards
    );
});
```

## 実装コード参照

### GachaDrawUseCase

**ファイル**: `api/app/Domain/Gacha/UseCases/GachaDrawUseCase.php`

```php
// トランザクションで囲んでDB更新処理を実行する
$this->applyUserTransactionChanges(function () use (
    $usr,
    $now,
    $platform,
    $logGachaAction,
    $oprGachaId,
    $costType,
    $costId,
    $costNum,
    $playNum,
) {
    // リソース消費を実行
    $this->gachaService->execConsumeResource($logGachaAction);

    // 報酬配布実行
    $this->rewardDelegator->sendRewards($usr->getUsrUserId(), $platform, $now);

    // ガチャ履歴のキャッシュ保存
    $gachaRewards = $this->rewardDelegator->getSentRewards(GachaReward::class);
    $this->gachaService->addGachaHistory(
        $usr->getUsrUserId(),
        $oprGachaId,
        $costType->value,
        $costId,
        $costNum,
        $playNum,
        $now,
        $gachaRewards
    );
});
```

### StageEndUseCase

**ファイル**: `api/app/Domain/Stage/UseCases/StageEndUseCase.php`

```php
// トランザクション処理
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
    // 報酬配布を実行
    $this->rewardDelegator->sendRewards($usrUserId, $platform, $now);

    // 報酬受け取りでレベルが上っている可能性があるので再取得
    $afterUsrUserParameter = $this->userDelegator->getUsrUserParameterByUsrUserId($usrUserId);
    if ($beforeLevel < $afterUsrUserParameter->getLevel()) {
        // レベルアップパックの開放
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
```

## チェックポイント

- [ ] `use UseCaseTrait;` を宣言しているか
- [ ] callback 内に課金基盤連携、報酬配布を含めているか
- [ ] 戻り値が必要な場合、正しく受け取っているか
- [ ] callback の use 句に必要な変数を渡しているか
