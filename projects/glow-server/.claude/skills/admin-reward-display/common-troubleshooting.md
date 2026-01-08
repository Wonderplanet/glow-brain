# よくある間違いとトラブルシューティング

全パターン共通のよくある間違いとトラブルシューティングをまとめます。

## 共通のよくある間違い

### ❌ 間違い1: Model属性が未定義

```php
// ❌ Modelにreward属性がない状態で使用
$this->addRewardInfoToPaginatedRecords($paginator);
```

**エラー**: `Undefined property: reward`

✅ **正しい方法**: Modelに `getRewardAttribute()` を実装

```php
class MstMissionReward extends BaseMstMissionReward
{
    public function getRewardAttribute()
    {
        return new RewardDto(
            $this->id,
            $this->resource_type,
            $this->resource_id,
            $this->resource_amount,
        );
    }
}
```

### ❌ 間違い2: フィールド名の不一致

```php
// スキーマ生成時
Forms\Components\Repeater::make('rewards')
    ->schema(self::getSendRewardSchema(
        $this->getDistributionTypes(),
        'reward_type',
        'reward_id',
        'amount'
    ))

// ❌ 保存時に異なるフィールド名を使用
foreach ($data['rewards'] as $reward) {
    Model::create([
        'resource_type' => $reward['distributionType'],  // 存在しない
    ]);
}
```

✅ **正しい方法**: フィールド名を一致させる

```php
foreach ($data['rewards'] as $reward) {
    Model::create([
        'resource_type' => $reward['reward_type'],  // 一致
        'resource_id' => $reward['reward_id'],
        'resource_amount' => $reward['amount'],
    ]);
}
```

### ❌ 間違い3: リソースIDが不要な報酬タイプの扱い

Coin、Stamina、ExpなどはリソースIDを持ちません。

```php
// ❌ nullチェックなし
Model::create([
    'resource_type' => $reward['reward_type'],
    'resource_id' => $reward['reward_id'],  // nullになる可能性
]);
```

✅ **正しい方法**: nullを許容

```php
Model::create([
    'resource_type' => $reward['reward_type'],
    'resource_id' => $reward['reward_id'] ?? null,  // OK
    'resource_amount' => $reward['amount'],
]);
```

## トラブルシューティング

### 報酬情報が表示されない

**確認手順**:

1. **ブラウザの開発者ツールでHTMLを確認**
   - `reward_info` 属性がレコードに存在するか？
   - 値が `null` または空でないか？

2. **dd()でデバッグ**

```php
protected function paginateTableQuery(Builder $query): Paginator | CursorPaginator
{
    $paginator = parent::paginateTableQuery($query);
    $this->addRewardInfoToPaginatedRecords($paginator);

    // デバッグ
    dd($paginator->items()[0]->reward_info);

    return $paginator;
}
```

3. **Modelの `reward` 属性を確認**

```php
// Tinkerやdd()で確認
$record = MstMissionReward::first();
dd($record->reward); // RewardDtoオブジェクトが返るか？
```

### "Call to a member function on null" エラー

**原因**: `$rewardInfos->get($record->id)` が `null` を返している

**確認事項**:
- RewardDtoのIDとレコードのIDが一致しているか？
- `getRewardAttribute()` で正しいIDを設定しているか？

```php
// Modelで確認
public function getRewardAttribute()
{
    return new RewardDto(
        $this->id,  // ← これがレコードのIDと一致するか？
        $this->resource_type,
        $this->resource_id,
        $this->resource_amount,
    );
}
```

### パフォーマンスが悪い（N+1問題）

**確認方法**:
1. Laravel Debugbarまたはブラウザのネットワークタブを使用
2. ページごとに実行されるクエリ数を確認

**期待されるクエリ数**:
- 基本: 2クエリ（レコード取得 + カウント）
- 報酬タイプごとに +1クエリ
- 例: 3種類の報酬タイプがある場合 → 5クエリ

**N+1が発生している場合**:
- Modelの `getRewardAttribute()` が正しく実装されているか確認
- サービスクラスが一括取得を行っているか確認

### クエリとDtoリストのクエリが異なる

```php
// ❌ 間違い
$query = MstMissionReward::query()
    ->where('group_id', 1);

$rewardDtoList = MstMissionReward::query()
    ->where('group_id', 2)  // 異なる条件
    ->get()
    ->map(fn($r) => $r->reward);
```

**結果**: テーブルに表示されるレコードと報酬情報が一致しない

✅ **正しい方法**: クエリ条件を統一

```php
$groupId = $mstMissionDaily->mst_mission_reward_group_id;

$query = MstMissionReward::query()->where('group_id', $groupId);
$rewardDtoList = MstMissionReward::query()->where('group_id', $groupId)->get()->map(fn($r) => $r->reward);
```

### リソースID選択肢が表示されない（フォーム）

**原因**: 報酬タイプが選択されていないか、`reactive()` が機能していない

**確認事項**:
1. 報酬タイプのSelectで `->reactive()` が設定されているか
2. Livewireが正しく動作しているか（ブラウザコンソールでエラー確認）
3. `getRewardResourceIds()` メソッドが正しく実装されているか（Trait内）

### 保存時にデータが消える（フォーム）

**原因**: フィールド名の不一致またはRepeaterの設定ミス

**デバッグ**:

```php
public function submit(): void
{
    $data = $this->form->getState();
    dd($data);  // フォームデータの構造を確認
}
```

**確認事項**:
1. `getSendRewardSchema()` のフィールド名パラメータ
2. 保存処理でのフィールド名
3. Repeaterの `name` 属性

## デバッグのコツ

### 1. 段階的にdd()を配置

```php
// ステップ1: RewardDtoリスト
$rewardDtoList = $query->get()->map(fn($r) => $r->reward);
dd('Step 1', $rewardDtoList);

// ステップ2: RewardInfoコレクション
$rewardInfos = $this->getRewardInfos($rewardDtoList);
dd('Step 2', $rewardInfos);

// ステップ3: 個別のRewardInfo
dd('Step 3', $rewardInfos->get($record->id));
```

### 2. クエリログの確認

```php
// クエリログを有効化
DB::enableQueryLog();

// 処理実行
$this->addRewardInfoToPaginatedRecords($paginator);

// クエリログを確認
dd(DB::getQueryLog());
```

### 3. Tinkerで確認

```bash
sail artisan tinker

# Modelの確認
$record = App\Models\Mst\MstMissionReward::first();
$record->reward;  // RewardDtoが返るか？

# RewardInfoの生成確認
$dto = $record->reward;
$service = app(App\Services\Reward\RewardInfoGetHandleService::class);
$infos = $service->build(collect([$dto]))->getRewardInfos();
$infos->first();  // RewardInfoが返るか？
```
