# 実装例とよくあるケース

実際のControllerコードから抽出した、よくあるバリデーション実装パターンの例を示します。

## 例1: 基本的なAPI（/api/stage/start）

### YAML定義

**ファイル:** `glow-schema/Schema/Stage.yml`

```yaml
api:
  - name: Stage
    actions:
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
        response: StageStartResultData
```

### Controller実装

**ファイル:** `api/app/Http/Controllers/StageController.php:26`

```php
public function start(StageStartUseCase $useCase, Request $request): JsonResponse
{
    $validated = $request->validate([
        'mstStageId' => 'required',
        'partyNo' => 'required',
        'isChallengeAd' => 'required|boolean',
    ]);

    $partyNo = $request->input('partyNo', 0);
    $isChallengeAd = $request->input('isChallengeAd', false);

    $resultData = $useCase->exec(
        $this->request->user(),
        $validated['mstStageId'],
        $partyNo,
        $isChallengeAd,
    );

    return $this->responseFactory->createStartResponse($resultData);
}
```

**ポイント:**
- bool型は `|boolean` を明示
- デフォルト値を設定（ただし、`'required'` なので通常は不要）

## 例2: パラメータなしのAPI（/api/user/info）

### YAML定義

**ファイル:** `glow-schema/Schema/User.yml`

```yaml
api:
  - name: User
    actions:
      - name: Info
        path: "/api/user/info"
        params:  # パラメータなし
        method: GET
        response: UserInfoResultData
```

### Controller実装

**ファイル:** `api/app/Http/Controllers/UserController.php:30`

```php
public function info(UserInfoUseCase $useCase, Request $request): JsonResponse
{
    $resultData = $useCase->exec($request->user());

    return $this->responseFactory->createInfoResponse($resultData);
}
```

**ポイント:**
- パラメータがないので、バリデーション不要
- `$request->user()` のみ使用

## 例3: オプショナルパラメータ（/api/user/change_emblem）

### YAML定義

**ファイル:** `glow-schema/Schema/User.yml`

```yaml
- name: ChangeEmblem
  path: "/api/user/change_emblem"
  params:
    - name: mstEmblemId
      type: string  # 実際はnullable（コード参照）
  method: POST
  response: UserChangeEmblemResultData
```

### Controller実装

**ファイル:** `api/app/Http/Controllers/UserController.php:62`

```php
public function changeEmblem(UserChangeEmblemUseCase $useCase): JsonResponse
{
    $validated = $this->request->validate([
        'mstEmblemId' => 'present',  // キーは必須、値はnullでもOK
    ]);

    $user = $this->request->user();
    $mstEmblemId = $validated['mstEmblemId'];

    $resultData = $useCase->exec($user, $mstEmblemId);

    return $this->responseFactory->createUserChangeEmblemResponse($resultData);
}
```

**ポイント:**
- `'present'` ルールを使用
- キーの存在を要求するが、値は `null` でも許可

## 例4: 複雑なオブジェクト（/api/stage/end）

### YAML定義

**ファイル:** `glow-schema/Schema/Stage.yml`

```yaml
- name: End
  path: "/api/stage/end"
  params:
    - name: mstStageId
      type: string
    - name: inGameBattleLog
      type: InGameEndBattleLogData
  method: POST
  response: StageEndResultData

# InGameEndBattleLogData の定義
data:
  - name: InGameEndBattleLog
    obscure: true
    params:
      - name: defeatEnemyCount
        type: int
      - name: defeatBossEnemyCount
        type: int
      - name: score
        type: long
      - name: clearTimeMs
        type: int
      - name: partyStatus
        type: PartyStatusData[]
      - name: maxDamage
        type: int
      - name: discoveredEnemies
        type: DiscoveredEnemyData[]
```

### Controller実装

**ファイル:** `api/app/Http/Controllers/StageController.php:47`

```php
public function end(StageEndUseCase $useCase, Request $request): JsonResponse
{
    $platform = (int) $request->header(System::HEADER_PLATFORM);

    $validated = $request->validate([
        'mstStageId' => 'required',
        'inGameBattleLog' => 'required',
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
- ヘッダーから `platform` を取得
- 内部構造の詳細検証はUseCaseに委譲

## 例5: 複数パラメータのガチャAPI（/api/gacha/draw/item）

### YAML定義

**ファイル:** `glow-schema/Schema/Gacha.yml`

```yaml
- name: Item
  path: "/api/gacha/draw/item"
  params:
    - name: oprGachaId
      type: string
    - name: drewCount
      type: int
    - name: playNum
      type: int
    - name: costId
      type: string
    - name: costNum
      type: int
  method: POST
  response: GachaDrawResultData
```

### Controller実装

**ファイル:** `api/app/Http/Controllers/GachaController.php:91`

```php
public function drawItem(GachaDrawUseCase $useCase): JsonResponse
{
    $validated = $this->request->validate([
        'oprGachaId' => 'required',
        'drewCount' => 'required',
        'playNum' => 'required',
        'costId' => 'required',
        'costNum' => 'required',
    ]);

    $resultData = $useCase->exec(
        $this->request->user(),
        $validated['oprGachaId'],
        $validated['drewCount'],
        $validated['playNum'],
        $validated['costId'],
        $validated['costNum'],
        (int)$this->request->header(System::HEADER_PLATFORM),
        $this->request->getBillingPlatform(),
        CostType::ITEM
    );

    return $this->responseFactory->createDrawResponse($resultData);
}
```

**ポイント:**
- 複数のパラメータを `$validated` から取得
- ヘッダーとEnumを組み合わせて使用

## 例6: HeadOKレスポンス（/api/user/change_name）

### YAML定義

**ファイル:** `glow-schema/Schema/User.yml`

```yaml
- name: ChangeName
  path: "/api/user/change_name"
  params:
    - name: name
      type: string
  method: POST
  response: HeadOK
```

### Controller実装

**ファイル:** `api/app/Http/Controllers/UserController.php:37`

```php
public function changeName(UserChangeNameUseCase $useCase, Request $request): JsonResponse
{
    $validated = $this->request->validate([
        'name' => 'required',
    ]);

    $user = $this->request->user();
    $newName = $validated['name'];

    $useCase->exec($user, $newName);

    return response()->json();  // HeadOK: 空のJSONレスポンス
}
```

**ポイント:**
- `HeadOK` は `response()->json()` で空のレスポンスを返す
- ResponseFactory は使用しない

## 例7: アウトポスト強化（/api/outpost/enhance）

### YAML定義

**ファイル:** `glow-schema/Schema/Outpost.yml`

```yaml
- name: Enhance
  path: "/api/outpost/enhance"
  params:
    - name: mstOutpostEnhancementId
      type: string
    - name: level
      type: int
  method: POST
  response: OutpostEnhanceResultData
```

### Controller実装

**ファイル:** `api/app/Http/Controllers/OutpostController.php:22`

```php
public function enhance(OutpostEnhanceUseCase $useCase): JsonResponse
{
    $validated = $this->request->validate([
        'mstOutpostEnhancementId' => 'required',
        'level' => 'required',
    ]);

    $enhancementId = $validated['mstOutpostEnhancementId'];
    $level = $validated['level'];

    $resultData = $useCase->exec($this->request->user(), $enhancementId, $level);

    return $this->responseFactory->createEnhanceResponse($resultData);
}
```

**ポイント:**
- バリデーション後、変数に代入してから使用
- コードの可読性向上

## 例8: presentルールの使用（/api/outpost/change_artwork）

### YAML定義

**ファイル:** `glow-schema/Schema/Outpost.yml`

```yaml
- name: ChangeArtwork
  path: "/api/outpost/change_artwork"
  params:
    - name: mstOutpostId
      type: string
    - name: mstArtworkId
      type: string?  # オプショナル
  method: POST
  response: OutpostChangeArtworkResultData
```

### Controller実装

**ファイル:** `api/app/Http/Controllers/OutpostController.php:37`

```php
public function changeArtwork(OutpostChangeArtworkUseCase $useCase): JsonResponse
{
    $validated = $this->request->validate([
        'mstOutpostId' => 'required',
        'mstArtworkId' => 'present',  // キー必須、値はnullでもOK
    ]);

    $mstOutpostId = $validated['mstOutpostId'];
    $mstArtworkId = $validated['mstArtworkId'];

    $resultData = $useCase->exec($this->request->user(), $mstOutpostId, $mstArtworkId);

    return $this->responseFactory->createChangeArtworkResponse($resultData);
}
```

**ポイント:**
- オプショナル型でも、キーの存在を要求する場合は `'present'`
- `null` を明示的に送信できる

## 例9: GET APIでのバリデーション（/api/gacha/prize）

### YAML定義

**ファイル:** `glow-schema/Schema/Gacha.yml`

```yaml
- name: Prize
  path: "/api/gacha/prize"
  params:
    - name: oprGachaId
      type: string
  method: GET
  response: GachaPrizeResultData
```

### Controller実装

**ファイル:** `api/app/Http/Controllers/GachaController.php:24`

```php
public function prize(GachaPrizeUseCase $useCase): JsonResponse
{
    $validated = $this->request->validate([
        'oprGachaId' => 'required',
    ]);

    $resultData = $useCase->exec($validated['oprGachaId']);

    return $this->responseFactory->createPrizeResponse($resultData);
}
```

**ポイント:**
- GET APIでもバリデーションは同じパターン
- クエリパラメータとして送信される

## チェックリスト

実装例を参考にする際の確認項目:

- [ ] YAML定義のparamsと実装が一致しているか？
- [ ] bool型に `|boolean` を指定しているか？
- [ ] オプショナル型を適切に扱っているか？（`'nullable'` または `'present'`）
- [ ] ヘッダーから取得する値があるか？（platform, billing等）
- [ ] HeadOKレスポンスの場合、`response()->json()` を返しているか？
- [ ] バリデーション済み値を `$validated` から取得しているか？
- [ ] 複雑なオブジェクトの内部検証はUseCaseに委譲しているか？

## まとめ

よくある実装パターン:

1. **基本パターン:** `'required'` のみ
2. **bool型:** `'required|boolean'`
3. **オプショナル:** `'nullable'` または `'present'`
4. **複雑なオブジェクト:** `'required'` のみ（詳細検証はUseCaseへ）
5. **HeadOK:** `response()->json()`
6. **パラメータなし:** バリデーション省略

これらのパターンを組み合わせて、YAML定義に基づいた適切なバリデーションを実装してください。
