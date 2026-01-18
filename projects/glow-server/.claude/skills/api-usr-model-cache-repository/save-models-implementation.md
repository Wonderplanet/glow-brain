# saveModels実装ガイド

## saveModelsとは

UsrModelManagerから呼び出される、DB一括更新ロジックを記述するメソッドです。

**重要ポイント:**
- **新規テーブル追加時**、**列追加時**、**列変更時**は必ずsaveModelsを更新する
- 実装者が直接実行することは想定していない（UsrModelManagerが自動で呼び出す）

## 実装手順

### 1. 新規テーブル追加時

#### Step 1: Repositoryクラスの作成

1ユーザーあたりのレコード数に応じて、継承するクラスを選択します。

```php
// 1ユーザーあたり最大1レコードの場合
class UsrUserLoginRepository extends UsrModelSingleCacheRepository
{
    protected string $modelClass = UsrUserLogin::class;
}

// 1ユーザーあたり2レコード以上の場合
class UsrExchangeLineupRepository extends UsrModelMultiCacheRepository
{
    protected string $modelClass = UsrExchangeLineup::class;
}
```

#### Step 2: saveModelsメソッドの実装

**SingleCacheRepository の場合:**

1レコードのみなので、saveModelsの実装は**不要**です（親クラスのデフォルト実装を使用）。

**MultiCacheRepository の場合:**

必ずsaveModelsを実装してください。

```php
protected function saveModels(Collection $models): void
{
    // Step 1: upsert用の配列を作成（全カラムを記述）
    $upsertValues = $models->map(function (UsrExchangeLineup $model) {
        return [
            'id' => $model->getId(),
            'usr_user_id' => $model->getUsrUserId(),
            'mst_exchange_lineup_id' => $model->getMstExchangeLineupId(),
            'mst_exchange_id' => $model->getMstExchangeId(),
            'trade_count' => $model->getTradeCount(),
            'reset_at' => $model->getResetAt(),
        ];
    })->toArray();

    // Step 2: upsertを実行
    UsrExchangeLineup::query()->upsert(
        $upsertValues,
        ['usr_user_id', 'mst_exchange_lineup_id', 'mst_exchange_id'],  // ユニークキー
        ['trade_count', 'reset_at'],  // 更新対象カラム（ユニークキー以外）
    );
}
```

### 2. 列追加時の手順

既存テーブルに列を追加する場合、**必ずsaveModelsも更新**してください。

#### 例: usr_pvpテーブルに `max_score` カラムを追加

**Before:**
```php
protected function saveModels(Collection $models): void
{
    $upsertValues = $models->map(function (UsrPvpInterface $model) {
        return [
            'id' => $model->getId(),
            'usr_user_id' => $model->getUsrUserId(),
            'sys_pvp_season_id' => $model->getSysPvpSeasonId(),
            'score' => $model->getScore(),
            'ranking' => $model->getRanking(),
        ];
    })->toArray();

    UsrPvp::query()->upsert(
        $upsertValues,
        ['usr_user_id', 'sys_pvp_season_id'],
        ['score', 'ranking'],  // 更新対象カラム
    );
}
```

**After: max_scoreカラムを追加**
```php
protected function saveModels(Collection $models): void
{
    $upsertValues = $models->map(function (UsrPvpInterface $model) {
        return [
            'id' => $model->getId(),
            'usr_user_id' => $model->getUsrUserId(),
            'sys_pvp_season_id' => $model->getSysPvpSeasonId(),
            'score' => $model->getScore(),
            'max_score' => $model->getMaxScore(),  // ← 追加
            'ranking' => $model->getRanking(),
        ];
    })->toArray();

    UsrPvp::query()->upsert(
        $upsertValues,
        ['usr_user_id', 'sys_pvp_season_id'],
        ['score', 'max_score', 'ranking'],  // ← max_scoreを追加
    );
}
```

### 3. 列変更時の手順

列名や型を変更する場合も、saveModelsを更新してください。

#### 例: `reset_at` を `last_reset_at` に変更

**Before:**
```php
return [
    'reset_at' => $model->getResetAt(),
];
```

**After:**
```php
return [
    'last_reset_at' => $model->getLastResetAt(),  // カラム名変更
];
```

## saveModels実装のチェックリスト

新規テーブル追加・列追加・列変更時は、以下を確認してください。

### 必須チェック項目

- [ ] **全カラムがsaveModelsに含まれているか**
  - マイグレーションで追加したカラムが漏れなく記述されているか確認
- [ ] **Modelのgetterメソッドが存在するか**
  - `$model->getMaxScore()` のようなgetterが実装されているか確認
- [ ] **upsertのユニークキーが正しいか**
  - DBスキーマのユニークキー制約と一致しているか確認
- [ ] **更新対象カラムが正しいか**
  - ユニークキー以外のカラムが更新対象に含まれているか確認
- [ ] **SingleCacheRepositoryの場合、saveModelsは不要**
  - 1レコードのみの場合は親クラスのデフォルト実装を使用

### 実装パターン

#### パターン1: 標準的なMultiCacheRepository

```php
protected function saveModels(Collection $models): void
{
    $upsertValues = $models->map(function (UsrArtwork $model) {
        return [
            'id' => $model->getId(),
            'usr_user_id' => $model->getUsrUserId(),
            'mst_artwork_id' => $model->getMstArtworkId(),
            'is_new_encyclopedia' => $model->getIsNewEncyclopedia(),
        ];
    })->toArray();

    UsrArtwork::query()->upsert(
        $upsertValues,
        ['usr_user_id', 'mst_artwork_id'],  // ユニークキー
    );
}
```

#### パターン2: 更新対象カラムを明示

```php
UsrPvp::query()->upsert(
    $upsertValues,
    ['usr_user_id', 'sys_pvp_season_id'],  // ユニークキー
    ['score', 'ranking', 'is_season_reward_received'],  // 更新対象
);
```

## よくあるミス

### ミス1: 新規カラムをsaveModelsに追加し忘れ

**症状:** キャッシュからDB更新しても、新規カラムが更新されない

**解決策:** saveModelsに新規カラムを追加する

```php
// ❌ 間違い: new_columnが含まれていない
return [
    'id' => $model->getId(),
    'usr_user_id' => $model->getUsrUserId(),
];

// ✅ 正しい: new_columnを追加
return [
    'id' => $model->getId(),
    'usr_user_id' => $model->getUsrUserId(),
    'new_column' => $model->getNewColumn(),  // 追加
];
```

### ミス2: Modelにgetterメソッドがない

**症状:** `Call to undefined method getNewColumn()`

**解決策:** Modelクラスにgetterを実装する

```php
// Modelクラスに追加
public function getNewColumn(): string
{
    return $this->new_column;
}
```

### ミス3: SingleCacheRepositoryでsaveModelsを実装してしまう

**症状:** 重複した実装により、メンテナンスコストが増加

**解決策:** SingleCacheRepositoryの場合、saveModelsは不要（親クラスのデフォルト実装を使用）

```php
// ❌ 不要な実装
class UsrUserLoginRepository extends UsrModelSingleCacheRepository
{
    protected function saveModels(Collection $models): void
    {
        // 不要！
    }
}

// ✅ 正しい: saveModelsは記述しない
class UsrUserLoginRepository extends UsrModelSingleCacheRepository
{
    protected string $modelClass = UsrUserLogin::class;

    // saveModelsは親クラスに任せる
}
```

## まとめ

- **新規テーブル追加時**: MultiCacheRepositoryの場合のみsaveModelsを実装
- **列追加時**: saveModelsに新規カラムを追加（upsertの配列と更新対象カラムの両方）
- **列変更時**: saveModelsのカラム名を変更
- **SingleCacheRepository**: saveModelsは不要
