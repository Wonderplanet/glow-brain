# Controllerでのバリデーション実装パターン

Controllerメソッド内でリクエストパラメータのバリデーションを実装する際のパターンを説明します。

## 基本パターン

### パターン1: 基本的なバリデーション

**YAML定義:**
```yaml
- name: Start
  path: "/api/stage/start"
  params:
    - name: mstStageId
      type: string
    - name: partyNo
      type: int
    - name: isChallengeAd
      type: bool
  method: POST
```

**Controller実装:**
```php
public function start(StageStartUseCase $useCase, Request $request): JsonResponse
{
    $validated = $request->validate([
        'mstStageId' => 'required',
        'partyNo' => 'required',
        'isChallengeAd' => 'required|boolean',
    ]);

    $resultData = $useCase->exec(
        $this->request->user(),
        $validated['mstStageId'],
        $validated['partyNo'],
        $validated['isChallengeAd'],
    );

    return $this->responseFactory->createStartResponse($resultData);
}
```

**ポイント:**
- `$request->validate()` を使用
- バリデーション済みの値は `$validated` 配列から取得
- UseCase の引数として渡す

### パターン2: パラメータなしのAPI

**YAML定義:**
```yaml
- name: Info
  path: "/api/user/info"
  params:  # パラメータなし
  method: GET
```

**Controller実装:**
```php
public function info(UserInfoUseCase $useCase, Request $request): JsonResponse
{
    // バリデーション不要
    $resultData = $useCase->exec($request->user());

    return $this->responseFactory->createInfoResponse($resultData);
}
```

**ポイント:**
- パラメータがない場合、バリデーションは不要
- `$request->validate()` を記述しない

### パターン3: オプショナルパラメータ

**YAML定義:**
```yaml
- name: ChangeEmblem
  path: "/api/user/change_emblem"
  params:
    - name: mstEmblemId
      type: string?  # オプショナル
  method: POST
```

**Controller実装:**
```php
public function changeEmblem(UserChangeEmblemUseCase $useCase): JsonResponse
{
    $validated = $this->request->validate([
        'mstEmblemId' => 'present',  // キーは必須、値は null でもOK
    ]);

    $user = $this->request->user();
    $mstEmblemId = $validated['mstEmblemId'];

    $resultData = $useCase->exec($user, $mstEmblemId);

    return $this->responseFactory->createUserChangeEmblemResponse($resultData);
}
```

**ポイント:**
- オプショナル型でも、キーの存在を要求する場合は `'present'` を使用
- 値が `null` でも許可される

## Requestオブジェクトの使い分け

### $this->request vs $request パラメータ

**$this->request（コンストラクタ注入）:**
```php
public function __construct(
    private Request $request,
    private StageResponseFactory $responseFactory,
) {
}

public function start(StageStartUseCase $useCase, Request $request): JsonResponse
{
    $validated = $this->request->validate([...]);

    $user = $this->request->user();  // ← $this->request を使用
    ...
}
```

**$request（メソッド注入）:**
```php
public function start(StageStartUseCase $useCase, Request $request): JsonResponse
{
    $validated = $request->validate([...]);  // ← どちらでもOK

    $user = $request->user();  // ← どちらでもOK
    ...
}
```

**推奨:**
- プロジェクト内で統一されている方を使用
- 既存のControllerのパターンに従う

## 値の取得パターン

### バリデーション済み値の取得

✅ **推奨: $validated 配列から取得**
```php
$validated = $request->validate([
    'mstStageId' => 'required',
    'partyNo' => 'required',
]);

$mstStageId = $validated['mstStageId'];  // ← 推奨
$partyNo = $validated['partyNo'];
```

❌ **非推奨: $request->input() で取得**
```php
$validated = $request->validate([...]);

$mstStageId = $request->input('mstStageId');  // ← 非推奨（バリデーション後でも使用しない）
```

**理由:**
- `$validated` 配列を使うことで、バリデーション済みであることが明確
- 型安全性が向上

### デフォルト値の扱い

**例: partyNo が存在しない場合は 0 にする**

```php
$validated = $request->validate([
    'mstStageId' => 'required',
    'partyNo' => 'required',
]);

$partyNo = $request->input('partyNo', 0);  // ← デフォルト値を指定
```

**注意:**
- バリデーションで `'required'` を指定している場合、デフォルト値は不要
- 本当にオプショナルなら、YAML定義も `int?` にすべき

## 複雑なパラメータのバリデーション

### ネストしたオブジェクト

**YAML定義:**
```yaml
- name: End
  params:
    - name: mstStageId
      type: string
    - name: inGameBattleLog
      type: InGameEndBattleLogData  # 複雑なオブジェクト
```

**Controller実装:**
```php
public function end(StageEndUseCase $useCase, Request $request): JsonResponse
{
    $platform = (int) $request->header(System::HEADER_PLATFORM);

    $validated = $request->validate([
        'mstStageId' => 'required',
        'inGameBattleLog' => 'required',  // 配列として扱う
    ]);

    $inGameBattleLog = $request->input('inGameBattleLog', []);

    $resultData = $useCase->exec(
        $this->request->user(),
        $platform,
        $validated['mstStageId'],
        $inGameBattleLog
    );

    return $this->responseFactory->createEndResponse($resultData);
}
```

**ポイント:**
- 複雑なオブジェクトは `'required'` のみで検証
- 内部構造の詳細検証はUseCaseやServiceレイヤーで実施

### 配列パラメータ

**YAML定義:**
```yaml
- name: rewards
  type: RewardData[]
```

**Controller実装:**
```php
$validated = $request->validate([
    'rewards' => 'required|array',
    'rewards.*' => 'required',  // 各要素が存在することを確認
]);
```

**より詳細な検証:**
```php
$validated = $request->validate([
    'rewards' => 'required|array',
    'rewards.*.resourceType' => 'required',
    'rewards.*.resourceId' => 'required',
    'rewards.*.resourceAmount' => 'required|integer',
]);
```

## ヘッダーからの値取得

### Platformヘッダー

```php
$platform = (int) $request->header(System::HEADER_PLATFORM);
```

**使用例:**
```php
public function continueDiamond(StageContinueDiamondUseCase $useCase, Request $request): JsonResponse
{
    $platform = (int) $request->header(System::HEADER_PLATFORM);
    $billingPlatform = $request->getBillingPlatform();

    $validated = $request->validate([
        'mstStageId' => 'required',
    ]);

    $resultData = $useCase->exec(
        $this->request->user(),
        $platform,
        $validated['mstStageId'],
        $billingPlatform
    );

    return $this->responseFactory->createContinueResponse($resultData);
}
```

## レスポンスパターン

### 標準JSONレスポンス

```php
return $this->responseFactory->createStartResponse($resultData);
```

### 空のJSONレスポンス（HeadOK）

**YAML定義:**
```yaml
response: HeadOK
```

**Controller実装:**
```php
return response()->json();
```

**例:**
```php
public function changeName(UserChangeNameUseCase $useCase, Request $request): JsonResponse
{
    $validated = $this->request->validate([
        'name' => 'required',
    ]);

    $user = $this->request->user();
    $newName = $validated['name'];

    $useCase->exec($user, $newName);

    return response()->json();  // ← HeadOK
}
```

## 実装チェックリスト

Controllerメソッドを実装する際の確認項目:

- [ ] YAML定義の `params` を確認したか？
- [ ] パラメータがない場合、バリデーションを省略したか？
- [ ] `$request->validate()` でバリデーションルールを記述したか？
- [ ] バリデーション済み値を `$validated` 配列から取得しているか？
- [ ] bool型のパラメータに `|boolean` を指定したか？
- [ ] オプショナル型を `'nullable'` または `'present'` で適切に扱っているか？
- [ ] ヘッダーから取得する値（platform等）を確認したか？
- [ ] レスポンス型が `HeadOK` の場合、`response()->json()` を返しているか？
- [ ] UseCaseに渡す引数の順序と型を確認したか？

## よくある間違い

### ❌ 間違い1: バリデーション後も $request->input() を使用

```php
$validated = $request->validate([
    'mstStageId' => 'required',
]);

$mstStageId = $request->input('mstStageId');  // ← 間違い
```

✅ **正しい:**
```php
$validated = $request->validate([
    'mstStageId' => 'required',
]);

$mstStageId = $validated['mstStageId'];  // ← 正しい
```

### ❌ 間違い2: bool型に型指定を忘れる

```php
$validated = $request->validate([
    'isChallengeAd' => 'required',  // ← bool型に |boolean を忘れている
]);
```

✅ **正しい:**
```php
$validated = $request->validate([
    'isChallengeAd' => 'required|boolean',  // ← 正しい
]);
```

### ❌ 間違い3: オプショナル型を required で扱う

**YAML:**
```yaml
- name: endAt
  type: DateTimeOffset?  # オプショナル
```

```php
$validated = $request->validate([
    'endAt' => 'required|date',  // ← 間違い（必須にしている）
]);
```

✅ **正しい:**
```php
$validated = $request->validate([
    'endAt' => 'nullable|date',  // ← 正しい
]);
```
