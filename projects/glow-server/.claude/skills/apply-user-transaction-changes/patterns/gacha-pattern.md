# ガチャ系実装パターン

ガチャ関連APIでの `applyUserTransactionChanges` 使用パターンです。

## 特徴

- 課金基盤連携（ダイヤ消費）
- 報酬配布（ユニット、アイテム等）
- ミッション進捗更新
- ガチャ履歴のキャッシュ保存

## 処理フロー

```
1. バリデーション
   - ガシャ情報取得・期限チェック
   - 既に引いた回数チェック
   - N連数チェック
   - コスト種類チェック

2. ビジネスロジック（callback 外）
   - ガシャ回数カウント
   - 広告視聴処理（広告の場合）
   - リソース消費設定（まだ実行しない）
   - ガシャ抽選
   - 報酬追加（addRewards）
   - ミッショントリガー送信
   - ログ作成

3. トランザクション処理（callback 内）
   - リソース消費実行（課金基盤連携）
   - 報酬配布実行
   - ガチャ履歴キャッシュ保存

4. レスポンス作成
   - 配布済み報酬を取得
   - ResultData 作成
```

## 実装例

**ファイル**: `api/app/Domain/Gacha/UseCases/GachaDrawUseCase.php`

```php
public function exec(
    CurrentUser $usr,
    string $oprGachaId,
    int $drewCount,
    int $playNum,
    ?string $costId,
    int $costNum,
    int $platform,
    string $billingPlatform,
    CostType $costType
): GachaDrawResultData {
    // ===== 1. バリデーション =====
    $oprGacha = $this->gachaService->getOprGacha($oprGachaId);
    $usrGacha = $this->gachaService->getUsrGacha($usr->getUsrUserId(), $oprGacha->getId());
    $now = $this->clock->now();
    $this->gachaService->validateExpiration($usrGacha, $now);
    $this->gachaService->validateDrewCount($drewCount, $usrGacha->getCount());
    $this->gachaService->validatePlayNum($playNum, $oprGacha->getMultiDrawCount());
    $this->gachaService->validateCostType($oprGacha, $costType);

    // ===== 2. ビジネスロジック =====
    // ガシャ回数カウント
    $usrGacha->incrementPlayCount($playNum);
    $this->gachaService->validatePlayCount($oprGacha, $usrGacha);
    $usrGacha->setPlayedAt($now->toDateTimeString());

    // 広告処理（該当する場合）
    $checkedAd = false;
    if ($costType === CostType::AD) {
        $usrGacha->incrementAdPlayCount($playNum);
        $this->gachaService->validateAd($oprGacha, $usrGacha);
        $usrGacha->setAdPlayedAt($now->toDateTimeString());
        $checkedAd = true;
        $this->adPlayService->adPlay(...);
    }

    // リソース消費設定（まだ実行しない）
    $this->gachaService->setConsumeResource(
        $oprGacha, $usr->getUsrUserId(), $playNum,
        $costId, $costNum, $platform, $billingPlatform,
        $checkedAd, $costType
    );

    // ガシャ抽選
    $gachaResultData = $this->gachaService->draw(...);
    $gachaRewards = $this->gachaService->makeGachaRewardByGachaBoxes(...);
    $this->rewardDelegator->addRewards($gachaRewards);  // 報酬追加

    // ミッショントリガー送信
    $this->gachaMissionTriggerService->sendDrawTrigger($oprGacha->getId(), $playNum);

    // ログ作成
    $logGachaAction = $this->logGachaActionRepository->create(...);

    // ユーザデータ保存設定
    $this->gachaService->saveUsr($usrGacha, $usrGachaUppers);

    // ログ送信
    $this->gachaLogService->sendGachaLog(...);

    // ===== 3. トランザクション処理 =====
    $this->applyUserTransactionChanges(function () use (
        $usr, $now, $platform, $logGachaAction,
        $oprGachaId, $costType, $costId, $costNum, $playNum,
    ) {
        // リソース消費実行（課金基盤連携）
        $this->gachaService->execConsumeResource($logGachaAction);

        // 報酬配布実行
        $this->rewardDelegator->sendRewards($usr->getUsrUserId(), $platform, $now);

        // ガチャ履歴キャッシュ保存
        $gachaRewards = $this->rewardDelegator->getSentRewards(GachaReward::class);
        $this->gachaService->addGachaHistory(...);
    });

    // ===== 4. レスポンス作成 =====
    $gachaRewards = $this->rewardDelegator
        ->getSentRewards(GachaReward::class)
        ->sortBy(fn(GachaReward $reward) => $reward->getSortOrder())
        ->values();

    return new GachaDrawResultData(
        $gachaRewards,
        $this->usrModelDiffGetService->getChangedUsrUnits(),
        $this->usrModelDiffGetService->getChangedUsrItems(),
        $this->makeUsrParameterData(...),
        $usrGacha,
        $usrGachaUppers
    );
}
```

## ポイント

### callback 内に含める処理

1. **リソース消費実行** - 課金基盤との連携はトランザクション内で
2. **報酬配布実行** - `rewardDelegator->sendRewards()`
3. **キャッシュ保存** - ガチャ履歴など

### callback 外で行う処理

1. **バリデーション** - 早期エラー検出
2. **ログ作成** - 参照用のログオブジェクト作成
3. **リソース消費設定** - 設定のみ、実行はcallback内
4. **報酬追加** - `addRewards()` は callback 外
5. **ミッショントリガー送信** - callback 外

### UsrModelManager の活用

```php
// UsrModelManager 経由の変更は saveAll() で自動保存される
$usrGacha->incrementPlayCount($playNum);  // callback 外で変更
$usrGacha->setPlayedAt($now->toDateTimeString());

// 明示的な saveUsr 呼び出しは不要（saveAll で自動保存）
$this->gachaService->saveUsr($usrGacha, $usrGachaUppers);
```
