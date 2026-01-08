# 型システムとサフィックス規則

glow-schemaのYAMLで使用されるデータ型と命名規則を説明します。

## 基本型

### プリミティブ型

| YAML型 | PHP型 | 説明 | 例 |
|--------|------|------|-----|
| `string` | `string` | 文字列 | `"abc"`, `"12345"` |
| `int` | `int` | 整数 | `123`, `-10` |
| `long` | `int` | 長整数（PHPではint） | `9999999999` |
| `float` | `float` | 浮動小数点数 | `1.5`, `0.99` |
| `bool` | `bool` | 真偽値 | `true`, `false` |
| `DateTimeOffset` | `string` (ISO8601) | 日時 | `"2025-11-21T10:30:00Z"` |

### 特殊な型表記

#### オプショナル型（`?` サフィックス）

```yaml
- name: endAt
  type: DateTimeOffset?  # ← ? がついている = nullable
```

**意味:** この値は `null` になる可能性があります。

**PHP実装:**
```php
// Controller バリデーション
$validated = $request->validate([
    'endAt' => 'nullable|date',  // ← nullable を指定
]);

// ResponseFactory
'endAt' => $data->endAt ? StringUtil::convertToISO8601($data->endAt) : null,
```

#### 配列型（`[]` サフィックス）

```yaml
- name: stageRewards
  type: StageRewardData[]  # ← [] がついている = 配列
```

**意味:** この値は配列（リスト）です。

**PHP実装:**
```php
// Controller バリデーション
$validated = $request->validate([
    'stageRewards' => 'array',
    'stageRewards.*' => 'required',  // 配列の各要素
]);

// ResponseFactory
'stageRewards' => array_map(
    fn($reward) => $this->responseDataFactory->createStageRewardData($reward),
    $result->stageRewards
),
```

### enum型

```yaml
enum:
  - name: Difficulty
    params:
      - name: Normal
      - name: Hard
      - name: Extra

data:
  - name: MstQuest
    params:
      - name: difficulty
        type: Difficulty  # ← enum型
```

**PHP実装:**
```php
// Controller バリデーション
$validated = $request->validate([
    'difficulty' => 'required|in:Normal,Hard,Extra',
]);

// または Enum クラスを使用
$validated = $request->validate([
    'difficulty' => ['required', new Enum(Difficulty::class)],
]);
```

## サフィックス規則

### Mst* (マスターデータ)

```yaml
- name: MstStage
  obscure: true
  params:
    - name: id
      type: string
    - name: costStamina
      type: int
```

**用途:**
- ゲームの基本設定データ（マスターテーブル）
- 通常は運営が管理し、ユーザーは変更できない
- `mst_` プレフィックスのテーブルに対応

**例:**
- `MstStage` → `mst_stage` テーブル
- `MstUnit` → `mst_unit` テーブル
- `MstQuest` → `mst_quest` テーブル

### Usr* (ユーザーデータ)

```yaml
- name: UsrParameter
  obscure: true
  params:
    - name: level
      type: int
    - name: exp
      type: int
    - name: stamina
      type: int
```

**用途:**
- プレイヤー個別のデータ（ユーザーテーブル）
- ゲームプレイによって変化する
- `usr_` プレフィックスのテーブルに対応

**例:**
- `UsrParameter` → `usr_parameter` テーブル
- `UsrStage` → `usr_stage` テーブル
- `UsrUnit` → `usr_unit` テーブル

### Opr* (運営データ)

```yaml
- name: OprGacha
  obscure: true
  params:
    - name: id
      type: string
    - name: startAt
      type: DateTimeOffset
    - name: endAt
      type: DateTimeOffset
```

**用途:**
- 運営が管理する期間限定のデータ
- イベント、ガチャ、キャンペーン等
- `opr_` プレフィックスのテーブルに対応

**例:**
- `OprGacha` → `opr_gacha` テーブル
- `OprEvent` → `opr_event` テーブル

### Log* (ログデータ)

```yaml
- name: LogStage
  obscure: true
  params:
    - name: mstStageId
      type: string
    - name: clearTimeMs
      type: int
```

**用途:**
- ゲームプレイのログ、履歴
- `log_` プレフィックスのテーブルに対応

### *Result (結果型)

```yaml
- name: StageStartResult
  obscure: true
  params:
    - name: usrParameter
      type: UsrParameterData
    - name: usrInGameStatus
      type: UsrInGameStatusData
```

**用途:**
- API レスポンスのトップレベル型
- 複数のデータをまとめて返す

**命名規則:**
- API アクション名 + `Result`
- 例: `Start` アクション → `StageStartResult`
- 例: `End` アクション → `StageEndResult`

**レスポンスでの使用:**
```yaml
api:
  - name: Stage
    actions:
      - name: Start
        response: StageStartResultData  # ← Result型に Data サフィックス
```

### *Data サフィックス

```yaml
data:
  - name: UsrParameter  # ← YAML定義では Data なし
    obscure: true
    params:
      - name: level
        type: int

# 他の型から参照する際は Data サフィックスをつける
- name: StageStartResult
  params:
    - name: usrParameter
      type: UsrParameterData  # ← 参照時は Data サフィックス
```

**ルール:**
- `data` セクションで定義する際は `Data` サフィックスなし
- 他の型から参照する際は `Data` サフィックスをつける
- API の `response` でも `Data` サフィックスをつける

**PHP実装での対応:**
- クラス名は `Data` サフィックスなし（例: `UsrParameter`）
- ResponseFactory では `createUsrParameterData()` のようにメソッド名に `Data` をつける場合がある

## 複雑な型の例

### ネストした型

```yaml
- name: StageEndResult
  obscure: true
  params:
    - name: stageRewards
      type: StageRewardData[]  # ← 配列
    - name: userLevel
      type: UserLevelUpData    # ← オブジェクト
    - name: usrConditionPacks
      type: UsrConditionPackData[]  # ← 配列

# ネストした型の定義
- name: StageReward
  obscure: true
  params:
    - name: rewardCategory
      type: RewardCategory  # ← enum型
    - name: reward
      type: RewardData     # ← さらにネスト
```

### オプショナル配列

```yaml
- name: usrLevelReward
  type: UsrLevelRewardData[]?  # ← 配列かつオプショナル
```

**意味:** 配列そのものが `null` になる可能性があります。空配列 `[]` とは異なります。

## 日時型の特別な扱い

### DateTimeOffset 型

```yaml
- name: startAt
  type: DateTimeOffset
- name: endAt
  type: DateTimeOffset?  # オプショナル
```

**PHP実装での注意:**

✅ **正しい実装:**
```php
// ResponseFactory
'startAt' => StringUtil::convertToISO8601($data->startAt),
'endAt' => $data->endAt ? StringUtil::convertToISO8601($data->endAt) : null,
```

❌ **間違った実装:**
```php
// 変換を忘れている
'startAt' => $data->startAt,  // DateTime オブジェクトのまま

// null チェックを忘れている
'endAt' => StringUtil::convertToISO8601($data->endAt),  // endAt が null の場合エラー
```

**ISO8601 形式の例:**
```
2025-11-21T10:30:00Z
2025-11-21T19:30:00+09:00
```

## 型マッピング一覧表

| YAML型 | Controller バリデーション | PHP変数型 | ResponseFactory |
|--------|------------------------|----------|----------------|
| `string` | `'required\|string'` | `string` | そのまま返す |
| `int` | `'required\|integer'` | `int` | そのまま返す |
| `bool` | `'required\|boolean'` | `bool` | そのまま返す |
| `float` | `'required\|numeric'` | `float` | そのまま返す |
| `DateTimeOffset` | `'required\|date'` | `Carbon` | `StringUtil::convertToISO8601()` |
| `string?` | `'nullable\|string'` | `?string` | `$val ?? null` |
| `int?` | `'nullable\|integer'` | `?int` | `$val ?? null` |
| `DateTimeOffset?` | `'nullable\|date'` | `?Carbon` | `$val ? StringUtil::convertToISO8601($val) : null` |
| `Type[]` | `'array'` | `array` | `array_map()` で変換 |
| `Type[]?` | `'nullable\|array'` | `?array` | `$val ? array_map() : null` |
| `EnumType` | `'in:Val1,Val2'` | `string` | そのまま返す |

## チェックリスト

型を確認する際のチェック項目:

- [ ] 基本型（string, int, bool等）を正しく理解したか？
- [ ] オプショナル型（`?`）を見落としていないか？
- [ ] 配列型（`[]`）を正しく認識したか？
- [ ] enum型の場合、enum定義を確認したか？
- [ ] DateTimeOffset型の場合、ISO8601変換が必要と理解したか？
- [ ] ネストした型（*Data）の定義を確認したか？
- [ ] サフィックス規則（Mst*, Usr*, *Result, *Data）を理解したか？
