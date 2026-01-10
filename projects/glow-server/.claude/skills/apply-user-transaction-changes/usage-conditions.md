# 使用条件と判断基準

## applyUserTransactionChanges を使用する条件

以下の **いずれか** に該当する場合、`applyUserTransactionChanges` を使用します:

### 1. 報酬配布がある

```php
// RewardDelegator で報酬を追加・配布する場合
$this->rewardDelegator->addRewards($rewards);

$this->applyUserTransactionChanges(function () use (...) {
    $this->rewardDelegator->sendRewards($usrUserId, $platform, $now);
});
```

### 2. 課金基盤との連携がある

```php
// 有料ダイヤの消費、課金アイテムの購入など
$this->applyUserTransactionChanges(function () use (...) {
    $this->gachaService->execConsumeResource($logGachaAction);  // 課金基盤連携
});
```

### 3. usr_* テーブルへの書き込みがある

```php
// UsrModelManager を使用する Repository で値を変更している場合
$usrGacha->incrementPlayCount($playNum);
$usrGacha->setPlayedAt($now->toDateTimeString());

$this->applyUserTransactionChanges(function () use (...) {
    // 変更は saveAll() で自動保存される
});
```

### 4. ミッション進捗更新が必要

```php
// ミッショントリガーを送信している場合
$this->gachaMissionTriggerService->sendDrawTrigger($oprGachaId, $playNum);

$this->applyUserTransactionChanges(function () use (...) {
    // updateMissionProgresses() が自動実行される
});
```

---

## processWithoutUserTransactionChanges を使用する条件

以下の **すべて** に該当する場合、`processWithoutUserTransactionChanges` を使用します:

### 1. データ変更がない（読み取り専用）

```php
// データを取得して返すだけ
public function exec(CurrentUser $user, string $language): GameFetchResultData
{
    $gameFetchData = $this->gameService->fetch($usrUserId, $now, $language, $gameStartAt);

    $this->processWithoutUserTransactionChanges();
    return new GameFetchResultData($gameFetchData);
}
```

### 2. ログ保存・アクセス日時更新のみ必要

```php
// ユーザーデータは変更しないが、アクセスログは記録したい
$this->processWithoutUserTransactionChanges();
```

---

## 判断フローチャート

```
API実装開始
    │
    ├─ 報酬配布がある？
    │   └─ Yes → applyUserTransactionChanges
    │
    ├─ 課金基盤連携がある？
    │   └─ Yes → applyUserTransactionChanges
    │
    ├─ usr_* テーブルへの書き込みがある？
    │   └─ Yes → applyUserTransactionChanges
    │
    ├─ ミッション進捗更新が必要？
    │   └─ Yes → applyUserTransactionChanges
    │
    └─ 上記すべてNo
        └─ processWithoutUserTransactionChanges
```

---

## 具体例

### applyUserTransactionChanges を使う例

| API | 理由 |
|-----|------|
| `POST /api/gacha/draw` | 報酬配布、課金基盤連携、ミッション進捗更新 |
| `POST /api/stage/end` | 報酬配布、usr_stage_sessions更新 |
| `POST /api/exchange/trade` | 報酬配布、usr更新 |
| `POST /api/user/buy_stamina_diamond` | 課金基盤連携、usr更新 |
| `POST /api/unit/level_up` | 報酬消費、usr更新 |

### processWithoutUserTransactionChanges を使う例

| API | 理由 |
|-----|------|
| `POST /api/game/fetch` | データ取得のみ |
| `POST /api/game/server_time` | サーバー時刻取得のみ |
| `POST /api/gacha/history` | 履歴取得のみ |
| `POST /api/pvp/ranking` | ランキング取得のみ |
| `POST /api/user/info` | ユーザー情報取得のみ |

---

## 注意事項

### どちらも呼ばないのはNG

```php
// ❌ これはダメ
public function exec(...): ResultData
{
    $data = $this->service->getData();
    return new ResultData($data);
    // processWithoutUserTransactionChanges() も applyUserTransactionChanges() も呼んでいない
}
```

どちらかを **必ず** 呼ぶ必要があります。理由:
- アクセス日時更新（BankF001用）が実行されない
- ログ保存が実行されない

### Webhook 受信は別パターン

外部サービスからの Webhook 受信は、認証ユーザーが存在しないため、これらのメソッドは使用しません。
別のトランザクション制御パターンを使用します。
