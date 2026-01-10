# YAML型とLaravelバリデーションルールの対応表

glow-schemaのYAML型をLaravelバリデーションルールに変換する際の対応表です。

## 基本型

### string

**YAML定義:**
```yaml
- name: mstStageId
  type: string
```

**バリデーションルール:**
```php
'mstStageId' => 'required'
```

**解説:**
- 文字列型は `'required'` のみで十分
- 追加の型指定（`|string`）は不要（Laravelのデフォルト動作）

### int / long

**YAML定義:**
```yaml
- name: partyNo
  type: int
```

**バリデーションルール:**
```php
'partyNo' => 'required'
```

**解説:**
- 整数型も `'required'` のみで十分
- PHPでは `int` と `long` は同じ扱い

### bool

**YAML定義:**
```yaml
- name: isChallengeAd
  type: bool
```

**バリデーションルール:**
```php
'isChallengeAd' => 'required|boolean'
```

**解説:**
- 真偽値型は `|boolean` を明示的に指定
- 受け入れられる値: `true`, `false`, `1`, `0`, `"1"`, `"0"`

### float

**YAML定義:**
```yaml
- name: rate
  type: float
```

**バリデーションルール:**
```php
'rate' => 'required|numeric'
```

**解説:**
- 浮動小数点数は `|numeric` を使用
- `|numeric` は整数・小数どちらも受け入れる

### DateTimeOffset

**YAML定義:**
```yaml
- name: startAt
  type: DateTimeOffset
```

**バリデーションルール:**
```php
'startAt' => 'required|date'
```

**解説:**
- 日時型は `|date` を使用
- ISO8601形式の文字列を受け入れる

## オプショナル型（`?` サフィックス）

### string?

**YAML定義:**
```yaml
- name: memo
  type: string?
```

**バリデーションルール:**
```php
'memo' => 'nullable'
```

**解説:**
- オプショナル型は `'nullable'` を使用
- 値が存在しない場合や `null` の場合も許可される

### int?

**YAML定義:**
```yaml
- name: count
  type: int?
```

**バリデーションルール:**
```php
'count' => 'nullable|integer'
```

**解説:**
- `'nullable'` と型ルール（`|integer`）を組み合わせる
- 値が存在する場合のみ整数型チェックが実行される

### DateTimeOffset?

**YAML定義:**
```yaml
- name: endAt
  type: DateTimeOffset?
```

**バリデーションルール:**
```php
'endAt' => 'nullable|date'
```

## 配列型（`[]` サフィックス）

### Type[]

**YAML定義:**
```yaml
- name: stageRewards
  type: StageRewardData[]
```

**バリデーションルール:**
```php
'stageRewards' => 'required|array'
```

**解説:**
- 配列型は `'required|array'` を使用
- 配列の各要素のバリデーションは別途定義可能

### Type[]? (オプショナル配列)

**YAML定義:**
```yaml
- name: rewards
  type: RewardData[]?
```

**バリデーションルール:**
```php
'rewards' => 'nullable|array'
```

**解説:**
- 配列そのものが `null` になる可能性がある
- 空配列 `[]` とは異なる

### 配列の各要素のバリデーション

**YAML定義:**
```yaml
- name: partyStatus
  type: PartyStatusData[]
```

配列の各要素が複雑なオブジェクトの場合、ネストしたバリデーションを定義できます。

**バリデーションルール:**
```php
'partyStatus' => 'required|array',
'partyStatus.*.usrUnitId' => 'required',
'partyStatus.*.mstUnitId' => 'required',
'partyStatus.*.hp' => 'required|integer',
```

**解説:**
- `.*` で配列の各要素を参照
- 各フィールドのバリデーションルールを個別に指定

## enum型

**YAML定義:**
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
        type: Difficulty
```

**バリデーションルール:**

### 方法1: `in` ルールを使用
```php
'difficulty' => 'required|in:Normal,Hard,Extra'
```

### 方法2: Enumクラスを使用（Laravel 9+）
```php
use App\Domain\Common\Enums\Difficulty;
use Illuminate\Validation\Rules\Enum;

'difficulty' => ['required', new Enum(Difficulty::class)]
```

**解説:**
- `in` ルールは許可される値を列挙
- Enumクラスを使用すると、より型安全

## 複雑なオブジェクト型

**YAML定義:**
```yaml
- name: inGameBattleLog
  type: InGameEndBattleLogData
```

InGameEndBattleLogData がネストしたオブジェクトの場合:

```yaml
data:
  - name: InGameEndBattleLog
    params:
      - name: defeatEnemyCount
        type: int
      - name: clearTimeMs
        type: int
      - name: partyStatus
        type: PartyStatusData[]
```

**バリデーションルール:**
```php
'inGameBattleLog' => 'required|array',
'inGameBattleLog.defeatEnemyCount' => 'required|integer',
'inGameBattleLog.clearTimeMs' => 'required|integer',
'inGameBattleLog.partyStatus' => 'required|array',
'inGameBattleLog.partyStatus.*' => 'required',
```

**解説:**
- オブジェクト型は `'required|array'` として扱う
- ネストしたフィールドはドット記法で指定

## 特殊ケース

### パラメータなし

**YAML定義:**
```yaml
- name: Info
  path: "/api/user/info"
  params:  # 空リスト
  method: GET
```

**バリデーションルール:**

バリデーション不要（メソッドに `$request->validate()` を記述しない）

```php
public function info(UserInfoUseCase $useCase, Request $request): JsonResponse
{
    // バリデーションなし
    $resultData = $useCase->exec($request->user());
    return $this->responseFactory->createInfoResponse($resultData);
}
```

### present ルール

**使用例:**
```php
'mstEmblemId' => 'present'
```

**意味:**
- キーが存在することを要求
- 値が `null` や空文字列でも許可される

**使用場面:**
- YAMLで `type: string?` だが、キー自体は必須の場合
- 空文字列やnullを明示的に送信したい場合

**例:**
```php
// UserController.php
$validated = $this->request->validate([
    'mstEmblemId' => 'present',  // null でもOKだが、キーは必須
]);
```

## 型マッピング一覧表

| YAML型 | バリデーションルール | 備考 |
|--------|------------------|------|
| `string` | `'required'` | |
| `int` | `'required'` | |
| `long` | `'required'` | intと同じ扱い |
| `bool` | `'required\|boolean'` | 明示的に指定 |
| `float` | `'required\|numeric'` | |
| `DateTimeOffset` | `'required\|date'` | ISO8601形式 |
| `string?` | `'nullable'` | |
| `int?` | `'nullable\|integer'` | |
| `bool?` | `'nullable\|boolean'` | |
| `DateTimeOffset?` | `'nullable\|date'` | |
| `Type[]` | `'required\|array'` | |
| `Type[]?` | `'nullable\|array'` | |
| `EnumType` | `'required\|in:Val1,Val2'` | または `new Enum()` |
| `ObjectType` | `'required\|array'` | ネストフィールドも定義 |

## チェックリスト

バリデーションルールを実装する際の確認項目:

- [ ] YAML定義の型を確認したか？
- [ ] オプショナル型（`?`）を見落としていないか？
- [ ] 配列型（`[]`）を正しく扱っているか？
- [ ] bool型に `|boolean` を明示したか？
- [ ] DateTimeOffset型に `|date` を指定したか？
- [ ] enum型の場合、許可される値を列挙したか？
- [ ] 複雑なオブジェクト型の場合、ネストフィールドも定義したか？
